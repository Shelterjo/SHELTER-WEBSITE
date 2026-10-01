<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentSection;
use App\Services\Content\Pages;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Brand content pages with fixed URLs: About (SI-B03), FAQ (SI-B05), Privacy (SI-B06), Terms (SI-B07). The text is
 * the Owner's (PO-017, PO-034) or the lawyer's (PO-019): until it is published in both languages the page is a 404,
 * so nothing empty, placeholder or invented is ever public. FAQPage structured data lists only the published answers.
 */
final class ContentPageController extends Controller
{
    public function __invoke(Pages $pages, string $key): View
    {
        $locale = app()->getLocale();
        $page = $pages->published($key, $locale);
        abort_if($page === null, 404);

        $canonical = PageUrl::route($key);
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => $page->title, 'href' => $canonical],
        ];
        $jsonLd = [StructuredData::breadcrumbs($crumbs)];
        if ($page->type === 'faq') {
            $jsonLd[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_values(array_map(fn (ContentSection $item): array => [
                    '@type' => 'Question',
                    'name' => (string) $item->heading,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => implode("\n\n", $item->paragraphs)],
                ], array_filter($page->sections, fn (ContentSection $section): bool => $section->type === 'faq'))),
            ];
        } else {
            $jsonLd[] = ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $page->title, 'url' => $canonical, 'inLanguage' => $locale];
        }

        return view('site.content-page', [
            'page' => $page,
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates($key),
            'description' => $page->description,
            'crumbs' => $crumbs,
            'jsonLd' => $jsonLd,
        ]);
    }
}
