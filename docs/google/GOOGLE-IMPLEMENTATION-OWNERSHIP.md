# GOOGLE FULL IMPLEMENTATION & INTEGRATION OWNERSHIP

> **المصدر:** الـOwner — 2026-10-01 (D-051). ملحق إلزامي لـ[`GOOGLE-ECOSYSTEM-POLICY.md`](GOOGLE-ECOSYSTEM-POLICY.md).
> **الخلاصة:** في مرحلة التنفيذ، وبعد منح الصلاحيات، الفريق التقني (Claude) هو **ARCHITECT + IMPLEMENTER + INTEGRATOR + TESTER** لمنظومة Google كاملة: **CONFIGURE → CONNECT → IMPLEMENT → TEST → VERIFY → DOCUMENT**.
> الـOwner يعطي المعلومات والموافقات والصلاحيات فقط.
> **الحالة الآن:** Phase 01 (Discovery) — **لا تنفيذ الآن**. هذا الملف يحدد طريقة العمل عند الوصول للتنفيذ.

---

## A. نص السياسة (كما اعتمدها الـOwner)

1. **منظومة واحدة مترابطة:** Website ↕ Google Tag Manager ↕ Google tag ↕ GA4 ↕ Search Console ↕ Google Business Profile ↕ Google Maps ↕ Structured Data / Schema ↕ Sitemap ↕ Google Search ↕ PageSpeed / Core Web Vitals. لا تُعامل كل خدمة كجزيرة. تصميم **Google Measurement & Search Architecture** كاملة.

2. **تنفيذ وليس توصيات:**
   - عند منح الصلاحيات لا يُطلب من الـOwner "أنشئ Property" أو "انسخ الكود". إذا سمحت الأدوات والصلاحيات، ينفذ الفريق بنفسه.
   - يتوقف فقط عند خطوة تتطلب: Login · 2FA · Ownership confirmation · Billing · Legal acceptance · Google security confirmation. عندها يقول للـOwner بالضبط ماذا يفعل، ثم يكمل الباقي.

3. **فحص الموجود أولًا:** قبل إنشاء أي Property أو Container، يُجرد كامل الموجود:
   - GA4 properties و Web Data Streams و Google tags.
   - GTM containers.
   - Search Console properties و Verification methods.
   - Site Kit configuration.
   - Google Business Profiles و Google Maps integrations.
   - Analytics IDs و GTM IDs.
   - Duplicate tracking و Hardcoded scripts و HubSpot tracking و Pixels/tags.

   **ممنوع إنشاء شيء جديد قبل التأكد أنه غير موجود أصلًا.**

4. **منع التكرار:**
   - فحص وجود GA4 عبر Site Kit + GA4 Hardcoded + GA4 عبر GTM في نفس الوقت.
   - مصدر واحد واضح لكل آلية تتبع.
   - أي إزالة أو تغيير على Production يحتاج موافقة.

5. **GTM احترافي** (إذا تقرر استخدامه):
   - Correct container · Environments عند الحاجة · Naming convention · Variables · Triggers · Tags · Folders.
   - Documentation · Preview testing · Versioning · Publishing workflow.

6. **هيكل GTM — أمثلة التسمية:**
   - **Tags:** `GA4 - Config` · `GA4 - Event - Menu View` · `GA4 - Event - Product View` · `GA4 - Event - Directions Click` · `GA4 - Event - Phone Click` · `GA4 - Event - Campaign Click`
   - **Triggers:** `TR - Menu View` · `TR - Directions Click` · `TR - Phone Click`
   - **Variables:** `VAR - Branch Name` · `VAR - Product Name` · `VAR - Category` · `VAR - Language` · `VAR - Market`
   - **Naming Standard موحد قبل التنفيذ.**

7. **Google tag:**
   - المقارنة بين GTM و Google tag مباشرة و Site Kit و Hardcoded.
   - لا أكثر من Implementation بدون سبب.
   - **One clean measurement architecture.**

8. **إعداد GA4 كاملًا** (عند الحاجة):
   - Property · Web Data Stream · Website URL صحيح · Time zone · Currency.
   - مراجعة Enhanced Measurement.
   - Internal traffic filtering و Developer traffic filtering.
   - Cross-domain tracking مستقبلًا إن احتجناه.
   - Data retention · Referral exclusions عند الحاجة.
   - Event architecture · Key Events · Debug testing.
   - **لا اعتماد للإعدادات الافتراضية بدون مراجعة.**

