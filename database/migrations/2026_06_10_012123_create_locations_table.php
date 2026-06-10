<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name', 180);
                $table->foreignId('area_id')->constrained('areas')->restrictOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS locations_area_id_idx ON locations (area_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS locations_is_active_idx ON locations (is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS locations_area_active_idx ON locations (area_id, is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS locations_name_idx ON locations (name)');

        DB::statement("
            CREATE INDEX IF NOT EXISTS locations_name_trgm_idx
            ON locations USING GIN (LOWER(name) gin_trgm_ops)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
