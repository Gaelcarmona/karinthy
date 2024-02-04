<?php

namespace App\Http\Livewire;

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

    public function search($start, $end): void
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
        $this->dispatchBrowserEvent('stopScript');
    }

    public function showResults($start, $end): void
    {
        $isParent = false;
        $maxExecutionTime = Carbon::now()->addMinutes(5);
        ini_set('memory_limit', '32768M');
        if ($start === null) {
            $this->resultMessages[] = 'Page de départ inconnue ';
            return;
        }
        if ($end === null) {
            $this->resultMessages[] = 'Page d\'arrivée inconnue ';
            return;
        }
        if ($end == $start) {
            $this->resultMessages[] = 'Page de départ et d\'arrivée identiques';
            return;
        }
        //Ier niveau de séparation
        if (json_decode($start->paths) != null) {
            if (in_array($end->id, json_decode($start->paths))) {
                Path::updateOrCreate([
                    'start_entry_id' => $start->id,
                    'end_entry_id' => $end->id,
                ]);
                $this->dispatchBrowserEvent('stopScript');
                return;
            }
        }

        //IIer niveau de séparation
        $children = Entry::query()->whereIn('id', json_decode($start->paths))->where('paths', '!=', null)->get();
        foreach ($children as $child) {
            if (in_array($end->id, json_decode($child->paths))) {
                $this->storePath($start, $end, $child);
                $isParent = true;
            }
        }
        if ($isParent) {
            $this->dispatchBrowserEvent('stopScript');
            return;
        }

        //IIIer niveau de séparation
        foreach ($children as $child) {
            $greatChildren = Entry::query()->whereIn('id', json_decode($child->paths))->where('paths', '!=', null)->get();
            foreach ($greatChildren as $greatChild) {
                if (in_array($end->id, json_decode($greatChild->paths))) {
                    $this->storePath($start, $end, $child, $greatChild);
                    $isParent = true;
                }
            }
        }
        if ($isParent) {
            $this->dispatchBrowserEvent('stopScript');
            return;
        }

        //IVer niveau de séparation
        foreach ($children as $child) {
            $greatChildren = Entry::query()->whereIn('id', json_decode($child->paths))->where('paths', '!=', null)->get();
            foreach ($greatChildren as $greatChild) {
                if (Carbon::now() > $maxExecutionTime) {
                    $this->resultMessages[] = "Temps écoulé";
                    return;
                }
                $greatChildren2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->where('paths', '!=', null)->get();
                foreach ($greatChildren2 as $greatChild2) {
                    if (in_array($end->id, json_decode($greatChild2->paths))) {
                        $this->storePath($start, $end, $child, $greatChild, $greatChild2);
                        $isParent = true;
                    }
                }
            }
        }
        if ($isParent) {
            $this->dispatchBrowserEvent('stopScript');
            return;
        }

        //Ver niveau de séparation
        foreach ($children as $child) {
            $greatChildren = Entry::query()->whereIn('id', json_decode($child->paths))->where('paths', '!=', null)->get();
            foreach ($greatChildren as $greatChild) {
                if (Carbon::now() > $maxExecutionTime) {
                    $this->resultMessages[] = "Temps écoulé";
                    return;
                }
                $greatChildren2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->where('paths', '!=', null)->get();
                foreach ($greatChildren2 as $greatChild2) {
                    $greatChildren3 = Entry::query()->whereIn('id', json_decode($greatChild2->paths))->where('paths', '!=', null)->get();
                    foreach ($greatChildren3 as $greatChild3) {
                        if (in_array($end->id, json_decode($greatChild3->paths))) {
                            $this->storePath($start, $end, $child, $greatChild, $greatChild2, $greatChild3);
                            $isParent = true;
                        }
                    }
                }
            }
        }
        if ($isParent) {
            $this->dispatchBrowserEvent('stopScript');
            return;
        }

        //VIer niveau de séparation
        foreach ($children as $child) {
            $greatChildren = Entry::query()->whereIn('id', json_decode($child->paths))->where('paths', '!=', null)->get();
            foreach ($greatChildren as $greatChild) {
                if (Carbon::now() > $maxExecutionTime) {
                    $this->resultMessages[] = "Temps écoulé";
                    return;
                }
                $greatChildren2 = Entry::query()->whereIn('id', json_decode($greatChild->paths))->where('paths', '!=', null)->get();
                foreach ($greatChildren2 as $greatChild2) {
                    $greatChildren3 = Entry::query()->whereIn('id', json_decode($greatChild2->paths))->where('paths', '!=', null)->get();
                    foreach ($greatChildren3 as $greatChild3) {
                        $greatChildren4 = Entry::query()->whereIn('id', json_decode($greatChild3->paths))->where('paths', '!=', null)->get();
                        foreach ($greatChildren4 as $greatChild4) {
                            if (in_array($end->id, json_decode($greatChild4->paths))) {
                                $this->storePath($start, $end, $child, $greatChild, $greatChild2, $greatChild3, $greatChild4);
                                $isParent = true;
                            }
                        }
                    }
                }
            }
        }
        if ($isParent) {
            $this->dispatchBrowserEvent('stopScript');
            return;
        }
        if (empty($this->resultMessages)) {
            $this->resultMessages[] = "La théorie est fausse ?";
        }
    }

    public function render()
    {
        return view('livewire.results');
    }

    public function storePath($start, $end, mixed $child, mixed $greatChild = null, mixed $greatChild2 = null, mixed $greatChild3 = null, mixed $greatChild4 = null): void
    {
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
        if ($greatChild4 != null) {
            $newData = [
                $child->id, $greatChild->id, $greatChild2->id, $greatChild3->id, $greatChild4->id,
            ];
        } elseif ($greatChild3 != null) {
            $newData = [
                $child->id, $greatChild->id, $greatChild2->id, $greatChild3->id,
            ];
        } elseif ($greatChild2 != null) {
            $newData = [
                $child->id, $greatChild->id, $greatChild2->id,
            ];
        } elseif ($greatChild != null) {
            $newData = [
                $child->id, $greatChild->id,
            ];
        } else {
            $newData = [
                $child->id,
            ];
        }
        $data[] = $newData;
        $path->update(['data' => json_encode($data)]);
    }
}
