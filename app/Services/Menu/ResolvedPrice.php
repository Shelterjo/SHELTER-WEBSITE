<?php

namespace App\Services\Menu;

/** A price as the customer sees it, plus where it came from (M33 §5: Inherited vs Overridden). */
final readonly class ResolvedPrice
{
    public function __construct(public int $fils, public string $currency, public bool $overridden) {}

    public function amount(): string
    {
        return number_format($this->fils / 1000, 2, '.', '');
    }
}
