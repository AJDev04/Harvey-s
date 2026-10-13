#!/usr/bin/env python3
"""Maakt het logo van Harvey's als SVG-paden uit het ontwerp tools/logo-bron.svg
(HARVEY’S met CASUAL LUXURY eronder).

Het ontwerp is tekst in Microgramma Extended Bold: per letter de plaats, de grootte
en de spatiëring. Een browser toont dat enkel juist als dat lettertype op de computer
staat. Daarom zet dit script elke letter om in een pad, op exact de plaats uit het
ontwerp; zo ziet iedereen hetzelfde logo. Het lettertype zelf komt niet op de site
en niet in deze map.

Het script zoekt het lettertype zelf: een geïnstalleerd lettertype met de
PostScript-naam uit het ontwerp (MicrogrammaExtD-Bol), ook als het via Adobe Fonts
(Creative Cloud) geactiveerd is. Wordt het niet gevonden, dan valt het terug op
Michroma (de open heruitgave van Microgramma, tools/fonts/), met een dunne omlijning
voor het gewicht van de Bold, en zegt het dat.

Wat het script schrijft:
  images/harveys-casual-luxury.svg         het logo, zwart op transparant
  images/harveys-casual-luxury-oranje.svg  het logo op het oranje vlak, zoals de huisstijl
  favicon.svg                              de H op een oranje vlak
  index.html, disclaimer.html, privacy.html, tussen de markeringen
      <!-- logo:naam:start --> … <!-- logo:naam:einde -->        de naam (header, bon)  
      <!-- logo:slogan:start --> … <!-- logo:slogan:einde -->    de slogan (menu, bon)  
      <!-- logo:lockup:start --> … <!-- logo:lockup:einde -->    naam + slogan (footer)
  index.html
      <!-- logo:hero:start --> … <!-- logo:hero:einde -->        het hero-logo, elk
                                                                teken een apart pad

Gebruik:  python3 tools/maak-logo.py [--bron ontwerp.svg] [--font lettertype.otf] [--vet 0.06]
Vereist:  python3 -m pip install fonttools
          (optioneel uharfbuzz: past ook de kerning van het lettertype toe)
"""

import argparse
import os
import re
import sys
import xml.etree.ElementTree as ET
from pathlib import Path

try:
    from fontTools.ttLib import TTFont
    from fontTools.pens.svgPathPen import SVGPathPen
    from fontTools.pens.transformPen import TransformPen
    from fontTools.pens.boundsPen import BoundsPen
except ImportError:
    sys.exit("fontTools ontbreekt: python3 -m pip install fonttools")

try:
    import uharfbuzz as hb
except ImportError:
    hb = None

WORTEL = Path(__file__).resolve().parent.parent
BRON = WORTEL / "tools" / "logo-bron.svg"
RESERVE_FONT = WORTEL / "tools" / "fonts" / "Michroma-Regular.ttf"
PAGINAS = [WORTEL / "index.html", WORTEL / "disclaimer.html", WORTEL / "privacy.html"]

# Waar geïnstalleerde lettertypes staan; Adobe Fonts bewaart geactiveerde
# lettertypes verborgen in de map van Creative Cloud
ZOEKPLEKKEN = [
    Path.home() / "Library" / "Fonts",
    Path("/Library/Fonts"),
    Path.home() / "Library" / "Application Support" / "Adobe" / "CoreSync" / "plugins" / "livetype",
    Path.home() / ".fonts",
    Path.home() / ".local" / "share" / "fonts",
    Path(os.environ.get("WINDIR", "C:/Windows")) / "Fonts",
]

SVG = "{http://www.w3.org/2000/svg}"
INKT = "#15110e"  # gelijk aan --inkt in css/tokens.css
ORANJE = "#e04b00"  # gelijk aan --oranje

GETAL = re.compile(r"-?\d+(?:\.\d+)?")


def rond(d):
    """Kort de getallen in een padbeschrijving in tot twee decimalen."""
    return GETAL.sub(lambda m: f"{float(m.group(0)):.2f}".rstrip("0").rstrip("."), d)


# ---------- Het ontwerp lezen ----------

def spatiering(waarde, grootte):
    """letter-spacing in SVG-eenheden: '.02em' is relatief aan de lettergrootte."""
    if not waarde:
        return 0.0
    waarde = waarde.strip()
    if waarde.endswith("em"):
        return float(waarde[:-2] or 0) * grootte
    return float(waarde.rstrip("px") or 0)


