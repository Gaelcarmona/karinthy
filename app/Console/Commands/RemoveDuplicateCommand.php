<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Illuminate\Console\Command;

class RemoveDuplicateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:remove-duplicate-command';

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
        $duplicateIds = Entry::selectRaw('MAX(id) as max_id')
        ->where('title', 'like', '%' .'Portail:' . '%')
        ->orWhere('title', 'like', '%' .'Catégorie:' . '%')
        ->groupBy('title')
        ->havingRaw('COUNT(*) > 1')
        ->get()
        ->pluck('max_id');

        Entry::whereIn('id', $duplicateIds)->delete();
    }
}
