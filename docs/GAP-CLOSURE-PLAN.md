# GAP-CLOSURE PLAN — Platform Quality & Operations (M32)

> **المرجع:** "MASTER GAP-CLOSURE ADDENDUM" (M32، 2026-10-01) — `OWNER APPROVED`. **ملحق لا يستبدل** ما سبق.
> **الحالة:** `DRAFT — PENDING OWNER REVIEW` · **لا Production** قبل بوابة الإنتاج. البناء يسير بمراحل M36 (PLANNING CLOSED)، ومراجعة معمارية الـDashboard غير مانعة. قرار المنصة (DB-08) **حُسم** بـ[`ADR-001`](adr/ADR-001-platform.md).
> **تحديث الحالة (2026-10-01):**
> - **وثائق M32 §51 الـ19 موجودة الآن في [`docs/platform/`](platform/)** (الجدول §E). هذه الخطة تبقى الخريطة، والتفاصيل الملزمة في تلك الوثائق.
> - **أسماء الجداول** في §B و§D حُدّثت إلى الأسماء الملزمة في [`PLATFORM-ARCHITECTURE`](architecture/PLATFORM-ARCHITECTURE.md) §3.
> - **معرض المكونات = Storybook** (M37، `@storybook/html-vite`، **القصص تُولّد من مكونات Blade نفسها**). يحل محل أي معرض مقترح سابقًا (ADR-001، [`TOOLCHAIN`](TOOLCHAIN.md)).
>
> **الفلتر الحاكم (M32 §57):** كل نظام هنا يجب أن يحسّن بشكل ملموس واحدًا على الأقل من:
> - تجربة العميل.
> - تحكم الـOwner.
> - الظهور في البحث.
> - الأداء.
> - الثقة.
> - الأمان.
> - جودة البيانات.
>
> **وإلا لا يُضاف.**

**أقسام الوثيقة:**
- **A.** Gap Audit — §A (مُولّد من التدقيق، في نهاية الملف).
- **B.** خريطة المعمارية.
- **C.** تحديث IA الـDashboard.
- **D.** أثر نموذج البيانات.
- **E.** خطة P0.
- **F.** خطة P1.
- **G.** خطة P2.
- **H.** المخاطر.
- **I.** التكلفة والـAPIs.
- **J.** بوابات الاعتماد.

---

## B. خريطة المعمارية: نواة واحدة، ووحدات تستخدمها (لا جزر بيانات)

```
                         ┌──────────────── SHELTER PLATFORM CORE (مصدر حقيقة واحد) ────────────────┐
                         │  Content Graph: كل كيان (منتج، فئة، فرع، صفحة/قسم، مقال، FAQ، حدث، تجربة،   │
                         │  موظف علني، وسيط، إعداد عام، حقيقة) + لغات AR/EN + حالة النشر + الجدولة     │
                         │  ├─ Versions (كل حفظ)        ├─ Audit Log (كل إجراء)                        │
                         │  ├─ Usage Graph (من يستخدم من: صفحة→هاتف، مكوّن→صورة، Schema→عنوان)        │
                         │  └─ Feature Flags (Safe Mode، الإيقاف الطارئ، التجارب المستقبلية)          │
                         └───────────────▲──────────────────▲──────────────────▲───────────────────────┘
                                         │                  │                  │
   ┌─────────────────────────┐   ┌───────┴────────┐  ┌──────┴───────┐  ┌───────┴──────────────────┐
   │ Global Data Registry    │──►│ Publish Pipeline│  │ Schedule View│  │ Health Engine (واحد)      │
   │ + Fact Registry (حالة   │   │ Publish Guard → │  │ Global       │  │ فحوص دورية تكتب "Issues"  │
   │  التحقق لكل حقيقة)      │   │ Change Impact → │  │ Calendar +   │  │ في جدول واحد:             │
   └─────────────────────────┘   │ Publish/Schedule│  │ Collision    │  │ Freshness · Parity · Links│
                                 └─────────────────┘  │ (محرك أولوية │  │ · Schema · Sitemap ·      │
                                                      │  DX نفسه)    │  │ Indexation · A11y · Perf  │
   Telemetry (أول طرف، بلا PII):                      └──────────────┘  │ · Security · Backup ·     │
   RUM Web Vitals · Search log · Feedback (VoC) ───────────────────────►│ Dependencies · Media      │
   Google (APIs رسمية): GA4 · Search Console · GBP Reviews · CrUX ─────►└────────────┬──────────────┘
                                                                                    ▼
                                         OWNER COMMAND CENTER — "يحتاج انتباه" (كل بند يفتح مكانه)
```

