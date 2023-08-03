<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PathsToEntriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:paths-to-entries-command {idStart} {idEnd}';

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

        $countEntries = Entry::query()
            ->whereBetween('id', [$idStart, $idEnd])
            ->where('paths', '=', null)
            ->count();

        for ($i = 0; $i < 1000000; $i++) {
            $entry = Entry::has('availableChildEntries')
                ->whereBetween('id', [$idStart, $idEnd])
                ->where('paths', '=', null)
                ->with('availableChildEntries')
                ->first();
            if ($entry == null) {
                return;
            }
            $this->info('Traitement: ' . $entry->title . ', traité : ' . $i . '/' . $countEntries . '  ' . $dateDebut->diff(Carbon::now())->format('%hH%imin%ssec') . " ids: " . $idStart . ' à ' . $idEnd);
            foreach ($entry->availableChildEntries as $availableChildEntry) {
                $entryPaths[] = $availableChildEntry->child_entry_id;
                $entry->paths = json_encode($entryPaths);
                $entry->save();
            }
                unset($entryPaths);
        }
    }
}
