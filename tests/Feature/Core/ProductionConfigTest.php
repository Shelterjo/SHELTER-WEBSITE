<?php

namespace Tests\Feature\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FINAL-QA QA-025: a fresh install copies .env.example, which is written for a laptop (APP_DEBUG=true,
 * SESSION_SECURE_COOKIE=false). On staging and production those two can never weaken the site.
 */
class ProductionConfigTest extends TestCase
{
    /**
     * @param  array<string, string>  $env
     * @return array<string, mixed>
     */
    private function load(string $file, array $env): array
    {
        $before = [];
        foreach ($env as $name => $value) {
            $before[$name] = $_SERVER[$name] ?? null;
            $_SERVER[$name] = $value;
        }
        try {
            /** @var array<string, mixed> $config */
            $config = require config_path($file);
        } finally {
            foreach ($before as $name => $value) {
                if ($value === null) {
                    unset($_SERVER[$name]);
                } else {
                    $_SERVER[$name] = $value;
                }
            }
        }

        return $config;
    }

    /** @return iterable<string, array{string}> */
    public static function servers(): iterable
    {
        yield 'production' => ['production'];
        yield 'staging' => ['staging'];
    }

    #[DataProvider('servers')]
    public function test_the_example_env_cannot_weaken_a_server(string $environment): void
    {
        $env = ['APP_ENV' => $environment, 'APP_DEBUG' => 'true', 'SESSION_SECURE_COOKIE' => 'false'];
        $this->assertFalse($this->load('app.php', $env)['debug'], 'no debug pages');
        $this->assertTrue($this->load('session.php', $env)['secure'], 'HTTPS-only session cookie');
    }

    public function test_a_laptop_keeps_what_its_env_says(): void
    {
        $env = ['APP_ENV' => 'local', 'APP_DEBUG' => 'true', 'SESSION_SECURE_COOKIE' => 'false'];
        $this->assertTrue($this->load('app.php', $env)['debug']);
        $this->assertFalse($this->load('session.php', $env)['secure']);
    }
}
