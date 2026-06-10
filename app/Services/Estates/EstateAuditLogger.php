<?php

namespace App\Services\Estates;

use App\Models\AuditLog;
use App\Models\Estate;

class EstateAuditLogger
{
    public function created(Estate $estate): AuditLog
    {
        return AuditLog::register(
            'CREATED',
            $estate,
            null,
            $this->estateValues($estate)
        );
    }

    public function updated(Estate $estate, array $oldValues, array $newValues): AuditLog
    {
        return AuditLog::register(
            'UPDATED',
            $estate,
            $oldValues,
            $newValues
        );
    }

    public function changedLocation(Estate $estate, array $oldValues, array $newValues): AuditLog
    {
        return AuditLog::register(
            'CHANGED_LOCATION',
            $estate,
            $this->locationValues($oldValues),
            $this->locationValues($newValues)
        );
    }

    public function deleted(Estate $estate): AuditLog
    {
        return AuditLog::register(
            'DELETED',
            $estate,
            $this->estateValues($estate),
            null
        );
    }

    public function estateValues(Estate $estate): array
    {
        $estate->loadMissing(['location.area']);

        return [
            'id' => $estate->id,
            'uuid' => $estate->uuid,
            'location_id' => $estate->location_id,
            'area' => $estate->location?->area?->name,
            'location' => $estate->location?->name,
            'patrimonial_code' => $estate->patrimonial_code,
            'internal_code' => $estate->internal_code,
            'denomination' => $estate->denomination,
            'brand' => $estate->brand,
            'model' => $estate->model,
            'type' => $estate->type,
            'color' => $estate->color,
            'series' => $estate->series,
            'dimensions' => $estate->dimensions,
            'others' => $estate->others,
            'situation' => $estate->situation,
            'conservation_status' => $estate->conservation_status,
            'observation' => $estate->observation,
        ];
    }

    private function locationValues(array $values): array
    {
        return [
            'location_id' => $values['location_id'] ?? null,
            'area' => $values['area'] ?? null,
            'location' => $values['location'] ?? null,
        ];
    }
}