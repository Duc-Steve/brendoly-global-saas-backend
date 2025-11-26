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
        Schema::create('tenants', function (Blueprint $table) {
            // Identifiant unique
            $table->uuid('id_tenant')->primary();
            // Infos de base
            $table->text('name');
            $table->text('type');
            $table->text('sector');
            $table->text('employees_number')->nullable();
            $table->text('address')->nullable();
            $table->text('city')->nullable();
            $table->text('zipcode')->nullable();
            $table->text('country');
            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
