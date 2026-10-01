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

        $log = new AuditLog([
            'user_id' => $user instanceof User ? $user->id : null,
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
}
