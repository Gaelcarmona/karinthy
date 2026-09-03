<?php

namespace App\Console\Commands;

use App\Services\Wikipedia\SqlDumpReader;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Peuple `pages` et `page_redirects` depuis les dumps officiels de Wikimedia.
 *
 * L'API ne convient pas à ce travail : énumérer les 2,78 M d'articles puis
 * leurs liens demanderait des centaines de milliers de requêtes. Les dumps
 * donnent le même contenu, exact et complet, en une poignée de fichiers.
 */
class ImportPagesCommand extends Command
{
    protected $signature = 'karinthy:import-pages
                            {--dump-dir= : Dossier des dumps (par défaut storage/app/dumps)}
                            {--redownload : Retélécharge les dumps même s\'ils sont déjà là}';

    protected $description = 'Importe les articles et les redirections de fr.wikipedia depuis les dumps officiels';

    private const BASE_URL = 'https://dumps.wikimedia.org/frwiki/latest/';

    private const USER_AGENT = 'Karinthy/1.0 (https://github.com/Gaelcarmona/karinthy)';

    /** Lignes envoyées par requête d'insertion. */
    private const INSERT_CHUNK = 2000;

    /** Espace de noms principal : les articles. */
    private const MAIN_NAMESPACE = '0';

    private string $dumpDir;

    public function handle(): int
    {
        $this->dumpDir = $this->option('dump-dir') ?: storage_path('app/dumps');

        if (! is_dir($this->dumpDir) && ! mkdir($this->dumpDir, 0755, true)) {
            $this->error("Impossible de créer {$this->dumpDir}");

            return self::FAILURE;
        }

        try {
            $pageDump = $this->dump('frwiki-latest-page.sql.gz');
            $redirectDump = $this->dump('frwiki-latest-redirect.sql.gz');

            $this->importPages($pageDump);
            $this->resolveRedirects($redirectDump);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info(number_format(DB::table('pages')->count(), 0, ',', ' ').' articles');
        $this->info(number_format(DB::table('page_redirects')->count(), 0, ',', ' ').' redirections');

        return self::SUCCESS;
    }

    /**
     * Télécharge le dump s'il n'est pas déjà présent et renvoie son chemin.
     */
    private function dump(string $file): string
    {
        $path = $this->dumpDir.DIRECTORY_SEPARATOR.$file;

        if (is_file($path) && ! $this->option('redownload')) {
            $this->line("<info>{$file}</info> déjà présent (".$this->humanBytes(filesize($path)).')');

            return $path;
        }

        $context = stream_context_create(['http' => ['header' => 'User-Agent: '.self::USER_AGENT]]);
        $source = @fopen(self::BASE_URL.$file, 'rb', false, $context);

        if ($source === false) {
            throw new RuntimeException("Téléchargement impossible : {$file}");
        }

        $total = $this->contentLength($http_response_header ?? []);
        $target = fopen($path.'.part', 'wb');
        $bar = $this->output->createProgressBar($total ?: 0);
        $bar->setFormat(" {$file}\n [%bar%] %percent:3s%% %elapsed%");
        $bar->start();

        while (! feof($source)) {
            $chunk = fread($source, 1 << 20);

            if ($chunk === false) {
                break;
            }

            fwrite($target, $chunk);
            $bar->advance(strlen($chunk));
        }

        $bar->finish();
        $this->newLine(2);

        fclose($source);
        fclose($target);
        rename($path.'.part', $path);

        return $path;
    }

    /**
     * Première passe : les articles d'un côté, les pages de redirection de
     * l'autre — sans leur cible, que seul le dump `redirect` connaît.
     */
    private function importPages(string $path): void
    {
        $this->line('Lecture de <info>page.sql.gz</info>…');

        DB::table('pages')->truncate();
        DB::table('page_redirects')->truncate();

        $pages = [];
        $redirects = [];
        $read = 0;

        foreach ((new SqlDumpReader($path))->rows() as $row) {
            $read++;

            if ($read % 1_000_000 === 0) {
                $this->line('  '.number_format($read, 0, ',', ' ').' lignes lues…');
            }

            // page_id, page_namespace, page_title, page_is_redirect, …, page_random
            if ($row[1] !== self::MAIN_NAMESPACE) {
                continue;
            }

            $id = (int) $row[0];
            $title = str_replace('_', ' ', (string) $row[2]);

            if ($row[3] === '1') {
                $redirects[] = ['id' => $id, 'title' => $title, 'page_id' => 0];

                if (count($redirects) >= self::INSERT_CHUNK) {
                    DB::table('page_redirects')->insertOrIgnore($redirects);
                    $redirects = [];
                }

                continue;
            }

            $pages[] = [
                'id' => $id,
                'title' => $title,
                'random' => (float) $row[5],
                'outbound_count' => 0,
                'inbound_count' => 0,
            ];

            if (count($pages) >= self::INSERT_CHUNK) {
                DB::table('pages')->insertOrIgnore($pages);
                $pages = [];
            }
        }

        if ($pages !== []) {
            DB::table('pages')->insertOrIgnore($pages);
        }

        if ($redirects !== []) {
            DB::table('page_redirects')->insertOrIgnore($redirects);
        }

        $this->line('  '.number_format($read, 0, ',', ' ').' lignes lues au total.');
    }

    /**
     * Seconde passe : rattacher chaque redirection à l'article qu'elle vise.
     *
     * Le dump `redirect` donne la cible par son titre. Le rapprochement se fait
     * en SQL, sur l'index unique des titres : garder une table de 2,78 M de
     * titres en mémoire PHP coûterait plusieurs centaines de mégaoctets.
     */
    private function resolveRedirects(string $path): void
    {
        $this->line('Lecture de <info>redirect.sql.gz</info>…');

        Schema::dropIfExists('redirect_targets');
        Schema::create('redirect_targets', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('title', 255);
        });

        $targets = [];
        $read = 0;

        foreach ((new SqlDumpReader($path))->rows() as $row) {
            $read++;

            // rd_from, rd_namespace, rd_title, rd_interwiki, rd_fragment
            if ($row[1] !== self::MAIN_NAMESPACE) {
                continue;
            }

            $targets[] = [
                'id' => (int) $row[0],
                'title' => str_replace('_', ' ', (string) $row[2]),
            ];

            if (count($targets) >= self::INSERT_CHUNK) {
                DB::table('redirect_targets')->insertOrIgnore($targets);
                $targets = [];
            }
        }

        if ($targets !== []) {
            DB::table('redirect_targets')->insertOrIgnore($targets);
        }

        $this->line('  '.number_format($read, 0, ',', ' ').' lignes lues.');
        $this->line('Rapprochement des cibles…');

        DB::statement('
            UPDATE page_redirects r
            JOIN redirect_targets t ON t.id = r.id
            JOIN pages p ON p.title = t.title
            SET r.page_id = p.id
        ');

        // Redirections vers une page inexistante, un autre espace de noms ou une
        // autre redirection : elles ne mènent à aucun article.
        $orphans = DB::table('page_redirects')->where('page_id', 0)->delete();

        Schema::dropIfExists('redirect_targets');

        $this->line('  '.number_format($orphans, 0, ',', ' ').' redirections sans article cible écartées.');
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function contentLength(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('/^content-length:\s*(\d+)/i', $header, $m) === 1) {
                return (int) $m[1];
            }
        }

        return 0;
    }

    private function humanBytes(int $bytes): string
    {
        return $bytes >= 1 << 20
            ? round($bytes / (1 << 20)).' Mo'
            : round($bytes / (1 << 10)).' Ko';
    }
}
