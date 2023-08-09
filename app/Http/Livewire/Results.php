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

    public function showResults($start, $end)
    {
        $dateDebut = Carbon::now();
        ini_set('memory_limit', '32768M');
        $start = Entry::query()
        ->where('title', $start)
        ->where('paths', '!=', null)
        ->first();
        $arrival = Entry::query()
        ->where('title', $end)
        ->where('paths', '!=', null)
        ->first();
        if ($start === null) {
            $this->resultMessages[] = 'Page de départ inconnue ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
            return;        
        }
        if ($arrival === null) {
            $this->resultMessages[] = 'Page d\'arrivée inconnue ' . $dateDebut->diff(Carbon::now())->format('%h heures %i minutes %s secondes');
            return;        
        }
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
        // if (!empty($this->resultMessages)) {
        //     return;
        // }
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
        // if (!empty($this->resultMessages)) {
        //     return;
        // }
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

    public function render()
    {
        return view('livewire.results');
    }
}
