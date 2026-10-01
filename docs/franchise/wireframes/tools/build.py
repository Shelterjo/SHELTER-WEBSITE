"""SHELTER COFFEE — Franchise / Partnership LOW-FI wireframes (M29 deliverables 8–11 + application form A/B + Owner Dashboard module).
Deterministic: run `python3 build.py` from anywhere → ../html/.

Rules baked in (do not relax without an owner decision):
- ONE design system (M34, FROZEN P0): every page inlines design-system/build/tokens.css + design-system/wireframe-kit.css (read at
  build time, never copied by hand) and uses the kit classes (.ds-*). The page layer (PAGE_CSS) is layout only and uses var(--…)
  tokens exclusively — no raw px font sizes / radii / spacing, no hex or rgb colours. Primitives the kit does not have yet are
  built here from tokens and listed in ../README.md (accordion header, disclosure, side drawer, timeline, sticky CTA bar).
- Low-fi: no brand values (identity files missing — M-10). No images: every visual is "MEDIA PENDING OWNER APPROVAL".
- Approved facts only (M29 §03): SHELTER COFFEE / شلتر كوفي · founded 2019 · anniversary 20/04 · Irbid, Jordan ·
  SHELTER COFFEE DRIVE · SHELTER COFFEE HOUSE. Everything unverified keeps the exact status marker used in docs/franchise/02–04.
- No financial terms, no market/territory availability labels, no response-time promise, no superiority claims.
- Page IA = docs/franchise/02 §1. Form fields = docs/franchise/03. Dashboard module = docs/franchise/04 (owner only in V1 — M30).
- The scripts are prototype helpers (sticky CTA, accordion, client checks, step navigation) so behaviour can be tested and the
  A/B measured. They are NOT production code."""
import html, json, os, re, shutil

ROOT = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(ROOT, '..', 'html'))
DS = os.path.normpath(os.path.join(ROOT, '..', '..', '..', '..', 'design-system'))
esc = html.escape

def tr(L, ar, en):
    return ar if L == 'ar' else en

AR = re.compile('[\u0600-\u06FF]')
LAT = re.compile(r"[A-Za-z(\[][A-Za-z0-9&.'’/:()\[\]\-–— ]*[A-Za-z0-9)\]]")

def bd(s):
    """Escape; in Arabic text wrap each Latin run (codes like "(PF-07)", brand names) in <bdi> so it never breaks or flips."""
    if not AR.search(s):
        return esc(s)
    out, pos = [], 0
    for m in LAT.finditer(s):
        out += [esc(s[pos:m.start()]), f'<bdi>{esc(m.group(0))}</bdi>']
        pos = m.end()
    return ''.join(out) + esc(s[pos:])

def read(p):
    with open(p, encoding='utf-8') as fh:
        return fh.read()

# ---------------------------------------------------------------- status markers (exact wording from docs/franchise/02–04 and M29)
PV = 'PENDING VERIFICATION — Franchise Master'
MEDIA = 'MEDIA PENDING OWNER APPROVAL'
LEGAL = 'PENDING LEGAL REVIEW'
SAMPLE = {'ar': 'عيّنة — SAMPLE', 'en': 'SAMPLE'}
DSAMPLE = 'بيانات تجريبية — SAMPLE'
APPNO = 'FR-2026-00125'  # M29 §34 example — always shown next to a SAMPLE label

# ---------------------------------------------------------------- page copy (docs/franchise/02 §2–§5 · direction drafts, not final copy)
H1 = {'ar': 'كن شريكًا في نمو SHELTER COFFEE', 'en': 'Grow with SHELTER COFFEE'}          # option A (owner-preferred direction)
H1_B = {'ar': 'كن شريكًا مع SHELTER COFFEE', 'en': 'Partner with SHELTER COFFEE'}           # option B — post-launch test only
LEAD = {'ar': 'نبحث عن شركاء يشاركوننا الاهتمام بالجودة والتجربة والنمو، لبناء حضور SHELTER في أسواق جديدة وفق معايير العلامة.',
        'en': "We're looking for partners who share our care for quality, experience and growth, to build SHELTER's presence in new markets to the brand's standards."}
CTA1 = {'ar': 'ابدأ طلب الشراكة', 'en': 'Start your partnership application'}
CTA2 = {'ar': 'تعرّف على SHELTER', 'en': 'Discover SHELTER'}
CTA2_B = {'ar': 'اكتشف SHELTER', 'en': 'Discover SHELTER'}
CTA3 = {'ar': 'اكتشف تجربتنا', 'en': 'Explore our experiences'}

FACTS = {'ar': [('تأسست', '2019'), ('انطلقت من', 'إربد، الأردن'), ('تجربتان', 'SHELTER COFFEE DRIVE · SHELTER COFFEE HOUSE'), ('المجال', 'القهوة وتجربة المقاهي')],
         'en': [('Founded', '2019'), ('Born in', 'Irbid, Jordan'), ('Two experiences', 'SHELTER COFFEE DRIVE · SHELTER COFFEE HOUSE'), ('What we do', 'Coffee and the café experience')]}
WHY = {'ar': [('العلامة والتجربة', ['تجربة العلامة والعميل', 'منظومة القهوة والمنيو', 'تصميم المكان والتجربة']),
              ('التشغيل والجودة', ['معايير التشغيل', 'الجودة والاتساق', 'التقنية والأنظمة']),
              ('الدعم والنمو', ['التدريب', 'التسويق ودعم العلامة', 'التوريد والمشتريات'])],
       'en': [('Brand & experience', ['Brand & customer experience', 'Coffee & menu system', 'Store design & experience']),
              ('Operations & quality', ['Operational standards', 'Quality & consistency', 'Technology & systems']),
              ('Support & growth', ['Training', 'Marketing & brand support', 'Supply & procurement'])]}
SYSTEM = {'ar': ['معايير العلامة', 'تجربة المكان', 'معايير المنيو والوصفات', 'التدريب', 'التشغيل', 'الجودة', 'التوريد', 'التسويق', 'التقنية'],
          'en': ['Brand standards', 'Store experience', 'Menu standards & recipes', 'Training', 'Operations', 'Quality', 'Supply', 'Marketing', 'Technology']}
CRITERIA = {'ar': ['الالتزام بهوية SHELTER وقيمها', 'الجدية في الاستثمار والتشغيل', 'قدرة إدارية مناسبة', 'فهم السوق المحلي', 'الالتزام بمعايير الجودة', 'الالتزام بمعايير التشغيل', 'الرغبة في علاقة طويلة المدى'],
            'en': ["Commitment to SHELTER's identity and values", 'Serious about investing in and running the business', 'Suitable management capability',
                   'Understanding of the local market', 'Commitment to quality standards', 'Commitment to operating standards', 'A wish to build a long-term relationship']}
JOURNEY = {'ar': ['طلب اهتمام', 'مراجعة أولية', 'اجتماع تعريفي', 'تقييم السوق والموقع', 'مناقشة النموذج', 'الموافقات', 'التعاقد', 'التجهيز والتدريب', 'الافتتاح'],
           'en': ['Expression of interest', 'Initial review', 'Introductory meeting', 'Market & site assessment', 'Model discussion', 'Approvals', 'Agreement', 'Setup & training', 'Opening']}
SUPPORT = {'ar': ['التقييم الأولي', 'مراجعة السوق', 'مراجعة الموقع', 'التصميم', 'التجهيز', 'التدريب', 'ما قبل الافتتاح', 'الافتتاح', 'الدعم المستمر'],
           'en': ['Initial evaluation', 'Market review', 'Location review', 'Design', 'Setup', 'Training', 'Pre-opening', 'Opening', 'Ongoing support']}
# FAQ (docs/franchise/02 §4). visible=True only where the doc marks the answer approved/neutral (✅); the rest stay out of the public flow.
FAQ = [
    (1, True, ('هل يمكن التقديم من داخل الأردن؟', 'يمكنك إرسال طلب اهتمام عبر هذه الصفحة. تتم مراجعة الطلبات وفق خطط نمو SHELTER ومعاييرها، ولا يعني استلام الطلب توفر فرصة في سوق محدد.'),
     ('Can I apply from within Jordan?', "You can send an expression of interest through this page. Applications are reviewed in line with SHELTER's growth plans and standards; receiving an application does not confirm an opportunity in any specific market.")),
    (2, True, ('هل يمكن التقديم من خارج الأردن؟', 'يمكنك إرسال طلب اهتمام من أي دولة عبر هذه الصفحة. تتم مراجعة الطلبات وفق خطط نمو SHELTER، ولا يعني استلام الطلب توفر فرصة في سوق محدد.'),
     ('Can I apply from outside Jordan?', "You can send an expression of interest from any country through this page. Applications are reviewed in line with SHELTER's growth plans; receiving an application does not confirm an opportunity in any specific market.")),
    (3, False, ('هل يجب أن تكون لدي خبرة في المقاهي؟', 'تتم مراجعة الخبرة الإدارية والتشغيلية لكل متقدم ضمن مراحل التقييم، وتُناقش التفاصيل مع المتقدمين خلال المراحل اللاحقة.'),
     ('Do I need café experience?', "Each applicant's management and operating experience is reviewed during the evaluation stages; details are discussed with applicants at later stages.")),
    (4, False, ('كيف يتم تقييم السوق أو الموقع؟', 'يُقيَّم السوق والموقع المقترح ضمن مراحل الشراكة بعد المراجعة الأولية.'),
     ('How are the market and the site assessed?', 'The market and the proposed site are assessed during the partnership stages, after the initial review.')),
    (5, True, ('ماذا يحدث بعد إرسال الطلب؟', 'سيقوم فريق SHELTER بمراجعة الطلب، والتواصل عند الانتقال إلى المرحلة التالية.'),
     ('What happens after I apply?', 'The SHELTER team will review your application and get in touch when it moves to the next stage.')),
    (6, True, ('هل توجد رسوم فرنشايز؟', 'تتم مناقشة التفاصيل التجارية والاستثمارية مع المتقدمين المؤهلين خلال المراحل اللاحقة.'),
     ('Are there franchise fees?', 'Commercial and investment details are discussed with qualified applicants at later stages.')),
    (7, True, ('متى تتم مناقشة الاستثمار؟', 'تُناقش التفاصيل الاستثمارية مع المتقدمين المؤهلين خلال المراحل اللاحقة من رحلة الشراكة.'),
     ('When is investment discussed?', 'Investment details are discussed with qualified applicants at later stages of the partnership journey.')),
    (8, False, ('هل توفر SHELTER التدريب؟', f'[{PV}]'), ('Does SHELTER provide training?', f'[{PV}]')),
    (9, False, ('هل توفر SHELTER دعمًا في التصميم والتجهيز؟', f'[{PV}]'), ('Does SHELTER support design and setup?', f'[{PV}]')),
    (10, False, ('ما الخطوات بعد الموافقة الأولية؟', 'انظر «رحلة الشراكة». التفاصيل تُناقش مع المتقدم في كل مرحلة.'),
     ('What are the steps after initial approval?', "See 'The partnership journey'. Details are discussed with the applicant at each stage.")),
]
DISCLAIMER = {'ar': 'إرسال الطلب لا يمثل موافقة على منح الامتياز ولا التزامًا تعاقديًا.',
              'en': 'Submitting this application is not an approval to grant a franchise and is not a contractual commitment.'}
CONSENT = {'ar': 'أوافق على قيام SHELTER COFFEE بمعالجة البيانات الواردة في هذا الطلب لغرض دراسة طلب الشراكة والتواصل معي بشأنه.',
           'en': 'I agree that SHELTER COFFEE may process the information in this application to review my partnership application and contact me about it.'}
NEXT_STAGE = {'ar': 'سيقوم فريق SHELTER بمراجعة الطلب والتواصل عند الانتقال إلى المرحلة التالية.',
              'en': 'The SHELTER team will review your application and get in touch when it moves to the next stage.'}

# ---------------------------------------------------------------- application form (docs/franchise/03 · fields 1–13 + consent; 14 and 15 are NOT in V1)
YESNO = [('نعم', 'Yes'), ('لا', 'No')]
EXP = [('بدون خبرة', 'No experience'), ('أقل من سنة', 'Less than 1 year'), ('1–2 سنة', '1–2 years'), ('3–5 سنوات', '3–5 years'), ('6–10 سنوات', '6–10 years'), ('أكثر من 10 سنوات', 'More than 10 years')]
LOCSTAT = [('لدي موقع محدد', 'I have a specific location'), ('أبحث عن موقع', "I'm looking for a location"), ('لم أبدأ البحث بعد', "I haven't started looking yet")]
COUNTRY = [('الأردن', 'Jordan'), ('دولة تجريبية 1', 'Sample country 1'), ('دولة تجريبية 2', 'Sample country 2')]
GROUPS = [('about', ('عنك', 'About you')), ('market', ('السوق', 'Market')), ('exp', ('الخبرة', 'Experience')), ('opp', ('الفرصة', 'Opportunity')),
          ('consent', ('الإقرار والإرسال', 'Consent & submit'))]
STEPS = [(('عنك', 'About you'), ['about']), (('السوق', 'Market'), ['market']), (('الخبرة', 'Experience'), ['exp']), (('الفرصة', 'Opportunity'), ['opp']),
         (('المراجعة والإرسال', 'Review & submit'), ['review', 'consent'])]
