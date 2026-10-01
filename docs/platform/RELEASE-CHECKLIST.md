# RELEASE-CHECKLIST — تعريف الإنجاز الموحد (Global Definition of Done)

| البند | القيمة |
|---|---|
| **الغرض** | **تعريف إنجاز واحد** لكل صفحة وميزة وشاشة Dashboard وتكامل وإصدار. كل الوثائق الأخرى **تُحيل إليه** ولا تكرره |
| **الحالة** | `SPEC — READY FOR BUILD` · يُطبق من أول Feature |
| **مرحلة البناء (M36 §6)** | **كل المراحل PHASE 1–7.** يُستخدم في كل مرحلة (M36 §7: PLAN → BUILD → TEST → FIX → DOCUMENT → COMMIT → VERIFY)، وصاحبه **PHASE 7** |
| **المتطلبات** | M35 §36 · §37 · §38 · M32 §31 · §55 · M36 §13 · §14 · §19 · §20 · OPS-054 · TEST-002 · TEST-004 · TEST-010 · TEST-011 · TEST-021 · TEST-022 · RESP-001 · RESP-004 · RESP-005 · RESP-007 · RESP-008 · RESP-009 · A11Y-001 · A11Y-003 · A11Y-004 · A11Y-017 · PERF-001 · PERF-017 · PERF-018 · SEO-041 · GSC-010 · GOV-071 · GOV-086 |
| **المراجع الملزمة** | [`tooling/`](../../tooling/) · [`RESPONSIVE-QA-MATRIX`](../qa/RESPONSIVE-QA-MATRIX.md) · [`PERFORMANCE-BUDGET`](../menu-ia/PERFORMANCE-BUDGET.md) · [`CI-CD-QUALITY-GATES`](CI-CD-QUALITY-GATES.md) · [`FINAL-ARCHITECTURE-REVIEW`](../FINAL-ARCHITECTURE-REVIEW.md) §5 (مرجع واحد) |

> **يحل محل** قوائم الإنجاز المتفرقة (الاستجابة، والتوظيف، والشراكات، وM32 §55، وM27، وGOV-086) **كمرجع واحد**. معانيها كلها مدمجة هنا، ولم يُحذف شرط.

## 1. تعريف الإنجاز (DoD)
**لا تُعتبر الصفحة أو الميزة منجزة قبل نجاح كل بند ينطبق عليها.**

