<?php

namespace App\Http\Controllers\Dashboard\Requests;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use App\Services\Requests\FeedbackBoard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Requests → Customer feedback (dashboard, VOICE-OF-CUSTOMER §5, OPS-031, M50): the numbers with their counts, the
 * branch comparison, the weekly trend and the comments — each can be archived or cleaned of personal data. Owner only.
 */
final class FeedbackController extends Controller
{
    public function index(Request $request, FeedbackBoard $board): View
    {
        $filters = FeedbackBoard::filters($request->query());
        $comments = $board->comments($filters, max(1, (int) $request->query('page', '1')));
        $locale = app()->getLocale();

        return view('dashboard.requests.feedback.index', [
            'filters' => $filters,
            'branches' => $board->branches($locale),
            'averages' => $board->averages($filters),
            'byBranch' => $board->byBranch($filters),
            'weekly' => $board->weekly($filters),
            'topics' => $board->topics($filters),
            'comments' => $comments->items(),
            'current' => $comments->currentPage(),
            'last' => $comments->lastPage(),
            'total' => $comments->total(),
        ]);
    }

    public function archive(Request $request, Feedback $feedback, FeedbackBoard $board): RedirectResponse
    {
        $board->archive($feedback, $this->owner($request));

        return back()->with('status', __('dashboard.requests.feedback.archived'));
    }

    public function restore(Request $request, Feedback $feedback, FeedbackBoard $board): RedirectResponse
    {
        $board->restore($feedback, $this->owner($request));

        return back()->with('status', __('dashboard.requests.feedback.restored'));
    }

    public function redact(Request $request, Feedback $feedback, FeedbackBoard $board): RedirectResponse
    {
        $board->redact($feedback, $request->string('comment')->toString(), $this->owner($request));

        return back()->with('status', __('dashboard.requests.feedback.redacted'));
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
