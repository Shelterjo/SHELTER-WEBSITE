<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Careers & recruitment (docs/RECRUITMENT-DATA-MODEL.md — names and columns are binding). Owned by SHELTER, kept on
     * the site's own database (M28 §01): no external CRM, no Falcon. Choice columns are strings validated by the
     * application against the approved lists (portable "VARCHAR + CHECK" form of the spec's ENUMs).
     * - Identity numbers live only in application_identity_secure, AES-256-GCM encrypted + HMAC blind index (§2.2).
     * - Attachments: private storage, random keys; drafts belong to an upload session until submitted (§2.3, §2.4).
     * - Nothing is deleted automatically; permanent delete is the Owner's, from the archive, with a tombstone (§7).
     */
    public function up(): void
    {
        Schema::create('jordan_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 80);
            $table->string('name_en', 80)->nullable();
            $table->string('governorate_ar', 80)->nullable();
            $table->string('governorate_en', 80)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('source', 200)->nullable();
            $table->string('verification_status', 40)->default('PENDING DATA VERIFICATION');
            $table->timestamps();
        });

        Schema::create('consent_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 40)->unique();
            $table->text('text_ar');
            $table->timestamp('active_from');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 20)->unique();
            $table->string('full_name', 150);
            $table->string('phone_raw', 40);
            $table->string('phone_normalized', 20)->index();
            $table->string('email', 254);
            $table->string('email_normalized', 254)->index();
            $table->string('gender', 10)->index();
            $table->date('birth_date');
            $table->string('marital_status', 10);
            $table->string('nationality_type', 20)->index();
            $table->string('nationality_text', 80)->nullable();
            $table->foreignId('city_id')->constrained('jordan_cities')->restrictOnDelete();
            $table->string('area_text', 120);
            $table->string('job_title_text', 150)->index();
            $table->string('job_tag', 80)->nullable();
            $table->string('education_level', 20)->index();
            $table->string('experience_band', 10)->index();
            $table->boolean('same_field_experience');
            $table->boolean('currently_employed');
            $table->decimal('expected_salary_jod', 9, 2)->index();
            $table->boolean('has_driving_license');
            $table->text('notes_text');
            $table->string('status', 30)->default('received');
            $table->string('status_before_archive', 30)->nullable();
            $table->timestamp('first_viewed_at')->nullable()->index();
            $table->foreignId('first_viewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('primary_attachment_id')->nullable();
            $table->unsignedInteger('applicant_group_size')->default(1);
            $table->timestamp('submitted_at')->index();
            $table->timestamp('archived_at')->nullable();
            $table->uuid('idempotency_key')->unique();
            $table->string('form_version', 20);
            $table->timestamps();
            $table->index(['status', 'submitted_at']);
            $table->index('updated_at');
        });

        Schema::create('application_identity_secure', function (Blueprint $table) {
            $table->foreignId('application_id')->primary()->constrained('job_applications')->cascadeOnDelete();
            $table->string('id_type', 30);
            $table->binary('id_ciphertext');
            $table->binary('id_nonce');
            $table->unsignedSmallInteger('id_key_version');
            $table->char('id_last4', 4);
            $table->char('id_blind_index', 64)->index();
            $table->timestamps();
        });

        Schema::create('application_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('job_applications')->cascadeOnDelete();
            $table->foreignUuid('upload_session_id')->nullable()->constrained('upload_sessions')->nullOnDelete();
            $table->char('storage_key', 32)->unique();
            $table->string('storage_path', 255);
            $table->string('original_filename', 255);
            $table->string('extension', 16);
            $table->string('declared_mime', 127)->nullable();
            $table->string('detected_mime', 127);
            $table->string('file_family', 20);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64)->index();
            $table->smallInteger('cv_score')->default(0);
            $table->string('cv_detection', 30)->default('pending');
            $table->string('scan_status', 30)->default('validated');
            $table->timestamps();
        });

        Schema::create('application_consents', function (Blueprint $table) {
            $table->foreignId('application_id')->primary()->constrained('job_applications')->cascadeOnDelete();
            $table->foreignId('consent_version_id')->constrained('consent_versions')->restrictOnDelete();
            $table->boolean('accepted');
            $table->timestamp('accepted_at');
            $table->timestamps();
        });

        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->text('internal_note')->nullable();
            $table->uuid('bulk_operation_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('application_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->text('body');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('interview_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 120);
            $table->string('name_en', 120);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('application_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->date('interview_date');
            $table->time('interview_time');
            $table->foreignId('location_id')->constrained('interview_locations')->restrictOnDelete();
            $table->text('internal_notes')->nullable();
            $table->boolean('is_current')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('application_links', function (Blueprint $table) {
            $table->foreignId('application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->foreignId('linked_application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->string('signal', 20);
            $table->timestamp('created_at')->nullable();
            $table->primary(['application_id', 'linked_application_id', 'signal']);
        });

        Schema::create('recruitment_saved_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120);
            $table->json('filter_json');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('key', 80);
            $table->json('value_json');
            $table->timestamps();
            $table->unique(['user_id', 'key']);
        });

        Schema::create('recruitment_settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->json('value_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['recruitment_settings', 'user_preferences', 'recruitment_saved_filters', 'application_links', 'application_interviews',
            'interview_locations', 'application_notes', 'application_status_history', 'application_consents',
            'application_attachments', 'application_identity_secure', 'job_applications', 'upload_sessions', 'consent_versions',
            'jordan_cities'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