FD = [  # n = row in the field matrix · k · group · kind · required · label (ar, en) · hint · empty message · options · extra attrs
    dict(n=1, k='name', g='about', kind='text', req=True, lab=('الاسم الكامل', 'Full name'), msg=('أدخل الاسم الكامل', 'Enter your full name'), attrs=' autocomplete="name"'),
    dict(n=2, k='phone', g='about', kind='tel', req=True, lab=('رقم الهاتف', 'Phone number'), hint=('مع رمز الدولة، مثل ‎+962…', 'Include the country code, e.g. +962…'),
         msg=('أدخل رقم الهاتف', 'Enter your phone number'), attrs=' inputmode="tel" autocomplete="tel"'),
    dict(n=3, k='email', g='about', kind='email', req=True, lab=('البريد الإلكتروني', 'Email'), msg=('أدخل البريد الإلكتروني', 'Enter your email'), attrs=' autocomplete="email"'),
    dict(n=4, k='country', g='market', kind='select', req=True, lab=('الدولة', 'Country'), opts=COUNTRY,
         hint=('القائمة الكاملة لدول ISO 3166 في النسخة النهائية — الخيارات الظاهرة عيّنة', 'Full ISO 3166 country list in the final build — the options shown are a sample'),
         msg=('اختر الدولة', 'Select your country')),
    dict(n=5, k='city', g='market', kind='text', req=True, lab=('المدينة', 'City'), msg=('أدخل المدينة', 'Enter your city'), attrs=' autocomplete="address-level2"'),
    dict(n=6, k='market', g='market', kind='text', req=True, lab=('السوق أو المنطقة المهتم بها', 'Market or area of interest'),
         hint=('ذكر السوق في الطلب لا يعني توفره.', 'Naming a market here does not mean SHELTER offers it.'),
         msg=('أدخل السوق أو المنطقة المهتم بها', "Enter the market or area you're interested in")),
    dict(n=8, k='years', g='exp', kind='select', req=True, lab=('سنوات الخبرة في الأعمال', 'Years of business experience'), opts=EXP,
         msg=('اختر سنوات الخبرة في الأعمال', 'Select your years of business experience')),
    dict(n=8, k='expnote', g='exp', kind='textarea', req=False, lab=('نبذة عن خبرتك (اختياري)', 'About your experience (optional)'), attrs=' maxlength="600" rows="3"'),
    dict(n=9, k='owns', g='exp', kind='radio', req=True, lab=('هل تملك أو تدير عملًا حاليًا؟', 'Do you currently own or manage a business?'), opts=YESNO,
         msg=('أجب: هل تملك أو تدير عملًا حاليًا؟', 'Answer: do you currently own or manage a business?')),
    dict(n=12, k='company', g='exp', kind='text', req=False, lab=('اسم الشركة (اختياري)', 'Company name (optional)'), attrs=' autocomplete="organization"'),
    dict(n=13, k='website', g='exp', kind='url', req=False, lab=('موقع الشركة الإلكتروني (اختياري)', 'Company website (optional)'), attrs=' inputmode="url" autocomplete="url"'),
    dict(n=7, k='interest', g='opp', kind='text', req=True, lab=('نوع الاهتمام بالشراكة', 'Partnership interest'),
         hint=('الخيارات النهائية PENDING OWNER DECISION (PF-06) — نص حر قصير مؤقتًا', 'Final options PENDING OWNER DECISION (PF-06) — short free text for now'),
         msg=('اكتب نوع اهتمامك بالشراكة', 'Describe your partnership interest')),
    dict(n=10, k='location', g='opp', kind='radio', req=True, lab=('حالة الموقع المقترح', 'Proposed location status'), opts=LOCSTAT,
         hint=('خيارات مقترحة (RECOMMENDED) بانتظار الاعتماد', 'Recommended options, pending approval'),
         msg=('اختر حالة الموقع المقترح', 'Select your proposed location status')),
    dict(n=11, k='message', g='opp', kind='textarea', req=True, lab=('رسالة تعريفية قصيرة', 'Short introduction'),
         hint=('عرّفنا بك وبسبب اهتمامك بالشراكة. حتى 2000 حرف.', "Tell us about yourself and why you're interested. Up to 2,000 characters."),
         msg=('اكتب رسالة تعريفية قصيرة', 'Write a short introduction'), attrs=' maxlength="2000" rows="5"'),
    dict(n=17, k='consent', g='consent', kind='checkbox', req=True, lab=(CONSENT['ar'], CONSENT['en']),
         hint=('نص الموافقة: PENDING OWNER APPROVAL (PF-03) · لا اشتراك في النشرة ولا رسائل تسويقية.', 'Consent wording: PENDING OWNER APPROVAL (PF-03) · No newsletter or marketing sign-up.'),
         msg=('يجب الموافقة على معالجة البيانات لإرسال الطلب', 'You need to agree to the data processing to send your application')),
]
FIELD = {f['k']: f for f in FD}
MSGS = {
    'ar': dict(ar=True, pre='خطأ:', email='أدخل بريدًا إلكترونيًا صحيحًا، مثل name@example.com', tel='أدخل رقم هاتف صحيحًا مع رمز الدولة',
               url='أدخل رابطًا صحيحًا، مثل https://example.com', headA='تعذّر إرسال الطلب: يرجى تصحيح', headB='يرجى تصحيح', step='الخطوة {i} من {n}: {s}'),
    'en': dict(ar=False, pre='Error:', email='Enter a valid email, like name@example.com', tel='Enter a valid phone number with the country code',
               url='Enter a valid web address, like https://example.com', headA="We couldn't send your application. Please fix", headB='Please fix', step='Step {i} of {n}: {s}'),
}
FILLED = {
    'ar': dict(name='متقدم تجريبي 1', phone='+962700000001', email='applicant1@example.com', country='دولة تجريبية 1', city='مدينة تجريبية 1', market='سوق تجريبي 1',
               years='6–10 سنوات', expnote='', owns='نعم', company='شركة تجريبية', website='https://example.com', interest='نص تجريبي: نوع الاهتمام',
               location='أبحث عن موقع', message='نص تجريبي للرسالة التعريفية.', consent=True),
    'en': dict(name='Sample Applicant 1', phone='+962700000001', email='applicant1@example.com', country='Sample country 1', city='Sample city 1', market='Sample market 1',
               years='6–10 years', expnote='', owns='Yes', company='Sample company', website='https://example.com', interest='Sample text: partnership interest',
               location="I'm looking for a location", message='Sample introduction text.', consent=True),
}

# ---------------------------------------------------------------- page layer: layout + missing primitives, var(--…) tokens ONLY (M34)
PAGE_CSS = '''
/* ---- franchise page layer (tokens only) */
a{color:inherit}p{margin:0 0 var(--space-2)}
.wf-tag{background:var(--color-accent);color:var(--color-on-accent);font-size:var(--text-xs);padding:var(--space-1) var(--space-4)}
.vh{position:absolute!important;width:1px;height:1px;margin:0;padding:0;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0}
.fr-narrow{max-width:var(--container-reading)}
.fr-row{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2)}
.fr-tags{display:flex;flex-wrap:wrap;gap:var(--space-2);margin-block-end:var(--space-4)}
.fr-stack{display:flex;flex-direction:column;gap:var(--space-3);margin-block-start:var(--space-4)}
.fr-block{display:flex;width:100%}
.site{border-block-end:var(--border-default) solid var(--color-border)}
.site-in{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:var(--control-lg)}
.logo{font-weight:var(--weight-bold);letter-spacing:var(--tracking-en-caps)}
.legend{font-size:var(--text-xs);color:var(--color-muted);border-block-start:var(--border-default) solid var(--color-border);padding-block-start:var(--space-2);margin-block:var(--space-6) var(--space-4)}
.sec{padding-block:var(--space-10);scroll-margin-top:var(--space-2)}.sec.alt{background:var(--color-surface)}
.sec h2{font-size:var(--text-3xl)}
@media (min-width:1024px){.sec{padding-block:var(--space-16)}}
.eyebrow{display:block;font-size:var(--text-xs);font-weight:var(--weight-bold);letter-spacing:var(--tracking-en-caps);margin-block-end:var(--space-2)}
.lead{font-size:var(--text-lg)}
.ds-media.fr-wide{aspect-ratio:16/10}.ds-media.fr-43{aspect-ratio:4/3}
.fr-media-in{margin:var(--space-4);text-align:center}
.hero{padding-block:var(--space-5) var(--space-8)}
.hero-g{display:grid;gap:var(--space-5);align-items:center}
.hero h1{font-size:var(--text-5xl);margin-block:var(--space-1) var(--space-3)}
.fr-actions{display:flex;flex-wrap:wrap;gap:var(--space-3);margin-block-start:var(--space-4)}
.fr-actions .ds-btn{flex:1 1 12rem}
@media (min-width:768px){.fr-actions .ds-btn{flex:0 0 auto}}
@media (min-width:1024px){.hero{padding-block:var(--space-12) var(--space-16)}.hero-g{grid-template-columns:minmax(0,5fr) minmax(0,6fr);gap:var(--space-12)}}
.facts{display:grid;margin-block:var(--space-4) var(--space-2)}
.facts>div{border-block-start:var(--border-default) solid var(--color-border);padding-block:var(--space-3)}
.facts dt{font-size:var(--text-sm);color:var(--color-muted)}.facts dd{margin:0;font-size:var(--text-lg);font-weight:var(--weight-bold)}
@media (min-width:768px){.facts{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:var(--space-6)}}
@media (min-width:1024px){.facts{grid-template-columns:repeat(4,minmax(0,1fr))}}
.exp{display:grid;gap:var(--space-6);margin-block-start:var(--space-4)}.exp h3{font-size:var(--text-xl);margin-block:var(--space-3) var(--space-1)}
@media (min-width:768px){.exp{grid-template-columns:repeat(2,minmax(0,1fr))}}
/* accordion header (missing kit primitive) — rows collapse below 1024, always open on desktop (aria-disabled, APG) */
.why{border-block-end:var(--border-default) solid var(--color-border)}.why-row{border-block-start:var(--border-default) solid var(--color-border)}
.why-h{margin:0}
.acc{display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);width:100%;min-height:var(--control-lg);padding-block:var(--space-2);padding-inline:0;border:0;background:none;font:inherit;font-size:var(--text-xl);font-weight:var(--weight-bold);color:var(--color-text);text-align:start;cursor:pointer}
.acc .num{font-size:var(--text-sm);margin-inline-end:var(--space-3)}
.acc svg{flex:none}.acc[aria-expanded=true] svg{transform:rotate(180deg)}
.acc[aria-disabled=true]{cursor:default}.acc[aria-disabled=true] svg{display:none}
.why-p{display:grid;gap:var(--space-4);padding-block-end:var(--space-5)}.why-p[hidden]{display:none}
.ticks,.crit{list-style:none;margin:0;padding:0}
.ticks li,.crit li{display:flex;gap:var(--space-3);align-items:flex-start;padding-block:var(--space-1)}
.ticks svg,.crit svg{flex:none;margin-block-start:var(--space-1)}
@media (min-width:1024px){.acc{font-size:var(--text-2xl)}.why-p{grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--space-12);align-items:center;padding-block-end:var(--space-10)}
 .why-row:nth-child(even) .why-p>.ds-media{order:2}}
.sys{list-style:none;margin:var(--space-4) 0 0;padding:0;display:grid;counter-reset:s}
.sys li{counter-increment:s;display:flex;align-items:center;gap:var(--space-3);min-height:var(--control-lg);border-block-start:var(--border-default) solid var(--color-border);padding-block:var(--space-2);font-weight:var(--weight-bold)}
.sys li::before{content:counter(s,decimal-leading-zero);font-size:var(--text-sm);min-width:var(--space-8);color:var(--color-muted);font-weight:var(--weight-regular)}
@media (min-width:768px){.sys{grid-template-columns:repeat(3,minmax(0,1fr));column-gap:var(--space-6)}}
.fr-midband{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-3) var(--space-6);margin-block:var(--space-2)}
.fr-midband p{margin:0;font-size:var(--text-lg);font-weight:var(--weight-bold);flex:1 1 16rem}.fr-midband .ds-btn{flex:1 1 14rem}
@media (min-width:768px){.fr-midband .ds-btn{flex:0 0 auto}}
.crit{margin-block-start:var(--space-4);display:grid}.crit li{border-block-start:var(--border-default) solid var(--color-border);padding-block:var(--space-3);font-size:var(--text-lg)}
@media (min-width:768px){.crit{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:var(--space-8)}}
/* timeline (missing kit primitive) — vertical below 1024, horizontal on desktop */
.tl{list-style:none;margin:var(--space-5) 0 0;padding:0}
.tl li{position:relative;display:flex;align-items:flex-start;gap:var(--space-3);padding-block-end:var(--space-5);min-height:var(--touch-target)}
.tl li::before{content:"";position:absolute;inset-inline-start:calc(var(--control-sm) / 2);top:calc(var(--control-sm) + var(--space-1));bottom:var(--space-1);border-inline-start:var(--border-strong) solid var(--color-muted)}
.tl li:last-child::before{display:none}
.tl .n{flex:none;display:flex;align-items:center;justify-content:center;width:var(--control-sm);height:var(--control-sm);border:var(--border-strong) solid var(--color-accent);border-radius:50%;background:var(--color-bg);font-weight:var(--weight-bold);font-size:var(--text-sm)}
.tl .t{padding-block-start:var(--space-1);font-weight:var(--weight-bold)}
@media (min-width:1024px){.tl{display:grid;grid-template-columns:repeat(9,minmax(0,1fr));gap:var(--space-1)}
 .tl li{flex-direction:column;align-items:center;text-align:center;gap:var(--space-2);padding:0}
 .tl li::before{inset-inline-start:calc(50% + var(--control-sm) / 2 + var(--space-1));inset-inline-end:calc(var(--control-sm) / 2 + var(--space-1) - 50%);top:calc(var(--control-sm) / 2);bottom:auto;border-inline-start:0;border-block-start:var(--border-strong) solid var(--color-muted)}
 .tl .t{padding:0;font-size:var(--text-sm);overflow-wrap:anywhere;min-width:0;max-width:100%}}
@media (min-width:1024px) and (max-width:1279.98px){.tl .t{font-size:var(--text-xs)}}
.stages{list-style:none;margin:var(--space-4) 0 0;padding:0;display:grid;counter-reset:g}
.stages li{counter-increment:g;display:flex;align-items:center;gap:var(--space-3);border-block-start:var(--border-default) solid var(--color-border);min-height:var(--touch-target);padding-block:var(--space-1)}
.stages li::before{content:counter(g);display:inline-flex;align-items:center;justify-content:center;flex:none;width:var(--space-8);height:var(--space-8);border:var(--border-strong) solid var(--color-muted);border-radius:50%;font-size:var(--text-sm)}
.stages li.link{font-weight:var(--weight-bold)}
@media (min-width:1024px){.stages{display:flex;flex-wrap:wrap;gap:var(--space-2)}.stages li{border:var(--border-strong) solid var(--color-border);border-radius:var(--radius-pill);padding-block:var(--space-1);padding-inline:var(--space-1) var(--space-4)}
 .stages li.link{border-color:var(--color-accent)}}
/* disclosure (missing kit primitive) — native details/summary inside a .ds-card */
.faq{display:grid;gap:var(--space-3);margin-block-start:var(--space-4)}
@media (min-width:1024px){.faq{grid-template-columns:repeat(2,minmax(0,1fr));align-items:start;gap:var(--space-3) var(--space-6)}}
.qa summary{display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);min-height:var(--touch-target);font-weight:var(--weight-bold);cursor:pointer;list-style:none}
.qa summary::-webkit-details-marker{display:none}.qa summary::after{content:"+";font-size:var(--text-xl);line-height:1;flex:none}
.qa[open] summary::after{content:"−"}.qa .a{padding-block-start:var(--space-2)}
.final-g{display:grid;gap:var(--space-5);align-items:center}
@media (min-width:1024px){.final-g{grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--space-12)}}
.foot{border-block-start:var(--border-default) solid var(--color-border);background:var(--color-surface);padding-block:var(--space-5) var(--space-6)}
.foot ul{display:flex;flex-wrap:wrap;gap:var(--space-1) var(--space-5);list-style:none;margin:var(--space-2) 0;padding:0}
.fr-anno{display:block;padding:var(--space-4);margin-block:var(--space-6)}
.fr-anno h2{font-size:var(--text-xl)}.fr-anno h3{font-size:var(--text-base);margin-block:var(--space-4) var(--space-2)}
.fr-anno ol,.fr-anno ul{margin:0;padding-inline-start:var(--space-5)}.fr-anno li{margin-block-end:var(--space-3)}.fr-anno .q{font-weight:var(--weight-bold);display:block}
/* sticky CTA bar (missing kit primitive) — below 1024 only, shown after the hero, hidden over CTA bands and the form */
.sticky{position:fixed;inset-inline:0;bottom:0;z-index:var(--z-sticky);background:var(--color-bg);border-block-start:var(--border-strong) solid var(--color-accent);padding:var(--space-2) var(--space-4) calc(var(--space-2) + env(safe-area-inset-bottom))}
@media (min-width:1024px){.sticky{display:none!important}}
@media (max-width:1023.98px){.has-sticky{padding-block-end:var(--space-20)}html:has(.has-sticky){scroll-padding-bottom:var(--space-20)}}
/* ---- form layout */
.intro h1{margin-block:var(--space-4) var(--space-2)}.intro p{color:var(--color-muted);font-size:var(--text-sm)}
.fr-group{margin-block-end:var(--space-4)}.fr-group>h2,.fr-group>h3{font-size:var(--text-xl)}
fieldset.ds-field{border:0;margin-inline:0;padding:0;min-width:0}
fieldset.ds-field>legend{padding:0;margin-block-end:var(--space-1)}
.req{margin-inline-start:var(--space-1)}
.opts{display:flex;flex-wrap:wrap;gap:var(--space-2)}.opts .ds-chip{white-space:normal}
.ds-chip input{margin:0;margin-inline-end:var(--space-2)}
.ds-chip:has(input:checked){border-color:var(--color-accent);font-weight:var(--weight-bold)}
input[type=radio],input[type=checkbox]{width:var(--icon-md);height:var(--icon-md);accent-color:var(--color-accent);flex:none}
.ds-check.consent{align-items:flex-start}.ds-check.consent input{margin-block-start:var(--space-1)}
.disc{border-inline-start:var(--space-1) solid var(--color-accent);padding:var(--space-2) var(--space-3);margin-block-start:var(--space-3);background:var(--color-surface);font-size:var(--text-sm)}
.esum{margin-block-end:var(--space-4)}.esum:focus{outline:3px solid var(--color-focus);outline-offset:2px}
.esum-h{font-size:var(--text-lg);display:flex;gap:var(--space-2);align-items:center}
.esum ul{margin:0;padding:0;list-style:none}.esum li a{display:flex;align-items:center;min-height:var(--touch-target);font-weight:var(--weight-bold)}
.err{margin:0}
.fr-alert{margin-block-end:var(--space-4)}.fr-alert h2{font-size:var(--text-lg)}
.prog{margin-block-end:var(--space-4)}.prog-t{font-weight:var(--weight-bold)}
.bars{display:grid;grid-template-columns:repeat(5,1fr);gap:var(--space-2);list-style:none;margin:0;padding:0}
.bars li{height:var(--space-2);border-radius:var(--radius-sm);background:var(--color-border)}.bars li.on{background:var(--color-accent)}
.nav{display:flex;gap:var(--space-3);justify-content:space-between;margin-block-start:var(--space-2)}.nav .ds-btn{flex:1}
.rv{border-block-start:var(--border-default) solid var(--color-border);padding-block:var(--space-2)}.rv:first-child{border-block-start:0}
.rv-h{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2)}.rv-h h3,.rv-h h4{font-size:var(--text-base);margin:0}
.center{text-align:center}.appno{font-size:var(--text-2xl);font-weight:var(--weight-bold);margin-block:var(--space-1)}
.kv{display:grid;margin:0}
.kv>div{display:grid;grid-template-columns:minmax(6rem,36%) minmax(0,1fr);gap:var(--space-2);border-block-start:var(--border-default) solid var(--color-border);padding-block:var(--space-2)}
.kv>div:first-child{border-block-start:0}.kv dt{color:var(--color-muted);font-size:var(--text-sm)}.kv dd{margin:0;font-weight:var(--weight-bold);overflow-wrap:anywhere}
/* ---- owner dashboard layout (same patterns as the careers module — Applications Core, TD-AC-01) */
.dtop{display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);min-height:var(--control-lg);padding-inline:var(--space-5);border-block-end:var(--border-default) solid var(--color-border)}
.dtop-r{display:flex;align-items:center;gap:var(--space-3);font-size:var(--text-sm)}
.dshell{display:grid;grid-template-columns:13rem minmax(0,1fr)}
.dside{border-inline-end:var(--border-default) solid var(--color-border);padding:var(--space-3)}
.dside-h{font-weight:var(--weight-bold);margin:0}.dside-s{font-size:var(--text-xs);color:var(--color-muted);margin-block-end:var(--space-3)}
.dside nav,.dnav{display:flex;flex-direction:column;gap:var(--space-2)}
.dnav .ds-chip{justify-content:space-between;gap:var(--space-2)}
.dmain{padding:var(--space-5) var(--space-6) var(--space-10);min-width:0;max-width:var(--container-dashboard)}
.mtop{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:var(--control-lg);padding-inline:var(--space-3);border-block-end:var(--border-default) solid var(--color-border)}
.mtabs{display:flex;flex-wrap:wrap;gap:var(--space-2);padding:var(--space-2) var(--space-3);border-block-end:var(--border-default) solid var(--color-border)}
.mtabs .ds-chip{gap:var(--space-1)}
.mmain{padding:var(--space-3) var(--space-4) var(--space-8)}
.dlegend{margin:var(--space-6) var(--space-4) var(--space-4);font-size:var(--text-xs);color:var(--color-muted);border-block-start:var(--border-default) solid var(--color-border);padding-block-start:var(--space-2)}
.ptitle{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-3);margin-block-end:var(--space-4)}.ptitle h1{font-size:var(--text-2xl);margin:0}
.sub{font-size:var(--text-sm);color:var(--color-muted)}
.kpis{list-style:none;margin:0 0 var(--space-5);padding:0;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--space-3)}
.kpi{display:flex;flex-direction:column;justify-content:space-between;gap:var(--space-1);min-height:var(--space-20);text-decoration:none}
.kpi-n{font-size:var(--text-3xl);font-weight:var(--weight-bold);font-variant-numeric:tabular-nums}
.dpanel{margin-block-end:var(--space-4)}.dpanel>h2{font-size:var(--text-lg)}.dpanel h3{font-size:var(--text-base);margin-block:var(--space-3) var(--space-2)}
.rows{list-style:none;margin:0;padding:0}
.rows li{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-1) var(--space-3);border-block-start:var(--border-default) solid var(--color-border);padding-block:var(--space-2);min-height:var(--control-lg)}
.rows li:first-child{border-block-start:0}
.toolbar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:var(--space-3);margin-block-end:var(--space-3)}
.toolbar .ds-field{margin:0}.toolbar .grow{flex:1 1 16rem}
.chips{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2);margin-block-end:var(--space-3);font-size:var(--text-sm)}
.fr-tbl{overflow-x:auto;border:var(--border-default) solid var(--color-border);border-radius:var(--radius-lg)}
.ds-table caption{text-align:start;padding:var(--space-2) var(--space-3);font-size:var(--text-sm);color:var(--color-muted)}
.ds-table thead th{background:var(--color-surface)}.ds-table th[scope=row]{font-weight:var(--weight-regular)}
tr.unread th a,.unread .acard-n a{font-weight:var(--weight-bold)}
.nowrap{white-space:nowrap}
.pager{display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-2);margin-block-start:var(--space-4)}
.pager [aria-current=page]{background:var(--color-accent);color:var(--color-on-accent)}
.jump{display:flex;align-items:center;gap:var(--space-2);margin-inline-start:auto;font-size:var(--text-sm)}.jump .ds-input{width:5rem}
.acards{list-style:none;margin:0;padding:0;display:grid;gap:var(--space-3)}
@media (min-width:700px){.acards{grid-template-columns:repeat(2,minmax(0,1fr))}.kpis{grid-template-columns:repeat(4,minmax(0,1fr))}}
.acard{display:grid;grid-template-columns:minmax(0,1fr) var(--touch-target);gap:var(--space-2);align-items:start}.acard.one{grid-template-columns:minmax(0,1fr)}
.acard-n{margin:0;display:flex;flex-wrap:wrap;align-items:center;gap:var(--space-1) var(--space-2)}
.acard p{font-size:var(--text-sm);margin-block-end:var(--space-1)}
.appgrid{display:grid;grid-template-columns:minmax(0,1fr);gap:var(--space-4)}
.toc{display:none}
.notes,.meet{list-style:none;margin:0 0 var(--space-3);padding:0;display:grid;gap:var(--space-2)}.notes .by{font-size:var(--text-sm);color:var(--color-muted);margin:0}
.two{display:grid;grid-template-columns:repeat(auto-fit,minmax(9rem,1fr));gap:var(--space-3)}
.three{display:grid;grid-template-columns:minmax(0,1fr);gap:var(--space-3)}
@media (min-width:700px){.three{grid-template-columns:repeat(3,minmax(0,1fr))}}
/* side drawer (missing kit primitive, desktop quick view / filters) + sticky dialog head/foot inside .ds-sheet and the drawer */
.fr-drawer{position:fixed;inset-block:0;inset-inline-end:0;width:min(30rem,100vw);overflow:auto;background:var(--color-bg);padding:0 var(--space-4);z-index:var(--z-modal);box-shadow:var(--shadow-lg)}
.ds-sheet.fr-dlg{padding-block:0}
.dlg-h{position:sticky;top:0;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);background:var(--color-bg);padding-block:var(--space-2);border-block-end:var(--border-default) solid var(--color-border)}
.dlg-h h2{font-size:var(--text-xl);margin:0}
.dlg-b{padding-block:var(--space-4)}
.dlg-f{position:sticky;bottom:0;display:flex;flex-wrap:wrap;gap:var(--space-2);background:var(--color-bg);padding-block:var(--space-3);border-block-start:var(--border-default) solid var(--color-border)}
.dlg-f .ds-btn{flex:1 1 9rem}
@media (min-width:1024px){.kpis{grid-template-columns:repeat(4,minmax(0,1fr))}.appgrid{grid-template-columns:12rem minmax(0,1fr)}
 .toc{display:flex;flex-direction:column;position:sticky;top:var(--space-3);align-self:start}}
'''
CSS = read(os.path.join(DS, 'build', 'tokens.css')) + '\n' + read(os.path.join(DS, 'wireframe-kit.css')) + '\n' + PAGE_CSS

