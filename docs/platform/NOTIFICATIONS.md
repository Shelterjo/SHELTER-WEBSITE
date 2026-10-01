# NOTIFICATIONS — مركز الإشعارات الواحد

| البند | القيمة |
|---|---|
| **الغرض** | مكان واحد يجمع كل ما يحتاج الـOwner معرفته: طلبات جديدة، ومواعيد قريبة، ومشاكل. **مرتب بالأولوية، وبلا إزعاج** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 6** (Notifications). الإشارات من الوحدات تُكتب منذ بنائها (PHASE 3–5) |
| **الأولوية** | P1 (فوق نفس الإشارات — FINAL-ARCHITECTURE-REVIEW §7–9) |
| **المتطلبات** | M35 §7 · §8 · M25 §38 · §67 · M28 §23 · §37 · MON-007 · MON-008 · MON-009 · MON-010 · DASH-017 · DASH-018 · CAREERS-041 · CAREERS-055 · OPS-048 |
| **المراجع الملزمة** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) §3.1 (`signals`) · [`FINAL-ARCHITECTURE-REVIEW`](../FINAL-ARCHITECTURE-REVIEW.md) §5 · §10 · [`INCIDENTS`](INCIDENTS.md) · [`ENVIRONMENTS`](ENVIRONMENTS.md) §2 |

## 1. القاعدة
- **مركز واحد فوق جدول واحد:** `signals` بنوعين:
  - `EVENT`: شيء حدث (طلب جديد، حملة تنتهي).
  - `ISSUE`: مشكلة مفتوحة ([`INCIDENTS`](INCIDENTS.md)).
- **لا إشعارات داخل الوحدات** (التوظيف، الشراكات، الاستفسارات…). الوحدة تعرض **شارة عدد** فقط (FINAL-ARCHITECTURE-REVIEW §5).
- **"يحتاج انتباه"** = `ISSUE` المفتوحة. **الجرس 🔔** = كل جديد غير مقروء (`EVENT` + `ISSUE`).

## 2. الأولوية (M35 §8)
| `priority` | يُشتق من | المعنى للـOwner |
|---|---|---|
| **CRITICAL** | `ISSUE` بشدة CRITICAL | تصرّف الآن |
| **ACTION_REQUIRED** | `ISSUE` بشدة HIGH · `EVENT` ينتظر قرارك (طلب، استفسار، مراجعة، تغيير خارجي) | يحتاج إجراء منك |
| **IMPORTANT** | `ISSUE` بشدة MEDIUM · `EVENT` بموعد قريب | اعرفه اليوم |
| **INFORMATION** | `ISSUE` بشدة LOW أو INFO · `EVENT` للعلم | للعلم فقط |

## 3. ما يجمعه المركز (M35 §7)
| الإشعار | `kind` | `priority` | المصدر | `dedupe_key` | يُحل تلقائيًا عند |
|---|---|---|---|---|---|
| طلب توظيف جديد | EVENT | ACTION_REQUIRED | `applications` (JOB) | `app:{reference}` | فتح الطلب أول مرة |
| طلب فرنشايز جديد | EVENT | ACTION_REQUIRED | `applications` (FR) | `app:{reference}` | فتح الطلب أول مرة |
| استفسار جديد | EVENT | ACTION_REQUIRED | `applications` (INQ) ← [`INQUIRIES`](INQUIRIES.md) | `app:{reference}` | فتح الاستفسار (NEW ← OPEN) |
| رأي عميل جديد (VoC) | EVENT | IMPORTANT (يُجمع يوميًا) | Feedback (P1) | `feedback:{date}` | فتح الملخص |
| مراجعة تحتاج ردًا | EVENT | ACTION_REQUIRED | GBP Reviews (بعد PO-009) | `review:{id}` | الرد أو "لا يحتاج ردًا" |
| حملة تنتهي (≤ 24 ساعة) | EVENT | IMPORTANT | `experiences` | `exp:{id}:ending` | انتهاء الحملة |
| فعالية تبدأ قريبًا (≤ 24 ساعة) | EVENT | IMPORTANT | `experiences` / الفعاليات | `exp:{id}:starting` | بدء الفعالية |
| ساعات خاصة قادمة (≤ 48 ساعة) | EVENT | IMPORTANT | `hours_exceptions` | `hours:{branch}:{date}` | بدء التاريخ |
| فشل مزامنة Google | ISSUE HIGH | ACTION_REQUIRED | `sync_jobs` | `sync:{channel}:{entity}` | نجاح المزامنة |
| محتوى غير متطابق (قناة أو لغة) | ISSUE MEDIUM | IMPORTANT | المزامنة · تطابق اللغتين | `oos:{channel}:{entity}` · `parity:{entity}` | التطابق |
| فشل النسخ الاحتياطي | ISSUE CRITICAL | CRITICAL | [`MONITORING`](MONITORING.md) #13 | `backup:{type}` | نسخة ناجحة |
| تنبيه أمني | ISSUE HIGH أو CRITICAL | حسب الشدة | [`SECURITY-CENTER`](SECURITY-CENTER.md) | `sec:{type}` | زوال السبب |
| تراجع أداء الموقع | ISSUE MEDIUM | IMPORTANT | [`REAL-USER-MONITORING`](REAL-USER-MONITORING.md) | `perf:{route}:{device}:{metric}` | التعافي 7 أيام |
| محتوى يحتاج مراجعة (الحداثة) | EVENT | IMPORTANT | Content Health | `fresh:{entity}` | المراجعة أو التحديث |
| وسيط ينقصه بيانات (alt، حقوق) | ISSUE LOW | INFORMATION | Media Center | `media:{id}:meta` | إكمال البيانات |

