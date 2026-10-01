# FINAL ARCHITECTURE REVIEW — Completeness Audit (M32 + M33 + M34 + M35)

> **المرجع:**
> - M32 Gap-Closure.
> - M33 Master Data + Channel Sync.
> - M34 Global Design System.
> - **M35 Final Completeness Gap Addendum** (`OWNER APPROVED · P0 ARCHITECTURE REVIEW REQUIRED`).
>
> **الحالة:** `DRAFT — AWAITING OWNER ARCHITECTURE APPROVAL`. **لا Coding** قبل الاعتماد (M35 §61).
>
> **حقيقة أساسية:** لا يوجد موقع ولا Dashboard مبنيان بعد. الموجود:
> - وثائق ومواصفات.
> - بيانات منيو مجمّدة.
> - Wireframes مختبرة (منيو، توظيف، شراكات، Dashboard).
> - أدوات جودة (`tooling/`).
> - Design tokens.
>
> **لذلك** "IMPLEMENTED" تعني هنا **مصمم وموثق ومختبر على النموذج**، وليس منفذًا في موقع.
>
> **الفلتر الحاكم (M35 §62):** كل نظام يجب أن يخدم واحدًا على الأقل من: تجربة العميل · تحكم الـOwner · اتساق البيانات · الظهور في البحث · الأداء · الأمان · الموثوقية · التوسع · قابلية الصيانة. **البساطة إلزامية.**

---

## 1–4. تدقيق الاكتمال النهائي: الموجود · الجزئي · الناقص فعلًا

**التصنيف:**
- **DESIGNED:** مُصمم وموثق، وجزء منه مختبر على النموذج.
- **PARTIAL:** مغطى جزئيًا في متطلبات أو وثائق قائمة.
- **MISSING:** غير مغطى.
- **NOT NEEDED:** خارج نطاق V1.

> لا شيء "IMPLEMENTED" بمعنى موقع يعمل.

