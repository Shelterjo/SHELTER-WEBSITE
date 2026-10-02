<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Indexing;
use Illuminate\Http\Response;

/** robots.txt per environment: only indexable production allows crawling (SEO-028, ENVIRONMENTS.md). */
final class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        // "Disallow: /dashboard" covers the address with and without the slash; the sitemap is announced
        // (FINAL-QA QA-042). Search results stay crawlable for their links but carry noindex.
        $body = Indexing::siteIndexable()
            ? "User-agent: *\nAllow: /\nDisallow: /dashboard\n\nSitemap: ".url('/sitemap.xml')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
