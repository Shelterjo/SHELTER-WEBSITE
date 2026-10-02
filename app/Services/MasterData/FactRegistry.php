<?php

namespace App\Services\MasterData;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Enums\Priority;
use App\Enums\Severity;
use App\Enums\SignalCategory;
use App\Enums\SignalKind;
use App\Models\Fact;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Core\Signals;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Fact registry (FACT-REGISTRY.md, OPS-013). The only way a business value becomes publishable.
 * - Imports, seeds and Claude can never create APPROVED without an owner decision reference (FR-T2).
 * - Only the owner approves, verifies or rejects. REJECTED and SUPERSEDED are final.
 * - value_hash pins the approved value; a live value that no longer matches is hidden and raised (drift).
 */
final class FactRegistry
{
    public function __construct(private readonly Signals $signals, private readonly AuditLogger $audit) {}

    public static function hash(mixed $value): string
    {
        return hash('sha256', (string) json_encode(self::canonical($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param  list<string>|null  $blockedPhrases
     */
    public function register(
        string $key,
        string $category,
        mixed $value,
        FactStatus $status,
        FactSource $source,
        ?string $sourceRef = null,
        ?string $decisionRef = null,
        ?string $labelAr = null,
        ?string $labelEn = null,
        ?array $blockedPhrases = null,
        string $classification = 'PUBLIC',
        ?string $notes = null,
    ): Fact {
        if ($status->isPublishable() && ($decisionRef === null || in_array($source, [FactSource::LegacySite, FactSource::ExternalListing], true))) {
            $status = $source === FactSource::LegacySite || $source === FactSource::ExternalListing
                ? FactStatus::PendingVerification
                : FactStatus::PendingOwnerApproval;
        }
        if ($status === FactStatus::Missing) {
            $value = null;
        }

        return DB::transaction(function () use ($key, $category, $value, $status, $source, $sourceRef, $decisionRef, $labelAr, $labelEn, $blockedPhrases, $classification, $notes): Fact {
            if ($this->current($key) !== null) {
                throw new LogicException("Fact [{$key}] already has a current row; use approve/supersede instead.");
            }
            $fact = Fact::query()->create([
                'code' => $this->nextCode(),
                'key' => $key,
                'category' => $category,
                'label_ar' => $labelAr,
                'label_en' => $labelEn,
                'value' => $value,
                'value_hash' => $status->isPublishable() ? self::hash($value) : null,
                'status' => $status,
                'source_type' => $source,
                'source_ref' => $sourceRef,
                'decision_ref' => $status->isPublishable() ? $decisionRef : null,
                'blocked_phrases' => $blockedPhrases,
                'classification' => $classification,
                'notes' => $notes,
            ]);
            $this->audit->record('facts.registered', $fact, ['after' => ['status' => $status->value, 'key' => $key]]);

            return $fact;
        });
    }

    public function current(string $key): ?Fact
    {
        return Fact::query()->current()->where('key', $key)->orderByDesc('id')->first();
    }

    public function approve(Fact $fact, User $owner, ?string $decisionRef, bool $replaceValue = false, mixed $value = null): Fact
    {
        $this->assertOwner($owner);
        $this->assertTransition($fact, FactStatus::Approved);
        $before = $fact->status;
        $approved = $replaceValue ? $value : $fact->value;
        $fact->forceFill([
            'value' => $approved,
            'value_hash' => self::hash($approved),
            'status' => FactStatus::Approved,
            'approved_by' => $owner->id,
            'decision_ref' => $decisionRef,
            'last_reviewed_at' => now(),
        ])->save();
        $this->audit->record('facts.approved', $fact, ['before' => ['status' => $before->value], 'after' => ['status' => 'APPROVED']], actor: $owner);
        $this->signals->resolve("fact:{$fact->code}:unpublishable", $owner);
        $this->signals->resolve("fact:{$fact->code}:drift", $owner);

        return $fact;
    }

    public function verify(Fact $fact, User $owner, string $evidence, ?CarbonInterface $expiresAt = null): Fact
    {
        $this->assertOwner($owner);
        $this->assertTransition($fact, FactStatus::Verified);
        $fact->forceFill([
            'status' => FactStatus::Verified,
            'verified_by' => $owner->id,
            'verified_at' => now(),
            'evidence' => $evidence,
            'expires_at' => $expiresAt,
            'last_reviewed_at' => now(),
        ])->save();
        $this->audit->record('facts.verified', $fact, ['after' => ['status' => 'VERIFIED']], actor: $owner);

        return $fact;
    }

    /** @param list<string> $blockedPhrases */
    public function reject(Fact $fact, User $owner, ?string $reason = null, array $blockedPhrases = []): Fact
    {
        $this->assertOwner($owner);
        $this->assertTransition($fact, FactStatus::Rejected);
        $fact->forceFill([
            'status' => FactStatus::Rejected,
            'value_hash' => null,
            'blocked_phrases' => $blockedPhrases === [] ? $fact->blocked_phrases : $blockedPhrases,
            'notes' => trim(($fact->notes ?? '')."\n".($reason ?? '')) ?: null,
        ])->save();
        $this->audit->record('facts.rejected', $fact, ['after' => ['status' => 'REJECTED']], ['reason' => $reason], actor: $owner);

        return $fact;
    }

    /** A new approved value replaces an approved/verified one: the old row becomes SUPERSEDED (final). */
    public function supersede(Fact $old, mixed $newValue, User $owner, ?string $decisionRef): Fact
    {
        $this->assertOwner($owner);
        $this->assertTransition($old, FactStatus::Superseded);

        return DB::transaction(function () use ($old, $newValue, $owner, $decisionRef): Fact {
            $old->forceFill(['status' => FactStatus::Superseded])->save();
            $new = Fact::query()->create([
                'code' => $this->nextCode(),
                'key' => $old->key,
                'category' => $old->category,
                'market_id' => $old->market_id,
                'label_ar' => $old->label_ar,
                'label_en' => $old->label_en,
                'value' => $newValue,
                'value_hash' => self::hash($newValue),
                'status' => FactStatus::Approved,
                'source_type' => FactSource::OwnerDashboard,
                'decision_ref' => $decisionRef,
                'approved_by' => $owner->id,
                'supersedes_id' => $old->id,
                'classification' => $old->classification,
                'last_reviewed_at' => now(),
            ]);
            $this->audit->record('facts.superseded', $new, ['before' => ['code' => $old->code], 'after' => ['code' => $new->code]], actor: $owner);
            // The Owner's new approved value settles what was open on the old row (MON-007: an issue closes once fixed).
            foreach (['unpublishable', 'drift', 'expired'] as $issue) {
                $this->signals->resolve("fact:{$old->code}:{$issue}", $owner);
            }

            return $new;
        });
    }

    /**
     * True only when the current fact is APPROVED/VERIFIED and the live value still matches the approved one.
     * A mismatch hides the value and raises one HIGH drift signal (FR-T4).
     */
    public function isPublishable(string $key, mixed $liveValue): bool
    {
        $fact = $this->current($key);
        if ($fact === null || ! $fact->status->isPublishable()) {
            return false;
        }
        if ($fact->value_hash !== self::hash($liveValue)) {
            $this->signals->raise(
                SignalKind::Issue, SignalCategory::DataConsistency, Severity::High, Priority::ActionRequired, 'facts',
                'قيمة تغيّرت بلا اعتماد', 'A value changed without approval',
                dedupeKey: "fact:{$fact->code}:drift", subject: $fact, details: ['key' => $key],
            );

            return false;
        }

        return true;
    }

    /** VERIFIED facts past expires_at fall back to APPROVED (still shown) + a review signal (FR-T6). */
    public function expireVerified(): int
    {
        $count = 0;
        Fact::query()->where('status', FactStatus::Verified->value)->whereNotNull('expires_at')->where('expires_at', '<=', now())
            ->each(function (Fact $fact) use (&$count): void {
                $fact->forceFill(['status' => FactStatus::Approved])->save();
                $this->signals->raise(
                    SignalKind::Issue, SignalCategory::DataConsistency, Severity::Medium, Priority::Important, 'facts',
                    'معلومة تحتاج مراجعة: انتهت صلاحية التحقق', 'Fact needs review: verification expired',
                    dedupeKey: "fact:{$fact->code}:expired", subject: $fact,
                );
                $count++;
            });

        return $count;
    }

    private function nextCode(): string
    {
        $last = Fact::query()->lockForUpdate()->orderByDesc('id')->value('code');
        $n = is_string($last) ? (int) substr($last, 5) : 0;

        return sprintf('FACT-%04d', $n + 1);
    }

    private function assertOwner(User $user): void
    {
        if (! $user->isOwner()) {
            throw new AuthorizationException('Only the owner can change a fact status.');
        }
    }

    private function assertTransition(Fact $fact, FactStatus $to): void
    {
        if (! $fact->status->canTransitionTo($to)) {
            throw new LogicException("Fact {$fact->code}: {$fact->status->value} → {$to->value} is not allowed.");
        }
    }

    private static function canonical(mixed $value): mixed
    {
        if (is_array($value)) {
            if (! array_is_list($value)) {
                ksort($value);
            }

            return array_map(self::canonical(...), $value);
        }

        return is_float($value) || is_int($value) ? (string) $value : $value;
    }
}
