<?php

namespace App\Services\Media;

use App\Models\Media;
use Carbon\CarbonImmutable;

/**
 * THE publication rule for an asset (MEDIA-RIGHTS §3) — one function used by every public view, the image pipeline
 * and the publish guard. An asset may be used on a channel only when ALL hold:
 *  1. the Owner approved it (approval_status APPROVED) and it is not archived;
 *  2. the channel is allowed (ok_website for the site, ok_ads for ads);
 *  3. its rights have not expired;
 *  4. nobody is in it, or every person in it consented for that channel and has not withdrawn;
 *  5. its source is not stock, AI, Google or the old site — unless that very asset was explicitly approved.
 */
final class MediaRights
{
    public const CHANNELS = ['website', 'ads'];

    public static function canUse(?Media $media, string $channel = 'website', ?CarbonImmutable $now = null): bool
    {
        return $media !== null && in_array($channel, self::CHANNELS, true) && self::problems($media, $channel, $now) === [];
    }

    /**
     * Why the asset may not be used on the channel — the same five checks, as reasons the Owner Dashboard can name
     * (empty = usable): not_approved · rejected · archived · channel_off · expired · people_not_recorded ·
     * people_scope · people_withdrawn · source_restricted.
     *
     * @return list<string>
     */
    public static function problems(Media $media, string $channel = 'website', ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $problems = [];
        if ($media->archived_at !== null) {
            $problems[] = 'archived';
        }
        if ($media->approval_status !== Media::APPROVED) {
            $problems[] = $media->approval_status === Media::REJECTED ? 'rejected' : 'not_approved';
        }
        if (! ($channel === 'website' ? $media->ok_website : $media->ok_ads)) {
            $problems[] = 'channel_off';
        }
        if ($media->rights_expires_at !== null && ! $media->rights_expires_at->greaterThan($now)) {
            $problems[] = 'expired';
        }
        $people = self::peopleProblem($media, $channel);
        if ($people !== null) {
            $problems[] = $people;
        }
        if (in_array($media->source, Media::RESTRICTED_SOURCES, true) && ! $media->source_explicitly_approved) {
            $problems[] = 'source_restricted';
        }

        return $problems;
    }

    private static function peopleProblem(Media $media, string $channel): ?string
    {
        if ($media->people_consent === 'none') {
            return null;
        }
        if ($media->people_consent !== 'recorded' || $media->people_consents === null || $media->people_consents === []) {
            return 'people_not_recorded';
        }
        foreach ($media->people_consents as $person) {
            if (! empty($person['withdrawn_at'])) {
                return 'people_withdrawn';
            }
            if (! in_array($channel, $person['scopes'] ?? [], true)) {
                return 'people_scope';
            }
        }

        return null;
    }
}
