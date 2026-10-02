<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\SiteText;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Content\SiteTexts;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Content → Site texts (M50): the Owner rewords listed fixed texts, page titles and Google descriptions — no code. */
class SiteTextsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    private function page(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->assertOk()->getContent();
    }

    public function test_the_owners_wording_shows_on_the_site_and_empty_brings_the_original_back(): void
    {
        $original = SiteTexts::original('site.home.lead', 'ar');
        $this->get('/dashboard/content/texts')->assertOk()->assertSee('الصفحة الرئيسية')->assertSee('السطر تحت الشعار')->assertSee($original);
        $approved = SiteTexts::original('site.meta.home', 'ar');
        $this->assertStringContainsString('<meta name="description" content="'.e($approved).'">', $this->page('/ar/'), 'the approved description by default (D-332)');

        $this->put('/dashboard/content/texts/home', ['texts' => [
            'site.home.lead' => ['ar' => 'قهوة مختصة في إربد — جرّبها اليوم.', 'en' => ''],
            'site.meta.home' => ['ar' => 'وصف تجريبي للصفحة الرئيسية.', 'en' => ''],
            'site.brand' => ['ar' => 'اسم آخر'], // not a listed text: ignored
        ]])->assertSessionHas('status', 'تغيّر نصان.');
        $ar = $this->page('/ar/');
        $this->assertStringContainsString('قهوة مختصة في إربد — جرّبها اليوم.', $ar);
        $this->assertStringContainsString('<meta name="description" content="وصف تجريبي للصفحة الرئيسية.">', $ar);
        $this->assertStringNotContainsString(e($approved), $ar, 'the Owner\'s new wording replaces the approved one');
        $this->assertStringContainsString(SiteTexts::original('site.home.lead', 'en'), $this->page('/en/'), 'each language on its own');
        $this->assertStringNotContainsString('اسم آخر', $ar);
        $this->get('/dashboard/content/texts')->assertSee('معدّل');

        $this->put('/dashboard/content/texts/home', ['texts' => ['site.home.lead' => ['ar' => '', 'en' => ''], 'site.meta.home' => ['ar' => 'وصف تجريبي للصفحة الرئيسية.', 'en' => '']]]);
        $this->assertStringContainsString($original, $this->page('/ar/'), 'empty = the original wording');
        $this->assertSame(1, SiteText::query()->where('key', 'site.home.lead')->count(), 'the row stays (history), its value is empty');
        $this->assertNull(SiteText::query()->where('key', 'site.home.lead')->value('value'));
        $this->assertSame(2, AuditLog::query()->where('action', 'texts.saved')->count());
    }

    public function test_placeholders_stay_and_page_titles_follow(): void
    {
        $this->put('/dashboard/content/texts/locations', ['texts' => ['site.branch.title' => ['ar' => 'فرع بدون اسم', 'en' => '']]])
            ->assertSessionHasErrors([SiteTexts::fieldId('site.branch.title', 'ar')], null, 'texts-locations');
        $this->put('/dashboard/content/texts/locations', ['texts' => [
            'site.branch.title' => ['ar' => ':name — شلتر كوفي إربد', 'en' => ''],
            'site.locations.title' => ['ar' => 'فروعنا', 'en' => 'Our branches'],
        ]])->assertSessionHasNoErrors();
        $this->assertStringContainsString('<title>فروعنا — ', $this->page('/ar/jo/locations/'));
        $this->assertStringContainsString('<title>Our branches — ', $this->page('/en/jo/locations/'));
        $this->assertMatchesRegularExpression('#<title>[^<]+ — شلتر كوفي إربد</title>#u', $this->page('/ar/jo/locations/irbid/drive/'));
    }

    public function test_a_missing_table_never_breaks_the_site(): void
    {
        SiteTexts::flush();
        Schema::rename('site_texts', 'site_texts_broken');
        try {
            $this->assertSame([], SiteTexts::overrides());
            $this->assertStringContainsString(SiteTexts::original('site.home.lead', 'ar'), $this->page('/ar/'));
        } finally {
            Schema::rename('site_texts_broken', 'site_texts');
        }
    }
}
