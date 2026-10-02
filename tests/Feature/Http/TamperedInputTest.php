<?php

namespace Tests\Feature\Http;

use App\Services\Forms\FormGuard;
use Database\Seeders\FranchiseSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Tests\Feature\Careers\CareersTestHelpers;
use Tests\TestCase;

/**
 * FINAL-QA QA-026: a hand-made request — an array where text is expected (?q[]=x, name[]=x), very long or odd text —
 * is answered (a page, a redirect with errors, a 404), never with a server error. Found by the live probe:
 * /ar/search/?q[]=x answered 500.
 */
class TamperedInputTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->bootCareers();
        $this->seed(FranchiseSeeder::class);
        $this->app->forgetScopedInstances();
    }

    public function test_public_addresses_with_arrays_or_odd_text_never_answer_500(): void
    {
        foreach (['/ar/search/?q[]=x', '/en/search/?q[a][b]=x', '/ar/jo/menu/?branch[]=x', '/ar/feedback/?branch[]=1', '/ar/careers/track/?ref[]=1',
            '/ar/jo/events/?page[]=1', '/ar/search/?q='.str_repeat('ق', 3000), '/ar/search/?q=%00%01', '/en/jo/menu/?branch=%E2%80%AE'] as $url) {
            $this->assertLessThan(500, $this->get($url)->getStatusCode(), $url);
        }
    }

    public function test_every_public_form_field_sent_as_an_array_is_refused_politely(): void
    {
        $token = fn (): array => ['form_token' => Crypt::encryptString((string) (now()->getTimestamp() - 30)), 'idempotency_key' => (string) Str::uuid()];
        $forms = [
            '/ar/careers/' => array_keys($this->validInput()) + [100 => 'primary_attachment'],
            '/en/franchise/' => ['full_name', 'phone', 'email', 'country', 'city', 'market', 'partnership_interest_type', 'experience_band', 'experience_text',
                'owns_business', 'location_status', 'introduction', 'non_binding_acknowledgement', 'data_processing_consent', 'attr_utm_source', 'attr_landing_path'],
            '/ar/feedback/' => ['branch', 'rating_overall', 'rating_coffee', 'comment', 'entry'],
            '/ar/careers/track/' => ['number', 'phone'],
        ];
        foreach ($forms as $url => $fields) {
            $base = ($url === '/ar/careers/' ? $this->validInput() : []) + $token();
            foreach ([...$fields, 'form_token', 'idempotency_key', FormGuard::HONEYPOT] as $field) {
                $status = $this->post($url, [$field => ['x' => ['y']]] + $base)->getStatusCode();
                $this->assertLessThan(500, $status, "{$url} {$field}[]");
            }
            $all = array_fill_keys([...$fields, 'form_token', 'idempotency_key'], ['x']);
            $this->assertLessThan(500, $this->post($url, $all)->getStatusCode(), "{$url} everything as arrays");
        }
        $this->assertLessThan(500, $this->postJson('/ar/careers/uploads/', ['file' => ['x']])->getStatusCode(), 'upload with no file');
    }
}
