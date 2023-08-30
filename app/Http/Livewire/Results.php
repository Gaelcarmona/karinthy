<?php

namespace App\Http\Livewire;

use App\Models\AvailableEntry;
use App\Models\Entry;
use App\Models\Path;
use Carbon\Carbon;
use Livewire\Component;

class Results extends Component
{
    public array $resultMessages = [];

    public $path;

    protected $listeners = [
        'searchSubmitted' => 'search',
    ];

    public function search($start, $end)
    {
        $path = null;
        $start = Entry::query()
            ->where('title', $start)
            ->where('paths', '!=', null)
            ->first();
        $end = Entry::query()
            ->where('title', $end)
            ->where('paths', '!=', null)
            ->first();
        if ($start !== null && $end !== null) {
            $path = Path::query()
                ->where('start_entry_id', $start->id)
                ->where('end_entry_id', $end->id)
                ->first();
        }
        if ($path == null) {
            $this->showResults($start, $end);
        }
    }

    public function showResults($start, $end)
    {
        $path = null;
        $startTime = time();
        ini_set('memory_limit', '32768M');


        if ($start === null) {
            $this->resultMessages[] = 'Page de départ inconnue ';
            return;
        }
        if ($end === null) {
            $this->resultMessages[] = 'Page d\'arrivée inconnue ';
            return;
        }
        //Ier niveau de séparation
        if (json_decode($start->paths) != null) {
            if (in_array($end->id, json_decode($start->paths))) {
                $path = Path::updateOrCreate([
                    'start_entry_id' => $start->id,
                    'end_entry_id' => $end->id,
                ]);
                return;
            }
        }

        //-------------------------------------------------------------------------------------------------------------------------------------

        $childs_from_start = Entry::query()->whereIn('id', json_decode($start->paths))->get();
        //IInd niveau de séparation
        foreach ($childs_from_start as $child) {
            if (json_decode($child->paths) != null) {

                if (in_array($end->id, json_decode($child->paths))) {

                    $path = Path::query()
                        ->where('start_entry_id', $start->id)
                        ->where('end_entry_id', $end->id)
                        ->firstOrCreate([
                            'start_entry_id' => $start->id,
                            'end_entry_id' => $end->id,
                        ]);
                    $data = json_decode($path->data, true);
                    if ($data === null) {
                        $data = [];
                    }
                    $newData = [
                        $child->title,
                    ];
                    $data[] = $newData;
                    $path->update(['data' => json_encode($data)]);
                }
            }
        }

        //-------------------------------------------------------------------------------------------------------------------------------------

        if ($path !== null) {
            return;
        }
        //IIIème niveau de séparation
        foreach ($childs_from_start as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        if (in_array($end->id, json_decode($greatChild->paths))) {

                            $path = Path::query()
                                ->where('start_entry_id', $start->id)
                                ->where('end_entry_id', $end->id)
                                ->firstOrCreate([
                                    'start_entry_id' => $start->id,
                                    'end_entry_id' => $end->id,
                                ]);
                            $data = json_decode($path->data, true);
                            if ($data === null) {
                                $data = [];
                            }
                            $newData = [
                                $child->title, $greatChild->title,
                            ];
                            $data[] = $newData;
                            $path->update(['data' => json_encode($data)]);
                        }
                    }
                }
            }
        }
        if ($path !== null) {
            return;
        }
        if (time() - $startTime >= 5 * 60) {
            return;
        }
        //IVème niveau de séparation
        foreach ($childs_from_start as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        $greatChilds2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->get();
                        foreach ($greatChilds2 as $greatChild2) {
                            if (time() - $startTime >= 5 * 60) {
                                return;
                            }
                            if (json_decode($greatChild2->paths) != null) {
                                if (in_array($end->id, json_decode($greatChild2->paths))) {

                                    $path = Path::query()
                                        ->where('start_entry_id', $start->id)
                                        ->where('end_entry_id', $end->id)
                                        ->firstOrCreate([
                                            'start_entry_id' => $start->id,
                                            'end_entry_id' => $end->id,
                                        ]);
                                    $data = json_decode($path->data, true);
                                    if ($data === null) {
                                        $data = [];
                                    }
                                    $newData = [
                                        $child->title, $greatChild->title, $greatChild2->title,
                                    ];
                                    $data[] = $newData;
                                    $path->update(['data' => json_encode($data)]);
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($path !== null) {
            return;
        }
        if (time() - $startTime >= 5 * 60) {
            return;
        }
        //Vème niveau de séparation
        foreach ($childs_from_start as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        $greatChilds2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->get();
                        foreach ($greatChilds2 as $greatChild2) {
                            if (time() - $startTime >= 5 * 60) {
                                return;
                            }
                            if (json_decode($greatChild2->paths) != null) {
                                $greatChilds3 = Entry::query()->whereIn('id', json_decode($greatChild2->paths))->get();
                                foreach ($greatChilds3 as $greatChild3) {
                                    if (json_decode($greatChild3->paths) != null) {
                                        if (in_array($end->id, json_decode($greatChild3->paths))) {

                                            $path = Path::query()
                                                ->where('start_entry_id', $start->id)
                                                ->where('end_entry_id', $end->id)
                                                ->firstOrCreate([
                                                    'start_entry_id' => $start->id,
                                                    'end_entry_id' => $end->id,
                                                ]);
                                            $data = json_decode($path->data, true);
                                            if ($data === null) {
                                                $data = [];
                                            }
                                            $newData = [
                                                $child->title, $greatChild->title, $greatChild2->title, $greatChild3->title,
                                            ];
                                            $data[] = $newData;
                                            $path->update(['data' => json_encode($data)]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($path !== null) {
            return;
        }
        if (time() - $startTime >= 5 * 60) {
            return;
        }
        //VIème niveau de séparation
        foreach ($childs_from_start as $child) {
            if (json_decode($child->paths) != null) {
                $greatChilds = Entry::query()->whereIn('id', json_decode($child->paths))->get();
                foreach ($greatChilds as $greatChild) {
                    if (json_decode($greatChild->paths) != null) {
                        $greatChilds2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->get();
                        foreach ($greatChilds2 as $greatChild2) {
                            if (time() - $startTime >= 5 * 60) {
                                return;
                            }
                            if (json_decode($greatChild2->paths) != null) {
                                $greatChilds3 = Entry::query()->whereIn('id', json_decode($greatChild2->paths))->get();
                                foreach ($greatChilds3 as $greatChild3) {
                                    if (json_decode($greatChild3->paths) != null) {
                                        $greatChilds4 = Entry::query()->whereIn('id', json_decode($greatChild3->paths))->get();
                                        foreach ($greatChilds4 as $greatChild4) {
                                            if (json_decode($greatChild4->paths) != null) {
                                                if (in_array($end->id, json_decode($greatChild4->paths))) {

                                                    $path = Path::query()
                                                        ->where('start_entry_id', $start->id)
                                                        ->where('end_entry_id', $end->id)
                                                        ->firstOrCreate([
                                                            'start_entry_id' => $start->id,
                                                            'end_entry_id' => $end->id,
                                                        ]);
                                                    $data = json_decode($path->data, true);
                                                    if ($data === null) {
                                                        $data = [];
                                                    }
                                                    $newData = [
                                                        $child->title, $greatChild->title, $greatChild2->title, $greatChild3->title, $greatChild4->title,
                                                    ];
                                                    $data[] = $newData;
                                                    $path->update(['data' => json_encode($data)]);
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
