<?php

namespace App\Http\Livewire;

use App\Models\Entry;
use Livewire\Component;

class Search extends Component
{
    public string $start = '';
    public string $end = '';
    

    public array $searchResults = [];

    public function updatedStart()
    {
        if ($this->start != '') {
            $this->searchResults =
                Entry::query()
                ->where('paths', '!=', null)
                ->where('title', 'like', '%' . $this->start . '%')
                ->limit(10)
                ->pluck('title')
                ->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    public function updatedEnd()
    {
        if ($this->end != '') { 
            $this->searchResults =
                Entry::query()
                ->where('paths', '!=', null)
                ->where('title', 'like', '%' . $this->end . '%')
                ->limit(10)
                ->pluck('title')
                ->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    public function submit()
    {

        $this->emit('searchSubmitted', $this->start, $this->end);
    }


    public function render()
    {
        return view('livewire.search');
    }
}