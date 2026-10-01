# 05 — SEO Migration · Analytics · Performance (Deliverables 16–18)

## 1. خطة SEO والهجرة (M29 §64–§67، §71–§73، §90)
| البند | القرار |
|---|---|
| الروابط الجديدة | `/ar/franchise/` · `/en/franchise/` (طبقة العلامة، متسقة مع مسودة الروابط D-031) |
| الرابط القديم | `/franchise-shelter-coffee/`<br>- **لا Redirect الآن.**<br>- 301 إلى الرابط الجديد المناسب **بعد** الخطوات العشر **وموافقة الـOwner** |
| الخطوات العشر قبل الإطلاق | 1. Baseline لـSearch Console (PO-011)<br>2. فحص الترتيب الحالي<br>3. الروابط الخلفية<br>4. مراجعة الـCanonical<br>5. الفهرسة<br>6. التحقق من الرابط الجديد<br>7. hreflang<br>8. Sitemap<br>9. الروابط الداخلية<br>10. اختبار الـRedirect |
| Canonical / hreflang | كل صفحة Canonical لنفسها.<br>- `ar` ↔ `en` متبادلة.<br>- **x-default** حسب المعمارية العالمية المعتمدة: بند مفتوح PO-005.<br>- **لا تحويل تلقائي حسب الموقع الجغرافي** |
| H1 | AR: "كن شريكًا في نمو SHELTER COFFEE" (أو البديل). EN: "Grow with SHELTER COFFEE". **H1 واحد** |
| Title / Meta (مسودة) | AR: `فرنشايز SHELTER COFFEE — كن شريكًا مع شلتر كوفي`<br>EN: `SHELTER COFFEE Franchise & Partnerships`<br>الوصف بلا ادعاءات مالية. ⏳ اعتماد النص |
| نية البحث | AR: فرنشايز قهوة · فرنشايز كوفي شوب · امتياز تجاري قهوة · فرنشايز قهوة الأردن · فرنشايز كوفي الأردن · كن شريكًا مع شلتر · امتياز SHELTER COFFEE<br>EN: coffee franchise Jordan · coffee shop franchise · SHELTER COFFEE franchise · coffee franchise opportunity<br>**بلا حشو كلمات:** كل عبارة في موضع طبيعي واحد (H1/H2/الوصف/الـFAQ) |
| AEO / GEO | عناوين واضحة · سطر حقائق مختصر (2019، إربد، DRIVE/HOUSE) · FAQ · علاقة كيان واضحة مع Organization. **لا محتوى لخداع محركات AI** |
| Schema | **مسموح:** `Organization` (عام) · `WebPage` · `BreadcrumbList` · `FAQPage` للأسئلة المعتمدة المنشورة فقط<br>**ممنوع:** Offer · Price · Rating · Review · Investment product · Availability |
| الروابط الداخلية | من هي SHELTER/About · الفروع · المنيو · Coffee Knowledge · التواصل. **الهدف الأساسي يبقى طلب الشراكة** |
| Sitemap / Robots | تدخل الـSitemap عند النشر فقط. لا `noindex` على النسخة المنشورة. الـStaging محمي بمصادقة (وليس بـrobots فقط) |

## 2. خطة التحليلات (M29 §68–§70، §91)
| الحدث | متى | المعاملات (بلا PII) |
|---|---|---|
| `franchise_page_view` | مرة لكل فتح صفحة | `page_language` · `entry_context` |
| `franchise_cta_click` | ضغط CTA | `cta_id` · `cta_location` (hero / mid / final / sticky) |
| `franchise_form_start` | أول تفاعل بحقل | `form_variant` |
| `franchise_form_step` | إكمال خطوة (Multi-step فقط) | `step_number` · `step_name` |
| `franchise_form_submit` | نجاح الإرسال في الخادم | `form_variant` |
| `franchise_form_error` | خطأ تحقق أو خادم | `error_type` (قائمة ثابتة) |
| `franchise_faq_open` | فتح سؤال | `faq_id` |

**القواعد:**
- **ممنوع:** الاسم، والهاتف، والبريد، والشركة، والرسالة، وبيانات الاستثمار، والملفات، ورقم الطلب.
- **التسمية:** متسقة مع نمط المشروع (`menu_view`، `careers_page_view`). تُضاف لقاموس الأحداث العام للاعتماد (PO-043).
- **التحقق:** حدث واحد لكل فعل، بلا ازدواج، وبلا PII. GTM Preview + DebugView على الـStaging.
- **الإسناد (Attribution):** يُخزن مع الطلب في الخادم (المصدر، الوسيط، الحملة، صفحة الهبوط، اللغة).
  - لا موقع دقيق.
  - **الدولة يختارها المتقدم في النموذج**، ولا تُستنتج من الـIP.

## 3. ميزانية الأداء (M29 §62، §86، §87)
> صفحة بصرية قوية، لكن **السرعة أهم**. الأرقام مبررة، وليست أهدافًا عشوائية.

| البند | الهدف | التبرير |
|---|---|---|
| LCP (موبايل p75) | ≤ 2.5s | Core Web Vitals. عنصر الـLCP = صورة الـHero أو العنوان |
| INP | ≤ 200ms | تفاعلات بسيطة (Accordion، النموذج) |
| CLS | ≤ 0.1 | أبعاد محجوزة لكل الصور، وخطوط بمقاسات بديلة |
| صورة الـHero | AVIF:<br>- ≤ 120KB موبايل (عرض 360–430 × 2x).<br>- ≤ 250KB ديسكتوب.<br>`fetchpriority="high"`، **ليست Lazy** | أكبر عنصر في الشاشة الأولى |
| الصور تحت الشاشة الأولى | `loading="lazy"` · `srcset/sizes` · AVIF/WebP من خط Sharp | |
| HTML | ≤ 40KB مضغوط لكل لغة | محتوى نصي أطول من المنيو |
| CSS | ≤ 30KB مضغوط | |
| JS | ≤ 60KB مضغوط للصفحة:<br>- Motion يُحمّل Lazy للأقسام التي تحتاجه.<br>- **لا Lenis افتراضيًا.**<br>- **لا مكتبة حركة مكررة** | الحركة لا تؤخر التفاعل |
| الخطوط | ضمن ميزانية الموقع ≤ 120KB WOFF2 | |
| الفيديو | **لا فيديو على الموبايل افتراضيًا.** إن وُجد على الديسكتوب: Poster + تحميل كسول + احترام `prefers-reduced-motion` | |
| الأطراف الثالثة | GA4/GTM بعد `load`/idle. **HubSpot والدردشة غير محمّلين في هذه الصفحة.** تدقيق HubSpot الحالي قبل أي إزالة عامة | |
