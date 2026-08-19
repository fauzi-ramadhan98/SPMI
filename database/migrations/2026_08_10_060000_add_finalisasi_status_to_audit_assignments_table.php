<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak mendukung ALTER COLUMN enum — ubah ke string agar nilai
        // 'finalisasi' dapat diterima (pola sama seperti audit_findings.status).
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};