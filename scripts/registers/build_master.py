#!/usr/bin/env python3
"""Builds the SHELTER master registers (markdown) from the consolidated audit JSON.
Inputs: cons/G1..G9.json, out/I-docs-registry.json, out/[A-H]-*.json (raw), manual/*.md, tooling/reports/results.json (stats only).
Outputs (repo): docs/SHELTER-WEBSITE-MASTER-REQUIREMENTS.md, docs/MASTER-DECISION-REGISTER.md, docs/CONFLICT-REGISTER.md,
docs/PENDING-OWNER-INPUT.md, docs/REQUIREMENTS-TRACEABILITY-MATRIX.md, docs/IMPLEMENTATION-GAP-ANALYSIS.md, docs/IMPLEMENTATION-PLAN.md,
docs/governance/DECISION-LOG.md (append D-150+ and status sync), audit snapshot JSON."""
import json, re, glob, os, collections

AUD = os.path.dirname(os.path.abspath(__file__))
REPO = os.path.dirname(os.path.dirname(AUD))
TODAY = '2026-10-01'
J = lambda p: json.load(open(os.path.join(AUD, p)))

def esc(s):
    s = '' if s is None else str(s)
    return s.replace('|', '\\|').replace('\r', '').replace('\n', '<br>').strip()

TEMP_MAP = {}
def write(rel, text):
    for t in sorted(TEMP_MAP, key=len, reverse=True):
        text = text.replace(t, TEMP_MAP[t])
    p = os.path.join(REPO, rel); os.makedirs(os.path.dirname(p), exist_ok=True)
    open(p, 'w').write(text.rstrip() + '\n'); print(f'wrote {rel} ({len(text)//1024} KB)')

# ---------------------------------------------------------------- load
G = {g: J(f'cons/{g}.json') for g in [f'G{i}' for i in range(1, 9)]}
G9 = J('cons/G9.json')
EXTRA = {g: J(f'cons/{g}.json') for g in ['G10', 'G11', 'G12', 'G13', 'G14', 'G15', 'G16', 'G17', 'G18', 'G19', 'G20', 'G21', 'G22', 'G23', 'G24', 'G25', 'G26', 'G27', 'G28', 'G29', 'G30', 'G31', 'G32', 'G33', 'G34', 'G35', 'G36', 'G37', 'G38', 'G39', 'G40', 'G41', 'G42', 'G43', 'G44', 'G45', 'G46', 'G47', 'G48', 'G49', 'G50', 'G51', 'G52', 'G53', 'G54', 'G55'] if os.path.exists(os.path.join(AUD, f'cons/{g}.json'))}
I = J('out/I-docs-registry.json')
MSGS = J('msgs-stats.json')  # counts only — the raw Owner messages are not kept in the repo
RAW = {r['key']: r for f in sorted(glob.glob(os.path.join(AUD, 'out/[A-H]-*.json'))) for r in json.load(open(f))['items']}

AREAS = {
 '01': 'Brand & Design System', '02': 'Information Architecture', '03': 'Global Website Architecture', '04': 'Responsive Design',
 '05': 'Navigation', '06': 'Homepage', '07': 'Menu', '08': 'Products', '09': 'Search', '10': 'Branches & Locations', '11': 'Hours',
 '12': 'Campaigns & Events', '13': 'About', '14': 'Contact', '15': 'Blog / Coffee Knowledge', '16': 'Franchise', '17': 'CMS',
 '18': 'Owner Dashboard', '19': 'Analytics', '20': 'GA4 / GTM', '21': 'Search Console', '22': 'Google Business Profile & Maps', '23': 'SEO',
 '24': 'AEO / GEO / AI Search', '25': 'Schema', '26': 'Performance', '27': 'Accessibility', '28': 'Motion', '29': 'Media & Images',
 '30': 'Security', '31': 'Permissions & Auth', '32': 'Audit / Versioning', '33': 'Integrations & Paid Services', '34': 'Cloudflare / Hosting / DNS',
 '35': 'Testing & QA', '36': 'Deployment & Production Safety', '37': 'Monitoring & Alerts', '38': 'Documentation & Governance',
 '39': 'Content & Copy', '40': 'Internationalization (AR/EN · RTL/LTR · Global)', '41': 'Tooling', '42': 'Privacy & Legal', '43': 'UX Principles',
 '44': 'Careers & Recruitment', '45': 'Dynamic Experience Engine', '46': 'Platform Quality & Operations',
 '47': 'Master Data & Channel Sync', '48': 'Design System & UI Consistency', '49': 'Infrastructure, Release & Operations', '50': 'Build Mode & Delivery Governance'}
PREFIX_AREA = {'BRAND': '01', 'IA': '02', 'WEB': '03', 'RESP': '04', 'NAV': '05', 'HOME': '06', 'MENU': '07', 'PROD': '08', 'SRCH': '09',
 'BRANCH': '10', 'HOURS': '11', 'CAMP': '12', 'ABOUT': '13', 'CONTACT': '14', 'BLOG': '15', 'FRAN': '16', 'CMS': '17', 'DASH': '18',
 'ANL': '19', 'GOOGLE': '20', 'GSC': '21', 'GBP': '22', 'SEO': '23', 'AEO': '24', 'SCHEMA': '25', 'PERF': '26', 'A11Y': '27',
 'MOTION': '28', 'MEDIA': '29', 'SEC': '30', 'PERM': '31', 'AUDIT': '32', 'INT': '33', 'CF': '34', 'TEST': '35', 'DEPLOY': '36',
 'MON': '37', 'GOV': '38', 'CONTENT': '39', 'I18N': '40', 'TOOL': '41', 'PRIV': '42', 'UX': '43', 'CAREERS': '44', 'DX': '45', 'OPS': '46', 'MDH': '47', 'DS': '48', 'INFRA': '49', 'BUILD': '50'}
# Owner's 36-step sequence puts Design System/UI before Technical Architecture → P05 = design, P06 = platform (swap of the agents' codes)
PHASE_SWAP = {'P05': 'P06', 'P06': 'P05'}
PHASES = {
 'P00': 'Governance & Master Source of Truth', 'P01': 'Discovery completion', 'P02': 'Menu IA & Wireframes', 'P03': 'Site-wide IA, Sitemap, URL & SEO architecture',
 'P04': 'Owner Dashboard & CMS architecture', 'P05': 'Brand, Design System & Visual Design', 'P06': 'Platform & technical architecture (DB-08)',
 'P07': 'Content & media approval', 'P08': 'Build on staging (website + CMS + Dashboard V1)', 'P09': 'Google ecosystem implementation',
 'P10': 'QA gate', 'P11': 'SEO migration & launch', 'P12': 'Post-launch & V2'}
MSG_NAME = {'M33': 'Unified Master Data + Channel Sync', 'M34': 'Design System global consistency', 'M35': 'Final completeness addendum', 'M36': 'Planning closed — start build', 'M37': 'Development toolchain', 'M38': 'Owner clarification rule (global)', 'M39': 'Access delegation + brand from old site', 'M40': 'Premium UI/UX/Motion directive', 'M41': 'PO-070 answer (DNS)', 'M42': 'PO-072 answer (email auth)', 'M43': 'PO-073 answer (TLS)', 'M44': 'Build order: pages first', 'M45': 'PO-074 answer (city list)', 'M46': 'FR numbering start', 'M47': 'Franchise page final content V1', 'M48': 'JOB numbering + PO-075 answer', 'M49': 'PO-076 English for two franchise sections', 'M28': 'Careers & Recruitment spec', 'M29': 'Franchise / Partnership spec', 'M30': 'Owner-only dashboard & no-code', 'M31': 'Dynamic Experience Engine', 'M32': 'Gap-closure addendum', 'M01': 'Master Project Prompt', 'M02': 'R1 follow-up', 'M03': 'R1 answers', 'M04': 'R1 corrections', 'M10': 'R2 / URL', 'M11': 'Google master prompt',
 'M12': 'Google execution addendum', 'M13': 'Root / URL decisions', 'M14': 'Contact & root decisions', 'M15': 'R3 menu intake', 'M17': 'R3 menu file', 'M18': 'Menu architecture corrections',
 'M19': 'R3 P0 decisions', 'M20': 'Menu naming decisions', 'M21': 'Inventory v1.0 acceptance', 'M23': 'Menu IA / UX brief', 'M24': 'Frontend tooling', 'M25': 'Owner Dashboard prompt',
 'M26': 'Responsive mandatory', 'M27': 'Consolidation prompt'}

