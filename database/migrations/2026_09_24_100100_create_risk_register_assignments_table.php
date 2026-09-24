<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penugasan pengisian Risk Register per periode (T.A. + Semester)
     * yang dibuat oleh SPMI untuk satu Risk Owner (prodi atau unit).
     * Poin catatan client: "Prodi tiap semester harus isi risk register
     * tapi ditugaskan oleh SPMI".
     */
    public function up(): void
    {
        if (Schema::hasTable('risk_register_assignments')) {
            return;
        }

        Schema::create('risk_register_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 10);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->string('owner_key', 30)->comment('prodi-{id} atau unit-{id}');
            $table->foreignId('academic_program_id')->nullable()->constrained('academic_programs')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->text('note')->nullable()->comment('Catatan dari SPMI');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['academic_year', 'semester', 'owner_key'], 'rr_assign_period_owner_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_register_assignments');
    }
};
