<?php

namespace Tests\Feature\Http;

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
            ->assertHeaderMissing('Strict-Transport-Security');
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
