# Harvey's — website

Statische site voor Harvey's Women en Harvey's Men in Aalst, in de huisstijl
"Casual Luxury": een oranje vlak (#e04b00) met het woordmerk HARVEY'S en de slogan
CASUAL LUXURY in Microgramma Extended. Gewone HTML, CSS en JavaScript, zonder
buildstap. Later te hosten op Cloudflare Pages.

De ontwerpregels (kleuren, typografie, componenten, beweging, do's en don'ts) staan
in `DESIGN.md`. Wijzig je iets aan de vormgeving, houd dat bestand dan bij.

## Structuur

```
index.html          alle inhoud en structuur van de pagina
disclaimer.html     disclaimer (tekst van harveys.be), gelinkt in de footer
privacy.html        privacyverklaring (tekst van harveys.be), gelinkt in de footer
DESIGN.md           de ontwerpregels van de huisstijl op het web
css/tokens.css      kleuren, spacing, typografie, timing — alle waarden staan hier
css/base.css        reset en basiselementen
css/layout.css      containers, secties en rasters
css/components.css  header, hero, band, galerij, kaarten, formulier, footer
css/beweging.css    animaties bij het laden en op scroll (uit bij "minder beweging")
js/main.js          menu, header, hero, band, titels, klantenkaart, open/gesloten,
                    nieuwsbrief, reveal van secties, merkbanden, carrousel
js/galerij.js       tabbladen, de bol "Bekijk" en de lichtbak van de Collectie
tools/maak-logo.py  maakt het logo als SVG-paden en zet het in de pagina's
tools/fonts/        Michroma (OFL-licentie), invoer voor maak-logo.py
images/             alle beelden
favicon.svg
```

## Bekijken

Open `index.html` in de browser, of start een lokale server in deze map:

```sh
python3 -m http.server 8000
```

en ga naar http://localhost:8000.

## Huisstijl

- **Kleur**: oranje `--oranje` als vlak (hero, merken, nieuwsbrief, klantenkaart),
  met zwarte tekst erop, zoals in de huisstijl. Verder gebroken wit `--kalk`, linnen
  en warm zwart `--inkt`. Op de oranje vlakken ligt een fijne filmkorrel (`--korrel`).
- **Letters**: Michroma (de open heruitgave van Microgramma) voor titels, labels en
  navigatie, altijd in kapitalen; Instrument Serif voor uitspraken en titels van
  nieuws en lichtbak; Geist voor lopende tekst. Alle drie via Google Fonts.
- **Logo**: staat nooit als gewone tekst op de site, maar als SVG-paden (zie hieronder).

## Logo

Het logo komt uit het ontwerp `tools/logo-bron.svg` ("Harvey logo and slogan.svg"):
HARVEY’S met CASUAL LUXURY eronder, als tekst in Microgramma Extended Bold. Een
browser toont die tekst enkel juist als het lettertype op de computer staat, dus zet
een script elke letter om in een pad, op exact de plaats, grootte en spatiëring uit
het ontwerp:

```sh
python3 -m pip install fonttools uharfbuzz   # uharfbuzz is optioneel (kerning)
python3 tools/maak-logo.py
```

Het script zoekt het lettertype uit het ontwerp (PostScript-naam
`MicrogrammaExtD-Bol`) zelf op de computer, ook als het via Adobe Fonts in
Creative Cloud geactiveerd is. Het lettertype komt niet op de site en niet in deze
map; de licentie van Adobe Fonts staat het omzetten van een logo naar paden wel toe.
Vindt het script het lettertype niet, dan valt het terug op Michroma (`tools/fonts/`)
met een dunne omlijning voor het gewicht, en meldt het dat. Een ander lettertype of
ontwerp: `--font pad/naar/lettertype.otf`, `--bron ontwerp.svg`.

Het script schrijft `images/harveys-casual-luxury.svg` (zwart op transparant),
`images/harveys-casual-luxury-oranje.svg` (op het oranje vlak, zoals de huisstijl)
en `favicon.svg` (de H), en zet het logo in de pagina's tussen de markeringen
`<!-- logo:naam:… -->` (header, klantenkaart), `<!-- logo:slogan:… -->` (mobiel
menu, klantenkaart), `<!-- logo:lockup:… -->` (footer) en `<!-- logo:hero:… -->`
(hero, elk teken een apart pad voor de intro). Een nieuwe versie van het logo:
vervang `tools/logo-bron.svg` en draai het script opnieuw.

## Inhoud aanpassen

Alle teksten staan in `index.html`. Elke sectie is gemarkeerd met een commentaar
(`<!-- ===== Merken ===== -->`) en genummerd (`01`–`06`, ook in het mobiele menu).
Merken zijn `<li class="merk">`-blokken met een logo uit `images/merken/` en de naam
(`merk__naam`); nieuwsitems zijn `<article class="nieuws-item">`-blokken: kopiëren, plakken
en tekst wijzigen.

Titels met de klasse `kop` worden door JavaScript in letters gesplitst die bij het
scrollen één voor één opkomen; de tekst zelf pas je gewoon aan in de HTML.

De merken staan op het oranje vlak in twee banden (haar, hem) die traag
voorbijschuiven; JavaScript zet de lijst daarvoor tweemaal in het spoor. Bij
"minder beweging" staat de lijst stil als raster. Snelheid: `--merk-tempo` in
`css/tokens.css`; de breedte van één merk in `--merk-w` (vast, zodat Safari de
banden juist uitrekent), de hoogte van het logovak in `--merk-logo-h`. In beeld
staat enkel het logo: het schaalt in een vak van vaste maat, zodat geen enkel logo
groter wordt dan de andere. De naam staat er enkel voor schermlezers bij; vul ze dus
wel altijd in.

Een merk zonder logo krijgt tijdelijk de naam als tekst in het logovak:
`<span class="merk__tekst" aria-hidden="true">Naam</span>` in plaats van de `<img>`.
Logo's het liefst als SVG of transparante PNG, zwart of donker op transparant.
Ze worden in grijstinten getoond (`filter: grayscale`) en met `mix-blend-mode:
multiply`, zodat ze inktzwart op het oranje staan en een wit vlak van een
jpg-logo wegvalt.

De band met grote tekst onder de hero (Casual Luxury, Harvey’s Women, …) staat in
`index.html` in `<div class="band">`; de snelheid in `--band-tempo`.

## Beelden vervangen

Beelden staan in `images/`. Een foto vervangen: zet een nieuw bestand met dezelfde
naam op dezelfde plek, of pas het `src` en de `alt`-tekst in `index.html` aan.

| Bestand                              | Gebruik                                  |
| ------------------------------------ | ---------------------------------------- |
| `harveys-casual-luxury.svg`          | Logo, zwart op transparant (gegenereerd) |
| `harveys-casual-luxury-oranje.svg`   | Logo op het oranje vlak (gegenereerd)    |
| `hero-poster.jpg`                    | Stilstaand beeld tot de hero-video laadt |
| `winkel-women.jpg`                   | Gevel Harvey's Women (contact)           |
| `winkel-men.jpg`                     | Gevel Harvey's Men (contact)             |
| `winkel/*.jpg`                       | Carrousel bij "Over Harvey's": interieurfoto's van beide winkels |
| `collectie-schoenen.jpg`             | Nieuwsbericht Nathan-Baume               |
| `collectie/*.jpg`                    | Galerij: de collectiefoto's van harveys.be, `hem-`/`haar-` + merk |
| `merken/*`                           | Merklogo's, één bestand per merk         |

De bestanden `harveys-icon.*`, `harveys-logo.svg` en `harveys-logo-white.png` zijn
van de vorige huisstijl en worden niet meer gebruikt.

Foto's voor de galerij werken het best in staand formaat (4:5), minstens
1000 px breed. Een foto toevoegen: zet het bestand in `images/collectie/` en
kopieer een `<figure class="galerij__item">`-blok in `index.html` (in de groep
"Voor haar" of "Voor hem"); `data-merk` en `data-groep` zijn wat de lichtbak
toont, en het aantal op het tabblad telt vanzelf mee. Het raster is een bento:
elke zevende foto is dubbel zo groot (op een telefoon: elke vijfde over de volle
breedte). Het aantal kolommen staat in `--kolommen` in `css/tokens.css`.

## Hero

De hero is het oranje vlak van de huisstijl met het logo. Bij het laden komen de
letters van de naam één voor één omhoog en loopt de slogan open vanuit het midden
(± 2 s; timing in `--t-…` in `css/tokens.css`). Onder het logo staat een klein
videovenster; bij het scrollen blijft de hero staan en gaat het venster open tot de
video het hele scherm vult, waarna het logo licht wordt. De afstand van het venster
tot de rand in rust staat in `--v-top`, `--v-zij` en `--v-onder`, hoe lang de hero
blijft staan in `--hero-reis`. Met een muis volgt een zachte spotlight de cursor.

De video komt van Cloudflare Stream (HLS). De URL staat in `data-hls` op
`<video class="hero__video">` in `index.html`. Bij "minder beweging" blijft het
venster klein en toont het enkel de poster.

## Openingsuren

Onder de openingsuren toont een label of de winkels nu open zijn ("Nu open · tot
18 uur" of "Gesloten · opent maandag om 10 uur"). Het rekent in Belgische tijd en
kent de wettelijke feestdagen. Veranderen de uren, pas dan ook `setupStatus` in
`js/main.js` aan.

## Bronnen van de inhoud

Alle teksten, merken, adressen, telefoonnummers en openingsuren komen van
www.harveys.be (homepagina, collectiepagina's en het nieuwsbericht). De slogan
"Casual Luxury" komt uit de nieuwe huisstijl. Foto's in `images/` zijn van
harveys.be; merklogo's komen van Wikimedia Commons of van de websites van de merken
zelf. Voeg geen inhoud toe die niet bij Harvey's vandaan komt.

## Cloudflare Pages

Geen build command nodig; output directory: `/` (de hoofdmap).

De nieuwsbrief is in deze mockup nog niet gekoppeld: het formulier toont enkel
een bevestiging. Koppel later een Pages Function of een externe dienst in
`setupNewsletter` in `js/main.js`.
