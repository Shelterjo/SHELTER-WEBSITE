<?php

namespace App\Services\Menu\Page;

/**
 * One H2 section of the menu page: a category (CAT-…), a display group (SWEETS = CAKE + COOKIES, F-07) or the
 * seasonal section (SPRING, F-17). `id` is the stable in-page anchor (#hot-drinks).
 */
final readonly class MenuSection
{
    /** @param  list<MenuGroup>  $groups */
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $nameLang,
        public array $groups,
        public bool $subnav,
    ) {}

    /** @return list<MenuItem> */
    public function items(): array
    {
        return array_merge(...array_map(fn (MenuGroup $group): array => $group->items, $this->groups));
    }

    public function count(): int
    {
        return count($this->items());
    }
}
