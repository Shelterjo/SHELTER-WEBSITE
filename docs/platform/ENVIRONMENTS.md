# ENVIRONMENTS — البيئات: Development · Staging · Production

| البند | القيمة |
|---|---|
| **الغرض** | فصل واضح بين التطوير والاختبار والإنتاج: قواعد بيانات وإعدادات وأسرار منفصلة، Staging غير مفهرس، ولا تختلط بياناته أو تحليلاته بالإنتاج |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 1** (environments) ← تكتمل في **PHASE 7** (staging). خطة المشروع: P06 · P08 |
| **المتطلبات** | M35 §1 · §27 · §52 · M36 §11 · §16 · SEO-028 · SEC-002 · SEC-004 · CW-05 · CW-06 · CW-07 · DEPLOY-002 · DEPLOY-004 · CF-002 · CF-003 · TEST-024 · MON-009 · GC-37 · AC-4 (FINAL-ARCHITECTURE-REVIEW §6) |
| **المراجع الملزمة** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) · [`ADR-001`](../adr/ADR-001-platform.md) · [`CLOUDWAYS-RECRUITMENT-ARCHITECTURE`](../CLOUDWAYS-RECRUITMENT-ARCHITECTURE.md) §2–§3 |

> **القاعدة:** لا يعمل Claude Code ولا أي اختبار تطوير على Production (M35 §1).
> المسار الوحيد: **Development → Testing → Staging → Final Verification → Owner Approval → Production** (M36 §11).
> هذه الوثيقة **تصف العملية فقط**. إنشاء أي تطبيق، أو تغيير DNS أو Cloudflare أو Google، لا يُنفذ من هنا.

## 1. البيئات الثلاث
| | Development | Staging | Production |
|---|---|---|---|
| **المكان** | حاوية التطوير + الـCI | تطبيق Cloudways **مستقل** على السيرفر الحالي (اسم مقترح `shelter-staging`) | تطبيق Cloudways **جديد** `shelter` (ADR-001). WordPress القديم وقاعدته **لا يُلمسان** حتى الانتقال |
| **الرابط** | `127.0.0.1` | رابط Cloudways الافتراضي للتطبيق (**بلا تغيير DNS**). نطاق فرعي مثل `staging.shelterjo.com` = تغيير DNS ← موافقة (CF-003) | `www.shelterjo.com` بعد الانتقال فقط ([`LAUNCH-READINESS`](LAUNCH-READINESS.md)) |
| **قاعدة البيانات** | SQLite للاختبارات الآلية. MySQL في الـCI لاختبار الـMigrations فقط | MySQL/MariaDB **منفصلة** | MySQL/MariaDB **منفصلة** |
| **البيانات** | اصطناعية + Seeders المعتمدة فقط (المنيو المجمّد، السوق JO، الفروع بالحقول المعتمدة) | اصطناعية + نفس الـSeeders. **لا بيانات متقدمين أو شركاء حقيقيين** (CW-07) | حقيقية |
| **الأسرار** | قيم وهمية | `.env` خاص بـStaging | `.env` خاص بـProduction. **لا يُنسخ لأي بيئة أخرى** |
| **من يصل** | Claude | الـOwner وClaude فقط، خلف HTTP auth | الزوار + Dashboard للـOwner فقط |
| **الفهرسة** | — | **ممنوعة:** HTTP auth + `X-Robots-Tag: noindex, nofollow` + `robots.txt` = `Disallow: /`. robots.txt وحده ليس حماية (SEO-028، SEC-004) | حسب SEO (Sitemap، canonical) |
| **Analytics** | لا شيء | لا شيء افتراضيًا. لاختبار التتبع: GA4 Property أو Data stream **اختباري** + DebugView. **لا معرّف Production أبدًا** (AC-4) | معرّفات Production فقط |
| **البريد والنماذج** | `log` | `log`: لا يخرج أي بريد، والطلبات تُحفظ في قاعدة Staging فقط | V1 لا يرسل بريدًا؛ الإشعارات داخل الـDashboard (MON-009) |
| **التكاملات الخارجية** | معطلة | قراءة فقط أو `DEMO DATA`. **لا كتابة** على GBP أو GSC أو GA4 الحقيقية | حسب سجل `integrations` وبموافقتك |
| **Storybook** (مرجع المكونات، ملفات ثابتة خارج التطبيق) | ✅ | ✅ (عند الحاجة) | ❌ لا يُنشر |
| **شريط تعريف** | — | شريط ثابت "STAGING — بيانات اختبار" في الموقع والـDashboard | — |

