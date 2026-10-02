<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\Media;
use App\Models\User;
use App\Services\Content\Awards;
use App\Services\Dashboard\AwardEditor;
use App\Services\Media\MediaRights;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content → Awards (dashboard, M50): every award with its state on the site in plain words, and one form to add or
 * edit one (AwardEditor — publishing takes the Owner's confirmation). Owner only (routes/dashboard.php).
 */
final class AwardsController extends Controller
{
    public function index(Request $request, Awards $public, AwardEditor $editor): View
    {
        $archived = $request->query('show') === 'archived';
        $live = array_map(fn (array $item): int => $item['award']->id, $public->published('ar'));
        $awards = Award::query()->when($archived, fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->orderByDesc('year')->orderBy('sort')->orderBy('id')->get();

        return view('dashboard.awards.index', [
            'archived' => $archived,
            'archivedCount' => Award::query()->whereNotNull('archived_at')->count(),
            'rows' => $awards->map(fn (Award $award): array => [
                'award' => $award,
                'state' => match (true) {
                    $award->archived_at !== null => 'archived',
                    in_array($award->id, $live, true) => 'live',
                    $award->status === 'published' => $editor->isConfirmed($award) ? 'incomplete' : 'unconfirmed',
                    default => 'draft',
                },
            ])->all(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Award(['status' => 'draft']), false);
    }

    public function store(Request $request, AwardEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->save(null, $request->all(), $owner);
        if ($result['errors'] !== [] || $result['award'] === null) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.awards.edit', $result['award'])->with('status', __('dashboard.saved'));
    }

    public function edit(Award $award, AwardEditor $editor): View
    {
        return $this->form($award, $editor->isConfirmed($award));
    }

    public function update(Request $request, Award $award, AwardEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->save($award, $request->all(), $owner);
        if ($result['errors'] !== []) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.awards.edit', $award)->with('status', __('dashboard.saved'));
    }

    public function archive(Request $request, Award $award, AwardEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $editor->archive($award, $owner);

        return redirect()->route('dashboard.awards.edit', $award)->with('status', __('dashboard.awards.archived_done'));
    }

    public function restore(Request $request, Award $award, AwardEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $editor->restore($award, $owner);

        return redirect()->route('dashboard.awards.edit', $award)->with('status', __('dashboard.awards.restored_done'));
    }

    private function form(Award $award, bool $confirmed): View
    {
        return view('dashboard.awards.form', [
            'award' => $award,
            'confirmed' => $confirmed,
            'images' => self::usableImages(),
        ]);
    }

    /**
     * Images that may appear on the website now (approved, rights and consent in order) — the only ones offered.
     *
     * @return list<Media>
     */
    public static function usableImages(): array
    {
        return array_values(Media::query()->whereNull('archived_at')->where('approval_status', Media::APPROVED)->orderByDesc('id')->get()
            ->filter(fn (Media $media): bool => MediaRights::canUse($media))->all());
    }
}
