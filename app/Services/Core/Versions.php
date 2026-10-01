<?php

namespace App\Services\Core;

use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * One version store for every publishable entity (PLATFORM-ARCHITECTURE §3.1, ROLLBACK.md).
 */
final class Versions
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>|null  $guardResult
     */
    public function record(
        Model $model,
        string $status,
        array $snapshot,
        ?string $reason = null,
        ?User $actor = null,
        ?array $guardResult = null,
    ): ContentVersion {
        return DB::transaction(function () use ($model, $status, $snapshot, $reason, $actor, $guardResult): ContentVersion {
            $next = (int) ContentVersion::query()
                ->where('versionable_type', $model->getMorphClass())
                ->where('versionable_id', $model->getKey())
                ->lockForUpdate()
                ->max('version') + 1;

            $version = new ContentVersion([
                'version' => $next,
                'status' => $status,
                'snapshot' => AuditLogger::mask($snapshot),
                'reason' => $reason,
                'guard_result' => $guardResult,
                'user_id' => $actor?->id,
            ]);
            $version->versionable()->associate($model);
            $version->save();

            return $version;
        });
    }

    public function latest(Model $model): ?ContentVersion
    {
        return ContentVersion::query()
            ->where('versionable_type', $model->getMorphClass())
            ->where('versionable_id', $model->getKey())
            ->orderByDesc('version')
            ->first();
    }
}
