<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tautkan instrumen borang ke butir daftar tilik + salinan panduan/rubrik/bukti
     * agar auditor menilai berdasarkan rubrik yang sudah ditetapkan SPMI.
     */
    public function up(): void
    {
        Schema::table('audit_instruments', function (Blueprint $table) {
            $table->foreignId('checklist_item_id')->nullable()->after('audit_assignment_id')
                ->constrained('checklist_items')->nullOnDelete();
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
        Schema::table('audit_instruments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checklist_item_id');
            $table->dropColumn(['audit_question', 'rubric_4', 'rubric_3', 'rubric_2', 'rubric_1', 'evidence_document']);
        });
    }
};
