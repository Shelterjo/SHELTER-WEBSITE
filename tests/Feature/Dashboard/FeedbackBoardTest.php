<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Feedback;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Dashboard → Customer feedback (VOICE-OF-CUSTOMER §5, VC-T05, VC-T10): numbers with their n, branches, comments. */
class FeedbackBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    private function rate(int $branch, int $overall, ?int $coffee = null, ?string $comment = null, int $daysAgo = 1): Feedback
    {
        return Feedback::query()->create([
            'branch_id' => $branch, 'rating_overall' => $overall, 'rating_coffee' => $coffee, 'comment' => $comment, 'locale' => 'ar',
            'entry_point' => 'direct', 'idempotency_key' => (string) Str::uuid(), 'form_version' => 'feedback-form-v1', 'submitted_at' => now()->subDays($daysAgo),
        ]);
    }

    public function test_averages_and_the_branch_comparison_show_their_counts(): void
    {
        [$drive, $house] = Branch::query()->orderBy('id')->pluck('id')->all();
        $this->rate($drive, 5, 4);
        $this->rate($drive, 4);
        $this->rate($house, 2, 2, 'Slow service');
        $this->rate($house, 5, null, null, 45); // outside the 30 days

        $html = (string) $this->get('/dashboard/requests/feedback')->assertOk()->getContent();
        $this->assertStringContainsString('3.7 من 5', $html, 'overall: (5 + 4 + 2) / 3');
        $this->assertStringContainsString('n = 3', $html);
        $this->assertStringContainsString('3.0 من 5', $html, 'coffee: (4 + 2) / 2');
        $this->assertStringContainsString('4.5 (2)', $html, 'DRIVE overall in the comparison');
        $this->assertStringContainsString('2.0 (1)', $html, 'HOUSE overall in the comparison');
        $this->assertStringContainsString('Slow service', $html);

        $house30 = (string) $this->get('/dashboard/requests/feedback?branch='.$house)->getContent();
        $this->assertStringContainsString('2.0 من 5', $house30);
        $this->assertStringNotContainsString('مقارنة الفروع', $house30, 'one branch chosen → no comparison');
        $this->assertStringContainsString('n = 3', (string) $this->get('/dashboard/requests/feedback?period=week')->getContent(), 'all three within the week');
    }

    public function test_comments_can_be_archived_and_cleaned_of_personal_data_without_keeping_it(): void
    {
        $branch = (int) Branch::query()->value('id');
        $low = $this->rate($branch, 1, null, 'Call me on 0791234567 — Ahmad');
        $this->rate($branch, 5, null, 'Lovely coffee');

        $low_only = (string) $this->get('/dashboard/requests/feedback?low=1')->getContent();
        $this->assertStringContainsString('Ahmad', $low_only);
        $this->assertStringNotContainsString('Lovely coffee', $low_only);

        $this->from('/dashboard/requests/feedback')->put('/dashboard/requests/feedback/'.$low->id, ['comment' => 'Call me on [removed]'])->assertSessionHas('status');
        $this->assertSame('Call me on [removed]', $low->refresh()->comment);
        $audit = AuditLog::query()->where('action', 'feedback.redacted')->get()->toJson();
        $this->assertStringNotContainsString('0791234567', $audit, 'the removed text is not kept');
        $this->assertStringNotContainsString('Ahmad', $audit);

        $this->post('/dashboard/requests/feedback/'.$low->id.'/archive');
        $this->assertNotNull($low->refresh()->archived_at);
        $this->assertStringNotContainsString('[removed]', (string) $this->get('/dashboard/requests/feedback')->getContent());
        $this->assertStringContainsString('[removed]', (string) $this->get('/dashboard/requests/feedback?show=archived')->getContent());
        $this->assertStringContainsString('n = 2', (string) $this->get('/dashboard/requests/feedback')->getContent(), 'archived ratings still count');

        auth()->logout();
        $this->get('/dashboard/requests/feedback')->assertRedirect('/dashboard/login');
    }
}
