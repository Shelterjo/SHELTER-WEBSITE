# PLATFORM ARCHITECTURE — المعمارية النهائية للبناء

> **المرجع الحاكم للبناء.**
> - المنصة: [`ADR-001`](../adr/ADR-001-platform.md).
> - المراجعة المعمارية: [`FINAL-ARCHITECTURE-REVIEW.md`](../FINAL-ARCHITECTURE-REVIEW.md).
> - البيانات الرئيسية: [`MASTER-DATA-HUB.md`](../MASTER-DATA-HUB.md).
> - التصميم: [`DESIGN-SYSTEM-STANDARD.md`](../DESIGN-SYSTEM-STANDARD.md).
>
> **القاعدة:** **نظام واحد لكل وظيفة، ومصدر واحد لكل نوع بيانات** (M36 §3). أي وثيقة تشغيلية في `docs/platform/` تستخدم **أسماء الجداول والخدمات أدناه حرفيًا**.

## 1. شكل النظام

```
Cloudflare (CDN · WAF · TLS)
        │
Cloudways Flexible ── Laravel 13 app "shelter" (تطبيق مستقل؛ WordPress القديم لا يُلمس)
   ├─ Public website        /ar/... /en/...           (Blade، HTML من الخادم)
   ├─ Owner Dashboard       /dashboard/...            (Blade، Owner فقط، صلاحيات من الخادم)
   ├─ Internal API          /api/... (للـDashboard والنماذج فقط؛ لا API إداري عام)
   ├─ Scheduler + Queue     (cron كل دقيقة + worker)
   ├─ MySQL/MariaDB         (Production وStaging: قاعدتان منفصلتان)
   └─ Storage
        ├─ public media      (عامة، مُحسّنة، Hash)
        ├─ private careers   (خارج الجذر العام)
        ├─ private partnerships
        └─ exports           (مؤقتة، خاصة، تُحذف تلقائيًا)
```

## 2. هيكل الكود
| المسار | المحتوى |
|---|---|
| `app/Models/` | نماذج Eloquent (أسماء الجداول في §3) |
| `app/Services/<Domain>/` | منطق الأعمال: `Core`، `MasterData`، `Menu`، `Content`، `Experiences`، `Applications`، `Channels`، `Ops` |
| `app/Http/Controllers/Site/` | الموقع العام |
| `app/Http/Controllers/Dashboard/` | الـDashboard (كل Route خلف `auth` + `owner` + `2fa`) |
| `app/Policies/` | الصلاحيات من الخادم (**لا اعتماد على إخفاء الأزرار**) |
| `resources/views/components/ui/` | **مكتبة المكونات الوحيدة** (`x-ui.button`، `x-ui.field`…) للموقع والـDashboard |
| `resources/views/site/` · `resources/views/dashboard/` | القوالب |
| `resources/css/` | `tokens.css` (مولّد من `design-system/tokens/tokens.json`) + `components.css` + `site.css` + `dashboard.css` |
| `lang/ar` · `lang/en` | نصوص الواجهة الثابتة فقط. **المحتوى من قاعدة البيانات** |
| `database/migrations` | **مصدر المخطط الوحيد** (Expand → Migrate → Contract) |
| `database/seeders` | بيانات معتمدة فقط (المنيو المجمّد، السوق JO، الفروع بالحقول المعتمدة). **لا بيانات مخترعة** |
| `tests/Unit` · `tests/Feature` | PHPUnit |
| `tooling/` | Playwright (20 عرضًا) · axe · Lighthouse · Sharp (قائمة) |

## 3. الجداول الموحدة (أسماء ملزمة)

### 3.1 النواة (Phase 1)
| الجدول | الغرض | التصنيف |
|---|---|---|
| `users` | الـOwner (ومستخدمون مستقبلًا **بإنشاء الـOwner فقط**). كلمة مرور Argon2id، وسر TOTP مشفّر، و**رموز استرداد مُجزّأة (Hashed، تُعرض مرة واحدة وتُجدَّد)** | SENSITIVE |
| `webauthn_credentials` | مفاتيح الدخول (Passkeys): المعرّف، والمفتاح العام، والعداد، والاسم، وآخر استخدام | SENSITIVE |
| `sessions` | جلسات الخادم | CONFIDENTIAL |
| `audit_logs` | **سجل تدقيق واحد للنظام كله:** من، ماذا، متى، قبل/بعد (PII مقنّع)، والقنوات المتأثرة (`channels_affected`) | CONFIDENTIAL |
| `content_versions` | **إصدارات موحدة** لأي كيان قابل للنشر: Snapshot JSON + الحالة + الكاتب + **السبب** (`reason`، إلزامي لتغيير السعر والساعات والإغلاق) + نتيجة Publish Guard (`guard_result`) + موعد النشر المجدول (`scheduled_at`) | INTERNAL |
| `settings` | الإعدادات العامة (مفتاح، قيمة JSON، مجموعة، تصنيف) | INTERNAL |
| `feature_flags` | الأعلام: Safe Mode، تعطيل الطبقة الديناميكية، الصيانة | INTERNAL |
| `signals` | **مصدر واحد للإشعارات و"يحتاج انتباه" والحوادث:**<br>- `kind`: EVENT \| ISSUE<br>- `severity`: CRITICAL / HIGH / MEDIUM / LOW / INFO<br>- `priority`: CRITICAL / ACTION_REQUIRED / IMPORTANT / INFORMATION<br>- `category`: الفئات الـ9 + `OPERATIONS` (التوفر، القاعدة، الأخطاء، النماذج، المجدول — تغذي بطاقة "الموقع" ولا تدخل نموذج الصحة)<br>- `dedupe_key`، و`status`: OPEN / RESOLVED / DISMISSED | INTERNAL |
| `reference_sequences` | ترقيم `PREFIX-YYYY-NNNNN` من الخادم (JOB / FR / INQ) بقفل صفّي | INTERNAL |
| `idempotency_keys` | منع الإرسال المكرر لكل النماذج | INTERNAL |
| `scheduled_job_runs` | صحة المجدول: آخر تشغيل، المدة، النتيجة | INTERNAL |

