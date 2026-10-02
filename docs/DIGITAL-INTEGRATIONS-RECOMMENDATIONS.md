# DIGITAL INTEGRATIONS — توصيات التكاملات الرقمية لـSHELTER COFFEE

| البند | القيمة |
|---|---|
| **الحالة** | `DRAFT — PENDING OWNER APPROVAL`. هذه توصيات من Claude، وليست قرارات. أي بند يصبح قرارًا فقط بموافقتك الصريحة |
| **آخر تحديث** | 2026-10-02 |
| **الغرض** | قائمة واحدة بكل خدمة يحتاجها الموقع أو قد تحتاجها: لماذا، وماذا تربط، وأثرها على الخصوصية، وتكلفتها، وما المطلوب منك |
| **وثائق مرتبطة** | [`TRACKING-AND-COOKIES-INVENTORY`](TRACKING-AND-COOKIES-INVENTORY.md) · [`SHELTER-DIGITAL-ECOSYSTEM`](SHELTER-DIGITAL-ECOSYSTEM.md) · [`OWNER-CONNECTION-CHECKLIST`](OWNER-CONNECTION-CHECKLIST.md) · [`platform/INTEGRATION-REGISTRY`](platform/INTEGRATION-REGISTRY.md) · [`platform/ACCESS-SETUP`](platform/ACCESS-SETUP.md) |
| **القرارات الحاكمة** | D-308 (التفويض بأقل صلاحية) · D-342 / D-343 (لا Cloudways ولا Cloudflare قبل أمرك) · D-175 (Website → GTM → GA4) · D-204 / D-149 (الأحداث) · D-210 / D-230 (لا خدمة مدفوعة بلا موافقة) · D-214 (الإشعارات داخل اللوحة في V1) · D-311…D-313 (Cloudflare) · PRIV-007 (لا تتبع إعلاني بلا موافقتك) |

## القواعد الثابتة (معتمدة منك)
- **لا خدمة مدفوعة ولا حصة مدفوعة** قبل موافقتك. نعرض أولًا: الخدمة، والغرض، والاستهلاك، والتكلفة، والبديل المجاني.
- **أقل صلاحية.** دعوات وأدوار فقط. **لا كلمات مرور في المحادثة أبدًا.**
- **الأسرار على السيرفر فقط** (`.env` على Cloudways، أو GitHub Actions secrets). لا شيء في Git ولا في الواجهة.
- **لا بيانات شخصية (PII) في أي أداة تحليلات.**
- **لا تغيير على Production** (DNS، التحويلات، Google Business، النشر) بلا موافقة صريحة على كل إجراء.
- **نعيد استخدام الحسابات الموجودة** (GA4، Pixel، Search Console) بدل إنشاء حسابات مكررة. لا تتبع مكرر.

## معنى الحالات
| الحالة | المعنى |
|---|---|
| `REQUIRED NOW` | لازم لإطلاق V1. **يُجهَّز الآن، ويُنفَّذ عند بوابته** (لا يعني التنفيذ فورًا) |
| `RECOMMENDED` | قيمة واضحة، لكن الإطلاق لا يتوقف عليه |
| `OPTIONAL` | فقط عند حاجة تجارية حقيقية (حملة، قرار مالي) |
| `FUTURE` | البنية جاهزة له، ولا عمل الآن |

## الملخص
| الحالة | التكاملات |
|---|---|
| **REQUIRED NOW** | GitHub · Cloudways · Cloudflare · Backups + DR · Security · Uptime monitoring · Error monitoring (داخلي) · Google Search Console · Google Business Profile (يدويًا) · Google Maps (روابط — مبني) · GTM · GA4 · Consent management · WhatsApp (مبني) · خصوصية نص البحث |
| **RECOMMENDED** | Performance / RUM · Lighthouse CI · Meta Business Suite (الملكية) · روابط الحسابات الاجتماعية · GBP API (لاحقًا) |
| **OPTIONAL** | Transactional email · Meta Pixel · Snapchat Pixel · Google Maps Embed · Sentry · Bing Webmaster Tools |
| **FUTURE** | Google Ads conversion · Meta Conversions API |

---

## أ. البنية والتشغيل

### 1. GitHub
| الحقل | التفاصيل |
|---|---|
| NAME | GitHub (`Shelterjo/SHELTER-WEBSITE`) |
| CATEGORY | Code · CI · Deploy |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | المكان الوحيد للكود وسجل كل تغيير. النشر إلى Cloudways يتم منه (بيئة العمل تعمل عبر HTTPS فقط) |
| WHAT IT ENABLES | اختبارات آلية قبل كل دمج (`quality.yml`)، نشر قابل للتراجع، حفظ أسرار النشر مشفرة |
| WHAT IT CONNECTS TO | Cloudways (SSH/SFTP للتطبيق فقط) · Google (مفتاح حساب الخدمة للقراءة) |
| DATA / PRIVACY IMPACT | الكود فقط. لا أسرار ولا بيانات زبائن (فحص Gitleaks في كل تشغيل) |
| COST / QUOTA | خطة الحساب الحالية. دقائق Actions للمستودع الخاص لها حصة — to confirm at setup |
| OWNER ACTION REQUIRED | تأكيد أن الحساب باسم المنشأة وأنك Owner فيه، وتفعيل 2FA. إضافة أسرار النشر بنفسك في Settings ← Secrets |
| CREDENTIALS REQUIRED | GitHub Actions secrets: `CLOUDWAYS_STAGING_*` (ثم Production) · `GOOGLE_SERVICE_ACCOUNT_JSON` |
| CURRENT STATUS | **يعمل:** الـCI أخضر على GitHub. ملكية الحساب `PENDING OWNER INPUT` (PO-037) |
| RECOMMENDATION | يبقى المصدر الوحيد. حماية الفرع الرئيسي وإلزام نجاح الـCI قبل الدمج (قرار تقني) |

