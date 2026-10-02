<?php

namespace App\Http\Controllers\Dashboard\Requests;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\InterviewLocation;
use App\Models\Recruitment\JordanCity;
use App\Models\User;
use App\Services\Requests\RecruitmentSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Requests → Job applications → Settings (CAREERS-071/075): the "waiting too long" threshold, interview places, cities. */
final class CareersSettingsController extends Controller
{
    public function show(): View
    {
        return view('dashboard.requests.careers.settings', [
            'staleDays' => RecruitmentSettings::staleDays(),
            'locations' => InterviewLocation::query()->orderBy('sort_order')->get(),
            'cities' => JordanCity::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function stale(Request $request, RecruitmentSettings $settings): RedirectResponse
    {
        return $this->done($settings->setStaleDays($request->input('stale_days'), $this->owner($request)), 'stale', 'stale');
    }

    public function storeLocation(Request $request, RecruitmentSettings $settings): RedirectResponse
    {
        return $this->done($settings->saveLocation(null, $request->all(), $this->owner($request)), 'locations', 'location-new');
    }

    public function updateLocation(Request $request, InterviewLocation $location, RecruitmentSettings $settings): RedirectResponse
    {
        return $this->done($settings->saveLocation($location, $request->all(), $this->owner($request)), 'locations', 'location-'.$location->id);
    }

    public function storeCity(Request $request, RecruitmentSettings $settings): RedirectResponse
    {
        return $this->done($settings->saveCity(null, $request->all(), $this->owner($request)), 'cities', 'city-new');
    }

    public function updateCity(Request $request, JordanCity $city, RecruitmentSettings $settings): RedirectResponse
    {
        return $this->done($settings->saveCity($city, $request->all(), $this->owner($request)), 'cities', 'city-'.$city->id);
    }

    /** @param  array<string, string>  $errors */
    private function done(array $errors, string $anchor, string $bag): RedirectResponse
    {
        $to = redirect()->to(route('dashboard.careers.settings').'#'.$anchor);

        return $errors !== [] ? $to->withInput()->withErrors($errors, $bag) : $to->with('status', __('dashboard.saved'));
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
