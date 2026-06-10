<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement("
            CREATE INDEX CONCURRENTLY IF NOT EXISTS estates_live_search_aligned_trgm_idx
            ON estates USING GIN (
                LOWER(
                    COALESCE(patrimonial_code, '') || ' ' ||
                    COALESCE(internal_code, '') || ' ' ||
                    COALESCE(denomination, '') || ' ' ||
                    COALESCE(brand, '') || ' ' ||
                    COALESCE(model, '') || ' ' ||
                    COALESCE(type, '') || ' ' ||
                    COALESCE(observation, '')
                ) gin_trgm_ops
            )
        ");

        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS estates_live_search_trgm_idx');
        DB::statement('ALTER INDEX estates_live_search_aligned_trgm_idx RENAME TO estates_live_search_trgm_idx');
    }

    public function down(): void
    {
        DB::statement("
            CREATE INDEX CONCURRENTLY IF NOT EXISTS estates_live_search_legacy_trgm_idx
            ON estates USING GIN (
                LOWER(
                    COALESCE(patrimonial_code, '') || ' ' ||
                    COALESCE(internal_code, '') || ' ' ||
                    COALESCE(denomination, '') || ' ' ||
                    COALESCE(brand, '') || ' ' ||
                    COALESCE(model, '') || ' ' ||
                    COALESCE(type, '') || ' ' ||
                    COALESCE(color, '') || ' ' ||
                    COALESCE(series, '') || ' ' ||
                    COALESCE(situation, '') || ' ' ||
                    COALESCE(conservation_status, '')
                ) gin_trgm_ops
            )
        ");

        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS estates_live_search_trgm_idx');
        DB::statement('ALTER INDEX estates_live_search_legacy_trgm_idx RENAME TO estates_live_search_trgm_idx');
    }
};
