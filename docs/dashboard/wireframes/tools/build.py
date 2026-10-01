"""SHELTER Owner Dashboard — LOW-FI wireframes (P04 · M25 phase F + M32 "wireframes for new owner modules").
Deterministic: run `python3 build.py` from anywhere. Writes static HTML into ../html/.

Rules baked in (do not relax without an owner decision):
- Grayscale only, no brand values (identity files missing, M-10). Arabic RTL first (lang=ar dir=rtl).
- Every number is SAMPLE data inside a block that carries the "DEMO DATA" label (DASH-024). Nothing here is live.
- Google integrations not connected are shown as PENDING INTEGRATION (GA4 · Search Console · Google Business Profile).
- Every metric / warning is a link to the place where the owner acts on it (DASH-018, M32 §40).
- Owner business language first; technical detail only under a collapsible "تفاصيل متقدمة" (DASH-020, M32 §41).
- V1 = owner only: no user / role management anywhere (M30, M32 §49). Safe Mode is in the top bar of every screen.
No script on any page: static states only."""
import calendar, datetime, html, os, re, shutil

ROOT = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(ROOT, '..', 'html'))
esc = html.escape

DEMO = 'بيانات تجريبية — DEMO DATA'
PI = 'PENDING INTEGRATION'
CAREERS = '../../../careers/wireframes/html/{k}-overview.html'   # recruitment module: already wireframed (M28) — link, do not rebuild
FRANCHISE = '../../../franchise/wireframes/html/{k}-overview.html'  # partnerships module: wireframed in docs/franchise/wireframes — link only

# ---------------------------------------------------------------- IA (GAP-CLOSURE-PLAN §C — 7 groups)
# group key, label, [(item key, label, target)] ; target: screen key ('menu'), screen key + suffix ('calendar?type=event'), or EXT:<url>
GROUPS = [
    ('home', 'الرئيسية', [('home', 'مركز القيادة', 'home')]),
    ('content', 'المحتوى', [('menu', 'المنيو', 'menu'), ('branch', 'الفروع والساعات', 'branch'), ('pages', 'الصفحات', 'later#pages'),
                            ('knowledge', 'المعرفة (المدونة)', 'later#knowledge'), ('events', 'الفعاليات', 'calendar?type=event'),
                            ('media', 'مركز الوسائط', 'media'), ('parity', 'تطابق العربي والإنجليزي', 'parity')]),
    ('exp', 'التجارب', [('active', 'النشط الآن', 'active'), ('campaigns', 'الحملات والعروض', 'calendar?type=campaign'),
                        ('seasons', 'المواسم', 'calendar?type=seasonal'), ('calendar', 'التقويم', 'calendar'),
                        ('family', 'SHELTER Family والموظف المثالي', 'family')]),
    ('business', 'الأعمال', [('careers', 'التوظيف', 'EXT:' + CAREERS), ('partners', 'الشراكات', 'EXT:' + FRANCHISE),
                             ('feedback', 'آراء العملاء', 'feedback'), ('reputation', 'السمعة', 'reputation')]),
    ('growth', 'النمو', [('analytics', 'التحليلات', 'analytics'), ('search', 'البحث داخل الموقع', 'seo#search'),
                         ('seo', 'الـSEO', 'seo'), ('opps', 'فرص المحتوى', 'seo#opps')]),
    ('quality', 'الجودة', [('health', 'صحة الموقع', 'health'), ('performance', 'الأداء الفعلي', 'performance'),
                           ('a11y', 'الوصولية', 'a11y'), ('security', 'الأمان', 'security'), ('privacy', 'الخصوصية', 'privacy')]),
    ('system', 'النظام', [('global-data', 'البيانات العامة', 'global-data'), ('facts', 'سجل الحقائق', 'facts'),
                          ('audit', 'سجل التدقيق', 'audit'), ('settings', 'الإعدادات', 'later#settings')]),
]
BOTTOM = [('home', 'الرئيسية', 'home'), ('content', 'المحتوى', 'menu'), ('exp', 'التجارب', 'active'), ('business', 'الأعمال', 'feedback'), ('more', 'المزيد', 'more')]
MORE_GROUPS = ('growth', 'quality', 'system')  # what the mobile "المزيد" sheet lists

# screen key → (h1 / state name, group, active nav item)
SCREENS = {
    'home': ('مركز القيادة', 'home', 'home'),
    'analytics': ('التحليلات', 'growth', 'analytics'),
    'menu': ('المنيو', 'content', 'menu'),
    'product': ('تعديل صنف', 'content', 'menu'),
    'impact': ('أثر التغيير قبل النشر', 'content', 'menu'),
    'guard': ('فحص النشر', 'content', 'menu'),
    'branch': ('الفروع والساعات', 'content', 'branch'),
    'active': ('النشط الآن', 'exp', 'active'),
    'calendar': ('التقويم', 'exp', 'calendar'),
    'experience': ('تعديل تجربة', 'exp', 'active'),
    'family': ('SHELTER Family والموظف المثالي', 'exp', 'family'),
    'media': ('مركز الوسائط', 'content', 'media'),
    'global-data': ('البيانات العامة', 'system', 'global-data'),
    'facts': ('سجل الحقائق', 'system', 'facts'),
    'parity': ('تطابق العربي والإنجليزي', 'content', 'parity'),
    'health': ('صحة الموقع', 'quality', 'health'),
    'performance': ('الأداء الفعلي', 'quality', 'performance'),
    'a11y': ('الوصولية', 'quality', 'a11y'),
    'security': ('الأمان', 'quality', 'security'),
    'privacy': ('الخصوصية والبيانات', 'quality', 'privacy'),
    'seo': ('الظهور في البحث (SEO)', 'growth', 'seo'),
    'reputation': ('السمعة', 'business', 'reputation'),
    'feedback': ('آراء العملاء', 'business', 'feedback'),
    'audit': ('سجل التدقيق', 'system', 'audit'),
    'safe-mode': ('تأكيد وضع الأمان', 'home', 'home'),
    'later': ('وحدات خارج هذه الجولة', None, None),
    'more': ('المزيد', 'more', None),  # mobile only
}

# ---------------------------------------------------------------- frozen vocabularies (owner messages / approved docs)
AVAIL = [('UNKNOWN', 'غير محدد — لا يظهر شيء للعميل'), ('AVAILABLE', 'متوفر'),
         ('UNAVAILABLE_SHOW', 'غير متوفر — يبقى ظاهرًا مع «غير متوفر حاليًا»'), ('UNAVAILABLE_HIDE', 'غير متوفر — مخفي من المنيو')]  # menu IA §9.6 · CMS-015
AVAIL_SHORT = {'UNKNOWN': 'غير محدد', 'AVAILABLE': 'متوفر', 'UNAVAILABLE_SHOW': 'غير متوفر — ظاهر', 'UNAVAILABLE_HIDE': 'غير متوفر — مخفي'}
FACT_ST = ['APPROVED', 'VERIFIED', 'PENDING VERIFICATION', 'MISSING', 'SUPERSEDED', 'REJECTED']  # M32 §03
DEP_ST = ['GOOD', 'UPDATE AVAILABLE', 'SECURITY UPDATE', 'CRITICAL']                            # M32 §38
RUM_ST = {'GOOD': 'جيد', 'NEEDS IMPROVEMENT': 'يحتاج تحسين', 'POOR': 'ضعيف'}                    # M32 §11
GUARD_ST = ['BLOCKING', 'WARNING', 'PASS']                                                       # M32 §05
PRESENT = ['شريط إعلان علوي', 'Hero Banner', 'ميزة في الصفحة الرئيسية', 'شريط عرض بعرض الصفحة', 'نافذة منبثقة (فقط عند الضرورة)']  # M31 §3
EXP_TYPES = ['إعلان', 'عرض / حملة', 'فعالية', 'مناسبة', 'تجربة موسمية', 'ذكرى العلامة', 'تكريم موظف', 'إشعار طارئ']             # M31 §9
PRIORITY = ['إشعار طارئ', 'فعالية كبرى', 'حملة', 'تجربة موسمية', 'تكريم', 'إعلان عادي']                                         # M32 §10
EOM_ST = ['مسودة', 'مجدول', 'نشط', 'منتهي', 'مؤرشف']                                                                             # M31 §2
SAFE_KEEP = ['المنيو', 'الفروع والساعات', 'التواصل', 'التنقل', 'الصفحات الأساسية']                                               # M32 §23
REPLY_FLOW = ['المراجعة', 'رد مقترح (AI ASSISTED)', 'تعديلك', 'اعتمادك', 'النشر (إذا سمح الربط)']                                  # M32 §14
DAYS = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة']
MONTHS = ['كانون الثاني', 'شباط', 'آذار', 'نيسان', 'أيار', 'حزيران', 'تموز', 'آب', 'أيلول', 'تشرين الأول', 'تشرين الثاني', 'كانون الأول']
LOCS = ['SHELTER COFFEE DRIVE', 'SHELTER COFFEE HOUSE']

# ---------------------------------------------------------------- sample data (obviously fake — never real business data)
PRODS = [  # id, ar, en, category, sub, price, drive, house, status, edited, flags
    ('PRD-DEMO-01', 'صنف تجريبي 01', 'Sample item 01', 'فئة تجريبية A', 'قسم فرعي A1', '2.50', 'AVAILABLE', 'UNAVAILABLE_SHOW', 'مسودة — غير منشور', '2026-10-01 09:12', 'تغيير غير منشور'),
    ('PRD-DEMO-02', 'صنف تجريبي 02', 'Sample item 02', 'فئة تجريبية A', 'قسم فرعي A1', '3.00', 'AVAILABLE', 'AVAILABLE', 'منشور', '2026-09-28 18:40', 'جديد'),
    ('PRD-DEMO-03', 'صنف تجريبي 03', '— ناقص —', 'فئة تجريبية A', 'قسم فرعي A2', '2.75', 'UNKNOWN', 'UNKNOWN', 'مسودة — غير منشور', '2026-09-30 11:05', 'بلا صورة'),
    ('PRD-DEMO-04', 'صنف تجريبي 04', 'Sample item 04', 'فئة تجريبية B', 'قسم فرعي B1', '3.50', 'AVAILABLE', 'UNAVAILABLE_HIDE', 'منشور', '2026-09-20 10:00', 'موسمي'),
    ('PRD-DEMO-05', 'صنف تجريبي 05', 'Sample item 05', 'فئة تجريبية B', 'قسم فرعي B1', '1.75', 'AVAILABLE', 'AVAILABLE', 'منشور', '2026-09-18 08:15', 'صورة كبيرة'),
    ('PRD-DEMO-06', 'صنف تجريبي 06', 'Sample item 06', 'فئة تجريبية B', 'قسم فرعي B2', '4.00', 'UNAVAILABLE_SHOW', 'AVAILABLE', 'منشور', '2026-09-12 16:30', ''),
    ('PRD-DEMO-07', 'صنف تجريبي 07', 'Sample item 07', 'فئة تجريبية C', 'قسم فرعي C1', '2.25', 'AVAILABLE', 'AVAILABLE', 'منشور', '2026-09-02 12:00', 'بلا صورة'),
    ('PRD-DEMO-08', 'صنف تجريبي 08', 'Sample item 08', 'فئة تجريبية C', 'قسم فرعي C1', '3.25', 'AVAILABLE', 'AVAILABLE', 'مؤرشف', '2026-08-30 09:00', ''),
]
CATS = ['فئة تجريبية A', 'فئة تجريبية B', 'فئة تجريبية C']
SUBS = ['قسم فرعي A1', 'قسم فرعي A2', 'قسم فرعي B1', 'قسم فرعي B2', 'قسم فرعي C1']
VISITS14 = [318, 342, 297, 405, 388, 361, 420, 352, 376, 331, 446, 402, 389, 412]  # DEMO

# ---------------------------------------------------------------- CSS = ONE design system (owner rule M34 — FROZEN P0)
# Shared, not edited here: design-system/build/tokens.css (generated tokens) + design-system/wireframe-kit.css (ds-* primitives).
# PAGE_CSS below is dashboard layout only (shell, sidebar, bottom nav, grids) and uses ONLY var(--…) tokens — no raw px sizes,
# radii, spacing or colours. Media-query breakpoints are the only literal widths (CSS cannot read custom properties there).
DS = os.path.normpath(os.path.join(ROOT, '..', '..', '..', '..', 'design-system'))
with open(os.path.join(DS, 'build', 'tokens.css'), encoding='utf-8') as fh:
    TOKENS_CSS = fh.read()
with open(os.path.join(DS, 'wireframe-kit.css'), encoding='utf-8') as fh:
    KIT_CSS = fh.read()

PAGE_CSS = '''
/* ---- dashboard page-level rules (tokens only) ---- */
a{color:inherit;display:inline-flex;align-items:center;min-height:var(--touch-target);min-width:var(--touch-target);text-underline-offset:var(--space-1)}
p{margin:0 0 var(--space-2)}ul,ol{margin:0}
.vh{position:absolute!important;width:1px;height:1px;padding:0;margin:0;overflow:hidden;clip-path:inset(50%);white-space:nowrap;border:0}
.wf-tag{background:var(--color-accent);color:var(--color-on-accent);font-size:var(--text-xs);padding:var(--space-1) var(--space-3)}
.legend{margin:var(--space-6) var(--space-4) var(--space-4)}
.ds-btn{line-height:var(--leading-tight);text-align:center}
.safe{color:var(--color-danger);border-color:currentColor;white-space:nowrap}
.ds-badge{background:var(--color-bg);vertical-align:middle;line-height:var(--leading-normal-ar)}
.ds-badge svg{vertical-align:middle}
.demo{border-style:dashed;color:var(--color-text);white-space:nowrap}
.pi{border-style:dotted;color:var(--color-info)}
.ai{border-style:double;color:var(--color-info)}
.tone-danger{color:var(--color-danger)}.tone-warning{color:var(--color-warning)}.tone-info{color:var(--color-info)}.tone-success{color:var(--color-success)}
.tone-muted{color:var(--color-muted)}.tone-strike{text-decoration:line-through}
.tone-solid{background:var(--color-accent);color:var(--color-on-accent);border-color:var(--color-accent)}
.demo-banner{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2) var(--space-3);border-style:dashed;color:var(--color-text);background:var(--color-surface);font-size:var(--text-sm)}
.fresh{display:flex;flex-wrap:wrap;gap:var(--space-1) var(--space-2);font-size:var(--text-xs);color:var(--color-muted);margin:var(--space-3) 0 0}
.note{font-size:var(--text-sm)}
.flex{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2)}
.mt{margin-block-start:var(--space-3)}.mb{margin-block-end:var(--space-4)}.m0{margin:0}.full{flex-basis:100%;margin:0}.wfull{width:100%}
.ht{font-size:var(--text-lg);margin:0 0 var(--space-2)}
.ok::before{content:"✓ "}
/* desktop shell (≥ 1024px) */
.dtop{position:sticky;top:0;z-index:var(--z-sticky);display:flex;align-items:center;gap:var(--space-3);min-height:calc(var(--space-12) + var(--space-3));padding:0 var(--space-4);background:var(--color-bg);border-block-end:var(--border-default) solid var(--color-border)}
.logo{font-weight:var(--weight-bold);font-size:var(--text-sm);white-space:nowrap;text-decoration:none}
.gsearch{flex:1 1 calc(var(--space-16) * 4);max-width:calc(var(--space-16) * 8);margin:0}
.owner{font-size:var(--text-sm);white-space:nowrap}
.dshell{display:grid;grid-template-columns:calc(var(--space-16) * 4) minmax(0,1fr)}
.dside{position:sticky;top:calc(var(--space-12) + var(--space-3));align-self:start;max-height:calc(100vh - var(--space-12) - var(--space-3));overflow:auto;border-inline-end:var(--border-default) solid var(--color-border);padding:var(--space-3) var(--space-2) var(--space-6)}
.dside .grp-home,.dside li a{display:flex;padding:0 var(--space-3);border-radius:var(--radius-md);text-decoration:none;font-size:var(--text-sm);line-height:var(--leading-tight)}
.dside .grp-home{font-weight:var(--weight-bold);font-size:var(--text-base);margin-block-end:var(--space-1)}
.dside details{border-block-start:var(--border-default) solid var(--color-border)}
.dside summary{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:var(--touch-target);padding:0 var(--space-3);font-weight:var(--weight-bold);cursor:pointer;list-style:none;border-radius:var(--radius-md)}
.dside summary::-webkit-details-marker{display:none}
.dside summary::after{content:"+";font-size:var(--text-lg)}.dside details[open]>summary::after{content:"−"}
.dside ul{list-style:none;margin:0 0 var(--space-2);padding:0}
.dside a[aria-current=page]{background:var(--color-accent);color:var(--color-on-accent);font-weight:var(--weight-bold)}
.secure{margin:var(--space-4) var(--space-3) 0;padding-block-start:var(--space-3);border-block-start:var(--border-default) solid var(--color-border)}
.ds-container.dmain{max-width:var(--container-dashboard);width:100%;min-width:0;padding-block:var(--space-5) var(--space-12)}
/* mobile shell (< 1024px): top bar + group chips + 5-item bottom nav */
body.m{padding-block-end:calc(var(--control-lg) + var(--space-5) + env(safe-area-inset-bottom))}
.mtop{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:calc(var(--space-12) + var(--space-2));padding:0 var(--space-3);background:var(--color-bg);border-block-end:var(--border-default) solid var(--color-border);z-index:var(--z-sticky)}
@media (min-height:500px){.mtop{position:sticky;top:0}}
.mtop-r{display:flex;align-items:center;gap:var(--space-1)}
.gnav{display:flex;gap:var(--space-2);overflow-x:auto;padding:var(--space-2) var(--space-3);border-block-end:var(--border-default) solid var(--color-border)}
.gnav .ds-chip{flex:none}
.gnav .ds-chip[aria-current=page],.seg .ds-chip[aria-current=page]{background:var(--color-accent);color:var(--color-on-accent);border-color:var(--color-accent);font-weight:var(--weight-bold)}
.ds-container.mmain{max-width:var(--container-dashboard);padding-block:var(--space-3) var(--space-6)}
.bnav{position:fixed;inset-inline:0;bottom:0;z-index:var(--z-header);background:var(--color-bg);border-block-start:var(--border-strong) solid var(--color-accent);display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:var(--space-1);padding:var(--space-1) var(--space-1) calc(var(--space-1) + env(safe-area-inset-bottom))}
.bnav a{display:flex;flex-direction:column;justify-content:center;min-height:var(--control-lg);min-width:0;text-decoration:none;font-size:var(--text-xs);border-radius:var(--radius-md);text-align:center;line-height:var(--leading-tight)}
.bnav a[aria-current=page]{background:var(--color-accent);color:var(--color-on-accent);font-weight:var(--weight-bold)}
[id]{scroll-margin-top:var(--sticky-stack)}
/* page content */
.ptitle{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-3);margin:0 0 var(--space-2)}
.ptitle h1{font-size:var(--text-3xl);margin:0}
.lead{color:var(--color-muted);font-size:var(--text-sm);margin:0 0 var(--space-4)}
.acts{display:flex;flex-wrap:wrap;gap:var(--space-2)}
main .ds-card,main .ds-alert{margin-block-end:var(--space-4);min-width:0}
.dc-h{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-2) var(--space-3);margin:0 0 var(--space-3)}
.dc-h>h2,.dc-h>h3{font-size:var(--text-lg);margin:0}
.ds-card>h2{font-size:var(--text-lg)}
.ds-alert>h2,.ds-alert>h3,.ds-alert .dc-h>h2{display:flex;align-items:center;gap:var(--space-2);font-size:var(--text-base);margin:0 0 var(--space-2)}
.cols2{display:grid;grid-template-columns:minmax(0,1fr);gap:0 var(--space-4)}
.kpis,.strip,.qa,.mgrid,.rcards,.na-list,.list{list-style:none;margin:0;padding:0;display:grid;gap:var(--space-3)}
.kpis,.mgrid{grid-template-columns:repeat(auto-fill,minmax(min(100%,calc(var(--space-16) * 3)),1fr))}
.strip{grid-template-columns:repeat(auto-fill,minmax(min(100%,calc(var(--space-20) * 3)),1fr))}
.qa{grid-template-columns:repeat(auto-fill,minmax(min(100%,calc(var(--space-16) * 2)),1fr))}
main .kpis .ds-card,main .strip .ds-card,main .mgrid .ds-card,main .rcards .ds-card,main .na-list .ds-card{margin:0}
.kpi,.tile{display:flex;flex-direction:column;align-items:stretch;gap:var(--space-1);text-decoration:none;height:100%}
.kpi{min-height:calc(var(--space-12) * 2)}
.kpi-l{font-size:var(--text-sm)}.kpi-n{font-size:var(--text-2xl);font-weight:var(--weight-bold);font-variant-numeric:tabular-nums}.kpi-d{font-size:var(--text-xs);color:var(--color-muted)}
.qa .ds-btn{width:100%;height:100%;min-height:var(--control-lg)}
.na-sum{list-style:none;padding:0;margin:0 0 var(--space-3);display:flex;flex-wrap:wrap;gap:var(--space-2)}
.na-sum .ds-chip{gap:var(--space-2)}
.na-grp{margin:0 0 var(--space-4)}
.na-grp>h3{display:flex;align-items:center;gap:var(--space-2);font-size:var(--text-base);margin:0 0 var(--space-2)}
.ds-card.na-item{padding-block:var(--space-1)}
.ds-card.na-item.critical{border:var(--border-strong) solid var(--color-danger)}
.na-link{display:flex;justify-content:space-between;gap:var(--space-3);width:100%;font-weight:var(--weight-bold);text-decoration:none}
.na-link .go{flex:none;font-weight:var(--weight-regular);text-decoration:underline;font-size:var(--text-sm)}
.rows{list-style:none;margin:0;padding:0}
.rows>li{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-1) var(--space-3);border-block-start:var(--border-default) solid var(--color-border);padding:var(--space-1) 0;min-height:var(--control-lg)}
.rows>li:first-child{border-block-start:0}
.chips{display:flex;flex-wrap:wrap;gap:var(--space-2);margin:0 0 var(--space-3);padding:0;list-style:none}
.ds-chip{gap:var(--space-2)}
.toolbar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:var(--space-3);margin:0 0 var(--space-3)}
.toolbar .ds-field{margin:0}.toolbar .grow{flex:1 1 calc(var(--space-20) * 3)}
.seg{display:flex;flex-wrap:wrap;gap:var(--space-2);margin:0;padding:0;list-style:none}
.tbl-wrap{overflow-x:auto;border:var(--border-default) solid var(--color-border);border-radius:var(--radius-lg)}
.tbl caption{text-align:start;padding:var(--space-2) var(--space-3);font-size:var(--text-xs);color:var(--color-muted)}
.tbl th,.tbl td{vertical-align:middle;overflow-wrap:anywhere}
.tbl thead th{font-size:var(--text-xs);background:var(--color-surface);white-space:nowrap}
.tbl tbody tr{height:var(--control-lg)}
.rc-t{font-weight:var(--weight-bold);margin:0 0 var(--space-1);overflow-wrap:anywhere}
.kv{margin:0}
.kv>div{display:grid;grid-template-columns:minmax(calc(var(--space-12) * 2),36%) minmax(0,1fr);gap:var(--space-2);border-block-start:var(--border-default) solid var(--color-border);padding:var(--space-1) 0}
.kv>div:first-child{border-block-start:0}
.kv dt{color:var(--color-muted);font-size:var(--text-sm)}.kv dd{margin:0;overflow-wrap:anywhere}
.adv{margin:var(--space-1) 0;font-size:var(--text-sm)}
.adv summary{display:inline-flex;align-items:center;min-height:var(--touch-target);cursor:pointer;font-weight:var(--weight-bold);text-decoration:underline}
.adv p{font-size:var(--text-xs);background:var(--color-surface);color:var(--color-text);border-radius:var(--radius-sm);padding:var(--space-2) var(--space-3);overflow-wrap:anywhere;text-align:left;margin:0 0 var(--space-2)}
.sum{display:flex;align-items:center;min-height:var(--touch-target);cursor:pointer}
/* forms (ds-field / ds-label / ds-input / ds-check from the kit) */
fieldset{border:0;margin:0;padding:0;min-width:0}
legend{padding:0}
legend.ds-label{margin-block-end:var(--space-1)}
.opts{display:flex;flex-wrap:wrap;gap:0 var(--space-4)}
.ds-check input{width:var(--icon-md);height:var(--icon-md);margin:0;flex:none;accent-color:var(--color-accent)}
.ds-check.cbx{min-width:var(--touch-target);justify-content:center}
.ds-textarea{resize:vertical}
.two{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,calc(var(--space-20) * 2 + var(--space-5))),1fr));gap:0 var(--space-3)}
.ig{display:flex;align-items:stretch}.ig .ds-input{border-start-end-radius:0;border-end-end-radius:0;min-width:0}
.ig .sfx{display:flex;align-items:center;padding:0 var(--space-4);border:var(--border-default) solid var(--color-border);border-inline-start:0;border-start-end-radius:var(--radius-md);border-end-end-radius:var(--radius-md);background:var(--color-surface);font-weight:var(--weight-bold);white-space:nowrap}
.ds-media.ph{max-width:calc(var(--space-16) * 3);flex-direction:column;gap:var(--space-1);text-align:center;padding:var(--space-2)}
.actbar{display:flex;flex-wrap:wrap;gap:var(--space-2);border-block-start:var(--border-strong) solid var(--color-accent);padding-block-start:var(--space-3);margin:var(--space-2) 0 var(--space-4)}
.edit-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:var(--space-4)}
.days{list-style:none;margin:0;padding:0;display:grid;gap:var(--space-2)}
.day{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:0 var(--space-3);padding:var(--space-2) var(--space-3)}
.day>p{grid-column:1/-1;font-weight:var(--weight-bold);margin:0}
.day .ds-field{margin:0}
.steps{list-style:none;margin:0 0 var(--space-3);padding:0;display:grid;gap:var(--space-2);counter-reset:s}
.steps li{counter-increment:s;display:flex;align-items:center;gap:var(--space-2);border:var(--border-strong) solid var(--color-border);border-radius:var(--radius-md);padding:var(--space-1) var(--space-3);min-height:var(--touch-target);font-size:var(--text-sm)}
.steps li::before{content:counter(s);display:inline-flex;align-items:center;justify-content:center;width:var(--icon-lg);height:var(--icon-lg);border-radius:var(--radius-pill);background:var(--color-accent);color:var(--color-on-accent);font-size:var(--text-xs);flex:none}
.steps li[aria-current]{border-color:var(--color-accent);font-weight:var(--weight-bold)}
.prio{margin:0;padding-inline-start:var(--space-5)}
.ds-card.media{padding:var(--space-2)}
.ds-card.media.sel{outline:var(--border-strong) solid var(--color-accent);border-color:var(--color-accent)}
.media .ds-media.ph{max-width:none;aspect-ratio:4/3}
.media p{margin:var(--space-1) 0 0;font-size:var(--text-sm);overflow-wrap:anywhere}
/* the one small trend chart (desktop analytics) */
.chart{margin:0 0 var(--space-2)}
.chart svg{display:block;width:100%;height:auto;max-height:calc(var(--space-20) * 2)}
.chart figcaption{font-size:var(--text-sm);color:var(--color-muted)}
.bar{fill:var(--color-muted)}.bar.cur{fill:var(--color-accent)}.base{stroke:var(--color-border)}
.axis{display:flex;justify-content:space-between;font-size:var(--text-xs);color:var(--color-muted);margin:var(--space-1) 0 var(--space-2)}
/* calendar */
.cal{width:100%;border-collapse:collapse;table-layout:fixed}
.cal caption{text-align:start;font-weight:var(--weight-bold);padding:0 0 var(--space-2)}
.cal th{font-size:var(--text-xs);padding:var(--space-2) var(--space-1);background:var(--color-surface);border:var(--border-default) solid var(--color-border)}
.cal td{vertical-align:top;border:var(--border-default) solid var(--color-border);height:calc(var(--space-20) + var(--space-6));padding:var(--space-1);font-size:var(--text-xs)}
.cal td.out{background:var(--color-surface)}
.cal td.today{outline:calc(var(--border-strong) * 2) solid var(--color-accent);outline-offset:calc(var(--border-strong) * -2)}
.dn{font-weight:var(--weight-bold);font-size:var(--text-sm);display:block}
.cal a.ev{display:flex;font-size:var(--text-xs);border:var(--border-default) solid var(--color-border);border-radius:var(--radius-sm);padding:0 var(--space-1);text-decoration:none;margin:var(--space-1) 0 0;overflow-wrap:anywhere;line-height:var(--leading-tight)}
.coll{color:var(--color-warning)}.cal .coll{white-space:normal;display:inline-block}
.agenda{list-style:none;margin:0;padding:0}
.agenda>li{border-block-start:var(--border-default) solid var(--color-border);padding:var(--space-2) 0}
.agenda>li>p{font-weight:var(--weight-bold);margin:0 0 var(--space-1)}
/* dialogs: kit .ds-modal / .ds-sheet + fixed header/footer with a scrolling body */
.ds-modal.dlg,.ds-sheet.dlg{display:flex;flex-direction:column;overflow:hidden;padding:0}
.ds-modal.dlg{width:100%}
.ds-sheet.dlg{max-height:calc(100dvh - var(--space-10))}
.dhead{display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);padding:var(--space-2) var(--space-4);border-block-end:var(--border-default) solid var(--color-border)}
.dhead h2{font-size:var(--text-lg);margin:0}
.dbody{overflow:auto;padding:var(--space-3) var(--space-4);flex:1 1 auto;min-height:0}
.dbody h3{font-size:var(--text-base);margin:var(--space-3) 0 var(--space-2)}
.dfoot{display:flex;flex-wrap:wrap;gap:var(--space-2);padding:var(--space-3) var(--space-4);border-block-start:var(--border-default) solid var(--color-border)}
.dfoot .ds-btn{flex:1 1 calc(var(--space-20) + var(--space-16))}
.impact{margin:0 0 var(--space-3);padding-inline-start:var(--space-5)}
.ds-card.gres{margin:0 0 var(--space-3);padding:var(--space-2) var(--space-3)}
.ds-card>legend.ht{float:inline-start;width:100%}.ds-card>legend.ht+*{clear:both}
.gres.blocking{border:var(--border-strong) solid var(--color-danger)}.gres.warning{border:var(--border-strong) solid var(--color-warning)}.gres.pass{border-style:dashed}
.gres h3{display:flex;align-items:center;gap:var(--space-2);margin:0 0 var(--space-1)}
.gres ul{list-style:none;margin:0;padding:0}
.gres li{border-block-start:var(--border-default) solid var(--color-border);padding:var(--space-1) 0}.gres li:first-child{border-block-start:0}
.idx{padding-block:0 var(--space-8)}
.idx h1{font-size:var(--text-3xl);margin:var(--space-5) 0 var(--space-2)}
@media (min-width:700px){.rcards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (min-width:1024px){
 .cols2{grid-template-columns:repeat(2,minmax(0,1fr))}
 .edit-grid{grid-template-columns:minmax(0,1fr) calc(var(--space-20) * 4)}
 .edit-grid>aside{position:sticky;top:calc(var(--space-16) + var(--space-3))}
 .days{grid-template-columns:repeat(2,minmax(0,1fr))}
}
'''
CSS = TOKENS_CSS + '\n' + KIT_CSS + '\n' + PAGE_CSS

