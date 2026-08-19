<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penilaian mandiri (self-assessment) per indikator Evaluasi Diri.
     */
    public function up(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->string('self_assessment')->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->dropColumn('self_assessment');
        });
    }
};