<?php

namespace App\Support;

use App\Services\Site\BranchSummary;

/**
 * JSON-LD for public pages (SITE-INVENTORY schema column; allowed types only). Approved values only: a branch has
 * name, url, logo, telephone (D-060) and openingHoursSpecification — no address or geo while PO-010 is open.
 * Past-midnight hours keep closes < opens in one specification (MDH-022, HOURS-012).
 */
final class StructuredData
{
    private const DAYS = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

    /**
     * @param  list<array{label: string, href: string}>  $crumbs
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $crumb, int $i): array => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['label'],
                'item' => $crumb['href'],
            ], $crumbs, array_keys($crumbs)),
        ];
    }

    /** @return array<string, mixed> */
    public static function cafe(BranchSummary $branch, ?string $telephone, string $logo): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'CafeOrCoffeeShop',
            'name' => $branch->name,
            'url' => $branch->url,
            'logo' => $logo,
        ];
        if ($telephone !== null) {
            $data['telephone'] = $telephone;
        }
        $hours = self::openingHours($branch);
        if ($hours !== []) {
            $data['openingHoursSpecification'] = $hours;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $extra  approved optional fields (e.g. foundingDate)
     * @return array<string, mixed>
     */
    public static function organization(string $name, string $alternateName, string $url, string $logo, array $extra = []): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $name,
            'alternateName' => $alternateName,
            'url' => $url,
            'logo' => $logo,
        ] + $extra;
    }

    /**
     * JSON for a <script type="application/ld+json"> block: "<", ">" and "&" are escaped, so it can never close the tag.
     *
     * @param  array<string, mixed>  $data
     */
    public static function encode(array $data): string
    {
        return (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
    }

    /** @return list<array<string, mixed>> */
    private static function openingHours(BranchSummary $branch): array
    {
        $groups = [];
        foreach ($branch->week ?? [] as $day) {
            foreach ($day['intervals'] as $interval) {
                $key = $interval['opens_at'].'-'.$interval['closes_at'];
                $groups[$key] ??= ['opens' => $interval['opens_at'], 'closes' => $interval['closes_at'], 'days' => []];
                $groups[$key]['days'][] = 'https://schema.org/'.self::DAYS[$day['weekday']];
            }
        }

        return array_values(array_map(fn (array $g): array => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $g['days'],
            'opens' => $g['opens'],
            'closes' => $g['closes'],
        ], $groups));
    }
}
