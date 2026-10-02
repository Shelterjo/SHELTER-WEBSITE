<?php

use App\Http\Controllers\Dashboard\AttentionController;
use App\Http\Controllers\Dashboard\Auth\ConfirmIdentityController;
use App\Http\Controllers\Dashboard\Auth\LoginController;
use App\Http\Controllers\Dashboard\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Dashboard\Auth\TwoFactorSetupController;
use App\Http\Controllers\Dashboard\ConsentsController;
use App\Http\Controllers\Dashboard\Content\AnnouncementsController;
use App\Http\Controllers\Dashboard\Content\AwardsController;
use App\Http\Controllers\Dashboard\Content\EventsController;
use App\Http\Controllers\Dashboard\Content\MediaController;
use App\Http\Controllers\Dashboard\Content\PagesController;
use App\Http\Controllers\Dashboard\Content\TeamController;
use App\Http\Controllers\Dashboard\Content\TextsController;
use App\Http\Controllers\Dashboard\Data\BranchesController;
use App\Http\Controllers\Dashboard\Data\ContactsController;
use App\Http\Controllers\Dashboard\Data\MenuBulkController;
use App\Http\Controllers\Dashboard\Data\MenuController;
use App\Http\Controllers\Dashboard\Data\MenuSectionsController;
use App\Http\Controllers\Dashboard\HistoryController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\LiveController;
use App\Http\Controllers\Dashboard\RecoveryCodesController;
use App\Http\Controllers\Dashboard\RedirectsController;
use App\Http\Controllers\Dashboard\Requests\CareersBulkController;
use App\Http\Controllers\Dashboard\Requests\CareersController;
use App\Http\Controllers\Dashboard\Requests\CareersSettingsController;
use App\Http\Controllers\Dashboard\Requests\FeedbackController;
use App\Http\Controllers\Dashboard\Requests\PartnershipsController;
use App\Http\Controllers\Dashboard\SeoController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\ShaltoorController;
use App\Http\Middleware\DashboardLocale;
use App\Http\Middleware\PreventStaleEdits;
use App\Services\Dashboard\ExperienceCommands;
use Illuminate\Support\Facades\Route;

/*
| Owner dashboard routes (PHASE 1: authentication shell; modules from PHASE 3).
| Every route except the sign-in steps is behind auth + owner (active, second factor, absolute lifetime).
| No registration and no password-reset-by-email route exist (owner accounts are created on the server).
*/

