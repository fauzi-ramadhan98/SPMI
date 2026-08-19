<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Integrasi daftar tilik dengan IKU/IKT standar mutu + rubrik penilaian + bukti.
     * indicator_key   : referensi IKU/IKT yang dipilih (mis. "IKU 1", "IKT 2").
     * audit_question  : panduan auditor (Pertanyaan Audit).
     * rubric_4..1     : kriteria skor 4, 3, 2, 1.
     * evidence_document: Target Dokumen Bukti yang diharapkan.
     */
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->string('indicator_key')->nullable()->after('code');
            $table->text('audit_question')->nullable()->after('indicator');
            $table->text('rubric_4')->nullable()->after('audit_question');
            $table->text('rubric_3')->nullable()->after('rubric_4');
            $table->text('rubric_2')->nullable()->after('rubric_3');
            $table->text('rubric_1')->nullable()->after('rubric_2');
            $table->string('evidence_document')->nullable()->after('rubric_1');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropColumn(['indicator_key', 'audit_question', 'rubric_4', 'rubric_3', 'rubric_2', 'rubric_1', 'evidence_document']);
        });
    }
};
