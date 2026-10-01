<?php

namespace App\Services\Site;

/** A contact link ready for a view: href (tel: / wa.me), the number as displayed in this locale (D-065). */
final readonly class ContactAction
{
    public function __construct(
        public string $href,
        public string $display,
    ) {}
}
