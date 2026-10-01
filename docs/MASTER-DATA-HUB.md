# SHELTER MASTER DATA HUB + CHANNEL SYNC — First Deliverable (14 items)

> **المتطلب:** `OWNER APPROVED · FROZEN · P0 · CORE ARCHITECTURE` (M33، 2026-10-01) — المتطلبات MDH-001…MDH-036.
> **حالة هذه الوثيقة:** `DRAFT — PENDING OWNER REVIEW`. **لا تنفيذ** قبل بوابات المشروع والـCloudways Audit.
> **المنصة محسومة:** [`ADR-001`](adr/ADR-001-platform.md) (Laravel 13 + MySQL على تطبيق Cloudways Flexible جديد، Blade) — يحسم DB-08.
> **أسماء الجداول والخدمات** هنا ملزمة من [`PLATFORM-ARCHITECTURE`](architecture/PLATFORM-ARCHITECTURE.md) §3.
> **العلاقة بالملحق السابق (M32):**
> - **Global Data Registry = جزء "العلامة والتواصل والإعدادات العامة" من هذا الـHub.** خريطته: [`docs/platform/GLOBAL-DATA-REGISTRY.md`](platform/GLOBAL-DATA-REGISTRY.md).
> - **Fact Registry = طبقة التحقق** فوق قيمه: [`docs/platform/FACT-REGISTRY.md`](platform/FACT-REGISTRY.md).
> - **نظام واحد لا نظامان** (G15-CF-09).

**المبدأ:** ONE SOURCE OF TRUTH → MULTIPLE CHANNELS. الـOwner يعدّل المعلومة **مرة واحدة**، والنظام يتكفل بالقنوات.

**أقسام الوثيقة:**
- **§1–§14:** المخرجات الأربعة عشر (M33 §29). أرقامها ثابتة لأن وثائق `docs/platform/` تحيل إليها.
- **§15–§19:** إكمال المطابقة مع M33 (جولة doc-fix — G15-TF-07): تدفق النشر التسعي · لا محررات مكررة · تجاوزات القنوات · مراقبات "يحتاج انتباه" · بوابات التفعيل.
- **§20:** سجل الوثيقة.

---

## 1. تدقيق البيانات المكررة حاليًا (Current duplicated data audit)
> لا موقع جديد بعد. التدقيق يشمل: المستودع (وثائق ونماذج)، والقنوات الخارجية المعروفة. الموقع القديم وGBP لم يُفحصا مباشرة لأن الوصول محجوب (PO-008، PO-009).

| البيان | أين يتكرر الآن | الخطر | الحكم |
|---|---|---|---|
| **ساعات العمل** | 15 ملفًا في المستودع:<br>- قرار D-020.<br>- مواصفة المنيو §9.<br>- `hours_logic_check.py` (قاموس `REGULAR` مكتوب في الكود).<br>- مولّد الـWireframes.<br>- وثائق Google.<br>- 03 و04 و09 و15.<br>خارجيًا: GBP (غير معروف)، والأدلة بساعات مختلفة: TripAdvisor 09:00–23:00، findglocal 08:00–03:00 (PO-041) | **عالٍ:** تضارب فعلي في القنوات | **المصدر = جدول الساعات في الـHub.** الوثائق تشير إليه. الكود والنماذج تقرأ من ملف بيانات واحد (§14) |
| **أرقام التواصل** | 15 ملفًا (D-057، 14-contact، التوظيف، الشراكات…). خارجيًا: GBP، والسوشال، والموقع القديم | متوسط | **المصدر = Contact Registry** |
| **حقائق العلامة** (2019، 20/04، الاسم) | 17 ملفًا. الموقع القديم يحمل 2018 و"منذ 2022" (مُستبدلة) | عالٍ: Schema وAbout | **المصدر = Brand Registry + Fact Registry** |
| **الأسعار** | المصدر الأصلي (xlsx، لا يُعدّل) ← Inventory v1.0 (xlsx + csv). النماذج تُولّد من الـcsv (مشتقة، مقبول). خارجيًا: المنيو القديم بأسعار أقدم (RISK-09)، وتطبيقات التوصيل (غير معروف)، وقائمة GBP (غير معروف) | عالٍ | **المصدر = Menu Master** (`price_fils`). لا نسخة أسعار في الموقع |
| **العناوين وروابط الخرائط** | ناقصة: إنجليزي وشارع DRIVE متضارب (CF-M-019، PO-010) | عالٍ | تُستكمل من الـOwner ثم تدخل الـHub. GBP يُقارن ولا يتغلب |
| **روابط السوشال** | 11-social-accounts-verification (أدلة متضاربة، CF-M-028) | متوسط | Social Registry بعد تحقق الـOwner |
| **الحملات والفعاليات** | لا يوجد بعد | — | Events/Campaigns من محرك التجارب (DX) |

