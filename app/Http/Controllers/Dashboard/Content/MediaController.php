<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\User;
use App\Services\Dashboard\MediaEditor;
use App\Services\Media\MediaLibrary;
use App\Services\Media\MediaRights;
use App\Support\Input;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Content → Images (dashboard, M50): the library as a grid filtered by the Owner's decision, an upload screen, and one
 * screen per image (MediaEditor). Owner only (routes/dashboard.php). Previews come from the private disk through this
 * controller — originals are never public.
 */
final class MediaController extends Controller
{
    public const FILTERS = ['all', 'pending', 'approved', 'rejected', 'archived'];

    private const PER_PAGE = 24;

    public function index(Request $request): View
    {
        $filter = in_array($request->query('show'), self::FILTERS, true) ? Input::query($request, 'show') : 'all';
        $counts = [];
        foreach (self::FILTERS as $name) {
            $counts[$name] = $this->filtered($name)->count();
        }
        $page = $this->filtered($filter)->orderByDesc('id')->paginate(self::PER_PAGE)->withQueryString();

        return view('dashboard.media.index', [
            'filter' => $filter,
            'counts' => $counts,
            'items' => $page->getCollection()->map(fn (Media $media): array => [
                'media' => $media,
                'live' => MediaRights::canUse($media, 'website') && ! empty($media->variants),
            ])->all(),
            'current' => $page->currentPage(),
            'last' => $page->lastPage(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.media.create');
    }

    public function store(Request $request, MediaEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $files = $request->file('files');
        $result = $editor->upload(is_array($files) ? $files : [], $request->except('files'), $owner);
        if ($result['new'] === [] && $result['existing'] === []) {
            return back()->withInput($request->except('files'))->withErrors($result['errors']);
        }
        $status = trans_choice('dashboard.media.uploaded', count($result['new']), ['count' => count($result['new'])]);
        if ($result['existing'] !== []) {
            $status .= ' '.__('dashboard.media.already', ['codes' => implode('، ', array_map(fn (Media $m): string => $m->code, $result['existing']))]);
        }
        $redirect = count($result['new']) === 1 && $result['existing'] === []
            ? redirect()->route('dashboard.media.edit', $result['new'][0])
            : redirect()->route('dashboard.media.index', ['show' => 'pending']);

        return $redirect->with('status', $status)->withErrors($result['errors']);
    }

    public function edit(Request $request, Media $media, MediaEditor $editor): View
    {
        $people = $request->old('people', $media->people_consents ?? []);
        $people = is_array($people) ? array_values(array_filter($people, 'is_array')) : [];

        return view('dashboard.media.edit', [
            'media' => $media,
            'problems' => MediaRights::problems($media, 'website'),
            'live' => MediaRights::canUse($media, 'website') && ! empty($media->variants),
            'usedIn' => $editor->usedIn($media, app()->getLocale()),
            'pressKit' => $editor->inPressKit($media),
            'people' => $this->withBlankRow($people),
        ]);
    }

    public function update(Request $request, Media $media, MediaEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $errors = $editor->save($media, $request->all(), $owner);
        if ($errors !== []) {
            return back()->withInput()->withErrors($errors);
        }

        return redirect()->route('dashboard.media.edit', $media)->with('status', __('dashboard.saved'));
    }

    public function preview(Media $media, MediaLibrary $library): BinaryFileResponse
    {
        $response = response()->file($library->preview($media), ['Content-Type' => 'image/webp', 'X-Content-Type-Options' => 'nosniff']);
        $response->setPrivate();
        $response->setMaxAge(86400);

        return $response;
    }

    public function archive(Request $request, Media $media, MediaEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $editor->archive($media, $owner);

        return redirect()->route('dashboard.media.edit', $media)->with('status', __('dashboard.media.archived_done'));
    }

    public function restore(Request $request, Media $media, MediaEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $editor->restore($media, $owner);

        return redirect()->route('dashboard.media.edit', $media)->with('status', __('dashboard.media.restored_done'));
    }

    /**
     * The people rows to show, ending with one blank row to add a person (unless the last one sent is blank already).
     *
     * @param  list<array<mixed>>  $rows
     * @return list<array<mixed>>
     */
    private function withBlankRow(array $rows): array
    {
        $last = $rows === [] ? null : $rows[array_key_last($rows)];
        if ($last === null || array_filter($last, fn (mixed $value): bool => is_string($value) && trim($value) !== '') !== []) {
            $rows[] = [];
        }

        return $rows;
    }

    /** @return Builder<Media> */
    private function filtered(string $filter): Builder
    {
        $query = Media::query();

        return match ($filter) {
            'archived' => $query->whereNotNull('archived_at'),
            'pending' => $query->whereNull('archived_at')->where('approval_status', Media::PENDING),
            'approved' => $query->whereNull('archived_at')->where('approval_status', Media::APPROVED),
            'rejected' => $query->whereNull('archived_at')->where('approval_status', Media::REJECTED),
            default => $query->whereNull('archived_at'),
        };
    }
}