def msgs_of(keys):
    return sorted({k.split('-')[0] for k in keys if re.match(r'M\d\d', k)}, key=lambda m: int(m[1:3]))

reqs = []
for g, c in G.items():
    for r in c['requirements']:
        r['_g'] = g; r['_area'] = PREFIX_AREA[r['id'].split('-')[0]]
        r['phase'] = PHASE_SWAP.get(r.get('phase'), r.get('phase'))
        reqs.append(r)
for g, c in EXTRA.items():
    for r in c.get('requirements', []):
        r['_g'] = g; r['_area'] = PREFIX_AREA[r['id'].split('-')[0]]
        reqs.append(r)
REQ = {r['id']: r for r in reqs}
assert len(REQ) == len(reqs), [i for i, n in collections.Counter(r['id'] for r in reqs).items() if n > 1]
UPD_OTHER = []  # updates that target decisions / pending / other ids
for g, c in EXTRA.items():
    for u in c.get('updates_to_existing', []):
        tid = u['id']
        if tid in REQ:
            r = REQ[tid]
            r['notes'] = ((r.get('notes') or '') + f" ↻ {u.get('source','')}: {u['change']}").strip()
            src = (u.get('source') or '').split()[0] if u.get('source') else ''
            if src and src not in r.setdefault('sources', []): r['sources'].append(src)
            if u.get('type') == 'SUPERSEDE':
                r.setdefault('history', []).append({'old': r['requirement'], 'source': ', '.join(msgs_of(r.get('sources', []))), 'superseded_by': u.get('source', '')})
                r['status'] = 'SUPERSEDED'
            elif u.get('new_status') and u['new_status'] != r.get('status') and r.get('status') != 'SUPERSEDED':
                r.setdefault('history', []).append({'old': f"status {r.get('status')}", 'source': '', 'superseded_by': u.get('source', '')})
                r['status'] = u['new_status']
            if u.get('new_priority'): r['priority'] = u['new_priority']
            if u.get('new_phase'): r['phase'] = u['new_phase']
            # Build evidence for the traceability matrix: implementation status, tested flag, code and test references.
            if u.get('new_impl'): r['impl_status'] = u['new_impl']
            if u.get('tested'): r['tested'] = u['tested']
            for k in ('code_refs', 'test_refs'):
                for ref in u.get(k) or []:
                    if ref not in r.setdefault(k, []) and r[k] is not None: r[k].append(ref)
        else:
            UPD_OTHER.append(dict(u, _g=g))


# ---------------------------------------------------------------- new decisions → D-150+
MERGE_ND = {'G3-ND-01': 'G2-ND-04', 'G5-ND-05': 'G8-ND-01'}
nds = []
for g, c in G.items():
    for n in c['new_decisions']:
        n['_g'] = g; nds.append(n)
byt = {n['temp_id']: n for n in nds}
for a, b in MERGE_ND.items():
    byt[b]['sources'] = sorted(set(byt[b].get('sources', [])) | set(byt[a].get('sources', [])))
    byt[b]['decision'] += ' (+ ' + byt[a]['decision'] + ')'
nds = [n for n in nds if n['temp_id'] not in MERGE_ND]
nds.sort(key=lambda n: (min([int(s[1:3]) for s in n.get('sources', []) if re.match(r'M\d\d', s)] or [99]), n['temp_id']))
ND_ID = {}
for i, n in enumerate(nds):
    n['id'] = f'D-{150 + i}'; ND_ID[n['temp_id']] = n['id']
for a, b in MERGE_ND.items():
    ND_ID[a] = ND_ID[b]
nxt = 150 + len(nds)
for g, c in EXTRA.items():
    for n in c.get('new_decisions', []):
        n['_g'] = g; n['id'] = f'D-{nxt}'; ND_ID[n['temp_id']] = n['id']; nxt += 1; nds.append(n)
TEMP_MAP.update(ND_ID)

# ---------------------------------------------------------------- decision register
dstat = {}
for g, c in G.items():
    for d in c['decision_status']:
        dstat[d['id']] = d
docdec = {d['id']: d for d in I['decisions'] + I['open_decisions']}
CATS = ['FROZEN', 'APPROVED', 'APPROVED WITH CONDITIONS', 'PENDING OWNER INPUT', 'PENDING VERIFICATION', 'DEFERRED', 'REJECTED', 'SUPERSEDED']
dec_rows = []
for did, d in dstat.items():
    src = docdec.get(did, {})
    dec_rows.append({'id': did, 'area': src.get('area', 'Governance' if did.startswith('RISK') else ''), 'cat': d['category'],
        'current': d.get('current_text') or src.get('decision_ar', ''), 'old': d.get('old_text', ''), 'superseded_by': d.get('superseded_by', ''),
        'reason': src.get('reason', ''), 'order': src.get('order', ''), 'impact': d.get('impact') or src.get('impact', ''), 'doc': src.get('doc', '')})
for n in nds:
    srcs = msgs_of(n.get('sources', []))
    dec_rows.append({'id': n['id'], 'area': n.get('area', ''), 'cat': n.get('status') if n.get('status') in CATS else 'APPROVED',
        'current': n['decision'], 'old': '', 'superseded_by': '', 'reason': n.get('reason', '') or 'توجيه صريح من الـOwner',
        'order': f"{', '.join(srcs)} · {n.get('date', TODAY)} (سُجّل في التدقيق)", 'impact': n.get('impact', ''), 'doc': 'docs/governance/DECISION-LOG.md'})

def did_key(i):
    m = re.match(r'([A-Z§\-]+?)-?(\d+)', i.replace('§', ''))
    return (i.split('-')[0], int(re.sub(r'\D', '', i) or 0))
_dr = {r['id']: r for r in dec_rows}
for g, c in EXTRA.items():
    for u in c.get('updates_to_existing', []):
        r = _dr.get(u['id'])
        if not r: continue
        t = u.get('type')
        if t == 'SUPERSEDE' and r['cat'] != 'SUPERSEDED':
            r['old'] = r['current']; r['cat'] = 'SUPERSEDED'; r['superseded_by'] = u.get('source', ''); r['current'] = u['change']
        elif t == 'RESOLVE':
            r['old'] = r['current']; r['cat'] = 'APPROVED WITH CONDITIONS' if 'ADR-001' in u['change'] else 'APPROVED'; r['current'] = u['change']
        else:
            r['impact'] = ((r.get('impact') or '') + f" ↻ {u.get('source','')}: {u['change']}").strip()
dec_rows.sort(key=lambda r: did_key(r['id']))
dec_by_cat = collections.defaultdict(list)
for r in dec_rows:
    dec_by_cat[r['cat']].append(r)

