"""Careers & Recruitment LOW-FI wireframes (M28 phases 6 + 7). Deterministic: run `python3 build.py` from anywhere.
Writes static HTML into ../html/. No brand values (identity files missing), no real data, no external assets.
The only script is a tiny prototype helper on the two form variants (conditional fields, client-side checks,
step navigation) so the A/B test can be measured. It is NOT production code."""
import html, json, os, re, shutil

ROOT = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(ROOT, '..', 'html'))
esc = html.escape

# ---------------------------------------------------------------- frozen owner values (M28) — do not edit without an owner decision
CONSENT = 'أقر بأن جميع المعلومات المدخلة في طلب التوظيف صحيحة، وأوافق على قيام SHELTER COFFEE بجمع واستخدام بياناتي لغرض دراسة طلب التوظيف والتواصل معي بشأنه.'
GENDER = ['ذكر', 'أنثى']
MARITAL = ['أعزب', 'متزوج', 'أخرى']
NAT = ['أردني', 'غير أردني']
EDU = ['توجيهي ناجح', 'توجيهي راسب', 'بكالوريوس', 'ماستر', 'دكتوراه', 'على مقاعد الدراسة']
EXP = ['بدون خبرة', 'أقل من سنة', '1–2 سنة', '3–5 سنوات', '6–10 سنوات', 'أكثر من 10 سنوات']
YESNO = ['نعم', 'لا']
MONTHS = ['كانون الثاني', 'شباط', 'آذار', 'نيسان', 'أيار', 'حزيران', 'تموز', 'آب', 'أيلول', 'تشرين الأول', 'تشرين الثاني', 'كانون الأول']
YEARS = list(range(2010, 1945, -1))  # technical placeholder range (descending) — not an age rule
CITY_SAMPLE = ['مدينة تجريبية 1', 'مدينة تجريبية 2', 'مدينة تجريبية 3']
STATUSES = ['تم الاستلام', 'قيد المراجعة', 'مرشح للمقابلة', 'تمت المقابلة', 'مقبول', 'مرفوض', 'مؤرشف']
SORTS = ['الأحدث أولًا', 'الأقدم أولًا', 'الراتب الأعلى → الأقل', 'الراتب الأقل → الأعلى', 'سنوات الخبرة الأعلى → الأقل', 'سنوات الخبرة الأقل → الأعلى']  # verbatim M28 §39
RANGES = ['اليوم', 'آخر 7 أيام', 'آخر 30 يوم', 'هذا الشهر', 'كل الوقت']
LOCS = ['SHELTER COFFEE DRIVE', 'SHELTER COFFEE HOUSE']
COLS = ['الاسم', 'الوظيفة المتقدم لها', 'المدينة', 'سنوات الخبرة', 'الراتب المتوقع', 'تاريخ التقديم', 'حالة الطلب']
COLS_OPT = ['رقم الطلب', 'رقم الهاتف', 'البريد الإلكتروني', 'المؤهل العلمي', 'الجنس', 'الجنسية', 'آخر تحديث']
FILTERS = ['الاسم', 'رقم الهاتف', 'الوظيفة', 'المدينة', 'المؤهل العلمي', 'سنوات الخبرة', 'الحالة', 'الجنس', 'الجنسية', 'تاريخ التقديم']
KPIS = [('الطلبات الجديدة', 5, 'new'), ('إجمالي الطلبات', 42, 'all'), ('قيد المراجعة', 12, 'review'), ('مرشحون للمقابلة', 6, 'shortlist'),
        ('تمت المقابلة', 4, 'interviewed'), ('المقبولون', 3, 'accepted'), ('المرفوضون', 9, 'rejected'), ('المؤرشفون', 8, 'archived')]
JOBS = ['باريستا', 'محاسب تكاليف', 'كاشير', 'مشرف', 'Warehouse', 'Cost Accountant']  # the owner's own examples (M28 §09)
SAMPLE = 'بيانات تجريبية'
ON, CUR, UNREAD = ' class="on"', ' aria-current="page"', ' class="unread"'
CVB, CVB2 = '<span class="ds-badge badge cv">السيرة الذاتية</span>', '<span class="ds-badge badge cv">السيرة الذاتية الأساسية</span>'

# ---------------------------------------------------------------- form fields, in UX order (6 groups · 19 owner fields)
GROUPS = [('personal', 'البيانات الشخصية'), ('home', 'السكن'), ('edu', 'المؤهل والخبرة'), ('work', 'معلومات العمل'), ('files', 'المرفقات'), ('consent', 'الإقرار')]
F = [  # key, group, kind, label, options, hint, empty-message
    ('name', 'personal', 'text', 'الاسم الكامل', None, 'بالعربية أو الإنجليزية', 'أدخل الاسم الكامل'),
    ('phone', 'personal', 'tel', 'رقم الهاتف', None, 'مثل 079… أو ‎+962…', 'أدخل رقم الهاتف'),
    ('email', 'personal', 'email', 'البريد الإلكتروني', None, None, 'أدخل البريد الإلكتروني'),
    ('gender', 'personal', 'radio', 'الجنس', GENDER, None, 'اختر الجنس'),
    ('dob', 'personal', 'dob', 'تاريخ الميلاد', None, None, 'اختر تاريخ الميلاد: اليوم والشهر والسنة'),
    ('marital', 'personal', 'select', 'الحالة الاجتماعية', MARITAL, None, 'اختر الحالة الاجتماعية'),
    ('nat', 'personal', 'radio', 'الجنسية', NAT, None, 'اختر الجنسية'),
    ('city', 'home', 'select', 'المدينة', CITY_SAMPLE, 'القائمة الكاملة لمدن الأردن: PENDING DATA VERIFICATION — الخيارات الظاهرة عيّنة فقط', 'اختر المدينة'),
    ('area', 'home', 'text', 'المنطقة', None, 'بالعربية أو الإنجليزية — لا نطلب العنوان الكامل', 'أدخل المنطقة'),
    ('edu', 'edu', 'select', 'المؤهل العلمي', EDU, None, 'اختر المؤهل العلمي'),
    ('years', 'edu', 'select', 'سنوات الخبرة', EXP, None, 'اختر سنوات الخبرة'),
    ('same', 'edu', 'radio', 'هل لديك خبرة سابقة في نفس المجال أو الوظيفة التي تتقدم لها؟', YESNO, None, 'أجب عن سؤال الخبرة السابقة'),
    ('working', 'edu', 'radio', 'هل تعمل حاليًا؟', YESNO, None, 'أجب عن سؤال: هل تعمل حاليًا؟'),
    ('job', 'work', 'text', 'الوظيفة المتقدم لها', None, 'اكتبها كما تريد، مثل: باريستا أو Cost Accountant', 'اكتب الوظيفة المتقدم لها'),
    ('salary', 'work', 'salary', 'الراتب المتوقع', None, 'بالدينار الأردني، أرقام فقط', 'أدخل الراتب المتوقع بالأرقام'),
    ('license', 'work', 'radio', 'هل لديك رخصة قيادة؟', YESNO, None, 'أجب عن سؤال رخصة القيادة'),
    ('notes', 'work', 'textarea', 'ملاحظات إضافية', None, 'بالعربية أو الإنجليزية', 'أدخل الملاحظات الإضافية'),
    ('files', 'files', 'upload', 'المرفقات', None, 'السيرة الذاتية مطلوبة. يمكنك إضافة شهادات أو دورات أو مستندات داعمة (إضافية واختيارية).', 'أرفق السيرة الذاتية'),
    ('consent', 'consent', 'checkbox', CONSENT, None, None, 'يجب الموافقة على الإقرار لإرسال الطلب'),
]
COND = {  # nationality → conditional fields (required as soon as they appear)
    'أردني': [('nid', 'text', 'الرقم الوطني', None, 'أدخل الرقم الوطني')],
    'غير أردني': [('natx', 'text', 'ما هي جنسيتك؟', 'بالعربية أو الإنجليزية', 'اكتب جنسيتك'),
                  ('doc', 'text', 'رقم جواز السفر أو رقم وثيقة الهوية', None, 'أدخل رقم جواز السفر أو رقم وثيقة الهوية')],
}
STEPS = [('البيانات الشخصية', ['personal']), ('السكن والمؤهل والخبرة', ['home', 'edu']), ('معلومات العمل', ['work']), ('المرفقات والإقرار', ['files', 'consent'])]
FILLED = {'name': 'متقدم تجريبي 1', 'phone': '0700000001', 'email': 'applicant1@example.com', 'gender': 'ذكر', 'dob': ('12', '4', '1998'),
          'marital': 'أعزب', 'nat': 'أردني', 'nid': '0000001234', 'city': CITY_SAMPLE[0], 'area': 'منطقة تجريبية', 'edu': 'بكالوريوس',
          'years': '3–5 سنوات', 'same': 'نعم', 'working': 'لا', 'job': 'باريستا', 'salary': '450', 'license': 'نعم',
          'notes': 'نص تجريبي للملاحظات الإضافية.', 'consent': True}
FILES3 = [('سيرة-ذاتية-تجريبية.pdf', 'PDF · 240 KB', 'cv'), ('شهادة-تجريبية.jpg', 'JPG · 1.2 MB', 'ok'), ('دورة-تدريبية-تجريبية.pdf', 'PDF · 380 KB', 'up')]

# ---------------------------------------------------------------- ONE design system (M34): shared tokens + wireframe kit, read at build time
DS = os.path.normpath(os.path.join(ROOT, '..', '..', '..', '..', 'design-system'))
DS_CSS = ''.join(open(os.path.join(DS, *p), encoding='utf-8').read() + '\n' for p in (('build', 'tokens.css'), ('wireframe-kit.css',)))

