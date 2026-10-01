# FACT REGISTRY — سجل الحقائق

| البند | القيمة |
|---|---|
| **الغرض** | كل معلومة حساسة عن SHELTER (العلامة، الفروع، التواصل، الساعات، الادعاءات، الجوائز، الفرنشايز) لها **مصدر وحالة تحقق**. يمنع Claude أو أي محرر من اختراع معلومة أو إعادة استخدام معلومة قديمة. **غير المعتمد لا يُنشر** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 1** (Fact Registry + البذر من `04`) · الشاشة في **PHASE 3** · فحص النشر الكامل في **PHASE 6** |
| **الأولوية** | P0 (OPS-010) |
| **المتطلبات** | OPS-013 · OPS-014 · OPS-012 · OPS-048 · GOV-022 · GOV-024 · GOV-026 · GOV-027 · CONTENT-003 · CONTENT-005 · CONTENT-007 · SCHEMA-003 · GBP-004 · DX-005 · D-224 · G14-CF-11 · G14-CF-12 · M32 §03 · M36 §5 |
| **المراجع الملزمة** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) §3.2 §4.4 · [`MASTER-DATA-HUB`](../MASTER-DATA-HUB.md) §2 §14 · [`04-content-approval-register`](../phase-01-discovery/04-content-approval-register.md) · [`DATA-CLASSIFICATION`](DATA-CLASSIFICATION.md) |
| **اختبار القبول (OPS-003)** | الثقة + اتساق البيانات: لا رقم ولا ادعاء ولا تاريخ يظهر للعامة بلا اعتماد |

## 1. ما هو وما ليس هو
- **طبقة تحقق** فوق القيم، وجدول واحد `facts` (PLATFORM-ARCHITECTURE §3.2). **ليس مصدرًا ثانيًا للحقائق** (M32 §47).
- **يرث `04-content-approval-register` ويحل محله** بعد البذر (§7). بعدها `04` سجل تاريخي مجمّد.
- **بيانات المنتجات ليست هنا:** الأسماء والأسعار وحالة الاسم العربي في جداول المنيو (CMS-003، I18N-011). السجل يشير إليها ولا ينسخها (OPS-014).

## 2. الحقول (M32 §03) ← أعمدة `facts`
| الحقل (M32) | العمود | ملاحظات |
|---|---|---|
| Fact ID | `code` (`FACT-0001`) | ثابت، **لا يُعاد استخدامه** (مثل `PRD-`). البادئة `FACT-` تمنع الخلط مع قرارات المنيو `F-01…F-21` |
| Field | `key` + `label_ar` · `label_en` + `category` | المفتاح = مفتاح الاستخدام نفسه: `brand.founded_year` (في `settings`) · `contact.main` · `branch.BR-DRIVE.address_ar` · `award.{id}`. الفئات: brand · contact · branch · hours · social · claim · award · franchise · legal |
| Value | `value` (JSON: نص AR/EN أو رقم أو تاريخ) · `value_hash` | لقطة القيمة المعتمدة (§5) |
| Source | `source_type` + `source_ref` | الأنواع: `OWNER_DECISION` (D-xxx) · `OWNER_DASHBOARD` · `DOCUMENT` · `OFFICIAL_SOURCE` · `LEGACY_SITE` · `EXTERNAL_LISTING`. **الأخيران معلومة فقط ولا يكفيان للنشر** |
| Verification Status | `status` | §3 |
| Approved By | `approved_by` + `decision_ref` | الـOwner في V1. المرجع: D-xxx أو رقم سطر `audit_logs` |
| Verified Date | `verified_at` · `verified_by` · `evidence` | الدليل: وصف + رابط، أو ملف في التخزين الخاص |
| Last Reviewed | `last_reviewed_at` · `expires_at` (اختياري) | يغذي [`CONTENT-HEALTH`](CONTENT-HEALTH.md) |
| Used In | **محسوب** من محلّل الأثر ([`CHANGE-IMPACT`](CHANGE-IMPACT.md) §2). **لا يُخزن** | OPS-013 |
| Notes | `notes` | INTERNAL |
| — | `market_id` (NULL = كل الأسواق) · `supersedes_id` · `blocked_phrases[]` · `classification` | تجهيز لسوق ثانٍ · السلسلة التاريخية · عبارات ممنوعة للمرفوض (CONTENT-005) |

