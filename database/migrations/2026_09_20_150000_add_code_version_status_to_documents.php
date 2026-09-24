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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id'); // Kode Dokumen, optional but recommended
            $table->integer('version')->default(0)->after('code');
            $table->enum('status', ['draft', 'aktif'])->default('draft')->after('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['code', 'version', 'status']);
        });
    }
};