**قواعد الربط (M32 §47، §48):**

| ما طُلب | الربط بالنواة | **ليس** |
|---|---|---|
| Global Data Registry | **إعدادات مركزية** (العلامة، التواصل، السوشال، القانوني، بيانات الموقع العام) + الفروع ككيانات. **المكونات تقرأ بالمفتاح، ولا نص مكتوب في الكود** | ليس نسخة ثانية من بيانات الفروع |
| Fact Registry | **طبقة تحقق** فوق القيم. كل قيمة حساسة ترتبط بـ`fact_id` يحمل المصدر والحالة والمراجعة.<br>**يرث ويحل محل** سجل اعتماد المحتوى اليدوي (`04-content-approval-register.md`) | ليس مصدرًا ثانيًا للحقائق |
| Change Impact Preview | يقرأ **Usage Graph** ليعرض "هذا التغيير يؤثر على: الـFooter، التواصل، DRIVE…" | |
| Publish Guard | **مجموعة فحوص** في خط النشر. **تستخدم نفس فحوص** Health Engine | ليس نظام فحص ثانيًا |
| Global Content Calendar | **عرض واحد** لكل كيان له جدولة: الأحداث، الحملات، المواسم، الموظف المثالي، المقالات المجدولة، الساعات الخاصة، الذكرى 20/04 | ليس تقويمًا ثانيًا ولا جدولة ثانية |
| Collision Detector + Priority | **نفس محرك الأولوية** في Dynamic Experience Engine (DX-021). يُشغّل على نوافذ زمنية للتحذير مسبقًا | ليس محركًا ثانيًا |
| Content Freshness · Parity · Broken Links · Schema · Sitemap · Indexation · Accessibility · Performance Alerts · Security · Backup · Dependencies | **Health Engine واحد** وجدول `signals` واحد (`kind` = ISSUE). كل مجال "فحص" بإعداداته | ليست مراكز صحة متعددة |
| Owner Command Center | يعرض `signals` المفتوحة (`kind` = ISSUE) + "النشط الآن" + ما يبدأ أو ينتهي قريبًا (يمتد من DASH-017) | |
| RUM | جامع أول طرف خفيف (Web Vitals) **لقياس الأداء فقط**. التحليل السلوكي يبقى في GA4 (قاعدة "لا نظام تحليلات ثانٍ") | ليس بديلًا عن GA4 |
| Public Global Search | **فهرس بحث واحد** مبني من Content Graph. بحث المنيو = نفس الفهرس مقيّدًا بالمنتجات | ليس محرك بحث ثانيًا |
| Search Intelligence | سجل استعلامات مجمع منظّف (بلا هوية) من نفس البحث. يغذي Content Opportunities مع Search Console | |
| Media Rights + Press Kit | **حقول إضافية** في Media Center الواحد. Press Kit = **مجموعة منتقاة** من Media Center + Fact Registry | ليس مكتبة ثانية |
| Safe Mode + Emergency | **Feature Flags** في النواة. تعطيل طبقة التجارب والتكاملات الاختيارية، **والنواة تعمل** | |
| Security Center + Privacy Center | **شاشات قراءة** فوق: سجلات المصادقة، فحوص الـHealth Engine، نسخ الموافقات، أعمار السجلات | |
| CI/CD + Visual Regression | **خارج الموقع:** `tooling/` الموجودة (Playwright، axe، Lighthouse، Sharp) + **Storybook** (M37، `@storybook/html-vite` مغذّى من مكونات Blade) لمرجع المكونات + Pipeline في GitHub Actions | لا أدوات مكررة |

