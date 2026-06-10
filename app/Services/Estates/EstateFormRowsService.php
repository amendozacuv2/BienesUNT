<?php

namespace App\Services\Estates;

use App\Models\Estate;
use Illuminate\Support\Str;

class EstateFormRowsService
{
    public function emptyRow(): array
    {
        return [
            'row_key' => (string) Str::uuid(),
            'existing_uuid' => '',
            'current_area' => '',
            'current_location' => '',
            'patrimonial_code' => '',
            'internal_code' => '',
            'denomination' => '',
            'brand' => '',
            'model' => '',
            'type' => '',
            'color' => '',
            'series' => '',
            'dimensions' => '',
            'others' => '',
            'situation' => Estate::SITUATIONS[0],
            'conservation_status' => Estate::CONSERVATION_STATUSES[0],
            'observation' => '',
        ];
    }

    public function fromEstate(Estate $estate): array
    {
        $estate->loadMissing(['location.area']);

        return [
            'row_key' => (string) Str::uuid(),
            'existing_uuid' => $estate->uuid,
            'current_area' => $estate->location?->area?->name ?? '',
            'current_location' => $estate->location?->name ?? '',
            'patrimonial_code' => $estate->patrimonial_code ?? '',
            'internal_code' => $estate->internal_code ?? '',
            'denomination' => $estate->denomination ?? '',
            'brand' => $estate->brand ?? '',
            'model' => $estate->model ?? '',
            'type' => $estate->type ?? '',
            'color' => $estate->color ?? '',
            'series' => $estate->series ?? '',
            'dimensions' => $estate->dimensions ?? '',
            'others' => $estate->others ?? '',
            'situation' => $estate->situation ?: Estate::SITUATIONS[0],
            'conservation_status' => $estate->conservation_status ?: Estate::CONSERVATION_STATUSES[0],
            'observation' => $estate->observation ?? '',
        ];
    }

    public function containsEstate(array $rows, string $uuid): bool
    {
        return collect($rows)->contains(fn ($row) => ($row['existing_uuid'] ?? '') === $uuid);
    }

    public function addEstateToRows(array $rows, Estate $estate): array
    {
        $row = $this->fromEstate($estate);

        if (count($rows) === 1 && $this->isEmptyNewRow($rows[0])) {
            return [$row];
        }

        $rows[] = $row;

        return array_values($rows);
    }

    public function removeAt(array $rows, int $index): array
    {
        unset($rows[$index]);

        $rows = array_values($rows);

        if ($rows === []) {
            return [$this->emptyRow()];
        }

        return $rows;
    }

    public function isEmptyNewRow(array $row): bool
    {
        return empty($row['existing_uuid'])
            && trim((string) ($row['patrimonial_code'] ?? '')) === ''
            && trim((string) ($row['internal_code'] ?? '')) === ''
            && trim((string) ($row['denomination'] ?? '')) === ''
            && trim((string) ($row['brand'] ?? '')) === ''
            && trim((string) ($row['model'] ?? '')) === ''
            && trim((string) ($row['type'] ?? '')) === '';
    }
}