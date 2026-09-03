<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * La liste d'adjacence d'une page, dans un sens donné.
 *
 * Les lectures massives du parcours en largeur passent par le query builder
 * (toBase()) : ce modèle sert surtout à nommer les directions et à encoder ou
 * décoder les blobs.
 *
 * @property int $direction
 * @property int $page_id
 * @property int $count
 * @property string $targets  ids packés en uint32 little-endian
 */
class PageLink extends Model
{
    /** Pages vers lesquelles celle-ci pointe. */
    public const OUTBOUND = 0;

    /** Pages qui pointent vers celle-ci. */
    public const INBOUND = 1;

    protected $guarded = [];

    /** Clé primaire composite (direction, page_id) : pas d'id auto-incrémenté. */
    public $incrementing = false;

    public $timestamps = false;

    /**
     * @param  array<int, int>  $pageIds
     */
    public static function encode(array $pageIds): string
    {
        return $pageIds === [] ? '' : pack('V*', ...$pageIds);
    }

    /**
     * @return array<int, int>
     */
    public static function decode(?string $targets): array
    {
        if ($targets === null || $targets === '') {
            return [];
        }

        return array_values(unpack('V*', $targets));
    }

    /**
     * @return array<int, int>
     */
    public function ids(): array
    {
        return static::decode($this->targets);
    }
}
