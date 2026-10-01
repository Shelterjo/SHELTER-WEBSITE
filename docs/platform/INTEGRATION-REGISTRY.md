# INTEGRATION REGISTRY — سجل التكاملات والأسرار وملكية الخدمات

| البند | القيمة |
|---|---|
| **الغرض** | مكان واحد يجيب: ما الخدمات المربوطة؟ هل تعمل؟ متى ينتهي اتصالها؟ ومن يملك كل حساب؟ **بلا أي سر في الواجهة أو قاعدة البيانات** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 1:** جدول `integrations` + الأسرار لكل بيئة · **PHASE 5:** ربط Google وCloudflare وشاشة التكاملات · **PHASE 6:** CrUX وSMTP (إن اعتُمدا) |
| **الأولوية** | P0 |
| **المتطلبات** | M35 §26 · §27 · §28 · §58 · SEC-002 · CF-008 · CF-009 · INT-001 · INT-002 · INT-005 · INT-006 · D-043 · D-050 · D-051 · D-055 · D-208 · GBP-007 · GBP-009 · GOV-060 · FINAL-ARCHITECTURE-REVIEW §15 (S-3 · S-5) · G14-CF-05 · G14-CF-17 · G14-TF-01 · G14-TF-03 · PO-009 · PO-011 · PO-012 · PO-013 · PO-037 · PO-039 |
| **المراجع الملزمة** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) §3.2 (`integrations`) · [`MASTER-DATA-HUB`](../MASTER-DATA-HUB.md) §5 · §12 · §13 · [`SECURITY-CENTER`](SECURITY-CENTER.md) §6 · [`ENVIRONMENTS`](ENVIRONMENTS.md) §2 · [`CACHE-CDN`](CACHE-CDN.md) §7 |

## 1. القاعدة
- **سجل واحد:** `integrations` يخدم التكاملات البرمجية (`kind = API`) وسجل ملكية الخدمات (`kind = SERVICE`). لا جدول ملكية منفصل.
- **بلا أسرار:** السجل يحمل الحالة والنطاق والانتهاء **وأسماء** الأسرار فقط ("مُعد / غير مُعد").
- **أقل صلاحية دائمًا:** قراءة فقط حيث تكفي. لا Global API Key. لا Cloudways API Key (AC-RULE-04).
- **التنفيذ على Google** يتولاه Claude بعد الصلاحيات (D-051، D-055، D-208). الـOwner يُطلب منه فقط: الدخول · OAuth · 2FA · الملكية · الموافقة.
- **أي كتابة على Google بموافقة:** النشر من الـDashboard مع ظهور Google في معاينة الأثر = الموافقة (AC-1، GBP-009).

