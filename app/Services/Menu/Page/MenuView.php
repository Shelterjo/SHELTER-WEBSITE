<?php

namespace App\Services\Menu\Page;

/** Everything the public menu page renders (Menu IA spec §2): season, sections in the approved order, branches. */
final readonly class MenuView
{
    /**
     * @param  list<MenuSection>  $sections
     * @param  list<BranchOption>  $branches
     */
    public function __construct(
        public string $locale,
        public string $version,
        public string $timezone,
        public ?MenuSection $season,
        public array $sections,
        public array $branches,
        public string $branch,
    ) {}

    /** @return list<MenuSection> season first (when active), then the categories */
    public function allSections(): array
    {
        return $this->season === null ? $this->sections : [$this->season, ...$this->sections];
    }

    /** @return list<MenuItem> */
    public function items(): array
    {
        return array_merge(...array_map(fn (MenuSection $section): array => $section->items(), $this->allSections()));
    }

    /** @return list<string> */
    public function branchSlugs(): array
    {
        return array_map(fn (BranchOption $branch): string => $branch->slug, $this->branches);
    }

    public function branchLabel(string $slug): string
    {
        foreach ($this->branches as $branch) {
            if ($branch->slug === $slug) {
                return $branch->label;
            }
        }

        return strtoupper($slug);
    }
}