# ---------------------------------------------------------------- careers layout only: every value is a token (var(--…)) or a kit class
# Kit components used in the markup: .ds-btn (+ --primary/--outline/--ghost/--danger/--icon) · .ds-chip · .ds-card · .ds-badge · .ds-table · .ds-alert
# (+ --danger/--info/--warning) · .ds-overlay · .ds-modal · .ds-sheet · .ds-caption · .wf-note. Keep: text >= 12px, targets >= var(--touch-target).
# Field borders use --color-muted (not --color-border, 1.6:1) so input boundaries keep >= 3:1 (WCAG 1.4.11), as before (#767676).
# Raw px left on purpose = layout dimensions with no token: 56/60px row heights, 88px KPI, 120px drop zone, 72px jump field, 96px/140px/160px/260px
# column minimums, 190/200px side columns, 460px drawer; 3px focus outline = kit :focus-visible.
CSS = '''
a{color:var(--color-text)}p{margin:0 0 var(--space-2)}
.lnk,.rows a:not(.ds-btn){display:inline-flex;align-items:center;min-height:var(--touch-target)}
.wf-tag{background:var(--color-accent);color:var(--color-on-accent);font-size:var(--text-xs);padding:var(--space-1) var(--space-3)}
.sample{display:inline-block;vertical-align:middle}
.vh{position:absolute!important;width:1px;height:1px;margin:0;padding:0;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0}
.site{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:calc(var(--sticky-stack) / 2);padding:0 var(--space-4);border-bottom:var(--border-default) solid var(--color-border)}
.logo{font-weight:var(--weight-bold);font-size:var(--text-base)}
.ds-btn.block{display:flex;width:100%}
.legend{margin:var(--space-6) var(--space-4) var(--space-4);border-top:var(--border-default) solid var(--color-border);padding-top:var(--space-2)}
.note{font-size:var(--text-sm);color:var(--color-muted)}
/* public form */
.wrap{max-width:var(--container-reading);margin:0 auto;padding:0 var(--space-4) var(--space-8)}
.intro h1{font-size:var(--text-3xl);margin:var(--space-5) 0 var(--space-2)}.intro p{color:var(--color-muted);font-size:var(--text-sm)}
.ph{display:block;margin:var(--space-2) 0 var(--space-3)}
.group{margin:0 0 var(--space-5)}
.group>h2{font-size:var(--text-xl);margin:0 0 var(--space-4)}
.f{margin:0 0 var(--space-4);min-width:0}.f:last-child{margin-bottom:0}
fieldset{border:0;margin:0;padding:0;min-width:0}
.f>label,.lbl,.f>legend{display:block;font-weight:var(--weight-bold);font-size:var(--text-sm);margin:0 0 var(--space-1);padding:0}
.req{margin-inline-start:var(--space-1);font-weight:var(--weight-bold)}
.hint{display:block;font-size:var(--text-xs);color:var(--color-muted);margin:0 0 var(--space-2)}
input[type=text],input[type=tel],input[type=email],input[type=search],input[type=date],input[type=time],select,textarea{display:block;width:100%;min-height:var(--control-md);border:var(--border-strong) solid var(--color-muted);border-radius:var(--radius-md);padding:var(--space-2) var(--space-3);font:inherit;font-size:var(--text-base);background:var(--color-bg);color:var(--color-text)}
::placeholder{color:var(--color-muted);opacity:1}
textarea{min-height:calc(var(--control-lg) * 2);resize:vertical}
input[type=radio],input[type=checkbox]{width:var(--icon-lg);height:var(--icon-lg);margin:0;flex:none;accent-color:var(--color-accent)}
.opts{display:flex;flex-wrap:wrap;gap:var(--space-2)}
.tap{display:inline-flex;align-items:center;gap:var(--space-2);min-height:var(--touch-target);min-width:var(--touch-target);padding:var(--space-1) var(--space-3);border:var(--border-strong) solid var(--color-border);border-radius:var(--radius-md);cursor:pointer}
.tap:has(input:checked){border-color:var(--color-accent);background:var(--color-surface);font-weight:var(--weight-bold)}
.tap.consent{align-items:flex-start;padding:var(--space-3)}
.dob{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,2fr) minmax(0,1.4fr);gap:var(--space-2)}
.dob label{display:block;font-size:var(--text-sm);color:var(--color-muted);margin-bottom:var(--space-1)}
.ig{display:flex;align-items:stretch}.ig input{border-start-end-radius:0;border-end-end-radius:0;min-width:0}
.ig .sfx{display:flex;align-items:center;padding:0 var(--space-3);border:var(--border-strong) solid var(--color-muted);border-inline-start:0;border-start-end-radius:var(--radius-md);border-end-end-radius:var(--radius-md);background:var(--color-surface);font-weight:var(--weight-bold);white-space:nowrap}
.cond{border-inline-start:var(--space-1) solid var(--color-border);padding-inline-start:var(--space-3);margin-top:var(--space-3)}
.cond .f{margin-bottom:var(--space-3)}
.f>.drop,.drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:var(--space-2);min-height:120px;border:var(--border-strong) dashed var(--color-muted);border-radius:var(--radius-lg);padding:var(--space-4);text-align:center;cursor:pointer;background:var(--color-surface);font-weight:var(--weight-bold)}
.drop span{display:block}.drop small{display:block;font-weight:var(--weight-regular);font-size:var(--text-sm);color:var(--color-muted)}
.vh:focus-visible+.drop{outline:3px solid var(--color-focus);outline-offset:2px}
.files{list-style:none;margin:var(--space-3) 0 0;padding:0;display:grid;gap:var(--space-2)}
.file{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2) var(--space-3);border:var(--border-default) solid var(--color-border);border-radius:var(--radius-md);padding:var(--space-2) var(--space-3);min-height:56px}
.file .fname{flex:1 1 160px;min-width:0;overflow-wrap:anywhere;font-size:var(--text-sm)}
.fmeta{font-size:var(--text-sm);color:var(--color-muted)}
.badge{display:inline-flex;align-items:center;gap:var(--space-1)}
.badge.cv,.new{background:var(--color-accent);color:var(--color-on-accent);border-color:var(--color-accent)}
.bar{flex-basis:100%;height:var(--space-2);border-radius:var(--radius-pill);background:var(--color-border);overflow:hidden}.bar i{display:block;height:100%;width:60%;background:var(--color-accent)}
.file.bad{border:var(--border-strong) solid var(--color-danger);border-inline-start-width:var(--space-1)}
.alert{margin:0 0 var(--space-4)}
.alert h2,.alert h3{font-size:var(--text-lg);margin:0 0 var(--space-2)}.alert p{font-size:var(--text-sm)}
.esum{margin:0 0 var(--space-5)}
.esum:focus{outline:3px solid var(--color-focus);outline-offset:2px}
.esum-h{font-size:var(--text-lg);margin:0 0 var(--space-2);display:flex;gap:var(--space-2);align-items:center}
.esum ul{margin:0;padding:0;list-style:none}.esum li a{display:flex;align-items:center;min-height:var(--touch-target);font-weight:var(--weight-bold);text-underline-offset:3px}
.err{display:flex;gap:var(--space-2);align-items:flex-start;font-weight:var(--weight-bold);font-size:var(--text-sm);color:var(--color-danger);margin:0 0 var(--space-2)}
.err svg{flex:none;margin-top:var(--space-1)}
.has-err{border-inline-start:var(--space-1) solid var(--color-danger);padding-inline-start:var(--space-3)}
.has-err input,.has-err select,.has-err textarea{border-width:calc(2 * var(--border-strong));border-color:var(--color-danger)}
.submit{margin-top:var(--space-2)}
.prog{margin:0 0 var(--space-4)}.prog-t{font-weight:var(--weight-bold);margin:0 0 var(--space-2)}
.bars{display:grid;grid-template-columns:repeat(4,1fr);gap:var(--space-2);list-style:none;margin:0;padding:0}.bars li{height:var(--space-2);border-radius:var(--radius-pill);background:var(--color-border)}.bars li.on{background:var(--color-accent)}
.nav{display:flex;gap:var(--space-3);justify-content:space-between;margin-top:var(--space-2)}.nav .ds-btn{flex:1}
.center{text-align:center}.appno{font-size:var(--text-3xl);font-weight:var(--weight-bold);letter-spacing:var(--tracking-en-caps);margin:var(--space-1) 0}
.card{margin:var(--space-4) 0}
.stack{display:flex;flex-direction:column;gap:var(--space-3);margin-top:var(--space-4)}
/* dashboard */
.dtop{display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);min-height:calc(var(--sticky-stack) / 2);padding:0 var(--space-5);border-bottom:var(--border-default) solid var(--color-border)}
.dtop-r{display:flex;align-items:center;gap:var(--space-3);font-size:var(--text-sm)}
.dshell{display:grid;grid-template-columns:200px minmax(0,1fr)}
.dside{border-inline-end:var(--border-default) solid var(--color-border);padding:var(--space-4) var(--space-3)}
.dside-h{font-weight:var(--weight-bold);font-size:var(--text-sm);color:var(--color-muted);margin:var(--space-1) var(--space-3) var(--space-2)}
.dside a,.mtabs a{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:var(--touch-target);padding:0 var(--space-3);border-radius:var(--radius-md);color:var(--color-text);text-decoration:none}
.dside a[aria-current=page],.mtabs a[aria-current=page]{background:var(--color-accent);color:var(--color-on-accent);font-weight:var(--weight-bold)}
.dmain{padding:var(--space-5) var(--space-6) var(--space-10);min-width:0;max-width:var(--container-dashboard)}
.mtop{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:calc(var(--sticky-stack) / 2);padding:0 var(--space-3);border-bottom:var(--border-default) solid var(--color-border)}
.mtabs{display:grid;grid-template-columns:repeat(auto-fit,minmax(88px,1fr));gap:var(--space-2);padding:var(--space-2) var(--space-3);border-bottom:var(--border-default) solid var(--color-border)}
.mtabs a{justify-content:center;flex-wrap:wrap;text-align:center;padding:0 var(--space-2);border:var(--border-strong) solid var(--color-border);font-size:var(--text-sm);line-height:var(--leading-tight)}
.mmain{padding:var(--space-3) var(--space-4) var(--space-8)}
.count{display:inline-flex;align-items:center;justify-content:center;min-width:var(--icon-lg);height:var(--icon-lg);padding:0 var(--space-2);border-radius:var(--radius-pill);background:var(--color-accent);color:var(--color-on-accent);font-size:var(--text-xs);font-weight:var(--weight-bold);white-space:nowrap}
[aria-current=page] .count{background:var(--color-bg);color:var(--color-text)}
.ptitle{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-3);margin:0 0 var(--space-4)}
.ptitle h1{font-size:var(--text-2xl);margin:0}
.sub{font-size:var(--text-sm);color:var(--color-muted)}
.kpis{list-style:none;margin:0 0 var(--space-5);padding:0;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--space-3)}
.kpi{display:flex;flex-direction:column;justify-content:space-between;gap:var(--space-1);min-height:88px;text-decoration:none;color:var(--color-text)}
.kpi-l{font-size:var(--text-sm)}.kpi-n{font-size:var(--text-4xl);font-weight:var(--weight-bold);font-variant-numeric:tabular-nums}
.panel{margin:0 0 var(--space-4)}
.panel>h2{font-size:var(--text-lg);margin:0 0 var(--space-3)}
.rows{list-style:none;margin:0;padding:0}.rows li{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-1) var(--space-3);border-top:var(--border-default) solid var(--color-border);padding:var(--space-2) 0;min-height:56px}.rows li:first-child{border-top:0}
.prev{border-style:dashed}
.st{white-space:nowrap}
.toolbar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:var(--space-3);margin:0 0 var(--space-3)}
.pager+.toolbar{margin-top:var(--space-3)}
.toolbar .f{margin:0}.toolbar .grow{flex:1 1 260px}
.toolbar label,.toolbar legend{font-size:var(--text-sm);font-weight:var(--weight-bold);margin-bottom:var(--space-1);display:block}
.seg{display:flex;gap:var(--space-2)}
.chips{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2);margin:0 0 var(--space-3);font-size:var(--text-sm)}
.tbl-wrap{overflow-x:auto;border:var(--border-default) solid var(--color-border);border-radius:var(--radius-lg)}
.tbl caption{text-align:start;padding:var(--space-3);font-size:var(--text-sm);color:var(--color-muted)}
.tbl th,.tbl td{vertical-align:middle}
.tbl thead th{background:var(--color-surface)}
.tbl tbody tr{height:60px}.compact .tbl tbody tr{height:var(--touch-target)}.compact .tbl td,.compact .tbl th{padding-block:0}
.tbl th[scope=row]{font-weight:var(--weight-regular)}.tbl .nm{display:inline-flex;align-items:center;min-height:var(--touch-target)}
tr.unread .nm,.unread .acard-n a{font-weight:var(--weight-bold)}
.tbl .cb{width:var(--control-lg)}
.cbx{border:0;padding:0;justify-content:center}
.nowrap{white-space:nowrap}
.pager{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2);margin:var(--space-4) 0 0}
.pager .sep{padding:0 var(--space-1)}
.jump{display:flex;align-items:center;gap:var(--space-2);margin-inline-start:auto;font-size:var(--text-sm)}.jump input{width:72px;min-height:var(--touch-target)}
.bulk{display:flex;flex-wrap:wrap;align-items:flex-end;gap:var(--space-3);border:var(--border-strong) solid var(--color-accent);border-radius:var(--radius-lg);padding:var(--space-3);margin:0 0 var(--space-3);background:var(--color-surface)}
.bulk .f{margin:0}.bulk label{font-size:var(--text-sm);font-weight:var(--weight-bold);display:block;margin-bottom:var(--space-1)}
.bulk-n{font-weight:var(--weight-bold);margin-inline-end:auto;align-self:center}
.acards{list-style:none;margin:0;padding:0;display:grid;gap:var(--space-3)}
@media (min-width:600px){.acards{grid-template-columns:repeat(2,minmax(0,1fr))}.kpis{grid-template-columns:repeat(4,minmax(0,1fr))}}
.acard{display:grid;grid-template-columns:var(--touch-target) minmax(0,1fr) var(--touch-target);gap:var(--space-2);align-items:start}
.acard.solo{grid-template-columns:minmax(0,1fr)}
.acard-n{margin:0;display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-1) var(--space-2)}.acard-n a{display:inline-flex;align-items:center;min-height:var(--touch-target)}
.acard p{font-size:var(--text-sm);margin:0 0 var(--space-1)}
.mbar{position:fixed;inset-inline:0;bottom:0;z-index:var(--z-sticky);background:var(--color-bg);border-top:var(--border-strong) solid var(--color-accent);padding:var(--space-2) var(--space-3);display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:var(--space-2)}
.mbar .bulk-n{grid-column:1/-1;margin:0;font-size:var(--text-sm)}
.has-mbar{padding-bottom:calc(3 * var(--touch-target) + var(--space-12))}
.kv{display:grid;grid-template-columns:minmax(0,1fr);gap:0;margin:0}
.kv>div{display:grid;grid-template-columns:minmax(96px,34%) minmax(0,1fr);gap:var(--space-2);border-top:var(--border-default) solid var(--color-border);padding:var(--space-2) 0}.kv>div:first-child{border-top:0}
.kv dt{color:var(--color-muted);font-size:var(--text-sm)}.kv dd{margin:0;font-weight:var(--weight-bold);overflow-wrap:anywhere}
.masked{font-variant-numeric:tabular-nums;letter-spacing:var(--tracking-en-caps)}
.inline{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2)}
.appgrid{display:grid;grid-template-columns:minmax(0,1fr);gap:var(--space-4)}
.toc{display:none}
.notes{list-style:none;margin:0 0 var(--space-3);padding:0}.notes li{margin:0 0 var(--space-2)}.notes .by{font-size:var(--text-sm);color:var(--color-muted);margin:0}
/* dialogs = kit .ds-modal / .ds-sheet with a fixed header + scrolling body + fixed footer; the drawer has no kit equivalent yet */
.dlg{display:flex;flex-direction:column;padding:0}
.drawer{position:fixed;z-index:var(--z-modal);background:var(--color-bg);box-shadow:var(--shadow-lg);top:0;bottom:0;inset-inline-end:0;width:min(460px,100vw);border-start-start-radius:var(--radius-xl);border-end-start-radius:var(--radius-xl)}
.dhead{display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);padding-block:var(--space-2);padding-inline:var(--space-4) var(--space-3);border-bottom:var(--border-default) solid var(--color-border)}
.dhead h2{font-size:var(--text-xl);margin:0}
.dbody{overflow:auto;padding:var(--space-3) var(--space-4);flex:1 1 auto;min-height:0}
.dfoot{display:flex;flex-wrap:wrap;gap:var(--space-2);padding:var(--space-3) var(--space-4);border-top:var(--border-default) solid var(--color-border)}
.dfoot .ds-btn{flex:1 1 140px}
.cols{list-style:none;margin:0;padding:0}.colrow{display:grid;grid-template-columns:var(--touch-target) minmax(0,1fr) var(--touch-target) var(--touch-target);gap:var(--space-2);align-items:center;border-top:var(--border-default) solid var(--color-border);padding:var(--space-1) 0}
.colrow .tap{border:0;padding:0 var(--space-2)}
.checks{display:flex;flex-wrap:wrap;gap:var(--space-2)}.checks .tap{font-size:var(--text-sm);padding:0 var(--space-3)}
.two{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:var(--space-3)}
.warn{font-size:var(--text-sm);margin:var(--space-2) 0}
@media (min-width:1024px){
 .kpis{grid-template-columns:repeat(4,minmax(0,1fr))}
 .appgrid{grid-template-columns:190px minmax(0,1fr)}
 .toc{display:block;position:sticky;top:var(--space-3);align-self:start}.toc a{display:flex;align-items:center;min-height:var(--touch-target);padding:0 var(--space-3);border-radius:var(--radius-md);font-size:var(--text-sm);text-decoration:none;color:var(--color-text)}
 .appmain section{scroll-margin-top:var(--space-3)}
}
'''

