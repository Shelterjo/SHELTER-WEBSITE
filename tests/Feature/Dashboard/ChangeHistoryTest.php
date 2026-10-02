<?php

namespace Tests\Feature\Dashboard;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Recruitment\UploadSession;
use App\Models\Setting;
use App\Models\ShaltoorQuestion;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Core\AuditLogger;
use App\Services\Core\Settings;
use App\Services\Dashboard\ChangeHistory;
use App\Services\MasterData\FactRegistry;
use App\Services\Shaltoor\ShaltoorLog;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/**
 * Dashboard → Change history (AUDIT-001…003, AUDIT-009, AUDIT-002): every audited change with who, what, when (Amman
 * time) and from → to; filters by area, period and item; secrets and internal notes never shown; Owner only; the
 * system's own changes (scheduled jobs, commands) are in it with the system as the actor.
 */
class ChangeHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create(['name' => 'Test Owner']);
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->branch = Branch::query()->where('slug', 'drive')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>  $meta
     */
    private function entry(string $action, array $changes = [], array $meta = [], ?int $daysAgo = 0, ?int $userId = null, ?Setting $subject = null): AuditLog
    {
        $log = new AuditLog(['action' => $action, 'changes' => $changes === [] ? null : $changes, 'meta' => $meta === [] ? null : $meta, 'user_id' => $userId]);
        if ($subject !== null) {
            $log->subject()->associate($subject);
        }
        $log->created_at = now()->subDays((int) $daysAgo);
        $log->save();

        return $log;
    }

    /** @param array<string, string|null> $filters */
    private function total(array $filters): int
    {
        return app(ChangeHistory::class)->page(ChangeHistory::filters($filters), 1)->total();
    }

    public function test_a_change_shows_who_what_when_and_from_to(): void
    {
        $before = (string) $this->branch->name_ar;
        $this->put('/dashboard/data/branches/'.$this->branch->id.'/details', [
            'name_ar' => 'فرع تجريبي جديد', 'name_en' => (string) $this->branch->name_en, 'is_public' => '1', 'reason' => 'تصحيح الاسم',
        ])->assertSessionHasNoErrors();

        $html = (string) $this->get('/dashboard/history')->assertOk()->getContent();
        $this->assertStringContainsString('حفظ تفاصيل فرع', $html, 'what, in plain Arabic');
        $this->assertStringContainsString('تفاصيل الفروع', $html, 'the area');
        $this->assertStringContainsString('فرع تجريبي جديد', $html, 'the item by name, and the new value');
        $this->assertStringContainsString(e($before), $html, 'the old value');
        $this->assertStringContainsString('الاسم (عربي)', $html, 'the field in words');
        $this->assertStringContainsString('كان:', $html);
        $this->assertStringContainsString('أصبح:', $html);
        $this->assertStringContainsString('Test Owner', $html, 'who');
        $this->assertStringContainsString('2026-10-10 12:00', $html, 'when, in Amman time');
        $this->assertStringContainsString('السبب: تصحيح الاسم', $html, 'why');
        $this->assertStringContainsString(route('dashboard.history.versions', ['branch', $this->branch->id]), $html, 'its versions');
    }

    public function test_the_screen_speaks_english_too(): void
    {
        config(['shelter.dashboard_locale' => 'en']);
        $this->put('/dashboard/data/branches/'.$this->branch->id.'/details', [
            'name_ar' => (string) $this->branch->name_ar, 'name_en' => 'Test branch name', 'is_public' => '1',
        ])->assertSessionHasNoErrors();
        $this->get('/dashboard/history')->assertOk()->assertSee('Change history')->assertSee('Branch details saved')
            ->assertSee('Name (English)')->assertSee('Was:')->assertSee('Now:')->assertSee('Test branch name')->assertDontSee('dashboard.history.');
        $this->get('/dashboard/history/versions/branch/'.$this->branch->id)->assertOk()->assertSee('Earlier versions')->assertSee('Version 1');
    }

    public function test_filters_by_area_period_and_access_stay_out_of_all(): void
    {
        $this->entry('hours.regular_published', ['before' => ['hours' => [[6, '08:00', '23:00']]], 'after' => ['hours' => [[6, '09:00', '23:00']]]], userId: $this->owner->id);
        $this->entry('redirects.created', ['after' => ['source_path' => '/old-page', 'target' => '/ar/']], daysAgo: 20, userId: $this->owner->id);
        $this->entry('auth.login', [], ['second_factor' => 'totp'], userId: $this->owner->id);
        $setting = app(Settings::class)->set('shaltoor.welcome.ar', 'أهلًا من الاختبار', $this->owner);

        $this->assertSame(1, $this->total(['area' => 'hours']));
        $this->assertSame(1, $this->total(['area' => 'redirects']));
        $this->assertSame(0, $this->total(['area' => 'redirects', 'period' => 'week']), 'twenty days ago is outside the last 7 days');
        $this->assertSame(1, $this->total(['area' => 'redirects', 'period' => 'month']));
        $this->assertSame(1, $this->total(['area' => 'access']));
        $this->assertSame(1, $this->total(['area' => 'shaltoor']), 'a Shaltoor setting is Shaltoor, not Settings');
        $this->assertSame(1, AuditLog::query()->where('action', 'settings.updated')->where('subject_id', $setting->id)->count());

        $all = (string) $this->get('/dashboard/history')->assertOk()->getContent();
        $this->assertStringNotContainsString('تسجيل دخول', $all, 'signing in is access, not a change');
        $this->assertStringContainsString('السبت 9:00', $all, 'a week of hours in words');
        $this->get('/dashboard/history?area=access')->assertOk()->assertSee('تسجيل دخول');
        $this->get('/dashboard/history?area=hours')->assertOk()->assertSee('نشر ساعات العمل')->assertDontSee('إضافة رابط قديم');
        $this->get('/dashboard/history?area=settings')->assertOk()->assertDontSee('أهلًا من الاختبار');
        $this->get('/dashboard/history?area=nonsense&period=forever')->assertOk();
    }

    public function test_secrets_and_internal_notes_are_never_shown(): void
    {
        // An older row written without the logger: the screen masks it again exactly as AuditLogger::mask does.
        $this->entry('settings.updated', ['before' => ['token' => 'tok-old-123', 'value' => 'a'], 'after' => ['token' => 'tok-new-456', 'value' => 'b']], ['api_key' => 'key-789']);
        $this->entry('note.edited', ['before' => ['body' => 'المتقدم قال رقم هاتفه'], 'after' => ['body' => 'ملاحظة معدلة']], ['reference' => 'JOB-2026-000001'], userId: $this->owner->id);
        $this->entry('auth.failed', [], ['email_hash' => 'hash-abc']);
        AuditLog::query()->where('action', 'auth.failed')->update(['ip_address' => '203.0.113.7']);

        $html = (string) $this->get('/dashboard/history')->assertOk()->getContent();
        $this->assertStringNotContainsString('tok-old-123', $html);
        $this->assertStringNotContainsString('tok-new-456', $html);
        $this->assertStringNotContainsString('key-789', $html);
        $this->assertStringContainsString('(مخفي لأسباب أمنية)', $html);
        $this->assertStringNotContainsString('المتقدم قال رقم هاتفه', $html, 'an internal note on an applicant stays on its application');
        $this->assertStringContainsString('(لا يُعرض هنا)', $html);
        $this->assertStringContainsString('JOB-2026-000001', $html, 'the request is named by its reference only');
        $access = (string) $this->get('/dashboard/history?area=access')->assertOk()->getContent();
        $this->assertStringNotContainsString('hash-abc', $access);
        $this->assertStringNotContainsString('203.0.113.7', $access, 'no IP address on this screen');
    }

    public function test_only_the_owner_sees_it_and_restoring_needs_a_fresh_confirmation(): void
    {
        $this->get('/dashboard/history')->assertOk()->assertSee('سجل التغييرات');
        $this->get('/dashboard')->assertSee(route('dashboard.history'), false);

        $this->withSession([OwnerSession::CONFIRMED_AT => null]);
        $this->get('/dashboard/history/restore/1')->assertRedirect(route('dashboard.confirm'));
        $this->post('/dashboard/history/restore/1', ['fingerprint' => 'x'])->assertRedirect(route('dashboard.confirm'));

        $noSecondFactor = User::factory()->create();
        $this->actingAs($noSecondFactor)->get('/dashboard/history')->assertRedirect(route('login'));
        auth()->logout();
        $this->get('/dashboard/history')->assertRedirect(route('login'));
        $this->get('/dashboard/history/versions/branch/'.$this->branch->id)->assertRedirect(route('login'));
    }

    public function test_the_list_is_paginated_newest_first(): void
    {
        $before = AuditLog::query()->whereNot('action', 'like', 'auth.%')->count();
        for ($i = 1; $i <= ChangeHistory::PER_PAGE + 5; $i++) {
            $this->entry('redirects.updated', ['before' => ['target' => '/ar/'], 'after' => ['target' => '/ar/r'.$i.'/']], userId: $this->owner->id);
            $this->travel(1)->minutes();
        }
        $page = app(ChangeHistory::class)->page(ChangeHistory::filters([]), 1);
        $this->assertSame($before + ChangeHistory::PER_PAGE + 5, $page->total());
        $first = (string) $this->get('/dashboard/history?area=redirects')->assertOk()->getContent();
        $this->assertStringContainsString('/ar/r35/', $first, 'newest first');
        $this->assertStringNotContainsString('/ar/r1/', $first);
        $this->assertStringContainsString('page=2', $first);
        $this->get('/dashboard/history?area=redirects&page=2')->assertOk()->assertSee('/ar/r1/')->assertDontSee('/ar/r35/');
    }

    public function test_an_items_screen_links_to_its_own_history(): void
    {
        $other = Branch::query()->whereKeyNot($this->branch->id)->firstOrFail();
        foreach ([$this->branch, $other] as $branch) {
            $this->put('/dashboard/data/branches/'.$branch->id.'/details', ['name_ar' => 'اسم '.$branch->code, 'name_en' => 'Name '.$branch->code, 'is_public' => '1'])->assertSessionHasNoErrors();
        }
        $this->post('/dashboard/data/branches/'.$this->branch->id.'/exceptions', [
            'kind' => 'holiday', 'starts_on' => '2026-10-20', 'ends_on' => '2026-10-20', 'mode' => 'closed', 'reason' => 'عطلة تجريبية', 'status' => 'published',
        ])->assertSessionHasNoErrors();

        $link = route('dashboard.history', ['item' => 'branch:'.$this->branch->id]);
        $this->get('/dashboard/data/branches/'.$this->branch->id)->assertOk()->assertSee($link, false)->assertSee('سجل تغييرات هذه الشاشة');
        $this->get('/dashboard/data/contacts')->assertOk()->assertSee(route('dashboard.history', ['area' => 'contacts']), false);

        $html = (string) $this->get($link)->assertOk()->getContent();
        $this->assertStringContainsString('اسم '.$this->branch->code, $html);
        $this->assertStringContainsString('إضافة ساعات خاصة أو إغلاق', $html, 'its hours exceptions belong to its history');
        $this->assertStringNotContainsString('اسم '.$other->code, $html, 'another branch is not in it');
        $this->assertStringContainsString(route('dashboard.history.versions', ['branch', $this->branch->id]), $html);
    }

    public function test_the_systems_own_changes_are_audited_with_the_system_as_actor(): void
    {
        // facts:expire-verified (scheduled daily): a VERIFIED fact past its date falls back to APPROVED — audited.
        $facts = app(FactRegistry::class);
        $fact = $facts->register('test.expiring', 'brand', 'قيمة', FactStatus::PendingOwnerApproval, FactSource::OwnerDashboard);
        $facts->approve($fact, $this->owner, 'TEST');
        $facts->verify($fact->refresh(), $this->owner, 'evidence', now()->subDay());
        $this->assertSame(1, $facts->expireVerified());
        $log = AuditLog::query()->where('action', 'facts.verification_expired')->sole();
        $this->assertNull($log->user_id, 'never attributed to whoever is signed in');
        $this->assertSame(['actor_type' => AuditLogger::SYSTEM, 'job' => 'facts:expire-verified'], array_intersect_key($log->meta ?? [], array_flip(['actor_type', 'job'])));
        $this->assertSame(['before' => ['status' => 'VERIFIED'], 'after' => ['status' => 'APPROVED']], $log->changes);
        $this->get('/dashboard/history?area=facts')->assertOk()->assertSee('النظام — مهمة facts:expire-verified')->assertSee('انتهت صلاحية توثيق معلومة');

        // Shaltoor's retention clean-up (monitors:daily) and the unsent careers uploads (careers:prune-drafts).
        ShaltoorQuestion::query()->create(['locale' => 'ar', 'topic' => 'unknown', 'answered' => false, 'question' => 'سؤال قديم', 'normalized' => 'سؤال قديم', 'page' => 'default', 'created_at' => now()->subDays(120)]);
        $this->assertSame(1, app(ShaltoorLog::class)->prune());
        $this->assertSame(1, AuditLog::query()->where('action', 'shaltoor.questions_pruned')->whereNull('user_id')->count());
        $this->assertSame(0, app(ShaltoorLog::class)->prune());
        $this->assertSame(1, AuditLog::query()->where('action', 'shaltoor.questions_pruned')->count(), 'a run that removed nothing adds no line');
        Storage::fake('careers');
        $this->assertSame(0, Artisan::call('careers:prune-drafts'));
        $this->assertSame(0, AuditLog::query()->where('action', 'careers.drafts_pruned')->count(), 'nothing to remove, nothing to say');
        UploadSession::query()->create(['expires_at' => now()->subHour()]);
        $this->assertSame(0, Artisan::call('careers:prune-drafts'));
        $pruned = AuditLog::query()->where('action', 'careers.drafts_pruned')->sole();
        $this->assertSame(['files' => 0, 'sessions' => 1, 'actor_type' => 'system', 'job' => 'careers:prune-drafts'], $pruned->meta);

        // An image added on the server (media:import) is in the history too.
        Storage::fake('media');
        Storage::fake('media_public');
        $this->assertSame(0, Artisan::call('media:import', ['file' => MediaLibraryTest::imageFile(seed: 3), '--source' => 'shelter', '--people' => 'none']));
        $import = AuditLog::query()->where('action', 'media.imported')->sole();
        $this->assertNull($import->user_id);
        $this->assertSame('media:import', $import->meta['job'] ?? null);
        $this->get('/dashboard/history?area=media')->assertOk()->assertSee('إضافة صورة من الخادم')->assertSee('النظام — مهمة media:import');
    }
}