def lees_ontwerp(pad):
    """Geeft de regels van het ontwerp: per <text> de grootte, het lettertype en de
    stukken (x, y, spatiëring, tekst) uit de <tspan>'s, in absolute coördinaten.
    De grootste regel is de naam, de andere de slogan."""
    wortel = ET.parse(pad).getroot()
    regels = []
    for tekst in wortel.iter(SVG + "text"):
        m = re.search(r"translate\(\s*([-\d.]+)[\s,]+([-\d.]+)", tekst.get("transform", ""))
        tx, ty = (float(m.group(1)), float(m.group(2))) if m else (0.0, 0.0)
        grootte = float(tekst.get("font-size", "16").rstrip("px"))
        familie = tekst.get("font-family", "").split(",")[0].strip().strip("'\"")
        ls_tekst = tekst.get("letter-spacing")
        stukken = []
        if tekst.text and tekst.text.strip():
            stukken.append((tx, ty, spatiering(ls_tekst, grootte), tekst.text))
        for ts in tekst.iter(SVG + "tspan"):
            x = ts.get("x")
            ls = ts.get("letter-spacing", ls_tekst)
            stukken.append((tx + float(x) if x is not None else None,
                            ty + float(ts.get("y", 0)),
                            spatiering(ls, grootte),
                            ts.text or ""))
        regels.append({"grootte": grootte, "familie": familie, "stukken": stukken})
    if len(regels) < 2:
        sys.exit(f"{pad}: verwacht twee tekstregels (naam en slogan)")
    regels.sort(key=lambda r: -r["grootte"])
    return regels[0], regels[1], wortel.get("viewBox")


# ---------- Het lettertype ----------

def postscript_naam(pad):
    try:
        return TTFont(pad, lazy=True)["name"].getDebugName(6)
    except Exception:
        return None


def zoek_font(psnaam):
    """Zoekt een geïnstalleerd lettertype met deze PostScript-naam."""
    for plek in ZOEKPLEKKEN:
        if not plek.is_dir():
            continue
        for map_, _, bestanden in os.walk(plek):
            for b in bestanden:
                if b.lower().endswith((".otf", ".ttf")):
                    pad = Path(map_) / b
                    if postscript_naam(pad) == psnaam:
                        return pad
    return None


class Font:
    def __init__(self, pad):
        self.pad = Path(pad)
        self.tt = TTFont(pad)
        self.upm = self.tt["head"].unitsPerEm
        self.cmap = self.tt.getBestCmap()
        self.glyphs = self.tt.getGlyphSet()
        self.volgorde = self.tt.getGlyphOrder()
        self.psnaam = self.tt["name"].getDebugName(6)
        self.naam = self.tt["name"].getDebugName(4)
        self.hb = hb.Font(hb.Face(hb.Blob.from_file_path(str(pad)))) if hb else None

    def vorm(self, tekst):
        """De glyfen van een tekst: (glyf, voortgang, dx, dy) in font-eenheden. Met
        HarfBuzz komt de kerning van het lettertype erbij, zoals in een browser."""
        for teken in tekst:
            if ord(teken) not in self.cmap:
                sys.exit(f"{self.naam} heeft geen teken voor {teken!r}")
        if self.hb:
            buf = hb.Buffer()
            buf.add_str(tekst)
            buf.guess_segment_properties()
            hb.shape(self.hb, buf)
            return [(self.volgorde[i.codepoint], p.x_advance, p.x_offset, p.y_offset)
                    for i, p in zip(buf.glyph_infos, buf.glyph_positions)]
        return [(self.cmap[ord(t)], self.tt["hmtx"][self.cmap[ord(t)]][0], 0, 0) for t in tekst]


