# SECURITY CENTER — مصادقة الـOwner والحماية ولوحة "الأمان"

| البند | القيمة |
|---|---|
| **الغرض** | حماية الـDashboard والبيانات الحساسة (السير، الهويات، الشراكات، الأسعار)، وعرض حالة الأمان للـOwner بلغة واضحة **بلا أي سر** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 1:** المصادقة، والصلاحيات Owner-only، والترويسات، والأسرار · **PHASE 4:** خط الرفع (مع التوظيف والوسائط) · **PHASE 6:** لوحة الأمان وصحة الاعتماديات |
| **الأولوية** | P0 (Dependency health = P1) |
| **المتطلبات** | OPS-035 · OPS-036 · OPS-043 · OPS-050 · OPS-052 · SEC-002 · SEC-004 · SEC-005 · SEC-006 · SEC-007 · SEC-008 · SEC-009 · PERM-001 · PERM-004 · PERM-011 · DASH-032 · AUDIT-001 · AUDIT-010 · CAREERS-080 · CAREERS-081 · CAREERS-091 · M35 §26 §27 §58 · FINAL-ARCHITECTURE-REVIEW §15 (S-1 · S-3 · S-5 · S-6) |
| **المراجع** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) · [`ADR-001`](../adr/ADR-001-platform.md) · [`RECRUITMENT-SECURITY`](../RECRUITMENT-SECURITY.md) · [`RECRUITMENT-PERMISSIONS`](../RECRUITMENT-PERMISSIONS.md) · [`DATA-CLASSIFICATION`](DATA-CLASSIFICATION.md) |

## 1. النطاق
| V1 | لاحقًا |
|---|---|
| مستخدم واحد = الـOwner · Passkeys + TOTP + رموز استرداد · جلسات آمنة · Step-up · Rate limit · ترويسات · خط رفع واحد · أسرار لكل بيئة · Dependency health · لوحة "الأمان" | مستخدمون إضافيون **بإنشاء صريح من الـOwner فقط** وصلاحيات مختارة (PERM-011) · قاعدة WAF في Cloudflare (بعد PO-013) · فحص فيروسات محلي (إن سمح A-11) |

## 2. مصادقة الـOwner
| البند | القرار |
|---|---|
| **إنشاء الحساب** | أمر خادم واحد عند التثبيت (`php artisan shelter:create-owner`). **لا تسجيل عام:** لا Route `register` أصلًا (Fortify بلا `Features::registration()`). أي مستخدم مستقبلي = إنشاء صريح من الـOwner فقط |
| **الطريقة الأساسية** | **Passkey (WebAuthn)** بـ`userVerification=required`. يُنصح بمفتاحين (الهاتف + الحاسوب). مكتبة مفتوحة المصدر مجانية (مثل `laragear/webauthn` أو `web-auth/webauthn-lib`)، **تُسجّل في سجل الأدوات قبل التثبيت** |
| **البديل** | كلمة مرور + **TOTP إلزامي** (تطبيق مصادقة). **لا SMS.** سر TOTP مشفّر في `users`، ورفض إعادة استخدام نفس الرمز |
| **رموز الاسترداد** | 10 رموز لمرة واحدة، مشفّرة في `users`، تُعرض مرة واحدة. استخدام رمز ← إشارة HIGH. إعادة التوليد تتطلب Step-up |
| **كلمة المرور** | **Argon2id** (`hashing.driver=argon2id`). طول ≥ 12، بلا قواعد تركيب، وقائمة كلمات شائعة **محلية** (لا فحص عبر API خارجي). المعايرة لزمن ≈ 0.5 ثانية على السيرفر. دعم Argon2 في PHP يُتحقق في A-11 |
| **فقدان كل العوامل** | إجراء استرجاع موثق عبر SSH على الخادم **بموافقة الـOwner** ← سطر `audit_logs` + إشارة CRITICAL. لا استعادة بالبريد في V1 (لا خدمة بريد) |
| **الجلسة** | جدول `sessions`. Cookie `__Host-` + `Secure` + `HttpOnly` + `SameSite=Lax`. تجديد المعرّف عند الدخول والـStep-up. **خمول 30 دقيقة (مع تنبيه قبلها بدقيقتين وزر "متابعة")، وحد مطلق 12 ساعة.** قائمة الجلسات النشطة + "إنهاء الجلسة" + "إنهاء كل الجلسات الأخرى". جهاز جديد ← إشارة INFORMATION |
| **Rate limit** | 5 محاولات فاشلة / 15 دقيقة لكل (حساب + بصمة IP مشفّرة HMAC)، ثم إيقاف متصاعد 1 ← 5 ← 30 دقيقة. نفس المنطق لرموز TOTP. كل إيقاف ← `audit_logs` + إشارة |
| **سجل الدخول** | داخل `audit_logs` (`auth.login`، `auth.failed`، `auth.lockout`، `auth.step_up`، `auth.recovery_used`). يُخزن: الدولة (من ترويسة Cloudflare) + عائلة المتصفح + IP مقطوع (/24 أو /48). **لا كلمات مرور ولا IP كامل** |

