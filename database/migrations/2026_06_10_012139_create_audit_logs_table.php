<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();

                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->string('auditable_type', 150);
                $table->unsignedBigInteger('auditable_id');

                $table->string('action', 80);

                $table->jsonb('old_values')->nullable();
                $table->jsonb('new_values')->nullable();

                $table->string('ip_address', 80)->nullable();
                $table->text('user_agent')->nullable();

                $table->timestamp('created_at')->nullable();
            });
        }

        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_user_id_idx ON audit_logs (user_id)');

        DB::statement("
            CREATE INDEX IF NOT EXISTS audit_logs_auditable_idx
            ON audit_logs (auditable_type, auditable_id)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS audit_logs_auditable_created_at_idx
            ON audit_logs (auditable_type, auditable_id, created_at DESC)
        ");

        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_action_idx ON audit_logs (action)');
        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_created_at_idx ON audit_logs (created_at DESC)');

        DB::statement("
            CREATE INDEX IF NOT EXISTS audit_logs_user_created_at_idx
            ON audit_logs (user_id, created_at DESC)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS audit_logs_old_values_gin_idx
            ON audit_logs USING GIN (old_values jsonb_path_ops)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS audit_logs_new_values_gin_idx
            ON audit_logs USING GIN (new_values jsonb_path_ops)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
