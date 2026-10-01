# RECRUITMENT DATA MODEL

> **الحالة:** `DRAFT — PENDING OWNER ARCHITECTURE CHECK (M28 Phase 8)` · **آخر تحديث:** 2026-10-01
> **المصدر:** مواصفة الـOwner (M28 §06–§13، §21، §22، §26، §27، §28، §30، §36، §42–§45، §49، §52، §56، §58، §59).
> **المحرك:** صيغة محايدة تعمل على **MySQL 8 / MariaDB 10.6+** (Cloudways Flexible) أو **PostgreSQL 14+**. الفروقات مذكورة. المنصة نفسها قرار مفتوح (DB-08).
> **المبادئ:**
> - Normalized.
> - بلا جداول مكررة.
> - كل جدول فيه `created_at` / `updated_at`.
> - Soft archive للطلبات.
> - الحذف النهائي فقط بقرار الـOwner.
> - **لا حذف تلقائي.**

## 1. نظرة عامة

```
consent_versions ─┐
jordan_cities ────┤
                  ▼
job_applications ──┬── application_attachments
   │               ├── application_identity_secure   (1:1, مشفّر)
   │               ├── application_consents          (1:1)
   │               ├── application_status_history    (1:N)
   │               ├── application_notes             (1:N)
   │               ├── application_interviews ── interview_locations
   │               └── application_links             (إشارات تطابق مع طلبات سابقة — بلا دمج)
reference_sequences
recruitment_saved_filters · user_preferences · recruitment_settings
audit_logs (مشترك مع كل الـDashboard) · upload_sessions (مسودات رفع مؤقتة)
```

## 2. الجداول

### 2.1 `job_applications`: الطلب (بلا بيانات الهوية الحساسة)
| العمود | النوع (MySQL / PG) | قيود | ملاحظات |
|---|---|---|---|
| `id` | BIGINT UNSIGNED AI / BIGSERIAL | PK | داخلي، لا يظهر للمتقدم |
| `application_number` | VARCHAR(20) | UNIQUE, NOT NULL | `JOB-2026-00125` (§3) |
| `full_name` | VARCHAR(150) | NOT NULL | كما كُتب (عربي/إنجليزي/مختلط). تُزال المسافات الزائدة فقط |
| `phone_raw` | VARCHAR(40) | NOT NULL | كما كُتب |
| `phone_normalized` | VARCHAR(20) | NOT NULL, INDEX | E.164 إن أمكن، وإلا أرقام فقط (§4) |
| `email` | VARCHAR(254) | NOT NULL | كما كُتب |
| `email_normalized` | VARCHAR(254) | NOT NULL, INDEX | Lowercase + trim |
| `gender` | ENUM('male','female') / VARCHAR + CHECK | NOT NULL | العرض: ذكر / أنثى |
| `birth_date` | DATE | NOT NULL | `YYYY-MM-DD`. **العمر يُحسب عند العرض ولا يُخزن** |
| `marital_status` | ENUM('single','married','other') | NOT NULL | أعزب / متزوج / أخرى |
| `nationality_type` | ENUM('jordanian','non_jordanian') | NOT NULL | |
| `nationality_text` | VARCHAR(80) | NULL، **NOT NULL إذا** `non_jordanian` (CHECK) | "ما هي جنسيتك؟" نص حر |
| `city_id` | INT | FK → `jordan_cities.id`, NOT NULL | قائمة داخلية (§2.12) |
| `area_text` | VARCHAR(120) | NOT NULL | المنطقة، نص حر |
| `job_title_text` | VARCHAR(150) | NOT NULL, INDEX (prefix) | كما كُتب، **بلا تصحيح تلقائي** |
| `job_tag` | VARCHAR(80) | NULL | تصنيف داخلي لاحق من الـDashboard (ليس من المتقدم) |
| `education_level` | ENUM('tawjihi_pass','tawjihi_fail','bachelor','master','doctorate','student') | NOT NULL | الخيارات الستة المعتمدة فقط |
| `experience_band` | ENUM('none','lt1','y1_2','y3_5','y6_10','gt10') | NOT NULL | ترتيب الفرز مُعرّف (§5) |
| `same_field_experience` | BOOLEAN | NOT NULL | |
| `currently_employed` | BOOLEAN | NOT NULL | |
| `expected_salary_jod` | DECIMAL(9,2) | NOT NULL, CHECK > 0 AND ≤ 99999.99 | يُعرض `450 د.أ`. الحد الأعلى تقني ضد الإدخال العبثي |
| `has_driving_license` | BOOLEAN | NOT NULL | |
| `notes_text` | TEXT (≤ 3000 حرف عبر التحقق) | NOT NULL | "ملاحظات إضافية" من المتقدم. **غير** الملاحظات الداخلية |
| `status` | ENUM('received','under_review','interview_shortlisted','interviewed','accepted','rejected','archived') | NOT NULL, INDEX | الحالة الداخلية (§6) |
| `status_before_archive` | ENUM(…نفس القيم عدا archived) | NULL | لإلغاء الأرشفة |
| `first_viewed_at` | DATETIME / TIMESTAMPTZ | NULL, INDEX | **حالة القراءة منفصلة عن الحالة** (شارة "جديد" = NULL) |
| `first_viewed_by` | BIGINT | NULL, FK users | |
| `primary_attachment_id` | BIGINT | NULL، **NOT NULL عند الإرسال** (تحقق تطبيقي) | السيرة الذاتية الأساسية |
| `applicant_group_size` | INT | NOT NULL DEFAULT 1 | عدد الطلبات المرتبطة (Cache لعرض "لديه X طلبات") |
| `submitted_at` | DATETIME | NOT NULL, INDEX | تاريخ التقديم (فرز الأحدث) |
| `updated_at` | DATETIME | NOT NULL, INDEX | "Last Updated" (§53) |
| `archived_at` | DATETIME | NULL | |
| `idempotency_key` | CHAR(36) | UNIQUE | منع الإرسال المكرر وإعادة الإرسال (Replay) |
| `form_version` | VARCHAR(20) | NOT NULL | نسخة النموذج (للتتبع عند تغيير الحقول) |