**Step-up (إعادة التأكيد):** Passkey أو TOTP إذا مضى على آخر مصادقة قوية **أكثر من 10 دقائق**، قبل:

| المجموعة | الإجراءات |
|---|---|
| البيانات الحساسة (S-1) | إظهار الهوية · التصدير بالهوية الكاملة · الحذف النهائي · Rollback · فصل تكامل |
| التغييرات المركزية (OPS-036) | تعديل البيانات العامة والحقائق · تغيير أسعار جماعي · إعدادات الأمان وبيانات التكاملات · تنفيذ طلب خصوصية (حذف/تصدير) · إضافة/حذف Passkey · رموز استرداد جديدة |
| مفاتيح الطوارئ | Safe Mode وأخواته: **تأكيد واحد** مع نفس قاعدة الجلسة الحديثة (< 10 دقائق) كي لا يتأخر الإجراء |

## 3. الصلاحيات من الخادم
1. كل Route تحت `/dashboard` و`/api` الداخلي: Middleware `auth` + `owner` + `2fa` (يتحقق أن الجلسة بدأت **بعامل قوي**: Passkey، أو كلمة مرور + TOTP)، ثم **Policy لكل كيان** (فحص على مستوى الكائن لكل معرّف).
2. **Deny by default.** لا صلاحية بالبدل (wildcard). إخفاء الزر ليس حماية (PERM-004).
3. بلا جلسة ← `401`/تحويل للدخول. جلسة غير Owner ← `403`. **لا تمييز يكشف وجود سجل.**
4. Endpoints العامة (النماذج، RUM، البحث، الآراء، تقارير CSP): كتابة فقط أو قراءة عامة، **ولا تعيد أي بيانات داخلية** (OPS-050).
5. مستخدم قاعدة البيانات بأقل صلاحية (CW-06): لا `DROP`/`GRANT`، و`audit_logs` = `INSERT`/`SELECT` فقط.
6. Staging: مصادقة + `noindex` (SEC-004).

## 4. الترويسات وCSRF
**مصدر واحد:** Middleware `SecurityHeaders` في التطبيق. Cloudflare للـTLS والـWAF فقط (لا ترويسات مكررة).

