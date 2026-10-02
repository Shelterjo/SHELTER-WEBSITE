<?php

namespace App\Services\Dashboard;

use App\Enums\HoursExceptionKind;
use App\Enums\PublishStatus;
use App\Models\Branch;
use App\Models\BranchHour;
use App\Models\HoursException;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\MasterData\MasterData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The Owner's opening hours (no code — M50, MDH-006/007, HOURS-005…012, CMS-013). Regular weekly hours are published
 * only after a preview of exactly what visitors will see (BRANCH-010) and with a reason (G-25); publishing approves
 * the new value of the fact `hours.{CODE}.regular` in the same transaction (FACT-REGISTRY §3 — the Owner's edit in the
 * dashboard is the approval), so the site never shows hours nobody approved. Exceptions — special hours, holidays,
 * temporary and emergency closures — never touch the regular hours; the resolver applies them by priority
 * (Emergency > Temporary > Special/Holiday > Regular) and the regular hours return by themselves after the end date.
 * A shift that ends after midnight belongs to the day it starts. Removing = archiving. Everything is audited.
 */
final class HoursEditor
{
    private const REASON_MAX = 300;

    public function __construct(private readonly OwnerApproval $approval, private readonly AuditLogger $audit) {}

    /**
     * The current regular week as form values: weekday => [open, opens, closes].
     *
     * @return array<int, array{open: bool, opens: string, closes: string}>
     */
    public function current(Branch $branch): array
    {
        $week = [];
        foreach ([6, 0, 1, 2, 3, 4, 5] as $day) {
            $week[$day] = ['open' => false, 'opens' => '', 'closes' => ''];
        }
        foreach ($branch->hours()->get() as $hour) {
            $week[$hour->weekday] = ['open' => true, 'opens' => substr($hour->opens_at, 0, 5), 'closes' => substr($hour->closes_at, 0, 5)];
        }

        return $week;
    }

