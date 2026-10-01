# GOOGLE INTEGRATION ARCHITECTURE

> **الحالة:** `DRAFT` — تكاملات مطلوبة لاحقًا (D-050). **لا شيء يُنفذ الآن. لا تغيير لأي Google property.**
> **آخر تحديث:** 2026-10-01 · السياسة: [`GOOGLE-ECOSYSTEM-POLICY.md`](GOOGLE-ECOSYSTEM-POLICY.md)

## 1. Source Priority (D-047)
1. **OWNER APPROVED DATA**
2. **OFFICIAL GOOGLE BUSINESS PROFILE** (المصدر التشغيلي الرسمي للفرعين — D-048)
3. OFFICIAL SHELTER WEBSITE
4. OFFICIAL SHELTER SOCIAL ACCOUNTS (بعد تأكيدها — D-036)
5. OTHER SOURCES (Semrush، الأدلة، فهرس البحث)

**التعارض:** Source A · Source B · Difference · Recommended correction ← `CONFLICT — OWNER REVIEW REQUIRED` ← انتظار القرار. Google لا يتغلب على بيانات الـOwner المعتمدة تلقائيًا.

## 2. التكاملات المطلوبة

| التكامل | الدور في المشروع | الوضع الحالي المعروف | متى | مستوى الوصول |
|---|---|---|---|---|
| **Google Business Profile** (DRIVE، HOUSE) | المصدر التشغيلي للفروع + Local SEO + كيان AI | ملفان رسميان (حسب الـOwner)؛ البيانات غير مرئية لنا بعد | Discovery (الآن) | معلومات فقط (روابط + لقطات) — D-043 |
| **Google Maps** | روابط الاتجاهات الرسمية لكل فرع | روابط `share.google` قديمة في الموقع القديم — غير مؤكدة | Discovery | لا وصول — روابط من الـOwner |
| **Search Console** | مصدر الحقيقة للأداء + Baseline للنقل | **Site Kit مثبت** (مؤشر قوي على وجوده) — غير مؤكد | Discovery (Baseline) ثم مستمر | Restricted / Export — D-042 |
| **GA4** | الاستخدام الفعلي + القياس الجديد | غير معروف (Site Kit قد يكون مربوطًا) | لاحقًا | Viewer — D-042 |
| **GTM** | تنظيم التتبع في الموقع الجديد | غير معروف — WPCode قد يحتوي أكوادًا | Architecture → Build | Read (Export JSON) |
| **PageSpeed / Lighthouse** | Baseline الأداء قبل/بعد | لا Baseline بعد | بعد AC-01 | Lighthouse محلي مجاني |
| **CrUX (بيانات المستخدمين الحقيقيين)** | Core Web Vitals الحقيقية | غير معروف | مع Search Console | تقرير CWV في Search Console (مجاني) |
| **Rich Results / Schema validation** | التحقق من الـSchema | الـSchema القديمة فيها 12 خطأ (`01` §4) | Build → Pre-launch | أدوات Google العامة (مجانية) |
| **Sitemap submission / Indexing** | الفهرسة | Sitemaps قديمة (Yoast) | Pre-launch → Launch | عبر Search Console — **بموافقتك فقط** (§13 من السياسة) |

## 3. قرارات معمارية مفتوحة (تُعرض لاحقًا بخيارات)

### GA-01 — Site Kit مقابل Native مقابل GTM (§32 من السياسة)
| | Site Kit | Native (gtag مباشرة) | GTM |
|---|---|---|---|
| المنصة | WordPress فقط | أي منصة | أي منصة |
| الأداء | يضيف إضافة WP وسكربتات | الأخف | خفيف إذا نُظم جيدًا |
| الصيانة | سهل لغير التقنيين | يحتاج تطويرًا لكل تغيير | مرن بدون نشر كود |
| الصلاحيات | مرتبط بحساب Google لمستخدم WP | — | صلاحيات GTM مستقلة |
| جودة التتبع | أساسية | دقيقة لكن جامدة | الأفضل للأحداث المخصصة |

**ملاحظة:** القرار يعتمد على المنصة (DB-08) وعلى ما يديره Site Kit حاليًا. نفحصه بعد AC-01 ولقطات إعدادات Site Kit. **لا توصية نهائية قبل ذلك.**

