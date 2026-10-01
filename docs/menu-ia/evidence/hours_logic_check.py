"""
SHELTER Menu IA — branch open/closed logic: SPEC VERIFICATION HARNESS (not production code).
Verifies the rules in SHELTER-MENU-IA-SPEC.md §8 against the owner-approved regular hours (D-020),
including shifts that cross midnight. Wall-clock time is Asia/Amman (UTC+3, no DST).
Run: python3 hours_logic_check.py
"""
from datetime import datetime, timedelta, time

# weekday(): Mon=0 … Sun=6
SAT, SUN, MON, TUE, WED, THU, FRI = 5, 6, 0, 1, 2, 3, 4
REGULAR = {
    'drive': {d: (time(7), time(2)) for d in (SAT, SUN, MON, TUE, WED, THU)} | {FRI: (time(8), time(2))},
    'house': {d: (time(9), time(22)) for d in (SAT, SUN, MON, TUE, WED)} | {THU: (time(9), time(23)), FRI: (time(9), time(23))},
}
CLOSING_SOON_MIN = 60

def shifts_for(branch, day, overrides):
    """Shifts that START on calendar date `day`. Overrides (special/holiday/temporary/emergency) win over regular hours."""
    o = overrides.get((branch, day))
    if o is not None:
        return o  # list of (start_dt, end_dt); [] = closed all day
    reg = REGULAR[branch].get(day.weekday())
    if not reg:
        return []
    start = datetime.combine(day, reg[0]); end = datetime.combine(day, reg[1])
    if end <= start:  # crosses midnight
        end += timedelta(days=1)
    return [(start, end)]

def status(branch, now, overrides=None):
    overrides = overrides or {}
    today = now.date()
    for day in (today - timedelta(days=1), today):  # yesterday's shift may still be running after midnight
        for s, e in shifts_for(branch, day, overrides):
            if s <= now < e:
                left = int((e - now).total_seconds() // 60)
                return ('CLOSING_SOON', left, e) if left <= CLOSING_SOON_MIN else ('OPEN', left, e)
    for k in range(0, 8):  # next opening
        for s, e in sorted(shifts_for(branch, today + timedelta(days=k), overrides)):
            if s > now:
                return ('CLOSED', None, s)
    return ('CLOSED', None, None)

def fmt12(t, lang):
    h = t.hour % 12 or 12
    suf = ('ص' if t.hour < 12 else 'م') if lang == 'ar' else ('AM' if t.hour < 12 else 'PM')
    return f"{h}:{t.minute:02d} {suf}"

def label(branch, now, lang='ar', overrides=None):
    st, left, t = status(branch, now, overrides)
    if st == 'OPEN':
        return f"مفتوح الآن — حتى {fmt12(t, 'ar')}" if lang == 'ar' else f"Open now — until {fmt12(t, 'en')}"
    if st == 'CLOSING_SOON':
        return f"يغلق بعد {left} دقيقة" if lang == 'ar' else f"Closes in {left} min"
    if t is None:
        return "مغلق الآن" if lang == 'ar' else "Closed now"
    tomorrow = t.date() == now.date() + timedelta(days=1)
    if lang == 'ar':
        return f"مغلق الآن — يفتح {'غدًا ' if tomorrow else ''}{fmt12(t, 'ar')}"
    return f"Closed now — opens {'tomorrow ' if tomorrow else ''}{fmt12(t, 'en')}"

if __name__ == '__main__':
    D = lambda s: datetime.fromisoformat(s)  # 2026-10-03 = Saturday
    tmp_closed = {('house', D('2026-10-05').date()): []}  # Monday: temporary closure (example)
    cases = [
        ('drive', '2026-10-03T19:30', None, 'OPEN', 'مفتوح الآن — حتى 2:00 ص'),
        ('drive', '2026-10-04T01:15', None, 'CLOSING_SOON', 'يغلق بعد 45 دقيقة'),   # Saturday shift after midnight
        ('drive', '2026-10-04T02:00', None, 'CLOSED', 'مغلق الآن — يفتح 7:00 ص'),     # closing time is exclusive
        ('drive', '2026-10-02T07:30', None, 'CLOSED', 'مغلق الآن — يفتح 8:00 ص'),     # Friday opens 08:00
        ('drive', '2026-10-02T01:00', None, 'CLOSING_SOON', 'يغلق بعد 60 دقيقة'),    # Thursday shift into Friday
        ('drive', '2026-10-02T00:59', None, 'OPEN', 'مفتوح الآن — حتى 2:00 ص'),       # 61 min left
        ('house', '2026-10-07T21:30', None, 'CLOSING_SOON', 'يغلق بعد 30 دقيقة'),    # Wednesday 22:00 close
        ('house', '2026-10-08T22:30', None, 'CLOSING_SOON', 'يغلق بعد 30 دقيقة'),    # Thursday 23:00 close
        ('house', '2026-10-03T08:00', None, 'CLOSED', 'مغلق الآن — يفتح 9:00 ص'),
        ('house', '2026-10-07T22:00', None, 'CLOSED', 'مغلق الآن — يفتح غدًا 9:00 ص'),
        ('house', '2026-10-05T12:00', tmp_closed, 'CLOSED', 'مغلق الآن — يفتح غدًا 9:00 ص'),  # override wins
        ('drive', '2026-10-03T19:30', None, 'OPEN', 'Open now — until 2:00 AM'),
    ]
    ok = 0
    for b, ts, ov, exp_state, exp_label in cases:
        now = D(ts); lang = 'en' if exp_label[0].isascii() else 'ar'
        st = status(b, now, ov)[0]; lb = label(b, now, lang, ov)
        res = 'PASS' if (st == exp_state and lb == exp_label) else 'FAIL'
        ok += res == 'PASS'
        print(f"| {b.upper()} | {now:%a %Y-%m-%d %H:%M} | {st} | {lb} | {res} |")
    print(f"{ok}/{len(cases)} PASS")
