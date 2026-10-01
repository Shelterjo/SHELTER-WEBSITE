<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * One version store for every publishable entity (PLATFORM-ARCHITECTURE §3.1). Rollback restores a snapshot.
 *
 * @property int $id
 * @property string $versionable_type
 * @property int $versionable_id
 * @property int $version
 * @property string $status
 * @property array<string, mixed> $snapshot
 * @property string|null $reason
 * @property array<string, mixed>|null $guard_result
 * @property Carbon|null $scheduled_at
 * @property int|null $user_id
 */
class ContentVersion extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'guard_result' => 'array', 'scheduled_at' => 'datetime', 'version' => 'integer'];
    }

    /** @return MorphTo<Model, $this> */
    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }
}
