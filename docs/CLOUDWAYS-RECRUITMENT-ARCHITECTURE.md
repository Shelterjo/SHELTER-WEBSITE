# CLOUDWAYS RECRUITMENT ARCHITECTURE

> **الحالة:** `DRAFT — AUDIT BLOCKED (ACCESS)` · **المرحلة:** M28 Phase 2 (Cloudways audit) + Phase 5 (storage) · **آخر تحديث:** 2026-10-01
> **المصدر:** مواصفة الـOwner النهائية لنظام التوظيف (M28 §01، §02، §18، §20، §70، §71، §72) — `OWNER APPROVED`.
> **قاعدة:** لا تعديل على Production أثناء الـAudit. لا لمس لـFalcon أو قواعد بياناته. لا Database على جهاز الـOwner. لا خدمات خارجية بلا موافقة.

## 1. ما تحقق منه فعلًا، وما لم يُتحقق منه بعد

| البند | الحالة | الدليل |
|---|---|---|
| الوصول للموقع الحالي `www.shelterjo.com` من بيئة العمل | ❌ **محجوب** | سياسة شبكة البيئة ترفض الاتصال (`403`). الموافقة AC-01 موجودة لكن غير مفعّلة في إعدادات البيئة (PO-008) |
| الوصول لحساب Cloudways (API / SSH / SFTP / Console) | ❌ **لا يوجد** | لم تُمنح أي صلاحية بعد |
| **قدرات منصة Cloudways العامة (من مصادر عامة، تُطابق مع حسابكم في الـAudit)** | ⚠️ غير مؤكدة لحسابكم | |
| ↳ **Cloudways Flexible:** تطبيقات PHP (WordPress، Laravel، Custom PHP) + MySQL/MariaDB | مصدر عام | [Cloudways — Which web applications can be hosted](https://support.cloudways.com/en/articles/5134108-which-web-applications-can-be-hosted-on-cloudways) · [Flexible](https://www.cloudways.com/en/flexible-hosting.php) |
| ↳ **مجلد `private_html`:** خارج مجلد الويب `public_html`، ومشمول في النسخ الاحتياطي والاستعادة والاستنساخ | مصدر عام | [Securing files in private_html](https://support.cloudways.com/en/articles/5123384-securing-app-configuration-files-in-private_html-folder) |
| ↳ **حجم الرفع:** إعداد على مستوى السيرفر (الافتراضي المذكور 100MB) + إعدادات PHP-FPM لكل تطبيق (`upload_max_filesize`، `post_max_size`) | مصدر عام | [Customize PHP settings](https://support.cloudways.com/en/articles/5124759-how-to-customize-php-settings-for-your-application) · [Server settings](https://support.cloudways.com/en/articles/5120689-how-to-manage-your-server-settings) |
| ↳ **Cloudways Velocity (Node.js مُدار، يدعم Next.js SSR):** أُعلن توفره العام في سبتمبر 2026 | مصدر عام | [DigitalOcean — Velocity launch](https://investors.digitalocean.com/news/news-details/2026/Cloudways-Launches-Velocity-for-Managed-Node-js-Hosting-With-Flat-Predictable-Pricing-for-Developers-and-Agencies/default.aspx) |
| ↳ **قيود Velocity:** هل فيه SSH/SFTP؟ قاعدة بيانات مضمنة؟ قرص دائم للملفات الخاصة؟ | ⚠️ **متضاربة في المصادر** | تُحسم من حسابكم أو من الدعم الرسمي قبل أي قرار |

> **لا شيء في هذه الوثيقة يُعتبر حقيقة عن حسابكم** حتى يُكمل الـAudit في §3.

## 2. المعمارية المعتمدة (من الـOwner) وتفصيلها التقني

```
زائر (موبايل/ديسكتوب)
   │ HTTPS
   ▼
/ar/careers/ (نموذج عربي واحد) ──► SHELTER Backend (Server-side على Cloudways)
                                      │  تحقق Server-side · Rate limit · CSRF · فحص الملفات
                                      ├──► Private Website Database على Cloudways   (جداول التوظيف — RECRUITMENT-DATA-MODEL.md)
                                      └──► Private Recruitment Storage على Cloudways (خارج public_html)
                                                │
SHELTER Owner Dashboard ◄── مصادقة + صلاحية Owner فقط (Server-side) ── Stream/Download endpoint
```

**قرارات تقنية متخذة** (مفوّضة حسب M28 §78: الـOwner يحدد الهدف، وClaude يختار أفضل تنفيذ ويوثقه):

| # | القرار | السبب |
|---|---|---|
| CW-01 | **تطبيق Cloudways مستقل للموقع الجديد** (Application منفصلة)، بقاعدة بيانات خاصة به | عزل كامل عن WordPress القديم (M28 §02). نسخ احتياطي واستعادة مستقلان. لا خطر على الموقع الحالي |
| CW-02 | **لا تُستخدم قاعدة WordPress القديمة** لنظام التوظيف | طلب صريح (M28 §02) |
| CW-03 | **الملفات في مسار خاص خارج مجلد الويب:**<br>- Flexible: `…/applications/<app>/private_html/recruitment/`<br>- Velocity: مسار قرص دائم غير مخدوم يتحقق منه | لا URL عام يصل لأي ملف (M28 §20) |
| CW-04 | **تنزيل الملفات عبر Endpoint محمي فقط:**<br>- جلسة Owner.<br>- رابط قصير الصلاحية (≤ 60 ثانية).<br>- `Content-Disposition: attachment`.<br>- `nosniff`.<br>- CSP `sandbox`. | M28 §20، §60 |
| CW-05 | **مفاتيح التشفير والأسرار:** في متغيرات بيئة السيرفر أو ملف إعداد داخل `private_html`، **ليست في Git ولا في الـFrontend** | M28 §27، قواعد المشروع |
| CW-06 | **مستخدم قاعدة بيانات بأقل صلاحية:**<br>- لا `DROP` ولا `GRANT`.<br>- جدول الـAudit: `INSERT` و`SELECT` فقط.<br>- الـMigrations بمستخدم منفصل يُستخدم يدويًا. | Least privilege |
| CW-07 | **Staging أولًا:**<br>- Cloudways Staging (استنساخ التطبيق) أو تطبيق Staging مستقل.<br>- **ببيانات اختبار اصطناعية فقط.** لا بيانات Falcon، ولا بيانات متقدمين حقيقيين. | M28 §72 |

## 3. قائمة فحص الـAudit (للقراءة فقط — لا تغيير)

| # | البند | كيف يُفحص | النتيجة |
|---|---|---|---|
| A-01 | التطبيقات الموجودة على السيرفر (WordPress الحالي، غيره) ونوع كل تطبيق | لوحة Cloudways ← Applications | ⏳ |
| A-02 | نوع الخطة: Flexible أو Autonomous أو Velocity. مزود السحابة، المنطقة، حجم السيرفر (RAM/CPU/Disk) | Server Management | ⏳ |
| A-03 | Stack: Nginx / Apache / Varnish / Redis، إصدار PHP لكل تطبيق | Server ← Settings & Packages | ⏳ |
| A-04 | إصدار MySQL/MariaDB، والقواعد الموجودة ومستخدميها | Settings & Packages · Application Access Details | ⏳ |
| A-05 | SSH/SFTP: مستخدم Master ومستخدمو التطبيقات. هل يُسمح بمستخدم تطبيق مقيّد؟ | Application Credentials | ⏳ |
| A-06 | المساحة الكلية والمستخدمة، والنمو المتوقع لمرفقات التوظيف | Monitoring ← Disk | ⏳ |
| A-07 | حدود الرفع:<br>- Upload size على السيرفر.<br>- `upload_max_filesize` و`post_max_size` و`max_execution_time` و`max_input_time` و`memory_limit` لكل تطبيق.<br>- `client_max_body_size` في Nginx إن ظهر. | Server Settings · Application Settings ← PHP-FPM | ⏳ |
| A-08 | النسخ الاحتياطي: مفعّل؟ التكرار؟ مدة الاحتفاظ؟ Offsite؟ آخر نسخة ناجحة؟ هل `private_html` مشمول فعلًا؟ | Server ← Backups | ⏳ |
| A-09 | Staging: هل خاصية Staging متاحة في الخطة؟ | Application ← Staging Management | ⏳ |
| A-10 | الأمان:<br>- SSL.<br>- Bot Protection.<br>- قواعد Firewall/IP whitelisting لـSSH/SFTP.<br>- 2FA على حساب Cloudways.<br>- من يملك الحساب ومن له صلاحية. | Security · Account | ⏳ |
| A-11 | أدوات متاحة على السيرفر بلا Root:<br>- `pdftotext` / `poppler`.<br>- مكتبات PHP (`fileinfo`، `zip`، `sodium`، `openssl`، `intl`).<br>- ClamAV (غالبًا غير متاح في الخوادم المُدارة).<br>- Cron jobs. | SSH: `php -m` · `which pdftotext clamscan` · Cron Job Management | ⏳ |
| A-12 | Cloudflare أمام Cloudways؟ (حدود حجم الطلب في Cloudflare تؤثر على الرفع) | DNS / Cloudflare (PO-013) | ⏳ |
| A-13 | البريد الصادر (SMTP add-on) | **للعلم فقط** — النظام لا يرسل بريدًا (M28 §23، §37) | ⏳ |

**ما المطلوب للـAudit (أقل صلاحية ممكنة):** أحد الخيارين، **ولا تُرسل كلمات مرور في المحادثة.**
- **الخيار 1 (موصى به):**
  - Cloudways يدعو Claude كـTeam Member بصلاحية **قراءة/Billing-off** إن أتاحت اللوحة ذلك.
  - **أو** يشغّل الـOwner سكربت قراءة فقط (يُجهّز لاحقًا) عبر SSH، ويُرسل الناتج. السكربت لا يطبع أي سر.
- **الخيار 2:** مستخدم SFTP/SSH **على مستوى التطبيق** (ليس Master) لتطبيق Staging جديد، تُوضع بياناته في **أسرار بيئة العمل** (Environment secrets)، وليس في الرسائل.
- **ممنوع:** Cloudways API Key الكامل لهذا الغرض. صلاحيته واسعة لكل الحساب.

## 4. حدود الرفع (تُحسم بعد A-07 — لا أرقام من عندنا)

**قرار الـOwner:**
- لا حد تجاري ثابت لعدد الملفات أو حجمها (M28 §16، §18).
- لكن **حماية تقنية إلزامية.**

**المعادلة التي ستُطبق بعد الـAudit:**
- **حد الملف الواحد** = `min(upload_max_filesize, Server Upload size, حد Cloudflare إن وُجد) − هامش 10%`.
- **حد مجموع الطلب** = بحيث لا يتجاوز `post_max_size` لكل طلب رفع.
  - الرفع ملف بملف (Progressive)، فالمجموع لا يمر في طلب واحد.
  - **حد أمان للمساحة:** يُحسب من A-06 لحماية السيرفر.
- **حد عدد الملفات للطلب الواحد:** تقني، لمنع الإغراق فقط، ويُحدد مع حد المساحة.
  - يظهر للمستخدم كرسالة عربية واضحة عند الوصول له، **وليس كقيد معلن مسبقًا.**
- **حماية إضافية:** Rate limit على Endpoint الرفع، وتنظيف الملفات المؤقتة غير المرسلة بعد 24 ساعة.
  - هذه **ملفات مسودة لم تصبح طلبًا.** الطلبات المرسلة لا تُحذف تلقائيًا أبدًا (M28 §52).
- **عند التجاوز:** رسالة عربية واضحة، ولا فقدان لمدخلات النموذج (M28 §69).

## 5. أثر Cloudways على قرار منصة الموقع (DB-08) — تعارض تقني حقيقي يجب إظهاره

**قرار الـOwner:** البيانات على Cloudways، وServer-side، وقاعدة بيانات خاصة بالموقع (M28 §01).

**هذا يضيّق خيارات DB-08** (المنصة):

| الخيار | يحقق M28؟ | ملاحظات |
|---|---|---|
| **A. Cloudways Flexible + Laravel (PHP)**، وواجهات الـDashboard بـReact أو Blade | ✅ نظريًا | الأنضج على Cloudways: `private_html`، MySQL/MariaDB، Cron، SSH |
| **B. Cloudways Velocity + Next.js (Node)** | ⚠️ يحتاج تحقق | يجب التأكد من 3 أمور:<br>1. قاعدة بيانات SQL خاصة.<br>2. قرص دائم خاص للمرفقات لا يُخدم عامًا.<br>3. نسخ احتياطي للملفات.<br>المصادر العامة متضاربة حول SSH والتخزين |
| **C. WordPress (الحالي أو جديد) + Plugin** | ⚠️ ممكن تقنيًا | لكن M28 §02 يمنع استخدام قاعدة WordPress القديمة كمخزن عشوائي. أي WordPress جديد = تطبيق مستقل |
| **D. خدمة خارجية (Supabase، Vercel، HubSpot…)** | ❌ | يخالف M28 §01 و§71 للبيانات والملفات |

> **لا نقرر المنصة هنا.** القرار بند مفتوح (PO-006، PO-044). هذا القيد يدخل في مقارنة DB-08.
> **مخطط قاعدة البيانات** (`RECRUITMENT-DATA-MODEL.md`) **مكتوب بصيغة محايدة** تعمل على MySQL 8 / MariaDB 10.6+ وعلى PostgreSQL.

## 6. النسخ الاحتياطي والاستعادة (M28 §70) — يُتحقق ولا يُفترض

| البند | المطلوب | الحالة |
|---|---|---|
| **قاعدة البيانات** | نسخ تلقائي (التكرار من A-08)، ونسخة يدوية قبل أي Migration على Production | ⏳ يُتحقق |
| **المرفقات** (`private_html/recruitment/`) | التأكد أن النسخ يشملها فعلًا (A-08). **إن لم يشملها:** نسخ منفصل بسكربت مجدول إلى مسار خاص | ⏳ |
| **الاحتفاظ** | مدة الاحتفاظ الفعلية (A-08) تُوثق | ⏳ |
| **الحذف النهائي والنسخ** | البيانات المحذوفة نهائيًا تبقى في النسخ الاحتياطية حتى انتهاء مدة احتفاظها. **يُذكر هذا في سياسة الحذف** (`RECRUITMENT-SECURITY.md` §9) | موثق |
| **إجراء الاستعادة** | 1. استعادة على تطبيق Staging (وليس Production).<br>2. فحص عينة طلبات ومرفقات بالـchecksum.<br>3. توثيق الزمن.<br>4. **اختبار استعادة قبل الإطلاق** وكل 6 أشهر. | ⏳ يُنفذ في المرحلة 16 |
| **التكلفة** | أي Offsite backup إضافي له تكلفة تُعرض قبل التفعيل | قاعدة المشروع |

## 7. المراحل والبوابات (M28 §77)
| # | المرحلة | الحالة |
|---|---|---|
| 1 | قراءة المشروع والقرارات | ✅ (Master + M28) |
| 2 | Cloudways audit | ⛔ **محجوب: يحتاج الوصول** (§3) |
| 3 | Recruitment IA | ✅ مسودة: `CAREERS-REQUIREMENTS.md` + الـWireframes |
| 4 | Data model | ✅ مسودة: `RECRUITMENT-DATA-MODEL.md` |
| 5 | Security / privacy / storage | ✅ مسودة: `RECRUITMENT-SECURITY.md` + هذا الملف |
| 6 | Careers form wireframe | ✅ مسودة: `docs/careers/wireframes/` |
| 7 | Dashboard wireframes | ✅ مسودة |
| 8 | **Owner-approved architecture check** | ⏳ **بوابة: لا Backend قبلها** |
| 9–17 | التنفيذ والاختبار وStaging والجاهزية | ⛔ بعد 2 و8 + قرار المنصة DB-08 + بوابات المشروع العامة |
