#!/usr/bin/env python3
"""Splitst het woordmerk (images/harveys-logo.svg) in acht losse SVG's, één per
teken, en schrijft ze als fragment in index.html tussen de markeringen <!-- woordmerk:start --> en <!-- woordmerk:einde -->.

Per teken worden de x-grenzen berekend uit de padcoördinaten (bézierkrommen
worden bemonsterd). Elk teken krijgt een viewBox met dezelfde hoogte, zodat de
basislijn overal gelijk blijft; de witruimte tussen de tekens wordt als
--gap meegegeven, in dezelfde eenheden als de viewBox.

Gebruik:  python3 tools/splits-woordmerk.py
"""

import re
import sys
from pathlib import Path

WORTEL = Path(__file__).resolve().parent.parent
BRON = WORTEL / "images" / "harveys-logo.svg"
DOELEN = [WORTEL / "index.html"]
START, EINDE = "<!-- woordmerk:start -->", "<!-- woordmerk:einde -->"

TEKENS = ["H", "A", "R", "V", "E", "Y", "’", "S"]
HOOGTE = 78  # viewBox-hoogte voor elk teken (0 tot 78)
STAPPEN = 32  # monsterpunten per kromme

GETAL = re.compile(r"-?(?:\d+\.?\d*|\.\d+)(?:e-?\d+)?")
COMMANDO = re.compile(r"[MmLlHhVvCcSsQqTtZz]")


def tokens(d):
    """Zet een d-attribuut om in een lijst van commando's en getallen."""
    uit = []
    for m in re.finditer(r"[MmLlHhVvCcSsQqTtZz]|" + GETAL.pattern, d):
        t = m.group(0)
        uit.append(t if COMMANDO.fullmatch(t) else float(t))
    return uit


def bezier3(p0, p1, p2, p3, t):
    u = 1 - t
    return tuple(
        u * u * u * a + 3 * u * u * t * b + 3 * u * t * t * c + t * t * t * e
        for a, b, c, e in zip(p0, p1, p2, p3)
    )


def bezier2(p0, p1, p2, t):
    u = 1 - t
    return tuple(u * u * a + 2 * u * t * b + t * t * c for a, b, c in zip(p0, p1, p2))


def x_grenzen(d):
    """Loopt het pad af en geeft (x_min, x_max) terug."""
    ts = tokens(d)
    i = 0
    cmd = None
    x = y = 0.0
    sx = sy = 0.0
    laatste_c2 = None  # tweede controlepunt van de vorige C/S, voor S
    laatste_q1 = None
    xs = []

    def punt(px, py):
        xs.append(px)

    def kromme3(c1, c2, eind):
        nonlocal x, y, laatste_c2, laatste_q1
        for k in range(1, STAPPEN + 1):
            punt(*bezier3((x, y), c1, c2, eind, k / STAPPEN))
        x, y = eind
        laatste_c2, laatste_q1 = c2, None

    def kromme2(c1, eind):
        nonlocal x, y, laatste_c2, laatste_q1
        for k in range(1, STAPPEN + 1):
            punt(*bezier2((x, y), c1, eind, k / STAPPEN))
        x, y = eind
        laatste_q1, laatste_c2 = c1, None

    while i < len(ts):
        t = ts[i]
        if isinstance(t, str):
            cmd = t
            i += 1
            if cmd in "Zz":
                x, y = sx, sy
                laatste_c2 = laatste_q1 = None
            continue
        rel = cmd.islower()
        c = cmd.upper()
        if c == "M":
            x, y = (x + ts[i], y + ts[i + 1]) if rel else (ts[i], ts[i + 1])
            sx, sy = x, y
            punt(x, y)
            i += 2
            cmd = "l" if rel else "L"  # volgende paren zijn lijnstukken
            laatste_c2 = laatste_q1 = None
        elif c == "L":
            x, y = (x + ts[i], y + ts[i + 1]) if rel else (ts[i], ts[i + 1])
            punt(x, y)
            i += 2
            laatste_c2 = laatste_q1 = None
        elif c == "H":
            x = x + ts[i] if rel else ts[i]
            punt(x, y)
            i += 1
            laatste_c2 = laatste_q1 = None
        elif c == "V":
            y = y + ts[i] if rel else ts[i]
            punt(x, y)
            i += 1
            laatste_c2 = laatste_q1 = None
        elif c == "C":
            n = ts[i : i + 6]
            if rel:
                n = [x + n[0], y + n[1], x + n[2], y + n[3], x + n[4], y + n[5]]
            kromme3((n[0], n[1]), (n[2], n[3]), (n[4], n[5]))
            i += 6
        elif c == "S":
            n = ts[i : i + 4]
            if rel:
                n = [x + n[0], y + n[1], x + n[2], y + n[3]]
            c1 = (2 * x - laatste_c2[0], 2 * y - laatste_c2[1]) if laatste_c2 else (x, y)
            kromme3(c1, (n[0], n[1]), (n[2], n[3]))
            i += 4
        elif c == "Q":
            n = ts[i : i + 4]
            if rel:
                n = [x + n[0], y + n[1], x + n[2], y + n[3]]
            kromme2((n[0], n[1]), (n[2], n[3]))
            i += 4
        elif c == "T":
            n = ts[i : i + 2]
            if rel:
                n = [x + n[0], y + n[1]]
            c1 = (2 * x - laatste_q1[0], 2 * y - laatste_q1[1]) if laatste_q1 else (x, y)
            kromme2(c1, (n[0], n[1]))
            i += 2
        else:
            sys.exit(f"Onbekend padcommando: {cmd}")
    return min(xs), max(xs)


def main():
    svg = BRON.read_text(encoding="utf-8")
    paden = re.findall(r'<path d="([^"]+)"', svg)
    if len(paden) != len(TEKENS):
        sys.exit(f"Verwachtte {len(TEKENS)} paden, vond er {len(paden)}.")

    grenzen = [x_grenzen(d) for d in paden]
    regels = []
    vorige_max = grenzen[0][0]
    print(f"{'teken':<6}{'x_min':>9}{'x_max':>9}{'breedte':>9}{'gap':>7}")
    for teken, d, (x0, x1) in zip(TEKENS, paden, grenzen):
        gap = x0 - vorige_max
        breedte = x1 - x0
        print(f"{teken:<6}{x0:9.2f}{x1:9.2f}{breedte:9.2f}{gap:7.2f}")
        regels.append(
            f'      <svg class="teken" viewBox="{x0:.2f} 0 {breedte:.2f} {HOOGTE}" '
            f'style="--gap: {gap:.2f}" aria-hidden="true">'
            f'<path d="{d}" fill="currentColor" /></svg>'
        )
        vorige_max = x1

    for doel in DOELEN:
        if not doel.exists():
            print(f"\n{doel.name} bestaat niet, overgeslagen.")
            continue
        html = doel.read_text(encoding="utf-8")
        a, b = html.find(START), html.find(EINDE)
        if a < 0 or b < 0:
            sys.exit(f"Markeringen {START} / {EINDE} niet gevonden in {doel.name}.")
        # zelfde inspringing als de startmarkering
        inspring = html[html.rfind("\n", 0, a) + 1 : a]
        fragment = "\n".join(inspring + r.strip() for r in regels)
        html = html[: a + len(START)] + "\n" + fragment + "\n" + inspring + html[b:]
        doel.write_text(html, encoding="utf-8")
        print(f"\n{len(regels)} tekens weggeschreven in {doel.name}.")


if __name__ == "__main__":
    main()
