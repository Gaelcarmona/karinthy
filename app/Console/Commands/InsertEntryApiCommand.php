<?php

namespace App\Console\Commands;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class InsertEntryApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:insert-entry-api-command {prompt}';

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
        $prompt = $this->argument('prompt');
        $url = "https://fr.wikipedia.org/w/api.php?action=query&titles={$prompt}&prop=links&format=json&formatversion=2&pllimit=max";

        $client = new Client();
        $response = $client->get($url);
        $data = json_decode($response->getBody(), true);
        $parentEntryTitle = $data['query']['pages']['0']['title'];
        $parentEntryUrl = urlencode(str_replace(' ', '_', $parentEntryTitle));

        $parentEntry = Entry::query()
            ->where('url', $parentEntryUrl)
            ->firstOrCreate([
                'url' => $parentEntryUrl,
                'title' => $parentEntryTitle,
            ]);

        $this->StoreLinksOnPage($data['query']['pages']['0']['links'], $parentEntry);
        do {
            if (isset($data['continue']['plcontinue'])) {
                $plcontinue = $data['continue']['plcontinue'];
                $url = "https://fr.wikipedia.org/w/api.php?action=query&titles={$prompt}&prop=links&format=json&formatversion=2&pllimit=max&plcontinue={$plcontinue}";
                $client = new Client();
                $response = $client->get($url);
                $data = json_decode($response->getBody(), true);
                $this->StoreLinksOnPage($data['query']['pages']['0']['links'], $parentEntry);
            }
        } while (isset($data['continue']['plcontinue']));
        $this->info('traitement de la page ' . $parentEntry->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
    }

    public function StoreLinksOnPage($links, $parentEntry)
    {
        foreach ($links as $link) {
            if (!preg_match('(Spécial:|Aide:|Fichier:|Discussion:|Wikipédia:|Portail:Accueil|Modèle:|Utilisateur:|Projet:|501c|Catégorie:Accueil|Référence:|MediaWiki:|Discussion utilisateur:|Discussion Projet:|Utilisatrice:|Module:|Discussion modèle:|Catégorie:Article|Discussion Portail)', $link['title'])) {
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
}
