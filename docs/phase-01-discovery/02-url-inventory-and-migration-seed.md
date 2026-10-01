# 02 — URL Inventory & Migration Map (Seed)

> **تحديث 2026-10-01:** الجرد المباشر في [`24-live-site-crawl`](24-live-site-crawl-2026-10-01.md):
> - صفحتا الـDemo في القسم B أصبحتا **404**.
> - 7 مقالات و5 صفحات جديدة غير موجودة هنا.
>
> **هذه بذرة (Seed) وليست خريطة معتمدة.** الوجهات الجديدة (New Target) تعتمد على قرارات الـURL Architecture (DB-02، DB-03، DB-06، DB-07) التي لم تُحسم بعد.
> القاعدة: أي URL يحمل ترتيبًا أو روابط خارجية **لا يُحذف بدون 301** إلى أقرب صفحة مكافئة.
> الإجراءات: `KEEP` (نفس الرابط) · `REPLACE` (صفحة جديدة برابط جديد + 301) · `REDIRECT` (دمج في صفحة أخرى + 301) · `REMOVE` (410/404 مقبول) · `UNDECIDED`

## A. URLs تحمل قيمة SEO (ممنوع فقدانها)

| Old URL | ماذا يحمل | الإجراء المقترح | الوجهة المقترحة (مبدئية) | يعتمد على |
|---|---|---|---|---|
| `https://shelterjo.com/` + `http://shelterjo.com/` | 160 رابط خارجي من ~95 نطاق + رابط الـKnowledge Panel | `KEEP` (301 على مستوى الـHost) | `https://www.shelterjo.com/` (أو العكس — قرار Host واحد) | DB-11 (Host) |
| `/` | #2 "shelter coffee drive"، #2 "شلتر"، ~69 زيارة/شهر | `KEEP` | `/` | DB-02 |
| `/menu` (+ نسخة `http` وبدون `www`) | 42 رابط خارجي (مواقع منيو) | `KEEP` | `/menu` (المنيو الجديد نفسه) | DB-02، DB-06 |
| `/القائمة-شلتر-كافية-محافظة-اربد/` | 6 كلمات، ~15/شهر | `REPLACE` | `/menu` | DB-03 |
| `/best-cafes-irbid-2026/` | **#4 "كافيهات اربد" (2,400/شهر)** — أعلى صفحة زيارات | `UNDECIDED` | خيار 1: مقالة دائمة في الـKnowledge Hub بدون سنة في الرابط · خيار 2: صفحة مدينة إربد · خيار 3: إبقاء الرابط كما هو | DB-04، DB-09 |
| `/coffee-house-vs-cafe/` | ~27/شهر + مصر ولبنان؛ People Also Ask | `REPLACE` أو `KEEP` | مقالة في الـKnowledge Hub | DB-09 |
| `/blog/` | 2 كلمات | `KEEP` أو `REPLACE` | `/blog/` أو مسار الـKnowledge Hub | DB-09 |
| `/موقع-شلتر-كافية-محافظة-اربد/` | 2 كلمات | `REPLACE` | `/locations` | DB-07 |
| `/موقعنا` | مكرر لصفحة الموقع | `REDIRECT` | `/locations` | DB-07 |
| `/city-centre-branch/` | صفحة فرع مفهرسة | `REPLACE` | صفحة فرع سيتي سنتر الجديدة | DB-01، DB-07 |
| `/drive-thru/` | صفحة خدمة مفهرسة | `REPLACE` | صفحة فرع الدرايف (أو صفحة خدمة الدرايف ثرو) | DB-01، DB-07 |
| `/drive-thru-shelter-irbid-how-it-works/` | 1 كلمة | `REDIRECT` أو `REPLACE` | دمجها في صفحة الدرايف ثرو (تجنب التنافس) | DB-07 |
| `/من-هو-شلتر-كافية-في-اربد/` | 2 كلمات | `REPLACE` | `/about` | DB-03 |
| `/التوظيف-في-شلتر-كافية/` + `/hiring` | 1 كلمة + رابط خارجي | `UNDECIDED` | `/careers` إن بقي القسم، وإلا `/about` أو `/contact` | DB-04 |
| `/franchise-shelter-coffee/` | 1 كلمة | `UNDECIDED` | `/franchise` إن بقي، وإلا `/contact` | DB-04 |
| `/الشروط-والاحكام-لموقع-شلتر-كافية/` | 2 كلمات | `REPLACE` | `/terms` | DB-03 |
| `/category/uncategorized/` | 2 كلمات ضعيفة | `REDIRECT` | المدونة الجديدة | — |

## B. URLs بدون قيمة تُذكر

| Old URL | الإجراء المقترح | الوجهة | السبب |
|---|---|---|---|
| `/family/` | `UNDECIDED` | `/about` أو صفحة فريق | يحتاج موافقة على نشر الأسماء |
| `/newsletter/` | `UNDECIDED` | `/contact` أو `/` | هل النشرة مطلوبة؟ |
| `/أول-خدمة-سيارات-في-الشمال-كافيه-شلتر/` | `REDIRECT` | صفحة الدرايف ثرو | ادعاء "أول" + سبام |
| `/the-biggest-mental-and-physical-benefits-of-working-out/` | `REMOVE` أو `REDIRECT` | المدونة | رابط Demo + ادعاءات صحية |
| `/mental-physical-challenges-to-overcome/` | `REDIRECT` | `/menu` | منيو 2024 على رابط Demo |
| `/author/info/` | `REDIRECT` | المدونة أو `/` | يكشف اسم مستخدم WordPress |
| `/?uicore-tb=personal-trainer-footer` | `REMOVE` (410) | — | قالب Demo |
| `/locations.kml`، `/geo-sitemap.xml`، `/uicore-tb-sitemap.xml`، `/author-sitemap.xml`، `/category-sitemap.xml` | `REMOVE` | — | تُستبدل بـSitemap جديد |
| `/sitemap_index.xml`، `/post-sitemap.xml`، `/page-sitemap.xml` | `REDIRECT` | الـSitemap الجديد | Search Console قد يكون مسجلًا عليها |
| `/robots.txt`، `/llms.txt` | `KEEP` (محتوى جديد) | نفس المسار | |
| روابط خارجية مكسورة `''%20rel=nofollow` | `KEEP` الـ301 الحالي | `/` | روابط واردة مشوهة |

## C. روابط غير معروفة بعد (MISSING)

- 6 مقالات ظهرت في قائمة المدونة بدون روابط معروفة (انظر P19 في `01-current-website-inventory.md`).
- أي صفحات Draft / Private / Tags / Attachments / Feeds.
- `shop.shelterjo.com` وكل مساراته.
- أهداف الـ301 الحالية (`/menu` و`/hiring` والجذر) — غير مرئية من بيئة العمل.

**الإغلاق:** فتح الوصول للموقع (Crawl كامل + قراءة الـSitemaps) **أو** WordPress Export (Tools → Export → All content) **و** قائمة الصفحات من Google Search Console.
