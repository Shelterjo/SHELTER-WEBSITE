<?php

namespace App\Enums;

/** Shared lifecycle for publishable content (CMS: Draft → Preview → Publish / Schedule → Archive). */
enum PublishStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
}
