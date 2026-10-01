# DATA CLASSIFICATION — تصنيف البيانات وقواعده

| البند | القيمة |
|---|---|
| **الغرض** | تصنيف كل جدول وحقل إلى PUBLIC / INTERNAL / CONFIDENTIAL / SENSITIVE، وربط التصنيف بمن يقرأ وأين يُخزن وكيف يُصدّر وماذا يصل للتحليلات (M35 §53) |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 1** (database/data model). يُطبّق في كل المراحل التالية |
| **الأولوية** | P0 |
| **المتطلبات** | M35 §40 §52 §53 · FINAL-ARCHITECTURE-REVIEW §11 · S-2 · AC-2 · CAREERS-045 · CAREERS-072 · CAREERS-074 · CAREERS-076 · FRAN-062 · FRAN-092 · PRIV-013 · PRIV-014 · SEC-002 · OPS-037 · M36 §15 |
| **المرجع الملزم للأسماء** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) §3 (§4.5 يحيل إلى هذا الملف) |

## 1. التعريفات
| التصنيف | التعريف | أمثلة (M35 §53 أولًا) |
|---|---|---|
| **PUBLIC** | مخصص للنشر **بعد الاعتماد** | سعر المنيو · الساعات · صفحة منشورة · نسخ الصور المعتمدة |
| **INTERNAL** | تشغيلي، ليس شخصيًا، ضرره محدود إن تسرّب | ملاحظة داخلية على محتوى · مسودة لم تُنشر · إعدادات · إشارات |
| **CONFIDENTIAL** | بيانات شخصية، أو بيانات تجارية حساسة | مستندات أعمال الفرنشايز · الاستفسارات · طلبات التوظيف · سجل التدقيق |
| **SENSITIVE** | تسريبها يسبب ضررًا جسيمًا | **الرقم الوطني / رقم الجواز** · أسرار المصادقة · مفاتيح التشفير · رموز OAuth |

**قواعد الحسم:**
1. **السجل يأخذ أعلى تصنيف في حقوله.** ملاحظة مرتبطة بمتقدم = CONFIDENTIAL (مثال M35 "Internal note: INTERNAL" ينطبق على الملاحظات غير المرتبطة بأشخاص).
2. المسودة غير المنشورة لكيان عام = INTERNAL حتى النشر.
3. الحقل غير المصنف = **فشل اختبار** (§5).

## 2. القواعد لكل تصنيف
| | PUBLIC | INTERNAL | CONFIDENTIAL | SENSITIVE |
|---|---|---|---|---|
| **من يقرأ** | الجميع بعد النشر | الـOwner (Server-side) | الـOwner + Policy على مستوى الكائن | الـOwner + **Step-up** لكل إظهار |
| **التخزين** | قاعدة البيانات + الوسائط العامة/CDN | قاعدة البيانات | قاعدة البيانات + **التخزين الخاص** (`private careers` · `private partnerships`) خارج الجذر العام | **مشفّر في التطبيق** (AES-256-GCM) أو Hash (Argon2id). المفاتيح خارج قاعدة البيانات |
| **العرض** | عادي | عادي | عادي داخل الوحدة. أسماء الملفات والهويات لا تظهر في الإشعارات | **مقنّع** `********1234`، والإظهار مسجل |
| **التصدير** | عادي + Audit | عادي + Audit | **تأكيد صريح** + Audit + قناع حيث يلزم (M35 §40) | **مستبعد افتراضيًا.** الكامل باختيار صريح + Step-up + تحذير + Audit. **الأسرار لا تُصدّر أبدًا** |
| **التحليلات** | **فقط هنا، ومجمّعًا** | لا | لا | لا |
| **السجلات و`signals`** | مسموح | مسموح | معرّفات/أرقام مرجعية فقط | ممنوع (`[REDACTED]`) |
| **الاحتفاظ** | دورة المحتوى (Archive) | حسب الوحدة | [`PRIVACY-CENTER`](PRIVACY-CENTER.md) §3 | كالسجل الأب. التوظيف: قرار الـOwner فقط |
| **النسخ الاحتياطي** | عادي | عادي | ضمن النسخة (التخزين الخاص مشمول — A-08) | نص مشفّر فقط في النسخة |