### 2. Cloudways
| الحقل | التفاصيل |
|---|---|
| NAME | Cloudways (السيرفر Flexible الحالي) |
| CATEGORY | Hosting (Origin) |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | السيرفر الحالي يستضيف الموقع القديم، والقرار المعتمد (ADR-001) تطبيق Laravel جديد عليه |
| WHAT IT ENABLES | تطبيق Staging ثم Production، لكل منهما قاعدة بيانات منفصلة · مجلد `private_html` للملفات الخاصة (مرفقات التوظيف) · Cron للمهام المجدولة · نسخ احتياطي |
| WHAT IT CONNECTS TO | GitHub Actions (النشر) · Cloudflare (أمامه) · MySQL · لوحة التحكم |
| DATA / PRIVACY IMPACT | يحمل كل بيانات الموقع والطلبات (بيانات شخصية). الأسرار في `private_html/shared/.env` فقط |
| COST / QUOTA | اشتراك السيرفر القائم. أثر تطبيق إضافي على موارد السيرفر — to confirm at setup (Audit A-01…A-13) |
| OWNER ACTION REQUIRED | بعد أمرك «START CLOUDWAYS DEPLOYMENT»: إنشاء تطبيق `shelter-staging` على نفس السيرفر، ثم Application Credentials (ليست Master) تضعها أنت في GitHub secrets. WordPress الحالي لا يُلمس |
| CREDENTIALS REQUIRED | SFTP/SSH على مستوى التطبيق فقط. **لا Cloudways API Key** (AC-RULE-04) |
| CURRENT STATUS | **BLOCKED:** بوابة D-342/D-343 + PO-064 + PO-046. سكربتات النشر جاهزة محليًا: `DEFERRED — REQUIRES INFRASTRUCTURE CONNECTION` |
| RECOMMENDATION | PHP 8.3+ (متطلب Laravel 13) · MySQL/MariaDB منفصلة لكل بيئة · Cron كل دقيقة (`schedule:run` + الطابور) · Staging أولًا برابط Cloudways الافتراضي، بلا DNS وغير مفهرس (D-339) |

### 3. Cloudflare
| الحقل | التفاصيل |
|---|---|
| NAME | Cloudflare (زون `shelterjo.com`) |
| CATEGORY | DNS · CDN · SSL · WAF |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | **يعمل الآن فعلًا** أمام الموقع، والـNameservers عليه. يحمي السيرفر ويسرّع الصفحات |
| WHAT IT ENABLES | DNS · شهادة الحافة · Cache للملفات الثابتة والصفحات العامة · WAF وحماية DDoS · إخفاء عنوان السيرفر |
| WHAT IT CONNECTS TO | Namecheap (المسجّل) · Cloudways (الـOrigin) · Google Workspace (MX، SPF، DKIM، DMARC) · Search Console (سجل TXT) |
| DATA / PRIVACY IMPACT | يمرر كل الطلبات (IP والمتصفح) كمعالج. قد يضع Cookie أمان (`__cf_bm`) — انظر جرد الكوكيز |
| COST / QUOTA | **Free** (الخطة الحالية — تدقيق 2026-10-01) |
| OWNER ACTION REQUIRED | موافقة صريحة على كل تغيير Production. لاحقًا (PHASE 5/7): إنشاء Token لإبطال الـCache، محدود بالزون فقط (ACCESS-SETUP §2) |
| CREDENTIALS REQUIRED | Token قراءة `shelter-readonly` (قائم، صالح حتى 2027-10-01، محفوظ كـAPI credential). لاحقًا Token `Cache Purge` فقط. **لا Global API Key** |
| CURRENT STATUS | **نُفذ:** حذف السجلات المكشوفة (D-311) · SPF/DKIM/DMARC (D-312) · TLS 1.2 (D-313). **مؤجل:** Full (strict) بعد شهادة تشمل `www` · DNSSEC · الربط بالـOrigin الجديد (بعد Cloudways — D-343) |
| RECOMMENDATION | البقاء على Free. قواعد Cache حسب [`CACHE-CDN`](platform/CACHE-CDN.md): تجاوز (Bypass) للوحة التحكم والنماذج. `TRUSTED_PROXIES` بنطاقات Cloudflare |

### 4. Backups + Disaster Recovery
| الحقل | التفاصيل |
|---|---|
| NAME | النسخ الاحتياطي والاستعادة |
| CATEGORY | Backups · Disaster recovery |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | طلبات التوظيف والشراكات والمحتوى لا تعود إن ضاعت. **النسخة بلا اختبار استعادة لا تُعتبر نسخة** |
| WHAT IT ENABLES | الرجوع لنسخة سابقة: القاعدة، والوسائط، والملفات الخاصة، و`.env` مشفرًا |
| WHAT IT CONNECTS TO | نسخ Cloudways · مهام `ops:backup-*` و`ops:restore-test` · شاشة صحة الموقع |
| DATA / PRIVACY IMPACT | النسخ فيها بيانات شخصية: مشفرة، والوصول إليها محدود |
| COST / QUOTA | نسخ Cloudways على السيرفر ضمن الاشتراك (التفاصيل A-08). نسخة خارج السيرفر (Offsite): إضافة Cloudways **مدفوعة لكل GB**، أو تخزين خارجي بطبقة مجانية محدودة — to confirm at setup. البديل المجاني: تنزيل شهري مشفر |
| OWNER ACTION REQUIRED | اختيار خيار الـOffsite (قرار مالي). حفظ نسخة Offline من `APP_KEY` ومفاتيح الهوية ورموز الاسترداد |
| CREDENTIALS REQUIRED | لا جديد مع خيار Cloudways. مفتاح التخزين الخارجي (إن اختير) في `.env` |
| CURRENT STATUS | `SPEC — READY FOR BUILD` ([`DISASTER-RECOVERY`](platform/DISASTER-RECOVERY.md)). تدقيق A-08 `BLOCKED — ACCESS`. سجل المهام المجدولة مبني |
| RECOMMENDATION | نسخ يومي للقاعدة. **اختبار استعادة على Staging قبل الإطلاق** (شرط PHASE 7)، ثم كل 6 أشهر |

