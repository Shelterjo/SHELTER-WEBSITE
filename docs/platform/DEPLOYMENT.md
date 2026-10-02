# DEPLOYMENT — النشر وسجل الإصدارات وحوكمة الـMigrations

| البند | القيمة |
|---|---|
| **الغرض** | لا نشر عشوائي: كل إصدار له رقم وتغييرات ونتيجة اختبار وحالة ومرجع تراجع، ويعرف الـOwner دائمًا **"ماذا تغيّر في هذا الإصدار؟"** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `PARTIAL` — جاهز في التطبيق (`IMPLEMENTED — NOT YET VERIFIED` على Cloudways): `ops:backup-db` (§5 الخطوة 4) · `/up` يفحص القاعدة والـCache والتخزين · `TRUSTED_PROXIES` يُقرأ من `.env` · حارس الإنتاج في أدوات الاختبار. سكربت النشر وسجل `releases` وصفحة "الإصدارات": `NOT STARTED` |
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
4. **Backup لقاعدة البيانات:** `php artisan ops:backup-db --reason=pre-deploy`. **فشله يوقف النشر** (DEPLOY-005): الأمر يخرج بـexit ≠ 0 (التفاصيل في §10).
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
- **ما يشغّله الـCI اليوم** (`.github/workflows/quality.yml`، مع كل push):
  - **fast:** Pint · Larastan (level 8) · PHPUnit على **SQLite في الذاكرة** (الـMigrations تُشغَّل هنا على SQLite فقط) · TypeScript · ESLint/Prettier · Vitest.
  - **build:** `npm run build` مع ميزانية الحزمة.
  - **design-system:** Storybook × axe × 360/768/1280 × AR/EN.
  - **security:** Gitleaks · Semgrep · `composer audit` · `npm audit`.
  - **full** (تشغيل يدوي فقط): Trivy · Knip.
- **ما لا يشغّله الـCI بعد** (`NOT STARTED`): الـMigrations على MySQL/MariaDB، واختبار `migrate:rollback` لآخر دفعة، و**اختبارات الإصدار السابق على المخطط الجديد** (ضمان التراجع). حتى تُبنى:
  - `migrate --force` على **Staging** (MariaDB) قبل Production هو التحقق الوحيد على المحرك الحقيقي.
  - مع Backup قبله (الخطوة 4) ومراجعة `down()` لكل Migration جديدة.

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

## 10. نسخ قاعدة البيانات من التطبيق — `ops:backup-db` (DEPLOY-005، OPS-041)
**الحالة:** `IMPLEMENTED — NOT YET VERIFIED` على Cloudways. تحقق محلي: MariaDB 10.11 (نسخ ثم استرجاع في قاعدة ثانية بنفس عدد الجداول والصفوف) وSQLite (اختبارات `DatabaseBackupTest`).

| البند | القيمة |
|---|---|
| **متى** | يوميًا 03:00 بتوقيت عمّان من الـScheduler (§11)، وفي كل نشر قبل الـMigrations (§5 الخطوة 4). يدويًا: `php artisan ops:backup-db --reason=manual`. [`DISASTER-RECOVERY`](DISASTER-RECOVERY.md) §1 يقترح كل 6 ساعات: التغيير سطر واحد في `routes/console.php` مع رفع `BACKUP_KEEP` (56 = 14 يومًا) |
| **MySQL/MariaDB** | `mysqldump --single-transaction --quick --no-tablespaces --hex-blob`: نسخة متسقة بلا قفل للموقع |
| **كلمة المرور** | في **ملف خيارات مؤقت 0600** داخل مجلد النسخ (`--defaults-extra-file`)، يُحذف فور انتهاء الـdump حتى عند الفشل. **لا شيء على سطر الأوامر** (لا يظهر في `ps`) |
| **SQLite** (محلي) | `VACUUM INTO` (نسخة متسقة حتى أثناء الكتابة)، ثم `PRAGMA integrity_check` |
| **لا تُحسب النسخة قبل فحصها** | الـdump كامل (ينتهي بسطر `-- Dump completed`) أو نسخة SQLite سليمة، ثم الـ`.gz` يُقرأ كاملًا ويعطي نفس الحجم |
| **المكان** | `BACKUP_ROOT` (فارغ = `storage/app/private/backups`). المجلد 0700 والملفات 0600. **يرفض** مسارًا نسبيًا أو داخل `public/` (ولو عبر symlink). Cloudways: `…/private_html/backups` (يُتحقق في الـAudit أنه ضمن نسخ Cloudways، A-08) |
| **اسم الملف** | `db-<UTC yyyymmdd-hhmmss>-<السبب>-<6 أحرف عشوائية>.sql.gz` (أو `.sqlite.gz`) |
| **الاحتفاظ** | أحدث `BACKUP_KEEP` نسخة (الافتراضي 14). الأقدم يُحذف بعد كل نسخة ناجحة. لا يلمس أي ملف آخر في المجلد |
| **السجل والتنبيه** | كل تشغيل سطر في `scheduled_job_runs` (`JobRuns`): اسم الملف وحجمه وSHA-256. الفشل: exit 1، وإشارة HIGH "تعذّر تشغيل مهمة مجدولة: ops:backup-db" في **يحتاج انتباهك**، تُغلق وحدها بأول نسخة ناجحة. `--reason` خاطئ = exit 2 بلا تسجيل |
| **ما يراه الـOwner** | سطر **"آخر نسخة احتياطية لقاعدة البيانات"** أعلى شاشة **يحتاج انتباهك**: الوقت (عمّان) والحجم والحالة: سليمة · متأخرة (أقدم من 36 ساعة: ربما توقف الـCron) · فشلت آخر محاولة (مع وقتها) · لا توجد نسخة بعد |
| **الاسترجاع** | يدوي، وعلى Production **بموافقتك فقط**: `zcat db-….sql.gz \| mysql --defaults-extra-file=<ملف 0600> <قاعدة>`، ثم التحقق. لم يُجرَّب على Cloudways بعد. خطة التراجع الكاملة في [`ROLLBACK`](ROLLBACK.md) |
| **مكمّل لا بديل** | نسخ Cloudways للسيرفر تبقى كما هي. هذه النسخ على السيرفر نفسه، فلا تحمي من فقدانه. نسخة خارج السيرفر قرار لاحق (قد تكون خدمة مدفوعة: تُعرض عليك أولًا) |

