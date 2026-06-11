<?php

namespace App\Services\Estates\Exports;

use App\Models\Estate;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class EstateExportQuery
{
    public function build(
        array $allowedAreaIds,
        ?string $search,
        ?int $areaId,
        ?int $locationId,
        ?string $situation,
        ?string $conservationStatus
    ): Builder {
        $query = DB::table('estates')
            ->join('locations', 'locations.id', '=', 'estates.location_id')
            ->join('areas', 'areas.id', '=', 'locations.area_id')
            ->select([
                'estates.id as id',
                'estates.location_id as location_id',
                'areas.id as area_id',
                'estates.patrimonial_code',
                'estates.internal_code',
                'estates.denomination',
                'estates.brand',
                'estates.model',
                'estates.type',
                'estates.color',
                'estates.series',
                'estates.dimensions',
                'estates.others',
                'estates.situation',
                'estates.conservation_status',
                'estates.observation',
                'locations.name as location_name',
                'areas.name as area_name',
                DB::raw("to_char(estates.created_at, 'YYYY-MM-DD HH24:MI:SS') as created_at"),
                DB::raw("to_char(estates.updated_at, 'YYYY-MM-DD HH24:MI:SS') as updated_at"),
            ]);

        if (empty($allowedAreaIds)) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereIn('locations.area_id', $allowedAreaIds);

        if ($areaId) {
            if (! in_array($areaId, $allowedAreaIds, true)) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('locations.area_id', $areaId);
        }

        if ($locationId) {
            $query->where('estates.location_id', $locationId);
        }

        if ($situation && in_array($situation, Estate::SITUATIONS, true)) {
            $query->where('estates.situation', $situation);
        }

        if ($conservationStatus && in_array($conservationStatus, Estate::CONSERVATION_STATUSES, true)) {
            $query->where('estates.conservation_status', $conservationStatus);
        }

        $this->applySearch($query, $search);

        return $query;
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);

        if (mb_strlen($search) < 2) {
            return;
        }

        $query->whereRaw($this->liveSearchExpression().' LIKE ?', [
            '%'.mb_strtolower($search).'%',
        ]);
    }

    private function liveSearchExpression(): string
    {
        return <<<'SQL'
            LOWER(
                COALESCE(estates.patrimonial_code, '') || ' ' ||
                COALESCE(estates.internal_code, '') || ' ' ||
                COALESCE(estates.denomination, '') || ' ' ||
                COALESCE(estates.brand, '') || ' ' ||
                COALESCE(estates.model, '') || ' ' ||
                COALESCE(estates.type, '') || ' ' ||
                COALESCE(estates.observation, '')
            )
            SQL;
    }
}
