<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\ShaltoorQuestion;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Core\Attention;
use App\Services\Shaltoor\ShaltoorSettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Dashboard › Shaltoor (M69 §23): on/off and words (audited), the period's numbers, unanswered questions and the monitor. */
class ShaltoorDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
    }

    private function asOwner(): self
    {
        return $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    private function ask(string $question, bool $answered, string $topic = 'unknown', int $daysAgo = 0): void
    {
        ShaltoorQuestion::query()->create(['locale' => 'ar', 'topic' => $topic, 'answered' => $answered, 'question' => $question,
            'normalized' => $question, 'page' => 'default', 'created_at' => now()->subDays($daysAgo)]);
    }

    public function test_the_owner_sees_the_numbers_and_the_unanswered_questions_grouped(): void
    {
        $this->ask('عندكم موقف؟', false);
        $this->ask('عندكم موقف؟', false);
        $this->ask('ساعات الدوام', true, 'hours');
        $this->ask('سؤال قديم', false, 'unknown', 60);

        $html = (string) $this->asOwner()->get('/dashboard/shaltoor')->assertOk()->getContent();
        $this->assertStringContainsString('عندكم موقف؟', $html);
        $this->assertSame(1, substr_count($html, 'data-shaltoor-unanswered'), 'the same question once, with its count');
        $this->assertStringContainsString(trans_choice('dashboard.shaltoor.times', 2, ['count' => 2]), $html);
        $this->assertStringNotContainsString('سؤال قديم', $html, 'outside the 30-day period');
        $this->assertStringContainsString('سؤال قديم', (string) $this->get('/dashboard/shaltoor?days=90')->getContent());
        $this->assertStringContainsString(__('dashboard.shaltoor.ai_pending'), $html);
    }

    public function test_dealt_with_removes_the_question_and_is_audited(): void
    {
        $this->ask('عندكم موقف؟', false);
        $this->asOwner()->post('/dashboard/shaltoor/handled', ['normalized' => 'عندكم موقف؟'])->assertSessionHas('status', __('dashboard.shaltoor.handled_done'));
        $this->assertNotNull(ShaltoorQuestion::query()->value('handled_at'));
        $this->assertSame(1, AuditLog::query()->where('action', 'shaltoor.handled')->count());
        $this->assertStringNotContainsString('data-shaltoor-unanswered', (string) $this->get('/dashboard/shaltoor')->getContent());
    }

    public function test_switching_off_and_new_words_reach_the_site(): void
    {
        $this->asOwner()->put('/dashboard/shaltoor', ['enabled' => '1', 'welcome_ar' => 'أهلًا بك في شلتر', 'welcome_en' => '', 'suggestions_ar' => "المنيو\nالفروع", 'suggestions_en' => ''])
            ->assertSessionHasNoErrors();
        $home = (string) $this->get('/ar/')->getContent();
        $this->assertStringContainsString('أهلًا بك في شلتر', $home);
        $this->assertStringContainsString('>الفروع</button>', $home);
        $this->assertStringContainsString(e(__('shaltoor.welcome', [], 'en')), (string) $this->get('/en/')->getContent(), 'empty = the default');

        $this->asOwner()->put('/dashboard/shaltoor', ['enabled' => '0'])->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('data-shaltoor', (string) $this->get('/ar/')->getContent());
        $this->assertGreaterThan(0, AuditLog::query()->count(), 'settings changes are versioned and audited');
    }

    public function test_too_many_or_too_long_suggestions_are_refused(): void
    {
        $this->asOwner()->put('/dashboard/shaltoor', ['enabled' => '1', 'suggestions_ar' => implode("\n", array_fill(0, 9, 'اقتراح'))])
            ->assertSessionHasErrors(['suggestions_ar'], null, 'shaltoor');
        $this->asOwner()->put('/dashboard/shaltoor', ['enabled' => '1', 'suggestions_en' => str_repeat('x', 41)])
            ->assertSessionHasErrors(['suggestions_en'], null, 'shaltoor');
    }

    public function test_a_question_left_unanswered_again_and_again_needs_attention_until_dealt_with(): void
    {
        foreach (range(1, 3) as $i) {
            $this->ask('عندكم موقف؟', false);
        }
        app(Attention::class)->runMonitors();
        $items = app(Attention::class)->open('ar');
        $this->assertCount(1, $items);
        $this->assertSame(route('dashboard.shaltoor'), $items[0]['href']);
        $this->assertTrue($items[0]['dismissible'], 'information, not an action-required issue');

        $this->asOwner()->post('/dashboard/shaltoor/handled', ['normalized' => 'عندكم موقف؟']);
        app(Attention::class)->runMonitors();
        $this->assertSame(0, app(Attention::class)->count());
    }

    public function test_questions_past_the_retention_window_are_deleted(): void
    {
        $this->ask('قديم جدًا', false, 'unknown', 120);
        $this->ask('حديث', false);
        app(Attention::class)->runMonitors();
        $this->assertSame(['حديث'], ShaltoorQuestion::query()->pluck('question')->all());
    }

    public function test_only_the_owner_reaches_it_and_changes_need_a_fresh_confirmation(): void
    {
        $this->get('/dashboard/shaltoor')->assertRedirect('/dashboard/login');
        $this->post('/dashboard/shaltoor/handled', ['normalized' => 'x'])->assertRedirect('/dashboard/login');

        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->subDay()->getTimestamp()])
            ->put('/dashboard/shaltoor', ['enabled' => '0'])->assertRedirect();
        $this->assertTrue(app(ShaltoorSettings::class)->enabled(), 'not changed without re-confirmation');
    }
}
