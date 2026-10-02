<?php

namespace App\Services\Content\Search;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Cache as CacheFacade;

/**
 * Keeps the derived search index in step with what visitors see (GLOBAL-SEARCH §2, CMS-018): any saved change to a
 * menu item, category, search word, event or branch marks the index as changed, and the index also expires at the next
 * moment the season or an event starts or ends. The next search then rebuilds it (one at a time, under a lock) — no
 * nightly job is needed for correctness. The state is operational data in the cache: losing it only costs one rebuild.
 */
final class SearchFreshness
{
    private const CHANGED = 'search:index:changed_at';

    private const STATE = 'search:index:state';

    public function __construct(private readonly Cache $cache) {}

    public function invalidate(): void
    {
        $this->cache->forever(self::CHANGED, microtime(true));
    }

    /** Called by the indexer: built from data read after $startedAt; valid until $validUntil (null = no time limit). */
    public function built(float $startedAt, ?CarbonImmutable $validUntil): void
    {
        $this->cache->forever(self::STATE, ['built_at' => $startedAt, 'valid_until' => $validUntil?->getTimestamp()]);
    }

    public function stale(?CarbonImmutable $now = null): bool
    {
        $state = $this->cache->get(self::STATE);
        if (! is_array($state) || ! is_numeric($state['built_at'] ?? null)) {
            return true;
        }
        $changed = $this->cache->get(self::CHANGED);
        $until = $state['valid_until'] ?? null;

        return (is_numeric($changed) && (float) $changed >= (float) $state['built_at'])
            || (is_int($until) && ($now ?? CarbonImmutable::now())->getTimestamp() >= $until);
    }

    /** Runs the rebuild unless another request is already doing it (that one's result is used next time). */
    public function rebuild(Closure $rebuild): void
    {
        CacheFacade::lock('search:index:rebuild', 120)->get($rebuild);
    }
}
