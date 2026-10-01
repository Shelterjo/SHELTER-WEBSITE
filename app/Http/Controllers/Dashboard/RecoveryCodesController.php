<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Shows the recovery codes exactly once, right after enrollment (they are stored only as digests). */
final class RecoveryCodesController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $codes = $request->session()->get('recovery_codes');
        if (! is_array($codes) || $codes === []) {
            return redirect()->route('dashboard.home');
        }

        return view('dashboard.auth.recovery-codes', ['codes' => $codes]);
    }
}
