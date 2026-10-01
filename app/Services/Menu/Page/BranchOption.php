<?php

namespace App\Services\Menu\Page;

use App\Services\Site\StatusTimeline;

/**
 * A branch in the menu's branch selector (F-18): its slug (?branch=drive), its selector label (DRIVE), the status line
 * computed on the server, and the effective opening intervals the browser uses to refresh the status every minute.
 * `status` and `intervals` are empty when the branch's hours are not publishable (then no status line is shown).
 */
final readonly class BranchOption
{
    /**
     * @param  array{state: string, text: string}|null  $status  state = open · closing · closed
     * @param  list<array{0: int, 1: int}>  $intervals  [opens, closes] as Unix milliseconds
     */
    public function __construct(
        public string $slug,
        public string $label,
        public ?array $status,
        public array $intervals,
        public ?StatusTimeline $timeline = null,
    ) {}
}
