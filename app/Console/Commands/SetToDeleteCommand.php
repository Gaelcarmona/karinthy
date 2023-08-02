<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SetToDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:set-to-delete-command';

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
        $entries = Entry::query()
        ->where('title', 'like', '%' .'Catégorie:Article:' . '%')
        ->orWhere('title', 'like', '%' .'Discussion modèle:' . '%')
        ->orWhere('title', 'like', '%' .'Module:' . '%')
        ->orWhere('title', 'like', '%' .'Utilisatrice:' . '%')
        ->orWhere('title', 'like', '%' .'Discussion Projet:' . '%')
        ->orWhere('title', 'like', '%' .'Discussion utilisateur:' . '%')
        ->orWhere('title', 'like', '%' .'MediaWiki:' . '%')
        ->orWhere('title', 'like', '%' .'Référence:' . '%')
        ->orWhere('title', 'like', '%' .'Catégorie:Accueil' . '%')
        ->orWhere('title', 'like', '%' .'501c' . '%')
        ->orWhere('title', 'like', '%' .'Projet:' . '%')
        ->orWhere('title', 'like', '%' .'Utilisateur:' . '%')
        ->orWhere('title', 'like', '%' .'Modèle:' . '%')
        ->orWhere('title', 'like', '%' .'Portail:Accueil' . '%')
        ->orWhere('title', 'like', '%' .'Wikipédia:' . '%')
        ->orWhere('title', 'like', '%' .'Discussion:' . '%')
        ->orWhere('title', 'like', '%' .'Fichier:' . '%')
        ->orWhere('title', 'like', '%' .'Aide:' . '%')
        ->orWhere('title', 'like', '%' .'Discussion Portail' . '%')
        ->get();

        $countEntries = count($entries);
        $this->info($countEntries . ' pages à traiter     ' .  $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        foreach ($entries as $entry) {
            $entry->not_a_page = 1;
            $entry->save();
        }
        $this->info('Fin     ' .  $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));

    }
}