| # | النظام (M35) | التصنيف | الموجود الآن (المرجع) | الناقص فعلًا |
|---|---|---|---|---|
| 1 | Environment strategy: Dev / Staging / Prod | PARTIAL | Staging-first (DEPLOY-*، `CLOUDWAYS-RECRUITMENT-ARCHITECTURE` CW-07). Staging محمي (SEO §5 لـ`franchise/05`) | **وثيقة موحدة:**<br>- فصل قواعد البيانات والإعدادات والأسرار.<br>- Staging: بلا فهرسة (مصادقة + `noindex`)، وبلا GA4 Production، ونماذجه لا تختلط بالإنتاج ← `ENVIRONMENTS.md` |
| 2 | Production deployment control (Release ID · التغييرات · الحالة · الاختبارات · مرجع التراجع) | MISSING | بوابات الإنتاج (D-002، DEPLOY) | **سجل الإصدارات** + صفحة "ماذا تغيّر في هذا الإصدار؟" ← `DEPLOYMENT.md` |
| 3 | Rollback system | PARTIAL | Rollback للمحتوى (Master Data §12، Versions) | **تراجع الكود والإعدادات + سياسة Migrations قابلة للعكس** ← `ROLLBACK.md` |
| 4 | Monitoring center | PARTIAL | **Health Engine** (M32: الروابط، الـSchema، الـSitemap، النسخ، الاعتماديات)، وRUM | Uptime، وصحة الـAPI، وأخطاء الخادم والواجهة، وفشل النماذج والرفع، والاتصال بقاعدة البيانات، وفشل المزامنة، وصحة التحليلات، وانتهاء الرموز، و**صحة المجدول** ← `MONITORING.md` |
| 5 | Incident center | PARTIAL ← **دمج** | "يحتاج انتباه" (DASH-017، M32 §39) | **دمج:** الحادثة = Issue بشدة CRITICAL/HIGH/MEDIUM/LOW/INFO **في نفس الجدول**. لا مركز ثانٍ ← `INCIDENTS.md` |
| 6 | Owner-friendly errors | DESIGNED | لغة صاحب العمل (DASH-020، DASH-034، M32 §41) | أمثلة أخطاء التكامل + زر "إصلاح الاتصال" + تفاصيل متقدمة (في `INCIDENTS.md`) |
| 7–8 | Unified notification center + الأولويات | MISSING ← **مع دمج** | إشعارات التوظيف داخل الـDashboard (CAREERS) | **مركز إشعارات واحد** (مصدر إشارات واحد مع الـIssues):<br>- الأولويات: Critical / Action Required / Important / Information.<br>- الإجراءات: Mark read / Resolve / Open source / Dismiss (للمعلومات فقط).<br>- **لا إخفاء لحادثة حرجة بلا سجل** ← `NOTIFICATIONS.md` |
| 9–10 | Unified inquiries center (INQ) | MISSING | صفحة تواصل حسب النية بالأرقام وواتساب (D-059) | **صندوق استفسارات:**<br>- الأنواع: تواصل، شكاوى، اقتراحات، Catering، B2B، فعاليات، عام.<br>- الحقول والحالات: NEW / OPEN / IN PROGRESS / RESOLVED / ARCHIVED.<br>- **التوظيف والشراكات مستقلان** ← `INQUIRIES.md` |
| 11–13 | Form reliability + الفشل + منع التكرار | PARTIAL | التوظيف: Idempotency key، والتحقق في الخادم، والحفاظ على المدخلات (`RECRUITMENT-SECURITY` §6) | **تعميم كطبقة واحدة لكل النماذج:** الحفظ ثم التأكيد ثم النجاح، وإعادة محاولة آمنة ← `FORM-RELIABILITY.md` |
| 14 | Reference numbers | PARTIAL | `JOB-YYYY-NNNNN` · `FR-YYYY-NNNNN` | **`INQ-YYYY-NNNNN`** + خدمة ترقيم مشتركة (Applications Core) |
| 15–16 | Legacy URL inventory + Redirect map | PARTIAL | `02-url-inventory-and-migration-seed` · `SEO-MIGRATION-MAP` · تحويلات `/menu` والفرنشايز والتوظيف | **الجرد الكامل يحتاج الوصول للموقع القديم وSearch Console** (PO-008، PO-011) ← `LEGACY-URL-MIGRATION.md` (قاعدة: أقرب بديل، **لا تحويل جماعي للرئيسية**، 410 نادرًا) |
| 17–20 | تجارب 404 / 500 / الصيانة / الحالات الفارغة | PARTIAL | الحالات الفارغة (DX-012، Wireframes) | **صفحات 404 و500 والصيانة بالـDesign System** (Wireframes في الدفعة التالية) |
| 21 | فشل الشبكة | PARTIAL | عزل الفشل (DX-037، Master Data §12) | قاعدة عامة: **الهيكل لا ينهار، والأجزاء الديناميكية تختفي برفق** (في `MONITORING.md`) |
| 22–25 | Cache / CDN + إبطال ذكي + حداثة البيانات + ربطها بالـHub | MISSING | — | **استراتيجية مصدر وإبطال** بوسوم لكل كيان ← `CACHE-CDN.md` (§13 أدناه) |
| 26–28 | Integration registry + الأسرار + سجل ملكية الخدمات | PARTIAL | سجل الأدوات (`FRONTEND-TOOLING`)، وقواعد الأسرار، وPO-037 (الملكية) | **سجل تكاملات واحد** (حالة، بيئة، آخر اتصال، انتهاء الرمز، النطاق، يحتاج إعادة ربط) + **سجل ملكية** ← `INTEGRATION-REGISTRY.md` |
| 29–30 | Disaster recovery + اختبار الاستعادة | PARTIAL | `CLOUDWAYS-RECRUITMENT-ARCHITECTURE` §6 · M32 Backup/Restore health | **خطة DR شاملة** (الكود، والقاعدة، والوسائط، والمرفقات الخاصة، والإعدادات) ← `DISASTER-RECOVERY.md` |
| 31–33 | التدويل · لا تثبيت للدولة · Country→City→Branch | PARTIAL | D-010، D-031 (`/ar/jo/`)، الفروع ككيانات، المنطقة الزمنية في التجارب | **Market configuration** (`JO` · ar/en · JOD · Asia/Amman · صيغ التاريخ والهاتف) ككيان ← `INTERNATIONALIZATION.md` |
| 34 | Master site inventory | PARTIAL | مسودة الروابط (`10`) · IA المنيو · التوظيف · الشراكات | **ملف جرد واحد لكل Route** ← `SITE-INVENTORY.md` |
| 35 | Content ownership map | PARTIAL | خريطة مصدر الحقيقة (`MASTER-DATA-HUB` §3) | **جدول لكل نوع محتوى** ← `CONTENT-SOURCE-OF-TRUTH.md` |
| 36 | Release checklist (DoD عام) | PARTIAL | DoD في: الاستجابة، والتوظيف، والشراكات، وM32 §55، وM27 | **قائمة واحدة** تُحال إليها كل الوثائق ← `RELEASE-CHECKLIST.md` |
| 37–38 | Browser QA + Device QA | PARTIAL | 20 عرضًا (Chromium) + AR/EN · axe | Firefox وWebKit وEdge في الـCI (I-07)، **وأجهزة حقيقية** (iPhone Safari، Android Chrome) |
| 39–41 | Data portability + أمان التصدير + قابلية نقل النظام | PARTIAL | تصدير التوظيف (مقنّع + Audit)، ومخططات بيانات محايدة | **خدمة تصدير موحدة** لكل الكيانات + مخططات موثقة ← `DATA-PORTABILITY.md` |
| 42–45 | Command center + لا Dashboard تجميلي + نموذج الصحة + طابور واحد | PARTIAL ← **تحسين** | DASH-017/025/026 · M32 §39 | **9 فئات صحة** (المحتوى، SEO، الأداء، الوصولية، الأمان، الخصوصية، اتساق البيانات، التكاملات، النسخ) بحالات Healthy / Needs attention / Critical. **بلا رقم إجمالي مزيف** |
| 46 | Audit trail موحد | DESIGNED | `audit_logs` مشترك | — (يضاف: Rollback، وإجراءات النشر، والمزامنة) |
| 47–48 | Owner global search + Command palette | PARTIAL | DASH-019 | **بحث واحد** يغطي: المنتجات، والصفحات، والفروع، والأحداث، والوسائط، والجوائز، والتوظيف، والشراكات، والاستفسارات، والإعدادات. **Command palette = P2** (البحث يكفي V1) |
| 49–50 | Scheduled job health + أتمتة زمنية موثوقة | PARTIAL | DX-013/023 (من الخادم، Asia/Amman) | **مراقبة المجدول:** آخر تشغيل، والقادم، والإخفاقات. **تجربة منتهية لا تبقى ظاهرة:** الحل يُحسب عند الطلب **أيضًا**، ولا يعتمد على المهمة وحدها |
| 51 | DB migration governance | PARTIAL | CW-06 (مستخدم Migrations منفصل) | Migrations بإصدارات، وقابلة للعكس، وBackup قبلها، وStaging أولًا (في `DEPLOYMENT.md`) |
| 52 | File storage governance | PARTIAL | `private_html` للتوظيف | **أربع مناطق:** وسائط عامة · مرفقات التوظيف الخاصة · مرفقات الشراكات الخاصة · صادرات النظام (مؤقتة، خاصة، تُحذف بعد التنزيل) |
| 53 | Data classification | MISSING | ضمني (الهوية حساسة…) | **PUBLIC / INTERNAL / CONFIDENTIAL / SENSITIVE** مرتبط بقواعد الوصول والتخزين والتصدير ← `DATA-CLASSIFICATION.md` |
| 54–55 | Launch readiness + Post-launch verification | PARTIAL | `POST-LAUNCH-GOOGLE-CHECKLIST` · خطة هجرة SEO | **قائمة إطلاق شاملة + تحقق T+1h / T+24h / T+7d** ← `LAUNCH-READINESS.md` · `POST-LAUNCH-VERIFICATION.md` |

