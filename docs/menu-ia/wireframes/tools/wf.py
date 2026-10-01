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
CSS='''
*{box-sizing:border-box}html{-webkit-text-size-adjust:100%}
body{margin:0;font-family:"DejaVu Sans",system-ui,sans-serif;color:#1a1a1a;background:#fff;font-size:16px;line-height:1.45}
.wf-tag{background:#222;color:#fff;font-size:11px;padding:4px 12px;letter-spacing:.02em}
.site{display:flex;align-items:center;justify-content:space-between;height:56px;padding:0 16px;border-bottom:1px solid #ddd}
.logo{font-weight:700;letter-spacing:.08em;font-size:15px}.hbtn{min-width:44px;min-height:44px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #ccc;border-radius:8px;background:#fff;font:inherit;font-size:13px}
.head{padding:16px 16px 4px}.head h1{margin:0;font-size:26px}.head p{margin:2px 0 0;color:#666;font-size:13px}
.titlerow{display:flex;align-items:center;justify-content:space-between;gap:8px}
.search{padding:12px 16px 4px}.search label{display:block;font-size:12px;color:#555;margin-bottom:4px}
.sbox{display:flex;align-items:center;gap:8px;height:48px;border:1.5px solid #999;border-radius:10px;padding:0 12px;background:#fafafa;color:#666}
.sbox .q{flex:1;color:#1a1a1a}.sbox.focus{border-color:#111;background:#fff}
.branch{padding:12px 16px 4px}.seg{display:grid;grid-template-columns:repeat(3,1fr);border:1.5px solid #999;border-radius:10px;overflow:hidden}
.seg button{min-height:44px;border:0;border-inline-start:1px solid #ccc;background:#fff;font:inherit;font-size:14px}.seg button:first-child{border-inline-start:0}.seg button[aria-pressed=true]{background:#222;color:#fff;font-weight:700}
.bstat{display:flex;flex-wrap:wrap;gap:4px 14px;font-size:13px;color:#333;margin-top:8px}.bstat b{letter-spacing:.04em}.dot{display:inline-block;width:8px;height:8px;border-radius:50%;border:1.5px solid #111;margin-inline-end:4px;vertical-align:middle}.dot.on{background:#111}
.compactsel{min-height:44px;border:1.5px solid #999;border-radius:10px;padding:0 10px;font:inherit;font-size:13px;background:#fff;white-space:nowrap}
.season{margin:12px 16px 4px;border:1.5px dashed #888;border-radius:12px;padding:10px 12px}
.season h2{margin:0 0 6px;font-size:17px;display:flex;align-items:center;gap:8px}.badge{display:inline-block;font-size:11px;font-weight:700;border:1.5px solid #111;border-radius:6px;padding:1px 6px;letter-spacing:.03em}
.srow{display:grid;grid-template-columns:56px 1fr auto;gap:10px;align-items:center;min-height:64px;border-top:1px solid #e2e2e2;padding:4px 0}.srow:first-of-type{border-top:0}
.thumb{width:56px;height:56px;border-radius:8px;background:repeating-linear-gradient(45deg,#e9e9e9 0 6px,#f5f5f5 6px 12px);border:1px solid #d5d5d5}
.catbar{position:sticky;top:0;z-index:20;background:#fff;border-bottom:1px solid #ddd;display:flex;align-items:center;gap:6px;padding:6px 8px;height:56px}
.chips{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;flex:1;-webkit-mask-image:linear-gradient(var(--fade),transparent 0,#000 28px);mask-image:linear-gradient(var(--fade),transparent 0,#000 28px)}
.chip{flex:none;min-height:44px;display:inline-flex;align-items:center;text-decoration:none;color:inherit;padding:0 14px;border:1.5px solid #bbb;border-radius:22px;background:#fff;font:inherit;font-size:14px;white-space:nowrap}.chip.on{background:#111;color:#fff;border-color:#111;font-weight:700}
.subbar{position:sticky;top:56px;z-index:19;background:#fff;display:flex;gap:8px;overflow-x:auto;padding:6px 16px;border-bottom:1px solid #eee;scrollbar-width:none;height:56px;align-items:center}
.subchip{flex:none;min-height:44px;display:inline-flex;align-items:center;text-decoration:none;color:inherit;padding:0 14px;border:1px solid #ccc;border-radius:18px;font-size:13px;background:#f7f7f7;white-space:nowrap}.subchip.on{border-color:#111;background:#fff;font-weight:700}
.sec{padding:8px 16px 4px;scroll-margin-top:120px}.sec>h2{font-size:21px;margin:18px 0 2px}.cnt{color:#777;font-size:13px;margin:0 0 6px}
h3{font-size:15px;margin:14px 0 8px;color:#333;scroll-margin-top:120px}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.card{border:1px solid #d6d6d6;border-radius:12px;overflow:hidden;background:#fff;display:flex;flex-direction:column;min-width:0}
.card .img{aspect-ratio:1/1;background:repeating-linear-gradient(45deg,#e9e9e9 0 8px,#f5f5f5 8px 16px);display:flex;align-items:center;justify-content:center;color:#999;font-size:11px}
.card .body{padding:8px 10px 10px;display:flex;flex-direction:column;gap:2px;flex:1}
.n1{font-weight:700;font-size:15px;line-height:1.35;overflow-wrap:anywhere}.n2{font-size:12.5px;color:#555;line-height:1.35;overflow-wrap:anywhere;letter-spacing:.02em}
.pr{margin-top:auto;padding-top:6px;font-weight:700;font-size:15px;white-space:nowrap;font-variant-numeric:tabular-nums}
.pend{text-decoration:underline dotted #999;text-underline-offset:3px}
.note{font-size:12px;border:1.5px solid #111;border-radius:6px;padding:2px 6px;margin-top:4px;align-self:flex-start}
.demo{font-size:10px;background:#ffe9a8;color:#000;padding:0 4px;border-radius:3px;margin-inline-start:4px}
.card.dim .img,.card.dim .n1,.card.dim .n2,.card.dim .pr{opacity:.55}
.card.noimg .body{padding:12px;min-height:112px}
.card.hl{outline:3px solid #111;outline-offset:2px}
.legend{margin:16px;font-size:11px;color:#666;border-top:1px solid #eee;padding-top:8px}
.ovl{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:50}
.sheet{position:fixed;inset-inline:0;bottom:0;z-index:60;background:#fff;border-radius:18px 18px 0 0;max-height:86vh;overflow:auto;padding:8px 16px 24px}
.handle{width:44px;height:5px;border-radius:3px;background:#bbb;margin:4px auto 8px}
.sheet .x{position:absolute;top:10px;inset-inline-end:12px}
.sheet .bigimg{aspect-ratio:1/1;max-width:360px;margin:8px auto;border-radius:12px;background:repeating-linear-gradient(45deg,#e9e9e9 0 10px,#f5f5f5 10px 20px);display:flex;align-items:center;justify-content:center;color:#999;font-size:12px}
.sheet h2{margin:8px 0 0;font-size:22px}.sheet .en{font-size:14px;color:#555;letter-spacing:.02em}.sheet .bp{font-size:20px;font-weight:700;margin-top:8px}
.kv{font-size:13px;color:#444;border-top:1px solid #eee;margin-top:12px;padding-top:10px}
.sug{margin:4px 16px 0;border:1.5px solid #111;border-radius:10px;overflow:hidden}
.sug .it{display:grid;grid-template-columns:1fr auto;gap:2px 8px;padding:8px 12px;border-top:1px solid #eee;min-height:52px}.sug .it:first-child{border-top:0}.sug small{color:#666;grid-column:1/-1;font-size:12px}
.reshead{padding:10px 16px 0;font-size:14px;color:#333}
.empty{margin:20px 16px;border:1.5px dashed #999;border-radius:12px;padding:16px;text-align:center}.empty .btn{display:inline-flex;min-height:44px;align-items:center;padding:0 16px;border:1.5px solid #111;border-radius:10px;margin:8px 4px;font-size:14px;background:#fff}
.allcats .row{display:flex;justify-content:space-between;align-items:center;min-height:52px;border-top:1px solid #eee;font-size:16px}.allcats .row small{color:#666}.allcats .subs{font-size:12px;color:#666;padding:0 0 8px}
html[lang=en] .card .n1{font-size:14px;letter-spacing:.01em}
@media (max-width:359px){.grid{grid-template-columns:1fr}.card{flex-direction:row}.card .img{width:96px;flex:none}.card.noimg .body{min-height:auto}}
@media (min-width:600px){.grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
/* 1-column variant (very small screens) */
body.one .grid{grid-template-columns:1fr}body.one .card{flex-direction:row}body.one .card .img{width:96px;flex:none}body.one .card.noimg .body{min-height:auto}
/* desktop */
.desk .wrap{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:240px minmax(0,1fr);gap:32px;padding:0 24px}
.desk .side{position:sticky;top:0;align-self:start;max-height:100vh;overflow:auto;padding:16px 0}
.desk .side a{display:block;min-height:40px;padding:9px 12px;border-radius:8px;color:#1a1a1a;text-decoration:none;font-size:15px}.desk .side a.on{background:#111;color:#fff;font-weight:700}.desk .side .s2{font-size:13px;padding:6px 12px 6px 24px;min-height:32px;color:#555}
html[dir=rtl] .desk .side .s2{padding:6px 24px 6px 12px}
.desk .grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
@media (min-width:1200px){.desk .grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.desk .head,.desk .search,.desk .branch,.desk .sec{padding-inline:0}.desk .season{margin-inline:0}
.desk .toprow{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:20px;align-items:end}
.desk .srows{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:40px}.desk .srow{border-top:1px solid #e2e2e2}
.modal{position:fixed;z-index:60;inset:0;display:flex;align-items:center;justify-content:center}
.modal .box{background:#fff;border-radius:16px;width:min(760px,92vw);display:grid;grid-template-columns:320px 1fr;gap:24px;padding:24px;position:relative}
.modal .box .bigimg{margin:0;max-width:none}
'''
def card(r,lang,mode='img',extra=None,dim=False,hl=False):
    p1,p2=(r['ar'],r['en']) if lang=='ar' else (r['en'],r['ar'])
    l2='en' if lang=='ar' else 'ar'
    pend1=' pend' if (lang=='ar' and r['ar_pending']) else ''
    pend2=' pend' if (lang=='en' and r['ar_pending']) else ''
    img='' if mode=='noimg' else f'<div class="img" aria-hidden="true">1:1</div>'
    cls='card'+(' noimg' if mode=='noimg' else '')+(' dim' if dim else '')+(' hl' if hl else '')
    ex=f'<span class="note">{esc(extra[0])}<span class="demo">{T[lang]["demo"]}</span></span>' if extra else ''
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
            h+='<nav class="subbar" aria-label="sub">'+''.join(f'<a class="subchip{" on" if (active_sub==s or (active_sub is None and i==0)) else ""}" href="#{sid}-{slug(SUBLBL[s][0])}">{esc(SUBLBL[s][1] if lang=="ar" else SUBLBL[s][0])}</a>' for i,(s,g) in enumerate(groups))+'</nav>'
        for s,g in groups:
            h+=f'<h3 id="{sid}-{slug(SUBLBL[s][0])}">{esc(SUBLBL[s][1] if lang=="ar" else SUBLBL[s][0])}</h3><div class="grid">'+''.join(card(r,lang,mode,*(demo(r) if demo else (None,False,False))) for r in g)+'</div>'
    else:
        h+='<div class="grid">'+''.join(card(r,lang,mode,*(demo(r) if demo else (None,False,False))) for r in items)+'</div>'
    return h+'</section>'
