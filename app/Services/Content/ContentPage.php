<?php

namespace App\Services\Content;

use Illuminate\Support\Carbon;

/** A published content page ready for a view, in one language. */
final readonly class ContentPage
{
    /** @param  list<ContentSection>  $sections */
    public function __construct(
        public string $key,
        public string $type,
        public string $title,
        public ?string $description,
        public array $sections,
        public ?Carbon $updatedAt,
    ) {}
}
