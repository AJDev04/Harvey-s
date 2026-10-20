<?php
/**
 * De instellingen van de site: welke velden er zijn (per tabblad), ze ophalen en
 * ze bewaren. Alles staat samen in één optie, 'harveys'.
 *
 * Veldtypes: tekst, tekstvak, url, getal, vinkje, kleur, pagina, afbeelding,
 * svg, galerij (lijst van foto's) en uren (zeven dagen met van/tot).
 */

defined( 'ABSPATH' ) || exit;

const HARVEYS_OPTIE = 'harveys';

/** De kleur van de huisstijl als er geen andere gekozen is (--oranje in tokens.css). */
const HARVEYS_KLEUR = '#e04b00';

/** De dagen van de week, maandag eerst (zo staan de uren ook bewaard). */
function harveys_dagen() {
	return array( 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag' );
}

/**
 * Alle tabbladen met hun velden. 'recht' is de bevoegdheid die nodig is om het
 * tabblad te zien en te bewaren.
 */
function harveys_tabbladen() {
	$winkel = function ( $n ) {
		return array(
			"winkel{$n}_naam"     => array( 'type' => 'tekst', 'label' => 'Naam', 'hulp' => $n === 2 ? 'Leeg laten als er maar één winkel is.' : '' ),
			"winkel{$n}_kort"     => array( 'type' => 'tekst', 'label' => 'Korte naam', 'hulp' => 'Bij "Of bel:" in de sectie Cadeaubon, bv. Women.' ),
			"winkel{$n}_straat"   => array( 'type' => 'tekst', 'label' => 'Straat en nummer', 'hulp' => 'Het huisnummer verschijnt ook klein naast de naam.' ),
			"winkel{$n}_gemeente" => array( 'type' => 'tekst', 'label' => 'Postcode en gemeente' ),
			"winkel{$n}_telefoon" => array( 'type' => 'tekst', 'label' => 'Telefoon', 'hulp' => 'Zoals het op de site moet staan, bv. 053 80 92 84.' ),
			"winkel{$n}_foto"     => array( 'type' => 'afbeelding', 'label' => 'Foto van de gevel' ),
		);
	};

	return array(
		'algemeen'    => array(
			'titel'  => 'Algemeen',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'De naam en de slogan van de site (voor zoekmachines en schermlezers) staan bij <a href="' . esc_url( admin_url( 'options-general.php' ) ) . '">Instellingen → Algemeen</a> als Sitetitel en Ondertitel. Het logo, het icoontje in het browsertabblad en de kleur stel je in bij <a href="' . esc_url( admin_url( 'admin.php?page=harveys-logo' ) ) . '">Logo en kleur</a>.',
			'velden' => array(
				'beschrijving'      => array( 'type' => 'tekstvak', 'label' => 'Beschrijving voor zoekmachines', 'hulp' => 'Eén of twee zinnen; Google toont ze onder de titel.' ),
				'adres_kort'        => array( 'type' => 'tekst', 'label' => 'Adres, kort', 'hulp' => 'In het mobiele menu, bv. Kattestraat 38 & 49.' ),
				'gemeente'          => array( 'type' => 'tekst', 'label' => 'Postcode en gemeente', 'hulp' => 'Onder het korte adres in het mobiele menu.' ),
				'footer_regel'      => array( 'type' => 'tekst', 'label' => 'Regel onderaan de footer' ),
				'btw'               => array( 'type' => 'tekst', 'label' => 'Btw-nummer', 'hulp' => 'Zoals het er moet staan, bv. BTW BE 0455.889.607.' ),
				'disclaimer_pagina' => array( 'type' => 'pagina', 'label' => 'Pagina Disclaimer', 'hulp' => 'De pagina Privacy kies je bij Instellingen → Privacy.' ),
			),
		),
		'logo'        => array(
			'titel'  => 'Logo en kleur',
			'recht'  => 'edit_theme_options',
			'uitleg' => 'Upload de naam en de slogan als twee aparte SVG-bestanden, met de tekst omgezet naar contouren (paden). De kleur van het bestand doet er niet toe: het logo neemt overal de kleur van de omgeving aan. Is een letter een apart pad, dan komen de letters in de hero één voor één op. Zonder logo staat de naam van de site (Sitetitel) er in letters.',
			'velden' => array(
				'logo_naam'      => array( 'type' => 'svg', 'label' => 'Naam (SVG)', 'hulp' => 'Bovenaan in de header, in de hero, op de cadeaubon en in de footer.' ),
				'logo_slogan'    => array( 'type' => 'svg', 'label' => 'Slogan (SVG)', 'hulp' => 'Onder de naam in de hero en de footer, in het mobiele menu en op de cadeaubon. Mag leeg blijven.' ),
				'slogan_breedte' => array( 'type' => 'getal', 'label' => 'Breedte van de slogan', 'na' => '% van de naam', 'stap' => '0.1', 'standaard' => 57.4 ),
				'slogan_afstand' => array( 'type' => 'getal', 'label' => 'Ruimte tussen naam en slogan', 'na' => '% van de hoogte van de naam', 'stap' => '0.1', 'standaard' => 79.5 ),
				'favicon'        => array( 'type' => 'svg', 'label' => 'Icoontje (SVG)', 'hulp' => 'In het browsertabblad, met zijn eigen kleuren; best vierkant. Een PNG kan ook, via Site-pictogram in de Customizer: dat gaat voor.' ),
				'kleur'          => array( 'type' => 'kleur', 'label' => 'Kleur', 'standaard' => HARVEYS_KLEUR, 'hulp' => 'Het vlak van de hero, de merken en de nieuwsbrief, en de accenten. De tekst erop is zwart: kies een heldere kleur.' ),
			),
		),
		'hero'        => array(
			'titel'  => 'Hero en band',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'De hero is het oranje vlak met het logo bovenaan de pagina; bij het scrollen opent er een video. De band is de grote schuivende tekst eronder.',
			'velden' => array(
				'hero_video'  => array( 'type' => 'url', 'label' => 'Video', 'hulp' => 'Een link naar een .mp4-bestand (bv. uit de mediabibliotheek) of een HLS-stream (.m3u8).' ),
				'hero_poster' => array( 'type' => 'afbeelding', 'label' => 'Stilstaand beeld', 'hulp' => 'Te zien zolang de video laadt, en voor wie minder beweging wil.' ),
				'hero_links'  => array( 'type' => 'tekst', 'label' => 'Tekst links onderaan' ),
				'hero_rechts' => array( 'type' => 'tekst', 'label' => 'Tekst rechts onderaan' ),
				'band_1'      => array( 'type' => 'tekstvak', 'label' => 'Band, eerste rij', 'hulp' => 'Eén stuk per regel; er komt een ster tussen.' ),
				'band_2'      => array( 'type' => 'tekstvak', 'label' => 'Band, tweede rij (omlijnd)', 'hulp' => 'Eén stuk per regel.' ),
			),
		),
		'collectie'   => array(
			'titel'  => 'Collectie',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'Kies of upload foto\'s en sleep ze in de juiste volgorde. Typ onder elke foto het merk; dat staat bij de vergrote foto. Een groep zonder foto\'s verdwijnt van de site.',
			'velden' => array(
				'collectie_intro'      => array( 'type' => 'tekstvak', 'label' => 'Inleiding' ),
				'collectie_haar_titel' => array( 'type' => 'tekst', 'label' => 'Naam van de eerste groep', 'standaard' => 'Voor haar', 'hulp' => 'Op het tabblad boven de foto\'s.' ),
				'collectie_haar'       => array( 'type' => 'galerij', 'label' => 'Foto\'s van de eerste groep' ),
				'collectie_hem_titel'  => array( 'type' => 'tekst', 'label' => 'Naam van de tweede groep', 'standaard' => 'Voor hem' ),
				'collectie_hem'        => array( 'type' => 'galerij', 'label' => 'Foto\'s van de tweede groep' ),
			),
		),
		'secties'     => array(
			'titel'  => 'Nieuws en merken',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'Nieuwsberichten schrijf je bij <a href="' . esc_url( admin_url( 'edit.php' ) ) . '">Nieuws</a>, merken voeg je toe bij <a href="' . esc_url( admin_url( 'edit.php?post_type=harveys_merk' ) ) . '">Merken</a>.',
			'velden' => array(
				'nieuws_aantal' => array( 'type' => 'getal', 'label' => 'Aantal nieuwsberichten op de homepage', 'hulp' => 'De nieuwste eerst. Bij meer dan één bericht staan ze in een carrousel waarin de bezoeker verder klikt.', 'stap' => '1', 'min' => 1, 'max' => 10, 'standaard' => 5 ),
				'merken_intro'  => array( 'type' => 'tekstvak', 'label' => 'Inleiding bij Merken' ),
			),
		),
		'over'        => array(
			'titel'  => 'Over ons',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'In de tekst maakt een woord tussen sterretjes het schuin: *begrip*. De uitspraak staat altijd recht.',
			'velden' => array(
				'over_titel'     => array( 'type' => 'tekst', 'label' => 'Titel' ),
				'over_statement' => array( 'type' => 'tekstvak', 'label' => 'Uitspraak', 'hulp' => 'De grote zin bovenaan.' ),
				'over_tekst'     => array( 'type' => 'tekstvak', 'label' => 'Tekst', 'hulp' => 'Laat een lege regel tussen twee alinea\'s.', 'rijen' => 10 ),
				'over_fotos'     => array( 'type' => 'galerij', 'label' => 'Foto\'s van de winkel', 'bijschrift' => 'Onderschrift (enkel voor schermlezers)' ),
			),
		),
		'cadeaubon'   => array(
			'titel'  => 'Cadeaubon',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'Zet een woord tussen sterretjes om het schuin en oranje te maken: *zelf*. Bij de lijsten: één regel per rij, met het label en de tekst gescheiden door een verticale streep: Geldigheid | 1 jaar.',
			'velden' => array(
				'cadeaubon_tonen'  => array( 'type' => 'vinkje', 'label' => 'Sectie tonen' ),
				'cadeaubon_lead'   => array( 'type' => 'tekstvak', 'label' => 'Inleiding' ),
				'cadeaubon_feiten' => array( 'type' => 'tekstvak', 'label' => 'Gegevens', 'hulp' => 'Label | Tekst, één per regel.' ),
				'cadeaubon_knop'   => array( 'type' => 'tekst', 'label' => 'Tekst op de knop', 'hulp' => 'De knop springt naar Contact.' ),
				'cadeaubon_klein'  => array( 'type' => 'tekstvak', 'label' => 'Kleine lettertjes', 'hulp' => 'Label | Tekst, één per regel; bv. de regels voor een tegoedbon.' ),
				'bon_merken'       => array( 'type' => 'tekst', 'label' => 'Op de bon: regel onder de naam', 'hulp' => 'Bv. Men – Women.' ),
				'bon_geldig'       => array( 'type' => 'tekst', 'label' => 'Op de bon: geldigheid', 'hulp' => 'Bv. 1 jaar geldig.' ),
			),
		),
		'winkels'     => array(
			'titel'  => 'Winkels en uren',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'De openingsuren staan in woorden op de site, en bepalen ook het label "Nu open" of "Gesloten".',
			'groepen' => array(
				'Contact'       => array(
					'contact_intro' => array( 'type' => 'tekstvak', 'label' => 'Inleiding bij Contact' ),
				),
				'Eerste winkel' => $winkel( 1 ),
				'Tweede winkel' => $winkel( 2 ),
				'Openingsuren'  => array(
					'uren'             => array( 'type' => 'uren', 'label' => 'Uren', 'hulp' => 'Laat beide vakjes leeg op een dag dat de winkels dicht zijn.' ),
					'feestdagen_dicht' => array( 'type' => 'vinkje', 'label' => 'Gesloten op Belgische feestdagen' ),
					'uren_extra'       => array( 'type' => 'tekst', 'label' => 'Extra regel', 'hulp' => 'Bv. een uitzonderlijke koopzondag. Mag leeg blijven.' ),
				),
			),
		),
		'nieuwsbrief' => array(
			'titel'  => 'Nieuwsbrief',
			'recht'  => 'edit_others_posts',
			'uitleg' => 'Wie zich inschrijft, komt in de lijst <a href="' . esc_url( admin_url( 'edit.php?post_type=harveys_inschrijving' ) ) . '">Inschrijvingen</a>; daar kan je ze ook exporteren voor je mailprogramma.',
			'velden' => array(
				'nieuwsbrief_tonen' => array( 'type' => 'vinkje', 'label' => 'Sectie tonen' ),
				'nieuwsbrief_titel' => array( 'type' => 'tekst', 'label' => 'Titel' ),
				'nieuwsbrief_tekst' => array( 'type' => 'tekstvak', 'label' => 'Tekst' ),
			),
		),
	);
}

/** De velden van één tabblad, ook als ze in groepen staan. */
function harveys_velden_van( $tab ) {
	if ( isset( $tab['velden'] ) ) {
		return $tab['velden'];
	}
	$velden = array();
	foreach ( $tab['groepen'] as $groep ) {
		$velden += $groep;
	}
	return $velden;
}

/** Alle velden van alle tabbladen. */
function harveys_alle_velden() {
	static $velden = null;
	if ( null === $velden ) {
		$velden = array();
		foreach ( harveys_tabbladen() as $tab ) {
			$velden += harveys_velden_van( $tab );
		}
	}
	return $velden;
}

/** Eén instelling; leeg (of de standaard van het veld) als ze niet ingevuld is. */
function harveys_optie( $sleutel ) {
	$waarden = get_option( HARVEYS_OPTIE, array() );
	if ( is_array( $waarden ) && isset( $waarden[ $sleutel ] ) && '' !== $waarden[ $sleutel ] ) {
		return $waarden[ $sleutel ];
	}
	$velden = harveys_alle_velden();
	if ( isset( $velden[ $sleutel ]['standaard'] ) ) {
		return $velden[ $sleutel ]['standaard'];
	}
	if ( isset( $velden[ $sleutel ] ) && in_array( $velden[ $sleutel ]['type'], array( 'galerij', 'uren' ), true ) ) {
		return array();
	}
	return '';
}

/**
 * Bewaart het tabblad dat net verstuurd is. De andere tabbladen blijven zoals ze
 * waren: alles zit in één optie.
 */
function harveys_bewaar( $invoer ) {
	$oud = get_option( HARVEYS_OPTIE, array() );
	$oud = is_array( $oud ) ? $oud : array();

	// update_option zonder formulier (bv. de import) geeft gewoon de nieuwe waarden
	if ( ! is_array( $invoer ) || empty( $invoer['_tab'] ) ) {
		return is_array( $invoer ) ? $invoer : $oud;
	}

	$tabs = harveys_tabbladen();
	$tab  = sanitize_key( $invoer['_tab'] );
	if ( ! isset( $tabs[ $tab ] ) || ! current_user_can( $tabs[ $tab ]['recht'] ) ) {
		return $oud;
	}

	foreach ( harveys_velden_van( $tabs[ $tab ] ) as $sleutel => $veld ) {
		$waarde          = isset( $invoer[ $sleutel ] ) ? wp_unslash( $invoer[ $sleutel ] ) : null;
		$oud[ $sleutel ] = harveys_schoon_veld( $veld, $waarde );
	}

	// onderschriften van foto's in een galerij: die horen bij de foto zelf
	if ( ! empty( $_POST['harveys_bijschrift'] ) && is_array( $_POST['harveys_bijschrift'] ) ) {
		foreach ( wp_unslash( $_POST['harveys_bijschrift'] ) as $id => $tekst ) {
			$id = absint( $id );
			if ( $id && current_user_can( 'edit_post', $id ) ) {
				$tekst = sanitize_text_field( $tekst );
				if ( wp_get_attachment_caption( $id ) !== $tekst ) {
					wp_update_post( array( 'ID' => $id, 'post_excerpt' => $tekst ) );
				}
			}
		}
	}

	return $oud;
}

/** Schoont één waarde op volgens het type van het veld. */
function harveys_schoon_veld( $veld, $waarde ) {
	switch ( $veld['type'] ) {
		case 'vinkje':
			return ! empty( $waarde );
		case 'tekstvak':
			return sanitize_textarea_field( (string) $waarde );
		case 'url':
			return esc_url_raw( trim( (string) $waarde ) );
		case 'kleur':
			$kleur = sanitize_hex_color( trim( (string) $waarde ) );
			return $kleur ? strtolower( $kleur ) : '';
		case 'getal':
			if ( '' === trim( (string) $waarde ) ) {
				return '';
			}
			$getal = (float) str_replace( ',', '.', (string) $waarde );
			if ( isset( $veld['min'] ) ) {
				$getal = max( $veld['min'], $getal );
			}
			if ( isset( $veld['max'] ) ) {
				$getal = min( $veld['max'], $getal );
			}
			return $getal;
		case 'pagina':
		case 'afbeelding':
		case 'svg':
			return absint( $waarde ) ?: '';
		case 'galerij':
			return array_values( array_filter( array_map( 'absint', explode( ',', (string) $waarde ) ) ) );
		case 'uren':
			$uren = array();
			for ( $d = 0; $d < 7; $d++ ) {
				$van      = isset( $waarde[ $d ]['van'] ) ? harveys_schoon_tijd( $waarde[ $d ]['van'] ) : '';
				$tot      = isset( $waarde[ $d ]['tot'] ) ? harveys_schoon_tijd( $waarde[ $d ]['tot'] ) : '';
				$uren[]   = ( $van && $tot && $van < $tot ) ? array( $van, $tot ) : array( '', '' );
			}
			return $uren;
		default:
			return sanitize_text_field( (string) $waarde );
	}
}

/** "9:5", "09.30" of "9:30" wordt "09:30"; iets anders wordt leeg. */
function harveys_schoon_tijd( $tijd ) {
	if ( ! preg_match( '/^\s*(\d{1,2})[:.h](\d{2})\s*$/', (string) $tijd, $m ) ) {
		return '';
	}
	$uur = (int) $m[1];
	$min = (int) $m[2];
	return ( $uur < 24 && $min < 60 ) ? sprintf( '%02d:%02d', $uur, $min ) : '';
}

add_action(
	'init',
	function () {
		register_setting(
			HARVEYS_OPTIE,
			HARVEYS_OPTIE,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'harveys_bewaar',
				'default'           => array(),
			)
		);
	}
);

// Ook redacteurs (niet enkel beheerders) mogen de inhoud aanpassen; per tabblad
// controleert harveys_bewaar() het juiste recht.
add_filter(
	'option_page_capability_' . HARVEYS_OPTIE,
	function () {
		return 'edit_others_posts';
	}
);
