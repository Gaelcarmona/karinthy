<?php

namespace App\Services;

/**
 * Résultat d'une recherche de chemin.
 *
 * Une "chaîne" est la liste ordonnée des ids d'entrées intermédiaires entre le
 * départ et l'arrivée (exclus). Une chaîne vide décrit donc un lien direct.
 */
final class PathSearchResult
{
    /**
     * @param  array<int, array<int, int>>  $chains
     */
    private function __construct(
        public readonly array $chains,
        public readonly bool $timedOut,
    ) {
    }

    /**
     * @param  array<int, array<int, int>>  $chains
     */
    public static function withChains(array $chains): self
    {
        return new self($chains, false);
    }

    public static function none(): self
    {
        return new self([], false);
    }

    public static function timeout(): self
    {
        return new self([], true);
    }

    public function hasChains(): bool
    {
        return $this->chains !== [];
    }

    /**
     * Le départ pointe directement vers l'arrivée : aucune entrée intermédiaire.
     */
    public function isDirect(): bool
    {
        return $this->chains === [[]];
    }
}