## 11. الـCron والمهام المجدولة (شرط للنشر)
بدون الـCron لا نسخ يومية، ولا حذف لملفات التقديم غير المرسلة بعد 24 ساعة (خصوصية)، ولا تنبيهات الشاشة.

**سطر واحد لكل تطبيق** (Staging وProduction كلٌّ لنفسه): Cloudways ← Application ← Cron Job Management ← Advanced. تأكد من المجلد بـ`pwd` عبر SSH، ومن أن `/usr/bin/php -v` هو 8.3:
```
* * * * * cd /home/master/applications/<APP_FOLDER>/public_html/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
بدون تخطيط `current` الذري (§5): احذف `/current`.

| المهمة | متى (عمّان) | ماذا تفعل |
|---|---|---|
| `ops:backup-db` | يوميًا 03:00 | نسخة قاعدة البيانات (§10)، قبل أي مهمة تغيّر البيانات |
| `facts:expire-verified` | يوميًا 03:10 | المعلومات الموثّقة المنتهية تعود إلى "معتمدة" مع تنبيه للمراجعة |
| `monitors:daily` | يوميًا 03:20 | تنبيهات "يحتاج انتباهك": حقوق صور تنتهي خلال 30 يومًا، أسئلة شلتور المتكررة بلا جواب. ويحذف أسئلة شلتور الأقدم من 90 يومًا |
| `careers:prune-drafts` | كل ساعة | يحذف ملفات التقديم **غير المرسلة** الأقدم من 24 ساعة. الطلبات المرسلة لا تُلمس |

- كل مهمة تُسجَّل في `scheduled_job_runs` وكل فشل يرفع إشارة HIGH. كلها `withoutOverlapping`.
- **التحقق بعد الإعداد:** `php artisan schedule:list`، ثم في صباح اليوم التالي: سطر "آخر نسخة احتياطية" = سليمة.
- إذا توقف الـCron لا تستطيع مهمة أن تنبّه عن نفسها: سطر النسخة الاحتياطية يصبح "متأخرة" بعد 36 ساعة، وهذا هو المؤشر.

## 12. متغيرات البيئة على الخادم
- **المرجع الكامل:** [`.env.example`](../../.env.example). يغطي كل متغير تقرؤه الإعدادات، و`EnvExampleTest` يفشل إذا نقص متغير أو بقي متغير لا يُقرأ.
- **ملف `.env` لكل بيئة:** `private_html/shared/.env` (600)، مربوط بكل إصدار.
- **لا قيم سرية هنا ولا في Git ولا في المحادثة.**
- **الأسرار:** `APP_KEY` و`DB_PASSWORD` و`RECRUITMENT_ID_*`، ومعها `SHELTER_BASIC_AUTH_PASSWORD` على Staging. تُولَّد على السيرفر، **مختلفة بين Staging وProduction**.

| المتغير | Production | Staging | ملاحظة |
|---|---|---|---|
| `APP_ENV` | `production` | `staging` | |
| `APP_DEBUG` | `false` | `false` | الكود يفرض `false` هنا أيضًا |
| `APP_URL` | المضيف الرسمي https (DB-11: www)، بلا `/` في النهاية | مضيف Staging https | الموقع يرد فقط على نطاق `APP_URL` (غيره = 400، حتى `/up`) |
| `APP_KEY` | سر جديد | سر جديد آخر | `php artisan key:generate --show` |
| `DB_CONNECTION` | `mysql` أو `mariadb` حسب محرك Cloudways | نفسه | |
| `DB_HOST` · `DB_PORT` · `DB_DATABASE` · `DB_USERNAME` · `DB_PASSWORD` | من Cloudways ← Access details | قاعدة منفصلة | |
| `SESSION_LIFETIME` | `30` | `30` | **إلزامي صراحة:** افتراضي الكود 120 |
| `SESSION_ENCRYPT` | `true` | `true` | **إلزامي صراحة:** افتراضي الكود `false` |
| `LOG_STACK` · `LOG_LEVEL` · `LOG_DAILY_DAYS` | `daily` · `warning` · `14` | نفسها | الافتراضي `single` (ملف يكبر بلا حد) و`debug` |
| `TRUSTED_PROXIES` | `127.0.0.1,::1`، ومع Cloudflare: + كل نطاقاته من https://www.cloudflare.com/ips/ | نفسه | يُقرأ من `.env` (`config/trustedproxy.php`). تحقق على Staging أن عنوان الزائر يظهر لا عنوان Cloudflare. لا `*` |
| `SHELTER_INDEXING` | `false` حتى موافقة الإطلاق، ثم `true` | `false` دائمًا | |
| `SHELTER_BASIC_AUTH_USER` · `_PASSWORD` | فارغان | مطلوبان | كل الموقع خلفها عدا `/up` |
| `CAREERS_STORAGE_ROOT` | `…/private_html/recruitment` | مثله في تطبيق Staging | خارج الـwebroot |
| `MEDIA_STORAGE_ROOT` | `…/private_html/media` | مثله | الأصول الخاصة |
| `MEDIA_PUBLIC_ROOT` | فارغ | فارغ | `public/media` symlink إلى مجلد مشترك ليبقى بين الإصدارات |
| `BACKUP_ROOT` · `BACKUP_KEEP` | `…/private_html/backups` · `14` | مثله | §10 |
| `CAREERS_FORM_ENABLED` · `FRANCHISE_FORM_ENABLED` · `FEEDBACK_FORM_ENABLED` | `false` حتى موافقتك على كل نموذج | لا أثر | على Staging تفتح النماذج متى توفرت متطلباتها: بيانات اختبار فقط |
| `RECRUITMENT_ID_ENC_KEY` · `RECRUITMENT_ID_HMAC_KEY` | أسرار جديدة | أسرار أخرى | نسخة offline عند الـOwner. فقدانها = أرقام هوية لا تُقرأ |
| `CACHE_STORE` · `SESSION_DRIVER` · `QUEUE_CONNECTION` | `database` | `database` | لا Queue worker مطلوب اليوم |
| `MAIL_MAILER` | `log` | `log` | لا بريد صادر في V1 |
| `AI_PROVIDER` | `none` | `none` | خدمة مدفوعة: لا تُفعَّل إلا بموافقتك |
| `SHELTER_RELEASE` | — | — | **ليس في `.env`:** يُمرَّر في shell البناء (`SHELTER_RELEASE=production npm run build`) |

- **حُذف من `.env.example`** (لا يقرؤه شيء): `APP_TIMEZONE` (التطبيق يعمل بـUTC وتوقيت عمّان صريح في الكود) و`VITE_APP_NAME`.

## 13. فحص الجاهزية `/up`
- يرد **200** فقط إذا:
  - أقلع التطبيق.
  - أجابت قاعدة البيانات.
  - احتفظ الـCache بقيمة.
  - قَبِل `storage/framework` و`storage/logs` الكتابة.
- غير ذلك: **500**. الاستجابة تقول "يعمل" أو "لا يعمل" فقط (JSON: `{"status":"up"}` / `{"status":"down"}`)، والسبب يُكتب في الـlog فقط.
- **مستثنى من:** Basic auth (Staging) ووضع الصيانة.
- **يخضع لـ`trustHosts`:** مراقب التشغيل يطلبه بالمضيف الحقيقي، لا برابط `*.cloudwaysapps.com`.
- **التحقق بعد النشر (§5 الخطوة 11):** `/up` + الصفحات الأساسية (`/ar/` · `/ar/jo/menu/` · `/en/jo/locations/`) + `robots.txt` و`sitemap.xml`.
- **أدوات الاختبار لا تعمل على الموقع الحي بالخطأ (INFRA-002، TEST-024):**
  - Playwright وLighthouse يرفضان `shelterjo.com` و`www.shelterjo.com`.
  - إلا مع `SHELTER_ALLOW_PRODUCTION=1`، لتحقق قراءة فقط بعد موافقتك.
  - الفحص الذاتي: `npm --prefix tooling run guard:selftest`.
