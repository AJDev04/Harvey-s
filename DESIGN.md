# DESIGN.md — Harvey's · Casual Luxury

> A burnt-orange field, a wide black wordmark and a lot of air: the boutique you walk into
> in sneakers and leave in cashmere.

## 1. Visual Theme & Atmosphere

**Style**: Casual Luxury. Brand-led editorial: one loud colour, extended display type,
calm photography, hairline detail.
**Keywords**: burnt orange, extended grotesque, wide tracking, editorial, gallery-calm,
tactile, confident, playful-in-the-details
**Tone**: hip, self-assured, warm, NOT flashy, NOT corporate, NOT "sale-shop"
**Feel**: a concept store in Antwerp at golden hour: orange light on bone walls, one
black-lettered sign above the door, everything else quiet.

The new identity (`new style harveys's.svg`) is the source of truth: a **#e04b00** square
with **HARVEY'S** set in Microgramma Extended Bold and **CASUAL LUXURY** underneath at
~29% of the size, tracked +0.2em. The site follows that brand board. Orange is used for
whole blocks of colour, never for small decoration. The display face is wide and set in
capitals. Black type goes on orange, bone is used everywhere else, and a single italic
serif adds a soft, luxurious accent.

**Interaction Tier**: L2 (smooth interaction), with one L3 moment (the sticky hero aperture)
**Dependencies**: none. Vanilla CSS + JS, IntersectionObserver, rAF. hls.js is lazy-loaded
for the hero video only (already part of the site).

## 2. Color Palette & Roles

```css
:root {
  /* Brand */
  --oranje: #e04b00;          /* brand field: hero, merken, nieuwsbrief, cadeaubon */
  --oranje-diep: #b83c00;     /* hover on orange; orange text on light (AA 5:1) */
  --oranje-licht: #f2835a;    /* hero spotlight core, never for text */

  /* Neutrals */
  --kalk: #f4efe8;            /* page background ("bone") */
  --linnen: #e9e1d5;          /* alternate section surface */
  --inkt: #15110e;            /* warm black: wordmark, text, footer */
  --steen: #655d55;           /* secondary text (AA on kalk + linnen) */
  --wit: #fff;
  --zwart: #000;

  /* RGB helpers for rgba() */
  --oranje-rgb: 224, 75, 0;
  --inkt-rgb: 21, 17, 14;
  --kalk-rgb: 244, 239, 232;

  /* Derived, always mixed from the palette */
  --lijn: color-mix(in srgb, var(--inkt) 14%, transparent);         /* hairlines on light */
  --lijn-sterk: color-mix(in srgb, var(--inkt) 32%, transparent);
  --lijn-op-oranje: color-mix(in srgb, var(--inkt) 28%, transparent);
  --lijn-op-inkt: color-mix(in srgb, var(--kalk) 18%, transparent);
  --beeld-vlak: color-mix(in srgb, var(--linnen) 70%, var(--kalk)); /* image placeholder */
  --kalk-op-inkt: color-mix(in srgb, var(--kalk) 72%, var(--inkt)); /* muted text on ink */
  --scrim: color-mix(in srgb, var(--inkt) 38%, transparent);         /* over video */

  /* Film grain for orange surfaces: black noise at low alpha, baked into the SVG
     (feColorMatrix last row). Used as `background: var(--oranje) var(--korrel)`. */
  --korrel: url("data:image/svg+xml,…feTurbulence…");

  /* Semantic: the open/closed chip only */
  --succes: var(--oranje);     /* "Nu open": the pulsing dot is brand orange */
  --fout: var(--oranje-diep);  /* form errors */
  --rust: var(--steen);        /* "Gesloten" */
}
```

| Pair | Ratio | Use |
|---|---|---|
| inkt on oranje | 4.6 : 1 | all text on orange (AA) |
| inkt on kalk | 17 : 1 | body |
| steen on kalk / linnen | 5.7 / 5.0 : 1 | secondary text |
| oranje-diep on kalk | 5.0 : 1 | small orange text, links |
| oranje on kalk | 3.5 : 1 | **large display only** (≥ 24px) |
| kalk on inkt | 17 : 1 | footer, lightbox, cadeaubon |
| oranje on inkt | 3.9 : 1 | **large display only** (≥ 24px), cadeaubon lead |

