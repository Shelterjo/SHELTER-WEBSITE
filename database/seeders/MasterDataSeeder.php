<?php

namespace Database\Seeders;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Models\Branch;
use App\Models\City;
use App\Models\ContactPoint;
use App\Models\Country;
use App\Models\Market;
use App\Services\Core\Settings;
use App\Services\MasterData\FactRegistry;
use App\Services\MasterData\MasterData;
use Illuminate\Database\Seeder;

/**
 * Seeds the Master Data Hub with owner-approved values only (database/seeders/data/master-data.php).
 * Idempotent: safe to run on every environment; existing facts are never overwritten (owner changes win).
 */
class MasterDataSeeder extends Seeder
{
    public function run(FactRegistry $facts, Settings $settings): void
    {
        /** @var array<string, mixed> $data */
        $data = require __DIR__.'/data/master-data.php';

        $market = Market::query()->updateOrCreate(['code' => $data['market']['code']], $data['market']);
        $country = Country::query()->updateOrCreate(['iso2' => $data['country']['iso2']], $data['country'] + ['market_id' => $market->id]);
        $cityData = $data['city'];
        $city = City::query()->updateOrCreate(
            ['country_id' => $country->id, 'slug' => $cityData['slug']],
            ['name_ar' => $cityData['name_ar'], 'name_en' => $cityData['name_en']],
        );
        $this->fact($facts, "city.{$city->slug}.name_ar", 'branch', $city->name_ar, FactStatus::from($cityData['facts']['name_ar']['status']), $cityData['facts']['name_ar']['ref']);

        foreach ($data['branches'] as $b) {
            $branch = Branch::query()->firstOrCreate(['code' => $b['code']], [
                'city_id' => $city->id, 'slug' => $b['slug'], 'type' => $b['type'], 'sort' => $b['sort'], 'is_public' => $b['is_public'],
                'name_ar' => $b['name_ar'], 'name_en' => $b['name_en'],
            ]);
            $this->fact($facts, $branch->factKey('name_ar'), 'branch', $b['name_ar'], FactStatus::Approved, $b['names_ref']);
            $this->fact($facts, $branch->factKey('name_en'), 'branch', $b['name_en'], FactStatus::Approved, $b['names_ref']);
            foreach ($b['missing'] as $field => $ref) {
                $this->fact($facts, $branch->factKey($field), 'branch', null, FactStatus::Missing, $ref);
            }
            // Location descriptions (D-334): written once on a fresh install, never over an Owner edit (the fact exists then).
            foreach ($b['landmarks'] ?? [] as $field => $l) {
                if ($facts->current($branch->factKey($field)) !== null) {
                    continue;
                }
                if ($l['value'] !== null && $branch->getAttribute($field) === null) {
                    $branch->forceFill([$field => $l['value']])->save();
                }
                $this->fact($facts, $branch->factKey($field), 'branch', $l['value'], $l['value'] === null ? FactStatus::Missing : FactStatus::Approved, $l['ref']);
            }

            if ($branch->hours()->doesntExist()) {
                foreach ($b['hours'] as [$weekday, $opens, $closes]) {
                    $branch->hours()->create(['weekday' => $weekday, 'opens_at' => $opens, 'closes_at' => $closes]);
                }
            }
            $this->fact($facts, "hours.{$branch->code}.regular", 'hours', MasterData::normalizeHours($branch->hours()->get()), FactStatus::Approved, $b['hours_ref']);

            foreach ($data['branch_attributes'] as $group => $keys) {
                foreach ($keys as $key) {
                    $branch->branchAttributes()->firstOrCreate(['group' => $group, 'key' => $key], ['value' => null]);
                }
            }
        }

        foreach ($data['contacts'] as $sort => $c) {
            $point = ContactPoint::query()->firstOrCreate(['scope' => 'brand', 'kind' => $c['kind']], [
                'value' => $c['value'], 'is_public' => $c['is_public'], 'show_on_branch_cards' => $c['cards'], 'sort' => $sort,
            ]);
            $this->fact($facts, $point->factKey(), 'contact', $point->value, FactStatus::from($c['status']), $c['ref']);
        }

        foreach ($data['brand'] as $key => $b) {
            if (! $settings->has($key)) {
                $settings->set($key, $b['value'], reason: "seed {$b['ref']}");
            }
            $this->fact($facts, $key, 'brand', $b['value'], FactStatus::Approved, $b['ref']);
        }

        foreach ($data['rejected'] as $r) {
            $this->fact($facts, $r['key'], 'brand', $r['value'], FactStatus::Rejected, $r['ref'], $r['blocked']);
        }
    }

    /** @param list<string>|null $blocked */
    private function fact(FactRegistry $facts, string $key, string $category, mixed $value, FactStatus $status, string $ref, ?array $blocked = null): void
    {
        if ($facts->current($key) !== null) {
            return;
        }
        $isDecision = str_starts_with($ref, 'D-');
        $facts->register(
            $key, $category, $value, $status,
            $isDecision ? FactSource::OwnerDecision : FactSource::Document,
            sourceRef: $ref,
            decisionRef: $isDecision && $status->isPublishable() ? $ref : null,
            blockedPhrases: $blocked,
            notes: $status === FactStatus::Missing ? "MISSING — OWNER INPUT REQUIRED ({$ref})" : null,
        );
    }
}
