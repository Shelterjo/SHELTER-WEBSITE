<?php

namespace App\Services\Core;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * The one audit trail (AUDIT-*, M35 §46). Sensitive keys are masked before anything is stored;
 * the IP address is kept only for security events (auth.*), never for content changes.
 */
final class AuditLogger
{
    /** meta key naming who acted when no user did: 'system' (a job, a command) or 'applicant' (a visitor's form). */
    public const ACTOR_TYPE = 'actor_type';

    public const SYSTEM = 'system';

    public function __construct(private readonly AuthFactory $auth, private readonly Request $request) {}

    /**
     * @param  array<string, mixed>  $changes  e.g. ['before' => [...], 'after' => [...]]
     * @param  array<string, mixed>  $meta
     * @param  list<string>  $channels
     */
    public function record(
        string $action,
        ?Model $subject = null,
        array $changes = [],
        array $meta = [],
        array $channels = [],
        ?User $actor = null,
    ): AuditLog {
        $user = $actor ?? $this->auth->guard()->user();

        return $this->write($user instanceof User ? $user : null, $action, $subject, $changes, $meta, $channels);
    }

    /**
     * A change made by the system itself — a scheduled job, a command, an import (AUDIT-002: no silent change, the
     * actor is "system"). Never attributed to whoever happens to be signed in; the job name says which task did it.
     *
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>  $meta
     * @param  list<string>  $channels
     */
    public function system(string $action, string $job, ?Model $subject = null, array $changes = [], array $meta = [], array $channels = []): AuditLog
    {
        return $this->write(null, $action, $subject, $changes, $meta + [self::ACTOR_TYPE => self::SYSTEM, 'job' => $job], $channels);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public static function mask(array $data): array
    {
        /** @var list<string> $masked */
        $masked = config('shelter.audit.masked_keys', []);
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $masked, true)) {
                $data[$key] = '[MASKED]';
            } elseif (is_array($value)) {
                $data[$key] = self::mask($value);
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>  $meta
     * @param  list<string>  $channels
     */
    private function write(?User $user, string $action, ?Model $subject, array $changes, array $meta, array $channels): AuditLog
    {
        $log = new AuditLog([
            'user_id' => $user?->id,
            'action' => $action,
            'changes' => $changes === [] ? null : self::mask($changes),
            'meta' => $meta === [] ? null : self::mask($meta),
            'channels_affected' => $channels === [] ? null : $channels,
            'ip_address' => str_starts_with($action, 'auth.') ? $this->request->ip() : null,
        ]);
        if ($subject !== null) {
            $log->subject()->associate($subject);
        }
        $log->save();

        return $log;
    }
}