**Color Rules:**
- Every colour is a token in `css/tokens.css`; components never contain hex values.
- Orange is a *field*, not a sprinkle: it fills a whole section, a card, a voucher or a pill. Small
  orange accents are limited to the active-state dot, the focus ring and the progress line.
- Text on orange is always `--inkt`, as on the brand board, never white.
- Never put orange text under 24px on a light background; use `--oranje-diep`.
- One loud section at a time: orange sections never touch each other; a kalk/linnen/inkt
  section always sits between them.

## 3. Typography Rules

**Font Stack:**
```css
@import url('https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Instrument+Serif:ital@0;1&family=Michroma&display=swap');

--font-display: 'Michroma', 'Microgramma Extd D', 'Eurostile Extended', 'Arial Black', sans-serif;
--font-serif:   'Instrument Serif', 'Iowan Old Style', Georgia, serif;
--font-sans:    'Geist', 'Helvetica Neue', Arial, sans-serif;
```

**Michroma** is the open-source revival of Microgramma, the face on the brand board, and
carries all live display text. The **logo itself is never live text**: `tools/maak-logo.py`
reads the design file `tools/logo-bron.svg` and outlines it with the real Microgramma
Extended Bold (found via Adobe Fonts) at the exact positions, sizes and tracking of the
design. The font file never ships with the site. Without the font the script falls back
to Michroma with a hairline stroke for weight. To change the logo, replace the design file
and re-run the script; every logo on the site updates.

| Role | Font | Size | Weight | Line Height | Letter Spacing |
|------|------|------|--------|-------------|----------------|
| Logo (hero) | SVG paths from `tools/logo-bron.svg` (Microgramma Extd D Bold) | `clamp(17rem, 58vw, 50rem)` wide | Bold | n/a | as designed (slogan +0.2em) |
| Section H2 | Michroma, UPPERCASE | `clamp(1.625rem, 0.9rem + 3.6vw, 4.25rem)` | 400 | 1.05 | 0.01em |
| Statement | Instrument Serif | `clamp(1.75rem, 1.1rem + 2.8vw, 3.75rem)` | 400 / italic | 1.12 | -0.01em |
| H3 | Instrument Serif | `clamp(1.5rem, 1.2rem + 1.2vw, 2.25rem)` | 400 | 1.15 | n/a |
| Body | Geist | 1.0625rem | 400 | 1.65 | n/a |
| Lead | Geist | `clamp(1.125rem, 1rem + 0.5vw, 1.375rem)` | 400 | 1.45 | n/a |
| Label / eyebrow / nav | Michroma, UPPERCASE | 0.6875rem | 400 | 1.4 | 0.14em |
| Small / meta | Geist | 0.8125rem | 500 | 1.5 | 0.02em |

**Typography Rules:**
- Michroma only in capitals, only for display, labels, numbers and navigation. Never for a
  paragraph.
- Instrument Serif is the "luxury" voice: story statements, news titles, the lightbox title.
  Italic is reserved for one or two words of emphasis per statement.
- Geist carries every paragraph, form and address.
- Display tracking is never negative; labels are always ≥ 0.12em.
- **NEVER use**: Crimson Text, Karla (old identity), Inter, Roboto, Poppins, Montserrat,
  any script or handwritten face.

**Text Decoration** (per text-decoration-rules):
- Hero logo: no gradient, no shadow. The brand board is flat and that holds here.
- H2: no gradient, no shadow, no index number.
- Marquee: the second row is *outlined* (`-webkit-text-stroke`, transparent fill) for rhythm.
- Body: no decoration ever.

## 4. Component Stylings

### Buttons (`.knop`): ink pill, magnetic on pointer devices
```css
.knop {
  display: inline-flex; align-items: center; justify-content: center; gap: var(--space-3);
  min-height: 3rem; padding: 0 var(--space-6);
  background: var(--inkt); color: var(--kalk);
  border: 1px solid var(--inkt); border-radius: var(--radius-pil);
  font: 400 var(--fs-label) / 1 var(--font-display);
  letter-spacing: var(--tracking-label); text-transform: uppercase;
  cursor: pointer;
  transition: background-color var(--duur-snel) var(--ease), color var(--duur-snel) var(--ease),
              transform var(--duur-magneet) var(--ease-uit);
}
.knop:hover        { background: var(--kalk); border-color: var(--kalk); color: var(--inkt); }  /* reads on orange and on bone */
.knop:active       { transform: scale(0.97); }
.knop:focus-visible{ outline: 2px solid var(--inkt); outline-offset: 3px; }
.knop:disabled     { opacity: 0.4; cursor: not-allowed; }
```

