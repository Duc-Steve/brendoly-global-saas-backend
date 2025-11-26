<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_identifies', function (Blueprint $table) {
            // Identifiant unique
            $table->uuid('id_user_identity')->primary();

            // Infos de base
            $table->text('first_name');
            $table->text('last_name');
            $table->string('email')->unique();
            $table->string('phone')->unique();

            // Vérifications
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // Authentification
            $table->text('password');

            // Statuts internes
            $table->boolean('is_active')->default(true);
            $table->boolean('is_superadmin')->default(false);
            $table->timestamp('last_login_at')->nullable();

            // Lien vers la société
            $table->foreignUuid('tenant_id')
                ->nullable()
                ->constrained('tenants', 'id_tenant')
                ->nullOnDelete();

            // Token de session
            $table->rememberToken();

            // Timestamps
            $table->timestamps();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('code');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_identity');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