## 3. الحالات (مفردات D-224 + VERIFIED — G14-CF-12)
| الحالة | المعنى | يُنشر؟ | من يضعها |
|---|---|---|---|
| `APPROVED` | اعتمدها الـOwner صراحة: قرار مسجل، أو نشره للقيمة من الـDashboard | ✅ | الـOwner |
| `VERIFIED` | `APPROVED` + مطابقة لدليل موثق بتاريخ تحقق. **GBP وحده لا يكفي** (GBP-004) | ✅ | الـOwner (Claude يجهز الدليل فقط) |
| `PENDING OWNER APPROVAL` | قيمة مقترحة (من Claude، أو استيراد، أو اقتراح AI) تنتظر الـOwner | ❌ | النظام / Claude |
| `PENDING VERIFICATION` | قيمة موجودة (الموقع القديم، أدلة خارجية، ادعاء) تحتاج دليلًا أو تأكيدًا | ❌ | النظام / Claude |
| `MISSING` | لا قيمة. الحقل `NULL`. يُكتب في الوثائق `MISSING — OWNER INPUT REQUIRED` | ❌ | النظام |
| `REJECTED` | رفضها الـOwner، أو `OLD OR INCORRECT` (مثل 2018 و"منذ 2022" — CONTENT-005). **لا تُستخدم أبدًا** | ❌ | الـOwner |
| `SUPERSEDED` | حلت محلها قيمة أحدث، وتشير إليها | ❌ | النظام عند اعتماد البديل |

**الأسماء القديمة (aliases):** `PENDING OWNER REVIEW` = `PENDING OWNER APPROVAL` · `NEEDS OWNER VERIFICATION` / `OWNER VERIFICATION REQUIRED` / `NEEDS UPDATE` = `PENDING VERIFICATION`.

## 4. دورة الحياة
```
MISSING ──(إدخال الـOwner + نشر)──► APPROVED ──(دليل + تاريخ)──► VERIFIED
PENDING OWNER APPROVAL ──(اعتماد)──┘   ▲                              │ انتهى expires_at
PENDING VERIFICATION ──(تأكيد)─────────┘                              ▼
        └──(رفض)──► REJECTED  [نهائية]                   APPROVED + إشارة "تحتاج مراجعة"
APPROVED/VERIFIED ──(قيمة جديدة معتمدة)──► SUPERSEDED [نهائية] + صف جديد APPROVED
```
- **الـOwner وحده** ينقل إلى `APPROVED` أو `VERIFIED` أو `REJECTED`. Policy في الخادم، وإعادة تأكيد الهوية للحقائق المركزية (OPS-036).
- **`REJECTED` و`SUPERSEDED` نهائيتان.** القيمة الجديدة = صف جديد برقم جديد. لا حذف (Archive فقط).
- **مسار Claude والاستيراد والـAI لا يستطيع إنشاء `APPROVED`.** الخادم يفرض `PENDING OWNER APPROVAL` (CONTENT-003).
- **الصمت ≠ موافقة:** لا اعتماد تلقائي بمرور الوقت.
- **تعديل الـOwner لقيمة في الـDashboard:** المسودة `PENDING OWNER APPROVAL`، و**النشر بعد معاينة الأثر = الاعتماد** (`source_type = OWNER_DASHBOARD`، `decision_ref` = سطر التدقيق).

## 5. الربط بالقيم التشغيلية
| نوع الحقيقة | أين تعيش القيمة الحية | القاعدة |
|---|---|---|
| بلا جدول تشغيلي (الادعاءات، حقائق الفرنشايز، عدد الفروع كادعاء) | `facts.value` نفسه | **مصدر وحيد** |
| فوق حقل تشغيلي: العلامة والموقع العام (`settings` `brand.*` · `website.*`) · `branches` · `contact_points` · `branch_hours` · `hours_exceptions` · `social_links` · `awards` | الجدول المالك (PLATFORM-ARCHITECTURE §3.2: "كل قيمة مربوطة بـ`facts`") | `facts.value_hash` = بصمة القيمة المعتمدة. **تُعرض فقط إذا الحالة قابلة للنشر والبصمة مطابقة** |

