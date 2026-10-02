<?php

namespace App\Services\Content;

use App\Models\Fact;

/**
 * Phrases that never go public. Two sources, one rule for every surface the Owner publishes from (pages, events,
 * announcements and campaigns, site texts):
 * - the phrases blocked for one page (config content.blocked_phrases.{key} — e.g. guaranteed profit on the franchise
 *   page, FRAN-097);
 * - every phrase a fact blocks everywhere (facts.blocked_phrases — e.g. an old or wrong founding year, D-018).
 * Matching is case-insensitive on the plain text; a hit keeps the text from being published and names the phrase.
 */
final class ContentGuard
{
    /** @return list<string> */
    public static function phrases(?string $scope = null): array
    {
        /** @var list<string> $scoped */
        $scoped = $scope !== null ? config('content.blocked_phrases.'.$scope, []) : [];

        return array_values(array_unique([...$scoped, ...self::factPhrases()]));
    }

    /** The first blocked phrase found in the text, or null. */
    public static function find(?string $text, ?string $scope = null): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }
        foreach (self::phrases($scope) as $phrase) {
            if ($phrase !== '' && mb_stripos($text, $phrase) !== false) {
                return $phrase;
            }
        }

        return null;
    }

    /**
     * The field holding the first blocked phrase, with the phrase.
     *
     * @param  array<string, string|null>  $fields
     * @return array{0: string, 1: string}|null
     */
    public static function firstHit(array $fields, ?string $scope = null): ?array
    {
        foreach ($fields as $field => $text) {
            $phrase = self::find($text, $scope);
            if ($phrase !== null) {
                return [$field, $phrase];
            }
        }

        return null;
    }

    /** @return list<string> */
    private static function factPhrases(): array
    {
        return array_values(once(fn (): array => Fact::query()->whereNotNull('blocked_phrases')->pluck('blocked_phrases')
            ->flatMap(fn ($phrases): array => is_array($phrases) ? $phrases : [])
            ->filter(fn ($phrase): bool => is_string($phrase) && trim($phrase) !== '')
            ->map(fn (string $phrase): string => trim($phrase))
            ->unique()->values()->all()));
    }
}
