<?php

namespace App\Services\Content;

/** One rendered block of a content page in one language: `text` (optional heading) or `faq` (question → answer). */
final readonly class ContentSection
{
    /** @param  list<string>  $paragraphs */
    public function __construct(
        public string $type,
        public ?string $heading,
        public array $paragraphs,
    ) {}
}
