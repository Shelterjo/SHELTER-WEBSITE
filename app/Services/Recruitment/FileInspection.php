<?php

namespace App\Services\Recruitment;

/** Result of inspecting one upload: accepted (family + detected MIME) or rejected with a reason code. */
final readonly class FileInspection
{
    public function __construct(
        public bool $accepted,
        public ?string $family = null,
        public ?string $mime = null,
        public ?string $reason = null,
    ) {}

    public static function accept(string $family, string $mime): self
    {
        return new self(true, $family, $mime);
    }

    /** @param  'dangerous'|'macro'|'archive'|'unsupported'|'mismatch'|'active_content'|'empty' $reason */
    public static function reject(string $reason): self
    {
        return new self(false, reason: $reason);
    }
}
