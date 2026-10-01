import csv, json, re, sys, html
from datetime import datetime
sys.path.insert(0,'/home/user/SHELTER-WEBSITE/docs/menu-ia/evidence')
from hours_logic_check import label as hours_label, status as hours_status
OUT='/home/user/SHELTER-WEBSITE/docs/menu-ia/wireframes/html/'
import os; os.makedirs(OUT,exist_ok=True)
R=[r for r in csv.DictReader(open('/home/user/SHELTER-WEBSITE/docs/phase-01-discovery/menu/menu-inventory-v1.0.csv',encoding='utf-8-sig')) if not r['id_status'].startswith('FROZEN — RETIRED')]
SC=json.load(open('subcats.json')); SUB=SC['SUB']; SMAP=SC['MAP']
esc=html.escape
def slug(s): return re.sub(r'-+','-',re.sub(r'[^a-z0-9]+','-',s.lower())).strip('-')
for r in R:
    r['ar']=r['display_name_ar'] or r['source_name_ar']; r['ar_pending']=not r['display_name_ar']
    r['en']=r['display_name_en']; r['slug']=slug(r['en']); r['price']=int(r['price_fils'])
    r['sub']=SMAP.get(r['en'])
def by_cat(c): return sorted([r for r in R if r['category_id']==c], key=lambda r:int(r['source_row']) if r['source_sheet']=='Sheet1' else 1000+int(r['source_row']))
CAT_AR={'CAT-001':'مشروبات ساخنة','CAT-002':'مشروبات باردة','CAT-003':'مشروبات غازية','CAT-004':'ميلك شيك','CAT-005':'سموذي','CAT-006':'فرابيه','CAT-007':'شاي','CAT-008':'قهوة مختصة','CAT-009':'سبرينغ'}
SECTIONS=[('speciality-coffee',['CAT-008'],'قهوة مختصة','SPECIALITY COFFEE',None),
 ('hot-drinks',['CAT-001'],'مشروبات ساخنة','HOT DRINKS',['HOT-ESP','HOT-LAT','HOT-TRD','HOT-CHM','PENDING']),
 ('cold-drinks',['CAT-002'],'مشروبات باردة','COLD DRINKS',['COLD-CLS','COLD-LAT','COLD-SHK','COLD-TEA','COLD-CHM','PENDING']),
 ('frappe',['CAT-006'],'فرابيه','FRAPPE',None),('milkshake',['CAT-004'],'ميلك شيك','MILKSHAKE',None),('smoothies',['CAT-005'],'سموذي','SMOOTHIES',None),
 ('fizzy-drinks',['CAT-003'],'مشروبات غازية','FIZZY DRINKS',['FZ-MOJ','FZ-NRG','FZ-SFT','PENDING']),('tea',['CAT-007'],'شاي','TEA',None),
 ('sweets',['CAT-010','CAT-011'],'حلويات','SWEETS',['CAKE','COOKIES'])]
SUBLBL=dict(SUB); SUBLBL['PENDING']=('MORE','المزيد'); SUBLBL['CAKE']=('CAKE','كيك'); SUBLBL['COOKIES']=('COOKIES','كوكيز')
T={'ar':dict(menu='المنيو',note='الأسعار بالدينار الأردني وشاملة الضريبة',search='ابحث في المنيو',searchlbl='بحث',all='كل الفروع',allcats='كل الفئات',items='صنفًا',season='الموسم',seasonal='موسمي',new='جديد',
   unavail='غير متوفر حاليًا في HOUSE',only='متوفر في DRIVE فقط',demo='مثال',results='نتيجة',noresults='لا توجد نتائج لـ',clear='مسح البحث',maybe='ربما تبحث في',browse='تصفّح الفئات',close='إغلاق',
   cur='د.أ',lang='EN',sweets='حلويات'),
   'en':dict(menu='Menu',note='Prices in JOD, VAT included',search='Search the menu',searchlbl='Search',all='All branches',allcats='All categories',items='items',season='Seasonal',seasonal='SEASONAL',new='NEW',
   unavail='Currently unavailable at HOUSE',only='Available at DRIVE only',demo='DEMO',results='results',noresults='No results for',clear='Clear search',maybe='Maybe you are looking for',browse='Browse categories',close='Close',
   cur='JOD',lang='ع',sweets='SWEETS')}