9. **Event Taxonomy قبل التنفيذ:**
   - أحداث للدراسة على الأقل: `menu_view` · `menu_category_click` · `menu_search` · `product_view` · `branch_view` · `directions_click` · `phone_click` · `whatsapp_click` · `social_click` · `campaign_view` · `campaign_click` · `event_view` · `article_view` · `language_switch` · `site_search` — وأي حدث إضافي مقترح.
   - **Business Meaningful Events فقط** (لا أحداث Scroll 10% ولا "أي ضغطة").

10. **Parameters ثابتة التسمية:** `branch_name` · `branch_id` · `product_name` · `product_category` · `campaign_name` · `event_name` · `language` · `market` · `destination` · `content_type`.

11. **Key Events:**
    - تُحدد مع الـOwner، مثل: Directions Click · Phone Click · WhatsApp Click · Campaign CTA.
    - لا تُعتبر كل ضغطة Conversion.

12. **Search Console:**
    - دراسة أفضل Property Architecture أولًا: Domain Property أو URL Prefix، والملكية الحالية، وطريقة التحقق.
    - إذا كان Domain Property مناسبًا: تجهيزه وربطه **بعد الموافقة**.

13. **التحقق عبر DNS (Cloudflare):**
    - تجهيز القيمة المطلوبة.
    - **لا تغيير DNS بدون موافقة.**
    - بعد الموافقة: التطبيق إن سمحت الصلاحيات، ثم التأكد أن الـProperty أصبحت Verified.

14. **ربط Search Console بـGA4** إن كان مناسبًا ومتاحًا، والتحقق أن Queries و Organic Search Data و Landing Pages قابلة للتحليل.

15. **Sitemap:**
    - بعد اعتماد الموقع الجديد: إنشاء Sitemap صحيحة، والتحقق منها، ثم إرسالها إلى Search Console.
    - متابعة الـStatus، لأن **Submit ≠ Success**.

16. **Indexing بعد الإطلاق:** تقرير بحالة الروابط: Indexed · Discovered · Crawled not indexed · Duplicate · Canonical issue · Blocked · 404 · Redirect.

17. **Google Business Profile:**
    - المصدر التشغيلي للفرعين (DRIVE و HOUSE)، والمقارنة مع الموقع.
    - لا تعديل إلا بموافقة. **عند الموافقة والصلاحية: التنفيذ يكون من الفريق** إن سمحت الأدوات.

18. **Google Maps:**
    - كل Branch Page مرتبطة بـGoogle Maps الصحيح: Get Directions · Maps URL · Correct branch · Correct location.
    - **لا نفس رابط Maps للفرعين.**

19. **Local SEO Sync (Branch Data Sync):**
    - المسار: GBP ↔ Website ↔ Schema ↔ Maps ↔ Search.
    - الحقول: Name · Address · Phone · Opening Hours · Special Hours · Coordinates · Website · Branch URL.

20. **تنفيذ الـSchema فعليًا** بعد اعتماد البيانات، ثم اختبارها: Organization · CafeOrCoffeeShop · LocalBusiness · BreadcrumbList · Article · BlogPosting · Event — حسب الحاجة.

21. **Rich Results Validation:**
    - تصحيح الـErrors ومراجعة الـWarnings.
    - وجود JSON-LD في الصفحة لا يعني أن الـSchema مكتملة.

22. **GTM + Consent:**
    - دراسة **Google Consent Mode** عندما يكون مطلوبًا، متوافقًا مع Analytics، وتقنيات الإعلان مستقبلًا، وCookie consent.
    - **لا Advertising Tracking بدون موافقة.**

23. **الخصوصية:**
    - أي تتبع يراعي Privacy Policy و Cookie Policy و Consent.
    - لا نشر لتتبع يحتاج إفصاحًا قانونيًا قبل تحديث صفحات الخصوصية.

24. **Debug Everything:**
    - الأدوات: GTM Preview · GA4 DebugView · Chrome DevTools · Network Requests · Browser Console · Tag diagnostics.
    - نتأكد من:
      - الـTags تعمل في الوقت الصحيح.
      - لا أحداث مكررة.
      - الـParameters صحيحة.
      - الأحداث تصل إلى GA4، والـKey Events تعمل.
      - لا أخطاء JavaScript.
      - سلوك الـConsent صحيح.

