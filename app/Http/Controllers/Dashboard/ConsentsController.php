<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\ConsentVersion;
use App\Models\User;
use App\Services\Dashboard\ConsentEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Settings → Consent texts (M50): the active text of each form, its earlier versions, and a new version. */
final class ConsentsController extends Controller
{
    public function index(): View
    {
        $scopes = [];
        foreach (ConsentEditor::SCOPES as $scope => $languages) {
            $scopes[$scope] = [
                'languages' => $languages,
                'active' => ConsentEditor::active($scope),
                'history' => ConsentVersion::query()->where('scope', $scope)->orderByDesc('active_from')->orderByDesc('id')->get(),
            ];
        }

        return view('dashboard.consents', ['scopes' => $scopes]);
    }

    public function publish(Request $request, string $scope, ConsentEditor $editor): RedirectResponse
    {
        abort_unless(array_key_exists($scope, ConsentEditor::SCOPES), 404);
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->publish($scope, $request->all(), $owner);
        $url = route('dashboard.consents').'#consent-'.$scope;
        if ($result['version'] === null) {
            return redirect()->to($url)->withInput()->withErrors($result['errors'], 'consent-'.$scope);
        }

        return redirect()->to($url)->with('status', __('dashboard.consents.published', ['version' => $result['version']->version]));
    }
}