def season_html(lang):
    items=by_cat('CAT-009'); t=T[lang]
    rows=''.join(f'<div class="srow"><div class="thumb" aria-hidden="true"></div><div><div class="n1{" pend" if (lang=="ar" and r["ar_pending"]) else ""}" lang="{lang}">{esc(r["ar"] if lang=="ar" else r["en"])}</div><div class="n2" lang="{"en" if lang=="ar" else "ar"}">{esc(r["en"] if lang=="ar" else r["ar"])}</div></div><div class="pr">{price(r,lang)}</div></div>' for r in items)
    name='SPRING · سبرينغ' if lang=='ar' else 'SPRING'
    return f'<section class="season" id="spring" aria-labelledby="h-spring"><h2 id="h-spring">{t["season"]} — {name} <span class="badge">{t["seasonal"]}</span></h2><div class="srows">{rows}</div></section>'
def branch_html(lang,sel='all',now=None,variant='A'):
    t=T[lang]; now=now or datetime(2026,10,3,19,30)
    lab={'all':t['all'],'drive':'DRIVE','house':'HOUSE'}
    stat=lambda b: f'<span><span class="dot{" on" if hours_status(b,now)[0]!="CLOSED" else ""}"></span><b>{b.upper()}</b> · {esc(hours_label(b,now,lang))}</span>'
    st=''.join(stat(b) for b in (['drive','house'] if sel=='all' else [sel]))
    if variant=='A':
        segs=''.join(f'<button aria-pressed="{"true" if k==sel else "false"}">{lab[k]}</button>' for k in ('all','drive','house'))
        return f'<div class="branch"><div class="seg" role="group" aria-label="branch">{segs}</div><div class="bstat">{st}</div></div>'
    return f'<button class="compactsel">{lab[sel]} ▾</button>', f'<div class="bstat" style="padding:4px 16px 0">{st}</div>'
