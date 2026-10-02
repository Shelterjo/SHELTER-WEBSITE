<?php

namespace Tests\Feature\Dashboard;

use App\Enums\ContactKind;
use App\Enums\FactStatus;
use App\Models\AuditLog;
use App\Models\ContactPoint;
use App\Models\Fact;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Dashboard → Contact numbers and social accounts (CMS-030, CONTACT-004…027, M50): the Owner's edit is the approval. */
class ContactsEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    private function point(ContactKind $kind): ContactPoint
    {
        return ContactPoint::query()->where('scope', 'brand')->where('kind', $kind->value)->firstOrFail();
    }

    private function site(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->getContent();
    }

    public function test_a_number_changes_everywhere_it_may_appear_and_its_approval_is_recorded(): void
    {
        $html = (string) $this->get('/dashboard/data/contacts')->assertOk()->getContent();
        $this->assertStringContainsString('صفحة التواصل فقط', $html, 'where each number may appear is stated');
        $main = $this->point(ContactKind::PhoneMain);

        $this->put('/dashboard/data/contacts/'.$main->id, ['value' => '12', 'reason' => ''])->assertSessionHasErrors(['value', 'reason'], null, 'c'.$main->id);
        $this->put('/dashboard/data/contacts/'.$main->id, ['value' => '079 111 2233', 'is_public' => '1', 'reason' => 'New line'])->assertSessionHasNoErrors();
        $this->assertSame('+962791112233', $main->refresh()->value);
        $facts = Fact::query()->where('key', $main->factKey())->orderBy('id')->get();
        $this->assertSame([FactStatus::Superseded, FactStatus::Approved], $facts->pluck('status')->all());
        $this->assertStringContainsString('tel:+962791112233', $this->site('/ar/irbid/'));
        $this->assertSame('New line', AuditLog::query()->where('action', 'contacts.saved')->firstOrFail()->meta['reason'] ?? null);

        $this->put('/dashboard/data/contacts/'.$main->id, ['value' => '0791112233', 'reason' => 'Hide for now']);
        $this->assertStringNotContainsString('tel:+962791112233', $this->site('/ar/irbid/'), 'not public → not shown');
    }

    public function test_the_email_appears_only_once_the_owner_makes_it_public(): void
    {
        $email = $this->point(ContactKind::Email);
        $this->assertStringNotContainsString('mailto:', $this->site('/ar/contact/'));
        $this->put('/dashboard/data/contacts/'.$email->id, ['value' => 'not-an-email', 'is_public' => '1', 'reason' => 'x'])->assertSessionHasErrors(['value'], null, 'c'.$email->id);
        $this->put('/dashboard/data/contacts/'.$email->id, ['value' => 'Info@ShelterJo.com', 'is_public' => '1', 'reason' => 'Publish the email'])->assertSessionHasNoErrors();
        $this->assertSame(FactStatus::Approved, Fact::query()->where('key', $email->factKey())->latest('id')->firstOrFail()->status);
        $this->assertStringContainsString('mailto:info@shelterjo.com', $this->site('/ar/contact/'));
    }

    public function test_social_accounts_show_in_the_footer_only_when_switched_on(): void
    {
        $this->assertStringNotContainsString('تابعنا', $this->site('/ar/irbid/'));
        $this->put('/dashboard/data/contacts/social/instagram', ['url' => 'https://evil.example/shelter', 'is_active' => '1'])->assertSessionHasErrors(['url'], null, 's-instagram');
        $this->put('/dashboard/data/contacts/social/instagram', ['url' => '', 'is_active' => '1'])->assertSessionHasErrors(['url'], null, 's-instagram');
        $this->put('/dashboard/data/contacts/social/instagram', ['url' => 'https://www.instagram.com/shelter.sample', 'is_active' => '1'])->assertSessionHasNoErrors();
        $html = $this->site('/ar/irbid/');
        $this->assertStringContainsString('تابعنا', $html);
        $this->assertStringContainsString('href="https://www.instagram.com/shelter.sample"', $html);

        $this->put('/dashboard/data/contacts/social/instagram', ['url' => 'https://www.instagram.com/shelter.sample']);
        $this->assertStringNotContainsString('instagram.com', $this->site('/ar/irbid/'), 'switched off → gone');
        $this->put('/dashboard/data/contacts/social/myspace', ['url' => 'https://myspace.com/x'])->assertNotFound();
    }

    public function test_contacts_need_a_fresh_confirmation(): void
    {
        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->subMinutes(30)->getTimestamp()]);
        $this->get('/dashboard/data/contacts')->assertRedirect('/dashboard/confirm');
    }
}