**الفهارس:**
- `(status, submitted_at DESC)`
- `(city_id)`
- `(education_level)`
- `(experience_band)`
- `(gender)`
- `(nationality_type)`
- `(expected_salary_jod)`
- `(submitted_at)`
- FULLTEXT أو `pg_trgm`: `(full_name, job_title_text)` للبحث السريع
- فهرس على `phone_normalized` و`email_normalized` و`application_number`

### 2.2 `application_identity_secure`: الرقم الوطني ووثيقة الهوية (1:1)
| العمود | النوع | ملاحظات |
|---|---|---|
| `application_id` | BIGINT | PK + FK (ON DELETE CASCADE) |
| `id_type` | ENUM('national_id','passport_or_document') | أردني ← رقم وطني · غير أردني ← جواز أو وثيقة |
| `id_ciphertext` | VARBINARY(256) / BYTEA | **AES-256-GCM** على مستوى التطبيق (`RECRUITMENT-SECURITY.md` §5) |
| `id_nonce` | BINARY(12) | فريد لكل قيمة |
| `id_key_version` | SMALLINT | لتدوير المفاتيح |
| `id_last4` | CHAR(4) | للعرض المقنّع `********1234` فقط |
| `id_blind_index` | CHAR(64) | HMAC-SHA256 بمفتاح منفصل على القيمة المطبّعة. **INDEX لاكتشاف التكرار بلا Plaintext** |

> لا يظهر الرقم كاملًا إلا بعرض صريح من الـOwner، ويُسجل في الـAudit (§27).
> لا يُرسل إلى GA4 أو GTM أو أي Log.