## 3. تصنيف الجداول (أسماء PLATFORM-ARCHITECTURE حرفيًا)
### 3.1 النواة
| الجدول | التصنيف | ملاحظات على الحقول |
|---|---|---|
| `users` | SENSITIVE | Hash كلمة المرور · سر TOTP ورموز الاسترداد مشفّرة. الاسم والبريد = CONFIDENTIAL |
| `webauthn_credentials` (**إضافة مقترحة**) | CONFIDENTIAL | مفاتيح عامة فقط، بلا سر |
| `sessions` | CONFIDENTIAL | — |
| `audit_logs` | CONFIDENTIAL | قبل/بعد مقنّع. لا قيمة SENSITIVE أبدًا |
| `content_versions` | INTERNAL | **للكيانات القابلة للنشر فقط**. بيانات الطلبات لا تدخله (لها `application_status_history`) |
| `settings` | **لكل صف** (عمود `classification`) | صفوف الأسرار (رموز OAuth) = SENSITIVE مشفّرة، ولا تُعرض |
| `feature_flags` · `signals` · `reference_sequences` · `idempotency_keys` · `scheduled_job_runs` | INTERNAL | `signals` بلا PII |

### 3.2 البيانات الرئيسية
| الجدول | التصنيف | ملاحظات |
|---|---|---|
| `markets` · `countries` · `cities` · `branches` · `branch_hours` · `hours_exceptions` · `social_links` | PUBLIC | الحقل بلا قيمة معتمدة = NULL + Fact PENDING (INTERNAL حتى الاعتماد) |
| `contact_points` | PUBLIC إذا `is_public`، وإلا INTERNAL | الأرقام تُعرض في سياقها فقط (D-057، D-059) |
| `facts` | القيمة PUBLIC عند APPROVED/VERIFIED | المصدر والدليل والملاحظات = INTERNAL |
| `external_references` · `channel_sync_states` · `sync_jobs` · `integrations` | INTERNAL | `integrations` **بلا أسرار** |

### 3.3 المحتوى والمنيو
| الجدول | التصنيف | ملاحظات |
|---|---|---|
| `menu_categories` · `menu_subcategories` · `products` · `product_prices` · `product_branch_overrides` | PUBLIC (المنشور) | المسودات INTERNAL |
| `media` | النسخ المعتمدة PUBLIC | الأصل غير المخدوم وبيانات الحقوق INTERNAL. **أسماء الأشخاص وموافقاتهم ومراجع مستنداتها = CONFIDENTIAL** |
| `media_usages` · `redirects` | INTERNAL | — |
| `pages` · `page_sections` · `experiences` | PUBLIC (المنشور) | ملف الموظف العام PUBLIC **بعد موافقته** فقط. بيانات HR لا تدخل النظام (DX-036) |

### 3.4 الطلبات
| الجدول | التصنيف | ملاحظات |
|---|---|---|
| `applications` · `job_applications` · `application_notes` · `application_status_history` | CONFIDENTIAL | تاريخ الميلاد والجنسية والراتب داخل CONFIDENTIAL |
| `application_identity_secure` (من RECRUITMENT-DATA-MODEL) | **SENSITIVE** | §4 |
| `application_attachments` | CONFIDENTIAL | تخزين خاص. قد تحتوي صور وثائق ← لا معاينة إلا من نسخة معاد ترميزها |
| بقية جداول التوظيف (RECRUITMENT-DATA-MODEL): `application_interviews` · `application_links` · `upload_sessions` | CONFIDENTIAL | `ip_hash` في `upload_sessions` لـRate limit فقط ويُحذف معها. اسم المرفقات الملزم = `application_attachments` (لا `application_attachments`) |
| `interview_locations` · `jordan_cities` · `recruitment_settings` | INTERNAL | — |
| `partnership_applications` | CONFIDENTIAL | تجاري حساس (FRAN-062). القدرة الاستثمارية غير مجمّعة أصلًا (PENDING OWNER DECISION) |
| `inquiries` | CONFIDENTIAL | AC-2 |
| `consent_versions` / `application_consents` (**إضافة مقترحة**) | INTERNAL / CONFIDENTIAL | النص INTERNAL، والقبول المرتبط بشخص CONFIDENTIAL |