- أي تغيير خارج مسار الاعتماد (Seeder، استيراد، SQL) ← البصمة لا تطابق ← **القيمة تختفي** + إشارة HIGH "قيمة تغيّرت بلا اعتماد".

## 6. الإخفاء في الموقع ورفع الإشارات
**الإخفاء (دالة واحدة `MasterData::publicValue($key)`):**
- تُرجع `null` لأي حالة غير `APPROVED`/`VERIFIED` ← المكون لا يُرسم، **بلا حاوية فارغة ولا نص "MISSING" على Production** (DX-012).
- **المعاينة والـStaging فقط:** شارة ظاهرة `MISSING — OWNER INPUT REQUIRED` أو `PENDING` لتنبيه الـOwner.
- **JSON-LD:** الخاصية تُحذف ولا تُخمّن (SCHEMA-003). **GBP:** المحوّل لا يرسل قيمة غير قابلة للنشر. **البحث:** لا تُفهرس ([`GLOBAL-SEARCH`](GLOBAL-SEARCH.md)).
- **النشر:** Token يشير إلى حقيقة غير قابلة للنشر، أو عبارة من `blocked_phrases` = **BLOCKING** ([`PUBLISH-GUARD`](PUBLISH-GUARD.md) G-17، G-18).

**الإشارات (`signals`، kind = ISSUE، الفئة DATA CONSISTENCY):**
| الحالة | الشدة · الأولوية | `dedupe_key` |
|---|---|---|
| حقيقة غير قابلة للنشر **مطلوبة في صفحة أساسية منشورة** (الرئيسية، المنيو، الفرع، التواصل) | HIGH · ACTION_REQUIRED | `fact:{code}:unpublishable` |
| حقيقة غير قابلة للنشر في سطح غير أساسي أو غير منشور | LOW · INFORMATION | نفسه |
| البصمة لا تطابق | HIGH · ACTION_REQUIRED | `fact:{code}:drift` |
| `VERIFIED` وانتهى `expires_at` ← تعود `APPROVED` | MEDIUM · IMPORTANT | `fact:{code}:expired` |
- **تجميع** (MON-008): الحقائق المعلقة على نفس بند الـOwner تظهر كبطاقة واحدة (مثل: عناوين الفرعين ← PO-010).
- **حل تلقائي:** عند صيرورة الحالة قابلة للنشر ← `RESOLVED` + Audit. كل إشارة تفتح الحقيقة وبند PENDING-OWNER-INPUT المرتبط.

## 7. البذر من `04` ثم التجميد
| في `04` | ← في `facts` |
|---|---|
| `APPROVED` (مع مرجع في عمود Owner Approval) | `APPROVED` + `decision_ref`. **بلا مرجع = `PENDING OWNER APPROVAL`** |
| `MISSING` | `MISSING` (`value = NULL`) |
| `PENDING VERIFICATION` · `NEEDS UPDATE` | `PENDING VERIFICATION` (القيمة كما وُجدت، `source_type = LEGACY_SITE / EXTERNAL_LISTING`) |
| `PENDING OWNER REVIEW` | `PENDING OWNER APPROVAL` |
| `REJECTED` / OLD OR INCORRECT | `REJECTED` + `blocked_phrases` |
| `OLD MENU — COMPARISON ONLY` | **لا يُستورد** (D-076). مصدر المنيو جداوله المجمّدة |

