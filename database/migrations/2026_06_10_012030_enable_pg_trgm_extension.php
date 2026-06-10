<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    }

    public function down(): void
    {
        // No elimino la extensión porque puede ser usada por otros índices/tablas.
        // DB::statement('DROP EXTENSION IF EXISTS pg_trgm');
    }
};
