<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Brand content pages (SI-B03 About · SI-B05 FAQ · SI-B06 Privacy · SI-B07 Terms — CONTENT-SOURCE-OF-TRUTH):
     * - a page is one fixed key with a fixed URL (/ar/about/ …), shown only while `published` with both languages
     *   (G-02/G-03, LANGUAGE-PARITY); anything else is a 404, so no empty or invented page is ever public;
     * - sections come from approved section types only (Design lock): `text` (heading + paragraphs) and `faq`
     *   (question + answer). Text is plain (no HTML), rendered escaped;
     * - `origin` = owner | ai | import: AI text stays unpublishable until the owner confirms it (G-20, OPS-053);
     * - publishing writes content_versions (PHASE 3 dashboard); archive instead of delete.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('type', 20);
            $table->string('title_ar')->nullable(); // the H1 (a line break is kept as the Owner wrote it)
            $table->string('title_en')->nullable();
            $table->string('name_ar', 120)->nullable(); // page name: <title>, breadcrumb, footer, search (null = the title)
            $table->string('name_en', 120)->nullable();
            $table->string('description_ar', 300)->nullable();
            $table->string('description_en', 300)->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('origin', 10)->default('owner');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('content_updated_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('heading_ar')->nullable();
            $table->string('heading_en')->nullable();
            $table->text('body_ar')->nullable();
            $table->text('body_en')->nullable();
            $table->string('origin', 10)->default('owner');
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['page_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('pages');
    }
};
