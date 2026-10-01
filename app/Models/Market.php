<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Market configuration (INTERNATIONALIZATION.md). Adding a market is data, not code.
 *
 * @property int $id
 * @property string $code
 * @property string $name_ar
 * @property string $name_en
 * @property string $default_locale
 * @property list<string> $locales
 * @property string $currency
 * @property string $timezone
 * @property string $phone_country_code
 * @property bool $is_active
 */
class Market extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['locales' => 'array', 'is_active' => 'boolean'];
    }

    /** @return HasMany<Country, $this> */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public function name(?string $locale = null): string
    {
        return ($locale ?? app()->getLocale()) === 'en' ? $this->name_en : $this->name_ar;
    }

    /** URLs carry the market code (/ar/jo/…), never the numeric id; ResolveMarket still rejects inactive markets. */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
