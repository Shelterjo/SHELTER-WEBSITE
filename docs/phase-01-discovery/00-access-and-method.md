# 00 — منهجية الفحص وحدود الوصول (Phase 01)

**التاريخ:** 2026-10-01
**المرحلة:** PHASE 01 — DISCOVERY & OWNER INTERVIEW
**ما تم تغييره في أي نظام حي:** لا شيء. لم يتم لمس Cloudflare أو Cloudways أو DNS أو WordPress أو Semrush (قراءة فقط).

## 1. حدود الوصول — مهم

بيئة العمل السحابية (Cloud sandbox) لديها سياسة شبكة تحجب الوصول المباشر إلى:

| Host | النتيجة |
|---|---|
| `www.shelterjo.com` / `shelterjo.com` | محجوب (403 من الـEgress proxy) |
| `web.archive.org` | محجوب |
| `pagespeedonline.googleapis.com` | متاح، لكن الحصة المجانية المشتركة منتهية (429) |

النتيجة: **لم يتم زحف الموقع مباشرة** (لا HTML خام، لا Playwright، لا Lighthouse، لا قراءة Headers/Scripts/Images مباشرة).
DNS الخاص بـ`www.shelterjo.com` يحل إلى عناوين Cloudflare (IPv6 `2606:4700:...`) — يتوافق مع ما ذكرته (Cloudflare أمام Cloudways).

### كيف نفتح الوصول الكامل (مطلوب منك)
في إعدادات بيئة العمل (قائمة Cloud environment في أعلى الجلسة ← Edit ← Network access): أضف `shelterjo.com` و`www.shelterjo.com` إلى الـAllowed domains (أو اختر مستوى وصول أوسع). التفاصيل: https://code.claude.com/docs/en/claude-code-on-the-web
بعدها نستطيع تشغيل: Crawl كامل، Playwright، Lighthouse/Core Web Vitals، فحص الـHeaders والـScripts والـTracking، وجرد الصور الفعلي.

## 2. المصادر التي استُخدمت بدلًا من ذلك

| المصدر | ماذا أعطانا | درجة الموثوقية |
|---|---|---|
| **Semrush — Site Audit (Project 29223054)** | آخر زحف قام به Semrush للموقع: الصفحات، العناوين، الوصف، المشاكل التقنية | عالية للبيانات التقنية، بتاريخ آخر زحف |
| **Semrush — Organic / Position Tracking / Backlinks / Competitors** | الكلمات المفتاحية، الصفحات التي تحمل الترتيب، الروابط الخارجية | عالية (تقديرات Semrush) |
| **WebSearch (فهرس البحث)** | الصفحات المفهرسة + ملخصات محتواها | متوسطة — ملخصات وليست نصًا خامًا |
| **مصادر خارجية (Maps / Social / Directories)** | ما يقوله الآخرون عن SHELTER | منخفضة — `PENDING OWNER VERIFICATION` دائمًا |

## 3. الأدوات المطلوبة في الـMaster Prompt — حالة التوفر

| الأداة | الحالة في هذه الجلسة | البديل المستخدم |
|---|---|---|
| `/superpowers:brainstorming` | غير مثبتة في هذه الجلسة | نقاش منظم بخيارات A/B/C + Decision Log |
| `/ui-ux-pro-max` | متاحة (`anthropic-skills:ui-ux-pro-max`) | ستُستخدم في مرحلة UI/UX فقط — ليس الآن |
| Frontend Design | متاحة (`anthropic-skills:frontend-design`) | مرحلة التصميم فقط |
| Playwright | Chromium موجود محليًا، لكن الموقع محجوب | بعد فتح الوصول |
| Chrome DevTools (MCP) | غير متاحة | Lighthouse عبر Chromium/PSI بعد فتح الوصول |
| Context7 | غير متاحة | توثيق رسمي عند الحاجة |
| SearchFit SEO | غير متاحة | **Semrush MCP** (متصل بحسابك) |
| Code Review | متاحة (`/code-review`) | بعد كل Milestone تطوير |
| Firecrawl | غير متاحة | Semrush Site Audit + بعد فتح الوصول: Crawl مباشر |

## 4. قاعدة الحقيقة

كل معلومة في ملفات هذه المرحلة حالتها **`PENDING OWNER VERIFICATION`** حتى لو جاءت من الموقع الحالي. لم تُعتمد أي معلومة ولم تُعتمد أي صورة.
