<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\ContentVersion;
use App\Models\MenuCategory;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SiteText;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Content\SiteTexts;
use App\Services\Core\Settings;
use App\Services\Core\Versions;
use App\Services\Dashboard\OwnerApproval;
use App\Services\Dashboard\PageEditor;
use App\Services\Dashboard\VersionRestore;
use App\Services\MasterData\FactRegistry;
use App\Services\Menu\MenuEditor;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Restore an earlier version (AUDIT-004 "when safe", AUDIT-008, ROLLBACK.md RB-T5): previewed first, then saved as a
 * NEW version through the item's own rules — blocked phrases, both languages, the approval of business facts — with
 * one audit line "restored from version N". History is never rewritten; a change after the preview stops it.
 */
class VersionRestoreTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    /** @return array<string, mixed> */
    private function about(string $title, string $extra = '', ?int $storyId = null): array
    {
        $sections = [['id' => $storyId, 'type' => 'text', 'heading_ar' => 'قصتنا', 'heading_en' => 'Our story', 'body_ar' => 'نص القصة.', 'body_en' => 'The story.', 'visible' => '1']];
        if ($extra !== '') {
            $sections[] = ['type' => 'text', 'heading_ar' => $extra, 'heading_en' => 'More', 'body_ar' => 'نص إضافي.', 'body_en' => 'More text.', 'visible' => '1'];
        }

        return ['status' => 'published', 'title_ar' => $title, 'title_en' => 'About SHELTER', 'sections' => $sections];
    }

    /** @return list<ContentVersion> */
    private function versionsOf(Model $item): array
    {
        return array_values(ContentVersion::query()->where('versionable_type', $item->getMorphClass())->where('versionable_id', $item->getKey())->orderBy('version')->get()->all());
    }

    /** @return TestResponse<Response> */
    private function restore(ContentVersion $version, ?string $fingerprint = null, string $note = ''): TestResponse
    {
        // What the preview would have shown: the version as the request loads it (its item fresh from the database).
        $fingerprint ??= app(VersionRestore::class)->fingerprint($version->fresh() ?? $version);

        return $this->post('/dashboard/history/restore/'.$version->id, ['fingerprint' => $fingerprint, 'note' => $note]);
    }

    /**
     * Refused: back on the preview, which says why.
     *
     * @param  TestResponse<Response>  $response
     */
    private function assertRefused(TestResponse $response, ContentVersion $version, string $message): void
    {
        $response->assertRedirect(route('dashboard.history.restore', $version));
        $this->get(route('dashboard.history.restore', $version))->assertOk()->assertSee(__('dashboard.history.errors.title'))->assertSee($message);
    }

    public function test_a_page_comes_back_as_a_new_version_and_history_is_never_rewritten(): void
    {
        $editor = app(PageEditor::class);
        $this->assertSame([], $editor->save('about', $this->about('عن شلتر — الأولى'), $this->owner));
        $page = Page::query()->where('key', 'about')->firstOrFail();
        $story = (int) $page->sections()->value('id');
        $this->assertSame([], $editor->save('about', $this->about('عن شلتر — الثانية', 'قسم أضيف لاحقًا', $story), $this->owner));
        [$first, $second] = $this->versionsOf($page);
        $snapshots = [$first->snapshot, $second->snapshot];

        $this->get('/dashboard/history/versions/page/'.$page->id)->assertOk()
            ->assertSee(__('dashboard.history.version', ['version' => 1]))->assertSee(__('dashboard.history.latest'))
            ->assertSee(route('dashboard.history.restore', $first), false);
        $this->get('/dashboard/history/restore/'.$first->id)->assertOk()
            ->assertSee('عن شلتر — الأولى')->assertSee('عن شلتر — الثانية')->assertSee('قسم أضيف لاحقًا')
            ->assertSee(__('dashboard.history.restore_button'))->assertSee('name="fingerprint"', false);

        $this->restore($first, note: 'العنوان الأول أوضح')
            ->assertRedirect(route('dashboard.history.versions', ['page', $page->id]))
            ->assertSessionHas('status', __('dashboard.history.restored', ['from' => 1, 'to' => 3]));

        $page->refresh();
        $this->assertSame('عن شلتر — الأولى', $page->title_ar);
        $this->assertSame('published', $page->status->value, 'the page keeps its current state');
        $this->assertCount(1, $page->sections()->whereNull('archived_at')->get());
        $this->assertSame(1, $page->sections()->whereNotNull('archived_at')->count(), 'the later section is archived, never deleted');
        $versions = $this->versionsOf($page);
        $this->assertCount(3, $versions, 'a NEW version');
        $this->assertSame($snapshots, [$versions[0]->snapshot, $versions[1]->snapshot], 'earlier versions untouched');
        $this->assertSame('استعادة النسخة 1 — العنوان الأول أوضح', $versions[2]->reason);

        $log = AuditLog::query()->where('action', 'pages.restored')->sole();
        $this->assertSame($this->owner->id, $log->user_id);
        $this->assertSame(1, $log->meta['from_version'] ?? null);
        $this->assertSame(3, $log->meta['new_version'] ?? null);
        $this->assertSame('عن شلتر — الأولى', $log->changes['after']['title_ar'] ?? null);
        $this->assertSame('عن شلتر — الثانية', $log->changes['before']['title_ar'] ?? null);
        $this->get('/dashboard/history?area=pages')->assertOk()->assertSee('استعادة نسخة سابقة من صفحة')
            ->assertSee('السبب: استعادة النسخة 1 — العنوان الأول أوضح');
        $this->get('/ar/about/')->assertOk()->assertSee('عن شلتر — الأولى');
    }

    public function test_a_restore_meets_the_same_publishing_rules_as_a_save(): void
    {
        $editor = app(PageEditor::class);
        $this->assertSame([], $editor->save('about', $this->about('عن شلتر'), $this->owner));
        $page = Page::query()->where('key', 'about')->firstOrFail();
        // An older version that holds a phrase blocked since (D-018: an old founding year is never published again).
        $old = app(Versions::class)->record($page, 'published', [
            'page' => ['key' => 'about', 'type' => 'brand', 'title_ar' => 'شلتر كوفي منذ 2018', 'title_en' => 'About SHELTER', 'name_ar' => null, 'name_en' => null, 'description_ar' => null, 'description_en' => null],
            'sections' => [['id' => null, 'type' => 'text', 'heading_ar' => 'قصتنا', 'heading_en' => 'Our story', 'body_ar' => 'نص.', 'body_en' => 'Text.', 'is_visible' => true, 'media_id' => null, 'sort' => 1]],
        ], 'dashboard', $this->owner);
        $this->assertSame([], $editor->save('about', $this->about('عن شلتر — أحدث'), $this->owner));
        $count = count($this->versionsOf($page));

        $this->assertRefused($this->restore($old), $old, __('dashboard.pages.errors.blocked', ['phrase' => 'منذ 2018']));
        $this->assertSame('عن شلتر — أحدث', $page->refresh()->title_ar);
        $this->assertCount($count, $this->versionsOf($page), 'nothing saved');
        $this->assertSame(0, AuditLog::query()->where('action', 'pages.restored')->count());

        // The franchise page's own phrases (FRAN-097) hold for a restore too.
        $franchise = $this->about('الفرنشايز');
        $this->assertSame([], $editor->save('franchise', $franchise, $this->owner));
        $fr = Page::query()->where('key', 'franchise')->firstOrFail();
        $claim = app(Versions::class)->record($fr, 'published', [
            'page' => ['key' => 'franchise', 'title_ar' => 'فرصة العمر مع شلتر', 'title_en' => 'Franchise'],
            'sections' => [['type' => 'text', 'heading_ar' => 'عنوان', 'heading_en' => 'Heading', 'body_ar' => 'نص.', 'body_en' => 'Text.', 'is_visible' => true]],
        ], 'dashboard', $this->owner);
        $this->assertSame([], $editor->save('franchise', $this->about('الفرنشايز — أحدث'), $this->owner));
        $this->assertRefused($this->restore($claim), $claim, __('dashboard.pages.errors.blocked', ['phrase' => 'فرصة العمر']));
    }

    public function test_a_change_after_the_preview_stops_the_restore(): void
    {
        $editor = app(PageEditor::class);
        $editor->save('about', $this->about('الأولى'), $this->owner);
        $editor->save('about', $this->about('الثانية'), $this->owner);
        $page = Page::query()->where('key', 'about')->firstOrFail();
        $first = $this->versionsOf($page)[0];
        $seen = app(VersionRestore::class)->fingerprint($first);
        $editor->save('about', $this->about('الثالثة من نافذة أخرى'), $this->owner);

        $this->assertRefused($this->restore($first, $seen), $first, __('dashboard.history.errors.changed'));
        $this->assertSame('الثالثة من نافذة أخرى', $page->refresh()->title_ar);

        // The version that is live now has nothing to restore.
        $latest = $this->versionsOf($page)[2];
        $this->assertRefused($this->restore($latest), $latest, __('dashboard.history.errors.same'));
        $this->get('/dashboard/history/restore/'.$latest->id)->assertOk()->assertSee(__('dashboard.history.errors.same'))->assertDontSee('name="fingerprint"', false);
    }

    public function test_a_site_text_and_its_google_description_come_back_without_touching_the_others(): void
    {
        $this->put('/dashboard/content/texts/home', ['texts' => ['site.meta.home' => ['ar' => 'وصف أول للصفحة الرئيسية.'], 'site.home.cta_menu' => ['en' => 'See the menu']]])->assertSessionHasNoErrors();
        $this->put('/dashboard/content/texts/home', ['texts' => ['site.meta.home' => ['ar' => 'وصف ثان للصفحة الرئيسية.'], 'site.home.cta_menu' => ['en' => 'See the menu']]])->assertSessionHasNoErrors();
        $row = SiteText::query()->where('key', 'site.meta.home')->where('locale', 'ar')->firstOrFail();
        $first = $this->versionsOf($row)[0];

        $this->get('/dashboard/history/restore/'.$first->id)->assertOk()->assertSee('وصف أول للصفحة الرئيسية.');
        $this->restore($first)->assertSessionHasNoErrors()->assertSessionHas('status');
        $this->assertSame('وصف أول للصفحة الرئيسية.', SiteTexts::current('site.meta.home', 'ar'));
        $this->assertSame('See the menu', SiteTexts::current('site.home.cta_menu', 'en'), 'the rest of the page’s texts stay as they are');
        $this->assertCount(3, $this->versionsOf($row));
        $this->assertSame(1, AuditLog::query()->where('action', 'texts.restored')->where('subject_id', $row->id)->count());
        $this->get('/dashboard/history?item=text:'.$row->id)->assertOk()->assertSee('استعادة نسخة سابقة من نص')->assertSee('حفظ نصوص الموقع');
    }

    public function test_the_founding_year_comes_back_through_the_owners_approval(): void
    {
        $this->put('/dashboard/settings', ['founded_year' => '2019'])->assertSessionHasNoErrors();
        $this->put('/dashboard/settings', ['founded_year' => '2020'])->assertSessionHasNoErrors();
        $setting = Setting::query()->where('key', 'brand.founded_year')->firstOrFail();
        $versions = $this->versionsOf($setting);
        $year2019 = collect($versions)->first(fn (ContentVersion $v): bool => ($v->snapshot['value'] ?? null) === 2019);
        $this->assertInstanceOf(ContentVersion::class, $year2019);
        $superseded = AuditLog::query()->where('action', 'facts.superseded')->count();

        $this->restore($year2019)->assertSessionHasNoErrors();
        $this->assertSame(2019, app(Settings::class)->get('brand.founded_year'));
        $fact = app(FactRegistry::class)->current('brand.founded_year');
        $this->assertNotNull($fact);
        $this->assertTrue($fact->status->isPublishable());
        $this->assertSame($this->owner->id, $fact->approved_by, 'approved in the Owner’s name, as a normal save');
        $this->assertTrue(app(OwnerApproval::class)->isApproved('brand.founded_year', 2019));
        $this->assertSame($superseded + 1, AuditLog::query()->where('action', 'facts.superseded')->count(), 'through the approval path');
        $this->assertSame(1, AuditLog::query()->where('action', 'settings.restored')->count());
        $this->get('/dashboard/history?area=settings')->assertOk()->assertSee('استعادة نسخة سابقة من إعداد')->assertSee('سنة التأسيس');
    }

    public function test_branch_details_come_back_approved_and_services_are_left_alone(): void
    {
        $branch = Branch::query()->where('slug', 'drive')->firstOrFail();
        $form = fn (string $name): array => ['name_ar' => $name, 'name_en' => (string) $branch->name_en, 'address_ar' => 'عنوان '.$name, 'is_public' => '1'];
        $this->put('/dashboard/data/branches/'.$branch->id.'/details', $form('الاسم الأول'))->assertSessionHasNoErrors();
        $this->put('/dashboard/data/branches/'.$branch->id.'/details', $form('الاسم الثاني') + ['attributes' => ['service' => ['wifi' => 'yes']]])->assertSessionHasNoErrors();
        $first = $this->versionsOf($branch)[0];

        $this->get('/dashboard/history/restore/'.$first->id)->assertOk()->assertSee('الاسم الأول')->assertSee(__('dashboard.history.notes.fact'));
        $this->restore($first)->assertSessionHasNoErrors();
        $branch->refresh();
        $this->assertSame('الاسم الأول', $branch->name_ar);
        $this->assertSame('عنوان الاسم الأول', $branch->address_ar);
        $this->assertTrue(app(OwnerApproval::class)->isApproved($branch->factKey('name_ar'), 'الاسم الأول'), 'the restored name is the approved one the site shows');
        $this->assertTrue((bool) $branch->branchAttributes()->where('group', 'service')->where('key', 'wifi')->value('value'), 'services stay as they are');
        $this->assertSame(1, AuditLog::query()->where('action', 'branch.details_restored')->count());
    }

    public function test_a_price_comes_back_from_today_and_never_under_a_newer_price(): void
    {
        $product = Product::query()->whereHas('prices', fn ($q) => $q->whereNull('valid_to'))->firstOrFail();
        $menu = app(MenuEditor::class);
        $menu->changeBasePrice($product, 3250, $this->owner, 'سعر تجريبي أول');
        $menu->changeBasePrice($product, 3500, $this->owner, 'سعر تجريبي ثان');
        $old = collect($this->versionsOf($product))->first(fn (ContentVersion $v): bool => ($v->snapshot['price_fils'] ?? null) === 3250);
        $this->assertInstanceOf(ContentVersion::class, $old);
        $this->travel(1)->days();
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);

        $this->get('/dashboard/history/restore/'.$old->id)->assertOk()->assertSee('3.25')->assertSee('3.50');
        $this->restore($old)->assertSessionHasNoErrors();
        $current = $product->prices()->whereNull('valid_to')->firstOrFail();
        $this->assertSame(3250, $current->price_fils);
        $this->assertSame(now('Asia/Amman')->toDateString(), $current->valid_from->toDateString(), 'from today');
        $this->assertSame(1, $product->prices()->where('price_fils', 3500)->count(), 'the replaced price stays in the history');
        $this->assertSame(1, AuditLog::query()->where('action', 'menu.price_restored')->count());

        // A newer price already set to start later: an earlier price never lands under it.
        $menu->changeBasePrice($product, 4000, $this->owner, 'سعر قادم', CarbonImmutable::now('Asia/Amman')->addDays(5)->startOfDay());
        $this->assertRefused($this->restore($old), $old, __('dashboard.history.errors.newer_price'));
        $this->assertSame(1, AuditLog::query()->where('action', 'menu.price_restored')->count());
    }

    public function test_versions_that_cannot_come_back_say_why(): void
    {
        $category = MenuCategory::query()->firstOrFail();
        $version = app(Versions::class)->record($category, 'published', ['code' => $category->code, 'name_ar' => 'قسم'], null, $this->owner);
        $this->assertSame('unsupported', app(VersionRestore::class)->blocker($version));
        $this->assertRefused($this->restore($version), $version, __('dashboard.history.blocked.unsupported'));

        $setting = app(Settings::class)->set('shaltoor.welcome.ar', 'أهلًا', $this->owner);
        $masked = app(Versions::class)->record($setting, 'published', ['key' => 'shaltoor.welcome.ar', 'value' => 'أهلًا', 'token' => 'abc'], null, $this->owner);
        $this->assertSame('masked', app(VersionRestore::class)->blocker($masked));
        app(Settings::class)->set('shaltoor.welcome.ar', 'أهلًا بكم', $this->owner);
        $this->get('/dashboard/history/versions/setting/'.$setting->id)->assertOk()->assertSee(__('dashboard.history.blocked.masked'));
        $this->get('/dashboard/history/versions/nothing/1')->assertNotFound();
        $this->get('/dashboard/history/versions/page/999999')->assertNotFound();
    }
}
