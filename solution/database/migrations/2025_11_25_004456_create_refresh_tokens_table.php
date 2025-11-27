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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id_refresh_token')->primary();
            $table->string('token')->unique();
            $table->foreignUuid('user_identity_id')->constrained('user_identifies', 'id_user_identity')->onDelete('cascade');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Ajouter un index sur expires_at pour les performances
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_sessions');
    }
};
