<?php

namespace App\View\Composers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * Dashboard navigation (FINAL-ARCHITECTURE-REVIEW §10, simplified for the Owner — M50): plain-language groups, and an
 * item appears only once its screen exists (modules ship one by one). Same list on phones (a scrollable row) and
 * desktop (the side column).
 */
final class DashboardChrome
{
    /** group ('home' = untitled) => [route => [lang key, icon, active route pattern]] */
    private const NAV = [
        'home' => [
            'dashboard.home' => ['dashboard.command_center', 'house', 'dashboard.home'],
            'dashboard.live' => ['dashboard.nav.live', 'clock', 'dashboard.live'],
            'dashboard.seo' => ['dashboard.nav.seo', 'search', 'dashboard.seo'],
        ],
        'content' => [
            'dashboard.pages.index' => ['dashboard.nav.pages', 'file-text', 'dashboard.pages.*'],
            'dashboard.media.index' => ['dashboard.nav.media', 'image', 'dashboard.media.*'],
            'dashboard.awards.index' => ['dashboard.nav.awards', 'circle-check', 'dashboard.awards.*'],
            'dashboard.team.index' => ['dashboard.nav.team', 'hand', 'dashboard.team.*'],
            'dashboard.events.index' => ['dashboard.nav.events', 'calendar', 'dashboard.events.*'],
            'dashboard.announcements.index' => ['dashboard.nav.announcements', 'message-square-text', 'dashboard.announcements.*'],
            'dashboard.texts.index' => ['dashboard.nav.texts', 'file-text', 'dashboard.texts.*'],
        ],
        'requests' => [
            'dashboard.careers.index' => ['dashboard.nav.careers', 'briefcase-business', 'dashboard.careers.*'],
            'dashboard.partnerships.index' => ['dashboard.nav.partnerships', 'handshake', 'dashboard.partnerships.*'],
            'dashboard.feedback.index' => ['dashboard.nav.feedback', 'message-square-text', 'dashboard.feedback.*'],
        ],
        'data' => [
            'dashboard.branches.index' => ['dashboard.nav.branches', 'store', 'dashboard.branches.*'],
            'dashboard.menu.index' => ['dashboard.nav.menu', 'coffee', 'dashboard.menu.*'],
            'dashboard.contacts.index' => ['dashboard.nav.contacts', 'phone', 'dashboard.contacts.*'],
            'dashboard.settings' => ['dashboard.nav.settings', 'info', 'dashboard.settings*'],
        ],
    ];

    public function __construct(private readonly Request $request) {}

    public function compose(View $view): void
    {
        $groups = [];
        foreach (self::NAV as $group => $items) {
            $links = [];
            foreach ($items as $route => [$label, $icon, $active]) {
                if (Route::has($route)) {
                    $links[] = ['label' => (string) __($label), 'href' => route($route), 'icon' => $icon, 'current' => $this->request->routeIs($active)];
                }
            }
            if ($links !== []) {
                $groups[] = ['label' => $group !== 'home' ? (string) __('dashboard.nav.groups.'.$group) : null, 'links' => $links];
            }
        }
        $view->with('dashboardNav', $groups);
    }
}
