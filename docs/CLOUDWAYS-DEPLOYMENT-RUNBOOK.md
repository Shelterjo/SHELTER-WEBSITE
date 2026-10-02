# CLOUDWAYS DEPLOYMENT RUNBOOK — دليل النشر على Cloudways

| البند | القيمة |
|---|---|
| **الحالة** | `DRAFT — PENDING OWNER APPROVAL` · Staging: `IN PROGRESS` (D-349) · Production: `BLOCKED` (بوابة: تحقق Staging + موافقة صريحة من الـOwner) |
| **المرجع** | قرار D-349 (M74: البدء ببناء Staging الآن، دون لمس الموقع الحالي) · [`platform/DEPLOYMENT.md`](platform/DEPLOYMENT.md) §5 (الآلية) · [`platform/ENVIRONMENTS.md`](platform/ENVIRONMENTS.md) · [`platform/ROLLBACK.md`](platform/ROLLBACK.md) · [`platform/CACHE-CDN.md`](platform/CACHE-CDN.md) |
| **الملفات** | `.github/workflows/deploy-staging.yml` · `scripts/deploy/build-release.sh` · `scripts/deploy/remote-release.sh` · `scripts/deploy/render-env.py` · `scripts/deploy/prepare-key.py` · `deploy/staging.env.template` |
| **قاعدة ثابتة** | التطبيق `Shelter Website` (WordPress الحالي) وقاعدة بياناته وسجلات البريد (MX/SPF/DKIM/DMARC) **لا تُلمس أبدًا** |

## 1. البنية الحالية (تدقيق قراءة فقط، 2026-10-02، من صور الـOwner)

### Cloudways
| البند | القيمة | الأثر |
|---|---|---|
| السيرفر | DigitalOcean · 2 GB · Amsterdam · IP في الـSecret `STAGING_SSH_HOST` | Staging والموقع الحالي على نفس السيرفر |
| PHP / قاعدة البيانات | PHP 8.3 · MariaDB 10.11 | = ما اختُبر عليه التطبيق محليًا |
| Redis / Supervisor | مفعّل / غير مثبت | لا حاجة لـworker (لا Jobs في التطبيق) |
| التطبيقات | `shelter-staging` (PHP، Lightning: Nginx + PHP-FPM) · `Shelter Website` (WordPress، حي) | لا يُلمس الثاني |
| مستخدم النشر | `shelterdeploy` (SFTP/SSH على مستوى تطبيق `shelter-staging` فقط، بمفتاح SSH) | لا master ولا كلمات مرور |
| النسخ الاحتياطي | يومي 11:37 UTC، أسبوع | + نسخة التطبيق `ops:backup-db` قبل كل إصدار |

### Cloudflare
| البند | القيمة | متى يتغير |
|---|---|---|
| A `@` و`www` | → IP السيرفر، Proxied | لا تغيير: التحويل يتم داخل Cloudways (أي تطبيق يملك الدومين) |
| `_acme-challenge` CNAME | → تطبيق WordPress | يُوجَّه للتطبيق الجديد عند التحويل فقط |
| SSL/TLS | Full · Always Use HTTPS ON · TLS ≥ 1.2 · HSTS OFF | Full (strict) عند التحويل · HSTS بعد الاستقرار (بموافقة) |
| Page Rules / Rules | لا يوجد | — |
| Caching | Standard · Browser TTL 4h | "Respect Existing Headers" عند التحويل (بموافقة) |
| Rocket Loader / Email Obfuscation | OFF / OFF | يبقيان OFF (يكسران الـCSP) |
| Bot Fight Mode | ON | قد يتحدى أدوات المراقبة |
| DNSSEC | Pending (DS غير مضاف عند المسجّل) | بعد استقرار الموقع الجديد فقط |
| خط الأساس للموقع القديم (RUM p75) | LCP 2,334 ms · TTFB 2,030 ms | للمقارنة بعد الإطلاق (PERF-004) |

