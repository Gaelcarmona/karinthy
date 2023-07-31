<?php

namespace App\Console\Commands;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EntryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:delete-command {idStart} {idEnd}';

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

        $dateDebut = Carbon::now();
        $idStart = $this->argument('idStart');
        $idEnd = $this->argument('idEnd');
        $entries = Entry::query()
            ->whereBetween('id', [$idStart, $idEnd])
            ->where('toDelete', '=', 1)
            ->get();

        $totalEntries = count($entries);

        foreach ($entries as $key => $entry) {

            // $availableChildEntries = $entry->load('availableChildEntries')->availableChildEntries;
            // if (empty($availableChildEntries)) {
            //     foreach ($availableChildEntries as $availableChildEntry) {
            //         $availableChildEntry->delete();
            //     }
            // }
            // $availableParentEntries = $entry->load('availableParentEntries')->availableParentEntries;
            // if (empty($availableChildEntries)) {
            //     foreach ($availableParentEntries as $availableParentEntry) {
            //         $availableParentEntry->delete();
            //     }
            // }
            $entry->delete();

            $this->info(($key + 1) . '/' . count($entries) . ' pages à traiter     ' .  $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
        }
    }
}
