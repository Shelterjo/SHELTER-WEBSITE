<?php

namespace App\Services\Seo;

use App\Models\Redirect;
use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Old and changed addresses (SEO-011/012/014/018/020, INFRA-033, docs/platform/LEGACY-URL-MIGRATION.md).
 *
 * - Only an address no page answers is looked up (the 404 path), so a row can never hide a live page.
 * - One hop: the target is a page, never another redirect; the visitor's query string is kept.
 * - A row works only once the Owner switches it on (active). Rows seeded from the approved migration map start as
 *   drafts: production redirects are a launch step the Owner approves (SEO-013, CLAUDE.md production safety).
 * - Targets stay on this site (no open redirect). 410 = gone on purpose (old demo pages, retired sitemaps).
 * - The active rows are cached as one small map; every change clears it. Hits are counted for the Owner.
 */
final class LegacyRedirects
{
    public const CODES = [301, 302, 410];

    public const STATES = ['draft', 'active', 'archived'];

    private const CACHE_KEY = 'redirects.active.v1';

    /** Paths that are never a legacy source: the dashboard, the health check, built assets and public storage. */
    private const RESERVED = '#^/(dashboard|up|build|storage|livewire)(/|$)#';

    public function __construct(private readonly AuditLogger $audit) {}

    /** One form for every stored and requested path: decoded, lower case, single slashes, no trailing slash. */
    public static function normalize(string $path): string
    {
        $path = (string) (parse_url($path, PHP_URL_PATH) ?? '');
        $path = '/'.ltrim((string) preg_replace('#/+#', '/', rawurldecode($path)), '/');
        $path = mb_strtolower(trim($path));

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * The active row for a path, if any.
     *
     * @return array{id: int, target: string|null, code: int}|null
     */
    public function find(string $path): ?array
    {
        /** @var array<string, array{id: int, target: string|null, code: int}> $map */
        $map = Cache::rememberForever(self::CACHE_KEY, fn (): array => Redirect::query()->where('state', 'active')
            ->get(['id', 'source_path', 'target', 'status_code'])
            ->mapWithKeys(fn (Redirect $r): array => [$r->source_path => ['id' => $r->id, 'target' => $r->target, 'code' => $r->status_code]])
            ->all());

        return $map[self::normalize($path)] ?? null;
    }

    /** The answer for an address no page answers (301/302 to the target, or 410), or null for the normal 404. */
    public function respond(Request $request): ?Response
    {
        if (! $request->isMethodSafe()) {
            return null;
        }
        try {
            $hit = $this->find($request->getPathInfo());
            if ($hit === null) {
                return null;
            }
            Redirect::query()->whereKey($hit['id'])->update(['hits' => DB::raw('hits + 1'), 'last_hit_at' => now()]);
        } catch (Throwable) {
            return null; // a missing table or a database hiccup never turns a 404 into a 500
        }
        if ($hit['code'] === 410 || $hit['target'] === null) {
            return response()->view('errors.404', [], 410);
        }
        $query = $request->getQueryString();

        return redirect()->to(self::absolute($hit['target']).($query !== null ? '?'.$query : ''), $hit['code']);
    }

    /** A stored target (a path on this site) as an absolute URL that keeps its trailing slash (no second hop). */
    public static function absolute(string $target): string
    {
        return rtrim(url('/'), '/').$target;
    }

    /**
     * Validate and save one row (new or edited). Saving never switches a row on: activation is its own step.
     *
     * @param  array<string, mixed>  $input
     * @return array{redirect: Redirect|null, errors: array<string, string>}
     */
    public function save(?Redirect $redirect, array $input, User $owner): array
    {
        $errors = [];
        $rawSource = is_string($input['source_path'] ?? null) ? trim($input['source_path']) : '';
        $source = $rawSource === '' ? '' : self::normalize($rawSource);
        $code = (int) ($input['status_code'] ?? 301);
        $note = is_string($input['note'] ?? null) ? mb_substr(trim($input['note']), 0, 500) : '';

        if ($source === '' || $source === '/' || mb_strlen($source) > 500) {
            $errors['source_path'] = (string) __('dashboard.redirects.errors.source');
        } elseif (preg_match(self::RESERVED, $source) === 1) {
            $errors['source_path'] = (string) __('dashboard.redirects.errors.reserved');
        } else {
            $other = Redirect::query()->where('source_path', $source)->when($redirect !== null, fn ($q) => $q->whereKeyNot($redirect?->id))->first();
            if ($other !== null) {
                $errors['source_path'] = (string) __($other->state === 'archived' ? 'dashboard.redirects.errors.archived_exists' : 'dashboard.redirects.errors.exists');
            }
        }
        if (! in_array($code, self::CODES, true)) {
            $errors['status_code'] = (string) __('dashboard.redirects.errors.code');
        }

        $target = null;
        if ($code !== 410) {
            [$target, $targetError] = $this->target($input['target'] ?? null, $source);
            if ($targetError !== null) {
                $errors['target'] = $targetError;
            }
        }
        // No chains either way: this source must not be where another row points.
        if (! isset($errors['source_path']) && $source !== '') {
            $pointing = Redirect::query()->where('state', '!=', 'archived')->when($redirect !== null, fn ($q) => $q->whereKeyNot($redirect?->id))
                ->get(['source_path', 'target'])->first(fn (Redirect $r): bool => $r->target !== null && self::normalize($r->target) === $source);
            if ($pointing !== null) {
                $errors['source_path'] = (string) __('dashboard.redirects.errors.pointed', ['source' => $pointing->source_path]);
            }
        }
        if ($errors !== []) {
            return ['redirect' => $redirect, 'errors' => $errors];
        }

        $row = $redirect ?? new Redirect(['origin' => 'owner', 'state' => 'draft']);
        $before = $row->exists ? $row->only(['source_path', 'target', 'status_code', 'note']) : null;
        $row->fill(['source_path' => $source, 'target' => $target, 'status_code' => $code, 'note' => $note === '' ? null : $note, 'updated_by' => $owner->id])->save();
        $this->audit->record($before === null ? 'redirects.created' : 'redirects.updated', $row,
            ['before' => $before, 'after' => $row->only(['source_path', 'target', 'status_code', 'note'])], actor: $owner);
        $this->flush();

        return ['redirect' => $row, 'errors' => []];
    }

    /** Switch on / off / archive / restore. Switching on re-checks the target (pages can change after a row is saved). */
    public function command(Redirect $redirect, string $command, User $owner): ?string
    {
        $state = match ($command) {
            'activate' => 'active',
            'deactivate', 'restore' => 'draft',
            'archive' => 'archived',
            default => null,
        };
        if ($state === null) {
            return (string) __('dashboard.redirects.errors.command');
        }
        if ($state === 'active' && $redirect->status_code !== 410) {
            [, $error] = $this->target($redirect->target, $redirect->source_path, $redirect);
            if ($error !== null) {
                return $error;
            }
        }
        $before = $redirect->state;
        $redirect->forceFill(['state' => $state, 'updated_by' => $owner->id])->save();
        $this->audit->record('redirects.'.$command, $redirect, ['before' => ['state' => $before], 'after' => ['state' => $state]], actor: $owner);
        $this->flush();

        return null;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * A target as a path on this site, with the reason it is refused (if it is).
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function target(mixed $value, string $source, ?Redirect $self = null): array
    {
        $raw = is_string($value) ? trim($value) : '';
        if ($raw === '') {
            return [null, (string) __('dashboard.redirects.errors.target_required')];
        }
        if (preg_match('#^https?://#i', $raw) === 1) {
            $host = (string) parse_url($raw, PHP_URL_HOST);
            $site = (string) parse_url((string) config('app.url'), PHP_URL_HOST);
            $bare = fn (string $h): string => (string) preg_replace('/^www\./', '', mb_strtolower($h));
            if ($host === '' || $site === '' || $bare($host) !== $bare($site)) {
                return [null, (string) __('dashboard.redirects.errors.external')];
            }
            $query = parse_url($raw, PHP_URL_QUERY);
            $raw = (string) (parse_url($raw, PHP_URL_PATH) ?? '/').(is_string($query) ? '?'.$query : '');
        }
        if (! str_starts_with($raw, '/') || str_starts_with($raw, '//') || mb_strlen($raw) > 500) {
            return [null, (string) __('dashboard.redirects.errors.target_path')];
        }
        if (self::normalize($raw) === $source) {
            return [null, (string) __('dashboard.redirects.errors.loop')];
        }
        $chained = Redirect::query()->where('state', '!=', 'archived')->where('source_path', self::normalize($raw))
            ->when($self !== null, fn ($q) => $q->whereKeyNot($self?->id))->exists();
        if ($chained) {
            return [null, (string) __('dashboard.redirects.errors.chain')];
        }
        try {
            Route::getRoutes()->match(Request::create($raw));
        } catch (HttpException) {
            return [null, (string) __('dashboard.redirects.errors.no_page')];
        }

        return [$raw, null];
    }
}
