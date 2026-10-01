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
| D-009 | **سنة التأسيس: 2018.** القيم الأخرى المنشورة في الموقع القديم ("2019"، "منذ 2022") = `NEEDS VERIFICATION / OLD OR INCORRECT CONTENT` ولا تُستخدم. تاريخ السنوية **20/04**: بانتظار توضيح (R1-03a) | 2026-10-01 | R1-03 | ✅ Owner (2018) · ⏳ (20/04) | من نحن، الـSchema (foundingDate) | `APPROVED` (جزئيًا) |
| D-010 | **Architecture جاهزة عالميًا من البداية:** دولة ← مدينة ← فرع، فروع مملوكة وFranchise، بدون إعادة بناء. لا توجد قائمة دول/مدن معتمدة. لا تُخترع خطة توسع، ولا تُعرض أي دولة أو فرع مستقبلي للجمهور قبل معلومات وموافقة الـOwner | 2026-10-01 | R1-04 | ✅ Owner | الـIA، الـURL، الـData model | `APPROVED` |
| D-011 | **الدومين:** `shelterjo.com` هو الحالي والمستخدم، وليس بالضرورة الدومين العالمي النهائي (يُبحث عن Global Domain لاحقًا ولم يُعتمد). لا تغيير للدومين ولا Migration الآن. الـArchitecture يجب ألا ترتبط بالأردن بشكل يصعب نقله، وتكون قابلة لنقل 301 صحيح لاحقًا بدون خسارة SEO | 2026-10-01 | R1-05 | ✅ Owner | الـURL Architecture، الـSEO | `APPROVED` |
| D-012 | **ترتيب الجمهور (مدخل وليس قرار تصميم):** 1) سكان إربد 2) الموظفون ومستخدمو الـDrive Thru 3) طلاب الجامعات 4) العائلات 5) زوار إربد من مدن أخرى 6) الأجانب المقيمون أو الزوار 7) شركاء الأعمال. لا يُستخدم لقرارات تصميم نهائية قبل شرح أثره على الـUX والـHomepage | 2026-10-01 | R1-06 | ✅ Owner | الـUX، الـHomepage | `APPROVED` (كمدخل) |
| D-013 | **أهم أفعال الزائر:** 1) مشاهدة المنيو 2) معرفة الفروع والوصول إليها (Get Directions) 3) معلومات الفرع وساعات العمل والتواصل. ترتيب الـCTA النهائي غير معتمد — مقترح في `09` AR-03 | 2026-10-01 | R1-07 | ✅ Owner (الأفعال) · ⏳ (ترتيب الـCTA) | الـCTA، الـNavigation، القياس | `APPROVED` (جزئيًا) |
| D-014 | **اللغات:** العربية أساسية، الإنجليزية نسخة كاملة، روابط لاتينية واضحة. البنية جاهزة للغات مستقبلية (لا لغة ثالثة معتمدة). **الـLanguage/URL Architecture النهائية غير معتمدة** حتى مقارنة الخيارات (`09` AR-01) | 2026-10-01 | R1-08 | ✅ Owner (المبدأ) · ⏳ (البنية) | الـURL، الـhreflang | `APPROVED` (جزئيًا) |
| D-015 | **الصفحات المعتمدة:** الرئيسية · المنيو · الفروع · صفحة لكل فرع · من نحن · Coffee Knowledge Hub · الفعاليات والحملات · تواصل · FAQ · سياسة الخصوصية · الشروط (عند الحاجة القانونية) · **التوظيف** (يعاد تصميمه بالكامل مع مراجعة البيانات المطلوبة والخصوصية) · **Franchise** (كقدرة مستقبلية — لا تُنشر رسوم أو شروط أو أن التقديم مفتوح قبل اعتماد المحتوى). **مؤجلة للنقاش:** الحجز والحفلات · الرعايات · صفحة الفريق · النشرة البريدية | 2026-10-01 | R1-09 | ✅ Owner | الـSitemap | `APPROVED` |
| D-016 | **الطلب أونلاين:** الموقع الآن ليس E-Commerce (الهدف: Brand + Menu + Locations + Content + Events + Blog)، والبنية قابلة لإضافة Online Ordering / E-Commerce لاحقًا بدون إعادة بناء. `shop.shelterjo.com`: لا يُفترض وظيفته؛ يُفحص عند توفر الوصول ويُعرض على الـOwner قبل أي قرار. تطبيقات التوصيل لكل فرع: جولة مستقلة (R2B)، ولا تُستنتج من الموقع القديم | 2026-10-01 | R1-10 | ✅ Owner | الـCTA، الـArchitecture | `APPROVED` |
| D-017 | **قواعد العمل:** أي معلومة ناقصة = `MISSING — OWNER INPUT REQUIRED` · أي معلومة متعارضة = `NEEDS OWNER VERIFICATION` · الأسئلة على دفعات قصيرة · أي سؤال Architecture-critical يُطرح فورًا · لا Design ولا Coding قبل إنهاء الـArchitecture والمحتوى الأساسي · لا تغيير على Production بدون موافقة | 2026-10-01 | رسالة الـOwner بعد R1 | ✅ Owner | كل المراحل | `APPROVED` |

## قرارات مفتوحة (PROPOSED — بانتظار الـOwner)

القائمة التفصيلية في [`../phase-01-discovery/05-decisions-before-design.md`](../phase-01-discovery/05-decisions-before-design.md).

| # | Decision | Recommendation | Status |
|---|---|---|---|
| DB-01 | الحقائق التأسيسية (الاسم، سنة التأسيس، الفروع) | — | ✅ حُسمت في D-007، D-008، D-009 (عدا 20/04) |
| DB-02 | بنية اللغة + السوق + الـURL + قابلية نقل الدومين | **Option C** (طبقة العلامة + طبقة السوق) — انظر `09` AR-01 | `PROPOSED` — بانتظار اختيار الـOwner |
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
| DB-16 | ترتيب الـCTA على الموبايل | `09` AR-03 | `PROPOSED` — يُحسم في R4 |
| DB-17 | النشرة البريدية | لا في الإصدار الأول؛ مكان جاهز لاحقًا — `09` AR-05 | `PROPOSED` |
| DB-13 | الروابط الخارجية السبام | لا إجراء قبل Search Console | `PROPOSED` |
| DB-14 | HubSpot و Newsletter | — | `PROPOSED` |
