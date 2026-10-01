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
        $body = Indexing::siteIndexable()
            ? "User-agent: *\nAllow: /\nDisallow: /dashboard/\n"
            : "User-agent: *\nDisallow: /\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
