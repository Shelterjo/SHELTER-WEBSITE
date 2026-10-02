<?php

namespace Tests\Feature\Shaltoor;

use App\Models\ShaltoorQuestion;
use App\Models\User;
use App\Services\Core\Settings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The public endpoint and launcher (M69 §24–§33): JSON in/out, CSRF, a per-visitor rate limit, nothing personal kept,
 * off means gone (no launcher, 404), and the launcher only on the language pages with that page's suggestions.
 */
class ShaltoorEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        RateLimiter::clear('shaltoor');
    }

    public function test_a_question_gets_a_plain_text_answer_that_is_never_cached(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $response = $this->postJson('/ar/shaltoor/', ['question' => 'ساعات الدوام', 'page' => 'default', 'conversation' => '6f1c1e5a-0b5e-4a8e-9a43-3a7b2d1c9e10'])
            ->assertOk()->assertJsonStructure(['text', 'topic', 'answered', 'actions', 'suggestions'])
            ->assertJsonPath('topic', 'hours');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertSame(1, ShaltoorQuestion::query()->count());
        $this->assertSame('6f1c1e5a-0b5e-4a8e-9a43-3a7b2d1c9e10', ShaltoorQuestion::query()->value('conversation'));
    }

    public function test_it_sits_behind_the_forgery_check_like_every_form(): void
    {
        // The framework skips the check while tests run, so the route's middleware is asserted instead.
        $route = Route::getRoutes()->getByName('shaltoor');
        $this->assertNotNull($route);
        $this->assertContains('web', Route::gatherRouteMiddleware($route));
        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = app(Kernel::class);
        $web = $kernel->getMiddlewareGroups()['web'] ?? [];
        $this->assertNotSame([], array_intersect([PreventRequestForgery::class, ValidateCsrfToken::class], $web));
    }

    public function test_personal_details_are_removed_before_anything_is_kept(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->postJson('/en/shaltoor/', ['question' => 'call me on 0791234567 or mail me at someone@example.test see www.example.test/x', 'conversation' => 'not-a-uuid'])->assertOk();
        $row = ShaltoorQuestion::query()->firstOrFail();
        $this->assertStringNotContainsString('0791234567', $row->question);
        $this->assertStringNotContainsString('someone@example.test', $row->question);
        $this->assertStringNotContainsString('example.test/x', $row->question);
        $this->assertNull($row->conversation, 'only a random id from the browser is kept, nothing else');
        $this->assertArrayNotHasKey('ip_address', $row->getAttributes());
    }

    public function test_answers_are_text_and_never_echo_markup(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $json = $this->postJson('/en/shaltoor/', ['question' => '<script>alert(1)</script> menu'])->assertOk()->json();
        $this->assertStringNotContainsString('<script>', (string) $json['text']);
        $this->assertStringNotContainsString('<script>', (string) ShaltoorQuestion::query()->value('question'));
    }

    public function test_too_long_questions_are_refused_and_not_kept(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->postJson('/ar/shaltoor/', ['question' => str_repeat('قهوة ', 200)])->assertOk()->assertJsonPath('topic', 'too_long');
        $this->assertSame(0, ShaltoorQuestion::query()->count());
    }

    public function test_a_visitor_is_slowed_down_after_the_minute_limit(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['shaltoor.rate_limit.per_minute' => 3]);
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/ar/shaltoor/', ['question' => 'مرحبا'])->assertOk();
        }
        $this->postJson('/ar/shaltoor/', ['question' => 'مرحبا'])->assertStatus(429)->assertJsonPath('topic', 'rate_limited');
    }

    public function test_the_launcher_shows_on_language_pages_with_that_pages_suggestions(): void
    {
        $home = (string) $this->get('/ar/')->assertOk()->getContent();
        $this->assertStringContainsString('data-shaltoor-launcher', $home);
        $this->assertStringContainsString('data-page="default"', $home);
        $this->assertStringContainsString(e(__('shaltoor.suggestions.default.0', [], 'ar')), $home);
        $this->assertStringContainsString('name="_token"', $home, 'the token travels in the form');

        $menu = (string) $this->get('/en/jo/menu/')->assertOk()->getContent();
        $this->assertStringContainsString('data-page="menu"', $menu);

        $branch = (string) $this->get('/ar/jo/locations/irbid/drive/')->assertOk()->getContent();
        $this->assertStringContainsString('data-branch="drive"', $branch);

        $this->assertStringNotContainsString('data-shaltoor-launcher', (string) $this->get('/')->getContent(), 'not on the bilingual gateway');
    }

    public function test_switched_off_there_is_no_launcher_and_no_endpoint(): void
    {
        app(Settings::class)->set('shaltoor.enabled', false, User::factory()->create(), 'test', 'PUBLIC');
        $this->assertStringNotContainsString('data-shaltoor', (string) $this->get('/ar/')->getContent());
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->postJson('/ar/shaltoor/', ['question' => 'مرحبا'])->assertNotFound();
    }
}
