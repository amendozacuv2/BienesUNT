<?php

namespace App\Services\Estates\Exports;

use App\Services\Estates\SearchEstates;
use Illuminate\Database\Eloquent\Builder;

class EstateExportQuery
{
    public function __construct(
        private readonly SearchEstates $searchEstates
    ) {}

    public function build(
        array $allowedAreaIds,
        ?string $search,
        ?int $areaId,
        ?int $locationId,
        ?string $situation,
        ?string $conservationStatus
    ): Builder {
        $query = $this->searchEstates->forIndex(
            allowedAreaIds: $allowedAreaIds,
            search: $search,
            areaId: $areaId,
            locationId: $locationId,
            situation: $situation,
            conservationStatus: $conservationStatus
        );

        /*
         * Quitamos eager loads del listado y ponemos los necesarios para export.
         * Así evitamos N+1 y no cargamos relaciones sobrantes.
         */
        $query->setEagerLoads([]);

        return $query
            ->select([
                'estates.id',
                'estates.location_id',
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
                'estates.created_at',
                'estates.updated_at',
            ])
            ->with([
                'location:id,name,area_id',
                'location.area:id,name',
                'location.area.employees' => function ($query) {
                    $query
                        ->select([
                            'employees.id',
                            'employees.name',
                            'employees.lastname',
                        ])
                        ->where('employees.is_active', true)
                        ->where('employee_area.is_active', true)
                        ->orderBy('employees.lastname')
                        ->orderBy('employees.name');
                },
            ]);
    }
}
