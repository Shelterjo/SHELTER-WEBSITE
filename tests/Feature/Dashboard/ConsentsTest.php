<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Recruitment\ConsentVersion;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Franchise\FranchiseForm;
use App\Services\Recruitment\CareersForm;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Settings → Consent texts: a change is a new version; the old one stays as it was (each application keeps its own). */
class ConsentsTest extends TestCase
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

    public function test_a_new_version_replaces_the_active_one_and_the_old_one_stays(): void
    {
        $v1 = app(CareersForm::class)->consent();
        $this->assertNotNull($v1);
        $this->get('/dashboard/settings/consents')->assertOk()->assertSee('نصوص الموافقة')->assertSee($v1->version);

        $this->post('/dashboard/settings/consents/careers', ['text_ar' => 'نص جديد'])->assertSessionHasErrors(['reason'], null, 'consent-careers');
        $this->post('/dashboard/settings/consents/careers', ['text_ar' => $v1->text_ar, 'reason' => 'لا شيء'])->assertSessionHasErrors(['text_ar'], null, 'consent-careers');
        $this->post('/dashboard/settings/consents/careers', ['text_ar' => "أوافق على نص الموافقة التجريبي.\n\nفقرة ثانية.", 'reason' => 'مراجعة المحامي'])
            ->assertSessionHas('status');

        $active = app(CareersForm::class)->consent();
        $this->assertSame('careers-consent-v2', $active?->version);
        $this->assertSame("أوافق على نص الموافقة التجريبي.\n\nفقرة ثانية.", $active->text_ar);
        $old = ConsentVersion::query()->findOrFail($v1->id);
        $this->assertFalse($old->is_active);
        $this->assertSame($v1->text_ar, $old->text_ar, 'the accepted text is never changed');
        $this->assertSame(1, AuditLog::query()->where('action', 'consent.published')->count());
    }

    public function test_the_partnership_texts_need_both_languages(): void
    {
        $this->post('/dashboard/settings/consents/partnerships', ['text_ar' => 'نص', 'reason' => 'x'])->assertSessionHasErrors(['text_en'], null, 'consent-partnerships');
        $this->post('/dashboard/settings/consents/partnership_ack', ['text_ar' => 'إقرار تجريبي', 'text_en' => 'Sample acknowledgement', 'reason' => 'x'])->assertSessionHasNoErrors();
        $this->assertSame('Sample acknowledgement', app(FranchiseForm::class)->acknowledgement()?->text_en);
        $this->post('/dashboard/settings/consents/unknown', ['text_ar' => 'x', 'reason' => 'x'])->assertNotFound();
    }
}