### 3.2 البيانات الرئيسية (Phase 1)
| الجدول | الغرض |
|---|---|
| `settings` (مجموعتا `brand.*` و`website.*`) | كيانا **Brand** و**Global Website** بمفاتيح ثابتة: الاسم الرسمي، والشعار، وسنة التأسيس، والبريد الرسمي، والروابط العامة. **كل قيمة مربوطة بـ`facts`** |
| `markets` | السوق: `JO` · اللغات ar/en · العملة JOD · المنطقة Asia/Amman · رمز الهاتف · صيغ التاريخ. **لا تثبيت للدولة في الكود** |
| `countries` → `cities` → `branches` | Country → City → Branch. الفرع: `code` (BR-DRIVE / BR-HOUSE)، والنوع، والحالة، والحقول ثنائية اللغة، والإحداثيات، ورابط الخرائط، ومعرّف GBP. **كل حقل بلا قيمة معتمدة = NULL + Fact بحالة PENDING** |
| `branch_hours` | الساعات المنتظمة: عدة فترات لكل يوم، وتجاوز منتصف الليل |
| `hours_exceptions` | `kind`: emergency > temporary > special/holiday (الأولوية مشتقة من النوع)، ونطاق تاريخ |
| `contact_points` | أرقام ونقاط تواصل حسب النية (`scope` brand/branch، `kind`، `is_public`) |
| `social_links` | حسابات التواصل |
| `facts` | **سجل الحقائق:**<br>- `code` بصيغة `FACT-0001`، والمفتاح، والفئة، والسوق.<br>- القيمة وبصمتها (`value_hash`).<br>- الحالة (D-224 + VERIFIED).<br>- المصدر (`source_type`/`source_ref`) ومرجع القرار.<br>- الموثِّق، وتواريخ التحقق والمراجعة والانتهاء.<br>- عبارات ممنوعة (`blocked_phrases`) |
| `external_references` | معرّفات الأنظمة الخارجية (GBP · POS · ERP · App) لكل كيان |
| `channel_overrides` | قيمة خاصة بقناة واحدة فقط (مثلًا وصف GBP مختلف) **بموافقة وسبب**، والأصل يبقى في الـHub (M33 §24) |
| `channel_sync_states` · `sync_jobs` | حالة المزامنة لكل كيان وقناة + طابور بإعادة محاولة 1 → 5 → 30 دقيقة → 6 ساعات (حد أقصى 5 محاولات)، وكل مهمة مربوطة بسطر `audit_logs` الذي أطلقها |
| `integrations` | **سجل التكاملات الوحيد:** الحالة، البيئة، آخر نجاح، انتهاء الرمز، النطاق، يحتاج إعادة ربط. **بلا أسرار.** رموز OAuth (Google) تُحفظ **مشفّرة** في `settings` بتصنيف SENSITIVE، ولا تُعرض في الواجهة أبدًا |

