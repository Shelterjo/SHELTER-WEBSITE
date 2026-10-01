# SHELTER COFFEE — DECISION LOG

> القاعدة: لا يُعتمد أي قرار بدون موافقة صريحة من الـOwner. عدم الرد ≠ موافقة.
> لا يتم تغيير أي قرار معتمد لاحقًا بدون الرجوع للـOwner.

## الحالات

| Status | المعنى |
|---|---|
| `PROPOSED` | مقترح من فريق المشروع، بانتظار نقاش الـOwner |
| `APPROVED` | اعتمده الـOwner صراحةً — يمكن البناء عليه |
| `REJECTED` | رفضه الـOwner |
| `SUPERSEDED` | استُبدل بقرار لاحق معتمد (يُذكر رقم القرار الجديد) |

## القرارات

| # | Decision | Date | Reason | Owner Approval | Impact | Status |
|---|---|---|---|---|---|---|
| D-000 | الموقع الحالي `www.shelterjo.com` = مصدر معلومات فقط، وليس مرجعًا للتصميم أو الـUX أو الكود أو الـArchitecture | 2026-10-01 | تعليمات الـOwner في Master Prompt | ✅ من الـOwner (Master Prompt) | كل المراحل | `APPROVED` |
| D-001 | تسلسل العمل المرحلي (Discovery → … → Post-Launch) ولا قفز لمرحلة قبل اعتماد السابقة | 2026-10-01 | تعليمات الـOwner | ✅ من الـOwner (Master Prompt) | كل المراحل | `APPROVED` |
| D-002 | ممنوع Coding / Plugins / تغييرات Cloudflare أو Cloudways أو DNS / نشر محتوى خلال Phase 01 | 2026-10-01 | تعليمات الـOwner | ✅ من الـOwner (Master Prompt) | Phase 01 | `APPROVED` |
| D-003 | أي معلومة أو صورة تظهر للزوار تمر بمسار: Research → Collect → Review → Present → Owner Confirmation → APPROVED → Implementation → Publish | 2026-10-01 | تعليمات الـOwner | ✅ من الـOwner (Master Prompt) | المحتوى والأصول | `APPROVED` |
| D-004 | ترتيب الأولويات عند التعارض التقني: UX → Accuracy → Speed → Mobile → Accessibility → SEO/AEO/GEO → Security → Maintainability → Visual → Motion | 2026-10-01 | تعليمات الـOwner | ✅ من الـOwner (Master Prompt) | كل القرارات التقنية | `APPROVED` |
| D-005 | ممنوع "AI / Vibe-coding look" (Gradients عشوائية، Neon، Glassmorphism، Blobs…) — التصميم Bespoke لـSHELTER | 2026-10-01 | تعليمات الـOwner | ✅ من الـOwner (Master Prompt) | Design System | `APPROVED` |
| D-006 | المنيو الرسمي هو الذي يرسله الـOwner، والمنيو القديم للمقارنة فقط | 2026-10-01 | تعليمات الـOwner | ✅ من الـOwner (Master Prompt) | Menu | `APPROVED` |
| D-007 | **الاسم الرسمي:** بالإنجليزية `SHELTER COFFEE` · بالعربية `شلتر كوفي` · الكلمة الوصفية المفضلة "كوفي". "كافيه/كافية" ليست جزءًا من الاسم، وتُستخدم فقط في محتوى SEO أو وصف النشاط عند الحاجة وبعد موافقة الـOwner | 2026-10-01 | R1-01 | ✅ Owner | الشعار، العناوين، الـSchema، كل النصوص | `APPROVED` |
| D-008 | **الفروع العامة الحالية = فرعان:** (1) `SHELTER COFFEE DRIVE` — الفرع الرئيسي في إربد، بجانب صالة قصر النخيل / أرابيلا، فيه Drive Thru · (2) `SHELTER COFFEE HOUSE` — داخل Irbid City Center، الطابق الأول. **مشغل الحلويات** = منشأة إنتاج وليس فرعًا للعملاء. لا يوجد فرع Franchise عامل يُعرض. أي فرع آخر يُكتشف لا يُعتبر نشطًا قبل سؤال الـOwner | 2026-10-01 | R1-02 | ✅ Owner | الفروع، الـSchema، الـLocation model | `APPROVED` |
| D-009 | ~~سنة التأسيس 2018~~ — **خطأ في التوثيق، صحّحه الـOwner** | 2026-10-01 | R1-03 (قراءة خاطئة) | ❌ | — | `SUPERSEDED` → D-018 |
| D-010 | **Architecture جاهزة عالميًا من البداية:** دولة ← مدينة ← فرع، فروع مملوكة وFranchise، بدون إعادة بناء. لا توجد قائمة دول/مدن معتمدة. لا تُخترع خطة توسع، ولا تُعرض أي دولة أو فرع مستقبلي للجمهور قبل معلومات وموافقة الـOwner | 2026-10-01 | R1-04 | ✅ Owner | الـIA، الـURL، الـData model | `APPROVED` |
| D-011 | **الدومين:** `shelterjo.com` هو الحالي والمستخدم، وليس بالضرورة الدومين العالمي النهائي (يُبحث عن Global Domain لاحقًا ولم يُعتمد). لا تغيير للدومين ولا Migration الآن. الـArchitecture يجب ألا ترتبط بالأردن بشكل يصعب نقله، وتكون قابلة لنقل 301 صحيح لاحقًا بدون خسارة SEO | 2026-10-01 | R1-05 | ✅ Owner | الـURL Architecture، الـSEO | `APPROVED` |
| D-012 | **ترتيب الجمهور (مدخل وليس قرار تصميم):** 1) سكان إربد 2) الموظفون ومستخدمو الـDrive Thru 3) طلاب الجامعات 4) العائلات 5) زوار إربد من مدن أخرى 6) الأجانب المقيمون أو الزوار 7) شركاء الأعمال. لا يُستخدم لقرارات تصميم نهائية قبل شرح أثره على الـUX والـHomepage | 2026-10-01 | R1-06 | ✅ Owner | الـUX، الـHomepage | `APPROVED` (كمدخل) |
| D-013 | **أهم أفعال الزائر:** 1) مشاهدة المنيو 2) معرفة الفروع والوصول إليها (Get Directions) 3) معلومات الفرع وساعات العمل والتواصل. ترتيب الـCTA النهائي غير معتمد — مقترح في `09` AR-03 | 2026-10-01 | R1-07 | ✅ Owner (الأفعال) · ⏳ (ترتيب الـCTA) | الـCTA، الـNavigation، القياس | `APPROVED` (جزئيًا) |
| D-014 | **اللغات:** العربية أساسية، الإنجليزية نسخة كاملة، روابط لاتينية واضحة. البنية جاهزة للغات مستقبلية (لا لغة ثالثة معتمدة). **الـLanguage/URL Architecture النهائية غير معتمدة** حتى مقارنة الخيارات (`09` AR-01) | 2026-10-01 | R1-08 | ✅ Owner (المبدأ) · ⏳ (البنية) | الـURL، الـhreflang | `APPROVED` (جزئيًا) |
| D-015 | **الصفحات المعتمدة:** الرئيسية · المنيو · الفروع · صفحة لكل فرع · من نحن · Coffee Knowledge Hub · الفعاليات والحملات · تواصل · FAQ · سياسة الخصوصية · الشروط (عند الحاجة القانونية) · **التوظيف** (يعاد تصميمه بالكامل مع مراجعة البيانات المطلوبة والخصوصية) · **Franchise** (كقدرة مستقبلية — لا تُنشر رسوم أو شروط أو أن التقديم مفتوح قبل اعتماد المحتوى). **مؤجلة للنقاش:** الحجز والحفلات · الرعايات · صفحة الفريق · النشرة البريدية | 2026-10-01 | R1-09 | ✅ Owner | الـSitemap | `APPROVED` |
| D-016 | **الطلب أونلاين:** الموقع الآن ليس E-Commerce (الهدف: Brand + Menu + Locations + Content + Events + Blog)، والبنية قابلة لإضافة Online Ordering / E-Commerce لاحقًا بدون إعادة بناء. `shop.shelterjo.com`: لا يُفترض وظيفته؛ يُفحص عند توفر الوصول ويُعرض على الـOwner قبل أي قرار. تطبيقات التوصيل لكل فرع: جولة مستقلة (R2B)، ولا تُستنتج من الموقع القديم | 2026-10-01 | R1-10 | ✅ Owner | الـCTA، الـArchitecture | `APPROVED` |
| D-017 | **قواعد العمل:** أي معلومة ناقصة = `MISSING — OWNER INPUT REQUIRED` · أي معلومة متعارضة = `NEEDS OWNER VERIFICATION` · الأسئلة على دفعات قصيرة · أي سؤال Architecture-critical يُطرح فورًا · لا Design ولا Coding قبل إنهاء الـArchitecture والمحتوى الأساسي · لا تغيير على Production بدون موافقة | 2026-10-01 | رسالة الـOwner بعد R1 | ✅ Owner | كل المراحل | `APPROVED` |
| D-018 | **سنة التأسيس: 2019** · **تاريخ السنوية: 20/04** (معتمد للاستخدام مستقبلًا في: Brand Story، حملات السنوية، Events، المحتوى). القيم **2018** و**"منذ 2022"** = `OLD OR INCORRECT` ولا تُستخدم في الموقع الجديد ولا في SEO ولا في الـSchema ولا في من نحن. أي صياغة تسويقية عن عدد السنوات أو المناسبة تُعرض على الـOwner قبل النشر | 2026-10-01 | تصحيح الـOwner + R1-03a | ✅ Owner | من نحن، الـSchema (foundingDate)، الحملات | `APPROVED` |
| D-019 | **AR-01: اعتماد مبدئي لـOption C (Brand Layer + Market Layer)** للتوسع العالمي. **غير مجمّد:** الـOwner يطلب URL Tree كاملة ومقارنة ترتيب اللغة/الدولة (`/jo/menu` · `/en/jo/menu` والبدائل) بهدف نقل 1:1 بسيط إلى Global Domain مستقبلًا — المسودة في `10-url-architecture-draft.md` | 2026-10-01 | رد الـOwner على AR-01 | ✅ Owner (مبدئي) | الـURL Architecture | `APPROVED` (مبدئي) |
| D-020 | **أسماء الفروع وبياناتها المعتمدة:** DRIVE = `شلتر كوفي درايف` / `SHELTER COFFEE DRIVE` — "إربد — بجانب صالة قصر النخيل / منطقة أرابيلا" (ليست صياغة عنوان نهائية) — الساعات: السبت–الخميس 07:00 ص – 02:00 ص، الجمعة 08:00 ص – 02:00 ص · HOUSE = `شلتر كوفي هاوس` / `SHELTER COFFEE HOUSE` — Irbid City Center، الطابق الأول، بجانب البنك الإسلامي الأردني — الساعات: السبت–الأربعاء 09:00 ص – 10:00 م، الخميس–الجمعة 09:00 ص – 11:00 م. **غير معتمد:** العنوان التفصيلي (عربي/إنجليزي)، اسم المول الرسمي، روابط Google Maps — تُتحقق من Google Business Profile ثم تُعرض على الـOwner. لا فرق معتمد بين ساعات الدرايف والجلسات. أي ساعات خاصة للمول تُعرض كتعارض ولا تُغيّر تلقائيًا | 2026-10-01 | R2-01 | ✅ Owner | صفحات الفروع، الـSchema | `APPROVED` (جزئيًا) |
| D-021 | **متطلب: نظام ساعات خاصة في الـCMS:** Regular Hours + Special Hours / Holiday Override + Temporary Closure / Emergency Notice — لكل منها: Start Date، End Date، Reason، Branch، Publish Status. تعديل رمضان أو العيد لا يغيّر الساعات الأساسية. **الـUX والـCMS يُناقشان مع الـOwner قبل التنفيذ** | 2026-10-01 | R2-02 | ✅ Owner | الـCMS، صفحات الفروع، "مفتوح الآن" | `APPROVED` (كمتطلب) |
| D-022 | **الأرقام:** 0799009436 · 0799338445 · 0799530383 = كلها `PENDING OWNER VERIFICATION` — **لا يُنشر أي رقم** حتى يسمّي الـOwner وظيفته صراحة. 0799338445 رقم يستخدمه الـOwner شخصيًا أحيانًا — ليس عامًا ولا رقم شكاوى تلقائيًا. تُسأل لاحقًا كل وظيفة بشكل مستقل: Main Brand Phone · DRIVE Phone · HOUSE Phone · Reservations · Complaints · Franchise · WhatsApp | 2026-10-01 | R2-03 | ✅ Owner | التواصل، الـSchema | `APPROVED` |
| D-023 | **WhatsApp:** `PENDING OWNER DECISION` — لا يُضاف حتى يحدد الـOwner الرقم والغرض | 2026-10-01 | R2-04 | ✅ Owner | التواصل | `APPROVED` |
| D-024 | **البريد:** `info@shelterjo.com` = مرشح للبريد العام، **يُتحقق مع الـOwner قبل نشره**. البنية تدعم مستقبلًا عناوين منفصلة (Careers، Franchise، Business inquiries). لا يُنشأ ولا يُنشر أي بريد غير موجود فعليًا | 2026-10-01 | R2-05 | ✅ Owner | التواصل، النماذج | `APPROVED` |
| D-025 | **الحسابات الرسمية:** لا يُعتمد أي حساب من البحث تلقائيًا. جدول (Platform، Account Name، URL، Evidence، Status) — كلها `PENDING OWNER VERIFICATION` — تُعرض حسابًا حسابًا (`11-social-accounts-verification.md`). لا افتراض أن `youtube.com/@sheltercoffee` رسمي. لا قرار بتغيير أي Handle قبل النقاش | 2026-10-01 | R2-06 | ✅ Owner | الـFooter، الـSchema (sameAs) | `APPROVED` |
| D-026 | **مشغل الحلويات = NON-PUBLIC LOCATION:** لا يظهر كفرع، لا عنوان، لا يظهر في Locations. يمكن مستقبلًا ذكر وجود Production / Pastry Facility في Brand Story أو وصف المنتجات **إذا قرر الـOwner**، وأي صياغة (مثل "حلوياتنا تُحضّر في مشغلنا") تُعرض عليه قبل النشر | 2026-10-01 | R2-07 | ✅ Owner | الـLocation model، المحتوى | `APPROVED` |
| D-027 | **الـCTA على الموبايل (مبدئي):** 1) Menu 2) Locations / Directions · حالة الفرع وساعات العمل داخل Branch Card. **الشكل البصري النهائي في مرحلة UX/UI** | 2026-10-01 | رد الـOwner على AR-03 | ✅ Owner (مبدئي) | الـCTA، الـNavigation | `APPROVED` (مبدئي) |
| D-028 | **الصلاحيات:** AC-01 ✅ (الوصول المباشر أولًا، ثم فحص حقيقي كامل: Desktop، Mobile، DOM، Navigation، Redirects، Schema، Console، Network، Performance، Lighthouse، Playwright flows، shop.shelterjo.com — وعدم الاعتماد على Semrush وحده) · AC-02 Search Console ✅ مبدئيًا Read-only / Restricted عند تجهيز الربط · AC-03 Google Business Profile ✅ معلومات Read-only فقط · AC-04 Supermetrics ⏳ لا استهلاك قبل عرض: ماذا سنقرأ، لماذا، الاستهلاك المتوقع، ثم انتظار الموافقة | 2026-10-01 | رد الـOwner على `08` | ✅ Owner | الفحص | `APPROVED` |
| D-029 | **قاعدة الاستهلاك:** لا يُشغَّل أي Crawl أو Search أو API operation واسع قد يستهلك Credits / Quota / Paid Units بدون أن يُعرض مسبقًا: 1) الخدمة 2) الهدف 3) الاستهلاك المتوقع 4) البديل المجاني أو الأقل استهلاكًا — ثم انتظار موافقة الـOwner | 2026-10-01 | رسالة الـOwner | ✅ Owner | كل الأدوات المدفوعة/المحدودة | `APPROVED` |
| D-030 | **قاعدة الأسئلة:** لا يُعاد سؤال الـOwner عن معلومات أصبحت معتمدة؛ تُطرح الأسئلة الناقصة فقط | 2026-10-01 | رسالة الـOwner | ✅ Owner | المقابلات | `APPROVED` |

