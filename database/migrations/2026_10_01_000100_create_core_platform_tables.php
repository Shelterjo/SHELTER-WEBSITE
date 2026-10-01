<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform core shared by every module (PLATFORM-ARCHITECTURE §3.1): one audit log, one version store,
     * one settings store, one feature-flag store, one signals queue, one reference-number service.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80)->index();
            $table->nullableMorphs('subject');
            $table->json('changes')->nullable();
            $table->json('channels_affected')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('content_versions', function (Blueprint $table) {
            $table->id();
            $table->morphs('versionable');
            $table->unsignedInteger('version');
            $table->string('status', 20);
            $table->json('snapshot');
            $table->string('reason', 500)->nullable();
            $table->json('guard_result')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['versionable_type', 'versionable_id', 'version']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('group', 40)->index();
            $table->json('value')->nullable();
            $table->string('classification', 20)->default('INTERNAL');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->boolean('enabled')->default(false);
            $table->string('description', 500)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10);
            $table->string('category', 30)->index();
            $table->string('severity', 10);
            $table->string('priority', 20);
            $table->string('status', 12)->default('OPEN')->index();
            $table->string('dedupe_key', 191)->nullable()->index();
            $table->string('title_ar', 300);
            $table->string('title_en', 300);
            $table->text('body_ar')->nullable();
            $table->text('body_en')->nullable();
            $table->string('recommended_action', 500)->nullable();
            $table->string('source', 80);
            $table->nullableMorphs('subject');
            $table->json('details')->nullable();
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reference_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['prefix', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_sequences');
        Schema::dropIfExists('signals');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('content_versions');
        Schema::dropIfExists('audit_logs');
    }
};