# ---------------------------------------------------------------- icons
def svg(d, s=20, w=2):
    return f'<svg aria-hidden="true" focusable="false" width="{s}" height="{s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{w}" stroke-linecap="round" stroke-linejoin="round">{d}</svg>'
I_X = svg('<path d="M6 6l12 12M18 6L6 18"/>')
I_SAFE = svg('<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M12 8v5M12 16v.5"/>')
I_SEARCH = svg('<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>')
I_HOME = svg('<path d="M3 11l9-7 9 7v9H3z"/><path d="M10 20v-6h4v6"/>', 22)
I_DOC = svg('<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v4h4M9 12h6M9 16h6"/>', 22)
I_STAR = svg('<path d="M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6L12 16.4 6.7 19.4l1.2-6L3.4 9.3l6-.7z"/>', 22)
I_BRIEF = svg('<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V4h6v3"/>', 22)
I_MORE = svg('<circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>', 22, 3)
I_WARN = svg('<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.5"/>', 16, 2.5)
I_LOCK = svg('<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>', 16)
I_REFRESH = svg('<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 5v6h-6"/>', 18)
I_IMG = svg('<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 17l-5-5-9 8"/>', 28)
BICON = {'home': I_HOME, 'content': I_DOC, 'exp': I_STAR, 'business': I_BRIEF, 'more': I_MORE}

# ---------------------------------------------------------------- small helpers
def n(v, ltr=False):
    """A sample metric number. Must always sit inside a [data-demo] block that shows the DEMO label (tested)."""
    return f'<span data-n{" dir=ltr" if ltr else ""}>{v}</span>'

def demo(full=False):
    return f'<span class="ds-badge demo">{DEMO if full else "DEMO DATA"}</span>'

def banner(extra='كل الأرقام والأسماء والقيم في هذه الشاشة عيّنة للتوضيح فقط، وليست بيانات حقيقية.'):
    return f'<p class="ds-alert demo-banner">{demo(True)}<span>{extra}</span></p>'

def pending(src, note=''):
    return f'<span class="ds-badge pi" data-src="{esc(src)}"><b>{esc(src)}</b> — {PI}{(" · " + note) if note else ""}</span>'

def fresh(t='قبل 3 دقائق', src=''):
    return f'<p class="fresh"><span>آخر تحديث: {t}</span>{"<span>· " + src + "</span>" if src else ""}</p>'

def card(cid, title, body, demo_=True, fr='قبل 3 دقائق', src='', h='h2', cls='', extra_head=''):
    """Data card (.ds-card). demo_=True → carries the DEMO label (scope for every [data-n] inside) + a freshness line."""
    head = f'<div class="dc-h"><{h} id="{cid}-h">{title}</{h}><span class="flex">{extra_head}{demo() if demo_ else ""}</span></div>'
    tail = fresh(fr, src) if (demo_ and fr) else ''
    d = ' data-demo' if demo_ else ''
    return f'<section class="ds-card dcard {cls}" id="{cid}" aria-labelledby="{cid}-h"{d}>{head}{body}{tail}</section>'

STATUS_TONE = {'APPROVED': 'success', 'VERIFIED': 'success', 'PENDING VERIFICATION': 'warning', 'MISSING': 'danger', 'SUPERSEDED': 'muted tone-strike',
               'REJECTED': 'danger tone-strike', 'GOOD': 'success', 'UPDATE AVAILABLE': 'info', 'SECURITY UPDATE': 'warning', 'CRITICAL': 'danger'}
def chip_st(s):
    """Registry statuses (facts M32 §03, dependencies M32 §38): text label + tone, never colour alone."""
    return f'<span class="ds-badge tone-{STATUS_TONE[s]}">{esc(s)}</span>'

TONE_WORDS = [('solid', ('نشط', 'الحالية', 'جديد')), ('danger', ('فشل', 'مشبوه', 'MISSING', 'غير مسجلة')),
              ('warning', ('ينقص', 'جزئيًا', 'لم يُفحص', 'قيد المتابعة', 'بانتظار', 'Warning', 'مسودة', 'يتعارض', 'تحذير')),
              ('success', ('نجح', 'تم', 'سليم', 'مفعّل', 'مكتمل', 'على الخادم', 'Valid', 'منشور', 'مسجلة'))]
def st(s, tone=None):
    if tone is None:
        tone = next((t for t, words in TONE_WORDS if any(s.startswith(w) or w in s.split(' ')[0] for w in words)), '')
    return f'<span class="ds-badge{" tone-" + tone if tone else ""}">{esc(s)}</span>'

SEV = {'critical': 'حرج · CRITICAL', 'warning': 'تحذير · WARNING', 'info': 'معلومة · INFO', 'security': 'الأمان · SECURITY', 'seo': 'SEO'}
SEV_TONE = {'critical': 'danger', 'blocking': 'danger', 'warning': 'warning', 'info': 'info', 'security': 'solid', 'seo': 'info', 'pass': 'success'}
def sev(s, label=None):
    icon = I_LOCK if s == 'security' else (I_WARN if s in ('critical', 'warning', 'blocking') else '')
    return f'<span class="ds-badge sev-{s} tone-{SEV_TONE[s]}">{icon} {esc(label or SEV.get(s, s))}</span>'

def rum(s):
    tone = {'GOOD': 'success', 'NEEDS IMPROVEMENT': 'warning', 'POOR': 'danger'}[s]
    return f'<span class="ds-badge rum tone-{tone}">{RUM_ST[s]} · {s}</span>'

def adv(text):
    return f'<details class="adv"><summary>تفاصيل متقدمة</summary><p dir="ltr">{esc(text)}</p></details>'

def opt(v, sel=False):
    return f'<option{" selected" if sel else ""}>{esc(v)}</option>'

def sel(id_, label, options, selected=None, first=None, hint=''):
    o = (f'<option value="">{esc(first)}</option>' if first else '') + ''.join(opt(x, x == selected) for x in options)
    h = f'<span class="ds-help" id="{id_}-hint">{hint}</span>' if hint else ''
    d = f' aria-describedby="{id_}-hint"' if hint else ''
    return f'<div class="ds-field"><label class="ds-label" for="{id_}">{esc(label)}</label>{h}<select class="ds-select" id="{id_}"{d}>{o}</select></div>'

def inp(id_, label, value='', t='text', hint='', d='auto', cls='ds-field', ro=False, ph=''):
    h = f'<span class="ds-help" id="{id_}-hint">{hint}</span>' if hint else ''
    a = f' aria-describedby="{id_}-hint"' if hint else ''
    v = f' value="{esc(value)}"' if value else ''
    di = f' dir="{d}"' if t in ('text', 'search', 'url', 'tel') else ''
    p = f' placeholder="{esc(ph)}"' if ph else ''
    return f'<div class="{cls}"><label class="ds-label" for="{id_}">{label}</label>{h}<input class="ds-input" type="{t}" id="{id_}"{di}{v}{a}{p}{" readonly" if ro else ""}></div>'

def area(id_, label, value='', hint='', d='auto'):
    h = f'<span class="ds-help" id="{id_}-hint">{hint}</span>' if hint else ''
    a = f' aria-describedby="{id_}-hint"' if hint else ''
    return f'<div class="ds-field"><label class="ds-label" for="{id_}">{label}</label>{h}<textarea class="ds-textarea" id="{id_}" dir="{d}"{a}>{esc(value)}</textarea></div>'

def radios(name, legend, options, checked=None, hint=''):
    o = ''.join(f'<label class="ds-check"><input type="radio" name="{name}"{" checked" if x == checked else ""}> {esc(x)}</label>' for x in options)
    h = f'<span class="ds-help">{hint}</span>' if hint else ''
    return f'<fieldset class="ds-field" data-radios="{name}"><legend class="ds-label">{esc(legend)}</legend>{h}<div class="opts">{o}</div></fieldset>'

def checks(legend, options, checked=(), name=''):
    o = ''.join(f'<label class="ds-check"><input type="checkbox"{" checked" if x in checked else ""}> {esc(x)}</label>' for x in options)
    return f'<fieldset class="ds-field"{f" data-checks={name}" if name else ""}><legend class="ds-label">{esc(legend)}</legend><div class="opts">{o}</div></fieldset>'

def table(k, cap, cols, rows, cid=''):
    """Desktop: .ds-table inside a focusable scroll region. Mobile (< 1024): stacked .ds-card rows (no table)."""
    if k == 'd':
        h = ''.join(f'<th scope="col">{c}</th>' for c in cols)
        b = ''.join('<tr>' + ''.join((f'<th scope="row">{c}</th>' if i == 0 else f'<td>{c}</td>') for i, c in enumerate(r)) + '</tr>' for r in rows)
        return (f'<div class="tbl-wrap" role="region" tabindex="0" aria-label="{esc(re.sub("<[^>]+>", "", cap))}"><table class="ds-table tbl"{f" id={cid}" if cid else ""}>'
                f'<caption>{cap}</caption><thead><tr>{h}</tr></thead><tbody>{b}</tbody></table></div>')
    items = ''
    for r in rows:
        kv = ''.join(f'<div><dt>{cols[i]}</dt><dd>{c}</dd></div>' for i, c in enumerate(r) if i > 0 and c not in ('', None))
        items += f'<li class="ds-card rcard"><p class="rc-t">{r[0]}</p><dl class="kv">{kv}</dl></li>'
    return f'<p class="ds-caption">{cap}</p><ul class="rcards"{f" id={cid}" if cid else ""}>{items}</ul>'

def dialog(kind, did, title, body, foot, close):
    """kind: 'modal' (desktop, .ds-modal) or 'sheet' (mobile, .ds-sheet). Header and footer stay visible; the body scrolls."""
    return (f'<div class="ds-overlay" aria-hidden="true"></div><div class="ds-{kind} dlg" role="dialog" aria-modal="true" aria-labelledby="{did}">'
            f'<div class="dhead"><h2 id="{did}">{title}</h2><a class="ds-btn ds-btn--ghost ds-btn--icon x" href="{close}" aria-label="إغلاق">{I_X}</a></div>'
            f'<div class="dbody">{body}</div>{f"<div class=dfoot>{foot}</div>" if foot else ""}</div>')

def ptitle(h1, lead='', acts=''):
    return f'<div class="ptitle"><h1>{h1}</h1>{f"<div class=acts>{acts}</div>" if acts else ""}</div>{f"<p class=lead>{lead}</p>" if lead else ""}'

def refresh_btn():
    return f'<button type="button" class="ds-btn ds-btn--secondary">{I_REFRESH} تحديث البيانات</button>'

def kpi(href, label, value, delta=''):
    return f'<li><a class="ds-card kpi" href="{href}"><span class="kpi-l">{label}</span><span class="kpi-n">{n(value)}</span>{f"<span class=kpi-d>{delta}</span>" if delta else ""}</a></li>'