**تصنيف M32 وM33 وM34:**
- **M32** (الأنظمة الـ45): مفصّل في [`GAP-CLOSURE-PLAN.md`](GAP-CLOSURE-PLAN.md) §A.
- **M33:** [`MASTER-DATA-HUB.md`](MASTER-DATA-HUB.md) (DESIGNED).
- **M34:** [`DESIGN-SYSTEM-STANDARD.md`](DESIGN-SYSTEM-STANDARD.md) (tokens بنيوية مقررة، والقيم البصرية MISSING، والتدقيق يعمل).

## 5. الأنظمة المكررة التي وُجدت وكيف دُمجت (مصدر واحد)
| المكرر المحتمل | القرار |
|---|---|
| Incident Center (M35) · Needs Attention (M32/M25) · Health Engine issues · Monitoring alerts | **طابور واحد:** جدول `issues` واحد. الحادثة = Issue بشدة أعلى. المراقبة مصدر للـIssues، وليست مركزًا ثانيًا |
| Notification Center (M35) · إشعارات كل وحدة (التوظيف، الشراكات…) | **مصدر إشارات واحد** (`signals`) بعرضين: **الإشعارات** (أحداث: طلب جديد، حملة تنتهي) و**يحتاج انتباه** (مشاكل مفتوحة). **لا إشعارات داخل الوحدات** إلا شارة العدد |
| Global Data Registry (M32) · Master Data Hub (M33) | **نظام واحد** = Master Data Hub. السجل العام جزء منه |
| Global Content Calendar (M32) · جدولة التجارب (M31) | **عرض واحد** لكل ما له جدولة |
| Integration Registry (M35) · حالة مزامنة القنوات (M33) · سجل الأدوات | **سجل تكاملات واحد** (حالة الربط) + **حالة المزامنة لكل كيان** (من نفس السجلات) |
| Global Owner Search (M35) · Dashboard global search (M25) | **نفس البحث** (DASH-019) |
| Release checklist (M35) · DoD في 6 وثائق | **`RELEASE-CHECKLIST.md` المرجع الوحيد.** البقية تُحيل إليه |
| تصدير التوظيف · Data portability | **خدمة تصدير واحدة** بقواعد التصنيف |
| Safe Mode (M32) · Maintenance (M35) · Emergency disable (M31) | **Feature flags واحدة.** Safe Mode تعطل الطبقة الديناميكية، والصيانة **آخر حل** للموقع كاملًا |
| Wireframe CSS لكل وحدة | **طقم واحد** `design-system/wireframe-kit.css` + tokens (الترحيل جارٍ) |

