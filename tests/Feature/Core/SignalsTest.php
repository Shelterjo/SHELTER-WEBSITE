<?php

namespace Tests\Feature\Core;

use App\Enums\Priority;
use App\Enums\Severity;
use App\Enums\SignalCategory;
use App\Enums\SignalKind;
use App\Enums\SignalStatus;
use App\Models\Signal;
use App\Models\User;
use App\Services\Core\Signals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalsTest extends TestCase
{
    use RefreshDatabase;

    private function raise(Severity $severity = Severity::Low, Priority $priority = Priority::Information, ?string $key = 'test:key'): Signal
    {
        return app(Signals::class)->raise(SignalKind::Issue, SignalCategory::Content, $severity, $priority, 'test', 'عنوان', 'Title', dedupeKey: $key);
    }

    public function test_same_dedupe_key_updates_the_open_signal_instead_of_duplicating(): void
    {
        $this->raise();
        $second = $this->raise();

        $this->assertSame(1, Signal::query()->count());
        $this->assertSame(2, $second->occurrences);
    }

    public function test_severity_escalates_but_never_downgrades(): void
    {
        $this->raise(Severity::High, Priority::ActionRequired);
        $after = $this->raise(Severity::Low, Priority::Information);

        $this->assertSame(Severity::High, $after->severity);
        $this->assertSame(Priority::ActionRequired, $after->priority);
    }

    public function test_resolve_closes_and_a_new_occurrence_opens_a_fresh_signal(): void
    {
        $this->raise();
        $this->assertSame(1, app(Signals::class)->resolve('test:key'));
        $this->raise();

        $this->assertSame(1, Signal::query()->where('status', SignalStatus::Resolved->value)->count());
        $this->assertSame(1, Signal::query()->open()->count());
    }

    public function test_only_information_signals_can_be_dismissed(): void
    {
        $owner = User::factory()->create();
        $info = $this->raise(key: 'a');
        $critical = $this->raise(Severity::Critical, Priority::Critical, key: 'b');

        $this->assertTrue(app(Signals::class)->dismiss($info, $owner));
        $this->assertFalse(app(Signals::class)->dismiss($critical, $owner));
        $this->assertSame(SignalStatus::Open, $critical->fresh()?->status);
    }
}
