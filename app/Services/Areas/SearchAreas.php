<?php

namespace App\Services\Areas;

use App\Models\Area;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchAreas
{
    private const ACCENTED_CHARACTERS = 'áàäâãåéèëêíìïîóòöôõúùüûñç';

    private const PLAIN_CHARACTERS = 'aaaaaaeeeeiiiiooooouuuunc';

    public function apply(Builder $query, ?string $search): Builder
    {
        $rawSearch = $this->clean($search);

        if (mb_strlen($rawSearch) < 2) {
            return $query;
        }

        $normalizedSearch = $this->normalize($rawSearch);

        return $query->where(function (Builder $query) use ($rawSearch, $normalizedSearch) {
            $query->whereRaw('LOWER(name) ILIKE ?', [
                '%' . $rawSearch . '%',
            ])->orWhereRaw(
                'translate(LOWER(name), ?, ?) ILIKE ?',
                [
                    self::ACCENTED_CHARACTERS,
                    self::PLAIN_CHARACTERS,
                    '%' . $normalizedSearch . '%',
                ]
            );
        });
    }

    public function existsNormalizedName(string $name, ?int $ignoreAreaId = null): bool
    {
        $normalizedName = $this->normalize($name);

        if ($normalizedName === '') {
            return false;
        }

        return Area::query()
            ->when($ignoreAreaId, function (Builder $query) use ($ignoreAreaId) {
                $query->where('id', '!=', $ignoreAreaId);
            })
            ->whereRaw(
                'translate(LOWER(name), ?, ?) = ?',
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

    private function clean(?string $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/\s+/u', ' ', $value) ?? '';
    }
}