## C. تحديث IA الـDashboard (7 مجموعات، لا 40 بندًا)
| المجموعة | البنود | ملاحظات |
|---|---|---|
| **الرئيسية** | مركز القيادة: يحتاج انتباه · النشط الآن · ملخص الأداء والزيارات | أول شاشة. **كل بند يفتح مكانه** |
| **المحتوى** | الصفحات · المنيو · المعرفة (المدونة) · الفعاليات · مركز الوسائط (Press Kit والحقوق داخله) | |
| **التجارب** | النشط الآن · الحملات والعروض · المواسم · **التقويم** · SHELTER Family والموظف المثالي | |
| **الأعمال** | التوظيف · الشراكات · آراء العملاء (VoC) · السمعة | |
| **النمو** | التحليلات · البحث داخل الموقع · الـSEO (الفهرسة، Schema، Sitemap داخله) · فرص المحتوى | |
| **الجودة** | صحة الموقع (الروابط، النسخ الاحتياطي، الاعتماديات) · الأداء الفعلي · الوصولية · الأمان · الخصوصية | |
| **النظام** | البيانات العامة · سجل الحقائق · سجل التدقيق · الإعدادات | |

**عناصر خارج القائمة الجانبية:**
- **زر ⚠️ "وضع الأمان"** في الشريط العلوي. ظاهر دائمًا، ويعمل بتأكيد واحد.
- **بحث عام** داخل الـDashboard (DASH-019).
- **تذكير بتسجيل الدخول الآمن.**

**الموبايل:**
- شريط سفلي بـ5 عناصر: الرئيسية · المحتوى · التجارب · الأعمال · المزيد.
- **كل المهام اليومية ممكنة** على الهاتف (M26).

**مثال لغة صاحب العمل:** "صورة الصفحة الرئيسية كبيرة وتبطئ التحميل". التفاصيل التقنية مثل "Hero LCP resource 2.8MB" تحت "تفاصيل متقدمة".

## D. أثر نموذج البيانات
**يُعاد استخدامه (لا تكرار):**

| الكيان الموجود (مصمم) | يُعاد استخدامه لـ |
|---|---|
| `audit_logs` (`RECRUITMENT-DATA-MODEL.md` §2.14) | كل الوحدات: النشر، تجاوز تحذيرات النشر، الوضع الآمن، كشف البيانات |
| Versions (`experience_versions`، نسخ المنيو) | **تعميمه** إلى `content_versions` لكل الكيانات |
| `consent_versions` (التوظيف) | **تعميمه** لكل الموافقات: التوظيف، الشراكات، الكوكيز، آراء العملاء، نشر الموظفين |
| `user_preferences` · `saved_filters` | كل القوائم |
| Media assets (`media/manifest.json` ← جدول `media` + `media_usages`) | + حقول الحقوق |
| Applications Core (التوظيف والشراكات) | — |
| `experiences` (DX — جدول واحد بـ`type`) | التقويم (عرض قراءة فقط) والتعارض |

**جديد** (بالأسماء الملزمة — PLATFORM-ARCHITECTURE §3؛ الاقتراح الأصلي في تاريخ Git):

