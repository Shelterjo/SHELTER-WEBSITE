# DEPLOYMENT — النشر وسجل الإصدارات وحوكمة الـMigrations

| البند | القيمة |
|---|---|
| **الغرض** | لا نشر عشوائي: كل إصدار له رقم وتغييرات ونتيجة اختبار وحالة ومرجع تراجع، ويعرف الـOwner دائمًا **"ماذا تغيّر في هذا الإصدار؟"** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 7** (production release process). حوكمة الـMigrations من **PHASE 1** (database). خطة المشروع: P06 · P08 · P11 |
| **المتطلبات** | M35 §2 · §51 · §58 · §59 · M36 §11 · §17 · OPS-039 · G14-CF-16 · DEPLOY-001 · DEPLOY-002 · DEPLOY-003 · DEPLOY-004 · DEPLOY-005 · DEPLOY-008 · DEPLOY-009 · D-172 · CW-06 · CF-005 · TEST-024 |
| **المراجع الملزمة** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) §3.5 (`releases`) · [`FINAL-ARCHITECTURE-REVIEW`](../FINAL-ARCHITECTURE-REVIEW.md) §13 · [`CI-CD-QUALITY-GATES`](CI-CD-QUALITY-GATES.md) · [`ROLLBACK`](ROLLBACK.md) · [`RELEASE-CHECKLIST`](RELEASE-CHECKLIST.md) |

## 1. المسار
```
GitHub (PR) → CI (بوابة الجودة) → Artifact واحد يُبنى مرة واحدة → نشر على Staging → تحقق على Staging
→ ملخص بلغة صاحب العمل → موافقة الـOwner (Dashboard أو المحادثة) → نشر على Production → تحقق بعد النشر → LIVE
```
- **نفس الـArtifact** (نفس الـchecksum) يُنشر على Staging ثم Production. **لا بناء على السيرفر.**
- أي فشل **P0** يوقف المسار (OPS-039).
- مسموح بلا موافقة مسبقة: Configure · Build · Test · Prepare، ومنها النشر على Staging (DEPLOY-002).
- **انضباط Git:** Commits صغيرة وذات معنى بعد كل Milestone مستقر، ولا عمليات Git مدمرة بلا داعٍ (M36 §17).

## 2. سجل الإصدارات `releases`
| الحقل | المحتوى |
|---|---|
| `release_id` | `YYYY.MM.DD-N` |
| `commit_sha` · `artifact_checksum` | الـCommit وSHA-256 للـArtifact |
| `changes_owner` | **ماذا تغيّر** بلغة صاحب العمل (عربي). **إلزامي** |
| `changes_technical` | من الـPRs والـCommits (تحت "تفاصيل متقدمة") |
| `ci_result` | لكل مرحلة: `PASS` / `FAIL` / `SKIPPED` / `NOT RUN` + رابط التقرير، أو `LOCAL RUN` |
| `migrations` | القائمة + النوع (expand / data / contract) + قابلة للعكس؟ |
| `config_changes` | **أسماء** مفاتيح `.env` و`settings` المتغيرة فقط — **بلا قيم** |
| `status` | انظر الدورة أدناه |
| `previous_stable_release_id` | **مرجع التراجع** |
| `approval` | من، متى، القناة (`DASHBOARD` / `CHAT`)، والدليل |
| `deployed_at` · `verified_at` · `rolled_back_at` | التوقيتات |

**دورة الحالة:**
`BUILT → ON_STAGING → VERIFIED → AWAITING_APPROVAL → APPROVED → DEPLOYING → DEPLOYED → LIVE`
- `DEPLOYED` = منشور ولم يُتحقق منه بعد (`IMPLEMENTED — NOT YET VERIFIED`). يصبح `LIVE` فقط بعد نجاح التحقق.
- الفروع: `FAILED` · `REJECTED` · `ROLLED_BACK` · `SUPERSEDED`.
- كل انتقال ← سطر في `audit_logs`. كل فشل ← `signals` (`kind=ISSUE`، الشدة HIGH أو CRITICAL).
- الكتابة **من سكربت النشر فقط** (`php artisan release:register` من ملف الـManifest داخل الـArtifact)، لا SQL يدوي.

