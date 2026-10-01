<?php

namespace App\Services\Menu\Page;

use App\Enums\Availability;

/**
 * One product as the public menu shows it (Menu IA spec §6): approved names only, the VAT-inclusive price in fils,
 * per-branch availability and price differences. Built by MenuPage; never carries unapproved display text.
 */
final readonly class MenuItem
{
    /**
     * @param  array<string, Availability>  $availability  branch slug => availability (UNKNOWN until confirmed)
     * @param  array<string, int>  $branchPrices  branch slug => fils, only where a branch override differs from the base
     * @param  list<string>  $searchTerms  extra names used only to match a search (source / normalized), never shown
     */
    public function __construct(
        public string $code,
        public string $slug,
        public string $name,
        public ?string $nameLang,
        public ?string $secondary,
        public ?string $secondaryLang,
        public string $nameEn,
        public int $priceFils,
        public string $currency,
        public bool $seasonal,
        public string $categoryCode,
        public ?string $subcategoryCode,
        public string $sectionId,
        public string $location,
        public array $availability,
        public array $branchPrices,
        public array $searchTerms,
    ) {}

    public function anchor(): string
    {
        return 'p-'.$this->slug;
    }

    /** True when at least one branch has confirmed availability data (otherwise nothing about availability is shown). */
    public function hasKnownAvailability(): bool
    {
        foreach ($this->availability as $state) {
            if ($state !== Availability::Unknown) {
                return true;
            }
        }

        return false;
    }

    public function priceFor(string $branch): int
    {
        return $this->branchPrices[$branch] ?? $this->priceFils;
    }
}
