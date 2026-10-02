<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

/**
 * City list of the careers form (RECRUITMENT-DATA-MODEL §2.12). Managed from Settings; PENDING DATA VERIFICATION until
 * an official list is approved — nothing is invented, and governorates never silently replace cities.
 *
 * @property int $id
 * @property string $name_ar
 * @property string|null $name_en
 * @property string|null $governorate_ar
 * @property bool $is_active
 * @property int $sort_order
 * @property string $verification_status
 */
class JordanCity extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
