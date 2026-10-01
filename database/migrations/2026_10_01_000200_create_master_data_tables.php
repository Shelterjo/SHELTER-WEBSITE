<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Data Hub (MASTER-DATA-HUB.md, PLATFORM-ARCHITECTURE §3.2): Market → Country → City → Branch,
     * hours with exceptions, contact points, social links, the fact registry and external references.
     * Every business value is nullable: a value without owner approval is NULL + a PENDING fact (M38).
     */
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('default_locale', 5);
            $table->json('locales');
            $table->string('currency', 3);
            $table->string('timezone', 64);
            $table->string('phone_country_code', 6);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->restrictOnDelete();
            $table->string('iso2', 2)->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('slug', 60);
            $table->string('name_ar');
            $table->string('name_en');
            $table->timestamps();
            $table->unique(['country_id', 'slug']);
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->string('slug', 60);
            $table->string('type', 30);
            $table->string('status', 30)->default('active');
            $table->boolean('is_public')->default(false);
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('address_ar', 500)->nullable();
            $table->string('address_en', 500)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('maps_url', 500)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['city_id', 'slug']);
        });

        Schema::create('branch_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();
            $table->index(['branch_id', 'weekday']);
        });

        Schema::create('hours_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_closed')->default(false);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->string('reason_ar', 300)->nullable();
            $table->string('reason_en', 300)->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['branch_id', 'starts_on', 'ends_on']);
        });

        Schema::create('branch_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('group', 20);
            $table->string('key', 40);
            $table->boolean('value')->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'group', 'key']);
        });

        Schema::create('contact_points', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 10);
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('kind', 40);
            $table->string('value', 191)->nullable();
            $table->string('label_ar')->nullable();
            $table->string('label_en')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('show_on_branch_cards')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['scope', 'kind']);
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30);
            $table->string('handle', 120)->nullable();
            $table->string('url', 500)->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('facts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();
            $table->string('key', 191)->index();
            $table->string('category', 20)->index();
            $table->foreignId('market_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label_ar')->nullable();
            $table->string('label_en')->nullable();
            $table->json('value')->nullable();
            $table->string('value_hash', 64)->nullable();
            $table->string('status', 30)->index();
            $table->string('source_type', 30);
            $table->string('source_ref', 191)->nullable();
            $table->string('decision_ref', 191)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('evidence')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('supersedes_id')->nullable()->constrained('facts')->nullOnDelete();
            $table->json('blocked_phrases')->nullable();
            $table->string('classification', 20)->default('PUBLIC');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('external_references', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->string('system', 30);
            $table->string('external_id', 191);
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id', 'system']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_references');
        Schema::dropIfExists('facts');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('contact_points');
        Schema::dropIfExists('branch_attributes');
        Schema::dropIfExists('hours_exceptions');
        Schema::dropIfExists('branch_hours');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('markets');
    }
};
