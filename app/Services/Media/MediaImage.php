<?php

namespace App\Services\Media;

/**
 * An approved image ready for <picture> (x-ui.picture): the fallback URL, one srcset per format (best first), the
 * intrinsic size that reserves its space (CLS 0) and the alternative text in the page language.
 */
final readonly class MediaImage
{
    /** @param  array<string, string>  $sources  MIME type => srcset ("url 480w, url 960w") */
    public function __construct(
        public string $src,
        public array $sources,
        public int $width,
        public int $height,
        public string $alt,
        public int $focalX = 50,
        public int $focalY = 50,
    ) {}
}
