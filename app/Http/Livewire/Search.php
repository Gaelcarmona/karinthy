<?php

namespace App\Http\Livewire;

use App\Models\Entry;
use Livewire\Component;

class Search extends Component
{
    public string $start = '';
    public string $end = '';

    public ?bool $allShortestPaths = false;

    public bool $formSubmitted = false;

    public array $startSearchResults = [];
    public array $endSearchResults = [];

    public function updatedStart()
    {
        if ($this->start != '') {
            $this->startSearchResults =
                Entry::query()
                    ->where('paths', '!=', null)
                    ->where('title', 'like', '%' . $this->start . '%')
                    ->limit(10)
                    ->pluck('title')
                    ->toArray();
        } else {
            $this->startSearchResults = [];
        }
    }

    public function updatedEnd()
    {
        if ($this->end != '') {
            $this->endSearchResults =
                Entry::query()
                    ->where('paths', '!=', null)
                    ->where('title', 'like', '%' . $this->end . '%')
                    ->limit(10)
                    ->pluck('title')
                    ->toArray();
        } else {
            $this->endSearchResults = [];
        }
    }

    public function submit()
    {
        $this->formSubmitted = true;
        $this->emit('searchSubmitted', $this->start, $this->end, $this->allShortestPaths);
    }


    public function render()
    {
        return view('livewire.search');
    }
}
