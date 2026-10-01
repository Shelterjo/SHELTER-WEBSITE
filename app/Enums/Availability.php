<?php

namespace App\Enums;

/** Item availability per branch (F-19, D-145). Unknown = no confirmed data (D-094): never shown as a claim. */
enum Availability: string
{
    case Available = 'available';
    case UnavailableShow = 'unavailable_show';
    case UnavailableHide = 'unavailable_hide';
    case Unknown = 'unknown';
}
