<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Menu version (MV-2026-10-01). Imports never erase history (D-107).
 *
 * @property int $id
 * @property string $code
 * @property string $status
 * @property Carbon $effective_from
 * @property string|null $source_file
 * @property string|null $source_hash
 */
class MenuVersion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['effective_from' => 'date'];
    }
}