| # | البُعد | المعيار | كيف يُتحقق |
|---|---|---|---|
| 1 | **FUNCTION** | يعمل كما في المواصفة. رحلات المستخدم الحقيقية تنجح (TEST-014). **مدمج** مع الأنظمة المرتبطة (Master Data، Audit، الإشارات) | PHPUnit + Playwright |
| 2 | **RESPONSIVE** (Mobile · Tablet · Desktop) | العروض الـ14: 320 · 360 · 375 · 390 · 412 · 430 · 768 · 820 · 1024 · 1280 · 1366 · 1440 · 1536 · 1920 + Landscape (568 · 844 · 932 · 1024 · 1180) + 2560 + **تكبير 200%**. **لا Overflow ولا Clipping ولا Overlap = Bug** (RESP-009) | `@responsive` على 20 مشروعًا (`viewports.mjs`) + تكبير متصفح حقيقي |
| 3 | **RTL / LTR** | العربي RTL والإنجليزي LTR لكل اختبار، و`lang`/`dir` صحيحان | كل اختبار يعمل مرتين |
| 4 | **الإدخال** | Keyboard كامل مع تركيز مرئي ≥ 2px · Touch بأهداف ≥ 44×44px · Mouse | Playwright + فحص يدوي |
| 5 | **ACCESSIBILITY** | WCAG 2.2 AA. **axe: صفر Serious وصفر Critical.** يدويًا: قارئ الشاشة (VoiceOver، TalkBack)، والتباين، والحركة المخفضة، والنماذج والأخطاء، والـModal والـBottom sheet | `@a11y` + قائمة يدوية (A11Y-003) |
| 6 | **PERFORMANCE** | Lighthouse موبايل: Performance ≥ 90 · A11y ≥ 95 · Best Practices ≥ 90 · SEO ≥ 90. **LCP ≤ 2.5s · CLS ≤ 0.1 · TBT ≤ 200ms · INP ≤ 200ms**. ومراقبة حجم الحزمة والصور والخطوط والحركة وسكربتات الطرف الثالث في كل مرحلة (M36 §14) | `npm run lighthouse -- --assert` (مخنوق — TEST-013). **INP:** ميدانيًا من `rum_metrics` (p75) بعد النشر؛ قبله فحص تفاعل في DevTools، وTBT بديل مختبري |
| 7 | **BROWSERS & DEVICES** | Chromium · Firefox · WebKit · Edge حيث يلزم · **iPhone Safari وAndroid Chrome حقيقيان**: سلوك اللمس والتنقل على الهاتف فعليًا، لا المحاكي وحده (M35 §37–§38) | `SHELTER_ALL_BROWSERS=1` في الـCI + فحص يدوي |
| 8 | **VISUAL** | فحص بصري بشري + Visual regression بلا فرق غير مراجَع (بعد تفعيله) | [`VISUAL-REGRESSION`](VISUAL-REGRESSION.md) |
| 9 | **ERROR & EMPTY STATES** | حالة فارغة محترمة، وخطأ واضح بلا Stack trace، و404/500 بالـDesign System، والشبكة تفشل برفق (الهيكل لا ينهار)، وسيناريوهات فشل الـDashboard (TEST-022) | Playwright بسيناريوهات فشل |
| 10 | **SEO & SCHEMA** | title · meta · canonical · hreflang · robots · قواعد الـSitemap. JSON-LD صالح **بلا قيم مخترعة**. لا روابط مكسورة | المرحلة 6 في البوابة |
| 11 | **ANALYTICS** | الأحداث حسب خطة القياس فقط، **بلا تكرار**، ومختبرة بـDebugView على Staging | Analytics Validation Report (TEST-016) |
| 12 | **SECURITY** | صلاحيات من الخادم (اختبار Policy لكل Route إداري) · CSRF · Rate limit · لا أسرار في الحزمة · ترويسات الأمان · رفع ملفات آمن | PHPUnit + فحص الحزمة |
| 13 | **PRIVACY** | لا PII في السجلات أو التحليلات. التصنيف (PUBLIC / INTERNAL / CONFIDENTIAL / SENSITIVE) محترم في التخزين والعرض والتصدير | مراجعة + اختبار |
| 14 | **CONTENT APPROVAL** | لا يُنشر إلا الحقائق `APPROVED` أو `VERIFIED` والصور المعتمدة. Publish Guard بلا BLOCKING | Publish Guard + سجل الحقائق |
| 15 | **AUDIT & BACKUP** | الإجراءات المهمة تكتب في `audit_logs`. **أثر الميزة على النسخ الاحتياطي مراجَع** (OPS-054) | مراجعة |
| 16 | **DOCS** | الوثيقة المعنية محدثة، وسلسلة التتبع كاملة: Requirement → Decision → Design → Code → Test | مراجعة المصفوفة |

**على مستوى الإطلاق** (GOV-086)، إضافة لما سبق:
- موافقات: Content · Images · Design · Mobile · Desktop.
- **Staging Tested + Owner Approved.**
- Google لا يُظهر مشاكل مهمة (GSC-010).

## 2. مفردات الحالة (M36 §19–§20)
| الحالة | المعنى |
|---|---|
| `NOT STARTED` | لم يبدأ |
| `IN PROGRESS` | قيد العمل |
| `BLOCKED` | متوقف على شيء محدد يُذكر (وصول، قرار، ملف) |
| `READY FOR REVIEW` | منفذ وينتظر مراجعة أو قرار الـOwner |
| `TESTED` | **كل** بنود الـDoD المنطبقة نجحت على Staging (آلي + يدوي) |
| `COMPLETE` | `TESTED` + الوثائق + موافقة الـOwner حين تلزم. للإصدارات: `LIVE` بعد التحقق |
| **`IMPLEMENTED — NOT YET VERIFIED`** | **إلزامي** لأي شيء كُتب ولم يُختبر |

