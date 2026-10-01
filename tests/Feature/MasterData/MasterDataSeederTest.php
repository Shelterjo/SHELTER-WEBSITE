<?php

namespace Tests\Feature\MasterData;

use App\Enums\ContactKind;
use App\Enums\FactStatus;
use App\Models\Branch;
use App\Models\BranchAttribute;
use App\Models\Fact;
use App\Models\Market;
use App\Services\MasterData\MasterData;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The seed contains approved values only; everything else stays hidden (M38). */
class MasterDataSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    public function test_seeding_twice_is_idempotent(): void
    {
        $facts = Fact::query()->count();
        $this->seed(MasterDataSeeder::class);

        $this->assertSame($facts, Fact::query()->count());
        $this->assertSame(2, Branch::query()->count());
    }

    public function test_approved_branch_names_and_hours_are_public(): void
    {
        $data = app(MasterData::class);
        $drive = Branch::query()->where('code', 'BR-DRIVE')->firstOrFail();

        $this->assertSame('SHELTER COFFEE DRIVE', $data->branchField($drive, 'name_en'));
        $this->assertNotNull($data->regularHours($drive));
        $this->assertTrue($data->openState($drive, Market::query()->firstOrFail(), CarbonImmutable::parse('2026-10-03 12:00', 'Asia/Amman'))?->isOpen);
    }

    public function test_unapproved_values_are_hidden(): void
    {
        $data = app(MasterData::class);
        $house = Branch::query()->where('code', 'BR-HOUSE')->firstOrFail();

        $this->assertNull($data->branchField($house, 'address_ar'));
        $this->assertNull($data->branchField($house, 'maps_url'));
        $this->assertNull($data->contact(ContactKind::Email));
        $this->assertSame(10 * 2, BranchAttribute::query()->whereNull('value')->count());
    }

    public function test_intent_numbers_are_never_on_branch_cards(): void
    {
        $data = app(MasterData::class);

        $this->assertTrue($data->contact(ContactKind::PhoneMain)?->show_on_branch_cards);
        $this->assertFalse($data->contact(ContactKind::ComplaintsFeedbackFranchise)?->show_on_branch_cards);
        $this->assertFalse($data->contact(ContactKind::CateringB2bEvents)?->show_on_branch_cards);
    }

    public function test_old_founding_year_is_rejected_with_blocked_phrases(): void
    {
        $legacy = Fact::query()->where('key', 'brand.founded_year.legacy')->firstOrFail();

        $this->assertSame(FactStatus::Rejected, $legacy->status);
        $this->assertContains('منذ 2022', $legacy->blocked_phrases ?? []);
        $this->assertSame(2019, app(MasterData::class)->setting('brand.founded_year'));
    }
}
