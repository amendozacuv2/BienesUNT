<?php

namespace App\Services\Estates;

use App\Models\Estate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchEstates
{
    public function forIndex(
        array $allowedAreaIds,
        ?string $search,
        ?int $areaId,
        ?int $locationId,
        ?string $situation,
        ?string $conservationStatus
    ): Builder {
        $query = Estate::query()
            ->with(['location.area']);

        if (empty($allowedAreaIds)) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereHas('location', function (Builder $query) use ($allowedAreaIds) {
            $query->whereIn('area_id', $allowedAreaIds);
        });

        if ($areaId) {
            if (! in_array($areaId, $allowedAreaIds, true)) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereHas('location', function (Builder $query) use ($areaId) {
                $query->where('area_id', $areaId);
            });
        }

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($situation && in_array($situation, Estate::SITUATIONS, true)) {
            $query->where('situation', $situation);
        }

        if ($conservationStatus && in_array($conservationStatus, Estate::CONSERVATION_STATUSES, true)) {
            $query->where('conservation_status', $conservationStatus);
        }

        $this->applySearch($query, $search);

        return $query;
    }

    public function forCreateSearch(
        ?string $search,
        int $perPage = 10,
        string $pageName = 'existingEstatesPage'
    ): LengthAwarePaginator {
        $search = trim((string) $search);

        $query = Estate::query()
            ->with(['location.area']);

        if (mb_strlen($search) < 2) {
            return $query
                ->whereRaw('1 = 0')
                ->paginate($perPage, ['*'], $pageName);
        }

        $this->applySearch($query, $search);

        return $query
            ->latest('updated_at')
            ->latest('id')
            ->paginate($perPage, ['*'], $pageName);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $query->liveSearch($search);
    }
}
