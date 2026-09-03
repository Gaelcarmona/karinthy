<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Le résultat mémorisé d'une recherche entre deux articles.
 *
 * @property int $start_page_id
 * @property int $end_page_id
 * @property int|null $degree  nombre de liens ; null si aucun chemin
 * @property array<int, array<int, int>>|null $chains
 */
class SearchPath extends Model
{
    protected $guarded = [];

    protected $casts = [
        'chains' => 'array',
        'degree' => 'integer',
    ];

    public function startPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'start_page_id');
    }

    public function endPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'end_page_id');
    }

    public function found(): bool
    {
        return $this->degree !== null;
    }
}
