<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Core\FeatureFlags;
use App\Services\Franchise\FranchiseForm;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Settings (M50): forms closed by the Owner, Safe Mode, the founding year — audited, only changes written. */
class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    public function test_the_owner_closes_a_form_and_switches_safe_mode(): void
    {
        $this->get('/dashboard/settings')->assertOk()->assertSee('الإعدادات')->assertSee('طلبات الشراكة والفرنشايز');
        $this->assertTrue(app(FranchiseForm::class)->isOpen());

        $this->put('/dashboard/settings', ['form_partnership' => 'closed', 'safe_mode' => 'on'])->assertSessionHas('status', 'تغيّر إعدادان.');
        $this->assertFalse(app(FranchiseForm::class)->isOpen(), 'closed by the Owner');
        $this->assertTrue(app(FeatureFlags::class)->enabled(FeatureFlags::SAFE_MODE));
        $this->get('/dashboard/live')->assertOk()->assertSee('الوضع الآمن مفعّل');
        $this->put('/dashboard/settings', ['form_partnership' => 'closed', 'safe_mode' => 'on'])->assertSessionHas('status', 'لم يتغير شيء.');

        $this->put('/dashboard/settings', ['form_partnership' => 'open', 'safe_mode' => 'off']);
        $this->assertTrue(app(FranchiseForm::class)->isOpen());
        $this->assertSame(4, AuditLog::query()->where('action', 'flags.updated')->count());
    }

    public function test_the_founding_year_is_checked_and_approved(): void
    {
        $this->put('/dashboard/settings', ['founded_year' => '20x8'])->assertSessionHasErrors(['founded_year'], null, 'settings');
        $this->put('/dashboard/settings', ['founded_year' => '2031'])->assertSessionHasErrors(['founded_year'], null, 'settings');
        $this->put('/dashboard/settings', ['founded_year' => '2019'])->assertSessionHasNoErrors();
        $this->get('/dashboard/settings')->assertSee('value="2019"', false);
    }
}
