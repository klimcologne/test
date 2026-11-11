import random, math, sys
OUT = sys.argv[1]
V = '#7a4dff'   # Siebdruck-Violett (auf Schwarz heller als das Web-Violett)
FONT = "font-family:Archivo,'Archivo Variable',Arial,sans-serif"

def head(w, h):
    return f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}">'

def wide(x, y, size, txt, fill, anchor='start', ls=0, stretch=125, weight=850, extra=''):
    return f'<text x="{x}" y="{y}" font-size="{size}" text-anchor="{anchor}" fill="{fill}" style="{FONT};font-weight:{weight};font-stretch:{stretch}%;letter-spacing:{ls}px" {extra}>{txt}</text>'

def mono(x, y, size, txt, fill, anchor='start', ls=2):
    return f'<text x="{x}" y="{y}" font-size="{size}" text-anchor="{anchor}" fill="{fill}" style="font-family:\'JetBrains Mono\',monospace;font-weight:500;letter-spacing:{ls}px">{txt}</text>'

def corners(x, y, w, h, c, l=28, t=3):
    p = []
    for (cx, cy, dx, dy) in [(x, y, 1, 1), (x + w, y, -1, 1), (x, y + h, 1, -1), (x + w, y + h, -1, -1)]:
        p.append(f'<path d="M{cx} {cy + dy * l}V{cy}H{cx + dx * l}" fill="none" stroke="{c}" stroke-width="{t}"/>')
    return ''.join(p)

# ---------- S-03 STATIC ----------
def static():
    W, H = 1000, 1250
    random.seed(303)
    s = [head(W, H)]
    # Rauschfeld: dicht oben, löst sich nach unten auf
    x0, y0, cols, rows, cell = 120, 170, 76, 52, 10
    for r in range(rows):
        dens = 0.62 - 0.55 * (r / rows) ** 1.6
        for c in range(cols):
            if random.random() < dens:
                w = cell * random.choice([1, 1, 1, 2])
                s.append(f'<rect x="{x0 + c * cell}" y="{y0 + r * cell}" width="{w}" height="{cell}" fill="#fff"/>')
    # Abstimmbalken
    s.append(f'<rect x="{x0}" y="{y0 + 205}" width="{cols * cell}" height="22" fill="{V}"/>')
    s.append(corners(x0 - 22, y0 - 22, cols * cell + 44, rows * cell + 44, '#fff'))
    s.append(wide(W / 2, 850, 168, 'STATIC', '#fff', 'middle', -4))
    s.append(mono(W / 2, 905, 24, 'BETWEEN THE STATIONS', V, 'middle', 6))
    s.append(f'<line x1="120" y1="960" x2="880" y2="960" stroke="#fff" stroke-width="2"/>')
    s.append(mono(120, 1005, 20, 'PIL S-03', '#fff'))
    s.append(mono(500, 1005, 20, 'CH 00 · NO CARRIER', '#fff', 'middle'))
    s.append(mono(880, 1005, 20, 'MMXXVI', '#fff', 'end'))
    s.append(wide(W / 2, 1090, 30, 'POLINIS®', '#fff', 'middle', 10, 125, 700))
    s.append('</svg>')
    return ''.join(s)

