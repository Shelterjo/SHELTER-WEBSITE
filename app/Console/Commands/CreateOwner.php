<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Creates the owner account on the server (no public registration exists). The password is typed in a hidden
 * prompt, never passed as an argument (it would land in shell history). Two-factor enrollment is forced at first sign-in.
 */
final class CreateOwner extends Command
{
    protected $signature = 'shelter:owner {email} {--name=Owner}';

    protected $description = 'Create the SHELTER owner account (server-side only)';

    public function handle(AuditLogger $audit): int
    {
        if (User::query()->where('role', Role::Owner->value)->where('is_active', true)->exists()) {
            $this->error('An active owner already exists. Future users are created by the owner from the dashboard.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->argument('email')));
        $password = (string) $this->secret('Password (min. 12 characters, letters and numbers)');
        $confirm = (string) $this->secret('Repeat password');

        /** @var int $min */
        $min = config('shelter.auth.password_min_length');
        $validator = Validator::make(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $confirm],
            ['email' => ['required', 'email:rfc', 'max:191', 'unique:users,email'], 'password' => ['required', 'confirmed', Password::min($min)->letters()->numbers()]],
        );
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => (string) $this->option('name'),
            'email' => $email,
            'password' => $password,
            'role' => Role::Owner,
            'is_active' => true,
        ]);
        $user->forceFill(['password_changed_at' => now()])->save();
        $audit->record('auth.owner_created', $user, actor: $user);
        $this->info('Owner created. Sign in at /dashboard/login to set up two-step verification.');

        return self::SUCCESS;
    }
}