## 4. الإجراءات
| الإجراء | متاح لـ | الأثر |
|---|---|---|
| **Mark read** | الكل | `read_at`. **CRITICAL المقروء يبقى مثبتًا أعلى القائمة** حتى يُحل |
| **Resolve** | الكل | `ISSUE`: حسب قواعد [`INCIDENTS`](INCIDENTS.md) §5 · `EVENT`: "تم" |
| **Open source** | الكل | يفتح المكان نفسه (`action_url`)، ويُعلّم مقروءًا |
| **Dismiss** | **INFORMATION فقط** | `DISMISSED` + سطر `audit_logs` |

- **لا تُخفى حادثة حرجة غير محلولة بلا سجل** (M35 §8). الخادم يرفض Dismiss وSnooze لأي CRITICAL.
- كل إجراء يكتب سطرًا في `audit_logs`.

## 5. التوصيل
### 5.1 V1 — داخل الـDashboard فقط (MON-009)
- **الجرس 🔔** في الشريط العلوي (Desktop وMobile).
  - الشارة = عدد غير المقروء من CRITICAL + ACTION_REQUIRED.
  - القائمة مجمّعة بالأولوية.
  - تحديث كل 60 ثانية بطلب خفيف، بلا WebSockets.
- **بطاقات مركز القيادة:** الأعداد الجديدة (توظيف · شراكات · استفسارات غير محلولة · يحتاج انتباه — M35 §42).
- **شارات الوحدات:** عدد فقط (مثل "الاستفسارات 3").

### 5.2 البريد للـCRITICAL — مصمم، ومعطّل حتى موافقتك
- **السبب:** قرار معتمد سابقًا: V1 = إشعارات داخل الـDashboard فقط، والبريد "لاحقًا إذا اعتُمد" (MON-009، MON-010، M25 §67). **لا يُفعّل بلا موافقة صريحة.**
- **المفتاح:** `feature_flags`: `notifications.email_critical` = `false`. وعلى Production `MAIL_MAILER=log` في V1 ([`ENVIRONMENTS`](ENVIRONMENTS.md) §2).
- **عند الموافقة فقط:**
  - CRITICAL فقط، لبريد يحدده الـOwner في `settings` بعد التحقق منه (PO-037). **لا يُفترض أنه `info@shelterjo.com`** (D-035 معلّق).
  - المحتوى: العنوان بلغة الـOwner + رابط "افتح لوحة التحكم". **بلا بيانات عملاء، ولا PII، ولا أسرار.**
  - بريد واحد لكل `dedupe_key` كل 6 ساعات، وحد أقصى 10 رسائل يوميًا.
- **لا بريد أبدًا** عند وصول طلب توظيف أو فرنشايز أو استفسار، ولا بريد أو واتساب للزائر (M28 §23، §37، CAREERS-041، CAREERS-055).
- **التوقف الكامل للخادم** لا يستطيع الخادم الإبلاغ عنه: ينبّه المراقب الخارجي ببريده الخاص (إن اعتُمد — [`MONITORING`](MONITORING.md) §4).