## 2. جدول `integrations` (بلا أسرار)
| العمود | القيم / ملاحظة |
|---|---|
| `key` · `kind` · `name_ar/en` · `provider` | مثل `gbp_business_info` · `API` |
| `environment` | `production` · `staging`. **لكل بيئة سطرها وبياناتها** (AC-4) |
| `status` | `NOT_CONFIGURED` · `PENDING_ACCESS` · `CONNECTED` · `DEGRADED` · `NEEDS_RECONNECT` · `DISABLED` |
| `scopes` | النطاقات أو الصلاحيات الممنوحة فعلًا |
| `last_success_at` · `last_error_at` · `last_error_code` | الخطأ مصنّف ومنظّف، بلا رسالة خام |
| `token_expires_at` · `needs_reauth` | يغذيان المراقبة ([`MONITORING`](MONITORING.md) #10) |
| `secret_names` · `secrets_configured` | أسماء متغيرات `.env` فقط + هل هي موجودة |
| `rotate_by` | موعد التدوير التالي (§4) |
| `account_owner` · `recovery_method` · `access_holders` | لسطور `SERVICE` (§5). **أسماء وأدوار فقط** |
| `phase` · `cost_note` | المرحلة · ملاحظة التكلفة أو الحصة |

## 3. أين تعيش الأسرار
| السر | المكان |
|---|---|
| ثابت (مفاتيح API، أسرار OAuth Client، كلمة مرور قاعدة البيانات، توكنات Cloudflare، `APP_KEY`، مفاتيح الهوية) | `.env` لكل بيئة **على الخادم فقط** (`private_html/shared/.env` — ENVIRONMENTS §2) |
| ديناميكي (Refresh tokens من OAuth) | مشفّر في `settings` بتصنيف `SENSITIVE`، ولا يصل لأي View أو API (SECURITY-CENTER §6) |

- **ممنوع:** Git · الحزمة (`public/build`) · الـCMS · المحادثة · السجلات.
- **`.env.example`** بالأسماء فقط. لا متغير سري يبدأ بـ`VITE_` (يدخل الحزمة).
- **Staging لا يشارك أي سر أو توكن مع Production.**
- **فحص أسرار في الـCI** بأداة مجانية، تُسجل في سجل الأدوات أولًا (SECURITY-CENTER §6).

## 4. سياسة التدوير
| السر | التدوير | ملاحظة |
|---|---|---|
| توكنات Cloudflare | كل 90 يومًا، وعند أي شك | تاريخ انتهاء مضبوط على التوكن نفسه |
| مفتاح CrUX | سنويًا، وعند أي شك | مقيّد بـCrUX API وعنوان الخادم |
| سر OAuth Client (Google) | عند أي شك أو تغيّر من لديه وصول | "فصل الحساب" يلغي الـRefresh token ويحذفه (MASTER-DATA-HUB §13) |
| كلمة مرور قاعدة البيانات | سنويًا، وعند تغيّر الوصول | بمستخدمين منفصلين (CW-06) |
| كلمة مرور SMTP (إن اعتُمد) | سنويًا | — |
| `APP_KEY` · مفاتيح الهوية | **لا تدوير عشوائي:** إجراء موثق لإعادة التشفير، و`key_version` لمفاتيح الهوية (RECRUITMENT-SECURITY §5) | نسخة Offline يحتفظ بها الـOwner ([`DISASTER-RECOVERY`](DISASTER-RECOVERY.md)) |

- **`rotate_by` قبل 14 يومًا** ← إشارة MEDIUM "حان تجديد مفتاح …".
- **شك بتسريب** ← تدوير فوري + إشارة CRITICAL + سطر `audit_logs`.

## 5. سجل ملكية الخدمات (M35 §28 — بلا أسرار)
**الهدف:** ألا تُفقد ملكية أي خدمة. **كل حساب يجب أن يكون باسم المنشأة** (PO-037).

| الخدمة | مالك الحساب | طريقة الاسترداد | من لديه وصول |
|---|---|---|---|
| الدومين `shelterjo.com` (المسجّل + موعد التجديد) | `PENDING OWNER INPUT (PO-037)` | `PENDING` | `PENDING` |
| DNS (هل الـNameservers على Cloudflare؟) | `PENDING (PO-013، PO-037)` | `PENDING` | `PENDING` |
| حساب Cloudflare | `PENDING (PO-013)` | `PENDING` | `PENDING` |
| حساب Cloudways | `PENDING (PO-037، A-10)` | `PENDING` | `PENDING` |
| مستودع Git (`Shelterjo/SHELTER-WEBSITE` على GitHub — مرصود) | `PENDING (PO-037)` | `PENDING` | `PENDING` |
| مشروع Google Cloud (للـAPIs) | لم يُنشأ بعد — يُنشأ باسم المنشأة بموافقتك | — | — |
| GA4 Property · GTM Container · Search Console Property | `PENDING (G-11، PO-011، PO-012)` | `PENDING` | `PENDING` |
| Google Business Profiles (DRIVE · HOUSE) | `PENDING (G-03، PO-009)` | `PENDING` | `PENDING` |
| البريد (استضافة `info@shelterjo.com`) | `PENDING (PO-037، D-035)` | `PENDING` | `PENDING` |
| مراقب التوفر الخارجي (إن اعتُمد) | يُنشأ باسم المنشأة | — | — |

- **"طريقة الاسترداد":** نوعها ومكان حفظها فقط (مثل "2FA بتطبيق مصادقة · رموز الاسترداد Offline لدى الـOwner"). **لا قيم.**
- **"من لديه وصول":** أسماء وأدوار فقط.
- **توصية:** مدير ثانٍ موثوق لكل خدمة، و2FA مفعّل. القرار للـOwner.

## 6. قائمة التكاملات
| التكامل | الغرض | النطاق / الصلاحية | التكلفة | المرحلة |
|---|---|---|---|---|
| **GBP Business Information API** | مزامنة الهاتف والساعات والساعات الخاصة والإغلاق المؤقت ورابط الموقع. **الاسم والعنوان والـPin يدوي** (MASTER-DATA-HUB §5) | OAuth `business.manage` بحساب Owner/Manager للفرعين | مجاني، لكن **يحتاج طلب وصول من Google** (الحصة قد تكون صفرًا حتى الموافقة — PO-009) | PHASE 5 |
| **GBP v4 Reviews** | قراءة المراجعات + نشر رد **معتمد** (P1 Reputation) | نفس `business.manage`. حتى منح الكتابة: "نسخ الرد" بلا نشر آلي (G14-CF-05) | مجاني بعد موافقة الوصول (G14-TF-01) | PHASE 5 (P1) |
| **Search Console API** | الفهرسة والاستعلامات وفحص عينات الروابط | OAuth `webmasters.readonly` (D-050: قراءة أولًا) | مجاني بحصص لكل Property (PO-011) | PHASE 5 |
| **GA4 Data API** | أعداد الزيارات في الـDashboard + فحص التتبع اليومي | OAuth `analytics.readonly` | مجاني ضمن الحصص (PO-012) | PHASE 5 |
| **CrUX API** | مقارنة RUM ببيانات Chrome الميدانية | مفتاح API مقيّد | مجاني، **يحتاج موافقة لإنشاء المفتاح** (AC-10، PO-039) | PHASE 6 |
| **Cloudflare API** | إبطال الـCache بالوسوم أو بالروابط · قراءة حالة الزون وSSL للمراقبة | توكنان محدودان بزون `shelterjo.com`: **Cache Purge فقط** · **قراءة فقط** لـSite Health، بشرح الصلاحيات الدقيقة قبل الطلب (CF-008، CF-009) | API مجاني. ميزات الإبطال بالوسوم حسب الخطة (PO-013) | PHASE 5 · PHASE 6 |
| **SMTP** | بريد CRITICAL للـOwner **فقط إن اعتُمد** ([`NOTIFICATIONS`](NOTIFICATIONS.md) §5.2) | صندوق بريد المنشأة القائم | بلا تكلفة إن كان الصندوق قائمًا. **لا خدمة بريد مدفوعة** | PHASE 6 (معلّق) |

**سطور `SERVICE` بلا API في V1:** حاوية GTM (النشر بموافقة — D-051) · Cloudways (بلا API Key) · الدومين وDNS.

**ممنوع:** Places API المدفوع (D-043، INT-002) · Scraping لأي لوحة Google (INT-006) · Supermetrics أو Semrush بحصة بلا موافقة (INT-003).

- **ملاحظة إعداد Google:** شاشة موافقة OAuth في وضع **Testing** تُنهي الـRefresh tokens بعد 7 أيام.
  - يُستخدم وضع **In production**، أو **Internal** إن كان الحساب Google Workspace.
  - يُتحقق وقت الإعداد (P09).

## 7. الحالة وإعادة الربط
- **كل استدعاء** يحدّث `last_success_at` أو `last_error_*`.
- **`invalid_grant`** أو انتهاء ← `NEEDS_RECONNECT` + إشارة HIGH بزر **"إصلاح الاتصال"** ([`INCIDENTS`](INCIDENTS.md) §3).
- **الربط:** تدفق OAuth من الـDashboard. يدخل الـOwner بنفسه ويوافق. يُخزن الرمز مشفرًا، وتُحل الإشارة آليًا.
- **"فصل الحساب":** Step-up ← إلغاء الرمز لدى Google وحذفه ← `DISABLED` ← سطر `audit_logs`.
- **فشل أي تكامل لا يكسر الموقع** (MASTER-DATA-HUB §12). الـDashboard يعرض "آخر بيانات محفوظة" (DASH-023).

## 8. البيانات
- `integrations` · `settings` (الرموز المشفّرة `SENSITIVE`) · `signals` · `audit_logs` · `sync_jobs` · `channel_sync_states` · `external_references`.
- **لا جدول جديد.**

## 9. مكانه في الـDashboard
- **النظام ← التكاملات:**
  - قائمة بالحالة · البيئة · آخر نجاح · الانتهاء · النطاق · "يحتاج إعادة ربط؟" (M35 §26).
  - تبويب **ملكية الخدمات** (§5).
- **"إصلاح الاتصال"** يظهر أيضًا في "يحتاج انتباه".
- **للـOwner فقط** (M35 §58).

## 10. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| السجل · الأسرار لكل بيئة · التدوير · سجل الملكية · GBP Business Information · GSC · GA4 Data · Cloudflare Purge | GBP Reviews (بعد PO-009) · CrUX (بعد الموافقة) · SMTP (بعد الموافقة) · POS/ERP/التطبيق عبر `external_references` (INT-016) · منشورات GBP وFood Menus (قرار منفصل) |

## 11. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| INT-T1 | أسرار وهمية (Canary) في `.env` الاختبار + رمز OAuth وهمي، ثم مسح جداول القاعدة | لا قيمة في `integrations`. الرمز في `settings` مشفّر فقط |
| INT-T2 | البحث عن قيم الـCanary في `public/build` وفي HTML وJSON الصفحات | 0 |
| INT-T3 | `git ls-files` | لا `.env`. و`.env.example` بالأسماء فقط |
| INT-T4 | محاكاة `invalid_grant` | `NEEDS_RECONNECT` + إشارة HIGH بزر إصلاح. بعد الربط: `CONNECTED` والإشارة `RESOLVED` |
| INT-T5 | `token_expires_at` بعد 10 أيام · `rotate_by` بعد 10 أيام | إشارتا MEDIUM |
| INT-T6 | "فصل الحساب" بجلسة أقدم من 10 دقائق | طلب Step-up، ثم حذف الرمز + `DISABLED` + سطر Audit |
| INT-T7 | إعدادات Staging وProduction | معرّفات Properties ومفاتيح مختلفة (اختبار إعدادات) |
| INT-T8 | محاولة حفظ مفتاح بصيغة Cloudflare Global API Key | رفض من مدقق الإعدادات |
| INT-T9 | تبويب الملكية | القيم المجهولة تظهر `PENDING OWNER INPUT (PO-037)` · لا حقل كلمة مرور في النموذج |
| INT-T10 | طلب الشاشة بلا جلسة / بجلسة غير Owner | `401` / `403` |
| INT-T11 | الشاشة على 20 عرضًا AR/EN | بلا Overflow + axe 0 Serious/Critical |

## 12. ما ليس منجزًا (NOT DONE)
- **لا كود بعد.** الحالة الصادقة `NOT STARTED`.
- **كل بيانات الملكية معلّقة (PO-037).** كل صلاحيات Google وCloudflare غير ممنوحة (PO-009، PO-011، PO-012، PO-013).
- مشروع Google Cloud غير منشأ. CrUX وSMTP غير معتمدين.
