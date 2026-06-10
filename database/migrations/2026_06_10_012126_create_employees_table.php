<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('dni', 20)->unique();
                $table->string('name', 120);
                $table->string('lastname', 120);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS employees_name_idx ON employees (name)');
        DB::statement('CREATE INDEX IF NOT EXISTS employees_lastname_idx ON employees (lastname)');
        DB::statement('CREATE INDEX IF NOT EXISTS employees_is_active_idx ON employees (is_active)');
        DB::statement('CREATE INDEX IF NOT EXISTS employees_dni_idx ON employees (dni)');

        DB::statement("
            CREATE INDEX IF NOT EXISTS employees_dni_trgm_idx
            ON employees USING GIN (LOWER(dni) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS employees_name_trgm_idx
            ON employees USING GIN (LOWER(name) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS employees_lastname_trgm_idx
            ON employees USING GIN (LOWER(lastname) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS employees_fullname_trgm_idx
            ON employees USING GIN (
                LOWER(name || ' ' || lastname) gin_trgm_ops
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
