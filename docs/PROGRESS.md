# PROGRESS — لوحة تقدم المشروع

> **الهدف (M36 §19):** أن تعرف دائمًا:
> - ما الذي انتهى؟
> - ما الذي أعمل عليه؟
> - ما الذي بقي؟
> - هل يوجد Blocker؟
> - هل يوجد قرار مطلوب منك؟
>
> **الحالات:** NOT STARTED · IN PROGRESS · BLOCKED · READY FOR REVIEW · TESTED · COMPLETE.
> **COMPLETE** = بُني واختُبر وتحقق. **`IMPLEMENTED — NOT YET VERIFIED`** لأي شيء لم يُختبر بعد.
>
> **آخر تحديث:** 2026-10-01

## الملخص
| البند | الحالة |
|---|---|
| **التخطيط** | CLOSED (M36) |
| **المرحلة الحالية** | **PHASE 2 — Core Website** (IN PROGRESS). PHASE 1 **COMPLETE**، والـStaging الفعلي ينتظر Cloudways (PO-064) |
| **اختبارات التطبيق** | **164 PHPUnit** · Vitest 19 · Storybook: آخر تشغيل كامل 521 ناجحًا + الإصلاح الوحيد مختبر (Header عند 360) + قصص بطاقة التواصل · Larastan المستوى 8 بلا أخطاء · Pint · Semgrep · Gitleaks |
| **CI** | `.github/workflows/quality.yml` يعمل على GitHub (سرعة + أمان + بناء) |
| **Blockers للعمل المحلي** | لا يوجد |
| **Blockers للـStaging والإطلاق** | - تطبيق Cloudways للـStaging (PO-064).<br>- ملفات الهوية (M-10).<br>- الوصول إلى Google وCloudflare (PO-011…013). |
| **الصلاحيات (D-308)** | خطوات [`ACCESS-SETUP`](platform/ACCESS-SETUP.md):<br>1. ✅ **الشبكة:** متحقق منها، وأول جرد مباشر للموقع القديم في [`24-live-site-crawl`](phase-01-discovery/24-live-site-crawl-2026-10-01.md).<br>2. ✅ **Cloudflare:** Token قراءة كـAPI credential، متحقق منه 2026-10-01.<br>3. ⏳ **Google:** حساب خدمة للقراءة عبر GitHub Secrets.<br>4. ⏳ **Cloudways Staging** |
| **قرار مطلوب منك الآن** | - **PO-071:** ترخيص خط GE SS Two (يمنع الإطلاق فقط).<br>- **PO-066:** ملف "Menu List" ([`23-menu-list-reconciliation`](phase-01-discovery/23-menu-list-reconciliation.md)).<br>- Cloudflare: PO-070 · PO-072 · PO-073 **نُفذت** (D-311…D-313). الباقي (Full strict · DNSSEC) مع Cloudways في النهاية (D-314) |

## PHASE 1 — Foundation
| الوحدة | الحالة | الدليل |
|---|---|---|
| تنظيف المستودع + هيكل Laravel 13 | **TESTED** | `composer.json`، `package.json`، CI |
| المعمارية الملزمة | **COMPLETE** | [`PLATFORM-ARCHITECTURE`](architecture/PLATFORM-ARCHITECTURE.md) · [`ADR-001`](adr/ADR-001-platform.md) |
| الأدوات (M37) | **TESTED** | [`TOOLCHAIN.md`](TOOLCHAIN.md): PHPUnit · Larastan · Pint · TS strict · ESLint · Prettier · Vitest · Knip · Storybook · Gitleaks · Semgrep · Trivy (FULL) |
| البيئات | IMPLEMENTED — NOT YET VERIFIED | `.env.example` + حارس Staging (مصادقة HTTP + noindex) مختبر محليًا. **Staging الفعلي BLOCKED** (PO-064) |
| نواة المنصة: Audit واحد، والإصدارات، والإعدادات، وFeature flags، وsignals، والأرقام المرجعية، وسجل المهام المجدولة | **TESTED** | `tests/Feature/Core/*` |
| تصنيف البيانات (كل جدول مصنف) | **TESTED** | `config/data_inventory.php` + DC-T01 |
| المصادقة: Owner فقط + TOTP إلزامي + رموز استرداد + إعادة تأكيد | **TESTED** | `tests/Feature/Auth/*` (16 اختبارًا) |
| Master Data Hub: السوق ← الدولة ← المدينة ← الفرع، والساعات والاستثناءات، والتواصل حسب النية | **TESTED** | البيانات المعتمدة فقط (D-018، D-020، D-057، D-058). الناقص NULL + Fact |
| سجل الحقائق (Fact Registry) | **TESTED** | دورة الحياة، واعتماد الـOwner فقط، وكشف التعديل غير المعتمد، وانتهاء التحقق |
| أولوية الساعات: Emergency > Temporary > Special/Holiday > Regular، مع الدوام بعد منتصف الليل | **TESTED** | `HoursResolverTest` |
| بيانات المنيو الرئيسية (v1.0: 191 صنفًا) | **TESTED** | `MenuMasterDataTest`: الوراثة، والتجاوز لكل فرع، وReset to Master، وتاريخ الأسعار |
| التوجيه + AR/EN + Canonical + hreflang + الـHeaders الأمنية + robots | **TESTED** | `tests/Feature/Http/*` |
| الـDesign System: Tokens + مكونات Blade + Storybook | **TESTED** · **الهوية من الموقع القديم مطبقة (D-309)** | - 36 مكونًا `x-ui.*` و143 قصة Storybook.<br>- 51 اختبار PHP.<br>- `ds-gate`: 0 مخالفات.<br>- اختبار Storybook: **432/432** (axe + التمدد + AR/EN × 360/768/1280).<br>- الحزم: site.css 5.8 KB وdashboard.css 6.4 KB.<br><br>الهوية: من الموقع القديم (D-309)، والخط العربي ينتظر الترخيص (PO-071) |