# ---------------------------------------------------------------- conflicts
CF = {c['id']: dict(c, refs=[]) for c in G9['conflicts']}
MAP = {  # (group, 1-based index) → existing CF-M id, 'NEW', or ('DUP', (group, idx))
 'G1': {1: 'CF-M-002', 2: 'NEW', 3: 'CF-M-067', 4: 'NEW', 5: 'CF-M-001', 6: 'NEW', 7: 'NEW', 8: 'NEW', 9: 'NEW', 10: 'CF-M-010', 11: 'NEW', 12: 'NEW', 13: 'NEW', 14: 'NEW'},
 'G2': {1: 'NEW', 2: 'NEW', 3: 'CF-M-062', 4: 'CF-M-025', 5: 'CF-M-010', 6: 'CF-M-007', 7: ('DUP', ('G1', 7)), 8: 'NEW', 9: 'NEW', 10: 'NEW', 11: 'NEW'},
 'G3': {1: 'CF-M-038', 2: 'CF-M-039', 3: 'CF-M-040', 4: 'CF-M-043', 5: 'CF-M-043', 6: 'CF-M-037', 7: 'CF-M-056', 8: 'CF-M-059', 9: 'CF-M-058', 10: 'CF-M-065',
        11: 'CF-M-053', 12: 'CF-M-060', 13: 'CF-M-054', 14: 'NEW', 15: 'CF-M-046', 16: 'CF-M-061', 17: 'NEW'},
 'G4': {1: 'CF-M-021', 2: 'CF-M-023', 3: 'CF-M-024', 4: 'CF-M-021', 5: 'CF-M-026', 6: 'NEW', 7: 'CF-M-014', 8: 'CF-M-025', 9: 'CF-M-058', 10: 'CF-M-031',
        11: 'CF-M-018', 12: 'CF-M-018', 13: 'CF-M-017', 14: 'CF-M-019', 15: 'CF-M-020', 16: 'CF-M-015'},
 'G5': {1: 'NEW', 2: 'CF-M-011', 3: 'NEW', 4: 'CF-M-012', 5: 'CF-M-013', 6: 'NEW', 7: 'CF-M-063', 8: 'NEW', 9: 'NEW', 10: 'NEW'},
 'G6': {1: 'CF-M-065', 2: 'CF-M-065', 3: 'CF-M-065', 4: 'NEW', 5: 'NEW', 6: 'CF-M-067', 7: 'NEW', 8: 'NEW', 9: 'NEW', 10: 'NEW', 11: 'NEW', 12: 'NEW', 13: 'NEW',
        14: 'CF-M-023', 15: 'NEW', 16: 'NEW', 17: 'CF-M-066'},
 'G7': {1: 'CF-M-063', 2: 'CF-M-004', 3: 'NEW', 4: 'CF-M-055', 5: 'NEW', 6: 'NEW', 7: ('DUP', ('G6', 11)), 8: 'CF-M-003'},
 'G8': {1: 'CF-M-068', 2: 'CF-M-069', 3: 'NEW', 4: ('DUP', ('G7', 5)), 5: 'NEW', 6: ('DUP', ('G2', 2)), 7: 'NEW', 8: 'NEW', 9: 'NEW'},
}
OVR = {  # status / winner overrides decided during assembly (documented)
 ('G6', 4): ('RESOLVED', '`menu_item_id` (نفس المعنى)', 'تفصيل تقني: القاعدة 8 في M27، وM23 §56 يسمح بإعادة التسمية التقنية الموثقة مع حفظ المعنى. السبب: تجنب التعارض مع `item_id` الخاص بالتجارة الإلكترونية في GA4. يُعرض ضمن قاموس الأحداث النهائي (PO-043)', ''),
 ('G6', 5): ('RESOLVED', '`event_title` لاسم الفعالية', 'تفصيل تقني: `event_name` اسم بُعد مدمج في GA4 ويسبب التباسًا في التقارير. المعنى محفوظ، ويُعرض ضمن القاموس (PO-043)', ''),
 ('G6', 9): ('RESOLVED', 'المعمارية المستهدفة Website → GTM → GA4 (M12)، والإعداد النهائي بعد جرد الموجود', 'آخر توجيه صريح (M12، الملاحظة الختامية) يحدد الاتجاه، و"قارن ثم أوصِ" يبقى خطوة تحقق (جرد Site Kit/GTM الحالي) وليس خيارًا مفتوحًا. لا ازدواج', ''),
 ('G7', 6): ('RESOLVED', 'طبقات M25 §68 = نطاق إصدار الـDashboard (V1 / لاحقًا) · P0–P3 = شدة/أولوية المتطلب (M27 §33)', 'مفهومان مختلفان لا يتعارضان. الوحدات غير المصنفة (Blog editor، Users UI، Global Search، FAQ، Events، Social Links) تُقترح في P04 وتُعتمد عند بوابة H', ''),
 ('G8', 5): ('RESOLVED', 'Motion مسموح للـSheet/Modal في المنيو: تحميل Lazy عند أول فتح، خارج المسار الحرج، ضمن ميزانية JS', 'توجيه الـOwner في M24 (Motion أساسية، ومن استخداماتها menu sheet) يتقدم على مسودة Claude غير المعتمدة (PERFORMANCE-BUDGET: CSS فقط) — القاعدة 5. شرط M24/M27: لا مساس بـLCP/INP/CLS، والأداء يتقدم على الحركة الزخرفية. **حُدّثت الوثيقتان**', 'docs/menu-ia/PERFORMANCE-BUDGET.md · docs/FRONTEND-TOOLING.md'),
 ('G6', 7): ('OWNER DECISION REQUIRED', '', 'اعتماد قاموس الأحداث النهائي حق للـOwner (M11 §26). M27 يطلب ضم "أي Events إضافية وردت". غير مانع حتى P09 ← **PO-043**', ''),
 ('G6', 8): ('OWNER DECISION REQUIRED', '', 'M12 §11 "حدد معي الأحداث المهمة تجاريًا". "بلا Key Events في المنيو" توصية من Claude وليست قرارًا ← **PO-036**. صياغة D-149 صُححت في الـLog', 'docs/governance/DECISION-LOG.md (D-149)'),
 ('G6', 15): ('OWNER DECISION REQUIRED', '', 'لا قرار Owner/قانوني بعد بشأن الـConsent. المسودات تفترضه ← **PO-019**', ''),
 ('G3', 17): ('OWNER DECISION REQUIRED', 'الافتراضي حتى القرار: **لا** تدخل الأسماء العربية غير المعتمدة في فهرس البحث المرسل للمتصفح (D-091)', 'إدخالها يحسن إيجاد 152 صنفًا بالعربي، لكنه "استخدام" لاسم غير معتمد على الموقع ← **PO-042**', ''),
 ('G5', 9): ('OWNER DECISION REQUIRED', '', 'جزء من اعتماد معمارية الـSEO/URL ← **PO-005**', ''),
 ('G5', 10): ('OWNER DECISION REQUIRED', '', 'لم يُعتمد صراحة ← **PO-005**', ''),
 ('G1', 7): ('OWNER DECISION REQUIRED', 'الافتراضي حتى القرار: **تسلسل الـOwner** (Design System/UI قبل Technical Architecture). الخطة مرتبة هكذا: P05 تصميم ← P06 منصة', 'التوصية: حسم DB-08 مبكرًا (بعد P03 وP04)، لأن أدوات التصميم (Storybook/shadcn/Tailwind) والـCMS تعتمد عليه ← **PO-044**', ''),
}
new_cfs, dup_note = [], collections.defaultdict(list)
n = 75
for g in ['G1', 'G2', 'G3', 'G4', 'G5', 'G6', 'G7', 'G8']:
    for idx, c in enumerate(G[g]['conflicts'], 1):
        tgt = MAP[g][idx]
        if isinstance(tgt, tuple):
            dup_note[tgt[1]].append(f'{g}#{idx}'); continue
        if tgt != 'NEW':
            CF[tgt]['refs'].append(f'{g}#{idx}'); continue
        n += 1; cid = f'CF-M-{n:03d}'
        cc = dict(c); cc['id'] = cid; cc['refs'] = [f'{g}#{idx}']; cc['_key'] = (g, idx)
        if (g, idx) in OVR:
            st, win, why, files = OVR[(g, idx)]
            cc['status'] = st
            if win: cc['winner'] = win
            cc['why'] = why + (' · ' + cc.get('why', '') if cc.get('why') else '')
            if files: cc['files'] = (cc.get('files') or []) + [files]
        cc['topic'] = re.sub(r'^G\d-CF-\d+:\s*', '', cc['topic'])
        new_cfs.append(cc); CF[cid] = cc