## قرارات مفتوحة (PROPOSED — بانتظار الـOwner)

القائمة التفصيلية في [`../phase-01-discovery/05-decisions-before-design.md`](../phase-01-discovery/05-decisions-before-design.md).

| # | Decision | Recommendation | Status |
|---|---|---|---|
| DB-01 | الحقائق التأسيسية (الاسم، سنة التأسيس، الفروع) | — | ✅ حُسمت في D-007، D-008، D-018، D-020 |
| DB-02 | بنية اللغة + السوق + الـURL + قابلية نقل الدومين | Option C ✅ مبدئيًا (D-019) — ترتيب اللغة/الدولة: توصيتنا **P3** (`/ar/jo/menu/` · `/en/jo/menu/`) — `10-url-architecture-draft.md` | `PROPOSED` — بانتظار اعتماد الـURL Tree |
| DB-03 | لغة الـSlugs | لاتينية + 301 من العربية | المبدأ ✅ (D-014) · التفاصيل مع DB-02 |
| DB-04 | الصفحات القديمة ذات القرار التجاري | — | ✅ جزئيًا (D-015)؛ المؤجل: الحجز، الرعايات، الفريق، النشرة، دليل كافيهات إربد (R7-03) |
| DB-05 | نموذج الـNavigation على الموبايل | Top Sticky Header + Contextual Actions | `PROPOSED` |
| DB-06 | بنية المنيو | Hybrid | `PROPOSED` |
| DB-07 | بنية الفروع | Hierarchical + Progressive Activation | `PROPOSED` |
| DB-08 | المنصة / الـCMS | يُدرس بعد الـIA | `PROPOSED` |
| DB-09 | اسم ومسار قسم المعرفة | Stage 6 | `PROPOSED` |
| DB-10 | عرض الأسعار | — | `PROPOSED` |
| DB-11 | الـHost الرسمي | `www.shelterjo.com` | `PROPOSED` |
| DB-12 | `shop.shelterjo.com` | يُفحص عند توفر الوصول (D-016) | `PENDING ACCESS` |
| DB-15 | نموذج بيانات المواقع (Country → City → Location بأنواع وحالات ورؤية) | `09` AR-02 | `PROPOSED` |
| DB-16 | ترتيب الـCTA على الموبايل | `09` AR-03 | ✅ مبدئيًا (D-027) — الشكل البصري في UX/UI |
| DB-17 | النشرة البريدية | لا في الإصدار الأول؛ مكان جاهز لاحقًا — `09` AR-05 | `PROPOSED` |
| DB-18 | نموذج الساعات الخاصة (UX + CMS) | `09` AR-07 | `PROPOSED` — نقاش قبل التنفيذ (D-021) |
| DB-13 | الروابط الخارجية السبام | لا إجراء قبل Search Console | `PROPOSED` |
| DB-14 | HubSpot و Newsletter | — | `PROPOSED` |
