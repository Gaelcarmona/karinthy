<?php

namespace App\Http\Livewire;

use App\Models\Entry;
use Livewire\Component;
use App\Models\Path;

class PathDisplay extends Component
{
    public $startId;
    public $endId;
    public $paths;
    public $start;
    public $end;
    public bool $formSubmitted = false;



    protected $listeners = ['pathUpdated' => '$refresh', 'searchSubmitted' => 'searchSubmitted',];

    public function mount()
    {
        $this->loadPath();
    }

    public function searchSubmitted($start, $end)
    {
        $this->start = $start;
        $this->end = $end;

        $startEntry = Entry::query()
            ->where('title', $this->start)
            ->where('paths', '!=', null)
            ->first();

        $endEntry = Entry::query()
            ->where('title', $this->end)
            ->where('paths', '!=', null)
            ->first();

        if ($startEntry && $endEntry) {
            $this->startId = $startEntry->id;
            $this->endId = $endEntry->id;
            $this->loadPath();
            $this->formSubmitted = true;
        }
    }

    public function loadPath()
    {
        $this->paths = Path::query()
            ->where('start_entry_id', $this->startId)
            ->where('end_entry_id', $this->endId)
            ->first();
    }

    public function render()
    {
        return view('livewire.path-display');
    }
}
