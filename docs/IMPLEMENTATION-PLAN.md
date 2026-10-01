# IMPLEMENTATION PLAN — SHELTER COFFEE Website (خطة واحدة للمشروع)

> **الحالة:** `BUILD MODE` (M36 — PLANNING CLOSED، OWNER APPROVED) · **آخر تحديث:** 2026-10-01
>
> **ماذا تغيّر:**
> - خطة البناء الآن هي **مراحل M36 السبع**.
> - الترميز القديم P00–P12 في حقول المتطلبات يبقى للتتبع فقط، وخريطته أدناه.
> - **الحالة الحية** لكل مرحلة ووحدة: [`PROGRESS.md`](PROGRESS.md).
> - **المنصة:** [`ADR-001`](adr/ADR-001-platform.md).
>
> **القاعدة داخل كل مرحلة:** PLAN → BUILD → TEST → FIX → DOCUMENT → COMMIT → VERIFY.
>
> **الأسئلة للـOwner** فقط عند مانع: تجاري، أو قانوني، أو مالي، أو حقيقة غير معتمدة، أو تفويض خارجي، أو تعارض تصميمي كبير (M36 §24 · M38).

## البوابات بعد M36
| النوع | الحالة |
|---|---|
| **اعتمادات المعمارية والـIA والـWireframes** (P02 / P03 / Gate H / Careers Phase 8) | **مراجعة غير مانعة** (READY FOR REVIEW) |
| **الإنتاج:** النشر، وDNS، وتحويلات الإنتاج، وقاعدة الإنتاج، وGoogle الحي | **مانعة**: Dev → Testing → Staging → Final Verification → **موافقة الـOwner** → Production |
| **الخدمات المدفوعة، والحسابات، والصلاحيات (Cloudways/Google/Cloudflare)** | **مانعة**: شرح ثم موافقة |
| **الحقائق التجارية، والوسائط، وملفات الهوية (M-10)، وFranchise Master، والقانوني** | `PENDING OWNER INPUT`: **تمنع النشر وليس البناء** |

## مراحل البناء (M36 §6)
| المرحلة | المحتوى | الترميز القديم | ما يمنعها |
|---|---|---|---|
| **0 — Toolchain** (M37) | الأدوات المجانية (`TOOLCHAIN.md`) + خط الجودة على GitHub | P06 | — |
| **PHASE 1 — Foundation** | المستودع، والمعمارية، والبيئات، ونموذج البيانات، والمصادقة، وصلاحيات Owner فقط، والإعدادات، وMaster Data Hub، والحقائق، والـDesign System، والتوجيه، والعربية/الإنجليزية، وCountry/City/Branch | P05 (البنية) · P06 · P08 | Staging: تطبيق Cloudways (تفويض خارجي) |
| **PHASE 2 — Core Website** | الهيدر والتنقل، والفوتر، والرئيسية، والمنيو، والفروع، وصفحة الفرع، ومن نحن، والتواصل، والأسئلة، والصفحات القانونية والنظامية، والبحث | P02 · P03 · P08 | النصوص والصور غير المعتمدة تبقى مخفية أو Placeholder داخليًا |
| **PHASE 3 — Owner Dashboard** | Command Center، وMaster Data، والصفحات، والمنيو، والفروع، والوسائط، والفعاليات، والحملات، والتجارب، والتقويم، والـSEO، والتحليلات، وصحة الموقع | P04 · P08 | — |
| **PHASE 4 — Business Modules** | التوظيف، والشراكات، والمركز الإعلامي، والجوائز، SHELTER Family، وموظف الشهر، وآراء العملاء، والاستفسارات | P08 | ملفات Franchise Master · المراجعة القانونية (PO-019) |
| **PHASE 5 — Integrations** | GBP، وGSC، وGA4، وGTM، والـSchema، والمزامنة الخارجية، وCloudflare/Cloudways | P09 | **الصلاحيات** (PO-008…013) |
| **PHASE 6 — Quality / Operations** | Publish Guard، ومعاينة الأثر، وحالة المزامنة، وتطابق اللغتين، وحداثة المحتوى، وRUM، والوصولية، والأداء، والأمان، والخصوصية، والنسخ والاستعادة، والإشعارات، والحوادث، ووضع الأمان | P10 · P12 | — |
| **PHASE 7 — Release** | الـCI/CD، والاختبارات، والـVisual regression، والـStaging، وهجرة الروابط القديمة، وخريطة التحويل، وقائمة الإطلاق، والإصدار، والتحقق بعد الإطلاق | P10 · P11 | **موافقة الإنتاج** + الوصول للموقع القديم وGSC |

