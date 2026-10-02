<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Partnership pipeline stages as settings, not code (TD-FR-01, docs/franchise/04 §2): the stages M29 §38 proposed —
 * the Franchise Master may replace them later (PO-048) without a code change. And partnership meetings (FRAN-059):
 * date and time, channel, place, internal notes, state — never shown to the applicant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->id();
            $table->string('module', 20); // FR (partnerships)
            $table->string('code', 30);
            $table->string('label_ar', 80);
            $table->string('label_en', 80);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['module', 'code']);
        });

        // The proposed stages, as written in docs/franchise/04 §2 (M29 §38). `archived` is always last.
        $stages = [
            ['received', 'جديد / تم الاستلام', 'Received'], ['qualified', 'مؤهل مبدئيًا', 'Pre-qualified'], ['meeting', 'اجتماع', 'Meeting'],
            ['market_review', 'مراجعة السوق', 'Market review'], ['site_review', 'مراجعة الموقع', 'Site review'], ['approved', 'موافق', 'Approved'],
            ['contract', 'مرحلة العقد', 'Contract stage'], ['closed', 'مغلق', 'Closed'], ['archived', 'مؤرشف', 'Archived'],
        ];
        foreach ($stages as $i => [$code, $ar, $en]) {
            DB::table('pipeline_stages')->insert(['module' => 'FR', 'code' => $code, 'label_ar' => $ar, 'label_en' => $en, 'sort' => $i + 1,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }

        Schema::create('application_meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->timestamp('meeting_at');
            $table->string('channel', 20); // in_person · video · phone
            $table->string('place', 150)->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('state', 20)->default('planned'); // planned · done · cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['application_id', 'meeting_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_meetings');
        Schema::dropIfExists('pipeline_stages');
    }
};
