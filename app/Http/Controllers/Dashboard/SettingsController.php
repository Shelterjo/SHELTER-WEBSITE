<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Dashboard\SettingsEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Settings (M50): forms open or closed, Safe Mode, anonymous search counting, the founding year. */
final class SettingsController extends Controller
{
    public function index(SettingsEditor $editor): View
    {
        return view('dashboard.settings', ['state' => $editor->state(), 'production' => app()->isProduction()]);
    }

    public function update(Request $request, SettingsEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->save($request->all(), $owner);
        if ($result['errors'] !== []) {
            return redirect()->route('dashboard.settings')->withInput()->withErrors($result['errors'], 'settings');
        }

        return redirect()->route('dashboard.settings')->with('status', trans_choice('dashboard.settings.saved', $result['changed'], ['count' => $result['changed']]));
    }
}