# ---------------------------------------------------------------- prototype helpers (NOT production code)
PAGE_JS = r'''(()=>{const mq=matchMedia('(min-width:1024px)');
const acc=[...document.querySelectorAll('.acc')];
const set=(b,o)=>{b.setAttribute('aria-expanded',String(o));document.getElementById(b.getAttribute('aria-controls')).hidden=!o};
function sync(){acc.forEach((b,i)=>{if(mq.matches){set(b,true);b.setAttribute('aria-disabled','true')}else{b.removeAttribute('aria-disabled');set(b,i===0)}})}
acc.forEach(b=>b.addEventListener('click',()=>{if(b.getAttribute('aria-disabled')!=='true')set(b,b.getAttribute('aria-expanded')!=='true')}));
mq.addEventListener('change',sync);sync();
const st=document.querySelector('.sticky');if(!st)return;const on=new Set();
const upd=()=>{st.hidden=mq.matches||on.size>0};
const io=new IntersectionObserver(es=>{es.forEach(e=>e.isIntersecting?on.add(e.target):on.delete(e.target));upd()});
document.querySelectorAll('[data-hide-sticky]').forEach(w=>io.observe(w));mq.addEventListener('change',upd)})();'''

FORM_JS = r'''(()=>{const f=document.querySelector('form.app');if(!f)return;
const M=JSON.parse(f.dataset.msgs),B=f.classList.contains('steps'),HL=f.dataset.hl||'2',$=(s,r=f)=>r.querySelector(s),$$=(s,r=f)=>[...r.querySelectorAll(s)];
const N=n=>M.ar?(n==1?'خطأ واحد':n==2?'خطأين':n<=10?n+' أخطاء':n+' خطأً'):(n==1?'1 error':n+' errors');
const val=w=>{const k=w.dataset.kind,c=$$('input,select,textarea',w);if(k==='radio'){const x=c.find(i=>i.checked);return x?x.value:''}if(k==='checkbox')return c[0].checked?'1':'';return c[0].value.trim()};
function check(w){const v=val(w),k=w.dataset.kind;if(!v)return w.dataset.req==='1'?w.dataset.msg:'';
 if(k==='email'&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))return M.email;
 if(k==='tel'&&(v.match(/[0-9٠-٩]/g)||[]).length<7)return M.tel;
 if(k==='url'&&!/^(https?:\/\/)?[^\s\/.]+(\.[^\s\/.]+)+(\/\S*)?$/i.test(v))return M.url;return''}
function clear(){$$('.esum,.err').forEach(e=>e.remove());$$('[aria-invalid]').forEach(i=>{i.removeAttribute('aria-invalid');i.dataset.d?i.setAttribute('aria-describedby',i.dataset.d):i.removeAttribute('aria-describedby')});$$('.ds-field--error').forEach(w=>w.classList.remove('ds-field--error'))}
function validate(scope){clear();const bad=[];
 $$('[data-f]',scope).forEach(w=>{const m=check(w);if(!m)return;const p=document.createElement('p');p.className='ds-error err';p.id='e-'+w.dataset.f;p.textContent=M.pre+' '+m;
  const a=w.querySelector(':scope>.ds-help')||w.querySelector(':scope>legend,:scope>label');a?a.after(p):w.prepend(p);w.classList.add('ds-field--error');
  $$('input,select,textarea',w).forEach(i=>{i.setAttribute('aria-invalid','true');i.setAttribute('aria-describedby',(p.id+' '+(i.dataset.d||'')).trim())});bad.push([w.dataset.first,m])});
 if(!bad.length)return true;const s=document.createElement('div');s.className='ds-alert ds-alert--danger esum';s.setAttribute('role','alert');s.tabIndex=-1;s.setAttribute('aria-labelledby','esum-h');
 s.innerHTML='<h'+HL+' class="esum-h" id="esum-h"></h'+HL+'><ul></ul>';s.querySelector('#esum-h').textContent=(B?M.headB:M.headA)+' '+N(bad.length);
 bad.forEach(([id,m])=>{const li=document.createElement('li'),a=document.createElement('a');a.href='#'+id;a.textContent=m;li.append(a);$('ul',s).append(li)});
 (B?scope:f).prepend(s);s.scrollIntoView({block:'start'});s.focus({preventScroll:true});return false}
const steps=$$('.step');let cur=Math.max(0,steps.findIndex(s=>!s.hidden));
function fillReview(){$$('.rv').forEach((rv,i)=>{const dl=$('dl',rv);dl.textContent='';$$('[data-f]',steps[i]).forEach(w=>{const l=w.querySelector(':scope>label,:scope>legend'),d=document.createElement('div'),dt=document.createElement('dt'),dd=document.createElement('dd');
 dt.textContent=l.textContent.replace('*','').trim();let v=val(w);if(w.dataset.kind==='select'){const s=$('select',w);v=s.value?s.selectedOptions[0].textContent:''}dd.textContent=v||'—';dd.dir='auto';d.append(dt,dd);dl.append(d)})})}
function show(i){steps.forEach((s,j)=>s.hidden=j!==i);cur=i;const n=JSON.parse(f.dataset.steps);$('.prog-t').textContent=M.step.replace('{i}',i+1).replace('{n}',n.length).replace('{s}',n[i]);
 $$('.bars li').forEach((b,j)=>b.classList.toggle('on',j<=i));if(i===steps.length-1)fillReview();window.scrollTo(0,f.getBoundingClientRect().top+scrollY-8);$('.prog-t').focus({preventScroll:true})}
f.addEventListener('click',e=>{const a=e.target.closest('.esum a');if(a){e.preventDefault();const t=document.getElementById(a.hash.slice(1));t.closest('[data-f]').scrollIntoView({block:'start'});t.focus({preventScroll:true});return}
 const n=e.target.closest('[data-nav]');if(n){if(n.dataset.nav==='next'){if(validate(steps[cur]))show(cur+1)}else{clear();show(cur-1)}return}
 const g=e.target.closest('[data-goto]');if(g){clear();show(+g.dataset.goto)}});
f.addEventListener('submit',e=>{e.preventDefault();if(B&&cur<steps.length-1){if(validate(steps[cur]))show(cur+1);return}if(validate(B?steps[cur]:f))location.href=f.getAttribute('action')});
if(B&&cur===steps.length-1)fillReview()})();'''

