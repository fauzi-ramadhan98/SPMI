<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_findings', function (Blueprint $table) {
            $table->string('prodi_decision')->nullable()->after('status');
            $table->text('prodi_decision_note')->nullable()->after('prodi_decision');
            $table->timestamp('prodi_decision_at')->nullable()->after('prodi_decision_note');
        });
    }

    public function down(): void
    {
        Schema::table('audit_findings', function (Blueprint $table) {
            $table->dropColumn(['prodi_decision', 'prodi_decision_note', 'prodi_decision_at']);
        });
    }
};