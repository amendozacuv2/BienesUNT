<?php

namespace App\Services\Users;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchUsers
{
    private const ACCENTED_CHARACTERS = 'áàäâãåéèëêíìïîóòöôõúùüûñç';

    private const PLAIN_CHARACTERS = 'aaaaaaeeeeiiiiooooouuuunc';

    public function apply(Builder $query, ?string $search, ?string $areaId): Builder
    {
        $this->applyAreaFilter($query, $areaId);
        $this->applySearch($query, $search);

        return $query;
    }

    public function cleanForStorage(?string $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/\s+/u', ' ', $value) ?? '';
    }

    public function cleanUsername(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', '', $value) ?? '';

        return mb_strtolower($value);
    }

    public function normalize(?string $value): string
    {
        $value = mb_strtolower($this->cleanForStorage($value));

        if ($value === '') {
            return '';
        }

        return Str::ascii($value);
    }

    private function applyAreaFilter(Builder $query, ?string $areaId): void
    {
        if (! is_numeric($areaId)) {
            return;
        }

        $query->whereHas('areas', function (Builder $query) use ($areaId) {
            $query->where('areas.id', (int) $areaId)
                ->where('user_area.is_active', true);
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
                ->whereRaw('LOWER(users.username) ILIKE ?', [
                    '%' . mb_strtolower($rawSearch) . '%',
                ])
                ->orWhereRaw(
                    'translate(LOWER(users.name), ?, ?) ILIKE ?',
                    [
                        self::ACCENTED_CHARACTERS,
                        self::PLAIN_CHARACTERS,
                        '%' . $normalizedSearch . '%',
                    ]
                );
        });
    }
}
