# PROGRESS — لوحة تقدم المشروع

> **الهدف (M36 §19):** أن تعرف دائمًا:
> - ما الذي انتهى؟
> - ما الذي أعمل عليه الآن؟
> - ما الذي بقي؟
> - هل يوجد Blocker؟
> - هل يوجد قرار مطلوب منك؟
>
> **الحالات:**
> - **NOT STARTED:** لم يبدأ.
> - **IN PROGRESS:** قيد العمل.
> - **BLOCKED:** متوقف على شيء خارجي.
> - **READY FOR REVIEW:** جاهز لمراجعتك.
> - **TESTED:** مختبر.
> - **COMPLETE:** بُني واختُبر وتحقق.
> - **`IMPLEMENTED — NOT YET VERIFIED`:** أي شيء لم يُختبر بعد.
>
> **آخر تحديث:** 2026-10-01

## الملخص
| البند | الحالة |
|---|---|
| **التخطيط** | **CLOSED** (M36) |
| **إغلاق المهام المفتوحة قبل البناء** | IN PROGRESS: دمج M32–M38، و39 وثيقة تشغيلية، وتوحيد الـWireframes |
| **أعمل الآن على** | (1) إغلاق المهام. (2) إعداد الـToolchain (M37) + هيكل التطبيق. ثم PHASE 1 |
| **Blockers للبناء المحلي** | لا يوجد |
| **Blockers للـStaging والإطلاق** | إنشاء تطبيقي Cloudways (Staging/Production) أو منح الصلاحية · ملفات الهوية (M-10) · الحقائق غير المعتمدة · الوصول إلى Google وCloudflare |

## إغلاق المهام قبل البناء
| المهمة | الحالة |
|---|---|
| المراجعة المعمارية النهائية (M35، 17 بندًا) — `FINAL-ARCHITECTURE-REVIEW.md` | READY FOR REVIEW |
| قرار المنصة — `adr/ADR-001-platform.md` | COMPLETE (قرار تقني بتفويض M36 §24) |
| المعمارية الملزمة — `architecture/PLATFORM-ARCHITECTURE.md` | COMPLETE |
| تدقيق الأدوات (M37) — `TOOLCHAIN.md` | READY FOR REVIEW |
| قاعدة التوضيح (M38) — `CLAUDE.md` | COMPLETE |
| دمج M32 (OPS) في السجلات | IN PROGRESS (المجموعة جاهزة، وإعادة التوليد بانتظار M33–M36) |
| دمج M33 · M34 · M35 · M36 | IN PROGRESS |
| الوثائق التشغيلية (19 من M32 + 20 من M35) — `docs/platform/` | IN PROGRESS |
| Wireframes الشراكات · الـDashboard · توحيد المنيو والتوظيف على الـTokens | IN PROGRESS |
| إعادة توليد السجلات (Master Requirements، القرارات، التعارضات، المعلّق، التتبع، الفجوات، الخطة) | NOT STARTED (بعد اكتمال الدمج) |

## مراحل البناء (M36 §6)
| المرحلة | الوحدة | الحالة | ملاحظات / Blocker |
|---|---|---|---|
| **0 — Toolchain** | الأدوات المجانية + CI | IN PROGRESS | الأدوات المدفوعة أو التي تحتاج حسابًا: لا شيء |
| **1 — Foundation** | تنظيف المستودع · المعمارية · البيئات | NOT STARTED | Staging يحتاج تطبيق Cloudways |
| | قاعدة البيانات ونموذج البيانات | NOT STARTED | |
| | المصادقة + صلاحيات Owner فقط | NOT STARTED | |
| | الإعدادات العامة | NOT STARTED | |
| | Master Data Hub + سجل الحقائق | NOT STARTED | الحقول غير المعتمدة تبقى `PENDING OWNER INPUT` |
| | الـDesign System (Tokens + المكونات + Storybook) | NOT STARTED | **القيم البصرية** (الخطوط والألوان) بانتظار M-10 |
| | التوجيه + العربية/الإنجليزية + Country/City/Branch | NOT STARTED | |
| **2 — Core Website** | الهيدر والفوتر، والرئيسية، والمنيو، والفروع، وصفحة الفرع، ومن نحن، والتواصل، والأسئلة، والصفحات القانونية، والبحث | NOT STARTED | محتوى "من نحن" والأسئلة والقانوني: `PENDING OWNER INPUT` |
| **3 — Owner Dashboard** | Command Center، وMaster Data، والصفحات، والمنيو، والفروع، والوسائط، والفعاليات، والحملات، والتجارب، والتقويم، والـSEO، والتحليلات، وصحة الموقع | NOT STARTED | |
| **4 — Business Modules** | التوظيف، والشراكات، والمركز الإعلامي، والجوائز، SHELTER Family، وموظف الشهر، وآراء العملاء، والاستفسارات | NOT STARTED | شروط الشراكات وملفات Franchise Master: `PENDING OWNER INPUT` |
| **5 — Integrations** | GBP، وGSC، وGA4، وGTM، والـSchema، والمزامنة، وCloudflare/Cloudways | NOT STARTED | **BLOCKED** على الصلاحيات (PO-008…013) |
| **6 — Quality / Operations** | Publish Guard، ومعاينة الأثر، وحالة المزامنة، وتطابق اللغتين، وحداثة المحتوى، وRUM، والوصولية، والأداء، والأمان، والخصوصية، والنسخ والاستعادة، والإشعارات، والحوادث، ووضع الأمان | NOT STARTED | |
| **7 — Release** | الـCI/CD، والاختبارات، والـVisual regression، والـStaging، وهجرة الروابط القديمة، وخريطة التحويل، وقائمة الإطلاق، والإصدار، والتحقق بعد الإطلاق | NOT STARTED | Staging + الوصول للموقع القديم وGSC |

## قرارات مطلوبة منك الآن
لا يوجد قرار يوقف العمل الحالي. الأسئلة تُطرح فقط عند الوصول لنقطة لا يمكن تجاوزها (M38)، بالتنسيق الإلزامي:
- المعلومة المطلوبة
- سبب الحاجة
- الخيارات
- التوصية
