<?php

namespace App\View\Composers;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
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
            'dashboard.attention' => ['dashboard.nav.attention', 'triangle-alert', 'dashboard.attention'],
            'dashboard.live' => ['dashboard.nav.live', 'clock', 'dashboard.live'],
            'dashboard.seo' => ['dashboard.nav.seo', 'search', 'dashboard.seo'],
            'dashboard.redirects.index' => ['dashboard.nav.redirects', 'arrow-right', 'dashboard.redirects.*'],
            'dashboard.history' => ['dashboard.history.nav', 'rotate-cw', 'dashboard.history*'],
        ],
        'content' => [
            'dashboard.pages.index' => ['dashboard.nav.pages', 'file-text', 'dashboard.pages.*'],
            'dashboard.media.index' => ['dashboard.nav.media', 'image', 'dashboard.media.*'],
            'dashboard.awards.index' => ['dashboard.nav.awards', 'circle-check', 'dashboard.awards.*'],
            'dashboard.team.index' => ['dashboard.nav.team', 'hand', 'dashboard.team.*'],
            'dashboard.events.index' => ['dashboard.nav.events', 'calendar', 'dashboard.events.*'],
            'dashboard.announcements.index' => ['dashboard.nav.announcements', 'message-square-text', 'dashboard.announcements.*'],
            'dashboard.texts.index' => ['dashboard.nav.texts', 'file-text', 'dashboard.texts.*'],
            'dashboard.shaltoor' => ['dashboard.nav.shaltoor', 'messages-square', 'dashboard.shaltoor*'],
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

    /**
     * Screens with their own change history (AUDIT-003): an item's screen links to that item's history, a list to its
     * area. route => ['item', type, route parameter] or ['area', area].
     */
    private const HISTORY = [
        'dashboard.pages.edit' => ['item', 'page', 'key'],
        'dashboard.branches.show' => ['item', 'branch', 'branch'],
        'dashboard.menu.show' => ['item', 'product', 'product'],
        'dashboard.events.edit' => ['item', 'event', 'event'],
        'dashboard.announcements.edit' => ['item', 'announcement', 'announcement'],
        'dashboard.media.edit' => ['item', 'media', 'media'],
        'dashboard.awards.edit' => ['item', 'award', 'award'],
        'dashboard.team.edit' => ['item', 'team', 'member'],
        'dashboard.careers.show' => ['item', 'application', 'application'],
        'dashboard.partnerships.show' => ['item', 'application', 'application'],
        'dashboard.pages.index' => ['area', 'pages'],
        'dashboard.branches.index' => ['area', 'branches'],
        'dashboard.menu.index' => ['area', 'menu'],
        'dashboard.menu.sections' => ['area', 'menu'],
        'dashboard.menu.season' => ['area', 'menu'],
        'dashboard.texts.index' => ['area', 'texts'],
        'dashboard.settings' => ['area', 'settings'],
        'dashboard.consents' => ['area', 'settings'],
        'dashboard.contacts.index' => ['area', 'contacts'],
        'dashboard.redirects.index' => ['area', 'redirects'],
        'dashboard.shaltoor' => ['area', 'shaltoor'],
        'dashboard.events.index' => ['area', 'events'],
        'dashboard.announcements.index' => ['area', 'announcements'],
        'dashboard.media.index' => ['area', 'media'],
        'dashboard.awards.index' => ['area', 'people'],
        'dashboard.team.index' => ['area', 'people'],
        'dashboard.careers.index' => ['area', 'requests'],
        'dashboard.partnerships.index' => ['area', 'requests'],
        'dashboard.feedback.index' => ['area', 'requests'],
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
        $view->with('dashboardHistory', $this->history());
    }

    /** The link to this screen's change history, or null (no history for it, or the item was never saved). */
    private function history(): ?string
    {
        $name = $this->request->route()?->getName();
        $target = is_string($name) ? (self::HISTORY[$name] ?? null) : null;
        if ($target === null || ! Route::has('dashboard.history')) {
            return null;
        }
        if ($target[0] === 'area') {
            return route('dashboard.history', ['area' => $target[1]]);
        }
        $value = $this->request->route($target[2]);
        $id = match (true) {
            $value instanceof Model => $value->getKey(),
            $target[1] === 'page' && is_string($value) => Page::query()->where('key', $value)->value('id'),
            is_numeric($value) => (int) $value,
            default => null,
        };

        return is_numeric($id) ? route('dashboard.history', ['item' => $target[1].':'.$id]) : null;
    }
}