for c in new_cfs:
    for d in dup_note.get(c['_key'], []):
        c['refs'].append(d)
for g, c in EXTRA.items():
    for idx, cc in enumerate(c.get('conflicts', []), 1):
        n += 1; cid = f'CF-M-{n:03d}'
        if cc.get('id'): TEMP_MAP[cc['id']] = cid
        cc = dict(cc); cc['id'] = cid; cc['refs'] = [f'{g}#{idx}']
        cc['topic'] = re.sub(r'^G\d+-CF-\d+:\s*', '', cc.get('topic', ''))
        CF[cid] = cc
# An Owner answer that settles an open conflict: updates_to_existing {id: CF-M-xxx, type: RESOLVE, change: how}.
for u in UPD_OTHER:
    if u['id'] in CF and u.get('type') == 'RESOLVE':
        CF[u['id']]['status'] = 'RESOLVED'
        CF[u['id']]['winner'] = f"{u['change']} ({u.get('source', '')})"
conflicts = list(CF.values())
TFLAGS = [dict(t, _g=g) for g, c in EXTRA.items() for t in c.get('technical_flags', [])]
cf_stat = collections.Counter(c['status'] for c in conflicts)

# ---------------------------------------------------------------- pending
PO = list(G9['pending'])
PO += [
 {'id': 'PO-042', 'item': 'الأسماء العربية المصدرية غير المعتمدة (152) داخل فهرس البحث: هل تُستخدم كمرادفات بحث مخفية (لا تُعرض) حتى يكتمل اعتمادها، أم تُستبعد تمامًا؟ الافتراضي الآن: تُستبعد (D-091)', 'category': 'NON-BLOCKING', 'type': 'BUSINESS DECISION', 'blocks': 'جودة البحث العربي في المنيو عند الإطلاق قبل اكتمال PO-003', 'refs': ['CF-M (G3#17)', 'D-091', 'SPEC §8'], 'count': '', 'notes': ''},
 {'id': 'PO-043', 'item': 'اعتماد قاموس أحداث القياس النهائي للموقع كاملًا: الأحداث المعتمدة الـ14 + الأحداث المذكورة سابقًا وغير المحسومة (branch_view · social_click · event_view) + التسميات التقنية الموثقة (`menu_item_id`، `event_title`)', 'category': 'NON-BLOCKING', 'type': 'BUSINESS DECISION', 'blocks': 'P09 (إعداد GA4/GTM). لا يمنع التوثيق ولا البناء', 'refs': ['M11 §26', 'M27 §18', 'D-149', 'G6 event_registry'], 'count': '', 'notes': 'يُعرض مع ANALYTICS-MEASUREMENT-PLAN في P04'},
 {'id': 'PO-044', 'item': 'ترتيب قرار المنصة (DB-08): الالتزام بتسلسلك الأصلي (التصميم ثم المعمارية التقنية)، أم حسم المنصة مبكرًا بعد P03 وP04 (توصيتنا)؟', 'category': 'NON-BLOCKING', 'type': 'BUSINESS DECISION', 'blocks': 'ترتيب P05/P06 في الخطة. الافتراضي تسلسلك الأصلي', 'refs': ['M01 §4', 'D-001', 'DB-08', 'PO-006'], 'count': '', 'notes': ''},
 {'id': 'PO-045', 'item': 'اعتماد حزمة المرجع الرئيسي: Master Requirements + Decision Register + Conflict Register + هذه القائمة + Traceability + Gap Analysis + Implementation Plan', 'category': 'BLOCKING', 'type': 'BUSINESS DECISION', 'blocks': 'إغلاق P00. وتثبيت CLAUDE.md كمرجع معتمد (M27 §29)', 'refs': ['M27'], 'count': '', 'notes': 'القرارات المعتمدة سابقًا داخلها تبقى معتمدة'},
]
po_ids = {p['id']: p for p in PO}
po_n = 46
for g, c in EXTRA.items():
    for p in c.get('pending_owner', []):
        if p['id'] in po_ids:
            po_ids[p['id']]['notes'] = ((po_ids[p['id']].get('notes') or '') + ' ↻ ' + p.get('item', '')).strip()
            continue
        p = dict(p); TEMP_MAP[p['id']] = f'PO-{po_n:03d}'; p['id'] = f'PO-{po_n:03d}'; po_n += 1
        PO.append(p); po_ids[p['id']] = p
for u in UPD_OTHER:
    if u['id'] in po_ids:
        q = po_ids[u['id']]
        q['notes'] = ((q.get('notes') or '') + f" ↻ {u.get('source','')} ({u.get('type','')}): {u['change']}").strip()
        if u.get('type') == 'RECLASSIFY' and q['category'] != 'RESOLVED': q['category'] = 'NON-BLOCKING'
        if u.get('type') == 'RESOLVE': q['category'] = 'RESOLVED'
po_stat = collections.Counter((p['category'], p['type']) for p in PO)

# ---------------------------------------------------------------- stats
S = {
 'messages': MSGS['messages'], 'chars': MSGS['chars'], 'raw': len(RAW), 'reqs': len(reqs),
 'status': collections.Counter(r['status'] for r in reqs), 'impl': collections.Counter(r['impl_status'] for r in reqs),
 'prio': collections.Counter(r['priority'] for r in reqs), 'type': collections.Counter(r['type'] for r in reqs),
 'tested': collections.Counter(r['tested'] for r in reqs), 'phase': collections.Counter(r['phase'] for r in reqs),
 'dec': collections.Counter(r['cat'] for r in dec_rows), 'decisions': len(dec_rows), 'new_dec': len(nds),
 'cf': cf_stat, 'cf_total': len(conflicts), 'po': po_stat, 'po_total': sum(p['category'] != 'RESOLVED' for p in PO),
}
covered = set(k for r in reqs for k in r.get('sources', []))
dropped = {d['key']: d['reason'] for c in G.values() for d in c.get('dropped', [])}
S['covered'] = len(covered & set(RAW)); S['dropped'] = len(set(dropped) & set(RAW)); S['unaccounted'] = len(set(RAW) - covered - set(dropped))
json.dump({k: ({' / '.join(kk) if isinstance(kk, tuple) else kk: vv for kk, vv in v.items()} if isinstance(v, collections.Counter) else v) for k, v in S.items()}, open(os.path.join(AUD, 'final-stats.json'), 'w'), ensure_ascii=False, indent=1, default=str)

def area_reqs(a):
    return [r for r in reqs if r['_area'] == a]

IMPL_DONE = {'IMPLEMENTED — NOT TESTED', 'IMPLEMENTED — NOT YET VERIFIED', 'TESTED', 'FROZEN'}

# ================================================================ 1. MASTER REQUIREMENTS
def req_cell(r):
    parts = [f"**{esc(r['title'])}**", esc(r['requirement'])]
    for d in r.get('details') or []:
        parts.append('• ' + esc(d))
    for h in r.get('history') or []:
        if h.get('old'):
            parts.append(f"↺ **سابقًا:** {esc(h['old'])} ({esc(h.get('source',''))}) ← {esc(h.get('superseded_by',''))}")
    if r.get('conflict'):
        parts.append('⚠️ **تعارض:** ' + esc(r['conflict']))
    if r.get('notes'):
        parts.append('ℹ️ ' + esc(r['notes']))
    return '<br>'.join(p for p in parts if p)

def refs_cell(r):
    ms = msgs_of(r.get('sources', []))
    dec = [ND_ID.get(x, x) for x in (r.get('decision_refs') or [])]
    return esc(' · '.join(ms) + (' — ' + ', '.join(dec) if dec else ''))

