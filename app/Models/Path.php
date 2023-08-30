<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Path extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function startEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'start_entry_id', 'id');
    }

    public function endEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'end_entry_id', 'id');
    }
}
