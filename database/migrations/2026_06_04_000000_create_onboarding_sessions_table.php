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
        Schema::create('onboarding_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_step')->default('kyc'); // kyc, risk, labft, signature, completed
            $table->json('payload')->nullable(); // Saisies temporaires (Civ, Nom, etc.)
            $table->string('risk_level')->default('LOW'); // LOW, HIGH
            $table->string('status')->default('in_progress'); // in_progress, completed
            $table->string('signature_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_sessions');
    }
};
