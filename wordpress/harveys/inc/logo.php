<?php
/**
 * Het logo, inline in de pagina zodat het de tekstkleur van zijn plek volgt.
 *
 * Het bestaat uit twee SVG's: de naam en de slogan (Website → Logo en kleur).
 * Zonder upload staat de naam van de site er in letters; het logo van Harvey's
 * komt er pas bij de startinhoud in. Het samengestelde logo
 * (hero, footer) zet de slogan gecentreerd onder de naam; de breedte en de ruimte
 * ertussen staan in de instellingen. In de hero krijgt elk pad van de naam de
 * klasse .teken (de letters komen één voor één op) en elk pad van de slogan
 * .slogan-teken met --dx (de slogan loopt open vanuit het midden), net als in
 * de statische site.
 */

defined( 'ABSPATH' ) || exit;

/** De SVG-tekst van 'naam' of 'slogan', of '' als er geen is. */
function harveys_logo_bron( $soort ) {
	$id = (int) harveys_optie( 'logo_' . $soort );
	if ( ! $id || ! harveys_is_svg( $id ) ) {
		return '';
	}
	$pad = get_attached_file( $id );
	return ( $pad && is_readable( $pad ) ) ? (string) file_get_contents( $pad ) : '';
}

/** Het opgekuiste logo als DOMDocument (onthouden per pagina), of null. */
function harveys_logo_doc( $soort ) {
	static $docs = array();
	if ( ! array_key_exists( $soort, $docs ) ) {
		$doc = harveys_svg_lees( harveys_logo_bron( $soort ) );
		if ( $doc ) {
			harveys_svg_opkuisen( $doc, true );
			if ( ! harveys_svg_viewbox( $doc->documentElement ) ) {
				$doc = null;
			}
		}
		$docs[ $soort ] = $doc;
	}
	return $docs[ $soort ] ? $docs[ $soort ]->cloneNode( true ) : null;
}

/**
 * Geeft elke id in de SVG een uniek voorvoegsel: het logo staat meerdere keren op
 * de pagina, en een id moet uniek zijn (anders verwijst url(#...) naar de
 * verkeerde, die misschien verborgen is).
 */
function harveys_logo_ids( $svg ) {
	static $n = 0;
	$n++;
	$p = 'logo' . $n . '-';
	return preg_replace(
		array( '/\sid="([^"]+)"/', '/url\(#/', '/href="#/' ),
		array( ' id="' . $p . '$1"', 'url(#' . $p, 'href="#' . $p ),
		$svg
	);
}

/** De naam of de slogan als inline SVG. */
function harveys_logo( $soort, $klasse = '' ) {
	$doc = harveys_logo_doc( $soort );
	if ( ! $doc ) {
		if ( 'naam' !== $soort ) {
			return '';
		}
		// het aantal tekens: zo maakt de CSS de naam even breed als een logo (wordpress.css)
		$naam = get_bloginfo( 'name' );
		$n    = max( 1, function_exists( 'mb_strlen' ) ? mb_strlen( $naam ) : strlen( $naam ) );
		return '<span class="logo__tekst" style="--tekens: ' . (int) $n . '">' . esc_html( $naam ) . '</span>';
	}
	$svg = $doc->documentElement;
	if ( $klasse ) {
		$svg->setAttribute( 'class', $klasse );
	}
	$svg->setAttribute( 'aria-hidden', 'true' );
	$svg->setAttribute( 'focusable', 'false' );
	return harveys_logo_ids( $doc->saveXML( $svg ) );
}

/** Is er een slogan? */
function harveys_heeft_slogan() {
	return (bool) harveys_logo_doc( 'slogan' );
}

/** Ongeveer de x van een vorm: genoeg om te weten aan welke kant van het midden ze staat. */
function harveys_logo_x( DOMElement $el ) {
	switch ( strtolower( $el->localName ) ) {
		case 'path':
			return preg_match( '/^\s*[Mm]\s*(-?[\d.]+(?:e-?\d+)?)/i', $el->getAttribute( 'd' ), $m ) ? (float) $m[1] : null;
		case 'rect':
			return (float) $el->getAttribute( 'x' ) + (float) $el->getAttribute( 'width' ) / 2;
		case 'circle':
		case 'ellipse':
			return (float) $el->getAttribute( 'cx' );
		case 'line':
			return (float) $el->getAttribute( 'x1' );
		case 'polygon':
		case 'polyline':
			return preg_match( '/-?[\d.]+/', $el->getAttribute( 'points' ), $m ) ? (float) $m[0] : null;
		default:
			return (float) $el->getAttribute( 'x' );
	}
}

