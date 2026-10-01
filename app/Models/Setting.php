<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Global configuration and the Brand / Global Website entities (`brand.*`, `website.*`).
 * Business values here are tied to `facts` and only shown when publishable.
 *
 * @property int $id
 * @property string $key
 * @property string $group
 * @property mixed $value
 * @property string $classification
 * @property int|null $updated_by
 */
class Setting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }
}
