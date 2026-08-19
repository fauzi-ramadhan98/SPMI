<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul Rapat Tinjauan Manajemen (RTM) — P3 Pengendalian.
     *
     * rtm_meetings      : agenda/jadwal rapat (SPMI = operator).
     * rtm_meeting_user  : peserta yang diundang (SPMI) / hadir.
     * rtm_instructions  : instruksi tindak lanjut per auditee (prodi/unit),
     *                     disahkan pimpinan setelah notulensi final.
     *
     * Alur status: dijadwalkan -> berlangsung -> notulensi (menunggu sah) -> disahkan.
     */
    public function up(): void
    {
        Schema::create('rtm_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_cycle_id')->constrained('audit_cycles')->cascadeOnDelete();
            $table->string('title');
            $table->date('meeting_date')->nullable();
            $table->time('meeting_time')->nullable();
            $table->string('location')->nullable();
            $table->text('agenda')->nullable();
            $table->longText('notulensi')->nullable();
            $table->string('status')->default('dijadwalkan');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rtm_meeting_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtm_meeting_id')->constrained('rtm_meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['rtm_meeting_id', 'user_id']);
            $table->timestamps();
        });

        Schema::create('rtm_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtm_meeting_id')->constrained('rtm_meetings')->cascadeOnDelete();
            $table->foreignId('academic_program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->text('instruction');
            $table->date('target_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtm_instructions');
        Schema::dropIfExists('rtm_meeting_user');
        Schema::dropIfExists('rtm_meetings');
    }
};