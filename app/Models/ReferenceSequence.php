<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Server-side counters for PREFIX-YYYY-NNNNN reference numbers (JOB / FR / INQ).
 *
 * @property int $id
 * @property string $prefix
 * @property int $year
 * @property int $last_number
 */
class ReferenceSequence extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['year' => 'integer', 'last_number' => 'integer'];
    }
}
