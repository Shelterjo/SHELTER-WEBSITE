<?php

namespace Tests\Feature\Http;

use App\Services\Core\HealthCheck;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * deploy-facts §f: /up used to prove only that the framework boots. It now also proves the database answers, the cache
 * keeps a value and storage takes a write (App\Services\Core\HealthCheck) — and says only up or down: the cause stays
 * in the log, never in the response.
 */
class HealthCheckTest extends TestCase
{
    private ?string $blocker = null;

    protected function setUp(): void
    {
        parent::setUp();
        // Staging and production never run in debug (config/app.php); in debug the route rethrows instead of answering.
        config(['app.debug' => false]);
    }

    protected function tearDown(): void
    {
        if ($this->blocker !== null) {
            @unlink($this->blocker);
        }
        parent::tearDown();
    }

    /** A plain file used as a folder: nothing can be written under it, whoever runs the tests (even root). */
    private function notAFolder(): string
    {
        $this->blocker = (string) tempnam(sys_get_temp_dir(), 'shelter-health-');

        return $this->blocker.'/inside';
    }

    /** Only the broken part fails (the log names it; the response does not). */
    private function assertFailingPart(string $part): void
    {
        try {
            app(HealthCheck::class)->handle(new DiagnosingHealth);
            $this->fail('the health check passed');
        } catch (RuntimeException $e) {
            $this->assertSame("Health check failed: {$part}", $e->getMessage());
            $this->assertNotNull($e->getPrevious(), 'the cause is kept for the log');
        }
    }

    /** @param TestResponse<Response> $response */
    private function assertDownWithoutDetails(TestResponse $response, string ...$secrets): void
    {
        $response->assertStatus(500);
        $html = (string) $response->getContent();
        foreach (['Health check failed', 'SQLSTATE', 'database', 'cache', 'storage', ...$secrets] as $detail) {
            $this->assertStringNotContainsStringIgnoringCase($detail, $html, "the response names [{$detail}]");
        }
        $this->getJson('/up')->assertStatus(500)->assertExactJson(['status' => 'down']);
    }

    public function test_a_healthy_app_is_up_and_leaves_nothing_behind(): void
    {
        $this->get('/up')->assertOk()->assertSee('Application up');
        $this->getJson('/up')->assertOk()->assertExactJson(['status' => 'up']);

        $this->assertSame([], glob(storage_path('framework/health-*')) ?: [], 'probe files are removed');
        $this->assertSame([], glob(storage_path('logs/health-*')) ?: []);
    }

    public function test_a_broken_database_connection_is_down(): void
    {
        $path = $this->notAFolder().'/shelter.sqlite';
        config(['database.connections.sqlite.database' => $path]);
        DB::purge('sqlite');

        $this->assertFailingPart('database');
        $this->assertDownWithoutDetails($this->get('/up'), $path, 'sqlite');
    }

    public function test_a_cache_store_that_cannot_write_is_down(): void
    {
        $path = $this->notAFolder();
        config(['cache.default' => 'file', 'cache.stores.file.path' => $path]);

        $this->assertFailingPart('cache');
        $this->assertDownWithoutDetails($this->get('/up'), $path);
    }

    public function test_storage_that_cannot_take_a_write_is_down(): void
    {
        $storage = $this->app->storagePath();
        $this->app->useStoragePath($this->notAFolder());
        try {
            $this->assertFailingPart('storage');
            $this->assertDownWithoutDetails($this->get('/up'), $this->app->storagePath());
        } finally {
            $this->app->useStoragePath($storage);
        }
    }
}
