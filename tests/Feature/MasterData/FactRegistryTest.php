<?php

namespace Tests\Feature\MasterData;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Enums\Role;
use App\Models\Signal;
use App\Models\User;
use App\Services\MasterData\FactRegistry;
use App\Services\MasterData\MasterData;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\Support\MasterDataFixtures;
use Tests\TestCase;

class FactRegistryTest extends TestCase
{
    use MasterDataFixtures;
    use RefreshDatabase;

    private function registry(): FactRegistry
    {
        return app(FactRegistry::class);
    }

    public function test_import_path_cannot_create_approved_without_owner_decision(): void
    {
        // FR-T2
        $fact = $this->registry()->register('claim.example', 'claim', 'x', FactStatus::Approved, FactSource::Document);

        $this->assertSame(FactStatus::PendingOwnerApproval, $fact->status);
        $this->assertNull($fact->value_hash);
    }

    public function test_legacy_and_external_sources_are_never_publishable_on_their_own(): void
    {
        $fact = $this->registry()->register('claim.legacy', 'claim', 'x', FactStatus::Approved, FactSource::LegacySite, decisionRef: 'D-999');

        $this->assertSame(FactStatus::PendingVerification, $fact->status);
    }

    public function test_codes_are_sequential_and_one_current_row_per_key(): void
    {
        $a = $this->registry()->register('k.a', 'brand', null, FactStatus::Missing, FactSource::Document);
        $b = $this->registry()->register('k.b', 'brand', null, FactStatus::Missing, FactSource::Document);
        $this->assertSame(['FACT-0001', 'FACT-0002'], [$a->code, $b->code]);

        $this->expectException(LogicException::class);
        $this->registry()->register('k.a', 'brand', null, FactStatus::Missing, FactSource::Document);
    }

    public function test_only_the_owner_can_approve(): void
    {
        $fact = $this->registry()->register('claim.x', 'claim', 'x', FactStatus::PendingOwnerApproval, FactSource::Document);
        $notOwner = User::factory()->inactive()->create(['role' => Role::Owner]);

        $this->expectException(AuthorizationException::class);
        $this->registry()->approve($fact, $notOwner, 'D-1');
    }

    public function test_rejected_is_final(): void
    {
        $owner = User::factory()->create();
        $fact = $this->registry()->register('brand.since', 'brand', 'منذ 2022', FactStatus::PendingVerification, FactSource::LegacySite);
        $this->registry()->reject($fact, $owner, 'OLD OR INCORRECT', ['منذ 2022']);

        $this->expectException(LogicException::class);
        $this->registry()->approve($fact->fresh() ?? $fact, $owner, 'D-1');
    }

    public function test_unapproved_branch_field_is_hidden_and_shown_after_approval(): void
    {
        // FR-T3 / FR-T5 equivalent on a branch field.
        $owner = User::factory()->create();
        $branch = $this->branch(attributes: ['address_ar' => 'عنوان تجريبي']);
        $fact = $this->registry()->register($branch->factKey('address_ar'), 'branch', 'عنوان تجريبي', FactStatus::PendingOwnerApproval, FactSource::Document);
        $data = app(MasterData::class);

        $this->assertNull($data->branchField($branch, 'address_ar'));

        $this->registry()->approve($fact, $owner, 'D-TEST');
        $this->assertSame('عنوان تجريبي', $data->branchField($branch, 'address_ar'));
    }

    public function test_direct_database_change_hides_the_value_and_raises_one_drift_signal(): void
    {
        // FR-T4
        $owner = User::factory()->create();
        $branch = $this->branch(attributes: ['address_ar' => 'عنوان معتمد']);
        $fact = $this->registry()->register($branch->factKey('address_ar'), 'branch', 'عنوان معتمد', FactStatus::PendingOwnerApproval, FactSource::Document);
        $this->registry()->approve($fact, $owner, 'D-TEST');

        $branch->forceFill(['address_ar' => 'تعديل بلا اعتماد'])->save();
        $data = app(MasterData::class);

        $this->assertNull($data->branchField($branch, 'address_ar'));
        $this->assertNull($data->branchField($branch, 'address_ar'));
        $this->assertSame(1, Signal::query()->open()->where('dedupe_key', "fact:{$fact->code}:drift")->count());
    }

    public function test_expired_verification_falls_back_to_approved_and_stays_visible(): void
    {
        // FR-T6
        $owner = User::factory()->create();
        $fact = $this->registry()->register('claim.v', 'claim', 'v', FactStatus::PendingOwnerApproval, FactSource::Document);
        $this->registry()->approve($fact, $owner, 'D-1');
        $this->registry()->verify($fact, $owner, 'evidence', now()->subHour());

        $this->assertSame(1, $this->registry()->expireVerified());
        $this->assertSame(FactStatus::Approved, $fact->fresh()?->status);
        $this->assertSame('v', app(MasterData::class)->value('claim.v'));
    }

    public function test_supersede_creates_a_new_approved_row_and_finalises_the_old_one(): void
    {
        $owner = User::factory()->create();
        $old = $this->registry()->register('claim.s', 'claim', 'old', FactStatus::PendingOwnerApproval, FactSource::Document);
        $this->registry()->approve($old, $owner, 'D-1');

        $new = $this->registry()->supersede($old, 'new', $owner, 'D-2');

        $this->assertSame(FactStatus::Superseded, $old->fresh()?->status);
        $this->assertSame($old->id, $new->supersedes_id);
        $this->assertSame('new', app(MasterData::class)->value('claim.s'));
    }
}
