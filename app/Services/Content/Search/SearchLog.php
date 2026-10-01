<?php

namespace App\Services\Content\Search;

use App\Services\Core\FeatureFlags;
use Illuminate\Support\Facades\DB;

/**
 * Anonymous daily search counters (GLOBAL-SEARCH §6, OPS-045, PRIV-011): the query is cleaned before it is stored
 * (normalised, cut to 50 characters; anything that looks like an email, a phone/ID number or a link becomes
 * `[redacted]`), and only counters are increased — no session, user, IP or user agent, ever. Owner searches are never
 * logged. Off until PO-019 decides disclosure or consent (feature flag `search.log`).
 */
final class SearchLog
{
    public const FLAG = 'search.log';

    public function __construct(private readonly FeatureFlags $flags) {}

    public static function clean(string $query): string
    {
        $lower = mb_strtolower($query);
        $normalized = Normalizer::normalize($query);
        if (str_contains($lower, '@') || str_contains($lower, 'http') || str_contains($lower, 'www') || preg_match('/\d{5}/', $normalized) === 1) {
            return '[redacted]';
        }

        return mb_substr($normalized, 0, 50);
    }

    public function record(string $scope, string $locale, string $query, int $results): void
    {
        $clean = self::clean($query);
        if ($clean === '' || ! $this->flags->enabled(self::FLAG)) {
            return;
        }
        $zero = $results === 0 ? 1 : 0;
        DB::table('search_query_daily')->upsert(
            [['day' => now()->toDateString(), 'scope' => $scope, 'locale' => $locale, 'query_norm' => $clean, 'searches' => 1, 'zero_results' => $zero]],
            ['day', 'scope', 'locale', 'query_norm'],
            ['searches' => DB::raw('search_query_daily.searches + 1'), 'zero_results' => DB::raw('search_query_daily.zero_results + '.$zero)],
        );
    }
}