### Cards: news item, store card, gallery tile
```css
.galerij__knop { border-radius: var(--radius-md); overflow: hidden; background: var(--beeld-vlak); }
.galerij__knop img { transition: transform var(--duur-beeld) var(--ease-uit); }
.galerij__knop:hover img,
.galerij__knop:focus-visible img { transform: scale(1.05); }
.galerij__knop:focus-visible { outline: 2px solid var(--oranje-diep); outline-offset: 3px; }
.galerij__label { /* orange tag slides up on hover; always visible on touch */
  background: var(--oranje); color: var(--inkt); font-family: var(--font-display);
  transform: translateY(calc(100% + var(--space-3)));
}
.galerij__knop:hover .galerij__label { transform: none; }
```

### Navigation
```css
.header { position: fixed; inset: 0 0 auto; transition: transform var(--duur-menu) var(--ease-uit), background-color var(--duur-snel) var(--ease); }
.header.is-gescrold { background: color-mix(in srgb, var(--kalk) 90%, transparent); backdrop-filter: blur(12px); box-shadow: 0 1px 0 var(--lijn); }
.header.is-weg { transform: translateY(-100%); }  /* hides on scroll-down, returns on scroll-up */
.nav__link { font: 400 var(--fs-label)/1 var(--font-display); letter-spacing: var(--tracking-label); text-transform: uppercase; }
.nav__link::after { transform: scaleX(0); transform-origin: right; }  /* underline slides in */
.nav__link:hover::after { transform: scaleX(1); transform-origin: left; }
/* < 48em: full-screen orange panel, giant Michroma links that stagger in */
```

### Links
```css
a { color: var(--oranje-diep); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
a:hover { color: var(--inkt); text-decoration-thickness: 2px; }
.pil-link { border: 1px solid var(--lijn-sterk); border-radius: var(--radius-pil); padding: var(--space-2) var(--space-4); }
.pil-link:hover { background: var(--inkt); color: var(--kalk); border-color: var(--inkt); }
```

### Tags / Badges
```css
.label   { font: 400 var(--fs-label)/1.4 var(--font-display); letter-spacing: var(--tracking-label); text-transform: uppercase; }
.status  { display: inline-flex; gap: var(--space-2); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pil); background: var(--kalk); }
.status__stip { width: .5rem; height: .5rem; border-radius: 50%; background: var(--rust); }
.status.is-open .status__stip { background: var(--succes); animation: stip 2s var(--ease) infinite; }
```

### Segmented tabs (collectie): sliding orange indicator
```css
.tabs { position: relative; display: inline-flex; padding: 4px; border: 1px solid var(--lijn-sterk); border-radius: var(--radius-pil); }
.tabs__knop[aria-selected="true"] { color: var(--inkt); }
.tabs__glijder { position: absolute; background: var(--oranje); border-radius: var(--radius-pil);
  transform: translateX(var(--x)); width: var(--w); transition: transform var(--duur-menu) var(--ease-uit), width var(--duur-menu) var(--ease-uit); }
```

### Cadeaubon: two paper vouchers (signature detail)
An ink section with two A6 vouchers (105 × 148) stacked at an angle: the orange cover
(logo, slogan, MEN · WOMEN) behind, the bone voucher itself in front (hairline inner frame,
*Cadeaubon* in Instrument Serif italic, dotted "geschonken door" lines, "1 jaar geldig" in
`--oranje-diep`). Everything on the vouchers is sized in `cqi`, so it scales with the image.
On enter the vouchers fan out from a straight stack; on hover devices the pair tilts up
to 6° in 3D and a glare follows the pointer. Next to it: lead, a hairline fact list
(`dl`), an orange pill (`.knop--oranje`) and the phone numbers.

