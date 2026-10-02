<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Priority;
use App\Enums\Severity;
use App\Enums\SignalCategory;
use App\Enums\SignalKind;
use App\Models\Branch;
use App\Models\Media;
use App\Models\Signal;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Core\Attention;
use App\Services\Core\Signals;
use App\Services\Dashboard\OwnerApproval;
use App\Services\MasterData\FactRegistry;
use App\Services\Media\MediaLibrary;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/** Needs attention (MON-007, DASH-017, OPS-048): open issues shown with their fix, closed by the fix itself. */
class AttentionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        Storage::fake('media_public');
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    public function test_a_value_hidden_by_an_unapproved_change_is_shown_with_its_screen_and_closes_on_approval(): void
    {
        $this->get('/dashboard')->assertOk()->assertDontSee(__('dashboard.attention.fix'));

        $drive = Branch::query()->where('code', 'BR-DRIVE')->firstOrFail();
        $facts = app(FactRegistry::class);
        $this->assertFalse($facts->isPublishable($drive->factKey('name_ar'), 'اسم غيّره أحد مباشرة في قاعدة البيانات'));

        $html = (string) $this->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('قيمة تغيّرت بلا اعتماد', $html);
        $this->assertStringContainsString(route('dashboard.branches.show', $drive), $html, 'the issue links to the screen that fixes it');
        $this->get('/dashboard/attention')->assertOk()->assertSee('قيمة تغيّرت بلا اعتماد');
        $this->assertSame(1, app(Attention::class)->count());

        // Action-required issues cannot be hidden; approving the value closes them.
        $signal = Signal::query()->firstOrFail();
        $this->post("/dashboard/attention/{$signal->id}/dismiss")->assertSessionHas('status', __('dashboard.attention.not_dismissible'));
        app(OwnerApproval::class)->approve($drive->factKey('name_ar'), 'اسم معتمد جديد', $this->owner, 'branch');
        $this->assertSame(0, app(Attention::class)->count());
    }

    public function test_image_rights_ending_soon_raise_an_issue_that_renewal_closes(): void
    {
        $media = app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: 7), ['source' => 'shelter', 'people_consent' => 'none', 'alt_ar' => 'صورة', 'alt_en' => 'Photo']);
        $media->forceFill(['approval_status' => Media::APPROVED, 'approved_at' => now(), 'ok_website' => true, 'rights_expires_at' => now()->addDays(10)])->save();

        app(Attention::class)->runMonitors();
        $items = app(Attention::class)->open('ar');
        $this->assertCount(1, $items);
        $this->assertSame(route('dashboard.media.edit', $media), $items[0]['href']);

        $media->forceFill(['rights_expires_at' => now()->addYear()])->save();
        app(Attention::class)->runMonitors();
        $this->assertSame(0, app(Attention::class)->count());
    }

    public function test_information_can_be_hidden_and_outsiders_never_see_the_list(): void
    {
        $info = app(Signals::class)->raise(SignalKind::Issue, SignalCategory::Content, Severity::Info, Priority::Information, 'test', 'معلومة', 'Info');
        $this->post("/dashboard/attention/{$info->id}/dismiss")->assertSessionHas('status', __('dashboard.attention.dismissed'));
        $this->assertSame(0, app(Attention::class)->count());

        auth()->logout();
        $this->flushSession();
        $this->get('/dashboard/attention')->assertRedirect('/dashboard/login');
    }
}
