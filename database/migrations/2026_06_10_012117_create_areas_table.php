<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name', 150);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS areas_name_idx ON areas (name)');
        DB::statement('CREATE INDEX IF NOT EXISTS areas_is_active_idx ON areas (is_active)');

        DB::statement("
            CREATE INDEX IF NOT EXISTS areas_name_trgm_idx
            ON areas USING GIN (LOWER(name) gin_trgm_ops)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
