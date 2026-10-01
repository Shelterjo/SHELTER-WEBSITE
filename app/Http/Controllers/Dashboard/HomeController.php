<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/** Command Center shell (PHASE 1). The cards and queues arrive in PHASE 3 (FINAL-ARCHITECTURE-REVIEW §10). */
final class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard.home');
    }
}
