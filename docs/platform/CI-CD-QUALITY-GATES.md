# CI-CD-QUALITY-GATES — بوابة الجودة قبل أي نشر

| البند | القيمة |
|---|---|
| **الغرض** | لا يصل أي تغيير كود من Claude أو أي مطور إلى Production بدون بوابة جودة. **أي فشل P0 = STOP DEPLOY** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` (الأدوات المحلية في `tooling/` تعمل اليوم على الـWireframes) |
| **مرحلة البناء (M36 §6)** | **PHASE 7** (CI/CD، automated tests). الاختبار يبدأ مع كل Feature من **PHASE 1** (M36 §12). خطة المشروع: P06 · P08 · P10 |
| **المتطلبات** | M32 §25 · §38 · §45 · M35 §37 · §38 · M36 §12 · §21 · OPS-039 · OPS-043 · OPS-052 · TEST-001 · TEST-004 · TEST-010 · TEST-011 · TEST-013 · TEST-014 · TEST-019 · TEST-021 · TEST-022 · TOOL-003 · TOOL-006 · INT-001 · INT-004 · G14-TF-13 · G14-TF-14 · I-07 · PO-039 |
| **المراجع الملزمة** | [`tooling/`](../../tooling/) · [`FRONTEND-TOOLING`](../FRONTEND-TOOLING.md) · [`RELEASE-CHECKLIST`](RELEASE-CHECKLIST.md) (تعريف الإنجاز الوحيد) · [`DEPLOYMENT`](DEPLOYMENT.md) · [`VISUAL-REGRESSION`](VISUAL-REGRESSION.md) |

> **أداة واحدة لكل وظيفة.** البوابة تعيد استخدام `tooling/` الموجود: Playwright 1.56.1 (20 عرضًا من `viewports.mjs`) · axe-core/playwright · Lighthouse (`scripts/lighthouse.mjs --assert`) · Sharp · `qa-matrix`.
> أي أداة جديدة تُسجَّل في [`FRONTEND-TOOLING`](../FRONTEND-TOOLING.md) §3 **قبل** تثبيتها (TOOL-006).

## 1. المراحل
| # | المرحلة | الأداة (موجودة ✅ / جديدة 🆕) | فشل P0 عندما |
|---|---|---|---|
| 1 | **CODE** | `composer validate` · Laravel Pint (افتراضي في Laravel) · `composer audit` + `npm audit` · 🆕 فحص Hardcode للبيانات المركزية (OPS-012) · 🆕 فحص أنماط أسرار بسيط | سر في الكود · ثغرة CRITICAL · قيمة أعمال مكتوبة في الكود |
| 2 | **BUILD** | `composer install --no-dev` · Vite · `npm run tokens:css` ✅ · Artifact + checksum + Manifest | فشل البناء · غياب "ملخص للـOwner" |
| 3 | **PHPUnit** | Unit + Feature (SQLite) · الـMigrations على MySQL · **اختبار صلاحية لكل Route في `/dashboard`** | أي فشل |
| 4 | **Playwright** | ✅ Chromium · Firefox · WebKit (`SHELTER_ALL_BROWSERS=1`) · رحلات المستخدم (TEST-014) · النماذج · سيناريوهات فشل الـDashboard (TEST-022) | فشل رحلة أو نموذج أو صلاحية |
| 5 | **A11Y (axe)** | ✅ `@a11y` — WCAG 2.2 AA | أي مخالفة Serious أو Critical |
| 6 | **SEO · Schema · Links** | 🆕 فحص SEO تقني (title · meta · canonical · hreflang · robots · noindex · قواعد الـSitemap) · 🆕 تحقق JSON-LD (الأنواع المسموحة، لا قيم مخترعة) · 🆕 زاحف روابط محلي على النسخة المبنية | رابط أو CTA داخلي مكسور · خطأ Schema في صفحة مفهرسة · noindex/canonical خاطئ في صفحة مفهرسة |
| 7 | **RTL/LTR + 20 عرضًا** | ✅ `@responsive` + `npm run qa:matrix` | Overflow · Clipping · Overlap (RESP-009) · فشل RTL أو LTR |
| 8 | **Visual regression** | ✅ `@visual` + `SHELTER_VISUAL=1` | فرق بصري كبير **غير مراجَع** (بعد التفعيل). **الآن: `SKIPPED — NOT ENABLED`** |
| 9 | **Lighthouse** | ✅ `npm run lighthouse -- --assert` | بوابة أداء لصفحة حرجة (الرئيسية، المنيو، الفروع، صفحة الفرع) |
| 10 | **Staging** | نشر الـArtifact + نفس الاختبارات على Staging (`SHELTER_BASE_URL`) | أي فشل |
| 11 | **موافقة الـOwner** | الـDashboard أو المحادثة ([`DEPLOYMENT`](DEPLOYMENT.md) §4) | رفض أو صمت |
| 12 | **Production** | نشر + تحقق بعد النشر | فشل ← تراجع تلقائي |

**الشدة:**
- **P0** ← توقف. لا استثناء ولا تجاوز.
- **P1** ← يُسمح بالإصدار فقط إذا ذُكرت المشكلة في ملخص الـOwner ووافق عليها صراحة.
- **P2** ← قائمة لاحقة.
- `SKIPPED` أو `NOT RUN` **ليس نجاحًا.** يظهر كما هو في `releases.ci_result`.

## 2. شروط مدمجة في البوابة
- **ملخص للـOwner** في كل PR (قسم إلزامي) ← يُبنى منه `changes_owner`.
- **مراجعة كود** بعد كل Milestone (TEST-019).
- **الإلزام في سكربت النشر نفسه:** يرفض أي Artifact بلا `ci_result` ناجح. لا نعتمد على إعدادات GitHub قد تتطلب خطة مدفوعة للمستودع الخاص.

## 3. خطة التشغيل (للتحكم بالدقائق)
| متى | النطاق |
|---|---|
| **كل PR** | 1–7 على Chromium (20 عرضًا × AR/EN) + Firefox وWebKit على 3 عروض (390 · 820 · 1440) |
| **مرشح إصدار** (قبل Staging) | كل المراحل: 3 محركات × 20 عرضًا × AR/EN + Lighthouse + Visual (عند تفعيله) |
| **أسبوعيًا** | فحص الاعتماديات (OPS-043) |
| **يدويًا قبل كل إصدار كبير** | أجهزة حقيقية (iPhone Safari، Android Chrome) + قارئ شاشة + Edge حيث يلزم (M35 §37–§38، TEST-011) |

- Linux runners فقط (WebKit يعمل على Linux). لا macOS runners.

## 4. GitHub Actions = بند تكلفة/حصة (I-07) — يحتاج موافقتك قبل تفعيل أي Workflow
| الحقل | القيمة |
|---|---|
| **الخدمة** | GitHub Actions (Linux runners) |
| **الغرض** | تشغيل البوابة آليًا على كل PR ومرشح إصدار |
| **الاستهلاك المتوقع** | يُقاس بالدقائق في أول تشغيل حقيقي ويُعرض عليك قبل التفعيل. أكبر مستهلك: Playwright (20 عرضًا × 3 محركات × لغتان) |
| **التكلفة/الحصة** | المستودع الخاص له حصة دقائق شهرية مجانية حسب خطة GitHub، والزائد مدفوع. **الأرقام تُتحقق من خطة حسابكم وقت القرار — لا رقم من عندنا** |
| **البديل المجاني** | تشغيل محلي بنفس الأوامر بواسطة Claude، أو Runner ذاتي |

**الحالة:** `NOT ENABLED — OWNER APPROVAL REQUIRED` (J-4، PO-039).

## 5. البديل المحلي (يعمل اليوم)
```
cd tooling && npm ci
npm run test:e2e            # 20 عرضًا × AR/EN على Chromium
npm run qa:matrix           # يولّد docs/qa/RESPONSIVE-QA-MATRIX.md
npm run lighthouse -- --assert
npm run images:selftest
php artisan test            # بعد وجود التطبيق
```
- محليًا: **Chromium فقط** (لا `playwright install`). Firefox وWebKit = `NOT RUN — LOCAL` في `ci_result`.
- غياب المحركين **فجوة P1 معلنة** في ملخص الإصدار، ولا يُكتب `COMPLETE` قبل تشغيلهما.

## 6. سياسة الاعتماديات (OPS-043، G14-TF-13)
- المصادر المجانية: Dependabot alerts + `composer audit` + `npm audit` (أسبوعيًا).
- تصنيف للـOwner: `GOOD` · `UPDATE AVAILABLE` · `SECURITY UPDATE` · `CRITICAL` ← يظهر في **الجودة ← الأمان** (OPS-035).
- كل تحديث: PR ← البوابة ← Staging ← موافقة ← Production.
- **لا دمج تلقائي لأي تحديث Major، ولا تحديثات تلقائية على Production.**
- ترقية PHP أو MySQL على Cloudways = تغيير بنية: Staging أولًا ثم موافقتك.

## 7. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| المراحل 1–12، والمرحلة 8 معطلة حتى اعتماد التصميم | Unlighthouse للموقع كاملًا قبل الإطلاق (TOOL-017) · sitespeed.io عند الحاجة (TOOL-018) |
| تشغيل محلي إن لم تُعتمد دقائق CI | الـCI الكامل بعد موافقة I-07 |

## 8. البيانات ومكانها في الـDashboard
- **البيانات:** `releases.ci_result` (لكل مرحلة) · `signals` (فشل فحص الاعتماديات الأسبوعي) · التقارير في `tooling/reports/`.
- **الـDashboard:** **النظام ← الإصدارات** (نتيجة كل مرحلة بلغة بسيطة) · **الجودة ← الأمان** (صحة الاعتماديات).

## 9. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| CI-T1 | رابط داخلي مكسور مقصود | المرحلة 6 تفشل، والنشر يتوقف |
| CI-T2 | مخالفة axe Serious مقصودة | المرحلة 5 تفشل |
| CI-T3 | Route في `/dashboard` بلا Policy | المرحلة 3 تفشل |
| CI-T4 | Overflow أفقي على 320px | المرحلة 7 تفشل |
| CI-T5 | Artifact بلا `ci_result` ناجح | سكربت النشر يرفضه |
| CI-T6 | تحديث Major من Dependabot | لا دمج تلقائي، ويُعلَّم للمراجعة |
| CI-T7 | تشغيل محلي بلا Firefox وWebKit | `NOT RUN — LOCAL` ظاهر، ولا `COMPLETE` |

## 10. ما لا يُفعل
- لا تفعيل لأي Workflow قبل موافقة I-07.
- لا Percy ولا Chromatic ولا أدوات SaaS مدفوعة (I-09)، ولا أداة تكرر أداة موجودة.
- لا Load testing ولا زحف عدواني على Production (TEST-024).
- لا اعتبار `SKIPPED` نجاحًا، ولا تجاوز P0.
