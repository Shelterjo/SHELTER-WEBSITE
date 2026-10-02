<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu item fields the Owner edits in the dashboard (Menu IA §6, §19 — M50): the main image (an approved library image),
 * when the "New" badge ends (F-14 `new_until`) and an optional spoken name for ALL CAPS names (§6 (4), D-131).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('media_id')->nullable()->after('description_en')->constrained('media')->nullOnDelete();
            $table->date('new_until')->nullable()->after('is_new');
            $table->string('aria_label_en', 120)->nullable()->after('display_name_en');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('media_id');
            $table->dropColumn(['new_until', 'aria_label_en']);
        });
    }
};
