<?php

namespace Tests\Support;

use App\Enums\BranchType;
use App\Models\Branch;
use App\Models\City;
use App\Models\Country;
use App\Models\Market;

/** Minimal market → country → city → branch tree for tests. Values here are test data, not business facts. */
trait MasterDataFixtures
{
    protected function market(): Market
    {
        return Market::query()->firstOrCreate(['code' => 'jo'], [
            'name_ar' => 'الأردن', 'name_en' => 'Jordan', 'default_locale' => 'ar', 'locales' => ['ar', 'en'],
            'currency' => 'JOD', 'timezone' => 'Asia/Amman', 'phone_country_code' => '962', 'is_active' => true,
        ]);
    }

    protected function city(): City
    {
        $country = Country::query()->firstOrCreate(['iso2' => 'JO'], ['market_id' => $this->market()->id, 'name_ar' => 'الأردن', 'name_en' => 'Jordan']);

        return City::query()->firstOrCreate(['country_id' => $country->id, 'slug' => 'test-city'], ['name_ar' => 'مدينة', 'name_en' => 'City']);
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: string}>  $hours  weekday, opens, closes
     * @param  array<string, mixed>  $attributes
     */
    protected function branch(string $code = 'BR-TEST', array $hours = [], array $attributes = []): Branch
    {
        $branch = Branch::query()->create(array_merge([
            'code' => $code, 'city_id' => $this->city()->id, 'slug' => strtolower($code), 'type' => BranchType::DriveThru,
            'is_public' => true, 'name_ar' => 'فرع تجريبي', 'name_en' => 'Test branch',
        ], $attributes));
        foreach ($hours as [$weekday, $opens, $closes]) {
            $branch->hours()->create(['weekday' => $weekday, 'opens_at' => $opens, 'closes_at' => $closes]);
        }

        return $branch;
    }
}
