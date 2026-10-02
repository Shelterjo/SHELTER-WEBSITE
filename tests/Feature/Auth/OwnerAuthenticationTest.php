<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Auth\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class OwnerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

    private const PASSWORD = 'correct horse battery staple';

    private function otp(string $secret = self::SECRET): string
    {
        return app(Google2FA::class)->getCurrentOtp($secret);
    }

    private function owner(): User
    {
        return User::factory()->withTwoFactor(self::SECRET)->create(['email' => 'owner@example.test']);
    }

    /** @return TestResponse<Response> */
    private function passwordStep(string $email = 'owner@example.test', string $password = self::PASSWORD): TestResponse
    {
        return $this->post('/dashboard/login', ['email' => $email, 'password' => $password]);
    }

    public function test_login_page_is_never_indexed_and_there_is_no_registration(): void
    {
        $this->get('/dashboard/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->get('/register')->assertNotFound();
        $this->get('/dashboard/register')->assertNotFound();
    }

    public function test_wrong_password_and_unknown_email_get_the_same_answer(): void
    {
        $this->owner();

        $this->passwordStep(password: 'wrong')->assertSessionHasErrors(['email' => __('dashboard.auth.failed')]);
        $this->passwordStep(email: 'nobody@example.test')->assertSessionHasErrors(['email' => __('dashboard.auth.failed')]);
        $this->assertGuest();
        $this->assertSame(2, AuditLog::query()->where('action', 'auth.failed')->count());
        $this->assertStringNotContainsString('nobody', (string) json_encode(AuditLog::query()->pluck('meta')));
    }

    public function test_password_step_is_rate_limited(): void
    {
        $this->owner();
        for ($i = 0; $i < 5; $i++) {
            $this->passwordStep(password: 'wrong');
        }

        $this->passwordStep()->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_alone_never_grants_access(): void
    {
        $this->owner();

        $this->passwordStep()->assertRedirect('/dashboard/two-factor');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/dashboard/login');
    }

    public function test_full_sign_in_with_totp_and_code_replay_is_refused(): void
    {
        $owner = $this->owner();
        $this->passwordStep();
        $code = $this->otp();

        $this->post('/dashboard/two-factor', ['code' => $code])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);
        $this->get('/dashboard')->assertOk()->assertSee(__('dashboard.command_center'));

        $this->post('/dashboard/logout');
        $this->passwordStep();
        $this->post('/dashboard/two-factor', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_the_code_step_waits_out_the_hour_after_a_run_of_failures(): void
    {
        // FINAL-QA QA-008: the per-minute limit resets every minute; a second, hourly window per account stops the run.
        $owner = $this->owner();
        $this->passwordStep();
        for ($i = 0; $i < 10; $i++) {
            if ($i > 0 && $i % 5 === 0) {
                $this->travel(61)->seconds(); // past the per-minute window, still inside the hour
                $this->passwordStep();
            }
            $this->post('/dashboard/two-factor', ['code' => '000000'])->assertSessionHasErrors('code');
        }
        $this->travel(61)->seconds();
        $this->passwordStep();
        $this->post('/dashboard/two-factor', ['code' => $this->otp()])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertSame(1, AuditLog::query()->where('action', 'auth.two_factor_locked')->count(), 'the lock leaves a trace');

        $this->travel(1)->hours();
        $this->passwordStep();
        $this->post('/dashboard/two-factor', ['code' => $this->otp()])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_a_code_older_than_the_last_one_used_is_refused_even_by_a_stale_copy_of_the_owner(): void
    {
        // FINAL-QA QA-009: the step is consumed with one conditional update, so a request that loaded the owner before
        // another request used the code cannot use it again.
        $owner = $this->owner();
        $stale = User::query()->findOrFail($owner->id);
        $code = $this->otp();
        $this->assertTrue(app(TwoFactor::class)->verify($owner, $code));
        $this->assertFalse(app(TwoFactor::class)->verify($stale, $code), 'the parallel request loses');
    }

    public function test_recovery_code_works_exactly_once(): void
    {
        $owner = $this->owner();
        $codes = app(TwoFactor::class)->generateRecoveryCodes();
        $owner->forceFill(['two_factor_recovery_codes' => $codes['digests']])->save();

        $this->passwordStep();
        $this->post('/dashboard/two-factor', ['recovery_code' => strtolower($codes['plain'][0])])->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->post('/dashboard/logout');
        $this->passwordStep();
        $this->post('/dashboard/two-factor', ['recovery_code' => $codes['plain'][0]])->assertSessionHasErrors('recovery_code');
        $this->assertCount(9, $owner->fresh()->two_factor_recovery_codes ?? []);
    }

    public function test_first_sign_in_forces_enrollment_and_shows_recovery_codes_once(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.test']);
        $this->passwordStep()->assertRedirect('/dashboard/two-factor/setup');
        $this->get('/dashboard/two-factor/setup')->assertOk()->assertSee('<svg', false);
        $secret = session(OwnerSession::SETUP_SECRET);
        $this->assertIsString($secret);

        $this->post('/dashboard/two-factor/setup', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/dashboard/two-factor/setup', ['code' => $this->otp($secret)])->assertRedirect('/dashboard/recovery-codes');

        $this->assertAuthenticatedAs($owner);
        $this->assertTrue($owner->fresh()?->hasTwoFactorEnabled());
        $page = $this->get('/dashboard/recovery-codes')->assertOk();
        $this->assertSame(10, substr_count((string) $page->getContent(), '<li><code>'));
        $this->get('/dashboard/recovery-codes')->assertRedirect('/dashboard');
    }

    public function test_inactive_owner_cannot_sign_in(): void
    {
        User::factory()->inactive()->withTwoFactor(self::SECRET)->create(['email' => 'owner@example.test']);

        $this->passwordStep()->assertSessionHasErrors('email');
    }

    public function test_pending_sign_in_expires(): void
    {
        $this->owner();
        $this->passwordStep();
        $this->travel(6)->minutes();

        $this->get('/dashboard/two-factor')->assertRedirect('/dashboard/login');
    }

    public function test_absolute_session_lifetime_signs_the_owner_out(): void
    {
        $this->owner();
        $this->passwordStep();
        $this->post('/dashboard/two-factor', ['code' => $this->otp()]);
        $this->travel(12 * 60 + 1)->minutes();

        $this->get('/dashboard')->assertRedirect('/dashboard/login');
        $this->assertGuest();
    }

    public function test_every_dashboard_route_is_protected_server_side(): void
    {
        $open = ['login', 'login.store', 'two-factor.challenge', 'two-factor.verify', 'two-factor.setup', 'two-factor.enable'];
        $checked = 0;
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'dashboard') || in_array($route->getName(), $open, true)) {
                continue;
            }
            $this->assertContains('owner', $route->gatherMiddleware(), "Route {$route->uri()} is missing the owner gate");
            $this->assertContains('auth', $route->gatherMiddleware(), "Route {$route->uri()} is missing auth");
            $checked++;
        }
        $this->assertGreaterThan(0, $checked);
    }

    public function test_authenticated_user_without_second_factor_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/dashboard/login');
        $this->assertGuest();
    }

    public function test_sensitive_actions_require_recent_reconfirmation(): void
    {
        Route::middleware(['web', 'auth', 'owner', 'confirmed'])->get('/dashboard/__sensitive', fn () => 'ok');
        $owner = $this->owner();
        $this->passwordStep();
        $this->post('/dashboard/two-factor', ['code' => $this->otp()]);
        $this->get('/dashboard/__sensitive')->assertOk();

        $this->travel(11)->minutes();
        $this->get('/dashboard/__sensitive')->assertRedirect('/dashboard/confirm');
        // google2fa reads the real clock (not Carbon's test time), so the sign-in code is still the current one:
        // re-using it must be refused (replay), then a fresh step is accepted.
        $this->post('/dashboard/confirm', ['password' => self::PASSWORD, 'code' => $this->otp()])->assertSessionHasErrors('password');
        $owner->forceFill(['two_factor_last_step' => 0])->save();
        $this->app['auth']->forgetGuards(); // the test app caches the signed-in model between requests
        $this->post('/dashboard/confirm', ['password' => self::PASSWORD, 'code' => $this->otp()])
            ->assertSessionHasNoErrors()->assertRedirect('/dashboard/__sensitive');
        $this->get('/dashboard/__sensitive')->assertOk();
        $this->assertTrue(AuditLog::query()->where('action', 'auth.confirmed')->where('user_id', $owner->id)->exists());
    }
}
