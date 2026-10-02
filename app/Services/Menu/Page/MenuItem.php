<?php

namespace App\Services\Menu\Page;

use App\Enums\Availability;
use App\Services\Media\MediaImage;

/**
 * One product as the public menu shows it (Menu IA spec §6): approved names only, the VAT-inclusive price in fils,
 * per-branch availability and price differences, the description in the page language (only when written in both),
 * the approved image and the "New" badge. Built by MenuPage; never carries unapproved display text.
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
        public ?string $description = null,
        public ?MediaImage $image = null,
        public bool $isNew = false,
        public ?string $ariaLabel = null,
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

    /** True when something differs by branch (availability known somewhere, or a branch price). */
    public function variesByBranch(): bool
    {
        return $this->hasKnownAvailability() || $this->branchPrices !== [];
    }

    /**
     * What the card shows for a branch choice (Menu IA §9.6): `hidden` (UNAVAILABLE_HIDE there), `status` (null =
     * nothing), `unavailable` (faded, price still readable) and the price. UNKNOWN never produces a claim (CF-02).
     * On "all branches": "available at X only" when X is the one branch where it is AVAILABLE and every other branch
     * is known to be unavailable; hidden only when it is hidden everywhere.
     *
     * @param  array<string, string>  $labels  branch slug => selector label (DRIVE)
     * @return array{hidden: bool, status: string|null, unavailable: bool, price: int}
     */
    public function display(string $branch, array $labels): array
    {
        if ($branch !== 'all') {
            $state = $this->availability[$branch] ?? Availability::Unknown;

            return [
                'hidden' => $state === Availability::UnavailableHide,
                'status' => $state === Availability::UnavailableShow ? (string) __('menu.unavailable') : null,
                'unavailable' => $state === Availability::UnavailableShow,
                'price' => $this->priceFor($branch),
            ];
        }
        $states = [];
        foreach (array_keys($labels) as $slug) {
            $states[$slug] = $this->availability[$slug] ?? Availability::Unknown;
        }
        $available = array_keys(array_filter($states, fn (Availability $s): bool => $s === Availability::Available));
        $unavailable = array_filter($states, fn (Availability $s): bool => in_array($s, [Availability::UnavailableShow, Availability::UnavailableHide], true));
        $hiddenEverywhere = $states !== [] && array_filter($states, fn (Availability $s): bool => $s !== Availability::UnavailableHide) === [];
        $nowhere = $states !== [] && count($unavailable) === count($states);
        $status = match (true) {
            count($available) === 1 && count($unavailable) === count($states) - 1 && count($states) > 1 => (string) __('menu.only_at', ['branches' => $labels[$available[0]]]),
            $nowhere && ! $hiddenEverywhere => (string) __('menu.unavailable'),
            default => null,
        };

        return ['hidden' => $hiddenEverywhere, 'status' => $status, 'unavailable' => $nowhere, 'price' => $this->priceFils];
    }
}
