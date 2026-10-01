<?php

namespace App\Services\Content\Search;

/** One result in the page language. `titleLang` / `metaLang` are set when the text is in the other language (CF-03). */
final readonly class SearchHit
{
    public function __construct(
        public string $type,
        public string $title,
        public ?string $titleLang,
        public ?string $meta,
        public ?string $metaLang,
        public string $url,
    ) {}
}
