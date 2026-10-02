<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Http\Controllers\Controller;
use App\Models\ContactPoint;
use App\Models\SocialLink;
use App\Models\User;
use App\Services\Dashboard\ContactsEditor;
use App\Services\Dashboard\OwnerApproval;
use App\Support\PhoneNumber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Business data → Contact numbers (dashboard, M50, CMS-030, CONTACT-004…027): each number with where it may appear,
 * its value and whether it is public; then the social accounts. Central facts: a fresh re-confirmation to edit.
 */
final class ContactsController extends Controller
{
    public function index(OwnerApproval $approval): View
    {
        $points = ContactPoint::query()->where('scope', 'brand')->orderBy('sort')->orderBy('id')->get();

        return view('dashboard.contacts.index', [
            'points' => $points->map(fn (ContactPoint $p): array => [
                'point' => $p,
                'display' => $p->value !== null && str_starts_with($p->value, '+') ? PhoneNumber::display($p->value, '962', app()->getLocale()) : $p->value,
                'approved' => $p->value !== null && $approval->isApproved($p->factKey(), $p->value),
            ])->all(),
            'social' => SocialLink::query()->get()->keyBy('platform'),
            'platforms' => array_keys(ContactsEditor::PLATFORMS),
        ]);
    }

    public function update(Request $request, ContactPoint $point, ContactsEditor $editor): RedirectResponse
    {
        abort_unless($point->scope === 'brand', 404);
        /** @var User $owner */
        $owner = $request->user();
        $errors = $editor->saveContact($point, $request->all(), $owner);
        if ($errors !== []) {
            return redirect()->to(route('dashboard.contacts.index').'#c'.$point->id)->withInput()->withErrors($errors, 'c'.$point->id);
        }

        return redirect()->to(route('dashboard.contacts.index').'#c'.$point->id)->with('status', __('dashboard.saved'));
    }

    public function social(Request $request, string $platform, ContactsEditor $editor): RedirectResponse
    {
        abort_unless(array_key_exists($platform, ContactsEditor::PLATFORMS), 404);
        /** @var User $owner */
        $owner = $request->user();
        $errors = $editor->saveSocial($platform, $request->all(), $owner);
        if ($errors !== []) {
            return redirect()->to(route('dashboard.contacts.index').'#s-'.$platform)->withInput()->withErrors($errors, 's-'.$platform);
        }

        return redirect()->to(route('dashboard.contacts.index').'#s-'.$platform)->with('status', __('dashboard.saved'));
    }
}