out = [f"""# SHELTER COFFEE WEBSITE — MASTER REQUIREMENTS

> **SINGLE SOURCE OF TRUTH** لمتطلبات المشروع كله: الموقع + الـOwner Dashboard + الـCMS + Google + الجودة.
> **الحالة:** `DRAFT — PENDING OWNER APPROVAL` (PO-045). كل متطلب اعتمده الـOwner سابقًا يبقى معتمدًا، والاعتماد المطلوب هو للتجميع نفسه.
> **آخر تحديث:** {TODAY} · **المصدر:** تدقيق كامل للمحادثة ({S['messages']} رسالة، حوالي {S['chars']//1000} ألف حرف) + ملفات المشروع + الكود والأدوات.

## كيف بُني هذا الملف
1. **الاستخراج:** كل رسائل الـOwner قُرئت كاملة بالترتيب M01 ← M{S['messages']:02d}.
   - M01–M27: استُخرج منها **{S['raw']} بندًا خامًا** بمصدره ونصه الحرفي.
   - M28 (التوظيف) وM29 (الشراكات) وM30 (لوحة التحكم للـOwner فقط): مواصفات مُهيكلة، دُمجت مباشرة بمتطلبات قانونية مع مصادرها بالأقسام.
2. **الدمج:** دُمجت البنود في **{S['reqs']} متطلبًا قانونيًا** بمعرفات ثابتة `PREFIX-NNN`.
3. **التغطية:**
   - {S['covered']} بندًا خامًا مربوطة بمتطلب.
   - {S['dropped']} مستبعدة لأنها مثال أو مرجع فقط، والسبب موثق.
   - **{S['unaccounted']} غير محسوبة.**
4. **قاعدة الأولوية بين المصادر:**
   1. آخر قرار صريح من الـOwner.
   2. قرار سابق للـOwner لم يُستبدل.
   3. وثيقة معتمدة.
   4. مصدر رسمي متحقق منه.
   5. الكود: دليل تنفيذ فقط.
   6. اقتراح AI.
5. **التاريخ لا يُحذف:** أي متطلب تغيّر يحمل سطر `↺ سابقًا`.
6. **أي تعارض لا يُحسم بصمت:** يُسجّل في `CONFLICT-REGISTER.md`.

## الملفات المرتبطة
| الملف | الدور |
|---|---|
| [`MASTER-DECISION-REGISTER.md`](MASTER-DECISION-REGISTER.md) | الحالة الحالية لكل قرار ({S['decisions']} قرارًا) |
| [`governance/DECISION-LOG.md`](governance/DECISION-LOG.md) | السجل الزمني (D-000 ← D-{149 + S['new_dec']}) |
| [`CONFLICT-REGISTER.md`](CONFLICT-REGISTER.md) | {S['cf_total']} تعارضًا وطريقة حسمها |
| [`PENDING-OWNER-INPUT.md`](PENDING-OWNER-INPUT.md) | {S['po_total']} بندًا فقط تحتاجك |
| [`REQUIREMENTS-TRACEABILITY-MATRIX.md`](REQUIREMENTS-TRACEABILITY-MATRIX.md) | متطلب ← قرار ← تصميم ← كود ← اختبار |
| [`IMPLEMENTATION-GAP-ANALYSIS.md`](IMPLEMENTATION-GAP-ANALYSIS.md) | ما الموجود وما الناقص |
| [`IMPLEMENTATION-PLAN.md`](IMPLEMENTATION-PLAN.md) | الخطة الموحدة P00 ← P12 والبوابات |
| [`menu-ia/MENU-DECISION-REGISTER.md`](menu-ia/MENU-DECISION-REGISTER.md) | تفاصيل قرارات المنيو (F / P / M / CF / R) |

## المفردات
- **الحالة (Status):**
  - `FROZEN`: مجمّد، لا يُفتح إلا بتعارض حقيقي.
  - `APPROVED`: توجيه أو قرار صريح من الـOwner.
  - `APPROVED WITH CONDITIONS`
  - `PENDING OWNER INPUT`
  - `PENDING VERIFICATION`
  - `DEFERRED`
  - `REJECTED`
  - `SUPERSEDED`
- **النوع (Type):**
  - `REQUIREMENT`: ما يُبنى.
  - `RULE`: قاعدة دائمة.
  - `GATE`: بوابة اعتماد.
  - `DELIVERABLE`: مُخرج.
  - `DECISION`: قرار.
- **الأولوية:**
  - `P0`: يمنع الإطلاق، أو خطأ أمني/بيانات/UX كبير.
  - `P1`: مهم جدًا قبل الإنتاج.
  - `P2`: تحسين مهم.
  - `P3`: مستقبلي.
- **التنفيذ:**
  - `NOT STARTED`
  - `PARTIAL`
  - `IMPLEMENTED — NOT TESTED`
  - `TESTED`
  - `FROZEN`: بيانات أو قرار مجمّد.
  - `NEEDS FIX`
  - `CONFLICT`
  - **المواصفات وحدها ليست تنفيذًا.** اختبارات الـWireframes = `PROTOTYPE`.
- **البيانات التجارية:** `APPROVED` · `MISSING` · `PENDING OWNER APPROVAL` · `PENDING VERIFICATION` · `REJECTED` · `SUPERSEDED`. **لا تخمين أبدًا.**

## لقطة الحالة
| المقياس | العدد |
|---|---|
| متطلبات | **{S['reqs']}**: {', '.join(f"{k} {v}" for k, v in S['type'].most_common())} |
| حسب الحالة | {', '.join(f"{k} {v}" for k, v in S['status'].most_common())} |
| حسب الأولوية | {', '.join(f"{k} {v}" for k, v in sorted(S['prio'].items()))} |
| حسب التنفيذ | {', '.join(f"{k} {v}" for k, v in S['impl'].most_common())} |
| مُختبر | {', '.join(f"{k} {v}" for k, v in S['tested'].most_common())} |

## فهرس المجالات
| # | المجال | متطلبات | P0 | معتمد/مجمّد | معلّق | تنفيذ (منفذ أو مجمّد) |
|---|---|---|---|---|---|---|
"""]
def slug(a):
    return f"master-requirements/{a}-" + re.sub(r'[^a-z0-9]+', '-', AREAS[a].lower()).strip('-') + '.md'
for a, name in AREAS.items():
    rs = area_reqs(a)
    if not rs: continue
    out.append(f"| {a} | [{name}]({slug(a)}) | {len(rs)} | {sum(r['priority']=='P0' for r in rs)} | {sum(r['status'] in ('APPROVED','FROZEN','APPROVED WITH CONDITIONS') for r in rs)} | {sum(r['status'].startswith('PENDING') for r in rs)} | {sum(r['impl_status'] in IMPL_DONE for r in rs)} |\n")
out.append("\n> **التفاصيل الكاملة لكل متطلب** في ملف مجاله تحت [`master-requirements/`](master-requirements/):\n> - النص الكامل، والتفاصيل، والتاريخ (↺ سابقًا)، والتعارضات، والملاحظات، والمصادر.\n> - **هذا الملف هو الفهرس والمرجع الرسمي، وملفات المجالات جزء منه.** القسمة لسهولة القراءة فقط، والمحتوى من مصدر واحد مُولّد.\n")
for a, name in AREAS.items():
    rs = area_reqs(a)
    if not rs: continue
    srt = sorted(rs, key=lambda r: (r['id'].split('-')[0], int(r['id'].split('-')[1])))
    out.append(f"\n## {a} · {name} — [التفاصيل]({slug(a)})\n\n| ID | المتطلب | الحالة | P | التنفيذ |\n|---|---|---|---|---|\n")
    for r in srt:
        out.append(f"| `{r['id']}` | {esc(r['title'])} | {r['status']} | {r['priority']} | {r['impl_status']} |\n")
    det = [f"# {a} · {name} — تفاصيل المتطلبات\n\n> جزء من [`../SHELTER-WEBSITE-MASTER-REQUIREMENTS.md`](../SHELTER-WEBSITE-MASTER-REQUIREMENTS.md) · مُولّد · آخر تحديث {TODAY}\n\n| ID | المتطلب | النوع | الحالة | P | التنفيذ | اختُبر | المصدر — القرار |\n|---|---|---|---|---|---|---|---|\n"]
    for r in srt:
        det.append(f"| `{r['id']}` | {req_cell(r)} | {r['type']} | {r['status']} | {r['priority']} | {r['impl_status']} | {r['tested']} | {refs_cell(r)} |\n")
    write('docs/' + slug(a), ''.join(det))