**قواعد:**
- ممنوع قول DONE أو COMPLETE أو FROZEN بلا Build + Test + Verify (M36 §20).
- `PASS ⁽ᵃ⁾` في مصفوفة الاستجابة (الآلي فقط) **ليس `TESTED`** حتى يكتمل اليدوي.
- بند `SKIPPED` أو `NOT RUN` يمنع `COMPLETE`، ويُذكر صراحة في ملخص الـOwner.

## 3. أين تُسجَّل النتيجة؟
| المستوى | السجل |
|---|---|
| الصفحة × العرض | [`RESPONSIVE-QA-MATRIX`](../qa/RESPONSIVE-QA-MATRIX.md) (مولّد بـ`npm run qa:matrix`) |
| الإصدار | `releases.ci_result` لكل مرحلة + ملخص الـOwner ([`DEPLOYMENT`](DEPLOYMENT.md)) |
| المتطلب | [`REQUIREMENTS-TRACEABILITY-MATRIX`](../REQUIREMENTS-TRACEABILITY-MATRIX.md) |
| الوحدة أو المرحلة | لوحة التقدم بالمفردات أعلاه (M36 §19) |

## 4. القائمة المختصرة قبل كل طلب موافقة
```
□ الوظيفة والرحلات ناجحة ومدمجة            □ 20 عرضًا × AR/EN بلا Overflow/Clipping/Overlap
□ Keyboard + Touch + تكبير 200%              □ axe = 0 Serious/Critical + فحص يدوي
□ Lighthouse ضمن الميزانية (الصفحات الحرجة)  □ Chromium/Firefox/WebKit (أو NOT RUN معلن)
□ حالات الخطأ والفراغ                         □ SEO/Schema/روابط سليمة
□ Analytics بلا تكرار (إن وُجدت أحداث)       □ الصلاحيات من الخادم + لا أسرار
□ لا PII + التصنيف محترم                      □ محتوى وحقائق وصور معتمدة فقط
□ Audit + أثر النسخ الاحتياطي                 □ الوثائق والتتبع محدثة
□ الحالة مكتوبة بصدق (IMPLEMENTED — NOT YET VERIFIED إن لزم)
```

## 5. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| كل البنود 1–16. البند 8 (Visual regression) **معطل حتى اعتماد التصميم**، ويُعلن `SKIPPED` | Unlighthouse للموقع كاملًا قبل الإطلاق (TEST-005) |

## 6. البيانات ومكانها في الـDashboard
- **البيانات:** `releases` (`ci_result`) · `audit_logs` · `rum_metrics` (INP الميداني).
- **الـDashboard:** **النظام ← الإصدارات** يعرض نتيجة الـDoD لكل إصدار بلغة بسيطة ("الموبايل ✅ · الوصولية ✅ · الشكل ⏸").

## 7. اختبارات القبول (لهذه الوثيقة)
| # | الاختبار | المتوقع |
|---|---|---|
| DOD-T1 | ميزة بلا اختبار يدوي للوصولية | حالتها لا تتجاوز `READY FOR REVIEW` |
| DOD-T2 | إصدار فيه مرحلة `NOT RUN` | لا يُكتب `COMPLETE`، والملخص يذكرها |
| DOD-T3 | وثيقة جديدة تعرّف DoD خاصًا | تُرفض في المراجعة وتُحال إلى هنا |

## 8. ما لا يُفعل
- لا DoD ثانٍ في أي وثيقة. الوثائق تُحيل هنا.
- لا اعتبار "يعمل" أو "يبدو جيدًا" إنجازًا (GOV-086).
- لا مطاردة رقم Lighthouse على حساب تجربة المستخدم (PERF-018).
