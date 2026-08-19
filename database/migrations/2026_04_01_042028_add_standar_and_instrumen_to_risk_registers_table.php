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
        Schema::table('risk_registers', function (Blueprint $table) {
            // Standar mutu yang berkaitan dengan risiko (misal: Standar 1 - Visi Misi)
            $table->string('standar_mutu')->nullable()->after('academic_year');
            // Instrumen / indikator temuan yang akan digunakan saat audit
            $table->text('instrumen_temuan')->nullable()->after('mitigation_plan');
        });
    }

    public function down(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropColumn(['standar_mutu', 'instrumen_temuan']);
        });
    }
};
