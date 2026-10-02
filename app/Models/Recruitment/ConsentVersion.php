<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The careers consent text, versioned (M28 §21). Version 1 is the Owner's text, word for word.
 *
 * @property int $id
 * @property string $version
 * @property string $text_ar
 * @property Carbon $active_from
 * @property bool $is_active
 */
class ConsentVersion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['active_from' => 'datetime', 'is_active' => 'boolean'];
    }
}
