# SHELTER COFFEE — GOOGLE ECOSYSTEM POLICY (سياسة إلزامية)

> **المصدر:** الـOwner — 2026-10-01 (D-046). النص أدناه هو السياسة كما اعتمدها الـOwner، وتُطبق طوال المشروع وليس فقط في مرحلة SEO.
> **ملخص القواعد الذهبية (§57):**
> - DO NOT CHANGE GOOGLE DATA WITHOUT OWNER APPROVAL.
> - DO NOT USE GOOGLE PHOTOS WITHOUT OWNER APPROVAL.
> - DO NOT USE PAID API QUOTA WITHOUT OWNER APPROVAL.
> - DO NOT TRUST THIRD-PARTY DATA OVER OFFICIAL GOOGLE DATA WITHOUT A REASON.
> - **OWNER IS FINAL AUTHORITY.**
>
> **ملحق إلزامي:** [`GOOGLE-IMPLEMENTATION-OWNERSHIP.md`](GOOGLE-IMPLEMENTATION-OWNERSHIP.md) — التنفيذ الكامل (Configure → Connect → Implement → Test → Verify → Document) مسؤولية الفريق عند منح الصلاحيات (D-051).
> **الملفات التنفيذية للسياسة:** [`GOOGLE-ECOSYSTEM-CHECKLIST.md`](GOOGLE-ECOSYSTEM-CHECKLIST.md) · [`GOOGLE-INTEGRATION-ARCHITECTURE.md`](GOOGLE-INTEGRATION-ARCHITECTURE.md) · [`GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md`](GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md) · [`BRANCH-DATA-SYNC.md`](BRANCH-DATA-SYNC.md) · [`SEARCH-CONSOLE-BASELINE.md`](SEARCH-CONSOLE-BASELINE.md) · [`GA4-MEASUREMENT-PLAN.md`](GA4-MEASUREMENT-PLAN.md) · [`GTM-TAG-REGISTER.md`](GTM-TAG-REGISTER.md) · [`SEO-MIGRATION-MAP.md`](SEO-MIGRATION-MAP.md) · [`POST-LAUNCH-GOOGLE-CHECKLIST.md`](POST-LAUNCH-GOOGLE-CHECKLIST.md)

---

## 1. Google Ecosystem Scope
سنعتمد وندير التكامل مع: Google Business Profile · Google Maps · Google Search · Google Search Console · Google Analytics 4 (GA4) · Google Tag Manager (GTM) · Google Business Profile Insights إن توفرت · Google PageSpeed Insights · Lighthouse / Chrome UX related data · Google Structured Data / Rich Results validation · Google indexing · Google Sitemap submission · Google Local SEO.
**الهدف:** أن تكون جميع معلومات SHELTER على Website و Google و Google Maps و Schema و Search Console و Analytics متناسقة وصحيحة وقابلة للقياس.

## 2. Owner Is Final Authority
الـOwner هو المرجع النهائي لأي قرار تجاري أو معلومة تتعارض بين المصادر.
**Source Priority:** 1) OWNER APPROVED DATA · 2) OFFICIAL GOOGLE BUSINESS PROFILE · 3) OFFICIAL SHELTER WEBSITE · 4) OFFICIAL SHELTER SOCIAL ACCOUNTS · 5) OTHER SOURCES.
عند التعارض: لا تغيير تلقائي. يُعرض: Source A · Source B · Difference · Recommended correction — ثم انتظار الموافقة.

## 3. Official Google Business Profiles
فرعان رسميان على Google: **SHELTER COFFEE DRIVE** و **SHELTER COFFEE HOUSE**.
الـGoogle Business Profile الرسمي لكل فرع = **OFFICIAL OPERATIONAL SOURCE** لـ: Official branch name · Address · Map pin · Google Maps URL · Opening hours · Special hours · Phone number shown publicly · Website URL · Business category · Business status · Location data.

## 4. Always Verify Branch Data From Google
عند بناء أو تعديل أي صفحة Branch: التحقق أولًا من الـGBP الرسمي، والمقارنة بين Website Data و GBP و Structured Data و Owner-approved data، وإنتاج **BRANCH DATA CONSISTENCY CHECK**.

## 5. GBP Does Not Override Owner
إذا احتوى Google معلومة مختلفة عن معلومة اعتمدها الـOwner: لا يُعتبر Google صحيحًا تلقائيًا ← **CONFLICT — OWNER REVIEW REQUIRED** مع توضيح الفرق.

