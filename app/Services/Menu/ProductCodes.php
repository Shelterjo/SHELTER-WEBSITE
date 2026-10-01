<?php

namespace App\Services\Menu;

use App\Models\Product;

/** Next product code. Retired codes count, so no ID is ever reused (D-125, D-136: next is PRD-00193). */
final class ProductCodes
{
    public function next(): string
    {
        $max = 0;
        foreach (Product::query()->pluck('code') as $code) {
            $max = max($max, (int) substr((string) $code, 4));
        }

        return sprintf('PRD-%05d', $max + 1);
    }
}
