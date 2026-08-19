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
            // Butir / Daftar Tilik (instrumen/indikator audit) — wajib
            $table->text('butir_tilik')->nullable()->after('standar_mutu');
            // Temuan — catatan temuan/kondisi saat ini — wajib
            $table->text('temuan')->nullable()->after('risk_description');
        });

        // Hapus kolom instrumen_temuan yang sudah digantikan butir_tilik
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropColumn('instrumen_temuan');
        });
    }

    public function down(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropColumn(['butir_tilik', 'temuan']);
            $table->text('instrumen_temuan')->nullable();
        });
    }
};