def catbar(lang,active='speciality-coffee',scrolled=False):
    t=T[lang]
    AC='aria-current="true"'
    chips=''.join(f'<a class="chip{" on" if s[0]==active else ""}" href="#{s[0]}" {AC if s[0]==active else ""}>{esc(s[2] if lang=="ar" else s[3])}</a>' for s in SECTIONS)
    sb=f'<button class="hbtn" aria-label="{t["searchlbl"]}">{SVG_SEARCH}</button>' if scrolled else ''
    fade='to right' if lang=='ar' else 'to left'
    return f'<nav class="catbar" aria-label="categories" style="--fade:{fade}">{sb}<div class="chips">{chips}</div><button class="hbtn" aria-label="{t["allcats"]}" aria-haspopup="dialog">{SVG_LIST}</button></nav>'
def page(lang,body,title,cls=''):
    d='rtl' if lang=='ar' else 'ltr'
    return f'<!doctype html><html lang="{lang}" dir="{d}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{esc(title)}</title><style>{CSS}</style></head><body class="{cls}">{body}</body></html>'
def legend(lang):
    return '<p class="legend">'+('LOW-FI WIREFRAME. الصور مربعات رمادية (لا صور معتمدة بعد). الأسماء العربية المسطّرة بنقاط = من المصدر، بانتظار الاعتماد (D-137). "مثال" = حالة توضيحية وليست بيانات حقيقية. أسماء الأقسام الفرعية مقترحة.' if lang=='ar' else 'LOW-FI WIREFRAME. Grey boxes = images (none approved yet). Dotted-underlined Arabic = source name pending approval (D-137). DEMO = illustrative state, not real data. Subcategory names are proposals.')+'</p>'
