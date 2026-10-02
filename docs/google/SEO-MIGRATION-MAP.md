# SEO MIGRATION MAP (الخريطة الرسمية — قيد البناء)

> **الحالة:** `DRAFT` — تتحول إلى خريطة نهائية بعد: (1) اعتماد الـURL Architecture (D-031 + ROOT-01) (2) Search Console Baseline (3) الفحص المباشر (AC-01).
> **المصطلحات (السياسة §15):** `KEEP` · `REBUILD` · `REDIRECT` · `REMOVE`. ملاحظة: الـSeed السابق استخدم `REPLACE`، وهي = `REBUILD` هنا.
> **قاعدة:** أي URL يتغير ويملك قيمة (Traffic / Impressions / Clicks / Backlinks / Rankings) ← **301** إلى الصفحة المكافئة بالـIntent، بقفزة واحدة.
> **آخر تحديث:** 2026-10-01

## المصادر
- البذرة الحالية: [`../phase-01-discovery/02-url-inventory-and-migration-seed.md`](../phase-01-discovery/02-url-inventory-and-migration-seed.md)
- خطة التحويلات والجذر و`/menu`: [`../phase-01-discovery/13-root-and-international-seo-plan.md`](../phase-01-discovery/13-root-and-international-seo-plan.md) §5–§7
- الأرقام الحقيقية: [`SEARCH-CONSOLE-BASELINE.md`](SEARCH-CONSOLE-BASELINE.md) (بانتظار البيانات)

## أهم الروابط ذات القيمة (من Semrush — تقديرات حتى وصول Search Console)

| Old URL | القيمة | الإجراء | الوجهة المقترحة (P3) |
|---|---|---|---|
| `shelterjo.com/` (بدون www) + `http` | 160 رابطًا خارجيًا + Knowledge Panel | KEEP (تحويل Host بقفزة واحدة) | حسب ROOT-01 |
| `/` | #2 "shelter coffee drive" | KEEP / REBUILD | حسب ROOT-01 |
| `/menu` | 42 رابطًا خارجيًا | REDIRECT 301 (M1 مقترح) | `/ar/jo/menu/` |
| `/القائمة-شلتر-كافية-محافظة-اربد/` | 6 كلمات | REDIRECT 301 | `/ar/jo/menu/` |
| `/best-cafes-irbid-2026/` | #4 "كافيهات اربد" (2,400) | ⏳ R7-03 — M57 §26: لا يُعاد إنشاء مقال «أفضل كافيهات إربد» (ترتيب منافسين)؛ المقترح REDIRECT 301 إلى `/ar/jo/locations/` | — |
| `/coffee-house-vs-cafe/` | ~27 زيارة/شهر + مصر ولبنان | REBUILD | `/ar/{hub}/{slug}/` |
| `/city-centre-branch/` | مفهرسة | REDIRECT 301 | `/ar/jo/locations/irbid/house/` (URL-02) |
| `/drive-thru/` · `/drive-thru-shelter-irbid-how-it-works/` | مفهرسة | REDIRECT 301 | `/ar/jo/locations/irbid/drive/` (URL-02) |
| `/blog/` | يظهر في نتائج عربية لاستعلامات إربد (M57 — `docs/seo/LOCAL-KEYWORD-RESEARCH.md` §7.1) | ⏳ مقترح: REDIRECT 301 إلى قسم المعرفة عند إنشائه (PO-035)، وإلا إلى `/ar/` | يُقرّ في بوابة PHASE 7 بموافقة الـOwner |

الخريطة الكاملة لكل URL تُبنى هنا بعد الفحص المباشر.
