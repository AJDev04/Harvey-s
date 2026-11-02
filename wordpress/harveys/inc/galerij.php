<?php
/**
 * Collectie: welke foto's groot zijn, zodat het raster van elke groep vol is.
 *
 * De statische site volgt een vast patroon (css/layout.css): op een laptop
 * (4 kolommen) is elke zevende foto groot (2 × 2), op een gsm (2 kolommen) staat
 * elke vijfde over de volle breedte. Komt het aantal foto's daarmee niet uit, dan
 * blijft er een gat. Hier rekent het thema per groep zelf uit welke foto's groot
 * zijn, apart voor laptop en gsm, met zo weinig mogelijk wijzigingen aan dat
 * patroon. sectie-collectie.php zet het resultaat als klassen op de foto's:
 * galerij__item--groot (2 × 2 vanaf een tablet) en galerij__item--breed (volle
 * breedte op een gsm); de stijl staat in assets/css/wordpress.css.
 *
 * Het raster gebruikt grid-auto-flow: dense, zoals in de CSS: een foto komt op de
 * eerste vrije plek waar ze past, ook als die vóór een eerdere grote foto ligt.
 * harveys_galerij_leg() bootst dat na.
 */

defined( 'ABSPATH' ) || exit;

/**
 * De klassen per foto voor een groep van $n foto's: [index => 'klasse ...'],
 * met de index vanaf 0, zoals de foto's in de groep staan.
 */
function harveys_galerij_klassen( $n ) {
	$n = (int) $n;
	if ( $n < 1 ) {
		return array();
	}
	$groot   = harveys_galerij_groot_laptop( $n );
	$breed   = harveys_galerij_breed_gsm( $n );
	$klassen = array();
	for ( $i = 1; $i <= $n; $i++ ) {
		$klassen[ $i - 1 ] = trim( ( isset( $groot[ $i ] ) ? 'galerij__item--groot' : '' ) . ( isset( $breed[ $i ] ) ? ' galerij__item--breed' : '' ) );
	}
	return $klassen;
}

/**
 * Gsm: elke vijfde foto over de volle breedte (1, 6, 11, ...). Twee kolommen zijn
 * vol als het aantal vakjes even is; is het oneven (bij 2, 4, 7, 9, 12, ... foto's),
 * dan wordt de laatste brede foto gewoon klein. Geeft [nummer => true], vanaf 1.
 */
function harveys_galerij_breed_gsm( $n ) {
	$breed = array();
	for ( $i = 1; $i <= $n; $i += 5 ) {
		$breed[ $i ] = true;
	}
	if ( 1 === ( $n + count( $breed ) ) % 2 ) {
		unset( $breed[ max( array_keys( $breed ) ) ] );
	}
	return $breed;
}

/**
 * Laptop: vertrekt van elke zevende foto groot (1, 8, 15, ...) en zoekt, als het
 * raster dan niet vol is, de kleinste wijziging: zo weinig mogelijk foto's die van
 * grootte wisselen (een grote wordt klein of een kleine groot), liefst klein maken,
 * liefst zonder rij met enkel grote foto's, en zo ver mogelijk achteraan. Met 1 of 3
 * foto's kan vier kolommen nooit vol: dan het kleinste gat. Geeft [nummer => true].
 *
 * Elke 14 foto's van het vaste patroon vullen samen precies 5 rijen. Bij veel foto's
 * blijven die blokken dus staan en zoekt het thema enkel in de rest (15 tot 28
 * foto's); gewisseld wordt enkel bij de grote foto's van het patroon en bij de
 * laatste 13. Zo blijft het rekenwerk klein, en het resultaat is hetzelfde.
 */
