<?php

namespace Tests\Feature\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * deploy-facts F-1: TRUSTED_PROXIES was read in bootstrap/app.php before .env loads, so a value in .env was silently
 * ignored. The list now lives in config/trustedproxy.php and the framework's TrustProxies reads it per request: the client
 * address comes from X-Forwarded-For only when the peer that connects is a listed proxy.
 */
class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // One URL form per page (CanonicalTrailingSlash): the probe is asked with its trailing slash.
        Route::get('/_probe/client', fn (Request $request): array => ['ip' => $request->ip(), 'secure' => $request->isSecure()]);
    }

    /** @return array<string, mixed> */
    private function probe(string $peer): array
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $peer])
            ->getJson('/_probe/client/', ['X-Forwarded-For' => '203.0.113.7', 'X-Forwarded-Proto' => 'https'])
            ->assertOk()->json();
    }

    public function test_a_proxy_listed_in_config_is_trusted_at_request_time(): void
    {
        config(['trustedproxy.proxies' => ['10.0.0.0/8', '2001:db8::/32']]);

        $this->assertSame(['ip' => '203.0.113.7', 'secure' => true], $this->probe('10.1.2.3'), 'CIDR range (IPv4)');
        $this->assertSame(['ip' => '203.0.113.7', 'secure' => true], $this->probe('2001:db8::5'), 'CIDR range (IPv6)');
    }

    public function test_an_unlisted_peer_cannot_spoof_its_address(): void
    {
        config(['trustedproxy.proxies' => ['10.0.0.0/8']]);

        $this->assertSame(['ip' => '198.51.100.20', 'secure' => false], $this->probe('198.51.100.20'));
    }

    public function test_the_default_trusts_only_the_local_stack(): void
    {
        config(['trustedproxy.proxies' => $this->load(null)]);

        $this->assertSame('203.0.113.7', $this->probe('127.0.0.1')['ip'], 'Cloudways Nginx/Varnish → Apache hop');
        $this->assertSame('198.51.100.20', $this->probe('198.51.100.20')['ip']);
    }

    public function test_a_wildcard_trusts_any_peer(): void
    {
        config(['trustedproxy.proxies' => '*']);

        $this->assertSame('203.0.113.7', $this->probe('198.51.100.20')['ip']);
    }

    /** @return iterable<string, array{string|null, string|list<string>}> */
    public static function values(): iterable
    {
        yield 'unset' => [null, ['127.0.0.1', '::1']];
        yield 'empty (copied from .env.example)' => ['', ['127.0.0.1', '::1']];
        yield 'list with spaces and CIDR' => [' 127.0.0.1, ::1 ,173.245.48.0/20,, 2400:cb00::/32 ', ['127.0.0.1', '::1', '173.245.48.0/20', '2400:cb00::/32']];
        yield 'wildcard' => ['*', '*'];
    }

    /** @param string|list<string> $expected */
    #[DataProvider('values')]
    public function test_the_env_value_is_parsed_into_the_config(?string $value, string|array $expected): void
    {
        $this->assertSame($expected, $this->load($value));
    }

    /**
     * config/trustedproxy.php's list for this TRUSTED_PROXIES value (null = unset), whatever the local .env says.
     *
     * @return string|list<string>
     */
    private function load(?string $value): string|array
    {
        $before = [$_SERVER['TRUSTED_PROXIES'] ?? null, $_ENV['TRUSTED_PROXIES'] ?? null, getenv('TRUSTED_PROXIES')];
        unset($_SERVER['TRUSTED_PROXIES'], $_ENV['TRUSTED_PROXIES']);
        putenv('TRUSTED_PROXIES');
        if ($value !== null) {
            $_SERVER['TRUSTED_PROXIES'] = $value;
        }
        try {
            /** @var array{proxies: string|list<string>} $config */
            $config = require config_path('trustedproxy.php');
        } finally {
            unset($_SERVER['TRUSTED_PROXIES']);
            if (is_string($before[0])) {
                $_SERVER['TRUSTED_PROXIES'] = $before[0];
            }
            if (is_string($before[1])) {
                $_ENV['TRUSTED_PROXIES'] = $before[1];
            }
            if (is_string($before[2])) {
                putenv('TRUSTED_PROXIES='.$before[2]);
            }
        }

        return $config['proxies'];
    }

    public function test_bootstrap_sets_no_proxy_list_that_would_override_the_config(): void
    {
        $this->assertStringNotContainsString('trustProxies(', (string) file_get_contents(base_path('bootstrap/app.php')));
    }
}
