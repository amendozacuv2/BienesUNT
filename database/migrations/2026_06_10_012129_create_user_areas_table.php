<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_area')) {
            Schema::create('user_area', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('area_id')->constrained('areas')->restrictOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'area_id'], 'user_area_user_id_area_id_unique');
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS user_area_user_id_idx ON user_area (user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS user_area_area_id_idx ON user_area (area_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS user_area_is_active_idx ON user_area (is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS user_area_user_active_idx ON user_area (user_id, is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS user_area_area_active_idx ON user_area (area_id, is_active)');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_area');
    }
};