### 3.3 المحتوى والمنيو (Phase 2–3)
| الجدول | الغرض |
|---|---|
| `menu_categories` · `menu_subcategories` · `products` · `product_prices` · `product_branch_overrides` | المنيو المجمّد v1.0. المعرّفات `CAT-`/`PRD-` ثابتة. **لا إعادة استخدام لمعرّف متقاعد**، والقادم PRD-00193. التجاوز لكل فرع: موروث / مُعدّل / إعادة ضبط |
| `media` · `media_usages` | **مكتبة وسائط واحدة:** الحقوق، والترخيص، والانتهاء، والنص البديل ar/en، ونقطة التركيز، وحالة الاعتماد. Usage graph لمعرفة أين تُستخدم كل صورة |
| `pages` · `page_sections` | صفحات من أقسام معتمدة فقط (Design lock) |
| `articles` | المعرفة/المقالات (Coffee Knowledge) |
| `awards` | الجوائز والتقدير (مع وسائطها من `media`) |
| `reviews` | نسخة محلية من مراجعات GBP + مسودة الرد (لا نشر بلا موافقة) |
| `feedback` | آراء العملاء (Voice of Customer) |
| `experiences` | **جدول واحد** للتجارب الديناميكية بـ`type`: حملة، أو موسم/ثيم، أو فعالية، أو إعلان، أو تقدير (موظف الشهر). مع الأولوية، والجدولة، والتعطيل الطارئ (`emergency_disabled`) |
| `team_members` | ملفات SHELTER Family العامة (**بموافقة الموظف**)، ويرتبط بها "موظف الشهر" |
| _(عرض)_ التقويم العام | **عرض قراءة فقط** فوق `experiences` + `hours_exceptions` + `content_versions` المجدولة + المناسبات من `facts`. **لا جدول خاص به** |
| `redirects` | خريطة التحويل (301 / 302 / 410) + عدّاد الاستخدام |
| `search_index` · `search_query_daily` | فهرس بحث واحد مشتق (عام + Owner، مفصول بالصلاحية) + إحصاء يومي مجمّع للاستعلامات **بلا PII** |

### 3.4 الطلبات (Phase 4) — Applications Core
| الجدول | الغرض |
|---|---|
| `applications` | النواة المشتركة: `type` (JOB / FR / INQ)، والرقم المرجعي، والحالة، والمسؤول، والتواريخ |
| `job_applications` · `partnership_applications` · `inquiries` | الحقول الخاصة بكل نوع (التوظيف حسب `RECRUITMENT-DATA-MODEL.md`) |
| `application_notes` · `application_status_history` · `application_attachments` | ملاحظات، وتاريخ حالات، وملفات خاصة (الاسم كما في `RECRUITMENT-DATA-MODEL.md`) |
| `consent_versions` · `application_consents` | نصوص الموافقة بإصدارات + موافقة كل طلب (`scope`: careers / partnerships / inquiries / feedback) |

### 3.5 الجودة والعمليات (Phase 6–7)
| الجدول | الغرض |
|---|---|
| `rum_metrics` | Web Vitals **مجمّعة**: اليوم، ومجموعة المسار، والجهاز، وعائلة المتصفح، واللغة، والمقياس، وp75، والتوزيع، والعينات. **بلا معرّف وبلا IP** |
| `releases` | سجل الإصدارات: الرقم، والـCommit، والتغييرات، ونتيجة الـCI، والحالة، والإصدار المستقر السابق |
| `exports` | ملفات تصدير مؤقتة: المالك، والانتهاء، والتصنيف |

### 3.6 أعمدة مشتركة لكل كيان قابل للنشر
- `status` (Draft / Scheduled / Published / Archived).
- `published_at`.
- `ar_updated_at` / `en_updated_at` + `translated_from_revision`: لتطابق اللغتين.
- `last_reviewed_at`: لحداثة المحتوى.
- `seo_updated_at`.
- `origin`.

**خريطة الأثر** (أين تظهر كل قيمة): `config/impact.php` تُحل في `app/Services/Core/Impact`، **وتستخدمها معاينة الأثر وإبطال الـCache معًا**.

## 4. قواعد عابرة لكل الوحدات
1. **الصلاحيات من الخادم:** Middleware `owner` + Policy لكل كيان. أي Route إداري بلا مصادقة = فشل اختبار.
2. **النشر:** Draft → Preview → Publish / Schedule → Archive.
   - كل نشر: Publish Guard (يمنع BLOCKING ويحذّر WARNING) + معاينة الأثر (الموقع / Schema / Google).
   - ثم إصدار في `content_versions` وسطر في `audit_logs` وإبطال Cache بالوسوم.
3. **الوقت:** كل حالة زمنية ("مفتوح الآن"، "نشط الآن"، انتهاء حملة) **تُحسب عند الطلب** بتوقيت السوق، **ولا تعتمد على مهمة مجدولة وحدها**.
4. **لا بيانات أعمال مخترعة:** الحقل غير المعتمد = NULL، ويُخفى في الواجهة، ويُسجّل Fact بحالة PENDING ← يظهر في "يحتاج انتباه".
5. **لا PII في السجلات أو التحليلات.** تصنيف كل حقل يحدد التخزين والتصدير والعرض (`docs/platform/DATA-CLASSIFICATION.md`).
6. **الحذف:** Archive بدل الحذف النهائي. **استثناء التوظيف:** Active → Archive → Permanent Delete (Owner فقط + Tombstone).
7. **لا أسرار في الواجهة أو Git.** الأسرار في `.env` لكل بيئة على الخادم فقط.
8. **الأداء:** HTML من الخادم، وCSS حرج صغير، وJS عند الحاجة فقط، والصور AVIF/WebP بأحجام 240–1080، **بلا سكربتات طرف ثالث دون موافقة**.
