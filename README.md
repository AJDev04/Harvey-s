# Harvey's — website

Statische site voor Harvey's Women en Harvey's Men in Aalst.
Gewone HTML, CSS en JavaScript, zonder buildstap. Later te hosten op Cloudflare Pages.

## Structuur

```
index.html          alle inhoud en structuur van de pagina
disclaimer.html     disclaimer (tekst van harveys.be), gelinkt in de footer
privacy.html        privacyverklaring (tekst van harveys.be), gelinkt in de footer
css/tokens.css      kleuren, spacing, typografie — alle waarden staan hier
css/base.css        reset en basiselementen
css/layout.css      containers, secties en rasters
css/components.css  header, panelen, kaarten, formulier, footer
css/beweging.css    animaties bij het laden en op scroll (uit bij "minder beweging")
js/main.js          mobiel menu, hero-video, nieuwsbriefformulier, reveal van secties
js/galerij.js       lichtbak voor de fotogalerij in de sectie Collectie
tools/splits-woordmerk.py  splitst het woordmerk in losse letters voor de hero-animatie
images/             alle beelden
favicon.png
```

## Bekijken

Open `index.html` in de browser, of start een lokale server in deze map:

```sh
python3 -m http.server 8000
```

en ga naar http://localhost:8000.

## Inhoud aanpassen

Alle teksten staan in `index.html`. Elke sectie is gemarkeerd met een commentaar
(`<!-- ===== Merken ===== -->`). Merken zijn `<li class="merk">`-blokken met een
logo uit `images/merken/` en de naam eronder; nieuwsitems zijn
`<article class="nieuws-item">`-blokken: kopiëren, plakken en tekst wijzigen.

De merken staan in twee banden (haar, hem) die traag voorbijschuiven; JavaScript
zet de lijst daarvoor tweemaal in het spoor. Bij "minder beweging" staat de lijst
stil als raster. Snelheid: `--merk-tempo` in `css/tokens.css`.

Een merk zonder logo krijgt tijdelijk de naam als tekst in het logovak:
`<span class="merk__tekst" aria-hidden="true">Naam</span>` in plaats van de `<img>`.
Logo's het liefst als SVG of transparante PNG, zwart of donker op transparant.
Ze worden in grijstinten getoond (`filter: grayscale`) en met `mix-blend-mode:
multiply`, zodat een wit vlak van een jpg-logo wegvalt op de achtergrond.

## Beelden vervangen

Beelden staan in `images/`. Een foto vervangen: zet een nieuw bestand met dezelfde
naam op dezelfde plek, of pas het `src` en de `alt`-tekst in `index.html` aan.

| Bestand                    | Gebruik                                  |
| -------------------------- | ---------------------------------------- |
| `harveys-icon.png`         | Logo in de header (ook favicon)          |
| `harveys-logo-white.png`   | Logo in de footer                        |
| `harveys-logo.svg`         | Woordmerk als vector; invoer voor `tools/splits-woordmerk.py` |
| `harveys-icon.svg`         | Icoon als vector; de paden staan inline in `index.html` |
| `hero-poster.jpg`          | Stilstaand beeld tot de hero-video laadt |
| `winkel-women.jpg`         | Gevel Harvey's Women (contact)            |
| `winkel-men.jpg`           | Gevel Harvey's Men (contact)              |
| `winkel/*.jpg`             | Carrousel bij "Over Harvey's": interieurfoto's van beide winkels |
| `collectie-schoenen.jpg`   | Nieuwsbericht Nathan-Baume               |
| `collectie/*.jpg`          | Galerij: de collectiefoto's van harveys.be, `hem-`/`haar-` + merk |
| `merken/*`                 | Merklogo's, één bestand per merk         |

Foto's voor de galerij werken het best in staand formaat (4:5), minstens
1000 px breed. Een foto toevoegen: zet het bestand in `images/collectie/` en
kopieer een `<figure class="galerij__item">`-blok in `index.html` (in de groep
"Voor haar" of "Voor hem"); `data-merk` en `data-groep` zijn wat de lichtbak
toont. De minimumbreedte van de foto's staat in `css/tokens.css`
(`--galerij-item-w`).

## Hero-video en logo-animatie

Bovenop de video speelt één keer de intro van het logo: de H verschijnt links,
veegt naar rechts en laat het woordmerk als spoor achter, schuift omhoog naar het
midden, de ring tekent zich eromheen, een haarlijn opent en de tagline en het
adres faden in. De timing staat in `css/tokens.css` (`--t-…`), de
animaties in `css/beweging.css`. De acht letter-SVG's in `index.html` worden
gegenereerd met `python3 tools/splits-woordmerk.py`; pas het logo dus aan in
`images/harveys-logo.svg` en draai het script opnieuw.

De video op de landingspagina komt van Cloudflare Stream (HLS). De URL staat in
`data-hls` op `<video class="hero__video">` in `index.html`. Hoe donker het beeld
is, regel je met `--hero-video-opacity` in `css/tokens.css`: de video ligt op een
zwart vlak, dus een lagere waarde geeft een donkerder beeld. De grootte van het
logo staat in `--hero-logo-w`.

## Bronnen van de inhoud

Alle teksten, merken, adressen, telefoonnummers en openingsuren komen van
www.harveys.be (homepagina, collectiepagina's en het nieuwsbericht). Foto's in
`images/` zijn van dezelfde site; merklogo's komen van Wikimedia Commons of van de
websites van de merken zelf. Voeg geen inhoud toe die niet bij Harvey's vandaan komt.

## Cloudflare Pages

Geen build command nodig; output directory: `/` (de hoofdmap).

De nieuwsbrief is in deze mockup nog niet gekoppeld: het formulier toont enkel
een bevestiging. Koppel later een Pages Function of een externe dienst in
`setupNewsletter` in `js/main.js`.
