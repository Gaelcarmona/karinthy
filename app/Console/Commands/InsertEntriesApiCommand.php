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

        $this->info('récupération des pages à traiter     ' .  $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        $entries = Entry::doesntHave('availableChildEntries')
            ->whereBetween('id', [$idStart, $idEnd])
            ->get();
        $countEntries = count($entries);
        $this->info($countEntries . ' pages à traiter     ' .  $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));

        foreach ($entries as $entry) {
            $client = new Client();
            try {                  
            $this->info('Traitement: ' . $entry->title . ', restant ' . $countEntries . '/' . count($entries) . ' ' .  $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);

            $url = "https://fr.wikipedia.org/w/api.php?action=query&titles={$entry->url}&prop=links&redirects&format=json&formatversion=2&pllimit=max";

            $response = $client->get($url);
            $statusCode = $response->getStatusCode();

            // Vérifier le code de statut de la réponse
            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);

                if (isset($data['query']['pages']['0']['missing'])) {

                    $this->info('Suppression: ' . $entry->title  . ' ' .  $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);
                    $entry->delete();
                    $countEntries--;
                    continue;
                
                }

                if (isset($data['query']['redirects']['0']['to'])) {

                    $this->info('Redirection vers : ' . $data['query']['redirects']['0']['to'] . ', suppression de ' . $entry->title  . ' ' .  $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);

                    $parentsOfOldEntry = AvailableEntry::query()->where('child_entry_id', $entry->id)->pluck('parent_entry_id')->toArray();

                    $parentEntryTitle = $data['query']['redirects']['0']['to'];
                    $parentEntryUrl = urlencode(str_replace(' ', '_', $parentEntryTitle));
                    $newEntry = Entry::query()
                        ->where('url', $parentEntryUrl)
                        ->firstOrCreate([
                            'url' => $parentEntryUrl,
                            'title' => $parentEntryTitle,
                        ]);

                    Redirect::firstOrCreate([
                        'title' => $entry->title,
                        'url' => $entry->url,
                        'redirect_to_entry_id' => $newEntry->id,
                    ]);
                    $entry->delete();
                    $entry = $newEntry;
                    
                    foreach ($parentsOfOldEntry as $parentId) {
                        AvailableEntry::query()
                        ->where('parent_entry_id', $parentId)
                        ->where('child_entry_id', $entry->id)
                        ->firstOrCreate([
                            'parent_entry_id' => $parentId,
                            'child_entry_id' => $entry->id,
                        ]);
                    }
                    if ($entry->has('availableChildEntries')) {
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

                $countEntries--;
            } else {
                $countEntries--;
                $this->error('Erreur lors de l\'accès à la page ' . $entry->url . ' - Code de statut : ' . $statusCode . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
                continue;
            }
            } catch (\Exception $e) {
                $this->error('Erreur lors de l\'accès à la page ' . $entry->url . ' - ' . $e->getMessage());
                Log::error([$entry->id,$e->getMessage()]); 
                continue;
            }
        }
    }

    public function StoreLinksOnPage($links, $parentEntry)
    {
        foreach ($links as $link) {
            $childEntryTitle = $link['title'];
            $childEntryUrl = urlencode(str_replace(' ', '_', $childEntryTitle));

            $childEntry = Entry::query()
                ->where('url', $childEntryUrl)
                ->firstOrCreate([
                    'url' => $childEntryUrl,
                    'title' => $childEntryTitle,
                ]);
            if ($parentEntry->id !== $childEntry->id) {
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
