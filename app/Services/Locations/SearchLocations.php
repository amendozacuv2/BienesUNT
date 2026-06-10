<?php

namespace App\Services\Locations;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchLocations
{
    private const ACCENTED_CHARACTERS = 'áàäâãåéèëêíìïîóòöôõúùüûñç';

    private const PLAIN_CHARACTERS = 'aaaaaaeeeeiiiiooooouuuunc';

    public function apply(Builder $query, ?string $search, ?string $areaId, array $allowedAreaIds): Builder
    {
        $allowedAreaIds = $this->normalizeIds($allowedAreaIds);

        $this->applyAllowedAreas($query, $allowedAreaIds);
        $this->applyAreaFilter($query, $areaId, $allowedAreaIds);
        $this->applyNameSearch($query, $search);

        return $query;
    }

    public function existsNormalizedName(string $name, int $areaId, ?int $ignoreLocationId = null): bool
    {
        $normalizedName = $this->normalize($name);

        if ($normalizedName === '') {
            return false;
        }

        return Location::query()
            ->where('locations.area_id', $areaId)
            ->when($ignoreLocationId, function (Builder $query) use ($ignoreLocationId) {
                $query->where('locations.id', '!=', $ignoreLocationId);
            })
            ->whereRaw(
                'translate(LOWER(locations.name), ?, ?) = ?',
                [
                    self::ACCENTED_CHARACTERS,
                    self::PLAIN_CHARACTERS,
                    $normalizedName,
                ]
            )
            ->exists();
    }

    public function cleanForStorage(string $name): string
    {
        return $this->clean($name);
    }

    public function normalize(?string $value): string
    {
        $value = mb_strtolower($this->clean($value));

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

        $query->whereIn('locations.area_id', $allowedAreaIds);
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

        $query->where('locations.area_id', $areaId);
    }

    private function applyNameSearch(Builder $query, ?string $search): void
    {
        $rawSearch = $this->clean($search);

        if (mb_strlen($rawSearch) < 2) {
            return;
        }

        $normalizedSearch = $this->normalize($rawSearch);

        $query->where(function (Builder $query) use ($rawSearch, $normalizedSearch) {
            $query->whereRaw('LOWER(locations.name) ILIKE ?', [
                '%' . mb_strtolower($rawSearch) . '%',
            ])->orWhereRaw(
                'translate(LOWER(locations.name), ?, ?) ILIKE ?',
                [
                    self::ACCENTED_CHARACTERS,
                    self::PLAIN_CHARACTERS,
                    '%' . $normalizedSearch . '%',
                ]
            );
        });
    }

    private function clean(?string $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/\s+/u', ' ', $value) ?? '';
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
