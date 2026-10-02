<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;
use App\Services\Content\Pages;
use App\Services\Dashboard\PageEditor;
use App\Support\SiteLinks;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content → Pages (dashboard): every fixed brand page in one list, and one editor per page (PageEditor). Owner only
 * (routes/dashboard.php). The list says plainly whether each page is live on the site, a draft, or not written yet.
 */
final class PagesController extends Controller
{
    public function index(Pages $pages): View
    {
        $stored = Page::query()->withCount(['sections' => fn ($q) => $q->whereNull('archived_at')])->whereIn('key', array_keys(PageEditor::PAGES))->get()->keyBy('key');
        $rows = [];
        foreach (array_keys(PageEditor::PAGES) as $key) {
            /** @var Page|null $page */
            $page = $stored->get($key);
            $live = $pages->published($key, 'ar') !== null && $pages->published($key, 'en') !== null;
            $rows[] = [
                'key' => $key,
                'label' => (string) __('dashboard.pages.keys.'.$key),
                'state' => $page === null ? 'missing' : ($live ? 'live' : ($page->status === PublishStatus::Published ? 'blocked' : 'draft')),
                'sections' => $page === null ? 0 : (int) $page->sections_count,
                'updated' => $page === null ? null : ($page->content_updated_at ?? $page->updated_at),
                'url' => $live ? SiteLinks::to($key, ['locale' => 'ar']) : null,
            ];
        }

        return view('dashboard.pages.index', ['rows' => $rows]);
    }

    public function edit(string $key, Request $request, PageEditor $editor, Pages $pages): View
    {
        abort_unless(isset(PageEditor::PAGES[$key]), 404);
        $live = $pages->published($key, 'ar') !== null;
        $page = $editor->page($key);

        return view('dashboard.pages.edit', [
            'key' => $key,
            'images' => AwardsController::usableImages(),
            'label' => (string) __('dashboard.pages.keys.'.$key),
            'page' => $page,
            'cards' => $this->cards($page, $request->old('sections')),
            'url' => $live ? SiteLinks::to($key, ['locale' => 'ar']) : null,
        ]);
    }

    /**
     * The section cards to show: the stored sections — or, after a refused save, exactly the cards that were sent (new
     * ones included, in the order they were on screen) so nothing typed is lost — then one blank card to add a section.
     *
     * @return list<array{i: int, s: PageSection|null}>
     */
    private function cards(Page $page, mixed $sent): array
    {
        $cards = [];
        if (is_array($sent)) {
            $stored = $page->sections->keyBy('id');
            foreach ($sent as $formKey => $row) {
                if (is_array($row) && ctype_digit((string) $formKey)) {
                    $id = $row['id'] ?? null;
                    $cards[] = ['i' => (int) $formKey, 's' => is_scalar($id) ? $stored->get((int) $id) : null, 'blank' => $this->isBlank($row)];
                }
            }
        } else {
            foreach ($page->sections->values() as $index => $section) {
                $cards[] = ['i' => $index, 's' => $section, 'blank' => false];
            }
        }
        $last = $cards === [] ? null : $cards[array_key_last($cards)];
        if ($last === null || $last['s'] !== null || ! $last['blank']) {
            $cards[] = ['i' => $cards === [] ? 0 : max(array_column($cards, 'i')) + 1, 's' => null, 'blank' => true];
        }

        return array_map(fn (array $card): array => ['i' => $card['i'], 's' => $card['s']], $cards);
    }

    /** @param  array<mixed>  $row */
    private function isBlank(array $row): bool
    {
        foreach (['heading_ar', 'heading_en', 'body_ar', 'body_en'] as $field) {
            if (is_string($row[$field] ?? null) && trim($row[$field]) !== '') {
                return false;
            }
        }

        return true;
    }

    public function update(string $key, Request $request, PageEditor $editor): RedirectResponse
    {
        abort_unless(isset(PageEditor::PAGES[$key]), 404);
        /** @var User $owner */
        $owner = $request->user();
        $errors = $editor->save($key, $request->all(), $owner);
        if ($errors !== []) {
            return back()->withInput()->withErrors($errors);
        }

        return redirect()->route('dashboard.pages.edit', $key)->with('status', __('dashboard.saved'));
    }
}
