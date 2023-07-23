<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvailableEntry extends Model
{
    use HasFactory;


    protected $guarded = [];

    public function url(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'parent_entry_id', 'id');
    }

    public function childEntries(): HasMany
    {
        return $this->hasMany(AvailableEntry::class, 'parent_entry_id', 'child_entry_id');
    }
    public function parents(): HasMany
    {
        return $this->hasMany(AvailableEntry::class, 'child_entry_id', 'parent_entry_id');
    }
    public function parentEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'parent_entry_id', 'id');
    }

    public function childEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'child_entry_id', 'id');
    }

}
