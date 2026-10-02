<?php

namespace Tests\Feature\Http;

use App\Services\Forms\FormGuard;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * FINAL-QA QA-006: every error a visitor can meet is a page in the site's design and language. The framework's own
 * pages use inline <style>, which the CSP (style-src 'self') blocks, so they rendered unstyled and in English. A public
 * form left open past the session lifetime goes back to the form with what was typed — never a dead end.
 */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        // The framework skips the CSRF check under PHPUnit; these tests need the real check.
        $this->app->instance(PreventRequestForgery::class, new class($this->app, $this->app->make(Encrypter::class)) extends PreventRequestForgery
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
    }

    public function test_an_expired_public_form_returns_to_the_form_with_what_was_typed(): void
    {
        $fields = ['branch' => 'drive', 'rating_overall' => '4', 'comment' => 'قهوة ممتازة', 'entry' => 'direct',
            'form_token' => Crypt::encryptString((string) (now()->getTimestamp() - 30)), 'idempotency_key' => (string) Str::uuid(),
            FormGuard::HONEYPOT => '', 'national_id' => '9991234567'];

        // No CSRF token: what a browser sends after the session ran out.
        $response = $this->post('/ar/feedback/', $fields);

        $response->assertRedirect('http://localhost/ar/feedback/');
        $response->assertSessionHasErrors(['form' => __('site.errors.form_expired', [], 'ar')]);
        $this->assertSame('قهوة ممتازة', session()->getOldInput('comment'), 'what was typed is kept');
        $this->assertNull(session()->getOldInput('national_id'), 'an identity number never sits in the session');
    }

    public function test_an_expired_dashboard_form_shows_the_styled_page(): void
    {
        $response = $this->post('/dashboard/login', ['email' => 'owner@example.test', 'password' => 'x']);

        $response->assertStatus(419);
        $html = (string) $response->getContent();
        $this->assertStringContainsString('انتهت صلاحية الصفحة', $html);
        $this->assertStringContainsString('This page has expired', $html, 'bilingual outside /ar and /en');
        $this->assertStringNotContainsString('<style', $html, 'no inline styles for the CSP to block');
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
    }

    public function test_too_many_requests_and_other_client_errors_use_the_site_design(): void
    {
        config(['shelter.search_per_minute' => 1]);
        $this->get('/ar/search/?q=latte')->assertOk();
        $html = (string) $this->get('/ar/search/?q=latte')->assertStatus(429)->getContent();
        $this->assertStringContainsString('طلبات كثيرة خلال وقت قصير', $html);
        $this->assertStringContainsString('lang="ar" dir="rtl"', $html, 'the language comes from the address');
        $this->assertStringNotContainsString('<style', $html);

        Route::get('/en/qa-forbidden', fn () => abort(403));
        Route::get('/en/qa-gone', fn () => abort(410));
        $this->assertStringContainsString('You can’t open this page', (string) $this->get('/en/qa-forbidden')->assertForbidden()->getContent());
        $gone = (string) $this->get('/en/qa-gone')->assertStatus(410)->getContent();
        $this->assertStringContainsString('This page couldn’t be opened', $gone, 'the 4xx fallback');
        $this->assertStringNotContainsString('<style', $gone);
    }
}
