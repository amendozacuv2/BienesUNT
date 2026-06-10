<?php

namespace App\Services\Estates;

use App\Models\Estate;

class NormalizeEstateData
{
    public function cleanText(?string $value): ?string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        if ($value === '') {
            return null;
        }

        return mb_strtoupper($value);
    }

    public function cleanInternalCode(?string $value): string
    {
        return mb_strtoupper(preg_replace('/\s+/u', ' ', trim((string) $value)));
    }

    public function internalCodeKey(?string $value): string
    {
        return mb_strtolower($this->cleanInternalCode($value));
    }

    public function normalizeSituation(?string $value): string
    {
        $value = mb_strtoupper(trim((string) $value));

        return in_array($value, Estate::SITUATIONS, true)
            ? $value
            : Estate::SITUATIONS[0];
    }

    public function normalizeConservationStatus(?string $value): string
    {
        $value = mb_strtoupper(trim((string) $value));

        return in_array($value, Estate::CONSERVATION_STATUSES, true)
            ? $value
            : Estate::CONSERVATION_STATUSES[0];
    }

    public function fromRow(array $row, int $locationId): array
    {
        return [
            'location_id' => $locationId,
            'patrimonial_code' => $this->cleanText($row['patrimonial_code'] ?? null),
            'internal_code' => $this->cleanInternalCode($row['internal_code'] ?? null),
            'denomination' => $this->cleanText($row['denomination'] ?? null),
            'brand' => $this->cleanText($row['brand'] ?? null),
            'model' => $this->cleanText($row['model'] ?? null),
            'type' => $this->cleanText($row['type'] ?? null),
            'color' => $this->cleanText($row['color'] ?? null),
            'series' => $this->cleanText($row['series'] ?? null),
            'dimensions' => $this->cleanText($row['dimensions'] ?? null),
            'others' => $this->cleanText($row['others'] ?? null),
            'situation' => $this->normalizeSituation($row['situation'] ?? null),
            'conservation_status' => $this->normalizeConservationStatus($row['conservation_status'] ?? null),
            'observation' => $this->cleanText($row['observation'] ?? null),
        ];
    }
}