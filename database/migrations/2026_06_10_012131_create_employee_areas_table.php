<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_area')) {
            Schema::create('employee_area', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
                $table->foreignId('area_id')->constrained('areas')->restrictOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['employee_id', 'area_id'], 'employee_area_employee_id_area_id_unique');
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS employee_area_employee_id_idx ON employee_area (employee_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS employee_area_area_id_idx ON employee_area (area_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS employee_area_is_active_idx ON employee_area (is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS employee_area_employee_active_idx ON employee_area (employee_id, is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS employee_area_area_active_idx ON employee_area (area_id, is_active)');
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_area');
    }
};
