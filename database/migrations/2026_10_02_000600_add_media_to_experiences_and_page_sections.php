<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An approved image for an event (DX-011 Media) and for a content page section (M50). Only images that MediaRights
     * allows on the website can be chosen, and the site draws them only while that stays true (MEDIA-RIGHTS).
     */
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
        });
        Schema::table('page_sections', function (Blueprint $table) {
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_id');
        });
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_id');
        });
    }
};
