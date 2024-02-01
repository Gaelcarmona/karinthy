<?php

namespace App\Http\Livewire;

use App\Models\Entry;
use Livewire\Component;

class Search extends Component
{
    public string $start = '';
    public string $end = '';
    public bool $formSubmitted = false;
    public array $startSearchResults = [];
    public array $endSearchResults = [];

    public function updatedStart()
    {
        if ($this->start != '' && strlen($this->start) >= 3) {
            $this->startSearchResults =
                Entry::query()
                    ->where('paths', '!=', null)
                    ->where('title', 'like', '%' . $this->start . '%')
                    ->limit(5)
                    ->pluck('title')
                    ->toArray();
            array_push($this->startSearchResults, $this->start);
        } else {
            $this->startSearchResults = [];
        }
    }

    public function selectStartResult($result)
    {
        $this->start = str_replace(".", "'", $result);
        $this->startSearchResults = [];
    }

    public function updatedEnd()
    {
        if ($this->end != '' && strlen($this->end) >= 3) {
            $this->endSearchResults =
                Entry::query()
                    ->where('paths', '!=', null)
                    ->where('title', 'like', '%' . $this->end . '%')
                    ->limit(5)
                    ->pluck('title')
                    ->toArray();
            array_push($this->endSearchResults, $this->end);
        } else {
            $this->endSearchResults = [];
        }
    }

    public function selectEndResult($result)
    {
        $this->end = str_replace(".", "'", $result);
        $this->endSearchResults = [];
    }

    public function shuffleStart()
    {
        $correctEntry = false;

        while (!$correctEntry) {
            $randomId = rand(1, 1000000);
            $randomStart =
                Entry::query()
                    ->where('id', '=', $randomId)
                    ->first();
            if ($randomStart != null) {
                if ($randomStart->paths != null) {
                    $correctEntry = true;
                    $this->start = $randomStart->title;
                }
            }
        }
    }

    public function shuffleEnd()
    {
        $correctEntry = false;

        while (!$correctEntry) {
            $randomId = rand(1, 1000000);
            $randomEnd =
                Entry::query()
                    ->where('id', '=', $randomId)
                    ->first();
            if ($randomEnd != null) {
                if ($randomEnd->paths != null) {
                    $correctEntry = true;
                    $this->end = $randomEnd->title;
                }
            }
        }
    }

    public function submit()
    {
        $this->formSubmitted = true;
        $this->emit('searchSubmitted', $this->start, $this->end);
    }

    public function render()
    {
        return view('livewire.search');
    }
}
