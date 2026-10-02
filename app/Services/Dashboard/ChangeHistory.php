<?php

namespace App\Services\Dashboard;

use App\Models\AuditLog;
use App\Models\Award;
use App\Models\Branch;
use App\Models\ContactPoint;
use App\Models\ContentVersion;
use App\Models\Experience;
use App\Models\Fact;
use App\Models\FeatureFlag;
use App\Models\HoursException;
use App\Models\Media;
use App\Models\MenuCategory;
use App\Models\Page;
use App\Models\Product;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\ApplicationInterview;
use App\Models\Recruitment\ApplicationMeeting;
use App\Models\Recruitment\ApplicationNote;
use App\Models\Recruitment\ConsentVersion;
use App\Models\Recruitment\InterviewLocation;
use App\Models\Recruitment\JordanCity;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\SiteText;
use App\Models\SocialLink;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Support\LocalTime;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use LogicException;

/**
 * The change history (AUDIT-001…003, AUDIT-009, AUDIT-010, M35 §46): every audited change in plain words — who (the
 * Owner, the system for scheduled jobs and commands, or a visitor's form), what (the area and the item by name), when
 * (Amman time) and, where the entry kept them, each field from → to with the reason. Filters: area, period, one item.
 * Values pass AuditLogger::mask again on display (an older row can never show a secret), and a few values are never
 * shown here at all (internal notes on applicants, e-mail hashes). Sign-ins and opened files are access, not changes:
 * they have their own area and stay out of "all".
 */
final class ChangeHistory
{
    public const PER_PAGE = 30;

    /** period => days back (null = everything, 0 = since midnight in Amman). The first is the default. */
    public const PERIODS = ['all' => null, 'today' => 0, 'week' => 7, 'month' => 30, 'quarter' => 90];

    /** area => actions: a prefix ending in "." or one exact action. The order is the order of the filter. */
    public const AREAS = [
        'hours' => ['hours.'],
        'contacts' => ['contacts.', 'social.'],
        'branches' => ['branch.'],
        'texts' => ['texts.'],
        'pages' => ['pages.'],
        'menu' => ['menu.'],
        'settings' => ['settings.', 'flags.', 'consent.'],
        'media' => ['media.'],
        'events' => ['events.'],
        'announcements' => ['announcements.'],
        'people' => ['awards.', 'team.'],
        'requests' => ['application.', 'applications.bulk_status_changed', 'note.', 'interview.', 'meeting.', 'feedback.', 'careers.'],
        'redirects' => ['redirects.'],
        'shaltoor' => ['shaltoor.'],
        'facts' => ['facts.'],
        'access' => ['auth.', 'application.first_viewed', 'identity.revealed', 'attachments.', 'applications.exported'],
    ];

    /** Looking is not changing: these areas are listed only when chosen. */
    private const NOT_CHANGES = ['access'];

    /** The item filter (?item=type:id) and the version screens: short name => model. */
    public const TYPES = [
        'page' => Page::class,
        'branch' => Branch::class,
        'product' => Product::class,
        'category' => MenuCategory::class,
        'event' => Experience::class,
        'announcement' => Experience::class,
        'media' => Media::class,
        'award' => Award::class,
        'team' => TeamMember::class,
        'text' => SiteText::class,
        'setting' => Setting::class,
        'redirect' => Redirect::class,
        'contact' => ContactPoint::class,
        'application' => Application::class,
    ];

    /** Values never shown on this screen: the line says the field changed, not what it holds. */
    private const HIDDEN = ['body', 'email_hash'];

    /** Fields that name the item rather than change it. */
    private const IDENTITY = ['code', 'key', 'locale'];

    /** Fields whose values are words from a fixed list (translated on display). */
    private const WORDS = ['status', 'state', 'publish_status', 'availability', 'kind', 'approval_status'];

    /** Longest value shown in a line; the full text stays in the version it came from. */
    private const MAX_VALUE = 300;

