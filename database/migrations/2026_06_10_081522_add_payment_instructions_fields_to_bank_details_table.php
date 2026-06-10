<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_details', function (Blueprint $table) {
            $table->text('om_instructions')->nullable();
            $table->text('momo_instructions')->nullable();
            $table->text('bank_instructions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_details', function (Blueprint $table) {
            $table->dropColumn(['om_instructions', 'momo_instructions', 'bank_instructions']);
        });
    }
};
