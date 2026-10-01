<?php

namespace Tests\Feature\Core;

use App\Models\ScheduledJobRun;
use App\Models\Signal;
use App\Services\Core\JobRuns;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use RuntimeException;
use Tests\TestCase;

class JobRunsTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_is_recorded(): void
    {
        $run = app(JobRuns::class)->run('demo', fn (): string => '3 done');

        $this->assertSame('success', $run->status);
        $this->assertSame('3 done', $run->summary);
        $this->assertNotNull($run->finished_at);
    }

    public function test_failure_is_recorded_and_raises_one_signal_resolved_by_the_next_success(): void
    {
        $jobs = app(JobRuns::class);
        $jobs->run('demo', fn () => throw new RuntimeException('boom'));
        $jobs->run('demo', fn () => throw new RuntimeException('boom'));

        $this->assertSame(2, ScheduledJobRun::query()->where('status', 'failed')->count());
        $this->assertSame(1, Signal::query()->open()->where('dedupe_key', 'job:demo:failed')->count());

        $jobs->run('demo', fn (): ?string => null);
        $this->assertSame(0, Signal::query()->open()->count());
    }

    public function test_the_fact_expiry_job_is_scheduled_in_market_time(): void
    {
        $command = $this->artisan('schedule:list');
        $this->assertInstanceOf(PendingCommand::class, $command);
        $command->expectsOutputToContain('facts:expire-verified')->assertSuccessful();
    }
}