1. ملف بذرة واحد `data/master/seed/facts.json` يُولّد مرة من `04` (الأقسام A–F وC)، ولكل حقيقة `source_ref = 04:CR-xxx`.
2. **قرارات DECISION-LOG الأحدث تتغلب** على حالة `04` (ترتيب السلطة في CLAUDE.md).
3. `FactSeeder` يستورد ويطبع **تقرير عدد + Checksum** مقابل صفوف `04`.
4. بعد نجاح البذر على Staging: ترويسة `04` تصبح "مجمّد — المصدر `facts`"، **ولا صيانة مزدوجة** (G14-CF-11). **هذا التعديل خطوة في PHASE 1، وليس الآن.**
5. صفوف أسماء الأشخاص (مثل الفريق) = `classification = CONFIDENTIAL`، ولا تُنشر قبل الموافقة (DX-005).

## 8. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| الجدول · الحالات · دورة الحياة · قاعدة العرض · البذر · الإشارات · الشاشة | حقائق لكل سوق (`market_id`) عند سوق ثانٍ |
| الدليل: نص + رابط + ملف خاص اختياري | Press Kit يقرأ الحقائق المعتمدة (P1 — [`MEDIA-RIGHTS`](MEDIA-RIGHTS.md) §5) |

## 9. مكانه في الـDashboard
- **النظام ← الحقائق** (FINAL-ARCHITECTURE-REVIEW §10): عدادات لكل حالة · فلاتر (الحالة، الفئة) · جدول: المعرّف، الحقل، القيمة، المصدر، الحالة، اعتمدها، آخر مراجعة، مستخدمة في، ملاحظات (Wireframe `d-facts`).
- صفحة الحقيقة: السجل الكامل (كل الصفوف المرتبطة) · الدليل · "مستخدمة في" · الأفعال: اعتماد · تحقق بدليل · رفض · "تمت المراجعة".
- شارة الحالة تظهر أيضًا بجانب كل قيمة في **النظام ← البيانات العامة**.
- Owner فقط (OPS-050). بلا كود (OPS-051).

## 10. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| FR-T1 | مصفوفة الانتقالات (Unit) | كل انتقال غير مسموح يُرفض، ومنه الانتقال من `REJECTED` إلى `APPROVED` |
| FR-T2 | إنشاء حقيقة بحالة `APPROVED` من مسار الاستيراد بلا `decision_ref` | تُخزن `PENDING OWNER APPROVAL` |
| FR-T3 | حقيقة `brand.founded_year` بحالة `PENDING VERIFICATION` | لا قيمة ولا حاوية في HTML الرئيسية و"من نحن"، ولا `foundingDate` في JSON-LD |
| FR-T4 | تعديل `branches.address_ar` مباشرة في القاعدة | العنوان يختفي من صفحة الفرع + إشارة HIGH واحدة (`fact:…:drift`) |
| FR-T5 | اعتماد الحقيقة من الـOwner | الإشارة `RESOLVED`، والقيمة تظهر بعد النشر، وسطر `audit_logs` فيه قبل/بعد |
| FR-T6 | ساعة بعد `expires_at` لحقيقة `VERIFIED` | تصبح `APPROVED` + إشارة MEDIUM، والقيمة **تبقى ظاهرة** |
| FR-T7 | تشغيل `FactSeeder` | العدد والـChecksum يطابقان صفوف `04` ناقص المستبعد، ولكل حقيقة `source_ref` |
| FR-T8 | مسودة صفحة فيها "منذ 2022" | فحص النشر BLOCKING |
| FR-T9 | `/dashboard/system/facts*` بلا جلسة أو بمستخدم غير Owner | إعادة توجيه / 403 |
| FR-T10 | الشاشة على مصفوفة العروض بالعربي والإنجليزي | بلا Overflow، وaxe بلا Serious/Critical |

## 11. ما لا يُفعل
- لا اعتماد تلقائي، ولا "تحقق" من GBP أو الموقع القديم أو الأدلة وحدها.
- لا حقائق يكتبها AI أو Claude: اقتراح بحالة معلقة فقط (CONTENT-003).
- لا حذف لأي حقيقة. لا إعادة استخدام لمعرّف.
- لا عرض لمصدر الحقيقة أو دليلها للعامة (INTERNAL).
- لا تعديل على `04` الآن.
