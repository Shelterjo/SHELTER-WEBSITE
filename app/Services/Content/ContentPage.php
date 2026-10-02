<?php

namespace App\Services\Content;

use Illuminate\Support\Carbon;

/**
 * A published content page ready for a view, in one language. `title` is the H1 on one line, `titleLines` the H1 as the
 * Owner broke it (e.g. "كن شريكًا في نمو" / "SHELTER COFFEE"), `name` the page name for <title>, breadcrumb and links.
 */
final readonly class ContentPage
{
    /**
     * @param  list<string>  $titleLines
     * @param  list<ContentSection>  $sections
     */
    public function __construct(
        public string $key,
        public string $type,
        public string $title,
        public array $titleLines,
        public string $name,
        public ?string $description,
        public array $sections,
        public ?Carbon $updatedAt,
    ) {}
}
