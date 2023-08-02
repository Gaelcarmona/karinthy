<?php

namespace App\Console\Commands;

use App\Exports\AvailableEntriesExport;
use App\Models\AvailableEntry;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class RetrieveCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:retrieve-csv-command';

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

        $availableEntries = AvailableEntry::query()
            ->with('parentEntry')
            ->with('childEntry')
            ->whereBetween('parent_entry_id', [2, 5000])
            ->get();

        $rows = [];
        foreach ($availableEntries as $availableEntry) {
            $parent = $availableEntry->parentEntry;
            $child = $availableEntry->childEntry;
            $rows[] = '//' . $parent->title . '//,' . '//' . $child->title . '//';
        }
        Excel::download(new AvailableEntriesExport($rows), "test.csv");
    }

        public function test()
    {
        ini_set('memory_limit', '32768M');

        $availableEntries = AvailableEntry::query()
            ->with('parentEntry')
            ->with('childEntry')
            ->whereBetween('parent_entry_id', [2, 5000])
            ->get();

        $rows = [];
        foreach ($availableEntries as $availableEntry) {
            $parent = $availableEntry->parentEntry;
            $child = $availableEntry->childEntry;
            $rows[] = '//' . $parent->title . '//,' . '//' . $child->title . '//';
        }
        Excel::download(new AvailableEntriesExport($rows), "test.csv");
    }
}