### 5. Security (Cloudflare WAF + أمان التطبيق)
| الحقل | التفاصيل |
|---|---|
| NAME | الحماية على الحافة وفي التطبيق |
| CATEGORY | Security |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | الموقع يجمع بيانات حساسة (التوظيف)، ولوحة التحكم تملك صلاحيات كاملة |
| WHAT IT ENABLES | **الحافة:** قواعد Cloudflare المُدارة المجانية + DDoS. **التطبيق:** CSP صارم (`'self'` فقط) · HSTS · رؤوس أمان · TOTP إلزامي للـOwner · حد للطلبات على البحث · تشفير أرقام الهوية |
| WHAT IT CONNECTS TO | Cloudflare · Laravel · فحوص الـCI (Gitleaks · Semgrep · Trivy) |
| DATA / PRIVACY IMPACT | يقلل خطر التسريب. لا يجمع بيانات إضافية عن الزوار |
| COST / QUOTA | Free (خطة Cloudflare المجانية + أدوات مفتوحة المصدر) |
| OWNER ACTION REQUIRED | 2FA على كل حساب. موافقة على أي قاعدة WAF جديدة على Production |
| CREDENTIALS REQUIRED | لا جديد |
| CURRENT STATUS | **مبني ومختبر محليًا** (الرؤوس، المصادقة، فحوص الـCI = 0 مشاكل). على الحافة: القواعد المُدارة فقط، بلا قواعد مخصصة |
| RECOMMENDATION | بعد الإطلاق: قاعدة WAF مخصصة لحماية `/dashboard`، وفحص ZAP على Staging. **تنبيه:** تشغيل GTM/GA4 يحتاج توسيع الـCSP بنطاقات Google المحددة فقط، ويُختبر على Staging |

