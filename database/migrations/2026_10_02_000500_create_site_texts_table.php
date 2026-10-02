<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Owner's own wording for the site's fixed texts (M50 — no code): one row per text and language, on top of the
     * language files. Only the texts listed in App\Services\Content\SiteTexts can be changed; an empty value means "the
     * original wording". Rows are never deleted — every change is versioned and audited.
     */
    public function up(): void
    {
        Schema::create('site_texts', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120);
            $table->string('locale', 2);
            $table->text('value')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_texts');
    }
};
