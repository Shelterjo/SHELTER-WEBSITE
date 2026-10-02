# SHELTER COFFEE — APPROVED ASSET LIBRARY

> القاعدة الصارمة: **ممنوع استخدام أي صورة / فيديو / Graphic / Illustration / Lottie / أيقونة مخصصة على الموقع قبل عرضها على الـOwner والحصول على `Approved`.**
> يشمل ذلك: Hero, Product, Branch, Blog, Background, Stock, AI-generated, Graphics, Illustrations, Videos, Decorative assets.

## الحقول الإلزامية لكل Asset

| Field | الوصف |
|---|---|
| Asset ID | `AST-###` |
| Name | اسم وصفي |
| Type | Photo / Video / Logo / Icon / Illustration / Lottie / Graphic / Font |
| Category | Product / Branch / Brand / People / Event / Blog / Background / UI |
| Source | من أين جاء (الـOwner، الموقع القديم، مصوّر، Stock، AI…) مع رابط/مسار |
| Rights / License | من يملك الحقوق؟ هل الاستخدام التجاري مسموح؟ |
| Alt Text (AR / EN) | نص بديل معتمد |
| Focal Point | نقطة التركيز للقص (Crop) بالنسب المختلفة |
| Suggested Placement | أين نقترح استخدامه ولماذا |
| Usage | الصفحات التي يُستخدم فيها فعليًا بعد الاعتماد |
| Approval Status | `Pending` / `Approved` / `Rejected` / `Archived` |
| Owner Decision Date | تاريخ القرار |

## السجل

| Asset ID | Name | Type | Category | Source | Rights | Alt Text | Focal Point | Suggested Placement | Approval Status |
|---|---|---|---|---|---|---|---|---|---|
| AST-001 | شعار SHELTER الأبيض (الرأس) | Logo | Brand | الموقع القديم `wp-content/uploads/2024/01/shelter-offee-drive-logo-2024-13.png` ← `resources/brand/source/old-site-logo-white.png` ← `public/brand/logo-white-{240,480}.{webp,png}` | علامة الـOwner التجارية | AR/EN: `SHELTER COFFEE` (الاسم الرسمي، BRAND-001) | — | رأس الموقع على الخلفية الداكنة | **Approved** (D-309، 2026-10-01) |
| AST-002 | أيقونة الموقع (Favicon) | Icon | Brand | الموقع القديم `Favicon-shelter-Logo-14.png` ← `public/brand/favicon-{32,180}.png` | علامة الـOwner | — (زخرفية) | — | تبويب المتصفح وأيقونة الهاتف | **Approved** (D-309) |
| AST-003 | شعار SHELTER COFFEE DRIVE (أسود على أبيض) | Logo | Brand | أرسله الـOwner في المحادثة 2026-10-01 ← `resources/brand/source/owner-upload-drive-emblem.jpg` | علامة الـOwner | AR/EN: `SHELTER COFFEE DRIVE` | المركز | مرجع للفرع DRIVE ونسخة الطباعة الفاتحة (لم يُستخدم بعد) | **Approved** (D-309) |
| AST-004 | خط GE SS Two (Light/Bold) | Font | Brand | الموقع القديم `ARBFONTS-GE-SS-TWO-*.ttf` ← `public/fonts/licensed/` (**غير مرفوع إلى Git**) | © Boutros International — **ترخيص Web غير مؤكد** | — | — | النص العربي | **خارج البناء** (D-329): يعود عند تأكيد ترخيص الويب (PO-071) |
| AST-005 | خط Poppins (400/600/700) | Font | Brand | `@fontsource/poppins` 5.3.0 ← `resources/fonts/poppins/` | SIL OFL 1.1 (`OFL.txt`) | — | — | النص اللاتيني والأزرار | **Approved** (D-309) |
| AST-006 | خط Noto Kufi Arabic (Light 300 للنص / Medium 500 / Bold 700) | Font | Brand | `@fontsource/noto-kufi-arabic` 5.3.0 (المجموعة العربية) ← `resources/fonts/noto-kufi-arabic/` | SIL OFL 1.1 (`OFL.txt`) | — | — | النص العربي (بديل GE SS Two بنفس الطابع، size-adjust 92%) | **Approved** (D-329) |
| AST-007 | بطاقة المشاركة (Open Graph) 1200×630 | Image | Brand | `tooling/scripts/og-card.mjs` ← `resources/brand/og/og-default-1200x630.png` (من AST-001 + #131313 + سداسي الشعار + SHELTER COFFEE بخط AST-005) | مكونات العلامة المعتمدة فقط | — | — | معاينة الروابط على واتساب وفيسبوك وإنستغرام | **Approved** (D-331) — منشورة: `public/brand/og-default-1200x630.png` |

## ملاحظات Phase 01

- لم يتم تنزيل أو استخدام أي صورة من الموقع القديم. الوصول المباشر للموقع محجوب من بيئة العمل (انظر `docs/phase-01-discovery/00-access-and-method.md`).
- أي صورة يتم اكتشافها في الموقع القديم تُسجَّل هنا كـ`Pending` فقط **بعد** عرضها على الـOwner مع المصدر والحقوق.
- الأصول المطلوبة من الـOwner (Logo الأصلي بصيغة Vector، صور المنتجات، صور الفروع، دليل الهوية إن وجد) مذكورة في `04-content-approval-register.md` كـ`MISSING`.
