<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Consent texts, versioned per scope (PLATFORM-ARCHITECTURE §3.4): careers v1 is the Owner's text word for word
 * (M28 §21); partnerships, inquiries and feedback wait for their approved texts.
 *
 * @property int $id
 * @property string $scope careers | partnerships | inquiries | feedback
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
