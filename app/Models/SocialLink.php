<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Official social accounts. None is published until the owner verifies it (D-025, D-036).
 *
 * @property int $id
 * @property string $platform
 * @property string|null $handle
 * @property string|null $url
 * @property bool $is_active
 * @property int $sort
 */
class SocialLink extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort' => 'integer'];
    }
}
