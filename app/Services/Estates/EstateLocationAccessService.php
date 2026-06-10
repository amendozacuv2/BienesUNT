<?php

namespace App\Services\Estates;

use App\Models\Area;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Collection;

class EstateLocationAccessService
{
    public function assignedAreaIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return $user
            ->activeAreas()
            ->pluck('areas.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function canAccessArea(?User $user, int $areaId): bool
    {
        if ($areaId <= 0) {
            return false;
        }

        return in_array($areaId, $this->assignedAreaIds($user), true);
    }

    public function activeAreasForUser(?User $user): Collection
    {
        $allowedAreaIds = $this->assignedAreaIds($user);

        if (empty($allowedAreaIds)) {
            return collect();
        }

        return Area::query()
            ->whereIn('id', $allowedAreaIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function activeLocationsForUserArea(?User $user, ?int $areaId): Collection
    {
        $allowedAreaIds = $this->assignedAreaIds($user);

        if (empty($allowedAreaIds) || ! $areaId) {
            return collect();
        }

        if (! in_array($areaId, $allowedAreaIds, true)) {
            return collect();
        }

        return Location::query()
            ->whereIn('area_id', $allowedAreaIds)
            ->where('area_id', $areaId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function selectedAreaForView(?User $user, ?int $areaId): ?Area
    {
        if (! $areaId) {
            return null;
        }

        $allowedAreaIds = $this->assignedAreaIds($user);

        if (! in_array($areaId, $allowedAreaIds, true)) {
            return null;
        }

        return Area::query()
            ->with(['employees' => function ($query) {
                $query
                    ->where('employees.is_active', true)
                    ->where('employee_area.is_active', true)
                    ->orderBy('lastname')
                    ->orderBy('name');
            }])
            ->whereIn('id', $allowedAreaIds)
            ->where('is_active', true)
            ->find($areaId);
    }

    public function findAllowedLocation(?User $user, int $areaId, int $locationId): ?Location
    {
        if ($areaId <= 0 || $locationId <= 0) {
            return null;
        }

        $allowedAreaIds = $this->assignedAreaIds($user);

        if (! in_array($areaId, $allowedAreaIds, true)) {
            return null;
        }

        return Location::query()
            ->whereKey($locationId)
            ->where('area_id', $areaId)
            ->whereIn('area_id', $allowedAreaIds)
            ->where('is_active', true)
            ->first();
    }
}