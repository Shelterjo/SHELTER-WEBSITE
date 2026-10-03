"""Read-only public content capture. Never submits forms or follows off-site links.
Use: python scripts/capture-public-site.py
Output is evidence, not approved master data. Scripts, tracking, and form values are excluded.
"""
from html.parser import HTMLParser
from urllib.request import Request, urlopen
from urllib.parse import urljoin, urlsplit, urlunsplit, unquote, quote
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path
import json, hashlib, re
from datetime import datetime, timezone

ROOT='https://www.shelterjo.com/'
OUT=Path('docs/redesign/source-2026-10-03')
OUT.mkdir(parents=True,exist_ok=True)

def normal(url):
 p=urlsplit(urljoin(ROOT,url))
 if p.hostname not in ('www.shelterjo.com','shelterjo.com'): return None
 if p.query or re.search(r'\.(png|jpe?g|webp|gif|svg|pdf|css|js|zip|woff2?)$',p.path,re.I): return None
 if any(x in p.path for x in ('wp-admin','wp-json','wp-login','feed','xmlrpc')): return None
 return urlunsplit(('https','www.shelterjo.com',quote(unquote(p.path),safe='/') or '/','',''))

class Page(HTMLParser):
 def __init__(self,url):
  super().__init__(convert_charrefs=True);self.url=url;self.skip=0;self.parts=[];self.links=[];self.images=[];self.meta={};self.styles=[];self.title=[];self.in_title=False;self.headings=[];self.heading=None
 def handle_starttag(self,t,a):
  d=dict(a)
  if t in ('script','style','noscript'): self.skip+=1
  if t=='title': self.in_title=True
  if t in ('h1','h2','h3'): self.heading={'level':t,'text':''};self.headings.append(self.heading)
  if t=='meta' and d.get('name') in ('description','robots'): self.meta[d['name']]=d.get('content','')
  if t=='link' and d.get('rel')=='stylesheet': self.styles.append(urljoin(self.url,d.get('href','')))
  if t=='a' and d.get('href'): self.links.append(urljoin(self.url,d['href']))
  if t=='img':
   src=d.get('data-src') or d.get('src','')
   if src and not src.startswith('data:'): self.images.append({'url':urljoin(self.url,src),'alt':d.get('alt',''),'srcset':d.get('srcset','')})
 def handle_endtag(self,t):
  if t in ('script','style','noscript'): self.skip=max(0,self.skip-1)
  if t=='title': self.in_title=False
  if t in ('h1','h2','h3'): self.heading=None
 def handle_data(self,s):
  if self.skip:return
  s=' '.join(s.split())
  if not s:return
  if self.in_title:self.title.append(s)
  if self.heading is not None:self.heading['text']+=s+' '
  self.parts.append(s)
 def result(self):return {'url':self.url,'title':' '.join(self.title),'meta':self.meta,'headings':self.headings,'text':'\n'.join(self.parts),'links':sorted(set(self.links)),'images':list({x['url']:x for x in self.images}.values()),'stylesheets':sorted(set(self.styles))}

def get(url):
 try:
  with urlopen(Request(url,headers={'User-Agent':'ShelterWebsiteContentAudit/1.0'}),timeout=25) as res:
   if urlsplit(res.url).hostname not in ('shelterjo.com','www.shelterjo.com'):return {'url':url,'error':'off-site redirect'}
   body=res.read(3_000_000).decode('utf-8','replace')
  p=Page(url);p.feed(body);d=p.result();d['fetched_at']=datetime.now(timezone.utc).isoformat();d['source_sha256']=hashlib.sha256(body.encode()).hexdigest();return d
 except Exception as e:return {'url':url,'error':str(e)}

first=get(ROOT);pages=[first];seen={ROOT};queue=sorted({n for x in first.get('links',[]) if (n:=normal(x)) and n not in seen})
for depth in range(4):
 urls=queue[:40-len(pages)];seen.update(urls)
 with ThreadPoolExecutor(max_workers=4) as pool: batch=list(pool.map(get,urls))
 pages.extend(batch);queue=sorted({n for d in batch for x in d.get('links',[]) if (n:=normal(x)) and n not in seen})
 if not queue:break
images={}
for i,p in enumerate(pages):
 filename=f'page-{i:02d}.json';(OUT/filename).write_text(json.dumps(p,ensure_ascii=False,indent=2)+'\n')
 for asset in p.get('images',[]):
  if urlsplit(asset['url']).hostname in ('shelterjo.com','www.shelterjo.com'):
   images.setdefault(asset['url'],{**asset,'pages':[],'status':'SOURCE ONLY — NOT APPROVED FOR PUBLICATION'})['pages'].append(p['url'])
(OUT/'images.json').write_text(json.dumps(list(images.values()),ensure_ascii=False,indent=2)+'\n')
summary={'captured_at':datetime.now(timezone.utc).isoformat(),'source':ROOT,'status':'SOURCE ONLY — COMPARE WITH MASTER DATA','pages':[{'url':p['url'],'title':p.get('title'),'error':p.get('error'),'file':f'page-{i:02d}.json'} for i,p in enumerate(pages)],'unique_first_party_images':len(images),'unvisited_discovered_urls':queue}
(OUT/'index.json').write_text(json.dumps(summary,ensure_ascii=False,indent=2)+'\n')
print(json.dumps(summary,ensure_ascii=False))
