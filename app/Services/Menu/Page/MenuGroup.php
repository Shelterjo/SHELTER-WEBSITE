<?php

namespace App\Services\Menu\Page;

/**
 * A run of products inside a section: an approved subsection (H3), one category of a display group (SWEETS → CAKE /
 * COOKIES), the "MORE" group for products without an approved subsection, or the untitled whole category.
 */
final readonly class MenuGroup
{
    /** @param  list<MenuItem>  $items */
    public function __construct(
        public ?string $id,
        public ?string $name,
        public ?string $nameLang,
        public ?string $code,
        public array $items,
    ) {}
}