# ---------------------------------------------------------------- page + shell
def page(body, title, state, cls=''):
    tag = f'<div class="wf-tag">LOW-FI WIREFRAME · AR · {esc(state)}</div>'
    return (f'<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            f'<title>{esc(title)}</title><style>{CSS}</style></head><body class="{cls}">{tag}{body}</body></html>\n')

DATE = re.compile(r'(?<![\w="/-])(\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2})?)(?![\w"-])')

def write(name, content):
    head, sep, rest = content.partition('<body')
    content = head + sep + DATE.sub(r'<span dir="ltr">\1</span>', rest)
    with open(os.path.join(OUT, name), 'w', encoding='utf-8', newline='\n') as fh:
        fh.write(content)

def target(k, t):
    if t.startswith('EXT:'):
        return t[4:].format(k=k)
    m = re.match(r'([\w-]+)(.*)', t)
    return f'{k}-{m.group(1)}.html{m.group(2)}'

def L(k, key, suffix=''):
    return f'{k}-{key}.html{suffix}'

def sidebar(k, grp, item):
    out = ''
    for g, label, items in GROUPS:
        if g == 'home':
            cur = ' aria-current="page"' if item == 'home' else ''
            out += f'<a class="grp-home" data-group="home" href="{L(k, "home")}"{cur}><span class="gname">{label}</span></a>'
            continue
        links = ''.join(f'<li><a href="{target(k, t)}"{" aria-current=page" if key == item and g == grp else ""}>{esc(lab)}</a></li>' for key, lab, t in items)
        out += f'<details data-group="{g}"{" open" if g == grp else ""}><summary><span class="gname">{label}</span></summary><ul>{links}</ul></details>'
    secure = (f'<p class="secure">دخول آمن: Passkey + المصادقة الثنائية · <a href="{L(k, "security")}#logins">نشاط الدخول</a></p>')
    return f'<nav class="dside" aria-label="أقسام لوحة التحكم">{out}{secure}</nav>'

def gnav(k, grp, item):
    for g, label, items in GROUPS:
        if g == grp and g != 'home':
            links = ''.join(f'<a class="ds-chip" href="{target(k, t)}"{" aria-current=page" if key == item else ""}>{esc(lab)}</a>' for key, lab, t in items)
            return f'<nav class="gnav" aria-label="أقسام {label}">{links}</nav>'
    return ''

def bnav(k, grp):
    cur_more = grp in MORE_GROUPS or grp == 'more'
    links = ''
    for g, label, t in BOTTOM:
        on = (g == grp) or (g == 'more' and cur_more)
        links += f'<a href="{L(k, t)}"{" aria-current=page" if on else ""}>{BICON[g]}<span>{label}</span></a>'
    return f'<nav class="bnav" aria-label="التنقل الرئيسي">{links}</nav>'

def shell(k, key, main, after='', state=None):
    title, grp, item = SCREENS[key]
    safe = f'<a class="ds-btn ds-btn--outline safe" href="{L(k, "safe-mode")}" data-safe>{I_SAFE}<span>وضع الأمان</span></a>'
    if k == 'd':
        top = (f'<header class="dtop"><a class="logo" href="d-home.html">SHELTER OWNER DASHBOARD</a>'
               f'<form class="gsearch" role="search" action="#"><label class="vh" for="gs">بحث في لوحة التحكم</label>'
               f'<input class="ds-input" type="search" id="gs" dir="auto" placeholder="ابحث: صنف، صفحة، فرع، حملة…"></form>'
               f'{safe}<span class="owner">المالك</span></header>')
        body = top + f'<div class="dshell">{sidebar(k, grp, item)}<main class="ds-container dmain">{main}</main></div>'
        cls = 'd'
    else:
        top = (f'<header class="mtop"><a class="logo" href="m-home.html">SHELTER</a><div class="mtop-r">'
               f'<a class="ds-btn ds-btn--ghost ds-btn--icon" href="m-more.html#gs-m" aria-label="بحث في لوحة التحكم">{I_SEARCH}</a>{safe}</div></header>')
        body = top + gnav(k, grp, item) + f'<main class="ds-container mmain">{main}</main>' + bnav(k, grp)
        cls = 'm'
    body += after + ('<p class="wf-note legend">LOW-FI — ليست هوية بصرية. لوحة التحكم للمالك وحده في V1، والتحقق في الخادم وليس بإخفاء الواجهة. '
                     'كل الأرقام بيانات تجريبية (DEMO DATA). لا شيء هنا متصل ببيانات حية.</p>')
    write(f'{k}-{key}.html', page(body, f'{state or title} · Owner Dashboard', f'OWNER DASHBOARD · {"DESKTOP" if k == "d" else "MOBILE"} · {state or title}', cls))

# ================================================================= SCREENS
# 1 — Command Center home ------------------------------------------------------
def home_main(k):
    na = [
        ('critical', [('تتبع البحث داخل الموقع لا يسجّل منذ يومين — لا نعرف ماذا يبحث الزوار', L(k, 'seo', '#search'), 'site_search events: 0 received since 2026-09-29 (collector endpoint returns 5xx)')]),
        ('warning', [('صورة الصفحة الرئيسية كبيرة وتبطئ تحميل الصفحة', L(k, 'performance', '#alerts'), 'Hero LCP resource 2.8MB'),
                     (f'{n(3)} صور أصناف كبيرة وتبطئ صفحة المنيو', L(k, 'media', '?filter=oversized'), None),
                     ('صفحة «من نحن» بالإنجليزية أقدم من العربية', L(k, 'parity'), None)]),
        ('info', [('حملة «يوم القهوة العالمي» تنتهي الليلة 23:59', L(k, 'active', '#exp-1'), None),
                  (f'مسودات في المنيو تنتظر النشر: {n(2)}', L(k, 'menu', '?status=draft'), None)]),
        ('security', [('النسخة الاحتياطية لقاعدة البيانات فشلت الليلة الماضية', L(k, 'health', '#backups'), 'db backup job exit code 1 at 03:00 (storage quota)'),
                      (f'{n(5)} محاولات دخول فاشلة من جهاز غير معروف', L(k, 'security', '#logins'), None)]),
        ('seo', [('رابط Canonical خاطئ في صفحة فرع DRIVE', L(k, 'seo', '#issues'), 'rel=canonical → /ar/jo/drive-old/ (404)'),
                 (f'{n(4)} عمليات بحث بلا نتائج هذا الأسبوع', L(k, 'seo', '#zero'), None)]),
    ]
    summ = ''.join(f'<li><a class="ds-chip" href="#na-{s}">{sev(s)} <b>{n(len(items))}</b></a></li>' for s, items in na)
    grps = ''
    for s, items in na:
        lis = ''
        for text, href, a in items:
            lis += (f'<li class="ds-card na-item {s}"><a class="na-link" href="{href}"><span>{text}</span><span class="go">افتح</span></a>'
                    f'{adv(a) if a else ""}</li>')
        grps += f'<section class="na-grp" id="na-{s}" data-sev="{s}" aria-labelledby="na-{s}-h"><h3 id="na-{s}-h">{sev(s)}</h3><ul class="na-list">{lis}</ul></section>'
    attention = card('na', 'يحتاج انتباهك', f'<ul class="na-sum">{summ}</ul>{grps}', fr='قبل دقيقتين', src='فحوص صحة الموقع (كل ساعة)')

    live = [('exp-1', 'حملة', 'يوم القهوة العالمي', 'نشط · ينتهي اليوم 23:59 · الشريط العلوي + Hero', L(k, 'active', '#exp-1')),
            ('exp-2', 'تكريم', 'الموظف المثالي — تشرين الأول', 'نشط · حتى 2026-10-31', L(k, 'family', '#eom')),
            ('exp-3', 'إعلان', 'ساعات خاصة لفرع HOUSE', 'مجدول · يبدأ اليوم 20:00 · يتعارض', L(k, 'active', '#exp-3'))]
    strip = ''.join(f'<li><a class="ds-card tile" href="{h}"><span class="ds-caption">{t}</span><b>{esc(ti)}</b><span>{d}</span></a></li>' for _, t, ti, d, h in live)
    nxt = (f'<ul class="rows"><li><span>يبدأ التالي: <b>ثيم موسمي تجريبي</b></span><a href="{L(k, "calendar")}#d-20">2026-10-20 18:00</a></li>'
           f'<li><span>ينتهي التالي: <b>حملة يوم القهوة العالمي</b></span><a href="{L(k, "active")}#exp-1">اليوم 23:59</a></li></ul>')
    active = card('live', 'النشط الآن على الموقع', f'<ul class="strip">{strip}</ul>{nxt}', demo_=False,
                  extra_head=f'<a href="{L(k, "active")}">كل التجارب</a>')

    src = pending('GA4', 'الأرقام عيّنة')
    kp = (f'<ul class="kpis">{kpi(L(k, "analytics", "?range=today"), "زيارات اليوم", "412", "ارتفاع 8% عن أمس")}'
          f'{kpi(L(k, "analytics", "?range=7d"), "زيارات هذا الأسبوع", "2,950", "ارتفاع 5% عن الأسبوع الماضي")}'
          f'{kpi(L(k, "analytics", "?range=month"), "زيارات هذا الشهر", "11,830", "انخفاض 2% عن الشهر الماضي")}</ul>')
    visits = card('visits', 'الزيارات', kp, src=src)

    quick = [('تغيير سعر صنف', L(k, 'menu')), ('تغيير توفر صنف', L(k, 'menu', '?view=availability')), ('تعديل ساعات فرع', L(k, 'branch', '#hours')),
             ('تشغيل أو إيقاف حملة', L(k, 'active')), ('رفع صورة', L(k, 'media', '#upload')), ('مراجعة صحة الموقع', L(k, 'health'))]
    qa = '<section class="ds-card panel" aria-labelledby="qa-h"><h2 id="qa-h" class="vh">إجراءات سريعة</h2><ul class="qa">' + ''.join(f'<li><a class="ds-btn ds-btn--outline" href="{h}">{t}</a></li>' for t, h in quick) + '</ul></section>'

    sources = (f'<ul class="rows"><li>{pending("GA4")}<a href="{L(k, "analytics")}#source">ما المطلوب للربط؟</a></li>'
               f'<li>{pending("Search Console")}<a href="{L(k, "seo")}#gsc">ما المطلوب للربط؟</a></li>'
               f'<li>{pending("Google Business Profile")}<a href="{L(k, "reputation")}">ما المطلوب للربط؟</a></li>'
               f'<li><span>قياس الأداء الذاتي (RUM) — يعمل · {demo()}</span><a href="{L(k, "performance")}">الأداء الفعلي</a></li></ul>')
    srcs = card('sources', 'مصادر البيانات', sources, demo_=False)
    if k == 'd':
        layout = f'{attention}<div class="cols2"><div>{active}{qa}</div><div>{visits}{srcs}</div></div>'
    else:
        layout = f'{attention}{qa}{active}{visits}{srcs}'
    return ptitle('مركز القيادة', 'ما يحتاج انتباهك، وما يعمل الآن على الموقع، وملخص الزيارات. كل بند يفتح مكانه.', refresh_btn()) + banner() + layout

# 2 — Analytics overview -------------------------------------------------------
def chart_svg():
    w, h, gap, top = 560, 150, 4, 16
    bw = (w - gap * 13) / 14
    mx = max(VISITS14)
    bars = ''
    for i, v in enumerate(VISITS14):
        bh = (v / mx) * (h - top - 4)
        x = i * (bw + gap)
        y = h - bh
        bars += (f'<path d="M{x:.1f},{h} V{y + 4:.1f} Q{x:.1f},{y:.1f} {x + 4:.1f},{y:.1f} H{x + bw - 4:.1f} Q{x + bw:.1f},{y:.1f} {x + bw:.1f},{y + 4:.1f} V{h} Z" '
                 f'class="bar{" cur" if i == 13 else ""}"><title>اليوم {i + 1}: {v} زيارة</title></path>')
    return (f'<svg viewBox="0 0 {w} {h}" role="img" aria-labelledby="ch-t ch-d" preserveAspectRatio="none">'
            f'<title id="ch-t">الزيارات اليومية — آخر 14 يومًا</title><line class="base" x1="0" y1="{h}" x2="{w}" y2="{h}" stroke-width="1"/>{bars}</svg>')

def analytics_main(k):
    tb = ('<div class="toolbar">' + sel('range', 'الفترة', ['اليوم', 'أمس', 'آخر 7 أيام', 'آخر 30 يومًا', 'هذا الشهر', 'الشهر السابق', 'فترة مخصصة'], 'آخر 7 أيام')
          + sel('cmp', 'المقارنة', ['الفترة السابقة', 'السنة السابقة (إذا توفرت البيانات)', 'بلا مقارنة'], 'الفترة السابقة') + '</div>')
    source = (f'<section class="ds-alert ds-alert--info" id="source" aria-labelledby="src-h"><h2 id="src-h" class="vh">مصدر البيانات</h2>'
              f'<p>{pending("GA4")} ربط Google Analytics 4 بلوحة التحكم ينتظر صلاحيتك (PO-012). حتى ذلك الحين كل الأرقام أدناه <b>{DEMO}</b> لتوضيح الشكل فقط.</p>'
              '<p class="ds-muted note">بعد الربط: تُحدَّث الأرقام كل 15 دقيقة من الخادم (لا استدعاء مع كل فتح للصفحة)، ويظهر «آخر تحديث» على كل بطاقة.</p></section>')
    top10 = [('الزوار', '2,180', '#pages', 'ارتفاع 6%'), ('الجلسات', '2,950', '#pages', 'ارتفاع 5%'), ('مشاهدات الصفحات', '8,420', '#pages', 'ارتفاع 4%'),
             ('مشاهدات المنيو', '1,960', '#menu-a', 'ارتفاع 9%'), ('مشاهدات الأصناف', '3,310', '#menu-a', 'ارتفاع 3%'),
             ('نقرات واتساب', '142', '#actions', 'انخفاض 4%'), ('نقرات الاتصال', '96', '#actions', 'ارتفاع 2%'), ('نقرات الاتجاهات', '231', '#actions', 'ارتفاع 11%'),
             ('عمليات البحث', '640', L(k, 'seo', '#search'), 'ارتفاع 7%'), ('بحث بلا نتائج', '38', L(k, 'seo', '#zero'), 'ارتفاع 12%')]
    kp = '<ul class="kpis">' + ''.join(kpi(h, l, v, d + ' عن الفترة السابقة') for l, v, h, d in top10) + '</ul>'
    summary = card('summary', 'الملخص — آخر 7 أيام', kp, src=pending('GA4'))
    if k == 'd':
        tr = (f'<figure class="chart">{chart_svg()}<div class="axis" aria-hidden="true"><span>قبل 14 يومًا</span><span>اليوم: {n(412)}</span></div>'
              f'<figcaption id="ch-d">الزيارات اليومية لآخر 14 يومًا. أعلى يوم {n(446)}، وأقل يوم {n(297)}. آخر عمود (اليوم) بلون أغمق.</figcaption></figure>'
              '<details class="adv"><summary>عرض كجدول</summary>' + table('d', 'الزيارات اليومية (DEMO)', ['اليوم', 'الزيارات'],
                                                                             [[f'قبل {13 - i} يوم' if i < 13 else 'اليوم', n(v)] for i, v in enumerate(VISITS14)]) + '</details>')
        trend = card('trend', 'اتجاه الزيارات', tr, src=pending('GA4'))
    else:
        trend = card('trend', 'اتجاه الزيارات', f'<p>الزيارات في آخر 7 أيام <b>{n("2,950")}</b> — أعلى بـ{n("5%")} من الأسبوع الذي قبله. أعلى يوم {n(446)} وأقل يوم {n(297)}.</p>'
                     '<p class="ds-muted note">على الشاشات الصغيرة نعرض ملخصًا بدل الرسم البياني.</p>', src=pending('GA4'))
    pages = [('الرئيسية', '2,410', '1,620', '0:48'), ('المنيو', '1,960', '1,340', '2:05'), ('SHELTER COFFEE DRIVE', '820', '640', '0:52'),
             ('SHELTER COFFEE HOUSE', '610', '470', '0:47'), ('من نحن', '240', '210', '0:39')]
    pt = table(k, f'أكثر الصفحات زيارة · {DEMO}', ['الصفحة', 'المشاهدات', 'المستخدمون', 'متوسط الوقت', 'التفاصيل'],
               [[p, n(v), n(u), n(t), f'<a href="{L(k, "health")}?page={i}">صحة الصفحة</a>'] for i, (p, v, u, t) in enumerate(pages)])
    srcs = [('بحث Google (Organic)', '1,240', '61%'), ('مباشر (Direct)', '820', '54%'), ('السوشال', '610', '47%'), ('إحالة (Referral)', '170', '58%'), ('مدفوع', '0', '—'), ('بريد', '0', '—'), ('أخرى', '110', '40%')]
    st_ = table(k, f'من أين يأتي الزوار · {DEMO}', ['القناة', 'الجلسات', 'نسبة التفاعل'], [[a, n(b), n(c)] for a, b, c in srcs])
    act = table(k, f'إجراءات العملاء حسب الفرع · {DEMO}', ['الإجراء', 'DRIVE', 'HOUSE', 'تواصل عام'],
                [['الاتجاهات', n(150), n(81), '—'], ['الاتصال', n(52), n(31), n(13)], ['واتساب', n(70), n(48), n(24)]])
    dev = (f'<ul class="rows"><li><span>موبايل</span><b>{n("78%")}</b></li><li><span>ديسكتوب</span><b>{n("18%")}</b></li><li><span>تابلت</span><b>{n("4%")}</b></li>'
           f'<li><span>العربية</span><b>{n("71%")}</b></li><li><span>الإنجليزية</span><b>{n("29%")}</b></li></ul>')
    mp = table(k, f'أكثر الأصناف مشاهدة · {DEMO}', ['الصنف', 'المشاهدات', 'إجراء'],
               [[p[1], n(v), f'<a href="{L(k, "product")}">تعديل الصنف</a>'] for p, v in zip(PRODS[:4], ['420', '355', '298', '240'])])
    return (ptitle('التحليلات', 'الزيارات وما يفعله الزوار على الموقع. كل رقم يفتح تفاصيله.', refresh_btn()) + banner() + tb + source + summary + trend
            + card('pages', 'أكثر الصفحات زيارة', pt, src=pending('GA4'))
            + f'<div class="cols2">{card("sources-c", "مصادر الزيارات", st_, src=pending("GA4"))}{card("actions", "إجراءات العملاء", act, src=pending("GA4"))}</div>'
            + f'<div class="cols2">{card("devices", "الأجهزة واللغة", dev, src=pending("GA4"))}{card("menu-a", "المنيو", mp, src=pending("GA4"))}</div>')

# 3 — Menu editor list ---------------------------------------------------------
def menu_main(k):
    acts = f'<a class="ds-btn ds-btn--outline" href="{L(k, "product")}">إضافة صنف</a><button type="button" class="ds-btn ds-btn--outline">معاينة</button><a class="ds-btn ds-btn--primary" href="{L(k, "impact")}">نشر التغييرات</a>'
    draft = (f'<section class="ds-alert ds-alert--info" aria-labelledby="dr-h" data-demo><h2 id="dr-h" class="vh">حالة النشر</h2><p>{demo()} <b>لديك {n(3)} تغييرات غير منشورة</b> في نسخة المنيو الجديدة. '
             'العملاء يرون النسخة المنشورة فقط حتى تضغط «نشر التغييرات».</p></section>')
    tb = ('<div class="toolbar">' + inp('q', 'بحث في الأصناف', t='search', cls='ds-field grow', ph='الاسم بالعربية أو الإنجليزية')
          + sel('cat', 'الفئة', CATS, first='كل الفئات') + sel('br', 'الفرع', LOCS, first='كل الفروع') + sel('stf', 'الحالة', ['منشور', 'مسودة', 'مؤرشف'], first='كل الحالات') + '</div>')
    chips = (f'<ul class="chips"><li><a class="ds-chip" href="?filter=no-image">بلا صورة ({n(2)})</a></li><li><a class="ds-chip" href="?filter=oversized">صور كبيرة ({n(3)})</a></li>'
             f'<li><a class="ds-chip" href="?filter=new">جديد ({n(1)})</a></li><li><a class="ds-chip" href="?filter=seasonal">موسمي ({n(1)})</a></li><li><a class="ds-chip" href="?status=draft">مسودات ({n(2)})</a></li></ul>')
    rows = []
    for p in PRODS:
        name = f'<a href="{L(k, "product")}">{esc(p[1])}</a><br><span class="ds-caption" dir="ltr">{esc(p[2])}</span>'
        flag = f' {st(p[10])}' if p[10] else ''
        if k == 'd':
            cb = f'<label class="ds-check cbx"><input type="checkbox"><span class="vh">تحديد {esc(p[1])}</span></label>'
            rows.append([cb + ' ' + name, f'{p[3]}<br><span class="ds-caption">{p[4]}</span>', n(p[5] + ' د.أ'), AVAIL_SHORT[p[6]], AVAIL_SHORT[p[7]], st(p[8]) + flag, p[9],
                         f'<a class="ds-btn ds-btn--secondary" href="{L(k, "product")}">تعديل</a>'])
        else:
            rows.append([name, f'{p[3]} · {p[4]}', n(p[5] + ' د.أ'), AVAIL_SHORT[p[6]], AVAIL_SHORT[p[7]], st(p[8]) + flag,
                         f'<span class="flex"><a class="ds-btn ds-btn--secondary" href="{L(k, "product")}#price">تغيير السعر</a><a class="ds-btn ds-btn--secondary" href="{L(k, "product")}#avail">التوفر</a></span>'])
    if k == 'd':
        t = table(k, f'الأصناف ({n(8)} من {n(186)}) · {DEMO}', ['الصنف', 'الفئة / القسم الفرعي', 'السعر', 'DRIVE', 'HOUSE', 'الحالة', 'آخر تعديل', 'إجراء'], rows)
        bulk = ('<p class="ds-muted note">حدّد أصنافًا لإجراء جماعي: تغيير الفئة · متوفر · غير متوفر · موسمي · توفر فرع. '
                '<b>تغيير الأسعار جماعيًا يحتاج تأكيدًا قويًا</b> ويُسجَّل كل سعر قديم وجديد.</p>')
    else:
        t = table(k, f'الأصناف ({n(8)} من {n(186)}) · {DEMO}', ['الصنف', 'الفئة', 'السعر', 'DRIVE', 'HOUSE', 'الحالة', 'إجراء'], rows)
        bulk = ''
    legend = ('<p class="ds-muted note">التوفر لكل فرع: <b>غير محدد</b> (لا يظهر شيء للعميل) · <b>متوفر</b> · <b>غير متوفر — ظاهر</b> · <b>غير متوفر — مخفي</b>. '
              'لا حذف للأصناف — الأرشفة فقط.</p>')
    return (ptitle('المنيو', 'الأصناف والأسعار والتوفر. التعديل يذهب إلى مسودة، ولا يصل للعميل قبل النشر.', acts) + banner() + draft + tb
            + card('items', 'الأصناف', chips + t + bulk + legend, fr='الآن', src='قاعدة بيانات الموقع'))

# 4 — Product editor -----------------------------------------------------------
def product_main(k):
    p = PRODS[0]
    acts = f'<a class="ds-btn ds-btn--secondary" href="{L(k, "audit")}#versions">سجل النسخ</a><button type="button" class="ds-btn ds-btn--outline">معاينة</button>'
    names = ('<section class="ds-card sect" aria-labelledby="s-name"><h2 id="s-name">الاسم والوصف</h2><div class="two">'
             + inp('name-ar', 'الاسم بالعربية', p[1]) + inp('name-en', 'الاسم بالإنجليزية', p[2], d='ltr') + '</div><div class="two">'
             + area('desc-ar', 'الوصف بالعربية', 'وصف تجريبي قصير للصنف.') + area('desc-en', 'الوصف بالإنجليزية', 'Short sample description.', d='ltr') + '</div></section>')
    price = (f'<section class="ds-card sect" id="price" aria-labelledby="s-price" data-demo><div class="dc-h"><h2 id="s-price">السعر</h2>{demo()}</div>'
             '<div class="ds-field"><label class="ds-label" for="pr">السعر الجديد</label><span class="ds-help" id="pr-hint">السعر المنشور الآن: '
             f'{n("2.50 د.أ")}. التغيير ينشئ نسخة منيو جديدة، ويُسجَّل القديم والجديد.</span>'
             '<div class="ig"><input class="ds-input" type="text" id="pr" inputmode="decimal" dir="ltr" value="2.75" aria-describedby="pr-hint"><span class="sfx" aria-hidden="true">د.أ</span></div></div></section>')
    cat = ('<section class="ds-card sect" aria-labelledby="s-cat"><h2 id="s-cat">التصنيف والترتيب</h2><div class="two">'
           + sel('pcat', 'الفئة', CATS, p[3]) + sel('psub', 'القسم الفرعي', SUBS, p[4]) + '</div>'
           + inp('sort', 'ترتيب الظهور داخل القسم', '3', hint='ترتيب يدوي — لا ترتيب عشوائي.', d='ltr') + '</section>')
    av = ''
    for i, (b, cur) in enumerate([(LOCS[0], 'AVAILABLE'), (LOCS[1], 'UNAVAILABLE_SHOW')]):
        o = ''.join(f'<label class="ds-check"><input type="radio" name="av-{i}"{" checked" if code == cur else ""}> {esc(t)}</label>' for code, t in AVAIL)
        av += f'<fieldset class="ds-field" data-branch="{b}"><legend class="ds-label">{b}</legend><div class="opts">{o}</div></fieldset>'
    avail = f'<section class="ds-card sect" id="avail" aria-labelledby="s-av"><h2 id="s-av">التوفر لكل فرع</h2>{av}</section>'
    img = (f'<section class="ds-card sect" id="image" aria-labelledby="s-img"><h2 id="s-img">الصورة</h2>'
           f'<div class="ds-media ph">{I_IMG}<span>صورة معتمدة · IMG-DEMO-014 · 1:1</span></div>'
           f'<p class="flex mt"><a class="ds-btn ds-btn--outline" href="{L(k, "media")}?pick=1">اختيار من مركز الوسائط</a><a class="ds-btn ds-btn--secondary" href="{L(k, "media")}#upload">رفع صورة جديدة</a></p>'
           '<p class="ds-muted note">النص البديل والحقوق ونقطة التركيز تُدار في مركز الوسائط. الصورة تُرفع مرة واحدة وتُستخدم في كل مكان.</p></section>')
    badges = ('<section class="ds-card sect" aria-labelledby="s-bd"><h2 id="s-bd">الشارات</h2>'
              '<div class="ds-field"><label class="ds-check"><input type="checkbox" id="is-new" checked> جديد</label></div>'
              + inp('new-until', 'يبقى «جديد» حتى', '2026-10-31', t='date')
              + '<div class="ds-field"><label class="ds-check"><input type="checkbox" id="is-season"> موسمي</label></div><div class="two">'
              + inp('season-from', 'الموسم من', t='date') + inp('season-to', 'الموسم إلى', t='date') + '</div></section>')
    advs = ('<details class="ds-card sect adv-sect"><summary class="ds-label sum">إعدادات متقدمة (محمية)</summary>'
            '<p class="ds-muted note">تغييرها قد يؤثر على الظهور في Google — يُطلب تأكيد إضافي.</p>'
            + inp('slug', 'الرابط (Slug)', 'sample-item-01', d='ltr') + inp('seo-t', 'عنوان SEO', 'Sample item 01 — SHELTER COFFEE', d='ltr') + '</details>')
    bar = (f'<div class="actbar"><button type="button" class="ds-btn ds-btn--outline">حفظ كمسودة</button><button type="button" class="ds-btn ds-btn--outline">معاينة (عربي · English · موبايل · ديسكتوب)</button>'
           f'<a class="ds-btn ds-btn--primary" href="{L(k, "impact")}">نشر…</a><button type="button" class="ds-btn ds-btn--secondary">أرشفة</button></div>'
           '<p class="ds-muted note">لا حذف نهائي للأصناف — الأرشفة فقط، ويمكن الاستعادة.</p>')
    side = card('pub', 'حالة النشر', f'<dl class="kv"><div><dt>الحالة</dt><dd>{st("مسودة — غير منشور")}</dd></div><div><dt>المنشور الآن</dt><dd>النسخة 2</dd></div>'
                '<div><dt>آخر تعديل</dt><dd>2026-10-01 09:12 · المالك</dd></div></dl>'
                f'<h3 class="mt">سجل النسخ</h3><ul class="rows"><li><span>النسخة 3 (مسودة)</span><b>{n("2.75 د.أ")}</b></li>'
                f'<li><span>النسخة 2 (منشورة)</span><b>{n("2.50 د.أ")}</b></li><li><span>النسخة 1</span><b>{n("2.25 د.أ")}</b></li></ul>'
                f'<a href="{L(k, "audit")}#versions">كل النسخ وما تغيّر</a>', h='h2', fr='الآن', src='قاعدة بيانات الموقع')
    form = names + price + cat + avail + img + badges + advs + bar
    return (ptitle(f'تعديل صنف: {p[1]}', 'كل تعديل يُحفظ مسودة. قبل النشر ترى أثر التغيير ونتيجة فحص النشر.', acts) + banner()
            + f'<div class="edit-grid"><div>{form}</div><aside aria-label="حالة النشر والنسخ">{side}</aside></div>')

def impact_dialog(k):
    body = ('<p><b>هذا التغيير يؤثر على:</b></p><ul class="impact" id="impact-list">'
            '<li>صفحة المنيو — العربية والإنجليزية</li><li>بطاقة الصنف ونافذة تفاصيله</li><li>نتائج البحث في الموقع</li>'
            '<li>فرع HOUSE: سيظهر الصنف «غير متوفر حاليًا»</li><li>بيانات Schema للمنيو (السعر)</li></ul>'
            f'<section data-demo aria-labelledby="chg-h"><div class="dc-h"><h3 id="chg-h">ما الذي تغيّر</h3>{demo()}</div>'
            f'<dl class="kv"><div><dt>السعر</dt><dd>{n("2.50")} ← {n("2.75")} د.أ</dd></div><div><dt>توفر HOUSE</dt><dd>متوفر ← غير متوفر — ظاهر</dd></div>'
            '<div><dt>الوصف بالإنجليزية</dt><dd>بلا تغيير</dd></div></dl></section>'
            f'<p class="ds-muted note">يظهر للعملاء فقط بعد النشر. القديم والجديد يُسجَّلان في سجل التدقيق. <a href="{L(k, "product")}?preview=1">معاينة الأثر</a></p>')
    foot = f'<a class="ds-btn ds-btn--primary" href="{L(k, "guard")}">متابعة إلى فحص النشر</a><a class="ds-btn ds-btn--outline" href="{L(k, "product")}">رجوع للتعديل</a>'
    return dialog('modal' if k == 'd' else 'sheet', 'imp-h', 'قبل النشر: ما الذي سيتغيّر؟', body, foot, L(k, 'product'))

def guard_dialog(k):
    body = ('<p><b>النتيجة: لا يمكن النشر بعد</b> — مشكلة مانعة واحدة، وتحذيران، و6 فحوص ناجحة.</p>'
            '<section class="ds-card gres blocking" data-guard="BLOCKING" aria-labelledby="g-b"><h3 id="g-b">' + sev('blocking', 'يمنع النشر · BLOCKING') + ' (1)</h3><ul>'
            f'<li>الاسم بالإنجليزية فارغ. <a href="{L(k, "product")}#name-en">اذهب للحقل</a></li></ul></section>'
            '<section class="ds-card gres warning" data-guard="WARNING" aria-labelledby="g-w"><h3 id="g-w">' + sev('warning', 'تحذير · WARNING') + ' (2)</h3><ul>'
            f'<li>صورة الصنف كبيرة وتبطئ صفحة المنيو. <a href="{L(k, "media")}#asset">افتح الصورة</a>{adv("Image transfer 1.9MB, 2400x2400 JPEG, no AVIF/WebP variant; LCP candidate on /menu")}</li>'
            f'<li>النص البديل بالإنجليزية للصورة ناقص. <a href="{L(k, "media")}#asset">افتح الصورة</a></li></ul></section>'
            '<section class="ds-card gres pass" data-guard="PASS" aria-labelledby="g-p"><h3 id="g-p">' + sev('pass', 'ناجح · PASS') + ' (6)</h3><ul>'
            '<li>المحتوى العربي كامل</li><li>السعر بصيغة صحيحة</li><li>الفئة والقسم الفرعي موجودان</li><li>الصورة معتمدة منك</li><li>الروابط سليمة</li>'
            '<li>لا حقائق PENDING VERIFICATION في هذا المحتوى</li></ul></section>'
            '<fieldset class="ds-card sect" id="override"><legend class="ht">تجاوز التحذيرات</legend>'
            '<p class="ds-muted note">التحذيرات لا تمنع النشر إذا كتبت السبب. <b>المشاكل المانعة لا يمكن تجاوزها.</b></p>'
            + area('ov-r', 'سبب التجاوز (يُسجَّل في سجل التدقيق)', hint='مثال: الصورة ستُستبدل بنسخة مضغوطة غدًا.') + '</fieldset>')
    foot = (f'<button type="button" class="ds-btn ds-btn--primary" disabled aria-describedby="pub-why">نشر رغم التحذيرات</button>'
            f'<a class="ds-btn ds-btn--outline" href="{L(k, "product")}#name-en">رجوع للتعديل</a><p class="ds-muted note full" id="pub-why">يتفعّل النشر بعد حل المشكلة المانعة.</p>')
    return dialog('modal' if k == 'd' else 'sheet', 'grd-h', 'فحص النشر', body, foot, L(k, 'product'))

# 7 — Branch editor ------------------------------------------------------------
def branch_main(k):
    sw = f'<ul class="seg" aria-label="الفرع"><li><a class="ds-chip" href="{L(k, "branch")}" aria-current="page">{LOCS[0]}</a></li><li><a class="ds-chip" href="{L(k, "branch")}?b=house">{LOCS[1]}</a></li></ul>'
    status = f'<p class="ds-alert ds-alert--info">الحالة على الموقع الآن: <b>مفتوح — حتى 22:00</b> (تُحسب بتوقيت عمّان) · {demo()}</p>'
    if k == 'd':
        rows = [[d, f'<label class="vh" for="o{i}">{d} — يفتح</label><input class="ds-input" type="time" id="o{i}" value="08:00">',
                 f'<label class="vh" for="c{i}">{d} — يغلق</label><input class="ds-input" type="time" id="c{i}" value="22:00">'] for i, d in enumerate(DAYS)]
        hours = table('d', f'الساعات العادية · {DEMO}', ['اليوم', 'يفتح', 'يغلق'], rows)
    else:
        hours = f'<p class="ds-caption">الساعات العادية · {DEMO}</p><ul class="days">' + ''.join(
            f'<li class="day"><p>{d}</p><div class="ds-field"><label class="ds-label" for="o{i}"><span class="vh">{d} — </span>يفتح</label><input class="ds-input" type="time" id="o{i}" value="08:00"></div>'
            f'<div class="ds-field"><label class="ds-label" for="c{i}"><span class="vh">{d} — </span>يغلق</label><input class="ds-input" type="time" id="c{i}" value="22:00"></div></li>' for i, d in enumerate(DAYS)) + '</ul>'
    regular = (f'<section class="ds-card sect" id="hours" aria-labelledby="s-h" data-demo><div class="dc-h"><h2 id="s-h">الساعات العادية</h2>{demo()}</div>{hours}'
               '<p class="ds-muted note">الإغلاق بعد منتصف الليل مدعوم (مثل 02:00). الساعات الخاصة لا تغيّر الساعات العادية.</p></section>')
    sp_rows = [['رمضان (مثال)', '2027-02-17 ← 2027-03-18', '19:00–01:00', 'مسودة'], ['تغيير مؤقت: صيانة', '2026-10-15 ← 2026-10-16', '12:00–22:00', 'مجدول']]
    special = (f'<section class="ds-card sect" id="special" aria-labelledby="s-sp" data-demo><div class="dc-h"><h2 id="s-sp">ساعات خاصة (رمضان · عطلة · تغيير مؤقت)</h2>{demo()}</div>'
               + table(k, f'الساعات الخاصة لهذا الفرع · {DEMO}', ['النوع', 'من ← إلى', 'الساعات', 'الحالة'], [[a, b, n(c), st(d)] for a, b, c, d in sp_rows])
               + '<h3 class="ht mt">إضافة ساعات خاصة</h3>' + sel('sp-type', 'النوع', ['رمضان', 'عطلة', 'تغيير مؤقت'])
               + '<div class="two">' + inp('sp-from', 'من تاريخ', t='date') + inp('sp-to', 'إلى تاريخ', t='date') + inp('sp-o', 'يفتح', t='time') + inp('sp-c', 'يغلق', t='time') + '</div>'
               + inp('sp-r', 'السبب', hint='يظهر لك فقط في السجل، وليس للعملاء.') + checks('تطبيق على', LOCS, (LOCS[0],), 'sp-br')
               + '<button type="button" class="ds-btn ds-btn--outline">إضافة</button></section>')
    closure = ('<section class="ds-card sect" id="closure" aria-labelledby="s-cl"><h2 id="s-cl">إغلاق مؤقت أو طارئ</h2>'
               '<div class="ds-field"><label class="ds-check"><input type="checkbox" id="cl-on"> الفرع مغلق مؤقتًا</label></div><div class="two">'
               + inp('cl-from', 'من', t='date') + inp('cl-to', 'حتى', t='date') + '</div>' + inp('cl-r', 'السبب')
               + '<p class="ds-muted note">سيرى العملاء: «مغلق مؤقتًا — يفتح …». الأولوية: طارئ ← مؤقت ← خاص/عطلة ← عادي.</p></section>')
    contact = (f'<section class="ds-card sect" id="contact" aria-labelledby="s-ct" data-demo><div class="dc-h"><h2 id="s-ct">التواصل والعنوان — من البيانات العامة</h2>{demo()}</div>'
               f'<dl class="kv"><div><dt>الهاتف</dt><dd dir="ltr">{n("+962 7 0000 0001")}</dd></div><div><dt>واتساب</dt><dd dir="ltr">{n("+962 7 0000 0002")}</dd></div>'
               '<div><dt>العنوان</dt><dd>عنوان تجريبي</dd></div><div><dt>رابط الخريطة</dt><dd>رابط تجريبي</dd></div></dl>'
               f'<p class="ds-muted note">الهاتف والواتساب والعنوان لا تُكتب هنا: قيمة واحدة لكل الموقع. <a href="{L(k, "global-data")}#contacts">تعديل في البيانات العامة</a></p></section>')
    impact = ('<section class="ds-alert ds-alert--info" aria-labelledby="bi-h"><h2 id="bi-h" class="ht">تعديل الساعات يؤثر على:</h2>'
              '<ul class="impact"><li>صفحة الفرع</li><li>«مفتوح الآن / مغلق» في المنيو والرئيسية</li><li>بيانات Schema للفرع (LocalBusiness)</li>'
              f'<li>Google Business Profile — {pending("Google Business Profile", "المزامنة بعد الربط")}</li></ul></section>')
    bar = '<div class="actbar"><button type="button" class="ds-btn ds-btn--outline">حفظ كمسودة</button><button type="button" class="ds-btn ds-btn--outline">معاينة</button><button type="button" class="ds-btn ds-btn--primary">نشر الساعات</button></div>'
    return (ptitle('الفروع والساعات', 'الساعات العادية، والساعات الخاصة، والإغلاق المؤقت. يمكن تعديلها من الهاتف.', sw) + banner() + status
            + regular + special + closure + contact + impact + bar)

# 8 — Experiences: Active Now --------------------------------------------------
def active_main(k):
    acts = f'<a class="ds-btn ds-btn--primary" href="{L(k, "experience")}">إنشاء تجربة</a><a class="ds-btn ds-btn--outline" href="{L(k, "calendar")}">التقويم</a>'
    coll = (f'<section class="ds-alert ds-alert--warning" id="collision" aria-labelledby="co-h"><h2 id="co-h">{I_WARN} تعارض: تجربتان على الشريط العلوي اليوم 20:00–23:59</h2>'
            '<p>«حملة يوم القهوة العالمي» و«ساعات خاصة لفرع HOUSE» تريدان نفس المكان. <b>سيظهر الأعلى أولوية فقط</b> (الحملة).</p>'
            '<p>التوصية: انقل الإعلان إلى صفحة الفرع، أو أجّله إلى الغد.</p>'
            f'<p class="flex"><a class="ds-btn ds-btn--outline" href="{L(k, "experience")}#priority">تغيير الأولوية</a><a class="ds-btn ds-btn--secondary" href="{L(k, "calendar")}#d-1">افتح في التقويم</a></p></section>')
    exps = [('exp-1', 'عرض / حملة', 'حملة يوم القهوة العالمي', 'الشريط العلوي + Hero', 'كل الفروع', 'نشط', 'ينتهي اليوم 23:59', 'حملة'),
            ('exp-2', 'تكريم موظف', 'الموظف المثالي — تشرين الأول', 'قسم في الرئيسية + SHELTER Family', 'كل الفروع', 'نشط', 'ينتهي 2026-10-31 23:59', 'تكريم'),
            ('exp-3', 'إعلان', 'ساعات خاصة لفرع HOUSE', 'الشريط العلوي', 'HOUSE', 'مجدول — يتعارض', 'يبدأ اليوم 20:00', 'إعلان عادي')]
    lis = ''
    for i, (eid, typ, title, place, br, status, when, pr) in enumerate(exps):
        lis += (f'<li class="ds-card rcard" id="{eid}"><p class="flex"><span class="ds-badge">{typ}</span>{st(status, "solid" if status == "نشط" else "warning")}</p>'
                f'<h3 class="ht">{title}</h3><dl class="kv"><div><dt>المكان</dt><dd>{place}</dd></div><div><dt>الفروع</dt><dd>{br}</dd></div>'
                f'<div><dt>الوقت</dt><dd>{when} (بتوقيت عمّان)</dd></div></dl>'
                + sel(f'pr-{i}', 'الأولوية', PRIORITY, pr)
                + f'<p class="flex"><button type="button" class="ds-btn ds-btn--danger">أوقف الآن</button><a class="ds-btn ds-btn--outline" href="{L(k, "experience")}">تعديل</a>'
                  f'<button type="button" class="ds-btn ds-btn--secondary">تمديد</button></p></li>')
    live = f'<section aria-labelledby="live-h"><h2 id="live-h" class="ht">يؤثر على الموقع الآن</h2><ul class="rcards">{lis}</ul></section>'
    soon = (f'<section class="ds-card panel mt" aria-labelledby="soon-h"><h2 id="soon-h" class="ht">يبدأ قريبًا</h2><ul class="rows">'
            f'<li><span><b>مقال مجدول (تجريبي)</b> · المعرفة</span><a href="{L(k, "calendar")}#d-8">2026-10-08 10:00</a></li>'
            f'<li><span><b>ثيم موسمي تجريبي</b> · كل الموقع</span><a href="{L(k, "calendar")}#d-20">2026-10-20 18:00</a></li>'
            f'<li><span><b>حملة تجريبية 2</b> · Hero</span><a href="{L(k, "calendar")}#d-22">2026-10-22 09:00</a></li></ul></section>')
    prio = ('<section class="ds-card panel" aria-labelledby="pr-h"><h2 id="pr-h" class="ht">ترتيب الأولوية الافتراضي</h2><ol class="prio">'
            + ''.join(f'<li>{p}</li>' for p in PRIORITY) + '</ol><p class="ds-muted note">تجربة واحدة فقط لكل مكان، وثيم موسمي واحد فقط في نفس الوقت. يمكنك تغيير أولوية أي تجربة.</p></section>')
    empty = '<p class="ds-muted note">إذا لم يكن هناك شيء نشط: لا يظهر على الموقع أي مكان فارغ أو بانر فارغ — يعود الموقع لوضعه الطبيعي.</p>'
    return ptitle('النشط الآن', 'كل ما يظهر على الموقع الآن، بتوقيت عمّان. «أوقف الآن» يعمل فورًا ويُسجَّل.', acts) + banner() + coll + live + soon + prio + empty

# 9 — Global content calendar --------------------------------------------------
EVENTS = [  # day(s), label, type, place, href-key, collision
    ((1,), 'حملة: يوم القهوة العالمي', 'campaign', 'الشريط العلوي + Hero', 'active#exp-1'),
    ((1,), 'إعلان: ساعات خاصة HOUSE', 'announcement', 'الشريط العلوي', 'active#exp-3'),
    ((8,), 'مقال مجدول (تجريبي)', 'article', 'المعرفة', 'later#knowledge'),
    ((15, 16), 'ساعات خاصة: HOUSE — صيانة', 'hours', 'صفحة الفرع', 'branch#special'),
    ((20, 21, 22, 23, 24, 25), 'ثيم موسمي تجريبي', 'seasonal', 'كل الموقع (Hero)', 'experience'),
    ((22, 23), 'حملة تجريبية 2', 'campaign', 'Hero', 'experience'),
    ((29,), 'فعالية: مشاركة في معرض (تجريبي)', 'event', 'الرئيسية + مركز الوسائط', 'experience'),
]
COLL_DAYS = {1: 'الشريط العلوي', 22: 'Hero', 23: 'Hero'}

def calendar_main(k):
    acts = f'<a class="ds-btn ds-btn--primary" href="{L(k, "experience")}">إضافة إلى التقويم</a>'
    top = (f'<ul class="strip"><li><a class="ds-card tile" href="{L(k, "active")}"><span class="ds-caption">النشط الآن</span><b>{n(3)} تجارب</b></a></li>'
           f'<li><a class="ds-card tile" href="{L(k, "active")}#exp-3"><span class="ds-caption">يبدأ التالي</span><b>ساعات خاصة HOUSE — اليوم 20:00</b></a></li>'
           f'<li><a class="ds-card tile" href="{L(k, "active")}#exp-1"><span class="ds-caption">ينتهي التالي</span><b>حملة يوم القهوة — اليوم 23:59</b></a></li></ul>')
    view = 'month' if k == 'd' else 'list'
    seg = ('<ul class="seg" aria-label="طريقة العرض">' + ''.join(
        f'<li><a class="ds-chip" href="{L(k, "calendar")}?view={v}"{" aria-current=page" if v == view else ""}>{t}</a></li>' for v, t in [('month', 'شهر'), ('week', 'أسبوع'), ('list', 'قائمة')]) + '</ul>')
    tb = (f'<div class="toolbar">{seg}' + sel('ctype', 'النوع', ['حملات وعروض', 'فعاليات', 'مواسم', 'الموظف المثالي', 'مقالات مجدولة', 'ساعات خاصة', 'الذكرى السنوية'], first='كل الأنواع')
          + f'<p class="flex m0"><a class="ds-btn ds-btn--secondary" href="?m=2026-09">الشهر السابق</a><a class="ds-btn ds-btn--secondary" href="?m=2026-11">الشهر التالي</a></p></div>')
    span = (f'<p class="ds-muted note">طوال الشهر: <a href="{L(k, "family")}#eom">الموظف المثالي — تشرين الأول</a> · الذكرى السنوية للعلامة 20/04 تظهر في نيسان.</p>')
    by_day = {}
    for days, label, typ, place, hk in EVENTS:
        for d in days:
            by_day.setdefault(d, []).append((label, typ, place, target(k, hk), d == days[0], days))
    coll_mark = lambda d: f'<span class="ds-badge coll">{I_WARN} تعارض: {COLL_DAYS[d]}</span>' if d in COLL_DAYS else ''
    if k == 'd':
        first_wd = (datetime.date(2026, 10, 1).weekday() + 2) % 7  # Saturday-first week (Jordan)
        ndays = calendar.monthrange(2026, 10)[1]
        cells = [None] * first_wd + list(range(1, ndays + 1))
        cells += [None] * (-len(cells) % 7)
        rows = ''
        for w in range(0, len(cells), 7):
            tds = ''
            for d in cells[w:w + 7]:
                if d is None:
                    tds += '<td class="out"></td>'
                    continue
                evs = ''.join(f'<a class="ev" href="{h}">{esc(l) if first else "↳ " + esc(l)}</a>' for l, _, _, h, first, _ in by_day.get(d, []))
                tds += f'<td id="d-{d}"{" class=today" if d == 1 else ""}><span class="dn">{d}{" · اليوم" if d == 1 else ""}</span>{coll_mark(d)}{evs}</td>'
            rows += f'<tr>{tds}</tr>'
        grid = (f'<table class="cal" aria-describedby="cal-note"><caption>تشرين الأول 2026 · {DEMO}</caption><thead><tr>'
                + ''.join(f'<th scope="col">{d}</th>' for d in DAYS) + f'</tr></thead><tbody>{rows}</tbody></table>'
                '<p class="ds-muted note" id="cal-note">⚠ تعارض = أكثر من تجربة على نفس المكان في نفس الوقت. الموقع يعرض الأعلى أولوية فقط.</p>')
        body = span + grid
    else:
        items = ''
        for d in sorted(by_day):
            evs = ''.join(f'<li class="rows-i"><a href="{h}">{esc(l)}</a> <span class="ds-caption">· {esc(p)}</span></li>' for l, _, p, h, first, _ in by_day[d] if first or d in COLL_DAYS)
            if not evs:
                continue
            items += f'<li id="d-{d}"><p>{d} تشرين الأول{" · اليوم" if d == 1 else ""} {coll_mark(d)}</p><ul class="list">{evs}</ul></li>'
        body = (span + f'<p class="ds-caption">تشرين الأول 2026 · {DEMO}</p><ul class="agenda">{items}</ul>'
                '<p class="ds-muted note">على الهاتف يفتح التقويم بعرض «قائمة»: شبكة الشهر بسبعة أعمدة لا تعطي أهداف لمس كافية على 320px.</p>')
    colls = (f'<section class="ds-alert ds-alert--warning" aria-labelledby="cc-h" data-demo><h2 id="cc-h">{I_WARN} تعارضات هذا الشهر ({n(2)}) {demo()}</h2><ul class="rows">'
             f'<li><span>1 تشرين الأول — الشريط العلوي: حملة يوم القهوة + إعلان ساعات HOUSE</span><a href="{L(k, "active")}#collision">حل التعارض</a></li>'
             f'<li><span>22–23 تشرين الأول — Hero: ثيم موسمي تجريبي + حملة تجريبية 2</span><a href="{L(k, "experience")}#priority">تغيير الأولوية</a></li></ul></section>')
    cal = card('cal', 'التقويم', tb + body, fr='الآن', src='التجارب والمحتوى المجدول')
    return (ptitle('التقويم', 'كل ما له موعد في مكان واحد: الحملات، الفعاليات، المواسم، الموظف المثالي، المقالات المجدولة، الساعات الخاصة، الذكرى السنوية.', acts)
            + banner() + card('next', 'الآن والتالي', top, fr='الآن', src='محرك التجارب') + colls + cal)

# 10 — Campaign / experience editor --------------------------------------------
def experience_main(k):
    acts = f'<a class="ds-btn ds-btn--secondary" href="{L(k, "audit")}#versions">سجل النسخ</a>'
    say = ('<p class="ds-alert ds-alert--info"><b>بلغة بسيطة:</b> سيظهر هذا الإعلان في أعلى الموقع وفي الواجهة الرئيسية حتى 23:59 بتوقيت عمّان، في كل الفروع.</p>')
    basics = ('<section class="ds-card sect" aria-labelledby="e-b"><h2 id="e-b">المحتوى</h2>' + sel('etype', 'نوع التجربة', EXP_TYPES, 'عرض / حملة')
              + '<div class="two">' + inp('et-ar', 'العنوان بالعربية', 'يوم القهوة العالمي') + inp('et-en', 'العنوان بالإنجليزية', 'International Coffee Day', d='ltr') + '</div>'
              + '<div class="two">' + area('ed-ar', 'الوصف بالعربية', 'نص تجريبي للعرض.') + area('ed-en', 'الوصف بالإنجليزية', 'Sample offer text.', d='ltr') + '</div>'
              + '<div class="two">' + inp('ec-ar', 'نص الزر بالعربية', 'اعرف أكثر') + inp('ec-en', 'نص الزر بالإنجليزية', 'Learn more', d='ltr') + '</div>'
              + inp('ec-url', 'رابط الزر', '/ar/jo/menu/', t='url', d='ltr') + '</section>')
    pres = f'<section class="ds-card sect" aria-labelledby="e-p"><h2 id="e-p">شكل الظهور</h2>{radios("pres", "شكل العرض", PRESENT, "Hero Banner", "شكل واحد لكل تجربة — لا نعرض كل الأشكال معًا.")}</section>'
    sched = ('<section class="ds-card sect" id="schedule" aria-labelledby="e-s"><h2 id="e-s">الجدولة</h2><div class="two">'
             + inp('es-d', 'يبدأ — التاريخ', '2026-10-01', t='date') + inp('es-t', 'يبدأ — الوقت', '00:00', t='time')
             + inp('ee-d', 'ينتهي — التاريخ', '2026-10-01', t='date') + inp('ee-t', 'ينتهي — الوقت', '23:59', t='time') + '</div>'
             '<p class="ds-muted note">المنطقة الزمنية: عمّان (Asia/Amman). يبدأ وينتهي تلقائيًا، ويعود الموقع لوضعه الطبيعي بعد الانتهاء.</p>'
             + checks('الفروع', ['كل الفروع'] + LOCS, ('كل الفروع',), 'ebr') + '</section>')
    prio = ('<section class="ds-card sect" id="priority" aria-labelledby="e-pr"><h2 id="e-pr">الأولوية والتعارض</h2>'
            + sel('eprio', 'الأولوية', PRIORITY, 'حملة', hint='الافتراضي حسب النوع. يمكنك تغييره.')
            + f'<div class="ds-alert ds-alert--warning"><h3>{I_WARN} سيتعارض مع «ساعات خاصة لفرع HOUSE»</h3><p>على الشريط العلوي يوم 2026-10-01 من 20:00 حتى 23:59. سيظهر الأعلى أولوية فقط.</p>'
              f'<a href="{L(k, "calendar")}#d-1">افتح في التقويم</a></div></section>')
    media = (f'<section class="ds-card sect" aria-labelledby="e-m"><h2 id="e-m">الوسائط</h2><div class="ds-media ph">{I_IMG}<span>IMG-DEMO-031 · معتمدة · صالحة للموقع</span></div>'
             f'<p class="flex mt"><a class="ds-btn ds-btn--outline" href="{L(k, "media")}?pick=1">اختيار من مركز الوسائط</a></p>'
             '<div class="ds-field"><label class="ds-check"><input type="checkbox" id="e-cd" checked> عدّاد تنازلي (يُحسب من وقت الخادم)</label></div>'
             '<div class="ds-field"><label class="ds-check"><input type="checkbox" id="e-mo"> حركة خفيفة (تتوقف تلقائيًا لمن يطلب تقليل الحركة)</label></div></section>')
    terms = ('<section class="ds-card sect" aria-labelledby="e-t"><h2 id="e-t">الشروط</h2><div class="two">' + area('tm-ar', 'الشروط بالعربية', 'شروط تجريبية.')
             + area('tm-en', 'الشروط بالإنجليزية', 'Sample terms.', d='ltr') + '</div></section>')
    prev = ('<fieldset class="ds-card sect" id="preview"><legend class="ht">المعاينة حسب التاريخ</legend><div class="two">' + radios('pv-l', 'اللغة', ['العربية', 'English'], 'العربية')
            + radios('pv-d', 'الجهاز', ['موبايل', 'ديسكتوب'], 'موبايل') + '</div><div class="two">'
            + inp('pv-date', 'أرني الموقع في تاريخ', '2026-10-01', t='date') + inp('pv-time', 'الساعة', '21:00', t='time') + '</div>'
            '<button type="button" class="ds-btn ds-btn--outline">افتح المعاينة</button><p class="ds-muted note">المعاينة لا تغيّر الموقع الحي.</p></fieldset>')
    bar = ('<div class="actbar"><button type="button" class="ds-btn ds-btn--outline">حفظ كمسودة</button><button type="button" class="ds-btn ds-btn--primary">جدولة</button>'
           '<button type="button" class="ds-btn ds-btn--outline">تمديد</button><button type="button" class="ds-btn ds-btn--outline">إيقاف مؤقت</button><button type="button" class="ds-btn ds-btn--secondary">إلغاء التجربة</button>'
           '<button type="button" class="ds-btn ds-btn--secondary">أرشفة</button></div>')
    side = card('es', 'الحالة', f'<dl class="kv"><div><dt>الحالة</dt><dd>{st("نشط", "solid")}</dd></div><div><dt>ينتهي</dt><dd>اليوم 23:59</dd></div>'
                f'<div><dt>نقرات الزر اليوم</dt><dd>{n(86)}</dd></div><div><dt>مشاهدات</dt><dd>{n("1,120")}</dd></div></dl>'
                f'<button type="button" class="ds-btn ds-btn--danger wfull mt">أوقف الآن</button>', fr='قبل 5 دقائق', src='قياس الموقع (بلا بيانات شخصية)')
    form = say + basics + pres + sched + prio + media + terms + prev + bar
    return (ptitle('تعديل تجربة: حملة يوم القهوة العالمي', 'حملة، أو فعالية، أو مناسبة، أو ثيم موسمي — نفس المحرر لكل الأنواع.', acts) + banner()
            + f'<div class="edit-grid"><div>{form}</div><aside aria-label="حالة التجربة">{side}</aside></div>')

# 11 — Employee of the Month & SHELTER Family ----------------------------------
def family_main(k):
    people = [('موظف تجريبي 1', 'باريستا', 'الخدمة', 'DRIVE', True, 'منشور'), ('موظف تجريبي 2', 'مشرف وردية', 'الخدمة', 'HOUSE', True, 'منشور'),
              ('موظف تجريبي 3', 'محاسب', 'الإدارة', '—', True, 'مخفي'), ('موظف تجريبي 4', 'باريستا', 'الخدمة', 'HOUSE', False, 'مخفي'),
              ('موظف تجريبي 5', 'مدرب قهوة', 'الجودة', 'DRIVE', True, 'منشور')]
    eom = (f'<section class="ds-card sect" id="eom" aria-labelledby="eom-h"><div class="dc-h"><h2 id="eom-h">الموظف المثالي — تشرين الأول 2026</h2>{st("نشط", "solid")}</div><div class="two">'
           + sel('eom-p', 'الموظف', [p[0] for p in people if p[4]], 'موظف تجريبي 1', hint='من الملفات العامة التي وافق أصحابها على النشر فقط.')
           + sel('eom-m', 'الشهر', MONTHS, 'تشرين الأول') + sel('eom-y', 'السنة', ['2026', '2027'], '2026') + sel('eom-s', 'الحالة', EOM_ST, 'نشط') + '</div>'
           + f'<div class="ds-media ph">{I_IMG}<span>صورة من مركز الوسائط · موافقة الشخص على النشر: مسجلة</span></div>'
           + '<div class="two mt">' + inp('eom-tar', 'العنوان بالعربية', 'الموظف المثالي لهذا الشهر') + inp('eom-ten', 'العنوان بالإنجليزية', 'Employee of the Month', d='ltr') + '</div>'
           + '<div class="two">' + area('eom-rar', 'نص التكريم بالعربية', 'نص تكريم تجريبي.') + area('eom-ren', 'نص التكريم بالإنجليزية', 'Sample recognition text.', d='ltr') + '</div>'
           + checks('أماكن الظهور', ['الصفحة الرئيسية', 'SHELTER Family', 'مركز الوسائط'], ('الصفحة الرئيسية', 'SHELTER Family'), 'eom-pl')
           + '<p class="flex"><button type="button" class="ds-btn ds-btn--outline">حفظ</button><button type="button" class="ds-btn ds-btn--primary">نشر</button></p>'
           '<p class="ds-muted note">عند انتهاء الشهر لا يُحذف شيء: ينتقل التكريم إلى الأرشيف تلقائيًا.</p></section>')
    rows = []
    for i, (nm, job, dep, br, ok, vis) in enumerate(people):
        cons = 'مسجلة · النسخة 1 · 2026-09-20' if ok else '<b>غير مسجلة</b>'
        pub = (f'<button type="button" class="ds-btn ds-btn--outline">{"إخفاء" if vis == "منشور" else "نشر"}</button>' if ok else
               f'<button type="button" class="ds-btn ds-btn--outline" disabled aria-describedby="nc-{i}">نشر</button><span class="ds-caption" id="nc-{i}">لا نشر بلا موافقة الموظف</span>')
        act = (f'<span class="flex"><button type="button" class="ds-btn ds-btn--secondary">تعديل</button>{pub}'
               f'<button type="button" class="ds-btn ds-btn--ghost ds-btn--icon" aria-label="تحريك {nm} للأعلى">↑</button><button type="button" class="ds-btn ds-btn--ghost ds-btn--icon" aria-label="تحريك {nm} للأسفل">↓</button></span>')
        rows.append([nm, job, dep, br, cons, st(vis, 'success' if vis == 'منشور' else 'muted'), act])
    fam = (f'<section class="ds-card sect" id="family" aria-labelledby="fm-h" data-demo><div class="dc-h"><h2 id="fm-h">SHELTER Family — الملفات العامة</h2>{demo()}</div>'
           + table(k, f'الملفات العامة ({n(5)}) · {DEMO}', ['الاسم المعروض', 'المسمى', 'القسم', 'الفرع', 'موافقة النشر', 'الظهور', 'إجراءات'], rows)
           + '<p class="flex mt"><button type="button" class="ds-btn ds-btn--outline">إضافة ملف عام</button></p>'
           '<p class="ds-alert ds-alert--info">الملف العام منفصل تمامًا عن سجل الموظف الداخلي وعن طلبات التوظيف: <b>لا راتب، ولا هاتف، ولا رقم هوية، ولا حضور، ولا مستندات.</b> '
           'لا يظهر أي موظف قبل تسجيل موافقته وتفعيلك للنشر.</p></section>')
    hist = ('<section class="ds-card panel" aria-labelledby="eh-h"><h2 id="eh-h" class="ht">الأرشيف</h2><ul class="rows">'
            '<li><span>أيلول 2026 — موظف تجريبي 2</span>' + st('منتهي — في الأرشيف') + '</li><li><span>آب 2026 — موظف تجريبي 5</span>' + st('منتهي — في الأرشيف') + '</li></ul></section>')
    return ptitle('SHELTER Family والموظف المثالي', 'التكريم الشهري وفريق SHELTER العلني — بموافقة كل موظف.') + banner() + eom + fam + hist

# 12 — Media Center ------------------------------------------------------------
def media_main(k):
    up = ('<div class="acts" id="upload"><input type="file" id="up" class="vh" accept="image/*,video/*"><label for="up" class="ds-btn ds-btn--primary">رفع صورة أو فيديو</label></div>')
    filters = (f'<ul class="chips"><li><a class="ds-chip" href="?f=all" aria-current="true">الكل ({n(48)})</a></li><li><a class="ds-chip" href="?f=pending">بانتظار اعتمادك ({n(5)})</a></li>'
               f'<li><a class="ds-chip" href="?filter=oversized">كبيرة الحجم ({n(3)})</a></li><li><a class="ds-chip" href="?filter=no-alt">بلا نص بديل ({n(8)})</a></li>'
               f'<li><a class="ds-chip" href="?filter=unused">غير مستخدمة ({n(6)})</a></li><li><a class="ds-chip" href="?filter=rights">حقوق تنتهي قريبًا ({n(1)})</a></li>'
               f'<li><a class="ds-chip" href="#press-kit">Press Kit ({n(9)})</a></li></ul>')
    assets = [('IMG-DEMO-014', '1.9 MB', '2400×2400', 'معتمدة', 'الموقع ✓ · الإعلانات ✗', 3, True), ('IMG-DEMO-021', '320 KB', '1600×1600', 'معتمدة', 'الموقع ✓ · الإعلانات ✓', 1, False),
              ('IMG-DEMO-031', '410 KB', '1920×1080', 'معتمدة', 'الموقع ✓ · الإعلانات ✗', 1, False), ('IMG-DEMO-040', '2.8 MB', '3000×2000', 'معتمدة', 'الموقع ✓ · الإعلانات ✗', 1, False),
              ('IMG-DEMO-052', '540 KB', '1200×1200', 'بانتظار اعتمادك', '— لم تُحدد', 0, False), ('VID-DEMO-003', '6.4 MB', '1080×1920', 'مرفوضة', '— لم تُحدد', 0, False)]
    grid = '<ul class="mgrid">' + ''.join(
        f'<li class="ds-card media{" sel" if s else ""}"><div class="ds-media ph">{I_IMG}<span>{nm}</span></div><p><b>{nm}</b></p><p>{n(sz)} · <span dir="ltr">{dim}</span></p>'
        f'<p>{st(ap, {"معتمدة": "success", "بانتظار اعتمادك": "warning"}.get(ap, "danger"))}</p><p>{r}</p><a href="#asset">مستخدمة في {n(u)} أماكن</a></li>' for nm, sz, dim, ap, r, u, s in assets) + '</ul>'
    lib = card('lib', 'المكتبة', filters + grid, fr='الآن', src='مركز الوسائط')
    det = (f'<section class="ds-card sect" id="asset" aria-labelledby="as-h" data-demo><div class="dc-h"><h2 id="as-h">IMG-DEMO-014</h2>{demo()}</div>'
           f'<div class="ds-media ph">{I_IMG}<span>معاينة</span></div>'
           f'<dl class="kv mt"><div><dt>الحجم</dt><dd>{n("1.9 MB")} — <b>الصورة كبيرة وتبطئ صفحة المنيو</b>{adv("Transfer 1.9MB, 2400x2400 JPEG; no AVIF/WebP responsive variants; LCP candidate on /ar/jo/menu/")}</dd></div>'
           '<div><dt>الأبعاد</dt><dd dir="ltr">2400×2400</dd></div><div><dt>الاعتماد</dt><dd>معتمدة منك · 2026-09-20</dd></div></dl>'
           + inp('alt-ar', 'النص البديل بالعربية', 'صورة تجريبية لصنف') + inp('alt-en', 'النص البديل بالإنجليزية', hint='ناقص — يظهر كتحذير في فحص النشر.', d='ltr')
           + '<button type="button" class="ds-btn ds-btn--outline">تحديد نقطة التركيز</button>'
           '<fieldset class="ds-card sect mt" id="rights"><legend class="ht">الحقوق والموافقات</legend>'
           + inp('r-ph', 'المصور', 'مصور تجريبي') + sel('r-src', 'المصدر', ['تصوير SHELTER', 'مصور خارجي بعقد', 'مقدم من شريك'], 'تصوير SHELTER')
           + sel('r-use', 'حقوق الاستخدام', ['استخدام كامل', 'الموقع فقط', 'محدود بفترة'], 'الموقع فقط')
           + sel('r-ppl', 'موافقة الأشخاص الظاهرين', ['لا يوجد أشخاص', 'مسجلة', 'غير مسجلة — لا نشر'], 'لا يوجد أشخاص')
           + '<div class="opts ds-field"><label class="ds-check"><input type="checkbox" id="r-web" checked> صالحة للموقع</label><label class="ds-check"><input type="checkbox" id="r-ads"> صالحة للإعلانات</label></div>'
           + area('r-lim', 'القيود', 'لا تُستخدم في الطباعة.') + inp('r-exp', 'تنتهي الحقوق في', t='date', hint='اتركه فارغًا إذا لا تنتهي.') + '</fieldset>'
           f'<h3 id="usage" class="ht mt">مستخدمة في ({n(3)})</h3><ul class="rows"><li><a href="{L(k, "product")}#image">صنف تجريبي 01 — الصورة الرئيسية</a></li>'
           f'<li><a href="{L(k, "experience")}">حملة يوم القهوة العالمي</a></li><li><a href="#press-kit">Press Kit — صور معتمدة</a></li></ul>'
           '<p class="flex mt"><button type="button" class="ds-btn ds-btn--outline">استبدال الصورة (تبقى كل الاستخدامات)</button>'
           '<button type="button" class="ds-btn ds-btn--secondary" disabled aria-describedby="arch-why">أرشفة</button></p>'
           '<p class="ds-muted note" id="arch-why">مستخدمة في 3 أماكن — استبدلها أولًا. لا حذف نهائي من هنا.</p></section>')
    pk = [('الاسم الرسمي', 'SHELTER COFFEE', 'APPROVED', 'facts'), ('الاسم بالعربية', 'شلتر كوفي', 'APPROVED', 'facts'), ('سنة التأسيس', '2019', 'VERIFIED', 'facts'),
          ('قصة العلامة', 'MISSING — OWNER INPUT REQUIRED', 'MISSING', 'facts'), ('الشعار المعتمد', 'بانتظار ملفات الهوية (M-10)', 'MISSING', 'facts'),
          ('صور معتمدة للصحافة', 'مجموعة من المكتبة', 'APPROVED', 'media'), ('حقائق الفروع', 'من البيانات العامة', 'APPROVED', 'global-data'),
          ('الجوائز', 'تحتاج تحقق', 'PENDING VERIFICATION', 'facts'), ('جهة التواصل الإعلامي', 'MISSING — OWNER INPUT REQUIRED', 'MISSING', 'global-data')]
    press = (f'<section class="ds-card sect" id="press-kit" aria-labelledby="pk-h"><h2 id="pk-h">Press Kit — مجموعة منتقاة</h2>'
             '<p class="ds-muted note">مجموعة من مركز الوسائط وسجل الحقائق للصحفيين والشركاء. لا تُنشر قبل اعتمادك، ولا تتضمن إرشادات العلامة الداخلية.</p><ul class="rows">'
             + ''.join(f'<li><span><b>{a}</b> · {esc(b)}</span><span class="flex">{chip_st(c)}<a href="{L(k, h)}">افتح</a></span></li>' for a, b, c, h in pk) + '</ul></section>')
    if k == 'd':
        layout = f'<div class="edit-grid"><div>{lib}{press}</div><aside aria-label="تفاصيل الصورة المحددة">{det}</aside></div>'
    else:
        layout = lib + det + press
    return ptitle('مركز الوسائط', 'ارفع مرة واحدة واستخدم في كل مكان. لا ملفات مكررة، ولا نشر لصورة شخص بلا موافقته.', up) + banner() + layout

# 13 — Global Data registry ----------------------------------------------------
def global_main(k):
    def rows(items):
        return [[a, f'<span dir="auto">{b}</span>', f'<a href="{L(k, "facts")}?id={fid}">{chip_st(s)}</a>', f'<a href="#edit-phone">{n(u)} أماكن</a>' if u else '—',
                f'<a class="ds-btn ds-btn--secondary" href="#edit-phone">تعديل</a>' if e else ''] for a, b, s, fid, u, e in items]
    cols = ['البيان', 'القيمة', 'حالة التحقق', 'مستخدم في', 'إجراء']
    brand = rows([('الاسم الرسمي', 'SHELTER COFFEE', 'APPROVED', 'FACT-0001', 14, False), ('الاسم بالعربية', 'شلتر كوفي', 'APPROVED', 'FACT-0002', 12, False),
                  ('سنة التأسيس', '2019', 'VERIFIED', 'FACT-0003', 4, False), ('الذكرى السنوية', '20/04', 'APPROVED', 'FACT-0005', 2, False)])
    contacts = rows([('الهاتف الرئيسي', n('+962 7 0000 0000', True), 'PENDING VERIFICATION', 'FACT-0009', 8, True), ('واتساب', n('+962 7 0000 0003', True), 'PENDING VERIFICATION', 'FACT-0010', 5, True),
                     ('البريد العام', 'MISSING — OWNER INPUT REQUIRED', 'MISSING', 'FACT-0011', 0, True), ('تواصل الآراء', 'MISSING — OWNER INPUT REQUIRED', 'MISSING', 'FACT-0012', 0, True),
                     ('تواصل الشراكات', 'MISSING — OWNER INPUT REQUIRED', 'MISSING', 'FACT-0013', 0, True), ('الطلبات الخاصة (B2B)', 'MISSING — OWNER INPUT REQUIRED', 'MISSING', 'FACT-0014', 0, True)])
    social = rows([('Instagram', 'رابط تجريبي', 'PENDING VERIFICATION', 'FACT-0015', 3, True), ('Facebook', 'رابط تجريبي', 'PENDING VERIFICATION', 'FACT-0016', 3, True)])
    site = rows([('حقوق النشر في التذييل', '© SHELTER COFFEE', 'APPROVED', 'FACT-0001', 1, False), ('سياسة الخصوصية', 'MISSING — OWNER INPUT REQUIRED', 'MISSING', 'FACT-0020', 0, False)])
    br = ''.join(f'<li><span><b>{b}</b> · الاسم، العنوان، الخريطة، الهاتف، الساعات، الحالة</span><a class="ds-btn ds-btn--secondary" href="{L(k, "branch")}">فتح الفرع</a></li>' for b in LOCS)
    edit = (f'<section class="ds-alert ds-alert--warning" id="edit-phone" aria-labelledby="ep-h" data-demo><div class="dc-h"><h2 id="ep-h">تعديل: الهاتف الرئيسي</h2>{demo()}</div>'
            f'<p>القيمة الحالية: <span dir="ltr">{n("+962 7 0000 0000")}</span></p>' + inp('ph-new', 'القيمة الجديدة', '+962 7 0000 0009', t='tel', d='ltr')
            + '<p><b>هذا التغيير يؤثر على:</b></p><ul class="impact" id="gd-impact"><li>التذييل (Footer)</li><li>صفحة التواصل</li><li>صفحة DRIVE</li><li>صفحة HOUSE</li>'
              '<li>روابط واتساب</li><li>بيانات Schema</li><li>صفحة الشراكات</li><li>صفحة التوظيف</li></ul>'
            '<p class="flex"><button type="button" class="ds-btn ds-btn--outline">معاينة الأثر</button><button type="button" class="ds-btn ds-btn--primary">نشر</button></p>'
            '<p class="ds-muted note">قيمة واحدة تتحدث في كل الأماكن. القديم والجديد يُسجَّلان في سجل التدقيق. القيمة الجديدة تبدأ PENDING VERIFICATION حتى تعتمدها.</p></section>')
    return (ptitle('البيانات العامة', 'قيمة واحدة لكل معلومة تتكرر في الموقع. غيّرها هنا مرة واحدة فتتحدث في كل مكان.') + banner() + edit
            + card('brand', 'العلامة', table(k, f'بيانات العلامة · {DEMO}', cols, brand), fr='الآن', src='سجل الحقائق')
            + card('contacts', 'التواصل', table(k, f'أرقام وعناوين التواصل · {DEMO}', cols, contacts), fr='الآن', src='سجل الحقائق')
            + card('branches', 'الفروع', f'<ul class="rows">{br}</ul>', demo_=False)
            + card('social', 'السوشال', table(k, f'روابط السوشال · {DEMO}', cols, social), fr='الآن', src='سجل الحقائق')
            + card('site', 'الموقع العام', table(k, f'التذييل والروابط القانونية · {DEMO}', cols, site), fr='الآن', src='سجل الحقائق'))

# 14 — Fact registry -----------------------------------------------------------
def facts_main(k):
    counts = {'APPROVED': 6, 'VERIFIED': 3, 'PENDING VERIFICATION': 5, 'MISSING': 7, 'SUPERSEDED': 2, 'REJECTED': 1}
    chips = '<ul class="chips" id="fact-status">' + ''.join(f'<li><a class="ds-chip" href="?status={s.replace(" ", "-").lower()}">{chip_st(s)} {n(c)}</a></li>' for s, c in counts.items()) + '</ul>'
    U = lambda t, h: f'<a href="{target(k, h)}">{t}</a>'
    facts = [
        ('FACT-0001', 'اسم العلامة الرسمي', 'SHELTER COFFEE', 'D-007', 'APPROVED', 'المالك', '2026-10-01', U('البيانات العامة', 'global-data#brand') + ' · ' + U('Press Kit', 'media#press-kit'), ''),
        ('FACT-0003', 'سنة التأسيس', '2019', 'D-018 (المالك)', 'VERIFIED', 'المالك', '2026-10-01', U('البيانات العامة', 'global-data#brand') + ' · ' + U('Press Kit', 'media#press-kit'), ''),
        ('FACT-0004', 'سنة التأسيس', '2018', 'D-009', 'SUPERSEDED', '—', '2026-10-01', '—', 'حلّت محلها FACT-0003'),
        ('FACT-0005', 'الذكرى السنوية', '20/04', 'D-018 (المالك)', 'APPROVED', 'المالك', '2026-10-01', U('التقويم', 'calendar'), ''),
        ('FACT-0007', 'قصة العلامة', 'MISSING — OWNER INPUT REQUIRED', 'PO-017', 'MISSING', '—', '—', U('Press Kit', 'media#press-kit'), 'لا نص مخترع'),
        ('FACT-0008', '«أكثر من 8000 زبون»', 'ادعاء من الموقع القديم', 'D-038', 'PENDING VERIFICATION', '—', '—', '—', 'ممنوع النشر قبل الدليل'),
        ('FACT-0009', 'الهاتف الرئيسي', '+962 7 0000 0000 (تجريبي)', 'إدخال تجريبي', 'PENDING VERIFICATION', '—', '—', U('البيانات العامة', 'global-data#contacts'), ''),
        ('FACT-0017', '«منذ 2022»', 'صياغة قديمة', 'D-018', 'REJECTED', 'المالك', '2026-10-01', '—', 'OLD OR INCORRECT'),
    ]
    cols = ['المعرّف', 'الحقل', 'القيمة', 'المصدر', 'الحالة', 'اعتمدها', 'آخر مراجعة', 'مستخدمة في', 'ملاحظات']
    rows = [[f'<span dir="ltr">{a}</span>', b, f'<span dir="auto">{esc(c)}</span>', d, chip_st(e), f_, g, h, i] for a, b, c, d, e, f_, g, h, i in facts]
    rule = ('<p class="ds-alert ds-alert--info"><b>القاعدة:</b> أي محتوى يعتمد على حقيقة PENDING VERIFICATION أو MISSING لا يُنشر — فحص النشر يمنعه (BLOCKING). '
            'القيم مأخوذة من سجل القرارات وأمثلة المالك؛ الأعداد والحالات المعروضة تجريبية.</p>')
    return (ptitle('سجل الحقائق', 'كل معلومة حساسة لها مصدر وحالة تحقق. يمنع اختراع المعلومات أو إعادة استخدام القديمة.') + banner() + rule
            + card('facts', 'الحقائق', chips + table(k, f'سجل الحقائق ({n(24)}) · {DEMO}', cols, rows), fr='الآن', src='سجل الحقائق'))

# 15 — Language parity ---------------------------------------------------------
def parity_main(k):
    chips = (f'<ul class="chips"><li><a class="ds-chip" href="?p=ar-newer">العربية أحدث ({n(3)})</a></li><li><a class="ds-chip" href="?p=en-newer">الإنجليزية أحدث ({n(1)})</a></li>'
             f'<li><a class="ds-chip" href="?p=missing">ترجمة ناقصة ({n(4)})</a></li><li><a class="ds-chip" href="?p=diff">سعر أو ساعات مختلفة ({n(1)})</a></li>'
             f'<li><a class="ds-chip" href="?p=alt">نص بديل ناقص ({n(8)})</a></li><li><a class="ds-chip" href="?p=hreflang">ربط اللغتين ناقص ({n(1)})</a></li></ul>')
    rows = [
        (f'<a href="{L(k, "later")}#pages">صفحة «من نحن»</a>', 'صفحة', 'العربية عُدّلت بعد الإنجليزية بـ12 يومًا', '2026-09-26', '2026-09-14'),
        (f'<a href="{L(k, "product")}#name-en">صنف تجريبي 03</a>', 'صنف', 'الاسم بالإنجليزية ناقص', '2026-09-30', '—'),
        (f'<a href="{L(k, "branch")}#special">SHELTER COFFEE HOUSE</a>', 'فرع', 'الساعات الخاصة بالإنجليزية مختلفة عن العربية', '2026-09-29', '2026-09-20'),
        (f'<a href="{L(k, "experience")}">حملة يوم القهوة العالمي</a>', 'حملة', 'الشروط بالإنجليزية أقصر — قسم ناقص', '2026-09-30', '2026-09-30'),
        (f'<a href="{L(k, "media")}#asset">IMG-DEMO-014</a>', 'وسائط', 'النص البديل بالإنجليزية ناقص', '2026-09-20', '—'),
        (f'<a href="{L(k, "seo")}#issues">صفحة DRIVE بالإنجليزية</a>', 'SEO', 'ربط اللغتين (hreflang) ناقص', '2026-09-10', '2026-09-10'),
    ]
    t = table(k, f'الفروقات بين العربي والإنجليزي ({n(18)}) · {DEMO}', ['المحتوى', 'النوع', 'المشكلة', 'آخر تعديل بالعربية', 'آخر تعديل بالإنجليزية'], [list(r) for r in rows])
    note = ('<p class="ds-alert ds-alert--info">لا ترجمة آلية ولا نشر تلقائي. يمكن طلب <b>اقتراح ترجمة كمسودة</b> (AI ASSISTED) ثم تراجعه وتنشره بنفسك. '
            'الفروقات تُفحص أيضًا قبل النشر (فحص النشر).</p>')
    return (ptitle('تطابق العربي والإنجليزي', 'أين تختلف النسختان، وما الأحدث. اضغط أي بند لفتح المحتوى للمقارنة والتعديل.') + banner() + note
            + card('parity', 'الفروقات', chips + t, fr='قبل ساعة', src='فحوص صحة الموقع (يوميًا)'))

# 16 — Site Health -------------------------------------------------------------
def health_main(k):
    tiles = (f'<ul class="kpis"><li><a class="ds-card kpi" href="#uptime"><span class="kpi-l">الموقع يعمل</span><span class="kpi-n">{n("100%")}</span><span class="kpi-d">آخر 7 أيام · زمن الاستجابة {n("320ms")}</span></a></li>'
             f'<li><a class="ds-card kpi" href="{L(k, "security")}#ssl"><span class="kpi-l">شهادة SSL</span><span class="kpi-n">سليمة</span><span class="kpi-d">تنتهي بعد {n(63)} يومًا</span></a></li>'
             f'<li><a class="ds-card kpi" href="#issues"><span class="kpi-l">مشاكل مفتوحة</span><span class="kpi-n">{n(7)}</span><span class="kpi-d">{n(1)} حرجة</span></a></li>'
             f'<li><a class="ds-card kpi" href="#backups"><span class="kpi-l">النسخ الاحتياطي</span><span class="kpi-n">فشل</span><span class="kpi-d">الليلة الماضية</span></a></li>'
             f'<li><a class="ds-card kpi" href="#deps"><span class="kpi-l">الاعتماديات</span><span class="kpi-n">{n(1)}</span><span class="kpi-d">تحديث أمني متوفر</span></a></li>'
             f'<li><a class="ds-card kpi" href="#index"><span class="kpi-l">الفهرسة في Google</span><span class="kpi-n">—</span><span class="kpi-d">{PI}</span></a></li></ul>')
    cats = ['الكل', 'حداثة المحتوى', 'الروابط', 'Schema', 'Sitemap', 'الفهرسة', 'النسخ الاحتياطي', 'الاعتماديات', 'الوسائط']
    fchips = '<ul class="chips">' + ''.join(f'<li><a class="ds-chip" href="?cat={i}"{" aria-current=true" if i == 0 else ""}>{c}</a></li>' for i, c in enumerate(cats)) + '</ul>'
    issues = [
        (sev('critical'), 'النسخ الاحتياطي', 'نسخة قاعدة البيانات فشلت الليلة الماضية', 'اليوم 03:00', f'<a href="#backups">افتح</a>', 'db backup job exit 1 (storage quota)'),
        (sev('warning'), 'الروابط', 'زر «اعرف أكثر» في حملة منتهية يفتح صفحة غير موجودة', '2026-09-29', f'<a href="{L(k, "experience")}">افتح الحملة</a>', 'GET /ar/jo/offers/old-campaign/ → 404 (linked from 2 places)'),
        (sev('warning'), 'Schema', 'بيانات فرع HOUSE في Google ينقصها رقم الهاتف', '2026-09-28', f'<a href="{L(k, "global-data")}#contacts">افتح البيانات العامة</a>', 'LocalBusiness: missing "telephone"'),
        (sev('warning'), 'حداثة المحتوى', 'صفحة الأسئلة الشائعة لم تُراجع منذ 7 أشهر', '2026-09-01', f'<a href="{L(k, "later")}#pages">افتح الصفحة</a>', None),
        (sev('warning'), 'Sitemap', 'صفحة مسودة ظهرت في خريطة الموقع', '2026-09-30', '<a href="#sitemap">افتح</a>', 'sitemap.xml contains /ar/jo/draft-page/ (status: draft)'),
        (sev('info'), 'الوسائط', f'{n(6)} صور غير مستخدمة', '2026-09-15', f'<a href="{L(k, "media")}?filter=unused">افتح الصور</a>', None),
        (sev('info'), 'تطابق اللغتين', f'{n(4)} ترجمات ناقصة', '2026-09-30', f'<a href="{L(k, "parity")}">افتح الفروقات</a>', None),
    ]
    rows = [[f'{b}', a, c + (adv(f_) if f_ else ''), d, e] for a, b, c, d, e, f_ in issues]
    it = table(k, f'المشاكل المفتوحة ({n(7)}) · {DEMO}', ['المجال', 'الشدة', 'المشكلة', 'أول اكتشاف', 'الإجراء'], rows)
    bk = table(k, f'النسخ الاحتياطي واختبار الاستعادة · {DEMO}', ['النوع', 'آخر نسخة', 'الحالة', 'آخر اختبار استعادة (Staging)', 'نتيجة الاستعادة'],
               [['قاعدة البيانات', '2026-10-01 03:00', st('فشلت', 'danger'), '2026-09-15', st('نجح')],
                ['الوسائط', '2026-10-01 03:30', st('نجحت'), '2026-09-15', st('نجح')]])
    backups = bk + ('<p class="ds-muted note">طريقة الاستعادة موثّقة: نعم. <b>لا نعتبر النسخة الاحتياطية حقيقية إلا إذا نجح اختبار استعادتها.</b></p>'
                    '<p class="flex"><button type="button" class="ds-btn ds-btn--outline">إعادة محاولة النسخ الآن</button></p>')
    dsum = '<ul class="chips">' + ''.join(f'<li><a class="ds-chip" href="?dep={s.replace(" ", "-").lower()}">{chip_st(s)} {n(c)}</a></li>' for s, c in zip(DEP_ST, [14, 3, 1, 0])) + '</ul>'
    deps = dsum + table(k, f'الاعتماديات · {DEMO}', ['المكوّن', 'الحالة', 'ماذا يعني', 'الإجراء'],
                        [['مكتبة معالجة الصور', chip_st('SECURITY UPDATE'), 'إصلاح أمني متوفر', '<button type="button" class="ds-btn ds-btn--outline">تجربة على Staging</button>'],
                         ['إطار الموقع', chip_st('UPDATE AVAILABLE'), 'تحديث كبير — لا يُطبق تلقائيًا', '<button type="button" class="ds-btn ds-btn--secondary">قراءة التغييرات</button>'],
                         ['مكتبة الحركة', chip_st('GOOD'), 'محدّثة', '—']]) + '<p class="ds-muted note">لا تحديثات كبيرة تلقائية على الموقع الحي — Staging أولًا ثم موافقتك.</p>'
    sm = ('<ul class="rows"><li><span>تُولَّد تلقائيًا</span><span class="ok">نعم</span></li><li><span>روابط أساسية (Canonical) فقط</span><span class="ok">نعم</span></li>'
          '<li><span>لا صفحات خاصة أو لوحة التحكم</span><span class="ok">نعم</span></li><li><span>لا مسودات</span><b>✗ صفحة مسودة واحدة</b></li>'
          '<li><span>لا روابط بحث أو معاملات زائدة</span><span class="ok">نعم</span></li></ul>')
    sc = table(k, f'صحة Schema · {DEMO}', ['النوع', 'الحالة'], [['Organization', st('سليم · Valid')], ['LocalBusiness', st('تحذير · Warning')],
                                                               ['Event', st('سليم · Valid')], ['Article', '—'], ['Breadcrumb', st('سليم · Valid')], ['FAQ', '—']])
    idx = (f'<p>{pending("Search Console")}</p><p>لا نعرض حالة الفهرسة (مفهرسة · غير مفهرسة · مستبعدة · مشكلة Canonical · مشكلة تحويل) قبل ربط Search Console. '
           f'<a href="{L(k, "seo")}#gsc">ما المطلوب للربط؟</a></p>')
    return (ptitle('صحة الموقع', 'فحص واحد لكل الموقع: الروابط، وحداثة المحتوى، وSchema، وخريطة الموقع، والنسخ الاحتياطي، والاعتماديات.', refresh_btn()) + banner()
            + card('uptime', 'الملخص', tiles, fr='قبل 4 دقائق', src='فحص داخلي كل 5 دقائق')
            + card('issues', 'المشاكل المفتوحة', fchips + it, fr='قبل ساعة', src='فحوص صحة الموقع')
            + card('backups', 'النسخ الاحتياطي والاستعادة', backups, fr='اليوم 03:30', src='سجل النسخ الاحتياطي')
            + card('deps', 'الاعتماديات', deps, fr='اليوم 06:00', src='فحص الاعتماديات (يوميًا)')
            + f'<div class="cols2">{card("sitemap", "خريطة الموقع (Sitemap)", sm, fr="قبل ساعة", src="فحوص صحة الموقع")}{card("schema", "البيانات المنظمة (Schema)", sc, fr="قبل ساعة", src="فحوص صحة الموقع")}</div>'
            + card('index', 'الفهرسة في Google', idx, demo_=False))

# 17 — Performance (RUM) -------------------------------------------------------
def performance_main(k):
    tb = ('<div class="toolbar">' + sel('pdev', 'الجهاز', ['موبايل', 'ديسكتوب', 'تابلت'], first='كل الأجهزة') + sel('plang', 'اللغة', ['العربية', 'English'], first='كل اللغات')
          + sel('pbr', 'المتصفح', ['Chrome', 'Safari', 'Firefox', 'أخرى'], first='كل المتصفحات') + sel('pper', 'الفترة', ['آخر 7 أيام', 'آخر 28 يومًا'], 'آخر 28 يومًا') + '</div>')
    alerts = [
        ('صفحة المنيو أصبحت أبطأ على الموبايل', 'المنيو', 'موبايل (Android وiPhone)', f'ظهور المحتوى الرئيسي ارتفع من {n("2.3")} إلى {n("3.4")} ثانية',
         f'الصورة الرئيسية للصنف المميز حجمها {n("2.3 MB")} — وهي أكبر عنصر في {n("82%")} من الزيارات البطيئة.', 'ضغط الصورة إلى AVIF/WebP بأحجام للموبايل.',
         L(k, 'media', '#asset'), 'LCP p75 mobile /ar/jo/menu/: 2.3s → 3.4s (28d); LCP element img.featured (2.3MB JPEG) in 82% of poor samples'),
        ('صورة الصفحة الرئيسية كبيرة وتبطئ تحميل الصفحة', 'الرئيسية', 'موبايل', f'ظهور المحتوى الرئيسي {n("3.1")} ثانية', f'صورة الواجهة {n("2.8 MB")}.',
         'استبدالها بنسخة مضغوطة من مركز الوسائط.', L(k, 'media', '?filter=oversized'), 'Hero LCP resource 2.8MB'),
        ('الاستجابة بطيئة في البحث على الموبايل', 'المنيو — البحث', 'موبايل', f'زمن الاستجابة للضغط {n("280ms")}',
         '<b>لا يوجد دليل كافٍ بعد — لن نخمّن السبب.</b> نجمع بيانات 7 أيام إضافية.', 'لا إجراء الآن — سنعيد الفحص تلقائيًا.', L(k, 'performance', '#pages-perf'),
         'INP p75 mobile /ar/jo/menu/ (search interactions): 280ms; attribution insufficient (n=120)'),
    ]
    al = ''
    for i, (title, pg, dev, prob, cause, act, href, a) in enumerate(alerts):
        al += (f'<li class="ds-card rcard"><h3 class="ht">{title}</h3><dl class="kv"><div><dt>المشكلة</dt><dd>{prob}</dd></div>'
               f'<div><dt>الصفحة</dt><dd>{pg}</dd></div><div><dt>الأجهزة</dt><dd>{dev}</dd></div><div><dt>السبب المرجّح (بدليل)</dt><dd>{cause}</dd></div>'
               f'<div><dt>الإجراء المقترح</dt><dd>{act}</dd></div></dl><a class="ds-btn ds-btn--outline" href="{href}">افتح</a>{adv(a)}</li>')
    alerts_c = card('alerts', 'تنبيهات الأداء', f'<ul class="rcards">{al}</ul>', fr='قبل 20 دقيقة', src='قياس ذاتي من الزوار (RUM) · p75')
    sumc = (f'<ul class="kpis"><li><a class="ds-card kpi" href="#pages-perf"><span class="kpi-l">ظهور المحتوى الرئيسي (LCP) — موبايل</span><span class="kpi-n">{n("3.4 ث")}</span><span class="kpi-d">{rum("NEEDS IMPROVEMENT")}</span></a></li>'
            f'<li><a class="ds-card kpi" href="#pages-perf"><span class="kpi-l">الاستجابة للضغط (INP) — موبايل</span><span class="kpi-n">{n("280ms")}</span><span class="kpi-d">{rum("NEEDS IMPROVEMENT")}</span></a></li>'
            f'<li><a class="ds-card kpi" href="#pages-perf"><span class="kpi-l">ثبات الصفحة (CLS) — موبايل</span><span class="kpi-n">{n("0.04")}</span><span class="kpi-d">{rum("GOOD")}</span></a></li>'
            f'<li><a class="ds-card kpi" href="#pages-perf"><span class="kpi-l">ظهور المحتوى الرئيسي (LCP) — ديسكتوب</span><span class="kpi-n">{n("1.9 ث")}</span><span class="kpi-d">{rum("GOOD")}</span></a></li></ul>')
    data = [('الرئيسية', 'موبايل', ('3.1 ث', 'NEEDS IMPROVEMENT'), ('190ms', 'GOOD'), ('0.02', 'GOOD'), '1,240'),
            ('المنيو', 'موبايل', ('3.4 ث', 'NEEDS IMPROVEMENT'), ('280ms', 'NEEDS IMPROVEMENT'), ('0.04', 'GOOD'), '1,610'),
            ('المنيو', 'ديسكتوب', ('1.9 ث', 'GOOD'), ('90ms', 'GOOD'), ('0.01', 'GOOD'), '310'),
            ('SHELTER COFFEE DRIVE', 'موبايل', ('2.2 ث', 'GOOD'), ('150ms', 'GOOD'), ('0.31', 'POOR'), '540'),
            ('SHELTER COFFEE HOUSE', 'موبايل', ('4.6 ث', 'POOR'), ('170ms', 'GOOD'), ('0.05', 'GOOD'), '380')]
    rows = [[p, d, f'{n(l[0])} {rum(l[1])}', f'{n(i[0])} {rum(i[1])}', f'{n(c[0])} {rum(c[1])}', n(v)] for p, d, l, i, c, v in data]
    t = table(k, f'حسب الصفحة والجهاز — p75 · {DEMO}', ['الصفحة', 'الجهاز', 'LCP', 'INP', 'CLS', 'زيارات مقاسة'], rows)
    th = adv('Thresholds (p75): LCP good ≤ 2.5s, poor > 4.0s · INP good ≤ 200ms, poor > 500ms · CLS good ≤ 0.1, poor > 0.25. Aggregated daily, no identifiers, no cookies.')
    return (ptitle('الأداء الفعلي', 'كيف يعمل الموقع عند الزوار الحقيقيين — وليس اختبار المختبر فقط. نستخدم p75: 75% من الزيارات أسرع من هذا الرقم.', refresh_btn())
            + banner() + tb + alerts_c + card('summary-p', 'الملخص', sumc, fr='قبل 20 دقيقة', src='RUM · p75 · آخر 28 يومًا')
            + card('pages-perf', 'حسب الصفحة والجهاز', t + th, fr='قبل 20 دقيقة', src='RUM · p75'))

# 18 — Accessibility center ----------------------------------------------------
def a11y_main(k):
    rows = [
        ('زر «إغلاق» في نافذة الصنف بلا اسم مسموع', 'المنيو', sev('critical'), 'آلي', 'إضافة اسم للزر (aria-label)', f'<a href="{L(k, "health")}?cat=a11y">افتح</a>'),
        ('نص السعر فوق الصورة غير واضح (تباين ضعيف)', 'حملة يوم القهوة', sev('warning'), 'آلي', 'خلفية خلف النص أو لون أغمق', f'<a href="{L(k, "experience")}">افتح الحملة</a>'),
        (f'{n(8)} صور بلا نص بديل', 'عدة صفحات', sev('warning'), 'آلي', 'كتابة نص بديل بالعربية والإنجليزية', f'<a href="{L(k, "media")}?filter=no-alt">افتح الصور</a>'),
        ('ترتيب التنقل بلوحة المفاتيح في التقويم غير منطقي', 'لوحة التحكم — التقويم', sev('warning'), 'يدوي', 'إعادة ترتيب عناصر الشهر', f'<a href="{L(k, "calendar")}">افتح</a>'),
        ('الحركة في الثيم الموسمي لا تتوقف مع «تقليل الحركة»', 'ثيم موسمي تجريبي', sev('critical'), 'يدوي', 'تفعيل البديل الثابت', f'<a href="{L(k, "experience")}">افتح التجربة</a>'),
    ]
    t = table(k, f'مشاكل الوصولية ({n(5)}) · {DEMO}', ['المشكلة', 'الصفحة', 'الشدة', 'المصدر', 'الإصلاح المقترح', 'الإجراء'], [list(r) for r in rows])
    manual = table(k, f'الفحص اليدوي · {DEMO}', ['البند', 'الحالة', 'آخر فحص'],
                   [['لوحة المفاتيح', st('تم'), '2026-09-25'], ['قارئ الشاشة (VoiceOver · TalkBack)', st('لم يُفحص'), '—'], ['التكبير 200%', st('تم'), '2026-09-25'],
                    ['اللمس (44px)', st('تم'), '2026-09-25'], ['النماذج ورسائل الأخطاء', st('تم'), '2026-09-25'], ['تقليل الحركة', st('فشل', 'danger'), '2026-09-28'],
                    ['العربي والإنجليزي (RTL · LTR)', st('تم'), '2026-09-25']])
    return (ptitle('الوصولية', 'الهدف: WCAG 2.2 AA. الفحص الآلي وحده لا يكفي — الفحص اليدوي جزء من الحالة.') + banner()
            + card('a11y-issues', 'المشاكل', t, fr='قبل ساعة', src='فحص آلي (axe) + فحص يدوي')
            + card('manual', 'قائمة الفحص اليدوي', manual, fr='2026-09-28', src='فحص يدوي'))

# 19 — Security center ---------------------------------------------------------
def security_main(k):
    tiles = (f'<ul class="kpis"><li><a class="ds-card kpi" href="#ssl"><span class="kpi-l">شهادة SSL</span><span class="kpi-n">سليمة</span><span class="kpi-d">تنتهي بعد {n(63)} يومًا</span></a></li>'
             f'<li><a class="ds-card kpi" href="#headers"><span class="kpi-l">رؤوس الأمان</span><span class="kpi-n">{n("5 من 6")}</span><span class="kpi-d">ينقص بند واحد</span></a></li>'
             f'<li><a class="ds-card kpi" href="#auth"><span class="kpi-l">تسجيل دخولك</span><span class="kpi-n">قوي</span><span class="kpi-d">Passkey + المصادقة الثنائية</span></a></li>'
             f'<li><a class="ds-card kpi" href="{L(k, "health")}#backups"><span class="kpi-l">آخر نسخة احتياطية</span><span class="kpi-n">فشلت</span><span class="kpi-d">الليلة الماضية 03:00</span></a></li>'
             f'<li><a class="ds-card kpi" href="#logins"><span class="kpi-l">محاولات دخول فاشلة</span><span class="kpi-n">{n(5)}</span><span class="kpi-d">آخر 24 ساعة · {n(1)} مشبوهة</span></a></li>'
             f'<li><a class="ds-card kpi" href="{L(k, "health")}#deps"><span class="kpi-l">أمان الاعتماديات</span><span class="kpi-n">{n(1)}</span><span class="kpi-d">تحديث أمني متوفر</span></a></li></ul>')
    checks_ = table(k, f'الحماية · {DEMO}', ['البند', 'الحالة', 'التفاصيل'],
                    [['<span id="ssl">SSL</span>', st('سليم'), 'تجديد تلقائي'],
                     ['<span id="headers">رؤوس الأمان</span>', st('ينقص بند'), 'ينقص Permissions-Policy' + adv('Missing header: Permissions-Policy. Present: HSTS, X-Content-Type-Options, Referrer-Policy, X-Frame-Options, CSP')],
                     ['سياسة المحتوى (CSP)', st('مفعّلة'), 'وضع الحظر'], ['حماية لوحة التحكم', st('مفعّلة'), 'حد للمحاولات + إعادة تأكيد للعمليات الحساسة'],
                     ['أمان رفع الملفات', st('مفعّل جزئيًا', 'warning'), 'أنواع مسموحة فقط · فحص الفيروسات بانتظار فحص الاستضافة'],
                     ['الأسرار والمفاتيح', st('على الخادم فقط'), 'لا تُعرض هنا ولا في المتصفح']])
    auth = (f'<dl class="kv"><div><dt>Passkey</dt><dd>مفعّل · جهازان</dd></div><div><dt>المصادقة الثنائية</dt><dd>مفعّلة (تطبيق)</dd></div>'
            f'<div><dt>رموز الاسترداد</dt><dd>{n(8)} متبقية</dd></div><div><dt>انتهاء الجلسة</dt><dd>بعد 12 ساعة أو عند الخمول</dd></div></dl>'
            '<p class="flex"><button type="button" class="ds-btn ds-btn--outline">إدارة مفاتيح الدخول</button><button type="button" class="ds-btn ds-btn--secondary">إنشاء رموز استرداد جديدة</button></p>')
    logins = table(k, f'نشاط الدخول · {DEMO}', ['الوقت', 'النتيجة', 'الجهاز', 'الدولة'],
                   [['اليوم 08:41', st('نجح'), 'Chrome · macOS', 'الأردن'], ['اليوم 02:10–02:19', st('5 محاولات فاشلة — مشبوه', 'danger'), 'جهاز غير معروف', 'غير معروفة'],
                    ['أمس 21:05', st('إعادة تأكيد للتصدير', ''), 'Safari · iOS', 'الأردن'], ['أمس 09:30', st('نجح'), 'Safari · iOS', 'الأردن']])
    logins += '<p class="ds-muted note">بعد 5 محاولات فاشلة يُوقف الدخول مؤقتًا تلقائيًا. لا تُخزَّن كلمات مرور ولا عناوين كاملة.</p>'
    sess = ('<ul class="rows"><li><span><b>هذا الجهاز</b> · Chrome · macOS · الآن</span>' + st('الحالية', 'solid') + '</li>'
            '<li><span>Safari · iOS · قبل ساعتين</span><button type="button" class="ds-btn ds-btn--secondary">إنهاء الجلسة</button></li></ul>'
            '<p class="flex mt"><button type="button" class="ds-btn ds-btn--outline">إنهاء كل الجلسات الأخرى</button></p>')
    return (ptitle('الأمان', 'حالة حماية الموقع ولوحة التحكم بلغة واضحة. لا تُعرض أي أسرار أو مفاتيح هنا.', refresh_btn()) + banner()
            + card('sec-sum', 'الملخص', tiles, fr='قبل 10 دقائق', src='فحوص الأمان')
            + card('protect', 'الحماية', checks_, fr='اليوم 06:00', src='فحوص الأمان (يوميًا)')
            + f'<div class="cols2">{card("auth", "تسجيل دخولك", auth, fr="الآن", src="سجل المصادقة")}{card("sessions", "الجلسات النشطة", sess, demo_=False)}</div>'
            + card('logins', 'نشاط الدخول', logins, fr='الآن', src='سجل المصادقة'))

# 20 — Privacy center ----------------------------------------------------------
def privacy_main(k):
    inv = table(k, f'البيانات التي نجمعها · {DEMO}', ['البيانات', 'الغرض', 'بيانات شخصية؟', 'الاحتفاظ', 'أين تُدار'],
                [['طلبات التوظيف', 'دراسة الطلب والتواصل', 'نعم (حساسة)', 'لا حذف تلقائي — قرارك', f'<a href="{CAREERS.format(k=k)}">التوظيف</a>'],
                 ['طلبات الشراكات', 'دراسة الطلب', 'نعم', 'لا حذف تلقائي — قرارك', f'<a href="{FRANCHISE.format(k=k)}">الشراكات</a>'],
                 ['آراء العملاء', 'تحسين التجربة', 'لا', 'مجمّعة', f'<a href="{L(k, "feedback")}">آراء العملاء</a>'],
                 ['سجل البحث داخل الموقع', 'معرفة ما يبحث عنه الزوار', 'لا (منظّف)', 'مجمّع يوميًا', f'<a href="{L(k, "seo")}#search">البحث</a>'],
                 ['قياس الأداء (RUM)', 'سرعة الموقع', 'لا', 'مجمّع يوميًا', f'<a href="{L(k, "performance")}">الأداء</a>'],
                 ['تفضيلات الكوكيز', 'احترام اختيار الزائر', 'لا', 'حسب السياسة', '<a href="#cookies">الكوكيز</a>']])
    cons = table(k, f'نسخ نصوص الموافقة · {DEMO}', ['الموافقة', 'النسخة الحالية', 'منذ', 'الحالة'],
                 [['التوظيف', 'النسخة 1', '2026-10-01', st('نشطة', 'solid')], ['الشراكات', 'النسخة 1', '—', st('بانتظار الاعتماد')],
                  ['نشر الموظفين (SHELTER Family)', 'النسخة 1', '2026-09-20', st('نشطة', 'solid')], ['الكوكيز والتحليلات', '—', '—', st('MISSING — قيد مراجعة الخصوصية')]])
    ret = table(k, f'عمر السجلات · {DEMO}', ['النوع', 'العدد', 'أقدم سجل (العمر)', 'آخر وصول', 'آخر تحديث', 'مؤرشف'],
                [['طلبات التوظيف', n(186), f'{n(14)} شهرًا', 'اليوم', 'اليوم', n(42)], ['طلبات الشراكات', n(9), f'{n(3)} أشهر', 'أمس', 'أمس', n(1)],
                 ['آراء العملاء', n(1240), f'{n(6)} أشهر', 'اليوم', 'اليوم', '—']])
    ret += (f'<p class="ds-alert ds-alert--info">تنبيه: {n(12)} طلب توظيف عمرها أكثر من سنة. <b>لا حذف تلقائي</b> (قرارك) — يمكنك مراجعتها وأرشفتها. '
            f'<a href="{CAREERS.format(k=k)}">افتح التوظيف</a></p>')
    req = table(k, f'طلبات الخصوصية · {DEMO}', ['الطلب', 'النوع', 'تاريخ الاستلام', 'الحالة'],
                [['REQ-DEMO-001', 'تصدير بيانات', '2026-09-10', st('مكتمل')], ['REQ-DEMO-002', 'حذف بيانات', '2026-09-28', st('قيد المتابعة')],
                 ['REQ-DEMO-003', 'تصحيح بيانات', '2026-09-30', st('جديد', 'solid')]])
    cookies = ('<ul class="rows"><li><span>ضرورية</span>' + st('دائمًا') + '</li><li><span>التحليلات</span>' + st('بموافقة الزائر') + '</li>'
               '<li><span>التسويق</span>' + st('غير مفعّلة') + '</li></ul><p class="ds-muted note">تصنيف قياس الأداء (RUM) وآراء العملاء ضمن مراجعة الخصوصية (PO-019).</p>')
    pol = table(k, f'نسخ السياسات · {DEMO}', ['السياسة', 'النسخة', 'الحالة'],
                [['سياسة الخصوصية', '—', st('MISSING — OWNER INPUT REQUIRED')], ['سياسة الكوكيز', '—', st('MISSING — OWNER INPUT REQUIRED')]])
    return (ptitle('الخصوصية والبيانات', 'ما نجمعه، ولماذا، وكم عمره، ومن طلب ماذا. الخصوصية هنا ليست صفحة ثابتة فقط.') + banner()
            + card('inventory', 'البيانات التي نجمعها', inv, fr='الآن', src='إعداد قائمة البيانات')
            + card('consents', 'نسخ الموافقات', cons, fr='الآن', src='سجل الموافقات')
            + card('retention', 'الاحتفاظ وعمر السجلات', ret, fr='اليوم 06:00', src='قاعدة البيانات')
            + card('requests', 'طلبات الخصوصية', req, fr='الآن', src='سجل الطلبات')
            + f'<div class="cols2">{card("cookies", "تفضيلات الكوكيز", cookies, demo_=False)}{card("policies", "نسخ السياسات", pol, fr="الآن", src="سجل السياسات")}</div>')

# 21 — SEO + content opportunities + site search intelligence ------------------
def seo_main(k):
    gsc = (f'<p>{pending("Search Console")} ربط Search Console ينتظر صلاحيتك (PO-011). '
           'لا نعرض النقرات ولا الظهور ولا الترتيب قبل الربط — ولا نخترعها.</p>'
           '<dl class="kv"><div><dt>النقرات</dt><dd>—</dd></div><div><dt>مرات الظهور</dt><dd>—</dd></div><div><dt>نسبة النقر</dt><dd>—</dd></div><div><dt>متوسط الترتيب</dt><dd>—</dd></div></dl>')
    issues = table(k, f'مشاكل SEO ({n(4)}) · {DEMO}', ['المشكلة', 'الصفحة', 'الشدة', 'الشرح', 'الإصلاح المقترح'],
                   [['رابط Canonical خاطئ', f'<a href="{L(k, "branch")}">SHELTER COFFEE DRIVE</a>', sev('critical'), 'Google قد يتجاهل الصفحة الصحيحة' + adv('rel=canonical → /ar/jo/drive-old/ (404)'), 'تصحيح الرابط — بموافقتك'],
                    ['وصف مكرر في نتائج Google', f'<a href="{L(k, "later")}#pages">من نحن · التواصل</a>', sev('warning'), 'صفحتان بنفس الوصف', 'كتابة وصف لكل صفحة'],
                    ['عنوان طويل يُقص في Google', f'<a href="{L(k, "branch")}?b=house">SHELTER COFFEE HOUSE</a>', sev('warning'), 'العنوان أطول من المساحة الظاهرة', 'اختصار العنوان'],
                    ['ربط اللغتين (hreflang) ناقص', f'<a href="{L(k, "parity")}">صفحة DRIVE بالإنجليزية</a>', sev('warning'), 'Google لا يعرف أن الصفحتين نسختان', 'ربط النسختين']])
    issues += f'<p class="ds-muted note">لا تغييرات SEO تلقائية — أنت توافق على كل تعديل حساس. Schema وخريطة الموقع والفهرسة: <a href="{L(k, "health")}#schema">في صحة الموقع</a>.</p>'
    topq = table(k, f'الأكثر بحثًا · {DEMO}', ['ما بحث عنه الزوار', 'العدد', 'النتائج', 'ما اختاروه'],
                 [['cold brew', n(45), n(3), f'<a href="{L(k, "product")}">صنف تجريبي 02</a>'], ['V60', n(31), n(2), f'<a href="{L(k, "product")}">صنف تجريبي 04</a>'],
                  ['DRIVE', n(28), n(4), f'<a href="{L(k, "branch")}">صفحة الفرع</a>']])
    zero = table(k, f'بحث بلا نتائج · {DEMO}', ['ما بحث عنه الزوار', 'العدد', 'إجراء مقترح'],
                 [['matcha', n(15), f'<a href="{L(k, "menu")}">هل يوجد صنف؟ أضف مرادفًا معتمدًا أو رسالة واضحة</a>'], ['وظائف', n(9), f'<a href="{CAREERS.format(k=k)}">ربطها بصفحة التوظيف</a>'],
                  ['ساعات رمضان', n(6), f'<a href="{L(k, "branch")}#special">أضف الساعات الخاصة</a>'], ['فرنشايز', n(4), f'<a href="{FRANCHISE.format(k=k)}">ربطها بصفحة الشراكات</a>']])
    emerg = table(k, f'بحث صاعد · {DEMO}', ['ما بحث عنه الزوار', 'هذا الأسبوع', 'الأسبوع الماضي'], [['iced', n(22), n(6)], ['يوم القهوة', n(18), n(0)]])
    search = (f'<p class="ds-muted note">من البحث داخل الموقع نفسه — مجمّع ومنظّف، بلا هوية الزائر. '
              f'<b>تنبيه:</b> التسجيل متوقف منذ يومين. <a href="{L(k, "health")}#issues">افتح المشكلة</a></p>'
              f'{topq}<h3 id="zero" class="ht mt">بحث بلا نتائج</h3>{zero}<h3 class="ht mt">بحث صاعد</h3>{emerg}')
    opps = (f'<ul class="rcards"><li class="ds-card rcard"><h3 class="ht">زوار يبحثون عن «matcha» ولا يجدون شيئًا</h3><dl class="kv"><div><dt>الدليل</dt><dd>{n(15)} بحثًا بلا نتائج في 7 أيام</dd></div>'
            f'<div><dt>مرتبط بـ</dt><dd><a href="{L(k, "menu")}">المنيو</a></dd></div></dl><p class="flex"><button type="button" class="ds-btn ds-btn--outline">أنشئ مسودة (أنت تقرر)</button><button type="button" class="ds-btn ds-btn--secondary">تجاهل</button></p></li>'
            f'<li class="ds-card rcard"><h3 class="ht">صفحة تظهر كثيرًا في Google ونقراتها قليلة</h3><dl class="kv"><div><dt>الدليل</dt><dd>{pending("Search Console")}</dd></div>'
            '<div><dt>مرتبط بـ</dt><dd>—</dd></div></dl><p class="ds-muted note">تظهر هذه الفرص بعد ربط Search Console.</p></li></ul>'
            '<p class="ds-muted note">لا مقالات تلقائية ولا محتوى لأجل SEO فقط. كل اقتراح مفيد للناس ومرتبط بـSHELTER، وأنت تعتمده قبل النشر.</p>')
    return (ptitle('الظهور في البحث (SEO)', 'كيف يجد الناس الموقع، وماذا يبحثون، وما الذي يمكن تحسينه.') + banner()
            + card('gsc', 'أداء البحث في Google', gsc, demo_=False)
            + card('issues', 'مشاكل SEO', issues, fr='قبل ساعة', src='فحوص صحة الموقع')
            + card('search', 'البحث داخل الموقع', search, fr='قبل يومين — التسجيل متوقف', src='سجل البحث (مجمّع، بلا هوية)')
            + card('opps', 'فرص المحتوى', opps, fr='اليوم 06:00', src='البحث داخل الموقع + Search Console'))

# 22 — Reputation --------------------------------------------------------------
def reputation_main(k):
    pend = (f'<section class="ds-alert ds-alert--warning" aria-labelledby="gbp-h"><h2 id="gbp-h">{pending("Google Business Profile")}</h2>'
            '<p>لا نعرض تقييمات Google قبل ربط الـAPI الرسمية وموافقة Google على الوصول (PO-009). <b>لا نسخ للصفحات (Scraping).</b></p>'
            '<p class="ds-muted note">بعد الربط تظهر لكل فرع: التقييم، وعدد المراجعات، والمراجعات الجديدة، واتجاه التقييم، وما يحتاج ردًا.</p></section>')
    br = ''.join(f'<li class="ds-card rcard"><p class="rc-t">{b}</p><dl class="kv"><div><dt>التقييم</dt><dd>—</dd></div><div><dt>عدد المراجعات</dt><dd>—</dd></div>'
                 f'<div><dt>تحتاج ردًا</dt><dd>—</dd></div></dl><p>{pending("Google Business Profile")}</p></li>' for b in LOCS)
    flow = '<ol class="steps" id="reply-flow">' + ''.join(f'<li{" aria-current=step" if i == 1 else ""}>{s}</li>' for i, s in enumerate(REPLY_FLOW)) + '</ol>'
    review = (f'<section class="ds-card sect" aria-labelledby="rv-h" data-demo><div class="dc-h"><h2 id="rv-h">كيف سيعمل الرد — مثال</h2>{demo()}</div>{flow}'
              f'<div class="ds-card rcard"><p class="rc-t">مراجعة تجريبية · SHELTER COFFEE DRIVE · {n("4 من 5")}</p><p>«نص مراجعة تجريبي: القهوة ممتازة لكن الانتظار طويل وقت الذروة.»</p></div>'
              + area('reply', 'الرد المقترح — يمكنك تعديله', 'شكرًا لزيارتك ولملاحظتك عن وقت الانتظار. نعمل على تحسينه في أوقات الذروة.', hint='اقتراح من AI ASSISTED — لا يُنشر بلا اعتمادك.')
              + '<p class="flex"><button type="button" class="ds-btn ds-btn--outline">اعتماد الرد</button><button type="button" class="ds-btn ds-btn--primary" disabled aria-describedby="rp-why">نشر على Google</button></p>'
              '<p class="ds-muted note" id="rp-why">النشر يتطلب ربط Google Business Profile. كل رد يُسجَّل في سجل التدقيق.</p></section>')
    return (ptitle('السمعة', 'تقييمات Google لكل فرع والرد عليها — بعد الربط الرسمي فقط.') + pend
            + f'<section aria-labelledby="brs-h"><h2 id="brs-h" class="vh">الفروع</h2><ul class="rcards mb">{br}</ul></section>' + banner('المراجعة والرد أدناه مثال تجريبي لشرح طريقة العمل.') + review)

# 23 — Feedback / Voice of Customer --------------------------------------------
def feedback_main(k):
    tb = '<div class="toolbar">' + sel('fb-r', 'الفترة', ['آخر 7 أيام', 'آخر 30 يومًا', 'هذا الشهر'], 'آخر 30 يومًا') + sel('fb-b', 'الفرع', LOCS, first='كل الفروع') + '</div>'
    scores = [('التجربة العامة', '4.3'), ('القهوة', '4.5'), ('الخدمة', '4.1'), ('النظافة', '4.4'), ('السرعة', '3.8')]
    kp = '<ul class="kpis">' + ''.join(kpi(f'#comments', l, f'{v} من 5', f'من {n(214)} رأيًا') for l, v in scores) + '</ul>'
    cmp_ = table(k, f'مقارنة الفروع · {DEMO}', ['البند', 'DRIVE', 'HOUSE'], [[l, n(a), n(b)] for l, a, b in
                                                                          [('التجربة العامة', '4.2', '4.4'), ('القهوة', '4.5', '4.5'), ('الخدمة', '4.0', '4.2'), ('النظافة', '4.3', '4.5'), ('السرعة', '3.6', '4.0')]])
    topics = (f'<p><span class="ds-badge ai">AI ASSISTED ANALYSIS</span></p><p class="ds-muted note">تصنيف مساعد وليس حقيقة مطلقة — اقرأ التعليقات نفسها قبل أي قرار.</p>'
              f'<ul class="rows"><li><span>الانتظار وقت الذروة</span><a href="#comments">{n(12)} تعليقًا</a></li><li><span>جودة الإسبريسو</span><a href="#comments">{n(9)} تعليقات</a></li>'
              f'<li><span>المواقف عند DRIVE</span><a href="#comments">{n(5)} تعليقات</a></li></ul>')
    com = table(k, f'آخر التعليقات · {DEMO}', ['الوقت', 'الفرع', 'التقييم العام', 'التعليق', 'الموضوع (AI ASSISTED)'],
                [['اليوم 10:12', 'DRIVE', n('3 من 5'), 'تعليق تجريبي: الطلب تأخر في الصباح.', 'الانتظار'], ['أمس 19:40', 'HOUSE', n('5 من 5'), 'تعليق تجريبي: المكان مريح وهادئ.', '—'],
                 ['أمس 08:05', 'DRIVE', n('4 من 5'), 'تعليق تجريبي: قهوة ممتازة.', 'جودة الإسبريسو']])
    note = '<p class="ds-alert ds-alert--info">نموذج آراء خاص بنا — بلا بيانات شخصية، ومستقل عن تقييمات Google. <b>لا نطلب من الراضين فقط ترك تقييم على Google</b> (لا Review gating).</p>'
    return (ptitle('آراء العملاء', 'ماذا يقول العملاء عن كل فرع: القهوة، والخدمة، والنظافة، والسرعة.') + banner() + note + tb
            + card('scores', 'التقييمات', kp, fr='قبل 15 دقيقة', src='نموذج الآراء')
            + f'<div class="cols2">{card("compare", "مقارنة الفروع", cmp_, fr="قبل 15 دقيقة", src="نموذج الآراء")}{card("topics", "مواضيع متكررة", topics, fr="اليوم 06:00", src="تحليل مساعد")}</div>'
            + card('comments', 'التعليقات', com, fr='قبل 15 دقيقة', src='نموذج الآراء'))

# 24 — Audit log ---------------------------------------------------------------
def audit_main(k):
    tb = ('<div class="toolbar">' + inp('aq', 'بحث في السجل', t='search', cls='ds-field grow', ph='صنف، فرع، حملة…')
          + sel('at', 'النوع', ['الأسعار', 'الساعات', 'التواصل', 'التجارب', 'النشر', 'تجاوز تحذيرات النشر', 'وضع الأمان', 'كشف بيانات حساسة'], first='كل الأنواع')
          + sel('ar', 'الفترة', ['اليوم', 'آخر 7 أيام', 'آخر 30 يومًا', 'كل الوقت'], 'آخر 7 أيام') + '</div>')
    rows = [
        ['2026-10-01 09:12', 'المالك', 'تغيير سعر', f'<a href="{L(k, "product")}">صنف تجريبي 01</a>', n('2.50 د.أ'), n('2.75 د.أ'), 'مسودة — لم يُنشر بعد'],
        ['2026-10-01 08:55', 'المالك', 'نشر رغم تحذير', f'<a href="{L(k, "experience")}">حملة يوم القهوة العالمي</a>', 'تحذير: صورة كبيرة', 'منشور', 'السبب: «ستُستبدل الصورة غدًا»'],
        ['2026-09-30 22:10', 'المالك', 'أوقف الآن', f'<a href="{L(k, "active")}">إعلان تجريبي</a>', 'نشط', 'متوقف', 'إيقاف طارئ'],
        ['2026-09-30 21:58', 'المالك', 'وضع الأمان', f'<a href="{L(k, "safe-mode")}">الموقع</a>', 'مطفأ', 'مفعّل', 'السبب: «حركة الثيم بطيئة»'],
        ['2026-09-30 18:20', 'المالك', 'تغيير ساعات', f'<a href="{L(k, "branch")}">SHELTER COFFEE HOUSE</a>', '22:00', '23:00', 'الخميس'],
        ['2026-09-29 12:00', 'المالك', 'تغيير قيمة عامة', f'<a href="{L(k, "global-data")}#contacts">الهاتف الرئيسي</a>', n('+962 7 0000 0005', True), n('+962 7 0000 0000', True), 'أثر: 8 أماكن'],
        ['2026-09-28 10:15', 'المالك', 'تغيير الموظف المثالي', f'<a href="{L(k, "family")}#eom">تشرين الأول</a>', '—', 'موظف تجريبي 1', ''],
        ['2026-09-27 16:40', 'المالك', 'تصدير بإظهار الهوية', 'طلبات التوظيف', '—', '20 طلبًا', 'بعد إعادة تأكيد الدخول'],
    ]
    t = table(k, f'السجل ({n(8)} من {n(312)}) · {DEMO}', ['الوقت', 'من', 'الإجراء', 'الهدف', 'قبل', 'بعد', 'السياق'], rows)
    vers = (f'<ul class="rows"><li><span><b>النسخة 3</b> · مسودة · 2026-10-01 09:12 · السعر {n("2.75 د.أ")}</span>{st("الحالية", "solid")}</li>'
            f'<li><span><b>النسخة 2</b> · منشورة · 2026-09-20 10:00 · السعر {n("2.50 د.أ")}</span><button type="button" class="ds-btn ds-btn--secondary">استعادة كمسودة</button></li>'
            f'<li><span><b>النسخة 1</b> · 2026-08-01 09:00 · السعر {n("2.25 د.أ")}</span><button type="button" class="ds-btn ds-btn--secondary">استعادة كمسودة</button></li></ul>'
            '<p class="ds-muted note">الاستعادة تنشئ مسودة جديدة — لا تغيّر الموقع الحي قبل النشر.</p>')
    return (ptitle('سجل التدقيق', 'كل إجراء في لوحة التحكم: من، ماذا، قبل، بعد، متى. لا تغييرات صامتة. السجل للقراءة فقط.', '<button type="button" class="ds-btn ds-btn--secondary">تصدير السجل</button>')
            + banner() + tb + card('log', 'السجل', t, fr='الآن', src='سجل التدقيق')
            + card('versions', 'سجل النسخ — صنف تجريبي 01', vers, fr='الآن', src='سجل النسخ'))

# 25 — Safe Mode confirmation --------------------------------------------------
def safe_dialog(k):
    body = ('<p><b>وضع الأمان يوقف فورًا كل التجارب الديناميكية والإضافات الاختيارية، ويبقى الموقع الأساسي يعمل.</b></p>'
            '<h3>سيتوقف:</h3><ul class="impact"><li>الحملات والإعلانات النشطة (3)</li><li>الثيمات الموسمية والحركة</li><li>النوافذ المنبثقة والعدّاد التنازلي</li>'
            '<li>الإضافات الاختيارية (مثل الخرائط التفاعلية)</li></ul>'
            '<h3>يبقى يعمل:</h3><ul class="impact" id="safe-keep">' + ''.join(f'<li>{x}</li>' for x in SAFE_KEEP) + '</ul>'
            + area('safe-r', 'السبب (اختياري — يُسجَّل في سجل التدقيق)')
            + f'<p class="ds-muted note">وضع الأمان لا يغلق الموقع. يمكنك إيقافه بنفس الزر. لإيقاف عنصر واحد فقط: <a href="{L(k, "active")}">النشط الآن ← أوقف الآن</a></p>')
    foot = f'<button type="button" class="ds-btn ds-btn--danger">تفعيل وضع الأمان</button><a class="ds-btn ds-btn--outline" href="{L(k, "home")}">إلغاء</a>'
    return dialog('modal' if k == 'd' else 'sheet', 'safe-h', 'تفعيل وضع الأمان؟', body, foot, L(k, 'home'))

# extra: modules outside this round + mobile "more" sheet ----------------------
def later_main(k):
    items = [('pages', 'الصفحات', 'محرر صفحات بسيط من أقسام معتمدة: نص، صورة، إظهار/إخفاء، ترتيب — بلا كسر للتصميم (CMS-010 · CMS-011).'),
             ('knowledge', 'المعرفة (المدونة)', 'المقالات بالعربية والإنجليزية، الجدولة، والنشر بموافقتك — لا مقالات تلقائية (M25 §28 · M32 §30).'),
             ('settings', 'الإعدادات', 'التنقل، التذييل، شريط الإعلان، الإشعار الطارئ، الزر العام. لا إدارة مستخدمين في V1 — اللوحة للمالك وحده (M30).')]
    lis = ''.join(f'<li class="ds-card rcard" id="{i}"><h2 class="ht">{t}</h2><p>{d}</p><p>{st("في جولة الـWireframes التالية — بعد اعتماد هذه الجولة")}</p></li>' for i, t, d in items)
    return (ptitle('وحدات خارج هذه الجولة', 'هذه البنود موجودة في القائمة حتى يكتمل شكل الـIA، وتصميمها في الجولة التالية.')
            + f'<ul class="rcards">{lis}</ul><p class="ds-muted note mt"><a href="index.html">فهرس كل الشاشات</a></p>')

def more_sheet():
    body = ('<form role="search" action="#" class="ds-field"><label class="ds-label" for="gs-m">بحث في لوحة التحكم</label><input class="ds-input" type="search" id="gs-m" dir="auto" placeholder="صنف، صفحة، فرع، حملة…"></form>')
    for g, label, items in GROUPS:
        if g in MORE_GROUPS:
            body += (f'<section aria-labelledby="mg-{g}"><h3 id="mg-{g}">{label}</h3><ul class="rows">'
                     + ''.join(f'<li><a href="{target("m", t)}">{esc(lab)}</a></li>' for _, lab, t in items) + '</ul></section>')
    return dialog('sheet', 'more-h', 'المزيد', body, '', 'm-home.html')

# ---------------------------------------------------------------- build
def build():
    for k in ('d', 'm'):
        shell(k, 'home', home_main(k))
        shell(k, 'analytics', analytics_main(k))
        shell(k, 'menu', menu_main(k))
        shell(k, 'product', product_main(k))
        shell(k, 'impact', product_main(k), impact_dialog(k))
        shell(k, 'guard', product_main(k), guard_dialog(k))
        shell(k, 'branch', branch_main(k))
        shell(k, 'active', active_main(k))
        shell(k, 'calendar', calendar_main(k))
        shell(k, 'experience', experience_main(k))
        shell(k, 'family', family_main(k))
        shell(k, 'media', media_main(k))
        shell(k, 'global-data', global_main(k))
        shell(k, 'facts', facts_main(k))
        shell(k, 'parity', parity_main(k))
        shell(k, 'health', health_main(k))
        shell(k, 'performance', performance_main(k))
        shell(k, 'a11y', a11y_main(k))
        shell(k, 'security', security_main(k))
        shell(k, 'privacy', privacy_main(k))
        shell(k, 'seo', seo_main(k))
        shell(k, 'reputation', reputation_main(k))
        shell(k, 'feedback', feedback_main(k))
        shell(k, 'audit', audit_main(k))
        shell(k, 'safe-mode', home_main(k), safe_dialog(k))
        shell(k, 'later', later_main(k))
    shell('m', 'more', home_main('m'), more_sheet())

INDEX = [  # n, key, title, requirement / area
    (1, 'home', 'مركز القيادة: يحتاج انتباه · النشط الآن · الزيارات', 'DASH-007 · DASH-017 · DASH-018 · M32 §39–41'),
    (2, 'analytics', 'التحليلات', 'DASH-008…012 · DASH-030 · M25 §5–12'),
    (3, 'menu', 'المنيو: قائمة الأصناف', 'CMS-005 · CMS-015 · M25 §23–24'),
    (4, 'product', 'محرر الصنف', 'CMS-015/016 · M25 §23 · M30 MENU'),
    (5, 'impact', 'أثر التغيير قبل النشر', 'M32 §04'),
    (6, 'guard', 'فحص النشر PASS / WARNING / BLOCKING', 'M32 §05'),
    (7, 'branch', 'الفروع والساعات', 'CMS-013 · D-021 · M25 §25–26'),
    (8, 'active', 'النشط الآن + أوقف الآن + التعارض', 'M31 §11–12 · DX §5'),
    (9, 'calendar', 'التقويم الموحد + كاشف التعارض', 'M32 §08–10'),
    (10, 'experience', 'محرر الحملة / التجربة', 'M25 §27 · M31 §3–4, §22–23'),
    (11, 'family', 'الموظف المثالي + SHELTER Family', 'M31 §1–2, §27'),
    (12, 'media', 'مركز الوسائط + الحقوق + Press Kit', 'MEDIA-012…015 · M32 §17–18'),
    (13, 'global-data', 'البيانات العامة', 'M32 §02'),
    (14, 'facts', 'سجل الحقائق', 'M32 §03'),
    (15, 'parity', 'تطابق العربي والإنجليزي', 'M32 §07'),
    (16, 'health', 'صحة الموقع (روابط · Schema · Sitemap · فهرسة · نسخ احتياطي · اعتماديات)', 'DASH-014 · M32 §06, §33–38'),
    (17, 'performance', 'الأداء الفعلي (RUM p75) + التنبيهات', 'PERF-020 · M32 §11–12'),
    (18, 'a11y', 'الوصولية', 'M25 §40 · M32 §31–32'),
    (19, 'security', 'الأمان', 'SEC-005…009 · M32 §19–20'),
    (20, 'privacy', 'الخصوصية والبيانات + عمر السجلات', 'PRIV-* · M32 §21–22'),
    (21, 'seo', 'SEO + البحث داخل الموقع + فرص المحتوى', 'SEO-038 · DASH-013 · M32 §28–30'),
    (22, 'reputation', 'السمعة (PENDING INTEGRATION) + مسار الرد', 'M32 §13–14'),
    (23, 'feedback', 'آراء العملاء (AI ASSISTED ANALYSIS)', 'M32 §15–16'),
    (24, 'audit', 'سجل التدقيق + سجل النسخ', 'M25 §34–35 · M30'),
    (25, 'safe-mode', 'تأكيد وضع الأمان', 'M32 §23–24'),
]

def build_index():
    rows = ''.join(f'<li><span><b>{i}.</b> {t} <span class="ds-caption">· {r}</span></span><span class="flex"><a href="d-{key}.html">Desktop</a><a href="m-{key}.html">Mobile</a></span></li>'
                   for i, key, t, r in INDEX)
    nav = ''.join(f'<li><span><b>{label}</b> · {"، ".join(lab for _, lab, _ in items)}</span></li>' for _, label, items in GROUPS)
    body = (f'<main class="ds-container idx"><h1>Owner Dashboard — فهرس الـWireframes</h1>'
            f'<p class="lead">LOW-FI (P04 · M25 المرحلة F + M32). بلا هوية بصرية. عربي RTL أولًا. كل الأرقام {DEMO}. Desktop من 1024px، وMobile لما دونه.</p>'
            f'<section class="ds-card panel" aria-labelledby="ix-s"><h2 id="ix-s" class="ht">الشاشات (25)</h2><ul class="rows">{rows}</ul></section>'
            f'<section class="ds-card panel" aria-labelledby="ix-n"><h2 id="ix-n" class="ht">القائمة الجانبية: 7 مجموعات</h2><ul class="rows">{nav}</ul>'
            '<p class="ds-muted note">الموبايل: شريط سفلي بخمسة عناصر (الرئيسية · المحتوى · التجارب · الأعمال · المزيد) — <a href="m-more.html">ورقة «المزيد»</a>. '
            '<a href="d-later.html">وحدات خارج هذه الجولة</a>.</p></section>'
            f'<section class="ds-card panel" aria-labelledby="ix-l"><h2 id="ix-l" class="ht">وحدات موجودة في مكان آخر (روابط فقط)</h2><ul class="rows">'
            f'<li><span>التوظيف — docs/careers/wireframes</span><a href="{CAREERS.format(k="d")}">فتح</a></li>'
            f'<li><span>الشراكات — docs/franchise/wireframes</span><a href="{FRANCHISE.format(k="d")}">فتح</a></li></ul></section></main>')
    write('index.html', page(body, 'فهرس الـWireframes · Owner Dashboard', 'INDEX'))

if __name__ == '__main__':
    if os.path.isdir(OUT):
        shutil.rmtree(OUT)
    os.makedirs(OUT)
    build(); build_index()
    print(len(os.listdir(OUT)), 'pages →', OUT)
