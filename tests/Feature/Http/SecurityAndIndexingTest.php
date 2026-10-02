<?php

namespace Tests\Feature\Http;

use App\Support\CanonicalHost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndIndexingTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/ar/');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $response->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeaderMissing('X-Powered-By')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_owner_screens_and_reference_pages_are_never_stored_by_the_browser(): void
    {
        // FINAL-QA QA-007: back/forward after logout or on a shared device must not show them again.
        foreach (['/dashboard/login', '/dashboard', '/ar/careers/track/', '/ar/careers/submitted/'] as $path) {
            $this->assertStringContainsString('no-store', (string) $this->get($path)->headers->get('Cache-Control'), $path);
        }
        $this->assertStringNotContainsString('no-store', (string) $this->get('/ar/')->headers->get('Cache-Control'), 'public pages stay cacheable');
    }

    public function test_staging_and_production_use_the_configured_address_never_the_host_header(): void
    {
        // FINAL-QA QA-012: canonicals, hreflang and the sitemap must not follow a forged or alternate Host header.
        CanonicalHost::apply('local', 'https://www.shelterjo.com');
        $this->assertStringStartsWith('http://localhost/', route('home', ['locale' => 'ar']), 'local keeps the request host');

        CanonicalHost::apply('production', 'https://www.shelterjo.com');
        $this->assertSame('https://www.shelterjo.com/ar', route('home', ['locale' => 'ar']));
        $html = (string) $this->get('http://evil.example/ar/')->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://www.shelterjo.com/ar/">', $html);
        $this->assertStringNotContainsString('evil.example', $html);
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $this->get('https://localhost/ar/')->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_non_production_is_never_indexable(): void
    {
        config(['shelter.indexing' => true]); // still not production

        $this->get('/ar/')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $this->get('/robots.txt')->assertOk()->assertSee("Disallow: /\n", false);
    }

    public function test_indexable_production_allows_crawling_but_not_the_dashboard(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['shelter.indexing' => true]);

        $this->get('/ar/')->assertHeaderMissing('X-Robots-Tag')->assertDontSee('name="robots"', false);
        $this->get('/robots.txt')->assertSee('Disallow: /dashboard/', false)->assertDontSee("Disallow: /\n", false);
    }

    public function test_staging_basic_auth_gate(): void
    {
        config(['shelter.basic_auth.user' => 'staging', 'shelter.basic_auth.password' => 'pass-phrase']);

        $this->get('/ar/')->assertStatus(401)->assertHeader('WWW-Authenticate');
        $this->get('/ar/', ['PHP_AUTH_USER' => 'staging', 'PHP_AUTH_PW' => 'wrong'])->assertStatus(401);
        $this->get('/ar/', ['PHP_AUTH_USER' => 'staging', 'PHP_AUTH_PW' => 'pass-phrase'])->assertOk();
        $this->get('/up')->assertOk();
    }
}
