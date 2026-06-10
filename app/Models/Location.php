<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'area_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function estates()
    {
        return $this->hasMany(Estate::class);
    }

    public function activeEstates()
    {
        return $this->estates();
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
