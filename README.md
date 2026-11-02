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
js/galerij.js       tabbladen, de bol "Bekijk", de lichtbak en de video's van de Collectie
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
telefoon (op een tablet, met drie kolommen, gaat 8 niet op). In WordPress hoeft
dat niet: daar rekent het thema het zelf uit (zie
[Collectie: geen gat in het raster](#collectie-geen-gat-in-het-raster)).

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
| `harveys.zip` (± 14 MB)             | Harvey's: met het logo, de foto's en de teksten van de huidige site |
| `harveys-zonder-startinhoud.zip`    | een andere winkel: enkel het thema (± 275 kB), zonder `starter/` en zonder `inc/startinhoud-data.php` |

De versie van het thema staat in `wordpress/harveys/style.css` (`Version:`) en in
`HARVEYS_VERSIE` in `functions.php`; verhoog ze allebei bij een nieuwe zip. Ontbreekt een
bestand dat de startinhoud noemt, dan stopt het script en zegt het welk; de vijf
productfoto's van Voor haar staan niet in git en haal je eerst op met
`sh tools/haal-collectiefotos.sh`.

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
   logo en het icoontje, alle teksten, de winkels en uren, 13 collectiefoto's (8 voor
   haar, 5 voor hem, zoals in `index.html`), 21 merken (17 met logo), de foto's van de winkels, het
   nieuwsbericht en de pagina's Privacy en Disclaimer erin. Eén keer doen. Ontbreekt
   een bestand, dan slaat de import het over en gaat verder; op het einde staat welke
   bestanden overgeslagen zijn. Loopt de server vast op een bestand, herlaad dan de
   pagina: de import slaat dat bestand over en gaat verder waar hij was. In de zip
   zonder startinhoud staat Startinhoud niet in het menu.

### Wat waar staat in de admin

| Op de site                      | In WordPress                                                        |
| ------------------------------- | ------------------------------------------------------------------- |
| Logo (header, hero, bon, footer) | Website → Logo en kleur: naam en slogan als SVG                    |
| Icoontje in het browsertabblad  | Website → Logo en kleur (SVG), of Site-pictogram in de Customizer (PNG) |
| Kleur (hero, merken, nieuwsbrief, accenten) | Website → Logo en kleur                                 |
| Hero-video, band                | Website → Hero en band                                              |
| Nieuws                          | Nieuws (de gewone berichten van WordPress, met een uitgelichte afbeelding). Bij meer dan één bericht een carrousel, het nieuwste eerst; hoeveel berichten erin staan: Website → Nieuws en merken |
| Collectie                       | Website → Collectie: twee groepen (standaard Voor haar en Voor hem, de naam pas je aan), foto's en video's kiezen, slepen voor de volgorde, merk eronder typen. Welke foto's groot zijn, kiest het thema zelf (zie hieronder); video's: zie [Collectie: video's](#collectie-videos) |
| Merken                          | Merken: per merk een naam, een logo en Voor haar en/of Voor hem; Volgorde bepaalt de plaats |
| Over ons                        | Website → Over ons: titel, uitspraak, tekst en foto's                |
| Cadeaubon                       | Website → Cadeaubon                                                 |
| Contact en openingsuren         | Website → Winkels en uren (een tweede winkel mag leeg blijven)     |
| Nieuwsbrief                     | Website → Nieuwsbrief; inschrijvingen in Website → Inschrijvingen, als CSV te downloaden (enkel voor beheerders) |
| Privacy, Disclaimer, footer     | gewone pagina's; Website → Algemeen; Instellingen → Privacy          |

Een sectie zonder inhoud (geen nieuws, geen foto's, ...) verdwijnt vanzelf, ook uit
het menu. Een foto die uit de mediabibliotheek verwijderd is, telt niet meer mee. In
teksten maakt `*woord*` een woord schuin; de uitspraak bij Over ons staat altijd
recht. Het onderschrift van een foto in de carrousel staat er enkel voor
schermlezers.

### Collectie: video's

Naast foto's kan een groep van de Collectie ook video's bevatten (Website → Collectie,
Foto's en video's toevoegen): beelden van in de winkel, zodat klanten zien wat er nu
ligt. Een video telt in het raster als een foto, ook om te bepalen welke groot zijn:
zet je ze als eerste, dan krijgt ze het grote vak.

- **In het raster** speelt een video zonder geluid in een lus, zodra ze voor een kwart
  in beeld is; buiten beeld of in het andere tabblad staat ze stil. Rechtsboven staat
  een kleine afspeelknop, zodat je ziet dat het geen foto is. Bij "minder beweging"
  speelt ze niet vanzelf.
- **Klik je erop**, dan opent ze groot in de lichtbak, met geluid en de knoppen van
  de browser. Bladeren gaat gewoon door foto's en video's heen; bij het sluiten stopt
  ze.
- **Formaat**: mp4 (H.264), webm of mov; een ander formaat weigert het beheerscherm.
  Kort en klein houden, liefst onder 10 MB: de hosting begrenst vaak de grootte van
  een upload, en een zware video laadt traag op een gsm. Staand (4:5) past het best;
  het raster snijdt bij zoals bij een foto.
- **Stilstaand beeld**: in Media de video openen, Meer details bewerken, Uitgelichte
  afbeelding. Zonder toont de browser het eerste beeld van de video.
- **Geen merk**: bij een video hoort geen merk, het beheerscherm toont er geen veld
  voor. In het raster staat er geen label onder; in de lichtbak staat "In de winkel"
  als titel, en schermlezers horen "Video in de winkel" met de naam van de groep.

Code: `harveys_is_video()` en `harveys_video_poster()` in `inc/hulp.php`, het veld
`'video' => true` in `inc/instellingen.php`, het tegeltje in
`template-parts/sectie-collectie.php` (`galerij__item--video`, `galerij__video`), het
afspelen en de lichtbak in `js/galerij.js` (`#lichtbak-video`), de stijl in
`css/components.css`. De statische site heeft (nog) geen video's, maar dezelfde
lichtbak en dezelfde code.

### Collectie: geen gat in het raster

In de statische site lost `galerij__item--klein` met de hand een gat in het raster
op. In WordPress rekent het thema per groep zelf uit welke foto's groot zijn, apart
voor laptop (4 kolommen) en gsm (2 kolommen), zodat het raster vol is
(`inc/galerij.php`; de klassen `galerij__item--groot` en `galerij__item--breed` op
een `galerij__raster--berekend`, de stijl in `assets/css/wordpress.css`). De winkel
hoeft niets te doen. De regel:

- **Gsm**: elke vijfde foto staat over de volle breedte (1, 6, 11, ...). Is het
  aantal foto's 2 of 4 meer dan een veelvoud van 5 (2, 4, 7, 9, 12, 14, ...), dan
  wordt de laatste brede foto gewoon klein.
- **Laptop**: elke zevende foto is groot, 2 × 2 (1, 8, 15, ...). Elke 14 foto's
  vullen zo precies 5 rijen. Is het raster daarna niet vol, dan zoekt het thema de
  kleinste aanpassing: zo weinig mogelijk foto's die van grootte wisselen (een grote
  wordt klein, of een kleine wordt groot), liefst een grote klein maken, liefst
  zonder rij met enkel grote foto's, en zo ver mogelijk achteraan.
- Met **1 of 3 foto's** kunnen vier kolommen nooit vol: dan blijft het kleinste gat
  (1 foto: groot, de helft van de breedte; 3 foto's: op één rij met één leeg vak).
- Een **tablet** (3 kolommen) gebruikt dezelfde grote foto's als de laptop; daar kan
  wel een gat blijven.

Zo valt het uit voor 1 tot 16 foto's (de nummers van de grote foto's):

| Foto's | Laptop: groot (2 × 2) | Gsm: volle breedte | Laptop |
| ------ | --------------------- | ------------------ | ------ |
| 1      | 1                     | 1                  | 4 lege vakken |
| 2      | 1, 2                  | geen               | vol    |
| 3      | geen                  | 1                  | 1 leeg vak |
| 4      | geen                  | geen               | vol    |
| 5      | 1                     | 1                  | vol    |
| 6      | 1, 4                  | 1, 6               | vol    |
| 7      | 1, 6, 7               | 1                  | vol    |
| 8      | geen                  | 1, 6               | vol    |
| 9      | 1                     | 1                  | vol    |
| 10     | 1, 8                  | 1, 6               | vol    |
| 11     | 1, 8, 9               | 1, 6, 11           | vol    |
| 12     | geen                  | 1, 6               | vol    |
| 13     | 1                     | 1, 6, 11           | vol    |
| 14     | 1, 8                  | 1, 6               | vol    |
| 15     | 1, 8, 13              | 1, 6, 11           | vol    |
| 16     | 1, 8, 15, 16          | 1, 6, 11, 16       | vol    |

Op gsm is het raster altijd vol. Wie de volgorde van de foto's zelf wil kiezen:
de eerste foto van een groep is meestal groot.

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
Een SVG uploaden kan enkel een beheerder; een redacteur gebruikt voor een merklogo
een PNG.

### Rechten en veiligheid

- **Beheerder**: alles. **Redacteur**: alle tabbladen onder Website behalve Logo en
  kleur en Startinhoud, plus Nieuws en Merken; geen SVG's, geen inschrijvingen.
- Elk formulier in de admin heeft een nonce en een controle van het recht; een
  tabblad bewaart enkel zijn eigen velden, en elk veld wordt opgekuist volgens zijn
  soort (ook bij de import). Foto-id's die niet in de mediabibliotheek staan, worden
  niet bewaard.
- **Nieuwsbrief**: inschrijvingen en de CSV-export zijn enkel voor beheerders (het
  zijn persoonsgegevens). Een adres staat er maar één keer in, ongeacht hoofdletters.
  Tegen spam: een verborgen veld dat enkel een bot invult, een tijdsval (wie binnen 2
  seconden na het laden verstuurt, is geen mens) en hoogstens 10 inschrijvingen per
  IP-adres per 10 minuten. Een bot krijgt "bedankt", maar er wordt niets bewaard. Het
  formulier heeft bewust geen nonce: de homepage wordt vaak gecachet, en dan zou een
  verlopen nonce echte bezoekers tegenhouden. Staat de sectie uit, dan neemt het
  formulier niets meer aan.
- Functies, constanten, opties, hooks, berichttypes en scripts beginnen met
  `harveys_`, `HARVEYS_` of `harveys-`, zodat niets botst met plugins (Genesis,
  WPForms, ...).

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
  inc/galerij.php          Collectie: welke foto's groot zijn, zodat het raster vol is
  inc/nieuwsbrief.php      inschrijvingen bewaren en exporteren
  inc/hulp.php             openingsuren in woorden, winkels, telefoonlinks
  inc/startinhoud*.php     de import van de huidige site
```

De startinhoud zelf (foto's, merklogo's en het logo van Harvey's in `starter/`) zet
`tools/maak-thema.sh` er bij het bouwen bij, uit `images/` en `favicon.svg`; enkel
`harveys.zip` bevat ze.

### Testen

Lokaal testen zonder hosting kan met WordPress Playground, in elke versie van PHP en
WordPress, met de meldingen van PHP in een logbestand:

```sh
sh tools/maak-thema.sh
mkdir -p /tmp/wp-log
npx @wp-playground/cli@latest server --login --php=7.4 --wp=6.4 \
  --define-bool WP_DEBUG true --define-bool WP_DEBUG_DISPLAY false \
  --define WP_DEBUG_LOG /log/debug.log --mount=/tmp/wp-log:/log \
  --mount=$PWD/dist/harveys:/wordpress/wp-content/themes/harveys
```

Activeer daarna het thema bij Weergave → Thema's (of upload de zip daar, om ook de
installatie te testen). Alles wat je daar doet, is weg als je de server stopt; de
meldingen staan in `/tmp/wp-log/debug.log`. Kies de versies met `--php` (7.4 tot
8.5) en `--wp` (bv. `6.4` of `latest`). Let op: met `--blueprint` gelden die twee
niet; zet de versies dan in de blueprint zelf (`"preferredVersions": { "php": "7.4",
"wp": "6.4" }`). De nieuwste versie van de CLI vraagt Node 24.18 of nieuwer; met een
oudere Node werkt `@wp-playground/cli@3.1.58`.

Elk PHP-bestand nakijken op fouten (`php -l`), in een bepaalde versie van PHP:

```sh
for f in $(cd wordpress/harveys && find . -name '*.php'); do
  npx @wp-playground/cli@latest php --php=7.4 --wordpress-install-mode=do-not-attempt-installing \
    --skip-sqlite-setup --verbosity=quiet --mount=$PWD/wordpress/harveys:/thema -- -l /thema/$f
done
```
