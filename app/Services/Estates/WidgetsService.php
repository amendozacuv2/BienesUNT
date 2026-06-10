<?php

namespace App\Services\Estates;

use App\Models\Estate;

class WidgetsService
{
    public function __construct(
        private readonly SearchEstates $searchEstates
    ) {
    }

    public function forIndex(
        array $allowedAreaIds,
        ?string $search,
        ?int $areaId,
        ?int $locationId,
        ?string $situation,
        ?string $conservationStatus
    ): array {
        $query = $this->searchEstates->forIndex(
            $allowedAreaIds,
            $search,
            $areaId,
            $locationId,
            $situation,
            $conservationStatus
        );

        $query->setEagerLoads([]);

        $summary = $query
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'SUM(CASE WHEN estates.situation = ? THEN 1 ELSE 0 END) as situation_en_uso',
                ['EN USO']
            )
            ->selectRaw(
                'SUM(CASE WHEN estates.situation = ? THEN 1 ELSE 0 END) as situation_desuso',
                ['DESUSO']
            )
            ->selectRaw(
                'SUM(CASE WHEN estates.conservation_status = ? THEN 1 ELSE 0 END) as conservation_bueno',
                ['BUENO']
            )
            ->selectRaw(
                'SUM(CASE WHEN estates.conservation_status = ? THEN 1 ELSE 0 END) as conservation_regular',
                ['REGULAR']
            )
            ->selectRaw(
                'SUM(CASE WHEN estates.conservation_status = ? THEN 1 ELSE 0 END) as conservation_malo',
                ['MALO']
            )
            ->first();

        return [
            'total' => (int) ($summary->total ?? 0),
            'situations' => [
                'EN USO' => (int) ($summary->situation_en_uso ?? 0),
                'DESUSO' => (int) ($summary->situation_desuso ?? 0),
            ],
            'conservation' => [
                'BUENO' => (int) ($summary->conservation_bueno ?? 0),
                'REGULAR' => (int) ($summary->conservation_regular ?? 0),
                'MALO' => (int) ($summary->conservation_malo ?? 0),
            ],
        ];
    }
}