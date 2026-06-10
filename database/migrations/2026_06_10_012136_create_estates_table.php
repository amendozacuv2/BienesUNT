<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('estates')) {
            Schema::create('estates', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();

                $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();

                $table->string('patrimonial_code', 100)->nullable();
                $table->string('internal_code', 100)->unique();

                $table->string('denomination', 255)->nullable();
                $table->string('brand', 150)->nullable();
                $table->string('model', 150)->nullable();
                $table->string('type', 150)->nullable();
                $table->string('color', 100)->nullable();
                $table->string('series', 150)->nullable();
                $table->text('dimensions')->nullable();
                $table->text('others')->nullable();
                $table->string('situation', 80)->nullable();
                $table->string('conservation_status', 80)->nullable();
                $table->text('observation')->nullable();

                $table->timestamps();
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS estates_location_id_idx ON estates (location_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_patrimonial_code_idx ON estates (patrimonial_code)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_denomination_idx ON estates (denomination)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_brand_idx ON estates (brand)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_model_idx ON estates (model)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_type_idx ON estates (type)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_series_idx ON estates (series)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_situation_idx ON estates (situation)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_conservation_status_idx ON estates (conservation_status)');

        DB::statement('CREATE INDEX IF NOT EXISTS estates_id_desc_idx ON estates (id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_created_at_desc_idx ON estates (created_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_updated_at_desc_idx ON estates (updated_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_location_id_id_desc_idx ON estates (location_id, id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS estates_location_id_created_at_idx ON estates (location_id, created_at DESC)');

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_live_search_trgm_idx
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

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_patrimonial_code_trgm_idx
            ON estates USING GIN (LOWER(patrimonial_code) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_internal_code_trgm_idx
            ON estates USING GIN (LOWER(internal_code) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_denomination_trgm_idx
            ON estates USING GIN (LOWER(denomination) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_brand_trgm_idx
            ON estates USING GIN (LOWER(brand) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_model_trgm_idx
            ON estates USING GIN (LOWER(model) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_series_trgm_idx
            ON estates USING GIN (LOWER(series) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS estates_type_trgm_idx
            ON estates USING GIN (LOWER(type) gin_trgm_ops)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('estates');
    }
};
