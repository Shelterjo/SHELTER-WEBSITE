<?php

namespace App\Services\Requests;

use App\Models\Branch;
use App\Models\Feedback;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\MasterData\MasterData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The feedback board (VOICE-OF-CUSTOMER §5, OPS-031): for a period and a branch — the average of each question with
 * its count (n) shown, DRIVE against HOUSE, the weekly trend, recurring topics, and the comments (filterable, each
 * archivable, and redactable when a customer typed personal data). Every rating counts in the numbers; archiving only
 * hides a comment from the list. No sentiment scores. Redaction is audited without the removed text.
 */
final class FeedbackBoard
{
    public const PERIODS = ['week', 'days30', 'month'];

    public const WEEKS = 8;

    public function __construct(private readonly MasterData $data, private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{period: string, branch: int|null, low: bool, archived: bool}
     */
    public static function filters(array $input): array
    {
        return [
            'period' => is_string($input['period'] ?? null) && in_array($input['period'], self::PERIODS, true) ? $input['period'] : 'days30',
            'branch' => is_numeric($input['branch'] ?? null) ? (int) $input['branch'] : null,
            'low' => ($input['low'] ?? '') === '1',
            'archived' => ($input['show'] ?? '') === 'archived',
        ];
    }

    /**
     * Branches by their approved name (id => name).
     *
     * @return array<int, string>
     */
    public function branches(string $locale): array
    {
        $names = [];
        foreach (Branch::query()->orderBy('id')->get() as $branch) {
            $name = $this->data->branchField($branch, $locale === 'ar' ? 'name_ar' : 'name_en');
            $names[$branch->id] = is_string($name) ? $name : $branch->code;
        }

        return $names;
    }

    /**
     * Average and count of each question for the period (and branch).
     *
     * @param  array{period: string, branch: int|null, low: bool, archived: bool}  $f
     * @return array<string, array{avg: float|null, n: int}>
     */
    public function averages(array $f, ?CarbonImmutable $now = null): array
    {
        return $this->stats($this->scope($f, $now));
    }

    /**
     * Each question per branch (DRIVE against HOUSE) for the period.
     *
     * @param  array{period: string, branch: int|null, low: bool, archived: bool}  $f
     * @return array<int, array<string, array{avg: float|null, n: int}>>
     */
    public function byBranch(array $f, ?CarbonImmutable $now = null): array
    {
        $result = [];
        foreach (array_keys($this->branches('ar')) as $branch) {
            $result[$branch] = $this->stats($this->scope(['branch' => $branch] + $f, $now));
        }

        return $result;
    }

    /**
     * The last weeks (7-day steps ending today, Jordan time), newest first.
     *
     * @param  array{period: string, branch: int|null, low: bool, archived: bool}  $f
     * @return list<array{from: CarbonImmutable, to: CarbonImmutable, stats: array<string, array{avg: float|null, n: int}>}>
     */
    public function weekly(array $f, ?CarbonImmutable $now = null): array
    {
        $end = ($now ?? CarbonImmutable::now())->setTimezone('Asia/Amman')->endOfDay();
        $weeks = [];
        for ($i = 0; $i < self::WEEKS; $i++) {
            $to = $end->subDays(7 * $i);
            $from = $to->subDays(6)->startOfDay();
            $query = Feedback::query()->whereBetween('submitted_at', [$from->utc(), $to->utc()])
                ->when($f['branch'] !== null, fn (Builder $q) => $q->where('branch_id', $f['branch']));
            $weeks[] = ['from' => $from, 'to' => $to, 'stats' => $this->stats($query)];
        }

        return $weeks;
    }

    /**
     * Recurring topics with their source (rule / AI) for the period.
     *
     * @param  array{period: string, branch: int|null, low: bool, archived: bool}  $f
     * @return list<array{tag: string, source: string, n: int}>
     */
    public function topics(array $f, ?CarbonImmutable $now = null): array
    {
        $counts = [];
        foreach ($this->scope($f, $now)->whereNotNull('topics')->pluck('topics') as $topics) {
            foreach (is_array($topics) ? $topics : [] as $topic) {
                $key = ($topic['tag'] ?? '').'|'.($topic['source'] ?? 'rule');
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }
        arsort($counts);
        $list = [];
        foreach ($counts as $key => $n) {
            [$tag, $source] = explode('|', (string) $key, 2);
            $list[] = ['tag' => $tag, 'source' => $source, 'n' => $n];
        }

        return $list;
    }

    /**
     * The written comments for the period and branch — low ratings only on request; archived ones apart.
     *
     * @param  array{period: string, branch: int|null, low: bool, archived: bool}  $f
     * @return LengthAwarePaginator<int, Feedback>
     */
    public function comments(array $f, int $page = 1, ?CarbonImmutable $now = null): LengthAwarePaginator
    {
        return $this->scope($f, $now)->whereNotNull('comment')->where('comment', '!=', '')
            ->when($f['archived'], fn (Builder $q) => $q->whereNotNull('archived_at'), fn (Builder $q) => $q->whereNull('archived_at'))
            ->when($f['low'], fn (Builder $q) => $q->where('rating_overall', '<=', 2))
            ->orderByDesc('submitted_at')->orderByDesc('id')->paginate(25, ['*'], 'page', $page);
    }

    public function archive(Feedback $feedback, User $owner): void
    {
        $feedback->forceFill(['archived_at' => now()])->save();
        $this->audit->record('feedback.archived', $feedback, [], actor: $owner);
    }

    public function restore(Feedback $feedback, User $owner): void
    {
        $feedback->forceFill(['archived_at' => null])->save();
        $this->audit->record('feedback.restored', $feedback, [], actor: $owner);
    }

    /** The Owner's cleaned version of a comment that contained personal data — the removed text is not kept anywhere. */
    public function redact(Feedback $feedback, string $text, User $owner): void
    {
        $text = trim(mb_substr($text, 0, (int) config('feedback.limits.comment', 1000)));
        $feedback->forceFill(['comment' => $text === '' ? null : $text])->save();
        $this->audit->record('feedback.redacted', $feedback, [], ['characters_after' => mb_strlen($text)], actor: $owner);
    }

    /**
     * @param  array{period: string, branch: int|null}  $f
     * @return Builder<Feedback>
     */
    private function scope(array $f, ?CarbonImmutable $now): Builder
    {
        $now = ($now ?? CarbonImmutable::now())->setTimezone('Asia/Amman');
        $since = match ($f['period']) {
            'week' => $now->subDays(6)->startOfDay(),
            'month' => $now->startOfMonth(),
            default => $now->subDays(29)->startOfDay(),
        };

        return Feedback::query()->where('submitted_at', '>=', $since->utc())
            ->when($f['branch'] !== null, fn (Builder $q) => $q->where('branch_id', $f['branch']));
    }

    /**
     * @param  Builder<Feedback>  $query
     * @return array<string, array{avg: float|null, n: int}>
     */
    private function stats(Builder $query): array
    {
        $select = [];
        foreach (Feedback::DIMENSIONS as $d) {
            $select[] = "avg(rating_{$d}) as avg_{$d}, count(rating_{$d}) as n_{$d}";
        }
        $row = (clone $query)->selectRaw(implode(', ', $select))->toBase()->first();
        $stats = [];
        foreach (Feedback::DIMENSIONS as $d) {
            $n = (int) ($row->{'n_'.$d} ?? 0);
            $stats[$d] = ['avg' => $n > 0 ? round((float) $row?->{'avg_'.$d}, 1) : null, 'n' => $n];
        }

        return $stats;
    }
}
