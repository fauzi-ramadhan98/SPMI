<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan/instruksi pimpinan pada Modul Pengendalian (P3):
     * - audit_cycles.pimpinan_note  → catatan RTM (Rapat Tinjauan Manajemen) per siklus
     * - audit_findings.pimpinan_note → catatan/instruksi pimpinan per temuan (RTL)
     */
    public function up(): void
    {
        Schema::table('audit_cycles', function (Blueprint $table) {
            $table->text('pimpinan_note')->nullable()->after('description');
        });

        Schema::table('audit_findings', function (Blueprint $table) {
            $table->text('pimpinan_note')->nullable()->after('target_date');
        });
    }

    public function down(): void
    {
        Schema::table('audit_cycles', function (Blueprint $table) {
            $table->dropColumn('pimpinan_note');
        });

        Schema::table('audit_findings', function (Blueprint $table) {
            $table->dropColumn('pimpinan_note');
        });
    }
};