/**
 * Zet een klasse op elke zichtbare vorm (niet die in defs, clipPath of mask) en
 * geeft --i (de volgorde) en, met $dx, de afstand tot het midden mee.
 */
function harveys_logo_tekens( DOMDocument $doc, $klasse, $dx ) {
	$svg    = $doc->documentElement;
	$vb     = harveys_svg_viewbox( $svg );
	$midden = $vb[0] + $vb[2] / 2;
	$xpath  = new DOMXPath( $doc );
	$xpath->registerNamespace( 's', HARVEYS_SVG_NS );
	$vormen = array( 'path', 'rect', 'circle', 'ellipse', 'line', 'polygon', 'polyline', 'use' );
	$tekens = array();
	foreach ( $xpath->query( '//*' ) as $el ) {
		if ( ! in_array( strtolower( $el->localName ), $vormen, true ) ) {
			continue;
		}
		for ( $ouder = $el->parentNode; $ouder instanceof DOMElement; $ouder = $ouder->parentNode ) {
			if ( in_array( strtolower( $ouder->localName ), array( 'defs', 'clippath', 'mask', 'symbol' ), true ) ) {
				continue 2;
			}
		}
		$tekens[] = $el;
	}
	// één pad voor de hele slogan: die schuift niet, ze komt gewoon op
	$dx = $dx && count( $tekens ) > 1;
	foreach ( $tekens as $i => $el ) {
		$stijl = '--i: ' . $i;
		if ( $dx ) {
			$x      = harveys_logo_x( $el );
			$stijl .= '; --dx: ' . round( null === $x ? 0 : ( $midden - $x ) * 0.45, 2 );
		}
		$el->setAttribute( 'class', $klasse );
		$el->setAttribute( 'style', $stijl );
	}
}

/**
 * Naam en slogan samen: de slogan gecentreerd onder de naam. Met $hero komen de
 * klassen voor de intro erbij.
 */
function harveys_logo_samen( $klasse = '', $hero = false ) {
	$naam = harveys_logo_doc( 'naam' );
	if ( ! $naam ) {
		return harveys_logo( 'naam' );
	}
	$slogan = harveys_logo_doc( 'slogan' );

	$b   = 1000;
	$nvb = harveys_svg_viewbox( $naam->documentElement );
	$nh  = $b * $nvb[3] / $nvb[2];
	$h   = $nh;

	if ( $hero ) {
		harveys_logo_tekens( $naam, 'teken', false );
	}
	$n = $naam->documentElement;
	$n->setAttribute( 'x', '0' );
	$n->setAttribute( 'y', '0' );
	$n->setAttribute( 'width', (string) $b );
	$n->setAttribute( 'height', (string) round( $nh, 2 ) );
	// buiten de naam is niets te zien: zo komen de letters in de hero "uit hun vak"
	$n->setAttribute( 'overflow', 'hidden' );
	// naam en slogan elk hun eigen voorvoegsel: allebei kunnen ze dezelfde id's hebben
	$binnen = harveys_logo_ids( $naam->saveXML( $n ) );

	if ( $slogan ) {
		if ( $hero ) {
			harveys_logo_tekens( $slogan, 'slogan-teken', true );
		}
		$svb     = harveys_svg_viewbox( $slogan->documentElement );
		$sb      = $b * min( 100, max( 5, (float) harveys_optie( 'slogan_breedte' ) ) ) / 100;
		$sh      = $sb * $svb[3] / $svb[2];
		$afstand = $nh * max( 0, (float) harveys_optie( 'slogan_afstand' ) ) / 100;
		$s       = $slogan->documentElement;
		$s->setAttribute( 'x', (string) round( ( $b - $sb ) / 2, 2 ) );
		$s->setAttribute( 'y', (string) round( $nh + $afstand, 2 ) );
		$s->setAttribute( 'width', (string) round( $sb, 2 ) );
		$s->setAttribute( 'height', (string) round( $sh, 2 ) );
		$s->setAttribute( 'overflow', 'visible' );
		$binnen .= harveys_logo_ids( $slogan->saveXML( $s ) );
		$h       = $nh + $afstand + $sh;
	}

	$klasse_attr = $klasse ? ' class="' . esc_attr( $klasse ) . '"' : '';
	return '<svg' . $klasse_attr . ' xmlns="' . HARVEYS_SVG_NS . '" viewBox="0 0 ' . $b . ' ' . round( $h, 2 ) . '" aria-hidden="true" focusable="false">' . $binnen . '</svg>';
}