### 2.3 `application_attachments`
| العمود | النوع | ملاحظات |
|---|---|---|
| `id` | BIGINT | PK |
| `application_id` | BIGINT | FK (CASCADE) — **NULL أثناء مرحلة المسودة** (مرتبط بـ`upload_session_id`) |
| `upload_session_id` | CHAR(36) | FK → `upload_sessions` |
| `storage_key` | CHAR(32) | اسم عشوائي (128-bit hex). **المسار لا يُشتق من اسم المتقدم** |
| `storage_path` | VARCHAR(255) | نسبي داخل `private_html/recruitment/` |
| `original_filename` | VARCHAR(255) | **Metadata فقط** (منظّف للعرض، لا يُستخدم للمسار) |
| `extension` | VARCHAR(16) | |
| `declared_mime` | VARCHAR(127) | ما أرسله المتصفح (غير موثوق) |
| `detected_mime` | VARCHAR(127) | من فحص الـSignature |
| `file_family` | ENUM('pdf','word','spreadsheet','presentation','text','image') | العائلة المسموحة (`RECRUITMENT-SECURITY.md` §3) |
| `size_bytes` | BIGINT | |
| `sha256` | CHAR(64) | للنزاهة والكشف عن التكرار |
| `cv_score` | SMALLINT | ناتج الاكتشاف (0–100) |
| `cv_detection` | ENUM('auto_confident','auto_single','applicant_selected','not_cv','pending') | |
| `scan_status` | ENUM('validated','rejected','scan_unavailable','clean','infected') | |
| `created_at` | DATETIME | |

### 2.4 `upload_sessions`: مسودات الرفع قبل الإرسال
| العمود | ملاحظات |
|---|---|
| `id` CHAR(36) PK | Token يرتبط بجلسة النموذج والـCSRF |
| `created_at` / `expires_at` | تُنظف الملفات **غير المرسلة** بعد 24 ساعة (ليست طلبات) |
| `ip_hash` | HMAC لعنوان IP **لأغراض Rate limit فقط**، ويُحذف مع الجلسة |

### 2.5 `consent_versions` و`application_consents`
- **`consent_versions`:**
  - `id`
  - `version` (مثل `careers-consent-v1`)
  - `text_ar`، **ونسخة 1 = النص المعتمد حرفيًا** (M28 §21)
  - `active_from`
  - `is_active`
- **`application_consents`:**
  - `application_id` (PK/FK)
  - `consent_version_id` (FK)
  - `accepted` (BOOLEAN = true)
  - `accepted_at`
  - **لا IP ولا User-Agent** (تقليل البيانات)

### 2.6 `application_status_history`
`id` · `application_id` · `old_status` · `new_status` · `actor_id` · `changed_at` · `internal_note` (NULL) · `bulk_operation_id` (NULL، يربط تغييرات الـBulk بعملية واحدة)

### 2.7 `application_notes`: ملاحظات داخلية متعددة
`id` · `application_id` · `body` (TEXT) · `author_id` · `created_at` · `updated_at` · `deleted_at` (NULL)

**التعديل أو الحذف:**
- **لا Overwrite صامت:**
  - كل تعديل أو حذف يُسجل في الـAudit (قبل/بعد).
  - الحذف = `deleted_at` (Soft).
  - يُحذف فعليًا فقط مع الحذف النهائي للطلب.

### 2.8 `application_interviews` و`interview_locations`
- **`application_interviews`:**
  - `id` · `application_id`
  - `interview_date` (DATE) · `interview_time` (TIME)
  - `location_id` (FK)
  - `internal_notes`
  - `created_by` · `created_at` · `updated_at`
  - `is_current` (BOOLEAN، يسمح بإعادة جدولة مع حفظ التاريخ)
- **`interview_locations`:**
  - `id` · `name_ar` · `name_en` · `is_active` · `sort_order`
  - **Seed:** `SHELTER COFFEE DRIVE` · `SHELTER COFFEE HOUSE`
  - تُدار من Settings
- **لا جداول تقييم ولا Scores** (M28 §31).

