<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One question asked to Shaltoor, scrubbed of personal details (see the migration).
 *
 * @property int $id
 * @property string|null $conversation
 * @property string $locale
 * @property string $topic
 * @property bool $answered
 * @property string $question
 * @property string $normalized
 * @property string|null $page
 * @property bool $used_ai
 * @property Carbon|null $handled_at
 * @property Carbon|null $created_at
 */
class ShaltoorQuestion extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['answered' => 'boolean', 'used_ai' => 'boolean', 'handled_at' => 'datetime', 'created_at' => 'datetime'];
    }
}
