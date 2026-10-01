<?php

namespace App\Enums;

/** V1 has one role. Future roles are created only by the owner (PERM, M30). */
enum Role: string
{
    case Owner = 'owner';
}