## 6. تعارضات معمارية
| # | التعارض | الحسم |
|---|---|---|
| AC-1 | **GBP:** "مصدر تشغيلي رسمي" (سياسة Google M11) مقابل "Master Data هو المصدر" (M33) | **M33 يفوز** (أحدث وصريح). GBP قناة تُزامن، والتغييرات الخارجية تُراجع (`MASTER-DATA-HUB` §10). **لا يكسر** قاعدة "تعديل GBP بموافقة": **نشر الـOwner في الـDashboard مع ظهور GBP في معاينة الأثر = موافقته** |
| AC-2 | "لا PII" في المراقبة (M25) مقابل جمع RUM والأخطاء والاستفسارات | المراقبة والـRUM: **مجمعة بلا معرّف وبلا IP مخزن**. الاستفسارات تحتوي PII **بطبيعتها**: تصنيف `CONFIDENTIAL`، Owner فقط، تصدير مقنّع |
| AC-3 | ترتيب المراحل (التصميم قبل المنصة، M01) مقابل حاجة المراقبة والـCI والـStaging لمنصة | **التسلسل يبقى:** التصميم والوثائق الآن، والبنية التحتية بعد DB-08. **التوصية لحسم DB-08 مبكرًا قائمة** (PO-044) |
| AC-4 | "Staging لا يرسل Analytics Production" مقابل اختبار التتبع | Staging بـGA4 **Property أو Data stream اختباري**، أو بلا إرسال + DebugView. **لا خلط** |
| AC-5 | الصيانة الكاملة مقابل Safe Mode | Safe Mode أولًا دائمًا. الصيانة الكاملة **فقط** لترحيل أو حادثة أمنية، **بموافقة الـOwner** |
| AC-6 | استفسارات "شكاوى" كنموذج مقابل الرقم المخصص 0799338445 | **لا تعارض:** قناتان. الرقم يبقى ظاهرًا، والنموذج يضيف تتبعًا برقم `INQ`. الرقم لا يظهر في بطاقات الفروع (قرار سابق) |

