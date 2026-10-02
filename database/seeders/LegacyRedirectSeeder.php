<?php

namespace Database\Seeders;

use App\Models\Redirect;
use Illuminate\Database\Seeder;

/**
 * The old site's addresses with a clear new page (docs/google/SEO-MIGRATION-MAP.md, docs/phase-01-discovery/02 + 24),
 * written once as SWITCHED-OFF drafts: switching a redirect on is the Owner's launch approval (SEO-013, D-054). A row
 * the Owner already has is never touched. Old pages whose fate is the Owner's decision (the articles — PO-015, the
 * blog and knowledge hub — PO-035, privacy/terms/data-deletion until their text is published — PO-019) are not here:
 * no "everything to the home page".
 */
class LegacyRedirectSeeder extends Seeder
{
    /** source => [target|null, code, decision/source reference, note] */
    private const ROWS = [
        '/menu' => ['/ar/jo/menu/', 301, 'D-054', 'رابط المنيو القصير (42 رابطًا خارجيًا وQR) — يعمل في الموقع القديم اليوم'],
        '/القائمة-شلتر-كافية-محافظة-اربد' => ['/ar/jo/menu/', 301, 'SEO-MIGRATION-MAP', 'صفحة المنيو القديمة'],
        '/hiring' => ['/ar/careers/', 301, 'LIVE-CRAWL-24', 'تحويل قائم في الموقع القديم'],
        '/موقعنا' => ['/ar/jo/locations/', 301, 'LIVE-CRAWL-24', 'تحويل قائم في الموقع القديم'],
        '/locations' => ['/ar/jo/locations/', 301, 'LIVE-CRAWL-24', 'أرشيف الفروع (Yoast Local)'],
        '/موقع-شلتر-كافية-محافظة-اربد' => ['/ar/jo/locations/', 301, 'LIVE-CRAWL-24', 'صفحة موقع مكررة'],
        '/city-centre-branch' => ['/ar/jo/locations/irbid/house/', 301, 'URL-02', 'فرع سيتي سنتر'],
        '/qasr-al-nakheel-branch' => ['/ar/jo/locations/irbid/drive/', 301, 'URL-02', 'فرع قصر النخيل (DRIVE)'],
        '/drive-thru' => ['/ar/jo/locations/irbid/drive/', 301, 'URL-02', 'صفحة الدرايف ثرو'],
        '/drive-thru-shelter-irbid-how-it-works' => ['/ar/jo/locations/irbid/drive/', 301, 'DB-07', 'دمج في صفحة الدرايف ثرو'],
        '/أول-خدمة-سيارات-في-الشمال-كافيه-شلتر' => ['/ar/jo/locations/irbid/drive/', 301, 'SEED-02', 'ادعاء «أول» + روابط سبام: لا يُعاد نشره'],
        '/franchise-shelter-coffee' => ['/ar/franchise/', 301, 'FRAN-093', 'بعد الفحوص العشرة وموافقتك'],
        '/sitemap_index.xml' => ['/sitemap.xml', 301, 'SEED-02', 'قد يكون مسجلًا في Search Console'],
        '/post-sitemap.xml' => ['/sitemap.xml', 301, 'SEED-02', ''],
        '/page-sitemap.xml' => ['/sitemap.xml', 301, 'SEED-02', ''],
        '/locations.kml' => [null, 410, 'SEED-02', 'خريطة Yoast Local القديمة'],
        '/geo-sitemap.xml' => [null, 410, 'SEED-02', ''],
        '/uicore-tb-sitemap.xml' => [null, 410, 'SEED-02', 'قالب Demo'],
        '/author-sitemap.xml' => [null, 410, 'SEED-02', ''],
        '/category-sitemap.xml' => [null, 410, 'SEED-02', ''],
    ];

    public function run(): void
    {
        foreach (self::ROWS as $source => [$target, $code, $ref, $note]) {
            Redirect::query()->firstOrCreate(['source_path' => $source], [
                'target' => $target, 'status_code' => $code, 'state' => 'draft', 'origin' => 'plan',
                'decision_ref' => $ref, 'note' => $note === '' ? null : $note,
            ]);
        }
    }
}