out.append(f"\n## البنود المستبعدة من الدمج ({S['dropped']})\n\n| البند | السبب |\n|---|---|\n" + ''.join(f"| {k} | {esc(v)} |\n" for k, v in sorted(dropped.items())))
write('docs/SHELTER-WEBSITE-MASTER-REQUIREMENTS.md', ''.join(out))

# ================================================================ 2. DECISION REGISTER
CAT_AR = {'FROZEN': 'مجمّد — لا يُفتح إلا بتعارض حقيقي', 'APPROVED': 'معتمد', 'APPROVED WITH CONDITIONS': 'معتمد بشروط', 'PENDING OWNER INPUT': 'بانتظار الـOwner',
          'PENDING VERIFICATION': 'بانتظار تحقق', 'DEFERRED': 'مؤجل', 'REJECTED': 'مرفوض', 'SUPERSEDED': 'مُستبدل (التاريخ محفوظ)'}
o = [f"""# MASTER DECISION REGISTER

> **الحالة الحالية لكل قرار في المشروع.** `DRAFT — PENDING OWNER APPROVAL` (PO-045) · **آخر تحديث:** {TODAY}
>
> **أدوار السجلات (لا مصدرين للحقيقة):**
> - **هذا الملف:** الحالة الحالية لكل قرار.
> - [`governance/DECISION-LOG.md`](governance/DECISION-LOG.md): التاريخ الزمني، Append-only. أي قرار جديد يأخذ رقمه هناك أولًا.
> - [`menu-ia/MENU-DECISION-REGISTER.md`](menu-ia/MENU-DECISION-REGISTER.md): تفاصيل المنيو.
>
> **المعرّفات:**
> - **`D-xxx`:** سجل القرارات.
>   - **D-{150}…D-{149 + S['new_dec']}:** قرارات صريحة من الـOwner في الرسائل M01–M27 لم تكن مسجلة، وأُضيفت في هذا التدقيق.
> - **`F-xx` / `R-xx`:** المنيو.
> - **`DB-xx`:** القرارات المفتوحة قبل التصميم.
> - **`GEP-§n` / `GIO-§n`:** سياسة Google وملكية التنفيذ.
> - **`RISK-xx`:** المخاطر.
> - **`AC/DL/CAR/MDR/RR/AAL`:** قواعد في رؤوس الوثائق.

## الملخص
| الفئة | العدد |
|---|---|
""" + ''.join(f"| {c} | {S['dec'].get(c, 0)} |\n" for c in CATS) + f"| **المجموع** | **{S['decisions']}** |\n"]
for c in CATS:
    rows = dec_by_cat.get(c, [])
    o.append(f"\n## {c} — {CAT_AR[c]} ({len(rows)})\n\n")
    if not rows:
        o.append('— لا يوجد.\n'); continue
    if c == 'SUPERSEDED':
        o.append("| Decision ID | Area | القرار القديم (OLD) | SUPERSEDED BY | القرار الحالي المعتمد | السبب | الترتيب | الأثر |\n|---|---|---|---|---|---|---|---|\n")
        for r in rows:
            o.append(f"| `{r['id']}` | {esc(r['area'])} | {esc(r['old'] or r['current'])} | {esc(r['superseded_by'])} | {esc(r['current'] if r['old'] else '')} | {esc(r['reason'])} | {esc(r['order'])} | {esc(r['impact'])} |\n")
    else:
        o.append("| Decision ID | Area | القرار الحالي المعتمد | القرار القديم (إن وجد) | السبب | الترتيب في المحادثة | أثر التنفيذ |\n|---|---|---|---|---|---|---|\n")
        for r in rows:
            o.append(f"| `{r['id']}` | {esc(r['area'])} | {esc(r['current'])} | {esc(r['old'])} | {esc(r['reason'])} | {esc(r['order'])} | {esc(r['impact'])} |\n")
oth = [u for u in UPD_OTHER if not u['id'].startswith('PO-')]
if oth:
    o.append(f"\n## تحديثات من المواصفات الجديدة (M28–M30) على قرارات وبنود قائمة ({len(oth)})\n\n| المعرّف | النوع | التغيير | المصدر |\n|---|---|---|---|\n")
    o += [f"| `{esc(u['id'])}` | {esc(u.get('type'))} | {esc(u['change'])} | {esc(u.get('source'))} |\n" for u in oth]
write('docs/MASTER-DECISION-REGISTER.md', ''.join(o))

# ================================================================ 3. CONFLICT REGISTER
def cf_row(c):
    return (f"| `{c['id']}` | {esc(c['topic'])} | {esc(c.get('old_instruction'))}<br>_({esc(c.get('old_source'))})_ | {esc(c.get('new_instruction'))}<br>_({esc(c.get('new_source'))})_ | "
            f"{esc(c.get('winner'))} | {esc(c.get('why'))} | {esc(c.get('impact'))} | {esc(' · '.join(c.get('files') or []))} | **{c['status']}** |\n")
o = [f"""# CONFLICT REGISTER

> **لا حسم صامت.** كل تعارض حقيقي بين التعليمات والقرارات والوثائق مسجل هنا. · **آخر تحديث:** {TODAY}
>
> **قاعدة الحسم (من الـOwner):**
> 1. آخر قرار صريح.
> 2. المعتمد يتقدم على الاقتراح.
> 3. التصحيح اللاحق يلغي القديم.
> 4. المجمّد لا يُفتح إلا بتعارض حقيقي.
> 5. اقتراح Claude ليس قرارًا.
> 6. الصمت ليس موافقة.
> 7. لا حل تجاري من عندنا.
> 8. التفاصيل التقنية يختار Claude أفضل ممارسة لها ما لم تخالف قرار الـOwner.

| الحالة | العدد |
|---|---|
| `OWNER DECISION REQUIRED` | {cf_stat.get('OWNER DECISION REQUIRED', 0)} |
| `RESOLVED` (حُسم من المحادثة بقاعدة موثقة) | {cf_stat.get('RESOLVED', 0)} |
| `DOC FIX NEEDED` (وثيقة متأخرة عن قرار لاحق) | {cf_stat.get('DOC FIX NEEDED', 0)} |
| **المجموع** | **{len(conflicts)}** |

> **عمود Refs:** يربط كل تعارض بمجموعة التدقيق التي وجدته (G1…G8)، لأن التعارض الواحد قد يظهر في أكثر من مجال.
> **معرّفات المنيو القديمة** (CF-01…CF-11 في `menu-ia/MENU-DECISION-REGISTER.md`) و**VQ / OBS** مربوطة في الملاحظات.
"""]
for st in ['OWNER DECISION REQUIRED', 'RESOLVED', 'DOC FIX NEEDED']:
    rows = [c for c in conflicts if c['status'] == st]
    o.append(f"\n## {st} ({len(rows)})\n\n| Conflict ID | Topic | Old instruction | New instruction | Which one wins | Why | Impact | Files/code affected | Status |\n|---|---|---|---|---|---|---|---|---|\n")
    o += [cf_row(c) for c in rows]