# ---------------------------------------------------------------- icons
def svg(d, s=20, w=2):
    return f'<svg aria-hidden="true" width="{s}" height="{s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{w}">{d}</svg>'
I_X = svg('<path d="M6 6l12 12M18 6L6 18"/>')
I_EYE = svg('<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>')
I_MENU = svg('<path d="M4 6h16M4 12h16M4 18h16"/>')
I_BELL = svg('<path d="M6 16V11a6 6 0 1 1 12 0v5l2 2H4z"/><path d="M10 20a2 2 0 0 0 4 0"/>')
I_DOWN = svg('<path d="M6 9l6 6 6-6"/>', 22, 2.5)
I_CHECK = svg('<path d="M5 12l5 5L19 7"/>', 18, 2.5)
I_U = svg('<path d="M6 15l6-6 6 6"/>')
I_D = svg('<path d="M6 9l6 6 6-6"/>')

# ---------------------------------------------------------------- kit helpers
B_PRI, B_OUT, B_GHOST, B_LINK = 'ds-btn ds-btn--primary', 'ds-btn ds-btn--outline', 'ds-btn ds-btn--ghost', 'ds-btn ds-btn--link'
B_ICON = 'ds-btn ds-btn--ghost ds-btn--icon'

def note(text):  # wireframe annotation (status marker) — not product UI
    return f'<span class="wf-note" dir="auto">{bd(text)}</span>'

def notes(*items):
    return '<div class="fr-tags">' + ''.join(note(t) for t in items) + '</div>'

def sample(L):
    return f'<span class="wf-note sample">{SAMPLE[L]}</span>'

def media(L, what, cls='fr-43'):
    return (f'<div class="ds-media {cls}"><span class="wf-note fr-media-in"><span lang="en" dir="ltr">{MEDIA}</span><br>'
            f'<span lang="{L}" dir="{"rtl" if L == "ar" else "ltr"}">{bd(what)}</span></span></div>')

def en_attr(L):
    return ' lang="en"' if L == 'ar' else ''

DATE = re.compile(r'(?<![\w="-])(\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2})?)(?![\w"-])')

