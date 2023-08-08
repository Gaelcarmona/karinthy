<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeletePathsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:set-to-delete-command {idStart} {idEnd}';

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
        ini_set('memory_limit', '32768M');
        $idStart = $this->argument('idStart');
        $idEnd = $this->argument('idEnd');
        $dateDebut = Carbon::now();
        $entries = Entry::query()
            ->whereBetween('id', [$idStart, $idEnd])
            ->where('paths', "!=", null)
            ->get();

        $countEntries = count($entries);
        $this->info($countEntries . ' pages à traiter     ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        foreach ($entries as $entry) {
            $entry->paths = null;
            $entry->save();
        }
        $this->info('Fin     ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));

    }
}