| الكيان | الغرض | الأولوية |
|---|---|---|
| `settings` (مجموعتا `brand.*` و`website.*`، كل قيمة مربوطة بـ`facts`) | Global Data Registry ([`GLOBAL-DATA-REGISTRY`](platform/GLOBAL-DATA-REGISTRY.md) §1) | P0 |
| `facts` (`FACT-0001`، الحقل، القيمة، المصدر، الحالة، اعتمد من، تاريخ التحقق، آخر مراجعة، ملاحظات) | Fact Registry. "Used In" محسوب من محلّل الأثر | P0 |
| **لا جدول جديد:** خريطة الأثر `config/impact.php` + `media_usages` + مراجع `page_sections` | الأثر قبل النشر، واستخدامات الوسائط، والحقائق، والمراجع المكسورة ([`CHANGE-IMPACT`](platform/CHANGE-IMPACT.md)) | P0 |
| `content_versions` (حقل `guard_result`: نتائج PASS/WARNING/BLOCKING + من تجاوز ولماذا) | Publish Guard ([`PUBLISH-GUARD`](platform/PUBLISH-GUARD.md)) | P0 |
| `signals` بـ`kind` = ISSUE (الفئة، الشدة، الكيان، السبب، الدليل، أول اكتشاف، آخر رؤية، الحالة، رابط الإجراء) | Health Engine + Command Center | P0 |
| `feature_flags` | Safe Mode، والإيقاف الطارئ، والتجارب المستقبلية | P0 |
| `rum_metrics` (اليوم، مجموعة المسار، الجهاز، المتصفح، اللغة، المقياس، العدد، p75، توزيع جيد/يحتاج تحسين/ضعيف) | RUM **مجمع فقط**، بلا أحداث فردية دائمة | P0 |
| `audit_logs` بأحداث `auth.*` (نجاح/فشل/مشبوه/إعادة تأكيد، **بلا كلمات مرور**) · `webauthn_credentials` (Passkeys) · `users` (سر TOTP مشفر + رموز استرداد Hash) | Security Center + المصادقة | P0 |
| `scheduled_job_runs` (المهام `ops:backup-db` · `ops:backup-files` · `ops:restore-test`) · `signals` + `releases` (حالة الاعتماديات) | صحة النسخ والاعتماديات | P0 |
| استفسار `INQ` بـ`type = privacy_request` (تصدير/حذف/تصحيح يدوي بمتابعة — لا جدول منفصل) · `config/data_inventory.php` (إعداد) | Privacy Center | P0 |
| `search_query_daily` (الاستعلام المطبّع المنظّف، العدد، النتائج، صفر نتائج، النتيجة المختارة) | Search Intelligence | P1 |
| `feedback` (الفرع، التقييمات، التعليق، الوقت — **بلا PII**؛ الوسوم في `topics` JSON بمصدرها، و`AI ASSISTED` موسوم) | Voice of Customer | P1 |
| `reviews` (نسخة محلية من مراجعات GBP + الرد) + `content_versions` (نسخ مسودة الرد: مسودة ← تعديل ← اعتماد ← نشر) | Reputation | P1 (بعد صلاحيات GBP) |
| `media` + المصور، والمصدر، والحقوق، وموافقة الأشخاص، وصالح للموقع، وصالح للإعلانات، والقيود، والانتهاء | Media Rights | P1 |

## E. خطة P0 (بالترتيب، **تصميمًا الآن** في P04 ثم بناء في P08)
| # | البند | لماذا بهذا الترتيب | المخرج الآن (P04) |
|---|---|---|---|
| 1 | **Platform Core:** Content Graph · Versions · Audit · Usage Graph · Feature Flags | كل ما بعده يعتمد عليه | `docs/PLATFORM-CORE.md` (يُكتب مع معمارية الـDashboard) — النواة الآن في [`PLATFORM-ARCHITECTURE`](architecture/PLATFORM-ARCHITECTURE.md) §3.1 |
| 2 | Global Data Registry + Fact Registry (مع ترحيل `04` المعتمد) | يمنع النصوص المكتوبة في الكود والحقائق القديمة | [`GLOBAL-DATA-REGISTRY.md`](platform/GLOBAL-DATA-REGISTRY.md) · [`FACT-REGISTRY.md`](platform/FACT-REGISTRY.md) |
| 3 | Publish Guard + Change Impact Preview + Language Parity (فحص نشر) | لا تغيير صامت ولا نشر لـPENDING | [`PUBLISH-GUARD.md`](platform/PUBLISH-GUARD.md) · [`CHANGE-IMPACT.md`](platform/CHANGE-IMPACT.md) · [`LANGUAGE-PARITY.md`](platform/LANGUAGE-PARITY.md) |
| 4 | Global Content Calendar + Collision Detector (محرك DX) | لا فوضى بصرية | [`GLOBAL-CONTENT-CALENDAR.md`](platform/GLOBAL-CONTENT-CALENDAR.md) |
| 5 | Safe Mode + Emergency Controls | شبكة أمان قبل أي تجربة ديناميكية | [`SAFE-MODE.md`](platform/SAFE-MODE.md) |
| 6 | Health Engine + Command Center + Backup/Restore Health | يعرف الـOwner ما الخطأ دائمًا | [`CONTENT-HEALTH.md`](platform/CONTENT-HEALTH.md) · قسم النسخ في [`SECURITY-CENTER.md`](platform/SECURITY-CENTER.md) |
| 7 | Security Center + مصادقة الـOwner القوية | البيانات حساسة (السير، الهويات، الشراكات) | [`SECURITY-CENTER.md`](platform/SECURITY-CENTER.md) |
| 8 | Privacy Center + رؤية الاحتفاظ | التزام وثقة | [`PRIVACY-CENTER.md`](platform/PRIVACY-CENTER.md) |
| 9 | RUM + تنبيهات الأداء | الأداء الحقيقي لا المختبر فقط | [`REAL-USER-MONITORING.md`](platform/REAL-USER-MONITORING.md) |
| 10 | CI/CD Quality Gate + Visual Regression | لا يصل كود للإنتاج بلا بوابة. **يمكن تفعيل جزء منه الآن على الـWireframes بعد موافقتك على دقائق CI** (I-07) | [`CI-CD-QUALITY-GATES.md`](platform/CI-CD-QUALITY-GATES.md) · [`VISUAL-REGRESSION.md`](platform/VISUAL-REGRESSION.md) |

