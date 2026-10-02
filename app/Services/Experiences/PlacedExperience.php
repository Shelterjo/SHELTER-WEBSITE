<?php

namespace App\Services\Experiences;

use Carbon\CarbonImmutable;

/** What one placement shows now, in the page language (DX-010/011): approved text only, a safe link or none. */
final readonly class PlacedExperience
{
    public function __construct(
        public int $id,
        public string $type,
        public string $placement,
        public string $title,
        public ?string $text,
        public ?string $ctaLabel,
        public ?string $ctaUrl,
        public bool $urgent,
        public CarbonImmutable $endsAt,
    ) {}
}
