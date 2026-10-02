<?php

namespace Tests\Feature\Site;

use App\Models\Feedback;
use App\Services\Forms\FormGuard;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/** SI-B16 Voice of Customer (docs/platform/VOICE-OF-CUSTOMER.md §2 and acceptance tests VC-T01…VC-T04). */
class FeedbackFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validInput(array $overrides = []): array
    {
        return $overrides + [
            'branch' => 'drive', 'rating_overall' => '4', 'rating_coffee' => '5', 'rating_service' => '', 'rating_cleanliness' => '',
            'rating_speed' => '', 'comment' => '', 'entry' => 'direct',
            'form_token' => Crypt::encryptString((string) (now()->getTimestamp() - 30)), 'idempotency_key' => (string) Str::uuid(),
        ];
    }

    /**
     * @param  array<string, string>  $overrides
     * @return TestResponse<Response>
     */
    private function send(array $overrides = [], string $locale = 'ar'): TestResponse
    {
        return $this->post('/'.$locale.'/feedback/', $this->validInput($overrides));
    }

    public function test_the_form_asks_nothing_personal_and_a_link_can_preset_the_branch(): void
    {
        $html = (string) $this->get('/ar/feedback/')->assertOk()->getContent();
        $this->assertStringContainsString('كيف كانت تجربتك؟', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        foreach (['id="branch-drive"', 'id="branch-house"', 'id="rating_overall-1"', 'id="rating_overall-5"', 'id="rating_speed-3"', 'لا تكتب بيانات شخصية في التعليق.'] as $part) {
            $this->assertStringContainsString($part, $html);
        }
        foreach (['name="phone"', 'type="email"', 'name="full_name"', 'name="name"', 'type="tel"'] as $personal) {
            $this->assertStringNotContainsString($personal, $html, 'VC-T02: no personal field');
        }
        $this->assertStringNotContainsString('application/ld+json">{"@context":"https://schema.org","@type":"WebPage"', $html, 'no structured data');

        $preset = (string) $this->get('/en/feedback/?branch=house')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#id="branch-house"[^>]*checked#', $preset);
        $this->assertStringContainsString('name="entry" value="branch_link"', $preset);
        $unknown = (string) $this->get('/en/feedback/?branch=nowhere')->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('#name="branch"[^>]*checked#', $unknown);
        $this->assertStringContainsString('name="entry" value="direct"', $unknown);
    }

    public function test_closed_in_production_until_switched_on(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        $this->get('/ar/feedback/')->assertNotFound();
        // Production also enforces CSRF, so the closed submit is reached with a valid token.
        $this->withSession(['_token' => 'test-token'])->post('/ar/feedback/', $this->validInput(['_token' => 'test-token']))->assertNotFound();

        config(['feedback.form_enabled_in_production' => true]);
        $this->get('/ar/feedback/')->assertOk();
    }

    public function test_an_answer_is_saved_without_anything_identifying(): void
    {
        $this->send(['comment' => "Great flat white.\nThanks", 'entry' => 'branch_link'])->assertRedirect('http://localhost/ar/feedback/submitted/');

        $feedback = Feedback::query()->sole();
        $this->assertSame([4, 5, null], [$feedback->rating_overall, $feedback->rating_coffee, $feedback->rating_service]);
        $this->assertSame("Great flat white.\nThanks", $feedback->comment);
        $this->assertSame(['ar', 'branch_link'], [$feedback->locale, $feedback->entry_point]);
        $this->assertSame('drive', $feedback->branch?->slug);
        $columns = array_keys((array) DB::table('feedback')->first());
        foreach (['ip', 'ip_hash', 'name', 'phone', 'email', 'user_agent', 'session_id', 'user_id'] as $identifying) {
            $this->assertNotContains($identifying, $columns, 'VC-T02');
        }

        $done = (string) $this->get('/ar/feedback/submitted/')->assertOk()->getContent();
        $this->assertStringContainsString('وصلنا رأيك.', $done);
        $this->assertStringNotContainsString('0799338445', $done, 'the complaints line on this screen is pending (PO-063)');
        $this->get('/ar/feedback/submitted/')->assertRedirect('http://localhost/ar/feedback/');
    }

    public function test_vc_t03_the_next_screen_is_identical_for_every_rating(): void
    {
        $screens = [];
        foreach (['1', '2', '3', '4', '5'] as $rating) {
            $this->send(['rating_overall' => $rating, 'rating_coffee' => $rating])->assertRedirect('http://localhost/ar/feedback/submitted/');
            $html = (string) $this->get('/ar/feedback/submitted/')->getContent();
            $screens[$rating] = (string) preg_replace('#<meta name="csrf-token"[^>]*>|name="_token" value="[^"]*"#', '', $html);
        }
        $this->assertCount(1, array_unique($screens), 'no review gating: the same screen whatever the score');
        $this->assertSame(5, Feedback::query()->count());
    }

    public function test_required_answers_limits_and_kept_input(): void
    {
        $this->send(['branch' => '', 'rating_overall' => '', 'rating_coffee' => '9', 'comment' => str_repeat('a', 1001)])->assertRedirect('http://localhost/ar/feedback/');
        $html = (string) $this->get('/ar/feedback/')->getContent();
        foreach (['الفرع: هذا الحقل مطلوب.', 'التجربة العامة: هذا الحقل مطلوب.', 'القهوة: اختر قيمة من القائمة.', 'تعليق (اختياري): عدد الأحرف أكبر من الحد المسموح (1000).'] as $line) {
            $this->assertStringContainsString($line, $html);
        }
        $this->assertStringContainsString(str_repeat('a', 1001), $html, 'the comment is kept after an error');
        $this->assertSame(0, Feedback::query()->count());

        $this->send(['branch' => 'house', 'rating_overall' => '2'])->assertRedirect('http://localhost/ar/feedback/submitted/');
        $this->assertSame(1, DB::table('feedback')->count(), 'only branch and overall are required');
    }

    public function test_vc_t04_bots_repeats_and_bursts(): void
    {
        $this->send([FormGuard::HONEYPOT => 'http://spam.example'])->assertRedirect('http://localhost/ar/feedback/');
        $this->send(['form_token' => Crypt::encryptString((string) now()->getTimestamp())])->assertRedirect('http://localhost/ar/feedback/');
        $this->assertSame(0, Feedback::query()->count(), 'honeypot and a too-fast form save nothing');

        $key = (string) Str::uuid();
        $this->send(['idempotency_key' => $key])->assertRedirect('http://localhost/ar/feedback/submitted/');
        $this->send(['idempotency_key' => $key])->assertRedirect('http://localhost/ar/feedback/submitted/');
        $this->assertSame(1, DB::table('feedback')->count(), 'the same answer is saved once');

        for ($i = 0; $i < 4; $i++) {
            $this->send();
        }
        $this->send()->assertRedirect('http://localhost/ar/feedback/');
        $this->assertSame(5, DB::table('feedback')->count(), '5 per 10 minutes from one (hashed) address');

        // FINAL-QA QA-004: refused sends count too — a script cannot resend an invalid form without end. Past the
        // 10-minute window, 6 sends this hour (valid or not) are the limit here.
        $this->travel(11)->minutes();
        config(['feedback.abuse.attempts_per_hour' => 7]);
        $this->send(['rating_overall' => ''])->assertSessionHasErrors('rating_overall');
        $this->send(['rating_overall' => ''])->assertSessionHasErrors('form');
        $this->assertSame(__('feedback.errors.rate'), session('errors')->first('form'));
    }

    public function test_the_form_is_linked_from_nowhere_until_its_entry_points_are_decided(): void
    {
        foreach (['/ar/', '/ar/contact/', '/ar/jo/locations/irbid/drive/', '/en/jo/locations/'] as $url) {
            $this->assertStringNotContainsString('/feedback/', (string) $this->get($url)->getContent(), $url);
        }
    }
}
