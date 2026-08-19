<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->string('criteria'); // Standar/kriteria (misal: Std 1 - Visi Misi)
            $table->text('indicator');  // Indikator penilaian
            $table->enum('score', ['4', '3', '2', '1', '0'])->nullable(); // Skor
            $table->text('finding')->nullable(); // Temuan
            $table->text('recommendation')->nullable(); // Rekomendasi
            $table->enum('finding_category', ['KTS', 'OB', 'Sesuai'])->nullable(); // Kategori temuan
            $table->timestamps();
        });

        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')->constrained('audit_assignments')->cascadeOnDelete();
            $table->enum('type', ['KTS', 'OB']); // Ketidaksesuaian / Observasi
            $table->string('criteria');
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->date('target_date')->nullable();
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
        Schema::dropIfExists('audit_instruments');
    }
};
