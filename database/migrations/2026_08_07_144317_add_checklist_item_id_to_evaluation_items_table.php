<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->unsignedBigInteger('checklist_item_id')->nullable()->after('quality_standard_id');
            $table->index('checklist_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->dropIndex(['checklist_item_id']);
            $table->dropColumn('checklist_item_id');
        });
    }
};