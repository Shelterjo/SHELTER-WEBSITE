<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\InterviewLocation;
use App\Models\Recruitment\JordanCity;
use App\Models\Recruitment\UploadSession;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Recruitment\ApplicationSubmitter;
use App\Services\Recruitment\ApplicationValidator;
use App\Services\Recruitment\AttachmentStore;
use App\Services\Recruitment\CareersForm;
use App\Services\Requests\RecruitmentSettings;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Careers\CareersTestHelpers;
use Tests\TestCase;

/** Dashboard → Job applications tools (CAREERS-058…075): saved searches, table view, quick view, waiting, settings. */
class CareersListToolsTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->bootCareers();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    /** @param  array<string, mixed>  $overrides */
    private function apply(array $overrides = []): Application
    {
        $result = app(ApplicationValidator::class)->validate($this->validInput($overrides), [$this->cityId()], CarbonImmutable::parse('2026-10-02'));
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $cv = app(AttachmentStore::class)->store($session, $this->pdf());
        $this->assertInstanceOf(ApplicationAttachment::class, $cv);

        return app(ApplicationSubmitter::class)->submit($result['data'], $session, $cv->id, (string) Str::uuid(), app(CareersForm::class)->consent() ?? throw new \RuntimeException);
    }

    public function test_the_table_view_and_rows_per_page_are_remembered_per_owner(): void
    {
        $this->apply();
        $html = (string) $this->get('/dashboard/requests/careers?period=all')->getContent();
        $this->assertStringContainsString('>الوظيفة</th>', $html);
        $this->assertStringNotContainsString('>الهاتف</th>', $html);

        $this->post('/dashboard/requests/careers/view', ['shown' => ['job', 'phone'], 'density' => 'compact', 'move' => 'phone:up', 'back' => 'https://evil.example/'])
            ->assertRedirect('/dashboard/requests/careers');
        $html = (string) $this->get('/dashboard/requests/careers?period=all')->getContent();
        $this->assertMatchesRegularExpression('#>الهاتف</th>\s*<th[^>]*>الوظيفة</th>#u', $html, 'shown, in the chosen order');
        $this->assertStringNotContainsString('>المدينة</th>', $html);
        $this->assertStringContainsString('ui-inbox-table--compact', $html);
        $this->assertStringNotContainsString('dashboard.requests.', $html, 'every label has its text');
        $this->assertStringNotContainsString('dashboard.requests.', (string) $this->get('/dashboard/requests/careers/settings')->getContent());

        $this->post('/dashboard/requests/careers/view', ['reset' => '1']);
        $this->assertStringContainsString('>المدينة</th>', (string) $this->get('/dashboard/requests/careers?period=all')->getContent());

        $this->get('/dashboard/requests/careers?period=all&per=25');
        $this->assertStringContainsString('<option value="25" selected', (string) $this->get('/dashboard/requests/careers?period=all')->getContent(), 'the last choice is remembered');
    }

    public function test_a_search_is_saved_under_a_name_and_opens_in_one_press(): void
    {
        $this->post('/dashboard/requests/careers/filters', ['name' => '', 'filters' => ['q' => 'barista']])->assertSessionHasErrors(['saved_name']);
        $this->post('/dashboard/requests/careers/filters', ['name' => 'باريستا إربد', 'filters' => ['q' => 'barista', 'gender' => 'female', 'per' => '100']])
            ->assertRedirect('/dashboard/requests/careers?q=barista&gender=female&sort=newest&period=month');
        $html = (string) $this->get('/dashboard/requests/careers')->getContent();
        $this->assertStringContainsString('باريستا إربد', $html);
        $this->assertStringContainsString('q=barista', $html);

        $id = (int) DB::table('recruitment_saved_filters')->value('id');
        $other = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create(['email' => 'second@example.test']);
        $this->actingAs($other)->delete('/dashboard/requests/careers/filters/'.$id);
        $this->assertSame(1, DB::table('recruitment_saved_filters')->count(), "nobody deletes someone else's search");
        $this->actingAs($this->owner)->delete('/dashboard/requests/careers/filters/'.$id);
        $this->assertSame(0, DB::table('recruitment_saved_filters')->count());
    }

    public function test_the_quick_view_shows_the_essentials_and_counts_as_opened(): void
    {
        $application = $this->apply();
        $this->get('/dashboard/requests/careers/'.$application->id.'/quick')->assertRedirect('/dashboard/requests/careers/'.$application->id);
        $html = (string) $this->withHeader('X-Quick-View', '1')->get('/dashboard/requests/careers/'.$application->id.'/quick')->assertOk()->getContent();
        $this->assertStringContainsString('data-quick-body', $html);
        $this->assertStringContainsString('+962791234567', $html);
        $this->assertStringNotContainsString('9991234567', $html, 'never the identity number');
        $this->assertNotNull($application->refresh()->first_viewed_at);
    }

    public function test_waiting_applications_are_flagged_never_deleted_and_the_lists_are_editable(): void
    {
        $application = $this->apply();
        Application::query()->whereKey($application->id)->update(['updated_at' => now()->subDays(20)]);
        $this->assertStringContainsString('ينتظر منذ مدة', (string) $this->get('/dashboard/requests/careers?period=all')->getContent());
        $this->put('/dashboard/requests/careers/settings/stale', ['stale_days' => '0'])->assertSessionHasErrors(['stale_days'], null, 'stale');
        $this->put('/dashboard/requests/careers/settings/stale', ['stale_days' => '30'])->assertSessionHasNoErrors();
        $this->assertSame(30, RecruitmentSettings::staleDays());
        $this->assertStringNotContainsString('ينتظر منذ مدة', (string) $this->get('/dashboard/requests/careers?period=all')->getContent());
        $this->assertSame(1, Application::query()->count());

        $this->get('/dashboard/requests/careers/settings')->assertOk()->assertSee('أماكن المقابلة');
        $this->post('/dashboard/requests/careers/settings/locations', ['name_ar' => 'فرع جديد', 'name_en' => ''])->assertSessionHasErrors(['name_en'], null, 'location-new');
        $this->post('/dashboard/requests/careers/settings/locations', ['name_ar' => 'مكتب الإدارة', 'name_en' => 'Head office'])->assertSessionHasNoErrors();
        $place = InterviewLocation::query()->where('name_en', 'Head office')->firstOrFail();
        $this->put('/dashboard/requests/careers/settings/locations/'.$place->id, ['name_ar' => 'مكتب الإدارة', 'name_en' => 'Head office']);
        $this->assertFalse($place->refresh()->is_active, 'switched off, not deleted');

        $city = JordanCity::query()->findOrFail($this->cityId());
        $this->put('/dashboard/requests/careers/settings/cities/'.$city->id, ['is_active' => '']);
        $this->assertFalse($city->refresh()->is_active);
        $this->post('/dashboard/requests/careers/settings/cities', ['name_ar' => $city->name_ar])->assertSessionHasErrors(['city_name_ar'], null, 'city-new');
        $this->assertSame(1, Application::query()->whereHas('job', fn ($q) => $q->where('city_id', $city->id))->count(), 'older applications keep their city');
        $this->assertSame(['careers.settings_changed', 'careers.location_saved', 'careers.location_saved', 'careers.city_switched'],
            AuditLog::query()->where('action', 'like', 'careers.%')->orderBy('id')->pluck('action')->all());
    }
}
