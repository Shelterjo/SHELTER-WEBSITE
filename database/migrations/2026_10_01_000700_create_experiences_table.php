<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dynamic experiences (docs/DYNAMIC-EXPERIENCE-ENGINE.md §2, PLATFORM-ARCHITECTURE §3.3): ONE table for every type —
     * campaign · seasonal_theme · event · announcement · recognition. Times are UTC, shown in `timezone` (IANA, default
     * Asia/Amman). Type-specific fields live in `details` (validated per type: event kind, surfaces, venue…; theme
     * tokens and bundles; recognition → team member). Versions go to content_versions, audit to audit_logs. The
     * placement engine and the Dashboard module are PHASE 3; PHASE 2 reads published events for /{locale}/{market}/events/.
     */
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->foreignId('market_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 80)->nullable();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('body_ar')->nullable();
            $table->text('body_en')->nullable();
            $table->string('cta_label_ar', 60)->nullable();
            $table->string('cta_label_en', 60)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->string('presentation', 30)->nullable();
            $table->json('placements')->nullable();
            $table->smallInteger('priority')->default(0);
            $table->json('branch_ids')->nullable();
            $table->json('media_ids')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('timezone', 64)->default('Asia/Amman');
            $table->string('status', 20)->default('draft');
            $table->string('manual_state', 3)->nullable();
            $table->boolean('emergency_disabled')->default(false);
            $table->boolean('countdown_enabled')->default(false);
            $table->boolean('motion_enabled')->default(true);
            $table->text('terms_ar')->nullable();
            $table->text('terms_en')->nullable();
            $table->json('details')->nullable();
            $table->foreignId('related_experience_id')->nullable()->constrained('experiences')->nullOnDelete();
            $table->string('origin', 10)->default('owner');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['market_id', 'slug']);
            $table->index(['type', 'status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
