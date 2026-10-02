<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The Owner's own view preferences (user_preferences — CAREERS-061/062/063): which columns, how dense, how many rows.
 * Only how screens look for that account; never business data.
 */
final class Preferences
{
    public function get(User $user, string $key, mixed $default = null): mixed
    {
        $value = DB::table('user_preferences')->where('user_id', $user->id)->where('key', $key)->value('value_json');

        return is_string($value) ? json_decode($value, true) : $default;
    }

    public function set(User $user, string $key, mixed $value): void
    {
        DB::table('user_preferences')->updateOrInsert(['user_id' => $user->id, 'key' => $key], [
            'value_json' => json_encode($value, JSON_UNESCAPED_UNICODE), 'updated_at' => now(), 'created_at' => now(),
        ]);
    }

    public function forget(User $user, string $key): void
    {
        DB::table('user_preferences')->where('user_id', $user->id)->where('key', $key)->delete();
    }
}