## 2. شكل التطبيق على السيرفر
```
<مجلد تطبيق shelter-staging>/
├─ public_html/
│   ├─ releases/<release_id>/      ← إصدار كامل (vendor + public/build)، آخر 3 فقط
│   └─ current → releases/<id>      ← Webroot في Cloudways = public_html/current/public
└─ private_html/shelter/
    ├─ .env            (600)        ← أسرار البيئة، يُكتب مرة واحدة من الـpipeline
    ├─ storage/                     ← جلسات، سجلات، cache، ملفات خاصة، النسخ الاحتياطية
    ├─ media-public/                ← صور معتمدة تُقدَّم على /media (مشتركة بين الإصدارات)
    ├─ bin/remote-release.sh
    └─ .seeded                      ← علامة: البيانات المعتمدة والمنيو عُبّئت مرة واحدة
```
`release_id` = `YYYY.MM.DD-HHMMSS-<commit>`، و`release.json` داخل كل إصدار يحمل الـcommit ووقت البناء.

## 3. إعداد لمرة واحدة (الـOwner)
| # | الخطوة | الحالة |
|---|---|---|
| 1 | مفتاح SSH على جهاز الـOwner: `ssh-keygen -t ed25519 -C "shelter-staging-deploy" -f "$env:USERPROFILE\.ssh\shelter_staging"` (Enter مرتين، بلا Passphrase) | ✅ |
| 2 | Cloudways ← `shelter-staging` ← Access Details ← Application Credentials: مستخدم `shelterdeploy` + المفتاح **العام** `shelter_staging.pub` | ✅ |
| 3 | GitHub ← Settings ← Secrets and variables ← Actions: الأسرار التسعة (القائمة في رأس `deploy-staging.yml`). `STAGING_SSH_KEY` = محتوى الملف **الخاص** `shelter_staging` كاملًا (انظر §7) | ✅ |
| 4 | Cloudways ← Server ← Settings & Packages / Security: **SSH Shell Access** مفعّل للتطبيق | يُتحقق في أول تشغيل |
| 5 | Cloudways ← `shelter-staging` ← Application Settings: **Varnish OFF** (كل صفحة لها جلسة وحالة "مفتوح الآن" حية) | مطلوب |
| 6 | Cloudways ← `shelter-staging` ← Application Settings ← **Webroot** = `public_html/current/public` — بعد أول تشغيل `stage` | بعد §4 الخطوة 1 |
| 7 | Cloudways ← Cron Job Management ← Advanced: `* * * * * cd <مجلد التطبيق>/public_html/current && php artisan schedule:run >> /dev/null 2>&1` | بعد أول `full` |
| 8 | حساب الـOwner على Staging: عبر SSH Terminal في Cloudways، داخل `public_html/current`: `php artisan shelter:owner info@shelterjo.com` — كلمة المرور يكتبها الـOwner بنفسه ولا تمر بالمحادثة | بعد أول `full` |

## 4. نشر إصدار
GitHub ← Actions ← **Deploy staging** ← Run workflow:
- `ref`: الفرع أو الـcommit.
- `step`: `stage` (رفع وفحص فقط) أو `full` (رفع + تفعيل + فحص بعد النشر).

| # | الخطوة | ما يحدث | يتوقف إن |
|---|---|---|---|
| 1 | فحص الأسرار | أسماء فقط، لا قيم | سر ناقص |
| 2 | البناء | `build-release.sh`: composer `--no-dev` + `npm run build`؛ يحذف tests/tooling/docs (عدا ملفي المنيو للـseeder) | فشل البناء |
| 3 | SSH | `prepare-key.py` يصلح المفتاح أو يذكر سبب رفضه؛ يُطبع الـfingerprint فقط | مفتاح غير صالح |
| 4 | رفع وتجهيز | scp + تحقق sha256 + فك في `releases/<id>` | اختلاف الـchecksum |
| 5 | حارس الـWebroot | يطلب `release.json` و`.env` و`composer.json` من الرابط العام | أي منها 200 ← يُحذف الإصدار ولا يُفعّل |
| 6 | `.env` | أول مرة فقط: `render-env.py` يولّد `APP_KEY` ومفاتيح التوظيف داخل الـrunner ويرسلها عبر SSH، لا تُطبع | — |
| 7 | التفعيل | ربط `.env` و`storage` و`public/media` ← `ops:backup-db --reason=pre-deploy` (من الإصدار الثاني) ← `migrate --force` ← seed مرة واحدة + `search:rebuild` ← `optimize` ← تبديل `current` ذري ← إبقاء آخر 3 | فشل النسخة أو الـmigration |
| 8 | فحص بعد النشر | 401 بلا كلمة مرور · 200 لـ`/up` و`/ar/` و`/en/` والمنيو والفروع ودخول اللوحة و`robots.txt` · `noindex` · ملفات CSS/JS والشعار 200 | أي فحص يفشل |

