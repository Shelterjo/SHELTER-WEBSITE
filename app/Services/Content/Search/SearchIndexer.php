<?php

namespace App\Services\Content\Search;

use App\Models\Market;
use App\Models\SearchEntry;
use App\Services\Content\Pages;
use App\Services\Experiences\Events;
use App\Services\Menu\Page\MenuItem;
use App\Services\Menu\Page\MenuPage;
use App\Services\Menu\Page\MenuSection;
use App\Services\Site\BranchDirectory;
use App\Support\SiteLinks;
use Illuminate\Support\Facades\DB;

/**
 * Builds the PUBLIC search index from what visitors can already see (GLOBAL-SEARCH §2): menu items by their approved
 * display names (English leads where no Arabic name is approved — CF-03; unapproved source names stay out — PO-042),
 * menu categories, active branches, events on now or coming up, the published content pages and their FAQ answers,
 * and the fixed pages (menu, locations, contact). Ended events drop out at the next rebuild (nightly — PHASE 6). Deterministic: empty then rebuild gives the same rows. Run by `php artisan search:rebuild`.
 */
final class SearchIndexer
{
    /** @var list<array<string, mixed>> */
    private array $rows = [];

    public function __construct(
        private readonly MenuPage $menu,
        private readonly BranchDirectory $branches,
        private readonly Pages $pages,
        private readonly Events $events,
    ) {}

    public function rebuild(): int
    {
        $this->rows = [];
        foreach (Market::query()->where('is_active', true)->orderBy('id')->get() as $market) {
            $this->indexMarket($market);
            $this->indexEvents($market);
        }
        $this->indexPages();

        DB::transaction(function (): void {
            SearchEntry::query()->where('scope', 'PUBLIC')->delete();
            $now = now();
            foreach (array_chunk($this->rows, 100) as $chunk) {
                SearchEntry::query()->insert(array_map(fn (array $row): array => $row + ['updated_at' => $now], $chunk));
            }
        });

        return count($this->rows);
    }

    private function indexMarket(Market $market): void
    {
        $parameters = ['market' => $market->code];
        $menuAr = SiteLinks::to('menu', ['locale' => 'ar'] + $parameters);
        $menuEn = SiteLinks::to('menu', ['locale' => 'en'] + $parameters);

        if ($menuAr !== null && $menuEn !== null) {
            $ar = $this->menu->build($market, 'ar', null);
            $en = $this->menu->build($market, 'en', null);
            $sectionsEn = [];
            foreach ($this->sections($en->season, $en->sections) as $section) {
                $sectionsEn[$section->code] = $section;
            }
            foreach ($this->sections($ar->season, $ar->sections) as $section) {
                $other = $sectionsEn[$section->code] ?? null;
                $this->add('category', $market->code.':'.$section->code, $section->nameLang === null ? $section->name : null, $other?->name,
                    null, null, $menuAr.'#'.$section->id, $menuEn.'#'.$section->id, [$section->name, (string) $other?->name], 1);
                foreach ($section->groups as $group) {
                    foreach ($group->items as $item) {
                        $this->addItem($market->code, $item, $section, $other, $menuAr, $menuEn);
                    }
                }
            }
            $this->add('page', $market->code.':menu', (string) __('site.nav.menu', [], 'ar'), (string) __('site.nav.menu', [], 'en'),
                null, null, $menuAr, $menuEn, [], 1);
        }

        $locationsAr = SiteLinks::to('locations', ['locale' => 'ar'] + $parameters);
        $locationsEn = SiteLinks::to('locations', ['locale' => 'en'] + $parameters);
        if ($locationsAr !== null && $locationsEn !== null) {
            $this->add('page', $market->code.':locations', (string) __('site.locations.title', [], 'ar'), (string) __('site.locations.title', [], 'en'),
                null, null, $locationsAr, $locationsEn, [(string) __('site.hours.title', [], 'ar'), (string) __('site.hours.title', [], 'en')], 1);
            $en = [];
            foreach ($this->branches->forMarket($market, 'en') as $branch) {
                $en[$branch->branch->code] = $branch;
            }
            foreach ($this->branches->forMarket($market, 'ar') as $branch) {
                $other = $en[$branch->branch->code] ?? null;
                $this->add('branch', (string) $branch->branch->code, $branch->name, $other?->name, null, null, $branch->url, $other?->url,
                    [(string) $branch->altName, (string) $branch->branch->slug], 2);
            }
        }
    }

