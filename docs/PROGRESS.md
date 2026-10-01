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
| **المرحلة الحالية** | **PHASE 1 — Foundation** (IN PROGRESS) |
| **اختبارات التطبيق** | **87 PHPUnit** ناجحة · Larastan المستوى 8 بلا أخطاء · Pint · Semgrep · Gitleaks |
| **CI** | `.github/workflows/quality.yml` يعمل على GitHub (سرعة + أمان + بناء) |
| **Blockers للعمل المحلي** | لا يوجد |
| **Blockers للـStaging والإطلاق** | - تطبيق Cloudways للـStaging (PO-064).<br>- ملفات الهوية (M-10).<br>- الوصول إلى Google وCloudflare (PO-008…013). |
| **قرار مطلوب منك الآن** | **PO-066:** ملف "Menu List" الذي أرسلته (التفاصيل في [`23-menu-list-reconciliation`](phase-01-discovery/23-menu-list-reconciliation.md)) |

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
| الـDesign System: Tokens + مكونات Blade + Storybook | **IN PROGRESS** | CSS والأيقونات جاهزة، والمكونات والقصص قيد البناء. **القيم البصرية النهائية BLOCKED** (M-10) |

## إغلاق المهام السابقة
| المهمة | الحالة |
|---|---|
| المراجعة المعمارية (M35) · ADR-001 · قاعدة التوضيح (M38) · دمج M32–M38 في السجلات · الوثائق التشغيلية الـ39 | **COMPLETE** |
| Wireframes الشراكات والـDashboard + توحيد المنيو والتوظيف على الـTokens | IN PROGRESS: إعادة تشغيل اختبارات المصفوفة الكاملة بعد إعادة تشغيل الحاوية |

## المراحل التالية
| المرحلة | الحالة | ما ينتظر منك |
|---|---|---|
| **2 — Core Website** | NOT STARTED | قرارات المنيو PO-066…069 (عند نشر المنيو) · النصوص والصور |
| **3 — Owner Dashboard** | NOT STARTED | — |
| **4 — Business Modules** | NOT STARTED | ملفات Franchise Master · المراجعة القانونية (PO-019) |
| **5 — Integrations** | NOT STARTED → **BLOCKED** على الصلاحيات | PO-008…013 |
| **6 — Quality / Operations** | NOT STARTED | — |
| **7 — Release** | NOT STARTED | **موافقة الإنتاج** |