if TFLAGS:
    o.append(f"\n## Technical flags من المواصفات الجديدة (M28–M30) — {len(TFLAGS)}\n\n> قيود تقنية أو أمنية أو قانونية **تُبلَّغ ولا تعيد فتح القرارات المجمّدة**. ما يحتاج الـOwner منها مربوط ببند في `PENDING-OWNER-INPUT.md`.\n\n| ID | الموضوع | التفاصيل | التوصية | يحتاج الـOwner؟ |\n|---|---|---|---|---|\n")
    o += [f"| {esc(t.get('id',''))} | {esc(t.get('topic'))} | {esc(t.get('detail'))} | {esc(t.get('recommendation'))} | {'✅' if t.get('needs_owner') else '—'} |\n" for t in TFLAGS]
if G9.get('notes'):
    o.append('\n## ملاحظات الربط (المعرفات القديمة ← الجديدة)\n\n' + esc(G9['notes']).replace('<br>', '\n') + '\n')
write('docs/CONFLICT-REGISTER.md', ''.join(o))

# ================================================================ 4. PENDING OWNER INPUT
o = [f"""# PENDING OWNER INPUT

> **فقط ما لا يمكن حسمه بدونك.** دُمجت 416 بندًا معلقًا في الوثائق القديمة، وأسئلة المحادثة، في **{len(PO)} بندًا.**
> **الباقي:**
> - **أُجيب سابقًا:** {len(G9['answered_but_stale'])} بندًا (أُصلحت وثائقها).
> - **تقني يقرره Claude:** {len(G9['dropped_technical'])} بندًا (القاعدة 8).
>
> **آخر تحديث:** {TODAY}
>
> **قبل أي سؤال جديد:** ابحث هنا، وفي `SHELTER-WEBSITE-MASTER-REQUIREMENTS.md`، و`MASTER-DECISION-REGISTER.md`. إذا كان الجواب موجودًا فلا يُسأل.
>
> **التصنيف:**
> - `BLOCKING`: تمنع بوابة مرحلة أو الإطلاق، ولا بديل معتمد.
> - `NON-BLOCKING`: يوجد بديل معتمد (MISSING / مخفي / Placeholder / بلا ادعاء)، فالعمل يستمر.

| | BUSINESS DECISION | CONTENT NEEDED | VERIFICATION NEEDED | ACCESS | المجموع |
|---|---|---|---|---|---|
"""]
for cat in ['BLOCKING', 'NON-BLOCKING']:
    o.append(f"| **{cat}** | " + ' | '.join(str(po_stat.get((cat, t), 0)) for t in ['BUSINESS DECISION', 'CONTENT NEEDED', 'VERIFICATION NEEDED', 'ACCESS']) + f" | {sum(v for (c, t), v in po_stat.items() if c == cat)} |\n")
for cat in ['BLOCKING', 'NON-BLOCKING']:
    for t in ['BUSINESS DECISION', 'CONTENT NEEDED', 'VERIFICATION NEEDED', 'ACCESS']:
        rows = [p for p in PO if p['category'] == cat and p['type'] == t]
        if not rows: continue
        o.append(f"\n## {cat} · {t} ({len(rows)})\n\n| ID | المطلوب منك | ماذا يمنع | المراجع |\n|---|---|---|---|\n")
        for p in sorted(rows, key=lambda p: p['id']):
            extra = (f"<br>_({esc(p['count'])})_" if p.get('count') else '') + (f"<br>ℹ️ {esc(p['notes'])}" if p.get('notes') else '')
            o.append(f"| **{p['id']}** | {esc(p['item'])}{extra} | {esc(p['blocks'])} | {esc(', '.join(p.get('refs') or []))} |\n")
res = [p for p in PO if p['category'] == 'RESOLVED']
if res:
    o.append(f"\n## RESOLVED — حُسمت ولم تعد تحتاجك ({len(res)})\n\n| ID | البند | كيف حُسم |\n|---|---|---|\n")
    o += [f"| **{p['id']}** | {esc(p['item'])} | {esc(p.get('notes'))} |\n" for p in sorted(res, key=lambda p: p['id'])]
o.append(f"\n## بنود أُجيبت سابقًا لكن وثائقها كانت متأخرة ({len(G9['answered_but_stale'])})\n\n| البند | أُجيب بـ | الوثيقة | التصحيح |\n|---|---|---|---|\n")
o += [f"| {esc(x['id'])} | {esc(x['answered_by'])} | {esc(x['doc'])} | {esc(x['fix'])} |\n" for x in G9['answered_but_stale']]
o.append(f"\n## أسئلة تقنية حُذفت من قائمتك (Claude يقررها ويشرحها — {len(G9['dropped_technical'])})\n\n| البند | السبب |\n|---|---|\n")
o += [f"| {esc(x['id'])} | {esc(x['reason'])} |\n" for x in G9['dropped_technical']]
write('docs/PENDING-OWNER-INPUT.md', ''.join(o))

# ================================================================ 5. TRACEABILITY
o = [f"""# REQUIREMENTS TRACEABILITY MATRIX

> **Requirement → Decision → Design → Code → Test.** لكل متطلب من **{S['reqs']}**: من أين جاء، وبأي قرار، وأين صُمم، وأين نُفذ، وكيف يُختبر. · **آخر تحديث:** {TODAY}
> **المصادر:** مفاتيح البنود الخام (مثل `M23-045`)، وأرشيفها في التدقيق.
>
> **قراءة الأعمدة:**
> - **Code فارغ** = لا تنفيذ بعد. لا يوجد كود موقع؛ `tooling/` أدوات جودة فقط.
> - **Test `PROTOTYPE`** = اختبار على الـWireframes فقط.

| ID | Title | Source (raw items) | Decision | Design | Code | Test | Impl | Tested |
|---|---|---|---|---|---|---|---|---|
"""]
for r in sorted(reqs, key=lambda r: (int(r['_area']), r['id'].split('-')[0], int(r['id'].split('-')[1]))):
    dec = [ND_ID.get(x, x) for x in (r.get('decision_refs') or [])]
    o.append(f"| `{r['id']}` | {esc(r['title'])} | {esc(', '.join(r.get('sources') or []))} | {esc(', '.join(dec))} | {esc(' · '.join(r.get('design_refs') or []))} | "
             f"{esc(' · '.join(r.get('code_refs') or []))} | {esc(' · '.join(r.get('test_refs') or []))} | {r['impl_status']} | {r['tested']} |\n")
write('docs/REQUIREMENTS-TRACEABILITY-MATRIX.md', ''.join(o))

# ================================================================ 6. GAP ANALYSIS
modules = open(os.path.join(AUD, 'manual/modules.md')).read()
o = [f"""# IMPLEMENTATION GAP ANALYSIS

> **ما طلبته مقابل ما هو موجود فعلًا.** · **آخر تحديث:** {TODAY}
> **الحقيقة الأساسية:** لا يوجد موقع ولا CMS ولا Dashboard بعد. الموجود:
> - وثائق Phase 01.
> - Inventory المنيو v1.0 (مجمّد).
> - Menu IA وWireframes (بانتظار اعتمادك).
> - أدوات جودة مختبرة.
>
> **التحقق من الحالة:**
> - لم يُعتبر أي شيء "منفذًا" لأن ملفًا يحمل اسمه.
> - **المواصفات = `NOT STARTED` للبناء**، مع إشارة `spec:` في عمود الموجود.

## التغطية الحالية
| | العدد | % |
|---|---|---|
""" + ''.join(f"| {k} | {v} | {v * 100 // S['reqs']}% |\n" for k, v in S['impl'].most_common()) + f"""
- **مُنفذ أو مجمّد** (وثائق، بيانات، أدوات): **{sum(S['impl'][k] for k in IMPL_DONE)} من {S['reqs']}** ({sum(S['impl'][k] for k in IMPL_DONE) * 100 // S['reqs']}%).
- **مُختبر فعليًا:** {S['tested'].get('YES', 0)}. **على النموذج فقط** (`PROTOTYPE`): {S['tested'].get('PROTOTYPE', 0)}.
- **الموقع والـDashboard المبنيان:** 0%.

{modules}

## فجوات P0 (حسب المرحلة)
"""]
for ph in PHASES:
    rs = [r for r in reqs if r['phase'] == ph and r['priority'] == 'P0' and r['impl_status'] not in IMPL_DONE]
    if rs:
        o.append(f"- **{ph} {PHASES[ph]}:** {len(rs)} — " + ', '.join(f"`{r['id']}`" for r in sorted(rs, key=lambda r: r['id'])) + '\n')
