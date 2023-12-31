<?php

namespace App\Console\Commands;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteCommand extends Command
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
        $entries = Entry::has('availableParentEntries')
            ->whereBetween('id', [$idStart, $idEnd])
            ->where('redirect_to', '!=', null)
            ->get();

        $totalEntries = count($entries);

        foreach ($entries as $key => $entry) {
            $entry->load('availableParentEntries');
            if ($entry->availableParentEntries != null) {
                foreach ($entry->availableParentEntries as $availableParentEntry) {
                    $availableParentEntry->delete();
                }
                $this->info(($key + 1) . '/' . $totalEntries . ' pages redirect_to à traiter     ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
            }
        }
        $entries = Entry::has('availableParentEntries')
            ->whereBetween('id', [$idStart, $idEnd])
            ->where('not_a_page', '!=', null)
            ->get();

        $totalEntries = count($entries);

        foreach ($entries as $key => $entry) {
            $entry->load('availableParentEntries');
            if ($entry->availableParentEntries != null) {
                foreach ($entry->availableParentEntries as $availableParentEntry) {
                    $availableParentEntry->delete();
                }
                $this->info(($key + 1) . '/' . $totalEntries . ' pages not_a_page à traiter     ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
            }
        }
    }
}