## PHASE 2 — Core Website (M40)
> الترتيب (D-314): كل صفحات الموقع أولًا، وCloudways وCloudflare في النهاية. كل صفحة تُفحص على 320…1920 بالعربي والإنجليزي، بلا تمدد أفقي وبلا أخطاء Console.

| الوحدة | الحالة | الدليل |
|---|---|---|
| الهيكل: Header (يقيس عرضه بنفسه — Container query) · Drawer · Footer · 404/500 | **TESTED** | `PagesTest` · `LocationsPagesTest` · Storybook (Header 360 مصلح) |
| الرئيسية (Hero + الفروع بالحالة الحية) | **TESTED** | `LocationsPagesTest` · صور 390/1440 |
| الفروع + صفحة كل فرع (الساعات، الحالة الآن، JSON-LD بالمعتمد فقط) | **TESTED** | `LocationsPagesTest` (8 اختبارات) |
| المنيو: بحث عربي/إنجليزي، اختيار الفرع، التفاصيل (Bottom sheet / Modal) مع زر Back | **TESTED** | `MenuPageTest` (8) · `search.test.ts` (7) · مسار المتصفح |
| التواصل حسب النية `/ar/contact/` (D-059، D-065، D-071، CT-06) | **TESTED** | `ContactPageTest` (5) · صور 390/1440 · البريد مخفي حتى D-035 |
| صفحات المحتوى: من نحن · الأسئلة الشائعة · الخصوصية · الشروط (نموذج `pages` · `page_sections`) | **TESTED** (القالب والقواعد) · المحتوى **PENDING OWNER INPUT** (PO-017 · PO-034 · PO-019) | `ContentPagesTest` (5): 404 حتى النشر باللغتين، لا نص AI غير مؤكد، لا جدولة مستقبلية، FAQPage من المنشور فقط، الروابط في الـFooter تظهر فقط بعد النشر. الصور بنص تجريبي محلي حُذف بعدها |
| البحث · الفعاليات | NOT STARTED | — |

## إغلاق المهام السابقة
| المهمة | الحالة |
|---|---|
| المراجعة المعمارية (M35) · ADR-001 · قاعدة التوضيح (M38) · دمج M32–M38 في السجلات · الوثائق التشغيلية الـ39 | **COMPLETE** |
| Wireframes الشراكات والـDashboard + توحيد المنيو والتوظيف على الـTokens | **TESTED**: المصفوفة الكاملة **2,617 ناجحًا · 0 فشل · 0 متقلب**.<br>المجموعات: التوظيف 736 · الشراكات 850 · الـDashboard 575 · المنيو 184 · الاستجابة 272.<br>الـ863 المتخطاة **مقصودة**: فحوص بنية تعمل مرة واحدة على عرض واحد، وعناصر خاصة بعرض معين.<br>**READY FOR REVIEW**. تدقيق التصميم: 0 مخالفات خط وRadius ومسافات، والمكونات المكررة 0. |

## المراحل التالية
| المرحلة | الحالة | ما ينتظر منك |
|---|---|---|
| **2 — Core Website** | **IN PROGRESS** (M40) — الهيكل والرئيسية والفروع والمنيو والتواصل **TESTED**؛ صفحات المحتوى قيد البناء (التفاصيل أعلاه) | قرارات المنيو PO-066…069 (عند النشر) · النصوص والصور (MEDIA PENDING OWNER APPROVAL) · ترخيص الخط PO-071 |
| **3 — Owner Dashboard** | NOT STARTED | — |
| **4 — Business Modules** | NOT STARTED | ملفات Franchise Master · المراجعة القانونية (PO-019) |
| **5 — Integrations** | NOT STARTED → **BLOCKED** على الصلاحيات | PO-009…013 (PO-008 ✅) |
| **6 — Quality / Operations** | NOT STARTED | — |
| **7 — Release** | NOT STARTED | **موافقة الإنتاج** |
