<?php

namespace Tests\Feature\Core;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_keys_are_masked_recursively(): void
    {
        $log = app(AuditLogger::class)->record('settings.updated', changes: [
            'after' => ['password' => 'secret', 'nested' => ['two_factor_secret' => 'abc', 'name' => 'Visible']],
        ]);

        $this->assertSame('[MASKED]', $log->changes['after']['password']);
        $this->assertSame('[MASKED]', $log->changes['after']['nested']['two_factor_secret']);
        $this->assertSame('Visible', $log->changes['after']['nested']['name']);
    }

    public function test_ip_is_stored_only_for_security_events(): void
    {
        $audit = app(AuditLogger::class);
        $audit->record('auth.login');
        $audit->record('facts.approved');

        $this->assertNotNull(AuditLog::query()->where('action', 'auth.login')->value('ip_address'));
        $this->assertNull(AuditLog::query()->where('action', 'facts.approved')->value('ip_address'));
    }

    public function test_actor_defaults_to_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->assertSame($owner->id, app(AuditLogger::class)->record('flags.updated')->user_id);
    }
}
