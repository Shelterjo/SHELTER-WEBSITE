<?php

namespace Tests\Feature\Core;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * deploy-facts §a: .env.example is the one list a server's .env is written from. Every variable the config reads is in
 * it (set, or named under "Optional"), nothing in it is dead, no secret has a value, and env() is read only in config/ —
 * anywhere else it runs before .env is loaded (bootstrap/app.php, F-1) or returns null once config is cached.
 */
class EnvExampleTest extends TestCase
{
    /** Secrets: always empty in the template. */
    private const SECRETS = [
        'APP_KEY', 'APP_PREVIOUS_KEYS', 'DB_PASSWORD', 'SHELTER_BASIC_AUTH_PASSWORD', 'RECRUITMENT_ID_ENC_KEY',
        'RECRUITMENT_ID_ENC_KEYS_PREVIOUS', 'RECRUITMENT_ID_HMAC_KEY', 'AI_ANTHROPIC_KEY',
    ];

    private function example(): string
    {
        return (string) file_get_contents(base_path('.env.example'));
    }

    /** @return list<string> variable names read by the PHP files in these folders */
    private function read(string ...$folders): array
    {
        $keys = [];
        foreach ((new Finder)->files()->in($folders)->name('*.php') as $file) {
            // env('KEY' …) and the small helpers some config files wrap it in ($bool('KEY' …), $int(…), $str(…)).
            preg_match_all('/(?:\benv|\$[a-z]+)\(\s*\'([A-Z][A-Z0-9_]+)\'/', $file->getContents(), $m);
            array_push($keys, ...$m[1]);
        }
        $keys = array_values(array_unique($keys));
        sort($keys);

        return $keys;
    }

    public function test_every_variable_the_config_reads_is_in_the_example(): void
    {
        $example = $this->example();
        $missing = array_values(array_filter(
            $this->read(config_path()),
            fn (string $key): bool => preg_match('/(?<![A-Z0-9_])'.$key.'(?![A-Z0-9_])/', $example) !== 1,
        ));

        $this->assertSame([], $missing, 'Add these to .env.example: set them, or name them under "Optional" if the default is right.');
    }

    public function test_every_variable_in_the_example_is_read(): void
    {
        preg_match_all('/^#?\s?([A-Z][A-Z0-9_]+)=/m', $this->example(), $m);
        $read = $this->read(config_path(), base_path('vendor/laravel/framework/config'));

        $this->assertSame([], array_values(array_diff($m[1], $read)), 'Nothing reads these: remove them from .env.example.');
    }

    public function test_no_secret_has_a_value(): void
    {
        foreach (self::SECRETS as $key) {
            $this->assertMatchesRegularExpression('/^'.$key.'=$/m', $this->example(), "{$key} must stay empty in the template");
        }
    }

    public function test_env_is_read_only_in_config(): void
    {
        $outside = [];
        foreach ((new Finder)->files()->in([app_path(), base_path('bootstrap'), base_path('routes'), database_path(), resource_path('views')])->name('*.php')->notPath('cache') as $file) {
            if (preg_match('/(?<![\w>$:@])env\(/', $file->getContents()) === 1) {
                $outside[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $outside, 'Read the value through config(): env() here runs before .env is loaded or returns null once config is cached.');
    }
}