## F. خطة P1
بعد اكتمال P0 في البناء، أو بالتوازي إذا لم تعتمد على نفس الأجزاء:
1. **مراكز:** Accessibility Center · Schema / Sitemap / Indexation Health (فحوص إضافية في Health Engine).
2. **البحث:** Public Global Search + Search Intelligence + Content Opportunities (يعتمد على Search Console).
3. **العملاء:** Voice of Customer + لوحة الآراء.
4. **الوسائط:** Media Rights Manager + Press / Media Kit (يعتمد على ملفات الهوية M-10 والحقائق المعتمدة).
5. **السمعة:** Reputation Center + مسار الرد (يعتمد على صلاحيات Google Business Profile API).
6. **الحداثة:** Content Freshness (يكتمل بعد أشهر من المحتوى المنشور، والفحص من P0).

## G. خطة P2 (مستقبلية — تقييم فقط الآن)
| البند | التوصية الآن | السبب |
|---|---|---|
| PWA / Offline (المنيو، الفروع، الساعات) | **NOT RECOMMENDED للإطلاق · RECOMMENDED للتقييم بعد 3 أشهر من البيانات** | خطر عرض سعر أو ساعات قديمة من الذاكرة المؤقتة. إن طُبّق لاحقًا: قراءة فقط + "آخر تحديث" ظاهر + انتهاء قصير |
| Experimentation (A/B) | **NOT RECOMMENDED حتى يكفي الزوار** | تجربة بلا عينة كافية تعطي نتائج مضللة. المعمارية جاهزة عبر `feature_flags` |
| AI Owner Copilot | **معمارية جاهزة فقط** | المسار الإلزامي: الطلب ← خطة AI ← معاينة ← الأثر ← موافقة الـOwner ← التطبيق ← Audit. **لا نشر حساس تلقائي** |

## H. المخاطر
| # | الخطر | الأثر | التخفيف |
|---|---|---|---|
| H-01 | تضخم إلى "برنامج مؤسسي" | بطء وتكلفة وتعقيد للـOwner | فلتر M32 §57 لكل ميزة · نواة واحدة · لا وحدة بلا سؤال تجيب عنه |
| H-02 | إنذارات كاذبة (روابط، Visual regression، صحة) | يتجاهل الـOwner التنبيهات | عتبات معقولة · تجميع · "لا تخمين سبب بلا دليل" · إسكات مؤقت مع سبب |
| H-03 | قبول قانوني لجمع RUM بلا موافقة الكوكيز | امتثال | بيانات مجمعة بلا معرّف ولا Cookie ولا IP مخزن · **قرار تصنيفه ضمن مراجعة الخصوصية** (PO-019) |
| H-04 | تأخر صلاحيات Google Business Profile API | السمعة بلا بيانات | `PENDING INTEGRATION` · لا Scraping |
| H-05 | Cloudways: Cron والمهام الخلفية والحدود | الفحوص الدورية | يُتحقق في الـAudit · فحوص خفيفة متباعدة |
| H-06 | لوحة الـOwner هدف عالي القيمة | تسريب سير وهويات | Passkeys/2FA · إعادة تأكيد · Rate limit · سجلات المصادقة · لا أسرار في الواجهة |
| H-07 | النسخ الاحتياطي غير قابل للاسترجاع | فقدان بيانات | **اختبار استعادة دوري على Staging** (لا يُعتبر النسخ حقيقيًا بلا ذلك) |
| H-08 | عبء الاعتماد على الـOwner وحده | تأخر النشر | اعتماد على دفعات · تحذيرات قابلة للتجاوز الموثق · Calendar يوضح القادم |
| H-09 | اعتماد كل شيء على قرار المنصة (DB-08) | تأخر البناء | التصميم محايد للمنصة. القرار بعد P04. **أُغلق:** DB-08 حُسم بـADR-001 |