def zet(font, regel, vrij, verschuiving=0.0):
    """Zet een regel als lijst van (glyf, pad, grenzen). Met het lettertype van het
    ontwerp staat elk stuk op zijn eigen x uit het ontwerp; met een ander lettertype
    ("vrij") loopt de tekst door met de eigen breedtes van dat lettertype.
    verschuiving schuift de hele regel horizontaal op."""
    schaal = regel["grootte"] / font.upm
    tekens = []
    x = None
    for sx, sy, ls, tekst in regel["stukken"]:
        if sx is not None and (x is None or not vrij):
            x = sx + verschuiving
        for glyf, voortgang, dx, dy in font.vorm(tekst):
            matrix = (schaal, 0, 0, -schaal, x + dx * schaal, sy - dy * schaal)
            pen = SVGPathPen(font.glyphs)
            font.glyphs[glyf].draw(TransformPen(pen, matrix))
            d = pen.getCommands()
            if d:  # een spatie heeft geen pad
                grens = BoundsPen(font.glyphs)
                font.glyphs[glyf].draw(TransformPen(grens, matrix))
                tekens.append((glyf, rond(d), grens.bounds))
            x += voortgang * schaal + ls
    return tekens


# ---------- SVG schrijven ----------

def omvang(*regels):
    bs = [b for tekens in regels for _, _, b in tekens]
    return min(b[0] for b in bs), min(b[1] for b in bs), max(b[2] for b in bs), max(b[3] for b in bs)


def kader(lijn, *regels):
    x0, y0, x1, y1 = omvang(*regels)
    m = lijn / 2
    return x0 - m, y0 - m, x1 - x0 + 2 * m, y1 - y0 + 2 * m


def vb(k):
    return " ".join(f"{v:.2f}" for v in k)


def groep(tekens, lijn, kleur, klasse=None, extra=None):
    paden = []
    for i, (glyf, d, b) in enumerate(tekens):
        attrs = f' class="{klasse}"' if klasse else ""
        if extra:
            attrs += extra(i, b)
        paden.append(f'<path{attrs} d="{d}"/>')
    lijn_attr = f' stroke="{kleur}" stroke-width="{lijn:.2f}" stroke-linejoin="round"' if lijn else ""
    return f'<g fill="{kleur}"{lijn_attr}>' + "".join(paden) + "</g>"


def svg_bestand(viewbox, inhoud):
    return (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{viewbox}">\n'
        f"  <title>Harvey’s — Casual Luxury</title>\n  {inhoud}\n</svg>\n"
    )


def vervang(pad, start, einde, fragment):
    tekst = pad.read_text(encoding="utf-8")
    patroon = re.compile(re.escape(start) + r".*?" + re.escape(einde), re.S)
    if not patroon.search(tekst):
        return
    tekst = patroon.sub(lambda _: f"{start}\n{fragment}\n{einde}", tekst)
    pad.write_text(tekst, encoding="utf-8")
    print(f"  {pad.name}: {start.split(':')[1]} bijgewerkt")