    /**
     * Reads the week from the form: one interval per day, or closed.
     *
     * @param  array<string, mixed>  $input
     * @return array{rows: list<array{0: int, 1: string, 2: string}>, errors: array<string, string>}
     */
    public function parseWeek(array $input): array
    {
        $rows = [];
        $errors = [];
        $days = is_array($input['days'] ?? null) ? $input['days'] : [];
        foreach ([6, 0, 1, 2, 3, 4, 5] as $day) {
            $row = is_array($days[$day] ?? null) ? $days[$day] : [];
            if (empty($row['open'])) {
                continue; // closed all day
            }
            $opens = self::time($row['opens'] ?? null);
            $closes = self::time($row['closes'] ?? null);
            if ($opens === null || $closes === null) {
                $errors["days.{$day}"] = (string) __('dashboard.hours.errors.times');

                continue;
            }
            if ($opens === $closes) {
                $errors["days.{$day}"] = (string) __('dashboard.hours.errors.same');

                continue;
            }
            $rows[] = [$day, $opens, $closes]; // closes <= opens = after midnight, counted on the day it starts (HOURS-012)
        }

        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * What the preview stands for: the exact week shown, so "publish" publishes what was seen.
     *
     * @param  list<array{0: int, 1: string, 2: string}>  $rows
     */
    public static function fingerprint(Branch $branch, array $rows): string
    {
        return hash('sha256', $branch->code.'|'.json_encode(self::canonical($rows)));
    }

    /**
     * @param  list<array{0: int, 1: string, 2: string}>  $rows
     * @return array<string, string> errors; empty = published
     */
    public function publishWeek(Branch $branch, array $rows, string $reason, string $previewed, User $owner): array
    {
        $reason = trim($reason);
        $errors = [];
        if ($reason === '' || mb_strlen($reason) > self::REASON_MAX) {
            $errors['reason'] = (string) __('dashboard.hours.errors.reason');
        }
        if (! hash_equals(self::fingerprint($branch, $rows), $previewed)) {
            $errors['preview'] = (string) __('dashboard.hours.errors.preview');
        }
        if ($errors !== []) {
            return $errors;
        }

        DB::transaction(function () use ($branch, $rows, $reason, $owner): void {
            $before = MasterData::normalizeHours($branch->hours()->get());
            BranchHour::query()->where('branch_id', $branch->id)->delete();
            foreach ($rows as [$day, $opens, $closes]) {
                BranchHour::query()->create(['branch_id' => $branch->id, 'weekday' => $day, 'opens_at' => $opens, 'closes_at' => $closes]);
            }
            $after = MasterData::normalizeHours($branch->hours()->get());
            $this->approval->approve("hours.{$branch->code}.regular", $after, $owner, 'hours', 'ساعات العمل: '.$branch->code, 'Opening hours: '.$branch->code);
            $this->audit->record('hours.regular_published', $branch, ['before' => ['hours' => $before], 'after' => ['hours' => $after]], ['reason' => $reason], actor: $owner);
        });

        return [];
    }

    /**
     * Adds or changes an exception (special hours, holiday, temporary or emergency closure — MDH-006, CMS-013).
     *
     * @param  array<string, mixed>  $input
     * @return array{exception: HoursException|null, errors: array<string, string>}
     */
    public function saveException(Branch $branch, ?HoursException $exception, array $input, User $owner, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::now('Asia/Amman')->startOfDay();
        $errors = [];
        $kind = HoursExceptionKind::tryFrom((string) ($input['kind'] ?? ''));
        if ($kind === null) {
            $errors['kind'] = (string) __('dashboard.hours.errors.kind');
        }
        $from = self::date($input['starts_on'] ?? null);
        $to = self::date($input['ends_on'] ?? null);
        if ($from === null) {
            $errors['starts_on'] = (string) __('dashboard.hours.errors.date');
        }
        if ($to === null) {
            $errors['ends_on'] = (string) __('dashboard.hours.errors.date');
        }
        if ($from !== null && $to !== null && $to->lessThan($from)) {
            $errors['ends_on'] = (string) __('dashboard.hours.errors.order');
        }
        if ($exception === null && $to !== null && $to->lessThan($today)) {
            $errors['ends_on'] ??= (string) __('dashboard.hours.errors.past');
        }
        // Closures are closed all day; special hours and holidays are closed or open for one interval.
        $closed = in_array($kind, [HoursExceptionKind::Emergency, HoursExceptionKind::Temporary], true) || ($input['mode'] ?? '') === 'closed';
        $opens = $closed ? null : self::time($input['opens_at'] ?? null);
        $closes = $closed ? null : self::time($input['closes_at'] ?? null);
        if (! $closed && ($opens === null || $closes === null || $opens === $closes)) {
            $errors['opens_at'] = (string) __('dashboard.hours.errors.times');
        }
        $reason = is_string($input['reason'] ?? null) ? trim($input['reason']) : '';
        if ($reason === '' || mb_strlen($reason) > self::REASON_MAX) {
            $errors['reason'] = (string) __('dashboard.hours.errors.reason');
        }
        $publish = ($input['status'] ?? '') === 'published';
        if ($publish && $kind !== null && $from !== null && $to !== null && $errors === []) {
            // Two published exceptions of the same priority on the same day would be ambiguous (MDH-025).
            $clash = HoursException::query()->where('branch_id', $branch->id)->where('status', PublishStatus::Published->value)
                ->when($exception !== null, fn ($q) => $q->whereKeyNot($exception?->id))
                ->whereDate('starts_on', '<=', $to->toDateString())->whereDate('ends_on', '>=', $from->toDateString())->get()
                ->first(fn (HoursException $e): bool => $e->kind->rank() === $kind->rank());
            if ($clash !== null) {
                $errors['starts_on'] = (string) __('dashboard.hours.errors.overlap', ['from' => $clash->starts_on->format('Y-m-d'), 'to' => $clash->ends_on->format('Y-m-d')]);
            }
        }
        if ($errors !== [] || $kind === null || $from === null || $to === null) {
            return ['exception' => $exception, 'errors' => $errors];
        }

        $exception ??= new HoursException(['branch_id' => $branch->id, 'created_by' => $owner->id]);
        $created = ! $exception->exists;
        $before = $exception->exists ? $exception->only(['kind', 'starts_on', 'ends_on', 'is_closed', 'opens_at', 'closes_at', 'status']) : [];
        $exception->forceFill([
            'kind' => $kind, 'starts_on' => $from->toDateString(), 'ends_on' => $to->toDateString(), 'is_closed' => $closed,
            'opens_at' => $opens, 'closes_at' => $closes, 'reason_ar' => $reason,
            'status' => $publish ? PublishStatus::Published : PublishStatus::Draft,
        ])->save();
        $this->audit->record($created ? 'hours.exception_created' : 'hours.exception_changed', $exception,
            ['before' => $before, 'after' => $exception->only(['kind', 'starts_on', 'ends_on', 'is_closed', 'opens_at', 'closes_at', 'status'])], ['reason' => $reason], actor: $owner);

        return ['exception' => $exception, 'errors' => []];
    }

    /** Ends an exception early or removes a planned one — archived, never deleted. */
    public function archiveException(HoursException $exception, User $owner): void
    {
        $exception->forceFill(['status' => PublishStatus::Archived])->save();
        $this->audit->record('hours.exception_archived', $exception, ['after' => ['status' => 'archived']], actor: $owner);
    }

    /**
     * @param  list<array{0: int, 1: string, 2: string}>  $rows
     * @return list<array{0: int, 1: string, 2: string}>
     */
    private static function canonical(array $rows): array
    {
        usort($rows, fn (array $a, array $b): int => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        return $rows;
    }

    private static function time(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', trim($value)) === 1 ? trim($value) : null;
    }

    private static function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($value), $m) !== 1 || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }

        return CarbonImmutable::create((int) $m[1], (int) $m[2], (int) $m[3], 0, 0, 0, 'Asia/Amman');
    }
}
