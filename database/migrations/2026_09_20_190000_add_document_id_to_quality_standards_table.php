<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quality_standards', 'document_id')) {
            Schema::table('quality_standards', function (Blueprint $table) {
                $table->foreignId('document_id')->nullable()->after('standard_decree_id')->constrained('documents')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quality_standards', 'document_id')) {
            Schema::table('quality_standards', function (Blueprint $table) {
                $table->dropConstrainedForeignId('document_id');
            });
        }
    }
};
