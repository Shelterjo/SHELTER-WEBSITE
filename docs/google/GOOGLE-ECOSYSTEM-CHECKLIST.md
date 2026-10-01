# GOOGLE ECOSYSTEM CHECKLIST

> مأخوذ من السياسة ([`GOOGLE-ECOSYSTEM-POLICY.md`](GOOGLE-ECOSYSTEM-POLICY.md)) ومقسّم حسب المراحل. ⬜ لم يبدأ · ⏳ جارٍ · ✅ تم · ⏸ ينتظر الـOwner
> **آخر تحديث:** 2026-10-01

## Phase 01 — Discovery (الآن)
| # | البند | الحالة | يعتمد على |
|---|---|---|---|
| GC-01 | توثيق السياسة | ✅ | — |
| GC-02 | GBP مصدر تشغيلي رسمي للفرعين (D-048) | ✅ موثق · ⏸ البيانات | G-01، G-02 |
| GC-03 | Source Priority محدث (D-047) | ✅ | — |
| GC-04 | تعريف Branch Data Sync (D-049) | ✅ | — |
| GC-05 | **أول Branch Data Sync Check** (SYNC-001) | ⏸ | G-01، G-02 |
| GC-06 | جرد التتبع في الموقع القديم (GA4، GTM، Site Kit، HubSpot، WPCode — كشف التكرار) | ⏸ | AC-01، G-06، G-07 |
| GC-07 | Search Console Baseline | ⏸ | SC-01 → SC-10 |
| GC-08 | Lighthouse Baseline (موبايل + ديسكتوب) للصفحات الأساسية القديمة | ⏸ | AC-01 |
| GC-09 | جرد Schema القديمة الفعلية (مقارنة بنتائج Semrush) | ⏸ | AC-01 |

## Architecture / Design (قبل البناء)
| # | البند | الحالة |
|---|---|---|
| GC-10 | قرار الجذر + x-default (ROOT-01) | ⏳ `13` |
| GC-11 | خطة canonical وhreflang | ⏳ مسودة في `13` §3–§4 |
| GC-12 | Local SEO Architecture لكل فرع (title، meta، schema، URL، روابط داخلية، محتوى الموقع، إحداثيات، رابط GBP) | ⬜ |
| GC-13 | GA4 Measurement Plan معتمد (أسماء الأحداث) | ⏳ مسودة |
| GC-14 | قرار Site Kit / Native / GTM (GA-01) | ⬜ |
| GC-15 | قرار الخرائط (GA-02) والخطوط (GA-03) | ⬜ |
| GC-16 | مصدر بيانات واحد للفرع داخل الـCMS + حقل رابط GBP + آخر Sync | ⬜ |

## Pre-Launch (السياسة §53)
| # | البند | الحالة |
|---|---|---|
| GC-20 | Google Business Profiles verified | ⬜ |
| GC-21 | Branch data matched (Sync) | ⬜ |
| GC-22 | Search Console configured (بموافقة) | ⬜ |
| GC-23 | Sitemap ready (Canonical / Public / Indexable / Approved فقط) | ⬜ |
| GC-24 | robots.txt correct | ⬜ |
| GC-25 | Canonicals correct | ⬜ |
| GC-26 | hreflang correct | ⬜ |
| GC-27 | x-default decision correct | ⬜ |
| GC-28 | GA4 working | ⬜ |
| GC-29 | GTM working | ⬜ |
| GC-30 | No duplicate tracking | ⬜ |
| GC-31 | Schema validated (Rich Results) | ⬜ |
| GC-32 | Maps correct (روابط GBP الرسمية) | ⬜ |
| GC-33 | 404 reviewed | ⬜ |
| GC-34 | 301 migration ready (قفزة واحدة) | ⬜ |
| GC-35 | Core Web Vitals tested | ⬜ |
| GC-36 | Mobile tested | ⬜ |
| GC-37 | Staging blocked (Auth + noindex) | ⬜ |
| GC-38 | Old URLs mapped | ⬜ |

## Post-Launch
انظر [`POST-LAUNCH-GOOGLE-CHECKLIST.md`](POST-LAUNCH-GOOGLE-CHECKLIST.md).