## 2. مصفوفة الإعدادات (`.env` لكل بيئة)
| المفتاح | Development / CI | Staging | Production |
|---|---|---|---|
| `APP_ENV` | `local` / `testing` | `staging` | `production` |
| `APP_DEBUG` | `true` | `false` | **`false` إلزاميًا** (سكربت النشر يرفض غير ذلك) |
| `APP_URL` | محلي | رابط Staging | النطاق الرسمي (قرار الـHost: DB-11) |
| `APP_KEY` | محلي | **مستقل** | **مستقل** — نسخته الآمنة في [`DISASTER-RECOVERY`](DISASTER-RECOVERY.md) §2 |
| `DB_*` | `sqlite` | مستخدم التطبيق بأقل صلاحية | مستخدم التطبيق: لا `DROP` ولا `GRANT` (CW-06) |
| `DB_MIGRATIONS_*` | — | مستخدم Migrations منفصل | مستخدم Migrations منفصل، يُستخدم **أثناء النشر فقط** |
| `MAIL_MAILER` | `log` | `log` (مقفل باختبار) | `log` في V1. أي بريد لاحقًا = قرار منفصل |
| `ANALYTICS_ENABLED` · `GA4_MEASUREMENT_ID` · `GTM_CONTAINER_ID` | `false` · فارغ | `false` · معرّف اختباري فقط عند الحاجة | `true` بعد Google setup · المعرّفات الحقيقية (G-08، G-10: `MISSING — OWNER INPUT REQUIRED`) |
| `RUM_ENABLED` | `false` | اختياري (لقاعدة Staging) | بعد قرار الخصوصية PO-019 |
| `ROBOTS_MODE` | `block` | `block` | `public` |
| `STAGING_BASIC_AUTH_*` | — | مفعّل؛ القيم في `.env` فقط ولا تُرسل في المحادثة | غير موجود |
| `CHANNEL_WRITE_ENABLED` | `false` | `false` | `true` لكل قناة بعد ربطها وموافقتك |
| `CLOUDFLARE_PURGE_TOKEN` | — | — | توكن محدود الصلاحية ([`CACHE-CDN`](CACHE-CDN.md) §6) |
| `QUEUE_CONNECTION` · `CACHE_STORE` | `sync` · `array` | `database` (أو `redis` إن توفر — A-03) | مثل Staging |

**قواعد:**
- القيم السرية لا تُكتب في Git، ولا في الـFrontend، ولا في الـCMS، ولا في المحادثة (M35 §27، SEC-002).
- مكانها: `private_html/shared/.env` لكل تطبيق، خارج مجلد الويب (CW-05).
- كل تكامل يُسجَّل في `integrations` مع حقل **البيئة**، **بلا القيمة السرية**.
- Staging لا يملك أي توكن Production (GBP، GSC، GA4، Cloudflare).

## 3. من ينشئ تطبيقات Cloudways؟
**الـOwner**، أو Claude **بصلاحية يمنحها الـOwner** (External authorization — M36 §24، ADR-001).

| الخيار | ما يفعله الـOwner | ما يفعله Claude |
|---|---|---|
| **A (موصى به)** | ينشئ تطبيقين (Staging وProduction) على السيرفر الحالي، ويمنح مستخدم SSH/SFTP **على مستوى التطبيق** (ليس Master) لكل منهما. البيانات توضع في **أسرار بيئة العمل**، لا في الرسائل | كل الإعداد داخل التطبيق: PHP، الـwebroot، `.env`، Cron، Queue، النشر |
| **B** | يدعو Claude كـTeam Member بصلاحيات محدودة (إن أتاحتها اللوحة) | ينشئ التطبيقين بعد موافقتك |

