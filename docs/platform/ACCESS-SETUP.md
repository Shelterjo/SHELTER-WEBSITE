# ACCESS SETUP — منح الصلاحيات بأقل صلاحية (Cloudways · Cloudflare · Google)

| البند | القيمة |
|---|---|
| **الغرض** | خطوات دقيقة يمنح بها الـOwner الصلاحيات **بأقل صلاحية ممكنة**، **وبلا أي سر في المحادثة أو في Git** |
| **الحالة** | - الـOwner فوّض الإعداد (D-308).<br>- **الخطوة 1 (الشبكة) منفذة ومتحقق منها** في 2026-10-01.<br>- الخطوات 2–4 تنتظر |
| **البنود المرتبطة** | PO-008…013 · PO-046 · PO-064 · AC-RULE-04 · D-041 · D-051 · D-308 · INFRA-046 · INTEGRATION-REGISTRY · ENVIRONMENTS |

## القواعد
1. **لا كلمات مرور ولا مفاتيح في المحادثة أبدًا.**
2. **لا أسرار في `Environment variables` الخاصة ببيئة التطوير.** النافذة نفسها تحذّر أنها ظاهرة لكل من يستخدم البيئة، وأي أمر في الجلسة يقرؤها (INFRA-046). كل سر يذهب إلى واحد من ثلاثة أماكن:

| المكان | لماذا | لأي شيء |
|---|---|---|
| **API credentials** في إعدادات البيئة (**Add credential**) | - المفتاح يبقى **خارج** الجلسة.<br>- الـProxy يضيفه للطلب تلقائيًا للنطاق المحدد فقط.<br>- **لا أرى قيمته أبدًا**، ولا يظهر في المتغيرات ولا في الملفات | APIs بمفتاح Bearer: **Cloudflare** |
| **GitHub Actions secrets** (المستودع ← Settings ← Secrets and variables ← Actions) | - مشفرة.<br>- تُستخدم داخل الـCI فقط.<br>- لا تُطبع في السجلات | - النشر إلى Cloudways عبر SSH (الجلسة تعمل عبر HTTPS فقط).<br>- مفتاح حساب خدمة Google (يحتاج توقيعًا، فلا يصلح كـBearer ثابت) |
| **`.env` على السيرفر** (Cloudways) | سر يستخدمه التطبيق نفسه وقت التشغيل | تكاملات الـDashboard في PHASE 5 |

3. **قراءة فقط أولًا.** أي كتابة تحتاج موافقتك في وقتها، كل واحدة على حدة: Google، وDNS، والـCache، والـProduction.
4. **لا Global API Key لـCloudflare، ولا Cloudways API Key** (AC-RULE-04).
5. **كل صلاحية تُسجّل** في [`INTEGRATION-REGISTRY.md`](INTEGRATION-REGISTRY.md): ماذا، ولماذا، ومتى تنتهي.

## 1) Network access — ✅ منفذ (2026-10-01)
1. افتح إعدادات البيئة: **Edit cloud environment**.
2. **Network access** ← **Custom**.
3. في **Allowed domains**، سطر لكل نطاق:
   ```
   shelterjo.com
   www.shelterjo.com
   shop.shelterjo.com
   ```
4. فعّل **Also include default list of common package managers**. بدونه يتوقف Composer وnpm.
5. **Save changes**.

| تحقق 2026-10-01 | النتيجة |
|---|---|
| `www.shelterjo.com` · `shelterjo.com` · `shop.shelterjo.com` | متاحة. الجرد في [`24-live-site-crawl`](../phase-01-discovery/24-live-site-crawl-2026-10-01.md) |
| `api.cloudflare.com` | متاح (ويُفتح تلقائيًا مع الـAPI credential) |
| `*.googleapis.com` | متاح ضمن القائمة الافتراضية |
| Packagist · npm · GitHub | تعمل |

> **ملاحظة:** نطاقات الـAPI credentials تُفتح تلقائيًا، فلا تُضاف إلى Allowed domains.

## 2) Cloudflare — API Token محدود (قراءة) ← **الخطوة التالية**
**أ. إنشاء الـToken** (في Cloudflare):
1. **My Profile ← API Tokens ← Create Token ← Create Custom Token**.
2. **Token name:** `shelter-readonly`.
3. **Permissions** (كل سطر Zone ثم الصلاحية ثم **Read**):

| النوع | الصلاحية |
|---|---|
| Zone → **Zone** | Read |
| Zone → **DNS** | Read |
| Zone → **Zone Settings** | Read |
| Zone → **Cache Rules** | Read |
| Zone → **Analytics** | Read |

4. **Zone Resources:** Include ← Specific zone ← `shelterjo.com`.
5. **TTL:** تاريخ انتهاء بعد 90 يومًا.
6. **Continue to summary ← Create Token**، ثم **Copy**. القيمة تظهر مرة واحدة فقط.

