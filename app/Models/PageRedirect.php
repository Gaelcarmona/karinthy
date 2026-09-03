<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une redirection de l'espace principal vers un article.
 *
 * @property int $id  page_id de la page de redirection
 * @property string $title
 * @property int $page_id  article visé
 */
class PageRedirect extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    /**
     * Résout un titre en article : le titre lui-même s'il en est un, sinon la
     * cible de sa redirection.
     */
    public static function resolve(string $title): ?Page
    {
        $page = Page::query()->where('title', $title)->first();

        if ($page !== null) {
            return $page;
        }

        return static::query()->where('title', $title)->first()?->page;
    }
}
