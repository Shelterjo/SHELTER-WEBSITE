# 11 — Social Accounts Verification (التحقق من الحسابات الرسمية)

> **القواعد (D-025، D-036):**
> - لا يُعتمد أي حساب. كل الحسابات `PENDING OWNER VERIFICATION` وتُعرض على الـOwner **واحدًا واحدًا** لاحقًا.
> - **Snapchat Place ليس حسابًا اجتماعيًا رسميًا.** الأماكن نُقلت إلى قسم منفصل.
> - **Linktree لا يُستخدم** إذا كان الموقع الجديد يوفر تجربة أفضل مباشرة.
> - لا قرار بتغيير أي Handle قبل النقاش.
>
> **مصادر الأدلة:**
> - **Current Website Reference:** لقطات الـOwner للموقع الحالي (`12` §3). تظهر أيقونات في الـFooter، **لكن وجهات الروابط غير مرئية** حتى نفحص الـDOM بعد AC-01.
> - **Search Evidence:** ملخصات فهرس البحث في Phase 01، وليس فتحًا مباشرًا للصفحات. الأرقام ملخّصة.

## الحسابات المرشحة

| # | Platform | Handle | URL | Evidence | Current Website Reference | Search Evidence | Status |
|---|---|---|---|---|---|---|---|
| S-01 | Instagram | `@sheltercoffeedrive` | `https://www.instagram.com/sheltercoffeedrive/` | الاسم المعروض "SHELTER COFFEE DRIVE"؛ Bio: "أول درايف ثرو للقهوة المختصة في اربد" / "STAY SHELTERED" | ✅ أيقونة Instagram في الـFooter (الرابط غير مرئي) | ظهر في المسح العربي والخارجي (~30K متابع حسب الملخص)؛ ⚠️ لم يظهر في المسح الإنجليزي | `PENDING OWNER VERIFICATION` |
| S-02 | Facebook | `sheltercoffeedrive` | `https://www.facebook.com/sheltercoffeedrive/` | اسم الصفحة "Shelter Coffee Drive"؛ معرّف صفحة محتمل `256499115299768` (دليل findglocal)؛ 96% توصية من 39 (ملخص) | ✅ أيقونة Facebook في الـFooter | عناوين نتائج متعددة | `PENDING OWNER VERIFICATION` |
| S-03 | X (Twitter) | `@ShelterDrive` | `https://x.com/ShelterDrive` | "Shelter Coffee Drive"؛ انضم يونيو 2019 (يتسق مع سنة التأسيس 2019)؛ Bio: "Drive Thru Coffee Service" | ✅ أيقونة X/Twitter في الـFooter | عنوان نتيجة | `PENDING OWNER VERIFICATION` |
| S-04 | Snapchat | `@shelter_coffee` | `https://www.snapchat.com/@shelter_coffee` | الاسم المعروض "SHELTER COFFEE DRIVE" | ✅ أيقونة Snapchat في الـFooter | عنوان نتيجة | `PENDING OWNER VERIFICATION` |
| S-05 | YouTube | `@sheltercoffee` | `https://www.youtube.com/@sheltercoffee` | **دليل ضعيف:** الوصف يشبه نص الدرايف فقط؛ توجد علامات كثيرة بنفس الاسم | ✅ أيقونة YouTube في الـFooter (لا نعرف إن كانت تشير لهذه القناة) | نتيجة واحدة | `PENDING OWNER VERIFICATION` — لا يُفترض رسميًا |
| S-06 | TikTok | — | — | لا يوجد رابط. ملخص واحد ذكر `@sheltercoffeedrive` بدون رابط | ❌ لا أيقونة TikTok في الـFooter | غير مؤكد | `MISSING — OWNER INPUT REQUIRED` |
| S-07 | LinkedIn | — | — | — | ❌ لا أيقونة | لم يُعثر عليه | `MISSING — OWNER INPUT REQUIRED` |
| S-08 | Linktree | `sheltercoffeedrive` | `https://linktr.ee/sheltercoffeedrive` | العنوان: "شلتر كوفي درايف اربد , روابط المنيو و الطلب" | ❌ غير ظاهر في الـFooter | عنوان نتيجة | `PENDING OWNER VERIFICATION` — **لن يُستخدم في الموقع الجديد** (D-036) |

## أماكن Snapchat (Places) — ليست حسابات رسمية (D-036)

تُعامل كقوائم أماكن مثل الأدلة. تُراجع لاحقًا ضمن توحيد بيانات الفروع (NAP)، ولا تُربط في الموقع.

| # | الاسم | الرابط | ملاحظة |
|---|---|---|---|
| P-01 | SHELTER COFFEE DRIVE | `https://www.snapchat.com/place/shelter-coffe-drive/74cd05ca-9207-11e9-83fa-93967fb5e6e2` | خطأ إملائي في الـSlug ("coffe") |
| P-02 | SHELTER COFFEE HOUSE | `https://www.snapchat.com/place/shelter-coffee-house/3875adf0-dca7-11ec-b133-1bba295e4827` | ظهر أيضًا تحت Slug "shelter-coffee-drive" |

## حسابات بنفس الاسم — نعتقد أنها **غير تابعة** لكم (لن تُربط أبدًا)

| المنصة | الحساب | سبب الشك |
|---|---|---|
| Facebook | "Shelter Café" (ID `61569982503664`) | لا دليل على ارتباطه بإربد |
| Facebook / Instagram | "Shelter Cafe" (`@sheltercafe1`) | لا دليل |
| Instagram | `@sheltercoffee_id` · `@thesheltercoffee` · `@shelterbkk` | علامات خارج الأردن (حسب الملخصات) |
| Snapchat | "شيلتر كافيه - SHELTER CAFFE" (مكة) | علامة أخرى |
| أدلة | "شلتر كافية" (الرياض) | علامة أخرى |

## الخطوة التالية
- بعد AC-01 نقرأ روابط أيقونات الـFooter الفعلية من الـDOM ونضيفها لعمود "Current Website Reference".
- ثم نعرض عليك الحسابات واحدًا واحدًا.
