<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shaltoor's question log (M69 §21–§22, §33): only what improves the answers — the question with digits, e-mail
 * addresses and links removed, its language and topic, whether it was answered, the page it was asked from, and a
 * random conversation id made by the browser (not tied to a person: no IP, no cookie, no account). Kept 90 days.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shaltoor_questions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('conversation')->nullable()->index();
            $table->string('locale', 2);
            $table->string('topic', 30)->index();
            $table->boolean('answered')->index();
            $table->string('question', 300);
            $table->string('normalized', 300)->index();
            $table->string('page', 200)->nullable();
            $table->boolean('used_ai')->default(false);
            $table->timestamp('handled_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shaltoor_questions');
    }
};
