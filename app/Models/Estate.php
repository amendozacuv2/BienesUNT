<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Estate extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'location_id',
        'patrimonial_code',
        'internal_code',
        'denomination',
        'brand',
        'model',
        'type',
        'color',
        'series',
        'dimensions',
        'others',
        'situation',
        'conservation_status',
        'observation',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function area()
    {
        return $this->hasOneThrough(
            Area::class,
            Location::class,
            'id',
            'id',
            'location_id',
            'area_id'
        );
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function scopeLiveSearch($query, ?string $search)
    {
        $search = trim((string) $search);

        if (mb_strlen($search) < 2) {
            return $query;
        }

        $search = mb_strtolower($search);

        return $query->whereRaw("
            LOWER(
                COALESCE(patrimonial_code, '') || ' ' ||
                COALESCE(internal_code, '') || ' ' ||
                COALESCE(denomination, '') || ' ' ||
                COALESCE(brand, '') || ' ' ||
                COALESCE(model, '') || ' ' ||
                COALESCE(type, '') || ' ' ||
                COALESCE(color, '') || ' ' ||
                COALESCE(series, '') || ' ' ||
                COALESCE(situation, '') || ' ' ||
                COALESCE(conservation_status, '')
            ) ILIKE ?
        ", ['%' . $search . '%']);
    }

    public function scopeByLocation($query, ?int $locationId)
    {
        return $query->when($locationId, function ($query) use ($locationId) {
            $query->where('location_id', $locationId);
        });
    }

    public function scopeByArea($query, ?int $areaId)
    {
        return $query->when($areaId, function ($query) use ($areaId) {
            $query->whereHas('location', function ($query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        });
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