25. **اختبار مسارات حقيقية:**
    - Home → Menu
    - Menu → Category → Product
    - Home → Locations → DRIVE → Directions
    - Home → Locations → HOUSE → Directions
    - Campaign → CTA
    - Blog → Article
    - Language switch
    - والتأكد أن Analytics تسجل المسار المتوقع.

26. **Analytics Validation Report** بالأعمدة: Event · Expected Trigger · Actual Trigger · Parameters · GA4 Received? · Duplicate? · Status (**PASS / FAIL**).

27. **GTM Version Control:**
    - Version باسم واضح، مثل `SHELTER Website - Initial Production Tracking`، مع Notes.
    - لا أسماء مبهمة.

28. **Production Approval:**
    - مسموح دون موافقة مسبقة: Configure · Build · Test · Prepare.
    - التغييرات الحساسة ذات الأثر المباشر (DNS · GTM Publish · Major GA4 change · Search Console ownership · Business Profile edit) ← **Summary أولًا ← موافقة ← تنفيذ**.

29. **لا عمل يدوي على الـOwner إذا أمكن الأتمتة.** يُطلب من الـOwner فقط ما يحتاج: Authentication · Approval · 2FA · Account ownership · Billing approval · Legal confirmation · Security confirmation.

30. **توثيق نهاية الإعداد:** GOOGLE-ARCHITECTURE.md · GA4-CONFIGURATION.md · GA4-EVENT-DICTIONARY.md · GTM-CONTAINER-REGISTER.md · SEARCH-CONSOLE-SETUP.md · GOOGLE-BUSINESS-PROFILE-SYNC.md · SCHEMA-REGISTER.md · SITEMAP-REGISTER.md · GOOGLE-TEST-REPORT.md

31. **Owner Dashboard (اقتراح إن أمكن بدون ثقل):**
    - داخل إدارة الموقع، يعرض: Visits · Organic traffic · Top pages · Menu views · Product views · Directions clicks · Branch interest · Campaign performance · Blog performance.
    - بدون APIs ثقيلة أو مدفوعة إلا بموافقة.

32. **Final Google Acceptance Test** — لا اكتمال حتى يتحقق كل ما يلي:
    - GA4 connected · GTM connected · Google tag correct.
    - Search Console verified · Sitemap submitted · Indexing ready.
    - Schema valid · Branch Maps correct · Business Profiles consistent.
    - Events tested · Key Events tested · No duplicate analytics · Consent reviewed.
    - Mobile performance tested · Documentation complete.

33. **القاعدة النهائية:**
    - الإعداد الموجود يُفحص قبل إنشاء بديل.
    - التكرار لا يُحذف بدون موافقة.
    - **أقل صلاحية لازمة.**
    - تغيير Production: خطة ← موافقة ← تنفيذ ← اختبار ← توثيق.

---

## B. مصفوفة القدرة التنفيذية (واقعية — تُحدّث عند التنفيذ)

> ما يمكن للفريق تنفيذه بنفسه، وبأي صلاحية (أقل صلاحية لازمة)، وما يبقى على الـOwner حتمًا.
> **الحالة التقنية اليوم (2026-10-01):**
> - خدمات `googleapis.com` قابلة للوصول من بيئة العمل.
> - `api.cloudflare.com` محجوب، ويحتاج إضافته للـAllowed domains.
> - لا يوجد Connector مباشر لـGA4 أو GTM أو Search Console في هذه الجلسة. التنفيذ يكون عبر الـAPIs الرسمية بمفتاح **Service Account** يُحفظ في إعدادات البيئة (**ليس في المحادثة**).