Route::prefix('dashboard')->middleware(DashboardLocale::class)->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
        Route::get('two-factor', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
        Route::post('two-factor', [TwoFactorChallengeController::class, 'store'])->name('two-factor.verify');
        Route::get('two-factor/setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');
        Route::post('two-factor/setup', [TwoFactorSetupController::class, 'store'])->name('two-factor.enable');
    });

    Route::middleware(['auth', 'owner', PreventStaleEdits::class])->name('dashboard.')->group(function (): void {
        Route::get('/', HomeController::class)->name('home');

        // Content (FINAL-ARCHITECTURE-REVIEW §10) — no code needed to change the site (M30, M50).
        Route::get('content/pages', [PagesController::class, 'index'])->name('pages.index');
        Route::get('content/pages/{key}', [PagesController::class, 'edit'])->name('pages.edit');
        Route::put('content/pages/{key}', [PagesController::class, 'update'])->name('pages.update');
        Route::get('content/media', [MediaController::class, 'index'])->name('media.index');
        Route::get('content/media/upload', [MediaController::class, 'create'])->name('media.create');
        Route::post('content/media', [MediaController::class, 'store'])->name('media.store');
        Route::whereNumber('media')->group(function (): void {
            Route::get('content/media/{media}', [MediaController::class, 'edit'])->name('media.edit');
            Route::put('content/media/{media}', [MediaController::class, 'update'])->name('media.update');
            Route::get('content/media/{media}/preview', [MediaController::class, 'preview'])->name('media.preview');
            Route::post('content/media/{media}/archive', [MediaController::class, 'archive'])->name('media.archive');
            Route::post('content/media/{media}/restore', [MediaController::class, 'restore'])->name('media.restore');
        });
        Route::get('content/awards', [AwardsController::class, 'index'])->name('awards.index');
        Route::get('content/awards/new', [AwardsController::class, 'create'])->name('awards.create');
        Route::post('content/awards', [AwardsController::class, 'store'])->name('awards.store');
        Route::whereNumber('award')->group(function (): void {
            Route::get('content/awards/{award}', [AwardsController::class, 'edit'])->name('awards.edit');
            Route::put('content/awards/{award}', [AwardsController::class, 'update'])->name('awards.update');
            Route::post('content/awards/{award}/archive', [AwardsController::class, 'archive'])->name('awards.archive');
            Route::post('content/awards/{award}/restore', [AwardsController::class, 'restore'])->name('awards.restore');
        });
        Route::get('content/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('content/team/new', [TeamController::class, 'create'])->name('team.create');
        Route::post('content/team', [TeamController::class, 'store'])->name('team.store');
        Route::whereNumber('member')->group(function (): void {
            Route::get('content/team/{member}', [TeamController::class, 'edit'])->name('team.edit');
            Route::put('content/team/{member}', [TeamController::class, 'update'])->name('team.update');
            Route::post('content/team/{member}/archive', [TeamController::class, 'archive'])->name('team.archive');
            Route::post('content/team/{member}/restore', [TeamController::class, 'restore'])->name('team.restore');
        });
        Route::get('live', LiveController::class)->name('live');
        Route::get('attention', [AttentionController::class, 'index'])->name('attention');
        Route::post('attention/{signal}/dismiss', [AttentionController::class, 'dismiss'])->whereNumber('signal')->name('attention.dismiss');
        // Change history (AUDIT-001…004, AUDIT-009): every audited change and an item's versions; bringing an earlier
        // version back changes the live site — a fresh re-confirmation for the preview and the restore.
        Route::get('history', [HistoryController::class, 'index'])->name('history');
        Route::get('history/versions/{type}/{id}', [HistoryController::class, 'versions'])->where('type', '[a-z]+')->whereNumber('id')->name('history.versions');
        Route::whereNumber('version')->middleware('confirmed')->group(function (): void {
            Route::get('history/restore/{version}', [HistoryController::class, 'preview'])->name('history.restore');
            Route::post('history/restore/{version}', [HistoryController::class, 'restore'])->name('history.restore.run');
        });
        Route::get('seo', SeoController::class)->name('seo');
        // Shaltoor (M69 §23): what visitors asked; switching it on/off or changing its words is a live change.
        Route::get('shaltoor', [ShaltoorController::class, 'index'])->name('shaltoor');
        Route::post('shaltoor/handled', [ShaltoorController::class, 'handled'])->name('shaltoor.handled');
        Route::put('shaltoor', [ShaltoorController::class, 'update'])->middleware('confirmed')->name('shaltoor.update');
        Route::get('seo/redirects', [RedirectsController::class, 'index'])->name('redirects.index');
        // Old links change what visitors and Google get on the live domain: a fresh re-confirmation to edit them.
        Route::middleware('confirmed')->group(function (): void {
            Route::post('seo/redirects', [RedirectsController::class, 'store'])->name('redirects.store');
            Route::put('seo/redirects/{redirect}', [RedirectsController::class, 'update'])->whereNumber('redirect')->name('redirects.update');
            Route::post('seo/redirects/{redirect}/{command}', [RedirectsController::class, 'command'])->whereNumber('redirect')
                ->whereIn('command', ['activate', 'deactivate', 'archive', 'restore'])->name('redirects.command');
        });
        Route::post('live/{experience}/disable', [LiveController::class, 'disable'])->whereNumber('experience')->name('live.disable');
        Route::get('content/announcements', [AnnouncementsController::class, 'index'])->name('announcements.index');
        Route::get('content/announcements/new', [AnnouncementsController::class, 'create'])->name('announcements.create');
        Route::post('content/announcements', [AnnouncementsController::class, 'store'])->name('announcements.store');
        Route::whereNumber('announcement')->group(function (): void {
            Route::get('content/announcements/{announcement}', [AnnouncementsController::class, 'edit'])->name('announcements.edit');
            Route::put('content/announcements/{announcement}', [AnnouncementsController::class, 'update'])->name('announcements.update');
            Route::post('content/announcements/{announcement}/{command}', [AnnouncementsController::class, 'command'])->whereIn('command', ExperienceCommands::COMMANDS)->name('announcements.command');
        });
        Route::get('content/events', [EventsController::class, 'index'])->name('events.index');
        Route::get('content/events/new', [EventsController::class, 'create'])->name('events.create');
        Route::post('content/events', [EventsController::class, 'store'])->name('events.store');
        Route::whereNumber('event')->group(function (): void {
            Route::get('content/events/{event}', [EventsController::class, 'edit'])->name('events.edit');
            Route::put('content/events/{event}', [EventsController::class, 'update'])->name('events.update');
            Route::post('content/events/{event}/{command}', [EventsController::class, 'command'])->whereIn('command', ExperienceCommands::COMMANDS)->name('events.command');
        });

        // Requests — job applications (CAREERS-052…073). Identity numbers and permanent deletion need a fresh re-confirmation.
        Route::get('requests/careers', [CareersController::class, 'index'])->name('careers.index');
        Route::post('requests/careers/view', [CareersController::class, 'view'])->name('careers.view');
        Route::post('requests/careers/filters', [CareersController::class, 'saveFilter'])->name('careers.filters.store');
        Route::delete('requests/careers/filters/{filter}', [CareersController::class, 'destroyFilter'])->whereNumber('filter')->name('careers.filters.destroy');
        Route::get('requests/careers/settings', [CareersSettingsController::class, 'show'])->name('careers.settings');
        Route::put('requests/careers/settings/stale', [CareersSettingsController::class, 'stale'])->name('careers.settings.stale');
        Route::post('requests/careers/settings/locations', [CareersSettingsController::class, 'storeLocation'])->name('careers.settings.locations.store');
        Route::put('requests/careers/settings/locations/{location}', [CareersSettingsController::class, 'updateLocation'])->whereNumber('location')->name('careers.settings.locations.update');
        Route::post('requests/careers/settings/cities', [CareersSettingsController::class, 'storeCity'])->name('careers.settings.cities.store');
        Route::put('requests/careers/settings/cities/{city}', [CareersSettingsController::class, 'updateCity'])->whereNumber('city')->name('careers.settings.cities.update');
        Route::post('requests/careers/bulk', [CareersBulkController::class, 'bulk'])->name('careers.bulk');
        Route::post('requests/careers/bulk/apply', [CareersBulkController::class, 'apply'])->name('careers.bulk.apply');
        Route::middleware('confirmed')->group(function (): void {
            Route::get('requests/careers/export', [CareersBulkController::class, 'form'])->name('careers.export');
            Route::post('requests/careers/export', [CareersBulkController::class, 'export'])->name('careers.export.run');
            Route::post('requests/careers/attachments', [CareersBulkController::class, 'zip'])->name('careers.zip');
        });
        Route::whereNumber(['application', 'note', 'attachment'])->group(function (): void {
            Route::get('requests/careers/{application}', [CareersController::class, 'show'])->name('careers.show');
            Route::get('requests/careers/{application}/quick', [CareersController::class, 'quick'])->name('careers.quick');
            Route::post('requests/careers/{application}/status', [CareersController::class, 'status'])->name('careers.status');
            Route::post('requests/careers/{application}/restore', [CareersController::class, 'restore'])->name('careers.restore');
            Route::post('requests/careers/{application}/notes', [CareersController::class, 'addNote'])->name('careers.notes.store');
            Route::put('requests/careers/{application}/notes/{note}', [CareersController::class, 'editNote'])->name('careers.notes.update');
            Route::post('requests/careers/{application}/interview', [CareersController::class, 'interview'])->name('careers.interview');
            Route::get('requests/attachments/{attachment}', [CareersController::class, 'attachment'])->name('requests.attachment');
            Route::middleware('confirmed')->group(function (): void {
                Route::get('requests/careers/{application}/identity', [CareersController::class, 'identity'])->name('careers.identity');
                Route::get('requests/careers/{application}/delete', [CareersController::class, 'confirmDelete'])->name('careers.delete');
                Route::delete('requests/careers/{application}', [CareersController::class, 'destroy'])->name('careers.destroy');
            });
        });

        // Requests — partnerships (docs/franchise/04): stage, notes, meetings; archive only, never deleted.
        Route::get('requests/partnerships', [PartnershipsController::class, 'index'])->name('partnerships.index');
        Route::whereNumber(['application', 'note', 'meeting'])->group(function (): void {
            Route::get('requests/partnerships/{application}', [PartnershipsController::class, 'show'])->name('partnerships.show');
            Route::post('requests/partnerships/{application}/status', [PartnershipsController::class, 'status'])->name('partnerships.status');
            Route::post('requests/partnerships/{application}/restore', [PartnershipsController::class, 'restore'])->name('partnerships.restore');
            Route::post('requests/partnerships/{application}/notes', [PartnershipsController::class, 'addNote'])->name('partnerships.notes.store');
            Route::put('requests/partnerships/{application}/notes/{note}', [PartnershipsController::class, 'editNote'])->name('partnerships.notes.update');
            Route::post('requests/partnerships/{application}/meetings', [PartnershipsController::class, 'meeting'])->name('partnerships.meetings.store');
            Route::put('requests/partnerships/{application}/meetings/{meeting}', [PartnershipsController::class, 'meetingState'])->name('partnerships.meetings.update');
        });

        // Requests — customer feedback (VOICE-OF-CUSTOMER §5): the board and the comments.
        Route::get('requests/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::whereNumber('feedback')->group(function (): void {
            Route::post('requests/feedback/{feedback}/archive', [FeedbackController::class, 'archive'])->name('feedback.archive');
            Route::post('requests/feedback/{feedback}/restore', [FeedbackController::class, 'restore'])->name('feedback.restore');
            Route::put('requests/feedback/{feedback}', [FeedbackController::class, 'redact'])->name('feedback.redact');
        });

        // Business data — branches and hours (MDH-005…007). Central facts: a fresh re-confirmation to edit.
        Route::get('data/branches', [BranchesController::class, 'index'])->name('branches.index');
        Route::whereNumber(['branch', 'exception'])->middleware('confirmed')->group(function (): void {
            Route::get('data/branches/{branch}', [BranchesController::class, 'show'])->name('branches.show');
            Route::put('data/branches/{branch}/details', [BranchesController::class, 'details'])->name('branches.details');
            // BRANCH-010: the branch page with the details not published yet, in one language (a new tab; nothing saved).
            Route::put('data/branches/{branch}/details/preview/{locale}', [BranchesController::class, 'previewPage'])->whereIn('locale', ['ar', 'en'])->name('branches.details.preview');
            Route::post('data/branches/{branch}/hours', [BranchesController::class, 'hours'])->name('branches.hours');
            Route::post('data/branches/{branch}/exceptions', [BranchesController::class, 'storeException'])->name('branches.exceptions.store');
            Route::put('data/branches/{branch}/exceptions/{exception}', [BranchesController::class, 'updateException'])->name('branches.exceptions.update');
            Route::post('data/branches/{branch}/exceptions/{exception}/archive', [BranchesController::class, 'archiveException'])->name('branches.exceptions.archive');
        });

        // Content — the site's fixed texts, page titles and Google descriptions (M50): the Owner's wording on top.
        Route::get('content/texts', [TextsController::class, 'index'])->name('texts.index');
        Route::put('content/texts/{group}', [TextsController::class, 'update'])->where('group', '[a-z]+')->name('texts.update');

        // Settings (M50): forms open or closed, Safe Mode, search counting, the founding year — a fresh re-confirmation.
        Route::middleware('confirmed')->group(function (): void {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::get('settings/consents', [ConsentsController::class, 'index'])->name('consents');
            Route::post('settings/consents/{scope}', [ConsentsController::class, 'publish'])->where('scope', '[a-z_]+')->name('consents.publish');
        });

        // Business data — contact numbers and social accounts (CMS-030). Central facts: a fresh re-confirmation.
        Route::middleware('confirmed')->group(function (): void {
            Route::get('data/contacts', [ContactsController::class, 'index'])->name('contacts.index');
            Route::put('data/contacts/{point}', [ContactsController::class, 'update'])->whereNumber('point')->name('contacts.update');
            Route::put('data/contacts/social/{platform}', [ContactsController::class, 'social'])->where('platform', '[a-z]+')->name('contacts.social');
        });

        // Business data — the menu (M33 §5, §16–§18): prices and branch values need a fresh re-confirmation; the review
        // of Arabic names (D-137) does not change prices.
        Route::get('data/menu', [MenuController::class, 'index'])->name('menu.index');
        Route::whereNumber('category')->group(function (): void {
            Route::get('data/menu/review/{category}', [MenuController::class, 'review'])->name('menu.review');
            Route::put('data/menu/review/{category}', [MenuController::class, 'saveReview'])->name('menu.review.save');
            Route::put('data/menu/review/{category}/name', [MenuController::class, 'categoryName'])->name('menu.category.name');
        });
        Route::get('data/menu/words', [MenuController::class, 'words'])->name('menu.words');
        Route::whereNumber(['product', 'branch', 'word', 'category'])->middleware('confirmed')->group(function (): void {
            Route::get('data/menu/sections', [MenuSectionsController::class, 'index'])->name('menu.sections');
            Route::post('data/menu/sections', [MenuSectionsController::class, 'store'])->name('menu.sections.store');
            Route::put('data/menu/sections/{category}', [MenuSectionsController::class, 'update'])->name('menu.sections.update');
            Route::post('data/menu/sections/{category}/{direction}', [MenuSectionsController::class, 'move'])->whereIn('direction', ['up', 'down'])->name('menu.sections.move');
            Route::get('data/menu/new', [MenuController::class, 'create'])->name('menu.create');
            Route::match(['get', 'post'], 'data/menu/bulk', [MenuBulkController::class, 'confirm'])->name('menu.bulk');
            Route::post('data/menu/bulk/apply', [MenuBulkController::class, 'apply'])->name('menu.bulk.apply');
            Route::post('data/menu', [MenuController::class, 'store'])->name('menu.store');
            Route::get('data/menu/season', [MenuController::class, 'season'])->name('menu.season');
            Route::put('data/menu/season/{category}', [MenuController::class, 'saveSeason'])->name('menu.season.save');
            Route::post('data/menu/{product}/words', [MenuController::class, 'searchWord'])->name('menu.words.add');
            Route::post('data/menu/{product}/words/{word}/archive', [MenuController::class, 'archiveWord'])->name('menu.words.archive');
            Route::get('data/menu/{product}', [MenuController::class, 'show'])->name('menu.show');
            Route::put('data/menu/{product}/names', [MenuController::class, 'names'])->name('menu.names');
            Route::put('data/menu/{product}/details', [MenuController::class, 'details'])->name('menu.details');
            Route::post('data/menu/{product}/price', [MenuController::class, 'price'])->name('menu.price');
            Route::put('data/menu/{product}/branches/{branch}', [MenuController::class, 'branch'])->name('menu.branch');
        });
        Route::get('recovery-codes', RecoveryCodesController::class)->name('recovery-codes');
        Route::get('confirm', [ConfirmIdentityController::class, 'show'])->name('confirm');
        Route::post('confirm', [ConfirmIdentityController::class, 'store'])->name('confirm.store');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    });
});