## 7–9. الأولويات (بعد كل الإضافات)
| P0 — أساس لا يُطلق الموقع بدونه | P1 — قبل الإطلاق أو بعده مباشرة | P2 — مستقبلي |
|---|---|---|
| Platform Core (Content Graph · Versions · Audit · Usage · Flags)<br>Master Data Hub + Fact layer + Change Impact + Publish Guard<br>**Cache/CDN + إبطال ذكي + حداثة البيانات**<br>Environments + Deployment control + **Rollback** + DB migration governance<br>Monitoring + **Issues queue واحد** + Scheduler health + Owner-friendly errors<br>**Form reliability** + Reference numbers + **Inquiries center**<br>Security Center + مصادقة قوية + Secrets + Integration registry<br>Privacy Center + **Data classification** + Storage governance<br>Backups + **Restore test** + Disaster recovery<br>Design System tokens + المكونات الأساسية + Visual regression للمكونات<br>CI/CD quality gate + Browser QA (Chromium/Firefox/WebKit)<br>Legacy URL inventory + Redirect map + 404/500<br>Launch readiness + Post-launch verification<br>Global Calendar + Collision + Safe Mode<br>RUM + Language parity | Notifications center (فوق نفس الإشارات)<br>Reputation (بعد صلاحيات GBP) + Review reply flow<br>Voice of Customer<br>Content freshness<br>Press kit + Media rights<br>Public global search + Search intelligence + Content opportunities<br>Accessibility center · Schema/Sitemap/Indexation health<br>Data portability (تصدير كل الكيانات)<br>Owner global search الشامل<br>Internationalization (إعداد السوق، دون سوق ثانٍ) | PWA / Offline (**NOT RECOMMENDED** الآن)<br>Experimentation (A/B)<br>AI Owner Copilot<br>Command palette<br>مزامنة POS/ERP/Delivery<br>Food menus وPosts على Google |

## 10. IA الـDashboard بعد كل الإضافات (7 مجموعات)
| المجموعة | البنود | ما أُضيف بعد M35 |
|---|---|---|
| **الرئيسية = SHELTER DIGITAL COMMAND CENTER** | **الحالة:** الموقع ONLINE · Master Data SYNCED · Google SYNCED · المنيو SYNCED · النسخ HEALTHY · الأداء GOOD · الـSEO · الأمان<br>**الحملة النشطة**<br>**الأعداد الجديدة:** طلبات توظيف · شراكات · استفسارات غير محلولة · يحتاج انتباه | بطاقات تجيب فقط عن: ماذا حدث؟ هل أحتاج أن أتصرف؟ أين أضغط؟ |
| **المحتوى** | الصفحات · المنيو · المعرفة · الفعاليات · الجوائز · مركز الوسائط | الجوائز داخل الوسائط والمحتوى |
| **التجارب** | النشط الآن · الحملات · المواسم · التقويم · SHELTER Family | |
| **الأعمال** | **الاستفسارات (INQ)** · التوظيف · الشراكات · آراء العملاء · السمعة | الاستفسارات |
| **النمو** | التحليلات · البحث · الـSEO · فرص المحتوى | |
| **الجودة** | **صحة الموقع** (المراقبة، الحوادث، المجدول، الروابط) · الأداء · الوصولية · الأمان · الخصوصية · النسخ والاستعادة | المراقبة والمجدول والاستعادة داخل "صحة الموقع" |
| **النظام** | البيانات العامة (Master Data + الاتساق) · الحقائق · **التكاملات** · **الإصدارات** · سجل التدقيق · الإعدادات (السوق، الأعلام، وضع الأمان) | التكاملات والإصدارات |