**ب. إضافته إلى البيئة** (في Claude):
1. **Edit cloud environment ← API credentials ← Add credential**.
2. **Credential type:** Bearer (الافتراضي).
3. **Name:** `Cloudflare read-only`.
4. **Allowed websites:** `api.cloudflare.com`.
5. **Custom headers:** اترك الصف كما هو (Name = `Authorization`، Prefix = `Bearer`)، والصق الـToken في **Value**.
6. **Connect**. يُحفظ فورًا، ولا تُعرض القيمة بعدها.

**ج. التحقق (أنا):**
- طلب واحد إلى `/user/tokens/verify` ثم قراءة إعدادات الـZone.
- النتيجة تُسجل في Integration Registry.
- `CLOUDFLARE_ZONE_ID` ليس سرًا، وأقرؤه من الـAPI نفسه.

> صلاحية **Cache Purge** (كتابة) تُطلب لاحقًا في PHASE 5/7، **بموافقة منفصلة**. صلاحيات **DNS Edit لا تُطلب** قبل بوابة الإطلاق.

## 3) Google — حساب خدمة للقراءة (Search Console · GA4 · GTM)
1. **Google Cloud Console:** أنشئ مشروعًا باسم `shelter-website` (مجاني).
2. **APIs & Services → Enable:**
   - Google Search Console API
   - Google Analytics Data API
   - Google Analytics Admin API
   - Tag Manager API
3. **IAM → Service Accounts → Create:** باسم `shelter-reader`، **بلا أي دور على المشروع**. ثم **Keys → Add key → JSON**.
4. أضف بريد حساب الخدمة (`shelter-reader@….iam.gserviceaccount.com`) مستخدمًا:

| الخدمة | المكان | المستوى |
|---|---|---|
| Search Console | Settings → Users and permissions → Add user | **Restricted** |
| GA4 | Admin → Property access management | **Viewer** |
| GTM | Admin → User Management | **Read** (للحاوية) |

5. **محتوى ملف الـJSON** يذهب إلى **GitHub Actions secret** باسم `GOOGLE_SERVICE_ACCOUNT_JSON`، **وليس** إلى إعدادات البيئة. بعدها احذف الملف من جهازك.
6. **كيف أقرأ البيانات:**
   - Workflow يدوي في GitHub Actions يقرأ GSC وGA4 وGTM.
   - يكتب تقريرًا مجمّعًا بلا أي PII كـArtifact.
   - أشغّله وأقرأ نتيجته، **والمفتاح لا يدخل الجلسة**.

### Google Business Profile (لاحقًا — PHASE 5)
- الـAPI يحتاج **طلب وصول من Google يقدّمه الـOwner** (مجاني)، ثم تسجيل دخولك مرة واحدة (OAuth). خطواته تُرسل عند الوصول للمرحلة.
- **لا تعديل على Google Business قبل موافقتك** (D-051).

## 4) Cloudways — تطبيق Staging منفصل
1. على **نفس السيرفر**: **Add Application**، ثم اختر **Custom PHP** (PHP 8.3 + MySQL) وسمّه `shelter-staging`. **تطبيق WordPress الحالي لا يُلمس.**
2. افتح التطبيق الجديد، ثم **Application Management ← Access Details**. أنشئ **Application Credentials** (SFTP/SSH للتطبيق فقط، **وليست** Master Credentials).
3. أضف في **GitHub Actions secrets**:

| الاسم | القيمة |
|---|---|
| `CLOUDWAYS_STAGING_HOST` | Public IP للسيرفر |
| `CLOUDWAYS_STAGING_USER` | اسم مستخدم التطبيق |
| `CLOUDWAYS_STAGING_PASSWORD` | كلمة مرور التطبيق |

4. إذا كان **SSH/SFTP IP Whitelisting** مفعّلًا في Cloudways (Server ← Security)، فقد يحجب GitHub Actions. يُبلَّغ عنه عند أول نشر.
5. **للفحص (PO-046):** لقطات من Server → Settings & Packages، وMonitoring، وBackups.

> - النشر يتم من **GitHub Actions** لأن الجلسة تعمل عبر HTTPS فقط (لا SSH).
> - مفتاح **Production** المحدود يُطلب لاحقًا عند بوابة الإطلاق فقط.

## بعد كل خطوة
| الخطوة | ما يحدث |
|---|---|
| أخبرني | أتحقق من الصلاحية **بقراءة واحدة**، وأسجل النتيجة في Integration Registry |
| أول استخدام | **قراءة فقط:**<br>- جرد الموقع القديم (✅ تم).<br>- إعدادات Cloudflare.<br>- Baseline من Search Console.<br>- تدقيق GA4/GTM الموجود |
| أي تغيير | تقرير + موافقتك أولًا |
