<?php

namespace App\Services\Content\Search;

use App\Models\SearchEntry;
use Illuminate\Support\Collection;

/**
 * The one search engine (GLOBAL-SEARCH §1, G14-CF-09). The scope comes from the calling route on the server, never
 * from the request. V1 data is small (hundreds of rows), so the rows of a scope are read once per request and matched
 * with the same rules as the menu search: every query word starts a word of the item; only when that finds nothing,
 * words of 5+ letters may differ by one letter (§3 step 4). Ranking: exact title → title start → title words → text,
 * then the row's boost; at most 20 results, grouped by kind. (A MySQL FULLTEXT pre-filter can be added in front of the
 * same matcher when the index grows — the results do not change.)
 */
final class Search
{
    public const MAX_QUERY = 100;

    public const LIMIT = 20;

    /** Result groups in display order → the entity types they hold. */
    public const GROUPS = [
        'menu' => ['product', 'category'],
        'branch' => ['branch'],
        'page' => ['page'],
        'faq' => ['faq'],
    ];

    /** @var array<string, Collection<int, SearchEntry>> */
    private array $rows = [];

    public function __construct(private readonly SearchIndexer $indexer) {}

    public function search(string $query, string $locale, string $scope = 'PUBLIC'): SearchResults
    {
        $query = mb_substr(trim($query), 0, self::MAX_QUERY);
        $normalized = Normalizer::normalize($query);
        $tokens = $normalized === '' ? [] : explode(' ', $normalized);
        if ($tokens === []) {
            return new SearchResults($query, [], 0);
        }

        $rows = $this->rows($scope);
        $found = $this->match($rows, $tokens, tolerant: false);
        if ($found === []) {
            $found = $this->match($rows, $tokens, tolerant: true);
        }

        $order = array_flip(array_keys(self::GROUPS));
        usort($found, function (array $a, array $b) use ($order): int {
            return [$b['score'], $order[$a['group']], $a['row']->sort] <=> [$a['score'], $order[$b['group']], $b['row']->sort];
        });
        $found = array_slice($found, 0, self::LIMIT);

        $groups = [];
        foreach (array_keys(self::GROUPS) as $group) {
            foreach ($found as $hit) {
                if ($hit['group'] === $group) {
                    $groups[$group][] = $this->hit($hit['row'], $locale);
                }
            }
        }

        return new SearchResults($query, $groups, count($found));
    }

    /**
     * @param  Collection<int, SearchEntry>  $rows
     * @param  list<string>  $tokens
     * @return list<array{row: SearchEntry, score: int, group: string}>
     */
    private function match(Collection $rows, array $tokens, bool $tolerant): array
    {
        $query = implode(' ', $tokens);
        $found = [];
        foreach ($rows as $row) {
            if (! Normalizer::tokensMatch($tokens, explode(' ', $row->normalized_text), $tolerant)) {
                continue;
            }
            $score = match (true) {
                $row->normalized_title === $query => 300,
                str_starts_with($row->normalized_title, $query) => 200,
                Normalizer::tokensMatch($tokens, explode(' ', $row->normalized_title), $tolerant) => 100,
                default => 0,
            };
            $found[] = ['row' => $row, 'score' => $score + $row->boost, 'group' => $this->group($row->entity_type)];
        }

        return $found;
    }

    private function group(string $type): string
    {
        foreach (self::GROUPS as $group => $types) {
            if (in_array($type, $types, true)) {
                return $group;
            }
        }

        return 'page';
    }

    private function hit(SearchEntry $row, string $locale): SearchHit
    {
        $other = $locale === 'ar' ? 'en' : 'ar';
        [$title, $titleLang] = $this->localized($row->{'title_'.$locale}, $row->{'title_'.$other}, $other);
        [$meta, $metaLang] = $this->localized($row->{'meta_'.$locale}, $row->{'meta_'.$other}, $other);

        return new SearchHit($row->entity_type, (string) $title, $titleLang, $meta, $metaLang, (string) ($row->{'url_'.$locale} ?? $row->{'url_'.$other}));
    }

    /** @return array{0: ?string, 1: ?string} the text in the page language, else the other language with its lang. */
    private function localized(mixed $own, mixed $other, string $otherLocale): array
    {
        if (is_string($own) && $own !== '') {
            return [$own, null];
        }

        return is_string($other) && $other !== '' ? [$other, $otherLocale] : [null, null];
    }

    /** @return Collection<int, SearchEntry> */
    private function rows(string $scope): Collection
    {
        if (! isset($this->rows[$scope])) {
            if ($scope === 'PUBLIC' && ! SearchEntry::query()->where('scope', $scope)->exists()) {
                $this->indexer->rebuild(); // derived data: an empty index (fresh deploy) is simply built
            }
            $this->rows[$scope] = SearchEntry::query()->where('scope', $scope)->orderBy('sort')->get();
        }

        return $this->rows[$scope];
    }
}
