<?php
/**
 * Kleine helpers voor de sjablonen: tekst opmaken, winkels, openingsuren, en
 * welke secties er op de homepage staan.
 */

defined( 'ABSPATH' ) || exit;

/** Tekst veilig tonen, met *woord* als <em>woord</em> (schuin; in de statement blijft het recht). */
function harveys_nadruk( $tekst ) {
	return preg_replace( '/\*([^*\n]+)\*/', '<em>$1</em>', esc_html( $tekst ) );
}

/** Alinea's: een lege regel begint een nieuwe alinea. */
function harveys_alineas( $tekst ) {
	$uit = '';
	foreach ( preg_split( '/\n\s*\n/', trim( (string) $tekst ) ) as $alinea ) {
		if ( '' !== trim( $alinea ) ) {
			$uit .= '<p>' . nl2br( harveys_nadruk( trim( $alinea ) ), false ) . '</p>';
		}
	}
	return $uit;
}

/** De niet-lege regels van een tekstvak. */
function harveys_regels( $tekst ) {
	return array_values( array_filter( array_map( 'trim', explode( "\n", (string) $tekst ) ), 'strlen' ) );
}

/** "Label | Tekst" per regel, als lijst van [label, tekst]. */
function harveys_paren( $tekst ) {
	$paren = array();
	foreach ( harveys_regels( $tekst ) as $regel ) {
		$delen   = array_map( 'trim', explode( '|', $regel, 2 ) );
		$paren[] = array( $delen[0], isset( $delen[1] ) ? $delen[1] : '' );
	}
	return $paren;
}

/** 053 80 92 84 wordt tel:+3253809284 (een Belgisch nummer zonder landcode krijgt +32). */
function harveys_tel( $nummer ) {
	$cijfers = preg_replace( '/[^\d+]/', '', (string) $nummer );
	if ( 0 === strpos( $cijfers, '00' ) ) {
		$cijfers = '+' . substr( $cijfers, 2 );
	} elseif ( 0 === strpos( $cijfers, '0' ) ) {
		$cijfers = '+32' . substr( $cijfers, 1 );
	}
	return 'tel:' . $cijfers;
}

/** Een foto uit de mediabibliotheek, of niets. */
function harveys_beeld( $id, $grootte = 'large', $attr = array() ) {
	return $id ? wp_get_attachment_image( (int) $id, $grootte, false, $attr ) : '';
}

/**
 * De ingevulde winkels: naam, korte naam, straat, gemeente, telefoon, foto, en
 * apart het huisnummer en de straatnaam.
 */
function harveys_winkels() {
	$winkels = array();
	foreach ( array( 1, 2 ) as $n ) {
		$naam = harveys_optie( "winkel{$n}_naam" );
		if ( '' === $naam ) {
			continue;
		}
		$straat    = harveys_optie( "winkel{$n}_straat" );
		$winkels[] = array(
			'naam'       => $naam,
			'kort'       => harveys_optie( "winkel{$n}_kort" ) ?: $naam,
			'straat'     => $straat,
			'gemeente'   => harveys_optie( "winkel{$n}_gemeente" ),
			'telefoon'   => harveys_optie( "winkel{$n}_telefoon" ),
			'foto'       => harveys_optie( "winkel{$n}_foto" ),
			'nr'         => preg_match( '/(\d+\s*[a-zA-Z]?)\s*$/', $straat, $m ) ? $m[1] : '',
			'straatnaam' => trim( preg_replace( '/\s*\d+\s*[a-zA-Z]?\s*$/', '', $straat ) ),
		);
	}
	return $winkels;
}

/** Een opsomming: "a", "a en b", "a, b en c". */
function harveys_opsomming( $delen ) {
	$laatste = array_pop( $delen );
	return $delen ? implode( ', ', $delen ) . ' en ' . $laatste : (string) $laatste;
}

/** 09:30 wordt "9.30", 18:00 wordt "18". */
function harveys_tijd( $hhmm ) {
	list( $u, $m ) = array_map( 'intval', explode( ':', $hhmm ) );
	return $m ? $u . '.' . sprintf( '%02d', $m ) : (string) $u;
}

