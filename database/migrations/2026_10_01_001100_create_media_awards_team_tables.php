<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One media library with its rights (MEDIA-RIGHTS §1–§2, PLATFORM-ARCHITECTURE §3.3), the awards that point to it
 * (verified through the Fact Registry — PO-032) and SHELTER Family's public profiles (DYNAMIC-EXPERIENCE-ENGINE §2):
 * a table of its own, published only with the employee's recorded consent and never linked to any HR data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // MED-00001
            $table->string('kind', 10)->default('image');
            $table->string('original_path'); // private `media` disk — never served directly
            $table->string('mime', 60);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('bytes');
            $table->char('sha256', 64)->unique(); // the same file is never stored twice (MR-T08)
            $table->string('alt_ar', 300)->nullable();
            $table->string('alt_en', 300)->nullable();
            $table->unsignedTinyInteger('focal_x')->default(50); // focus point, % of width
            $table->unsignedTinyInteger('focal_y')->default(50);
            // Rights (M32 §18).
            $table->string('photographer', 150)->nullable();
            $table->string('source', 20); // shelter · contracted · partner · other · stock · ai · google · legacy_site
            $table->boolean('source_explicitly_approved')->default(false); // stock/ai/google/legacy only with this (MEDIA-005)
            $table->string('rights_holder', 150)->nullable();
            $table->string('license', 20)->default('full'); // full · website_only · time_limited · other
            $table->string('license_note', 300)->nullable();
            $table->string('approval_status', 30)->default('PENDING OWNER APPROVAL'); // APPROVED · REJECTED · ARCHIVED
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_ref', 40)->nullable(); // the Owner decision that approved it
            $table->string('people_consent', 20)->default('not_recorded'); // none · recorded · not_recorded
            $table->json('people_consents')->nullable(); // [{person, consented_at, scopes: [website, ads, social], document_ref, withdrawn_at}]
            $table->boolean('ok_website')->default(false);
            $table->boolean('ok_ads')->default(false);
            $table->string('restrictions', 500)->nullable();
            $table->timestamp('rights_expires_at')->nullable();
            $table->json('variants')->nullable(); // generated web copies — approved assets only (MEDIA-011)
            $table->timestamp('variants_generated_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        // Where each asset is used (the usage graph): a page block, an award, a team profile, the press kit…
        Schema::create('media_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete(); // a used asset is never hard-deleted
            $table->morphs('usable');
            $table->string('slot', 40)->default('main'); // main · press_kit · gallery …
            $table->string('channel', 10)->default('website'); // website · ads
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['media_id', 'usable_type', 'usable_id', 'slot']);
        });

        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar', 200)->nullable();
            $table->string('title_en', 200)->nullable();
            $table->string('issuer_ar', 200)->nullable();
            $table->string('issuer_en', 200)->nullable();
            $table->unsignedSmallInteger('year');
            $table->string('description_ar', 600)->nullable();
            $table->string('description_en', 600)->nullable();
            $table->string('evidence_url', 500)->nullable(); // public link that proves it (PO-032)
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 20)->default('draft'); // draft · published · archived
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('display_name_ar', 120)->nullable();
            $table->string('display_name_en', 120)->nullable();
            $table->foreignId('photo_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('job_title_ar', 120)->nullable();
            $table->string('job_title_en', 120)->nullable();
            $table->string('department', 60)->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('join_date')->nullable();
            $table->boolean('show_join_date')->default(false);
            $table->string('bio_ar', 600)->nullable();
            $table->string('bio_en', 600)->nullable();
            $table->boolean('show_bio')->default(false);
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('publish_consent_at')->nullable(); // the employee's recorded consent (G13-TF-01)
            $table->string('publish_consent_version', 40)->nullable();
            $table->timestamp('consent_withdrawn_at')->nullable(); // withdrawal unpublishes at once (OPS-034)
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('awards');
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media');
    }
};
