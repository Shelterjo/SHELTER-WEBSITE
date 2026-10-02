<?php

use App\Models\Branch;
use App\Services\Media\MediaImage;
use App\Services\Site\BranchSummary;
use App\Services\Site\ContactAction;
use App\Services\Site\StatusSegment;
use App\Services\Site\StatusTimeline;
use Carbon\CarbonImmutable;

/*
| SHELTER design-system catalog — the stories Storybook shows for every x-ui component (DS-024).
| `php artisan ds:export` renders each story through the real Blade component in Arabic (RTL) and English (LTR) and
| writes design-system/stories/generated/{component}.json; design-system/stories/{component}.stories.ts exports one
| story per entry (same PascalCase name — the export checks it).
|
| Story keys: story (PascalCase) · state (default|hover|focus|active|disabled|loading|error|empty|open) ·
|   props (component props/attributes) · slot (Blade source) · slots (named slots) · blade (full Blade source, for
|   compositions). Any value may be ['@ar' => …, '@en' => …] (see $t).
|
| CONTENT RULES (CLAUDE.md, M38): no invented business data.
| - Product cards use REAL approved rows of docs/phase-01-discovery/menu/menu-inventory-v1.0.csv (MV-2026-10-01).
|   An Arabic name is shown only when display_name_ar is approved; otherwise the English name is primary on the
|   Arabic page and there is no secondary name (Menu IA spec §6 rule 2, CF-03) — e.g. PRD-00014 CAPPUCCINO, whose
|   Arabic source name is still pending owner review (D-091).
| - Availability and badges are UNKNOWN in Master Data, so those states use a clearly labelled sample item.
| - Events, metrics, tables and form texts are labelled samples ("تجريبي" / "Sample"). No images: placeholders only.
*/

$t = fn (mixed $ar, mixed $en): array => ['@ar' => $ar, '@en' => $en];
$both = fn (callable $make): array => ['@ar' => $make('ar'), '@en' => $make('en')];

// [display_name_en, display_name_ar (null = not approved yet), price_fils] — copied from the menu inventory CSV.
$menu = [
    'PRD-00001' => ['TURKISH COFFEE SINGLE', 'قهوة تركية سينجل', 1500],
    'PRD-00002' => ['TURKISH COFFEE DOUBLE', 'قهوة تركية دوبل', 2500],
    'PRD-00014' => ['CAPPUCCINO', null, 2500],
    'PRD-00054' => ['ICED HAZELNUT LATTE (SUGAR-FREE)', 'آيس هازلنت لاتيه (بدون سكر)', 3500],
    'PRD-00126' => ['SMOOTHIE STRAWBERRY + MANGO + PASSION', 'سموذي فراولة + مانجو + باشن', 3500],
    'PRD-00171' => ['TIRAMISU', 'تيراميسو', 3000],
];

// Blade tag of a product card for one locale, following the bilingual name rules of Menu IA spec §6.
$product = function (string $locale, string $id, string $extra = '', string $inner = '') use ($menu): string {
    [$en, $ar, $fils] = $menu[$id];
    if ($locale === 'ar') {
        $attributes = $ar !== null
            ? 'name="'.e($ar).'" secondary="'.e($en).'" secondary-lang="en"'
            : 'name="'.e($en).'" name-lang="en"';
    } else {
        $attributes = 'name="'.e($en).'"'.($ar !== null ? ' secondary="'.e($ar).'" secondary-lang="ar"' : '');
    }
    $tag = '<x-ui.product-card id="'.strtolower($id).'" '.$attributes.' :price="'.$fils.'"'.($extra !== '' ? ' '.$extra : '');

    return $inner === '' ? $tag.' />' : $tag.'>'.$inner.'</x-ui.product-card>';
};
$sampleItem = $t('صنف تجريبي (مثال حالة)', 'SAMPLE ITEM (STATE DEMO)');

// Branch selector options: labels approved in F-18 (كل الفروع / DRIVE / HOUSE).
$branchOptions = $both(fn (string $l): string => "['all' => '".($l === 'ar' ? 'كل الفروع' : 'All branches')."', 'drive' => ['label' => 'DRIVE', 'lang' => 'en'], 'house' => ['label' => 'HOUSE', 'lang' => 'en']]");
$segmented = fn (string $selected, string $state = 'default'): array => ['story' => '', 'state' => $state, 'blade' => $both(fn (string $l): string => '<x-ui.segmented label="'.($l === 'ar' ? 'الفرع' : 'Branch').'" :options="'.$branchOptions['@'.$l].'" selected="'.$selected.'" />')];

// Category bar: the approved display order (F-06) with the official English category names; Arabic names are still
// pending (P-01) except SWEETS / حلويات (F-07), so the Arabic page shows the English names with lang="en".
$categoryItems = function (string $locale): string {
    $items = [
        ['speciality-coffee', 'SPECIALITY COFFEE', null], ['hot-drinks', 'HOT DRINKS', null], ['cold-drinks', 'COLD DRINKS', null],
        ['frappe', 'FRAPPE', null], ['milkshake', 'MILKSHAKE', null], ['smoothies', 'SMOOTHIES', null],
        ['fizzy-drinks', 'FIZZY DRINKS', null], ['tea', 'TEA', null], ['sweets', 'SWEETS', 'حلويات'],
    ];
    $out = [];
    foreach ($items as $i => [$slug, $en, $ar]) {
        $label = $locale === 'ar' && $ar !== null ? $ar : $en;
        $lang = $locale === 'ar' && $ar === null ? ", 'lang' => 'en'" : '';
        $out[] = "['href' => '#{$slug}', 'label' => '".$label."'{$lang}".($i === 0 ? ", 'current' => true" : '').']';
    }

    return '['.implode(', ', $out).']';
};
$categoryNav = fn (string $extra = '', string $slots = ''): array => $both(fn (string $l): string => '<x-ui.category-nav label="'.($l === 'ar' ? 'فئات المنيو' : 'Menu categories').'" :items="'.$categoryItems($l).'" '.$extra.'>'.$slots.'</x-ui.category-nav>');
$categoryActions = fn (string $l): string => '<x-slot:start><x-ui.button variant="ghost" icon="search" icon-only label="'.($l === 'ar' ? 'بحث' : 'Search').'" /></x-slot:start><x-slot:end><x-ui.button variant="ghost" icon="menu" icon-only label="'.($l === 'ar' ? 'كل الفئات' : 'All categories').'" /></x-slot:end>';

$field = fn (string $control, array $field = []): array => $both(function (string $locale) use ($control, $field): string {
    $label = $locale === 'ar' ? 'البريد الإلكتروني' : 'Email';
    $attributes = 'label="'.$label.'" for="ds-email"';
    foreach ($field as $key => $value) {
        $attributes .= ' '.$key.'="'.e(is_array($value) ? $value['@'.$locale] : $value).'"';
    }

    return '<x-ui.field '.$attributes.'>'.$control.'</x-ui.field>';
});
$hint = $t('نص مساعد تجريبي', 'Sample hint text');
$emailError = $t('أدخل بريدًا إلكترونيًا صحيحًا، مثل name@example.com', 'Enter a valid email address, like name@example.com');

$dialogBody = fn (string $locale): string => $product($locale, 'PRD-00001');