- **ممنوع:** Cloudways API Key (AC-RULE-04)، وكلمات المرور في المحادثة، ولمس أي تطبيق آخر على السيرفر (ومنها Falcon — M36 §16).
- **لا قاعدة بيانات على جهاز الـOwner** (M36 §16).
- إذا لم يتحمل السيرفر تطبيقين إضافيين: الترقية **قرار مالي** يُعرض عليك، ولا يُنفذ قبل موافقتك (ADR-001).

## 4. ما يُتحقق في Cloudways audit قبل الإنشاء (قراءة فقط)
| البند | لماذا | المرجع |
|---|---|---|
| خطة السيرفر والمساحة والذاكرة | تطبيقان إضافيان + 5 إصدارات + نسخ احتياطية | A-02 · A-06 |
| إصدار PHP (8.3+) لكل تطبيق، وRedis، وVarnish | متطلبات Laravel 13، والـCache | A-03 |
| تغيير الـwebroot إلى `public_html/current/public` واتباع الـsymlink | النشر الذري ([`DEPLOYMENT`](DEPLOYMENT.md) §5) | A-03 |
| مستخدما قاعدة بيانات بصلاحيات مختلفة | Least privilege | A-04 · CW-06 |
| مستخدم SSH على مستوى التطبيق | النشر بلا Master | A-05 |
| Cron وإدارة الـQueue لكل تطبيق | الجدولة والطوابير | A-11 |
| النسخ الاحتياطي يشمل `private_html` | الملفات الخاصة | A-08 |

## 5. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| Development + Staging واحد + Production | نطاق فرعي لـStaging (بموافقة DNS) · Preview لكل PR (تكلفة أعلى، غير مقترح الآن) |
| Staging على رابط Cloudways، بلا Cloudflare أمامه | Staging خلف Cloudflare لاختبار سلوك الـCache حرفيًا |

## 6. البيانات
- **لا جداول جديدة.** كل بيئة لها قاعدتها: `integrations` (حقل البيئة)، و`settings`، و`feature_flags`.
- **اختبار الاستعادة على Staging** يُستعاد في **قاعدة مؤقتة منفصلة** تُحذف بعد التحقق، ويُسجَّل في `audit_logs` (G14-TF-11، [`DISASTER-RECOVERY`](DISASTER-RECOVERY.md) §5).

## 7. مكانه في الـDashboard
- **النظام ← التكاملات:** بيئة كل تكامل وحالته.
- شريط "STAGING" ثابت أعلى كل شاشة في Staging.

## 8. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| ENV-T1 | طلب أي مسار في Staging بلا بيانات HTTP auth (ومنه `robots.txt`) | `401` |
| ENV-T2 | طلب صفحة في Staging مع المصادقة | ترويسة `X-Robots-Tag: noindex, nofollow`، و`robots.txt` = `Disallow: /` |
| ENV-T3 | البحث في HTML وJS المبنيين لـStaging عن معرّف GA4 أو GTM الخاص بالإنتاج | لا وجود له |
| ENV-T4 | إرسال نموذج في Staging | سجل في قاعدة Staging فقط، ولا بريد صادر (`MAIL_MAILER=log`) |
| ENV-T5 | محاولة مزامنة GBP من Staging | مرفوضة لأن `CHANNEL_WRITE_ENABLED=false`، وتُسجَّل |
| ENV-T6 | بناء Production لا يحتوي `storybook-static` ولا أي مسار معرض | لا ملفات Storybook في الإصدار |
| ENV-T7 | `.env` على Production فيه `APP_DEBUG=true` | سكربت النشر يرفض النشر |
| ENV-T8 | خطأ 500 مقصود في Staging | صفحة الخطأ بالـDesign System، **بلا Stack trace** |
| ENV-T9 | فحص الأصول المبنية (`public/build`) عن أنماط أسرار | لا شيء |

## 9. ما لا يُفعل
- لا تطوير ولا اختبارات تطوير على Production. المسموح بعد نشر معتمد: **فحوص قراءة فقط** بحدود TEST-024 (150–300 طلب موزعة).
- لا نسخ لبيانات Production إلى Staging للاختبار العام (الاستثناء الوحيد: اختبار الاستعادة بشروطه).
- لا أسرار مشتركة بين البيئات، ولا Cloudways API Key.
- لا تغيير DNS لـStaging في V1.
