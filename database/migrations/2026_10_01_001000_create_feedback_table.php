<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Voice of Customer (docs/platform/VOICE-OF-CUSTOMER.md, OPS-030/031, M32 §15): one row per "how was your visit?"
 * answer for a branch — WITHOUT personal data: no name, phone, email, IP, cookie or identifier. The comment is free
 * text (the Owner can redact personal details a customer typed; retention PENDING LEGAL REVIEW — PO-019, no automatic
 * delete). `inquiry_id` links a comment turned into an INQ follow-up (joins when inquiries are built).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->unsignedTinyInteger('rating_overall'); // 1–5, required
            $table->unsignedTinyInteger('rating_coffee')->nullable();
            $table->unsignedTinyInteger('rating_service')->nullable();
            $table->unsignedTinyInteger('rating_cleanliness')->nullable();
            $table->unsignedTinyInteger('rating_speed')->nullable();
            $table->text('comment')->nullable(); // ≤ 1000 characters
            $table->string('locale', 5);
            $table->string('entry_point', 20)->default('direct'); // direct · branch_link (the branch came preset in the link)
            $table->json('topics')->nullable(); // [{tag, source: rule|ai}] — tagging joins with the dashboard
            $table->unsignedBigInteger('inquiry_id')->nullable()->index();
            $table->uuid('idempotency_key')->unique();
            $table->string('form_version', 40);
            $table->timestamp('submitted_at')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