## 6. Google Images Rule
وجود صورة على GBP / Maps / Reviews / Search Results لا يعني السماح باستخدامها. كل صورة تخضع لـ**OWNER IMAGE APPROVAL** وتُعرض على الـOwner قبل الاستخدام.

## 7. Branch Data Sync Check
عملية **BRANCH DATA SYNC CHECK** تقارن: Google Business Profile ↔ Website ↔ Schema ↔ Contact pages ↔ Branch pages — في: Branch name · Address · Phone · Opening hours · Special hours · Maps URL · Coordinates · Website URL · Business category.

## 8. When To Run Branch Data Sync
أثناء Discovery · قبل Launch · بعد Launch · بعد تغيير ساعات العمل · بعد تغيير رقم الهاتف · بعد نقل فرع · بعد تغيير اسم فرع · بعد إضافة فرع جديد · بعد تغيير Google Business Profile.

## 9. Special Hours
Google Special Hours مهمة (رمضان، العيد، العطل الرسمية، المناسبات الخاصة، الإغلاق المؤقت). تُقارن مع الموقع؛ إذا Google محدث والموقع غير محدث ← عرض الفرق، ولا تغيير تلقائي على Production.

## 10. Local SEO
لكل Branch: Official name · Address · Phone · Opening hours · Google Maps · Page title · Meta description · LocalBusiness / CafeOrCoffeeShop schema · Branch URL · Internal linking · Location content · Coordinates · GBP URL.

## 11. NAP Consistency
Name · Address · Phone متناسقة بين Website و GBP و Schema و Contact page و Branch pages. أي اختلاف يُسجَّل.

## 12. Google Search Console
المصدر الأساسي للحقيقة عن أداء SHELTER في Google Search. لا اعتماد على Semrush أو أدوات خارجية بدلًا منه إذا توفرت بياناته. يُستخدم لـ: Queries · Pages · Clicks · Impressions · CTR · Average Position · Devices · Countries · Search appearance · Indexed pages · Excluded pages · Sitemaps · Core Web Vitals · Links.

## 13. Search Console Access
**READ-ONLY / RESTRICTED** في البداية. لا تعديل لـ: Sitemaps · Removals · Settings · Users · Ownership · Disavow · URL removal — إلا بموافقة.

## 14. Search Console Migration Baseline
قبل إطلاق الموقع الجديد: Baseline يحفظ Top queries · Top pages · Top landing pages · Backlinked URLs · Indexed URLs · High-impression pages · High-click pages — ويُستخدم في SEO MIGRATION PLAN.

## 15. SEO Migration
أي URL قديم لديه Traffic / Impressions / Clicks / Backlinks / Rankings لا يُحذف عشوائيًا. يُحدد له: **KEEP · REBUILD · REDIRECT · REMOVE**. أي URL يتغير ويملك قيمة ← **301 Redirect**.

## 16. Old /menu Page
إذا كانت `/menu` لديها Backlinks أو Rankings: لا حذف بدون Migration Plan. عند الانتقال إلى `/ar/jo/menu/` أو أي URL جديد: **301 Redirect** مع الحفاظ على الـIntent.

## 17. Google Indexing
بعد البناء: Crawlability · Indexability · Canonicals · Sitemap · robots.txt · noindex · duplicate pages · parameter URLs · language versions · staging pages.

## 18. Staging Must Not Index
أي Staging لا يظهر في Google: Authentication + noindex + Search engine blocking — و**robots.txt وحده ليس حماية أمنية**.

## 19. XML Sitemap
تشمل فقط الصفحات: Canonical · Public · Indexable · Approved. لا تشمل: Draft · Private · Staging · Redirected URLs · Duplicate URLs · Admin URLs.

## 20. Language SEO
العربية والإنجليزية مع **hreflang** صحيح؛ لكل صفحة مترجمة Arabic Alternate و English Alternate؛ ومراجعة **x-default** قبل اعتماد سلوك الجذر.

## 21. Root Domain
لا Redirect نهائي من `/` إلى `/ar/` قبل تحليل: hreflang · x-default · International SEO · Future countries · Future global domain · User experience · Google crawling — ثم عرض الخيارات وانتظار القرار.

## 22. Canonicals
Canonical صحيح لكل صفحة، خصوصًا: Arabic / English · Menu filters · Search pages · Categories · Blog archives · Events · Branch pages · Query parameters.

