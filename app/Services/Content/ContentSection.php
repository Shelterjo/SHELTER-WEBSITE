<?php

namespace App\Services\Content;

use App\Services\Media\MediaImage;

/**
 * One rendered block of a content page in one language: `text` (optional heading), `faq` (question → answer), `list` and
 * `steps` (one item per line), `cards` (one card per paragraph: first line = its title) or `cta` (closing call to act).
 */
final readonly class ContentSection
{
    /** @param  list<string>  $paragraphs */
    public function __construct(
        public string $type,
        public ?string $heading,
        public array $paragraphs,
        public ?MediaImage $image = null,
    ) {}
}
