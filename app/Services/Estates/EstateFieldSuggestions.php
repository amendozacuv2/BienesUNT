<?php

namespace App\Services\Estates;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class EstateFieldSuggestions
{
    private const CACHE_TTL_SECONDS = 300;

    private const CACHE_VERSION_KEY = 'estates:field-suggestions:version';

    private const MIN_LENGTH = 3;

    private const DEFAULT_LIMIT = 20;

    private const MAX_LIMIT = 50;

    private const FIELDS = [
        'denomination' => '"denomination"',
        'brand' => '"brand"',
        'model' => '"model"',
        'type' => '"type"',
        'color' => '"color"',
    ];

    public function forSelect2(string $field, ?string $term, int $limit = self::DEFAULT_LIMIT): array
    {
        $column = self::FIELDS[$field] ?? null;

        if ($column === null) {
            return [];
        }

        $term = $this->normalizeTerm($term);

        if (mb_strlen($term) < self::MIN_LENGTH) {
            return [];
        }

        $limit = max(1, min($limit, self::MAX_LIMIT));

        try {
            return Cache::remember(
                $this->cacheKey($field, $term, $limit),
                self::CACHE_TTL_SECONDS,
                fn () => $this->querySuggestions($column, $term, $limit)
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->querySuggestions($column, $term, $limit);
        }
    }

    public function invalidateCache(): void
    {
        try {
            $version = (int) Cache::get(self::CACHE_VERSION_KEY, 1);

            Cache::forever(self::CACHE_VERSION_KEY, $version + 1);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function allowedFields(): array
    {
        return array_keys(self::FIELDS);
    }

    private function querySuggestions(string $column, string $term, int $limit): array
    {
        $search = '%'.mb_strtolower($term).'%';
        $startsWith = mb_strtolower($term).'%';

        return DB::table('estates')
            ->selectRaw("MIN(BTRIM({$column})) as value")
            ->selectRaw('COUNT(*) as total')
            ->whereRaw("{$column} IS NOT NULL")
            ->whereRaw("BTRIM({$column}) <> ''")
            ->whereRaw("LOWER({$column}) LIKE ?", [$search])
            ->groupByRaw("LOWER(BTRIM({$column}))")
            ->orderByRaw(
                "CASE WHEN LOWER(MIN(BTRIM({$column}))) LIKE ? THEN 0 ELSE 1 END",
                [$startsWith]
            )
            ->orderByRaw('COUNT(*) DESC')
            ->orderByRaw("MIN(BTRIM({$column})) ASC")
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'id' => (string) $row->value,
                'text' => (string) $row->value,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function cacheKey(string $field, string $term, int $limit): string
    {
        $version = (int) Cache::get(self::CACHE_VERSION_KEY, 1);

        return sprintf(
            'estates:field-suggestions:v%d:%s:%d:%s',
            $version,
            $field,
            $limit,
            hash('sha256', mb_strtolower($term))
        );
    }

    private function normalizeTerm(?string $term): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $term));
    }
}
