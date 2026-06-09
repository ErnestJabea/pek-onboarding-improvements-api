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
        Schema::table('onboarding_sessions', function (Blueprint $table) {
            $table->string('doc_piece_identite')->nullable();
            $table->string('doc_justificatif_domicile')->nullable();
            $table->string('doc_photo')->nullable();
            $table->string('doc_origine_fonds')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'doc_piece_identite',
                'doc_justificatif_domicile',
                'doc_photo',
                'doc_origine_fonds',
            ]);
        });
    }
};
