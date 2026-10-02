<?php

namespace App\Services\Experiences;

use App\Services\Media\MediaImage;

/** The Employee of the Month as a visitor sees it (DX-007/009), in the page language: approved text and photo only. */
final readonly class ShownRecognition
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $text,
        public string $name,
        public string $jobTitle,
        public MediaImage $image,
        public string $period,
    ) {}
}
