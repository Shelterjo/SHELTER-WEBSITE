<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Single system-wide audit trail (AUDIT-*, M35 §46). Append-only: never updated or deleted by the application.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property array<string, mixed>|null $changes
 * @property list<string>|null $channels_affected
 * @property array<string, mixed>|null $meta
 * @property string|null $ip_address
 * @property Carbon $created_at
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'channels_affected' => 'array', 'meta' => 'array', 'created_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