**تجربة محلية كاملة (2026-10-02):** بناء ← stage ← تفعيل أول (migrate + seed) ← تفعيل ثانٍ (نسخة احتياطية ← تبديل ← إبقاء) ← فحص بعد النشر: **0 فشل**، والملفات الحساسة كلها 404. كشفت التجربة خللًا واحدًا (ملفا المنيو) وأُصلح في `5a0ca15`.

## 5. التراجع (Rollback)
- **الكود:** على السيرفر `ln -sfn releases/<id-السابق> public_html/current.next && mv -Tf public_html/current.next public_html/current` ثم `php artisan optimize` داخل الإصدار السابق. الإصدارات الثلاثة الأخيرة موجودة دائمًا.
- **قاعدة البيانات:** آخر نسخة `private_html/shelter/storage/app/private/backups/db-*-pre-deploy-*.sql.gz`. الاستعادة قرار حساس: تُعرض على الـOwner أولًا.
- التفاصيل: [`platform/ROLLBACK.md`](platform/ROLLBACK.md).

## 6. التحويل إلى Production (لاحقًا — كل بند يحتاج موافقة صريحة)
1. Staging مُتحقق بالكامل + رسالة الجاهزية للـOwner.
2. خط أساس Search Console قبل التحويل.
3. تطبيق Production جديد في Cloudways (أو نقل الدومين) + `.env` إنتاجي (`APP_ENV=production` · `SHELTER_INDEXING` يبقى `false` حتى موافقة الإطلاق · بلا Basic Auth).
4. نقل الدومين من تطبيق WordPress إلى التطبيق الجديد داخل Cloudways + SSL.
5. توجيه `_acme-challenge` للتطبيق الجديد.
6. Cloudflare: SSL Full (strict) · Browser Cache TTL "Respect Existing Headers".
7. تفعيل التحويلات (redirects) من الروابط القديمة.
8. بعد الاستقرار: DNSSEC (DS عند المسجّل) ثم HSTS.

## 7. استكشاف الأعطال
| الرسالة في سجل GitHub | السبب | الحل |
|---|---|---|
| `STAGING_SSH_KEY … one short line` | لُصق شيء غير المفتاح (عادة لأن شيئًا آخر نُسخ بعد نسخ المفتاح) | `Get-Content -Raw "$env:USERPROFILE\.ssh\shelter_staging" \| Set-Clipboard` **كآخر نسخ**، ثم لصق مباشر في GitHub |
| `… holds the PUBLIC key` | لُصق `shelter_staging.pub` | الصق الملف بدون `.pub` |
| `… protected by a passphrase` | المفتاح بكلمة مرور | مفتاح جديد بـEnter مرتين، وتحديث العام في Cloudways |
| `Permission denied (publickey)` | المفتاح العام في Cloudways لا يطابق، أو SSH Shell Access مغلق | قارن الـfingerprint في السجل مع `ssh-keygen -lf "$env:USERPROFILE\.ssh\shelter_staging.pub"` |
| `NEXT: … set the web root` | أول تشغيل: الـWebroot ما زال `public_html` | §3 الخطوة 6 ثم `full` |
| 400 على كل الصفحات | `APP_URL` لا يطابق الرابط المستخدم (trustHosts) | صحّح `STAGING_APP_URL` (مع `https://` وبلا `/` في النهاية) |
| 500 بعد التفعيل | تفاصيله في `private_html/shelter/storage/logs/laravel-*.log` | — |
