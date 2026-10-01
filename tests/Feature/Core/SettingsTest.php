<?php

namespace Tests\Feature\Core;

use App\Models\AuditLog;
use App\Models\ContentVersion;
use App\Models\User;
use App\Services\Core\FeatureFlags;
use App\Services\Core\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_versions_audits_and_refreshes_the_cache(): void
    {
        $owner = User::factory()->create();
        $settings = app(Settings::class);
        $this->assertNull($settings->get('website.example'));

        $settings->set('website.example', ['ar' => 'قيمة'], $owner, 'test');

        $this->assertSame(['ar' => 'قيمة'], $settings->get('website.example'));
        $this->assertSame(1, ContentVersion::query()->count());
        $this->assertSame('settings.updated', AuditLog::query()->value('action'));
    }

    public function test_unknown_flags_are_off_and_changes_are_audited(): void
    {
        $owner = User::factory()->create();
        $flags = app(FeatureFlags::class);
        $this->assertFalse($flags->enabled(FeatureFlags::SAFE_MODE));

        $flags->set(FeatureFlags::SAFE_MODE, true, $owner, 'incident drill');

        $this->assertTrue($flags->enabled(FeatureFlags::SAFE_MODE));
        $this->assertSame(['reason' => 'incident drill'], AuditLog::query()->where('action', 'flags.updated')->value('meta'));
    }
}
