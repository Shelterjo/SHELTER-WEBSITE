# SEARCH CONSOLE BASELINE

> **الحالة:** ⏳ بانتظار Exports من الـOwner (D-042 — الخيار المجاني أولًا). **Read-only / Restricted** — لا تعديل لأي إعداد (§13 من السياسة).
> عند وصول البيانات **تحل محل تقديرات Semrush** في الـSEO وفي خريطة النقل (§12، §51).
> **آخر تحديث:** 2026-10-01

## ما نحتاجه (Exports مجانية من واجهة Search Console)

| # | التقرير في Search Console | الإعداد | الصيغة | لماذا |
|---|---|---|---|---|
| SC-01 | Performance → Search results → **Pages** | آخر 16 شهرًا | CSV أو Google Sheets | Top / High-click / High-impression pages |
| SC-02 | Performance → **Queries** | آخر 16 شهرًا | CSV | Top queries |
| SC-03 | Performance → **Countries** و **Devices** | آخر 16 شهرًا | CSV | التوزيع الجغرافي والأجهزة |
| SC-04 | Performance → **Search appearance** | آخر 16 شهرًا | CSV | Rich results الحالية |
| SC-05 | Performance → **Dates** | آخر 16 شهرًا | CSV | الاتجاه (تحقق من انخفاض ~75% المقدّر — RISK-03) |
| SC-06 | Indexing → **Pages** (Indexed + Not indexed مع الأسباب) | — | CSV | Indexed / Excluded URLs |
| SC-07 | Indexing → **Sitemaps** | — | لقطة | الـSitemaps المسجلة |
| SC-08 | Experience → **Core Web Vitals** (Mobile + Desktop) | — | لقطة أو CSV | Baseline الأداء الحقيقي |
| SC-09 | **Links** → Top linked pages + Top linking sites | — | CSV | Backlinked URLs (يعوّض بيانات Semrush الناقصة — RISK-02) |
| SC-10 | Settings → **نوع الخاصية والمستخدمون** (الأدوار فقط) | — | لقطة | G-04، G-11 |

## ما سنستخرجه (Baseline)
Top queries · Top pages · Top landing pages · Backlinked URLs · Indexed URLs · High-impression pages · High-click pages · خط أساس CWV — ثم نغذي بها [`SEO-MIGRATION-MAP.md`](SEO-MIGRATION-MAP.md).

## الجداول (تُملأ عند وصول البيانات)
| المؤشر | القيمة | التاريخ | المصدر |
|---|---|---|---|
| إجمالي النقرات (16 شهرًا) | MISSING | — | SC-05 |
| إجمالي الظهور (16 شهرًا) | MISSING | — | SC-05 |
| عدد الصفحات المفهرسة | MISSING | — | SC-06 |
| CWV Mobile (LCP / INP / CLS) | MISSING | — | SC-08 |
