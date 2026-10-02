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
        if ($media === null || ! in_array($channel, self::CHANNELS, true)) {
            return false;
        }
        $now ??= CarbonImmutable::now();

        return $media->approval_status === Media::APPROVED && $media->archived_at === null
            && ($channel === 'website' ? $media->ok_website : $media->ok_ads)
            && ($media->rights_expires_at === null || $media->rights_expires_at->greaterThan($now))
            && self::peopleConsented($media, $channel)
            && (! in_array($media->source, Media::RESTRICTED_SOURCES, true) || $media->source_explicitly_approved);
    }

    private static function peopleConsented(Media $media, string $channel): bool
    {
        if ($media->people_consent === 'none') {
            return true;
        }
        if ($media->people_consent !== 'recorded' || $media->people_consents === null || $media->people_consents === []) {
            return false;
        }
        foreach ($media->people_consents as $person) {
            if (! empty($person['withdrawn_at']) || ! in_array($channel, $person['scopes'] ?? [], true)) {
                return false;
            }
        }

        return true;
    }
}
