<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Entry;
use App\Models\Redirect;
use App\Models\AvailableEntry;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


class InsertEntriesApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:insert-entries-api-command {idStart} {idEnd}';

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

        $this->info('récupération des pages à traiter     ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        $entries = Entry::query()
            ->whereBetween('id', [$idStart, $idEnd])
            ->where('treated_at', '=', null)
            ->get();
        $countEntries = count($entries);
        $this->info($countEntries . ' pages à traiter     ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        $staticCountEntries = $countEntries;
        foreach ($entries as $entry) {
            $client = new Client();
            try {
                $this->info('Traitement: ' . $entry->title . ', restant ' . $countEntries . '/' . $staticCountEntries . ' ' . $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);
                if (preg_match('(Spécial:|Aide:|Fichier:|Discussion:|Wikipédia:|Portail:Accueil|Modèle:|Utilisateur:|Projet:|501c|Catégorie:Accueil|Référence:|MediaWiki:|Discussion utilisateur:|Discussion Projet:|Utilisatrice:|Module:|Discussion modèle:|Catégorie:Article|Discussion Portail)', $entry->title)) {
                    $this->info('Suppression avant ouverture: ' . $entry->title . ' ' . $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);
                    $entry->not_a_page = 1;
                    $entry->treated_at = Carbon::now();
                    $entry->save();
                    $countEntries--;
                    continue;
                }

                $url = "https://fr.wikipedia.org/w/api.php?action=query&titles={$entry->url}&prop=links&redirects&format=json&formatversion=2&pllimit=max";

                $response = $client->get($url);
                $statusCode = $response->getStatusCode();

                // Vérifier le code de statut de la réponse
                if ($statusCode === 200) {
                    $data = json_decode($response->getBody(), true);

                    if (isset($data['query']['pages']['0']['missing'])) {

                        $this->info('Suppression: ' . $entry->title . ' ' . $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);
                        $entry->not_a_page = 1;
                        $entry->treated_at = Carbon::now();
                        $entry->save();
                        $countEntries--;
                        continue;
                    }

                    if (isset($data['query']['redirects']['0']['to'])) {

                        $this->info('Redirection vers : ' . $data['query']['redirects']['0']['to'] . ', suppression de ' . $entry->title . ' ' . $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);

                        $availableParentEntries = $entry->load('availableParentEntries')->availableParentEntries;

                        $parentEntryTitle = $data['query']['redirects']['0']['to'];
                        $parentEntryUrl = urlencode(str_replace(' ', '_', $parentEntryTitle));
                        $newEntry = Entry::query()
                            ->where('title', $parentEntryTitle)
                            ->firstOrCreate([
                                'url' => $parentEntryUrl,
                                'title' => $parentEntryTitle,
                            ]);

                        $entry->redirect_to = $newEntry->id;
                        $entry->treated_at = Carbon::now();
                        $entry->save();
                        $entry = $newEntry;

                        foreach ($availableParentEntries as $availableParentEntry) {
                            AvailableEntry::query()
                                ->where('parent_entry_id', $availableParentEntry->parent_entry_id)
                                ->where('child_entry_id', $entry->id)
                                ->firstOrCreate([
                                    'parent_entry_id' => $availableParentEntry->parent_entry_id,
                                    'child_entry_id' => $entry->id,
                                ]);
                            $availableParentEntry->delete();
                        }
                        if ($entry->treated_at != null) {
                            $countEntries--;
                            continue;
                        }
                    }

                    if (isset($data['query']['pages']['0']['links'])) {

                        $this->StoreLinksOnPage($data['query']['pages']['0']['links'], $entry);
                    }

                    do {
                        if (isset($data['continue']['plcontinue'])) {

                            $plcontinue = $data['continue']['plcontinue'];
                            $url = "https://fr.wikipedia.org/w/api.php?action=query&titles={$entry->url}&prop=links&format=json&formatversion=2&pllimit=max&plcontinue={$plcontinue}";
                            $client = new Client();
                            $response = $client->get($url);
                            $data = json_decode($response->getBody(), true);

                            if (isset($data['query']['pages']['0']['links'])) {

                                $this->StoreLinksOnPage($data['query']['pages']['0']['links'], $entry);
                            }
                        }
                    } while (isset($data['continue']['plcontinue']));
                    $entry->treated_at = Carbon::now();
                    $entry->save();
                    $countEntries--;
                } else {
                    $countEntries--;
                    $this->error('Erreur lors de l\'accès à la page ' . $entry->url . ' - Code de statut : ' . $statusCode . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
                    continue;
                }
            } catch (\Exception $e) {
                $countEntries--;
                $this->error('Erreur lors de l\'accès à la page ' . $entry->url . ' - ' . $e->getMessage());
                Log::error([$entry->id, $e->getMessage()]);
                continue;
            }
        }
    }

    public function StoreLinksOnPage($links, $parentEntry)
    {
        foreach ($links as $link) {
            if (!preg_match('(Spécial:|Aide:|Fichier:|Discussion:|Wikipédia:|Portail:Accueil|Modèle:|Utilisateur:|Projet:|501c|Catégorie:Accueil|Référence:|MediaWiki:|Discussion utilisateur:|Discussion Projet:|Utilisatrice:|Module:|Discussion modèle:|Catégorie:Article|Discussion Portail)', $link['title'])) {
                $childEntryTitle = $link['title'];
                $childEntryUrl = urlencode(str_replace(' ', '_', $childEntryTitle));

                $childEntry = Entry::query()
                    ->where('title', $childEntryTitle)
                    ->firstOrCreate([
                        'url' => $childEntryUrl,
                        'title' => $childEntryTitle,
                    ]);
                if (($parentEntry->id !== $childEntry->id) && ($parentEntry->title !== $childEntryTitle)) {
                    AvailableEntry::query()
                        ->where('parent_entry_id', $parentEntry->id)
                        ->where('child_entry_id', $childEntry->id)
                        ->firstOrCreate([
                            'parent_entry_id' => $parentEntry->id,
                            'child_entry_id' => $childEntry->id,
                        ]);
                }
            }
        }
    }
}
