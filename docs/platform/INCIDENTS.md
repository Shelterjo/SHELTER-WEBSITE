# INCIDENTS — الحوادث و"يحتاج انتباه" (طابور واحد)

| البند | القيمة |
|---|---|
| **الغرض** | كل مشكلة في الموقع تظهر للـOwner **في مكان واحد**، بلغة مفهومة، مع ما يجب فعله، وسجل كامل حتى الحل |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 1:** جدول `signals` · **PHASE 3:** "يحتاج انتباه" في مركز القيادة · **PHASE 6:** Incident management كاملًا |
| **الأولوية** | P0 |
| **المتطلبات** | M35 §5 · §6 · §43 · §45 · M32 §39 · §40 · §41 · OPS-048 · DASH-002 · DASH-017 · DASH-018 · DASH-020 · DASH-034 · MON-007 · MON-008 · AUDIT-001 · AUDIT-002 · H-02 |
| **المراجع الملزمة** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) §3.1 (`signals`) · [`FINAL-ARCHITECTURE-REVIEW`](../FINAL-ARCHITECTURE-REVIEW.md) §5 · §14 · [`MONITORING`](MONITORING.md) · [`NOTIFICATIONS`](NOTIFICATIONS.md) |

## 1. القاعدة
- **الحادثة = سطر في `signals` بـ`kind = ISSUE`.** لا جدول ثانٍ، ولا "مركز حوادث" ثانٍ (FINAL-ARCHITECTURE-REVIEW §5).
- **"يحتاج انتباه" = نفس الإشارات المفتوحة.** الوحدات تعرض **عدّادًا** فقط.
- **المصادر:** [`MONITORING`](MONITORING.md) · [`REAL-USER-MONITORING`](REAL-USER-MONITORING.md) · Publish Guard · فحوص الصحة (تطابق اللغتين، الحداثة، Schema، Sitemap) · الأمان · المزامنة.
- **ملاحظة أسماء:** ورد اسم `issues` في GAP-CLOSURE-PLAN §B و§D. **الملزم `signals`** (PLATFORM-ARCHITECTURE §3.1، FINAL-ARCHITECTURE-REVIEW §5).

## 2. النموذج (M35 §5 ← أعمدة `signals`)
| حقل M35 | العمود | ملاحظة |
|---|---|---|
| Severity | `severity` | CRITICAL · HIGH · MEDIUM · LOW · INFO |
| What happened | `title_ar/en` · `body_ar/en` | لغة صاحب العمل (§3) |
| When | `first_seen_at` · `last_seen_at` · `occurrences` | يُعرض بتوقيت السوق |
| Affected area | `category` · `entity_type` / `entity_id` · `source` | §4 |
| Current status | `status` | OPEN · RESOLVED · DISMISSED. وأيضًا `read_at` · `snoozed_until` |
| Recommended action | `action_ar/en` · `action_kind` · `action_url` | زر واحد يفتح المكان نفسه (DASH-018) |
| Technical details (Advanced) | `advanced` | مطوي افتراضيًا، ومنظّف من PII والأسرار |
| Resolved / unresolved | `status` · `resolved_at` · `resolved_by` (owner / system) · `resolution_note` | |
| السجل | `audit_logs` | كل تغيير حالة وكل إجراء |
| — | `kind` · `priority` · `dedupe_key` · `evidence` (JSON بلا PII) · `environment` | من الوصف الملزم |

| الشدة | المعنى للـOwner | أمثلة |
|---|---|---|
| **CRITICAL** | الموقع أو بياناته في خطر الآن | الموقع متوقف · قاعدة البيانات لا تعمل · النسخة الاحتياطية فشلت · لا اختبار استعادة · وضع الصيانة مفعّل |
| **HIGH** | وظيفة مهمة معطلة، والموقع يعمل | فشل حفظ نموذج · مزامنة Google فشلت · انتهى رمز اتصال · المجدول متوقف · التتبع متوقف |
| **MEDIUM** | تراجع أو اختلاف يحتاج مراجعة | تراجع أداء · ساعات Google تختلف عن المعتمد · رمز ينتهي قريبًا |
| **LOW** | خلل صغير غير عاجل | رابط مكسور في مقال قديم · خطأ جديد ظهر مرة واحدة |
| **INFO** | للعلم فقط | تحديث اعتماديات متاح |

> **توحيد المفردات:** DASH-017 وOPS-048 استخدما CRITICAL / WARNING / INFO. **M35 §5 أحدث:** WARNING القديمة = HIGH أو MEDIUM أو LOW حسب الأثر.