o.append("""
## الجدول الكامل

| Requirement ID | Requirement | Current Status | Existing Implementation | Missing Work | Priority | Dependencies | Test Required | Phase |
|---|---|---|---|---|---|---|---|---|
""")
prio_ord = {'P0': 0, 'P1': 1, 'P2': 2, 'P3': 3}
for r in sorted(reqs, key=lambda r: (prio_ord.get(r['priority'], 9), r['phase'] or 'P99', r['id'])):
    o.append(f"| `{r['id']}` | {esc(r['title'])} | {r['impl_status']} | {esc(r.get('existing_impl'))} | {esc(r.get('missing_work'))} | {r['priority']} | "
             f"{esc(', '.join(ND_ID.get(x, x) for x in (r.get('dependencies') or [])))} | {esc(r.get('test_required'))} | {r['phase']} |\n")
write('docs/IMPLEMENTATION-GAP-ANALYSIS.md', ''.join(o))

# ================================================================ 7. PLAN
plan = open(os.path.join(AUD, 'manual/plan.md')).read()
o = [plan, "\n## المتطلبات حسب المرحلة (من الـMaster)\n\n| Phase | الاسم | المتطلبات | P0 | P1 | P2/P3 | معلّق على الـOwner |\n|---|---|---|---|---|---|---|\n"]
for ph, name in PHASES.items():
    rs = [r for r in reqs if r['phase'] == ph]
    o.append(f"| {ph} | {name} | {len(rs)} | {sum(r['priority']=='P0' for r in rs)} | {sum(r['priority']=='P1' for r in rs)} | {sum(r['priority'] in ('P2','P3') for r in rs)} | {sum(r['status'].startswith('PENDING') for r in rs)} |\n")
o.append('\n> القائمة الكاملة لكل مرحلة: عمود Phase في [`IMPLEMENTATION-GAP-ANALYSIS.md`](IMPLEMENTATION-GAP-ANALYSIS.md).\n')
write('docs/IMPLEMENTATION-PLAN.md', ''.join(o))

# ================================================================ 8. DECISION-LOG sync
log_p = os.path.join(REPO, 'docs/governance/DECISION-LOG.md'); log = open(log_p).read()
if '| D-150 |' not in log and not os.environ.get('NO_LOG'):
    SRC = lambda n: ' · '.join(f"{m} ({MSG_NAME.get(m, m)})" for m in msgs_of(n.get('sources', [])))
    STATUS_LOG = {'APPROVED': '`APPROVED`', 'APPROVED WITH CONDITIONS': '`APPROVED` (بشروط)', 'FROZEN': '`APPROVED` (FROZEN)', 'DEFERRED': '`APPROVED` (DEFERRED)'}
    rows = ''.join(f"| {n['id']} | {esc(n['decision'])} | {n.get('date', TODAY)} | Owner — {esc(SRC(n))} (سُجّل في تدقيق المحادثة الكامل) | ✅ Owner | {esc(n.get('area',''))} | {STATUS_LOG.get(n.get('status'), '`APPROVED`')} |\n" for n in nds)
    anchor = '\n## قرارات مفتوحة'
    i = log.index(anchor)
    # status sync: superseded decisions keep their row, status cell updated (history preserved)
    sup = {r['id']: r['superseded_by'] for r in dec_rows if r['cat'] == 'SUPERSEDED' and r['id'].startswith('D-') and r['superseded_by']}
    head, tail = log[:i], log[i:]
    lines = head.split('\n')
    for k, line in enumerate(lines):
        m = re.match(r'\| (D-\d+) \|', line)
        if m and m.group(1) in sup and 'SUPERSEDED' not in line.rsplit('|', 2)[-2]:
            cells = line.rstrip().rstrip('|').rsplit('|', 1)
            lines[k] = cells[0] + f"| `SUPERSEDED` → {sup[m.group(1)]} (كان: {cells[1].strip()}) |"
    head = '\n'.join(lines)
    head = head.rstrip('\n') + '\n' + rows
    note = ("\n> **أدوار السجلات (من تدقيق 2026-10-01):** هذا الملف هو **السجل الزمني Append-only**. "
            "**الحالة الحالية** لكل قرار في [`../MASTER-DECISION-REGISTER.md`](../MASTER-DECISION-REGISTER.md). "
            f"D-150 → D-{149 + len(nds)} قرارات صريحة من الـOwner في الرسائل M01–M27 لم تكن مسجلة. "
            "خانة الحالة للقرارات المُستبدلة حُدّثت مع حفظ النص الأصلي.\n")
    head = head.replace('## القرارات\n', '## القرارات\n' + note, 1)
    log = head + tail
    for t in sorted(TEMP_MAP, key=len, reverse=True):
        log = log.replace(t, TEMP_MAP[t])
    open(log_p, 'w').write(log); print('DECISION-LOG updated', len(nds), 'new rows,', len(sup), 'superseded synced')

log = open(log_p).read()
# Later runs: a decision superseded by a newer group (e.g. D-335 → D-336, D-338 → D-340) gets its status cell updated too.
_sup = {r['id']: r['superseded_by'] for r in dec_rows if r['cat'] == 'SUPERSEDED' and r['id'].startswith('D-') and r['superseded_by']}
_i = log.index('\n## قرارات مفتوحة')
_lines, _n = log[:_i].split('\n'), 0
for k, line in enumerate(_lines):
    m = re.match(r'\| (D-\d+) \|', line)
    if m and m.group(1) in _sup and 'SUPERSEDED' not in line.rsplit('|', 2)[-2]:
        cells = line.rstrip().rstrip('|').rsplit('|', 1)
        _lines[k] = cells[0] + f"| `SUPERSEDED` → {_sup[m.group(1)]} (كان: {cells[1].strip()}) |"; _n += 1
if _n and not os.environ.get('NO_LOG'):
    log = '\n'.join(_lines) + log[_i:]
    open(log_p, 'w').write(log); print('DECISION-LOG superseded synced', _n)
miss = [n for n in nds if f"| {n['id']} |" not in log]
if miss and not os.environ.get('NO_LOG'):
    SRC = lambda n: ' · '.join(f"{m} ({MSG_NAME.get(m, m)})" for m in msgs_of(n.get('sources', [])))
    STATUS_LOG = {'APPROVED': '`APPROVED`', 'APPROVED WITH CONDITIONS': '`APPROVED` (بشروط)', 'FROZEN': '`APPROVED` (FROZEN)', 'DEFERRED': '`APPROVED` (DEFERRED)'}
    rows = ''.join(f"| {n['id']} | {esc(n['decision'])} | {n.get('date', TODAY)} | Owner — {esc(SRC(n))} | ✅ Owner | {esc(n.get('area',''))} | {STATUS_LOG.get(n.get('status'), '`APPROVED`')} |\n" for n in miss)
    i = log.index('\n## قرارات مفتوحة')
    log = log[:i].rstrip('\n') + '\n' + rows + log[i:]
    for t in sorted(TEMP_MAP, key=len, reverse=True):
        log = log.replace(t, TEMP_MAP[t])
    open(log_p, 'w').write(log); print('DECISION-LOG appended', len(miss))
print(json.dumps({k: dict(v) if isinstance(v, collections.Counter) else v for k, v in S.items() if k not in ('po',)}, ensure_ascii=False, default=str))
print('PO', dict(po_stat))
