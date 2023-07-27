<?php

namespace App\Console\Commands;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FindWithTitleCommand2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karinthy:find2-command {prompt1} {prompt2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        $dateDebut = Carbon::now();
        ini_set('memory_limit', '32768M');
        $prompt1 = str_replace('_', ' ', $this->argument('prompt1'));
        $prompt2 = str_replace('_', ' ', $this->argument('prompt2'));

        $start = Entry::query()->where('title', $prompt1)->with('availableChildEntries.childEntry')->first();
        $arrival = Entry::query()->where('title', $prompt2)->with('availableParentEntries.parentEntry')->first();

        $toPageArrival = $arrival->availableParentEntries->pluck('parent_entry_id')->toArray();

        if (in_array($start->id,  $toPageArrival)) {
            dd($start->title . '->' . $arrival->title . ' sur la page de départ');
        }

        $this->info(count($toPageArrival) . '  ' . Carbon::now());
        $onPagesMinusOne = AvailableEntry::query()->whereIn('child_entry_id', $toPageArrival)->where('parent_entry_id', $start->id)->with('childEntry')->get();

        if ($onPagesMinusOne->isNotEmpty()) {

            foreach ($onPagesMinusOne as $onPageMinusOne) {
                $this->info($start->title . '->' . $onPageMinusOne->childEntry->title . '->' . $arrival->title);
            }
            $count = $onPagesMinusOne->count();

            dd('A 1 niveau il ya ' . $count . ' possibilités');
        }

        $this->depthEntries(2, $start, $arrival, $dateDebut);

        $this->depthEntries(3, $start, $arrival, $dateDebut);

        $this->depthEntries(4, $start, $arrival, $dateDebut);


        dd('la théorie est-elle fausse ?');
    }

    public function depthEntries($depth, $start, $arrival, $dateDebut)
    {
        $greatParents = $arrival->availableParentEntries;
        $isTrue = false;

        $count3levels = 0;
        $countPossibilities = 0;
        $greatParent = null;

        foreach ($greatParents as $greatParent) {
            $greatParent = $greatParent->parentEntry;

            $potentialsParents = $greatParent->load('availableParentEntries')->availableParentEntries->pluck('parent_entry_id')->toArray();

            $potentialsParents = array_unique($potentialsParents);

            $parents = $start->availableChildEntries;

            switch ($depth) {

                case ('2'):

                    $parents = $parents->whereIn('child_entry_id', $potentialsParents);

                    if ($parents->isNotEmpty()) {
                        foreach ($parents as $parent) {
                            $parent = $parent->childEntry;
                            $this->info($start->title . '->' . $parent->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
                            $countPossibilities++;
                            $isTrue = true;
                        }
                    }

                    break;
                case ('3'):

                    foreach ($parents as $parent) {
                        $parent = $parent->childEntry;

                        $inBetweenParents = AvailableEntry::query()
                            ->where('parent_entry_id', $parent->id)
                            ->whereIn('child_entry_id', $potentialsParents)
                            ->with('childEntry')
                            ->get();

                        if ($inBetweenParents->isNotEmpty()) {
                            foreach ($inBetweenParents as $inBetweenParent) {
                                $inBetweenParent = $inBetweenParent->childEntry;

                                $this->info($start->title . '->' . $parent->title . '->' . $inBetweenParent->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
                                $count3levels++;
                                $isTrue = true;
                            }
                        }
                        $countPossibilities = $count3levels;
                    }

                    break;
                case ('4'):

                    $potentialsInBetween = Entry::query()->whereIn('id', $potentialsParents)->with('availableParentEntries.parentEntry')->get();

                    foreach ($potentialsInBetween as $potentialInBetween) {

                        $potentialsChilds = $potentialInBetween->availableParentEntries->pluck('parent_entry_id')->toArray();
                        $potentialsChilds = array_unique($potentialsChilds);

                        $potentialsInBetween2 = Entry::query()->whereIn('id', $potentialsChilds)->get();

                        foreach ($potentialsInBetween2 as $potentialInBetween2) {
                            $linked = AvailableEntry::query()
                                ->where('parent_entry_id', $potentialInBetween2->id)
                                ->where('child_entry_id', $potentialInBetween->id)
                                ->with('childEntry')
                                ->with('parentEntry')
                                ->first();
                            if ($linked !== null) {
                                $first = $linked->parentEntry;
                                $second = $linked->childEntry;

                                if ($parents->isNotEmpty()) {
                                    foreach ($parents as $parent) {
                                        $parent = $parent->childEntry;
                                        foreach ($parent->availableChildEntries as $availableChildEntry) {
                                            if ($availableChildEntry->child_entry_id == $first->id) {
                                                $this->info($start->title . '->' . $parent->title . '->' . $first->title . '->' . $second->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
                                                $count3levels++;
                                                $countPossibilities = $count3levels;
                                                $isTrue = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;

                default:
                    $msg = 'Something went wrong.';
            }
        }
        if ($isTrue) {
            $this->info('A ' . $depth . ' niveau il ya ' . $countPossibilities . ' possibilités ' . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
            dd('ici');
        }

        return;
    }
}
