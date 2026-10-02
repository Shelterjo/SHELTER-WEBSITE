<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Ai\AiGateway;
use App\Services\Core\Settings;
use App\Services\Shaltoor\ShaltoorLog;
use App\Services\Shaltoor\ShaltoorSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Shaltoor (M69 §23): on/off, the welcome and quick suggestions in each language, what visitors asked in the period
 * and the questions it could not answer (grouped; "dealt with" removes one from the list). The assistant's rules and
 * sources are not editable here.
 */
final class ShaltoorController extends Controller
{
    private const PERIODS = [7, 30, 90];

    public function index(Request $request, ShaltoorSettings $shaltoor, ShaltoorLog $log, Settings $settings, AiGateway $ai): View
    {
        $days = in_array((int) $request->query('days'), self::PERIODS, true) ? (int) $request->query('days') : 30;
        $suggestions = fn (string $locale): string => implode("\n", is_array($custom = $settings->get('shaltoor.suggestions.'.$locale)) ? array_filter($custom, 'is_string') : []);

        return view('dashboard.shaltoor', [
            'enabled' => $shaltoor->enabled(),
            'welcome' => ['ar' => (string) ($settings->get('shaltoor.welcome.ar') ?? ''), 'en' => (string) ($settings->get('shaltoor.welcome.en') ?? '')],
            'defaults' => ['ar' => (string) __('shaltoor.welcome', [], 'ar'), 'en' => (string) __('shaltoor.welcome', [], 'en')],
            'suggestions' => ['ar' => $suggestions('ar'), 'en' => $suggestions('en')],
            'defaultSuggestions' => ['ar' => $shaltoor->suggestions('ar'), 'en' => $shaltoor->suggestions('en')],
            'aiConnected' => $ai->connected(),
            'days' => $days,
            'periods' => self::PERIODS,
            'stats' => $log->stats($days),
            'unanswered' => $log->unanswered($days),
        ]);
    }

    public function update(Request $request, ShaltoorSettings $shaltoor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $errors = $shaltoor->save($request->only(['enabled', 'welcome_ar', 'welcome_en', 'suggestions_ar', 'suggestions_en']), $owner);
        if ($errors !== []) {
            return redirect()->route('dashboard.shaltoor')->withErrors($errors, 'shaltoor')->withInput();
        }

        return redirect()->route('dashboard.shaltoor')->with('status', __('dashboard.saved'));
    }

    public function handled(Request $request, ShaltoorLog $log): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $normalized = is_string($request->input('normalized')) ? (string) $request->input('normalized') : '';
        $log->markHandled($normalized, $owner);

        return redirect()->route('dashboard.shaltoor', ['days' => $request->input('days')])->with('status', __('dashboard.shaltoor.handled_done'));
    }
}
