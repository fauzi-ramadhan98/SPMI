<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['kepuasan_layanan', 'visi_misi', 'pembelajaran', 'alumni', 'pengguna_lulusan', 'lainnya'])->default('kepuasan_layanan');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_anonymous')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('question_type', ['rating', 'text', 'multiple_choice', 'checkbox'])->default('rating');
            $table->json('options')->nullable(); // For multiple choice / checkbox
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_email')->nullable();
            $table->string('respondent_type')->nullable(); // mahasiswa, alumni, dosen, mitra
            $table->text('answer');
            $table->string('session_token', 64)->nullable(); // Grouping responses from same respondent
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
