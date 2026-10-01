<?php

namespace App\Services\Site;

use App\Models\Market;

/**
 * The market a page belongs to: the one resolved from the URL (/ar/jo/…, ResolveMarket), or — on brand-layer pages
 * (/ar/, 404) — the first active market. V1 has one market (jo, D-031); when a second one is activated the brand
 * pages need a market choice (I18N-002) instead of this fallback. No country code is hardcoded.
 */
final class Markets
{
    private ?Market $fallback = null;

    private bool $looked = false;

    public function current(): ?Market
    {
        if (app()->bound(Market::class)) {
            $market = app(Market::class);

            return $market instanceof Market && $market->exists ? $market : null;
        }
        if (! $this->looked) {
            $this->looked = true;
            $this->fallback = Market::query()->where('is_active', true)->orderBy('id')->first();
        }

        return $this->fallback;
    }
}