## I. التكلفة والـAPIs (لا شيء يُفعّل بلا موافقة — M32 §45)
| # | الميزة | الخدمة | لماذا | البديل المجاني / الذاتي | التكلفة / الحصة | يحتاج موافقة؟ |
|---|---|---|---|---|---|---|
| I-01 | RUM | **جامع ذاتي** (مكتبة `web-vitals` حوالي 2KB + Endpoint في الموقع) | الأداء الحقيقي | — (هو البديل) | مجاني · تخزين بسيط | لا (ذاتي)، **مع قرار تصنيف الخصوصية** |
| I-02 | RUM إضافي | CrUX API (Google) | منظور Google الميداني | — | مجاني بمفتاح API | ✅ إنشاء مفتاح (AC-10) |
| I-03 | الفهرسة والفرص | Search Console API | Indexation، والفرص | — | مجاني | ✅ صلاحية (PO-011) |
| I-04 | السمعة | Google Business Profile API (المراجعات والردود) | التقييمات والردود | **لا Scraping** | مجاني لكن **يتطلب طلب وصول من Google** | ✅ (PO-009) |
| I-05 | التحليلات | GA4 Data API | الزيارات داخل الـDashboard | — | مجاني ضمن الحصص | ✅ صلاحية (PO-012) |
| I-06 | الأخطاء | Sentry وأمثاله | تتبع أخطاء JS والخادم | **سجل أخطاء ذاتي مبسط** (بلا PII) | خطط مدفوعة | ✅ **غير مُفعّل.** الذاتي أولًا |
| I-07 | CI/CD | GitHub Actions | بوابة الجودة | تشغيل محلي يدوي | مستودع خاص: **حصة دقائق شهرية مجانية** محدودة، ثم مدفوع | ✅ **قبل تفعيل أي Workflow** |
| I-08 | Visual regression | Playwright محلي (موجود) | لقطات مقارنة | — | مجاني | لا |
| I-09 | | Percy / Chromatic | SaaS بديل | Playwright | مدفوع | ❌ غير مقترح |
| I-10 | الاعتماديات | Dependabot / `npm audit` | تنبيهات أمنية | — | مجاني | لا (Dependabot تفعيل إعداد مستودع ✅) |
| I-11 | Passkeys | مكتبة WebAuthn مفتوحة | مصادقة قوية | — | مجاني | لا |
| I-12 | Uptime | مراقب خارجي (مثل UptimeRobot) | تنبيه عند التوقف | فحص من Cron داخلي (لا يكشف توقف السيرفر نفسه) | مجاني محدود / مدفوع | ✅ |
| I-13 | Malware scan | ClamAV محلي (إن سمح Cloudways) | فحص المرفقات | منع صارم للأنواع | مجاني | يُتحقق في الـAudit |

## J. بوابات الاعتماد
| # | البوابة | ماذا يُعتمد | ما الذي تمنعه |
|---|---|---|---|
| J-1 | **هذه الخطة (A–J)** | الأنظمة والترتيب والدمج | كتابة وثائق التصميم التفصيلية للـP0 وWireframes الوحدات |
| J-2 | **Wireframes الوحدات الجديدة + معمارية الـDashboard (P04 Gate H)** | الشاشات والـIA | ~~أي بناء للـDashboard~~ لا شيء (M36: مراجعة غير مانعة) |
| J-3 | **قرار المنصة DB-08 + Cloudways Audit** | التقنية | أي Backend. **DB-08 حُسم بـADR-001**؛ يبقى الـCloudways Audit |
| J-4 | **I-07 دقائق CI** | تفعيل GitHub Actions | الـPipeline الآلي (التشغيل المحلي متاح) |
| J-5 | **Google APIs** (I-02…I-05) | الصلاحيات | البيانات الحية في الـDashboard (تُعرض `DEMO DATA` أو `PENDING INTEGRATION`) |
| J-6 | **تصنيف خصوصية RUM والـFeedback** (ضمن PO-019) | الإطار القانوني | تشغيل الجمع على Production |
| J-7 | **Production** | كل إصدار | النشر (Staging ← اختبارات ← موافقة) |
