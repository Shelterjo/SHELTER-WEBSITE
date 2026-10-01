# POST-LAUNCH GOOGLE CHECKLIST

> السياسة §54–§55 · **لا يُستخدم قبل الإطلاق.**

## جدول المراقبة

| الفحص | Day 1 | Day 3 | Day 7 | Day 14 | Day 30 |
|---|---|---|---|---|---|
| Indexing (Search Console → Pages) | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Sitemap status | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| 404 (Search Console + سجل الخادم) | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Redirects (عيّنة من خريطة النقل — قفزة واحدة → 200) | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Canonical errors | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Coverage issues | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| hreflang errors | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Core Web Vitals | — | — | ⬜ | ⬜ | ⬜ |
| Traffic vs Baseline (Clicks / Impressions) | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| Landing pages | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| GA4 data flowing + Events (DebugView / Realtime) | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| No duplicate tracking | ⬜ | — | ⬜ | — | ⬜ |
| Branch Data Sync (GBP ↔ Website ↔ Schema) | ⬜ | — | ⬜ | — | ⬜ |
| Rich Results / Schema errors | ⬜ | — | ⬜ | — | ⬜ |
| Staging still blocked | ⬜ | — | ⬜ | — | ⬜ |

## Traffic Drop Alert (§55)
عند هبوط كبير: **لا افتراض للسبب.** فحص بالترتيب: Redirects ← Indexing ← Canonicals ← robots.txt ← Sitemap ← hreflang ← Server errors ← Page performance ← Tracking errors ← ثم **Diagnosis مكتوب** يُعرض على الـOwner قبل أي إجراء.