### 2.9 `application_links`: المتقدم السابق (بلا دمج)
`application_id` · `linked_application_id` · `signal` ENUM('id_blind_index','phone','email') · `created_at` · PK مركب

**آلية الإنشاء:**
- تُنشأ عند الإرسال بمقارنة الإشارات المطابقة تمامًا.
- **لا دمج، ولا حذف، ولا تعديل** على الطلبات المرتبطة.

### 2.10 `reference_sequences`
`year` (PK) · `last_value` (INT). تفاصيل التوليد في §3.

### 2.11 تفضيلات الـOwner
- **`recruitment_saved_filters`:**
  - `id` · `owner_id`
  - `name` (مثل "مرشحين محاسبة إربد")
  - `filter_json` (هيكل مُتحقق منه)
  - `sort` · `created_at`
- **`user_preferences`** (مشترك مع الـDashboard):
  - `user_id` · `key` · `value_json`
  - **أمثلة المفاتيح:**
    - `recruitment.columns`: الظاهرة والترتيب.
    - `recruitment.density`: `comfortable` / `compact`.
    - `recruitment.page_size`: 25 / 50 / 100، **الافتراضي 50.**

### 2.12 `jordan_cities`: قائمة المدن (تُدار من Settings)
`id` · `name_ar` · `name_en` · `governorate_ar` · `governorate_en` · `sort_order` · `is_active` · `source` · `verification_status`

> **الحالة:** `PENDING DATA VERIFICATION`.
> - لم نتحقق بعد من قائمة رسمية كاملة لمدن الأردن.
> - المصدر المقترح للتحقق: القائمة الرسمية للبلديات من وزارة الإدارة المحلية، مع التقسيمات الإدارية لدائرة الإحصاءات العامة.
> - **لا تُخترع القائمة، ولا تُستبدل "المدينة" بـ"المحافظة" بصمت.**
> - عمود المحافظة يُخزن **إضافة** (مفيد للفلترة)، ولا يحل محل المدينة.

### 2.13 `recruitment_settings`
`key` · `value_json`:
- `active_consent_version`
- `form_version`
- حدود الرفع التقنية بعد الـAudit
- عتبات اكتشاف الـCV

### 2.14 `audit_logs`: مشترك، Append-only
| العمود | ملاحظات |
|---|---|
| `id` · `occurred_at` | |
| `actor_type` (owner / system / applicant) · `actor_id` | المتقدم بلا حساب ← `applicant` + رقم الطلب في `target` |
| `action` | مثل `application.created` · `application.first_viewed` · `status.changed` · `status.bulk_changed` · `note.added/edited/deleted` · `interview.created/changed` · `identity.revealed` · `export.created` · `attachments.downloaded` · `application.archived` · `application.permanently_deleted` · `admin.corrected` |
| `target_type` · `target_id` · `target_label` | `target_label` = رقم الطلب فقط (ليس الاسم) |
| `before_json` · `after_json` | **القيم الحساسة تُحذف أو تُقنّع:** رقم الهوية `[REDACTED]`، وأسماء الملفات تُستبدل بمعرّف المرفق |
| `context_json` | مثل عدد الطلبات في الـBulk، ونطاق التصدير |

**الصلاحيات على مستوى قاعدة البيانات:** مستخدم التطبيق `INSERT` و`SELECT` فقط على هذا الجدول.

## 3. توليد رقم الطلب (Server-side فقط)
```
BEGIN;
  INSERT INTO reference_sequences(year,last_value) VALUES (:y,0)
    ON DUPLICATE KEY UPDATE year=year;            -- PG: ON CONFLICT DO NOTHING
  SELECT last_value FROM reference_sequences WHERE year=:y FOR UPDATE;
  UPDATE reference_sequences SET last_value=last_value+1 WHERE year=:y;
  -- application_number = 'JOB-' || :y || '-' || LPAD(last_value+1, 5, '0')   (يتجاوز 5 خانات تلقائيًا بعد 99999)
  INSERT INTO job_applications(… application_number …);
COMMIT;
```
- **فريد:** `UNIQUE` + القفل على الصف.
- **لا تصادم:** معاملة واحدة.
- **قابل للبحث:** فهرس.
- **لا يولّد في المتصفح أبدًا.**

