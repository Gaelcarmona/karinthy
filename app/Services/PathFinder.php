<?php

namespace App\Services;

use App\Models\Entry;
use Carbon\CarbonInterface;

/**
 * Cherche les chemins les plus courts entre deux entrées.
 *
 * Le graphe est parcouru en largeur (BFS), niveau par niveau, jusqu'à
 * self::MAX_DEGREES degrés de séparation. Dès qu'un niveau contient au moins
 * une entrée pointant vers l'arrivée, la recherche s'arrête et renvoie toutes
 * les chaînes de ce niveau : ce sont les plus courtes possibles.
 */
class PathFinder
{
    /** Nombre maximum de liens entre le départ et l'arrivée. */
    public const MAX_DEGREES = 6;

    /** Nombre d'ids envoyés par requête SQL lors du chargement d'un niveau. */
    private const CHUNK_SIZE = 1000;

    public function __construct(private readonly ?CarbonInterface $deadline = null)
    {
    }

    public function find(Entry $start, Entry $end): PathSearchResult
    {
        $targetId = (int) $end->id;

        // Entrée visitée => entrée depuis laquelle on l'a atteinte (null pour le départ).
        $previous = [(int) $start->id => null];

        // Niveau courant : entrées situées à ($degree - 1) liens du départ.
        $frontier = [(int) $start->id => $this->linksOf($start)];

        for ($degree = 1; $degree <= self::MAX_DEGREES; $degree++) {
            $hits = $this->entriesLinkingTo($targetId, $frontier);

            if ($hits !== []) {
                return PathSearchResult::withChains(
                    array_map(fn (int $id): array => $this->chainTo($id, $previous), $hits)
                );
            }

            if ($degree === self::MAX_DEGREES) {
                break;
            }

            if ($this->outOfTime()) {
                return PathSearchResult::timeout();
            }

            $frontier = $this->expand($frontier, $previous, $targetId);

            if ($frontier === []) {
                break;
            }
        }

        return PathSearchResult::none();
    }

    /**
     * Ids des entrées du niveau courant qui pointent directement vers la cible.
     *
     * @param  array<int, array<int, int>>  $frontier
     * @return array<int, int>
     */
    private function entriesLinkingTo(int $targetId, array $frontier): array
    {
        $hits = [];

        foreach ($frontier as $id => $links) {
            if (in_array($targetId, $links, true)) {
                $hits[] = $id;
            }
        }

        return $hits;
    }

    /**
     * Construit le niveau suivant : les entrées jamais visitées vers lesquelles
     * pointe le niveau courant, et qui ont elles-mêmes des liens.
     *
     * @param  array<int, array<int, int>>  $frontier
     * @param  array<int, int|null>  $previous
     * @return array<int, array<int, int>>
     */
    private function expand(array $frontier, array &$previous, int $targetId): array
    {
        $discovered = [];

        foreach ($frontier as $id => $links) {
            foreach ($links as $linkId) {
                if ($linkId === $targetId
                    || array_key_exists($linkId, $previous)
                    || isset($discovered[$linkId])) {
                    continue;
                }

                $discovered[$linkId] = $id;
            }
        }

        // Marqué visité même sans liens : une entrée sans liens est un cul-de-sac,
        // inutile de la redécouvrir aux niveaux suivants.
        $previous += $discovered;

        $next = [];

        foreach (array_chunk(array_keys($discovered), self::CHUNK_SIZE) as $ids) {
            $entries = Entry::query()
                ->select(['id', 'paths'])
                ->whereIn('id', $ids)
                ->whereNotNull('paths')
                ->get();

            foreach ($entries as $entry) {
                $next[(int) $entry->id] = $this->linksOf($entry);
            }
        }

        return $next;
    }

    /**
     * Remonte jusqu'au départ pour reconstituer les entrées intermédiaires,
     * du départ vers l'arrivée. Vide si $id est le départ (lien direct).
     *
     * @param  array<int, int|null>  $previous
     * @return array<int, int>
     */
    private function chainTo(int $id, array $previous): array
    {
        $chain = [];

        while ($previous[$id] !== null) {
            $chain[] = $id;
            $id = $previous[$id];
        }

        return array_reverse($chain);
    }

    /**
     * @return array<int, int>
     */
    private function linksOf(Entry $entry): array
    {
        $links = json_decode((string) $entry->paths, true);

        if (! is_array($links)) {
            return [];
        }

        return array_map('intval', $links);
    }

    private function outOfTime(): bool
    {
        return $this->deadline !== null && $this->deadline->isPast();
    }
}