### Form (nieuwsbrief)
```css
.veld input { background: transparent; border: 0; border-bottom: 1px solid var(--inkt); border-radius: 0; color: var(--inkt); min-height: 3rem; }
.veld input:hover { border-bottom-width: 2px; }
.veld input:focus-visible { outline: none; border-bottom: 2px solid var(--inkt); box-shadow: 0 2px 0 0 var(--inkt); }
.veld input[aria-invalid="true"] { border-bottom-color: var(--fout); }
```

## 5. Layout Principles

**Container:**
- Max width: `--maxw: 84rem`
- Gutter: `clamp(1rem, 4vw, 3rem)`
- Narrow variant (legal pages): `44rem`

**Spacing Scale:** 0.25 · 0.5 · 0.75 · 1 · 1.5 · 2 · 3 · 4 · 6 · 8 rem
- Section padding: `clamp(4rem, 9vw, 9rem)`
- Section head → content: `3rem`
- Component gap: `1rem – 2rem`

**Grid:**
```css
/* Section head: title | intro, on one baseline row (desktop) */
.sectie__kop { display: grid; grid-template-columns: auto 1fr; column-gap: var(--space-5); }

/* Collectie: bento, every 7th photo is 2×2 and the rest pack around it */
.galerij__raster { display: grid; grid-template-columns: repeat(var(--kolommen), 1fr); grid-auto-flow: dense; gap: var(--galerij-gap); }
.galerij__item:nth-child(7n + 1) { grid-column: span 2; grid-row: span 2; }

/* Contact: two stores side by side, a small grey house number beside each name */
.contact { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-7); }
```

## 6. Depth & Elevation

The site is flat on purpose. Depth comes from colour blocks, scale and overlap, not shadow.

| Level | Treatment | Use |
|-------|-----------|-----|
| Flat | none | sections, gallery, text |
| Hairline | `box-shadow: 0 1px 0 var(--lijn)` | sticky header once scrolled |
| Lifted | `0 1.5rem 3rem -1.5rem rgba(var(--inkt-rgb), .45)` | cadeaubon, lightbox image |
| Overlay | `backdrop-filter: blur(12px)` + 90% kalk | header only (≤ 14px blur) |

## 7. Animation & Interaction

**Motion Philosophy**: slow in, soft landing. Everything moves on `transform`/`opacity`
with `cubic-bezier(0.16, 1, 0.3, 1)`. Motion follows the scroll and the pointer and never
starts on its own, except the one-time logo intro.
**Tier**: L2 + one pinned L3 scene

### Signature moments (6 required categories, 8 moments total)
| # | Category | Where | Effect |
|---|---|---|---|
| 1 | Text: Hero H1 | Hero logo | glyphs rise out of a mask one by one (SplitText); the slogan "tracks in" from the centre |
| 2 | Background | Hero | brand-orange field + film grain + a soft spotlight that follows the pointer |
| 3 | Component (L3) | Hero → video | **aperture**: a small video window under the logo opens to full-bleed as you scroll (sticky, clip-path) |
| 4 | First-scroll hook | After hero | two giant Michroma marquee rows, speed + direction coupled to scroll velocity (ScrollVelocity) |
| 5 | Text: Section H2 | All H2 | letters float up out of a mask on enter (ScrollFloat) |
| 6 | Text: Body | Over: statement | words light up from 15% to 100% as you scroll (ScrollReveal) |
| 7 | Component | Collectie | segmented tabs + bento grid + orange "Bekijk" cursor bubble + FLIP lightbox |
| 8 | Element | CTA / cadeaubon | magnetic buttons; vouchers fan out, 3D tilt + glare on hover |

**Clever detail:** the opening-hours block knows Belgian time and public holidays and shows
a live chip: *Nu open · tot 18 uur* with a pulsing orange dot, or *Gesloten · opent
maandag om 10 uur*.

### Entrance Animation
```css
@keyframes logo-teken { from { transform: translateY(115%); } }
.hero__logo .teken { transform-box: fill-box; animation: logo-teken 1100ms var(--ease-uit) calc(250ms + var(--i) * 70ms) both; }
@keyframes slogan-in { from { opacity: 0; transform: translateX(calc(var(--dx) * 1px)); } }
.hero__logo .slogan-teken { animation: slogan-in 1300ms var(--ease-uit) 900ms both; }
```

