<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Forms\FormGuard;
use App\Services\Shaltoor\Shaltoor;
use App\Services\Shaltoor\ShaltoorAnswer;
use App\Services\Shaltoor\ShaltoorLog;
use App\Services\Shaltoor\ShaltoorSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * POST /{locale}/shaltoor/ — one question in, one answer out (JSON). Untrusted input: length-capped, never echoed as
 * HTML, rate-limited per visitor (keyed by an HMAC of the IP, never the IP itself), answered from public data only.
 * When the Owner switches Shaltoor off, this answers 404 and the launcher is not drawn.
 */
final class ShaltoorController extends Controller
{
    public function __invoke(Request $request, Shaltoor $shaltoor, ShaltoorLog $log, ShaltoorSettings $settings): JsonResponse
    {
        abort_unless($settings->enabled(), 404);
        $locale = app()->getLocale(); // SetLocale has already applied and removed the {locale} parameter
        $key = 'shaltoor:'.FormGuard::clientKey($request);
        foreach (['m' => [(int) config('shaltoor.rate_limit.per_minute', 12), 60], 'd' => [(int) config('shaltoor.rate_limit.per_day', 150), 86400]] as $window => [$max, $decay]) {
            if (RateLimiter::tooManyAttempts($key.':'.$window, $max)) {
                return response()->json((new ShaltoorAnswer((string) __('shaltoor.answers.rate_limited', [], $locale), 'rate_limited', false))->toArray(), 429);
            }
            RateLimiter::hit($key.':'.$window, $decay);
        }

        $question = is_string($request->input('question')) ? (string) $request->input('question') : '';
        $branch = is_string($request->input('branch')) && preg_match('/^[a-z0-9-]{1,40}$/', (string) $request->input('branch')) === 1 ? (string) $request->input('branch') : null;
        $answer = $shaltoor->answer($question, $locale, $branch);
        if (! in_array($answer->topic, ['too_long', 'unavailable'], true)) {
            $page = is_string($request->input('page')) ? (string) $request->input('page') : null;
            $conversation = is_string($request->input('conversation')) ? (string) $request->input('conversation') : null;
            $log->record($question, $answer, $locale, $page, $conversation);
        }

        return response()->json($answer->toArray())->header('Cache-Control', 'no-store');
    }
}
