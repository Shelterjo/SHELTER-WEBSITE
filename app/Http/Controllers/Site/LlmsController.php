<?php

namespace App\Http\Controllers\Site;

use App\Enums\ContactKind;
use App\Http\Controllers\Controller;
use App\Services\Content\Pages;
use App\Services\Experiences\Events;
use App\Services\MasterData\MasterData;
use App\Services\Site\BranchDirectory;
use App\Services\Site\ContactActions;
use App\Services\Site\Markets;
use App\Support\PageUrl;
use App\Support\SiteLinks;
use Illuminate\Http\Response;

/**
 * /llms.txt (SI-S06, llmstxt.org format) — generated from approved, published data only (CONTENT-SOURCE-OF-TRUTH):
 * the brand names, the main pages in both languages (Events only while something is listed), the branches by their approved names, the main public number
 * and the founding year once approved. No descriptions or claims until the brand copy is approved (D-068).
 */
final class LlmsController extends Controller
{
    public function __invoke(Markets $markets, BranchDirectory $directory, ContactActions $contacts, Pages $pages, Events $events, MasterData $data): Response
    {
        $lines = ['# '.__('site.brand', [], 'en'), '', (string) __('site.brand_ar', [], 'ar')];
        $founded = $data->setting('brand.founded_year');
        if (is_int($founded)) {
            $lines[] = '';
            $lines[] = 'Founded: '.$founded;
        }

        $market = $markets->current();
        $links = [];
        foreach (['en', 'ar'] as $locale) {
            $parameters = ['locale' => $locale, 'market' => $market?->code];
            $links[] = [(string) __('site.nav.home', [], $locale), PageUrl::route('home', ['locale' => $locale])];
            foreach (['menu' => 'site.nav.menu', 'locations' => 'site.locations.title', 'events' => 'site.events.title'] as $route => $label) {
                $href = $market !== null ? SiteLinks::to($route, $parameters) : null;
                $hasContent = $route !== 'events' || ($market !== null && $events->listed($market, $locale) !== []);
                if ($href !== null && $hasContent) {
                    $links[] = [(string) __($label, [], $locale), $href];
                }
            }
            $contact = SiteLinks::to('contact', $parameters);
            if ($contact !== null) {
                $links[] = [(string) __('site.contact.title', [], $locale), $contact];
            }
            foreach ($pages->links([...Pages::EXPLORE, ...Pages::LEGAL], $locale) as $page) {
                $links[] = [$page['label'], $page['href']];
            }
        }
        $lines = [...$lines, '', '## Pages', ...array_map(fn (array $link): string => "- [{$link[0]}]({$link[1]})", $links)];

        if ($market !== null) {
            $branches = $directory->forMarket($market, 'en');
            if ($branches !== []) {
                $lines = [...$lines, '', '## Branches'];
                foreach ($branches as $branch) {
                    $lines[] = '- ['.$branch->name.']('.$branch->url.')'.($branch->altName !== null ? ' — '.$branch->altName : '');
                }
            }
        }

        $phone = $contacts->intent(ContactKind::PhoneMain, 'en');
        if ($phone !== null) {
            $lines = [...$lines, '', '## Contact', '- Phone: '.$phone->display];
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