## 2. كيانات الـMaster Data (Entities)
| الكيان | الحقول الأساسية | معرّف ثابت |
|---|---|---|
| **Brand** | `name_en` (SHELTER COFFEE) · `name_ar` (شلتر كوفي) · `founded_year` (2019) · `anniversary` (20/04) · أوصاف رسمية AR/EN (قصيرة، طويلة) · `logo_media_id` | `BRAND` |
| **Branch** | `branch_code` (`DRIVE` · `HOUSE`) · `name_ar/en` · `status` (active / inactive / coming_soon / temporarily_closed) · `address_ar/en` · `geo` (lat/lng) · `maps_url` · `phone` (مرجع Contact) · `whatsapp` (مرجع) · `services[]` · `timezone` (`Asia/Amman`) · `country/city` | `BR-DRIVE` · `BR-HOUSE` (Slug: `drive`/`house`) |
| **Hours** (كيان مستقل) | لكل فرع خمسة أنواع (M33 §6):<br>- **Regular** (يوم، فتح، إغلاق، عدة فترات، عبر منتصف الليل).<br>- **Special** و**Holiday** (نطاق تاريخ، ساعات أو مغلق، سبب).<br>- **Temporary Closure** (من/إلى/سبب).<br>- **Emergency Closure** (مفعّل الآن + رسالة عامة AR/EN) | `HRS-<branch>` |
| **Contact** | `phone_main` (0799009436) · `whatsapp` · `phone_feedback` (0799338445) · `phone_franchise` (0799338445) · `phone_catering_b2b` (0799530383) · `emails[]` (منشور/غير منشور) · صيغ العرض AR/EN. **التخزين:** صف لكل نقطة في `contact_points` حسب `kind` | `CT-*` |
| **Social** | المنصة · الرابط · `verified` · `published` | `SO-*` |
| **Menu** | المنتج (`PRD-#####` — الهوية المجمّدة) · الفئة (`CAT-###`) · القسم الفرعي (`DSC-`) · `price_fils` (JOD، شامل الضريبة) · **تجاوز الفرع** (`product_branch_overrides`: سعر/توفر لكل فرع) · `availability` لكل فرع: **3 حالات عامة** `AVAILABLE` · `UNAVAILABLE_SHOW` · `UNAVAILABLE_HIDE` (CMS-015) **+ `UNKNOWN` داخلي** افتراضي حتى تأكيد الـOwner، لا يُعرض كحالة عامة (D-079، D-094) · `image_media_id` · الأوصاف · الشارات · الموسمية | `PRD-*` (مجمّد، لا يُعاد استخدامه) |
| **Events / Campaigns** | من محرك التجارب (DX): العرض النشط، والتواريخ، والفروع، والنص العام | `EXP-*` · `EVT-*` |
| **Global Website** | حقوق النشر · معلومات الـFooter · Organization data · الروابط القانونية | `GW-*` |
| **Fact** (طبقة تحقق) | `code` · المفتاح · القيمة · المصدر · الحالة (مفردات D-224 + `VERIFIED` — G14-CF-12: `APPROVED` · `VERIFIED` · `PENDING OWNER APPROVAL` · `PENDING VERIFICATION` · `MISSING` · `REJECTED` · `SUPERSEDED`) · اعتمد من · تاريخ التحقق · آخر مراجعة. **المسودة = `PENDING OWNER APPROVAL`، ونشر الـOwner بعد معاينة الأثر = `APPROVED`** (§15). التفاصيل: [`FACT-REGISTRY`](platform/FACT-REGISTRY.md) §3–§4 | `FACT-0001` (`FACT-####`، لا يُعاد استخدامه. **ليس `F-`** لأنه يتصادم مع قرارات المنيو المجمّدة F-01…F-21) |

**قاعدة الوراثة والتجاوز (Branch overrides):**
- كل قيمة على مستوى الفرع تعرض **"موروث من الأساس"** أو **"مُعدّل لهذا الفرع"**، مع زر **"إرجاع للأساس"** (Reset to Master).
- **اليوم:** لا تجاوزات أسعار (لا بيانات بفروقات بين الفروع — D-094). المعمارية جاهزة فقط.
- تجاوز **الفرع** (قيمة مختلفة لفرع) ≠ تجاوز **القناة** (قيمة مختلفة لقناة واحدة مثل Google — §17).

**التخزين (أسماء ملزمة — PLATFORM-ARCHITECTURE §3):**

| الكيان / الوظيفة | الجدول |
|---|---|
| Brand | `settings` مجموعة `brand.*` (`brand.name_en` · `brand.name_ar` · `brand.founded_year` · `brand.anniversary` · `brand.logo_media_id`…) — **كل قيمة مربوطة بـ`facts`** |
| Global Website | `settings` مجموعة `website.*` — مربوطة بـ`facts` |
| السوق | `markets` (`JO`) |
| Branch | `countries` → `cities` → `branches` |
| Hours | `branch_hours` (Regular) + `hours_exceptions` (`kind`: emergency · temporary · special · holiday — الأولوية مشتقة من النوع) |
| Contact · Social | `contact_points` (`scope` brand/branch · `kind` · `is_public`) · `social_links` |
| Menu | `menu_categories` · `menu_subcategories` · `products` · `product_prices` · `product_branch_overrides` |
| Events / Campaigns | `experiences` |
| Fact | `facts` |
| معرّفات القنوات الخارجية | `external_references` |
| تجاوز خاص بقناة | `channel_overrides` (§17) |
| حالة المزامنة + الطابور | `channel_sync_states` · `sync_jobs` |
| حالة الربط والرموز | `integrations` (بلا أسرار). الرموز نفسها مشفّرة في `settings` بتصنيف SENSITIVE |
| النسخ | `content_versions` (+ `reason`) |
| التدقيق | `audit_logs` (+ `channels_affected`) |
| "يحتاج انتباه" | `signals` (`kind` = ISSUE) |

## 3. خريطة مصدر الحقيقة (Source-of-truth map)
```
              ┌──────────────── SHELTER MASTER DATA HUB (Owner Dashboard) ─────────────────┐
              │ Brand · Branches · Hours · Contact · Social · Menu · Events/Campaigns (DX) │
              │ · Global Website   + Fact Registry (تحقق) + channel_overrides (§17)          │
              └─────┬──────────────┬──────────────┬───────────────┬──────────────┬──────────┘
                    │ يُولَّد       │ يُولَّد       │ يُولَّد        │ يُدفع (Adapter)│ يُدفع (مستقبلي)
             ┌──────▼─────┐ ┌──────▼─────┐ ┌──────▼─────┐ ┌───────▼──────┐ ┌─────▼───────────┐
             │  Website   │ │    SEO     │ │  Schema    │ │ Google Biz   │ │ POS · ERP · App │
             │ صفحات/منيو/│ │  metadata  │ │  JSON-LD   │ │ Profile/Maps │ │ Delivery ·      │
             │ Footer/    │ │ title/desc │ │  (مشتق)    │ │ قناة مُزامَنة │ │ دول أخرى        │
             │ Today Hours│ │ (Tokens)   │ │            │ │ (API رسمي)   │ │ (IDs ثابتة)     │
             └────────────┘ └────────────┘ └────────────┘ └──────────────┘ └─────────────────┘
```

**القنوات (M33 §2):** Website · Google Business Profile / Google Maps · Structured Data / Schema · **SEO metadata** · Branch pages · Contact pages · Menu pages · Event/Campaign surfaces · future POS/ERP · future ordering channels · future external platforms.
- **قنوات تُولَّد وقت العرض** (Website · SEO metadata · Schema): تقرأ من الـHub مباشرة، فلا نسخة بيانات فيها.
- **قنوات تُدفع إليها** (GBP · المستقبلية): عبر محوّل مستقل لكل قناة (MDH-014)، بطابور وحالة وأخطاء خاصة بها.
- **SEO metadata قناة:** أي اسم فرع أو هاتف أو عنوان في `title`/`description` يأتي **Token** من الـHub (مثل `{branch.name}`)، لا نصًا ثابتًا (SEO-035، MDH-013).