# ---------------------------------------------------------------- prototype-only helper for the two form variants (A/B measurement)
JS = r'''(()=>{const f=document.querySelector('form.app');if(!f)return;
const B=f.classList.contains('steps'),$=(s,r=f)=>r.querySelector(s),$$=(s,r=f)=>[...r.querySelectorAll(s)];
const IC='<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.5"/></svg>';
const N=n=>n==1?'خطأ واحد':n==2?'خطأين':n<=10?n+' أخطاء':n+' خطأً';
function nat(){const v=($('input[name=nat]:checked')||{}).value;$$('[data-when]').forEach(w=>{const on=w.dataset.when===v;w.hidden=!on;$$('input,select,textarea',w).forEach(i=>i.disabled=!on)})}
const CV=/(^|[^a-z])(cv|resume|curriculum)([^a-z]|$)|سيرة/i;
function files(inp){const ul=$('.files'),fs=[...inp.files];ul.innerHTML='';const cv=fs.findIndex(x=>CV.test(x.name));
 fs.forEach((x,i)=>{const li=document.createElement('li');li.className='file';li.innerHTML='<span class="fname" dir="auto"></span><span class="fmeta">تم الرفع</span>'+(i===cv?'<span class="ds-badge badge cv">السيرة الذاتية</span><span class="fmeta">تم التعرف تلقائيًا</span>':'');$('.fname',li).textContent=x.name;ul.append(li)});
 const p=$('.cvpick');if(p){p.hidden=!(fs.length&&cv<0);p.querySelector('.opts').innerHTML=fs.map((x,i)=>'<label class="tap"><input type="radio" name="cvpick" id="cvpick-'+i+'" required> <span dir="auto"></span></label>').join('');$$('.cvpick .opts span').forEach((s,i)=>s.textContent=fs[i].name)}}
function empty(w){const k=w.dataset.kind,c=$$('input,select,textarea',w);
 if(k==='radio')return!c.some(i=>i.checked);if(k==='checkbox')return!c[0].checked;if(k==='dob')return c.some(s=>!s.value);
 if(k==='upload')return!(c[0].files.length||$('.file',w));return!c[0].value.trim()}
function check(w){if(empty(w))return w.dataset.msg;const k=w.dataset.kind,v=(($('input,textarea',w)||{}).value||'').trim();
 if(k==='email'&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))return'أدخل بريدًا إلكترونيًا صحيحًا، مثل name@example.com';
 if(k==='salary'&&!/^[0-9٠-٩]{1,6}$/.test(v))return'أدخل الراتب بالأرقام فقط، مثل 450';
 if(k==='dob'){const[d,m,y]=$$('select',w).map(s=>+s.value);if(new Date(y,m-1,d).getMonth()!==m-1)return'تاريخ الميلاد غير صحيح: هذا الشهر لا يحتوي '+d+' يومًا'}return''}
function clear(){$$('.esum,.err').forEach(e=>e.remove());$$('[aria-invalid]').forEach(i=>{i.removeAttribute('aria-invalid');i.dataset.d?i.setAttribute('aria-describedby',i.dataset.d):i.removeAttribute('aria-describedby')});$$('.has-err').forEach(w=>w.classList.remove('has-err'))}
function validate(scope){clear();const bad=[];
 $$('[data-f]',scope).filter(w=>!w.closest('[hidden]')).forEach(w=>{const m=check(w);if(!m)return;const p=document.createElement('p');p.className='err';p.id='e-'+w.dataset.f;p.innerHTML=IC+'<span>خطأ: '+m+'</span>';
  const a=w.querySelector(':scope>.hint')||w.querySelector(':scope>legend,:scope>label,:scope>.lbl');a?a.after(p):w.prepend(p);w.classList.add('has-err');
  $$('input,select,textarea',w).forEach(i=>{i.setAttribute('aria-invalid','true');i.setAttribute('aria-describedby',(p.id+' '+(i.dataset.d||'')).trim())});bad.push([w.dataset.first,m])});
 if(!bad.length)return true;const s=document.createElement('div');s.className='esum ds-alert ds-alert--danger';s.setAttribute('role','alert');s.tabIndex=-1;s.setAttribute('aria-labelledby','esum-h');
 s.innerHTML='<h2 class="esum-h" id="esum-h">'+IC+(B?'يرجى تصحيح ':'تعذّر إرسال الطلب: يرجى تصحيح ')+N(bad.length)+'</h2><ul></ul>';
 bad.forEach(([id,m])=>{const li=document.createElement('li'),a=document.createElement('a');a.href='#'+id;a.textContent=m;li.append(a);$('ul',s).append(li)});
 (B?scope:f).prepend(s);s.scrollIntoView({block:'start'});s.focus({preventScroll:true});return false}
const steps=$$('.step');let cur=Math.max(0,steps.findIndex(s=>!s.hidden));
function show(i){steps.forEach((s,j)=>s.hidden=j!==i);cur=i;const n=JSON.parse(f.dataset.steps);$('.prog-t').textContent='الخطوة '+(i+1)+' من '+n.length+': '+n[i];
 $$('.bars li').forEach((b,j)=>b.classList.toggle('on',j<=i));window.scrollTo(0,f.getBoundingClientRect().top+scrollY-8);$('.prog-t').focus({preventScroll:true})}
f.addEventListener('change',e=>{if(e.target.name==='nat')nat();if(e.target.type==='file')files(e.target)});
f.addEventListener('click',e=>{const a=e.target.closest('.esum a');if(a){e.preventDefault();const t=document.getElementById(a.hash.slice(1));t.closest('[data-f]').scrollIntoView({block:'start'});t.focus({preventScroll:true});return}
 const n=e.target.closest('[data-nav]');if(n){if(n.dataset.nav==='next'){if(validate(steps[cur]))show(cur+1)}else{clear();show(cur-1)}}});
f.addEventListener('submit',e=>{e.preventDefault();if(validate(B?steps[cur]:f))location.href='success.html'});nat()})();'''

# ---------------------------------------------------------------- icons
def svg(d, s=20, w=2):
    return f'<svg aria-hidden="true" width="{s}" height="{s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{w}">{d}</svg>'
I_X = svg('<path d="M6 6l12 12M18 6L6 18"/>')
I_UP = svg('<path d="M12 16V4M6 10l6-6 6 6M4 20h16"/>', 28)
I_EYE = svg('<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>')
I_MENU = svg('<path d="M4 6h16M4 12h16M4 18h16"/>')
I_BELL = svg('<path d="M6 16V11a6 6 0 1 1 12 0v5l2 2H4z"/><path d="M10 20a2 2 0 0 0 4 0"/>')
I_DRAG = svg('<circle cx="9" cy="6" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="18" r="1"/>', 20, 3)
I_U = svg('<path d="M6 15l6-6 6 6"/>')
I_D = svg('<path d="M6 9l6 6 6-6"/>')
I_WARN = svg('<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.5"/>', 18, 2.5)
I_FILE = svg('<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v4h4"/>')