def mobile(lang,state='default',mode='img',variant='A',sel='all',now=None,active='speciality-coffee',demo=None,cls=''):
    t=T[lang]
    tag=f'<div class="wf-tag">LOW-FI WIREFRAME · {lang.upper()} · {state}</div>'
    site=f'<header class="site"><span class="logo">SHELTER COFFEE</span><span style="display:flex;gap:6px"><button class="hbtn">{t["lang"]}</button><button class="hbtn" aria-label="menu">{SVG_LIST}</button></span></header>'
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
        body=tag+site+head+sq+f'<div class="empty" role="status"><p><b>{t["noresults"]} "xyz"</b></p><span class="btn">{t["clear"]}</span><span class="btn">{t["browse"]}</span></div>'+catbar(lang)+''.join(section_html(s,lang,mode) for s in SECTIONS[:1])+legend(lang)
        return page(lang,body,'wf',cls)
    body=tag+site+head+sq+br+season_html(lang)+catbar(lang,active,scrolled=(state=='scrolled'))+''.join(section_html(s,lang,mode,demo) for s in SECTIONS)+legend(lang)
    if state=='sheet':
        r=[x for x in R if x['en']=='ICED SPANISH LATTE'][0]
        p1,p2=(r['ar'],r['en']) if lang=='ar' else (r['en'],r['ar'])
        body+=f'<div class="ovl"></div><div class="sheet" role="dialog" aria-modal="true" aria-labelledby="sh-t"><div class="handle"></div><button class="hbtn x" aria-label="{t["close"]}">{SVG_X}</button><div class="bigimg">1:1</div><h2 id="sh-t" class="{"pend" if (lang=="ar" and r["ar_pending"]) else ""}">{esc(p1)}</h2><div class="en" lang="{"en" if lang=="ar" else "ar"}">{esc(p2)}</div><div class="bp">{price(r,lang)}</div><div class="kv">{"COLD DRINKS › ICED LATTES & FLAVORS" if lang=="en" else "مشروبات باردة › آيس لاتيه ونكهات"}<br>{"الوصف والمكونات: لا تظهر حتى تُعتمد (لا حقول فارغة)" if lang=="ar" else "Description/ingredients: hidden until approved (no empty fields)"}</div></div>'
    if state=='allcats':
        rows=''
        for s in SECTIONS:
            n=len(sec_items(s)); subs=s[4]
            sl=' · '.join((SUBLBL[x][1] if lang=='ar' else SUBLBL[x][0]) for x in (subs or []) if x!='PENDING')
            rows+=f'<div class="row"><span>{esc(s[2] if lang=="ar" else s[3])}</span><small>{n}</small></div>'+(f'<div class="subs">{esc(sl)}</div>' if sl else '')
        body+=f'<div class="ovl"></div><div class="sheet allcats" role="dialog" aria-modal="true" aria-label="{t["allcats"]}"><div class="handle"></div><button class="hbtn x" aria-label="{t["close"]}">{SVG_X}</button><h2>{t["allcats"]}</h2>{rows}</div>'
    return page(lang,body,'wf',cls)