**السلطة الافتراضية (M33 §23):**
- **SHELTER MASTER DATA > نسخة أي قناة خارجية.**
- **GBP قناة مُزامَنة (Synced channel)، لا مصدر.** وصفه السابق "Official Operational Source" (D-048) **SUPERSEDED** بـM33 (MDH-002، G15-CF-01).
- **D-047** (ترتيب المصادر) يبقى **فقط** لتقييم القيم التي لم يعتمدها الـOwner بعد (أثناء الترحيل). بعد دخول القيمة الـHub بحالة APPROVED/VERIFIED تصبح هي المرجع، والقنوات نسخ.
- **أي تغيير خارجي يذهب إلى المراجعة (Review)، ولا يُعتمد تلقائيًا أبدًا** (§10). العرض: **EXTERNAL CHANGE DETECTED** (M33 §13) = **CONFLICT DETECTED** (M38 §7).
- الاستثناء الوحيد: الـOwner يختار صراحة **"اعتماد التغيير الخارجي"** (Adopt — §10).

> هذا **يحل محل** وصف GBP كـ"Official Operational Source" في سياسة Google السابقة. انظر سجل التعارضات (G15-CF-01) وبانر [`GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH`](google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md).

## 4. مصفوفة قدرات القنوات (Channel capability matrix)
> هذه المصفوفة **تصميمية** (لكل نوع بيان). المصفوفة **الحية** لكل فرع × حقل في §4.1.

| البيان | Website | SEO metadata | Schema | Google Business Profile | POS/ERP/App/Delivery (مستقبلي) |
|---|---|---|---|---|---|
| اسم العلامة | ✅ قراءة | ✅ Token | ✅ Organization | ⚠️ الاسم حساس للتحقق (§5) | ✅ بالمعرّف |
| اسم الفرع | ✅ | ✅ Token | ✅ LocalBusiness | ⚠️ **يدوي موصى به** (§5) | ✅ |
| العنوان | ✅ | ✅ Token (عند الذكر) | ✅ PostalAddress | ⚠️ قد يعيد التحقق ← **يدوي موصى به** | ✅ |
| الموقع على الخريطة | ✅ رابط Maps | — | ✅ GeoCoordinates | ⚠️ يدوي (Pin) | — |
| الهاتف | ✅ | ✅ Token (عند الذكر) | ✅ telephone | ✅ API (`phoneNumbers`) | ✅ |
| الساعات العادية | ✅ | ⚠️ لا تُكتب في Meta افتراضيًا (تتقادم — اقتراح تقني) | ✅ openingHoursSpecification | ✅ API (`regularHours`) | ✅ |
| الساعات الخاصة والعطل | ✅ | — | ✅ (حسب الدعم) | ✅ API (`specialHours`) | ✅ |
| إغلاق مؤقت / طارئ | ✅ (بانر + حالة) | — | ✅ (ساعات خاصة) | ✅ API (`openInfo` حالة الإغلاق المؤقت) | ✅ |
| رابط الموقع | — | — | ✅ url | ✅ API (`websiteUri`) — **لا يُدفع قبل الإطلاق P11** (§19) | — |
| التصنيف (Category) | — | — | — | ✅ API (`categories`): يُدفع **فقط عند تغيير صريح من الـOwner وبتأكيد قوي**. القيمة الأولى من GBP بعد تأكيد الـOwner (لا اختراع) | — |
| الأوصاف الرسمية | ✅ About | ✅ الوصف الافتراضي | ✅ description | ✅ API (`profile.description`): وصف العلامة أو **Channel override** (§17) | — |
| الخدمات والسمات | ✅ | — | جزئي | ⚠️ API Attributes حسب ما تدعمه الفئة. **قيم YES المؤكدة فقط**، و`UNKNOWN` لا تُرسل أبدًا | — |
| المنيو والأسعار | ✅ **المصدر الوحيد للعرض** | ✅ Token (عند الذكر) | ✅ Menu | ⚠️ Food Menus API موجود. **يُقيّم لاحقًا، لا يُفعّل الآن** (قاعدة "لا منيو من Google بديلًا") | ✅ بنفس `PRD-*` |
| الحملات والفعاليات | ✅ (DX) | ✅ Token (عند الذكر) | ✅ Event عند الصحة | ⚠️ Posts API ← **اختياري لاحقًا، بموافقة** | — |
| المراجعات | — (قراءة في Reputation) | — | ❌ لا Review markup ذاتي | قراءة وردّ عبر API بموافقة (M32) | — |

**الرموز:** ✅ مدعوم آليًا · ⚠️ مدعوم بقيود أو يُفضّل يدويًا · ❌ ممنوع. حالة اليدوي في الـDashboard: **MANUAL ACTION REQUIRED** مع خطوات واضحة.

### 4.1 المصفوفة الحية في الـDashboard (لكل فرع × حقل — M33 §3)
**الأعمدة السبعة:**

| العمود | المعنى | المصدر |
|---|---|---|
| **FIELD** | الحقل في Google (`phoneNumbers`، `regularHours`…) | تعريف المحوّل (`capabilities()`) |
| **MASTER VALUE** | القيمة **الفعالة** للقناة: قيمة الـHub، أو قيمة `channel_overrides` مع وسم **OVERRIDDEN FOR GOOGLE** | الـHub + `channel_overrides` |
| **GOOGLE SUPPORTED?** | YES / NO / v4 (بطلب وصول) | تعريف المحوّل، يُعاد التحقق منه في P09 |
| **SYNC DIRECTION** | `MASTER → GOOGLE` · `READ/COMPARE ONLY` · `MANUAL` · `NONE` | إعداد لكل حقل؛ **تغييره قرار Owner مسجّل في `audit_logs`** (G15-CF-11) |
| **LAST SYNC** | آخر محاولة + آخر نجاح | `channel_sync_states` |
| **STATUS** | إحدى الحالات الست (§11) | `channel_sync_states` |
| **ERROR** | السبب بلغة الـOwner + الإجراء (Retry / Review / أعد الربط) | `sync_jobs.last_error` |

**التصنيف الافتراضي** (يُعاد التحقق منه من وثائق Google الرسمية عند P09):