**عناصر خارج القائمة الجانبية:**
- 🔔 **الإشعارات** في الشريط العلوي (مركز واحد).
- **البحث الشامل.**
- ⚠️ **وضع الأمان.**

> الموبايل: الشريط السفلي كما في `GAP-CLOSURE-PLAN` §C.

## 11. خريطة معمارية البيانات
```
                                   SHELTER OWNER
                                         │
                                OWNER COMMAND CENTER  ◄──── signals (Notifications) + issues (Needs Attention)
                                         │
            ┌────────────────────────────┼─────────────────────────────┐
            ▼                            ▼                             ▼
   MASTER DATA HUB               CONTENT GRAPH                     OPERATIONS
   Brand · Market(JO)            Pages/Sections · Articles ·       Inquiries (INQ) · Careers (JOB) ·
   Country→City→Branch           FAQ · Events · Experiences ·      Partnerships (FR) · Feedback ·
   Hours · Contact · Social      Awards · Public Employees         Reviews  ← Applications Core
   Menu (PRD/CAT/DSC) ·          Media Center (+rights)             (numbering · notes · status ·
   branch overrides · Facts                                          meetings · private files)
            └───────────── Versions · Audit · Usage graph · Feature flags · Data classification ─────────────┘
```

**تصنيف كل حقل** (PUBLIC / INTERNAL / CONFIDENTIAL / SENSITIVE) يحدد:
- من يقرأ.
- أين يُخزن (عام / خاص / مشفّر).
- كيف يُصدّر (عادي / مقنّع / بتأكيد).
- ماذا يُرسل للتحليلات (PUBLIC فقط ومجمّعًا).

## 12. خريطة معمارية التكاملات
```
Master Data / Content ──► Channel Adapters (مستقلة، لكل منها طابور مزامنة وحالة):
   ├─ Website (توليد + إبطال Cache بالوسوم)
   ├─ Schema (مشتق، بلا إدخال مستقل)
   ├─ Google Business Profile (Business Information API: الهاتف، الساعات، الخاصة، الإغلاق المؤقت، الرابط · الاسم/العنوان يدوي)
   ├─ Search Console API (قراءة) · GA4 Data API (قراءة) · CrUX (قراءة)
   ├─ GBP Reviews (قراءة + رد بموافقة) — بعد طلب الوصول
   └─ مستقبلي: POS · ERP · App · Delivery (external_references بمعرّفات ثابتة)
Integration Registry: الحالة · البيئة · آخر اتصال ناجح · انتهاء الرمز · النطاق · يحتاج إعادة ربط  (بلا أسرار في الواجهة)
Secrets: على الخادم فقط، مشفرة، لكل بيئة أسرارها، لا شيء في الحزمة أو Git أو الـCMS
```

