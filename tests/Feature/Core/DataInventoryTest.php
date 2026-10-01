<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** DC-T01: a table or column without a classification fails CI (DATA-CLASSIFICATION.md). */
class DataInventoryTest extends TestCase
{
    use RefreshDatabase;

    private const CLASSES = ['PUBLIC', 'INTERNAL', 'CONFIDENTIAL', 'SENSITIVE'];

    public function test_every_table_is_classified_and_every_override_points_to_a_real_column(): void
    {
        /** @var array<string, array{class: string, columns?: array<string, string>, per_row?: string}> $inventory */
        $inventory = config('data_inventory');
        $tables = array_map(fn (array $t): string => (string) $t['name'], Schema::getTables());

        $this->assertSame([], array_values(array_diff($tables, array_keys($inventory))), 'Unclassified tables');
        $this->assertSame([], array_values(array_diff(array_keys($inventory), $tables)), 'Classified tables that no longer exist');

        foreach ($inventory as $table => $entry) {
            $this->assertContains($entry['class'], self::CLASSES, $table);
            $columns = Schema::getColumnListing($table);
            foreach ($entry['columns'] ?? [] as $column => $class) {
                $this->assertContains($column, $columns, "{$table}.{$column} does not exist");
                $this->assertContains($class, self::CLASSES, "{$table}.{$column}");
            }
            if (isset($entry['per_row'])) {
                $this->assertContains($entry['per_row'], $columns, "{$table} per-row column");
            }
        }
    }

    public function test_secrets_are_never_classified_below_sensitive(): void
    {
        /** @var array<string, array{class: string, columns?: array<string, string>}> $inventory */
        $inventory = config('data_inventory');

        $this->assertSame('SENSITIVE', $inventory['users']['class']);
        $this->assertArrayNotHasKey('two_factor_secret', $inventory['users']['columns'] ?? []);
        $this->assertArrayNotHasKey('password', $inventory['users']['columns'] ?? []);
    }
}
