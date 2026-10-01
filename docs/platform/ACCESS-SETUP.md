# ACCESS SETUP — منح الصلاحيات بأقل صلاحية (Cloudways · Cloudflare · Google)

| البند | القيمة |
|---|---|
| **الغرض** | خطوات دقيقة يمنح بها الـOwner الصلاحيات، **بأقل صلاحية ممكنة**، و**بلا أي سر في المحادثة أو في Git** |
| **الحالة** | الـOwner وافق على منح الصلاحيات (2026-10-01). التنفيذ ينتظر إضافة القيم في إعدادات البيئة |
| **البنود المرتبطة** | PO-008…013 · PO-046 · PO-064 · AC-RULE-04 · D-041 · D-051 · INTEGRATION-REGISTRY · ENVIRONMENTS |

## القواعد
1. **لا كلمات مرور ولا مفاتيح في المحادثة أبدًا.** كل قيمة سرية تُضاف في إعدادات البيئة:
   - افتح قائمة البيئة في شريط عنوان الجلسة.
   - اختر **Edit**.
   - أضف القيمة كـ**Environment variable** بالاسم المذكور.
   - الجلسة الجديدة تقرؤها.
2. **قراءة فقط أولًا.** أي كتابة تحتاج موافقتك في وقتها، كل واحدة على حدة: Google، وDNS، والـCache، والـProduction.
3. **لا Global API Key لـCloudflare، ولا Cloudways API Key** (AC-RULE-04).
4. **كل صلاحية تُسجّل** في [`INTEGRATION-REGISTRY.md`](INTEGRATION-REGISTRY.md): ماذا، ولماذا، ومتى تنتهي.

## 1) Network access — النطاقات المسموح بها
أضفها في **Edit → Network access → Allowed domains**:

| النطاق | لماذا |
|---|---|
| `shelterjo.com` · `www.shelterjo.com` · `shop.shelterjo.com` | فحص الموقع القديم وجرد الروابط، قراءة فقط (D-041) |
| `api.cloudflare.com` | قراءة إعدادات Cloudflare |
| `searchconsole.googleapis.com` · `analyticsdata.googleapis.com` · `analyticsadmin.googleapis.com` · `tagmanager.googleapis.com` · `oauth2.googleapis.com` · `www.googleapis.com` | Search Console وGA4 وGTM (قراءة) |
| عنوان IP لسيرفر Cloudways | الاتصال بتطبيق الـStaging |

## 2) Cloudways — تطبيق Staging منفصل
1. على **نفس السيرفر**: **Add Application**، ثم اختر **Custom PHP** (PHP 8.3 + MySQL) وسمّه `shelter-staging`. **تطبيق WordPress الحالي لا يُلمس.**
2. افتح التطبيق الجديد، ثم **Application Settings / Access Details**، وانسخ بيانات **Application Credentials** (SFTP/SSH للتطبيق فقط، **وليست** Master Credentials).
3. أضف إلى إعدادات البيئة:

| المتغير | القيمة |
|---|---|
| `CLOUDWAYS_STAGING_HOST` | Public IP للسيرفر |
| `CLOUDWAYS_STAGING_USER` | اسم مستخدم التطبيق |
| `CLOUDWAYS_STAGING_PASSWORD` | كلمة مرور التطبيق |

4. **للفحص (PO-046):** لقطات من Server → Settings & Packages، وMonitoring، وBackups. أو أترك الفحص يتم من خلال اتصال الـStaging.

> إذا لم تسمح الشبكة باتصال SSH من هذه البيئة، يتم النشر من **GitHub Actions** بنفس القيم.
> - تُضاف في المستودع: **Settings → Secrets and variables → Actions**، بالأسماء نفسها.
> - المفتاح المحدود لـ**Production** يُطلب لاحقًا عند بوابة الإطلاق فقط.

## 3) Cloudflare — API Token محدود (قراءة)
1. افتح **My Profile → API Tokens → Create Token → Custom token**.
2. الاسم: `shelter-readonly`.
3. **Permissions:**

| النوع | الصلاحية |
|---|---|
| Zone → **Zone** | Read |
| Zone → **DNS** | Read |
| Zone → **Zone Settings** | Read |
| Zone → **Cache Rules** | Read |
| Zone → **Analytics** | Read |

4. **Zone Resources:** Include → Specific zone → `shelterjo.com`.
5. **TTL:** حتى 90 يومًا.
6. أضف إلى إعدادات البيئة:
   - `CLOUDFLARE_API_TOKEN`: التوكن.
   - `CLOUDFLARE_ZONE_ID`: من صفحة الـZone ← Overview (ليس سرًا).

> صلاحية **Cache Purge** (كتابة) تُطلب لاحقًا في PHASE 5/7، **بموافقة منفصلة**. صلاحيات DNS للكتابة: **لا تُطلب** قبل بوابة الإطلاق.

## 4) Google — حساب خدمة للقراءة (Search Console · GA4 · GTM)
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

5. أضف إلى إعدادات البيئة: `GOOGLE_SERVICE_ACCOUNT_JSON`، وقيمته محتوى ملف الـJSON كاملًا.

### Google Business Profile (لاحقًا — PHASE 5)
- الـAPI يحتاج **طلب وصول من Google يقدّمه الـOwner** (مجاني)، ثم تسجيل دخولك مرة واحدة (OAuth). خطواته تُرسل عند الوصول للمرحلة.
- **لا تعديل على Google Business قبل موافقتك** (D-051).

## بعد الإضافة
| الخطوة | ما يحدث |
|---|---|
| ابدأ جلسة جديدة أو أخبرني | أتحقق من كل صلاحية **بقراءة واحدة**، وأسجل النتيجة في Integration Registry |
| أول استخدام | قراءة فقط: جرد الموقع القديم، وإعدادات Cloudflare، وBaseline من Search Console، وتدقيق GA4/GTM الموجود |
| أي تغيير | تقرير + موافقتك أولًا |