### Scroll Behavior
```js
// Hero aperture: --p goes 0 → 1 over the sticky travel; CSS turns it into a clip-path
const p = Math.min(1, Math.max(0, -rect.top / ((hero.offsetHeight - innerHeight) * 0.8)));
hero.style.setProperty('--p', p.toFixed(4));
```
```css
.hero__venster { clip-path: inset(calc((1 - var(--p)) * var(--v-top)) calc((1 - var(--p)) * var(--v-zij))
                                  calc((1 - var(--p)) * var(--v-onder)) calc((1 - var(--p)) * var(--v-zij)) round calc((1 - var(--p)) * 6px)); }
```
- Text blocks: fade-up 2.5rem → 0 over 1.3s, staggered 60ms (IntersectionObserver).
- Header: hidden on scroll-down, back on scroll-up, solid once past 8px.

### Hover & Focus States
- Every interactive element has `:hover` and `:focus-visible`; the focus ring is 2px
  `--oranje-diep` (2px `--inkt` on orange surfaces), offset 3px.
- Images: scale 1.05 over 900ms.
- Links: underline thickens; nav underline slides in from the left.

### Special Effects
- Pointer effects (spotlight, magnet, tilt, cursor bubble) only under `(hover: hover) and (pointer: fine)`; all pointermove handlers are rAF-throttled.
- The marquee and video pause when off-screen (IntersectionObserver).

### Reduced Motion
```css
@media (prefers-reduced-motion: reduce) {
  html { scroll-behavior: auto; }
  *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
  .hero { height: auto; }            /* no sticky travel; the window stays at its resting size */
  .hero__plak { position: relative; }
  .band__rij { transform: none !important; }  /* marquee is a static line */
}
```
JS mirrors this: no split-text, no scrub, no autoplay, no carousel auto-advance.

## 8. Do's and Don'ts

### Do
- Use orange as a full field (hero, merken, nieuwsbrief, cadeaubon) with ink type on it.
- Let the wordmark breathe: at least 1× its cap-height of clear space on every side.
- Set Michroma in capitals with wide tracking; pair it with Instrument Serif for warmth.
- Keep photography untouched: no filters, duotones or overlays (except the hero scrim).
- Keep every text from harveys.be. The only new line is the slogan *Casual Luxury*.

### Don't
- ❌ Don't hard-code a hex value outside `tokens.css`.
- ❌ Don't set the logo in live text; always use the generated SVG paths.
- ❌ Don't put two orange sections next to each other, or orange text under 24px on light.
- ❌ Don't use white text on orange; the brand board uses black.
- ❌ Don't add drop shadows, gradients-on-text, glows or glassmorphism cards.
- ❌ Don't use Michroma for paragraphs or for anything in lower case.
- ❌ Don't animate `filter: blur()` on moving elements or put `backdrop-filter` on large scrolling areas.
- ❌ Don't add more than one pinned (sticky-scrub) scene; the hero is the only one.
- ❌ Don't use emoji or stock icons. Arrows are typographic (→ ←) or inline SVG.
- ❌ Don't round corners beyond 6px on images; pills (999px) are for controls only.

## 9. Responsive Behavior

**Breakpoints:**
| Name | Width | Key Changes |
|------|-------|-------------|
| Desktop | ≥ 64em (1024px) | 4-column bento, H2 grid head, nav inline |
| Tablet | 48–64em | 3-column bento, nav inline, contact 2-up |
| Mobile | < 48em (768px) | 2-column gallery (every 5th full-width), full-screen orange menu, stacked contact, hero aperture starts wider |

**Touch Targets:** minimum 44 × 44px (nav toggle 48px, tabs 44px, carousel buttons 48px)
**Collapsing Strategy:** grids collapse to fewer columns, never to horizontal scroll (only
the carousel and marquees scroll sideways, on purpose). Pointer-only effects switch off on touch.
Gallery labels are always visible on touch.

```css
@media (max-width: 47.99em) {
  :root { --kolommen: 2; --v-top: 60%; --v-onder: 14%; --v-zij: 18%; }
  .galerij__item:nth-child(n) { grid-column: auto; grid-row: auto; }
  .galerij__item:nth-child(5n + 1) { grid-column: span 2; }
  .contact { grid-template-columns: 1fr; }
}
@media (min-width: 48em) and (max-width: 63.99em) { :root { --kolommen: 3; } }
```
