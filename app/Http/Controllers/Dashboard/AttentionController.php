<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Models\User;
use App\Services\Core\Attention;
use App\Services\Core\Signals;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Needs attention (MON-007, DASH-017): every open issue, most serious first, each with the screen that fixes it. */
final class AttentionController extends Controller
{
    public function index(Attention $attention): View
    {
        return view('dashboard.attention', ['items' => $attention->open(app()->getLocale())]);
    }

    /** Only information can be dismissed; an action-required issue closes when its cause is fixed (M35 §8). */
    public function dismiss(Request $request, Signal $signal, Signals $signals): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $done = $signals->dismiss($signal, $owner);

        return redirect()->route('dashboard.attention')->with('status', __($done ? 'dashboard.attention.dismissed' : 'dashboard.attention.not_dismissible'));
    }
}