## 3. صفحة "ماذا تغيّر في هذا الإصدار؟"
**المكان:** الـDashboard ← **النظام ← الإصدارات** (Owner فقط، صلاحية من الخادم — M35 §58).
```
الإصدار 2026.11.03-1 · الحالة: يعمل ✅ · نُشر: …
ماذا تغيّر؟
  • (3–7 نقاط بلغة صاحب العمل)
ماذا اختُبر؟  الموبايل والتابلت والديسكتوب ✅ · العربي والإنجليزي ✅ · الوصولية ✅ · السرعة ✅ · الشكل ⏸ غير مفعّل بعد
هل يحتاج قرارك؟  نعم — الموافقة على النشر   [أوافق]  [لا أوافق]
إذا ظهرت مشكلة: نعود إلى الإصدار 2026.10.28-2 خلال دقائق.
▸ تفاصيل متقدمة: Commits · نتائج CI لكل مرحلة · Migrations
```
- **لا مصطلحات مطور في النص الأساسي** (M32 §41). التفاصيل التقنية قابلة للفتح فقط.
- كل PR فيه قسم إلزامي **"ملخص للـOwner"**. الـBuild يفشل إذا غاب (CI-CD-QUALITY-GATES §2).
- **Command Center:** بطاقة "إصدار بانتظار موافقتك" عند وجود `AWAITING_APPROVAL`.

## 4. الموافقة على الإنتاج — بلا GitHub (G14-CF-16)
| القناة | كيف | ما يُسجَّل في `approval` |
|---|---|---|
| **الـDashboard** | زر [أوافق على النشر] + **إعادة تأكيد الهوية** (Passkey) | المستخدم، الوقت، `DASHBOARD` |
| **المحادثة** | رسالة صريحة من الـOwner تذكر **رقم الإصدار** | الوقت، `CHAT`، نص الرسالة حرفيًا |

- **الصمت ليس موافقة.** الموافقة لكل `release_id` وحده، لا موافقة عامة.
- سكربت النشر **يرفض** Production إذا كانت الحالة ≠ `APPROVED` أو اختلف الـchecksum.
- **الموافقة تشمل خطة التراجع:** الملخص يذكر صراحة: "إذا فشل التحقق بعد النشر نعود تلقائيًا إلى الإصدار X" ([`ROLLBACK`](ROLLBACK.md) §3).
- التغييرات الحساسة (DNS · GTM Publish · تغيير GA4 كبير · ملكية Search Console · تعديل GBP) **موافقات منفصلة** ولا تُدمج داخل إصدار (DEPLOY-003).

## 5. آلية النشر على Cloudways — المختار: SSH + مجلدات إصدار ذرية
**لماذا ليس Git deploy من Cloudways؟** يسحب الكود فقط: البناء يصبح على السيرفر، والتبديل غير ذري، ولا خطوات بعد السحب (**يُتحقق في الـAudit**). الخيار المختار أبسط وأأمن: **رفع Artifact جاهز + تبديل symlink ذري**.

```
applications/<app>/
├─ public_html/
│   ├─ releases/<release_id>/        ← Artifact مفكوك (vendor + public/build)
│   └─ current → releases/<release_id> ← الـwebroot = public_html/current/public
└─ private_html/
    ├─ shared/.env  (600) · shared/storage/
    ├─ recruitment/ · partnerships/   ← خاصة (CW-03)
    └─ artifacts/                     ← آخر 10 Artifacts
```

**الخطوات (سكربت واحد، يتوقف عند أول فشل):**
1. **فحص مسبق:** مستخدم SSH على مستوى التطبيق، والمساحة، و`APP_DEBUG=false`، والحالة `APPROVED` (للإنتاج).
2. رفع الـArtifact ← تحقق الـchecksum ← فك إلى `releases/<id>`.
3. ربط `.env` و`storage` المشتركين.
4. **Backup لقاعدة البيانات** (`ops:backup-db --reason=pre-deploy`). **فشله يوقف النشر** (DEPLOY-005).
5. `php artisan migrate --force` باتصال مستخدم الـMigrations (Expand فقط — §6).
6. `php artisan optimize` (config · route · view · event cache).
7. **التبديل الذري:** symlink مؤقت ثم `mv -T` إلى `current`.
8. `php artisan queue:restart` + إعادة ضبط OPcache (الطريقة تُحدد في الـAudit).
9. **الـCache:** مفاتيح Cache التطبيق تحمل `release_id` فتتجدد تلقائيًا. **لا Purge شامل** في Cloudflare ([`CACHE-CDN`](CACHE-CDN.md) §5).
10. **تسخين:** طلب الصفحات الأساسية بهدوء (الرئيسيتان، المنيو AR/EN، الفروع).
11. **تحقق بعد النشر** (قراءة فقط، بحدود TEST-024) ← `LIVE`، أو تراجع تلقائي ([`ROLLBACK`](ROLLBACK.md)).
12. الإبقاء على آخر 5 مجلدات إصدار (تراجع خلال ثوانٍ) وآخر 10 Artifacts.

