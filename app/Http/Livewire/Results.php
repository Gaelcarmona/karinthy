<?php

namespace App\Http\Livewire;

use App\Models\AvailableEntry;
use App\Models\Entry;
use Carbon\Carbon;
use Livewire\Component;

class Results extends Component
{
    public $results = [];
    public array $resultMessages = [];
    protected $listeners = ['searchSubmitted' => 'showResults'];

//    public function showResults($start, $end)
//    {
////        $dateDebut = Carbon::now();
////        ini_set('memory_limit', '32768M');
//
////        $start = Entry::query()->where('title', $start)->with('availableChildEntries.childEntry')->first();
////        $arrival = Entry::query()->where('title', $end)->with('availableParentEntries.parentEntry')->first();
////
////        $toPageArrival = $arrival->availableParentEntries->pluck('parent_entry_id')->toArray();
////
////        if (in_array($start->id, $toPageArrival)) {
////            $this->resultMessages[] = $start->title . '->' . $arrival->title . ' sur la page de départ ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
////            return;
////        }
////        $onPagesMinusOne = AvailableEntry::query()->whereIn('child_entry_id', $toPageArrival)->where('parent_entry_id', $start->id)->with('childEntry')->get();
////
////        if ($onPagesMinusOne->isNotEmpty()) {
////
////            foreach ($onPagesMinusOne as $onPageMinusOne) {
////
////                $this->resultMessages[] = $start->title . '->' . $onPageMinusOne->childEntry->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
////                return;
////
////            }
////            return;
////        }
////
////        $greatParents = $arrival->availableParentEntries;
////        $greatParent = null;
////
////        foreach ($greatParents as $greatParent) {
////            $greatParent = $greatParent->parentEntry;
////
////            $potentialsParents = $greatParent->load('availableParentEntries')->availableParentEntries->pluck('parent_entry_id')->toArray();
////
////            $potentialsParents = array_unique($potentialsParents);
////
////            $parents = $start->availableChildEntries;
////
////            $parents = $parents->whereIn('child_entry_id', $potentialsParents);
////
////            if ($parents->isNotEmpty()) {
////                foreach ($parents as $parent) {
////
////                    $parent = $parent->childEntry;
////                    $this->resultMessages[] = $start->title . '->' . $parent->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
////                    return;
////
////                }
////            }
////            $parents = $start->availableChildEntries;
////            if (empty($this->resultMessages)) {
////                foreach ($parents as $parent) {
////                    $parent = $parent->childEntry;
////
////                    $inBetweenParents = AvailableEntry::query()
////                        ->where('parent_entry_id', $parent->id)
////                        ->whereIn('child_entry_id', $potentialsParents)
////                        ->with('childEntry')
////                        ->get();
////
////                    if ($inBetweenParents->isNotEmpty()) {
////                        foreach ($inBetweenParents as $inBetweenParent) {
////
////                            $inBetweenParent = $inBetweenParent->childEntry;
////                            $this->resultMessages[] = $start->title . '->' . $parent->title . '->' . $inBetweenParent->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
////                            return;
////
////                        }
////                    }
////                }
////            }
////            if (empty($this->resultMessages)) {
////                $potentialsInBetween = Entry::query()->whereIn('id', $potentialsParents)->with('availableParentEntries.parentEntry')->get();
////
////                foreach ($potentialsInBetween as $potentialInBetween) {
////
////                    $potentialsChilds = $potentialInBetween->availableParentEntries->pluck('parent_entry_id')->toArray();
////                    $potentialsChilds = array_unique($potentialsChilds);
////
////                    $potentialsInBetween2 = Entry::query()->whereIn('id', $potentialsChilds)->get();
////
////                    foreach ($potentialsInBetween2 as $potentialInBetween2) {
////                        $linked = AvailableEntry::query()
////                            ->where('parent_entry_id', $potentialInBetween2->id)
////                            ->where('child_entry_id', $potentialInBetween->id)
////                            ->with('childEntry')
////                            ->with('parentEntry')
////                            ->first();
////                        if ($linked !== null) {
////                            $first = $linked->parentEntry;
////                            $second = $linked->childEntry;
////
////                            if ($parents->isNotEmpty()) {
////
////                                foreach ($parents as $parent) {
////
////                                    $parent = $parent->childEntry;
////
////                                    foreach ($parent->availableChildEntries as $availableChildEntry) {
////
////                                        if ($availableChildEntry->child_entry_id == $first->id) {
////
////                                            $this->resultMessages[] = $start->title . '->' . $parent->title . '->' . $first->title . '->' . $second->title . '->' . $greatParent->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
////                                            return;
////
////                                        }
////                                    }
////                                }
////                            }
////                        }
////                    }
////                }
////            }
////            if (empty($this->resultMessages)) {
////                //TODO:6ème degré de séparation
////            }
////            if (empty($this->resultMessages)) {
////                $this->resultMessages[] = "La théorie est fausse ?";
////            }
////        }
//    }
    public function showResults($start, $end)
    {
        $dateDebut = Carbon::now();
        ini_set('memory_limit', '32768M');
        $start = Entry::query()->where('title', $start)->first();
        $arrival = Entry::query()->where('title', $end)->first();
        //Ier niveau de séparation
        if (json_decode($start->paths) != null) {
            if (in_array($arrival->id, json_decode($start->paths))) {
                $this->resultMessages[] = $start->title . '->' . $arrival->title . ' sur la page de départ ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                return;
            }
        }

        $childs = Entry::query()->whereIn('id', json_decode($start->paths))->get();
        //IInd niveau de séparation
        foreach ($childs as $child) {
            if (json_decode($child->paths) != null) {

                if (in_array($arrival->id, json_decode($child->paths))) {
                    $this->resultMessages[] = $start->title . '->' . $child->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                    return;
                }
            }
        }
        //IIIème niveau de séparation
        foreach ($childs as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        if (in_array($arrival->id, json_decode($greatChild->paths))) {
                            $this->resultMessages[] = $start->title . '->' . $child->title . '->' . $greatChild->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                            return;
                        }
                    }
                }
            }
        }
        //IVème niveau de séparation
        foreach ($childs as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        $greatChilds2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->get();
                        foreach ($greatChilds2 as $greatChild2) {
                            if (json_decode($greatChild2->paths) != null) {
                                if (in_array($arrival->id, json_decode($greatChild2->paths))) {
                                    $this->resultMessages[] = $start->title . '->' . $child->title . '->' . $greatChild->title . '->' . $greatChild2->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }
        //Vème niveau de séparation
        foreach ($childs as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        $greatChilds2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->get();
                        foreach ($greatChilds2 as $greatChild2) {
                            if (json_decode($greatChild2->paths) != null) {
                                $greatChilds3 = Entry::query()->whereIn('id', json_decode($greatChild2->paths))->get();
                                foreach ($greatChilds3 as $greatChild3) {
                                    if (json_decode($greatChild3->paths) != null) {
                                        if (in_array($arrival->id, json_decode($greatChild3->paths))) {
                                            $this->resultMessages[] = $start->title . '->' . $child->title . '->' . $greatChild->title . '->' . $greatChild2->title . '->' . $greatChild3->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                                            return;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //VIème niveau de séparation
        foreach ($childs as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        $greatChilds2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->get();
                        foreach ($greatChilds2 as $greatChild2) {
                            if (json_decode($greatChild2->paths) != null) {
                                $greatChilds3 = Entry::query()->whereIn('id', json_decode($greatChild2->paths))->get();
                                foreach ($greatChilds3 as $greatChild3) {
                                    if (json_decode($greatChild3->paths) != null) {
                                        $greatChilds4 = Entry::query()->whereIn('id', json_decode($greatChild3->paths))->get();
                                        foreach ($greatChilds4 as $greatChild4) {
                                            if (json_decode($greatChild4->paths) != null) {
                                                if (in_array($arrival->id, json_decode($greatChild4->paths))) {
                                                    $this->resultMessages[] = $start->title . '->' . $child->title . '->' . $greatChild->title . '->' . $greatChild2->title . '->' . $greatChild3->title . '->' . $greatChild4->title . '->' . $arrival->title . ' ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
                                                    return;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (empty($this->resultMessages)) {
            $this->resultMessages[] = "La théorie est fausse ?";
        }
    }

    public
    function render()
    {
        return view('livewire.results');
    }
}
