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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id_audit_log')->primary();
            $table->text('action'); // created, updated, deleted
            $table->text('universe'); // stock etc....
            $table->text('module'); // stock etc....
            $table->text('model_type'); // App\Models\...
            $table->uuid('model_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->ipAddress('ip_address');

            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants', 'id_tenant')->onDelete('cascade');
            $table->foreignUuid('user_identity_id')->constrained('user_identifies', 'id_user_identity')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
