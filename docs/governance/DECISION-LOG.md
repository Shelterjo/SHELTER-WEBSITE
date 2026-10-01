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

## قرارات مفتوحة (PROPOSED — بانتظار الـOwner)

القائمة التفصيلية في [`../phase-01-discovery/05-decisions-before-design.md`](../phase-01-discovery/05-decisions-before-design.md).
