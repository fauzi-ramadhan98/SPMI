<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Poin catatan client: "Prodi tiap semester harus isi risk register
     * tapi ditugaskan oleh SPMI" — entri RR kini per Semester (Ganjil/Genap).
     */
    public function up(): void
    {
        if (Schema::hasColumn('risk_registers', 'semester')) {
            return;
        }

        Schema::table('risk_registers', function (Blueprint $table) {
            $table->enum('semester', ['Ganjil', 'Genap'])->nullable()->after('academic_year');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('risk_registers', 'semester')) {
            return;
        }

        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }
};
