<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Un article de l'espace principal de fr.wikipedia.
 *
 * @property int $id  page_id de Wikipédia
 * @property string $title
 * @property float $random
 * @property int $outbound_count
 * @property int $inbound_count
 */
class Page extends Model
{
    protected $guarded = [];

    /** L'id vient de Wikipédia, il n'est pas généré par la base. */
    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $casts = [
        'random' => 'float',
        'outbound_count' => 'integer',
        'inbound_count' => 'integer',
    ];

    /**
     * Le segment d'URL Wikipédia, déduit du titre : inutile de le stocker.
     */
    protected function url(): Attribute
    {
        return Attribute::get(
            fn (): string => rawurlencode(str_replace(' ', '_', $this->title))
        );
    }

    protected function wikipediaUrl(): Attribute
    {
        return Attribute::get(
            fn (): string => 'https://fr.wikipedia.org/wiki/'.$this->url
        );
    }

    /**
     * Un article au hasard, en une lecture d'index.
     */
    public static function random(): ?self
    {
        $from = mt_rand() / mt_getrandmax();

        return static::query()->where('random', '>=', $from)->orderBy('random')->first()
            ?? static::query()->orderBy('random')->first();
    }

    /**
     * Recherche par préfixe : contrairement à un LIKE '%...%', celle-ci
     * s'appuie sur l'index unique du titre.
     */
    public function scopeTitleStartsWith(Builder $query, string $prefix): Builder
    {
        return $query->where('title', 'like', addcslashes($prefix, '%_\\').'%');
    }
}
