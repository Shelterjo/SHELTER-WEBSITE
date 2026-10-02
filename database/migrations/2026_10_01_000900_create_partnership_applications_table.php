<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Partnership (franchise) applications — the FR detail table of the Applications Core (PLATFORM-ARCHITECTURE §3.4,
     * docs/franchise/03-APPLICATION-FIELD-MATRIX.md fields 1–11). No investment data (field 14 is PENDING OWNER
     * DECISION), no attachments yet (field 15 pending), no newsletter opt-in (§75). Attribution is privacy-safe:
     * UTM values, landing path, page language and the referrer's domain only — never an IP or a precise location.
     */
    public function up(): void
    {
        Schema::create('partnership_applications', function (Blueprint $table) {
            $table->foreignId('application_id')->primary()->constrained('applications')->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('phone_raw', 40);
            $table->string('phone_normalized', 20)->index();
            $table->string('email', 254);
            $table->string('email_normalized', 254)->index();
            $table->char('country_code', 2)->index();
            $table->string('city_text', 120);
            $table->string('market_interest', 200);
            $table->string('partnership_interest_type', 30); // stable value (PF-06): single_location · multi_location · …
            $table->string('partnership_interest_other', 300)->nullable(); // only when the type is `other`
            $table->string('experience_band', 10);
            $table->string('experience_text', 300)->nullable();
            $table->boolean('owns_business');
            $table->string('location_status', 20);
            $table->text('introduction');
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 150)->nullable();
            $table->string('landing_path', 255)->nullable();
            $table->string('referrer_domain', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_applications');
    }
};
