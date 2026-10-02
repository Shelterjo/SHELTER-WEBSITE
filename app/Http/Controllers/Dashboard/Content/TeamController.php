<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Content\Team;
use App\Services\Dashboard\TeamEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content → SHELTER Family (dashboard, M50): every profile with its state on the site in plain words, and one form to
 * add or edit one (TeamEditor — published only with the employee's recorded consent). Owner only.
 */
final class TeamController extends Controller
{
    public function index(Request $request, Team $public): View
    {
        $archived = $request->query('show') === 'archived';
        $live = array_map(fn (array $item): int => $item['member']->id, $public->published('ar'));
        $members = TeamMember::query()->when($archived, fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('dashboard.team.index', [
            'archived' => $archived,
            'archivedCount' => TeamMember::query()->whereNotNull('archived_at')->count(),
            'rows' => $members->map(fn (TeamMember $member): array => [
                'member' => $member,
                'state' => match (true) {
                    $member->archived_at !== null => 'archived',
                    $member->consent_withdrawn_at !== null => 'withdrawn',
                    in_array($member->id, $live, true) => 'live',
                    $member->is_published => 'incomplete',
                    default => 'hidden',
                },
            ])->all(),
        ]);
    }

    public function create(TeamEditor $editor): View
    {
        return $this->form(new TeamMember, $editor);
    }

    public function store(Request $request, TeamEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->save(null, $request->all(), $owner);
        if ($result['errors'] !== [] || $result['member'] === null) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.team.edit', $result['member'])->with('status', __('dashboard.saved'));
    }

    public function edit(TeamMember $member, TeamEditor $editor): View
    {
        return $this->form($member, $editor);
    }

    public function update(Request $request, TeamMember $member, TeamEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->save($member, $request->all(), $owner);
        if ($result['errors'] !== []) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.team.edit', $member)->with('status', __('dashboard.saved'));
    }

    public function archive(Request $request, TeamMember $member, TeamEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $editor->archive($member, $owner);

        return redirect()->route('dashboard.team.edit', $member)->with('status', __('dashboard.team.archived_done'));
    }

    public function restore(Request $request, TeamMember $member, TeamEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $editor->restore($member, $owner);

        return redirect()->route('dashboard.team.edit', $member)->with('status', __('dashboard.team.restored_done'));
    }

    private function form(TeamMember $member, TeamEditor $editor): View
    {
        return view('dashboard.team.form', [
            'member' => $member,
            'branches' => $editor->branches(app()->getLocale()),
            'images' => AwardsController::usableImages(),
        ]);
    }
}
