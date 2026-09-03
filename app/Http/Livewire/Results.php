<?php

namespace App\Http\Livewire;

use App\Models\Entry;
use App\Models\Path;
use App\Services\PathFinder;
use App\Services\PathSearchResult;
use Livewire\Component;

class Results extends Component
{
    private const MAX_EXECUTION_MINUTES = 5;

    private const MEMORY_LIMIT = '1024M';

    public array $resultMessages = [];

    protected $listeners = [
        'searchSubmitted' => 'search',
    ];

    public function render()
    {
        return view('livewire.results');
    }

    public function search(?string $start, ?string $end): void
    {
        $this->resultMessages = [];

        try {
            $this->runSearch($start, $end);
        } finally {
            $this->dispatchBrowserEvent('stopScript');
        }
    }

    private function runSearch(?string $start, ?string $end): void
    {
        $startEntry = $this->findEntry($start);
        $endEntry = $this->findEntry($end);

        if ($startEntry === null) {
            $this->resultMessages[] = 'Page de départ inconnue';

            return;
        }

        if ($endEntry === null) {
            $this->resultMessages[] = 'Page d\'arrivée inconnue';

            return;
        }

        if ($startEntry->is($endEntry)) {
            $this->resultMessages[] = 'Page de départ et d\'arrivée identiques';

            return;
        }

        // Chemin déjà calculé lors d'une recherche précédente : PathDisplay l'affiche.
        if ($this->existingPath($startEntry, $endEntry) !== null) {
            return;
        }

        ini_set('memory_limit', self::MEMORY_LIMIT);

        $result = (new PathFinder(now()->addMinutes(self::MAX_EXECUTION_MINUTES)))
            ->find($startEntry, $endEntry);

        if ($result->timedOut) {
            $this->resultMessages[] = 'Temps écoulé';

            return;
        }

        if (! $result->hasChains()) {
            $this->resultMessages[] = 'La théorie est fausse ?';

            return;
        }

        $this->storePath($startEntry, $endEntry, $result);
    }

    private function findEntry(?string $title): ?Entry
    {
        if ($title === null || $title === '') {
            return null;
        }

        return Entry::query()
            ->where('title', $title)
            ->whereNotNull('paths')
            ->first();
    }

    private function existingPath(Entry $start, Entry $end): ?Path
    {
        return Path::query()
            ->where('start_entry_id', $start->id)
            ->where('end_entry_id', $end->id)
            ->first();
    }

    private function storePath(Entry $start, Entry $end, PathSearchResult $result): void
    {
        Path::updateOrCreate(
            [
                'start_entry_id' => $start->id,
                'end_entry_id' => $end->id,
            ],
            [
                // Un lien direct n'a aucune entrée intermédiaire à stocker.
                'data' => $result->isDirect() ? null : json_encode($result->chains),
            ],
        );
    }
}