## 23. Structured Data
فقط إذا كان المحتوى حقيقيًا وظاهرًا للمستخدم. عند الحاجة: Organization · LocalBusiness · CafeOrCoffeeShop · BreadcrumbList · Article · BlogPosting · Event · FAQPage (إذا كانت شروط الاستخدام مناسبة والمحتوى موجودًا فعليًا). **ممنوع Schema Spam.**

## 24. Branch Schema
لكل Branch Page: توافق الـSchema مع GBP — Name · Address · Geo · OpeningHoursSpecification · Telephone · URL · SameAs · Image only if owner approved.

## 25. Google Analytics 4
GA4 نظيف ومنظم؛ لا Events عشوائية؛ **MEASUREMENT PLAN** قبل التنفيذ.

## 26. Core GA4 Events (للدراسة — الأسماء النهائية تُعرض قبل الاعتماد)
menu_view · menu_category_click · product_view · branch_view · directions_click · phone_click · whatsapp_click · social_click · campaign_view · campaign_click · event_view · blog_view · language_switch · search_use

## 27. Event Documentation
لكل Event: Event Name · Trigger · Parameters · Purpose · Page · Destination · Business Value.

## 28. Google Tag Manager
GTM لتنظيم التتبع إن كان مناسبًا — لا يتحول لمستودع Scripts عشوائية. كل Tag: Documented · Named clearly · Justified · Tested.

## 29. GTM Naming
مثال: `GA4 - Event - Menu View` · `GA4 - Event - Directions Click`. ممنوع: `Tag 1` · `New Tag` · `Test2`.

## 30. Debug Mode
قبل نشر أي تغيير GA4/GTM: GTM Preview · GA4 DebugView · Browser Network · Console — والتأكد من عدم إرسال Event مرتين.

## 31. Duplicate Tracking
فحص القديم والجديد عن: GA4 loaded twice · GTM duplicated · Site Kit duplicate scripts · Plugin analytics · Hardcoded analytics · HubSpot analytics. **لا Double Counting.**

## 32. Site Kit
إذا كان مستخدمًا: ماذا يدير (Search Console؟ GA4؟ Tag Manager؟ PageSpeed؟). لا افتراض أنه سيُستخدم في الموقع الجديد. مقارنة **Site Kit vs Native integration vs GTM-based integration** (Performance · Maintainability · Permissions · Tracking quality) ثم توصية.

## 33. GBP Tracking
روابط Google Maps من الموقع ← Track **Directions Click**، بدون Tracking parameters تكسر رابط Maps.

## 34. Google Maps
لكل فرع: رابط Google Maps الرسمي الصحيح. لا Pin يدوي إذا وُجد GBP رسمي.

## 35. Map Embeds
لا Map Embed ثقيل في كل صفحة تلقائيًا. مقارنة: Static map preview · Lazy-loaded embed · Simple directions button (Performance · UX · Privacy) واختيار الأخف ما لم تكن الخريطة ضرورية.

## 36. Performance
مراقبة أثر GA4 · GTM · Maps · YouTube · Google Fonts · reCAPTCHA · Site Kit على LCP · INP · CLS · JavaScript execution · Network requests.

## 37. Google Fonts
دراسة Self-hosting للأداء والخصوصية؛ لا عائلات/أوزان كثيرة بلا داعٍ.

## 38. Google Maps API
لا API Key غير مقيد: HTTP referrer restrictions · API restrictions · Usage limits. لا Secret في Front-End.

## 39. Google PageSpeed
PageSpeed / Lighthouse لقياس Mobile · Desktop · Accessibility · SEO · Best practices — بدون مطاردة 100 على حساب الـUX. الأهم: Real User Experience · Core Web Vitals · Fast interaction.

## 40. Core Web Vitals
مراقبة LCP · INP · CLS، وتوثيق Baseline قبل وبعد إعادة البناء.

## 41. Google Rich Results
فحص الـStructured Data بأدوات Google. أي Error يُصلح قبل Launch إذا تعلق بمحتوى منشور. الـWarnings تُقيَّم.

## 42. GBP Reviews
تُقرأ لفهم تجربة العملاء — لا تُنقل (Name / Photo / Review text) للموقع إلا بعد Owner Approval و Rights / Usage Review.

## 43. Google Photos
Google Photo ≠ Approved Website Asset. كل صورة تحتاج Owner Approval.

## 44. GBP Changes
لا تعديل لأي GBP بدون موافقة. أي تغيير في Name · Address · Phone · Hours · Category · Website · Photos · Description يمر بـ: **Proposal → Owner Approval → Change**.

