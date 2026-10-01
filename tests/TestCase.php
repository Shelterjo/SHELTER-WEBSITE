<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Feature tests render Blade without a Vite build; asset tags are tested in the browser suite.
        $this->withoutVite();
    }

    /**
     * Laravel's test client trims trailing slashes, but SHELTER's canonical URLs end with one (/ar/jo/menu/).
     * Keep the slash so tests hit the real canonical URL instead of its 301.
     *
     * @param  string  $uri
     * @return string
     */
    protected function prepareUrlForRequest($uri)
    {
        $url = parent::prepareUrlForRequest($uri);
        $path = (string) parse_url($uri, PHP_URL_PATH);
        if ($path === '' || $path === '/' || ! str_ends_with($path, '/')) {
            return $url;
        }

        return (string) preg_replace('#^([^?]*?)/?(\?.*)?$#', '$1/$2', $url, 1);
    }
}
