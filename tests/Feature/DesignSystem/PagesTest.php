<?php

namespace Tests\Feature\DesignSystem;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/** The existing site, sign-in and dashboard views are built from x-ui components only (DS-013, no legacy classes). */
class PagesTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

    private function assertOnlyLibraryClasses(string $html): void
    {
        foreach (['ui-btn', 'ui-label', 'ui-dash', 'ui-details', 'ui-qr', 'ui-codes'] as $legacy) {
            $this->assertDoesNotMatchRegularExpression('/class="[^"]*\b'.$legacy.'\b/', $html, "Legacy class {$legacy}");
        }
        $this->assertStringNotContainsString('style="', $html);
    }

    public function test_site_pages_use_the_skip_link_and_nav_links(): void
    {
        foreach (['/', '/ar/', '/en/'] as $url) {
            $html = (string) $this->get($url)->assertOk()->getContent();
            $this->assertOnlyLibraryClasses($html);
            $this->assertMatchesRegularExpression('/<body>\s*<a href="#main" class="ui-skip-link">/', $html, "{$url}: skip link first");
            $this->assertStringContainsString('class="ui-nav-link"', $html);
        }
    }

    public function test_login_form_is_built_from_fields_and_shows_wired_errors(): void
    {
        $html = (string) $this->get('/dashboard/login')->assertOk()->getContent();
        $this->assertOnlyLibraryClasses($html);
        $this->assertStringContainsString('<label class="ui-field__label" for="email">', $html);
        $this->assertMatchesRegularExpression('/<input type="email" class="ui-input" id="email" required="required" name="email"/', $html);
        $this->assertStringContainsString('class="ui-button ui-button--primary"', $html);
        $this->assertStringNotContainsString('form-errors', $html);

        $this->from('/dashboard/login')->post('/dashboard/login', ['email' => 'nobody@example.test', 'password' => 'wrong-password']);
        $html = (string) $this->get('/dashboard/login')->getContent();
        $this->assertStringContainsString('id="form-errors" role="alert"', $html);
        $this->assertStringContainsString('<a href="#email">'.e(__('dashboard.auth.failed')).'</a>', $html);
        $this->assertStringContainsString('aria-describedby="email-error" aria-invalid="true"', $html);
        $this->assertStringContainsString('value="nobody@example.test"', $html);
    }

    public function test_dashboard_shell_marks_the_current_page(): void
    {
        User::factory()->withTwoFactor(self::SECRET)->create(['email' => 'owner@example.test']);
        $this->post('/dashboard/login', ['email' => 'owner@example.test', 'password' => 'correct horse battery staple']);
        $this->post('/dashboard/two-factor', ['code' => app(Google2FA::class)->getCurrentOtp(self::SECRET)]);

        $html = (string) $this->get('/dashboard')->assertOk()->getContent();
        $this->assertOnlyLibraryClasses($html);
        $this->assertStringContainsString('<body class="ui-shell">', $html);
        $this->assertStringContainsString('aria-label="'.__('ui.main_navigation').'"', $html);
        $this->assertMatchesRegularExpression('/<a class="ui-nav-link ui-sidebar__link" href="[^"]*\/dashboard" aria-current="page"/', $html);
        $this->assertStringContainsString('<h1 class="ui-page-header__title">'.__('dashboard.command_center').'</h1>', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
    }
}
