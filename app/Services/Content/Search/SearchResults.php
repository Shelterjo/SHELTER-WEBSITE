<?php

namespace App\Services\Content\Search;

/** Results grouped by kind in the fixed order of GLOBAL-SEARCH §4 (menu · branches · pages · questions). */
final readonly class SearchResults
{
    /** @param  array<string, list<SearchHit>>  $groups */
    public function __construct(
        public string $query,
        public array $groups,
        public int $total,
    ) {}
}