| المهمة | يمكن للفريق تنفيذها؟ | الصلاحية الأقل اللازمة | خطوة حتمية على الـOwner |
|---|---|---|---|
| جرد GA4 (Properties، Streams، Key events، الإعدادات) | ✅ عبر Google Analytics Admin API | Service Account بدور **Viewer** على الحساب/الـProperty | إضافة بريد الـService Account كمستخدم (يحتاج تسجيل دخولك) |
| إعداد GA4 (Stream، Key events، Custom definitions، Retention، Filters) | ✅ معظمها عبر Admin API | دور **Editor** على الـProperty — بعد موافقة | رفع الدور بعد الموافقة |
| ربط Search Console ↔ GA4 | ⚠️ قد يتطلب واجهة GA4 (يُتحقق عند التنفيذ) | Admin على GA4 + Owner/Full على Search Console | قد تحتاج ضغطة في الواجهة |
| جرد GTM (Containers، Tags، Triggers، Variables، Versions) | ✅ عبر Tag Manager API | **Read** على الـContainer | إضافة الـService Account |
| بناء Workspace في GTM + Preview | ✅ | **Edit** | — |
| نشر نسخة GTM (Publish) | ✅ تقنيًا | **Publish** — **فقط بعد Summary وموافقة** (§28) | الموافقة |
| جرد Search Console (Properties، Sitemaps، Performance، URL Inspection) | ✅ عبر Search Console API | مستخدم **Restricted / Full** | إضافة الـService Account |
| إرسال Sitemap | ✅ | **Full** أو **Owner** — بعد موافقة | الموافقة |
| التحقق من Domain Property عبر DNS | ✅ نجهز القيمة؛ التطبيق عبر Cloudflare API | Cloudflare Token بصلاحية **DNS Edit للزون فقط** — **بعد موافقة** (D-044) | إنشاء الـToken + الموافقة + أي تأكيد أمني من Google |
| إضافة مالك/تغيير ملكية Search Console | ❌ نترك للـOwner | — | Ownership confirmation |
| قراءة بيانات GBP | ⚠️ الـBusiness Profile APIs تتطلب **طلب وصول معتمد من Google** لمشروع Google Cloud | — | تقديم طلب الوصول (أو بديل مجاني: روابط + لقطات — D-043) |
| تعديل GBP | ⚠️ نفس شرط الـAPI أعلاه؛ وإلا يُنفَّذ يدويًا بإرشاد دقيق | Manager على الملف (بعد موافقة) | الموافقة + احتمال 2FA |
| Schema + Sitemap + robots + Canonical + hreflang في الموقع الجديد | ✅ بالكامل (جزء من البناء) | وصول للكود والـStaging | — |
| اختبارات Playwright + DevTools + Lighthouse + Rich Results | ✅ | محلي | — |
| Google Consent Mode | ✅ تنفيذ داخل الموقع + GTM | — | قرار سياسة الخصوصية (R11) |

**مبادئ الأمان:**
- مفتاح الـService Account يُحفظ في إعدادات البيئة فقط. **لا يُرسل في المحادثة.**
- الصلاحيات تبدأ Viewer/Read، وتُرفع للـEditor أو Publish فقط عند الحاجة وبموافقة، ثم تُخفّض بعد الإطلاق.
- كل تغيير على Production يُسجَّل في `GOOGLE-TEST-REPORT.md` و`DECISION-LOG`.

## C. ربط وثائق نهاية الإعداد (§30) بالوثائق الحالية

| وثيقة نهاية الإعداد | تتطور من |
|---|---|
| GOOGLE-ARCHITECTURE.md | `GOOGLE-INTEGRATION-ARCHITECTURE.md` |
| GA4-CONFIGURATION.md | جديدة — عند التنفيذ |
| GA4-EVENT-DICTIONARY.md | `GA4-MEASUREMENT-PLAN.md` |
| GTM-CONTAINER-REGISTER.md | `GTM-TAG-REGISTER.md` |
| SEARCH-CONSOLE-SETUP.md | `SEARCH-CONSOLE-BASELINE.md` |
| GOOGLE-BUSINESS-PROFILE-SYNC.md | `BRANCH-DATA-SYNC.md` + `GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md` |
| SCHEMA-REGISTER.md | جديدة — عند البناء |
| SITEMAP-REGISTER.md | جديدة — عند البناء |
| GOOGLE-TEST-REPORT.md | جديدة — عند الاختبار (يشمل Analytics Validation Report §26) |

## D. الخطوة الأولى عند الوصول للتنفيذ (وليس الآن)

**Google Inventory Audit** (§3) — قراءة فقط:
- الموقع القديم (بعد AC-01): Site Kit وWPCode وHubSpot وأي GA4 أو GTM.
- ثم GA4 وGTM وSearch Console بالصلاحيات الدنيا.

الناتج: جرد كامل + خريطة تكرار + توصية "مصدر واحد لكل آلية تتبع". **لا إنشاء ولا حذف قبل موافقتك.**
