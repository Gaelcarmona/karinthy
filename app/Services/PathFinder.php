<?php

namespace App\Services;

use App\Models\Entry;
use Carbon\CarbonInterface;

/**
 * Cherche les chemins les plus courts entre deux entrées.
 *
 * Le graphe est parcouru en largeur (BFS), niveau par niveau, jusqu'à
 * self::MAX_DEGREES degrés de séparation. Dès qu'un niveau contient au moins
 * une entrée pointant vers l'arrivée, la recherche s'arrête et renvoie les
 * chaînes de ce niveau : ce sont les plus courtes possibles.
 *
 * Le graphe est dense (~273 liens par entrée, ~700 000 entrées) : un niveau
 * profond représente des dizaines de millions de liens. Deux conséquences sur
 * l'implémentation :
 *  - les listes de liens ne sont jamais gardées pour tout un niveau ; la
 *    frontière ne contient que des ids et les liens sont relus par lots ;
 *  - un niveau est parcouru en une seule passe qui détecte les arrivées et
 *    construit le niveau suivant, et cette passe s'arrête dès qu'elle a de quoi
 *    répondre.
 */
class PathFinder
{
    /** Nombre maximum de liens entre le départ et l'arrivée. */
    public const MAX_DEGREES = 6;

    /** Nombre de chemins renvoyés au maximum (un niveau peut en contenir des milliers). */
    public const MAX_CHAINS = 20;

    /** Nombre d'ids envoyés par requête SQL. */
    private const CHUNK_SIZE = 1000;

    /** Valeur de $previous pour le point de départ, qui n'a pas de parent. */
    private const NO_PARENT = 0;

    private bool $timedOut = false;

    public function __construct(private readonly ?CarbonInterface $deadline = null)
    {
    }

    public function find(Entry $start, Entry $end): PathSearchResult
    {
        $this->timedOut = false;

        $targetId = (int) $end->id;

        // Entrée visitée => entrée depuis laquelle on l'a atteinte.
        $previous = [(int) $start->id => self::NO_PARENT];

        // Niveau courant : ids des entrées situées à ($degree - 1) liens du départ.
        $frontier = [(int) $start->id];

        for ($degree = 1; $degree <= self::MAX_DEGREES; $degree++) {
            $next = [];
            $hits = $this->scanLevel($frontier, $previous, $targetId, $next, $degree < self::MAX_DEGREES);

            if ($this->timedOut) {
                return PathSearchResult::timeout();
            }

            if ($hits !== []) {
                return PathSearchResult::withChains(
                    array_map(fn (int $id): array => $this->chainTo($id, $previous), $hits)
                );
            }

            if ($next === []) {
                break;
            }

            $frontier = $next;
        }

        return PathSearchResult::none();
    }

    /**
     * Parcourt un niveau en une passe : relève les entrées qui pointent vers la
     * cible et, tant qu'aucune n'a été trouvée, construit le niveau suivant.
     *
     * Dès qu'une entrée pointe vers la cible, le niveau courant est gagnant :
     * ses chemins seront renvoyés et le niveau suivant ne servira jamais, on
     * cesse donc de le construire — c'est ce qui évite de déplier le plus gros
     * niveau du parcours.
     *
     * @param  array<int, int>  $frontier  ids du niveau courant
     * @param  array<int, int>  $previous  table des visités, complétée au passage
     * @param  array<int, int>  $next      reçoit les ids du niveau suivant
     * @return array<int, int>             ids des entrées pointant vers la cible
     */
    private function scanLevel(array $frontier, array &$previous, int $targetId, array &$next, bool $buildNext): array
    {
        $hits = [];
        $next = [];

        // Lire les lignes dans l'ordre de la clé primaire : la table fait ~900 Mo
        // pour un buffer pool bien plus petit, et des ids éparpillés font relire
        // les mêmes pages plusieurs fois.
        sort($frontier);

        foreach (array_chunk($frontier, self::CHUNK_SIZE) as $ids) {
            if ($this->shouldStop()) {
                return [];
            }

            // toBase() : pas d'hydratation de modèles, on ne veut que deux colonnes.
            $rows = Entry::query()
                ->select(['id', 'paths'])
                ->whereIn('id', $ids)
                ->whereNotNull('paths')
                ->toBase()
                ->get();

            foreach ($rows as $row) {
                $links = $this->linksOf($row->paths);

                if (in_array($targetId, $links, true)) {
                    $hits[] = (int) $row->id;

                    if (count($hits) >= self::MAX_CHAINS) {
                        return $hits;
                    }

                    continue;
                }

                if (! $buildNext || $hits !== []) {
                    continue;
                }

                $parentId = (int) $row->id;

                foreach ($links as $linkId) {
                    // La cible est déjà écartée : les liens qui la contiennent
                    // ont fait un "continue" plus haut.
                    if (isset($previous[$linkId])) {
                        continue;
                    }

                    $previous[$linkId] = $parentId;
                    $next[] = $linkId;
                }
            }

            // Ce sont les listes de liens qui pèsent : on libère le lot.
            unset($rows);
        }

        return $hits;
    }

    /**
     * Remonte jusqu'au départ pour reconstituer les entrées intermédiaires,
     * du départ vers l'arrivée. Vide si $id est le départ (lien direct).
     *
     * @param  array<int, int>  $previous
     * @return array<int, int>
     */
    private function chainTo(int $id, array $previous): array
    {
        $chain = [];

        while ($previous[$id] !== self::NO_PARENT) {
            $chain[] = $id;
            $id = $previous[$id];
        }

        return array_reverse($chain);
    }

    /**
     * @return array<int, int>
     */
    private function linksOf(?string $paths): array
    {
        $links = json_decode((string) $paths, true);

        if (! is_array($links) || $links === []) {
            return [];
        }

        // json_decode rend déjà des entiers pour le format stocké ([1,2,3]) ;
        // on ne convertit que si une donnée plus ancienne dit le contraire.
        return is_int(reset($links)) ? $links : array_map('intval', $links);
    }

    private function shouldStop(): bool
    {
        if (! $this->timedOut && $this->deadline !== null && $this->deadline->isPast()) {
            $this->timedOut = true;
        }

        return $this->timedOut;
    }
}
