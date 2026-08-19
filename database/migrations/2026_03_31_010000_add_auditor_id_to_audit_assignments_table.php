<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            // Tambah foreign key ke users agar bisa filter per auditor yang login
            $table->foreignId('auditor_id')
                  ->nullable()
                  ->after('auditor_type')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropForeign(['auditor_id']);
            $table->dropColumn('auditor_id');
        });
    }
};