## 4. التطبيع (Normalize) — للقيم التقنية فقط (M28 §04)
- **الأرقام العربية/الهندية** (٠–٩ و۰–۹) ← لاتينية: في الهاتف والراتب والرقم الوطني.
- **الهاتف:**
  1. إزالة المسافات والشرطات والأقواس.
  2. `00` ← `+`.
  3. رقم أردني محلي `07XXXXXXXX` (10 أرقام) ← `+9627XXXXXXXX`.
  4. غير ذلك يُحفظ كما هو بعد التنظيف.
  - **لا رفض لأي صيغة منطقية**، ولا فرض لـ+962 على المتقدم.
- **البريد:** `trim` + `lowercase` في `email_normalized` فقط.
- **الرقم الوطني ورقم الوثيقة:** إزالة المسافات والشرطات، ثم Uppercase للوثائق، ثم التشفير والـBlind index.
- **النصوص الحرة** (الاسم، المنطقة، الوظيفة، الجنسية، الملاحظات):
  - **لا تصحيح ولا ترجمة ولا رفض لأي Script.**
  - فقط: trim، وتوحيد المسافات، وإزالة محارف التحكم غير المرئية.

## 5. الفرز والفلترة
| الفرز (M28 §39) | التنفيذ |
|---|---|
| الأحدث أولًا (افتراضي) / الأقدم أولًا | `submitted_at DESC/ASC` |
| الراتب الأعلى ← الأقل / الأقل ← الأعلى | `expected_salary_jod` |
| الخبرة الأعلى ← الأقل / الأقل ← الأعلى | ترتيب ثابت: `none < lt1 < y1_2 < y3_5 < y6_10 < gt10` (عمود ترتيب أو CASE) |

**التنقل بين الصفحات:**
- **Traditional pagination** (`LIMIT/OFFSET` مع `COUNT`).
- الأحجام 25 / 50 / 100.
- **مع حجم البيانات المتوقع** (آلاف الطلبات)، `OFFSET` مع الفهارس أعلاه كافٍ.
- يُعاد التقييم إذا تجاوزت 100 ألف.

**البحث السريع:**
- يعمل على: الاسم، والهاتف (`phone_normalized`)، والبريد، ورقم الطلب، والوظيفة، والمدينة.
- **Debounce 300ms** في الواجهة، مع إلغاء الطلب السابق.

## 6. الحالات
**التعريف والانتقالات:**
- التعريف والربط بما يراه المتقدم: [`RECRUITMENT-PUBLIC-STATUS-MAPPING.md`](RECRUITMENT-PUBLIC-STATUS-MAPPING.md).
- أي حالة ← أي حالة مسموح للـOwner، مع تسجيل التاريخ.
- **الأرشفة** = `status='archived'` + `archived_at` + `status_before_archive`.

## 7. الحذف النهائي (M28 §52)
**من يحذف ومتى:**
- Owner فقط.
- من **الأرشيف** فقط.
- **لا Bulk Permanent Delete.**

**التنفيذ — معاملة واحدة:**
1. حذف صفوف الطلب والهوية المشفرة والموافقة والملاحظات والمقابلات والروابط والتاريخ (CASCADE).
2. حذف الملفات من التخزين الخاص بعد نجاح المعاملة. مع إعادة المحاولة وتسجيل أي ملف يتيم.
3. **قيد Tombstone في `audit_logs`:**
   - `application.permanently_deleted`
   - رقم الطلب، والتاريخ، والفاعل، وعدد المرفقات المحذوفة.
   - **بلا أي بيانات شخصية.**

> النسخ الاحتياطية تحتفظ بالبيانات حتى انتهاء مدة احتفاظها (موثق في `CLOUDWAYS-RECRUITMENT-ARCHITECTURE.md` §6).
