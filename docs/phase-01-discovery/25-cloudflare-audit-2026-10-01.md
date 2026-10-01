# 25 — تدقيق Cloudflare (قراءة فقط)

| البند | القيمة |
|---|---|
| **التاريخ** | 2026-10-01 |
| **الوصول** | **Account API token** باسم `shelter-readonly`:<br>- قراءة فقط، لزون `shelterjo.com`.<br>- محفوظ كـ**API credential** في إعدادات البيئة (INFRA-046)، **ولا يظهر في الجلسة**.<br>- صالح حتى 2027-10-01 |
| **ما تغيّر** | **لا شيء.** لم يُعدَّل أي إعداد أو سجل DNS |
| **عناوين IP** | لا تُكتب هنا عمدًا (حماية الـOrigin) |

## 1. الزون
| البند | القيمة |
|---|---|
| الحالة | `active` |
| الخطة | **Free Website** |
| الـNameservers | Cloudflare (DNS Setup: Full) |
| شهادة الحافة | Universal (Google CA) لـ`shelterjo.com` و`*.shelterjo.com`، تتجدد تلقائيًا (تنتهي 2026-12-24) |
| DNSSEC | **غير مفعّل** |
| آخر 24 ساعة (من لقطة الـOwner) | 905 زائرًا · 28.34k طلب · 95% من الـCache · 3 GB |

## 2. سجلات DNS (12)
| النوع | الاسم | الوضع | الملاحظة |
|---|---|---|---|
| A | `shelterjo.com` | ✅ Proxied | — |
| A | `www.shelterjo.com` | ✅ Proxied | — |
| A | `*.shelterjo.com` | ⚠️ **DNS-only** | Wildcard: **أي نطاق فرعي** (مثل `shop`) يصل للسيرفر مباشرة |
| A | `order.shelterjo.com` | ⚠️ **DNS-only** | الغرض غير معروف |
| A | `sweet.shelterjo.com` | ⚠️ **DNS-only** | الغرض غير معروف |
| CNAME | `_acme-challenge` | DNS-only | شهادة Let's Encrypt لتطبيق Cloudways (طبيعي) |
| MX ×5 | `shelterjo.com` | — | **Google Workspace** (`aspmx.l.google.com` وبدائله) |
| TXT | `shelterjo.com` | — | تحقق Google Search Console فقط |

**السجلات الخمسة من نوع A تشير إلى نفس السيرفر.**

## 3. الإعدادات
| الإعداد | القيمة | التقييم |
|---|---|---|
| SSL/TLS mode | **Full** | ⚠️ الأفضل **Full (strict)**، لأن السيرفر عليه شهادة Let's Encrypt |
| Always Use HTTPS | on | ✅ (يجيب على البند المفتوح: `http` يتحوّل إلى `https`) |
| Automatic HTTPS Rewrites | on | ✅ |
| Minimum TLS | **1.0** | ⚠️ الموصى به **1.2** |
| TLS 1.3 · HTTP/2 · HTTP/3 · Brotli · IPv6 | on | ✅ |
| HSTS (من Cloudflare) | off | الموقع الجديد يرسل HSTS بنفسه (بلا includeSubDomains) |
| Cache level · Browser TTL | aggressive · 4 ساعات | — |
| Security level · Browser check | medium · on | ✅ |
| Rocket Loader · Minify · Polish · Mirage | off | ✅ (لا تعارض مع البناء الجديد) |
| Always Online | on | — |
| Email obfuscation | off | — |

## 4. القواعد
| النوع | النتيجة |
|---|---|
| Cache Rules · Redirect Rules · Transform Rules · Custom WAF | **لا يوجد** |
| Managed rulesets | Cloudflare Normalization · Managed Free · DDoS L7 |
| **Page Rules** | **غير مقروءة:** Cloudflare لا يسمح بقراءتها بـAccount token (خطأ 1011). يُكمل بلقطة من **Rules ← Page Rules** عند الحاجة |

## 5. الملاحظات حسب الأولوية
**لا شيء منها يوقف البناء. كلها تغييرات Production تحتاج موافقتك (M38 §10).**

| # | الملاحظة | الخطر | المقترح | السجل |
|---|---|---|---|---|
| 1 | **3 سجلات DNS-only** (`*` · `order` · `sweet`) تكشف عنوان السيرفر وتتجاوز حماية Cloudflare | يمكن مهاجمة السيرفر مباشرة دون WAF/DDoS، والزائر يرى تحذير شهادة على النطاقات الفرعية | ✅ **قرار الـOwner: A — الحذف (D-311).** الـWildcard أولًا. `order` (أُنشئ 2026-05-14) و`sweet` (أُنشئ 2026-09-22) حديثان، فيُؤكَّد عدم استخدامهما قبل الحذف.<br>✅ **نُفذ 2026-10-01:** الـOwner حذف الثلاثة، وتحقق بالقراءة: 9 سجلات متبقية، وwww يعمل والبريد (MX) سليم | PO-070 |
| 2 | **لا SPF ولا DKIM ولا DMARC** لبريد `@shelterjo.com` | يمكن انتحال بريد `info@shelterjo.com`، ورسائل الموقع قد تذهب إلى Spam | ✅ **قرار الـOwner: C (D-312):** SPF لـGoogle + DKIM من Google Admin + DMARC بوضع المراقبة `p=none`، والتقارير إلى `info@shelterjo.com`.<br>✅ **نُفذ 2026-10-01:** السجلات الثلاثة موجودة، ومفتاح DKIM صالح (RSA 2048). المتبقي: «Start authentication» في Google Admin، ومراجعة التقارير بعد أسبوعين | PO-072 |
| 3 | SSL **Full** بدل **Full (strict)** · TLS 1.0 · DNSSEC مطفأ | ضعف في التشفير بين Cloudflare والسيرفر، وإصدارات TLS قديمة | ✅ **قرار الـOwner: B (D-313):** TLS 1.2 الآن، وFull (strict) بعد التحقق من شهادة السيرفر في Cloudways، وDNSSEC لاحقًا (المسجّل: Namecheap).<br>✅ **TLS 1.2 نُفذ** (تحقق API). شهادة Cloudways تُظهر `shelterjo.com` فقط: تُضاف `www` قبل Strict.<br>⏸ **مؤجل بطلب الـOwner** («انتقل الى المرحلة الثانية واترك كلاود ويز»). الوضع الحالي آمن: Full + TLS 1.2 | PO-073 |
| 4 | Page Rules غير مقروءة | قد توجد تحويلات قديمة على مستوى الـEdge | لقطة شاشة واحدة عند جرد التحويلات (PHASE 7) | G22-TF |

> **ترتيب القرارات:** يُسأل الـOwner عنها **واحدًا تلو الآخر** (M38). الأول: رقم 1، ثم 2، ثم 3.
