<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

/**
 * A place interviews happen (CAREERS-048): chosen from this list only, managed in Settings.
 *
 * @property int $id
 * @property string $name_ar
 * @property string $name_en
 * @property bool $is_active
 * @property int $sort_order
 */
class InterviewLocation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
