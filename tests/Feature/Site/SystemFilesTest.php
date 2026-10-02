<?php

namespace Tests\Feature\Site;

use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** PHASE 2 system outputs: /llms.txt (SI-S06) from approved data only, and the static maintenance page (SI-S03). */
class SystemFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_lists_only_approved_published_facts(): void
    {
        $this->seed(MasterDataSeeder::class);
        $response = $this->get('/llms.txt')->assertOk();
        $this->assertStringStartsWith('text/plain', (string) $response->headers->get('Content-Type'));
        $text = (string) $response->getContent();

        $this->assertStringStartsWith("# SHELTER COFFEE\n\nشلتر كوفي\n", $text);
        $this->assertStringContainsString('Founded: 2019', $text, 'D-018 (2018 is OLD OR INCORRECT)');
        $this->assertStringNotContainsString('2018', $text);
        $this->assertStringContainsString('- [Menu](http://localhost/en/jo/menu/)', $text);
        $this->assertStringContainsString('- [المنيو](http://localhost/ar/jo/menu/)', $text);
        $this->assertStringContainsString('- [SHELTER COFFEE DRIVE](http://localhost/en/jo/locations/irbid/drive/) — شلتر كوفي درايف', $text);
        $this->assertStringContainsString('- Phone: +962 79 900 9436', $text);
        // Intent numbers, the unapproved email and empty or unpublished pages stay out.
        foreach (['799338445', '799530383', 'info@', '/about/', '/events/'] as $absent) {
            $this->assertStringNotContainsString($absent, $text);
        }
    }

    public function test_maintenance_page_is_static_and_bilingual(): void
    {
        $html = view('errors.503')->render();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringContainsString('نعمل على تحسين الموقع', $html);
        $this->assertStringContainsString('We are improving the site', $html);
        $this->assertStringNotContainsString('tel:', $html, 'no database read: no contact data on the maintenance page');
    }
}