### 3.5 الجودة والعمليات والوحدات P1
| الجدول | التصنيف | ملاحظات |
|---|---|---|
| `rum_metrics` | INTERNAL | مجمّع، بلا معرّف ولا IP |
| `releases` | INTERNAL | — |
| `exports` | **يرث أعلى تصنيف في محتواه** | العمود `classification` إلزامي |
| `reviews` (**إضافة مقترحة**) | CONFIDENTIAL | اسم المراجع ونصه بيانات شخصية |
| `feedback` (**إضافة مقترحة**) | INTERNAL · التعليق CONFIDENTIAL | التعليق قد يحتوي PII يكتبه العميل |
| جداول الإطار `jobs` · `failed_jobs` · `cache` | INTERNAL | **حمولة المهام = معرّفات فقط، بلا PII.** مفاتيح Rate limit = HMAC قصير العمر |

## 4. الرقم الوطني ورقم الجواز (SENSITIVE)
| الإجراء | التنفيذ (CAREERS-045، RECRUITMENT-SECURITY §5) |
|---|---|
| التشفير | **AES-256-GCM** في التطبيق · Nonce عشوائي 96-bit لكل قيمة · `key_version` للتدوير |
| التكرار | **Blind index** = `HMAC-SHA256(HMAC_KEY, normalized_id)` بلا فك تشفير |
| العرض | مقنّع `********1234` في القوائم والجداول والتصدير الافتراضي |
| الإظهار | **Owner فقط** + Step-up بعد 10 دقائق + `audit_logs` (`identity.revealed`) |
| ممنوع | GA4/GTM وأي تحليلات · السجلات ورسائل الأخطاء · الروابط والـQuery string · `signals` · الإشعارات |
| المفاتيح | `RECRUITMENT_ID_ENC_KEY` · `RECRUITMENT_ID_HMAC_KEY` خارج Git والواجهة، ونسخة Offline لدى الـOwner. **مكانها يُفضّل ألا يُنسخ مع نسخة القاعدة نفسها** (يُحسم في A-08/A-11) |

## 5. التنفيذ (مصدر واحد)
- **خريطة واحدة** `config/data_classification.php`: `table.column => class`. يقرأها: خدمة التصدير · منقّي السجلات · قائمة التحليلات المسموحة · مُقنّع `audit_logs` · جرد الخصوصية.
- حقول SENSITIVE: Cast مشفّر في الـModel، ولا تظهر في `toArray()`/JSON إلا عبر إجراء إظهار صريح.
- **لا حقل جديد بلا تصنيف:** اختبار في الـCI يقارن أعمدة الـMigrations بالخريطة.

## 6. اللوحة في الـDashboard
- **لا شاشة مستقلة** (البساطة). التصنيف يظهر في:
  - الجودة ← **الخصوصية**: عمود التصنيف في جرد البيانات.
  - شارة التصنيف على كل ملف تصدير، وعلى الحقول المقنّعة.

## 7. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| DC-T01 | إضافة عمود في Migration اختبارية بلا تصنيف | فشل الـCI |
| DC-T02 | قراءة `application_identity_secure` مباشرة من القاعدة | نص مشفّر فقط. لا رقم واضح في أي عمود |
| DC-T03 | البحث عن تكرار برقم وطني | يعمل بالـBlind index دون فك التشفير |
| DC-T04 | قائمة الطلبات والتصدير الافتراضي | `********1234` |
| DC-T05 | إظهار بعد 11 دقيقة من المصادقة | Step-up، ثم `identity.revealed` في `audit_logs` بلا القيمة |
| DC-T06 | تصدير CONFIDENTIAL | يطلب تأكيدًا صريحًا ويُسجل |
| DC-T07 | مسح السجلات وحمولات GA4 و`signals` و`jobs` بعد إرسال طلب بقيمة Canary | لا ظهور للقيمة |
| DC-T08 | `GET` لأي ملف في التخزين الخاص عبر الويب | `404` |
| DC-T09 | `toArray()`/JSON لـModel فيه حقل SENSITIVE | الحقل غائب |

**إغلاق البند:** قالب DoD العشري (OPS-054). **أثر النسخ الاحتياطي:** النسخة تحمل قيم SENSITIVE مشفّرة فقط؛ فقدان المفاتيح = فقدان القدرة على قراءتها.

## 8. ما لا يُنفّذ
- لا تصنيف يدوي من الواجهة في V1 (الخريطة في الكود وتتغير مع الإصدار).
- لا إرسال لأي بيانات غير PUBLIC للتحليلات، ولا PUBLIC غير مجمّع.
- لا تخزين للقدرة الاستثمارية أو أي حقل لم يعتمده الـOwner.
