# BRANCH DATA SYNC CHECK (عملية مزامنة بيانات الفروع)

> ⚠️ **محدّث بـM33 (2026-10-01) — المرجع الآن [`MASTER-DATA-HUB`](../MASTER-DATA-HUB.md) §10–§12:**
> - **المصدر = SHELTER MASTER DATA HUB.** GBP والموقع والـSchema **قنوات** تُقارن بالـMaster. **D-048 `SUPERSEDED`**؛ **D-047** يبقى فقط لتقييم القيم غير المعتمدة (MDH-002، G15-CF-01).
> - **الفحص اليدوي (D-049)** يصبح بعد البناء **OUT-OF-SYNC DETECTOR آليًا** (MDH-029، P0): بعد كل نشر + يوميًا. **MON-013** (فحص دوري مستقبلي P2) `SUPERSEDED` بـMDH-029. تشغيل SYNC-001 الاستكشافي اليدوي يبقى (بانتظار PO-009) (G15-CF-08).
> - **التغيير الخارجي لا يُعتمد تلقائيًا أبدًا:** يُعرض CONFLICT DETECTED (M38 §7) / EXTERNAL CHANGE DETECTED ← قرار الـOwner.
> - **مفردات الحالة الجديدة** (M33 §10): `SYNCED` · `PENDING` · `FAILED` · `NOT SUPPORTED` · `MANUAL ACTION REQUIRED` · `OUT OF SYNC`. **تحل محل** مفردات التصنيف القديمة أدناه (تبقى للتاريخ):
>
> | القديم (D-049) | الجديد (M33 §10) |
> |---|---|
> | `MATCH` | `SYNCED` |
> | `MISMATCH — GOOGLE OUTDATED` | `OUT OF SYNC` (ثم `PENDING` عند إعادة الدفع) |
> | `MISMATCH — WEBSITE OUTDATED` | خلل أو Cache قديم في قناة Website ← `OUT OF SYNC` للموقع (الموقع مولّد من الـMaster) |
> | `CONFLICT — OWNER REVIEW REQUIRED` | `OUT OF SYNC` + **CONFLICT DETECTED / EXTERNAL CHANGE DETECTED** ← Keep Master · Adopt · Review |
> | `MISSING` | قيمة الـMaster `MISSING` (حالة Fact) |

> **D-049** · السياسة §4، §7، §8، §9، §11 · **آخر تحديث:** 2026-10-01

## الهدف
ضمان أن **DRIVE** و**HOUSE** (وأي فرع مستقبلي) يحملان **نفس المعلومة** في كل مكان:

```
Google Business Profile  ↔  Website (Branch page + Contact page + Branch card)  ↔  Schema  ↔  Owner-approved data
```

## الحقول المقارنة
Branch name · Address · Phone · Opening hours · Special hours · Maps URL · Coordinates · Website URL · Business category (+ Business status).

## متى تُشغَّل
| الحدث | إلزامي |
|---|---|
| أثناء Discovery (التشغيل الأول) | ✅ عند وصول بيانات GBP (G-01، G-02) |
| قبل Launch | ✅ |
| بعد Launch | ✅ (Day 1 و Day 7) |
| تغيير ساعات العمل / ساعات خاصة (رمضان، عيد، عطلة، إغلاق مؤقت) | ✅ |
| تغيير رقم هاتف | ✅ |
| نقل فرع / تغيير اسم فرع / إضافة فرع | ✅ |
| أي تغيير على GBP | ✅ |
| دوري (مستقبلًا — §45 من السياسة) | مقترح: شهري + قبل رمضان والأعياد |

## طريقة العمل
1. **جمع:**
   - GBP: رابط ولقطات الآن، و**قراءة فقط** مستقبلًا.
   - الموقع: صفحة الفرع وصفحة التواصل وبطاقة الفرع.
   - الـSchema المنشورة.
   - سجل بيانات الـOwner المعتمدة (`DECISION-LOG` + السجل).
2. **مقارنة** كل حقل بين المصادر الأربعة، مع تطبيع الصيغ: الأرقام بصيغة دولية، والساعات بنظام 24 ساعة بتوقيت الفرع.
3. **تصنيف** كل حقل _(المفردات التالية تاريخية — التشغيل بمفردات M33 في البانر أعلاه)_:

| الحالة | المعنى |
|---|---|
| ✅ `MATCH` | متطابق في كل المصادر |
| ⚠️ `MISMATCH — WEBSITE OUTDATED` | Google والـOwner متفقان والموقع مختلف ← اقتراح تحديث الموقع (بموافقة) |
| ⚠️ `MISMATCH — GOOGLE OUTDATED` | الـOwner والموقع متفقان وGoogle مختلف ← **اقتراح** تحديث GBP (Proposal → Owner Approval → Change — §44) |
| 🔴 `CONFLICT — OWNER REVIEW REQUIRED` | الـOwner غير مؤكد أو المصادر الثلاثة مختلفة ← سؤال الـOwner |
| ⬜ `MISSING` | غير متوفر في مصدر أو أكثر |

4. **تقرير** بالقالب أدناه: Source A · Source B · Difference · Recommended correction.
5. **لا تغيير تلقائي** في أي مكان (Production أو Google).

## قالب التقرير

| الفرع | الحقل | Owner-approved | GBP | Website | Schema | الحالة | الفرق | التصحيح المقترح |
|---|---|---|---|---|---|---|---|---|
| DRIVE | Opening hours (Fri) | 08:00–02:00 | … | … | … | … | … | … |

## سجل التشغيلات
| # | التاريخ | المناسبة | النتيجة | الرابط |
|---|---|---|---|---|
| SYNC-001 | — | Discovery — التشغيل الأول | ⏳ بانتظار بيانات GBP (G-01، G-02) | — |

## في الموقع الجديد — محسوم بـM33 ([`MASTER-DATA-HUB`](../MASTER-DATA-HUB.md))
- مصدر واحد لبيانات الفرع داخل الـCMS، وتُولّد منه صفحة الفرع وبطاقته وصفحة التواصل والـSchema. لا إدخال يدوي مكرر.
- حقل "رابط GBP الرسمي" لكل فرع ← معرّف الموقع في `external_references`.
- حقل "آخر Sync" لكل فرع ← `channel_sync_states` (آخر مزامنة · آخر نجاح · الحالة · الخطأ).
