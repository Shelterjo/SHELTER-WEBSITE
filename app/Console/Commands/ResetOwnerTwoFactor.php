<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Console\Command;

/**
 * Server-side recovery when the owner lost both the authenticator and the recovery codes.
 * Requires shell access to the server (i.e. the owner or someone the owner authorised). Audited.
 */
final class ResetOwnerTwoFactor extends Command
{
    protected $signature = 'shelter:owner:reset-2fa {email}';

    protected $description = 'Clear an owner\'s two-step verification so it is re-enrolled at next sign-in';

    public function handle(AuditLogger $audit): int
    {
        $user = User::query()->where('email', mb_strtolower(trim((string) $this->argument('email'))))->first();
        if ($user === null) {
            $this->error('No such user.');

            return self::FAILURE;
        }
        if (! $this->confirm("Reset two-step verification for {$user->email}?")) {
            return self::FAILURE;
        }

        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null, 'two_factor_last_step' => null, 'two_factor_recovery_codes' => null])->save();
        $audit->record('auth.two_factor_reset', $user, meta: ['via' => 'cli'], actor: $user);
        $this->info('Two-step verification cleared. It will be set up again at next sign-in.');

        return self::SUCCESS;
    }
}
