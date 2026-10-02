<?php

namespace Tests\Feature\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * FINAL-QA QA-001: a fresh install copies .env.example, which lists the private storage roots with an empty value
 * (CAREERS_STORAGE_ROOT=). The private disks must still land outside the web root — an empty root would write applicant
 * files next to the running script (public/ under a web server).
 */
class StorageRootsTest extends TestCase
{
    /** @return iterable<string, array{string, string, string}> */
    public static function roots(): iterable
    {
        yield 'careers' => ['CAREERS_STORAGE_ROOT', 'careers', 'app/private/careers'];
        yield 'media originals' => ['MEDIA_STORAGE_ROOT', 'media', 'app/private/media'];
    }

    #[DataProvider('roots')]
    public function test_an_empty_variable_falls_back_to_the_private_default(string $variable, string $disk, string $default): void
    {
        $before = $_SERVER[$variable] ?? null;
        $_SERVER[$variable] = '';
        try {
            /** @var array{disks: array<string, array{root: string}>} $config */
            $config = require config_path('filesystems.php');
        } finally {
            if ($before === null) {
                unset($_SERVER[$variable]);
            } else {
                $_SERVER[$variable] = $before;
            }
        }

        $root = $config['disks'][$disk]['root'];
        $this->assertSame(storage_path($default), $root);
        $this->assertFalse(str_starts_with($root, public_path()), 'outside the web root');
    }

    public function test_an_empty_public_media_root_still_points_at_the_public_media_folder(): void
    {
        $_SERVER['MEDIA_PUBLIC_ROOT'] = '';
        try {
            /** @var array{disks: array<string, array{root: string}>} $config */
            $config = require config_path('filesystems.php');
        } finally {
            unset($_SERVER['MEDIA_PUBLIC_ROOT']);
        }

        $this->assertSame(public_path('media'), $config['disks']['media_public']['root']);
    }
}
