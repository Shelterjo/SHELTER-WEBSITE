<?php

namespace App\Services\Site;

use App\Models\SocialLink;
use App\Services\MasterData\FactRegistry;

/**
 * The social accounts the site may link to (CONTACT-026/027): switched on and approved by the Owner with exactly this
 * address — anything else (or a link changed behind the dashboard's back) is not shown.
 */
final class SocialLinks
{
    public function __construct(private readonly FactRegistry $facts) {}

    /** @return array<string, string> platform => https URL */
    public function published(): array
    {
        $links = [];
        foreach (SocialLink::query()->where('is_active', true)->whereNotNull('url')->orderBy('sort')->get() as $link) {
            if ($this->facts->isPublishable('social.'.$link->platform, $link->url)) {
                $links[$link->platform] = (string) $link->url;
            }
        }

        return $links;
    }
}