## 13. معمارية النشر والـCache
| البند | التصميم |
|---|---|
| **البيئات** | **Development** (محلي/Preview، بيانات اصطناعية) ← **Staging** (Cloudways App مستقل، محمي بمصادقة + `noindex`، قاعدة وأسرار منفصلة، تحليلات اختبارية، بريد/نماذج لا تصل للإنتاج) ← **Production** |
| **الإصدار** | سجل إصدارات: رقم الإصدار · التاريخ · قائمة التغييرات (من الـCommits والـPR) · نتيجة الـCI · حالة النشر · **مرجع الإصدار السابق المستقر**. صفحة "ماذا تغيّر؟" بلغة صاحب العمل |
| **البوابة** | CODE → BUILD → TESTS → PLAYWRIGHT (3 محركات) → A11Y → SEO/Schema/Links → RTL/LTR + 20 عرضًا → VISUAL REGRESSION → PERFORMANCE → STAGING → **موافقة الـOwner** → PRODUCTION. **P0 = إيقاف النشر** |
| **التراجع** | الكود: إعادة نشر الإصدار المستقر السابق (Artifact محفوظ). الإعدادات: بإصدارات. الـMigrations: **Expand → Migrate → Contract** (لا حذف أعمدة في نفس الإصدار) فيكون التراجع آمنًا. المحتوى: Versions |
| **Cache / CDN** | **المصدر الوحيد** = Master Data وContent Graph.<br>**الطبقات:**<br>- Cloudflare: HTML بزمن قصير + وسوم.<br>- الصور: طويل وغير قابل للتغيير، مع اسم ملف بالـHash.<br>- الـJS/CSS: Hash.<br>- الـAPI: قصير.<br>**إبطال ذكي بالوسوم** (Cache tags) لكل كيان: تغيير سعر الكابتشينو يبطل وسم `prd:PRD-00058` و`menu:ar`/`menu:en` و`schema:menu` فقط، **بلا Purge شامل**.<br>**البيانات الحساسة للوقت** (السعر، التوفر، الساعات، الإغلاق، الحملات): زمن تخزين قصير + إبطال فوري عند النشر + **حساب الحالة الزمنية من الخادم** ("مفتوح الآن"، انتهاء الحملة).<br>**قدرة Cloudflare** على الإبطال بالوسوم تُتحقق من الخطة (PO-013) وإلا إبطال بالروابط |

## 14. معمارية المراقبة
| المراقب | المصدر | يصبح Issue/إشارة عند |
|---|---|---|
| Uptime | مراقب خارجي بموافقة (I-12) أو Cron من خارج السيرفر | توقف ← **CRITICAL** |
| صحة الـAPI وقاعدة البيانات | Endpoint صحة داخلي | فشل ← CRITICAL / HIGH |
| أخطاء الخادم والواجهة | سجل أخطاء ذاتي مجمّع بلا PII (Sentry فقط بموافقة) | ارتفاع غير عادي ← HIGH |
| النماذج والرفع | طبقة موثوقية النماذج | فشل حفظ ← HIGH |
| المزامنة الخارجية | طوابير المزامنة | FAILED / OUT OF SYNC ← HIGH / MEDIUM |
| التحليلات | عدّاد أحداث يومي (GA4 Data API) | توقف التتبع ← HIGH |
| الرموز | Integration registry | قرب الانتهاء ← MEDIUM، وانتهى ← HIGH |
| المجدول | سجل تشغيل كل مهمة | تأخر أو فشل ← HIGH (تجربة يجب أن تنتهي) |
| النسخ والاستعادة | سجل النسخ واختبار الاستعادة | فشل ← CRITICAL · **بلا اختبار استعادة ← لا يُعرض "سليم بالكامل"** |
| الأداء | RUM p75 | تراجع فعلي ← MEDIUM (مع الدليل، **بلا تخمين سبب**) |
| الوسائط والروابط | Health Engine | مكسور ← MEDIUM / LOW |

**نموذج الحادثة:** الشدة · ماذا حدث (بلغة صاحب العمل) · متى · المنطقة المتأثرة · الحالة · الإجراء الموصى به · تفاصيل متقدمة · محلول/غير محلول · سجل.

## 15. فجوات الأمان والخصوصية المتبقية
| # | الفجوة | الإغلاق |
|---|---|---|
| S-1 | مصادقة الـOwner غير مصممة نهائيًا | **Passkeys (WebAuthn) أساسًا + TOTP احتياطي + رموز استرداد**، وجلسات آمنة، وانتهاء خمول، وRate limit، و**إعادة تأكيد** للإجراءات الحساسة (الهوية، والتصدير، والحذف النهائي، والتراجع، وفصل التكاملات) |
| S-2 | تصنيف البيانات غير مطبق | `DATA-CLASSIFICATION.md` مربوط بالتخزين والتصدير والتحليلات |
| S-3 | الأسرار لكل بيئة | خزنة أسرار الخادم لكل بيئة، وتدوير، وسجل في Integration registry بلا القيم |
| S-4 | الإطار القانوني (الخصوصية، RUM، موافقة الموظفين، بيانات الهوية) | PO-019: مراجعة قانونية قبل الإطلاق (قانون حماية البيانات الشخصية الأردني) |
| S-5 | ملكية الخدمات (الدومين، DNS، Cloudflare، Cloudways، Google) | سجل ملكية (PO-037)، **بلا أسرار شخصية** |
| S-6 | فحص الفيروسات للمرفقات | يُتحقق في الـCloudways Audit، وإلا منع صارم + لا تنفيذ |

