<?php

namespace App\Http\Livewire;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Carbon\Carbon;
use Livewire\Component;




class Search extends Component
{
    public string $start = '';
    public string $end = '';
    public ?array $resultMessages = [];

    protected $listeners = ['updateResultMessages'];



    public array $searchResults = [];

    public function updatedStart()
    {
        if ($this->start != '') {
            $this->searchResults =
                Entry::query()->where('title', 'like', '%' . $this->start . '%')->get()->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    public function updatedEnd()
    {
        if ($this->end != '') {
            $this->searchResults =
                Entry::query()->where('title', 'like', '%' . $this->end . '%')->get()->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    public function updatedResultMessages($messages)
    {
        $this->resultMessages = $messages;
    }
    public function submit()
    {

        $dateDebut = Carbon::now();
        ini_set('memory_limit', '32768M');

        $start = Entry::query()->where('title', $this->start)->with('availableChildEntries.childEntry')->first();
        $arrival = Entry::query()->where('title', $this->end)->with('availableParentEntries.parentEntry')->first();

        $toPageArrival = $arrival->availableParentEntries->pluck('parent_entry_id')->toArray();

        if (in_array($start->id,  $toPageArrival)) {
            $this->resultMessages[] = $start->title . '->' . $arrival->title . ' sur la page de départ';
            $this->emit('resultMessagesUpdated', $this->resultMessages);
            return; 
        }
        $onPagesMinusOne = AvailableEntry::query()->whereIn('child_entry_id', $toPageArrival)->where('parent_entry_id', $start->id)->with('childEntry')->get();

        if ($onPagesMinusOne->isNotEmpty()) {

            foreach ($onPagesMinusOne as $onPageMinusOne) {
                $this->resultMessages[] = $start->title . '->' . $onPageMinusOne->childEntry->title . '->' . $arrival->title;
                $this->emit('resultMessagesUpdated', $this->resultMessages);
            }
            return; 


        }

        $allLinksOnPages1 = $this->depthEntries(2, $toPageArrival, [], $start, $arrival, $dateDebut);

        $linksAlreadyProcessed = $toPageArrival;
        $allLinksOnPages2 = $this->depthEntries(3, $allLinksOnPages1, $linksAlreadyProcessed, $start, $arrival, $dateDebut);

        $linksAlreadyProcessed = array_unique(array_merge($allLinksOnPages1, $linksAlreadyProcessed));
        $allLinksOnPages3 = $this->depthEntries(4, $allLinksOnPages2, $linksAlreadyProcessed, $start, $arrival, $dateDebut);
    }

    public function depthEntries($depth, $allLinksOnPrecedentPages, $linksAlreadyProcessed, $start, $arrival, $dateDebut)
    {
        $resultMessages = [];
        $greatParents = $arrival->availableParentEntries;
        $isTrue = false;

        $count3levels = 0;
        $countPossibilities = 0;
        $greatParent = null;
        $allLinksOnPages = [];

        $allLinksOnPrecedentPages = array_diff($allLinksOnPrecedentPages, $linksAlreadyProcessed);

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
                            $resultMessages[] = $start->title . '->' . $parent->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                            $countPossibilities++;
                            $isTrue = true;
                            $this->emit('resultMessagesUpdated', $resultMessages);
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
                                $resultMessages[] = $start->title . '->' . $parent->title . '->' . $inBetweenParent->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                                $count3levels++;
                                $isTrue = true;
                                $this->emit('resultMessagesUpdated', $resultMessages);
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
                                                $resultMessages[] = $start->title . '->' . $parent->title . '->' . $first->title . '->' . $second->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                                                $count3levels++;
                                                $countPossibilities = $count3levels;
                                                $isTrue = true;
                                                $this->emit('resultMessagesUpdated', $resultMessages);
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
            // $this->info('A ' . $depth . ' niveau il ya ' . $countPossibilities . ' possibilités ' . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));
           return;
        }
        foreach ($allLinksOnPrecedentPages as $index => $allLinksOnPrecedentPage) {

            // $this->info('traitement du parent ' .  ($index + 1) . '/' . count($allLinksOnPrecedentPages) . ' dans la profondeur ' . $depth . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes'));

            $onPagesSubset = AvailableEntry::query()
                ->where('child_entry_id', $allLinksOnPrecedentPage)
                ->pluck('parent_entry_id')
                ->unique()
                ->toArray();

            $allLinksOnPages = array_merge($allLinksOnPages, $onPagesSubset);

        }
        $allLinksOnPages = array_unique($allLinksOnPages);
        $allLinksOnPages = array_diff($allLinksOnPages, $linksAlreadyProcessed);
        return $allLinksOnPages;
    }

    public function render()
    {
        return view('livewire.search');
    }
}