    private function indexEvents(Market $market): void
    {
        $en = [];
        foreach ($this->events->listed($market, 'en') as $event) {
            $en[$event->slug] = $event;
        }
        foreach ($this->events->listed($market, 'ar') as $event) {
            $other = $en[$event->slug] ?? null;
            $this->add('event', $market->code.':'.$event->slug, $event->title, $other?->title, $event->dateText, $other?->dateText,
                $event->url, $other?->url, [...$event->paragraphs, ...($other->paragraphs ?? []), (string) $event->place, (string) $other?->place], 1);
        }
    }

    /**
     * @param  list<MenuSection>  $sections
     * @return list<MenuSection>
     */
    private function sections(?MenuSection $season, array $sections): array
    {
        return $season !== null ? [$season, ...$sections] : $sections;
    }

    private function addItem(string $market, MenuItem $item, MenuSection $section, ?MenuSection $sectionEn, string $menuAr, string $menuEn): void
    {
        // On the Arabic page the item name is Arabic only when approved (nameLang null); otherwise English leads.
        $nameAr = $item->nameLang === null ? $item->name : null;
        $this->add('product', $market.':'.$item->code, $nameAr, $item->nameEn,
            $section->nameLang === null ? $section->name : null, $sectionEn?->name,
            $menuAr.'#'.$item->anchor(), $menuEn.'#'.$item->anchor(), $item->searchTerms, 0);
    }

    private function indexPages(): void
    {
        $contactAr = SiteLinks::to('contact', ['locale' => 'ar']);
        $contactEn = SiteLinks::to('contact', ['locale' => 'en']);
        if ($contactAr !== null && $contactEn !== null) {
            $intents = [];
            foreach (['ar', 'en'] as $locale) {
                foreach (['general', 'complaints', 'catering', 'franchise'] as $intent) {
                    $intents[] = (string) __("site.contact.{$intent}.title", [], $locale);
                }
            }
            $this->add('page', 'contact', (string) __('site.contact.title', [], 'ar'), (string) __('site.contact.title', [], 'en'),
                null, null, $contactAr, $contactEn, $intents, 1);
        }

        foreach ([...Pages::EXPLORE, ...Pages::LEGAL, ...Pages::BUSINESS] as $key) {
            $ar = $this->pages->published($key, 'ar');
            $en = $this->pages->published($key, 'en');
            $urlAr = SiteLinks::to($key, ['locale' => 'ar']);
            $urlEn = SiteLinks::to($key, ['locale' => 'en']);
            if ($ar === null || $en === null || $urlAr === null || $urlEn === null) {
                continue;
            }
            $text = [];
            foreach ($ar->sections as $position => $section) {
                $answer = $en->sections[$position] ?? null;
                if ($section->type === 'faq') {
                    // Each published answer is its own result, linked to its question (#q-N on the FAQ page).
                    $anchor = '#q-'.($position + 1);
                    $this->add('faq', $key.':'.($position + 1), $section->heading, $answer?->heading, $ar->title, $en->title,
                        $urlAr.$anchor, $urlEn.$anchor, [...$section->paragraphs, ...($answer->paragraphs ?? [])], 0);

                    continue;
                }
                $text = [...$text, (string) $section->heading, (string) $answer?->heading, ...$section->paragraphs, ...($answer->paragraphs ?? [])];
            }
            $this->add('page', $key, $ar->title, $en->title, null, null, $urlAr, $urlEn, [(string) $ar->description, (string) $en->description, ...$text], 1);
        }
    }

    /** @param  list<string>  $terms */
    private function add(string $type, string $id, ?string $titleAr, ?string $titleEn, ?string $metaAr, ?string $metaEn, ?string $urlAr, ?string $urlEn, array $terms, int $boost): void
    {
        $title = Normalizer::normalize(trim(($titleEn ?? '').' '.($titleAr ?? '')));
        $this->rows[] = [
            'entity_type' => $type,
            'entity_id' => $id,
            'scope' => 'PUBLIC',
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'meta_ar' => $metaAr,
            'meta_en' => $metaEn,
            'normalized_title' => mb_substr($title, 0, 255),
            'normalized_text' => Normalizer::normalize(implode(' ', array_filter([$title, ...$terms]))),
            'url_ar' => $urlAr !== null ? $this->path($urlAr) : null,
            'url_en' => $urlEn !== null ? $this->path($urlEn) : null,
            'boost' => $boost,
            'sort' => count($this->rows),
        ];
    }

    /** URLs are stored host-relative so one index serves Dev, Staging and Production alike. */
    private function path(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        return $path.($fragment !== null && $fragment !== false ? '#'.$fragment : '');
    }
}