// Site chrome and branch samples (PHASE 2). Branch names, hours and the public number are the APPROVED values
// (D-020, D-057); the open/closed states are labelled samples (the live state is computed per request).
$sampleNow = CarbonImmutable::parse('2026-10-01 12:00', 'Asia/Amman');
$status = fn (string $state, string $l): StatusTimeline => new StatusTimeline([
    new StatusSegment(
        $state,
        match ($state) {
            'open' => $l === 'ar' ? 'مفتوح الآن (مثال)' : 'Open now (sample)',
            'closing' => $l === 'ar' ? 'يغلق قريبًا (مثال)' : 'Closing soon (sample)',
            default => $l === 'ar' ? 'مغلق الآن (مثال)' : 'Closed now (sample)',
        },
        null,
    ),
], $sampleNow, $l);
$week = fn (string $l): array => array_map(
    fn (array $d): array => [
        'day' => $l === 'ar' ? $d[0] : $d[1],
        'today' => $d[2],
        'intervals' => [['opens' => $l === 'ar' ? $d[3] : $d[4], 'closes' => $l === 'ar' ? '2:00 ص' : '2:00 AM', 'overnight' => true]],
    ],
    [
        ['السبت', 'Saturday', false, '7:00 ص', '7:00 AM'], ['الأحد', 'Sunday', false, '7:00 ص', '7:00 AM'],
        ['الاثنين', 'Monday', false, '7:00 ص', '7:00 AM'], ['الثلاثاء', 'Tuesday', false, '7:00 ص', '7:00 AM'],
        ['الأربعاء', 'Wednesday', false, '7:00 ص', '7:00 AM'], ['الخميس', 'Thursday', true, '7:00 ص', '7:00 AM'],
        ['الجمعة', 'Friday', false, '8:00 ص', '8:00 AM'],
    ],
);
$phone = new ContactAction('tel:+962799009436', '0799009436');
$whatsapp = new ContactAction('https://wa.me/962799009436', 'WhatsApp');
$branchCard = fn (string $l, string $state) => new BranchSummary(
    new Branch,
    $l === 'ar' ? 'شلتر كوفي درايف' : 'SHELTER COFFEE DRIVE',
    $l,
    $l === 'ar' ? 'SHELTER COFFEE DRIVE' : 'شلتر كوفي درايف',
    $l === 'ar' ? 'en' : 'ar',
    '#branch',
    'irbid',
    $week($l),
    [['opens' => $l === 'ar' ? '7:00 ص' : '7:00 AM', 'closes' => $l === 'ar' ? '2:00 ص' : '2:00 AM']],
    $status($state, $l),
    $phone,
    $whatsapp,
);
$nav = fn (string $l): array => [
    ['label' => $l === 'ar' ? 'المنيو' : 'Menu', 'href' => '#menu', 'current' => null],
    ['label' => $l === 'ar' ? 'الفروع' : 'Locations', 'href' => '#locations', 'current' => 'page'],
];
$languages = fn (string $l): array => [
    ['locale' => 'ar', 'label' => 'العربية', 'href' => '#ar', 'current' => $l === 'ar'],
    ['locale' => 'en', 'label' => 'English', 'href' => '#en', 'current' => $l === 'en'],
];

