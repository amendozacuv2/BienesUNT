<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Necesario para índices GIN con gin_trgm_ops.
         */
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        /**
         * TABLA: users
         */
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();

                $table->uuid('uuid')->unique();

                $table->string('name', 150);
                $table->string('username', 100)->unique();

                $table->string('password', 255);
                $table->rememberToken();

                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
        }

        /**
         * ÍNDICES NORMALES
         */
        DB::statement('CREATE INDEX IF NOT EXISTS users_name_idx ON users (name)');
        DB::statement('CREATE INDEX IF NOT EXISTS users_username_idx ON users (username)');
        DB::statement('CREATE INDEX IF NOT EXISTS users_is_active_idx ON users (is_active)');

        /**
         * ÍNDICES PARA BÚSQUEDA EN VIVO
         */
        DB::statement("
            CREATE INDEX IF NOT EXISTS users_name_trgm_idx
            ON users USING GIN (LOWER(name) gin_trgm_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS users_username_trgm_idx
            ON users USING GIN (LOWER(username) gin_trgm_ops)
        ");

        /**
         * TABLA: password_reset_tokens
         *
         * La dejo porque Laravel/Breeze puede necesitarla.
         * Aunque uses username para login, esta tabla no estorba.
         */
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        /**
         * TABLA: sessions
         *
         * Necesaria si usas sesiones en base de datos.
         */
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();

                $table->foreignId('user_id')
                    ->nullable()
                    ->index();

                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_username_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS users_name_trgm_idx');

        DB::statement('DROP INDEX IF EXISTS users_is_active_idx');
        DB::statement('DROP INDEX IF EXISTS users_username_idx');
        DB::statement('DROP INDEX IF EXISTS users_name_idx');

        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
