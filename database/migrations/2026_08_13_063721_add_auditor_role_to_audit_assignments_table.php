<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->string('auditor_role')->nullable()->after('auditor_type');
        });

        // Data lama tanpa peran → default Anggota
        \Illuminate\Support\Facades\DB::table('audit_assignments')->whereNull('auditor_role')->update(['auditor_role' => 'Anggota']);
    }

    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropColumn('auditor_role');
        });
    }
};