### GA-02 — الخرائط في صفحات الفروع (§35 من السياسة)
| الخيار | الأداء | UX | الخصوصية | التكلفة |
|---|---|---|---|---|
| زر "الاتجاهات" فقط (رابط Google Maps الرسمي) | ✅ الأخف | ✅ يفتح تطبيق Maps على الموبايل | ✅ لا تحميل من Google قبل الضغط | 0 |
| خريطة مضمّنة عند الضغط فقط (Click-to-load) | ✅ لا تحميل افتراضي | ✅ لمن يريد رؤية الخريطة | ✅ | Maps Embed API بمفتاح مقيد (حسب تسعير Google المعروف بلا رسوم — **يُتحقق قبل الاستخدام**) |
| خريطة ثابتة (Static image) | ✅ | ⚠️ صورة غير تفاعلية | ✅ | Static Maps API — **قد تكون مدفوعة** ← تحتاج موافقة (D-044) |
| تضمين كامل تلقائي | ❌ ثقيل | ⚠️ | ⚠️ | — |

**اقتراح مبدئي:** زر الاتجاهات دائمًا + خريطة عند الضغط في صفحة الفرع فقط.

### GA-03 — Google Fonts (§37 من السياسة)
اقتراح مبدئي: **Self-hosting** لعدد محدود من العائلات والأوزان. القرار في الـDesign System.

### GA-04 — FAQPage Schema (§23 من السياسة)
منذ أغسطس 2023 حصرت Google نتائج FAQ المنسقة (Rich results) في مواقع حكومية وصحية موثوقة. الـMarkup مسموح إذا كان المحتوى ظاهرًا فعلًا، لكن **لا نتوقع منه نتيجة منسقة في Google**. قيمته الأساسية لفهم المحتوى في محركات البحث والذكاء الاصطناعي. يُقرر في مرحلة SEO.

## 4. قواعد ثابتة (من السياسة)
- **Staging:** Authentication + noindex. robots.txt وحده ليس حماية.
- **Sitemap:** الصفحات Canonical وPublic وIndexable وApproved فقط.
- **Maps API Key** (إن احتجناه): مقيد بالـReferrer وبالـAPI وبحد استخدام. لا Secrets في الـFront-end.
- **روابط Maps:** تتبع `directions_click` بدون إضافة معاملات تكسر الرابط.
- **التتبع المكرر:** فحص GA4 وGTM وSite Kit وHubSpot وأي أكواد في WPCode على الموقع القديم بعد AC-01.

## 5. ما لا نفعله
لا نعدّل GBP. لا نرفع أو نحذف Sitemaps. لا Removals. لا Disavow. لا نغيّر مستخدمين أو ملكية. لا Places API. لا Supermetrics بحصة. لا أي API مدفوع — بدون موافقتك.

## 6. المعلومات والصلاحيات التي سنحتاجها لاحقًا

| # | ماذا | من أين | مستوى | متى | لماذا |
|---|---|---|---|---|---|
| G-01 | رابط Google Maps الرسمي لكل فرع | الـOwner | معلومة | **الآن (Discovery)** | مصدر الاتجاهات والـSchema |
| G-02 | لقطات "Edit profile" لكل فرع: الاسم، التصنيف الأساسي والثانوي، العنوان، الـPin، الساعات العادية، **الساعات الخاصة**، الهاتف، رابط الموقع، الحالة | GBP | معلومات فقط | **الآن** | أول Branch Data Sync Check |
| G-03 | هل الملفان Verified؟ من الـOwner والمديرون فيهما؟ (أسماء الأدوار فقط) | GBP | معلومة | الآن | الاستمرارية والأمان |
| G-04 | نوع خاصية Search Console (Domain أم URL-prefix) والحساب المالك | Search Console | معلومة | قبل الـBaseline | معرفة نطاق البيانات |
| G-05 | Exports من Search Console (القائمة في `SEARCH-CONSOLE-BASELINE.md`) | Search Console | Export مجاني | عند المرحلة المناسبة (D-042) | Baseline + خريطة النقل |
| G-06 | لقطة إعدادات **Site Kit** (Settings → Connected Services) | WordPress | لقطة | بعد AC-01 أو الآن | ماذا يدير Site Kit (GA-01) |
| G-07 | قائمة الأكواد في **WPCode** (لقطة) | WordPress | لقطة | الآن أو بعد AC-01 | كشف التتبع المكرر |
| G-08 | GA4: Property ID / Measurement ID | GA4 | معلومة | لاحقًا | الـMeasurement Plan |
| G-09 | GA4 Viewer أو Exports | GA4 | Read-only | لاحقًا | الاستخدام الفعلي |
| G-10 | GTM: Container ID + Export JSON إن وُجد | GTM | Read | لاحقًا | `GTM-TAG-REGISTER.md` |
| G-11 | من يملك حسابات Google (Search Console وGA4 وGTM وGBP) — باسم المنشأة أم شخصي؟ | الـOwner | معلومة | لاحقًا (R12-05) | الاستمرارية |
