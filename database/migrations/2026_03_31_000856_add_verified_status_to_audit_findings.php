<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not support ALTER COLUMN for enums.
        // We recreate the column by adding a new text column, copying data,
        // dropping the old, and renaming — but SQLite also doesn't support DROP COLUMN easily.
        // The cleanest approach for SQLite: just change the column to a string type
        // so any status value (open, in_progress, closed, verified) is accepted.
        Schema::table('audit_findings', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }

    public function down(): void
    {
        Schema::table('audit_findings', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }
};