def main():
    p = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    p.add_argument("--bron", default=str(BRON), help="het ontwerp (svg met tekst)")
    p.add_argument("--font", help="lettertypebestand; standaard: zoek het lettertype uit het ontwerp")
    p.add_argument("--vet", type=float, default=None,
                   help="omlijning in em die het gewicht verhoogt; standaard 0 met het lettertype "
                        "van het ontwerp, 0.06 met Michroma")
    args = p.parse_args()

    naam_regel, slogan_regel, _ = lees_ontwerp(args.bron)
    psnaam = naam_regel["familie"]

    pad = Path(args.font) if args.font else zoek_font(psnaam)
    if pad is None:
        print(f"! {psnaam} niet gevonden (installeer het, of activeer het in Adobe Fonts);")
        print("  terugvallen op Michroma: de letters lijken op het ontwerp, maar zijn het niet.")
        pad = RESERVE_FONT
    font = Font(pad)
    echt = font.psnaam == psnaam
    vet = args.vet if args.vet is not None else (0.0 if echt else 0.06)
    print(f"Ontwerp: {Path(args.bron).name}  ·  lettertype: {font.naam}"
          + ("" if echt else "  (vervanger)") + (f"  ·  omlijning {vet} em" if vet else "")
          + ("" if hb else "  ·  zonder uharfbuzz: geen kerning"))

    naam = zet(font, naam_regel, vrij=not echt)
    slogan = zet(font, slogan_regel, vrij=not echt)
    if not echt:
        # met andere breedtes de slogan opnieuw centreren onder de naam, zoals in het ontwerp
        n0, _, n1, _ = omvang(naam)
        s0, _, s1, _ = omvang(slogan)
        slogan = zet(font, slogan_regel, vrij=True, verschuiving=(n0 + n1) / 2 - (s0 + s1) / 2)

    lijn_naam = vet * naam_regel["grootte"]
    lijn_slogan = vet * slogan_regel["grootte"]
    lockup = kader(lijn_naam, naam, slogan)

    # 1. Losse bestanden
    inhoud = groep(naam, lijn_naam, INKT) + groep(slogan, lijn_slogan, INKT)
    (WORTEL / "images" / "harveys-casual-luxury.svg").write_text(svg_bestand(vb(lockup), inhoud), encoding="utf-8")

    # Het oranje vlak van de huisstijl: 1000 × 1000, de naam op 42 pt, het logo in het midden
    s = 42 / naam_regel["grootte"]
    x0, y0, w, h = lockup
    vlak = (
        f'<rect width="1000" height="1000" fill="{ORANJE}"/>\n'
        f'  <g transform="translate({500 - (x0 + w / 2) * s:.2f} {500 - (y0 + h / 2) * s:.2f}) scale({s:g})">{inhoud}</g>'
    )
    (WORTEL / "images" / "harveys-casual-luxury-oranje.svg").write_text(svg_bestand("0 0 1000 1000", vlak), encoding="utf-8")

    # Favicon: de H van de naam, inkt op oranje
    h_teken = naam[:1]
    hx0, hy0, hw, hh = kader(lijn_naam, h_teken)
    zijde = max(hw, hh) * 1.55
    favicon = (
        f'<rect width="{zijde:.2f}" height="{zijde:.2f}" rx="{zijde * 0.18:.2f}" fill="{ORANJE}"/>\n'
        f'  <g transform="translate({zijde / 2 - (hx0 + hw / 2):.2f} {zijde / 2 - (hy0 + hh / 2):.2f})">'
        + groep(h_teken, lijn_naam, INKT) + "</g>"
    )
    (WORTEL / "favicon.svg").write_text(svg_bestand(f"0 0 {zijde:.2f} {zijde:.2f}", favicon), encoding="utf-8")
    print("  images/harveys-casual-luxury.svg, images/harveys-casual-luxury-oranje.svg, favicon.svg geschreven")

    # 2. Naam, slogan en lockup inline in de pagina's; de kleur volgt de tekstkleur
    def inline(viewbox, *groepen):
        return f'<svg viewBox="{vb(viewbox)}" aria-hidden="true" focusable="false">' + "".join(groepen) + "</svg>"

    stukken = {
        "naam": inline(kader(lijn_naam, naam), groep(naam, lijn_naam, "currentColor")),
        "slogan": inline(kader(lijn_slogan, slogan), groep(slogan, lijn_slogan, "currentColor")),
        "lockup": inline(lockup, groep(naam, lijn_naam, "currentColor"), groep(slogan, lijn_slogan, "currentColor")),
    }
    for pagina in PAGINAS:
        for sleutel, fragment in stukken.items():
            vervang(pagina, f"<!-- logo:{sleutel}:start -->", f"<!-- logo:{sleutel}:einde -->", fragment)

    # 3. Hero-logo: de naam in een clipPath (de letters komen van onder op), de
    #    slogan met per teken --dx: de afstand tot het midden van de slogan,
    #    waarvandaan het teken naar zijn plaats schuift (de spatiëring "loopt open").
    nx, ny, nw, nh = kader(lijn_naam, naam)
    s0, _, s1, _ = omvang(slogan)
    midden = (s0 + s1) / 2

    hero = (
        f'          <svg class="hero__logo-svg" viewBox="{vb(lockup)}" aria-hidden="true" focusable="false">\n'
        f'            <clipPath id="hero-naam-masker"><rect x="{nx:.2f}" y="{ny:.2f}" width="{nw:.2f}" height="{nh:.2f}"/></clipPath>\n'
        f'            <g clip-path="url(#hero-naam-masker)">'
        + groep(naam, lijn_naam, "currentColor", "teken", lambda i, b: f' style="--i: {i}"')
        + "</g>\n            "
        + groep(slogan, lijn_slogan, "currentColor", "slogan-teken",
                lambda i, b: f' style="--i: {i}; --dx: {(midden - (b[0] + b[2]) / 2) * 0.45:.2f}"')
        + "\n          </svg>"
    )
    vervang(WORTEL / "index.html", "<!-- logo:hero:start -->", "<!-- logo:hero:einde -->", hero)


if __name__ == "__main__":
    main()