**يُتحقق في Cloudways audit (CF-005، A-03، A-05، A-11):**
| البند | البديل إن لم يتحقق |
|---|---|
| تغيير الـwebroot إلى `public_html/current/public` واتباع الـsymlink | rsync إلى مجلد ثابت مع `artisan down` لثوانٍ — **أقل أمانًا، ويُعرض عليك قبل اعتماده** |
| إعادة ضبط OPcache بعد التبديل | إعادة تشغيل PHP-FPM من اللوحة (يدوي) |
| Cron لكل تطبيق (`schedule:run` كل دقيقة) | — (شرط) |
| تشغيل الـQueue worker بشكل دائم | `queue:work --stop-when-empty` من الـCron كل دقيقة |
| مستخدما قاعدة بيانات بصلاحيات مختلفة (CW-06) | مستخدم واحد مقيد + Migrations يدوية بإشراف |
| مسار الوسائط العامة المشترك بين الإصدارات | يُحدد بعد معرفة سياسة الـsymlink |

## 6. حوكمة الـMigrations (M35 §51)
- **مصدر المخطط الوحيد:** `database/migrations` بإصدارات. **لا تعديل يدوي لمخطط Production.**
- **Expand → Migrate → Contract:**

| المرحلة | متى | مثال |
|---|---|---|
| **Expand** | الإصدار N | إضافة عمود أو جدول `nullable`. الكود القديم يعمل كما هو |
| **Migrate** | الإصدار N (Job على الـQueue، idempotent) | نسخ البيانات أو تحويلها |
| **Contract** | الإصدار N+1 أو بعده، بعد أن يصبح N `LIVE` مستقرًا | حذف القديم. **ممنوع** في نفس إصدار الـExpand |

- لكل Migration دالة `down()`. Migration **غير قابلة للعكس** = موافقة صريحة منك (DEPLOY-008).
- **Backup قبل كل Migration** (آلي في الخطوة 4). **Staging أولًا** مع قياس المدة والأقفال.
- مستخدم Migrations منفصل يُستخدم أثناء النشر فقط. مستخدم التطبيق بلا `DROP` ولا `GRANT` (CW-06).
- الـCI يشغّل الـMigrations على MySQL، ويختبر `migrate:rollback` لآخر دفعة، ويشغّل **اختبارات الإصدار السابق على المخطط الجديد** (ضمان التراجع).

## 7. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| Claude ينفذ النشر عبر SSH **بعد موافقتك** | Job نشر في GitHub Actions بمفتاح نشر محدود (بعد موافقة I-07) |
| الموافقة من الـDashboard أو المحادثة | — |
| تشغيل البوابة محليًا إن لم تُعتمد دقائق CI | البوابة الكاملة في الـCI |

## 8. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| DEP-T1 | طلبات GET متواصلة أثناء النشر على Staging | صفر أخطاء 5xx أثناء التبديل |
| DEP-T2 | نشر Production وحالة الإصدار ≠ `APPROVED` (تشغيل تجريبي `--dry-run`) | رفض |
| DEP-T3 | فشل الـBackup المسبق | توقف النشر، الحالة `FAILED`، إشارة HIGH |
| DEP-T4 | اختبارات الإصدار السابق على المخطط بعد الـExpand | تنجح |
| DEP-T5 | فتح "الإصدارات" بلا جلسة Owner | منع من الخادم (Policy) |
| DEP-T6 | الموافقة من الـDashboard | تتطلب إعادة تأكيد الهوية، وتُسجَّل في `audit_logs` |
| DEP-T7 | فشل التحقق بعد النشر | تراجع تلقائي، الحالة `ROLLED_BACK`، إشارة CRITICAL في الـDashboard |
| DEP-T8 | PR بلا "ملخص للـOwner" | الـBuild يفشل |

## 9. ما لا يُفعل
- لا نشر على Production بلا موافقة لكل إصدار (DEPLOY-001، DEPLOY-004).
- لا بناء على السيرفر، ولا FTP، ولا تعديل ملفات على Production يدويًا، ولا SQL يدوي.
- لا Cloudways API Key، ولا مطالبة الـOwner باستخدام GitHub أو SQL أو الكود أو مدير ملفات Cloudways أو Terminal (M35 §59).
- لا تحديثات Major تلقائية على Production (OPS-043).
- لا وضع صيانة كامل في نشر عادي (AC-5: الصيانة الكاملة لترحيل أو حادثة أمنية فقط، وبموافقتك).
