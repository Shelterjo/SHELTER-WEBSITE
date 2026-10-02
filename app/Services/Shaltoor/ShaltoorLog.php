<?php

namespace App\Services\Shaltoor;

use App\Models\ShaltoorQuestion;
use App\Models\User;
use App\Services\Content\Search\Normalizer;
use App\Services\Core\AuditLogger;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * What Shaltoor keeps (M69 §33): the question without personal details (numbers of 6+ digits, e-mail addresses and
 * links are replaced), its topic, whether it was answered, the page and a random conversation id. No IP, no cookie,
 * no account. Kept config('shaltoor.retention_days') days. A logging failure never breaks the answer.
 */
final class ShaltoorLog
{
    public static function scrub(string $text): string
    {
        $text = (string) preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', '[email]', $text);
        $text = (string) preg_replace('#\b(?:https?://|www\.)\S+#iu', '[link]', $text);
        $text = (string) preg_replace('/[+\d\x{0660}-\x{0669}\x{06F0}-\x{06F9}][\d\x{0660}-\x{0669}\x{06F0}-\x{06F9}\s\-]{5,}/u', '[رقم] ', $text);

        return mb_substr(trim((string) preg_replace('/\s+/u', ' ', strip_tags($text))), 0, 300);
    }

    public function record(string $question, ShaltoorAnswer $answer, string $locale, ?string $page, ?string $conversation, bool $usedAi = false): void
    {
        try {
            $clean = self::scrub($question);
            if ($clean === '') {
                return;
            }
            ShaltoorQuestion::query()->create([
                'conversation' => is_string($conversation) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $conversation) === 1 ? strtolower($conversation) : null,
                'locale' => $locale,
                'topic' => mb_substr($answer->topic, 0, 30),
                'answered' => $answer->answered,
                'question' => $clean,
                'normalized' => mb_substr(Normalizer::normalize($clean), 0, 300),
                'page' => $page !== null ? mb_substr((string) parse_url($page, PHP_URL_PATH), 0, 200) : null,
                'used_ai' => $usedAi || $answer->topic === 'ai',
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * The period's numbers for the Owner (M69 §23): asked, answered, not answered, answered with AI, per topic.
     *
     * @return array{total: int, answered: int, unanswered: int, ai: int, topics: array<string, int>}
     */
    public function stats(int $days): array
    {
        $since = now()->subDays($days);
        $base = fn () => ShaltoorQuestion::query()->where('created_at', '>=', $since);
        $total = $base()->count();
        $answered = $base()->where('answered', true)->count();
        /** @var array<string, int> $topics */
        $topics = $base()->select('topic', DB::raw('count(*) as n'))->groupBy('topic')->orderByDesc('n')->limit(10)
            ->pluck('n', 'topic')->map(fn ($n): int => (int) $n)->all();

        return ['total' => $total, 'answered' => $answered, 'unanswered' => $total - $answered, 'ai' => $base()->where('used_ai', true)->count(), 'topics' => $topics];
    }

    /**
     * Questions Shaltoor could not answer and the Owner has not dealt with yet, the same question grouped (by its
     * normalised text), the most asked first.
     *
     * @return list<array{normalized: string, question: string, count: int, last_at: string|null}>
     */
    public function unanswered(int $days, int $limit = 50): array
    {
        $rows = ShaltoorQuestion::query()->where('answered', false)->whereNull('handled_at')->where('created_at', '>=', now()->subDays($days))
            ->select('normalized', DB::raw('count(*) as n'), DB::raw('max(created_at) as last_at'), DB::raw('max(question) as question'))
            ->groupBy('normalized')->orderByDesc('n')->orderByDesc('last_at')->limit($limit)->get();

        return array_values($rows->map(fn (ShaltoorQuestion $row): array => [
            'normalized' => (string) $row->normalized,
            'question' => (string) $row->getAttribute('question'),
            'count' => (int) $row->getAttribute('n'),
            'last_at' => is_string($row->getAttribute('last_at')) ? $row->getAttribute('last_at') : null,
        ])->all());
    }

    /** The Owner has dealt with a question (added the data, a search word or a page): it leaves the list. */
    public function markHandled(string $normalized, User $owner): int
    {
        $count = ShaltoorQuestion::query()->where('normalized', $normalized)->where('answered', false)->whereNull('handled_at')->update(['handled_at' => now()]);
        if ($count > 0) {
            app(AuditLogger::class)->record('shaltoor.handled', null, [], ['questions' => $count], actor: $owner);
        }

        return $count;
    }

    /**
     * Questions older than the retention window are deleted (no personal data, but no reason to keep them). Run by the
     * daily monitors; a run that deleted some leaves one audit line with the count (AUDIT-002: the system's own change).
     */
    public function prune(): int
    {
        $days = (int) config('shaltoor.retention_days', 90);
        $deleted = ShaltoorQuestion::query()->where('created_at', '<', now()->subDays($days))->delete();
        if ($deleted > 0) {
            app(AuditLogger::class)->system('shaltoor.questions_pruned', 'monitors:daily', meta: ['questions' => $deleted, 'older_than_days' => $days]);
        }

        return $deleted;
    }
}