### 6. Transactional email
| الحقل | التفاصيل |
|---|---|
| NAME | بريد الإشعارات |
| CATEGORY | Email |
| STATUS | `OPTIONAL` |
| WHY SHELTER NEEDS IT | **الموقع لا يرسل أي بريد اليوم** (`MAIL_MAILER=log`). المعتمد: V1 = إشعارات داخل اللوحة فقط (D-214). لا بريد عند وصول طلب توظيف أو شراكة أو استفسار، ولا بريد للزائر (M28، [`NOTIFICATIONS`](platform/NOTIFICATIONS.md) §5.2) |
| WHAT IT ENABLES | (إن اعتمدته) بريد لك فقط عند مشكلة **CRITICAL** (فشل نسخ، عطل جزئي) |
| WHAT IT CONNECTS TO | صندوق Google Workspace القائم لدومين `shelterjo.com` |
| DATA / PRIVACY IMPACT | الرسالة بلا أسماء أو هواتف أو بريد زبائن |
| COST / QUOTA | SMTP عبر صندوق Workspace القائم: **بلا تكلفة جديدة**. خدمات بريد المعاملات (Postmark · Resend · SES، موجودة كقالب في `config/services.php`): طبقات مجانية محدودة ثم دفع — to confirm at setup، وتحتاج موافقتك |
| OWNER ACTION REQUIRED | قرار: هل تريد بريد CRITICAL؟ وأي صندوق يستقبله (لا يُفترض `info@` — D-035) |
| CREDENTIALS REQUIRED | كلمة مرور تطبيق أو SMTP relay من Google Workspace، في `.env` فقط |
| CURRENT STATUS | مصمم ومعطّل حتى موافقتك. SPF/DKIM/DMARC للدومين جاهزة (D-312) |
| RECOMMENDATION | **لا خدمة بريد جديدة.** إن احتجت تنبيهًا خارج اللوحة: CRITICAL فقط عبر Workspace (حد 10 رسائل يوميًا). عند توقف السيرفر كليًا ينبهك مراقب التوفر (#7) ببريده |

### 7. Uptime monitoring
| الحقل | التفاصيل |
|---|---|
| NAME | مراقب التوفر الخارجي |
| CATEGORY | Monitoring |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | **السيرفر المتوقف لا يستطيع أن يخبرك بنفسه** |
| WHAT IT ENABLES | تنبيه خلال دقائق عند توقف الموقع، وسجل للتوفر |
| WHAT IT CONNECTS TO | `/up` (موجود اليوم) ثم `/health` الأعمق (PHASE 6) · بريدك |
| DATA / PRIVACY IMPACT | لا بيانات زوار |
| COST / QUOTA | UptimeRobot: خطة مجانية (فحص كل 5 دقائق). الحدود وشروط الاستخدام التجاري to confirm at setup ([`MONITORING`](platform/MONITORING.md) §4) |
| OWNER ACTION REQUIRED | الموافقة على إنشاء الحساب **باسم المنشأة**، وتحديد بريد التنبيه |
| CREDENTIALS REQUIRED | حساب الخدمة فقط. لا مفاتيح داخل الموقع |
| CURRENT STATUS | `NOT STARTED`. مسار `/up` موجود (Laravel). `/health` مخطط |
| RECOMMENDATION | UptimeRobot المجاني على Production بعد الإطلاق |

### 8. Error monitoring
| الحقل | التفاصيل |
|---|---|
| NAME | مراقبة الأخطاء |
| CATEGORY | Monitoring |
| STATUS | `REQUIRED NOW` (داخلي) · Sentry = `OPTIONAL` |
| WHY SHELTER NEEDS IT | أن نعرف بالعطل قبل أن يشكو زبون |
| WHAT IT ENABLES | سجلات Laravel ← **إشارة واحدة لكل مشكلة** (Signals) في «يحتاج انتباه» + ملخص يومي في اللوحة. أخطاء المتصفح ترسل إلى `/api/client-errors` منظّفة |
| WHAT IT CONNECTS TO | لوحة التحكم ← صحة الموقع |
| DATA / PRIVACY IMPACT | بلا PII (D-194): الرسائل مقصوصة ومنظفة من الأرقام والبريد |
| COST / QUOTA | الداخلي مجاني. **Sentry: طبقة مجانية محدودة، ثم اشتراك مدفوع** — to confirm at setup. لا تفعيل بلا موافقتك (D-210، D-230) |
| OWNER ACTION REQUIRED | لا شيء للداخلي |
| CREDENTIALS REQUIRED | لا (الداخلي). Sentry DSN في `.env` فقط إن اعتُمد |
| CURRENT STATUS | نواة `signals` وسجل المهام المجدولة **مبنية ومختبرة**. مراقبات الأخطاء نفسها PHASE 6 (`NOT STARTED`) |
| RECOMMENDATION | الداخلي فقط في V1. Sentry يُعرض عليك فقط إن ظهرت حاجة لا يغطيها الداخلي |

### 9. Performance / RUM
| الحقل | التفاصيل |
|---|---|
| NAME | قياس السرعة الحقيقية عند الزوار |
| CATEGORY | Performance |
| STATUS | `RECOMMENDED` |
| WHY SHELTER NEEDS IT | سرعة الموقع على هواتف الزوار تؤثر على الظهور في Google وعلى التجربة |
| WHAT IT ENABLES | **Search Console ← Core Web Vitals** (بيانات Chrome الحقيقية، مجانًا) + **RUM داخلي**: مكتبة `web-vitals` ← `/api/rum` ← أرقام مجمعة في اللوحة |
| WHAT IT CONNECTS TO | Search Console · لوحة التحكم · (اختياري) CrUX API |
| DATA / PRIVACY IMPACT | مجمّع، بلا معرّف، بلا IP مخزن، بلا Cookies |
| COST / QUOTA | مجاني (`web-vitals` مفتوح المصدر). CrUX API مجاني بمفتاح يحتاج موافقة (PO-039) |
| OWNER ACTION REQUIRED | قرار الخصوصية: هل يكفي الإفصاح في سياسة الخصوصية أم تلزم موافقة (G14-CF-02 ضمن PO-019) |
| CREDENTIALS REQUIRED | لا (الداخلي). مفتاح CrUX مقيّد إن اعتُمد |
| CURRENT STATUS | `SPEC — READY FOR BUILD` ([`REAL-USER-MONITORING`](platform/REAL-USER-MONITORING.md)). Lighthouse المحلي 0.99–1.0 |
| RECOMMENDATION | CWV من Search Console + RUM الداخلي. إرسال `web-vitals` إلى GA4 بديل اختياري فقط، **وليس الاثنين معًا** (لا قياس مكرر) |

### 10. Lighthouse CI / PageSpeed monitoring
| الحقل | التفاصيل |
|---|---|
| NAME | فحص الأداء الدوري |
| CATEGORY | Performance · QA |
| STATUS | `RECOMMENDED` |
| WHY SHELTER NEEDS IT | يكشف تراجع السرعة أو الوصولية قبل أن يصل للزوار |
| WHAT IT ENABLES | فحص الصفحات الحرجة (الرئيسية، المنيو، الفروع) ومقارنتها بخط أساس |
| WHAT IT CONNECTS TO | GitHub CI على Staging · تقرير في اللوحة (لاحقًا) |
| DATA / PRIVACY IMPACT | لا بيانات زوار |
| COST / QUOTA | Lighthouse مجاني. PageSpeed Insights API مجاني بمفتاح يحتاج موافقة (PO-039) |
| OWNER ACTION REQUIRED | لا شيء. الموافقة على مفتاح PSI فقط إن احتجناه |
| CREDENTIALS REQUIRED | مفتاح PSI مقيّد (اختياري) في GitHub secrets |
| CURRENT STATUS | Lighthouse في `tooling/` يعمل محليًا. ليس في الـCI بعد |
| RECOMMENDATION | إضافته للـCI على Staging بعد تشغيل الـStaging (مجاني) |

---

## ب. Google

### 11. Google Search Console
| الحقل | التفاصيل |
|---|---|
| NAME | Google Search Console |
| CATEGORY | SEO |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | الطريقة الوحيدة لمعرفة ماذا يبحث الناس ليصلوا إليك، ولحماية ترتيبك عند الانتقال (خريطة 301 — D-054) |
| WHAT IT ENABLES | الاستعلامات والصفحات والفهرسة والـSitemap وCore Web Vitals وأخطاء الـSchema · وحدة SEO في اللوحة (D-203) |
| WHAT IT CONNECTS TO | Google Search · GA4 (ربط) · لوحة التحكم (API قراءة) · Cloudflare (سجل TXT للتحقق) |
| DATA / PRIVACY IMPACT | أرقام مجمعة من Google، بلا PII |
| COST / QUOTA | مجاني |
| OWNER ACTION REQUIRED | تأكيد الـProperty ونوعه ومالكه (PO-011). إضافة بريد حساب الخدمة بصلاحية **Restricted**، أو تصدير CSV. أي تغيير ملكية أو إنشاء Domain property = موافقتك (D-051) |
| CREDENTIALS REQUIRED | حساب خدمة `shelter-reader` (مفتاحه في GitHub secrets — ACCESS-SETUP §3)، أو OAuth `webmasters.readonly` للوحة |
| CURRENT STATUS | الموقع القديم موثّق بـMeta tag في الرئيسية + سجل TXT في Cloudflare. الوصول `BLOCKED` (PO-011). الموقع الجديد: Sitemap وrobots وhreflang مبنية |
| RECOMMENDATION | الوصول **قبل** الانتقال لأخذ خط الأساس. **سجل TXT يبقى دائمًا**، لأن Meta tag القديم يختفي مع WordPress |

### 12. Google Business Profile (DRIVE · HOUSE)
| الحقل | التفاصيل |
|---|---|
| NAME | Google Business Profile — ملفا SHELTER COFFEE DRIVE وSHELTER COFFEE HOUSE |
| CATEGORY | Local SEO · Maps |
| STATUS | `REQUIRED NOW` (الملكية والتطابق يدويًا) · الربط بالـAPI = `RECOMMENDED` (PHASE 5) |
| WHY SHELTER NEEDS IT | أول ما يراه الناس في Google Maps وفي البحث المحلي |
| WHAT IT ENABLES | تطابق الاسم والعنوان والهاتف والساعات بين الموقع وGoogle. لاحقًا: إرسال الساعات الخاصة والإغلاق المؤقت من اللوحة، وقراءة المراجعات والرد عليها بموافقتك |
| WHAT IT CONNECTS TO | Master Data (المصدر) · Google Maps · Schema صفحات الفروع |
| DATA / PRIVACY IMPACT | بيانات أعمال عامة. المراجعات بيانات عامة لأشخاص (مدة تخزين نسخها في PO-019) |
| COST / QUOTA | مجاني. الـAPI مجاني لكنه يحتاج **طلب وصول توافق عليه Google** (الحصة صفر حتى الموافقة) |
| OWNER ACTION REQUIRED | تأكيد أن الملفين Verified، ومن المالك والمديرون (PO-009). إدخال نص العنوان AR/EN بنفسك من اللوحة (D-337). لاحقًا: الموافقة على طلب الـAPI وتسجيل دخول OAuth مرة واحدة |
| CREDENTIALS REQUIRED | OAuth بنطاق `business.manage` بحساب Owner أو Manager. الرمز يُخزن مشفرًا في الخادم |
| CURRENT STATUS | رابطا Maps للفرعين **معتمدان ومبنيان** (D-336). المحوّل (Adapter) `NOT STARTED` — PHASE 5 `BLOCKED` (PO-009) |
| RECOMMENDATION | **Master Data هو المصدر، وGBP نسخة منه.** نبدأ يدويًا (قائمة MANUAL ACTION في اللوحة)، ثم API بوضع «قراءة ومقارنة» قبل أي كتابة. الاسم والعنوان والدبوس (Pin) يدوية دائمًا |

### 13. Google Maps
| الحقل | التفاصيل |
|---|---|
| NAME | Google Maps |
| CATEGORY | Maps · Directions |
| STATUS | `REQUIRED NOW` (روابط مباشرة — مبنية) · الخريطة المضمّنة = `OPTIONAL` |
| WHY SHELTER NEEDS IT | ضغطة «الاتجاهات» أقرب مؤشر لزيارة الفرع |
| WHAT IT ENABLES | فتح تطبيق الخرائط على ملف الفرع في GBP مباشرة (`maps.app.goo.gl`)، و`hasMap` في الـSchema |
| WHAT IT CONNECTS TO | GBP · صفحات الفروع · حدث `directions_click` |
| DATA / PRIVACY IMPACT | **الرابط لا يحمّل أي سكربت من Google ولا يضع Cookies في موقعك.** الخريطة المضمّنة (iframe) تحمّل Cookies من Google، فتحتاج موافقة الزائر |
| COST / QUOTA | الروابط مجانية، **بلا API وبلا مفتاح**. Maps Embed API تذكر Google أنه بلا رسوم، لكنه يحتاج مشروع Google Cloud ومفتاحًا (شرط حساب الفوترة to confirm at setup). Places وMaps JavaScript API مدفوعة بالاستخدام وممنوعة بلا موافقة (D-043) |
| OWNER ACTION REQUIRED | لا شيء للروابط. للخريطة المضمّنة: قرارك + موافقة على المشروع والمفتاح |
| CREDENTIALS REQUIRED | لا للروابط. للتضمين: مفتاح مقيّد بـHTTP referrer (`https://www.shelterjo.com/*`) وبـMaps Embed API فقط |
| CURRENT STATUS | **مبني** (D-336): زر الاتجاهات في صفحة كل فرع وفي شريط الموبايل |
| RECOMMENDATION | الإبقاء على الروابط. إن أردت خريطة: «اضغط لعرض الخريطة» (تُحمّل بعد الضغط فقط) لحماية السرعة والخصوصية |

### 14. Google Tag Manager (GTM)
| الحقل | التفاصيل |
|---|---|
| NAME | Google Tag Manager |
| CATEGORY | Tag management |
| STATUS | `REQUIRED NOW` (يُفعّل مع بانر الموافقة) |
| WHY SHELTER NEEDS IT | **طبقة الوسوم الوحيدة** (D-175): كل وسم تتبع يمر منها موثقًا ومختبرًا، بدل سكربتات متفرقة في الكود |
| WHAT IT ENABLES | تشغيل GA4 (وأي Pixel تعتمده لاحقًا) حسب موافقة الزائر · اختبار Preview قبل النشر · سجل نسخ قابل للتراجع |
| WHAT IT CONNECTS TO | `dataLayer` في الموقع (`resources/js/ui/track.ts`) · GA4 · Consent Mode · (إن اعتُمدت) Google Ads وMeta وSnap |
| DATA / PRIVACY IMPACT | لا يجمع بيانات بنفسه. الوسوم داخله تجمع حسب فئتها |
| COST / QUOTA | مجاني |
| OWNER ACTION REQUIRED | الجرد لم يجد GTM في الموقع القديم. إنشاء الحاوية تحت حساب Google **للمنشأة**، ومنح صلاحية الإعداد. **كل نشر (Publish) لنسخة بموافقتك** |
| CREDENTIALS REQUIRED | Container ID (ليس سرًا) · صلاحية Edit للإعداد، وPublish بموافقتك · حساب الخدمة بصلاحية Read للفحص |
| CURRENT STATUS | **غير مثبت.** الـHooks جاهزة: تدفع الأحداث إلى `dataLayer` فقط إن وُجد. الـCSP الحالي يمنع أي سكربت خارجي. [`GTM-TAG-REGISTER`](google/GTM-TAG-REGISTER.md) فارغ |
| RECOMMENDATION | حاوية Web واحدة. الـStaging لا يرسل إلى بيانات Production. التسمية `GA4 - Event - …` |

### 15. Google Analytics 4 (GA4)
| الحقل | التفاصيل |
|---|---|
| NAME | Google Analytics 4 |
| CATEGORY | Analytics |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | مؤشراتك: الزوار، فتح المنيو، ضغطات الاتجاهات والاتصال وواتساب، البحث (D-212 = أولوية P0 في اللوحة) |
| WHAT IT ENABLES | تقارير الزيارات والأحداث · Key Events · أرقام اللوحة عبر GA4 Data API |
| WHAT IT CONNECTS TO | GTM · Search Console (ربط) · لوحة التحكم · (مستقبلًا) Google Ads |
| DATA / PRIVACY IMPACT | معرّف متصفح عشوائي (`_ga`) وصفحات وأحداث **بلا PII**. يُحمّل بعد موافقة الزائر فقط. Google signals مطفأ |
| COST / QUOTA | مجاني (GA4 القياسي) |
| OWNER ACTION REQUIRED | تأكيد الـProperty الحالي ومالكه: الموقع القديم يستخدم Google tag `GT-NNZXZLP5` عبر Site Kit (PO-012). إضافة حساب الخدمة Viewer، وصلاحية Editor مؤقتة للإعداد. اختيار Key Events (PO-036) |
| CREDENTIALS REQUIRED | Measurement ID (ليس سرًا) · حساب الخدمة (Viewer) · OAuth `analytics.readonly` للوحة |
| CURRENT STATUS | خطة القياس `DRAFT`. **8 أحداث جاهزة كـHooks**، ولا شيء يُرسل اليوم |
| RECOMMENDATION | **إعادة استخدام الـProperty القائم** (يحفظ التاريخ) بدل إنشاء جديد. مدة الاحتفاظ 14 شهرًا (الافتراضي في GA4 شهران). إطفاء ما يكرر أحداثنا في Enhanced Measurement: Outbound clicks · Site search · Form interactions |

### 16. Google Ads conversion
| الحقل | التفاصيل |
|---|---|
| NAME | وسم تحويلات Google Ads |
| CATEGORY | Advertising |
| STATUS | `FUTURE` |
| WHY SHELTER NEEDS IT | فقط عند وجود حملات Google Ads تحتاج قياس النتيجة |
| WHAT IT ENABLES | ربط نقرة الإعلان بضغطة الاتجاهات أو الاتصال |
| WHAT IT CONNECTS TO | GTM (فئة Marketing) · GA4 (استيراد Key Events) |
| DATA / PRIVACY IMPACT | Cookies إعلانية (`_gcl_au` · `_gcl_aw`). Marketing فقط، وبعد موافقة الزائر |
| COST / QUOTA | الوسم مجاني. الإنفاق الإعلاني قرار تجاري منفصل |
| OWNER ACTION REQUIRED | هل يوجد حساب Ads نشط وحملات تستخدم الموقع؟ الموقع القديم يحمّل `AW-454452815` على كل الصفحات (PO-012) |
| CREDENTIALS REQUIRED | Conversion ID/Label (ليست أسرارًا). ربط Ads ↔ GA4 بصلاحية Admin لديك |
| CURRENT STATUS | غير مبني. البنية جاهزة (GTM + Consent) |
| RECOMMENDATION | **قرار قبل الانتقال:** إن وُجدت حملة نشطة، يُعاد الوسم عبر GTM خلف موافقة Marketing يوم الانتقال. وإلا لا يُضاف |

### 17. Bing Webmaster Tools
| الحقل | التفاصيل |
|---|---|
| NAME | Bing Webmaster Tools |
| CATEGORY | SEO |
| STATUS | `OPTIONAL` |
| WHY SHELTER NEEDS IT | حصة صغيرة من البحث تأتي من Bing |
| WHAT IT ENABLES | إرسال الـSitemap وتقارير الفهرسة في Bing |
| WHAT IT CONNECTS TO | يمكنه استيراد الموقع من Search Console |
| DATA / PRIVACY IMPACT | لا بيانات زوار |
| COST / QUOTA | مجاني |
| OWNER ACTION REQUIRED | هل يوجد حساب؟ (ضمن PO-011) |
| CREDENTIALS REQUIRED | تسجيل دخول فقط |
| CURRENT STATUS | غير معروف |
| RECOMMENDATION | بعد الإطلاق، باستيراد من Search Console. أولوية منخفضة |

---

## ج. Meta والحسابات الاجتماعية

### 18. Meta Business Suite
| الحقل | التفاصيل |
|---|---|
| NAME | Meta Business Suite (Business portfolio) |
| CATEGORY | Social · Account ownership |
| STATUS | `RECOMMENDED` |
| WHY SHELTER NEEDS IT | صفحة Facebook وحساب Instagram والـPixel يجب أن تكون **ملكًا للمنشأة**، لا لحساب شخصي لموظف أو وكالة |
| WHAT IT ENABLES | إدارة الأدوار · ربط Instagram بـFacebook · إدارة الـPixel وتطبيق Meta (روابط الخصوصية وحذف البيانات) |
| WHAT IT CONNECTS TO | Instagram · Facebook · Meta Pixel · (لاحقًا) Conversions API |
| DATA / PRIVACY IMPACT | لا بيانات من الموقع بحد ذاته |
| COST / QUOTA | مجاني |
| OWNER ACTION REQUIRED | التأكد أنك Admin، وأن الـPixel `2425046304590717` (الموجود في الموقع القديم) وأي تطبيق Meta داخل حساب المنشأة. لا مشاركة كلمات مرور: دعوة شخص أو Partner بصلاحية على أصل محدد |
| CREDENTIALS REQUIRED | لا شيء للموقع. أدوار فقط |
| CURRENT STATUS | `PENDING OWNER INPUT` (PO-012، PO-026) |
| RECOMMENDATION | تدقيق الملكية **قبل الانتقال**: قد يعتمد تطبيق Meta على `/privacy/` و`/data-deletion/` في الموقع القديم |

### 19. Meta Pixel
| الحقل | التفاصيل |
|---|---|
| NAME | Meta Pixel |
| CATEGORY | Advertising |
| STATUS | `OPTIONAL` |
| WHY SHELTER NEEDS IT | فقط لقياس حملات Meta المدفوعة أو بناء جماهير إعادة الاستهداف |
| WHAT IT ENABLES | قياس نتائج إعلانات Instagram وFacebook |
| WHAT IT CONNECTS TO | GTM (فئة Marketing) · Meta Ads |
| DATA / PRIVACY IMPACT | Cookie `_fbp` (و`_fbc`)، وبيانات تصفح ترسل إلى Meta. ممنوع بلا موافقتك (PRIV-007)، وبلا موافقة الزائر، وقبل سياسة الخصوصية (PO-019) |
| COST / QUOTA | الوسم مجاني. الإنفاق الإعلاني منفصل |
| OWNER ACTION REQUIRED | هل توجد حملة أو جمهور فعلي يعتمد على الـPixel الحالي؟ (PO-012) |
| CREDENTIALS REQUIRED | Pixel ID (ليس سرًا) |
| CURRENT STATUS | غير مبني في الموقع الجديد. الموقع القديم يحمّله على كل الصفحات |
| RECOMMENDATION | لا يُضاف إلا بحاجة حملة حقيقية. **نفس الـPixel القائم، لا Pixel جديد.** عبر GTM خلف موافقة Marketing، وبلا Advanced Matching |

### 20. Meta Conversions API (CAPI)
| الحقل | التفاصيل |
|---|---|
| NAME | Meta Conversions API |
| CATEGORY | Advertising (server-side) |
| STATUS | `FUTURE` |
| WHY SHELTER NEEDS IT | تحسين دقة قياس حملات Meta عندما تحجب المتصفحات الـPixel |
| WHAT IT ENABLES | إرسال حدث من الخادم إلى Meta (مثل إرسال نموذج)، مع منع التكرار مع الـPixel بمعرّف حدث واحد |
| WHAT IT CONNECTS TO | Laravel (الخادم) · Meta Pixel |
| DATA / PRIVACY IMPACT | أقل حد ممكن: اسم الحدث فقط، وأي معرّف يُرسل مشفّرًا (Hashed). **فقط بعد موافقة Marketing** ومراجعة قانونية. لا بيانات التوظيف أو الشراكات أبدًا |
| COST / QUOTA | مجاني من Meta |
| OWNER ACTION REQUIRED | لا شيء الآن |
| CREDENTIALS REQUIRED | Access token لـMeta في `.env` على السيرفر فقط |
| CURRENT STATUS | `NOT STARTED` |
| RECOMMENDATION | لا قبل أن يثبت أن الـPixel نفسه لازم. الموقع لا يجمع بريدًا أو هاتفًا لأغراض تسويقية، فالفائدة محدودة اليوم |

### 21. روابط الحسابات الاجتماعية (Instagram · Facebook · Snapchat …)
| الحقل | التفاصيل |
|---|---|
| NAME | روابط الحسابات الرسمية |
| CATEGORY | Social · Brand identity |
| STATUS | `RECOMMENDED` |
| WHY SHELTER NEEDS IT | الروابط الرسمية في الـFooter وفي Schema (`sameAs`) تثبت لـGoogle أن هذه الحسابات للعلامة نفسها |
| WHAT IT ENABLES | أيقونات الحسابات في الـFooter، وربطها بالعلامة في البيانات المنظمة |
| WHAT IT CONNECTS TO | الكيان المركزي `SocialLink` ← الـFooter + Organization Schema |
| DATA / PRIVACY IMPACT | روابط عامة فقط. **لا سكربتات منصات ولا Embed**، فلا Cookies |
| COST / QUOTA | مجاني |
| OWNER ACTION REQUIRED | تأكيد كل حساب رسمي **واحدًا واحدًا** (PO-026). المرشحون في [`11-social-accounts-verification`](phase-01-discovery/11-social-accounts-verification.md) وكلهم `PENDING OWNER VERIFICATION`. ثم تضيفه وتفعّله من اللوحة ← التواصل (التفعيل = اعتماد) |
| CREDENTIALS REQUIRED | لا |
| CURRENT STATUS | **الكيان مبني:** 7 منصات (Instagram · Facebook · TikTok · Snapchat · YouTube · X · LinkedIn)، روابط `https` فقط، فحص نطاق كل منصة، والتفعيل مسجّل في Audit. **لا حساب مفعّل.** الروابط الرسمية `PENDING OWNER INPUT` |
| RECOMMENDATION | روابط فقط. Snapchat Place ليس حسابًا (D-036). لا Linktree. حدث `social_click` ما زال مرشحًا (PO-043) |

### 22. Snapchat Pixel
| الحقل | التفاصيل |
|---|---|
| NAME | Snap Pixel |
| CATEGORY | Advertising |
| STATUS | `OPTIONAL` |
| WHY SHELTER NEEDS IT | فقط إن خططت لإعلانات مدفوعة على Snapchat |
| WHAT IT ENABLES | قياس نتائج إعلانات Snapchat |
| WHAT IT CONNECTS TO | GTM (فئة Marketing) · Snap Ads |
| DATA / PRIVACY IMPACT | Cookie `_scid` وبيانات تصفح إلى Snap. Marketing بعد الموافقة فقط |
| COST / QUOTA | الوسم مجاني. الإنفاق منفصل |
| OWNER ACTION REQUIRED | قرار حملة. تأكيد حساب Snapchat الرسمي (PO-026) |
| CREDENTIALS REQUIRED | Pixel ID (ليس سرًا) |
| CURRENT STATUS | غير مبني. غير مذكور في جرد الموقع القديم (2026-10-01) |
| RECOMMENDATION | لا شيء حتى توجد حملة Snap فعلية |

---

## د. أفعال الزبون والخصوصية

### 23. WhatsApp
| الحقل | التفاصيل |
|---|---|
| NAME | WhatsApp |
| CATEGORY | Customer action (**ليس أداة تتبع**) |
| STATUS | `REQUIRED NOW` (مبني) |
| WHY SHELTER NEEDS IT | قناة تواصل رئيسية (الرقم معتمد — D-058) |
| WHAT IT ENABLES | رابط `wa.me` يفتح المحادثة مباشرة |
| WHAT IT CONNECTS TO | أرقام التواصل في Master Data · حدث `whatsapp_click` (بلا نص الرسالة) |
| DATA / PRIVACY IMPACT | الموقع لا يرسل شيئًا لواتساب غير فتح الرابط. المحادثة نفسها خارج الموقع |
| COST / QUOTA | مجاني. WhatsApp Business API غير مطلوب (ويحتاج موافقة — D-230) |
| OWNER ACTION REQUIRED | اعتماد نص الرسالة المسبقة AR/EN إن أردته (PO-020، D-064) |
| CREDENTIALS REQUIRED | لا |
| CURRENT STATUS | **مبني ومختبر** |
| RECOMMENDATION | يبقى رابطًا بسيطًا. لا أداة واتساب من طرف ثالث |

### 24. Consent management (إدارة الموافقة)
| الحقل | التفاصيل |
|---|---|
| NAME | بانر الموافقة على الكوكيز + Google Consent Mode v2 |
| CATEGORY | Privacy |
| STATUS | `REQUIRED NOW` — يُفعّل مع أول وسم غير ضروري |
| WHY SHELTER NEEDS IT | GA4 والـPixels تضع Cookies غير ضرورية، فلا تُحمّل قبل موافقة الزائر ([`PRIVACY-CENTER`](platform/PRIVACY-CENTER.md) §2) |
| WHAT IT ENABLES | ثلاث فئات: **Essential** (دائمًا) · **Analytics** · **Marketing**. Consent Mode v2 بقيم `denied` افتراضيًا. رابط «تفضيلات الكوكيز» في الـFooter |
| WHAT IT CONNECTS TO | GTM · GA4 · الـPixels (إن اعتُمدت) · سياسة الخصوصية |
| DATA / PRIVACY IMPACT | Cookie واحدة `shelter_consent`: الاختيار + نسخة السياسة + التاريخ، **بلا معرّف** |
| COST / QUOTA | بانر ذاتي بمكونات `x-ui`: مجاني. منصات الموافقة الجاهزة (CMP SaaS) باشتراك، وليست الخيار الافتراضي |
| OWNER ACTION REQUIRED | المراجعة القانونية: هل يلزم البانر، ولأي زوار، وبأي نص (PO-019). اعتماد نصوص البانر AR/EN |
| CREDENTIALS REQUIRED | لا |
| CURRENT STATUS | المواصفة جاهزة، والبانر **غير مبني**. موافقات النماذج مع نسخها **مبنية** (Dashboard ← نصوص الموافقة) |
| RECOMMENDATION | البانر يظهر **فقط** عند وجود وسم غير ضروري. «رفض» بنفس وزن «قبول». الوضع الأساسي: لا شيء يُحمّل من Google قبل الموافقة |

### 25. خصوصية نص البحث
| الحقل | التفاصيل |
|---|---|
| NAME | قاعدة: نص البحث لا يغادر الموقع |
| CATEGORY | Privacy rule |
| STATUS | `REQUIRED NOW` |
| WHY SHELTER NEEDS IT | الزائر قد يكتب في البحث رقم هاتف أو اسمًا |
| WHAT IT ENABLES | قياس البحث (عدد النتائج، لغة البحث، البحث بلا نتائج) **بلا نص البحث** |
| WHAT IT CONNECTS TO | `menu_search` · `site_search` · `zero_result_search` · سجل البحث الداخلي (مطفأ) |
| DATA / PRIVACY IMPACT | **لا نص بحث إلى GA4 أبدًا.** صفحة البحث العام `/ar/search/?q=` تحمل النص في الرابط، فيجب حذف `q` من `page_location` في GTM، وإطفاء Site search في Enhanced Measurement |
| COST / QUOTA | مجاني |
| OWNER ACTION REQUIRED | قرار PO-019: هل يُفعّل سجل البحث المجمّع داخل اللوحة (لتحسين المنيو)؟ |
| CREDENTIALS REQUIRED | لا |
| CURRENT STATUS | `menu_search` **يرسل عدد النتائج ولغة البحث فقط** (مبني). السجل الداخلي مطفأ حتى PO-019 |
| RECOMMENDATION | الكلمات التي لم تجد نتائج تظهر في اللوحة فقط، منظفة ومجمعة، بعد PO-019. لا في GA4 |

---

## ما قد لا تعرف أنك تحتاجه
أشياء ينساها أغلب أصحاب المواقع في أول مرة:

| # | البند | لماذا يهم | ما المطلوب | الحالة |
|---|---|---|---|---|
| 1 | **اختبار استعادة النسخة** | نسخة لم تُجرّب استعادتها قد تكون تالفة يوم الحاجة | استعادة كاملة على Staging قبل الإطلاق، ثم كل 6 أشهر | `NOT STARTED` (شرط PHASE 7) |
| 2 | **تجديد الدومين + قفله** | انتهاء الدومين = توقف الموقع والبريد معًا | في Namecheap: موعد التجديد، التجديد التلقائي، Registrar lock، و2FA | `PENDING OWNER INPUT` (PO-037) |
| 3 | **DNSSEC** | يمنع تزوير ردود DNS | سجل DS عند Namecheap بعد الإطلاق (D-313) | مؤجل بقرارك |
| 4 | **حماية البريد (SPF · DKIM · DMARC)** | يمنع انتحال `@shelterjo.com`، ويمنع ذهاب رسائلك إلى Spam | السجلات موجودة (D-312). المتبقي: «Start authentication» في Google Admin، ومراجعة تقارير DMARC، ثم قرار تشديد `p=none` لاحقًا | منفذ جزئيًا |
| 5 | **روابط تطبيق Meta** | تطبيق Meta قد يعتمد على `/privacy/` و`/terms/` و`/data-deletion/` في الموقع القديم. انكسارها قد يوقف التطبيق | التحقق من إعدادات التطبيق، ثم إبقاء الروابط أو تحويلها بـ301 قبل الانتقال | مسجّل في LEGACY-URL-MIGRATION |
| 6 | **ملكية Search Console** | التحقق الحالي بـMeta tag في الرئيسية القديمة **يختفي مع WordPress** | إبقاء سجل TXT في Cloudflare، وأن تكون أنت Owner في الـProperty | PO-011 |
| 7 | **ملكية Google Business Profile** | من يملك الملف يتحكم بظهورك في Maps | تأكيد أنك المالك الأساسي (Primary owner) للملفين، ومعرفة كل المديرين | PO-009 |
| 8 | **الوسوم القديمة تختفي يوم الانتقال** | الموقع القديم يحمّل GA4 (Site Kit) وGoogle Ads وMeta Pixel. حملة نشطة قد تفقد قياسها | قرار قبل الانتقال: ماذا يُعاد عبر GTM، وماذا يُترك | PO-012 |
| 9 | **مدير كلمات مرور + 2FA لكل حساب** | حساب واحد مخترق يكفي لخسارة الدومين أو GBP | مدير كلمات مرور موثوق (يوجد ما هو مجاني)، و2FA بتطبيق مصادقة على كل حساب | توصية |
| 10 | **من يملك كل حساب** | حساب باسم موظف سابق أو وكالة = خسارة الوصول | كل حساب باسم المنشأة، ومدير ثانٍ موثوق، وتسجيل ذلك في سجل الملكية ([`INTEGRATION-REGISTRY`](platform/INTEGRATION-REGISTRY.md) §5) | `PENDING OWNER INPUT` (PO-037) |
| 11 | **مفاتيح الطوارئ Offline** | بدون `APP_KEY` ومفاتيح الهوية لا تُقرأ البيانات المشفرة بعد الاستعادة | نسخة Offline لديك من المفاتيح ورموز استرداد اللوحة | توصية |
| 12 | **مدة احتفاظ GA4** | الافتراضي في GA4 شهران فقط للتقارير التفصيلية | ضبطها على 14 شهرًا عند الإعداد | عند خطوة GA4 |
| 13 | **انتهاء صلاحيات الـTokens** | Token منتهي = تكامل متوقف بصمت | تذكير قبل 14 يومًا من كل انتهاء (Integration Registry) | مصمم |
| 14 | **الـQR المطبوعة** | أي QR يشير لرابط قديم ينكسر بعد الانتقال | قائمة كل QR مطبوع لإدخالها في خريطة التحويل | PO-014 |