/** De openingsuren in woorden, één regel per groep dagen met dezelfde uren. */
function harveys_uren_regels() {
	$uren = harveys_optie( 'uren' );
	if ( ! is_array( $uren ) || 7 !== count( $uren ) ) {
		return array();
	}
	$dagen  = harveys_dagen();
	$regels = array();
	$dicht  = array();
	$groep  = array();
	$vorige = null;

	$schrijf = function () use ( &$groep, &$regels, $dagen, $uren ) {
		if ( ! $groep ) {
			return;
		}
		$eerste = ucfirst( $dagen[ $groep[0] ] );
		$laatst = $dagen[ end( $groep ) ];
		$wanneer = 1 === count( $groep ) ? $eerste : ( 2 === count( $groep ) ? "$eerste en $laatst" : "$eerste tot en met $laatst" );
		list( $van, $tot ) = $uren[ $groep[0] ];
		$regels[] = $wanneer . ': doorlopend van ' . harveys_tijd( $van ) . ' tot ' . harveys_tijd( $tot ) . ' uur';
		$groep    = array();
	};

	foreach ( $uren as $d => $u ) {
		if ( empty( $u[0] ) || empty( $u[1] ) ) {
			$schrijf();
			$vorige  = null;
			$dicht[] = $dagen[ $d ];
			continue;
		}
		$sleutel = $u[0] . '-' . $u[1];
		if ( $sleutel !== $vorige ) {
			$schrijf();
		}
		$groep[] = $d;
		$vorige  = $sleutel;
	}
	$schrijf();

	if ( ! $regels ) {
		return array();
	}
	if ( harveys_optie( 'feestdagen_dicht' ) ) {
		$dicht[] = 'feestdagen';
	}
	if ( $dicht ) {
		$regels[] = ucfirst( harveys_opsomming( $dicht ) ) . ': gesloten';
	}
	if ( harveys_optie( 'uren_extra' ) ) {
		$regels[] = harveys_optie( 'uren_extra' );
	}
	return $regels;
}

/**
 * De uren voor het label "Nu open" (js/main.js, setupStatus): per dag van de
 * week, zondag eerst zoals in JavaScript, [van, tot] in uren of null.
 */
function harveys_uren_data() {
	$uren = harveys_optie( 'uren' );
	if ( ! is_array( $uren ) || 7 !== count( $uren ) ) {
		return null;
	}
	$uur = function ( $hhmm ) {
		list( $u, $m ) = array_map( 'intval', explode( ':', $hhmm ) );
		return round( $u + $m / 60, 4 );
	};
	$js = array();
	foreach ( array( 6, 0, 1, 2, 3, 4, 5 ) as $d ) {
		$u    = $uren[ $d ];
		$js[] = ( ! empty( $u[0] ) && ! empty( $u[1] ) ) ? array( $uur( $u[0] ), $uur( $u[1] ) ) : null;
	}
	return array_filter( $js ) ? $js : null;
}

/** De regels op de omslag van de cadeaubon: adres, telefoons, website. */
function harveys_bon_adres() {
	$winkels = harveys_winkels();
	$regels  = array();
	$straten = array_unique( wp_list_pluck( $winkels, 'straatnaam' ) );
	if ( 1 === count( $straten ) && count( $winkels ) > 1 ) {
		// zelfde straat: Kattestraat 38/49
		$regels[] = reset( $straten ) . ' ' . implode( '/', wp_list_pluck( $winkels, 'nr' ) );
	} else {
		foreach ( $winkels as $w ) {
			$regels[] = $w['straat'];
		}
	}
	foreach ( array_unique( wp_list_pluck( $winkels, 'gemeente' ) ) as $gemeente ) {
		$regels[] = $gemeente;
	}
	$telefoons = array_filter( wp_list_pluck( $winkels, 'telefoon' ) );
	return array(
		'adres'   => array_filter( array_merge( $regels, $telefoons ) ),
		'website' => preg_replace( '#^https?://#', '', untrailingslashit( home_url() ) ),
	);
}

/** Link naar een sectie op de homepage; op de homepage zelf enkel het anker. */
function harveys_sectie_url( $id ) {
	return is_front_page() ? '#' . $id : home_url( '/#' . $id );
}

/**
 * De secties die op de homepage staan, met hun naam in het menu. Een sectie
 * zonder inhoud (geen nieuws, geen foto's, ...) valt weg, en dus ook haar link.
 */
function harveys_secties() {
	static $secties = null;
	if ( null !== $secties ) {
		return $secties;
	}
	$merken  = wp_count_posts( HARVEYS_MERK );
	$secties = array_filter(
		array(
			'home'      => 'Home',
			'nieuws'    => wp_count_posts( 'post' )->publish > 0 ? 'Nieuws' : '',
			'collectie' => ( harveys_optie( 'collectie_haar' ) || harveys_optie( 'collectie_hem' ) ) ? 'Collectie' : '',
			'merken'    => ( $merken && $merken->publish > 0 ) ? 'Merken' : '',
			'over'      => ( harveys_optie( 'over_titel' ) || harveys_optie( 'over_tekst' ) ) ? 'Over ons' : '',
			'cadeaubon' => harveys_optie( 'cadeaubon_tonen' ) ? 'Cadeaubon' : '',
			'contact'   => harveys_winkels() ? 'Contact' : '',
		)
	);
	return $secties;
}