def price(r,lang): v=f"{r['price']/1000:.2f}"; return f"{v} {T[lang]['cur']}"
SVG_SEARCH='<svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>'
SVG_LIST='<svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>'
SVG_X='<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>'
# ONE design system (M34): every page inlines the shared tokens + wireframe kit, read at build time (never copied by hand).
DS=os.path.normpath(os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..','..','..','design-system'))
DS_CSS=open(os.path.join(DS,'build','tokens.css'),encoding='utf-8').read()+'\n'+open(os.path.join(DS,'wireframe-kit.css'),encoding='utf-8').read()
# Menu-only layout. Values are tokens (var(--…)) or kit classes (.ds-btn/.ds-chip/.ds-card/.ds-media/.ds-badge/.ds-sheet/.ds-modal/.ds-overlay/.ds-empty).
# Measured rules kept (UX-VALIDATION): bars 56px = calc(var(--sticky-stack) / 2) (R-06) · targets >= var(--touch-target) · columns 1/2/3/4 at 360/600/1024/1200 (R-02/R-03)
# · R-09 short-height · image 1:1 (R-04: sheet <= 360px, modal 320px) · price on one line · EN card name var(--text-sm) (R-07).
# Raw px left on purpose = layout dimensions with no token: 56px thumb, 64px season row, 96px one-column image, 112px text card, 240px side nav, 320/360px detail image.
CSS='''
.wf-tag{background:var(--color-accent);color:var(--color-on-accent);font-size:var(--text-xs);padding:var(--space-1) var(--space-3)}
.site{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2);min-height:calc(var(--sticky-stack) / 2);padding:0 var(--space-4);border-bottom:var(--border-default) solid var(--color-border)}
.site .acts{display:flex;align-items:center;gap:var(--space-2)}
.logo{font-weight:var(--weight-bold);font-size:var(--text-base)}
.head{padding:var(--space-4) var(--space-4) var(--space-1)}.head h1{margin:0;font-size:var(--text-3xl)}.head p{margin:var(--space-1) 0 0;color:var(--color-muted);font-size:var(--text-sm)}
.titlerow{display:flex;align-items:center;justify-content:space-between;gap:var(--space-2)}
.search{padding:var(--space-3) var(--space-4) var(--space-1)}.search label{display:block;font-size:var(--text-xs);color:var(--color-muted);margin-bottom:var(--space-1)}
.sbox{display:flex;align-items:center;gap:var(--space-2);min-height:var(--control-md);border:var(--border-strong) solid var(--color-muted);border-radius:var(--radius-md);padding:0 var(--space-3);background:var(--color-surface);color:var(--color-muted)}
.sbox .q{flex:1;color:var(--color-text)}.sbox.focus{border-color:var(--color-focus);background:var(--color-bg)}
.branch{padding:var(--space-3) var(--space-4) var(--space-1)}
.seg{display:grid;grid-template-columns:repeat(3,1fr);border:var(--border-strong) solid var(--color-muted);border-radius:var(--radius-md);overflow:hidden}
.seg button{min-height:var(--touch-target);border:0;border-inline-start:var(--border-default) solid var(--color-border);background:var(--color-bg);color:var(--color-text);font:inherit;font-size:var(--text-sm)}
.seg button:first-child{border-inline-start:0}.seg button[aria-pressed=true]{background:var(--color-accent);color:var(--color-on-accent);font-weight:var(--weight-bold)}
.bstat{display:flex;flex-wrap:wrap;gap:var(--space-1) var(--space-3);font-size:var(--text-sm);color:var(--color-text);margin-top:var(--space-2)}.bstat b{letter-spacing:var(--tracking-en-caps)}
.bstat.solo{padding:var(--space-1) var(--space-4) 0}
.dot{display:inline-block;width:var(--space-2);height:var(--space-2);border-radius:50%;border:var(--border-strong) solid var(--color-accent);margin-inline-end:var(--space-1);vertical-align:middle}.dot.on{background:var(--color-accent)}
.compactsel{white-space:nowrap}
.season{margin:var(--space-3) var(--space-4) var(--space-1);border:var(--border-strong) dashed var(--color-border);border-radius:var(--radius-lg);padding:var(--space-3)}
.season h2{margin:0 0 var(--space-2);font-size:var(--text-lg);display:flex;align-items:center;gap:var(--space-2)}
.srow{display:grid;grid-template-columns:56px 1fr auto;gap:var(--space-3);align-items:center;min-height:64px;border-top:var(--border-default) solid var(--color-border);padding:var(--space-1) 0}.srow:first-of-type{border-top:0}
.thumb{width:56px;height:56px;border-radius:var(--radius-md);border:var(--border-default) solid var(--color-border)}
.catbar{position:sticky;top:0;z-index:var(--z-sticky);background:var(--color-bg);border-bottom:var(--border-default) solid var(--color-border);display:flex;align-items:center;gap:var(--space-2);padding:0 var(--space-2);height:calc(var(--sticky-stack) / 2)}
.chips{--fade:to left;display:flex;gap:var(--space-2);overflow-x:auto;scrollbar-width:none;flex:1;-webkit-mask-image:linear-gradient(var(--fade),transparent 0,#000 var(--space-8));mask-image:linear-gradient(var(--fade),transparent 0,#000 var(--space-8))}
[dir=rtl] .chips{--fade:to right}
.chip{flex:none}
.subbar{position:sticky;top:calc(var(--sticky-stack) / 2);z-index:calc(var(--z-sticky) - 1);background:var(--color-bg);display:flex;gap:var(--space-2);overflow-x:auto;padding:0 var(--space-4);border-bottom:var(--border-default) solid var(--color-border);scrollbar-width:none;height:calc(var(--sticky-stack) / 2);align-items:center}
.subchip{flex:none;background:var(--color-surface);border-width:var(--border-default)}.subchip.on{border-color:var(--color-accent);background:var(--color-bg);font-weight:var(--weight-bold)}
.sec{padding:var(--space-2) var(--space-4) var(--space-1);scroll-margin-top:calc(var(--sticky-stack) + var(--space-2))}.sec>h2{font-size:var(--text-xl);margin:var(--space-4) 0 var(--space-1)}.cnt{color:var(--color-muted);font-size:var(--text-sm);margin:0 0 var(--space-2)}
.sec h3{font-size:var(--text-base);margin:var(--space-3) 0 var(--space-2);scroll-margin-top:calc(var(--sticky-stack) + var(--space-2))}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--space-3)}
.card{padding:0;overflow:hidden;display:flex;flex-direction:column;min-width:0}
.card .img{border-radius:0}
.card .body{padding:var(--space-2) var(--space-2) var(--space-3);display:flex;flex-direction:column;gap:var(--space-1);flex:1}
.n1{font-weight:var(--weight-bold);font-size:var(--text-base);line-height:var(--leading-tight);overflow-wrap:anywhere}
.n2{font-size:var(--text-xs);color:var(--color-muted);line-height:var(--leading-tight);overflow-wrap:anywhere}
.n1:lang(en),.n2:lang(en),.en:lang(en){letter-spacing:var(--tracking-en-caps)}
.card .n1:lang(en){font-size:var(--text-sm)}
.pr{margin-top:auto;padding-top:var(--space-2);font-weight:var(--weight-bold);font-size:var(--text-base);white-space:nowrap;font-variant-numeric:tabular-nums}
.pend{text-decoration:underline dotted var(--color-muted);text-underline-offset:3px}
.note{margin-top:var(--space-1);align-self:flex-start}
.demo{margin-inline-start:var(--space-1)}
.card.dim .img,.card.dim .n1,.card.dim .n2,.card.dim .pr{opacity:.55}
.card.noimg .body{padding:var(--space-3);min-height:112px}
.card.hl{outline:3px solid var(--color-accent);outline-offset:2px}
.legend{margin:var(--space-6) var(--space-4) var(--space-4);border-top:var(--border-default) solid var(--color-border);padding-top:var(--space-2)}
.handle{width:var(--touch-target);height:var(--space-1);border-radius:var(--radius-pill);background:var(--color-border);margin:var(--space-1) auto var(--space-2)}
.x{position:absolute;top:var(--space-3);inset-inline-end:var(--space-3)}
.bigimg{max-width:360px;margin:var(--space-2) auto}
.sheet h2{margin:var(--space-2) 0 0}
.en{font-size:var(--text-sm);color:var(--color-muted)}.bp{font-size:var(--text-xl);font-weight:var(--weight-bold);margin-top:var(--space-2)}
.kv{font-size:var(--text-sm);color:var(--color-muted);border-top:var(--border-default) solid var(--color-border);margin-top:var(--space-3);padding-top:var(--space-3)}
.sug{margin:var(--space-1) var(--space-4) 0;border:var(--border-strong) solid var(--color-accent);border-radius:var(--radius-md);overflow:hidden}
.sug .it{display:grid;grid-template-columns:1fr auto;gap:var(--space-1) var(--space-2);padding:var(--space-2) var(--space-3);border-top:var(--border-default) solid var(--color-border);min-height:var(--control-lg)}.sug .it:first-child{border-top:0}
.sug small{color:var(--color-muted);grid-column:1/-1;font-size:var(--text-xs)}
.reshead{padding:var(--space-3) var(--space-4) 0;font-size:var(--text-sm)}
.empty{margin:var(--space-5) var(--space-4)}.empty .ds-btn{margin:var(--space-2) var(--space-1)}
.allcats .row{display:flex;justify-content:space-between;align-items:center;min-height:var(--control-lg);border-top:var(--border-default) solid var(--color-border)}
.allcats .row small{color:var(--color-muted)}.allcats .subs{font-size:var(--text-xs);color:var(--color-muted);padding:0 0 var(--space-2)}
@media (max-height:500px){.subbar{position:static}}
@media (max-width:359px){.grid{grid-template-columns:1fr}.card{flex-direction:row}.card .img{width:96px;flex:none}.card.noimg .body{min-height:auto}}
@media (min-width:600px){.grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
/* 1-column variant (very small screens) */
body.one .grid{grid-template-columns:1fr}body.one .card{flex-direction:row}body.one .card .img{width:96px;flex:none}body.one .card.noimg .body{min-height:auto}
/* desktop: .ds-container gives the 1280px page width + gutters */
.desk .site{padding-inline:var(--container-gutter-desktop)}.desk .site .acts{font-size:var(--text-sm)}
.desk .wrap{display:grid;grid-template-columns:240px minmax(0,1fr);gap:var(--space-8)}
.desk .side{position:sticky;top:0;align-self:start;max-height:100vh;overflow:auto;padding:var(--space-4) 0}
.desk .side a{display:block;min-height:var(--touch-target);padding:var(--space-2) var(--space-3);border-radius:var(--radius-md);color:var(--color-text);text-decoration:none}
.desk .side a.on{background:var(--color-accent);color:var(--color-on-accent);font-weight:var(--weight-bold)}
.desk .side .s2{font-size:var(--text-sm);padding-block:var(--space-1);padding-inline:var(--space-6) var(--space-3);min-height:var(--control-sm);color:var(--color-muted)}
.desk .grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:var(--space-5)}
@media (min-width:1200px){.desk .grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.desk .head,.desk .search,.desk .branch,.desk .sec{padding-inline:0}.desk .season{margin-inline:0}
.desk .toprow{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:var(--space-5);align-items:end}
.desk .srows{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:var(--space-10)}.desk .srow{border-top:var(--border-default) solid var(--color-border)}
.box{display:grid;grid-template-columns:320px 1fr;gap:var(--space-6)}.box .bigimg{margin:0;max-width:none}.box h2{margin:var(--space-6) 0 0;font-size:var(--text-3xl)}
'''
def card(r,lang,mode='img',extra=None,dim=False,hl=False):
    p1,p2=(r['ar'],r['en']) if lang=='ar' else (r['en'],r['ar'])
    l2='en' if lang=='ar' else 'ar'
    pend1=' pend' if (lang=='ar' and r['ar_pending']) else ''
    pend2=' pend' if (lang=='en' and r['ar_pending']) else ''
    img='' if mode=='noimg' else f'<div class="ds-media img" aria-hidden="true">1:1</div>'
    cls='ds-card card'+(' noimg' if mode=='noimg' else '')+(' dim' if dim else '')+(' hl' if hl else '')
    ex=f'<span class="ds-badge note">{esc(extra[0])}<span class="wf-note demo">{T[lang]["demo"]}</span></span>' if extra else ''
    return f'<article class="{cls}" id="p-{r["slug"]}">{img}<div class="body"><div class="n1{pend1}" lang="{lang}">{esc(p1)}</div><div class="n2{pend2}" lang="{l2}">{esc(p2)}</div>{ex}<div class="pr">{price(r,lang)}</div></div></article>'
def sec_items(sec):
    sid,cats,ar,en,subs=sec
    items=[r for c in cats for r in by_cat(c)]
    return items
def section_html(sec,lang,mode,demo=None,active_sub=None,subbar=True):
    sid,cats,ar,en,subs=sec
    items=sec_items(sec)
    t=ar if lang=='ar' else en
    h=f'<section class="sec" id="{sid}" aria-labelledby="h-{sid}"><h2 id="h-{sid}">{esc(t)}</h2><p class="cnt">{len(items)} {T[lang]["items"]}</p>'
    if subs:
        groups=[]
        for s in subs:
            if sid=='sweets': g=[r for r in items if r['category_id']==('CAT-010' if s=='CAKE' else 'CAT-011')]
            else: g=[r for r in items if r['sub']==s]
            if g: groups.append((s,g))
        if subbar:
            h+='<nav class="subbar" aria-label="sub">'+''.join(f'<a class="ds-chip subchip{" on" if (active_sub==s or (active_sub is None and i==0)) else ""}" href="#{sid}-{slug(SUBLBL[s][0])}">{esc(SUBLBL[s][1] if lang=="ar" else SUBLBL[s][0])}</a>' for i,(s,g) in enumerate(groups))+'</nav>'
        for s,g in groups:
            h+=f'<h3 id="{sid}-{slug(SUBLBL[s][0])}">{esc(SUBLBL[s][1] if lang=="ar" else SUBLBL[s][0])}</h3><div class="grid">'+''.join(card(r,lang,mode,*(demo(r) if demo else (None,False,False))) for r in g)+'</div>'
    else:
        h+='<div class="grid">'+''.join(card(r,lang,mode,*(demo(r) if demo else (None,False,False))) for r in items)+'</div>'
    return h+'</section>'
def season_html(lang):
    items=by_cat('CAT-009'); t=T[lang]
    rows=''.join(f'<div class="srow"><div class="ds-media thumb" aria-hidden="true"></div><div><div class="n1{" pend" if (lang=="ar" and r["ar_pending"]) else ""}" lang="{lang}">{esc(r["ar"] if lang=="ar" else r["en"])}</div><div class="n2" lang="{"en" if lang=="ar" else "ar"}">{esc(r["en"] if lang=="ar" else r["ar"])}</div></div><div class="pr">{price(r,lang)}</div></div>' for r in items)
    name='SPRING · سبرينغ' if lang=='ar' else 'SPRING'
    return f'<section class="season" id="spring" aria-labelledby="h-spring"><h2 id="h-spring">{t["season"]} — {name} <span class="ds-badge badge">{t["seasonal"]}</span></h2><div class="srows">{rows}</div></section>'
def branch_html(lang,sel='all',now=None,variant='A'):
    t=T[lang]; now=now or datetime(2026,10,3,19,30)
    lab={'all':t['all'],'drive':'DRIVE','house':'HOUSE'}
    stat=lambda b: f'<span><span class="dot{" on" if hours_status(b,now)[0]!="CLOSED" else ""}"></span><b>{b.upper()}</b> · {esc(hours_label(b,now,lang))}</span>'
    st=''.join(stat(b) for b in (['drive','house'] if sel=='all' else [sel]))
    if variant=='A':
        segs=''.join(f'<button aria-pressed="{"true" if k==sel else "false"}">{lab[k]}</button>' for k in ('all','drive','house'))
        return f'<div class="branch"><div class="seg" role="group" aria-label="branch">{segs}</div><div class="bstat">{st}</div></div>'
    return f'<button class="ds-btn ds-btn--secondary compactsel">{lab[sel]} ▾</button>', f'<div class="bstat solo">{st}</div>'
def catbar(lang,active='speciality-coffee',scrolled=False):
    t=T[lang]
    AC='aria-current="true"'
    chips=''.join(f'<a class="ds-chip chip" href="#{s[0]}" {AC if s[0]==active else ""}>{esc(s[2] if lang=="ar" else s[3])}</a>' for s in SECTIONS)
    sb=f'<button class="ds-btn ds-btn--ghost ds-btn--icon hbtn" aria-label="{t["searchlbl"]}">{SVG_SEARCH}</button>' if scrolled else ''
    return f'<nav class="catbar" aria-label="categories">{sb}<div class="chips">{chips}</div><button class="ds-btn ds-btn--ghost ds-btn--icon hbtn" aria-label="{t["allcats"]}" aria-haspopup="dialog">{SVG_LIST}</button></nav>'
def page(lang,body,title,cls=''):
    d='rtl' if lang=='ar' else 'ltr'
    return f'<!doctype html><html lang="{lang}" dir="{d}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{esc(title)}</title><style>{DS_CSS}{CSS}</style></head><body class="{cls}">{body}</body></html>'
def legend(lang):
    return '<p class="ds-caption legend">'+('LOW-FI WIREFRAME. الصور مربعات رمادية (لا صور معتمدة بعد). الأسماء العربية المسطّرة بنقاط = من المصدر، بانتظار الاعتماد (D-137). "مثال" = حالة توضيحية وليست بيانات حقيقية. أسماء الأقسام الفرعية مقترحة.' if lang=='ar' else 'LOW-FI WIREFRAME. Grey boxes = images (none approved yet). Dotted-underlined Arabic = source name pending approval (D-137). DEMO = illustrative state, not real data. Subcategory names are proposals.')+'</p>'
def mobile(lang,state='default',mode='img',variant='A',sel='all',now=None,active='speciality-coffee',demo=None,cls=''):
    t=T[lang]
    tag=f'<div class="wf-tag">LOW-FI WIREFRAME · {lang.upper()} · {state}</div>'
    site=f'<header class="site"><span class="logo">SHELTER COFFEE</span><span class="acts"><button class="ds-btn ds-btn--ghost hbtn">{t["lang"]}</button><button class="ds-btn ds-btn--ghost ds-btn--icon hbtn" aria-label="menu">{SVG_LIST}</button></span></header>'
    if variant=='A':
        head=f'<div class="head"><h1>{t["menu"]}</h1><p>{t["note"]}</p></div>'
        br=branch_html(lang,sel,now,'A')
    else:
        btn,stat=branch_html(lang,sel,now,'B')
        head=f'<div class="head"><div class="titlerow"><h1>{t["menu"]}</h1>{btn}</div><p>{t["note"]}</p></div>'
        br=stat
    sq=f'<div class="search"><label>{t["searchlbl"]}</label><div class="sbox{" focus" if state in ("search","zero") else ""}">{SVG_SEARCH}<span class="q">{ {"search":("لاتيه" if lang=="ar" else "spanish"),"zero":"xyz"}.get(state,"") or t["search"]}</span>{SVG_X if state in ("search","zero") else ""}</div></div>'
    if state=='search':
        q='لاتيه' if lang=='ar' else 'spanish'
        res=[r for r in R if (q in r['ar'] if lang=='ar' else q.upper() in r['en'])]
        sug=''.join(f'<div class="it"><span class="n1" lang="{lang}">{esc(r["ar"] if lang=="ar" else r["en"])}</span><span class="pr">{price(r,lang)}</span><small>{esc(r["en"] if lang=="ar" else r["ar"])} · {esc(CAT_AR.get(r["category_id"],"") if lang=="ar" else r["source_category_name"])}</small></div>' for r in res[:5])
        body=tag+site+head+sq+f'<div class="sug" role="listbox">{sug}</div><p class="reshead" aria-live="polite">{len(res)} {t["results"]}</p><div class="sec"><div class="grid">'+''.join(card(r,lang,mode) for r in res[:6])+'</div></div>'+legend(lang)
        return page(lang,body,'wf',cls)
    if state=='zero':
        body=tag+site+head+sq+f'<div class="ds-empty empty" role="status"><p><b>{t["noresults"]} "xyz"</b></p><span class="ds-btn ds-btn--outline">{t["clear"]}</span><span class="ds-btn ds-btn--outline">{t["browse"]}</span></div>'+catbar(lang)+''.join(section_html(s,lang,mode) for s in SECTIONS[:1])+legend(lang)
        return page(lang,body,'wf',cls)
    body=tag+site+head+sq+br+season_html(lang)+catbar(lang,active,scrolled=(state=='scrolled'))+''.join(section_html(s,lang,mode,demo) for s in SECTIONS)+legend(lang)
    if state=='sheet':
        r=[x for x in R if x['en']=='ICED SPANISH LATTE'][0]
        p1,p2=(r['ar'],r['en']) if lang=='ar' else (r['en'],r['ar'])
        body+=f'<div class="ds-overlay"></div><div class="ds-sheet sheet" role="dialog" aria-modal="true" aria-labelledby="sh-t"><div class="handle"></div><button class="ds-btn ds-btn--ghost ds-btn--icon hbtn x" aria-label="{t["close"]}">{SVG_X}</button><div class="ds-media bigimg">1:1</div><h2 id="sh-t" class="{"pend" if (lang=="ar" and r["ar_pending"]) else ""}">{esc(p1)}</h2><div class="en" lang="{"en" if lang=="ar" else "ar"}">{esc(p2)}</div><div class="bp">{price(r,lang)}</div><div class="kv">{"COLD DRINKS › ICED LATTES & FLAVORS" if lang=="en" else "مشروبات باردة › آيس لاتيه ونكهات"}<br>{"الوصف والمكونات: لا تظهر حتى تُعتمد (لا حقول فارغة)" if lang=="ar" else "Description/ingredients: hidden until approved (no empty fields)"}</div></div>'
    if state=='allcats':
        rows=''
        for s in SECTIONS:
            n=len(sec_items(s)); subs=s[4]
            sl=' · '.join((SUBLBL[x][1] if lang=='ar' else SUBLBL[x][0]) for x in (subs or []) if x!='PENDING')
            rows+=f'<div class="row"><span>{esc(s[2] if lang=="ar" else s[3])}</span><small>{n}</small></div>'+(f'<div class="subs">{esc(sl)}</div>' if sl else '')
        body+=f'<div class="ds-overlay"></div><div class="ds-sheet sheet allcats" role="dialog" aria-modal="true" aria-label="{t["allcats"]}"><div class="handle"></div><button class="ds-btn ds-btn--ghost ds-btn--icon hbtn x" aria-label="{t["close"]}">{SVG_X}</button><h2>{t["allcats"]}</h2>{rows}</div>'
    return page(lang,body,'wf',cls)
def desktop(lang,state='default',mode='img',sel='all',now=None,demo=None):
    t=T[lang]
    tag=f'<div class="wf-tag">LOW-FI WIREFRAME · DESKTOP · {lang.upper()} · {state}</div>'
    site=f'<header class="site"><span class="logo">SHELTER COFFEE</span><span class="acts">{"الرئيسية · المنيو · الفروع · من نحن · تواصل" if lang=="ar" else "Home · Menu · Locations · About · Contact"} <button class="ds-btn ds-btn--ghost hbtn">{t["lang"]}</button></span></header>'
    side=''.join(f'<a href="#{s[0]}" class="{"on" if i==0 else ""}">{esc(s[2] if lang=="ar" else s[3])}</a>'+''.join(f'<a class="s2" href="#">{esc(SUBLBL[x][1] if lang=="ar" else SUBLBL[x][0])}</a>' for x in (s[4] or []) if x!='PENDING' and i in (1,)) for i,s in enumerate(SECTIONS))
    BR=branch_html(lang,sel,now,"A")
    main=f'<div class="head"><h1>{t["menu"]}</h1><p>{t["note"]}</p></div><div class="toprow"><div class="search"><label>{t["searchlbl"]}</label><div class="sbox">{SVG_SEARCH}<span class="q">{t["search"]}</span></div></div>{BR}</div>'
    main+=season_html(lang)+''.join(section_html(s,lang,mode,demo,subbar=False) for s in SECTIONS)+legend(lang)
    body=tag+site+f'<div class="ds-container wrap"><aside class="side"><nav aria-label="categories">{side}</nav></aside><main>{main}</main></div>'
    if state=='modal':
        r=[x for x in R if x['en']=='ICED SPANISH LATTE'][0]
        p1,p2=(r['ar'],r['en']) if lang=='ar' else (r['en'],r['ar'])
        body+=f'<div class="ds-overlay"></div><div class="ds-modal box" role="dialog" aria-modal="true"><button class="ds-btn ds-btn--ghost ds-btn--icon hbtn x" aria-label="{t["close"]}">{SVG_X}</button><div class="ds-media bigimg">1:1</div><div><h2>{esc(p1)}</h2><div class="en" lang="{"en" if lang=="ar" else "ar"}">{esc(p2)}</div><div class="bp">{price(r,lang)}</div><div class="kv">{"Esc يغلق · التركيز يعود للبطاقة" if lang=="ar" else "Esc closes · focus returns to the card"}</div></div></div>'
    return page(lang,f'<div class="desk">{body}</div>','wf')
# demo states for branch screens
def demo_house(r):
    if r['en']=='ICED SPANISH LATTE': return ((T_lang['unavail'],),True,False)
    if r['en']=='ICED LATTE': return ((T_lang['only'],),False,False)
    return (None,False,False)
FILES={}
for lang in ('ar','en'):
    T_lang=T[lang]
    FILES[f'm-{lang}-default']=mobile(lang)
    FILES[f'm-{lang}-noimg']=mobile(lang,mode='noimg')
    FILES[f'm-{lang}-scrolled']=mobile(lang,state='scrolled',active='hot-drinks')
    FILES[f'm-{lang}-search']=mobile(lang,state='search')
    FILES[f'm-{lang}-zero']=mobile(lang,state='zero')
    FILES[f'm-{lang}-sheet']=mobile(lang,state='sheet')
    FILES[f'm-{lang}-allcats']=mobile(lang,state='allcats')
    FILES[f'm-{lang}-house-closed']=mobile(lang,sel='house',now=datetime(2026,10,3,8,0),demo=lambda r,L=lang:(((T[L]['unavail'],),True,False) if r['en']=='ICED SPANISH LATTE' else (((T[L]['only'],),False,False) if r['en']=='ICED LATTE' else (None,False,False))))
    FILES[f'm-{lang}-house-cold']=mobile(lang,sel='house',now=datetime(2026,10,3,8,0),active='cold-drinks',demo=lambda r,L=lang:(((T[L]['unavail'],),True,False) if r['en']=='ICED SPANISH LATTE' else (((T[L]['only'],),False,False) if r['en']=='ICED LATTE' else (None,False,False))))
    FILES[f'm-{lang}-drive-closing']=mobile(lang,sel='drive',now=datetime(2026,10,4,1,15))
    FILES[f'm-{lang}-variantB']=mobile(lang,variant='B')
    FILES[f'm-{lang}-one']=mobile(lang,cls='one')
    FILES[f'd-{lang}-default']=desktop(lang)
    FILES[f'd-{lang}-modal']=desktop(lang,state='modal')
    FILES[f'd-{lang}-house']=desktop(lang,sel='house',now=datetime(2026,10,3,8,0),demo=lambda r,L=lang:(((T[L]['unavail'],),True,False) if r['en']=='ICED SPANISH LATTE' else (((T[L]['only'],),False,False) if r['en']=='ICED LATTE' else (None,False,False))))
for k,v in FILES.items(): open(OUT+k+'.html','w').write(v)
print(len(FILES),'files'); print({k:len(v) for k,v in list(FILES.items())[:3]})
