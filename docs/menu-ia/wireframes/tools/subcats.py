import csv, json
ROWS=[r for r in csv.DictReader(open('/home/user/SHELTER-WEBSITE/docs/phase-01-discovery/menu/menu-inventory-v1.0.csv',encoding='utf-8-sig')) if not r['id_status'].startswith('FROZEN — RETIRED')]
SUB={  # id: (en, ar_proposed)
 'HOT-ESP':('ESPRESSO & CLASSICS','إسبريسو وكلاسيك'),
 'HOT-LAT':('LATTES & FLAVORS','لاتيه ونكهات'),
 'HOT-TRD':('TRADITIONAL','قهوة تقليدية'),
 'HOT-CHM':('CHOCOLATE, MATCHA & MORE','شوكولاتة وماتشا والمزيد'),
 'COLD-CLS':('ICED CLASSICS','آيس كلاسيك'),
 'COLD-LAT':('ICED LATTES & FLAVORS','آيس لاتيه ونكهات'),
 'COLD-SHK':('ICED SHAKEN','آيس شيكن'),
 'COLD-TEA':('ICED TEA','آيس تي'),
 'COLD-CHM':('CHOCOLATE, MATCHA & MORE','شوكولاتة وماتشا والمزيد'),
 'FZ-MOJ':('MOJITO','موهيتو'),
 'FZ-NRG':('ENERGY','مشروبات طاقة'),
 'FZ-SFT':('SOFT DRINKS & WATER','مشروبات غازية ومياه'),
 'PENDING':('PENDING OWNER REVIEW','بانتظار مراجعة الـOwner'),
}
H='HIGH'; M='MEDIUM'
MAP={
 # HOT
 'ESPRESSO DOUBLE':('HOT-ESP',H,''),'ESPRESSO CON PANNA':('HOT-ESP',H,''),'ESPRESSO MACCHIATO':('HOT-ESP',H,''),'MACCHIATO':('HOT-ESP',H,'صنف مختلف عن ESPRESSO MACCHIATO (D-112)'),
 'CORTADO':('HOT-ESP',H,''),'FLAT WHITE':('HOT-ESP',H,''),'CAPPUCCINO':('HOT-ESP',H,''),'LATTE':('HOT-ESP',H,'لاتيه بدون نكهة = كلاسيك'),'AMERICANO':('HOT-ESP',H,''),
 'RED EYE':('HOT-ESP',M,'اسم مشروب قهوة كلاسيكي معروف؛ وصفة SHELTER غير معروفة'),'BLACK EYE':('HOT-ESP',M,'نفس الملاحظة'),'DEAD EYE':('HOT-ESP',M,'نفس الملاحظة'),
 'EYE OF THE TIGER':('HOT-ESP',M,'اسم خاص؛ وُضع مع عائلة RED/BLACK/DEAD EYE بالاسم فقط'),'AFFOGATO':('HOT-ESP',M,'كلاسيك إسبريسو بالاسم'),
 'SPANISH LATTE':('HOT-LAT',H,''),'CARAMEL LATTE':('HOT-LAT',H,''),'VANILLA LATTE':('HOT-LAT',H,''),'HAZELNUT LATTE':('HOT-LAT',H,''),'CINNAMON NUT LATTE':('HOT-LAT',H,''),
 'SALTED CARAMEL LATTE':('HOT-LAT',H,''),'IRISH LATTE':('HOT-LAT',H,''),'CARAMEL LATTE (SUGAR-FREE)':('HOT-LAT',H,''),'VANILLA LATTE (SUGAR-FREE)':('HOT-LAT',H,''),
 'HAZELNUT LATTE (SUGAR-FREE)':('HOT-LAT',H,''),'CARAMEL MACCHIATO':('HOT-LAT',H,'منكّه'),'MOCHA':('HOT-LAT',M,'يمكن وضعه في الشوكولاتة؛ وُضع مع اللاتيه المنكّه'),
 'WHITE MOCHA':('HOT-LAT',M,'نفس ملاحظة MOCHA'),'IRISH COFFEE':('HOT-LAT',M,'منكّه بالاسم؛ الوصفة غير معروفة'),
 'TURKISH COFFEE SINGLE':('HOT-TRD',H,''),'TURKISH COFFEE DOUBLE':('HOT-TRD',H,''),'AMERICAN COFFEE':('HOT-TRD',M,'صنف مختلف عن AMERICANO (D-111)؛ طريقة التحضير غير معروفة'),
 'FRENCH COFFEE':('HOT-TRD',M,'"قهوة فرنسية" مشروب محلي شائع؛ الوصفة غير معروفة'),'NESCAFE':('HOT-TRD',M,'قهوة سريعة التحضير'),
 'HOT CHOCOLATE':('HOT-CHM',H,''),'HOT LOTUS':('HOT-CHM',M,'بنمط الاسم HOT + نكهة؛ لا نعرف إن كان يحتوي قهوة — لا ندّعي "بدون قهوة"'),
 'HOT PISTACHIO':('HOT-CHM',M,'نفس الملاحظة'),'HOT NUTELLA':('HOT-CHM',M,'نفس الملاحظة'),'MATCHA LATTE':('HOT-CHM',M,'ماتشا وليس قهوة؛ اسم القسم يذكر الماتشا صراحة'),
 'MESTEKH':('PENDING',None,'اسم غير واضح (D-139) — لا نعرف ما هو'),
 # COLD
 'ICED AMERICAN':('COLD-CLS',M,'صنف مختلف عن ICED AMERICANO (D-111)'),'ICED AMERICANO':('COLD-CLS',H,''),'ICED LATTE':('COLD-CLS',H,''),'ICED CAPPUCCINO':('COLD-CLS',H,''),
 'ICED SPANISH LATTE':('COLD-LAT',H,''),'ICED CARAMEL LATTE':('COLD-LAT',H,''),'ICED CARAMEL LATTE (SUGAR-FREE)':('COLD-LAT',H,''),'ICED VANILLA LATTE':('COLD-LAT',H,''),
 'ICED VANILLA LATTE (SUGAR-FREE)':('COLD-LAT',H,''),'ICED HAZELNUT LATTE':('COLD-LAT',H,''),'ICED HAZELNUT LATTE (SUGAR-FREE)':('COLD-LAT',H,''),'ICED CARAMEL MACCHIATO':('COLD-LAT',H,''),
 'ICED MOCHA':('COLD-LAT',M,'نفس ملاحظة MOCHA'),'ICED WHITE MOCHA':('COLD-LAT',M,'نفس الملاحظة'),'ICED IRISH LATTE':('COLD-LAT',H,''),'ICED TOFFEE NUT LATTE':('COLD-LAT',H,''),
 'ICED SALTED CARAMEL LATTE':('COLD-LAT',H,''),'ICED CINNAMON NUT LATTE':('COLD-LAT',H,''),
 'ICED SHAKEN CARAMEL':('COLD-SHK',H,'صنف مستقل (D-124)'),'ICED SHAKEN WHITE MOCHA':('COLD-SHK',H,''),'ICED SHAKEN VANILLA':('COLD-SHK',H,''),'ICED SHAKEN HAZELNUT':('COLD-SHK',H,''),
 'ICED SHAKEN SALTED CARAMEL':('COLD-SHK',H,'الاسم العربي PENDING (D-138)'),
 'ICED TEA BERRY':('COLD-TEA',H,''),'ICED TEA POMEGRANATE':('COLD-TEA',H,''),'ICED TEA PASSION + PEACH':('COLD-TEA',H,''),'ICED TEA PEACH':('COLD-TEA',H,''),'ICED TEA LEMON':('COLD-TEA',H,''),'ICED TEA MIX':('COLD-TEA',H,''),
 'ICED CHOCOLATE':('COLD-CHM',H,''),'ICED MATCHA':('COLD-CHM',M,'ماتشا'),'ICED NUTELLA':('COLD-CHM',M,'لا نعرف إن كان يحتوي قهوة'),'ICED PISTACHIO':('COLD-CHM',M,'نفس الملاحظة'),
 'ICED LOTUS':('COLD-CHM',M,'نفس الملاحظة'),'KIDS STRAWBERRY':('COLD-CHM',M,'مشروب أطفال؛ صنف وحيد فلا داعي لقسم "أطفال" مستقل'),
 'ICED CROCCONATE':('PENDING',None,'اسم غير واضح (D-139)'),
 # FIZZY
 'MOJITO 7UP':('FZ-MOJ',H,''),'MOJITO SODA':('FZ-MOJ',H,''),'MOJITO CODE RED':('FZ-MOJ',H,''),'MOJITO RED BULL':('FZ-MOJ',H,''),
 'RED BULL':('FZ-NRG',H,''),'RED BULL (SUGAR-FREE)':('FZ-NRG',H,''),'RED BULL WATERMELON':('FZ-NRG',H,''),'RED BULL COCONUT':('FZ-NRG',H,''),
 'SHELTER ENERGY CODE RED':('FZ-NRG',M,'كلمة ENERGY في الاسم'),'SHELTER ENERGY RED BULL':('FZ-NRG',H,''),
 '7UP':('FZ-SFT',H,''),'7UP SHELTER':('FZ-SFT',H,''),'SODA SUMMER CRUSH':('FZ-SFT',M,'صودا بالاسم'),'MINERAL WATER':('FZ-SFT',H,'ليس غازيًا؛ القسم اسمه "ومياه"'),
 'CODE RED':('PENDING',None,'هل هو مشروب طاقة أم مشروب غازي؟ لا نصنّفه من عندنا'),
}
out=[]; seen=set()
for r in ROWS:
    if r['category_id'] in ('CAT-001','CAT-002','CAT-003'):
        n=r['display_name_en']; assert n in MAP, n; seen.add(n)
        s,conf,note=MAP[n]
        out.append(dict(product_id=r['product_id'],name_en=n,name_ar=r['display_name_ar'] or r['source_name_ar'],name_ar_status='APPROVED' if r['display_name_ar'] else 'PENDING',category_id=r['category_id'],category=r['source_category_name'],
            subcategory_id=s,subcategory_en=SUB[s][0],subcategory_ar=SUB[s][1],confidence=conf or 'PENDING OWNER REVIEW',notes=note))
assert len(out)==90 and seen==set(MAP), (len(out), set(MAP)-seen)
from collections import Counter
print(Counter((o['category'],o['subcategory_en']) for o in out))
print(Counter(o['confidence'] for o in out))
with open('/home/user/SHELTER-WEBSITE/docs/menu-ia/subcategory-mapping.csv','w',newline='',encoding='utf-8-sig') as f:
    w=csv.DictWriter(f,fieldnames=list(out[0].keys())); w.writeheader(); w.writerows(out)
json.dump({'SUB':SUB,'MAP':{k:v[0] for k,v in MAP.items()}},open('subcats.json','w'),ensure_ascii=False)
