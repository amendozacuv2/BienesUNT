<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE INDEX IF NOT EXISTS areas_name_normalized_trgm_idx
            ON areas USING GIN (
                translate(
                    LOWER(name),
                    'áàäâãåéèëêíìïîóòöôõúùüûñç',
                    'aaaaaaeeeeiiiiooooouuuunc'
                ) gin_trgm_ops
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS areas_name_normalized_trgm_idx');
    }
};