## 3. أخطاء بلغة صاحب العمل (M35 §6)
**ممنوع في الواجهة الأساسية:** `ECONNREFUSED` · Stack trace · `SQLSTATE` · `Hydration mismatch`. مكانها "تفاصيل متقدمة" فقط.

| المشكلة | ما يراه الـOwner (DRAFT) | الزر | تفاصيل متقدمة (مطوية) |
|---|---|---|---|
| رمز Google منتهٍ | "تعذر تحديث ساعات Google Business لأن الاتصال يحتاج إعادة تسجيل الدخول." | **إصلاح الاتصال (Reconnect)** ← النظام ← التكاملات ← تسجيل دخول Google | `GBP Business Information API: 401 invalid_grant (refresh token revoked)` |
| قاعدة البيانات | "الموقع لا يستطيع قراءة البيانات الآن، والزوار يرون صفحة اعتذار." | افتح صحة الموقع | `SQLSTATE[HY000] [2002] Connection refused` |
| النسخ الاحتياطي | "النسخة الاحتياطية الليلية لم تكتمل." | أعد التشغيل الآن | `backup:db exit 1 (disk quota)` |
| فشل حفظ نموذج | "تعذر حفظ رسالة من نموذج التواصل. الزائر رأى رسالة خطأ، وبياناته بقيت أمامه." | افتح التفاصيل | `QueryException in inquiry store (payload not logged)` |
| المجدول | "المهام المجدولة متوقفة منذ 12 دقيقة. الموقع يعرض الحملات بوقتها الصحيح، لكن المزامنة والنسخ متوقفان." | افتح صحة الموقع | `no schedule:heartbeat since 2026-10-01T03:12+03:00` |
| التتبع | "Google Analytics لم يسجّل أي زيارة أمس، رغم أن الموقع استقبل زوارًا." | افتح التكاملات | `GA4 Data API eventCount(page_view, D-1)=0; public page requests=1,240` |
| الأداء | "صورة الصفحة الرئيسية كبيرة وتبطئ التحميل." (M32 §41) | افتح الصورة في مركز الوسائط | `Hero LCP resource 2.8MB` |

- **القوالب** لكل `source` في `lang/ar` و`lang/en`. النصوص أعلاه **DRAFT** للاعتماد مع نصوص الـDashboard.
- **خطأ بلا قالب:** "حدث خطأ غير متوقع في {المنطقة}. سجّلناه، ولا يلزمك فعل شيء الآن إن لم يتكرر." + التفاصيل المتقدمة.
- **الأمثلة المرقمة** في الجدول توضيحية فقط، وليست بيانات حقيقية.

## 4. المنطقة المتأثرة (`category`)
- **إلزامي للـISSUE.** القيم: الفئات التسع (M35 §44) + **`OPERATIONS`**.
- `OPERATIONS` يغذي بطاقة "الموقع" في مركز القيادة، **ولا يدخل في الفئات التسع**.

| المصدر | `category` |
|---|---|
| التوفر · `/health` · قاعدة البيانات · المساحة · أخطاء الخادم والواجهة · النماذج · المجدول | `OPERATIONS` |
| المزامنة · OUT OF SYNC · تغيير خارجي · تطابق اللغتين | `DATA_CONSISTENCY` |
| رموز التكاملات · صحة التحليلات · انقطاع تكامل | `INTEGRATIONS` |
| النسخ · اختبار الاستعادة | `BACKUPS` |
| RUM · Lighthouse | `PERFORMANCE` |
| الروابط · الوسائط · حداثة المحتوى | `CONTENT` |
| Schema · Sitemap · الفهرسة | `SEO` |
| الوصولية | `ACCESSIBILITY` |
| SSL · الترويسات · الدخول · الاعتماديات | `SECURITY` |
| الاحتفاظ · الموافقات | `PRIVACY` |