# ---------- S-04 FREQUENCY ----------
def frequency():
    W, H = 1000, 1250
    random.seed(404)
    s = [head(W, H)]
    s.append(wide(W / 2, 250, 94, 'FREQUENCY', '#fff', 'middle', -3))
    s.append(mono(W / 2, 300, 24, 'FIND YOUR FREQUENCY.', V, 'middle', 6))
    # Wellenlinie: Stille, dann das Signal
    pts = []
    mid = 560
    for i in range(0, 761, 4):
        x = 120 + i
        t = i / 760
        env = math.exp(-((t - 0.58) / 0.13) ** 2)
        y = mid + env * 190 * math.sin(i * 0.19) * (0.6 + 0.4 * math.sin(i * 0.031)) + (random.random() - .5) * 6 * env
        pts.append(f'{x:.1f},{y:.1f}')
    s.append(f'<polyline points="{" ".join(pts)}" fill="none" stroke="#fff" stroke-width="7" stroke-linejoin="round" stroke-linecap="round"/>')
    # Skala wie ein Radio-Display
    y = 840
    s.append(f'<line x1="120" y1="{y}" x2="880" y2="{y}" stroke="#fff" stroke-width="3"/>')
    for k in range(0, 41):
        x = 120 + k * 19
        tall = k % 5 == 0
        s.append(f'<line x1="{x}" y1="{y}" x2="{x}" y2="{y + (34 if tall else 16)}" stroke="#fff" stroke-width="{3 if tall else 2}"/>')
    for k, lab in [(0, '88'), (10, '92'), (20, '96'), (30, '100')]:
        s.append(mono(120 + k * 19, y + 72, 22, lab, '#fff', 'middle', 1))
    nx = 120 + 25.5 * 19
    s.append(f'<rect x="{nx - 4}" y="{y - 70}" width="8" height="128" fill="{V}"/>')
    s.append(mono(nx, y - 86, 22, 'S-04', V, 'middle', 3))
    s.append(mono(880, y + 72, 22, 'MHz', '#fff', 'end', 2))
    s.append(f'<line x1="120" y1="1000" x2="880" y2="1000" stroke="#fff" stroke-width="2"/>')
    s.append(mono(120, 1045, 20, 'PIL S-04', '#fff'))
    s.append(mono(500, 1045, 20, 'SIGNAL FOUND', '#fff', 'middle'))
    s.append(mono(880, 1045, 20, 'MMXXVI', '#fff', 'end'))
    s.append(wide(W / 2, 1130, 30, 'POLINIS®', '#fff', 'middle', 10, 125, 700))
    s.append('</svg>')
    return ''.join(s)

# ---------- S-05 HOMEBASE ----------
def homebase():
    W, H = 1000, 1250
    ink = '#141218'
    s = [head(W, H)]
    cx, cy = 500, 470
    random.seed(505)
    ph = [random.random() * 6.28 for _ in range(4)]
    for k in range(1, 15):
        r0 = 22 * k + 6
        pts = []
        for a in range(0, 361, 4):
            t = math.radians(a)
            r = r0 * (1 + 0.16 * math.sin(2 * t + ph[0] + k * .15) + 0.09 * math.sin(3 * t + ph[1]) + 0.05 * math.sin(5 * t + ph[2] + k * .3))
            x, y = cx + r * math.cos(t) * 1.15, cy + r * math.sin(t)
            if 120 <= x <= 880 and 140 <= y <= 800:
                pts.append(f'{x:.1f},{y:.1f}')
            else:
                if len(pts) > 1:
                    s.append(f'<polyline points="{" ".join(pts)}" fill="none" stroke="{ink}" stroke-width="{3.2 if k % 5 == 0 else 1.8}"/>')
                pts = []
        if len(pts) > 1:
            s.append(f'<polyline points="{" ".join(pts)}" fill="none" stroke="{ink}" stroke-width="{3.2 if k % 5 == 0 else 1.8}"/>')
    s.append(f'<rect x="120" y="140" width="760" height="660" fill="none" stroke="{ink}" stroke-width="3"/>')
    s.append(f'<circle cx="{cx}" cy="{cy}" r="34" fill="none" stroke="{V}" stroke-width="7"/>')
    s.append(f'<path d="M{cx} {cy - 70}V{cy - 44}M{cx} {cy + 44}V{cy + 70}M{cx - 70} {cy}H{cx - 44}M{cx + 44} {cy}H{cx + 70}" stroke="{V}" stroke-width="7"/>')
    s.append(f'<circle cx="{cx}" cy="{cy}" r="7" fill="{V}"/>')
    s.append(wide(120, 905, 96, 'HOMEBASE', ink, 'start', -3))
    s.append(mono(120, 960, 26, '50.8760° N · 6.5858° E', V, 'start', 3))
    s.append(f'<line x1="120" y1="1010" x2="880" y2="1010" stroke="{ink}" stroke-width="2"/>')
    s.append(mono(120, 1055, 20, 'PIL S-05', ink))
    s.append(mono(500, 1055, 20, 'KNOW WHERE YOU STAND', ink, 'middle'))
    s.append(mono(880, 1055, 20, 'MMXXVI', ink, 'end'))
    s.append(wide(W / 2, 1140, 30, 'POLINIS®', ink, 'middle', 10, 125, 700))
    s.append('</svg>')
    return ''.join(s)

for name, fn in [('s-03-static', static), ('s-04-frequency', frequency), ('s-05-homebase', homebase)]:
    open(f'{OUT}/{name}.svg', 'w').write(fn())
print('ok')
