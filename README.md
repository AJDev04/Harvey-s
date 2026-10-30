# Harvey's — website

Statische site voor Harvey's Women en Harvey's Men in Aalst, in de huisstijl
"Casual Luxury": een oranje vlak (#e04b00) met het woordmerk HARVEY'S en de slogan
CASUAL LUXURY in Microgramma Extended. Gewone HTML, CSS en JavaScript, zonder
buildstap. Later te hosten op Cloudflare Pages.

Dezelfde site bestaat ook als WordPress-thema, waarin de winkel zelf foto's, nieuws,
merken, teksten en het logo beheert: zie [WordPress-thema](#wordpress-thema).

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
js/main.js          menu, header, hero, band, titels, cadeaubon, open/gesloten,
                    nieuwsbrief, reveal van secties, merkbanden, carrousel
js/galerij.js       tabbladen, de bol "Bekijk" en de lichtbak van de Collectie
tools/maak-logo.py  maakt het logo als SVG-paden en zet het in de pagina's
tools/maak-thema.sh bouwt het WordPress-thema als zip in dist/
tools/haal-collectiefotos.sh haalt de productfoto's van Voor haar op (voorbeeldbeelden)
tools/fonts/        Michroma (OFL-licentie), invoer voor maak-logo.py
wordpress/harveys/  het WordPress-thema: PHP-sjablonen en de beheerschermen
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

- **Kleur**: oranje `--oranje` als vlak (hero, merken, nieuwsbrief),
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
`images/harveys-casual-luxury-oranje.svg` (op het oranje vlak, zoals de huisstijl),
`images/harveys-naam.svg` en `images/harveys-slogan.svg` (naam en slogan apart, voor
het WordPress-thema) en `favicon.svg` (de H), en zet het logo in de pagina's tussen de markeringen
`<!-- logo:naam:… -->` (header, cadeaubon), `<!-- logo:slogan:… -->` (mobiel
menu, cadeaubon), `<!-- logo:lockup:… -->` (footer) en `<!-- logo:hero:… -->`
(hero, elk teken een apart pad voor de intro). Een nieuwe versie van het logo:
vervang `tools/logo-bron.svg` en draai het script opnieuw.

## Inhoud aanpassen

Alle teksten staan in `index.html`. Elke sectie is gemarkeerd met een commentaar
(`<!-- ===== Merken ===== -->`).
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
| `harveys-naam.svg`, `harveys-slogan.svg` | Naam en slogan apart, voor de startinhoud van het WordPress-thema (gegenereerd) |
| `hero-poster.jpg`                    | Stilstaand beeld tot de hero-video laadt |
| `winkel-women.jpg`                   | Gevel Harvey's Women (contact)           |
| `winkel-men.jpg`                     | Gevel Harvey's Men (contact)             |
| `winkel/*.jpg`                       | Carrousel bij "Over Harvey's": interieurfoto's van beide winkels |
| `collectie-schoenen.jpg`             | Nieuwsbericht Nathan-Baume               |
| `collectie/*.jpg`                    | Galerij: `hem-`/`haar-` + merk of beschrijving. Voor haar staan enkel stukken die zeker bij Harvey's liggen: `haar-prl-donsjack-khaki` en `haar-7fam-lotta-corduroy` (nagekeken op een video uit de winkel), `haar-nosecrets-cardigan-macaron`, `haar-senso-neela-brown` en `haar-reset-felice-coffee-bean` (opgezocht aan de hand van het label), allemaal voorbeeldbeelden opgehaald met `sh tools/haal-collectiefotos.sh` (zie [Bronnen](#bronnen-van-de-inhoud)); plus `haar-cambio-1`, `haar-cambio-3` en `haar-polo-ralph-lauren-1`, collectiefoto's van harveys.be. Niet in gebruik: `haar-cambio-2`, `haar-liu-jo-*`, `haar-reset-1`, `haar-forallmankind-1`, `haar-guess-*` |
| `campagne-guess-*.jpg`               | Campagnebeelden van Guess FW26/27 (modellen, motor, close-up), nog niet in gebruik |
| `liujo/*.jpg`                        | Campagnebeelden van Liu Jo FW26, niet meer in de galerij; `liujo-avond-4x5` is de avondfoto al bijgesneden voor WordPress (dat snijdt in het midden en zou het gezicht wegsnijden) |
| `merken/*`                           | Merklogo's, één bestand per merk         |
| `videjo-logo.svg`                    | "Webdesign door VIDEJO" in de footer, linkt naar videjo.be; `tools/maak-thema.sh` zet het ook in `assets/` van het WordPress-thema |

De bestanden `harveys-icon.*`, `harveys-logo.svg` en `harveys-logo-white.png` zijn
van de vorige huisstijl en worden niet meer gebruikt.

Foto's voor de galerij werken het best in staand formaat (4:5), minstens
1000 px breed. Een foto toevoegen: zet het bestand in `images/collectie/` en
kopieer een `<figure class="galerij__item">`-blok in `index.html` (in de groep
"Voor haar" of "Voor hem"); `data-merk` en `data-groep` zijn wat de lichtbak
toont, met de `alt` van de foto als beschrijving (zonder `alt`: groep en merk).
Valt een gezicht weg bij het bijsnijden tot 4:5, zet dan op de `<img>` een
`style="object-position: 50% 20%"` (de tweede waarde: hoe ver van boven). In
WordPress staat de alt-tekst bij de foto in de mediabibliotheek; daar wordt elke
foto in het midden bijgesneden. Het raster is een bento:
elke zevende foto is dubbel zo groot (op een telefoon: elke vijfde over de volle
breedte). Het aantal kolommen staat in `--kolommen` in `css/tokens.css`.
Geeft dat een gat (bij een aantal foto's dat niet uitkomt), zet dan op de
`<figure>` van een foto die groot zou worden de klasse `galerij__item--klein`:
die blijft dan gewoon klein. Bij Voor haar (8 foto's) staat ze op de 1ste, 6de en
8ste foto: dan zijn alle foto's even groot en is het raster vol op laptop en
telefoon (op een tablet, met drie kolommen, gaat 8 niet op).

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
kent de wettelijke feestdagen. In de statische site staan de uren als standaard in
`setupStatus` in `js/main.js` (maandag tot zaterdag 10–18 uur); het WordPress-thema
geeft de ingestelde uren mee in `data-uren` op het label.

## Bronnen van de inhoud

Alle teksten, merken, adressen, telefoonnummers en openingsuren komen van
www.harveys.be (homepagina, collectiepagina's, het nieuwsbericht, disclaimer en
privacy), zo letterlijk mogelijk overgenomen. De Engelse zin bij de nieuwsbrief
("Sign up to our newsletter to receive exclusive offers.") staat er in het
Nederlands. De slogan "Casual Luxury" komt uit de nieuwe huisstijl; de gegevens bij
de cadeaubon komen van de bon uit de winkel (op harveys.be staat geen cadeaubon).
Foto's in `images/` zijn van harveys.be, behalve de campagnebeelden van Guess
FW26/27 (via de persdienst van Fashion Club 70) en van Liu Jo FW26 (uit hun B2B-deck),
en vijf productfoto's in de Collectie (Polo Ralph Lauren, 7 For All Mankind,
No Secrets, Senso en Reset): stukken die in de winkel liggen, nagekeken op een
video uit de winkel of opgezocht aan de hand van het label, met de foto van de
website van het merk (de Senso-foto van de webshop van een verkoper). Dat zijn **enkel voorbeeldbeelden om de site aan
Harvey's voor te stellen**. Vervang ze door de
officiële beelden van de merken (of eigen foto's) voor de site online gaat;
merklogo's komen van Wikimedia Commons of
van de websites van de merken zelf. Voeg geen inhoud toe die niet bij Harvey's
vandaan komt.

## Cloudflare Pages

Geen build command nodig; output directory: `/` (de hoofdmap).

De nieuwsbrief is in de statische site niet gekoppeld: het formulier toont enkel
een bevestiging. In het WordPress-thema werkt ze wel (zie hieronder).

## WordPress-thema

Hetzelfde ontwerp als WordPress-thema. De vormgeving is gedeeld: `css/` en `js/`
gaan ongewijzigd mee, dus een aanpassing daar geldt voor beide versies. Alles wat
per winkel verschilt, staat niet in de code maar in de admin van WordPress: logo,
icoontje, kleur, teksten, foto's, merken, winkels en openingsuren. Het thema zelf
bevat niets van Harvey's; dat komt er pas in met de startinhoud. Zo werkt hetzelfde
thema ook voor een tweede winkel.

### Bouwen

```sh
sh tools/maak-thema.sh
```

Dat maakt in `dist/`:

| Bestand                             | Voor                                                     |
| ----------------------------------- | -------------------------------------------------------- |
| `harveys.zip` (± 15 MB)             | Harvey's: met het logo, de foto's en de teksten van de huidige site |
| `harveys-zonder-startinhoud.zip`    | een andere winkel: enkel het thema (± 270 kB)            |

### Installeren

1. Maak eerst een back-up van de bestaande site (of test op een kopie/staging van
   de hosting): zodra je het thema activeert, ziet iedereen de nieuwe site.
2. In WordPress: Weergave → Thema's → Nieuw thema → Thema uploaden, kies de zip en
   activeer. Is de upload te groot voor de hosting, zet dan de map `dist/harveys/`
   via FTP in `wp-content/themes/`.
3. Instellingen → Algemeen: Sitetitel (`Harvey’s`), Ondertitel (`Casual Luxury`;
   schermlezers en zoekmachines lezen die als de naam van het logo) en de taal
   Nederlands (België), voor de datums.
4. Enkel voor Harvey's: Website → Startinhoud → Startinhoud importeren. Dat zet het
   logo en het icoontje, alle teksten, de winkels en uren, 38 collectiefoto's, 26
   merken met logo, het nieuwsbericht en de pagina's Privacy en Disclaimer erin.
   Eén keer doen.

### Wat waar staat in de admin

| Op de site                      | In WordPress                                                        |
| ------------------------------- | ------------------------------------------------------------------- |
| Logo (header, hero, bon, footer) | Website → Logo en kleur: naam en slogan als SVG                    |
| Icoontje in het browsertabblad  | Website → Logo en kleur (SVG), of Site-pictogram in de Customizer (PNG) |
| Kleur (hero, merken, nieuwsbrief, accenten) | Website → Logo en kleur                                 |
| Hero-video, band                | Website → Hero en band                                              |
| Nieuws                          | Nieuws (de gewone berichten van WordPress, met een uitgelichte afbeelding). Bij meer dan één bericht een carrousel, het nieuwste eerst; hoeveel berichten erin staan: Website → Nieuws en merken |
| Collectie                       | Website → Collectie: twee groepen (standaard Voor haar en Voor hem, de naam pas je aan), foto's kiezen, slepen voor de volgorde, merk eronder typen |
| Merken                          | Merken: per merk een naam, een logo en Voor haar en/of Voor hem; Volgorde bepaalt de plaats |
| Over ons                        | Website → Over ons: titel, uitspraak, tekst en foto's                |
| Cadeaubon                       | Website → Cadeaubon                                                 |
| Contact en openingsuren         | Website → Winkels en uren (een tweede winkel mag leeg blijven)     |
| Nieuwsbrief                     | Website → Nieuwsbrief; inschrijvingen in Website → Inschrijvingen, als CSV te downloaden |
| Privacy, Disclaimer, footer     | gewone pagina's; Website → Algemeen; Instellingen → Privacy          |

Een sectie zonder inhoud (geen nieuws, geen foto's, ...) verdwijnt vanzelf, ook uit
het menu. In teksten maakt `*woord*` een woord schuin; de uitspraak bij Over ons staat
altijd recht. Het onderschrift van een foto in de carrousel staat er enkel voor
schermlezers.

### Een tweede winkel

Installeer `harveys-zonder-startinhoud.zip` op de WordPress-site van die winkel en
vul alles in onder Website, Merken en Nieuws. Zolang er geen logo is, staat de
Sitetitel er in letters (in de hero en de footer even breed als een logo). Het
logo: Website → Logo en kleur, met twee SVG-bestanden.

- **Naam** en **slogan** apart, elk met de tekst omgezet naar contouren
  (Illustrator: Tekst → Contouren maken; daarna Exporteren als SVG).
- Elke letter een apart pad: dan komen de letters in de hero één voor één op.
  Eén pad voor alles werkt ook, dan komt het logo in één keer.
- De kleur doet er niet toe: het logo neemt de kleur van zijn plek aan (zwart op
  oranje, licht in de footer). Witte vlakken om letters "uit te sparen" werken
  daarom niet; gebruik gaten in het pad zelf (zoals in een O).
- Met *Breedte van de slogan* en *Ruimte tussen naam en slogan* zet je de slogan
  juist onder de naam, zoals in het ontwerp van het logo.
- Op hetzelfde tabblad: het *Icoontje* voor het browsertabblad (een SVG met zijn
  eigen kleuren) en de *Kleur* van de vlakken en accenten. De tekst op die kleur is
  zwart; kies dus een heldere kleur.

Elke SVG die geüpload wordt, ook een merklogo, wordt eerst opgekuist: scripts,
links naar buiten en ingesloten pagina's gaan eruit (`wordpress/harveys/inc/svg.php`).

### De vormgeving aanpassen

Pas `css/` of `js/` aan (zoals voor de statische site), bouw opnieuw met
`sh tools/maak-thema.sh` en upload de zip opnieuw (WordPress vraagt of het thema
vervangen mag worden: ja). De instellingen, foto's en merken blijven: die staan in
de database, niet in het thema. Wat enkel voor WordPress geldt, staat in
`wordpress/harveys/assets/css/wordpress.css`.

### De code

```
wordpress/harveys/
  functions.php            laadt alles uit inc/
  front-page.php           de homepage: de secties onder elkaar
  template-parts/sectie-*  één bestand per sectie (hero, nieuws, collectie, ...)
  header.php, footer.php   de header met menu, de footer
  page.php, single.php     een pagina (Privacy, ...), een nieuwsbericht
  inc/instellingen.php     de velden per tabblad onder Website
  inc/beheer.php           de schermen onder Website
  inc/logo.php             het logo inline, en samengesteld voor hero en footer
  inc/svg.php              SVG's uploaden (opgekuist) en tonen
  inc/merken.php           het berichttype Merk met de groepen
  inc/nieuwsbrief.php      inschrijvingen bewaren en exporteren
  inc/hulp.php             openingsuren in woorden, winkels, telefoonlinks
  inc/startinhoud*.php     de import van de huidige site
```

De startinhoud zelf (foto's, merklogo's en het logo van Harvey's in `starter/`) zet
`tools/maak-thema.sh` er bij het bouwen bij, uit `images/` en `favicon.svg`; enkel
`harveys.zip` bevat ze.

Lokaal testen zonder hosting kan met WordPress Playground:

```sh
sh tools/maak-thema.sh
npx @wp-playground/cli@latest server --login \
  --mount=$PWD/dist/harveys:/wordpress/wp-content/themes/harveys
```

Activeer daarna het thema bij Weergave → Thema's. Alles wat je daar doet, is weg
als je de server stopt.
