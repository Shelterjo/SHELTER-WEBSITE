<?php

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Core routing: gateway, locale prefixes, canonical trailing slash, hreflang (D-031, D-052, D-067). */
class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_is_bilingual_with_x_default_and_no_redirect(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('hreflang="x-default" href="http://localhost/"', false)
            ->assertSee('hreflang="ar" href="http://localhost/ar/"', false)
            ->assertSee('hreflang="en" href="http://localhost/en/"', false);
    }

    public function test_locale_home_sets_language_and_direction(): void
    {
        $this->get('/ar/')->assertOk()->assertSee('<html lang="ar" dir="rtl">', false)->assertHeader('Content-Language', 'ar');
        $this->get('/en/')->assertOk()->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('<link rel="canonical" href="http://localhost/en/">', false);
    }

    public function test_missing_trailing_slash_redirects_once_permanently(): void
    {
        $this->get('/en?x=1')->assertStatus(301)->assertRedirect('http://localhost/en/?x=1');
    }

    public function test_unknown_locale_is_not_found(): void
    {
        $this->get('/fr/')->assertNotFound();
    }

    public function test_language_comes_from_the_url_not_the_browser(): void
    {
        $this->get('/', ['Accept-Language' => 'en-US'])->assertOk()->assertSee('<html lang="ar"', false);
    }
}
