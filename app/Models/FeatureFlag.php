<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Safe Mode, dynamic-layer disable and maintenance switches (SAFE-MODE.md).
 *
 * @property int $id
 * @property string $key
 * @property bool $enabled
 * @property string|null $description
 * @property int|null $updated_by
 */
class FeatureFlag extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
