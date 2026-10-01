<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Scheduler health (M35 §49, MONITORING.md): every scheduled job records each run. */
    public function up(): void
    {
        Schema::create('scheduled_job_runs', function (Blueprint $table) {
            $table->id();
            $table->string('job', 80)->index();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 12);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('summary', 500)->nullable();
            $table->string('error', 500)->nullable();
            $table->index(['job', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_job_runs');
    }
};