| الخدمة | الغرض | الاستخدام المتوقع | التكلفة / الحصة | البديل المجاني |
|---|---|---|---|---|
| SMTP لصندوق بريد المنشأة **القائم** (إن وُجد — استضافة البريد غير معروفة، PO-037 وA-13) | بريد CRITICAL للـOwner | بضع رسائل شهريًا (حد 10/يوم) | بلا تكلفة إن كان الصندوق قائمًا. إضافة SMTP من Cloudways أو خدمة بريد معاملات = **غالبًا مدفوعة، غير مقترحة** | الجرس داخل الـDashboard (V1) + بريد المراقب الخارجي للتوقف الكامل |

### 5.3 منع الإزعاج (MON-008)
- **Dedupe:** فهرس فريد على `dedupe_key` للسطر المفتوح. التكرار يحدّث `occurrences` و`last_seen_at`، **ولا ينبّه مجددًا** إلا إذا ارتفعت الشدة.
- **الملخص اليومي:** كل `INFORMATION` في اليوم تُجمع في **بند واحد** "ملخص اليوم" (`digest:{date}`) بدل بنود منفصلة.
- **تجميع المتشابه:** `IMPORTANT` من نفس النوع خلال ساعة = بند واحد (مثل "3 حملات تنتهي اليوم").
- **لا تجميع** لـCRITICAL ولا ACTION_REQUIRED: كل بند مستقل.
- **المحلول** يختفي من الجرس بعد 7 أيام، ويبقى في السجل.

## 6. البيانات
- `signals` (يشمل `priority` و`read_at`) · `audit_logs` · `feature_flags` · `settings` · `applications`.
- **لا جدول إشعارات.** الأرشفة بعد 90 يومًا، بلا حذف (PLATFORM-ARCHITECTURE §4.6).

## 7. مكانه في الـDashboard
- **🔔 الشريط العلوي** + صفحة "كل الإشعارات" (`/dashboard/notifications`): نفس الاستعلام بفلاتر.
- **الرئيسية:** البطاقات و"يحتاج انتباه".
- **النظام ← الإعدادات ← الإشعارات:** الملخص اليومي، وحالة مفتاح البريد (للقراءة حتى الموافقة).

## 8. V1 مقابل لاحقًا
| V1 | لاحقًا (كل بند بموافقة — MON-010) |
|---|---|
| الجرس · الأولويات · الإجراءات الأربعة · Dedupe · الملخص اليومي · شارات الوحدات | بريد CRITICAL (§5.2) · Push للهاتف · واتساب · تفضيلات تفصيلية لكل نوع |

## 9. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| NOT-T1 | إرسال استفسار ناجح | إشارة `EVENT` واحدة `ACTION_REQUIRED` بـ`app:INQ-…` · **0 بريد** (Mail fake) حتى مع تفعيل مفتاح البريد |
| NOT-T2 | فتح الاستفسار من الجرس | الحالة OPEN · الإشعار `RESOLVED` · الشارة تنقص 1 |
| NOT-T3 | Dismiss على CRITICAL عبر API · على INFORMATION | `422` · `DISMISSED` + سطر Audit |
| NOT-T4 | CRITICAL مع المفتاح `false` · مع `true` · تكراره خلال 6 ساعات | 0 بريد · بريد واحد · يبقى بريدًا واحدًا |
| NOT-T5 | فحص جسم البريد | لا هاتف ولا بريد ولا اسم (Regex) ولا أسرار |
| NOT-T6 | 20 إشارة INFORMATION في يوم | بند واحد "ملخص اليوم" |
| NOT-T7 | شارة "الاستفسارات" | تساوي عدد NEW + OPEN + IN PROGRESS في الاستعلام |
| NOT-T8 | الجرس على 320px وعلى 20 عرضًا AR/EN · لوحة المفاتيح | بلا Overflow · قابل للوصول · تغيّر العدد يُعلن بـ`aria-live="polite"` |
| NOT-T9 | طلب قائمة الإشعارات بلا جلسة | `401` |

## 10. ما ليس منجزًا (NOT DONE)
- **لا كود بعد.** الحالة الصادقة `NOT STARTED`.
- **البريد للـCRITICAL غير معتمد** (تعارض مع MON-009/MON-010 — يُعرض على الـOwner). وجود SMTP قائم غير متحقق (PO-037، A-13).
- إشعارات المراجعات معلقة على وصول GBP API (PO-009). إشعارات VoC تبدأ مع وحدة Feedback (P1).