## 5. دورة الحياة
```
فحص يفشل ──► OPEN ──(نفس dedupe_key مجددًا)──► نفس السطر: occurrences+1 · last_seen_at
   │                                              الشدة ترتفع فقط، ولا تنخفض وهي مفتوحة
   ├─ الـOwner يفتح البند ──────► read_at (لا يغيّر الحالة)
   ├─ ينفّذ الإجراء ← ينجح الفحص مرتين متتاليتين ──► RESOLVED (resolved_by = system)
   ├─ "تم الحل" يدويًا ─────────► RESOLVED (resolved_by = owner)
   ├─ تأجيل (Snooze) ───────────► MEDIUM و LOW فقط · حتى 7 أيام · السبب إلزامي
   └─ تجاهل (Dismiss) ──────────► INFO فقط
RESOLVED ثم عاد خلال 7 أيام ──► إعادة فتح نفس السطر · بعد 7 أيام ──► سطر جديد
```
- **CRITICAL:** لا تجاهل ولا تأجيل، ويبقى أعلى القائمة حتى `RESOLVED`. **الخادم يرفض** أي محاولة إخفاء.
- **"تم الحل" يدويًا** لـCRITICAL أو HIGH والفحص ما زال يفشل ← **ملاحظة إلزامية**، والفحص التالي يعيد الفتح إن استمر الفشل.
- **كل انتقال = سطر `audit_logs`:** `signal.opened` · `signal.resolved` · `signal.reopened` · `signal.snoozed` · `signal.dismissed`.
- **لا حذف:** `RESOLVED` و`DISMISSED` تُؤرشف بعد 90 يومًا، وتبقى قابلة للبحث.

## 6. البيانات
- `signals` · `audit_logs` · `integrations` (لزر Reconnect) · `scheduled_job_runs` · `settings` (العتبات).
- قوالب الرسائل في `lang/ar` و`lang/en`. **لا جدول `issues`، ولا جدول حوادث.**

## 7. مكانه في الـDashboard
- **الرئيسية:** "يحتاج انتباه" — أعلى 5 بنود مرتبة بالشدة ثم الأحدث، + "عرض الكل".
- **الجودة ← صحة الموقع ← الحوادث:** نفس الاستعلام بفلاتر (الشدة، الفئة، الحالة، الفترة) + التاريخ.
- **الوحدات:** شارة عدد فقط.
- **الموبايل:** القائمة والإجراءات كاملة (DASH-027، DASH-028).

## 8. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| الطابور الواحد · النموذج · قوالب AR/EN · الحل التلقائي · التأجيل والتجاهل بقواعدهما · السجل | ربط الحادثة بإصدار تلقائيًا مع اقتراح Rollback ([`ROLLBACK`](ROLLBACK.md)) · تقرير شهري للحوادث · قوالب إضافية حسب الحاجة |

## 9. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| INC-T1 | نفس `dedupe_key` 50 مرة | سطر واحد · `occurrences=50` |
| INC-T2 | إشارة CRITICAL: البحث عن زر "تجاهل" + `POST` مباشر للتجاهل أو التأجيل | لا زر · الخادم يرفض (`422`) · بلا تغيير |
| INC-T3 | "تم الحل" لـHIGH والفحص ما زال يفشل، بلا ملاحظة / بملاحظة | رفض / قبول + سطر Audit، ثم إعادة فتح عند الفحص التالي |
| INC-T4 | فحص النص المرئي لكل بطاقة إشارة (خارج "تفاصيل متقدمة") | 0 تطابق لـ`SQLSTATE` · `ECONNREFUSED` · `Exception` · `stack` |
| INC-T5 | رمز Google منتهٍ ← زر "إصلاح الاتصال" | يفتح النظام ← التكاملات ← تدفق OAuth. بعد الربط: `RESOLVED` آليًا |
| INC-T6 | حل تلقائي ثم تكرار بعد 3 أيام / بعد 10 أيام | إعادة فتح نفس السطر / سطر جديد |
| INC-T7 | كل `action_url` في عينة من 10 مصادر | يفتح الكيان أو الشاشة الصحيحة (DASH-018) |
| INC-T8 | طلب API الإشارات بلا جلسة / بجلسة غير Owner | `401` / `403` بلا بيانات |
| INC-T9 | `advanced` لإشارة خطأ نموذج طلبه فيه بريد وهاتف | لا بريد ولا هاتف |
| INC-T10 | "يحتاج انتباه" على 320px وعلى 20 عرضًا AR/EN | بلا Overflow · الأزرار ≥ 44px · axe 0 Serious/Critical |

## 10. ما ليس منجزًا (NOT DONE)
- **لا كود بعد.** الحالة الصادقة `NOT STARTED`.
- قيمة `OPERATIONS` إضافة على "الفئات الـ9" في PLATFORM-ARCHITECTURE §3.1، **تحتاج تثبيتها هناك**.
- نصوص الرسائل **DRAFT** حتى اعتماد نصوص الـDashboard.
- مدد الأرشفة والتأجيل **افتراضات تقنية**.