| FIELD | GOOGLE SUPPORTED? | SYNC DIRECTION الافتراضي |
|---|---|---|
| الهاتف (`phoneNumbers`) | YES | `MASTER → GOOGLE` |
| الساعات العادية (`regularHours`) | YES | `MASTER → GOOGLE` |
| الساعات الخاصة والعطل (`specialHours`) | YES | `MASTER → GOOGLE` |
| الإغلاق المؤقت (`openInfo.status`) | YES | `MASTER → GOOGLE` بتأكيد خاص (يغيّر ظهور الفرع في Maps) |
| رابط الموقع (`websiteUri`) | YES | `READ/COMPARE ONLY` حتى الإطلاق، ثم `MASTER → GOOGLE` (§19) |
| التصنيف (`categories`) | YES | `MASTER → GOOGLE` **عند تغيير صريح فقط** + تأكيد قوي؛ غير ذلك `READ/COMPARE ONLY` |
| السمات (Attributes) | YES (حسب الفئة) | `MASTER → GOOGLE` لقيم YES المؤكدة فقط |
| الوصف (`profile.description`) | YES | `MASTER → GOOGLE` (أو Channel override — §17). القيمة اليوم MISSING (PO-016) |
| الاسم (`title`) | YES تقنيًا | `MANUAL` (قد يطلق إعادة تحقق؛ إرشادات Google تشترط الاسم الحقيقي) |
| العنوان (`storefrontAddress`) | YES تقنيًا | `MANUAL` (قد يطلق إعادة تحقق) |
| الـPin (`latlng`) | محدود | `MANUAL` |
| Food menus · Posts (v4) | v4 (بطلب وصول) | `NONE` الآن — كل واحد بقرار منفصل |
| Reviews (v4) | v4 (بطلب وصول) | خارج الـHub: وحدة Reputation (M32، الرد بموافقة) |
| الصور | — | `NONE` (صور معتمدة فقط، يدويًا — D-056) |

- **الحقل غير المدعوم:** يظهر `NOT SUPPORTED`، ومعه **MANUAL ACTION REQUIRED** بخطوات واضحة إن احتاج فعلًا منك.
- **المصفوفة التصميمية (§4) ≠ الحية:** الحية تقرأ `channel_sync_states` وتعرض القيمة الفعالة.

