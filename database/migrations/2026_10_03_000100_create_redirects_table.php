<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Legacy and changed addresses (SEO-011/012/014/018/020, INFRA-033, docs/platform/LEGACY-URL-MIGRATION.md): one row
 * per old path → its one-hop target (301/302) or 410 (gone). A row works only when the Owner switches it on (state
 * active); seeded rows from the approved migration map start as drafts. Only an address no page answers is ever
 * redirected, so a row can never hide a live page. Hits are counted so the Owner sees which old links still bring
 * visitors.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path', 500)->unique();
            $table->string('target', 500)->nullable();
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('state', 16)->default('draft')->index();
            $table->string('origin', 16)->default('owner');
            $table->string('decision_ref', 40)->nullable();
            $table->string('note', 500)->nullable();
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
