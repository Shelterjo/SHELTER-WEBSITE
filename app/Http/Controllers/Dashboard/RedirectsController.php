<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Models\User;
use App\Services\Seo\LegacyRedirects;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Google visibility → Old links (SEO-018 Redirect Manager): each old or changed address, where it goes now, whether it
 * is switched on, and how many visitors it still brings. Saving never switches a row on; switching on is the Owner's
 * approval (production redirects are a launch step — SEO-013). Archive instead of delete.
 */
final class RedirectsController extends Controller
{
    public function index(): View
    {
        $rows = Redirect::query()->orderByRaw("case state when 'active' then 0 when 'draft' then 1 else 2 end")->orderBy('source_path')->get();

        return view('dashboard.seo.redirects', [
            'rows' => $rows,
            'codes' => LegacyRedirects::CODES,
            'activeCount' => $rows->where('state', 'active')->count(),
        ]);
    }

    public function store(Request $request, LegacyRedirects $redirects): RedirectResponse
    {
        return $this->saved($redirects->save(null, $request->all(), $this->owner($request)), 'new');
    }

    public function update(Request $request, Redirect $redirect, LegacyRedirects $redirects): RedirectResponse
    {
        return $this->saved($redirects->save($redirect, $request->all(), $this->owner($request)), 'r'.$redirect->id);
    }

    public function command(Request $request, Redirect $redirect, string $command, LegacyRedirects $redirects): RedirectResponse
    {
        $error = $redirects->command($redirect, $command, $this->owner($request));
        $back = redirect()->to(route('dashboard.redirects.index').'#r'.$redirect->id);

        return $error !== null ? $back->withErrors(['command' => $error], 'r'.$redirect->id) : $back->with('status', __('dashboard.redirects.done.'.$command));
    }

    /** @param array{redirect: Redirect|null, errors: array<string, string>} $result */
    private function saved(array $result, string $bag): RedirectResponse
    {
        $anchor = $result['redirect']?->exists ? 'r'.$result['redirect']->id : 'new';
        if ($result['errors'] !== []) {
            return redirect()->to(route('dashboard.redirects.index').'#'.$bag)->withInput()->withErrors($result['errors'], $bag);
        }

        return redirect()->to(route('dashboard.redirects.index').'#'.$anchor)->with('status', __('dashboard.saved'));
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
