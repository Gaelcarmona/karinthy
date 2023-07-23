<?php

namespace App\Console\Commands;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class EntryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:entry-command {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $client = new Client();

        try {
            $response = $client->get('https://fr.wikipedia.org/wiki/' . $url);
            $statusCode = $response->getStatusCode();

            // Vérifier le code de statut de la réponse
            if ($statusCode === 200) {
                $crawler = new Crawler((string) $response->getBody());
                $links = $crawler->filter('a')->extract(['href']);
                $links = array_filter($links, function ($link) {
                    return (str_starts_with($link, '/wiki/')
                        && !preg_match('(Sp%C3%A9cial:|Aide:|Fichier:|Discussion:|Wikip%C3%A9dia:|Portail:Accueil|Mod%C3%A8le:|Utilisateur:|Discussion_utilisateur:|Projet:|Discussion_Projet:|501c|Cat%C3%A9gorie:Accueil)', $link)
                    );
                });
                $links = array_unique($links);

                $title = str_replace('_', ' ', urldecode($url));
                $parentEntry = Entry::query()
                    ->where('url', $url)
                    ->firstOrCreate([
                        'url' => $url,
                        'title' => $title,
                    ]);


                foreach ($links as $link) {

                    if (strstr($link, '#', true)) {
                        $link = strstr($link, '#', true);
                    }
                    $link = str_replace('/wiki/', '', $link);
                    $title = str_replace('_', ' ', urldecode($link));

                    $linkEntry = Entry::query()
                        ->where('url', $link)
                        ->firstOrCreate([
                            'url' => $link,
                            'title' => $title,
                        ]);

                    if ($parentEntry->id !== $linkEntry->id) {
                        $availableInEntry = AvailableEntry::query()
                            ->where('parent_entry_id', $parentEntry->id)
                            ->where('child_entry_id', $linkEntry->id)
                            ->firstOrCreate([
                                'parent_entry_id' => $parentEntry->id,
                                'child_entry_id' => $linkEntry->id,
                            ]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error($statusCode);
        }
    }
}
