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
| `users` | الـOwner (ومستخدمون مستقبلًا **بإنشاء الـOwner فقط**). كلمة مرور Argon2id، وأسرار TOTP مشفرة، ورموز استرداد مشفرة | SENSITIVE |
| `sessions` | جلسات الخادم | CONFIDENTIAL |
| `audit_logs` | **سجل تدقيق واحد للنظام كله:** من، ماذا، متى، قبل/بعد (PII مقنّع) | CONFIDENTIAL |
| `content_versions` | **إصدارات موحدة** لأي كيان قابل للنشر (Snapshot JSON + الحالة + الكاتب) | INTERNAL |
| `settings` | الإعدادات العامة (مفتاح، قيمة JSON، مجموعة، تصنيف) | INTERNAL |
| `feature_flags` | الأعلام: Safe Mode، تعطيل الطبقة الديناميكية، الصيانة | INTERNAL |
| `signals` | **مصدر واحد للإشعارات و"يحتاج انتباه" والحوادث:**<br>- `kind`: EVENT \| ISSUE<br>- `severity`: CRITICAL / HIGH / MEDIUM / LOW / INFO<br>- `priority`: CRITICAL / ACTION_REQUIRED / IMPORTANT / INFORMATION<br>- `category`: الفئات الـ9<br>- `dedupe_key`، و`status`: OPEN / RESOLVED / DISMISSED | INTERNAL |
| `reference_sequences` | ترقيم `PREFIX-YYYY-NNNNN` من الخادم (JOB / FR / INQ) بقفل صفّي | INTERNAL |
| `idempotency_keys` | منع الإرسال المكرر لكل النماذج | INTERNAL |
| `scheduled_job_runs` | صحة المجدول: آخر تشغيل، المدة، النتيجة | INTERNAL |

### 3.2 البيانات الرئيسية (Phase 1)
| الجدول | الغرض |
|---|---|
| `markets` | السوق: `JO` · اللغات ar/en · العملة JOD · المنطقة Asia/Amman · رمز الهاتف · صيغ التاريخ. **لا تثبيت للدولة في الكود** |
| `countries` → `cities` → `branches` | Country → City → Branch. الفرع: `code` (BR-DRIVE / BR-HOUSE)، والنوع، والحالة، والحقول ثنائية اللغة، والإحداثيات، ورابط الخرائط، ومعرّف GBP. **كل حقل بلا قيمة معتمدة = NULL + Fact بحالة PENDING** |
| `branch_hours` | الساعات المنتظمة: عدة فترات لكل يوم، وتجاوز منتصف الليل |
| `hours_exceptions` | `kind`: emergency > temporary > special/holiday (الأولوية مشتقة من النوع)، ونطاق تاريخ |
| `contact_points` | أرقام ونقاط تواصل حسب النية (`scope` brand/branch، `kind`، `is_public`) |
| `social_links` | حسابات التواصل |
| `facts` | **سجل الحقائق:** مفتاح، قيمة، الحالة (المفردات المعتمدة D-224 + VERIFIED)، المصدر، الدليل، الموثِّق، تاريخ التحقق والانتهاء |
| `external_references` | معرّفات الأنظمة الخارجية (GBP · POS · ERP · App) لكل كيان |
| `channel_sync_states` · `sync_jobs` | حالة المزامنة لكل كيان وقناة + طابور بإعادة محاولة 1 → 5 → 30 دقيقة → 6 ساعات (حد أقصى 5 محاولات) |
| `integrations` | **سجل التكاملات الوحيد:** الحالة، البيئة، آخر نجاح، انتهاء الرمز، النطاق، يحتاج إعادة ربط. **بلا أسرار** |

### 3.3 المحتوى والمنيو (Phase 2–3)
| الجدول | الغرض |
|---|---|
| `menu_categories` · `menu_subcategories` · `products` · `product_prices` · `product_branch_overrides` | المنيو المجمّد v1.0. المعرّفات `CAT-`/`PRD-` ثابتة. **لا إعادة استخدام لمعرّف متقاعد**، والقادم PRD-00193. التجاوز لكل فرع: موروث / مُعدّل / إعادة ضبط |
| `media` · `media_usages` | **مكتبة وسائط واحدة:** الحقوق، والترخيص، والانتهاء، والنص البديل ar/en، ونقطة التركيز، وحالة الاعتماد. Usage graph لمعرفة أين تُستخدم كل صورة |
| `pages` · `page_sections` | صفحات من أقسام معتمدة فقط (Design lock) |
| `experiences` | التجارب الديناميكية: حملات، مواسم، فعاليات، SHELTER Family، موظف الشهر. **التقويم = عرض فوق هذا الجدول** (ليس جدولًا ثانيًا) |
| `redirects` | خريطة التحويل (301 / 302 / 410) + عدّاد الاستخدام |

### 3.4 الطلبات (Phase 4) — Applications Core
| الجدول | الغرض |
|---|---|
| `applications` | النواة المشتركة: `type` (JOB / FR / INQ)، والرقم المرجعي، والحالة، والمسؤول، والتواريخ |
| `job_applications` · `partnership_applications` · `inquiries` | الحقول الخاصة بكل نوع (التوظيف حسب `RECRUITMENT-DATA-MODEL.md`) |
| `application_notes` · `application_status_history` · `application_files` | ملاحظات، وتاريخ حالات، وملفات خاصة |

### 3.5 الجودة والعمليات (Phase 6–7)
| الجدول | الغرض |
|---|---|
| `rum_metrics` | Web Vitals **مجمّعة** (اليوم، مجموعة المسار، الجهاز، p75، العينات). **بلا معرّف وبلا IP** |
| `releases` | سجل الإصدارات: الرقم، والـCommit، والتغييرات، ونتيجة الـCI، والحالة، والإصدار المستقر السابق |
| `exports` | ملفات تصدير مؤقتة: المالك، والانتهاء، والتصنيف |

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