| الترويسة | القيمة |
|---|---|
| `Content-Security-Policy` | `default-src 'self'; script-src 'self' 'nonce-…'; style-src 'self' 'nonce-…'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'`. الـNonce من `Vite::useCspNonce()`. يُضاف `style-src-attr 'unsafe-inline'` **فقط** إن احتاجته نقطة تركيز الصور (لا استثناء للسكربت). نطاقات GA4/GTM تُضاف **فقط** في PHASE 5 بعد الاعتماد. **Report-Only على Staging أولًا، ثم Enforce قبل الإطلاق.** التقارير إلى `/csp-report` (أول طرف، بلا IP) ← إشارات مجمّعة |
| CSP الـDashboard | أشد: لا أي نطاق خارجي |
| CSP الملفات الخاصة | `sandbox; default-src 'none'` + `Content-Disposition: attachment` (RECRUITMENT-SECURITY §4) |
| `Strict-Transport-Security` | `max-age=31536000`. **بلا `includeSubDomains` ولا `preload`** حتى تدقيق النطاقات الفرعية (`shop.` — DB-12، PO-037) |
| `X-Content-Type-Options` | `nosniff` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=(), payment=(), usb=()` (لا يُقيّد `publickey-credentials-*`) |
| `Cross-Origin-Opener-Policy` | `same-origin` |

- **CSRF:** Laravel CSRF على كل طلب يغيّر حالة (الـDashboard والنماذج العامة). المستثنى فقط: RUM Beacon و`/csp-report`، بشرط: فحص `Origin`، وكتابة فقط، ومخطط صارم، وRate limit.
- **الصفحات العامة بلا جلسة ولا Cookies** (تُخزن في الـCDN، وتدعم قاعدة "لا Cookies غير ضرورية" في [`PRIVACY-CENTER`](PRIVACY-CENTER.md)). الجلسة تبدأ فقط في صفحات النماذج والـDashboard.

## 5. أمان الرفع — خط واحد `app/Services/Core/Uploads`
يخدم: الوسائط العامة · مرفقات التوظيف · مرفقات الشراكات.

| الضابط | القاعدة |
|---|---|
| قائمة السماح | **الوسائط:** JPEG · PNG · WebP · AVIF · HEIC، وفيديو MP4/WebM. **التوظيف والشراكات:** العائلات في RECRUITMENT-SECURITY §3 **دون تغيير**. SVG وHTML والمضغوطات مرفوضة دائمًا |
| الامتداد + المحتوى | الامتداد في القائمة **و**التوقيع الفعلي (Magic bytes عبر `finfo`) يتطابقان، وإلا رفض |
| الحجم | حدود **تقنية** من الـAudit (A-07، معادلة CLOUDWAYS §4). لا حدود تجارية مخترعة |
| إعادة الترميز | الصور العامة تُعاد ترميزها بخط الصور المعتمد (Sharp — MEDIA-011) إن توفر Node على السيرفر، وإلا Imagick/GD بنفس القواعد (A-11) ← **تُزال بيانات EXIF/GPS**. الأصل يُحفظ غير مخدوم داخل منطقة الوسائط (MEDIA-011). معاينة صور التوظيف من نسخة معاد ترميزها |
| الاسم | عشوائي 128-bit بلا امتداد قابل للتنفيذ. الاسم الأصلي Metadata منظّفة. `sha256` لمنع التكرار (MEDIA-014) |
| لا تنفيذ | الخاص في `private_html` خارج الجذر العام. مسار الوسائط العامة بلا تنفيذ PHP (قاعدة الخادم). صلاحيات `0640`/`0750` |
| التنزيل الخاص | Endpoint محمي: جلسة Owner + رابط موقّع ≤ 60 ثانية + Audit للتنزيل |
| Rate limit | على كل Endpoint رفع |
| فحص الفيروسات | **ClamAV محلي** إن سمح A-11 ← `scan_status`. وإلا `scan_unavailable` + المنع الصارم أعلاه + لا تنفيذ (S-6). أي ماسح خارجي = خيار مدفوع في §9 |

## 6. الأسرار لكل بيئة
- `.env` منفصل لكل بيئة (Development · Staging · Production) **على الخادم فقط**. `.env.example` بالأسماء فقط. لا سر في Git ولا في الحزمة ولا في الـCMS (SEC-002، M35 §27).
- `APP_KEY` مختلف لكل بيئة. **Staging لا يشارك أي مفتاح أو رمز مع Production.**
- مفاتيح الهوية `RECRUITMENT_ID_ENC_KEY` و`RECRUITMENT_ID_HMAC_KEY` بـ`key_version` للتدوير، ونسخة احتياطية Offline يحتفظ بها الـOwner (RECRUITMENT-SECURITY §5). **مكانها لا يُنسخ مع نفس نسخة القاعدة إن أمكن** (يُحسم في A-08/A-11).
- رموز OAuth (Google) مشفّرة في `settings` بتصنيف `SENSITIVE`، ولا تصل لأي View أو API. `integrations` يحمل **الحالة والنطاق والانتهاء فقط** (بلا أسرار).
- Cloudflare: توكن محدود النطاق، **لا Global API Key**. Cloudways: لا API Key (AC-RULE-04).
- فحص أسرار في الـCI بأداة مجانية مفتوحة المصدر (مثل gitleaks)، تُسجل في سجل الأدوات أولًا.
- سجل الملكية (S-5، PO-037) في وثيقة سجل التكاملات، **بلا أسرار شخصية**.

## 7. صحة الاعتماديات (OPS-043)
- **المصادر (مجانية):** `composer audit` · `npm audit` (Vite + `tooling/`) · Dependabot alerts.
- **التشغيل:** أسبوعيًا في الـCI + قبل كل إصدار. تفعيل Dependabot وحصة GitHub Actions بموافقة (I-07، I-10، PO-039). البديل: تشغيل محلي قبل كل إصدار.
- **التصنيف:** `GOOD` · `UPDATE AVAILABLE` · `SECURITY UPDATE` · `CRITICAL` ← إشارة بفئة الأمان + نتيجة في `releases`.
- **لا دمج آلي لأي Major، ولا تحديث تلقائي على Production.** المسار: PR ← الـGate ← Staging ← موافقة الـOwner.

## 8. لوحة "الأمان" في الـDashboard
**الموقع:** الجودة ← **الأمان** (FINAL-ARCHITECTURE-REVIEW §10). فئة `SECURITY` في نموذج الصحة: HEALTHY / NEEDS ATTENTION / CRITICAL. **كل بند يفتح سببه**، والتفاصيل التقنية تحت "تفاصيل متقدمة".

| المؤشر (M32 §19) | المصدر | يحتاج انتباه / حرج |
|---|---|---|
| SSL | فحص يومي للشهادة | < 14 يومًا / < 3 أيام أو غير صالحة |
| Security headers · CSP | طلب ذاتي يومي + تقارير CSP | ترويسة ناقصة أو CSP Report-Only على Production / CSP غائبة |
| Authentication status | `users` + `webauthn_credentials` | رموز استرداد < 3 / لا Passkey ولا TOTP |
| Session status | `sessions` | — (عرض + إنهاء) |
| Failed / suspicious logins | `audit_logs` (`auth.*`) | إيقاف مؤقت / محاولات من دول متعددة خلال ساعة |
| Admin protection | Rate limit مفعّل · `noindex` · قاعدة WAF (اختيارية) | أي منها معطّل |
| Upload security | اختبار ذاتي للخط + حالة الفحص | `scan_unavailable` = انتباه (موثق) |
| Dependency security | §7 | SECURITY UPDATE / CRITICAL |
| Backup status · Last backup | `scheduled_job_runs` (نبضة مهمة النسخ) | أقدم من الفاصل / فشل ([`DISASTER-RECOVERY`](DISASTER-RECOVERY.md)) |
| الأسرار | "مُعد / غير مُعد" + تاريخ آخر تدوير | سر مطلوب غير مُعد |
| Critical security alerts | `signals` بفئة الأمان | أي CRITICAL مفتوح |

**البيانات:** `users` · `webauthn_credentials` (**إضافة مقترحة** لـPLATFORM-ARCHITECTURE §3.1) · `sessions` · `audit_logs` · `signals` · `settings` · `integrations` · `scheduled_job_runs` · `releases` · `media` · `application_attachments`.

## 9. الخدمات المدفوعة (خيارات فقط — بموافقة الـOwner)
| الخدمة | الغرض | الاستخدام المتوقع | التكلفة / الحصة | البديل المجاني |
|---|---|---|---|---|
| Sentry وأمثاله | تجميع أخطاء الخادم والواجهة | كل خطأ في Production | طبقة مجانية محدودة + خطط مدفوعة (يُتحقق من السعر الرسمي) | سجل أخطاء ذاتي مجمّع بلا PII |
| ماسح فيروسات SaaS | فحص مرفقات التوظيف والشراكات | كل ملف مرفوع | حسب عدد الملفات (مدفوع غالبًا) + **إرسال ملفات شخصية لطرف ثالث** | ClamAV محلي (إن سمح A-11) أو المنع الصارم |
| Auth SaaS / SMS OTP | مصادقة | كل دخول | مدفوع، وSMS عرضة لـSIM swap | Passkeys + TOTP ذاتيًا (**الموصى به**) |

## 10. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| SEC-T01 | `GET /register` و`/dashboard/register` + فحص قائمة الـRoutes | `404`، ولا Route تسجيل |
| SEC-T02 | مسح آلي لكل Routes `/dashboard` و`/api` بلا جلسة، ثم بمستخدم اختبار غير Owner | `401`/تحويل، ثم `403`، وبلا بيانات. P-01…P-08 (RECRUITMENT-PERMISSIONS §4) لكل الوحدات |
| SEC-T03 | Passkey بـVirtual authenticator (Playwright/Chromium) | تسجيل ودخول ناجحان |
| SEC-T04 | كلمة مرور صحيحة بلا TOTP · رمز TOTP مكرر · رمز استرداد مرتين | رفض في الثلاث |
| SEC-T05 | 6 محاولات فاشلة خلال 15 دقيقة | `429` + `auth.lockout` + إشارة |
| SEC-T06 | خمول 31 دقيقة · جلسة عمرها 12 ساعة | إعادة الدخول |
| SEC-T07 | إظهار الهوية بعد 11 دقيقة من آخر مصادقة / بعد 5 دقائق | طلب Step-up / مسموح + `identity.revealed` |
| SEC-T08 | ترويسات `/ar/` و`/dashboard` | الترويسات الست، وCSP بلا `unsafe-inline` للسكربت |
| SEC-T09 | POST بلا CSRF | `419` |
| SEC-T10 | PHP بامتداد `.jpg` · SVG · ZIP · صورة بإحداثيات GPS · رابط مباشر لمسار خاص | رفض · رفض · رفض · النسخة العامة بلا EXIF · `404` |
| SEC-T11 | قيمة سر "Canary" في `.env` الاختبار | غير موجودة في أي HTML/JSON أو في `public/build` |
| SEC-T12 | حزمة بثغرة معروفة في Lockfile اختباري | `SECURITY UPDATE`/`CRITICAL` + إشارة |
| SEC-T13 | لوحة الأمان: حالة جيدة وسيئة مزروعة لكل مؤشر على Staging | العرض الصحيح + لا سر في الاستجابة + 20 عرضًا RTL/LTR + axe 0 Serious/Critical |

**إغلاق البند:** قالب DoD العشري (OPS-054). **أثر النسخ الاحتياطي:** `users` و`webauthn_credentials` و`sessions` ضمن نسخة القاعدة. المفاتيح والأسرار: نسخة Offline لدى الـOwner، و`.env` داخل `private_html` **مشمول في نسخ Cloudways** (A-08) ← خطر موثق في §6.

## 11. ما لا يُنفّذ
- لا تسجيل عام، ولا أدوار أو مستخدمون في V1، ولا SMS، ولا Auth SaaS.
- لا عرض لأي سر أو مفتاح أو رمز في الواجهة أو السجلات.
- لا تعديل لقواعد Cloudflare أو Cloudways أو DNS من هذه الوحدة (بوابات P01/P11 وPO-013).
- لا تحديث Major تلقائي، ولا خدمة مدفوعة بلا موافقة.
- لا IP كامل مخزّن، ولا استعادة حساب بالبريد في V1.