return [
    // Components whose stories live under another entry.
    'covered' => [
        'tab-panel' => 'tabs',
        'dialog' => 'modal',
    ],

    'components' => [
        // ───────────────────────────── Foundations ─────────────────────────────
        'icon' => ['stories' => [
            ['story' => 'Library', 'blade' => '<ul class="ui-cluster" role="list">'.implode('', array_map(
                fn (string $name): string => '<li><x-ui.icon name="'.$name.'" label="'.$name.'" /></li>',
                ['arrow-right', 'ban', 'calendar', 'check', 'chevron-down', 'chevron-left', 'chevron-right', 'circle-alert', 'circle-check', 'circle-x', 'clock', 'hand', 'house', 'image', 'inbox', 'info', 'loader-circle', 'log-out', 'map-pin', 'menu', 'message-circle', 'phone', 'refresh-cw-off', 'rotate-cw', 'trending-down', 'trending-up', 'triangle-alert', 'x'],
            )).'</ul>'],
            ['story' => 'Sizes', 'blade' => '<p class="ui-cluster"><x-ui.icon name="house" size="sm" label="16" /><x-ui.icon name="house" label="20" /><x-ui.icon name="house" size="lg" label="24" /></p>'],
            ['story' => 'Directional', 'blade' => $t(
                '<p class="ui-cluster"><x-ui.icon name="chevron-left" label="السابق" /><x-ui.icon name="chevron-right" label="التالي" /><x-ui.icon name="x" label="إغلاق — لا ينعكس" /></p>',
                '<p class="ui-cluster"><x-ui.icon name="chevron-left" label="Previous" /><x-ui.icon name="chevron-right" label="Next" /><x-ui.icon name="x" label="Close — never mirrors" /></p>',
            )],
        ]],

        // ───────────────────────────── Actions ─────────────────────────────
        'button' => ['stories' => [
            ['story' => 'Primary', 'props' => ['variant' => 'primary'], 'slot' => $t('حفظ', 'Save')],
            ['story' => 'Secondary', 'props' => ['variant' => 'secondary'], 'slot' => $t('معاينة', 'Preview')],
            ['story' => 'Outline', 'props' => ['variant' => 'outline'], 'slot' => $t('تصدير', 'Export')],
            ['story' => 'Ghost', 'props' => ['variant' => 'ghost'], 'slot' => $t('إلغاء', 'Cancel')],
            ['story' => 'Danger', 'props' => ['variant' => 'danger'], 'slot' => $t('أرشفة', 'Archive')],
            ['story' => 'Link', 'props' => ['variant' => 'link', 'href' => '#'], 'slot' => $t('عرض الكل', 'View all')],
            ['story' => 'Sizes', 'blade' => $t(
                '<p class="ui-cluster"><x-ui.button size="sm">صغير</x-ui.button><x-ui.button>متوسط</x-ui.button><x-ui.button size="lg">كبير</x-ui.button></p>',
                '<p class="ui-cluster"><x-ui.button size="sm">Small</x-ui.button><x-ui.button>Medium</x-ui.button><x-ui.button size="lg">Large</x-ui.button></p>',
            )],
            ['story' => 'WithIcon', 'props' => ['variant' => 'secondary', 'iconEnd' => 'chevron-right'], 'slot' => $t('التالي', 'Next')],
            ['story' => 'IconOnly', 'props' => ['variant' => 'ghost', 'icon' => 'x', 'iconOnly' => true, 'label' => $t('إغلاق', 'Close')]],
            ['story' => 'Hover', 'state' => 'hover', 'props' => ['variant' => 'primary'], 'slot' => $t('حفظ', 'Save')],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['variant' => 'primary'], 'slot' => $t('حفظ', 'Save')],
            ['story' => 'Active', 'state' => 'active', 'props' => ['variant' => 'primary'], 'slot' => $t('حفظ', 'Save')],
            ['story' => 'Disabled', 'state' => 'disabled', 'props' => ['variant' => 'primary', 'disabled' => true], 'slot' => $t('حفظ', 'Save')],
            ['story' => 'Loading', 'state' => 'loading', 'props' => ['variant' => 'primary', 'loading' => true], 'slot' => $t('جارٍ الحفظ', 'Saving')],
        ]],

        'chip' => ['stories' => [
            ['story' => 'Default', 'slot' => $t('خيار تجريبي', 'Sample option')],
            ['story' => 'Pressed', 'props' => ['pressed' => true], 'slot' => $t('خيار تجريبي', 'Sample option')],
            ['story' => 'CurrentLink', 'props' => ['href' => '#', 'current' => true], 'slot' => $t('فئة تجريبية', 'Sample category')],
            ['story' => 'Hover', 'state' => 'hover', 'slot' => $t('خيار تجريبي', 'Sample option')],
            ['story' => 'Focus', 'state' => 'focus', 'slot' => $t('خيار تجريبي', 'Sample option')],
            ['story' => 'Active', 'state' => 'active', 'slot' => $t('خيار تجريبي', 'Sample option')],
            ['story' => 'Disabled', 'state' => 'disabled', 'props' => ['disabled' => true], 'slot' => $t('خيار تجريبي', 'Sample option')],
        ]],

        'segmented' => ['stories' => [
            ['story' => 'Default'] + $segmented('all'),
            ['story' => 'SecondSelected'] + $segmented('drive'),
            ['story' => 'ThirdSelected'] + $segmented('house'),
            ['story' => 'Hover', 'state' => 'hover'] + $segmented('all', 'hover'),
            ['story' => 'Focus', 'state' => 'focus'] + $segmented('all', 'focus'),
        ]],

        'category-nav' => ['stories' => [
            ['story' => 'Default', 'blade' => $categoryNav()],
            ['story' => 'WithActions', 'blade' => $both(fn (string $l): string => $categoryNav('', $categoryActions($l))['@'.$l])],
            ['story' => 'Sidebar', 'blade' => $both(fn (string $l): string => $categoryNav('sidebar', $categoryActions($l))['@'.$l])],
            ['story' => 'Focus', 'state' => 'focus', 'blade' => $categoryNav()],
        ]],

        // ───────────────────────────── Forms ─────────────────────────────
        'field' => ['stories' => [
            ['story' => 'Default', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" autocomplete="email" />')],
            ['story' => 'WithHint', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" />', ['hint' => $hint])],
            ['story' => 'Required', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" />', ['required' => 'required'])],
            ['story' => 'Optional', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" />', ['optional' => 'optional'])],
            ['story' => 'Error', 'state' => 'error', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" value="name@" />', ['hint' => $hint, 'error' => $emailError, 'required' => 'required'])],
        ]],

        'input' => ['stories' => [
            ['story' => 'Default', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" />')],
            ['story' => 'Filled', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" value="name@example.com" />')],
            ['story' => 'Hover', 'state' => 'hover', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" />')],
            ['story' => 'Focus', 'state' => 'focus', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" />')],
            ['story' => 'Disabled', 'state' => 'disabled', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" value="name@example.com" disabled />')],
            ['story' => 'Error', 'state' => 'error', 'blade' => $field('<x-ui.input type="email" name="email" dir="ltr" value="name@" />', ['error' => $emailError])],
        ]],

        'search-field' => ['stories' => [
            ['story' => 'Default', 'props' => ['id' => 'ds-search', 'label' => $t('بحث', 'Search'), 'placeholder' => $t('ابحث في المنيو', 'Search the menu')]],
            ['story' => 'Hover', 'state' => 'hover', 'props' => ['id' => 'ds-search', 'label' => $t('بحث', 'Search'), 'placeholder' => $t('ابحث في المنيو', 'Search the menu')]],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['id' => 'ds-search', 'label' => $t('بحث', 'Search'), 'placeholder' => $t('ابحث في المنيو', 'Search the menu')]],
        ]],

        'textarea' => ['stories' => [
            ['story' => 'Default', 'blade' => $both(fn (string $l): string => '<x-ui.field label="'.($l === 'ar' ? 'ملاحظات' : 'Notes').'" for="ds-notes"><x-ui.textarea name="notes" /></x-ui.field>')],
            ['story' => 'Error', 'state' => 'error', 'blade' => $both(fn (string $l): string => '<x-ui.field label="'.($l === 'ar' ? 'ملاحظات' : 'Notes').'" for="ds-notes" error="'.($l === 'ar' ? 'هذا الحقل مطلوب' : 'This field is required').'" required><x-ui.textarea name="notes" /></x-ui.field>')],
            ['story' => 'Disabled', 'state' => 'disabled', 'blade' => $both(fn (string $l): string => '<x-ui.field label="'.($l === 'ar' ? 'ملاحظات' : 'Notes').'" for="ds-notes"><x-ui.textarea name="notes" disabled /></x-ui.field>')],
        ]],

        'select' => ['stories' => [
            ['story' => 'Default', 'blade' => $both(fn (string $l): string => '<x-ui.field label="'.($l === 'ar' ? 'اختيار تجريبي' : 'Sample choice').'" for="ds-choice"><x-ui.select name="choice" :options="'.($l === 'ar' ? "['1' => 'الخيار الأول', '2' => 'الخيار الثاني']" : "['1' => 'First option', '2' => 'Second option']").'" selected="2" /></x-ui.field>')],
            ['story' => 'Placeholder', 'state' => 'empty', 'blade' => $both(fn (string $l): string => '<x-ui.field label="'.($l === 'ar' ? 'اختيار تجريبي' : 'Sample choice').'" for="ds-choice"><x-ui.select name="choice" placeholder :options="'.($l === 'ar' ? "['1' => 'الخيار الأول', '2' => 'الخيار الثاني']" : "['1' => 'First option', '2' => 'Second option']").'" /></x-ui.field>')],
            ['story' => 'Error', 'state' => 'error', 'blade' => $both(fn (string $l): string => '<x-ui.field label="'.($l === 'ar' ? 'اختيار تجريبي' : 'Sample choice').'" for="ds-choice" error="'.($l === 'ar' ? 'اختر أحد الخيارات' : 'Choose one of the options').'" required><x-ui.select name="choice" placeholder :options="'.($l === 'ar' ? "['1' => 'الخيار الأول', '2' => 'الخيار الثاني']" : "['1' => 'First option', '2' => 'Second option']").'" /></x-ui.field>')],
            ['story' => 'Disabled', 'state' => 'disabled', 'blade' => $both(fn (string $l): string => '<x-ui.field label="'.($l === 'ar' ? 'اختيار تجريبي' : 'Sample choice').'" for="ds-choice"><x-ui.select name="choice" disabled :options="'.($l === 'ar' ? "['1' => 'الخيار الأول']" : "['1' => 'First option']").'" /></x-ui.field>')],
        ]],

        'checkbox' => ['stories' => [
            ['story' => 'Default', 'props' => ['name' => 'terms', 'id' => 'ds-check', 'label' => $t('خيار تجريبي', 'Sample option')]],
            ['story' => 'Checked', 'props' => ['name' => 'terms', 'id' => 'ds-check', 'checked' => true, 'label' => $t('خيار تجريبي', 'Sample option')]],
            ['story' => 'WithHint', 'props' => ['name' => 'terms', 'id' => 'ds-check', 'label' => $t('خيار تجريبي', 'Sample option'), 'hint' => $hint]],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['name' => 'terms', 'id' => 'ds-check', 'label' => $t('خيار تجريبي', 'Sample option')]],
            ['story' => 'Disabled', 'state' => 'disabled', 'props' => ['name' => 'terms', 'id' => 'ds-check', 'disabled' => true, 'label' => $t('خيار تجريبي', 'Sample option')]],
            ['story' => 'Error', 'state' => 'error', 'props' => ['name' => 'terms', 'id' => 'ds-check', 'label' => $t('خيار تجريبي', 'Sample option'), 'error' => $t('هذا الخيار مطلوب', 'This option is required')]],
        ]],

        'radio' => ['stories' => [
            ['story' => 'Group', 'blade' => $both(fn (string $l): string => '<x-ui.fieldset legend="'.($l === 'ar' ? 'سؤال تجريبي' : 'Sample question').'" id="ds-group"><x-ui.radio name="g" value="1" id="ds-r1" label="'.($l === 'ar' ? 'الخيار الأول' : 'First option').'" checked /><x-ui.radio name="g" value="2" id="ds-r2" label="'.($l === 'ar' ? 'الخيار الثاني' : 'Second option').'" /></x-ui.fieldset>')],
            ['story' => 'Disabled', 'state' => 'disabled', 'props' => ['name' => 'g', 'value' => '1', 'id' => 'ds-r1', 'disabled' => true, 'label' => $t('الخيار الأول', 'First option')]],
        ]],

        'rating' => ['stories' => [
            ['story' => 'Default', 'props' => ['legend' => $t('سؤال تجريبي', 'Sample question'), 'name' => 'ds-rating', 'id' => 'ds-rating', 'hint' => $t('1 الأقل · 5 الأفضل', '1 lowest · 5 highest')]],
            ['story' => 'Selected', 'props' => ['legend' => $t('سؤال تجريبي', 'Sample question'), 'name' => 'ds-rating', 'id' => 'ds-rating', 'selected' => 4]],
            ['story' => 'Required', 'props' => ['legend' => $t('سؤال تجريبي', 'Sample question'), 'name' => 'ds-rating', 'id' => 'ds-rating', 'required' => true]],
            ['story' => 'Error', 'state' => 'error', 'props' => ['legend' => $t('سؤال تجريبي', 'Sample question'), 'name' => 'ds-rating', 'id' => 'ds-rating', 'required' => true, 'error' => $t('هذا الحقل مطلوب', 'This field is required')]],
        ]],

        'fieldset' => ['stories' => [
            ['story' => 'Default', 'blade' => $both(fn (string $l): string => '<x-ui.fieldset legend="'.($l === 'ar' ? 'سؤال تجريبي' : 'Sample question').'" id="ds-group" hint="'.($l === 'ar' ? 'نص مساعد تجريبي' : 'Sample hint text').'"><x-ui.radio name="g" value="1" id="ds-r1" label="'.($l === 'ar' ? 'الخيار الأول' : 'First option').'" /><x-ui.radio name="g" value="2" id="ds-r2" label="'.($l === 'ar' ? 'الخيار الثاني' : 'Second option').'" /></x-ui.fieldset>')],
            ['story' => 'Required', 'blade' => $both(fn (string $l): string => '<x-ui.fieldset legend="'.($l === 'ar' ? 'سؤال تجريبي' : 'Sample question').'" id="ds-group" required><x-ui.radio name="g" value="1" id="ds-r1" label="'.($l === 'ar' ? 'نعم' : 'Yes').'" /><x-ui.radio name="g" value="2" id="ds-r2" label="'.($l === 'ar' ? 'لا' : 'No').'" /></x-ui.fieldset>')],
            ['story' => 'Error', 'state' => 'error', 'blade' => $both(fn (string $l): string => '<x-ui.fieldset legend="'.($l === 'ar' ? 'سؤال تجريبي' : 'Sample question').'" id="ds-group" error="'.($l === 'ar' ? 'اختر إجابة' : 'Choose an answer').'"><x-ui.radio name="g" value="1" id="ds-r1" label="'.($l === 'ar' ? 'الخيار الأول' : 'First option').'" /><x-ui.radio name="g" value="2" id="ds-r2" label="'.($l === 'ar' ? 'الخيار الثاني' : 'Second option').'" /></x-ui.fieldset>')],
        ]],

        'switch' => ['stories' => [
            ['story' => 'Off', 'props' => ['name' => 'toggle', 'label' => $t('إعداد تجريبي', 'Sample setting')]],
            ['story' => 'On', 'props' => ['name' => 'toggle', 'checked' => true, 'label' => $t('إعداد تجريبي', 'Sample setting')]],
            ['story' => 'WithHint', 'props' => ['name' => 'toggle', 'id' => 'ds-switch', 'label' => $t('إعداد تجريبي', 'Sample setting'), 'hint' => $hint]],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['name' => 'toggle', 'label' => $t('إعداد تجريبي', 'Sample setting')]],
            ['story' => 'Disabled', 'state' => 'disabled', 'props' => ['name' => 'toggle', 'disabled' => true, 'label' => $t('إعداد تجريبي', 'Sample setting')]],
        ]],

        'error-summary' => ['stories' => [
            ['story' => 'Error', 'state' => 'error', 'props' => ['errors' => ['ds-email' => $emailError, 'ds-notes' => $t('هذا الحقل مطلوب', 'This field is required')]]],
            ['story' => 'Empty', 'state' => 'empty', 'props' => ['errors' => []]],
        ]],

        // ───────────────────────────── Display ─────────────────────────────
        'card' => ['stories' => [
            ['story' => 'Default', 'props' => ['title' => $t('عنوان تجريبي', 'Sample title')], 'slot' => $t('<p>نص تجريبي للبطاقة.</p>', '<p>Sample card text.</p>')],
            ['story' => 'Raised', 'props' => ['title' => $t('عنوان تجريبي', 'Sample title'), 'raised' => true], 'slot' => $t('<p>نص تجريبي للبطاقة.</p>', '<p>Sample card text.</p>')],
            ['story' => 'WithFooter', 'props' => ['title' => $t('عنوان تجريبي', 'Sample title')], 'slot' => $t('<p>نص تجريبي للبطاقة.</p>', '<p>Sample card text.</p>'), 'slots' => ['footer' => $t('<x-ui.button size="sm" variant="secondary">إجراء</x-ui.button>', '<x-ui.button size="sm" variant="secondary">Action</x-ui.button>')]],
            ['story' => 'WithMedia', 'props' => ['title' => $t('عنوان تجريبي', 'Sample title')], 'slot' => $t('<p>نص تجريبي للبطاقة.</p>', '<p>Sample card text.</p>'), 'slots' => ['media' => '<x-ui.media-placeholder />']],
            ['story' => 'Link', 'props' => ['title' => $t('عنوان تجريبي', 'Sample title'), 'href' => '#'], 'slot' => $t('<p>البطاقة كلها رابط واحد.</p>', '<p>The whole card is one link.</p>')],
            ['story' => 'Hover', 'state' => 'hover', 'props' => ['title' => $t('عنوان تجريبي', 'Sample title'), 'href' => '#'], 'slot' => $t('<p>البطاقة كلها رابط واحد.</p>', '<p>The whole card is one link.</p>')],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['title' => $t('عنوان تجريبي', 'Sample title'), 'href' => '#'], 'slot' => $t('<p>البطاقة كلها رابط واحد.</p>', '<p>The whole card is one link.</p>')],
        ]],

        'product-card' => ['stories' => [
            ['story' => 'Default', 'blade' => $both(fn (string $l): string => $product($l, 'PRD-00001', 'opens="ds-detail"'))],
            ['story' => 'ArabicNamePending', 'blade' => $both(fn (string $l): string => $product($l, 'PRD-00014', 'opens="ds-detail"'))],
            ['story' => 'LongName', 'blade' => $both(fn (string $l): string => '<ul class="ui-grid" role="list"><li>'.$product($l, 'PRD-00126', 'opens="ds-detail"').'</li><li>'.$product($l, 'PRD-00054', 'opens="ds-detail"').'</li></ul>')],
            ['story' => 'WithMedia', 'blade' => $both(fn (string $l): string => $product($l, 'PRD-00171', 'opens="ds-detail"', '<x-slot:media><x-ui.media-placeholder /></x-slot:media>'))],
            ['story' => 'Grid', 'blade' => $both(fn (string $l): string => '<ul class="ui-grid" role="list">'.implode('', array_map(fn (string $id): string => '<li>'.$product($l, $id, 'opens="ds-detail"').'</li>', ['PRD-00001', 'PRD-00002', 'PRD-00014', 'PRD-00171', 'PRD-00126', 'PRD-00054'])).'</ul>')],
            ['story' => 'Hover', 'state' => 'hover', 'blade' => $both(fn (string $l): string => $product($l, 'PRD-00001', 'opens="ds-detail"'))],
            ['story' => 'Focus', 'state' => 'focus', 'blade' => $both(fn (string $l): string => $product($l, 'PRD-00001', 'opens="ds-detail"'))],
            ['story' => 'Unavailable', 'state' => 'disabled', 'props' => ['name' => $sampleItem, 'unavailable' => true, 'status' => $t('غير متوفر حاليًا', 'Currently unavailable')], 'slots' => ['media' => '<x-ui.media-placeholder />']],
            ['story' => 'WithBadge', 'props' => ['name' => $sampleItem, 'badge' => $t('جديد', 'NEW')]],
            ['story' => 'ListItem', 'blade' => $both(fn (string $l): string => '<ul class="ui-grid" role="list"><li>'.$product($l, 'PRD-00001', 'href="#p-turkish-coffee-single" :level="0"').'</li><li>'.$product($l, 'PRD-00014', 'href="#p-cappuccino" :level="0"').'</li></ul>')],
            ['story' => 'Compact', 'blade' => $both(fn (string $l): string => '<ul class="ui-stack ui-stack--sm" role="list"><li>'.$product($l, 'PRD-00001', 'compact :level="0" href="#p-turkish-coffee-single"').'</li><li>'.$product($l, 'PRD-00014', 'compact :level="0" href="#p-cappuccino"').'</li></ul>')],
        ]],

        'price' => ['stories' => [
            ['story' => 'Default', 'props' => ['fils' => 1500]],
            ['story' => 'QuarterDinar', 'props' => ['fils' => 3250]],
        ]],

        'event-card' => ['stories' => [
            ['story' => 'Default', 'props' => ['title' => $t('فعالية تجريبية — نص للعرض فقط', 'Sample event — display text only'), 'date' => $t('تاريخ تجريبي', 'Sample date'), 'place' => $t('مكان تجريبي', 'Sample place')], 'slot' => $t('<p>وصف تجريبي للفعالية.</p>', '<p>Sample event description.</p>')],
            ['story' => 'WithBadge', 'props' => ['title' => $t('فعالية تجريبية — نص للعرض فقط', 'Sample event — display text only'), 'badge' => $t('شارة تجريبية', 'Sample badge'), 'date' => $t('تاريخ تجريبي', 'Sample date')]],
            ['story' => 'WithMedia', 'props' => ['title' => $t('فعالية تجريبية — نص للعرض فقط', 'Sample event — display text only'), 'date' => $t('تاريخ تجريبي', 'Sample date'), 'place' => $t('مكان تجريبي', 'Sample place')], 'slots' => ['media' => '<x-ui.media-placeholder />']],
            ['story' => 'Link', 'props' => ['title' => $t('فعالية تجريبية — نص للعرض فقط', 'Sample event — display text only'), 'href' => '#', 'date' => $t('تاريخ تجريبي', 'Sample date')]],
            ['story' => 'Hover', 'state' => 'hover', 'props' => ['title' => $t('فعالية تجريبية — نص للعرض فقط', 'Sample event — display text only'), 'href' => '#', 'date' => $t('تاريخ تجريبي', 'Sample date')]],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['title' => $t('فعالية تجريبية — نص للعرض فقط', 'Sample event — display text only'), 'href' => '#', 'date' => $t('تاريخ تجريبي', 'Sample date')]],
        ]],

        'badge' => ['stories' => [
            ['story' => 'Variants', 'blade' => $t(
                '<p class="ui-cluster"><x-ui.badge>محايد</x-ui.badge><x-ui.badge variant="success" icon="circle-check">نجاح</x-ui.badge><x-ui.badge variant="warning" icon="triangle-alert">تنبيه</x-ui.badge><x-ui.badge variant="danger" icon="circle-alert">خطأ</x-ui.badge><x-ui.badge variant="info" icon="info">معلومة</x-ui.badge></p>',
                '<p class="ui-cluster"><x-ui.badge>Neutral</x-ui.badge><x-ui.badge variant="success" icon="circle-check">Success</x-ui.badge><x-ui.badge variant="warning" icon="triangle-alert">Warning</x-ui.badge><x-ui.badge variant="danger" icon="circle-alert">Error</x-ui.badge><x-ui.badge variant="info" icon="info">Info</x-ui.badge></p>',
            )],
            ['story' => 'Default', 'slot' => $t('شارة تجريبية', 'Sample badge')],
        ]],

        'alert' => ['stories' => [
            ['story' => 'Info', 'props' => ['variant' => 'info', 'title' => $t('معلومة تجريبية', 'Sample information')], 'slot' => $t('نص تجريبي للتنبيه.', 'Sample alert text.')],
            ['story' => 'Success', 'props' => ['variant' => 'success', 'title' => $t('تم الحفظ', 'Saved')], 'slot' => $t('نص تجريبي للتنبيه.', 'Sample alert text.')],
            ['story' => 'Warning', 'props' => ['variant' => 'warning', 'title' => $t('تنبيه تجريبي', 'Sample warning')], 'slot' => $t('نص تجريبي للتنبيه.', 'Sample alert text.')],
            ['story' => 'Error', 'state' => 'error', 'props' => ['variant' => 'danger', 'title' => $t('تعذّر الحفظ', 'Could not save')], 'slot' => $t('نص تجريبي للتنبيه.', 'Sample alert text.')],
            ['story' => 'WithoutTitle', 'props' => ['variant' => 'info'], 'slot' => $t('نص تجريبي للتنبيه بلا عنوان.', 'Sample alert text without a title.')],
        ]],

        'empty-state' => ['stories' => [
            ['story' => 'Empty', 'state' => 'empty', 'props' => ['title' => $t('لا توجد عناصر بعد', 'Nothing here yet')], 'slot' => $t('عندما تضيف عنصرًا سيظهر هنا.', 'When you add an item it shows up here.')],
            ['story' => 'WithAction', 'state' => 'empty', 'props' => ['title' => $t('لا توجد نتائج', 'No results')], 'slot' => $t('جرّب كلمة أخرى أو امسح البحث.', 'Try another word or clear the search.'), 'slots' => ['actions' => $t('<x-ui.button variant="secondary">مسح البحث</x-ui.button>', '<x-ui.button variant="secondary">Clear search</x-ui.button>')]],
        ]],

        'skeleton' => ['stories' => [
            ['story' => 'Loading', 'state' => 'loading', 'props' => ['lines' => 3]],
            ['story' => 'WithMedia', 'state' => 'loading', 'props' => ['lines' => 2, 'media' => true]],
            ['story' => 'CardGrid', 'state' => 'loading', 'blade' => '<div aria-busy="true"><ul class="ui-grid" role="list"><li class="ui-card"><x-ui.skeleton media :lines="2" /></li><li class="ui-card"><x-ui.skeleton media :lines="2" decorative /></li><li class="ui-card"><x-ui.skeleton media :lines="2" decorative /></li><li class="ui-card"><x-ui.skeleton media :lines="2" decorative /></li></ul></div>'],
        ]],

        'media-placeholder' => ['stories' => [
            ['story' => 'Default'],
        ]],

        // Sample = the approved brand mark (D-309); real pages pass approved library images only.
        'picture' => ['stories' => [
            ['story' => 'Default', 'props' => ['image' => new MediaImage('/brand/logo-white-480.png', ['image/webp' => '/brand/logo-white-240.webp 240w, /brand/logo-white-480.webp 480w'], 480, 176, 'SHELTER COFFEE'), 'sizes' => '240px']],
            ['story' => 'Square', 'props' => ['image' => new MediaImage('/brand/logo-white-480.png', ['image/webp' => '/brand/logo-white-480.webp 480w'], 480, 176, 'SHELTER COFFEE'), 'ratio' => 'square', 'sizes' => '240px']],
        ]],

        'table' => ['stories' => [
            ['story' => 'Default', 'props' => [
                'caption' => $t('جدول تجريبي', 'Sample table'),
                'columns' => $t(
                    [['key' => 'item', 'label' => 'العنصر'], ['key' => 'status', 'label' => 'الحالة'], ['key' => 'count', 'label' => 'العدد', 'numeric' => true]],
                    [['key' => 'item', 'label' => 'Item'], ['key' => 'status', 'label' => 'Status'], ['key' => 'count', 'label' => 'Count', 'numeric' => true]],
                ),
                'rows' => $t(
                    [['item' => 'عنصر تجريبي 1', 'status' => 'مثال', 'count' => 12], ['item' => 'عنصر تجريبي 2', 'status' => 'مثال', 'count' => 3]],
                    [['item' => 'Sample item 1', 'status' => 'Example', 'count' => 12], ['item' => 'Sample item 2', 'status' => 'Example', 'count' => 3]],
                ),
                'rowHeader' => 'item',
            ]],
            ['story' => 'Stacked', 'props' => [
                'caption' => $t('جدول تجريبي يتحول إلى بطاقات على الموبايل', 'Sample table that becomes cards on mobile'),
                'stack' => true,
                'columns' => $t(
                    [['key' => 'item', 'label' => 'العنصر'], ['key' => 'status', 'label' => 'الحالة'], ['key' => 'count', 'label' => 'العدد', 'numeric' => true]],
                    [['key' => 'item', 'label' => 'Item'], ['key' => 'status', 'label' => 'Status'], ['key' => 'count', 'label' => 'Count', 'numeric' => true]],
                ),
                'rows' => $t(
                    [['item' => 'عنصر تجريبي 1', 'status' => 'مثال', 'count' => 12], ['item' => 'عنصر تجريبي 2', 'status' => 'مثال', 'count' => 3]],
                    [['item' => 'Sample item 1', 'status' => 'Example', 'count' => 12], ['item' => 'Sample item 2', 'status' => 'Example', 'count' => 3]],
                ),
                'rowHeader' => 'item',
            ]],
            ['story' => 'StackedWide', 'props' => [
                'caption' => $t('جدول تجريبي يبقى بطاقات حتى 1200px', 'Sample table that stays cards up to 1200px'),
                'stack' => 'wide',
                'columns' => $t(
                    [['key' => 'item', 'label' => 'العنصر'], ['key' => 'status', 'label' => 'الحالة'], ['key' => 'count', 'label' => 'العدد', 'numeric' => true]],
                    [['key' => 'item', 'label' => 'Item'], ['key' => 'status', 'label' => 'Status'], ['key' => 'count', 'label' => 'Count', 'numeric' => true]],
                ),
                'rows' => $t(
                    [['item' => 'عنصر تجريبي 1', 'status' => 'مثال', 'count' => 12], ['item' => 'عنصر تجريبي 2', 'status' => 'مثال', 'count' => 3]],
                    [['item' => 'Sample item 1', 'status' => 'Example', 'count' => 12], ['item' => 'Sample item 2', 'status' => 'Example', 'count' => 3]],
                ),
                'rowHeader' => 'item',
            ]],
        ]],

        // ───────────────────────────── Navigation ─────────────────────────────
        'pagination' => ['stories' => [
            ['story' => 'Middle', 'props' => ['current' => 5, 'total' => 9, 'url' => '#page-{page}']],
            ['story' => 'FirstPage', 'state' => 'disabled', 'props' => ['current' => 1, 'total' => 9, 'url' => '#page-{page}']],
            ['story' => 'LastPage', 'state' => 'disabled', 'props' => ['current' => 9, 'total' => 9, 'url' => '#page-{page}']],
            ['story' => 'FewPages', 'props' => ['current' => 2, 'total' => 3, 'url' => '#page-{page}']],
            ['story' => 'Hover', 'state' => 'hover', 'props' => ['current' => 2, 'total' => 3, 'url' => '#page-{page}']],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['current' => 2, 'total' => 3, 'url' => '#page-{page}']],
        ]],

        'breadcrumb' => ['stories' => [
            ['story' => 'Default', 'props' => ['items' => $t(
                [['label' => 'الرئيسية', 'href' => '#'], ['label' => 'المنيو', 'href' => '#'], ['label' => 'صفحة تجريبية']],
                [['label' => 'Home', 'href' => '#'], ['label' => 'Menu', 'href' => '#'], ['label' => 'Sample page']],
            )]],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['items' => $t(
                [['label' => 'الرئيسية', 'href' => '#'], ['label' => 'صفحة تجريبية']],
                [['label' => 'Home', 'href' => '#'], ['label' => 'Sample page']],
            )]],
        ]],

        'skip-link' => ['stories' => [
            ['story' => 'Focus', 'state' => 'focus', 'blade' => '<x-ui.skip-link href="#ds-main" /><main id="ds-main" tabindex="-1"></main>'],
        ]],

        'nav-link' => ['stories' => [
            ['story' => 'Default', 'blade' => $t(
                '<nav aria-label="تنقل تجريبي"><ul class="ui-cluster" role="list"><li><x-ui.nav-link href="#" icon="house">الرئيسية</x-ui.nav-link></li><li><x-ui.nav-link href="#" current>المنيو</x-ui.nav-link></li><li><x-ui.nav-link href="#">رابط تجريبي</x-ui.nav-link></li></ul></nav>',
                '<nav aria-label="Sample navigation"><ul class="ui-cluster" role="list"><li><x-ui.nav-link href="#" icon="house">Home</x-ui.nav-link></li><li><x-ui.nav-link href="#" current>Menu</x-ui.nav-link></li><li><x-ui.nav-link href="#">Sample link</x-ui.nav-link></li></ul></nav>',
            )],
            ['story' => 'Hover', 'state' => 'hover', 'props' => ['href' => '#'], 'slot' => $t('رابط تجريبي', 'Sample link')],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['href' => '#'], 'slot' => $t('رابط تجريبي', 'Sample link')],
            ['story' => 'Active', 'state' => 'active', 'props' => ['href' => '#'], 'slot' => $t('رابط تجريبي', 'Sample link')],
        ]],

        'tabs' => ['stories' => [
            ['story' => 'Default', 'blade' => $t(
                '<x-ui.tabs id="ds-tabs" label="أقسام تجريبية" :tabs="[\'one\' => \'نظرة عامة\', \'two\' => \'السجل\', \'three\' => \'الإعدادات\']" selected="one"><x-ui.tab-panel tab="one"><p>محتوى تجريبي للقسم الأول.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>محتوى تجريبي للقسم الثاني.</p></x-ui.tab-panel><x-ui.tab-panel tab="three"><p>محتوى تجريبي للقسم الثالث.</p></x-ui.tab-panel></x-ui.tabs>',
                '<x-ui.tabs id="ds-tabs" label="Sample sections" :tabs="[\'one\' => \'Overview\', \'two\' => \'History\', \'three\' => \'Settings\']" selected="one"><x-ui.tab-panel tab="one"><p>Sample content for the first section.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>Sample content for the second section.</p></x-ui.tab-panel><x-ui.tab-panel tab="three"><p>Sample content for the third section.</p></x-ui.tab-panel></x-ui.tabs>',
            )],
            ['story' => 'SecondSelected', 'blade' => $t(
                '<x-ui.tabs id="ds-tabs" label="أقسام تجريبية" :tabs="[\'one\' => \'نظرة عامة\', \'two\' => \'السجل\']" selected="two"><x-ui.tab-panel tab="one"><p>محتوى تجريبي للقسم الأول.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>محتوى تجريبي للقسم الثاني.</p></x-ui.tab-panel></x-ui.tabs>',
                '<x-ui.tabs id="ds-tabs" label="Sample sections" :tabs="[\'one\' => \'Overview\', \'two\' => \'History\']" selected="two"><x-ui.tab-panel tab="one"><p>Sample content for the first section.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>Sample content for the second section.</p></x-ui.tab-panel></x-ui.tabs>',
            )],
            ['story' => 'Hover', 'state' => 'hover', 'blade' => $t(
                '<x-ui.tabs id="ds-tabs" label="أقسام تجريبية" :tabs="[\'one\' => \'نظرة عامة\', \'two\' => \'السجل\']" selected="one"><x-ui.tab-panel tab="one"><p>محتوى تجريبي.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>محتوى تجريبي.</p></x-ui.tab-panel></x-ui.tabs>',
                '<x-ui.tabs id="ds-tabs" label="Sample sections" :tabs="[\'one\' => \'Overview\', \'two\' => \'History\']" selected="one"><x-ui.tab-panel tab="one"><p>Sample content.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>Sample content.</p></x-ui.tab-panel></x-ui.tabs>',
            )],
            ['story' => 'Focus', 'state' => 'focus', 'blade' => $t(
                '<x-ui.tabs id="ds-tabs" label="أقسام تجريبية" :tabs="[\'one\' => \'نظرة عامة\', \'two\' => \'السجل\']" selected="one"><x-ui.tab-panel tab="one"><p>محتوى تجريبي.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>محتوى تجريبي.</p></x-ui.tab-panel></x-ui.tabs>',
                '<x-ui.tabs id="ds-tabs" label="Sample sections" :tabs="[\'one\' => \'Overview\', \'two\' => \'History\']" selected="one"><x-ui.tab-panel tab="one"><p>Sample content.</p></x-ui.tab-panel><x-ui.tab-panel tab="two"><p>Sample content.</p></x-ui.tab-panel></x-ui.tabs>',
            )],
        ]],

        'disclosure' => ['stories' => [
            ['story' => 'Closed', 'props' => ['summary' => $t('سؤال تجريبي', 'Sample question')], 'slot' => $t('<p>إجابة تجريبية.</p>', '<p>Sample answer.</p>')],
            ['story' => 'Open', 'props' => ['summary' => $t('سؤال تجريبي', 'Sample question'), 'open' => true], 'slot' => $t('<p>إجابة تجريبية.</p>', '<p>Sample answer.</p>')],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['summary' => $t('سؤال تجريبي', 'Sample question')], 'slot' => $t('<p>إجابة تجريبية.</p>', '<p>Sample answer.</p>')],
        ]],

        // ───────────────────────────── Overlays ─────────────────────────────
        'modal' => ['stories' => [
            ['story' => 'Open', 'state' => 'open', 'blade' => $both(fn (string $l): string => '<x-ui.modal id="ds-detail" title="'.($l === 'ar' ? 'تفاصيل الصنف' : 'Item details').'">'.$dialogBody($l).'<x-slot:footer><form method="dialog"><x-ui.button type="submit" variant="secondary">'.($l === 'ar' ? 'رجوع' : 'Back').'</x-ui.button></form></x-slot:footer></x-ui.modal>')],
            ['story' => 'Trigger', 'blade' => $both(fn (string $l): string => '<x-ui.button opens="ds-detail">'.($l === 'ar' ? 'فتح النافذة' : 'Open dialog').'</x-ui.button><x-ui.modal id="ds-detail" title="'.($l === 'ar' ? 'تفاصيل الصنف' : 'Item details').'">'.$dialogBody($l).'</x-ui.modal>')],
        ]],

        'drawer' => ['stories' => [
            ['story' => 'OpenEnd', 'state' => 'open', 'blade' => $both(fn (string $l): string => '<x-ui.drawer id="ds-drawer" title="'.($l === 'ar' ? 'عرض سريع' : 'Quick view').'"><p>'.($l === 'ar' ? 'محتوى تجريبي للوحة الجانبية.' : 'Sample side panel content.').'</p></x-ui.drawer>')],
            ['story' => 'OpenStart', 'state' => 'open', 'blade' => $both(fn (string $l): string => '<x-ui.drawer id="ds-drawer" side="start" title="'.($l === 'ar' ? 'التنقل' : 'Navigation').'"><p>'.($l === 'ar' ? 'محتوى تجريبي للوحة الجانبية.' : 'Sample side panel content.').'</p></x-ui.drawer>')],
        ]],

        'bottom-sheet' => ['stories' => [
            ['story' => 'Open', 'state' => 'open', 'blade' => $both(fn (string $l): string => '<x-ui.bottom-sheet id="ds-sheet" title="'.($l === 'ar' ? 'تفاصيل الصنف' : 'Item details').'">'.$product($l, 'PRD-00014').'</x-ui.bottom-sheet>')],
            ['story' => 'AdaptiveOpen', 'state' => 'open', 'blade' => $both(fn (string $l): string => '<x-ui.bottom-sheet adaptive id="ds-sheet" title="'.($l === 'ar' ? 'تفاصيل الصنف' : 'Item details').'">'.$product($l, 'PRD-00001').'</x-ui.bottom-sheet>')],
        ]],

        // ───────────────────────────── Dashboard ─────────────────────────────
        'stat-tile' => ['stories' => [
            ['story' => 'Default', 'props' => ['label' => $t('مؤشر تجريبي', 'Sample metric'), 'value' => '12']],
            ['story' => 'TrendUp', 'props' => ['label' => $t('مؤشر تجريبي', 'Sample metric'), 'value' => '12', 'trend' => 'up', 'trendText' => $t('+3 عن الأسبوع الماضي (مثال)', '+3 vs last week (sample)')]],
            ['story' => 'TrendDown', 'props' => ['label' => $t('مؤشر تجريبي', 'Sample metric'), 'value' => '7', 'trend' => 'down', 'trendText' => $t('−2 عن الأسبوع الماضي (مثال)', '−2 vs last week (sample)'), 'hint' => $t('بيانات تجريبية', 'Sample data')]],
            ['story' => 'Link', 'props' => ['label' => $t('مؤشر تجريبي', 'Sample metric'), 'value' => '12', 'href' => '#']],
            ['story' => 'Hover', 'state' => 'hover', 'props' => ['label' => $t('مؤشر تجريبي', 'Sample metric'), 'value' => '12', 'href' => '#']],
            ['story' => 'Focus', 'state' => 'focus', 'props' => ['label' => $t('مؤشر تجريبي', 'Sample metric'), 'value' => '12', 'href' => '#']],
        ]],

        'status-pill' => ['stories' => [
            ['story' => 'AllStatuses', 'blade' => '<p class="ui-cluster"><x-ui.status-pill status="SYNCED" /><x-ui.status-pill status="PENDING" /><x-ui.status-pill status="FAILED" /><x-ui.status-pill status="NOT SUPPORTED" /><x-ui.status-pill status="MANUAL ACTION REQUIRED" /><x-ui.status-pill status="OUT OF SYNC" /></p>'],
            ['story' => 'Synced', 'props' => ['status' => 'SYNCED']],
            ['story' => 'Pending', 'state' => 'loading', 'props' => ['status' => 'PENDING']],
            ['story' => 'Failed', 'state' => 'error', 'props' => ['status' => 'FAILED']],
            ['story' => 'NotSupported', 'state' => 'disabled', 'props' => ['status' => 'NOT SUPPORTED']],
            ['story' => 'ManualActionRequired', 'props' => ['status' => 'MANUAL ACTION REQUIRED']],
            ['story' => 'OutOfSync', 'state' => 'error', 'props' => ['status' => 'OUT OF SYNC']],
        ]],

        'sidebar-item' => ['stories' => [
            ['story' => 'Default', 'blade' => $t(
                '<nav aria-label="تنقل تجريبي"><ul class="ui-sidebar"><x-ui.sidebar-item href="#" icon="house" current>مركز التحكم</x-ui.sidebar-item><x-ui.sidebar-item href="#" icon="inbox" :count="3">قسم تجريبي</x-ui.sidebar-item><x-ui.sidebar-item href="#" icon="calendar">قسم تجريبي آخر</x-ui.sidebar-item></ul></nav>',
                '<nav aria-label="Sample navigation"><ul class="ui-sidebar"><x-ui.sidebar-item href="#" icon="house" current>Command Center</x-ui.sidebar-item><x-ui.sidebar-item href="#" icon="inbox" :count="3">Sample section</x-ui.sidebar-item><x-ui.sidebar-item href="#" icon="calendar">Another sample section</x-ui.sidebar-item></ul></nav>',
            )],
            ['story' => 'Hover', 'state' => 'hover', 'blade' => $t(
                '<nav aria-label="تنقل تجريبي"><ul class="ui-sidebar"><x-ui.sidebar-item href="#" icon="inbox">قسم تجريبي</x-ui.sidebar-item></ul></nav>',
                '<nav aria-label="Sample navigation"><ul class="ui-sidebar"><x-ui.sidebar-item href="#" icon="inbox">Sample section</x-ui.sidebar-item></ul></nav>',
            )],
            ['story' => 'Focus', 'state' => 'focus', 'blade' => $t(
                '<nav aria-label="تنقل تجريبي"><ul class="ui-sidebar"><x-ui.sidebar-item href="#" icon="inbox">قسم تجريبي</x-ui.sidebar-item></ul></nav>',
                '<nav aria-label="Sample navigation"><ul class="ui-sidebar"><x-ui.sidebar-item href="#" icon="inbox">Sample section</x-ui.sidebar-item></ul></nav>',
            )],
        ]],

        'page-header' => ['stories' => [
            ['story' => 'Default', 'props' => ['title' => $t('صفحة تجريبية', 'Sample page')]],
            ['story' => 'WithActions', 'props' => ['title' => $t('صفحة تجريبية', 'Sample page'), 'description' => $t('وصف تجريبي قصير للصفحة.', 'A short sample description of the page.')], 'slots' => ['actions' => $t('<x-ui.button variant="secondary">تصدير</x-ui.button><x-ui.button>إضافة</x-ui.button>', '<x-ui.button variant="secondary">Export</x-ui.button><x-ui.button>Add</x-ui.button>')]],
            ['story' => 'WithBreadcrumb', 'props' => ['title' => $t('صفحة تجريبية', 'Sample page')], 'slots' => ['breadcrumb' => $t(
                '<x-ui.breadcrumb :items="[[\'label\' => \'مركز التحكم\', \'href\' => \'#\'], [\'label\' => \'صفحة تجريبية\']]" />',
                '<x-ui.breadcrumb :items="[[\'label\' => \'Command Center\', \'href\' => \'#\'], [\'label\' => \'Sample page\']]" />',
            )]],
        ]],

        'toolbar' => ['stories' => [
            ['story' => 'Default', 'blade' => $both(fn (string $l): string => '<x-ui.toolbar label="'.($l === 'ar' ? 'أدوات تجريبية' : 'Sample tools').'"><x-ui.field label="'.($l === 'ar' ? 'بحث' : 'Search').'" for="ds-search"><x-ui.input type="search" name="q" /></x-ui.field><x-ui.button variant="secondary">'.($l === 'ar' ? 'تصفية' : 'Filter').'</x-ui.button><x-ui.button variant="ghost">'.($l === 'ar' ? 'مسح' : 'Clear').'</x-ui.button></x-ui.toolbar>')],
        ]],

        // ───────────────────────────── Site (PHASE 2) ─────────────────────────────
        'site-header' => ['stories' => [
            ['story' => 'Default', 'props' => ['home' => '#home', 'nav' => $both($nav), 'languages' => $both($languages), 'drawer-id' => 'ds-site-nav']],
            ['story' => 'Minimal', 'props' => ['home' => '#home', 'minimal' => true]],
        ]],
        'site-footer' => ['stories' => [
            ['story' => 'Default', 'props' => ['home' => '#home', 'nav' => $both($nav), 'languages' => $both($languages), 'phone' => $phone, 'whatsapp' => $whatsapp, 'contact' => '#contact']],
            ['story' => 'WithLegal', 'props' => ['home' => '#home', 'nav' => $both($nav), 'languages' => $both($languages), 'phone' => $phone, 'whatsapp' => $whatsapp, 'contact' => '#contact',
                'legal' => $t([['label' => 'سياسة الخصوصية', 'href' => '#privacy'], ['label' => 'الشروط', 'href' => '#terms']], [['label' => 'Privacy policy', 'href' => '#privacy'], ['label' => 'Terms', 'href' => '#terms']])]],
        ]],
        'hero' => ['stories' => [
            ['story' => 'Brand', 'props' => [
                'lines' => $t(['شلتر', 'كوفي'], ['SHELTER', 'COFFEE']),
                'eyebrow' => $t('SHELTER COFFEE', 'شلتر كوفي'),
                'eyebrow-lang' => $t('en', 'ar'),
                'lead' => $t('نص تمهيدي تجريبي للواجهة.', 'Sample lead text for the hero.'),
                'title-id' => 'ds-hero',
            ], 'slot' => $t('<x-ui.button size="lg" href="#locations" icon-end="arrow-right">الفروع والمواعيد</x-ui.button>', '<x-ui.button size="lg" href="#locations" icon-end="arrow-right">Locations and hours</x-ui.button>')],
        ]],
        'section-heading' => ['stories' => [
            ['story' => 'Default', 'props' => ['title' => $t('عنوان قسم تجريبي', 'Sample section title'), 'lead' => $t('وصف قصير تجريبي للقسم.', 'A short sample description of the section.'), 'id' => 'ds-section']],
            ['story' => 'WithLink', 'props' => ['title' => $t('الفروع', 'Locations'), 'lead' => $t('وصف قصير تجريبي.', 'A short sample description.'), 'href' => '#locations', 'link-label' => $t('كل التفاصيل', 'All details'), 'id' => 'ds-section-link']],
        ]],
        'branch-card' => ['stories' => [
            ['story' => 'Row', 'props' => ['branch' => $both(fn (string $l) => $branchCard($l, 'open'))]],
            ['story' => 'Panel', 'props' => ['branch' => $both(fn (string $l) => $branchCard($l, 'closing')), 'variant' => 'panel', 'details-label' => $t('التفاصيل والساعات', 'Details and hours')]],
            ['story' => 'Closed', 'props' => ['branch' => $both(fn (string $l) => $branchCard($l, 'closed')), 'variant' => 'panel']],
        ]],
        'search-form' => ['stories' => [
            ['story' => 'Default', 'props' => ['action' => '#search', 'id' => 'ds-search-form', 'label' => $t('ابحث في الموقع', 'Search the site'), 'submit-label' => $t('بحث', 'Search')]],
            ['story' => 'WithQuery', 'props' => ['action' => '#search', 'id' => 'ds-search-form-q', 'label' => $t('ابحث في الموقع', 'Search the site'), 'value' => $t('لاتيه', 'latte'), 'submit-label' => $t('بحث', 'Search')]],
            ['story' => 'Compact', 'props' => ['action' => '#search', 'id' => 'ds-search-form-c', 'label' => $t('ابحث في الموقع', 'Search the site'), 'submit-label' => $t('بحث', 'Search'), 'compact' => true]],
        ]],
        'contact-card' => ['stories' => [
            ['story' => 'Intent', 'props' => [
                'icon' => 'message-square-text',
                'title' => $t('الشكاوى والاقتراحات', 'Complaints & Feedback'),
                'lead' => $t('وصف قصير تجريبي.', 'A short sample description.'),
                'phone' => $both(fn (string $l) => new ContactAction('tel:+962799338445', $l === 'ar' ? '0799338445' : '+962 79 933 8445')),
            ]],
            ['story' => 'General', 'props' => [
                'icon' => 'store',
                'title' => $t('التواصل العام والفروع', 'General & Branches'),
                'lead' => $t('وصف قصير تجريبي.', 'A short sample description.'),
                'phone' => $both(fn (string $l) => new ContactAction('tel:+962799009436', $l === 'ar' ? '0799009436' : '+962 79 900 9436')),
                'whatsapp' => $whatsapp,
                'href' => '#locations',
                'link-label' => $t('الفروع وساعات الدوام', 'Locations and opening hours'),
                'wide' => true,
            ], 'slot' => $t(
                '<ul class="ui-contact-card__list" role="list"><li class="ui-contact-card__item"><a class="ui-contact-card__item-link" href="#drive">شلتر كوفي درايف</a></li><li class="ui-contact-card__item"><a class="ui-contact-card__item-link" href="#house">شلتر كوفي هاوس</a></li></ul>',
                '<ul class="ui-contact-card__list" role="list"><li class="ui-contact-card__item"><a class="ui-contact-card__item-link" href="#drive">SHELTER COFFEE DRIVE</a></li><li class="ui-contact-card__item"><a class="ui-contact-card__item-link" href="#house">SHELTER COFFEE HOUSE</a></li></ul>',
            )],
            ['story' => 'WithLink', 'props' => [
                'icon' => 'handshake',
                'title' => $t('استفسارات الفرنشايز', 'Franchise inquiries'),
                'phone' => $both(fn (string $l) => new ContactAction('tel:+962799338445', $l === 'ar' ? '0799338445' : '+962 79 933 8445')),
                'href' => '#franchise',
                'link-label' => $t('صفحة الفرنشايز', 'Franchise page'),
            ]],
        ]],
        'open-status' => ['stories' => [
            ['story' => 'Open', 'props' => ['timeline' => $both(fn (string $l) => $status('open', $l))]],
            ['story' => 'Closing', 'props' => ['timeline' => $both(fn (string $l) => $status('closing', $l))]],
            ['story' => 'Closed', 'props' => ['timeline' => $both(fn (string $l) => $status('closed', $l))]],
            ['story' => 'Large', 'props' => ['timeline' => $both(fn (string $l) => $status('open', $l)), 'size' => 'lg']],
        ]],
        'hours-table' => ['stories' => [
            ['story' => 'Week', 'props' => ['rows' => $both($week), 'caption' => $t('ساعات الدوام', 'Opening hours')]],
        ]],
        'action-bar' => ['stories' => [
            ['story' => 'Default', 'props' => ['label' => $t('إجراءات الفرع', 'Branch actions')], 'slot' => $t('<x-ui.button icon="phone" href="#call">اتصال</x-ui.button><x-ui.button variant="secondary" icon="message-circle" href="#wa">واتساب</x-ui.button>', '<x-ui.button icon="phone" href="#call">Call</x-ui.button><x-ui.button variant="secondary" icon="message-circle" href="#wa">WhatsApp</x-ui.button>')],
        ]],
    ],
];
