<?php

namespace App\Console\Commands;

use App\Services\Content\Search\SearchIndexer;
use Illuminate\Console\Command;

/** Rebuilds the derived search index from published data (GLOBAL-SEARCH §3: deterministic, safe to run any time). */
final class SearchRebuild extends Command
{
    protected $signature = 'search:rebuild';

    protected $description = 'Rebuild the public search index from published data';

    public function handle(SearchIndexer $indexer): int
    {
        $this->info('search:rebuild: '.$indexer->rebuild().' rows');

        return self::SUCCESS;
    }
}