> **الاختبار مستمر في كل مرحلة**، وليس في النهاية:
> - PHPUnit، وLarastan، وPlaywright (20 عرضًا × AR/EN)، وaxe.
> - Lighthouse CI، وميزانية الحزمة، وGitleaks، وSemgrep.
>
> **تعريف "منجز":** [`RELEASE-CHECKLIST.md`](platform/RELEASE-CHECKLIST.md) (DoD الوحيد).

## خريطة الترميز القديم (P00–P12) ← مراحل M36
| القديم | الآن |
|---|---|
| P00 Governance | **مغلقة** (Planning closed). السجلات تُحدَّث باستمرار مع كل رسالة |
| P01 Discovery | جزء من PHASE 5 و7: الوصول والجرد |
| P02 Menu IA · P03 Site IA · P04 Dashboard architecture | Wireframes مختبرة: **READY FOR REVIEW**. تُبنى في PHASE 2 و3 |
| P05 Brand / Design System | **البنية في PHASE 1.** الطبقة البصرية النهائية بانتظار ملفات الهوية (M-10) |
| P06 Platform (DB-08) | **محسومة:** ADR-001 (PHASE 1) |
| P07 Content & media | مستمرة. غير المعتمد لا يُنشر |
| P08 Build | PHASE 1–4 |
| P09 Google | PHASE 5 |
| P10 QA gate | مستمر + PHASE 6–7 |
| P11 Launch | PHASE 7 |
| P12 Post-launch | بعد PHASE 7 |

## المتطلبات حسب المرحلة (من الـMaster)

| Phase | الاسم | المتطلبات | P0 | P1 | P2/P3 | معلّق على الـOwner |
|---|---|---|---|---|---|---|
| P00 | Governance & Master Source of Truth | 123 | 82 | 33 | 8 | 1 |
| P01 | Discovery completion | 91 | 47 | 37 | 7 | 11 |
| P02 | Menu IA & Wireframes | 52 | 16 | 34 | 2 | 2 |
| P03 | Site-wide IA, Sitemap, URL & SEO architecture | 112 | 31 | 74 | 7 | 12 |
| P04 | Owner Dashboard & CMS architecture | 122 | 68 | 48 | 6 | 2 |
| P05 | Brand, Design System & Visual Design | 67 | 30 | 30 | 7 | 1 |
| P06 | Platform & technical architecture (DB-08) | 61 | 43 | 13 | 5 | 1 |
| P07 | Content & media approval | 78 | 44 | 28 | 6 | 12 |
| P08 | Build on staging (website + CMS + Dashboard V1) | 447 | 252 | 181 | 14 | 0 |
| P09 | Google ecosystem implementation | 88 | 26 | 59 | 3 | 2 |
| P10 | QA gate | 56 | 25 | 29 | 2 | 0 |
| P11 | SEO migration & launch | 22 | 20 | 2 | 0 | 0 |
| P12 | Post-launch & V2 | 25 | 1 | 4 | 20 | 0 |

> القائمة الكاملة لكل مرحلة: عمود Phase في [`IMPLEMENTATION-GAP-ANALYSIS.md`](IMPLEMENTATION-GAP-ANALYSIS.md).
