<?php

namespace App\Services\Core;

use App\Models\FeatureFlag;
use App\Models\User;

/**
 * Safe Mode and other switches (SAFE-MODE.md). Unknown flags are OFF. Every change is audited.
 */
final class FeatureFlags
{
    public const SAFE_MODE = 'safe_mode';

    public const MAINTENANCE = 'maintenance';

    /** @var array<string, bool>|null */
    private ?array $memo = null;

    public function __construct(private readonly AuditLogger $audit) {}

    public function enabled(string $key): bool
    {
        $this->memo ??= FeatureFlag::query()->pluck('enabled', 'key')->map(fn (mixed $v): bool => (bool) $v)->all();

        return $this->memo[$key] ?? false;
    }

    public function set(string $key, bool $enabled, User $by, string $reason): FeatureFlag
    {
        $flag = FeatureFlag::query()->firstOrNew(['key' => $key]);
        $before = $flag->exists && $flag->enabled;
        $flag->fill(['enabled' => $enabled, 'updated_by' => $by->id])->save();
        $this->audit->record('flags.updated', $flag, ['before' => [$key => $before], 'after' => [$key => $enabled]], ['reason' => $reason], actor: $by);
        $this->memo = null;

        return $flag;
    }
}