function harveys_galerij_groot_laptop( $n ) {
	static $onthouden = array();
	if ( isset( $onthouden[ $n ] ) ) {
		return $onthouden[ $n ];
	}

	$blokken = $n > 28 ? (int) floor( ( $n - 15 ) / 14 ) : 0;
	$rest    = $n - 14 * $blokken;

	$patroon = array();
	for ( $i = 1; $i <= $rest; $i += 7 ) {
		$patroon[ $i ] = true;
	}
	$kandidaten = array_keys( $patroon );
	for ( $i = max( 1, $rest - 12 ); $i <= $rest; $i++ ) {
		$kandidaten[] = $i;
	}
	$kandidaten = array_values( array_unique( $kandidaten ) );
	rsort( $kandidaten );

	$beste = null;
	for ( $k = 0; $k <= 3; $k++ ) {
		foreach ( harveys_galerij_combinaties( $kandidaten, $k ) as $wissel ) {
			$groot = $patroon;
			foreach ( $wissel as $i ) {
				if ( isset( $groot[ $i ] ) ) {
					unset( $groot[ $i ] );
				} else {
					$groot[ $i ] = true;
				}
			}
			$leg = harveys_galerij_leg( $rest, $groot );
			// kleiner is beter: eerst geen gat (anders het kleinste, in zo weinig mogelijk
			// rijen), dan zo weinig mogelijk wijzigingen, liefst geen foto groter maken, ...
			$kost = array(
				$leg['gaten'] / ( $leg['rijen'] * 4 ),
				$leg['gaten'] ? $leg['rijen'] : 0,
				$k,
				count( array_diff_key( $groot, $patroon ) ),
				$leg['zonder_klein'],
			);
			foreach ( $wissel as $i ) {
				$kost[] = -$i; // later in de rij is beter
			}
			if ( null === $beste || harveys_galerij_kleiner( $kost, $beste[0] ) ) {
				$beste = array( $kost, $groot );
			}
		}
		if ( 0 == $beste[0][0] ) { // phpcs:ignore Universal.Operators.StrictComparisons -- 0 of 0.0
			break; // vol met $k wijzigingen: met meer wijzigingen wordt het niet beter
		}
	}

	// de volle blokken vooraan houden het patroon
	$groot = array();
	for ( $i = 1; $i <= 14 * $blokken; $i += 7 ) {
		$groot[ $i ] = true;
	}
	foreach ( array_keys( $beste[1] ) as $i ) {
		$groot[ 14 * $blokken + $i ] = true;
	}
	$onthouden[ $n ] = $groot;
	return $groot;
}

/**
 * Legt $n foto's in vier kolommen, zoals grid-auto-flow: dense: elke foto op de
 * eerste vrije plek, van boven naar onder en van links naar rechts. $groot:
 * [nummer => true] voor de foto's van 2 × 2. Geeft het aantal rijen, de lege
 * vakjes en het aantal rijen zonder kleine foto (daar bepaalt geen 4:5-foto de hoogte).
 */
function harveys_galerij_leg( $n, $groot ) {
	$rijen = array(); // per rij een bitmasker van de bezette kolommen
	$klein = array(); // rijen met een kleine foto
	for ( $i = 1; $i <= $n; $i++ ) {
		$is_groot = isset( $groot[ $i ] );
		$masker   = $is_groot ? 0b11 : 0b1;
		for ( $r = 0; ; $r++ ) {
			$boven = isset( $rijen[ $r ] ) ? $rijen[ $r ] : 0;
			$onder = isset( $rijen[ $r + 1 ] ) ? $rijen[ $r + 1 ] : 0;
			for ( $k = 0; $k <= ( $is_groot ? 2 : 3 ); $k++ ) {
				$m = $masker << $k;
				if ( ( $boven & $m ) || ( $is_groot && ( $onder & $m ) ) ) {
					continue;
				}
				$rijen[ $r ] = $boven | $m;
				if ( $is_groot ) {
					$rijen[ $r + 1 ] = $onder | $m;
				} else {
					$klein[ $r ] = true;
				}
				continue 3;
			}
		}
	}
	$aantal = count( $rijen );
	$gaten  = 0;
	foreach ( $rijen as $rij ) {
		$gaten += 4 - substr_count( decbin( $rij ), '1' );
	}
	return array(
		'rijen'        => $aantal,
		'gaten'        => $gaten,
		'zonder_klein' => $aantal - count( $klein ),
	);
}

/** Alle combinaties van $k elementen uit $lijst, in de volgorde van de lijst. */
function harveys_galerij_combinaties( $lijst, $k ) {
	if ( 0 === $k ) {
		return array( array() );
	}
	$uit = array();
	$n   = count( $lijst );
	for ( $i = 0; $i <= $n - $k; $i++ ) {
		foreach ( harveys_galerij_combinaties( array_slice( $lijst, $i + 1 ), $k - 1 ) as $rest ) {
			array_unshift( $rest, $lijst[ $i ] );
			$uit[] = $rest;
		}
	}
	return $uit;
}

/** Vergelijkt twee kosten element per element: is $a kleiner dan $b? */
function harveys_galerij_kleiner( $a, $b ) {
	$n = max( count( $a ), count( $b ) );
	for ( $i = 0; $i < $n; $i++ ) {
		$x = isset( $a[ $i ] ) ? $a[ $i ] : 0;
		$y = isset( $b[ $i ] ) ? $b[ $i ] : 0;
		if ( $x != $y ) { // phpcs:ignore Universal.Operators.StrictComparisons -- int en float
			return $x < $y;
		}
	}
	return false;
}