## 5. مراجعة قدرات تكامل Google (Business Profile)
| البند | ما تقوله المصادر الرسمية | التوصية |
|---|---|---|
| **API بيانات الموقع** | Business Information API (`mybusinessbusinessinformation.googleapis.com/v1`): `title` · `phoneNumbers` · `storefrontAddress` · `regularHours` · `specialHours` · `websiteUri` · `openInfo` · `categories` · `profile`. التحديث بـ`updateMask`، والقراءة بـ`readMask`. السمات عبر Attributes API ([Change log](https://developers.google.com/my-business/content/businessinformation/change-log)) | **مزامنة آلية:** الهاتف، والساعات العادية، والساعات الخاصة، والإغلاق المؤقت، والوصف، والسمات المؤكدة، ورابط الموقع (**بعد الإطلاق فقط** — §19). **التصنيف مدعوم في الـAPI:** يُدفع عند تغيير صريح منك فقط وبتأكيد قوي (§4.1). **يدوي (Manual Action) للاسم والعنوان والـPin:** تغييرها قد يطلق إعادة تحقق أو مراجعة من Google، فلا تُدفع آليًا |
| **تغييرات Google المقترحة** | `getGoogleUpdated` يعرض ما غيّرته Google أو المستخدمون | **أساس كشف التغييرات الخارجية** (§10) |
| **المنيو والمنشورات والمراجعات** | واجهات v4 (`foodMenus`، `localPosts`، المراجعات والردود) ما زالت موثقة ([v4 reference](https://developers.google.com/my-business/reference/rest)) | المراجعات والردود (M32، بموافقة). المنشورات والمنيو **لاحقًا** بقرار منفصل |
| **الوصول** | OAuth بصلاحية `business.manage`، وتفعيل الـAPI في Google Cloud، و**طلب وصول من Google** (الحصة قد تكون صفرًا حتى الموافقة) | **مجاني لكن يحتاج طلبًا** (PO-009 / I-04). **حتى الموافقة:** الحالة `PENDING INTEGRATION`، ولا Scraping |
| **بيئة الاختبار** | لا يوجد Sandbox رسمي لـGBP. `locations.patch` يقبل `validateOnly=true` (تحقق بلا حفظ) | **Staging = dry-run فقط** (diff + `validateOnly`)، ولا كتابة حقيقية من غير الإنتاج أبدًا (§19) |
| **التحقق قبل البناء** | الواجهات تتغير. يُعاد التحقق من الحقول والحصص عند التنفيذ (P09) | سطر في قائمة فحص P09 |

## 6. معمارية مزامنة المنيو
- **مصدر واحد:** جدول `products` بالمعرّف المجمّد `PRD-#####`. الأسعار بـ`price_fils` في `product_prices`. **الموقع لا يحمل نسخة أسعار:** يُولّد من المصدر عند النشر، ويُبطل الـCache.
- **التجاوز حسب الفرع:** `product_branch_overrides(product_id, branch_id, price_fils?, availability?)`. القيمة الفعالة = التجاوز إن وُجد، وإلا الأساس. الواجهة تعرض موروث/معدّل + إرجاع.
- **التوفر:** 3 حالات عامة (AVAILABLE · UNAVAILABLE_SHOW · UNAVAILABLE_HIDE — CMS-015) + `UNKNOWN` داخلي افتراضي. اليوم 382 قيمة `UNKNOWN` (191 × 2 — PO-023).
- **القنوات المستقبلية** (POS/ERP/Delivery/Google Food Menus):
  - **جدول ربط** `external_references(entity, entity_id, channel, external_id)`. موجود أصلًا في نموذج المنيو v0.4.
  - **لا منتج جديد لكل قناة.**
- **النشر:** تعديل السعر يمر بالخطوات التالية (التدفق الكامل في §15):
  1. Publish Guard.
  2. Change Impact: المنيو، والبحث، والـSchema، والقنوات المرتبطة.
  3. نسخة في `content_versions` (مع `reason` إلزامي لتغيير السعر).
  4. Audit في `audit_logs`.

## 7. معمارية مزامنة الساعات
**أولوية الحساب (M33 §7):**
1. Emergency Closure.
2. Temporary Closure.
3. Special/Holiday Hours.
4. Regular Hours.

**التخزين:** `branch_hours` (Regular) + `hours_exceptions` (`kind`: emergency · temporary · special · holiday).

**الخوارزمية الموحدة:**
- **دالة واحدة** `effective_hours(branch, date)` تستخدمها كل القنوات:
  - Website (مكون "اليوم" والحالة مفتوح/يغلق قريبًا/مغلق).
  - Schema.
  - مُحوّل Google.
- **الأساس:** امتداد لمنطق `hours_logic_check.py` (12/12 حالة ناجحة) مع إضافة مستوى الطوارئ والإغلاق المؤقت.

**الربط مع Google:**

| البيان في الـHub | يقابله في Google |
|---|---|
| Regular | `regularHours` |
| Special / Holiday / Temporary | `specialHours` (أيام محددة مغلق أو بساعات) |
| Temporary Closure | `openInfo.status = CLOSED_TEMPORARILY`، **بتأكيد خاص** لأنه يخفي الفرع في Maps |
| Emergency | Special hours لليوم + بانر الموقع |

- **الورديات بعد منتصف الليل** (DRIVE حتى 02:00): تُمثل بفترة إغلاق في اليوم التالي، حسب صيغة Google.
- **ممنوع** ساعات مكتوبة في أي صفحة.
- **محرر ساعات واحد** فقط (§16).

## 8. معمارية مزامنة الفروع
- كيان الفرع (`branches`) يملك المعرّف (`BR-DRIVE`) والـSlug المعتمد (`drive`).
- **مُحوّل Google:** يربط `BR-*` بـ`locations/{id}` عبر `external_references`.
- **حقول آلية:** الهاتف، والساعات، والإغلاق المؤقت، والوصف، ورابط الموقع (**بعد الإطلاق فقط** — §19).
- **حقول مدعومة بشروط:**
  - **التصنيف:** مدعوم في الـAPI، **ليس يدويًا**. يُدفع فقط عند تغيير صريح منك وبتأكيد قوي (§4.1).
  - **السمات:** حسب ما تدعمه الفئة. قيم YES المؤكدة فقط. السمة غير المدعومة = `NOT SUPPORTED`.
- **حقول يدوية مع تذكير وتحقق:** الاسم، والعنوان، والـPin (`MANUAL ACTION REQUIRED`).
- **فرع جديد:** `coming_soon` لا يظهر علنًا ولا يُرسل لأي قناة حتى التفعيل (المعمارية العالمية D-010).

## 9. تكامل الـSchema
- **لا إدخال بيانات مستقل للـSchema.** يُبنى آليًا من: Brand، وBranch، وHours، وContact، وEvent registries.
- **النتيجة:** النص الظاهر = البيانات المنظمة دائمًا.
- **الأنواع:** `Organization` · `CafeOrCoffeeShop`/`LocalBusiness` لكل فرع · `Menu` (من المنيو) · `Event` (للأحداث الصحيحة فقط).
- **ممنوع:** تقييمات ذاتية، أو أسعار عروض غير معتمدة.
- **إعدادات الـSchema** في الـDashboard (SCHEMA-010) = تفعيل واختيار النوع فقط، **بلا حقول قيم Business** (G15-CF-06).
- **فحص صحة الـSchema** (M32 Schema Health) يقارن الـJSON-LD المولّد بالـHub ("Schema uses old phone" — §18).

## 10. نموذج التعارض مع التغييرات الخارجية
**القاعدة:** **SHELTER MASTER DATA > أي نسخة خارجية.** التغيير الخارجي **لا يُعتمد تلقائيًا أبدًا**، ويذهب إلى المراجعة (M33 §13، M38 §7).

1. **كشف دوري** (مرة يوميًا + بعد كل نشر):
   - مقارنة Master ↔ Website (المولّد) ↔ Schema ↔ Google (عبر `get` و`getGoogleUpdated`).
2. **أي فرق = `OUT OF SYNC`، ويُصنّف** بالمقارنة مع آخر `payload_version` مدفوع:
   - **(أ) القناة متأخرة عنّا** (قيمة القناة = آخر مدفوع، ولم تصلها المزامنة الأحدث): ← **إعادة المحاولة** (`PENDING`).
   - **(ب) تغيير خارجي** (قيمة القناة ≠ آخر مدفوع: Google أو مستخدم عدّل): ← **EXTERNAL CHANGE DETECTED** (= **CONFLICT DETECTED** في M38 §7). تعرض القيمتين (مثال: Google 09:00 · Master 08:00) مع ثلاثة إجراءات:
     - **إبقاء الـMaster** (Keep Master): يُعاد الدفع (`sync_jobs`) + Audit. للحقول اليدوية (الاسم/العنوان/الـPin): `MANUAL ACTION REQUIRED` بخطوات.
     - **اعتماد التغيير الخارجي** (Adopt): **بقرار الـOwner فقط** وبتأكيد. يُنشئ نسخة Master جديدة: Fact جديد `APPROVED` (`source_type = OWNER_DASHBOARD`، مع ملاحظة "adopted from GBP" ومرجع سطر التدقيق)، والقديم `SUPERSEDED`. ثم يُعيد مزامنة بقية القنوات، ويُسجّل.
     - **مراجعة** (Review — **الافتراضي**): يبقى `OUT OF SYNC` كبند في "يحتاج انتباه" مع تذكير.
3. **ممنوع** أن يكتب Google (أو أي قناة) فوق الـMaster تلقائيًا. لا مسار في الكود يكتب قيمة خارجية في الـHub دون فعل الـOwner.
4. **نفس النموذج** لكل قناة مستقبلية تقبل القراءة.

## 11. تجربة حالة المزامنة (Sync status UX)
**الحالات لكل قناة × كيان/حقل** (التخزين: `channel_sync_states`):

| الحالة | المعنى |
|---|---|
| `SYNCED` | آخر قيمة منشورة مطابقة **بعد VERIFY** (§15 الخطوة 8) |
| `PENDING` | في الطابور أو بانتظار إعادة المحاولة |
| `FAILED` | استنفدت المحاولات أو خطأ غير قابل للإعادة |
| `NOT SUPPORTED` | الحقل غير مدعوم في القناة |
| `MANUAL ACTION REQUIRED` | يحتاج فعلك (حقل يدوي، إعادة ربط) |
| `OUT OF SYNC` | الكاشف وجد اختلافًا (تأخر أو تغيير خارجي — §10) |

- `PENDING INTEGRATION` = حالة **القناة** قبل الربط (من `integrations`)، وليست حالة حقل.
- مفردات BRANCH DATA SYNC القديمة (MATCH / MISMATCH / CONFLICT / MISSING) **مستبدلة** بهذه الست. التحويل في [`BRANCH-DATA-SYNC`](google/BRANCH-DATA-SYNC.md).

**المعلومات المعروضة:** آخر مزامنة · آخر نجاح · الخطأ بلغة مفهومة · زر إعادة المحاولة.

**شاشة "اتساق البيانات"** (ضمن مجموعة "النظام" ← البيانات العامة، وتظهر مشاكلها في "يحتاج انتباه" — §18):
```
فرع HOUSE
  الموقع           ✓ متطابق
  Google           ⚠ غير متطابق — "ساعات الجمعة على Google 09:00 والمعتمد 08:00"   [احتفظ بالمعتمد] [اعتمد تغيير Google] [راجع]
  البيانات المنظمة ✓ متطابق
  المنيو           ✓ متطابق
```

**بعد النشر** (الخطوة 9 في §15):
- **رسالة صريحة،** لا نجاح صامت. مثال: "تم تحديث الموقع بنجاح. **فشلت مزامنة Google Business Profile.** السبب: … [إعادة المحاولة] [مراجعة]".

## 12. نموذج إعادة المحاولة والأخطاء
**طابور مزامنة** (جدول `sync_jobs`):
- `entity` · `channel` · `payload_version` · `attempt` · `next_retry_at` · `last_error` · `status` · **مرجع سطر `audit_logs` الذي أطلق المهمة**.
- حالة كل كيان × قناة في `channel_sync_states`.

**إعادة المحاولة:**
- تلقائية بتأخير متصاعد: 1 ← 5 ← 30 دقيقة ← 6 ساعات.
- **حد أقصى 5 محاولات** (الأولى + 4 إعادات)، ثم `FAILED` + بند "يحتاج انتباه" (§18).
- **Idempotency:** مهمة لنسخة أقدم تُلغى عند نشر أحدث ("latest wins")، ومهمة واحدة جارية لكل كيان × قناة.
- زر **Retry** يدوي متاح دائمًا.

**تصنيف الأخطاء:**

| النوع | السلوك |
|---|---|
| مؤقت (شبكة، حصة، 5xx، 429) | إعادة محاولة |
| صلاحية أو Token منتهي | `MANUAL ACTION REQUIRED` + "أعد ربط حساب Google" |
| رفض Google للقيمة | عرض السبب، بلا إعادة |
| حقل غير مدعوم | `NOT SUPPORTED` |

**قواعد ثابتة:**
- **فشل أي قناة خارجية لا يكسر الموقع.** الموقع يعرض الـMaster دائمًا، ولا استدعاءات خارجية متزامنة أثناء عرض الصفحات.
- **الإرجاع (Rollback):**
  1. استعادة نسخة سابقة من الـMaster (نسخة جديدة في `content_versions`، لا محو للتاريخ).
  2. يُنشئ مهام مزامنة جديدة لكل القنوات المتأثرة.
  3. لا يكتمل حتى تعود الحالة SYNCED أو تُعرض المشكلة.

## 13. نموذج الأمان
- **رموز Google (OAuth refresh tokens):**
  - على الخادم فقط، **مشفرة** في `settings` بتصنيف SENSITIVE، بصلاحية `business.manage` فقط.
  - **لا شيء في حزمة المتصفح ولا في Git ولا في الـCMS.**
  - حالة الربط (متصل / يقترب الانتهاء / منتهي / ملغى) في `integrations`، **بلا أسرار**.
  - أسرار منفصلة لكل بيئة. **Staging بلا كتابة حية** على GBP (§19).
- **دورة حياة الرمز:**
  - تجديد آلي.
  - كشف الانتهاء أو الإلغاء ← تنبيه "أعد الربط" (§18).
  - زر "فصل الحساب" يلغي الرمز لدى Google ويحذفه + Audit.
- **الصلاحية:**
  - Owner فقط (V1) على الـHub والمزامنة، من الخادم.
  - "اعتماد تغيير خارجي" و"الإرجاع" و"إغلاق مؤقت على Google" و"فصل الحساب" تتطلب **تأكيدًا وإعادة تأكيد الهوية**.
- **السجل:** كل تعديل في `audit_logs`: من · ماذا · قبل · بعد · متى · **القنوات المتأثرة** (`channels_affected`) · **نتيجة المزامنة** (عبر `sync_jobs` المربوطة بالسطر).
- **النسخ:** `content_versions` للأسعار والساعات والعناوين والتواصل وتوفر المنيو والحملات (متى ولماذا). **حقل `reason` إلزامي** لتغيير السعر والساعات والإغلاق.

## 14. خطة ترحيل البيانات (Data migration plan)
**الخطوات:**

| # | الخطوة | المصدر | الناتج |
|---|---|---|---|
| 1 | **تجميع القيم المعتمدة فقط** في ملف بذرة واحد (`data/master/seed`) | DECISION-LOG (D-018، D-020، D-057…)، وInventory v1.0، وسجل اعتماد المحتوى | كل قيمة بمعرّف `FACT-####` وحالتها. **غير المعتمد يدخل بحالته (`PENDING OWNER APPROVAL` / `PENDING VERIFICATION` / `MISSING`) ولا يُنشر** |
| 2 | **توحيد المستودع** | — | النماذج وأدوات الاختبار (`hours_logic_check.py`، مولّدات الـWireframes) **تقرأ من ملف البذرة** بدل القيم المكتوبة. يمكن تنفيذه مبكرًا في `tooling/` بعد اعتمادك. **خطوة P0 قليلة المخاطر** |
| 3 | **لقطة القنوات الخارجية** (بعد الوصول PO-008/PO-009) | الموقع القديم، وGBP لكل فرع، والأدلة | تقرير **اختلافات أولي**، **بلا تعديل** |
| 4 | **قرارات الـOwner** على الاختلافات | (احتفظ / اعتمد) | قيم مثبتة في الـHub |
| 5 | **بعد بناء الـHub** (P08) | ملف البذرة | استيراد إلى قاعدة البيانات، والتحقق بالعدد والـchecksum |
| 6 | **أول مزامنة لـGoogle** (P09، بعد بوابة §19) | الـHub | **بموافقة الـOwner** وبحقول آلية فقط. ثم قائمة "MANUAL ACTION REQUIRED" للاسم والعنوان. **`websiteUri` عند الإطلاق P11 فقط** |
| 7 | **مراقبة 2–4 أسابيع** | كاشف عدم التطابق | الاستقرار، ثم تفعيل الفحص اليومي الكامل |

**القواعد طوال الترحيل:**
- لا Scraping.
- لا كتابة على Google بلا موافقة.
- لا حذف لأي بيانات خارجية.
- كل خطوة مسجلة.
- Source data لا يُعدّل (PROD-019).

---

## 15. تدفق النشر والمزامنة (9 خطوات — M33 §9)
```
OWNER EDIT → VALIDATE → IMPACT PREVIEW → OWNER PUBLISH → SAVE MASTER DATA
          → UPDATE WEBSITE → SYNC EXTERNAL CHANNELS → VERIFY → SHOW STATUS
```

| # | الخطوة | ماذا يحدث | الأثر القابل للفحص |
|---|---|---|---|
| 1 | **OWNER EDIT** | مسودة في **المحرر الواحد** للحقل (§16). Fact القيمة الجديدة = `PENDING OWNER APPROVAL` | مسودة |
| 2 | **VALIDATE** | Publish Guard + قواعد الكيان: E.164 · تداخل الفترات · النهاية بعد البداية · تواريخ الساعات الخاصة · **حالة الـFact:** كل قيمة أخرى تظهر في المخرجات يجب أن تكون `APPROVED`/`VERIFIED`؛ قيمة الـOwner الجديدة تُعتمد بالنشر (الخطوة 4) | نتيجة الحارس |
| 3 | **IMPACT PREVIEW** | "هذا التغيير سيؤثر على:" الصفحات والمكونات والقنوات (مثال: صفحة HOUSE · Locations · Footer · Google Business Profile · LocalBusiness Schema · Contact · مكون Today Hours). لكل قناة: مزامنة آلية / إجراء يدوي / غير مدعوم / بانتظار الربط | قائمة الأثر |
| 4 | **OWNER PUBLISH** | **النشر بعد معاينة الأثر = الاعتماد:** Fact القيمة ← `APPROVED` (`source_type = OWNER_DASHBOARD`، `decision_ref` = سطر التدقيق). **وضغطك "نشر" بعد معاينة تذكر Google Business Profile = موافقتك على تحديث GBP** (G15-CF-02، AC-1). الحالات الحساسة (إغلاق مؤقت على Google، التصنيف، Rollback، Adopt، فصل الحساب): تأكيد إضافي + إعادة تأكيد الهوية | قرار |
| 5 | **SAVE MASTER DATA** | نسخة في `content_versions` (+ `reason` الإلزامي للسعر/الساعات/الإغلاق) + سطر في `audit_logs` (+ `channels_affected`) | نسخة + سطر تدقيق |
| 6 | **UPDATE WEBSITE** | إبطال Cache **بالوسوم** للصفحات المتأثرة فقط (لا Purge شامل). الموقع يقرأ من الـHub | إبطال |
| 7 | **SYNC EXTERNAL CHANNELS** | `sync_jobs` لكل قناة دفع (GBP…) مربوطة بسطر `audit_logs`. في Staging: dry-run (§19) | مهام |
| 8 | **VERIFY** | **قراءة بعد الكتابة:** GBP عبر `locations.get` ومقارنة بما أُرسل · Website وSchema بعرض الصفحة والـJSON-LD بعد الإبطال ومقارنتهما بالـMaster. **`SYNCED` لا يُمنح إلا بعد نجاح VERIFY** | نتيجة تحقق |
| 9 | **SHOW STATUS** | نتيجة **لكل قناة على حدة** (§11). **لا نجاح صامت** (M33 §11) | حالة + بند "يحتاج انتباه" عند الفشل |

**التغييرات المجدولة:**
- الساعات الخاصة المؤرخة تُدفع إلى Google **فور النشر** (Google يطبقها في تاريخها).
- تغيير Regular أو سعر بتاريخ سريان لاحق يُدفع **عند تاريخ السريان** (تفصيل تقني — G15-TF-11).

**Rollback:** يمر بنفس التدفق (من الخطوة 3)، ولا يكتمل حتى تعود القنوات `SYNCED` أو تُعرض المشكلة (§12).

## 16. لا محررات مكررة (No duplicate editors — M33 §21)
- **حقل واحد = مصدر واحد = مسودة واحدة = نشر وتدقيق واحد.** لا محرر ساعات في وحدة الفروع، وآخر في الـSEO، وثالث في إعدادات Google.
- **نفس الـControl يمكن أن يظهر في أكثر من سياق** (صفحة الفرع، الـSEO، شاشة Google/الاتساق)، لكنه مربوط **بنفس مفتاح الـMaster**. تعديل الساعات من سياق الفرع يظهر فورًا في سياق الاتساق (نفس المسودة).
- **صفحة Google في الـDashboard** تعرض فقط: الربط · المصفوفة الحية (§4.1) · حالة المزامنة · تجاوزات القناة (§17). **لا نسخ قابلة للتعديل.**
- **محرر الـSEO:** لا حقول ساعات أو هاتف أو عنوان. يستخدم Tokens من الـHub.
- **إعدادات الـSchema:** تفعيل ونوع فقط (§9).
- **Wireframe متسق:** `d-branch.html` — "الهاتف والواتساب والعنوان لا تُكتب هنا: قيمة واحدة لكل الموقع. تعديل في البيانات العامة".
- **قاعدة مراجعة الكود:** لا Model ولا عمود ثانٍ لنفس الحقيقة.

## 17. تجاوزات خاصة بقناة (Channel-specific overrides — M33 §24)
- **الجدول:** `channel_overrides` (PLATFORM-ARCHITECTURE §3.2). حقول مقترحة (تفصيل تقني): `entity` · `field` · `channel` · `value` · `reason` · `created_by` · `created_at` · `active`.
- **مثال:** **Google Description Override** — وصف مختلف لـGoogle **لا يغيّر** Master Description في الموقع.
- **كل Override:**
  - **موثق:** السبب إلزامي.
  - **مدقق:** سطر في `audit_logs`.
  - **قابل للعكس:** زر **Reset to Master** يعيد القيمة الموروثة (والتاريخ يبقى في النسخ).
- **يظهر بوضوح** في المصفوفة الحية بوسم **OVERRIDDEN FOR GOOGLE** (§4.1).
- **الكاشف** يقارن القناة بقيمتها الفعالة (الـOverride إن وُجد، وإلا الـMaster).
- **قيمة الـOverride** تخضع لطبقة الحقائق وPublish Guard وقيود القناة (مثل حد طول وصف GBP وممنوعاته — يُتحقق منها في P09).
- **فروق أسعار تطبيقات التوصيل** (إن وُجدت) تكون Channel overrides **فقط عند توفر بيانات الـOwner** (PO-023).
- **اليوم:** لا Overrides. لا يُنشأ أي Override من عندنا.

## 18. مراقبات "يحتاج انتباه" لبيانات الـMaster (M33 §25)
كل مراقب يكتب **إشارة واحدة** في `signals` (`kind` = ISSUE، بـ`dedupe_key`)، بلغة الـOwner ومع إجراء، و**تُغلق تلقائيًا عند الحل**. الشدة متسقة مع [`docs/platform/MONITORING.md`](platform/MONITORING.md) (#8 و#10).

| المراقب (M33 §25) | ماذا يقارن | الشدة | الفئة | مثال بلغة الـOwner |
|---|---|---|---|---|
| **Google hours out of sync** | ساعات GBP مقابل `effective_hours` | MEDIUM | DATA CONSISTENCY | "ساعات فرع HOUSE على Google مختلفة عن الموقع." |
| **Website menu price differs from master** | السعر في الصفحة المولّدة فعليًا (أو الـCache) مقابل `product_prices` / `product_branch_overrides` | MEDIUM | DATA CONSISTENCY | "سعر منتج في صفحة المنيو لا يطابق السعر المعتمد." |
| **Schema uses old phone** | JSON-LD المولّد مقابل `contact_points` (Schema Health) | MEDIUM | DATA CONSISTENCY | "رقم الهاتف في البيانات المنظمة قديم." |
| **Branch missing special hours** | مناسبة أو عطلة قادمة **من قائمة يحددها الـOwner** خلال نافذة (اقتراح: 14 يومًا) بلا قرار ساعات خاصة لفرع نشط. **لا تواريخ عطل مخترعة** (PO-025) | MEDIUM (اقتراح تقني) | DATA CONSISTENCY | "لا توجد ساعات خاصة لفرع DRIVE في مناسبة قادمة." |
| **Failed sync** | `sync_jobs` = `FAILED` بعد 5 محاولات | HIGH | INTEGRATIONS | "فشلت مزامنة Google Business Profile. [إعادة المحاولة] [مراجعة]" |
| **Expired external token** | `integrations` | ينتهي خلال 14 يومًا ← MEDIUM · انتهى أو أُلغي ← HIGH | INTEGRATIONS | "انتهى ربط حساب Google. [أعد الربط]" |

- `OUT OF SYNC` أو `EXTERNAL CHANGE DETECTED` العامة ← MEDIUM (MONITORING #8).
- الحالة المختصرة في Command Center: Master Data SYNCED · Google SYNCED · المنيو SYNCED (FINAL-ARCHITECTURE-REVIEW §10).

## 19. بوابات التفعيل: Staging dry-run · توقيت `websiteUri` · أول كتابة حية
**Staging = dry-run إجباري:**
- **لا يوجد Sandbox رسمي لـGBP.** لذلك **Staging لا يكتب على GBP الحقيقي أبدًا** (`CHANNEL_WRITE_ENABLED=false` — [`docs/platform/ENVIRONMENTS.md`](platform/ENVIRONMENTS.md)).
- المحوّل في Staging يحسب الـdiff والـpayload، ويسجّل `sync_jobs` كـdry-run.
- **التحقق من الـpayload:** `locations.patch` مع `validateOnly=true` (بلا حفظ) **فقط** إن وُجد ربط لبيئة Staging بموافقتك، وإلا مقابل ردود API مسجّلة (fixtures) في اختبارات العقد.
- أي طلب كتابة فعلي من بيئة غير الإنتاج **يُرفض داخل الكود** ويُسجَّل.

**أول كتابة حية على GBP (MDH-023) — لا تعمل إلا بعد:**
1. موافقة Google على طلب الوصول لـBusiness Profile APIs (الحصة قد تكون صفرًا حتى الموافقة).
2. OAuth من حساب Owner/Manager للفرعين بصلاحية `business.manage` (PO-009).
3. الملفان Verified (GBP-014).
4. بلوغ P09 وبوابة الإنتاج، وموافقتك على أول مزامنة (§14 الخطوة 6).
- **حتى ذلك:** GBP = `PENDING INTEGRATION`، والموقع والـSchema يعملان من الـMaster بلا أي تأثير.
- **أقل صلاحية:** `business.manage` هو النطاق الوحيد (قراءة + كتابة)، لذلك يُفرض داخل التطبيق وضع **قراءة/مقارنة** حتى تفعّل الدفع.

**توقيت `websiteUri` (P11):**
- `websiteUri` لكل فرع = **رابط صفحة الفرع النهائي** في الموقع الجديد.
- **لا يُدفع رابط الموقع الجديد إلى GBP قبل الإطلاق (P11)**، وبعد اعتماد شجرة الروابط والـHost (PO-005، DB-11، PO-041).
- قبل الإطلاق: الحقل `READ/COMPARE ONLY`، والرابط الحالي في GBP **لا يُلمس**.

## 20. سجل الوثيقة
| التاريخ | التغيير | المرجع |
|---|---|---|
| 2026-10-01 | الإصدار الأول: المخرجات الأربعة عشر | M33 §29 · commit `32cdb91` |
| 2026-10-01 | **جولة doc-fix (بلا تغيير في أي قيمة Business):**<br>- الترويسة: المنصة محسومة بـADR-001 (لم تعد تنتظر DB-08).<br>- إحالات GLOBAL-DATA-REGISTRY وFACT-REGISTRY إلى `docs/platform/`.<br>- أسماء الجداول الملزمة من PLATFORM-ARCHITECTURE (§2 التخزين، §6، §12، §13).<br>- التوفر: 3 حالات عامة + `UNKNOWN` داخلي (CMS-015) بدل "4 حالات".<br>- معرّف الحقيقة `FACT-0001` بدل `F-*` (تصادم مع F-01…F-21)، وحالاتها مفردات D-224 + `VERIFIED` (G14-CF-12).<br>- SEO metadata قناة (§3، §4).<br>- GBP قناة مُزامَنة؛ D-048 SUPERSEDED؛ D-047 للقيم غير المعتمدة فقط؛ التغيير الخارجي إلى المراجعة (§3، §10).<br>- التصنيف مدعوم في الـAPI (لم يعد يدويًا — §4، §5، §8).<br>- المصفوفة الحية بأعمدتها السبعة (§4.1).<br>- إضافة §15–§19: التدفق التسعي مع VERIFY · لا محررات مكررة · `channel_overrides` · مراقبات §25 الست · Staging dry-run وتوقيت `websiteUri` | G15-TF-07 · G15-TF-08 · G15-CF-01 · G15-CF-08 · G15-CF-09 · G15-CF-11 · G15-CF-12 · MDH-036 |
