<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function scopeLiveSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if (mb_strlen($search) < 2) {
            return $query;
        }

        return $query->whereRaw('LOWER(name) LIKE ?', [
            '%' . mb_strtolower($search) . '%',
        ]);
    }

    public function canBeDeleted(): bool
    {
        return ! $this->users()->exists();
    }
}
