<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The Owner's wording for one listed site text in one language (null = the original wording). See SiteTexts.
 *
 * @property int $id
 * @property string $key
 * @property string $locale
 * @property string|null $value
 * @property int|null $updated_by
 */
class SiteText extends Model
{
    protected $guarded = ['id'];
}
