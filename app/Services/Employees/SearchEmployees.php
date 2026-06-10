<?php

namespace App\Services\Employees;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchEmployees
{
    private const ACCENTED_CHARACTERS = 'áàäâãåéèëêíìïîóòöôõúùüûñç';

    private const PLAIN_CHARACTERS = 'aaaaaaeeeeiiiiooooouuuunc';

    public function apply(Builder $query, ?string $search, ?string $areaId, array $allowedAreaIds): Builder
    {
        $allowedAreaIds = $this->normalizeIds($allowedAreaIds);

        $this->applyAllowedAreas($query, $allowedAreaIds);
        $this->applyAreaFilter($query, $areaId, $allowedAreaIds);
        $this->applySearch($query, $search);

        return $query;
    }

    public function cleanForStorage(?string $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/\s+/u', ' ', $value) ?? '';
    }

    public function normalize(?string $value): string
    {
        $value = mb_strtolower($this->cleanForStorage($value));

        if ($value === '') {
            return '';
        }

        return Str::ascii($value);
    }

    private function applyAllowedAreas(Builder $query, array $allowedAreaIds): void
    {
        if (empty($allowedAreaIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas('areas', function (Builder $query) use ($allowedAreaIds) {
            $query->whereIn('areas.id', $allowedAreaIds)
                ->where('employee_area.is_active', true);
        });
    }

    private function applyAreaFilter(Builder $query, ?string $areaId, array $allowedAreaIds): void
    {
        if (! is_numeric($areaId)) {
            return;
        }

        $areaId = (int) $areaId;

        if (! in_array($areaId, $allowedAreaIds, true)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas('areas', function (Builder $query) use ($areaId) {
            $query->where('areas.id', $areaId)
                ->where('employee_area.is_active', true);
        });
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $rawSearch = $this->cleanForStorage($search);

        if (mb_strlen($rawSearch) < 2) {
            return;
        }

        $normalizedSearch = $this->normalize($rawSearch);

        $query->where(function (Builder $query) use ($rawSearch, $normalizedSearch) {
            $query
                ->whereRaw('LOWER(employees.dni) ILIKE ?', [
                    '%' . mb_strtolower($rawSearch) . '%',
                ])
                ->orWhereRaw(
                    'translate(LOWER(employees.name), ?, ?) ILIKE ?',
                    [
                        self::ACCENTED_CHARACTERS,
                        self::PLAIN_CHARACTERS,
                        '%' . $normalizedSearch . '%',
                    ]
                )
                ->orWhereRaw(
                    'translate(LOWER(employees.lastname), ?, ?) ILIKE ?',
                    [
                        self::ACCENTED_CHARACTERS,
                        self::PLAIN_CHARACTERS,
                        '%' . $normalizedSearch . '%',
                    ]
                )
                ->orWhereRaw(
                    "translate(LOWER(employees.name || ' ' || employees.lastname), ?, ?) ILIKE ?",
                    [
                        self::ACCENTED_CHARACTERS,
                        self::PLAIN_CHARACTERS,
                        '%' . $normalizedSearch . '%',
                    ]
                )
                ->orWhereRaw(
                    "translate(LOWER(employees.lastname || ' ' || employees.name), ?, ?) ILIKE ?",
                    [
                        self::ACCENTED_CHARACTERS,
                        self::PLAIN_CHARACTERS,
                        '%' . $normalizedSearch . '%',
                    ]
                );
        });
    }

    private function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }
}
