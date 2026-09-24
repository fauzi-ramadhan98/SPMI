<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('standard_decrees', function (Blueprint $table) {
            // Add 'ditolak' to the status enum
            $table->enum('status', ['draft', 'menunggu_persetujuan', 'ditetapkan', 'ditolak'])->default('menunggu_persetujuan')->change();
            // Add reject_reason column (nullable — only filled when status = ditolak)
            $table->text('reject_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standard_decrees', function (Blueprint $table) {
            $table->enum('status', ['draft', 'menunggu_persetujuan', 'ditetapkan'])->default('menunggu_persetujuan')->change();
            $table->dropColumn('reject_reason');
        });
    }
};
