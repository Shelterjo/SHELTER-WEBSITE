<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Dashboard page editor (M50): a section removed by the Owner is archived, never hard-deleted. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('sort');
        });
    }

    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table): void {
            $table->dropColumn('archived_at');
        });
    }
};