## 45. GBP Monitoring
عملية دورية مستقبلية تفحص: Branch name · Hours · Special hours · Phone · Website URL · Maps link · Business status — وتقارنها بالموقع.

## 46. Google Search Visibility
ليس فقط Ranking على اسم SHELTER، بل Intent حقيقي مثل: specialty coffee irbid · coffee shop irbid · drive thru coffee irbid · specialty coffee jordan — والعبارات العربية المناسبة. **بدون Keyword Stuffing.**

## 47. Knowledge Hub
بناء Topic Authority حول: Specialty Coffee · Coffee Beans · Brewing · V60 · Espresso · Grinding · Roasting — فقط بمحتوى صحيح ومعتمد.

## 48. Google + AI Search
دعم Google AI Overviews · Gemini · ChatGPT Search · Perplexity · Copilot عبر: Clear facts · Strong entity relationships · Structured content · Consistent brand data · Authoritative branch data · Schema · Internal linking.

## 49. GBP and AI Entity Data
بيانات GBP للفرعين تتطابق قدر الإمكان مع بيانات الموقع الرسمية، ليفهم Google والأنظمة الأخرى: **SHELTER COFFEE → Jordan → Irbid → DRIVE / HOUSE**.

## 50. No Paid Quota Without Approval
لا Paid Google APIs · Places API · Large API exports · Paid Search services بدون موافقة. قبل أي عملية مدفوعة يُعرض: Service · Purpose · Expected requests · Expected cost · Free alternative.

## 51. Search Console First
إذا توفرت البيانات في Search Console تُستخدم بدل أداة SEO خارجية مدفوعة. Google first-party data لها الأولوية.

## 52. GA4 First-party Data
إذا توفرت في GA4 تُستخدم لفهم الاستخدام الفعلي — لكن Analytics لا تقرر التصميم وحدها.

## 53. Before Launch — Google Checklist
انظر [`GOOGLE-ECOSYSTEM-CHECKLIST.md`](GOOGLE-ECOSYSTEM-CHECKLIST.md) — القسم Pre-Launch.

## 54. After Launch
مراقبة Day 1 · 3 · 7 · 14 · 30 — انظر [`POST-LAUNCH-GOOGLE-CHECKLIST.md`](POST-LAUNCH-GOOGLE-CHECKLIST.md).

## 55. Traffic Drop Alert
عند هبوط كبير بعد الـMigration: لا افتراض للسبب. فحص: Redirects · Indexing · Canonicals · robots.txt · Sitemap · hreflang · Server errors · Page performance · Tracking errors — ثم عرض Diagnosis.

## 56. Documentation
GOOGLE-INTEGRATION-ARCHITECTURE.md · GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md · BRANCH-DATA-SYNC.md · SEARCH-CONSOLE-BASELINE.md · GA4-MEASUREMENT-PLAN.md · GTM-TAG-REGISTER.md · SEO-MIGRATION-MAP.md · POST-LAUNCH-GOOGLE-CHECKLIST.md — **أُنشئت في هذا المجلد (2026-10-01).**

## 57. Important Final Rule
Google ليس مجرد أداة Analytics — هو جزء أساسي من Local SEO · Search visibility · Branch accuracy · Performance · Analytics · Migration · AI discoverability. (القواعد الذهبية أعلى الملف.)

## 58. ما طُلب الآن (2026-10-01) — الحالة
| # | المطلوب | الحالة |
|---|---|---|
| 1 | إضافة السياسة للتوثيق | ✅ هذا الملف |
| 2 | Google Ecosystem Checklist | ✅ `GOOGLE-ECOSYSTEM-CHECKLIST.md` |
| 3 | GBP مصدر رسمي للفرعين | ✅ `GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md` + D-048 |
| 4 | تحديث Source Priority | ✅ D-047 |
| 5 | Branch Data Sync Check | ✅ `BRANCH-DATA-SYNC.md` + D-049 |
| 6 | تسجيل Search Console وGA4 وGTM كتكاملات مطلوبة لاحقًا | ✅ `GOOGLE-INTEGRATION-ARCHITECTURE.md` + D-050 |
| 7 | لا API مدفوع | ✅ لم يُستخدم |
| 8 | لا تغيير لأي Google property | ✅ لم يُلمس شيء |
| 9 | المعلومات والصلاحيات المطلوبة لاحقًا | ✅ `GOOGLE-INTEGRATION-ARCHITECTURE.md` §6 |
| 10 | العودة لـDiscovery | ⏳ R2P |
