<?php

namespace App\Console\Commands;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ChildEntriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:child-entries-command {idStart} {idEnd}';

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

        $dateDebut = Carbon::now();
        $idStart = $this->argument('idStart');
        $idEnd = $this->argument('idEnd');

        $this->info('récupération des pages à traiter     ' .  $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        $entries = Entry::doesntHave('availableChildEntries')
            ->whereBetween('id', [$idStart, $idEnd])
            ->get();
        $countEntries = count($entries);
        $this->info($countEntries . ' pages à traiter     ' .  $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        
        foreach ($entries as $entry) {
            $client = new Client();
            try {
                $response = $client->get('https://fr.wikipedia.org/wiki/' . $entry->url);
                $statusCode = $response->getStatusCode();

                // Vérifier le code de statut de la réponse
                if ($statusCode === 200) {
                    // Le lien est accessible, continuer le traitement
                    $crawler = new Crawler((string) $response->getBody());
                    $links = $crawler->filter('a')->extract(['href']);

                    $links = array_filter($links, function ($link) {
                        return (str_starts_with($link, '/wiki/')
                            && !preg_match('(Sp%C3%A9cial:|Aide:|Fichier:|Discussion:|Wikip%C3%A9dia:|Portail:Accueil|Mod%C3%A8le:|Utilisateur:|Discussion_utilisateur:|Projet:|Discussion_Projet:|501c|Cat%C3%A9gorie:Accueil)', $link)
                        );
                    });
                    $links = array_unique($links);

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

                        if ($entry->id !== $linkEntry->id) {
                            $availableInEntry = AvailableEntry::query()
                                ->where('parent_entry_id', $entry->id)
                                ->where('child_entry_id', $linkEntry->id)
                                ->firstOrCreate([
                                    'parent_entry_id' => $entry->id,
                                    'child_entry_id' => $linkEntry->id,
                                ]);
                        }
                    }
                    $countEntries--;
                    $this->info('La page suivante est traité: ' . $entry->title . ', restant ' . $countEntries . '/' . count($entries) . ' ' .  $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids entre" . $idStart . ' et ' . $idEnd);
                } else {
                    $this->error('Erreur lors de l\'accès à la page ' . $entry->url . ' - Code de statut : ' . $statusCode . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
                    continue;
                }
            } catch (\Exception $e) {
                $this->error('Erreur lors de l\'accès à la page ' . $entry->url . ' - ' . $e->getMessage());
                continue;
            }
        }
    }
}