def page(body, title, state, lang='ar', cls='', script=False):
    d = 'rtl' if lang == 'ar' else 'ltr'
    tag = f'<div class="wf-tag">LOW-FI WIREFRAME · {lang.upper()} · {esc(state)}</div>'
    js = f'<script>{JS}</script>' if script else ''
    return (f'<!doctype html><html lang="{lang}" dir="{d}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            f'<title>{esc(title)}</title><style>{DS_CSS}{CSS}</style></head><body class="{cls}">{tag}{body}{js}</body></html>\n')

DATE = re.compile(r'(?<![\w="-])(\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2})?)(?![\w"-])')

def write(name, content):
    head, sep, rest = content.partition('<body')
    content = head + sep + DATE.sub(r'<span dir="ltr">\1</span>', rest)
    with open(os.path.join(OUT, name), 'w', encoding='utf-8', newline='\n') as fh:
        fh.write(content)

def req():
    return '<span class="req" aria-hidden="true">*</span>'

def opt(v, sel):
    return f'<option{" selected" if sel else ""}>{esc(v)}</option>'

# ---------------------------------------------------------------- public form renderer
def err_p(k, msg):
    return f'<p class="err" id="e-{k}">{I_WARN}<span>خطأ: {esc(msg)}</span></p>'

def ctl_attrs(k, hint, err):
    d = f'h-{k}' if hint else ''
    desc = ' '.join(x for x in [f'e-{k}' if err else '', d] if x)
    a = f' data-d="{d}"' if d else ''
    if desc: a += f' aria-describedby="{desc}"'
    if err: a += ' aria-invalid="true"'
    return a

def text_field(k, kind, label, hint, msg, v, err, when=None):
    t = {'tel': 'tel', 'email': 'email'}.get(kind, 'text')
    extra = {'tel': ' inputmode="tel" autocomplete="tel"', 'email': ' autocomplete="email"', 'salary': ' inputmode="numeric"'}.get(kind, '')
    if k == 'name': extra = ' autocomplete="name"'
    if k == 'job': extra = ' autocomplete="off" spellcheck="false"'
    val = f' value="{esc(v)}"' if v else ''
    a = ctl_attrs(k, hint, err)
    h = f'<span class="hint" id="h-{k}">{esc(hint)}</span>' if hint else ''
    if kind == 'textarea':
        c = f'<textarea id="{k}" name="{k}" dir="auto" required{a}>{esc(v or "")}</textarea>'
    elif kind == 'salary':
        c = f'<div class="ig"><input type="text" id="{k}" name="{k}" dir="auto" required{extra}{val}{a}><span class="sfx" aria-hidden="true">د.أ</span></div>'
    else:
        c = f'<input type="{t}" id="{k}" name="{k}" dir="auto" required{extra}{val}{a}>'
    e = err_p(k, err) if err else ''
    cls = 'f has-err' if err else 'f'
    return f'<div class="{cls}" data-f="{k}" data-kind="{kind}" data-first="{k}" data-msg="{esc(msg)}"><label for="{k}">{esc(label)}{req()}</label>{h}{e}{c}</div>'

def select_field(k, label, opts, hint, msg, v, err, sample=False):
    a = ctl_attrs(k, hint, err)
    h = f'<span class="hint" id="h-{k}">{esc(hint)}</span>' if hint else ''
    o = '<option value="">اختر</option>'
    body = ''.join(opt(x, x == v) for x in opts)
    o += f'<optgroup label="عيّنة — القائمة قيد التحقق">{body}</optgroup>' if sample else body
    e = err_p(k, err) if err else ''
    return (f'<div class="{"f has-err" if err else "f"}" data-f="{k}" data-kind="select" data-first="{k}" data-msg="{esc(msg)}">'
            f'<label for="{k}">{esc(label)}{req()}</label>{h}{e}<select id="{k}" name="{k}" required{a}>{o}</select></div>')

def radio_field(k, label, opts, msg, v, err):
    a = ctl_attrs(k, None, err)
    e = err_p(k, err) if err else ''
    items = ''.join(f'<label class="tap"><input type="radio" id="{k}-{i + 1}" name="{k}" value="{esc(x)}" required{" checked" if x == v else ""}{a}> {esc(x)}</label>' for i, x in enumerate(opts))
    return (f'<fieldset class="{"f has-err" if err else "f"}" data-f="{k}" data-kind="radio" data-first="{k}-1" data-msg="{esc(msg)}">'
            f'<legend>{esc(label)}{req()}</legend>{e}<div class="opts">{items}</div></fieldset>')

def dob_field(msg, v, err):
    a = ctl_attrs('dob', None, err)
    d, m, y = v or ('', '', '')
    days = '<option value="">اختر</option>' + ''.join(f'<option value="{i}"{" selected" if str(i) == d else ""}>{i}</option>' for i in range(1, 32))
    mons = '<option value="">اختر</option>' + ''.join(f'<option value="{i + 1}"{" selected" if str(i + 1) == m else ""}>{i + 1} — {esc(n)}</option>' for i, n in enumerate(MONTHS))
    yrs = '<option value="">اختر</option>' + ''.join(f'<option value="{i}"{" selected" if str(i) == y else ""}>{i}</option>' for i in YEARS)
    e = err_p('dob', err) if err else ''
    return (f'<fieldset class="{"f has-err" if err else "f"}" data-f="dob" data-kind="dob" data-first="dob-d" data-msg="{esc(msg)}"><legend>تاريخ الميلاد{req()}</legend>{e}'
            f'<div class="dob"><div><label for="dob-d">اليوم</label><select id="dob-d" name="dob-d" required{a}>{days}</select></div>'
            f'<div><label for="dob-m">الشهر</label><select id="dob-m" name="dob-m" required{a}>{mons}</select></div>'
            f'<div><label for="dob-y">السنة</label><select id="dob-y" name="dob-y" required{a}>{yrs}</select></div></div></fieldset>')

def files_list(files):
    out = ''
    for name, meta, st in files:
        x = f'<button type="button" class="btn ds-btn ds-btn--ghost ds-btn--icon" aria-label="إزالة الملف {esc(name)}">{I_X}</button>'
        if st == 'cv':
            out += f'<li class="file"><span class="fname" dir="auto">{esc(name)}</span><span class="fmeta">{meta} · تم الرفع</span><span class="ds-badge badge cv">السيرة الذاتية</span><span class="fmeta">تم التعرف تلقائيًا</span><button type="button" class="btn ds-btn ds-btn--ghost">ليست السيرة الذاتية؟ غيّر</button>{x}</li>'
        elif st == 'up':
            out += f'<li class="file"><span class="fname" dir="auto">{esc(name)}</span><span class="fmeta">{meta} · جارٍ الرفع 60%</span>{x}<span class="bar" role="progressbar" aria-label="تقدم رفع {esc(name)}" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><i></i></span></li>'
        elif st == 'bad':
            out += f'<li class="file bad"><span class="fname" dir="auto">{esc(name)}</span><span class="fmeta">{meta}</span><span class="ds-badge badge">{I_WARN} مرفوض</span>{x}</li>'
        else:
            out += f'<li class="file"><span class="fname" dir="auto">{esc(name)}</span><span class="fmeta">{meta} · تم الرفع</span>{x}</li>'
    return out

def upload_field(msg, files, err, cvpick=None, rejected=None):
    hint = F[17][5]
    a = ctl_attrs('files', hint, err)
    e = err_p('files', err) if err else ''
    rj = ''
    if rejected:
        rj = ('<div class="alert ds-alert ds-alert--danger" role="alert"><h3>تعذّر رفع ملفين</h3>'
              '<p><b>setup.exe:</b> تم رفض الملف لأسباب أمنية — هذا النوع من الملفات غير مسموح.</p>'
              '<p><b>فيديو-كبير-تجريبي.mov:</b> حجم الملف أكبر من الحد المسموح. يمكنك ضغطه أو رفع ملف أصغر. <span class="wf-note sample">الحد التقني يُحدد بعد Cloudways audit</span></p>'
              '<p class="note">بقية الملفات وبيانات النموذج محفوظة.</p></div>')
    pick = ''
    if cvpick:
        radios = ''.join(f'<label class="tap"><input type="radio" name="cvpick" id="cvpick-{i}" required> <span dir="auto">{esc(n)}</span></label>' for i, n in enumerate(cvpick))
        pick = ('<div class="alert ds-alert ds-alert--info" role="status"><h3>لم نتمكن من تحديد السيرة الذاتية تلقائيًا</h3><p>اختر أي ملف هو السيرة الذاتية لإكمال الطلب.</p></div>'
                f'<fieldset class="f cvpick" data-f="cvpick" data-kind="radio" data-first="cvpick-0" data-msg="اختر أي ملف هو السيرة الذاتية"><legend>اختر أي ملف هو السيرة الذاتية{req()}</legend><div class="opts">{radios}</div></fieldset>')
    else:
        pick = ('<fieldset class="f cvpick" data-f="cvpick" data-kind="radio" data-first="cvpick-0" data-msg="اختر أي ملف هو السيرة الذاتية" hidden>'
                f'<legend>اختر أي ملف هو السيرة الذاتية{req()}</legend><div class="opts"></div></fieldset>')
    return (f'<div class="{"f has-err" if err else "f"}" data-f="files" data-kind="upload" data-first="files" data-msg="{esc(msg)}" id="files-wrap">'
            f'<span class="lbl" id="files-l">المرفقات{req()}</span><span class="hint" id="h-files">{esc(hint)}</span>{e}'
            f'<input class="vh" type="file" id="files" name="files" multiple required aria-labelledby="files-l files-d"{a}>'
            f'<label class="drop" for="files">{I_UP}<span id="files-d">اسحب الملفات هنا أو اضغط للرفع</span><small>CV والشهادات والدورات والمستندات الداعمة · تُرفض الملفات التنفيذية والخطرة</small></label>'
            f'{rj}<ul class="files" aria-label="الملفات المرفوعة">{files_list(files or [])}</ul>{pick}</div>')

def consent_field(msg, v, err):
    a = ctl_attrs('consent', None, err)
    e = err_p('consent', err) if err else ''
    return (f'<div class="{"f has-err" if err else "f"}" data-f="consent" data-kind="checkbox" data-first="consent" data-msg="{esc(msg)}">{e}'
            f'<label class="tap consent"><input type="checkbox" id="consent" name="consent" required{" checked" if v else ""}{a}> <span>{esc(CONSENT)}{req()}</span></label></div>')

def render_field(fd, vals, errs, opts):
    k, g, kind, label, options, hint, msg = fd
    v, e = vals.get(k), errs.get(k)
    if kind in ('text', 'tel', 'email', 'textarea', 'salary'):
        return text_field(k, kind, label, hint, msg, v, e)
    if kind == 'select':
        return select_field(k, label, options, hint, msg, v, e, sample=(k == 'city'))
    if kind == 'radio':
        h = radio_field(k, label, options, msg, v, e)
        if k == 'nat':
            for when, fields in COND.items():
                inner = ''.join(text_field(ck, ckind, cl, ch, cm, vals.get(ck), errs.get(ck)) for ck, ckind, cl, ch, cm in fields)
                shown = vals.get('nat') == when
                h += f'<div class="cond" data-when="{esc(when)}"{"" if shown else " hidden"}>{inner}</div>'
        return h
    if kind == 'dob':
        return dob_field(msg, v, e)
    if kind == 'upload':
        return upload_field(msg, opts.get('files'), e, opts.get('cvpick'), opts.get('rejected'))
    if kind == 'checkbox':
        return consent_field(msg, v, e)

def group_html(gid, gname, vals, errs, opts):
    inner = ''.join(render_field(fd, vals, errs, opts) for fd in F if fd[1] == gid)
    return f'<section class="group ds-card" aria-labelledby="g-{gid}"><h2 id="g-{gid}">{esc(gname)}</h2>{inner}</section>'

FIRST_ID = {fd[0]: ({'radio': f'{fd[0]}-1', 'dob': 'dob-d'}.get(fd[2], fd[0])) for fd in F}
FIRST_ID.update({'nid': 'nid', 'natx': 'natx', 'doc': 'doc'})
MSG_LABEL = {fd[0]: fd[3] for fd in F}

def summary(errs, step=False):
    n = len(errs)
    word = 'خطأ واحد' if n == 1 else 'خطأين' if n == 2 else f'{n} أخطاء' if n <= 10 else f'{n} خطأً'
    head = ('يرجى تصحيح ' if step else 'تعذّر إرسال الطلب: يرجى تصحيح ') + word
    items = ''.join(f'<li><a href="#{FIRST_ID[k]}">{esc(m)}</a></li>' for k, m in errs.items())
    return f'<div class="esum ds-alert ds-alert--danger" role="alert" tabindex="-1" aria-labelledby="esum-h"><h2 class="esum-h" id="esum-h">{I_WARN}{esc(head)}</h2><ul>{items}</ul></div>'

def site_header(lang='ar'):
    sw = '<a class="btn ds-btn ds-btn--ghost" href="en-careers.html" lang="en" hreflang="en">EN</a>' if lang == 'ar' else '<a class="btn ds-btn ds-btn--ghost" href="form-a.html" lang="ar" hreflang="ar">عربي</a>'
    return f'<header class="site"><span class="logo">SHELTER COFFEE</span>{sw}</header>'

def intro():
    return ('<div class="intro"><h1>التوظيف — طلب توظيف</h1>'
            '<span class="wf-note ph">نص تعريفي قصير عن العمل في SHELTER COFFEE — MISSING — OWNER INPUT REQUIRED</span>'
            f'<p>جميع الحقول مطلوبة ومُعلَّمة بـ <b aria-hidden="true">*</b><span class="vh">نجمة</span>. الملفات الإضافية بعد السيرة الذاتية اختيارية. يمكنك الكتابة بالعربية أو الإنجليزية.</p>'
            '<p><a class="lnk" href="track.html">لديك طلب سابق؟ تابع حالة طلبك</a></p></div>')

def legend(extra=''):
    return (f'<p class="ds-caption legend">LOW-FI WIREFRAME — تخطيط وسلوك فقط، بلا هوية بصرية (ملفات الهوية غير متوفرة). "{SAMPLE}" و"عيّنة" = قيم توضيحية وليست بيانات حقيقية. '
            f'نطاق سنوات الميلاد تقني مؤقت ولا يمثل شرط عمر. {extra}</p>')

def form_a(name, state, vals=None, errs=None, opts=None, banner='', focus_files=False):
    vals, errs, opts = vals or {}, errs or {}, opts or {}
    groups = ''.join(group_html(g, n, vals, errs, opts) for g, n in GROUPS)
    top = (summary(errs) if errs else '') + banner
    form = (f'<form class="app" action="success.html" method="post" novalidate>{top}{groups}'
            '<div class="submit"><button type="submit" class="btn ds-btn ds-btn--primary block">إرسال الطلب</button></div></form>')
    body = site_header() + f'<main class="wrap">{intro()}{form}</main>' + legend()
    write(name, page(body, 'طلب توظيف — نموذج A', f'CAREERS FORM · A (صفحة واحدة) · {state}', script=True))

def form_b(name, state, step=0, vals=None, errs=None, opts=None):
    vals, errs, opts = vals or {}, errs or {}, opts or {}
    names = [s[0] for s in STEPS]
    secs = ''
    for i, (sname, gids) in enumerate(STEPS):
        inner = (summary(errs, step=True) if (errs and i == step) else '') + ''.join(group_html(g, n, vals, errs, opts) for g, n in GROUPS if g in gids)
        prev = '<button type="button" class="btn ds-btn ds-btn--outline" data-nav="prev">السابق</button>' if i else ''
        nxt = '<button type="button" class="btn ds-btn ds-btn--primary" data-nav="next">التالي</button>' if i < len(STEPS) - 1 else '<button type="submit" class="btn ds-btn ds-btn--primary">إرسال الطلب</button>'
        secs += f'<div class="step" data-step="{i + 1}"{"" if i == step else " hidden"}>{inner}<div class="nav">{prev}{nxt}</div></div>'
    bars = ''.join(f'<li{ON if j <= step else ""}></li>' for j in range(len(STEPS)))
    prog = f'<div class="prog"><p class="prog-t" tabindex="-1" aria-live="polite">الخطوة {step + 1} من {len(STEPS)}: {esc(names[step])}</p><ol class="bars" aria-hidden="true">{bars}</ol></div>'
    form = f'<form class="app steps" action="success.html" method="post" novalidate data-steps="{esc(json.dumps(names, ensure_ascii=False))}">{prog}{secs}</form>'
    body = site_header() + f'<main class="wrap">{intro()}{form}</main>' + legend('النموذج B: 4 خطوات، نفس الحقول ونفس الترتيب.')
    write(name, page(body, 'طلب توظيف — نموذج B', f'CAREERS FORM · B (خطوات قصيرة) · {state}', script=True))

def public_simple(name, title, state, inner, lang='ar'):
    body = site_header(lang) + f'<main class="wrap">{inner}</main>' + (legend() if lang == 'ar' else
           '<p class="ds-caption legend">LOW-FI WIREFRAME — layout and behaviour only, no visual identity (brand files missing). Copy blocks are placeholders.</p>')
    write(name, page(body, title, state, lang=lang))

def build_public():
    form_a('form-a.html', 'فارغ (تفاعلي)')
    form_a('form-a-jo.html', 'الجنسية: أردني ← الرقم الوطني', vals={'nat': 'أردني'})
    form_a('form-a-nonjo.html', 'الجنسية: غير أردني ← الجنسية + الوثيقة', vals={'nat': 'غير أردني'})
    errs = {'email': 'أدخل بريدًا إلكترونيًا صحيحًا، مثل name@example.com', 'dob': 'تاريخ الميلاد غير صحيح: شهر شباط لا يحتوي 31 يومًا',
            'area': 'أدخل المنطقة', 'salary': 'أدخل الراتب بالأرقام فقط، مثل 450', 'files': 'أرفق السيرة الذاتية', 'consent': 'يجب الموافقة على الإقرار لإرسال الطلب'}
    v = dict(FILLED, email='applicant1@example', dob=('31', '2', '1998'), area='', salary='أربعمئة', consent=False)
    form_a('form-a-errors.html', 'أخطاء بعد الإرسال (ملخص + أخطاء داخلية)', vals=v, errs=errs)
    form_a('form-a-upload.html', 'رفع 3 ملفات + تعرّف تلقائي على السيرة الذاتية', opts={'files': FILES3})
    unk = [('document-01.pdf', 'PDF · 310 KB', 'ok'), ('scan-02.pdf', 'PDF · 820 KB', 'ok'), ('IMG_1043.jpg', 'JPG · 2.1 MB', 'ok')]
    form_a('form-a-cv-unknown.html', 'لم يُتعرّف على السيرة الذاتية', opts={'files': unk, 'cvpick': [x[0] for x in unk]})
    bad = FILES3[:2] + [('setup.exe', 'EXE · 1.4 MB · نوع غير مسموح', 'bad'), ('فيديو-كبير-تجريبي.mov', 'MOV · أكبر من الحد', 'bad')]
    form_a('form-a-file-errors.html', 'ملف خطر مرفوض + ملف كبير', opts={'files': bad, 'rejected': True})
    net = ('<div class="alert ds-alert ds-alert--danger" role="alert"><h2>تعذّر إرسال الطلب: انقطع الاتصال</h2><p>لم تُفقد بياناتك — ما زالت معبأة في النموذج، والملفات المرفوعة محفوظة. تحقق من الاتصال ثم أعد المحاولة.</p>'
           '<button type="submit" class="btn ds-btn ds-btn--primary">إعادة المحاولة</button></div>')
    form_a('form-a-network.html', 'خطأ شبكة — المدخلات محفوظة', vals=FILLED, opts={'files': [(n, m, 'cv' if s == 'cv' else 'ok') for n, m, s in FILES3]}, banner=net)
    form_b('form-b.html', 'الخطوة 1 (تفاعلي)', 0)
    for i in (1, 2, 3):
        form_b(f'form-b-s{i + 1}.html', f'الخطوة {i + 1}', i, vals=FILLED if i else {}, opts={'files': FILES3} if i == 3 else {})
    form_b('form-b-errors.html', 'أخطاء الخطوة 1', 0, vals=dict(FILLED, gender=None, nid=''),
           errs={'gender': 'اختر الجنس', 'nid': 'أدخل الرقم الوطني'})
    public_simple('success.html', 'تم استلام طلب التوظيف', 'SUCCESS',
                  '<div class="card ds-card center" role="status"><h1 tabindex="-1">تم استلام طلب التوظيف بنجاح</h1>'
                  '<p>رقم الطلب</p><p class="appno" dir="ltr">JOB-2026-00125</p><p><span class="wf-note sample">عيّنة — SAMPLE</span></p>'
                  '<p>احتفظ بهذا الرقم مع رقم هاتفك لمتابعة حالة طلبك.</p><p>سيتواصل فريق SHELTER COFFEE معك عند الحاجة.</p>'
                  '<div class="stack"><a class="btn ds-btn ds-btn--primary" href="index.html">العودة إلى الموقع</a><a class="btn ds-btn ds-btn--outline" href="track.html">متابعة طلب التوظيف</a></div></div>'
                  '<p class="note">لا تظهر أي بيانات شخصية في هذه الصفحة أو في الرابط.</p>')
    def track_form(err=False, vals=('', '')):
        a = '<div class="alert ds-alert ds-alert--danger" role="alert"><h2>تعذر العثور على الطلب</h2><p>تحقق من رقم الطلب ورقم الهاتف ثم حاول مرة أخرى.</p></div>' if err else ''
        v1 = f' value="{vals[0]}"' if vals[0] else ''
        v2 = f' value="{vals[1]}"' if vals[1] else ''
        return (f'<div class="intro"><h1>متابعة طلب التوظيف</h1><p>أدخل رقم الطلب ورقم الهاتف الذي استخدمته في الطلب. لا تحتاج إلى حساب.</p></div>{a}'
                f'<form class="card ds-card" action="track-result.html" method="post"><div class="f"><label for="tno">رقم الطلب{req()}</label><span class="hint" id="h-tno">مثل JOB-2026-00125</span>'
                f'<input type="text" id="tno" name="tno" dir="auto" required aria-describedby="h-tno"{v1}></div>'
                f'<div class="f"><label for="tph">رقم الهاتف{req()}</label><input type="tel" id="tph" name="tph" dir="auto" inputmode="tel" required{v2}></div>'
                '<button type="submit" class="btn ds-btn ds-btn--primary block">عرض حالة الطلب</button></form><p class="note">لحماية بياناتك يُحدَّد عدد المحاولات، ولا تعرض هذه الصفحة سوى حالة الطلب.</p>')
    public_simple('track.html', 'متابعة طلب التوظيف', 'TRACKING', track_form())
    public_simple('track-error.html', 'متابعة طلب التوظيف — تعذر العثور', 'TRACKING · خطأ عام', track_form(True, ('JOB-2026-00999', '0700000009')))
    public_simple('track-result.html', 'حالة طلب التوظيف', 'TRACKING · النتيجة (حالة عامة فقط)',
                  '<div class="intro"><h1>حالة طلب التوظيف</h1></div><div class="card ds-card"><dl class="kv"><div><dt>رقم الطلب</dt><dd dir="ltr">JOB-2026-00125 <span class="wf-note sample">عيّنة</span></dd></div>'
                  '<div><dt>الحالة</dt><dd>قيد المراجعة</dd></div></dl><p class="note">سيتواصل فريق SHELTER COFFEE معك عند الحاجة.</p></div>'
                  '<div class="stack"><a class="btn ds-btn ds-btn--outline" href="track.html">متابعة طلب آخر</a><a class="btn ds-btn ds-btn--outline" href="index.html">العودة إلى الموقع</a></div>'
                  '<p class="note">تعرض الصفحة الحالة العامة فقط (Public Status) — بلا بيانات شخصية أو مرفقات أو ملاحظات أو مواعيد مقابلة.</p>')
    public_simple('en-careers.html', 'Careers — SHELTER COFFEE', 'CAREERS · /en/careers/ (content only)',
                  '<div class="intro"><h1>Careers</h1><span class="wf-note ph">Careers introduction — MISSING — OWNER INPUT REQUIRED</span>'
                  '<p>The job application form is available in Arabic only. You can write your answers in Arabic or English.</p></div>'
                  '<div class="stack"><a class="btn ds-btn ds-btn--primary" href="form-a.html" hreflang="ar">Apply now — Arabic application form</a>'
                  '<a class="btn ds-btn ds-btn--outline" href="track.html" hreflang="ar">Track an application (Arabic)</a></div>', lang='en')

# ---------------------------------------------------------------- dashboard sample data (obviously fake)
ROWS = []
for i in range(1, 21):
    ROWS.append(dict(n=f'متقدم تجريبي {i}', id=f'JOB-2026-{126 - i:05d}', job=JOBS[(i - 1) % 6], city=CITY_SAMPLE[(i - 1) % 3],
                     exp=EXP[(i * 2) % 6], sal=300 + (i * 70) % 600, date=f'2026-09-{30 - (i - 1) // 2:02d}',
                     st=STATUSES[0] if i <= 6 else STATUSES[1 + (i % 5)], unread=i <= 5, prev=i in (1, 9)))
ARCH = [dict(n=f'متقدم تجريبي {30 + i}', id=f'JOB-2026-{87 - i:05d}', job=JOBS[i % 6], at=f'2026-09-{20 - i:02d}', before=['مرفوض', 'مقبول', 'مرفوض', 'تمت المقابلة', 'مرفوض', 'قيد المراجعة'][i]) for i in range(6)]
IVS = [dict(n=f'متقدم تجريبي {7 + i}', job=JOBS[i % 6], d=f'2026-10-{2 + i // 2:02d}', t=['10:00', '12:30'][i % 2], loc=LOCS[i % 2], st='مرشح للمقابلة' if i < 4 else 'تمت المقابلة') for i in range(6)]
NAV = [('overview', 'نظرة عامة'), ('list', 'الطلبات'), ('interviews', 'المقابلات'), ('archived', 'المؤرشفة'), ('saved', 'الفلاتر المحفوظة'), ('settings', 'الإعدادات')]

def href(k, key):
    return f'{k}-list.html#saved' if key == 'saved' else f'{k}-{key}.html'

def dpage(k, name, active, state, main, after='', cls=''):
    cnt = '<span class="count">5 جديد</span>' if k == 'd' else '<span class="count">5<span class="vh"> جديد</span></span>'
    links = ''.join(f'<a href="{href(k, key)}"{CUR if key == active else ""}>{esc(t)}{cnt if key == "list" else ""}</a>' for key, t in NAV)
    if k == 'd':
        top = (f'<header class="dtop"><span class="logo">SHELTER OWNER DASHBOARD</span><span class="dtop-r"><span class="wf-note sample">{SAMPLE}</span>'
               f'<a class="btn ds-btn ds-btn--ghost" href="d-list.html?read=unread">{I_BELL} طلبات جديدة <span class="count">5</span></a><span>المالك (Owner)</span></span></header>')
        body = top + f'<div class="dshell"><nav class="dside" aria-label="أقسام التوظيف"><p class="dside-h">التوظيف</p>{links}</nav><main class="dmain">{main}</main></div>'
    else:
        top = (f'<header class="mtop"><button type="button" class="btn ds-btn ds-btn--ghost ds-btn--icon" aria-label="قائمة الـDashboard">{I_MENU}</button><span class="logo">التوظيف</span>'
               f'<a class="btn ds-btn ds-btn--ghost" href="m-list.html?read=unread" aria-label="5 طلبات جديدة">{I_BELL}<span class="count" aria-hidden="true">5</span></a></header>')
        body = top + f'<nav class="mtabs" aria-label="أقسام التوظيف">{links}</nav><main class="mmain">{main}</main>'
    body += after + legend('الـDashboard للمالك فقط (Owner only) — التحقق من الصلاحية في الخادم، وليس بإخفاء الواجهة.')
    write(name, page(body, f'التوظيف — {state}', f'OWNER DASHBOARD · {"DESKTOP" if k == "d" else "MOBILE"} · {state} · {SAMPLE}', cls=cls))

DLG = {'modal': 'ds-modal', 'sheet': 'ds-sheet', 'drawer': 'drawer'}  # kit dialog containers; the drawer is careers-only

def dialog(kind, did, title, body, foot, close):
    return (f'<div class="ds-overlay" aria-hidden="true"></div><div class="dlg {DLG[kind]}" role="dialog" aria-modal="true" aria-labelledby="{did}">'
            f'<div class="dhead"><h2 id="{did}">{title}</h2><a class="btn ds-btn ds-btn--ghost ds-btn--icon x" href="{close}" aria-label="إغلاق">{I_X}</a></div>'
            f'<div class="dbody">{body}</div>{f"<div class=dfoot>{foot}</div>" if foot else ""}</div>')

def st(s):
    return f'<span class="ds-badge st">{esc(s)}</span>'

def sel(id_, label, options, selected=None, first=None):
    o = (f'<option value="">{esc(first)}</option>' if first else '') + ''.join(opt(x, x == selected) for x in options)
    return f'<div class="f"><label for="{id_}">{esc(label)}</label><select id="{id_}">{o}</select></div>'

def overview(k):
    rng = sel(f'range', 'الفترة', RANGES, 'هذا الشهر')
    cards = ''.join(f'<li><a class="ds-card kpi" href="{k}-list.html?view={key}"><span class="kpi-l">{esc(l)}</span><span class="kpi-n">{n}</span></a></li>' for l, n, key in KPIS)
    new = ''.join(f'<li><span><a href="{k}-application.html">{esc(r["n"])}</a> <span class="ds-badge new">جديد</span></span><span class="sub">{esc(r["job"])} · {r["date"]}</span></li>' for r in ROWS[:3])
    ivs = ''.join(f'<li><span>{esc(x["n"])} — {esc(x["loc"])}</span><span class="sub nowrap">{x["d"]} · {x["t"]}</span></li>' for x in IVS[:3])
    main = (f'<div class="ptitle"><h1>نظرة عامة — التوظيف</h1><span class="wf-note sample">{SAMPLE}</span></div>'
            f'<div class="toolbar">{rng}<p class="sub">بلا مقارنات مع الفترة السابقة.</p></div>'
            f'<h2 class="vh">ملخص الطلبات</h2><ul class="kpis">{cards}</ul>'
            f'<section class="panel ds-card" aria-labelledby="p-new"><h2 id="p-new">تحتاج انتباهك: طلبات جديدة (5)</h2><ul class="rows">{new}</ul><a class="btn ds-btn ds-btn--outline" href="{k}-list.html?read=unread">عرض كل الطلبات الجديدة</a></section>'
            f'<section class="panel ds-card" aria-labelledby="p-iv"><h2 id="p-iv">المقابلات القادمة</h2><ul class="rows">{ivs}</ul><a class="btn ds-btn ds-btn--outline" href="{k}-interviews.html">كل المقابلات</a></section>'
            f'<section class="panel ds-card" aria-labelledby="p-old"><h2 id="p-old">طلبات قديمة بلا تحديث</h2><p>3 طلبات لم تتغير حالتها منذ مدة <span class="wf-note sample">الحد الزمني يُحدد لاحقًا</span>. لا حذف تلقائي.</p><a class="btn ds-btn ds-btn--outline" href="{k}-list.html?view=stale">عرضها</a></section>')
    dpage(k, f'{k}-overview.html', 'overview', 'نظرة عامة', main)

def toolbar(k):
    q = (f'<div class="f grow"><label for="q">بحث سريع</label><input type="search" id="q" dir="auto" aria-describedby="h-q" placeholder="اسم، هاتف، بريد، رقم طلب، وظيفة، مدينة">'
         f'<span class="hint" id="h-q">يبحث أثناء الكتابة</span></div>')
    sort = sel('sort', 'الترتيب', SORTS, SORTS[0])
    fl = f'<a class="btn ds-btn ds-btn--outline" href="{k}-list-filters.html">فلاتر متقدمة <span class="count">0</span></a>'
    if k == 'd':
        dens = ('<fieldset class="f"><legend>الكثافة</legend><div class="seg"><label class="tap"><input type="radio" name="density" checked> مريح</label>'
                '<label class="tap"><input type="radio" name="density"> مضغوط</label></div></fieldset>')
        cols = f'<a class="btn ds-btn ds-btn--outline" href="d-list-columns.html">الأعمدة</a>'
        ex = f'<a class="btn ds-btn ds-btn--outline" href="d-export.html">تصدير</a>'
        return f'<div class="toolbar">{q}{sort}{fl}{cols}{dens}{ex}</div>'
    return f'<div class="toolbar">{q}{sort}{fl}<a class="btn ds-btn ds-btn--outline" href="m-export.html">تصدير</a></div>'

def saved_chips(k):
    return (f'<div class="chips" id="saved"><span><b>الفلاتر المحفوظة:</b></span><a class="ds-chip chip" href="{k}-list.html?saved=1" title="إربد + بكالوريوس + 3–5 سنوات + قيد المراجعة">مرشحين محاسبة إربد</a>'
            f'<a class="ds-chip chip" href="{k}-list.html?saved=2">مرشحون للمقابلة — هذا الشهر</a><span class="wf-note sample">أمثلة</span></div>')

def table(k, rows, checked=False):
    head = '<th scope="col" class="cb"><label class="tap cbx"><input type="checkbox" aria-label="تحديد كل الطلبات في هذه الصفحة"' + (' checked' if checked else '') + '></label></th>'
    head += ''.join(f'<th scope="col">{esc(c)}</th>' for c in COLS) + '<th scope="col"><span class="vh">عرض سريع</span></th>'
    body = ''
    for r in rows:
        badge = ' <span class="ds-badge new">جديد</span>' if r['unread'] else ''
        body += (f'<tr{UNREAD if r["unread"] else ""}><td class="cb"><label class="tap cbx"><input type="checkbox" aria-label="تحديد {esc(r["n"])}"{" checked" if checked else ""}></label></td>'
                 f'<th scope="row"><a class="nm" href="{k}-application.html">{esc(r["n"])}</a>{badge}</th><td dir="auto">{esc(r["job"])}</td><td>{esc(r["city"])}</td><td>{esc(r["exp"])}</td>'
                 f'<td class="nowrap">{r["sal"]} د.أ</td><td class="nowrap">{r["date"]}</td><td>{st(r["st"])}</td>'
                 f'<td><a class="btn ds-btn ds-btn--ghost ds-btn--icon" href="{k}-quickview.html" aria-label="عرض سريع: {esc(r["n"])}">{I_EYE}</a></td></tr>')
    cap = f'الطلبات — الأحدث أولًا · يُعرض 20 من 50 صفًا للاختصار · {SAMPLE}'
    return f'<div class="tbl-wrap"><table class="ds-table tbl"><caption>{cap}</caption><thead><tr>{head}</tr></thead><tbody>{body}</tbody></table></div>'

def cards(k, rows, checked=False):
    out = ''
    for r in rows:
        badge = ' <span class="ds-badge new">جديد</span>' if r['unread'] else ''
        pv = ' <span class="ds-badge prev">متقدم سابق</span>' if r['prev'] else ''
        out += (f'<li class="ds-card acard{" unread" if r["unread"] else ""}"><label class="tap cbx"><input type="checkbox" aria-label="تحديد {esc(r["n"])}"{" checked" if checked else ""}></label>'
                f'<div><p class="acard-n"><a href="{k}-application.html">{esc(r["n"])}</a>{badge}{pv}</p><p dir="auto"><b>{esc(r["job"])}</b></p>'
                f'<p>{esc(r["city"])} · {esc(r["exp"])} · <span class="nowrap">{r["sal"]} د.أ</span></p><p>{st(r["st"])} <span class="sub nowrap">{r["date"]}</span></p></div>'
                f'<a class="btn ds-btn ds-btn--ghost ds-btn--icon" href="{k}-quickview.html" aria-label="عرض سريع: {esc(r["n"])}">{I_EYE}</a></li>')
    return f'<p class="sub">الطلبات — الأحدث أولًا · بطاقات بدل الجدول على الشاشات الصغيرة · {SAMPLE}</p><ul class="acards">{out}</ul>'

def pager(k):
    ps = sel('ps', 'عدد الصفوف في الصفحة', ['25', '50', '100'], '50')
    if k == 'd':
        nums = '<a class="btn ds-btn ds-btn--primary ds-btn--icon pg" href="#" aria-current="page">1</a>' + ''.join(f'<a class="btn ds-btn ds-btn--outline ds-btn--icon pg" href="#" aria-label="صفحة {i}">{i}</a>' for i in (2, 3)) + '<span class="sep" aria-hidden="true">…</span><a class="btn ds-btn ds-btn--outline ds-btn--icon pg" href="#" aria-label="صفحة 9">9</a>'
        return (f'<nav class="pager" aria-label="ترقيم الصفحات"><span class="btn ds-btn ds-btn--outline" aria-disabled="true">السابق</span>{nums}<a class="btn ds-btn ds-btn--outline" href="#">التالي</a>'
                f'<span class="sub">عرض 1–50 من 412</span><div class="jump"><label for="jump">انتقل إلى صفحة</label><input type="text" id="jump" inputmode="numeric"><button type="button" class="btn ds-btn ds-btn--outline">انتقال</button></div></nav>'
                f'<div class="toolbar">{ps}</div>')
    return (f'<nav class="pager" aria-label="ترقيم الصفحات"><span class="btn ds-btn ds-btn--outline" aria-disabled="true">السابق</span><span class="sub">صفحة 1 من 9</span><a class="btn ds-btn ds-btn--outline" href="#">التالي</a>'
            f'<div class="jump"><label for="jump">انتقل إلى صفحة</label><input type="text" id="jump" inputmode="numeric"><button type="button" class="btn ds-btn ds-btn--outline">انتقال</button></div></nav>'
            f'<div class="toolbar">{ps}</div>')

def list_main(k, bulk=False, compact=False):
    head = f'<div class="ptitle"><h1>الطلبات</h1><span class="sub">412 طلبًا · <span class="wf-note sample">{SAMPLE}</span></span></div>'
    bar = ''
    if bulk and k == 'd':
        bar = (f'<div class="bulk" role="region" aria-label="إجراءات جماعية"><span class="bulk-n">تم تحديد 20 طلبًا</span>{sel("bst", "تغيير الحالة إلى", STATUSES[:6], "قيد المراجعة")}'
               f'<a class="btn ds-btn ds-btn--primary" href="d-bulk-confirm.html">تطبيق</a><a class="btn ds-btn ds-btn--outline" href="d-bulk-confirm.html">أرشفة</a><a class="btn ds-btn ds-btn--outline" href="d-export.html">تصدير</a>'
               f'<button type="button" class="btn ds-btn ds-btn--outline">تنزيل المرفقات (ZIP)</button><button type="button" class="btn ds-btn ds-btn--ghost">إلغاء التحديد</button></div>'
               '<p class="sub">لا يوجد حذف نهائي جماعي. الحذف النهائي من «المؤرشفة» لكل طلب على حدة.</p>')
    content = table(k, ROWS, bulk) if k == 'd' else cards(k, ROWS, bulk)
    return head + toolbar(k) + saved_chips(k) + bar + content + pager(k)

def mbar():
    return ('<div class="mbar" role="region" aria-label="إجراءات جماعية"><p class="bulk-n">تم تحديد 20 طلبًا · لا حذف جماعي</p>'
            '<a class="btn ds-btn ds-btn--primary" href="m-bulk-confirm.html">تغيير الحالة</a><a class="btn ds-btn ds-btn--outline" href="m-bulk-confirm.html">أرشفة</a><a class="btn ds-btn ds-btn--outline" href="m-export.html">تصدير</a>'
            '<button type="button" class="btn ds-btn ds-btn--outline">تنزيل المرفقات</button></div>')

def filters_body():
    chk = lambda name, opts, on=(): '<div class="checks">' + ''.join(f'<label class="tap"><input type="checkbox" name="{name}"{" checked" if o in on else ""}> {esc(o)}</label>' for o in opts) + '</div>'
    b = (f'<p class="sub">يمكن الجمع بين عدة فلاتر. <span class="wf-note sample">مثال المالك: إربد + بكالوريوس + 3–5 سنوات + قيد المراجعة</span></p>'
         f'<div class="f"><label for="fl-name">الاسم</label><input type="text" id="fl-name" dir="auto"></div>'
         f'<div class="f"><label for="fl-phone">رقم الهاتف</label><input type="tel" id="fl-phone" dir="auto"></div>'
         f'<div class="f"><label for="fl-job">الوظيفة</label><input type="text" id="fl-job" dir="auto" value="محاسب"></div>'
         + sel('fl-city', 'المدينة', CITY_SAMPLE, CITY_SAMPLE[0], 'كل المدن') +
         f'<fieldset class="f"><legend>المؤهل العلمي</legend>{chk("fl-edu", EDU, ("بكالوريوس",))}</fieldset>'
         f'<fieldset class="f"><legend>سنوات الخبرة</legend>{chk("fl-exp", EXP, ("3–5 سنوات",))}</fieldset>'
         f'<fieldset class="f"><legend>الحالة</legend>{chk("fl-st", STATUSES, ("قيد المراجعة",))}</fieldset>'
         f'<fieldset class="f"><legend>الجنس</legend>{chk("fl-g", GENDER)}</fieldset>'
         f'<fieldset class="f"><legend>الجنسية</legend>{chk("fl-nat", NAT)}</fieldset>'
         f'<fieldset class="f"><legend>تاريخ التقديم</legend><div class="two"><div><label for="fl-from">من</label><input type="date" id="fl-from"></div><div><label for="fl-to">إلى</label><input type="date" id="fl-to"></div></div></fieldset>'
         f'<fieldset class="f panel ds-card"><legend>حفظ كفلتر محفوظ</legend><label for="fl-save">اسم الفلتر</label><input type="text" id="fl-save" dir="auto" value="مرشحين محاسبة إربد"><p class="sub">يظهر في «الفلاتر المحفوظة» ويُفتح بضغطة.</p><button type="button" class="btn ds-btn ds-btn--outline">حفظ الفلتر</button></fieldset>')
    foot = '<a class="btn ds-btn ds-btn--primary" href="#">عرض النتائج (12)</a><button type="button" class="btn ds-btn ds-btn--outline">مسح الكل</button>'
    return b, foot

def quick_body(k):
    r = ROWS[0]
    files = ''.join(f'<li><span dir="auto">{esc(n)}</span> {CVB if s == "cv" else ""}<a class="btn ds-btn ds-btn--ghost" href="#">عرض</a></li>' for n, m, s in FILES3)
    b = (f'<p class="sub">{r["id"]} · {SAMPLE} · فُتح الآن: صار «مقروءًا» دون تغيير حالة الطلب.</p>'
         f'<dl class="kv"><div><dt>الاسم</dt><dd>{esc(r["n"])}</dd></div><div><dt>الوظيفة</dt><dd>{esc(r["job"])}</dd></div><div><dt>المدينة</dt><dd>{esc(r["city"])}</dd></div>'
         f'<div><dt>سنوات الخبرة</dt><dd>{esc(r["exp"])}</dd></div><div><dt>الراتب المتوقع</dt><dd>{r["sal"]} د.أ</dd></div><div><dt>الحالة</dt><dd>{st(r["st"])}</dd></div>'
         f'<div><dt>الهاتف</dt><dd dir="ltr">0700000001</dd></div><div><dt>البريد</dt><dd dir="ltr">applicant1@example.com</dd></div></dl>'
         f'<h3>المرفقات (3)</h3><ul class="rows">{files}</ul>'
         f'<div class="inline">{sel("qv-st", "تغيير الحالة إلى", STATUSES, r["st"])}<button type="button" class="btn ds-btn ds-btn--primary">حفظ الحالة</button></div>')
    foot = f'<a class="btn ds-btn ds-btn--primary" href="{k}-application.html">فتح الطلب الكامل</a>'
    return b, foot

def app_main(k):
    r = ROWS[0]
    kv = lambda pairs: '<dl class="kv">' + ''.join(f'<div><dt>{esc(a)}</dt><dd>{b}</dd></div>' for a, b in pairs) + '</dl>'
    sec = lambda sid, t, inner: f'<section class="panel ds-card" id="{sid}" aria-labelledby="{sid}-h"><h2 id="{sid}-h">{t}</h2>{inner}</section>'
    files = ''.join(f'<li><span><span dir="auto">{esc(n)}</span> <span class="sub">{m}</span> {CVB2 if s == "cv" else ""}</span><span class="inline"><a class="btn ds-btn ds-btn--ghost" href="#">عرض</a><a class="btn ds-btn ds-btn--ghost" href="#">تنزيل</a></span></li>' for n, m, s in FILES3)
    notes = ''.join(f'<li class="ds-card"><p>{esc(t)}</p><p class="by">{esc(a)} · {d}</p></li>' for t, a, d in [
        ('ملاحظة تجريبية 3: مرشح مناسب لمقابلة أولى.', 'المالك (Owner)', '2026-10-01 09:10'),
        ('ملاحظة تجريبية 2: تم الاتصال، طلب موعدًا صباحيًا.', 'المالك (Owner)', '2026-09-30 16:45'),
        ('ملاحظة تجريبية 1: السيرة الذاتية واضحة.', 'المالك (Owner)', '2026-09-30 11:20')])
    hist = ''.join(f'<li><span>{a} ← <b>{b}</b></span><span class="sub">{c} · {d}{" · " + e if e else ""}</span></li>' for a, b, c, d, e in [
        ('—', 'تم الاستلام', 'النظام', '2026-09-30 10:42', ''), ('تم الاستلام', 'قيد المراجعة', 'المالك (Owner)', '2026-10-01 09:12', 'ملاحظة داخلية اختيارية')])
    prev = ''.join(f'<li><span><a href="#">{a}</a> · {b}</span><span class="sub">{c} · {st(d)}</span></li>' for a, b, c, d in [
        ('JOB-2026-00031', 'كاشير', '2026-03-12', 'مرفوض'), ('JOB-2025-00410', 'باريستا', '2025-11-02', 'مؤرشف')])
    audit = ''.join(f'<li><span>{a}</span><span class="sub">{b} · {c}</span></li>' for a, b, c in [
        ('إنشاء الطلب', 'النظام', '2026-09-30 10:42'), ('أول فتح للطلب (تعليم كمقروء)', 'المالك (Owner)', '2026-09-30 11:05'),
        ('تغيير الحالة: تم الاستلام ← قيد المراجعة', 'المالك (Owner)', '2026-10-01 09:12'), ('إضافة ملاحظة', 'المالك (Owner)', '2026-10-01 09:10')])
    head = (f'<div class="ptitle"><h1>{esc(r["n"])}</h1><span class="inline"><span class="ds-badge prev">متقدم سابق — لديه 3 طلبات</span><span class="wf-note sample">{SAMPLE}</span></span></div>'
            f'<p class="sub">رقم الطلب <b dir="ltr">{r["id"]}</b> · تاريخ التقديم 2026-09-30 10:42 · آخر تحديث 2026-10-01 09:12 · المشاهدة: فُتح لأول مرة 2026-09-30 11:05</p>'
            f'<section class="panel ds-card" aria-labelledby="stbox-h"><h2 id="stbox-h">الحالة الحالية: قيد المراجعة</h2><div class="toolbar">{sel(f"app-st", "تغيير الحالة إلى", STATUSES, "قيد المراجعة")}'
            f'<div class="f grow"><label for="app-stn">ملاحظة داخلية (اختيارية)</label><input type="text" id="app-stn" dir="auto"></div><button type="button" class="btn ds-btn ds-btn--primary">حفظ الحالة</button></div>'
            f'<div class="inline"><a class="btn ds-btn ds-btn--outline" href="{k}-archived.html">أرشفة</a><button type="button" class="btn ds-btn ds-btn--outline">تنزيل المرفقات (ZIP)</button><a class="btn ds-btn ds-btn--ghost" href="{k}-list.html">العودة إلى القائمة</a></div></section>')
    secs = [
        ('s-sum', 'الملخص', kv([('الوظيفة المتقدم لها', 'باريستا'), ('الراتب المتوقع', '450 د.أ'), ('المدينة', CITY_SAMPLE[0]), ('سنوات الخبرة', '3–5 سنوات'), ('الحالة', st('قيد المراجعة'))])),
        ('s-per', 'البيانات الشخصية', kv([('الاسم الكامل', esc(r['n'])), ('رقم الهاتف', '<span dir="ltr">0700000001</span>'), ('البريد الإلكتروني', '<span dir="ltr">applicant1@example.com</span>'),
                                       ('الجنس', 'ذكر'), ('تاريخ الميلاد', '1998-04-12 · العمر 28 (محسوب)'), ('الحالة الاجتماعية', 'أعزب'), ('الجنسية', 'أردني'),
                                       ('الرقم الوطني', '<span class="inline"><span class="masked" dir="ltr">********1234</span><button type="button" class="btn ds-btn ds-btn--ghost">إظهار</button><span class="sub">للمالك فقط · يُسجَّل في سجل التدقيق</span></span>')])),
        ('s-home', 'السكن', kv([('المدينة', CITY_SAMPLE[0]), ('المنطقة', 'منطقة تجريبية')])),
        ('s-edu', 'المؤهل والخبرة', kv([('المؤهل العلمي', 'بكالوريوس'), ('سنوات الخبرة', '3–5 سنوات'), ('خبرة سابقة في نفس المجال', 'نعم'), ('يعمل حاليًا', 'لا')])),
        ('s-work', 'معلومات العمل', kv([('الوظيفة المتقدم لها', 'باريستا'), ('الراتب المتوقع', '450 د.أ'), ('رخصة قيادة', 'نعم'), ('ملاحظات المتقدم', 'نص تجريبي للملاحظات الإضافية.')])),
        ('s-files', 'المرفقات', f'<ul class="rows">{files}</ul>'),
        ('s-consent', 'الإقرار والموافقة', kv([('الموافقة', 'تمت الموافقة'), ('الوقت', '2026-09-30 10:42'), ('نسخة النص', 'v1')]) + f'<p class="sub">{esc(CONSENT)}</p>'),
        ('s-notes', 'الملاحظات الداخلية (3)', f'<ul class="notes">{notes}</ul><div class="f"><label for="note-new">ملاحظة جديدة</label><textarea id="note-new" dir="auto"></textarea></div><button type="button" class="btn ds-btn ds-btn--primary">إضافة ملاحظة</button><p class="sub">كل ملاحظة مستقلة ولا تُستبدل؛ أي تعديل أو حذف يُسجَّل.</p>'),
        ('s-iv', 'المقابلة', '<div class="two"><div class="f"><label for="iv-d">تاريخ المقابلة</label><input type="date" id="iv-d" value="2026-10-03"></div><div class="f"><label for="iv-t">وقت المقابلة</label><input type="time" id="iv-t" value="10:00"></div></div>'
            + sel('iv-loc', 'مكان المقابلة', LOCS, LOCS[0], 'اختر المكان') + '<div class="f"><label for="iv-n">ملاحظات داخلية</label><textarea id="iv-n" dir="auto"></textarea></div><button type="button" class="btn ds-btn ds-btn--primary">حفظ المقابلة</button>'
            '<p class="sub">لا تقييم ولا نجوم. موعد المقابلة ومكانها لا يظهران في صفحة المتابعة.</p>'),
        ('s-hist', 'سجل الحالات', f'<ul class="rows">{hist}</ul>'),
        ('s-prev', 'الطلبات السابقة', f'<p>متقدم سابق — لديه 3 طلبات (هذا الطلب + 2). التطابق: رقم الهاتف · البريد الإلكتروني. لا دمج تلقائي.</p><ul class="rows">{prev}</ul>'),
        ('s-audit', 'سجل التدقيق', f'<ul class="rows">{audit}</ul>'),
    ]
    toc = '<nav class="toc" aria-label="أقسام الطلب">' + ''.join(f'<a href="#{a}">{t}</a>' for a, t, _ in secs) + '</nav>'
    return head + f'<div class="appgrid">{toc}<div class="appmain">' + ''.join(sec(*s) for s in secs) + '</div></div>'

def simple_table(caption, cols, rows):
    h = ''.join(f'<th scope="col">{c}</th>' for c in cols)
    b = ''.join('<tr>' + ''.join((f'<th scope="row">{c}</th>' if i == 0 else f'<td>{c}</td>') for i, c in enumerate(r)) + '</tr>' for r in rows)
    return f'<div class="tbl-wrap"><table class="ds-table tbl"><caption>{caption}</caption><thead><tr>{h}</tr></thead><tbody>{b}</tbody></table></div>'

def interviews_main(k):
    f = f'<div class="toolbar">{sel("iv-f", "المكان", LOCS, None, "كل الأماكن")}{sel("iv-r", "الفترة", RANGES, "هذا الشهر")}</div>'
    if k == 'd':
        body = simple_table(f'المقابلات القادمة · {SAMPLE}', ['التاريخ', 'الوقت', 'المكان', 'المتقدم', 'الوظيفة', 'الحالة'],
                            [[x['d'], x['t'], x['loc'], f'<a class="nm" href="d-application.html">{esc(x["n"])}</a>', esc(x['job']), st(x['st'])] for x in IVS])
    else:
        body = '<ul class="acards">' + ''.join(f'<li class="ds-card acard solo"><div><p class="acard-n"><a href="m-application.html">{esc(x["n"])}</a></p><p>{x["d"]} · {x["t"]}</p><p>{esc(x["loc"])}</p><p dir="auto">{esc(x["job"])} · {st(x["st"])}</p></div></li>' for x in IVS) + '</ul>'
    return f'<div class="ptitle"><h1>المقابلات</h1><span class="wf-note sample">{SAMPLE}</span></div>{f}<p class="sub">لا تقييم للمقابلات — ملاحظات داخلية وحالة فقط.</p>{body}'

def archived_main(k):
    act = lambda r: f'<span class="inline"><button type="button" class="btn ds-btn ds-btn--outline">استعادة</button><a class="btn ds-btn ds-btn--danger" href="{k}-delete-confirm.html">حذف نهائي</a></span>'
    if k == 'd':
        body = simple_table(f'الطلبات المؤرشفة · {SAMPLE}', ['الاسم', 'رقم الطلب', 'الوظيفة', 'تاريخ الأرشفة', 'الحالة قبل الأرشفة', 'إجراءات'],
                            [[esc(r['n']), f'<span dir="ltr">{r["id"]}</span>', esc(r['job']), r['at'], st(r['before']), act(r)] for r in ARCH])
    else:
        body = '<ul class="acards">' + ''.join(f'<li class="ds-card acard solo"><div><p class="acard-n"><b>{esc(r["n"])}</b></p><p><span dir="ltr">{r["id"]}</span></p><p dir="auto">{esc(r["job"])} · أُرشف {r["at"]}</p><p>قبل الأرشفة: {st(r["before"])}</p>{act(r)}</div></li>' for r in ARCH) + '</ul>'
    return (f'<div class="ptitle"><h1>الطلبات المؤرشفة</h1><span class="wf-note sample">{SAMPLE}</span></div>'
            '<p class="sub">المسار: نشط ← مؤرشف ← حذف نهائي. الحذف النهائي من هنا فقط، للمالك فقط، ولكل طلب على حدة. لا حذف تلقائي.</p>' + body)

def settings_main(k):
    locs = ''.join(f'<li><span><b>{x}</b> · مفعّل</span><span class="inline"><button type="button" class="btn ds-btn ds-btn--ghost">تعديل</button><button type="button" class="btn ds-btn ds-btn--ghost">تعطيل</button></span></li>' for x in LOCS)
    return (f'<div class="ptitle"><h1>إعدادات التوظيف</h1></div>'
            f'<section class="panel ds-card" aria-labelledby="set-loc"><h2 id="set-loc">أماكن المقابلة</h2><ul class="rows">{locs}</ul><div class="toolbar"><div class="f grow"><label for="loc-new">مكان جديد</label><input type="text" id="loc-new" dir="auto"></div><button type="button" class="btn ds-btn ds-btn--outline">إضافة</button></div>'
            '<p class="sub">القائمة فقط — لا نص حر في نموذج المقابلة.</p></section>'
            '<section class="panel ds-card" aria-labelledby="set-city"><h2 id="set-city">قائمة المدن</h2><dl class="kv"><div><dt>الحالة</dt><dd>PENDING DATA VERIFICATION</dd></div><div><dt>عدد المدن المعتمدة</dt><dd>—</dd></div>'
            '<div><dt>المصدر</dt><dd>لم يُعتمد بعد — مصدر أردني موثوق يُخزَّن داخليًا</dd></div></dl><p class="sub">النموذج يعرض عيّنة مؤقتة حتى التحقق. «المدينة» لا تُستبدل بـ«المحافظة».</p>'
            '<button type="button" class="btn ds-btn ds-btn--outline" disabled>استيراد قائمة موثّقة</button></section>'
            f'<section class="panel ds-card" aria-labelledby="set-cons"><h2 id="set-cons">نسخ نص الإقرار</h2><ul class="rows"><li><span><b>v1</b> · نشطة · <span class="wf-note sample">تاريخ تجريبي 2026-10-01</span></span><span class="sub">{esc(CONSENT)}</span></li></ul>'
            '<button type="button" class="btn ds-btn ds-btn--outline">إنشاء نسخة جديدة</button><p class="sub">النسخ السابقة تبقى محفوظة ومربوطة بالطلبات التي وافقت عليها.</p></section>')

def build_dashboard():
    for k in ('d', 'm'):
        overview(k)
        dpage(k, f'{k}-list.html', 'list', 'قائمة الطلبات', list_main(k))
        fb, ff = filters_body()
        dpage(k, f'{k}-list-filters.html', 'list', 'فلاتر متقدمة', list_main(k), dialog('drawer', 'flt-h', 'فلاتر متقدمة', fb, ff, f'{k}-list.html'))
        if k == 'd':
            dpage(k, 'd-list-bulk.html', 'list', 'تحديد جماعي (مضغوط)', list_main(k, bulk=True), cls='compact')
            rows = ''.join(f'<li class="colrow"><button type="button" class="btn ds-btn ds-btn--ghost ds-btn--icon" aria-label="اسحب لإعادة ترتيب «{c}»">{I_DRAG}</button>'
                           f'<label class="tap"><input type="checkbox"{" checked" if c in COLS else ""}> {c}</label>'
                           f'<button type="button" class="btn ds-btn ds-btn--ghost ds-btn--icon" aria-label="نقل «{c}» للأعلى">{I_U}</button><button type="button" class="btn ds-btn ds-btn--ghost ds-btn--icon" aria-label="نقل «{c}» للأسفل">{I_D}</button></li>' for c in COLS + COLS_OPT)
            body = f'<p class="sub">إظهار / إخفاء · سحب وإفلات لإعادة الترتيب (أو الأسهم بالكيبورد). يُحفظ التفضيل للمالك.</p><ul class="cols">{rows}</ul>'
            dpage(k, 'd-list-columns.html', 'list', 'تخصيص الأعمدة', list_main(k),
                  dialog('modal', 'col-h', 'تخصيص الأعمدة', body, '<button type="button" class="btn ds-btn ds-btn--primary">حفظ</button><button type="button" class="btn ds-btn ds-btn--outline">استعادة الافتراضي</button>', 'd-list.html'))
        else:
            dpage(k, 'm-list-bulk.html', 'list', 'تحديد جماعي', list_main(k, bulk=True), mbar(), cls='has-mbar')
        cb = ('<p><b>أنت على وشك تغيير حالة 20 طلبًا إلى قيد المراجعة.</b></p><div class="f"><label for="bc-n">ملاحظة داخلية (اختيارية)</label><textarea id="bc-n" dir="auto"></textarea></div>'
              '<p class="sub">تأكيد واحد لكل العملية، وتُسجَّل في سجل التدقيق.</p>')
        dpage(k, f'{k}-bulk-confirm.html', 'list', 'تأكيد التغيير الجماعي', list_main(k, bulk=True),
              dialog('modal', 'bc-h', 'تأكيد تغيير الحالة', cb, '<button type="button" class="btn ds-btn ds-btn--primary">تأكيد</button>' + f'<a class="btn ds-btn ds-btn--outline" href="{k}-list-bulk.html">إلغاء</a>', f'{k}-list-bulk.html'),
              cls='compact' if k == 'd' else '')
        qb, qf = quick_body(k)
        dpage(k, f'{k}-quickview.html', 'list', 'عرض سريع', list_main(k), dialog('drawer' if k == 'd' else 'sheet', 'qv-h', esc(ROWS[0]['n']), qb, qf, f'{k}-list.html'))
        dpage(k, f'{k}-application.html', 'list', 'الطلب الكامل', app_main(k))
        dpage(k, f'{k}-interviews.html', 'interviews', 'المقابلات', interviews_main(k))
        dpage(k, f'{k}-archived.html', 'archived', 'المؤرشفة', archived_main(k))
        r = ARCH[0]
        delb = (f'<p><b>سيتم حذف الطلب <span dir="ltr">{r["id"]}</span> ({esc(r["n"])}) نهائيًا، بما في ذلك:</b></p>'
                '<ul><li>بيانات الطلب</li><li>بيانات الهوية الحساسة</li><li>المرفقات</li><li>الملاحظات الداخلية</li><li>بيانات المقابلة</li></ul>'
                '<p class="warn ds-alert ds-alert--warning">لا يمكن التراجع عن هذا الإجراء. يبقى في سجل التدقيق سجل مختصر بأن الحذف تم (Tombstone) دون أي بيانات شخصية.</p>'
                f'<div class="f"><label for="del-id">للتأكيد اكتب رقم الطلب: <span dir="ltr">{r["id"]}</span></label><input type="text" id="del-id" dir="auto" autocomplete="off"></div>'
                '<label class="tap"><input type="checkbox" id="del-ok"> أفهم أن هذا الحذف نهائي ولا يمكن استرجاعه</label>')
        dpage(k, f'{k}-delete-confirm.html', 'archived', 'تأكيد الحذف النهائي', archived_main(k),
              dialog('modal', 'del-h', 'حذف نهائي — للمالك فقط', delb, '<button type="button" class="btn ds-btn ds-btn--danger" disabled>حذف نهائي</button>' + f'<a class="btn ds-btn ds-btn--outline" href="{k}-archived.html">إلغاء</a>', f'{k}-archived.html'))
        radios = lambda name, opts, on: '<div class="opts">' + ''.join(f'<label class="tap"><input type="radio" name="{name}"{" checked" if o == on else ""}> {o}</label>' for o in opts) + '</div>'
        exb = (f'<fieldset class="f"><legend>صيغة الملف</legend>{radios("ex-f", ["Excel", "CSV", "PDF"], "Excel")}</fieldset>'
               f'<fieldset class="f"><legend>النطاق</legend>{radios("ex-s", ["كل الطلبات", "النتائج المفلترة (48)", "الطلبات المحددة (20)"], "النتائج المفلترة (48)")}</fieldset>'
               f'<fieldset class="f"><legend>تضييق إضافي (اختياري)</legend>{sel("ex-st", "الحالة", STATUSES, None, "كل الحالات")}'
               '<div class="two"><div class="f"><label for="ex-from">من تاريخ</label><input type="date" id="ex-from"></div><div class="f"><label for="ex-to">إلى تاريخ</label><input type="date" id="ex-to"></div></div>'
               '<div class="f"><label for="ex-job">الوظيفة</label><input type="text" id="ex-job" dir="auto"></div></fieldset>'
               '<fieldset class="f"><legend>رقم الهوية</legend><p class="sub">مقنّع افتراضيًا: <span class="masked" dir="ltr">********1234</span></p>'
               '<label class="tap"><input type="checkbox" id="ex-unmask"> إظهار أرقام الهوية كاملة في هذا التصدير</label>'
               '<p class="warn ds-alert ds-alert--warning">تحذير: هذا يكشف بيانات حساسة. سيُطلب تأكيد هويتك قبل التصدير، ويُسجَّل التصدير في سجل التدقيق.</p></fieldset>'
               '<p class="sub">المرفقات لا تُضمَّن في ملفات التصدير — استخدم «تنزيل المرفقات» (ZIP) للطلبات المحددة.</p>')
        dpage(k, f'{k}-export.html', 'list', 'تصدير', list_main(k),
              dialog('modal' if k == 'd' else 'sheet', 'ex-h', 'تصدير الطلبات', exb, '<button type="button" class="btn ds-btn ds-btn--primary">تصدير</button>' + f'<a class="btn ds-btn ds-btn--outline" href="{k}-list.html">إلغاء</a>', f'{k}-list.html'))
        dpage(k, f'{k}-settings.html', 'settings', 'الإعدادات', settings_main(k))

INDEX = [
    ('النموذج العام — A: صفحة واحدة', ['form-a', 'form-a-errors', 'form-a-jo', 'form-a-nonjo', 'form-a-upload', 'form-a-cv-unknown', 'form-a-file-errors', 'form-a-network']),
    ('النموذج العام — B: خطوات قصيرة', ['form-b', 'form-b-s2', 'form-b-s3', 'form-b-s4', 'form-b-errors']),
    ('بعد الإرسال والمتابعة', ['success', 'track', 'track-error', 'track-result', 'en-careers']),
    ('Owner Dashboard — Desktop (≥ 1024px)', [f'd-{x}' for x in ['overview', 'list', 'list-filters', 'list-columns', 'list-bulk', 'bulk-confirm', 'quickview', 'application', 'interviews', 'archived', 'delete-confirm', 'export', 'settings']]),
    ('Owner Dashboard — Mobile (< 1024px)', [f'm-{x}' for x in ['overview', 'list', 'list-filters', 'list-bulk', 'bulk-confirm', 'quickview', 'application', 'interviews', 'archived', 'delete-confirm', 'export', 'settings']]),
]

def build_index():
    body = '<main class="wrap"><div class="intro"><h1>Careers &amp; Recruitment — فهرس الـWireframes</h1><p>LOW-FI (M28 · Phase 6 + 7). بلا هوية بصرية. كل البيانات تجريبية.</p></div>'
    for t, items in INDEX:
        body += f'<section class="panel ds-card"><h2>{t}</h2><ul class="rows">' + ''.join(f'<li><a href="{x}.html">{x}.html</a></li>' for x in items) + '</ul></section>'
    write('index.html', page(site_header() + body + '</main>', 'فهرس الـWireframes — التوظيف', 'INDEX'))

if __name__ == '__main__':
    if os.path.isdir(OUT):
        shutil.rmtree(OUT)
    os.makedirs(OUT)
    build_public(); build_dashboard(); build_index()
    print(len(os.listdir(OUT)), 'pages →', OUT)
