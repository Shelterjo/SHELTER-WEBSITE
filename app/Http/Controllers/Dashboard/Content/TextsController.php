<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Content\SiteTexts;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content → Site texts (M50): the fixed texts of the site, page by page, in both languages, with the original wording
 * shown under each box. One form per page; empty = the original wording.
 */
final class TextsController extends Controller
{
    public function index(Request $request): View
    {
        $open = $request->string('page')->toString();

        return view('dashboard.texts.index', [
            'groups' => SiteTexts::GROUPS,
            'open' => array_key_exists($open, SiteTexts::GROUPS) ? $open : (string) session('texts_open', 'home'),
        ]);
    }

    public function update(Request $request, string $group, SiteTexts $texts): RedirectResponse
    {
        abort_unless(array_key_exists($group, SiteTexts::GROUPS), 404);
        /** @var User $owner */
        $owner = $request->user();
        $result = $texts->save($group, $request->all(), $owner);
        $back = redirect()->to(route('dashboard.texts.index', ['page' => $group]).'#texts-'.$group);
        if ($result['errors'] !== []) {
            return $back->withInput()->withErrors($result['errors'], 'texts-'.$group);
        }

        return $back->with('status', trans_choice('dashboard.texts.saved', $result['changed'], ['count' => $result['changed']]));
    }
}