    /**
     * @param  array<string, mixed>  $input
     * @return array{area: string|null, period: string, item: array{type: string, id: int}|null}
     */
    public static function filters(array $input): array
    {
        $area = is_string($input['area'] ?? null) && isset(self::AREAS[$input['area']]) ? $input['area'] : null;
        $period = is_string($input['period'] ?? null) && array_key_exists($input['period'], self::PERIODS) ? $input['period'] : 'all';
        $item = null;
        if (is_string($input['item'] ?? null) && preg_match('/^([a-z]+):(\d{1,10})$/', $input['item'], $m) === 1 && isset(self::TYPES[$m[1]])) {
            $item = ['type' => $m[1], 'id' => (int) $m[2]];
        }

        return ['area' => $area, 'period' => $period, 'item' => $item];
    }

    /**
     * @param  array{area: string|null, period: string, item: array{type: string, id: int}|null}  $filters
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function page(array $filters, int $page, ?CarbonImmutable $now = null): LengthAwarePaginator
    {
        $query = AuditLog::query()->with(['user', 'subject']);
        if ($filters['area'] !== null) {
            $query->where(fn (Builder $q) => $this->whereArea($q, (string) $filters['area']));
        } else {
            foreach (self::NOT_CHANGES as $area) {
                $query->whereNot(fn (Builder $q) => $this->whereArea($q, $area));
            }
        }
        $days = self::PERIODS[$filters['period']];
        if ($days !== null) {
            $now ??= CarbonImmutable::now('Asia/Amman');
            $query->where('created_at', '>=', $now->setTimezone('Asia/Amman')->startOfDay()->subDays($days)->utc());
        }
        if ($filters['item'] !== null) {
            $query->where(fn (Builder $q) => $this->whereItem($q, $filters['item']['type'], $filters['item']['id']));
        }

        /** @var LengthAwarePaginator<int, AuditLog> $result */
        $result = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page));

        return $result;
    }

    /** The area an entry belongs to ('other' when no area claims it). */
    public static function area(AuditLog $log): string
    {
        $action = $log->action;
        if (self::matches($action, self::AREAS['access'])) {
            return 'access';
        }
        if (str_starts_with($action, 'settings.') && $log->subject instanceof Setting && str_starts_with($log->subject->key, 'shaltoor.')) {
            return 'shaltoor';
        }
        foreach (self::AREAS as $area => $actions) {
            if (self::matches($action, $actions)) {
                return $area;
            }
        }

        return 'other';
    }

    /**
     * One entry in plain words.
     *
     * @param  array<string, int>  $textRows  "key.locale" => site_texts id (for the per-text version links)
     * @return array{id: int, area: string, action: string, item: string|null, who: string, when: string, reason: string|null,
     *     lines: list<array{field: string, from: string|null, to: string|null, versions: string|null}>, versions: string|null, restored: int|null}
     */
    public function describe(AuditLog $log, string $locale, array $textRows = []): array
    {
        $meta = AuditLogger::mask($log->meta ?? []);
        $reason = is_string($meta['reason'] ?? null) && trim($meta['reason']) !== '' ? trim($meta['reason']) : null;
        $lines = [];
        foreach ($this->lines($log, $locale) as $line) {
            $row = $textRows[$line['key']] ?? null;
            $lines[] = ['field' => $line['field'], 'from' => $line['from'], 'to' => $line['to'],
                'versions' => $row !== null ? route('dashboard.history.versions', ['text', $row]) : null];
        }
        $alias = $log->subject instanceof Model ? self::alias($log->subject) : null;
        $versioned = $alias !== null && $log->subject instanceof Model && ContentVersion::query()
            ->where('versionable_type', $log->subject->getMorphClass())->where('versionable_id', $log->subject->getKey())->exists();

        return [
            'id' => $log->id,
            'area' => self::area($log),
            'action' => self::actionLabel($log->action),
            'item' => $this->itemName($log->subject, $log, $locale),
            'who' => self::who($log),
            'when' => $log->created_at->setTimezone('Asia/Amman')->format('Y-m-d H:i'),
            'reason' => $reason === null ? null : mb_strimwidth($reason, 0, self::MAX_VALUE, '…'),
            'lines' => $lines,
            'versions' => $versioned && $alias !== null && $log->subject instanceof Model ? route('dashboard.history.versions', [$alias, $log->subject->getKey()]) : null,
            'restored' => is_numeric($meta['from_version'] ?? null) ? (int) $meta['from_version'] : null,
        ];
    }

    /**
     * site_texts ids for the texts named in these entries (one query for the page).
     *
     * @param  iterable<AuditLog>  $logs
     * @return array<string, int>
     */
    public static function textRows(iterable $logs): array
    {
        $wanted = [];
        foreach ($logs as $log) {
            if (str_starts_with($log->action, 'texts.')) {
                foreach (array_keys(self::flat($log->changes['after'] ?? null) + self::flat($log->changes['before'] ?? null)) as $field) {
                    if (preg_match('/^(.+)\.(ar|en)$/', $field, $m) === 1) {
                        $wanted[$m[1]][] = $m[2];
                    }
                }
            }
        }
        if ($wanted === []) {
            return [];
        }
        $rows = [];
        foreach (SiteText::query()->whereIn('key', array_keys($wanted))->get(['id', 'key', 'locale']) as $row) {
            $rows[$row->key.'.'.$row->locale] = $row->id;
        }

        return $rows;
    }

    /**
     * Who made the change: the person, a visitor's form, or the system (with the job's name).
     */
    public static function who(AuditLog $log): string
    {
        if ($log->user instanceof User) {
            return $log->user->name;
        }
        $meta = $log->meta ?? [];
        $type = $meta[AuditLogger::ACTOR_TYPE] ?? null;
        if ($type === 'applicant') {
            return (string) __('dashboard.history.actors.applicant');
        }
        if ($type === AuditLogger::SYSTEM && is_string($meta['job'] ?? null)) {
            return (string) __('dashboard.history.actors.job', ['job' => $meta['job']]);
        }

        return (string) __('dashboard.history.actors.system');
    }

    public static function actionLabel(string $action): string
    {
        $key = 'dashboard.history.actions.'.$action;

        return Lang::has($key) ? (string) __($key) : $action;
    }

    /** Short name of the model for links (?item=type:id), or null when it has no screen here. */
    public static function alias(Model $model): ?string
    {
        if ($model instanceof Experience) {
            return $model->type === 'event' ? 'event' : 'announcement';
        }
        $alias = array_search($model::class, self::TYPES, true);

        return is_string($alias) ? $alias : null;
    }

    /** The item an entry or a version is about, by name. */
    public function itemName(?Model $subject, ?AuditLog $log, string $locale): ?string
    {
        $ar = $locale === 'ar';
        $pick = fn (?string $arabic, ?string $english): ?string => ($ar ? ($arabic ?? $english) : ($english ?? $arabic));
        $meta = $log->meta ?? [];
        $reference = is_string($meta['reference'] ?? null) ? $meta['reference'] : (is_string($meta['target_label'] ?? null) ? $meta['target_label'] : null);

        $name = match (true) {
            $subject instanceof Page => (string) __('dashboard.pages.keys.'.$subject->key),
            $subject instanceof Branch => $pick($subject->name_ar, $subject->name_en) ?? $subject->code,
            $subject instanceof HoursException => ($subject->branch !== null ? ($pick($subject->branch->name_ar, $subject->branch->name_en) ?? $subject->branch->code).' · ' : '')
                .$subject->starts_on->format('Y-m-d').($subject->ends_on->equalTo($subject->starts_on) ? '' : ' – '.$subject->ends_on->format('Y-m-d')),
            $subject instanceof Product => ($pick($subject->display_name_ar, $subject->display_name_en) ?? $subject->normalized_name_en ?? '').' ('.$subject->code.')',
            $subject instanceof MenuCategory => $pick($subject->name_ar, $subject->name_en) ?? $subject->source_name,
            $subject instanceof Experience => $pick($subject->title_ar, $subject->title_en) ?? '#'.$subject->id,
            $subject instanceof Media => $subject->code,
            $subject instanceof Award => $pick($subject->title_ar, $subject->title_en) ?? '#'.$subject->id,
            $subject instanceof TeamMember => $pick($subject->display_name_ar, $subject->display_name_en) ?? '#'.$subject->id,
            $subject instanceof SiteText => self::fieldLabel($subject->key.'.'.$subject->locale),
            $subject instanceof Setting => self::fieldLabel($subject->key),
            $subject instanceof FeatureFlag => self::fieldLabel($subject->key),
            $subject instanceof Redirect => $subject->source_path,
            $subject instanceof ContactPoint => $pick($subject->label_ar, $subject->label_en) ?? $subject->kind->value,
            $subject instanceof SocialLink => (string) __('dashboard.contacts.platforms.'.$subject->platform),
            $subject instanceof Fact => $pick($subject->label_ar, $subject->label_en) ?? $subject->key,
            $subject instanceof Application => $subject->reference_number,
            $subject instanceof ConsentVersion => (string) __('dashboard.history.consents', ['scope' => $subject->scope, 'version' => $subject->version]),
            $subject instanceof JordanCity => $pick($subject->name_ar, $subject->name_en),
            $subject instanceof InterviewLocation => $pick($subject->name_ar, $subject->name_en),
            $subject instanceof User => $subject->name,
            default => null,
        };
        if ($name === null && $log !== null) {
            if ($reference !== null) {
                $name = $reference;
            } elseif ($log->subject_type !== null && $log->subject_id !== null) {
                $name = (string) __('dashboard.history.removed_item', ['id' => $log->subject_id]);
            }
        }

        return $name === null || trim($name) === '' ? null : $name;
    }

    /**
     * The fields an entry changed: from → to where it kept both, the value alone where it kept only the result.
     *
     * @return list<array{key: string, field: string, from: string|null, to: string|null}>
     */
    public function lines(AuditLog $log, string $locale): array
    {
        $changes = AuditLogger::mask($log->changes ?? []);
        $hasBefore = array_key_exists('before', $changes) && $changes['before'] !== null;
        $before = self::flat($changes['before'] ?? null);
        $after = self::flat($changes['after'] ?? null);
        $lines = [];
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
            $field = (string) $field;
            if (in_array($field, self::IDENTITY, true)) {
                continue;
            }
            $from = $before[$field] ?? null;
            $to = $after[$field] ?? null;
            // Unchanged fields are left out — but two masked values may differ, so a masked field always shows.
            if ($hasBefore && array_key_exists($field, $before) && array_key_exists($field, $after) && $from === $to && $from !== '[MASKED]') {
                continue;
            }
            $lines[] = [
                'key' => $field,
                'field' => self::fieldLabel($field),
                'from' => $hasBefore ? self::value($from, $field, $locale) : null,
                'to' => array_key_exists($field, $after) || $hasBefore ? self::value($to, $field, $locale) : null,
            ];
        }

        return $lines;
    }

    /** A field in the Owner's words: the shared list, a page section by its number, a site text by its label, else the stored name. */
    public static function fieldLabel(string $field): string
    {
        $key = 'dashboard.history.fields.'.str_replace('.', '_', $field);
        if (Lang::has($key)) {
            return (string) __($key);
        }
        if (preg_match('/^sections\.(\d+)\.([a-z_]+)$/', $field, $m) === 1) {
            return (string) __('dashboard.history.section_field', ['n' => $m[1], 'field' => self::fieldLabel($m[2])]);
        }
        if (preg_match('/^(.+)\.(ar|en)$/', $field, $m) === 1) {
            $text = 'dashboard.texts.labels.'.str_replace('.', '_', $m[1]);
            if (Lang::has($text)) {
                return (string) __('dashboard.history.in_language.'.$m[2], ['field' => __($text)]);
            }
        }

        return $field;
    }

    /** A value as the Owner reads it (long text cut at $max characters). */
    public static function value(mixed $value, string $field, string $locale, int $max = self::MAX_VALUE): string
    {
        $last = (string) preg_replace('/^.*\./', '', $field);
        if (in_array($last, self::HIDDEN, true) && $value !== null) {
            return (string) __('dashboard.history.values.hidden');
        }
        if ($value === '[MASKED]') {
            return (string) __('dashboard.history.values.masked');
        }
        if ($value === null || $value === '' || $value === []) {
            return (string) __('dashboard.history.values.empty');
        }
        if (is_bool($value)) {
            return (string) __('dashboard.history.values.'.($value ? 'yes' : 'no'));
        }
        if (str_ends_with($last, 'price_fils') && is_numeric($value)) {
            return MenuManager::dinars((int) $value).' '.__('ui.currency.JOD.symbol');
        }
        if (is_array($value)) {
            if ($last === 'hours' && array_is_list($value)) {
                return self::week($value, $locale);
            }
            $parts = [];
            foreach ($value as $key => $part) {
                $text = is_scalar($part) || $part === null ? self::value($part, (string) $key, $locale) : (string) json_encode($part, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $parts[] = is_string($key) ? $key.': '.$text : $text;
            }

            return mb_strimwidth(implode(' · ', $parts), 0, $max, '…');
        }
        $text = (string) (is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if (in_array($last, self::WORDS, true) && Lang::has('dashboard.history.values.'.$text)) {
            return (string) __('dashboard.history.values.'.$text);
        }
        // A page section's kind in the editor's words; an image by its code.
        if (preg_match('/^sections\.\d+\.type$/', $field) === 1 && Lang::has('dashboard.pages.types.'.$text)) {
            return (string) __('dashboard.pages.types.'.$text);
        }
        if ($last === 'media_id' && is_numeric($value)) {
            $code = Media::query()->whereKey((int) $value)->value('code');

            return is_string($code) ? $code : '#'.$text;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:?\d{2})$/', $text) === 1) {
            // A date column comes back as midnight UTC: the date alone; a moment is shown in Amman time.
            return preg_match('/T00:00:00(\.0+)?(Z|\+00:?00)$/', $text) === 1 ? substr($text, 0, 10) : CarbonImmutable::parse($text)->setTimezone('Asia/Amman')->format('Y-m-d H:i');
        }

        return mb_strimwidth($text, 0, $max, '…');
    }

    /**
     * Nested before/after maps as one level of "a.b" fields; a list of maps (texts saved together) becomes one map;
     * a list of values (a week of hours, suggestions) stays one value.
     *
     * @return array<string, mixed>
     */
    public static function flat(mixed $data, string $prefix = ''): array
    {
        if (! is_array($data)) {
            return [];
        }
        if (array_is_list($data) && $data !== [] && collect($data)->every(fn (mixed $row): bool => is_array($row) && ! array_is_list($row))) {
            $merged = [];
            foreach ($data as $row) {
                $merged += self::flat($row, $prefix);
            }

            return $merged;
        }
        $out = [];
        foreach ($data as $key => $value) {
            $field = $prefix.$key;
            if (is_array($value) && $value !== [] && ! array_is_list($value)) {
                $out += self::flat($value, $field.'.');
            } else {
                $out[$field] = $value;
            }
        }

        return $out;
    }

    /** @param list<mixed> $rows [[weekday, opens, closes], …] */
    private static function week(array $rows, string $locale): string
    {
        $parts = [];
        foreach ($rows as $row) {
            if (is_array($row) && count($row) === 3 && is_numeric($row[0]) && is_string($row[1]) && is_string($row[2])) {
                $parts[] = LocalTime::weekday((int) $row[0], $locale).' '.LocalTime::clock(substr($row[1], 0, 5), $locale).' – '.LocalTime::clock(substr($row[2], 0, 5), $locale);
            }
        }

        return $parts === [] ? (string) __('dashboard.history.values.empty') : implode(' · ', $parts);
    }

    /** The subject_type stored for a model class. */
    private static function morph(string $class): string
    {
        return self::model($class)->getMorphClass();
    }

    private static function model(string $class): Model
    {
        $model = new $class;
        if (! $model instanceof Model) {
            throw new LogicException("[{$class}] is not a model.");
        }

        return $model;
    }

    /** @param list<string> $actions */
    private static function matches(string $action, array $actions): bool
    {
        foreach ($actions as $pattern) {
            if (str_ends_with($pattern, '.') ? str_starts_with($action, $pattern) : $action === $pattern) {
                return true;
            }
        }

        return false;
    }

    /** @param Builder<AuditLog> $query */
    private function whereActions(Builder $query, string $area): void
    {
        $query->where(function (Builder $q) use ($area): void {
            foreach (self::AREAS[$area] as $pattern) {
                str_ends_with($pattern, '.') ? $q->orWhere('action', 'like', $pattern.'%') : $q->orWhere('action', $pattern);
            }
        });
    }

    /** @param Builder<AuditLog> $query */
    private function whereShaltoorSetting(Builder $query): void
    {
        $query->where('action', 'like', 'settings.%')->where('subject_type', self::morph(Setting::class))
            ->whereIn('subject_id', Setting::query()->select('id')->where('key', 'like', 'shaltoor.%'));
    }

    /** @param Builder<AuditLog> $query */
    private function whereArea(Builder $query, string $area): void
    {
        match ($area) {
            'requests' => $query->where(fn (Builder $q) => $this->whereActions($q, 'requests'))->whereNot(fn (Builder $q) => $this->whereActions($q, 'access')),
            'settings' => $query->where(fn (Builder $q) => $this->whereActions($q, 'settings'))->whereNot(fn (Builder $q) => $this->whereShaltoorSetting($q)),
            'shaltoor' => $query->where(fn (Builder $q) => $this->whereActions($q, 'shaltoor'))->orWhere(fn (Builder $q) => $this->whereShaltoorSetting($q)),
            default => $this->whereActions($query, $area),
        };
    }

    /** @param Builder<AuditLog> $query */
    private function whereItem(Builder $query, string $type, int $id): void
    {
        $query->where(fn (Builder $q) => $q->where('subject_type', self::morph(self::TYPES[$type]))->where('subject_id', $id));
        /** Entries about the rows of $model whose $column is $value. */
        $related = fn (string $model, string $column, mixed $value): \Closure => fn (Builder $q) => $q->where('subject_type', self::morph($model))
            ->whereIn('subject_id', DB::table(self::model($model)->getTable())->select('id')->where($column, $value));

        if ($type === 'branch') {
            // A branch's history: its details, its hours and exceptions, its own contact numbers, the approvals of its facts.
            $branch = Branch::query()->find($id);
            $query->orWhere($related(HoursException::class, 'branch_id', $id))->orWhere($related(ContactPoint::class, 'branch_id', $id));
            if ($branch !== null) {
                $query->orWhere(fn (Builder $q) => $q->where('subject_type', self::morph(Fact::class))->whereIn('subject_id', Fact::query()->select('id')
                    ->where(fn (Builder $f) => $f->where('key', 'like', 'branch.'.$branch->code.'.%')->orWhere('key', 'like', 'hours.'.$branch->code.'.%'))));
            }
        } elseif ($type === 'application') {
            foreach ([ApplicationNote::class, ApplicationInterview::class, ApplicationMeeting::class, ApplicationAttachment::class] as $model) {
                $query->orWhere($related($model, 'application_id', $id));
            }
        } elseif ($type === 'text') {
            // Texts saved together are one entry without a subject; the text's own key names it there.
            $text = SiteText::query()->find($id);
            if ($text !== null) {
                $query->orWhere(fn (Builder $q) => $q->where('action', 'texts.saved')->where('changes', 'like', '%"'.$text->key.'.'.$text->locale.'"%'));
            }
        }
    }
}
