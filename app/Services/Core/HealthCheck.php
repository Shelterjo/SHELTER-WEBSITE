<?php

namespace App\Services\Core;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Readiness behind /up (deploy-facts §f, MONITORING): the framework's health route dispatches DiagnosingHealth and
 * answers 500 when a listener throws. Besides "the framework boots", this proves the database answers, the cache store
 * keeps a value and storage takes a write. The cause goes to the log (the route reports the exception); the response
 * only says up or down — never which part, a path or a driver message. Every probe uses its own random name, so
 * monitors hitting /up at the same moment cannot disturb each other, and nothing is left behind.
 */
final class HealthCheck
{
    public function handle(DiagnosingHealth $event): void
    {
        $failed = [];
        foreach (['database' => $this->database(...), 'cache' => $this->cache(...), 'storage' => $this->storage(...)] as $name => $check) {
            try {
                $check();
            } catch (Throwable $e) {
                $failed[$name] = $e;
            }
        }
        if ($failed !== []) {
            throw new RuntimeException('Health check failed: '.implode(', ', array_keys($failed)), previous: reset($failed));
        }
    }

    private function database(): void
    {
        DB::connection()->select('select 1');
    }

    private function cache(): void
    {
        $key = 'health:'.Str::random(16);
        $value = Str::random(16);
        $store = Cache::store();
        $store->put($key, $value, 60);
        $kept = $store->get($key);
        $store->forget($key);
        if ($kept !== $value) {
            throw new RuntimeException('The cache store did not keep a value.');
        }
    }

    /** Sessions are in the database; compiled views, the maintenance flag and the logs need these two folders. */
    private function storage(): void
    {
        foreach ([storage_path('framework'), storage_path('logs')] as $directory) {
            $probe = $directory.'/health-'.Str::random(16).'.tmp';
            $written = @file_put_contents($probe, 'ok');
            $read = $written === 2 ? @file_get_contents($probe) : false;
            @unlink($probe);
            if ($read !== 'ok') {
                throw new RuntimeException("Storage folder not writable: {$directory}");
            }
        }
    }
}