def desktop(lang,state='default',mode='img',sel='all',now=None,demo=None):
    t=T[lang]
    tag=f'<div class="wf-tag">LOW-FI WIREFRAME · DESKTOP · {lang.upper()} · {state}</div>'
    site=f'<header class="site" style="padding:0 24px"><span class="logo">SHELTER COFFEE</span><span style="display:flex;gap:8px;align-items:center;font-size:14px">{"الرئيسية · المنيو · الفروع · من نحن · تواصل" if lang=="ar" else "Home · Menu · Locations · About · Contact"} <button class="hbtn">{t["lang"]}</button></span></header>'
    side=''.join(f'<a href="#{s[0]}" class="{"on" if i==0 else ""}">{esc(s[2] if lang=="ar" else s[3])}</a>'+''.join(f'<a class="s2" href="#">{esc(SUBLBL[x][1] if lang=="ar" else SUBLBL[x][0])}</a>' for x in (s[4] or []) if x!='PENDING' and i in (1,)) for i,s in enumerate(SECTIONS))
    BR=branch_html(lang,sel,now,"A").replace('class="branch"','class="branch" style="padding:12px 0 4px"')
    main=f'<div class="head"><h1>{t["menu"]}</h1><p>{t["note"]}</p></div><div class="toprow"><div class="search" style="padding:12px 0 4px"><label>{t["searchlbl"]}</label><div class="sbox">{SVG_SEARCH}<span class="q">{t["search"]}</span></div></div>{BR}</div>'
    main+=season_html(lang)+''.join(section_html(s,lang,mode,demo,subbar=False) for s in SECTIONS)+legend(lang)
    body=tag+site+f'<div class="wrap"><aside class="side"><nav aria-label="categories">{side}</nav></aside><main>{main}</main></div>'
    if state=='modal':
        r=[x for x in R if x['en']=='ICED SPANISH LATTE'][0]
        p1,p2=(r['ar'],r['en']) if lang=='ar' else (r['en'],r['ar'])
        body+=f'<div class="ovl"></div><div class="modal"><div class="box" role="dialog" aria-modal="true"><button class="hbtn x" style="position:absolute;top:12px;inset-inline-end:12px" aria-label="{t["close"]}">{SVG_X}</button><div class="bigimg" style="aspect-ratio:1/1;border-radius:12px;background:repeating-linear-gradient(45deg,#e9e9e9 0 10px,#f5f5f5 10px 20px);display:flex;align-items:center;justify-content:center;color:#999">1:1</div><div><h2 style="margin:24px 0 0;font-size:26px">{esc(p1)}</h2><div style="color:#555;letter-spacing:.02em" lang="{"en" if lang=="ar" else "ar"}">{esc(p2)}</div><div style="font-size:22px;font-weight:700;margin-top:12px">{price(r,lang)}</div><div class="kv">{"Esc يغلق · التركيز يعود للبطاقة" if lang=="ar" else "Esc closes · focus returns to the card"}</div></div></div></div>'
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
