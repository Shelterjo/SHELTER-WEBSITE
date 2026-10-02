<?php

namespace App\Services\Content;

use App\Models\Award;
use App\Services\MasterData\FactRegistry;
use App\Services\Media\MediaImage;
use App\Services\Media\MediaLibrary;

/**
 * Awards a visitor may see (SI-B14, PUBLISH-GUARD, PO-032): published, not archived, title and issuer in both
 * languages, a year — and verified: the fact `award.{id}` approved in the Fact Registry with exactly these values. Any
 * edit after approval takes it offline (with a data-consistency signal) until the Owner approves again.
 */
final class Awards
{
    public function __construct(private readonly FactRegistry $facts, private readonly MediaLibrary $media) {}

    /** @return list<array{award: Award, title: string, issuer: string, year: int, description: ?string, url: ?string, image: ?MediaImage}> */
    public function published(string $locale, ?int $limit = null): array
    {
        $all = $this->cache[$locale] ??= $this->load($locale);

        return $limit === null ? $all : array_slice($all, 0, $limit);
    }

    /** @var array<string, list<array{award: Award, title: string, issuer: string, year: int, description: ?string, url: ?string, image: ?MediaImage}>> */
    private array $cache = [];

    /** @return list<array{award: Award, title: string, issuer: string, year: int, description: ?string, url: ?string, image: ?MediaImage}> */
    private function load(string $locale): array
    {
        $awards = Award::query()->with('media')->where('status', 'published')->whereNull('archived_at')
            ->orderByDesc('year')->orderBy('sort')->orderBy('id')->get();
        $list = [];
        foreach ($awards as $award) {
            if (blank($award->title_ar) || blank($award->title_en) || blank($award->issuer_ar) || blank($award->issuer_en)
                || ! $this->facts->isPublishable($award->factKey(), $award->factValue())) {
                continue;
            }
            $ar = $locale === 'ar';
            $description = $ar ? $award->description_ar : $award->description_en;
            $list[] = [
                'award' => $award,
                'title' => (string) ($ar ? $award->title_ar : $award->title_en),
                'issuer' => (string) ($ar ? $award->issuer_ar : $award->issuer_en),
                'year' => $award->year,
                'description' => filled($description) ? (string) $description : null,
                'url' => self::safeUrl($award->evidence_url),
                'image' => $this->media->image($award->media, $locale),
            ];
        }

        return $list;
    }

    private static function safeUrl(?string $url): ?string
    {
        return is_string($url) && str_starts_with($url, 'https://') && filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : null;
    }
}
