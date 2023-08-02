<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entry extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function availableChildEntries(): HasMany
    {
        return $this->hasMany(AvailableEntry::class, 'parent_entry_id', 'id');
    }

    public function availableParentEntries(): HasMany
    {
        return $this->hasMany(AvailableEntry::class, 'child_entry_id', 'id');
    }

    public function availableEntry()
    {
        return $this->hasOne(AvailableEntry::class, 'parent_entry_id');
    }
}
