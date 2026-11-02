<?php
/**
 * Startinhoud: de inhoud van de oorspronkelijke site van Harvey's, om in één keer
 * te importeren (Website → Startinhoud). De teksten komen van www.harveys.be; de
 * foto's en het logo staan in de map starter/ van het thema.
 */

defined( 'ABSPATH' ) || exit;

return [
    'instellingen' => [
        'beschrijving' => 'Harvey’s is méér dan zomaar een winkel, het is een begrip in de regio. U vindt er stijlvolle mode in een sfeervol en hedendaags kader.',
        'adres_kort' => 'Kattestraat 38 & 49',
        'gemeente' => '9300 Aalst',
        'footer_regel' => 'Harvey’s Women & Harvey’s Men, Kattestraat, 9300 Aalst',
        'btw' => 'BTW BE 0455.889.607',
        'slogan_breedte' => 57.4,
        'slogan_afstand' => 79.5,
        'kleur' => '#e04b00',
        'hero_video' => 'https://customer-el0steweaibtzxvs.cloudflarestream.com/419ec1ab9adf4b9a2218a81a97b8692d/manifest/video.m3u8',
        'hero_links' => 'High level quality brands.',
        'hero_rechts' => 'Kattestraat 38 & 49 · 9300 Aalst',
        'band_1' => 'Casual Luxury
Harvey’s Women
Harvey’s Men',
        'band_2' => 'High level quality brands
Kattestraat Aalst',
        'nieuws_aantal' => 5,
        'collectie_intro' => '',
        'collectie_haar_titel' => 'Voor haar',
        'collectie_hem_titel' => 'Voor hem',
        'merken_intro' => '',
        'over_titel' => 'Over Harvey’s',
        'over_statement' => 'Harvey’s is méér dan zomaar een winkel, het is een begrip in de regio.',
        'over_tekst' => 'U vindt er stijlvolle mode in een sfeervol en hedendaags kader. Bij Harvey’s is het bovenal aangenaam en ontspannen shoppen in een familiale sfeer.

Door onze persoonlijke service maakt u, als klant, snel kennis met het uitgebreide merkenpakket. Wekelijks wordt onze collectie uitgebreid met de nieuwste mode.

Hippe merken gaan bij Harvey’s hand in hand met de meer klassiekere merken. We hebben een groot aanbod vrijetijds- en stadskleding voor zowel jong als oud die u bovendien kan aanvullen met ons ruim aanbod aan accessoires (schoenen, juwelen, handtassen, ceinturen, etc.).

Bij ons komen een goede service, kwaliteit en een professioneel advies op de eerste plaats, originaliteit staat bij ons centraal! Bij iedere aankoop ontvangt u een waardebon.',
        'cadeaubon_tonen' => true,
        'cadeaubon_lead' => '',
        'cadeaubon_feiten' => 'Geldig in | Harvey’s Women en Harvey’s Men
Geldigheid | 1 jaar',
        'cadeaubon_knop' => 'Kom langs',
        'cadeaubon_klein' => 'Tegoedbon | Geldig tot en met de datum op de bon, voor een aankoop in de winkel die erop staat. Niet geldig tijdens de solden.',
        'bon_merken' => 'Men – Women',
        'bon_geldig' => '1 jaar geldig',
        'contact_intro' => '',
        'winkel1_naam' => 'Harvey’s Women',
        'winkel1_kort' => 'Women',
        'winkel1_straat' => 'Kattestraat 38',
        'winkel1_gemeente' => '9300 Aalst',
        'winkel1_telefoon' => '053 80 92 84',
        'winkel2_naam' => 'Harvey’s Men',
        'winkel2_kort' => 'Men',
        'winkel2_straat' => 'Kattestraat 49',
        'winkel2_gemeente' => '9300 Aalst',
        'winkel2_telefoon' => '053 70 14 55',
        'uren' => [
            ['10:00', '18:00'],
            ['10:00', '18:00'],
            ['10:00', '18:00'],
            ['10:00', '18:00'],
            ['10:00', '18:00'],
            ['10:00', '18:00'],
            ['', ''],
        ],
        'feestdagen_dicht' => true,
        'uren_extra' => '',
        'nieuwsbrief_tonen' => true,
        'nieuwsbrief_titel' => 'Nieuwsbrief',
        'nieuwsbrief_tekst' => 'Schrijf u in op onze nieuwsbrief en ontvang exclusieve aanbiedingen.',
    ],
    'sitenaam' => 'Harvey’s',
    'ondertitel' => 'Casual Luxury',
    // het logo: enkel als er nog geen eigen logo gekozen is
    'logo' => [
        'logo_naam' => ['logo/naam.svg', 'Logo Harvey’s: naam'],
        'logo_slogan' => ['logo/slogan.svg', 'Logo Harvey’s: slogan'],
        'favicon' => ['logo/favicon.svg', 'Icoontje Harvey’s'],
    ],
    'poster' => 'hero-poster.jpg',
    'winkelfotos' => [
        ['winkel-women.jpg', 'Gevel van Harvey’s Women in de Kattestraat'],
        ['winkel-men.jpg', 'Gevel van Harvey’s Men in de Kattestraat'],
    ],
    'nieuws' => [
        'titel' => 'Ontdek de nieuwe collectie schoenen van Nathan-Baume',
        'datum' => '2026-03-15 10:00:00',
        // op harveys.be staat bij dit bericht enkel de titel, de datum en de foto
        'tekst' => '',
        'foto' => 'collectie-schoenen.jpg',
        'alt' => 'Crèmekleurige loafers van Nathan-Baume met gouden gesp, gedragen met witte sokken',
    ],
    'collectie' => [
        'haar' => [
            ['collectie/haar-prl-donsjack-khaki.jpg', 'Polo Ralph Lauren', 'Vrouw in een glanzend kakigroen donsjack met kap (kleur New olive), over een bruine broek'],
            ['collectie/haar-nosecrets-cardigan-macaron.jpg', 'No Secrets', 'Vrouw in een roze cardigan in de kleur macaron, met kraag en manchetten in imitatiebont'],
            ['collectie/haar-7fam-lotta-corduroy.jpg', '7 For All Mankind', 'Vrouw in een wijde donkerbruine corduroy broek en een zwart vest met een rode riem'],
            ['collectie/haar-senso-neela-brown.jpg', 'Senso', 'Vrouw in de bruine trui Neela met geborduurde bloemen en ballonmouwen'],
            ['collectie/haar-reset-felice-coffee-bean.jpg', 'Reset', 'Vrouw in de lange donsjas Felice in coffee bean, met kap en schuine doorstiksels'],
            ['collectie/haar-cambio-1.jpg', 'Cambio', 'Beige corduroy broek met wijde pijpen, gedragen met een witte top en beige enkellaarsjes'],
            ['collectie/haar-polo-ralph-lauren-1.jpg', 'Polo Ralph Lauren', 'Vrouw in een grijs kabelgebreid jack met drukknopen over een wit hemd, met een grijze broek en een zwarte handtas'],
            ['collectie/haar-cambio-3.jpg', 'Cambio', 'Antracietgrijze pantalon met rechte pijpen, gedragen met zilveren enkellaarsjes'],
        ],
        'hem' => [
            ['collectie/hem-zwarte-denim.jpg', '', 'Man met zonnebril in een zwart denim jasje en grijze jeans, de armen gekruist tegen een betonnen muur'],
            ['collectie/hem-kostuum.jpg', '', 'Man in een donkerblauw kostuum, van achteren gezien'],
            ['collectie/hem-marine-jas.jpg', '', 'Man in een donkerblauwe jas en een blauw hemd, met een beige chino en witte sneakers'],
            ['collectie/hem-veldjas.jpg', '', 'Man in een beige veldjas over een donkere trui, in een straat met oude gevels'],
            ['collectie/hem-bodywarmer.jpg', '', 'Man met zonnebril in een donkerblauwe bodywarmer over een crèmekleurige polo, tegen een houten deur'],
        ],
    ],
    'merken' => [
        [
            'Liu Jo',
            'merken/liu-jo.svg',
            ['haar'],
        ],
        [
            'Guess',
            'merken/guess.svg',
            ['haar'],
        ],
        [
            'Cambio',
            'merken/cambio.png',
            ['haar'],
        ],
        [
            'No Secrets',
            null,
            ['haar'],
        ],
        [
            'Rino & Pelle',
            'merken/rino-pelle.png',
            ['haar'],
        ],
        [
            'Senso',
            null,
            ['haar'],
        ],
        [
            '7 For All Mankind',
            'merken/forallmankind.png',
            ['haar', 'hem'],
        ],
        [
            'Reset',
            null,
            ['haar', 'hem'],
        ],
        [
            'Polo Ralph Lauren',
            'merken/ralph-lauren.svg',
            ['haar', 'hem'],
        ],
        [
            'Nathan-Baume',
            null,
            ['haar'],
        ],
        [
            'Barbour',
            'merken/barbour.svg',
            ['hem'],
        ],
        [
            'Antwrp',
            'merken/antwrp.svg',
            ['hem'],
        ],
        [
            'Façonnable',
            'merken/faconnable.png',
            ['hem'],
        ],
        [
            'Hackett',
            'merken/hackett.svg',
            ['hem'],
        ],
        [
            'Mason’s',
            'merken/masons.png',
            ['hem'],
        ],
        [
            'Profuomo',
            'merken/profuomo.svg',
            ['hem'],
        ],
        [
            'Lyle & Scott',
            'merken/lyle-scott.svg',
            ['hem'],
        ],
        [
            'Digel',
            'merken/digel.svg',
            ['hem'],
        ],
        [
            'Zilton',
            'merken/zilton.svg',
            ['hem'],
        ],
        [
            'McAlson',
            'merken/mcalson.svg',
            ['hem'],
        ],
        [
            'Fourten',
            'merken/fourten.png',
            ['hem'],
        ],
    ],
    'over_fotos' => [
        ['winkel/women-1.jpg', 'Wand met gestapelde truien en hemden in kleur, rek met jeans en een paspop', 'Harvey’s Women'],
        ['winkel/men-1.jpg', 'Wand met gestapelde hemden en truien in blauwtinten, rek met hemden', 'Harvey’s Men'],
        ['winkel/women-2.jpg', 'Wand met zwarte kleding en handtassen, rek met blouses en een paspop', 'Harvey’s Women'],
        ['winkel/men-2.jpg', 'Rekken met boxershorts, sokken, handschoenen en mutsen', 'Harvey’s Men'],
        ['winkel/men-3.jpg', 'Blazers en gestapelde hemden', 'Harvey’s Men'],
    ],
    'paginas' => [
        [
            'titel' => 'Privacy',
            'inhoud' => '<p>De verantwoordelijke voor de verwerking, DEVLO NV, Kattestraat 49 te 9300 Aalst respecteert de Belgische wet van 8 december 1992 met betrekking tot de bescherming van het privéleven in de verwerking van de persoonlijke gegevens.</p>

<p>De door u meegedeelde persoonsgegevens zullen enkel gebruikt worden om uw registratie als gebruiker en de aankoop van producten te beheren, te antwoorden op uw vragen en, desgewenst, u onze gepersonaliseerde mededelingen te sturen.</p>

<p>U beschikt over een wettelijk recht op inzage en eventuele correctie van uw persoonsgegevens. Mits bewijs van identiteit (kopie identiteitskaart) kunt u via een schriftelijke, gedateerde en ondertekende aanvraag aan DEVLO NV, of <a href="mailto:devlo@skynet.be">devlo@skynet.be</a> gratis de schriftelijke mededeling bekomen van uw persoonsgegevens. Indien nodig kunt u ook vragen de gegevens te corrigeren die onjuist, niet volledig of niet pertinent zouden zijn.</p>

<p>In geval van gebruik van gegevens voor direct marketing: U kan zich kosteloos verzetten tegen het gebruik van uw gegevens voor direct marketing. Hiertoe kan U zich steeds richten tot DEVLO NV of <a href="mailto:devlo@skynet.be">devlo@skynet.be</a></p>

<p>Wij behandelen uw gegevens als vertrouwelijke informatie en zullen die niet doorgeven, verhuren of verkopen aan derden.</p>

<p>DEVLO NV houdt online (anonieme) bezoekersstatistieken bij om te kunnen bekijken welke pagina’s van de internetsite in welke mate bezocht worden.</p>

<p>Indien u vragen heeft over deze privacy statement, kunt u ons contacteren via <a href="mailto:devlo@skynet.be">devlo@skynet.be</a> of telefonisch op de telefoonnummers zoals vermeld op de contactpagina.</p>',
            'privacy' => true,
        ],
        [
            'titel' => 'Disclaimer',
            'inhoud' => '<p>De volledige inhoud van deze website is in het bezit van of onder controle van Devlo NV, en wordt beschermd door de universele auteursrechten. U mag de inhoud enkel downloaden voor persoonlijk gebruik en niet-commerciële doeleinden, maar het is verboden de inhoud te wijzigen of te verveelvoudigen. De inhoud mag anders hoe dan ook niet worden gekopieerd of gebruikt.</p>

<p>Devlo N.V. zal zich inspannen om de informatie op deze website bijgewerkt en nauwkeurig te houden. Devlo N.V. kan in geen enkel geval aansprakelijk zijn voor schade of problemen aan uw systeem die het resultaat zijn van uw toegang tot, of uw onvermogen tot toegang tot, deze website.</p>

<p>Deze website geeft ook links of referenties naar andere sites, maar Devlo N.V. is niet verantwoordelijk voor de inhoud van die andere sites en is niet aansprakelijk voor enige schade die uit deze inhoud resulteert. Elke link naar andere sites is louter geplaatst voor het comfort van de gebruikers van deze website.</p>

<p>De handelsmerken, dienstenmerken, handelsnamen, omkleding en producten op deze website zijn internationaal beschermd. Ze mogen niet worden gebruikt zonder voorafgaande schriftelijke toelating van Devlo N.V., haar partners of de rechtmatige eigenaars van deze rechten, behalve om de producten of diensten te identificeren.</p>

<p>Devlo N.V. is vrij om de inhoud van alle berichten die u naar deze site of per email naar ons verstuurt, met inbegrip van ideeën, concepten of suggesties, te gebruiken voor om het even welk doel ter verrijking van Devlo N.V.. Dergelijke voorstellen kunnen bestaan in het ontwikkelen, vervaardigen en/of verkopen van goederen of diensten.</p>

<p>Deze website wordt verschaft als een dienst aan de bezoekers ervan. Devlo N.V. behoudt zich het recht voor om, te allen tijde en voor om het even welke reden, de inhoud van deze site te verwijderen, te wijzigen of aan te vullen zonder kennisgeving aan om het even wie.</p>',
            'privacy' => false,
        ],
    ],
];