## 16. مخاطر الإطلاق
| # | الخطر | التخفيف |
|---|---|---|
| L-1 | **الوصول محجوب** (الموقع القديم، GSC، GBP، Cloudflare، Cloudways) | بلا الوصول لا جرد روابط كامل، ولا Baseline، ولا مزامنة ← **أهم مانع تشغيلي** (PO-008…013، G10-PO-01) |
| L-2 | **الهوية البصرية مفقودة** (M-10) | لا تصميم نهائي ← لا إطلاق بصري |
| L-3 | **قرار المنصة (DB-08)** | لا بناء |
| L-4 | فقدان ترتيب البحث عند الهجرة | Baseline + خريطة تحويل دقيقة + تحقق T+1h/24h/7d |
| L-5 | بيانات قديمة بسبب الـCache | إبطال بالوسوم + حساب الوقت في الخادم + فحص الحداثة |
| L-6 | تعقيد زائد لمالك واحد | فلتر §62 · طابور واحد · إشعارات بأولوية |
| L-7 | محتوى ناقص (152 اسمًا عربيًا، الصور، الحقائق القانونية) | الإطلاق بما هو معتمد فقط + Fallbacks معتمدة |
| L-8 | النسخ غير مختبرة | اختبار استعادة قبل الإطلاق (بوابة) |

## 17. خطة التنفيذ المرحلية النهائية (تحل محل الترتيب الجزئي في الملاحق)
| المرحلة | المحتوى | البوابة |
|---|---|---|
| **P00** (الآن) | Master Requirements + السجلات + هذه المراجعة + الوثائق التفصيلية (M32 §51 ×19 · M35 §56 ×20) + توحيد الـWireframes على الـTokens | ✅ **اعتمادك لهذه المراجعة** |
| **P01** | **الوصول:** AC-01، وGSC، وGBP، وGA4/GTM، وCloudflare، و**Cloudways audit** + ملكية الخدمات + جرد الروابط القديمة | الصلاحيات منك |
| **P02** | اعتماد Menu IA (جاهزة) | اعتمادك |
| **P03** | IA الموقع كاملًا + Site Inventory + خريطة التحويل + Wireframes الصفحات (Home، Locations، Branch، About، Contact + INQ، Media، Awards، 404/500) | اعتمادك |
| **P04** | معمارية الـDashboard كاملة + Wireframes كل الوحدات (جارية) + Platform Core + Master Data + الأمان والخصوصية + Cache + Monitoring (وثائق) | **Gate H** |
| **P05** | ملفات الهوية ← قيم الـTokens + المكونات + التصميم المرئي | ملفات الهوية + اعتمادك |
| **P06** | قرار المنصة (DB-08) + البيئات + الـCI (بموافقة دقائق GitHub) | اعتمادك (أو مبكرًا حسب PO-044) |
| **P07** | المحتوى والوسائط والترجمات والحقائق | مستمر |
| **P08** | **البناء على Staging بالترتيب:**<br>1. Core.<br>2. Master Data + Cache.<br>3. الموقع العام.<br>4. النماذج (INQ/JOB/FR).<br>5. الـDashboard.<br>6. المراقبة والأمان. | لكل إصدار على Staging |
| **P09** | Google: جرد الموجود ← GA4/GTM/Consent ← GSC ← GBP sync | الخطوات الحساسة بموافقتك |
| **P10** | بوابة الجودة الكاملة + **اختبار الاستعادة** + Launch readiness | اعتمادك |
| **P11** | الإطلاق + التحويلات + التحقق T+1h / T+24h / T+7d | **إلزامي** |
| **P12** | المراقبة والتحسين + P1 المتبقية + P2 | حسب البند |
