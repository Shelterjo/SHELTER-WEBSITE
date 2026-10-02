<?php

namespace Tests\Feature\Dashboard;

use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FINAL-QA QA-044 — two tabs, one record: the save from the tab opened BEFORE the latest change is refused with an
 * explanation (what was typed is kept); the newer change stays. A save from a fresh page, or without the script, works.
 */
class StaleEditTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->product = Product::query()->where('code', 'PRD-00003')->firstOrFail();
    }

    public function test_a_save_from_an_older_tab_never_overwrites_a_newer_change(): void
    {
        $url = '/dashboard/data/menu/'.$this->product->id.'/details';
        $this->get('/dashboard/data/menu/'.$this->product->id)->assertOk()->assertSee('data-ui-rendered-at="'.now()->getTimestamp().'"', false);
        $tabA = (string) now()->getTimestamp(); // tab A opened now

        $this->travel(2)->minutes();
        $this->put($url, ['visible' => '1', 'description_ar' => 'نص Newer text from tab B', 'description_en' => 'Newer text from tab B', '_seen_at' => (string) now()->getTimestamp()])->assertSessionHasNoErrors();
        $this->assertSame('Newer text from tab B', $this->product->refresh()->description_en);

        $this->travel(1)->minutes();
        $this->put($url, ['visible' => '1', 'description_ar' => 'نص Older text from tab A', 'description_en' => 'Older text from tab A', '_seen_at' => $tabA])
            ->assertRedirect()->assertSessionHas('warning', __('dashboard.stale_edit'));
        $this->assertSame('Newer text from tab B', $this->product->refresh()->description_en, 'the newer change is kept');
        $this->assertSame('Older text from tab A', session()->getOldInput('description_en'), 'what was typed is not lost');

        // Reopened page (a fresh moment): the save goes through. Without the script (no moment) it goes through too.
        $this->put($url, ['visible' => '1', 'description_ar' => 'نص Edited again', 'description_en' => 'Edited again', '_seen_at' => (string) now()->getTimestamp()])->assertSessionMissing('warning');
        $this->assertSame('Edited again', $this->product->refresh()->description_en);
        $this->put($url, ['visible' => '1', 'description_ar' => 'نص No script', 'description_en' => 'No script'])->assertSessionMissing('warning');
        $this->assertSame('No script', $this->product->refresh()->description_en);
    }

    public function test_brand_pages_addressed_by_key_are_protected_too(): void
    {
        $page = Page::query()->create(['key' => 'about', 'type' => 'about', 'title_ar' => 'عنا', 'title_en' => 'About', 'status' => 'draft']);
        $old = (string) now()->subMinute()->getTimestamp();
        $this->put('/dashboard/content/pages/about', ['title_en' => 'Stale', '_seen_at' => $old])->assertSessionHas('warning', __('dashboard.stale_edit'));
        $this->assertSame('About', $page->refresh()->title_en);
    }
}
