<?php

namespace Tests\Feature\DesignSystem;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use Throwable;

/**
 * Renders x-ui components through Blade (no HTTP, no database) and queries the result as a DOM, so the tests assert
 * structure and ARIA wiring instead of string fragments.
 */
trait RendersComponents
{
    /** @param  array<string, mixed>  $data */
    protected function render(string $blade, array $data = [], string $locale = 'ar'): string
    {
        app()->setLocale($locale);

        return Blade::render($blade, $data, deleteCachedView: true);
    }

    /** @param  array<string, mixed>  $data */
    protected function dom(string $blade, array $data = [], string $locale = 'ar'): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true); // HTML5 elements (dialog, details) are unknown to libxml
        $document->loadHTML('<!doctype html><html><head><meta charset="utf-8"></head><body>'.$this->render($blade, $data, $locale).'</body></html>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    /** XPath predicate: element has the class. */
    protected static function cls(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
    }

    protected function element(DOMXPath $xpath, string $query): DOMElement
    {
        $nodes = $xpath->query($query);
        $node = $nodes === false ? null : $nodes->item(0);
        $this->assertInstanceOf(DOMElement::class, $node, "No element matches {$query}");

        return $node;
    }

    protected function countMatches(DOMXPath $xpath, string $query): int
    {
        $nodes = $xpath->query($query);

        return $nodes === false ? 0 : $nodes->length;
    }

    protected function assertRenderFails(string $blade, string $message): void
    {
        try {
            $this->render($blade);
        } catch (Throwable $e) {
            $this->assertStringContainsString($message, $e->getMessage());

            return;
        }
        $this->fail("Rendering should have failed with: {$message}");
    }
}