def page(body, title, state, L='ar', cls='', scripts=()):
    d = 'rtl' if L == 'ar' else 'ltr'
    tag_ = f'<div class="wf-tag">LOW-FI WIREFRAME · {L.upper()} · {bd(state)}</div>'
    js = ''.join(f'<script>{s}</script>' for s in scripts)
    bc = f' class="{cls}"' if cls else ''
    return (f'<!doctype html><html lang="{L}" dir="{d}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            f'<title>{esc(title)}</title><style>{CSS}</style></head><body{bc}>{tag_}{body}{js}</body></html>\n')

def write(name, content):
    head, sep, rest = content.partition('<body')
    content = head + sep + DATE.sub(r'<span dir="ltr">\1</span>', rest)
    with open(os.path.join(OUT, name), 'w', encoding='utf-8', newline='\n') as fh:
        fh.write(content)

def site_header(L, other):
    sw = (f'<a class="{B_OUT}" href="{other}" lang="en" hreflang="en">English</a>' if L == 'ar'
          else f'<a class="{B_OUT}" href="{other}" lang="ar" hreflang="ar">العربية</a>')
    return f'<header class="site"><div class="ds-container site-in"><span class="logo" lang="en">SHELTER COFFEE</span>{sw}</div></header>'

def legend(L, extra=''):
    t = tr(L, 'LOW-FI WIREFRAME — تخطيط وسلوك فقط، بلا هوية بصرية (ملفات الهوية غير متوفرة — M-10). نظام تصميم واحد: tokens.css + wireframe-kit.css (M34). '
              '"عيّنة" و"تجريبي" = قيم توضيحية وليست بيانات حقيقية. النصوص مسودات اتجاه (docs/franchise/02) وليست نصوصًا نهائية. ',
           'LOW-FI WIREFRAME — layout and behaviour only, no visual identity (brand files missing — M-10). One design system: tokens.css + wireframe-kit.css (M34). '
           '"SAMPLE" values are illustrative, not real data. Copy is direction draft (docs/franchise/02), not final copy. ')
    return f'<p class="legend">{bd(t + extra)}</p>'

# ---------------------------------------------------------------- public: form renderer (shared by the page and the standalone state pages)
def req_mark():
    return '<span class="req" aria-hidden="true">*</span>'

def err_p(k, msg, L):
    return f'<p class="ds-error err" id="e-{k}">{MSGS[L]["pre"]} {esc(msg)}</p>'

def first_id(k):
    return f'{k}-1' if FIELD[k]['kind'] == 'radio' else k

def ctl_attrs(k, hint, err):
    d = f'h-{k}' if hint else ''
    desc = ' '.join(x for x in [f'e-{k}' if err else '', d] if x)
    a = f' data-d="{d}"' if d else ''
    if desc: a += f' aria-describedby="{desc}"'
    if err: a += ' aria-invalid="true"'
    return a

def field(fd, L, v=None, err=None):
    k, kind, req = fd['k'], fd['kind'], fd['req']
    i = 0 if L == 'ar' else 1
    label, hint, msg = fd['lab'][i], (fd.get('hint') or (None, None))[i], (fd.get('msg') or ('', ''))[i]
    attrs = fd.get('attrs', '') + (' required' if req else '')
    star = req_mark() if req else ''
    e = err_p(k, err, L) if err else ''
    cls = 'ds-field ds-field--error' if err else 'ds-field'
    data = f'data-f="{k}" data-kind="{kind}" data-req="{1 if req else 0}" data-first="{first_id(k)}" data-msg="{esc(msg)}"'
    h = f'<span class="ds-help" id="h-{k}">{bd(hint)}</span>' if hint else ''
    lab = f'<label class="ds-label" for="{k}">{esc(label)}{star}</label>'
    if kind in ('text', 'tel', 'email', 'url'):
        val = f' value="{esc(v)}"' if v else ''
        return f'<div class="{cls}" {data}>{lab}{h}{e}<input class="ds-input" type="{kind}" id="{k}" name="{k}" dir="auto"{attrs}{val}{ctl_attrs(k, hint, err)}></div>'
    if kind == 'textarea':
        return f'<div class="{cls}" {data}>{lab}{h}{e}<textarea class="ds-textarea" id="{k}" name="{k}" dir="auto"{attrs}{ctl_attrs(k, hint, err)}>{esc(v or "")}</textarea></div>'
    if kind == 'select':
        body = ''.join(f'<option{" selected" if o[i] == v else ""}>{esc(o[i])}</option>' for o in fd['opts'])
        if k == 'country':
            body = f'<optgroup label="{esc(tr(L, "عيّنة — القائمة الكاملة ISO 3166", "Sample — full ISO 3166 list"))}">{body}</optgroup>'
        return (f'<div class="{cls}" {data}>{lab}{h}{e}<select class="ds-select" id="{k}" name="{k}"{attrs}{ctl_attrs(k, hint, err)}>'
                f'<option value="">{tr(L, "اختر", "Select")}</option>{body}</select></div>')
    if kind == 'radio':
        items = ''.join(f'<label class="ds-chip"><input type="radio" id="{k}-{j + 1}" name="{k}" value="{esc(o[i])}"{attrs}{" checked" if o[i] == v else ""}{ctl_attrs(k, hint, err)}>{esc(o[i])}</label>'
                        for j, o in enumerate(fd['opts']))
        return f'<fieldset class="{cls}" {data}><legend class="ds-label">{esc(label)}{star}</legend>{h}{e}<div class="opts">{items}</div></fieldset>'
    if kind == 'checkbox':
        return (f'<div class="{cls}" {data}><label class="ds-check consent"><input type="checkbox" id="{k}" name="{k}"{attrs}{" checked" if v else ""}{ctl_attrs(k, hint, err)}>'
                f'<span>{bd(label)}{star}</span></label><span class="ds-help" id="h-{k}">{note(hint.split(" · ")[0])} {bd(hint.split(" · ")[1])}</span>{e}</div>')
    raise ValueError(kind)

def disclaimer(L):
    return f'<div class="disc" id="disclaimer"><p>{esc(DISCLAIMER[L])}</p>{note(LEGAL + " (PF-02)")}</div>'

def group(gid, L, hl, vals, errs, name=None):
    name = name or dict(GROUPS)[gid][0 if L == 'ar' else 1]
    inner = ''.join(field(fd, L, vals.get(fd['k']), errs.get(fd['k'])) for fd in FD if fd['g'] == gid)
    if gid == 'consent':
        inner += disclaimer(L)
    return f'<section class="ds-card fr-group" aria-labelledby="g-{gid}"><h{hl} id="g-{gid}">{esc(name)}</h{hl}>{inner}</section>'

def summary(errs, L, hl, step=False):
    n = len(errs)
    word = (('خطأ واحد' if n == 1 else 'خطأين' if n == 2 else f'{n} أخطاء' if n <= 10 else f'{n} خطأً') if L == 'ar' else ('1 error' if n == 1 else f'{n} errors'))
    head = (MSGS[L]['headB'] if step else MSGS[L]['headA']) + ' ' + word
    items = ''.join(f'<li><a href="#{first_id(k)}">{esc(m)}</a></li>' for k, m in errs.items())
    return f'<div class="ds-alert ds-alert--danger esum" role="alert" tabindex="-1" aria-labelledby="esum-h"><h{hl} class="esum-h" id="esum-h">{esc(head)}</h{hl}><ul>{items}</ul></div>'

def review(L, hl, vals):
    out = ''
    for i, (names, gids) in enumerate(STEPS[:4]):
        nm = names[0 if L == 'ar' else 1]
        rows = ''.join(f'<div><dt>{esc(fd["lab"][0 if L == "ar" else 1])}</dt><dd dir="auto">{esc(vals.get(fd["k"]) or "—")}</dd></div>' for fd in FD if fd['g'] in gids)
        edit = tr(L, 'تعديل', 'Edit')
        out += (f'<div class="rv"><div class="rv-h"><h{hl + 1}>{esc(nm)}</h{hl + 1}><button type="button" class="{B_OUT}" data-goto="{i}" aria-label="{esc(edit)}: {esc(nm)}">{edit}</button></div>'
                f'<dl class="kv">{rows}</dl></div>')
    title = tr(L, 'راجع طلبك قبل الإرسال', 'Review your application before you submit')
    return f'<section class="ds-card fr-group" aria-labelledby="g-review"><h{hl} id="g-review">{esc(title)}</h{hl}><div class="review">{out}</div></section>'

def form_html(L, variant, hl, vals=None, errs=None, banner='', step=0):
    vals, errs = vals or {}, errs or {}
    msgs = esc(json.dumps(MSGS[L], ensure_ascii=False))
    action = f'{L}-success.html'
    submit = tr(L, 'إرسال الطلب', 'Submit application')
    if variant == 'A':
        top = (summary(errs, L, hl) if errs else '') + banner
        groups = ''.join(group(g, L, hl, vals, errs) for g, _ in GROUPS)
        return (f'<form class="app" action="{action}" method="post" novalidate data-variant="A" data-hl="{hl}" data-msgs="{msgs}">{top}{groups}'
                f'<button type="submit" class="{B_PRI} ds-btn--lg fr-block">{submit}</button></form>')
    names = [s[0][0 if L == 'ar' else 1] for s in STEPS]
    secs = ''
    for i, (_, gids) in enumerate(STEPS):
        inner = (summary(errs, L, hl, step=True) if (errs and i == step) else '') + (banner if i == step else '')
        for g in gids:
            if g == 'review':
                inner += review(L, hl, vals)
            elif g == 'consent':
                inner += group('consent', L, hl, vals, errs, name=tr(L, 'الإقرار', 'Consent'))
            else:
                inner += group(g, L, hl, vals, errs)
        prev = f'<button type="button" class="{B_OUT}" data-nav="prev">{tr(L, "السابق", "Back")}</button>' if i else ''
        nxt = (f'<button type="button" class="{B_PRI}" data-nav="next">{tr(L, "التالي", "Next")}</button>' if i < len(STEPS) - 1
               else f'<button type="submit" class="{B_PRI}">{submit}</button>')
        secs += f'<div class="step" data-step="{i + 1}"{"" if i == step else " hidden"}>{inner}<div class="nav">{prev}{nxt}</div></div>'
    bars = ''.join(f'<li{" class=" + chr(34) + "on" + chr(34) if j <= step else ""}></li>' for j in range(len(STEPS)))
    ptxt = MSGS[L]['step'].replace('{i}', str(step + 1)).replace('{n}', str(len(STEPS))).replace('{s}', names[step])
    prog = f'<div class="prog"><p class="prog-t" tabindex="-1" aria-live="polite">{esc(ptxt)}</p><ol class="bars" aria-hidden="true">{bars}</ol></div>'
    return (f'<form class="app steps" action="{action}" method="post" novalidate data-variant="B" data-hl="{hl}" data-msgs="{msgs}" '
            f'data-steps="{esc(json.dumps(names, ensure_ascii=False))}">{prog}{secs}</form>')

def form_intro(L, faq_href):
    return (f'<p>{esc(tr(L, "الحقول المطلوبة مُعلَّمة بـ", "Required fields are marked"))} <b aria-hidden="true">*</b><span class="vh">{tr(L, "نجمة", "with an asterisk")}</span>. '
            f'{esc(tr(L, "يمكنك الكتابة بالعربية أو الإنجليزية.", "You can write in Arabic or English."))}</p>'
            f'<p><a class="{B_LINK}" href="{faq_href}">{esc(tr(L, "لديك سؤال؟ الأسئلة الشائعة", "Have a question? Read the FAQ"))}</a></p>')

# ---------------------------------------------------------------- public: the franchise page (docs/franchise/02 §1 — 13 sections + mid CTA)
def sec_hero(L):
    return (f'<section class="hero" id="top" data-hide-sticky aria-labelledby="h1"><div class="ds-container hero-g"><div>'
            f'<span class="eyebrow"{en_attr(L)}>FRANCHISE &amp; PARTNERSHIPS</span>'
            f'<h1 id="h1">{esc(H1[L])}</h1><p class="lead ds-reading">{esc(LEAD[L])}</p>'
            + notes(tr(L, 'نص اتجاه من الـOwner — ليس التزامًا قانونيًا', 'Owner direction copy — not a legal commitment')) +
            f'<div class="fr-actions"><a class="{B_PRI} ds-btn--lg" href="#apply" data-cta="hero">{esc(CTA1[L])}</a>'
            f'<a class="{B_OUT} ds-btn--lg" href="#who" data-cta="hero-secondary">{esc(CTA2[L])}</a></div></div>'
            + media(L, tr(L, 'صورة حقيقية معتمدة: DRIVE أو HOUSE أو المكان (PF-07)', 'Approved real photography: DRIVE, HOUSE or the store (PF-07)'), 'fr-wide') +
            '</div></section>')

def sec_who(L):
    dl = ''.join(f'<div><dt>{esc(a)}</dt><dd>{esc(b)}</dd></div>' for a, b in FACTS[L])
    intro = tr(L, '<b lang="en">SHELTER COFFEE</b> (شلتر كوفي) علامة قهوة انطلقت من إربد، الأردن، عام 2019.',
               '<b>SHELTER COFFEE</b> (<span lang="ar" dir="rtl">شلتر كوفي</span>) is a coffee brand born in Irbid, Jordan, in 2019.')
    return (f'<section class="sec" id="who" aria-labelledby="who-h"><div class="ds-container"><h2 id="who-h">{tr(L, "من هي SHELTER؟", "Who is SHELTER?")}</h2>'
            f'<p class="lead ds-reading">{intro}</p><dl class="facts">{dl}</dl>'
            f'<p class="ds-empty ds-reading">{bd(tr(L, "فقرة قصة SHELTER — MISSING — OWNER INPUT REQUIRED (PO-017)", "SHELTER story paragraph — MISSING — OWNER INPUT REQUIRED (PO-017)"))}</p>'
            f'<a class="{B_LINK}" href="#experiences" data-cta="who-secondary">{esc(CTA3[L])}</a></div></section>')

def sec_experiences(L):
    items = ''
    for k in ('DRIVE', 'HOUSE'):
        nm = f'SHELTER COFFEE {k}'
        items += (f'<article aria-labelledby="x-{k.lower()}">' + media(L, tr(L, f'صورة حقيقية معتمدة لـ {nm} (PF-07)', f'Approved real photography of {nm} (PF-07)')) +
                  f'<h3 id="x-{k.lower()}"{en_attr(L)}>{nm}</h3>'
                  f'<p class="ds-empty">{bd(tr(L, "وصف قصير معتمد — PENDING", "Approved short description — PENDING"))}</p></article>')
    return (f'<section class="sec alt" id="experiences" aria-labelledby="exp-h"><div class="ds-container"><h2 id="exp-h">{tr(L, "نماذج تجربة SHELTER الحالية", "Today’s SHELTER experiences")}</h2>'
            f'<p class="ds-reading">{esc(tr(L, "تجارب العملاء الحالية لـ SHELTER — وليست باقات فرنشايز.", "SHELTER’s current customer experiences — not franchise packages."))}</p>'
            f'<div class="exp">{items}</div></div></section>')

def sec_why(L):
    rows = ''
    for i, (title, items) in enumerate(WHY[L], 1):
        lis = ''.join(f'<li>{I_CHECK}<span>{esc(x)}</span></li>' for x in items)
        rows += (f'<div class="why-row"><h3 class="why-h"><button type="button" class="acc" id="why-b{i}" aria-expanded="true" aria-controls="why-p{i}">'
                 f'<span><span class="num">0{i}</span>{esc(title)}</span>{I_DOWN}</button></h3>'
                 f'<div class="why-p" id="why-p{i}">' + media(L, tr(L, 'صورة تحريرية معتمدة', 'Approved editorial photography')) +
                 f'<div><ul class="ticks">{lis}</ul><p class="ds-empty">{bd(tr(L, "نص تحريري قصير — " + PV, "Short editorial copy — " + PV))}</p></div></div></div>')
    return (f'<section class="sec" id="why" aria-labelledby="why-h"><div class="ds-container"><h2 id="why-h">{tr(L, "لماذا تصبح شريكًا مع SHELTER؟", "Why partner with SHELTER?")}</h2>'
            + notes(PV + ' (PF-04)', tr(L, 'لا يُنشر أي بند كالتزام قبل التحقق', 'No item is published as a commitment before verification')) +
            f'<div class="why">{rows}</div></div></section>')

def sec_system(L):
    lis = ''.join(f'<li>{esc(x)}</li>' for x in SYSTEM[L])
    return (f'<section class="sec alt" id="system" aria-labelledby="sys-h"><div class="ds-container"><h2 id="sys-h">{tr(L, "أكثر من مجرد اسم على الواجهة", "More than a name on the storefront")}</h2>'
            f'<p class="lead ds-reading">{esc(tr(L, "الشراكة مع SHELTER منظومة متكاملة، وليست ترخيص شعار فقط.", "Partnering with SHELTER means a complete system, not just a logo licence."))}</p>'
            + notes(PV) + f'<ol class="sys">{lis}</ol></div></section>')

def mid_cta(L):
    return (f'<div class="ds-container"><div class="ds-card fr-midband" data-hide-sticky><p>{esc(tr(L, "قدّم طلب اهتمام، وسيراجع فريق SHELTER طلبك.", "Send an expression of interest and the SHELTER team will review it."))}</p>'
            f'<a class="{B_PRI} ds-btn--lg" href="#apply" data-cta="mid">{esc(CTA1[L])}</a></div></div>')

def sec_criteria(L):
    lis = ''.join(f'<li>{I_CHECK}<span>{esc(x)}</span></li>' for x in CRITERIA[L])
    return (f'<section class="sec" id="criteria" aria-labelledby="crit-h"><div class="ds-container"><h2 id="crit-h">{tr(L, "ما الذي نبحث عنه في الشريك؟", "Who we’re looking for")}</h2>'
            + notes(tr(L, 'اتجاه الـOwner (معايير عامة فقط)', 'Owner direction (high-level criteria only)'), PV) + f'<ul class="crit">{lis}</ul></div></section>')

def sec_journey(L):
    lis = ''.join(f'<li id="step-{i}"><span class="n" aria-hidden="true">{i}</span><span class="t"><span class="vh">{tr(L, "الخطوة", "Step")} {i}: </span>{esc(x)}</span></li>'
                  for i, x in enumerate(JOURNEY[L], 1))
    return (f'<section class="sec alt" id="journey" aria-labelledby="jr-h"><div class="ds-container"><h2 id="jr-h">{tr(L, "رحلة الشراكة", "The partnership journey")}</h2>'
            f'<p class="ds-reading">{esc(tr(L, "خطوات المتقدم من طلب الاهتمام إلى الافتتاح.", "The applicant’s steps, from expression of interest to opening."))}</p>'
            + notes(PV + ' (PF-05)') + f'<ol class="tl">{lis}</ol></div></section>')

def sec_support(L):
    lis = ''.join(f'<li{" class=" + chr(34) + "link" + chr(34) if i in (5, 6) else ""}>{esc(x)}</li>' for i, x in enumerate(SUPPORT[L], 1))
    return (f'<section class="sec" id="support" aria-labelledby="sp-h"><div class="ds-container"><h2 id="sp-h">{tr(L, "كيف ندعم شركاءنا", "How we support partners")}</h2>'
            f'<p class="ds-reading">{esc(tr(L, "ما تقدمه SHELTER للشريك. «التجهيز» و«التدريب» هنا يقابلان خطوة «التجهيز والتدريب» في رحلة الشراكة.", "What SHELTER provides to partners. “Setup” and “Training” here match the “Setup & training” step of the partnership journey."))}</p>'
            f'<a class="{B_LINK}" href="#step-8">{esc(tr(L, "انتقل إلى خطوة «التجهيز والتدريب»", "Go to the “Setup & training” step"))}</a>'
            + notes(PV + ' (PF-05)') + f'<ol class="stages">{lis}</ol></div></section>')

def sec_markets(L):
    return (f'<section class="sec alt" id="markets" aria-labelledby="mk-h"><div class="ds-container"><h2 id="mk-h">{tr(L, "أسواق النمو", "Where we grow")}</h2>'
            f'<p class="lead ds-reading">{esc(tr(L, "نبدأ من الأردن، ونتطلع للنمو في أسواق جديدة وفق خطة مدروسة.", "We start from Jordan and look to grow into new markets through a considered plan."))}</p>'
            + notes('PENDING — PF-09', tr(L, 'نص عام: بلا خريطة وبلا تسمية أسواق', 'General copy only: no map, no named markets')) + '</div></section>')

def sec_faq(L):
    i = 0 if L == 'ar' else 1
    items = ''.join(f'<details class="ds-card qa" id="faq-{n}"><summary>{esc((ar, en)[i][0])}</summary><div class="a"><p>{esc((ar, en)[i][1])}</p></div></details>'
                    for n, vis, ar, en in FAQ if vis)
    return (f'<section class="sec" id="faq" aria-labelledby="faq-h"><div class="ds-container"><h2 id="faq-h">{tr(L, "الأسئلة الشائعة", "Frequently asked questions")}</h2>'
            f'<p class="ds-reading">{esc(tr(L, "تُعرض هنا الأسئلة المعتمدة فقط.", "Only approved answers are shown here."))}</p><div class="faq">{items}</div></div></section>')

def sec_apply(L, variant):
    return (f'<section class="sec alt" id="apply" data-hide-sticky aria-labelledby="apply-h"><div class="ds-container fr-narrow">'
            f'<span class="eyebrow"{en_attr(L)}>SHELTER PARTNERSHIP APPLICATION</span>'
            f'<h2 id="apply-h">{tr(L, "طلب الشراكة", "Partnership application")}</h2>{form_intro(L, "#faq")}{form_html(L, variant, 3)}</div></section>')

def sec_final(L):
    return (f'<section class="sec" id="final" data-hide-sticky aria-labelledby="fin-h"><div class="ds-container final-g">'
            + media(L, tr(L, 'صورة ختامية معتمدة (PF-07)', 'Approved closing photography (PF-07)'), 'fr-wide') +
            f'<div><h2 id="fin-h">{tr(L, "ابدأ رحلة الشراكة مع SHELTER", "Start your partnership journey with SHELTER")}</h2>'
            f'<div class="fr-actions"><a class="{B_PRI} ds-btn--lg" href="#apply" data-cta="final">{esc(CTA1[L])}</a></div></div></div></section>')

def footer(L):
    links = tr(L, ['من نحن', 'الفروع', 'المنيو', 'Coffee Knowledge', 'التواصل'], ['About', 'Locations', 'Menu', 'Coffee Knowledge', 'Contact'])
    return (f'<footer class="foot"><div class="ds-container"><p><b>{bd(tr(L, "الـFooter العام للموقع — PLACEHOLDER", "Global site footer — PLACEHOLDER"))}</b></p>'
            f'<p class="ds-caption">{bd(tr(L, "روابط داخلية (الوجهات حسب الـIA المعتمدة):", "Internal links (destinations per the approved IA):"))}</p>'
            f'<ul class="ds-caption">{"".join(f"<li>{esc(x)}</li>" for x in links)}</ul></div></footer>')

def annotation(L):
    i = 0 if L == 'ar' else 1
    hidden = ''.join(f'<li><span class="q">{n}. {bd((ar, en)[i][0])}</span><span>{bd((ar, en)[i][1])}</span></li>' for n, vis, ar, en in FAQ if not vis)
    alts = (f'<li>{tr(L, "عنوان H1 (ب)", "H1 option B")}: <b>{esc(H1_B[L])}</b></li><li>{tr(L, "CTA ثانوي (ب)", "Secondary CTA option B")}: <b>{esc(CTA2_B[L])}</b></li>')
    return (f'<aside class="ds-container" aria-labelledby="anno-h"><div class="wf-note fr-anno">'
            f'<h2 id="anno-h">{bd(tr(L, "ملاحظة Wireframe — مخفي حتى التحقق (ليس جزءًا من الصفحة العامة)", "Wireframe annotation — hidden until verified (not part of the public page)"))}</h2>'
            f'<h3>{esc(tr(L, "أسئلة شائعة مخفية حتى التحقق", "FAQ items hidden until verified"))} — <bdi>{PV}</bdi></h3>'
            f'<p>{bd(tr(L, "حالة المحتوى ≠ APPROVED ← لا يُعرض في الصفحة المنشورة (CMS). المسودات من docs/franchise/02 §4.", "Content status ≠ APPROVED → not rendered on the published page (CMS). Drafts from docs/franchise/02 §4."))}</p>'
            f'<ul class="anno-hidden">{hidden}</ul>'
            f'<h3>{bd(tr(L, "بدائل للاختبار بعد الإطلاق (إذا أراد الـOwner)", "Alternatives to test after launch (if the owner wants)"))}</h3><ul>{alts}</ul></div></aside>')

def build_page(L, variant):
    other = f'{"en" if L == "ar" else "ar"}-franchise.html'
    main = (sec_hero(L) + sec_who(L) + sec_experiences(L) + sec_why(L) + sec_system(L) + mid_cta(L) + sec_criteria(L) + sec_journey(L)
            + sec_support(L) + sec_markets(L) + sec_faq(L) + sec_apply(L, variant) + sec_final(L))
    sticky = f'<div class="sticky" hidden><a class="{B_PRI} ds-btn--lg fr-block" href="#apply" data-cta="sticky">{esc(CTA1[L])}</a></div>'
    extra = tr(L, f'النموذج المضمَّن: {variant} (انظر README: قرار A/B).', f'Embedded form: {variant} (see README: A/B decision).')
    body = site_header(L, other) + f'<main>{main}</main>' + footer(L) + annotation(L) + sticky + f'<div class="ds-container">{legend(L, extra)}</div>'
    title = tr(L, 'فرنشايز SHELTER COFFEE — كن شريكًا مع شلتر كوفي', 'SHELTER COFFEE Franchise & Partnerships')
    state = tr(L, 'صفحة الفرنشايز والشراكات · /ar/franchise/', 'Franchise & partnerships page · /en/franchise/')
    write(f'{L}-franchise.html', page(body, title, state, L, 'has-sticky', (PAGE_JS, FORM_JS)))

# ---------------------------------------------------------------- public: standalone form state pages (section 11 alone, for state review and the A/B test)
def form_page(name, L, variant, state, **kw):
    other = name.replace(f'{L}-', f'{"en" if L == "ar" else "ar"}-', 1)
    intro = (f'<div class="intro"><span class="eyebrow"{en_attr(L)}>SHELTER PARTNERSHIP APPLICATION</span>'
             f'<h1>{tr(L, "طلب الشراكة مع SHELTER COFFEE", "Partnership application — SHELTER COFFEE")}</h1>{form_intro(L, f"{L}-franchise.html#faq")}</div>')
    body = site_header(L, other) + f'<main class="ds-container fr-narrow">{intro}{form_html(L, variant, 2, **kw)}{legend(L)}</main>'
    vname = tr(L, 'صفحة واحدة', 'single page') if variant == 'A' else tr(L, '5 خطوات قصيرة', '5 short steps')
    title = tr(L, f'طلب الشراكة — نموذج {variant}', f'Partnership application — form {variant}')
    write(name, page(body, title, f'{tr(L, "القسم 11 (#apply) منفردًا", "Section 11 (#apply) standalone")} · {variant} ({vname}) · {state}', L, '', (FORM_JS,)))

def build_forms():
    for L in ('ar', 'en'):
        F, i = FILLED[L], (0 if L == 'ar' else 1)
        form_page(f'{L}-form-a.html', L, 'A', tr(L, 'فارغ (تفاعلي)', 'empty (interactive)'))
        ea = {'email': MSGS[L]['email'], 'city': FIELD['city']['msg'][i], 'location': FIELD['location']['msg'][i], 'consent': FIELD['consent']['msg'][i]}
        form_page(f'{L}-form-a-errors.html', L, 'A', tr(L, 'أخطاء بعد الإرسال (ملخص + أخطاء داخلية)', 'errors after submit (summary + inline)'),
                  vals=dict(F, email='applicant1@example', city='', location=None, consent=False), errs=ea)
        net = (f'<div class="ds-alert ds-alert--danger fr-alert" role="alert"><h2>{tr(L, "تعذّر إرسال الطلب: انقطع الاتصال", "We couldn’t send your application: the connection was lost")}</h2>'
               f'<p>{esc(tr(L, "لم تُفقد بياناتك — ما زالت معبأة في النموذج. تحقق من الاتصال ثم أعد المحاولة.", "Your answers are still filled in. Check your connection and try again."))}</p>'
               f'<button type="submit" class="{B_PRI}">{tr(L, "إعادة المحاولة", "Try again")}</button></div>')
        form_page(f'{L}-form-a-network.html', L, 'A', tr(L, 'خطأ شبكة — المدخلات محفوظة', 'network error — inputs kept'), vals=F, banner=net)
        form_page(f'{L}-form-b.html', L, 'B', tr(L, 'الخطوة 1 (تفاعلي)', 'step 1 (interactive)'), step=0)
        for s in (1, 2, 3, 4):
            form_page(f'{L}-form-b-s{s + 1}.html', L, 'B', tr(L, f'الخطوة {s + 1}', f'step {s + 1}'), step=s, vals=F)
        eb = {'phone': FIELD['phone']['msg'][i], 'email': MSGS[L]['email']}
        form_page(f'{L}-form-b-errors.html', L, 'B', tr(L, 'أخطاء الخطوة 1', 'step 1 errors'), step=0, vals=dict(F, phone='', email='applicant1@example'), errs=eb)
        form_page(f'{L}-form-b-network.html', L, 'B', tr(L, 'خطأ شبكة في خطوة الإرسال — المدخلات محفوظة', 'network error on the submit step — inputs kept'), step=4, vals=F, banner=net)

def build_success():
    for L in ('ar', 'en'):
        other = f'{"en" if L == "ar" else "ar"}-success.html'
        inner = (f'<div class="ds-card center fr-stack" role="status"><h1 tabindex="-1">{esc(tr(L, "شكرًا لاهتمامك بالشراكة مع SHELTER COFFEE", "Thank you for your interest in partnering with SHELTER COFFEE"))}</h1>'
                 f'<div><p>{tr(L, "رقم الطلب", "Application number")}</p><p class="appno"><span dir="ltr">{APPNO}</span></p>{sample(L)}</div>'
                 f'<p>{esc(tr(L, "سيخضع طلبك للمراجعة.", "Your application will be reviewed."))} {esc(NEXT_STAGE[L])}</p>'
                 f'<p class="ds-muted">{esc(tr(L, "احتفظ برقم الطلب للرجوع إليه.", "Keep this number for your reference."))}</p>'
                 f'{disclaimer(L)}'
                 f'<a class="{B_PRI}" href="{L}-franchise.html">{tr(L, "العودة إلى صفحة الشراكة", "Back to the partnership page")}</a>'
                 f'<a class="{B_OUT}" href="index.html">{tr(L, "العودة إلى الموقع", "Back to the website")}</a></div>'
                 f'<p class="ds-caption">{esc(tr(L, "لا تظهر أي بيانات شخصية في هذه الصفحة أو في الرابط. رقم الطلب يُولَّد في الخادم وبلا بيانات شخصية.", "No personal data appears on this page or in its URL. The number is generated server-side and contains no personal data."))}</p>')
        body = site_header(L, other) + f'<main class="ds-container fr-narrow">{inner}{legend(L)}</main>'
        write(f'{L}-success.html', page(body, tr(L, 'تم استلام طلب الشراكة', 'Partnership application received'), 'SUCCESS', L))

# ---------------------------------------------------------------- Owner Dashboard · الشراكات (Arabic only · owner only in V1 — M30)
STAGES = [('received', 'تم الاستلام'), ('qualified', 'مؤهل مبدئيًا'), ('meeting', 'اجتماع'), ('market_review', 'مراجعة السوق'), ('site_review', 'مراجعة الموقع'),
          ('approved', 'موافق'), ('contract', 'مرحلة العقد'), ('closed', 'مغلق'), ('archived', 'مؤرشف')]
STAGE_ACTIVE = [n for c, n in STAGES if c != 'archived']
COLS = ['المتقدم', 'الدولة', 'المدينة', 'السوق المقترح', 'تاريخ الطلب', 'المرحلة', 'آخر تحديث']
SEARCH_BY = ['المتقدم', 'الهاتف', 'البريد', 'الدولة', 'المدينة', 'السوق', 'رقم الطلب']
FILTERS = ['المرحلة', 'الدولة', 'المدينة', 'تاريخ الطلب', 'السوق']
CHANNELS = ['مكالمة فيديو', 'مكالمة هاتفية', 'اجتماع حضوري']
MSTAT = ['مجدول', 'تم الاجتماع', 'مؤجل', 'ملغى']
RANGES = ['اليوم', 'آخر 7 أيام', 'آخر 30 يوم', 'هذا الشهر', 'كل الوقت']
CTRY = [f'دولة تجريبية {i}' for i in (1, 2, 3)]
CITY = [f'مدينة تجريبية {i}' for i in (1, 2, 3, 4)]
MKT = [f'سوق تجريبي {i}' for i in (1, 2, 3)]
ROWS = []
for _i in range(1, 13):
    ROWS.append(dict(n=f'متقدم تجريبي {_i}', id=f'FR-2026-{126 - _i:05d}', country=CTRY[(_i - 1) % 3], city=CITY[(_i - 1) % 4], market=MKT[(_i - 1) % 3],
                     date=f'2026-09-{30 - (_i - 1) * 2:02d}', upd='2026-10-01' if _i <= 4 else f'2026-09-{min(30, 33 - (_i - 1) * 2):02d}',
                     st=STAGE_ACTIVE[0] if _i <= 3 else STAGE_ACTIVE[1 + (_i - 4) % 7], unread=_i <= 3, prev=_i in (1, 7)))
ROWS[0]['st'] = 'مراجعة السوق'  # the application opened in quick view / full view (it was read; stage moved by the owner)
ROWS[0]['unread'] = False
ROWS[3]['unread'] = True
MEETS = [dict(n='متقدم تجريبي 6', d='2026-10-05', t='11:00', ch=CHANNELS[0], st='مجدول', stage='اجتماع'),
         dict(n='متقدم تجريبي 1', d='2026-10-07', t='13:30', ch=CHANNELS[2], st='مجدول', stage='مراجعة السوق'),
         dict(n='متقدم تجريبي 4', d='2026-09-28', t='10:00', ch=CHANNELS[1], st='تم الاجتماع', stage='مؤهل مبدئيًا'),
         dict(n='متقدم تجريبي 11', d='2026-09-24', t='16:00', ch=CHANNELS[0], st='ملغى', stage='اجتماع')]
ARCH = [dict(n=f'متقدم تجريبي {20 + _i}', id=f'FR-2026-{60 - _i:05d}', at=f'2026-09-{20 - _i * 2:02d}', before=['مغلق', 'مغلق', 'تم الاستلام', 'مؤهل مبدئيًا'][_i]) for _i in range(4)]
D_NAV = [('overview', 'نظرة عامة'), ('list', 'الطلبات'), ('meetings', 'الاجتماعات'), ('archived', 'الأرشيف'), ('settings', 'الإعدادات')]
OWNER = 'المالك (Owner)'
NEWC = sum(r['unread'] for r in ROWS)

def badge(s, extra=''):
    return f'<span class="ds-badge{(" " + extra) if extra else ""}">{esc(s)}</span>'

def opt(v, sel_):
    return f'<option{" selected" if sel_ else ""}>{esc(v)}</option>'

def sel(id_, label, options, selected=None, first=None):
    o = (f'<option value="">{esc(first)}</option>' if first else '') + ''.join(opt(x, x == selected) for x in options)
    return f'<div class="ds-field"><label class="ds-label" for="{id_}">{esc(label)}</label><select class="ds-select" id="{id_}">{o}</select></div>'

def appno(id_):
    return f'<span dir="ltr">{id_}</span> <span class="wf-note sample">{DSAMPLE}</span>'

def dpage(k, name, active, state, main, after=''):
    cnt = f'<span class="ds-badge">{NEWC} جديد</span>' if k == 'd' else f'<span class="ds-badge">{NEWC}<span class="vh"> جديد</span></span>'
    links = ''.join(f'<a class="ds-chip" href="{k}-{key}.html"{" aria-current=" + chr(34) + "true" + chr(34) if key == active else ""}>{esc(t)}{cnt if key == "list" else ""}</a>' for key, t in D_NAV)
    if k == 'd':
        top = (f'<header class="dtop"><span class="logo" lang="en">SHELTER OWNER DASHBOARD</span><span class="dtop-r"><span class="wf-note sample">{DSAMPLE}</span>'
               f'<a class="{B_OUT}" href="d-list.html?read=unread">{I_BELL} طلبات جديدة {badge(str(NEWC))}</a><span>{OWNER}</span></span></header>')
        body = (top + f'<div class="dshell"><div class="dside"><p class="dside-h">الشراكات</p><p class="dside-s" lang="en">Franchise &amp; Partnerships</p>'
                f'<nav class="dnav" aria-label="أقسام الشراكات">{links}</nav></div><main class="dmain">{main}</main></div>')
    else:
        top = (f'<header class="mtop"><button type="button" class="{B_ICON}" aria-label="قائمة الـDashboard">{I_MENU}</button><span class="logo">الشراكات</span>'
               f'<a class="{B_ICON}" href="m-list.html?read=unread" aria-label="{NEWC} طلبات جديدة">{I_BELL}</a></header>')
        body = top + f'<nav class="mtabs" aria-label="أقسام الشراكات">{links}</nav><main class="mmain">{main}</main>'
    body += after + ('<p class="dlegend">LOW-FI WIREFRAME — بلا هوية بصرية · نظام تصميم واحد (tokens.css + wireframe-kit.css — M34). كل البيانات تجريبية (SAMPLE). '
                     'المراحل مقترحة وقابلة للإعداد، وFranchise Master يفوز (TD-FR-01). V1: لوحة التحكم للـOwner فقط، والتحقق من الصلاحية في الخادم (M30).</p>')
    write(name, page(body, f'الشراكات — {state}', f'OWNER DASHBOARD · الشراكات · {"DESKTOP" if k == "d" else "MOBILE"} · {state} · SAMPLE', 'ar'))

def dialog(k, did, title, body, foot, close):
    box = 'fr-drawer' if k == 'd' else 'ds-sheet fr-dlg'
    return (f'<div class="ds-overlay" aria-hidden="true"></div><div class="{box}" role="dialog" aria-modal="true" aria-labelledby="{did}">'
            f'<div class="dlg-h"><h2 id="{did}">{title}</h2><a class="{B_ICON} x" href="{close}" aria-label="إغلاق">{I_X}</a></div>'
            f'<div class="dlg-b">{body}</div>{f"<div class=dlg-f>{foot}</div>" if foot else ""}</div>')

def simple_table(caption, cols, rows):
    h = ''.join(f'<th scope="col">{c}</th>' for c in cols)
    b = ''.join('<tr>' + ''.join((f'<th scope="row">{c}</th>' if i == 0 else f'<td>{c}</td>') for i, c in enumerate(r)) + '</tr>' for r in rows)
    return f'<div class="fr-tbl" role="region" tabindex="0" aria-label="{esc(re.sub("<[^>]+>", "", caption))}"><table class="ds-table"><caption>{caption}</caption><thead><tr>{h}</tr></thead><tbody>{b}</tbody></table></div>'

def panel(pid, title, inner, tag='section'):
    return f'<{tag} class="ds-card dpanel" aria-labelledby="{pid}"><h2 id="{pid}">{title}</h2>{inner}</{tag}>'

def overview(k):
    counts = {s: sum(r['st'] == s for r in ROWS) for s in STAGE_ACTIVE}
    kpis = [('الطلبات الجديدة', NEWC, f'{k}-list.html?read=unread'), ('إجمالي الطلبات', len(ROWS), f'{k}-list.html'),
            ('اجتماعات قادمة', sum(m['st'] == 'مجدول' for m in MEETS), f'{k}-meetings.html'), ('في مرحلة العقد', counts['مرحلة العقد'], f'{k}-list.html?stage=contract')]
    cards = ''.join(f'<li><a class="ds-card kpi" href="{h}"><span>{esc(l)}</span><span class="kpi-n">{n}</span></a></li>' for l, n, h in kpis)
    dist = ''.join(f'<li><span>{badge(s)}</span><a class="{B_LINK}" href="{k}-list.html?stage={c}">{counts[s]} طلبات</a></li>' for c, s in STAGES if s in counts)
    def tally(key, vals):
        return '<ul class="rows">' + ''.join(f'<li><span>{esc(v)}</span><span class="sub">{sum(r[key] == v for r in ROWS)}</span></li>' for v in vals) + '</ul>'
    new = ''.join(f'<li><span><a class="{B_LINK}" href="{k}-application.html">{esc(r["n"])}</a> {badge("جديد")}</span><span class="sub">{esc(r["country"])} · {esc(r["market"])} · {r["date"]}</span></li>'
                  for r in ROWS if r['unread'])
    meets = ''.join(f'<li><span>{esc(m["n"])} — {esc(m["ch"])}</span><span class="sub nowrap">{m["d"]} · {m["t"]}</span></li>' for m in MEETS if m['st'] == 'مجدول')
    main = (f'<div class="ptitle"><h1>نظرة عامة — الشراكات</h1><span class="wf-note sample">{DSAMPLE}</span></div>'
            f'<div class="toolbar">{sel("range", "الفترة", RANGES, "كل الوقت")}<p class="sub">بطاقات تشغيلية فقط — بلا رسوم بيانية ولا مقارنات.</p></div>'
            f'<h2 class="vh">ملخص الطلبات</h2><ul class="kpis">{cards}</ul>'
            + panel('p-new', 'تحتاج انتباهك: طلبات جديدة', f'<ul class="rows">{new}</ul><a class="{B_OUT}" href="{k}-list.html?read=unread">عرض كل الطلبات الجديدة</a>')
            + panel('p-dist', 'التوزيع على المراحل', f'<ul class="rows">{dist}</ul>')
            + panel('p-geo', 'الدول والمدن والأسواق المطلوبة', '<p class="sub">جدول بسيط من الطلبات — لا يعني توفر أي سوق.</p>'
                    f'<div class="three"><div><h3>الدول</h3>{tally("country", CTRY)}</div><div><h3>المدن</h3>{tally("city", CITY)}</div><div><h3>الأسواق</h3>{tally("market", MKT)}</div></div>')
            + panel('p-meet', 'الاجتماعات القادمة', f'<ul class="rows">{meets}</ul><a class="{B_OUT}" href="{k}-meetings.html">كل الاجتماعات</a>'))
    dpage(k, f'{k}-overview.html', 'overview', 'نظرة عامة', main)

def list_toolbar(k):
    q = (f'<div class="ds-field grow"><label class="ds-label" for="q">بحث</label><input class="ds-input" type="search" id="q" dir="auto" aria-describedby="h-q" placeholder="{esc("، ".join(SEARCH_BY))}">'
         f'<span class="ds-help" id="h-q">يبحث في: {esc(" · ".join(SEARCH_BY))}</span></div>')
    return f'<div class="toolbar">{q}<a class="{B_OUT}" href="{k}-list-filters.html">الفلاتر {badge("0")}</a></div>'

def stage_chips(k):
    chips = f'<a class="ds-chip" href="{k}-list.html" aria-current="true">الكل</a>' + ''.join(f'<a class="ds-chip" href="{k}-list.html?stage={c}">{esc(s)}</a>' for c, s in STAGES[:8])
    return f'<nav class="chips" aria-label="حسب المرحلة"><b>حسب المرحلة:</b>{chips}</nav>'

def saved_chips(k):
    return (f'<div class="chips" id="saved"><b>الفلاتر المحفوظة:</b><a class="ds-chip" href="{k}-list.html?saved=1">سوق تجريبي 1 — مراجعة السوق</a>'
            f'<a class="ds-chip" href="{k}-list.html?saved=2">طلبات هذا الشهر</a><span class="wf-note">أمثلة</span></div>')

def table(k):
    head = ''.join(f'<th scope="col">{esc(c)}</th>' for c in COLS) + '<th scope="col"><span class="vh">عرض سريع</span></th>'
    body = ''
    for r in ROWS:
        flags = (' ' + badge('جديد') if r['unread'] else '') + (' ' + badge('طلبات سابقة') if r['prev'] else '')
        body += (f'<tr{" class=" + chr(34) + "unread" + chr(34) if r["unread"] else ""}><th scope="row"><a class="{B_LINK}" href="{k}-application.html">{esc(r["n"])}</a>{flags}</th>'
                 f'<td>{esc(r["country"])}</td><td>{esc(r["city"])}</td><td dir="auto">{esc(r["market"])}</td><td class="nowrap">{r["date"]}</td><td>{badge(r["st"])}</td>'
                 f'<td class="nowrap">{r["upd"]}</td><td><a class="{B_ICON}" href="{k}-quickview.html" aria-label="عرض سريع: {esc(r["n"])}">{I_EYE}</a></td></tr>')
    return f'<div class="fr-tbl" role="region" tabindex="0" aria-label="الطلبات"><table class="ds-table"><caption>الطلبات — الأحدث أولًا · {DSAMPLE}</caption><thead><tr>{head}</tr></thead><tbody>{body}</tbody></table></div>'

def cards(k):
    out = ''
    for r in ROWS:
        flags = (' ' + badge('جديد') if r['unread'] else '') + (' ' + badge('طلبات سابقة') if r['prev'] else '')
        out += (f'<li class="ds-card acard{" unread" if r["unread"] else ""}"><div><p class="acard-n"><a class="{B_LINK}" href="{k}-application.html">{esc(r["n"])}</a>{flags}</p>'
                f'<p>{esc(r["country"])} · {esc(r["city"])}</p><p>السوق المقترح: <b dir="auto">{esc(r["market"])}</b></p>'
                f'<p>{badge(r["st"])} <span class="sub nowrap">تاريخ الطلب {r["date"]}</span> <span class="sub nowrap">آخر تحديث {r["upd"]}</span></p></div>'
                f'<a class="{B_ICON}" href="{k}-quickview.html" aria-label="عرض سريع: {esc(r["n"])}">{I_EYE}</a></li>')
    return f'<p class="sub">الطلبات — الأحدث أولًا · بطاقات بدل الجدول على الشاشات الصغيرة · {DSAMPLE}</p><ul class="acards">{out}</ul>'

def pager(k):
    ps = sel('ps', 'عدد الصفوف في الصفحة', ['25', '50', '100'], '50')
    jump = f'<div class="jump"><label for="jump">انتقل إلى صفحة</label><input class="ds-input" type="text" id="jump" inputmode="numeric"><button type="button" class="{B_OUT}">انتقال</button></div>'
    if k == 'd':
        nums = (f'<a class="{B_OUT} ds-btn--icon" href="#" aria-current="page">1</a>' + ''.join(f'<a class="{B_OUT} ds-btn--icon" href="#" aria-label="صفحة {i}">{i}</a>' for i in (2, 3))
                + f'<span aria-hidden="true">…</span><a class="{B_OUT} ds-btn--icon" href="#" aria-label="صفحة 6">6</a>')
        return (f'<nav class="pager" aria-label="ترقيم الصفحات"><button type="button" class="{B_OUT}" disabled>السابق</button>{nums}<a class="{B_OUT}" href="#">التالي</a>'
                f'<span class="sub">عرض 1–50 من 287 · {DSAMPLE}</span>{jump}</nav><div class="toolbar fr-stack">{ps}</div>')
    return (f'<nav class="pager" aria-label="ترقيم الصفحات"><button type="button" class="{B_OUT}" disabled>السابق</button><span class="sub">صفحة 1 من 6</span><a class="{B_OUT}" href="#">التالي</a>'
            f'{jump}</nav><div class="toolbar fr-stack">{ps}</div>')

def list_main(k):
    head = f'<div class="ptitle"><h1>الطلبات</h1><span class="sub">287 طلبًا · <span class="wf-note sample">{DSAMPLE}</span></span></div>'
    return head + list_toolbar(k) + stage_chips(k) + saved_chips(k) + (table(k) if k == 'd' else cards(k)) + pager(k)

def filters_body():
    chk = '<div class="opts">' + ''.join(f'<label class="ds-chip"><input type="checkbox" name="fl-st"{" checked" if o == "مراجعة السوق" else ""}>{esc(o)}</label>' for o in STAGE_ACTIVE) + '</div>'
    b = (f'<p class="sub">يمكن الجمع بين عدة فلاتر.</p>'
         f'<fieldset class="ds-field"><legend class="ds-label">المرحلة</legend>{chk}</fieldset>'
         + sel('fl-country', 'الدولة', CTRY, None, 'كل الدول') + sel('fl-city', 'المدينة', CITY, None, 'كل المدن') +
         '<fieldset class="ds-field"><legend class="ds-label">تاريخ الطلب</legend><div class="two"><div class="ds-field"><label class="ds-label" for="fl-from">من</label><input class="ds-input" type="date" id="fl-from"></div>'
         '<div class="ds-field"><label class="ds-label" for="fl-to">إلى</label><input class="ds-input" type="date" id="fl-to"></div></div></fieldset>'
         '<div class="ds-field"><label class="ds-label" for="fl-market">السوق</label><input class="ds-input" type="text" id="fl-market" dir="auto" value="سوق تجريبي 1"></div>'
         '<fieldset class="ds-card ds-field save"><legend class="ds-label">حفظ كفلتر محفوظ</legend><label class="ds-label" for="fl-save">اسم الفلتر</label>'
         '<input class="ds-input" type="text" id="fl-save" dir="auto" value="سوق تجريبي 1 — مراجعة السوق">'
         f'<p class="sub">يظهر في «الفلاتر المحفوظة» ويُفتح بضغطة.</p><button type="button" class="{B_OUT}">حفظ الفلتر</button></fieldset>')
    return b, f'<a class="{B_PRI}" href="#">عرض النتائج (3)</a><button type="button" class="{B_OUT}">مسح الكل</button>'

def quick_body(k):
    r = ROWS[0]
    b = (f'<p class="sub">{appno(r["id"])} · الفتح يجعله «مقروءًا» دون تغيير المرحلة.</p>'
         f'<dl class="kv"><div><dt>الدولة</dt><dd>{esc(r["country"])}</dd></div><div><dt>المدينة</dt><dd>{esc(r["city"])}</dd></div>'
         f'<div><dt>السوق المقترح</dt><dd>{esc(r["market"])}</dd></div><div><dt>نوع الاهتمام بالشراكة</dt><dd>نص تجريبي: نوع الاهتمام</dd></div>'
         f'<div><dt>حالة الموقع المقترح</dt><dd>أبحث عن موقع</dd></div><div><dt>سنوات الخبرة في الأعمال</dt><dd>6–10 سنوات</dd></div>'
         f'<div><dt>الهاتف</dt><dd dir="ltr">+962700000001</dd></div><div><dt>البريد</dt><dd dir="ltr">applicant1@example.com</dd></div>'
         f'<div><dt>المرحلة</dt><dd>{badge(r["st"])}</dd></div><div><dt>الملاحظات الداخلية</dt><dd>3 ملاحظات</dd></div></dl>'
         f'<div class="toolbar fr-stack">{sel("qv-st", "تغيير المرحلة إلى", STAGE_ACTIVE, r["st"])}<button type="button" class="{B_PRI}">حفظ المرحلة</button></div>')
    return b, f'<a class="{B_PRI}" href="{k}-application.html">فتح الطلب الكامل</a>'

AUDIT_COLS = ['المنفّذ (Actor)', 'الإجراء (Action)', 'الهدف (Target)', 'الوقت (Timestamp)', 'قبل (Before)', 'بعد (After)']

def app_main(k):
    r = ROWS[0]
    kv = lambda pairs: '<dl class="kv">' + ''.join(f'<div><dt>{esc(a)}</dt><dd>{b}</dd></div>' for a, b in pairs) + '</dl>'
    F = FILLED['ar']
    data = (kv([(FIELD[x]['lab'][0], f'<span dir="auto">{esc(F[x] or "—")}</span>') for x in ('name', 'phone', 'email', 'country', 'city', 'market', 'interest', 'years', 'expnote', 'owns', 'company', 'website', 'location', 'message')])
            + '<h3>الموافقة والإقرار</h3>' + kv([('الموافقة على معالجة البيانات', 'تمت الموافقة'), ('وقت الموافقة', '2026-09-30 10:42'), ('نسخة نص الموافقة', 'v1 · PENDING (PF-03)'),
                                                   ('نسخة الـDisclaimer المعروضة', f'v1 · {LEGAL} (PF-02)')]))
    market = (kv([('الدولة', esc(r['country'])), ('المدينة', esc(r['city'])), ('السوق أو المنطقة المهتم بها', esc(r['market'])), ('حالة الموقع المقترح', 'أبحث عن موقع')])
              + '<p class="sub">ذكر السوق في الطلب لا يعني توفره. لا حالة أسواق في V1 (PF-09).</p>')
    notes_ = ''.join(f'<li class="ds-card"><p>{esc(t)}</p><p class="by">{OWNER} · {d}</p></li>' for t, d in [
        ('ملاحظة تجريبية 3: طلب مكتمل، مناسب لاجتماع تعريفي.', '2026-10-01 09:10'), ('ملاحظة تجريبية 2: تم الاتصال لتحديد موعد.', '2026-09-30 16:45'),
        ('ملاحظة تجريبية 1: الرسالة التعريفية واضحة.', '2026-09-30 11:20')])
    notes_html = (f'<ul class="notes">{notes_}</ul><div class="ds-field"><label class="ds-label" for="note-new">ملاحظة جديدة</label><textarea class="ds-textarea" id="note-new" dir="auto"></textarea></div>'
                  f'<button type="button" class="{B_PRI}">إضافة ملاحظة</button><p class="sub">كل ملاحظة مستقلة (الكاتب والوقت) ولا تُستبدل بصمت؛ أي تعديل يُسجَّل في سجل التدقيق.</p>')
    meets = ''.join(f'<li class="ds-card"><p><b>{m["d"]} · {m["t"]}</b> · {esc(m["ch"])} · {badge(m["st"])}</p><p class="sub">ملاحظات داخلية: {esc(n)}</p></li>' for m, n in [
        (dict(d='2026-10-07', t='13:30', ch=CHANNELS[2], st='مجدول'), 'نص تجريبي — للتحضير.'), (dict(d='2026-09-29', t='12:00', ch=CHANNELS[0], st='تم الاجتماع'), 'نص تجريبي — ملخص الاجتماع التعريفي.')])
    meet_html = (f'<ul class="meet">{meets}</ul><h3>إضافة اجتماع</h3>'
                 '<div class="two"><div class="ds-field"><label class="ds-label" for="mt-d">تاريخ الاجتماع</label><input class="ds-input" type="date" id="mt-d" value="2026-10-07"></div>'
                 '<div class="ds-field"><label class="ds-label" for="mt-t">وقت الاجتماع</label><input class="ds-input" type="time" id="mt-t" value="13:30"></div></div>'
                 + sel('mt-ch', 'المكان أو القناة', CHANNELS, None, 'اختر من القائمة')
                 + '<div class="ds-field"><label class="ds-label" for="mt-n">ملاحظات داخلية</label><textarea class="ds-textarea" id="mt-n" dir="auto"></textarea></div>'
                 + sel('mt-st', 'حالة الاجتماع', MSTAT, 'مجدول') + f'<button type="button" class="{B_PRI}">حفظ الاجتماع</button>'
                 '<p class="sub">الاجتماعات وملاحظاتها داخلية ولا تُعرض للمتقدم. قائمة الأماكن والقنوات تُدار من الإعدادات.</p>')
    files = ('<div class="ds-empty"><p><b>المرفقات غير مفعّلة في V1</b></p><p><span class="wf-note" lang="en">disabled in V1</span> <span class="wf-note">PENDING OWNER DECISION</span></p>'
             '<p class="sub">عند التفعيل: تخزين خاص فقط، ونفس سياسة الأمان في RECRUITMENT-SECURITY.md §3.</p>'
             f'<button type="button" class="{B_OUT}" disabled>رفع مستند</button></div>')
    attr = (kv([('المصدر (Source)', '(direct)'), ('الوسيط (Medium)', '(none)'), ('الحملة (Campaign)', '—'), ('صفحة الهبوط', '<span dir="ltr">/ar/franchise/</span>'), ('لغة الصفحة', 'ar')])
            + '<p class="sub">بلا IP دائم وبلا موقع دقيق. الدولة يختارها المتقدم.</p>')
    hist = ''.join(f'<li><span>{a} ← <b>{b}</b></span><span class="sub">{c} · {d}</span></li>' for a, b, c, d in [
        ('—', 'تم الاستلام', 'النظام', '2026-09-30 10:42'), ('تم الاستلام', 'مؤهل مبدئيًا', OWNER, '2026-09-30 11:30'),
        ('مؤهل مبدئيًا', 'اجتماع', OWNER, '2026-09-30 16:50'), ('اجتماع', 'مراجعة السوق', OWNER, '2026-10-01 09:12')])
    prev = ''.join(f'<li><span><a class="{B_LINK}" href="#">{a}</a> <span class="wf-note sample">{DSAMPLE}</span></span><span class="sub">{b} · {badge(c)}</span></li>' for a, b, c in [
        ('FR-2026-00031', '2026-03-12', 'مغلق'), ('FR-2025-00410', '2025-11-02', 'مؤرشف')])
    audit_rows = [('النظام', 'إنشاء طلب', r['id'], '2026-09-30 10:42', '—', 'تم الاستلام'), (OWNER, 'أول فتح (مقروء)', r['id'], '2026-09-30 11:05', 'غير مقروء', 'مقروء'),
                  (OWNER, 'تغيير المرحلة', r['id'], '2026-09-30 11:30', 'تم الاستلام', 'مؤهل مبدئيًا'), (OWNER, 'جدولة اجتماع', r['id'], '2026-09-30 16:50', '—', '2026-10-07 13:30 · اجتماع حضوري'),
                  (OWNER, 'إضافة ملاحظة', r['id'], '2026-10-01 09:10', '—', 'ملاحظة 3'), (OWNER, 'تغيير المرحلة', r['id'], '2026-10-01 09:12', 'اجتماع', 'مراجعة السوق')]
    if k == 'd':
        audit = simple_table(f'سجل التدقيق · {DSAMPLE}', AUDIT_COLS, [[esc(a), esc(b), f'<span dir="ltr">{c}</span>', d, esc(e), esc(f)] for a, b, c, d, e, f in audit_rows])
    else:
        audit = (f'<p class="sub">الأحدث أولًا · <span class="wf-note sample">{DSAMPLE}</span></p><ul class="rows audit">' + ''.join(f'<li><span><b>{esc(b)}</b>: {esc(e)} ← {esc(f)}</span><span class="sub">{esc(a)} · {d} · <span dir="ltr">{c}</span></span></li>'
                                                   for a, b, c, d, e, f in audit_rows) + '</ul>' + '<p class="sub">الحقول: ' + ' · '.join(AUDIT_COLS) + '</p>')
    head = (f'<div class="ptitle"><h1>{esc(r["n"])}</h1><span class="fr-row">{badge("طلبات سابقة محتملة (2) — بلا دمج")}<span class="wf-note sample">{DSAMPLE}</span></span></div>'
            f'<p class="sub">رقم الطلب <b>{appno(r["id"])}</b> · تاريخ الطلب 2026-09-30 10:42 · آخر تحديث 2026-10-01 09:12</p>'
            + panel('stbox-h', 'المرحلة الحالية: مراجعة السوق',
                    f'<div class="toolbar">{sel("app-st", "تغيير المرحلة إلى", STAGE_ACTIVE, "مراجعة السوق")}'
                    f'<div class="ds-field grow"><label class="ds-label" for="app-stn">ملاحظة داخلية (اختيارية)</label><input class="ds-input" type="text" id="app-stn" dir="auto"></div>'
                    f'<button type="button" class="{B_PRI}">حفظ المرحلة</button></div>'
                    '<p class="sub">القرار بشري: الـOwner يغيّر المرحلة يدويًا. كل انتقال يُسجَّل في سجل المراحل وسجل التدقيق.</p>'
                    f'<div class="fr-row"><a class="{B_OUT}" href="{k}-archived.html">أرشفة</a><a class="{B_GHOST}" href="{k}-list.html">العودة إلى القائمة</a></div>'))
    secs = [('s-data', 'بيانات الطلب', data), ('s-market', 'معلومات السوق', market), ('s-notes', 'الملاحظات الداخلية (3)', notes_html), ('s-meet', 'الاجتماعات (2)', meet_html),
            ('s-files', 'المرفقات', files), ('s-attr', 'الإسناد (Attribution)', attr), ('s-hist', 'سجل المراحل', f'<ul class="rows">{hist}</ul>'),
            ('s-prev', 'الطلبات السابقة', '<p>تطابق محتمل: رقم الهاتف · البريد الإلكتروني (واسم الشركة عند توفره). لا دمج تلقائي ولا منع.</p>' + f'<ul class="rows">{prev}</ul>'),
            ('s-audit', 'سجل التدقيق', audit)]
    toc = '<nav class="toc" aria-label="أقسام الطلب">' + ''.join(f'<a class="{B_GHOST}" href="#{a}">{t}</a>' for a, t, _ in secs) + '</nav>'
    body = ''.join(f'<section class="ds-card dpanel" id="{a}" aria-labelledby="{a}-h"><h2 id="{a}-h">{t}</h2>{inner}</section>' for a, t, inner in secs)
    return head + f'<div class="appgrid">{toc}<div class="appmain">{body}</div></div>'

def meetings_main(k):
    f = f'<div class="toolbar">{sel("mf-st", "حالة الاجتماع", MSTAT, None, "كل الحالات")}{sel("mf-r", "الفترة", RANGES, "هذا الشهر")}{sel("mf-ch", "المكان أو القناة", CHANNELS, None, "الكل")}</div>'
    up, past = [m for m in MEETS if m['st'] == 'مجدول'], [m for m in MEETS if m['st'] != 'مجدول']
    def block(ms, cap):
        if k == 'd':
            return simple_table(f'{cap} · {DSAMPLE}', ['التاريخ', 'الوقت', 'المكان أو القناة', 'المتقدم', 'المرحلة', 'حالة الاجتماع'],
                                [[m['d'], m['t'], esc(m['ch']), f'<a class="{B_LINK}" href="d-application.html">{esc(m["n"])}</a>', badge(m['stage']), badge(m['st'])] for m in ms])
        return '<ul class="acards">' + ''.join(f'<li class="ds-card acard one"><div><p class="acard-n"><a class="{B_LINK}" href="m-application.html">{esc(m["n"])}</a></p><p>{m["d"]} · {m["t"]}</p>'
                                              f'<p>{esc(m["ch"])}</p><p>{badge(m["stage"])} {badge(m["st"])}</p></div></li>' for m in ms) + '</ul>'
    return (f'<div class="ptitle"><h1>الاجتماعات</h1><span class="wf-note sample">{DSAMPLE}</span></div>{f}'
            '<p class="sub">التاريخ · الوقت · المكان أو القناة · ملاحظات داخلية · الحالة. لا تُعرض للمتقدم.</p>'
            + panel('mu-h', 'القادمة', block(up, 'الاجتماعات القادمة')) + panel('mp-h', 'السابقة', block(past, 'الاجتماعات السابقة')))

def archived_main(k):
    act = f'<button type="button" class="{B_OUT}">استعادة</button>'
    if k == 'd':
        body = simple_table(f'الطلبات المؤرشفة · {DSAMPLE}', ['المتقدم', 'رقم الطلب', 'تاريخ الأرشفة', 'المرحلة قبل الأرشفة', 'إجراءات'],
                            [[esc(r['n']), f'<span dir="ltr">{r["id"]}</span>', r['at'], badge(r['before']), act] for r in ARCH])
    else:
        body = '<ul class="acards">' + ''.join(f'<li class="ds-card acard one"><div><p class="acard-n"><b>{esc(r["n"])}</b></p><p><span dir="ltr">{r["id"]}</span> · أُرشف {r["at"]}</p>'
                                              f'<p>قبل الأرشفة: {badge(r["before"])}</p>{act}</div></li>' for r in ARCH) + '</ul>'
    return (f'<div class="ptitle"><h1>الأرشيف</h1><span class="wf-note sample">{DSAMPLE}</span></div>'
            '<p class="sub">الأرشفة بدل الحذف (قاعدة المشروع). يمكن استعادة أي طلب مؤرشف، وكل أرشفة أو استعادة تُسجَّل في سجل التدقيق.</p>' + body)

def settings_main(k):
    rows = []
    for i, (code, name) in enumerate(STAGES, 1):
        acts = (f'<span class="fr-row"><button type="button" class="{B_OUT}">تعديل الاسم</button><button type="button" class="{B_ICON}" aria-label="نقل «{name}» للأعلى">{I_U}</button>'
                f'<button type="button" class="{B_ICON}" aria-label="نقل «{name}» للأسفل">{I_D}</button><button type="button" class="{B_GHOST}">تعطيل</button></span>')
        rows.append((i, name, code, acts))
    if k == 'd':
        stages = simple_table('مراحل الـPipeline — مقترحة (M29 §38)', ['#', 'المرحلة', 'الكود', 'الحالة', 'إجراءات'],
                              [[str(i), esc(n), f'<code dir="ltr">{c}</code>', 'مفعّلة', a] for i, n, c, a in rows])
    else:
        stages = '<ul class="rows stages-m">' + ''.join(f'<li><span>{i}. <b>{esc(n)}</b> · <code dir="ltr">{c}</code> · مفعّلة</span>{a}</li>' for i, n, c, a in rows) + '</ul>'
    chans = ''.join(f'<li><span><b>{x}</b> · مفعّل</span><span class="fr-row"><button type="button" class="{B_OUT}">تعديل</button><button type="button" class="{B_GHOST}">تعطيل</button></span></li>' for x in CHANNELS)
    vers = (f'<ul class="rows"><li><span><b>نص الموافقة v1</b> · مسودة · <span class="wf-note">PENDING OWNER APPROVAL (PF-03)</span></span><span class="sub">{esc(CONSENT["ar"])}</span></li>'
            f'<li><span><b>الـDisclaimer v1</b> · مسودة · <span class="wf-note">{LEGAL} (PF-02)</span></span><span class="sub">{esc(DISCLAIMER["ar"])}</span></li></ul>')
    return ('<div class="ptitle"><h1>إعدادات الشراكات</h1></div>'
            + panel('set-st', 'مراحل الـPipeline', '<p class="sub">قابلة للإعداد بلا كود (TD-FR-01): إعادة التسمية والترتيب والتعطيل والإضافة. Franchise Master يفوز إذا اختلف الـWorkflow. كل انتقال يُسجَّل.</p>'
                    f'{stages}<div class="toolbar fr-stack"><div class="ds-field grow"><label class="ds-label" for="st-new">مرحلة جديدة</label><input class="ds-input" type="text" id="st-new" dir="auto"></div>'
                    f'<button type="button" class="{B_OUT}">إضافة مرحلة</button></div>')
            + panel('set-ch', 'أماكن وقنوات الاجتماعات', f'<ul class="rows">{chans}</ul><div class="toolbar"><div class="ds-field grow"><label class="ds-label" for="ch-new">مكان أو قناة جديدة</label>'
                    f'<input class="ds-input" type="text" id="ch-new" dir="auto"></div><button type="button" class="{B_OUT}">إضافة</button></div><p class="sub">أمثلة قابلة للتعديل — القائمة فقط تظهر في نموذج الاجتماع.</p>')
            + panel('set-v', 'نسخ نص الموافقة والـDisclaimer', f'{vers}<button type="button" class="{B_OUT}">إنشاء نسخة جديدة</button><p class="sub">النسخ السابقة تبقى محفوظة ومربوطة بالطلبات التي عُرضت عليها.</p>'))

def build_dashboard():
    for k in ('d', 'm'):
        overview(k)
        dpage(k, f'{k}-list.html', 'list', 'قائمة الطلبات', list_main(k))
        fb, ff = filters_body()
        dpage(k, f'{k}-list-filters.html', 'list', 'الفلاتر', list_main(k), dialog(k, 'flt-h', 'الفلاتر', fb, ff, f'{k}-list.html'))
        qb, qf = quick_body(k)
        dpage(k, f'{k}-quickview.html', 'list', 'عرض سريع', list_main(k), dialog(k, 'qv-h', esc(ROWS[0]['n']), qb, qf, f'{k}-list.html'))
        dpage(k, f'{k}-application.html', 'list', 'الطلب الكامل', app_main(k))
        dpage(k, f'{k}-meetings.html', 'meetings', 'الاجتماعات', meetings_main(k))
        dpage(k, f'{k}-archived.html', 'archived', 'الأرشيف', archived_main(k))
        dpage(k, f'{k}-settings.html', 'settings', 'الإعدادات', settings_main(k))

# ---------------------------------------------------------------- index
DASH = ['overview', 'list', 'list-filters', 'quickview', 'application', 'meetings', 'archived', 'settings']
FORMS = ['form-a', 'form-a-errors', 'form-a-network', 'form-b', 'form-b-s2', 'form-b-s3', 'form-b-s4', 'form-b-s5', 'form-b-errors', 'form-b-network']
INDEX = [
    ('صفحة الفرنشايز والشراكات (صفحة واحدة متجاوبة 320 ← 1920+)', ['ar-franchise', 'en-franchise']),
    ('النموذج A — صفحة واحدة منظمة (عربي)', [f'ar-{x}' for x in FORMS if x.startswith('form-a')]),
    ('النموذج B — 5 خطوات قصيرة (عربي)', [f'ar-{x}' for x in FORMS if x.startswith('form-b')]),
    ('Form A — single structured page (English)', [f'en-{x}' for x in FORMS if x.startswith('form-a')]),
    ('Form B — 5 short steps (English)', [f'en-{x}' for x in FORMS if x.startswith('form-b')]),
    ('بعد الإرسال', ['ar-success', 'en-success']),
    ('Owner Dashboard · الشراكات — Desktop (≥ 1024px)', [f'd-{x}' for x in DASH]),
    ('Owner Dashboard · الشراكات — Mobile (< 1024px)', [f'm-{x}' for x in DASH]),
]

def build_index():
    body = ('<main class="ds-container fr-narrow"><div class="intro"><h1>Franchise &amp; Partnerships — فهرس الـWireframes</h1>'
            '<p>LOW-FI (M29 · المخرجات 8–11 + النموذج A/B + وحدة الشراكات في الـOwner Dashboard). نظام تصميم واحد (M34). كل البيانات تجريبية.</p></div>')
    for t, items in INDEX:
        body += (f'<section class="ds-card dpanel"><h2 lang="{"en" if t[0].isascii() else "ar"}">{esc(t)}</h2><ul class="rows">'
                 + ''.join(f'<li><a class="{B_LINK}" href="{x}.html" dir="ltr">{x}.html</a></li>' for x in items) + '</ul></section>')
    body += legend('ar') + '</main>'
    head = '<header class="site"><div class="ds-container site-in"><span class="logo" lang="en">SHELTER COFFEE</span></div></header>'
    write('index.html', page(head + body, 'فهرس الـWireframes — الفرنشايز والشراكات', 'INDEX', 'ar'))

PAGE_VARIANT = 'A'  # the form embedded in the page — set from the A/B decision (README · evidence/form-variant-measure.json)

if __name__ == '__main__':
    if os.path.isdir(OUT):
        shutil.rmtree(OUT)
    os.makedirs(OUT)
    for L in ('ar', 'en'):
        build_page(L, PAGE_VARIANT)
    build_forms(); build_success(); build_dashboard(); build_index()
    print(len(os.listdir(OUT)), 'pages →', OUT)
