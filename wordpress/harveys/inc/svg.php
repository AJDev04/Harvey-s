<?php
/**
 * SVG's: WordPress laat ze standaard niet toe, omdat een SVG ook code kan
 * bevatten. Dit thema laat ze wel toe (logo's van de winkel en van merken), maar
 * kuist elk bestand bij het uploaden op: enkel vormen, kleuren en tekst blijven
 * over, geen scripts, links of ingesloten pagina's.
 *
 * Voor het logo in de pagina (inline) gaat er nog meer uit: elke kleur wordt
 * currentColor, zodat het logo de tekstkleur van zijn plek aanneemt.
 *
 * Een SVG uploaden kan enkel een beheerder (harveys_mag_svg); ook als een andere
 * plugin SVG's toelaat, weigert het thema ze van andere gebruikers.
 */

defined( 'ABSPATH' ) || exit;

const HARVEYS_SVG_NS = 'http://www.w3.org/2000/svg';

/** Elementen die in een opgekuist bestand mogen blijven staan. */
function harveys_svg_elementen( $inline ) {
	// switch: Illustrator zet de tekening soms in een <switch> (met ernaast een Adobe-blok dat wegvalt)
	$basis = array( 'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'defs', 'clippath', 'mask', 'use', 'symbol', 'switch' );
	if ( $inline ) {
		return $basis;
	}
	return array_merge( $basis, array( 'title', 'desc', 'lineargradient', 'radialgradient', 'stop', 'pattern', 'text', 'tspan', 'style', 'image' ) );
}

/** Attributen die mogen blijven (in kleine letters). */
function harveys_svg_attributen( $inline ) {
	$basis = array(
		'id', 'd', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'width', 'height', 'points',
		'viewbox', 'preserveaspectratio', 'transform', 'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width',
		'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset',
		'stroke-opacity', 'opacity', 'clip-path', 'clip-rule', 'mask', 'clippathunits', 'maskunits',
		'maskcontentunits', 'href', 'xlink:href', 'display', 'visibility',
	);
	if ( $inline ) {
		return $basis;
	}
	return array_merge(
		$basis,
		array(
			'class', 'style', 'offset', 'stop-color', 'stop-opacity', 'gradientunits', 'gradienttransform',
			'spreadmethod', 'fx', 'fy', 'fr', 'patternunits', 'patterncontentunits', 'patterntransform',
			'font-family', 'font-size', 'font-weight', 'font-style', 'letter-spacing', 'text-anchor',
			'dominant-baseline', 'xml:space', 'version', 'dx', 'dy',
		)
	);
}

/** Leest SVG-tekst in; null als het geen geldige SVG is. */
function harveys_svg_lees( $inhoud ) {
	if ( ! class_exists( 'DOMDocument' ) || ! is_string( $inhoud ) || '' === trim( $inhoud ) ) {
		return null;
	}
	// Illustrator zet soms een DOCTYPE met eigen entiteiten bovenaan (de namen van
	// zijn namespaces). Die vullen we zelf in, één keer en niet genest, en de DOCTYPE
	// gaat weg: zo kan een bestand de server niet laten vastlopen of er bestanden van
	// inlezen.
	$entiteiten = array();
	if ( preg_match( '/<!DOCTYPE[^\[>]*(?:\[(.*?)\])?\s*>/is', $inhoud, $m ) ) {
		if ( ! empty( $m[1] ) && preg_match_all( '/<!ENTITY\s+([a-zA-Z_][\w.-]*)\s+(["\'])([^"\'&<%]{0,500})\2\s*>/', $m[1], $gevonden, PREG_SET_ORDER ) ) {
			foreach ( $gevonden as $e ) {
				$entiteiten[ $e[1] ] = $e[3];
			}
		}
		$inhoud = str_replace( $m[0], '', $inhoud );
	}
	$inhoud = preg_replace_callback(
		'/&([a-zA-Z_][\w.-]*);/',
		function ( $r ) use ( $entiteiten ) {
			if ( in_array( $r[1], array( 'amp', 'lt', 'gt', 'quot', 'apos' ), true ) ) {
				return $r[0];
			}
			return isset( $entiteiten[ $r[1] ] ) ? htmlspecialchars( $entiteiten[ $r[1] ], ENT_QUOTES | ENT_XML1 ) : '';
		},
		$inhoud
	);
	if ( stripos( $inhoud, '<!ENTITY' ) !== false || stripos( $inhoud, '<!DOCTYPE' ) !== false ) {
		return null;
	}
	$vorige = libxml_use_internal_errors( true );
	$doc    = new DOMDocument();
	$ok     = $doc->loadXML( $inhoud, LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $vorige );
	if ( ! $ok || ! $doc->documentElement || 'svg' !== strtolower( $doc->documentElement->localName ) ) {
		return null;
	}
	return $doc;
}

/** Is deze attribuutwaarde veilig? Geen scripts, geen verwijzingen naar buiten. */
function harveys_svg_waarde_veilig( $naam, $waarde, $element ) {
	$plat = strtolower( preg_replace( '/[\s\x00-\x1f]+/', '', $waarde ) );
	if ( strpos( $plat, 'javascript:' ) !== false || strpos( $plat, 'vbscript:' ) !== false || strpos( $plat, 'expression(' ) !== false ) {
		return false;
	}
	// CSS-escapes (\75rl) kunnen url( of javascript: verbergen
	if ( 'style' === $naam && strpos( $waarde, '\\' ) !== false ) {
		return false;
	}
	if ( 'href' === $naam || 'xlink:href' === $naam ) {
		if ( 0 === strpos( $waarde, '#' ) ) {
			return true;
		}
		// een ingesloten foto in een merklogo mag, als het echt een foto is
		return 'image' === $element && (bool) preg_match( '#^data:image/(png|jpe?g|gif|webp);base64,#i', trim( $waarde ) );
	}
	if ( strpos( $plat, 'url(' ) !== false ) {
		// enkel url(#iets): verwijzingen binnen het bestand
		return ! preg_match( '/url\((?!["\']?#)/', $plat );
	}
	return strpos( $plat, 'data:' ) === false;
}

/**
 * Kuist een SVG op. $inline: voor in de pagina (enkel vormen, alles in
 * currentColor, zonder klassen of stijlen); anders: voor een bestand in de
 * mediabibliotheek (kleuren en stijlen blijven).
 */
function harveys_svg_opkuisen( DOMDocument $doc, $inline ) {
	$elementen  = harveys_svg_elementen( $inline );
	$attributen = harveys_svg_attributen( $inline );

	// commentaar en processing instructions eruit; inline ook alle tekst (er blijft geen
	// element over dat tekst toont, en zo kan er ook geen CDATA in de pagina belanden)
	$xpath = new DOMXPath( $doc );
	$weg   = $inline ? '//comment() | //processing-instruction() | //text()' : '//comment() | //processing-instruction()';
	foreach ( iterator_to_array( $xpath->query( $weg ) ) as $knoop ) {
		$knoop->parentNode->removeChild( $knoop );
	}

	$loop = function ( DOMElement $el ) use ( &$loop, $elementen, $attributen, $inline ) {
		foreach ( iterator_to_array( $el->childNodes ) as $kind ) {
			if ( ! $kind instanceof DOMElement ) {
				continue;
			}
			$naam = strtolower( $kind->localName );
			if ( ! in_array( $naam, $elementen, true ) || ( $kind->namespaceURI && HARVEYS_SVG_NS !== $kind->namespaceURI ) ) {
				$el->removeChild( $kind );
				continue;
			}
			if ( 'style' === $naam && preg_match( '/@import|javascript:|expression\(|url\((?!["\']?#)|\\\\/i', $kind->textContent ) ) {
				$el->removeChild( $kind );
				continue;
			}
			$loop( $kind );
		}

		$tag = strtolower( $el->localName );
		foreach ( iterator_to_array( $el->attributes ) as $attr ) {
			$an = strtolower( $attr->nodeName );
			if ( ! in_array( $an, $attributen, true ) || ! harveys_svg_waarde_veilig( $an, $attr->value, $tag ) ) {
				$el->removeAttributeNode( $attr );
				continue;
			}
			if ( $inline && in_array( $an, array( 'fill', 'stroke' ), true ) && 'none' !== strtolower( trim( $attr->value ) ) && 0 !== strpos( trim( $attr->value ), 'url(' ) ) {
				$el->setAttribute( $attr->nodeName, 'currentColor' );
			}
		}
	};
	$loop( $doc->documentElement );

	if ( $inline ) {
		$wortel = $doc->documentElement;
		$vb     = harveys_svg_viewbox( $wortel );
		if ( $vb ) {
			$wortel->setAttribute( 'viewBox', implode( ' ', $vb ) );
		}
		$wortel->removeAttribute( 'width' );
		$wortel->removeAttribute( 'height' );
		if ( ! $wortel->hasAttribute( 'fill' ) ) {
			$wortel->setAttribute( 'fill', 'currentColor' );
		}
	}
	return $doc;
}

/** De viewBox als [x, y, breedte, hoogte], of uit width/height; null als er geen is. */
function harveys_svg_viewbox( DOMElement $svg ) {
	$vb = preg_split( '/[\s,]+/', trim( $svg->getAttribute( 'viewBox' ) ) );
	if ( 4 === count( $vb ) && (float) $vb[2] > 0 && (float) $vb[3] > 0 ) {
		return array_map( 'floatval', $vb );
	}
	$b = (float) $svg->getAttribute( 'width' );
	$h = (float) $svg->getAttribute( 'height' );
	return ( $b > 0 && $h > 0 ) ? array( 0.0, 0.0, $b, $h ) : null;
}

/** Een opgekuist SVG-bestand als tekst, of null als het geen geldige SVG is. */
function harveys_svg_schoon_bestand( $inhoud ) {
	$doc = harveys_svg_lees( $inhoud );
	if ( ! $doc ) {
		return null;
	}
	harveys_svg_opkuisen( $doc, false );
	return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $doc->saveXML( $doc->documentElement ) . "\n";
}

/* ---------- Uploaden ---------- */

/** Mag de huidige gebruiker een SVG uploaden? Enkel een beheerder. */
function harveys_mag_svg() {
	return current_user_can( 'manage_options' ) && current_user_can( 'upload_files' );
}

add_filter(
	'upload_mimes',
	function ( $mimes ) {
		if ( harveys_mag_svg() ) {
			$mimes['svg'] = 'image/svg+xml';
		} else {
			unset( $mimes['svg'], $mimes['svgz'] );
		}
		return $mimes;
	}
);

// PHP herkent een SVG soms als gewone tekst; dan zou WordPress het weigeren
add_filter(
	'wp_check_filetype_and_ext',
	function ( $data, $file, $bestandsnaam ) {
		if ( empty( $data['type'] ) && preg_match( '/\.svg$/i', (string) $bestandsnaam ) && harveys_mag_svg() ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		}
		return $data;
	},
	10,
	3
);

/** Kuist een SVG op nog voor WordPress het bewaart (uploaden en importeren). */
function harveys_svg_upload( $bestand ) {
	if ( empty( $bestand['name'] ) || ! preg_match( '/\.svgz?$/i', $bestand['name'] ) ) {
		return $bestand;
	}
	// een upload die al mislukt is (bv. te groot): WordPress meldt dat zelf
	if ( ! empty( $bestand['error'] ) || empty( $bestand['tmp_name'] ) || ! is_readable( $bestand['tmp_name'] ) ) {
		return $bestand;
	}
	if ( ! harveys_mag_svg() ) {
		$bestand['error'] = 'Een SVG uploaden kan enkel een beheerder. Upload het logo als PNG, of vraag het aan een beheerder.';
		return $bestand;
	}
	if ( preg_match( '/\.svgz$/i', $bestand['name'] ) ) {
		$bestand['error'] = 'Upload een gewoon .svg-bestand (geen .svgz).';
		return $bestand;
	}
	$schoon = harveys_svg_schoon_bestand( (string) file_get_contents( $bestand['tmp_name'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( null === $schoon ) {
		$bestand['error'] = 'Dit SVG-bestand kon niet gelezen worden. Bewaar het opnieuw als SVG (bv. in Illustrator: Bestand → Exporteren → SVG).';
		return $bestand;
	}
	if ( false === file_put_contents( $bestand['tmp_name'], $schoon ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
		$bestand['error'] = 'Het SVG-bestand kon niet opgekuist worden.';
	}
	return $bestand;
}
add_filter( 'wp_handle_upload_prefilter', 'harveys_svg_upload' );
add_filter( 'wp_handle_sideload_prefilter', 'harveys_svg_upload' );

/* ---------- Tonen ---------- */

/** Breedte en hoogte van een SVG uit de mediabibliotheek (uit de viewBox, onthouden). */
function harveys_svg_maten( $id ) {
	$maten = get_post_meta( $id, '_harveys_svg_maten', true );
	if ( is_array( $maten ) ) {
		return $maten;
	}
	$maten = array( 300, 150 );
	$pad   = get_attached_file( $id );
	$doc   = ( $pad && is_readable( $pad ) ) ? harveys_svg_lees( (string) file_get_contents( $pad ) ) : null;
	$vb    = $doc ? harveys_svg_viewbox( $doc->documentElement ) : null;
	if ( $vb ) {
		// een logo van een paar pixels breed is niet handig in de admin: schaal op tot 300
		$schaal = 300 / max( $vb[2], $vb[3] );
		$maten  = array( (int) round( $vb[2] * $schaal ), (int) round( $vb[3] * $schaal ) );
	}
	update_post_meta( $id, '_harveys_svg_maten', $maten );
	return $maten;
}

function harveys_is_svg( $id ) {
	return 'image/svg+xml' === get_post_mime_type( $id );
}

// Zo geeft wp_get_attachment_image() ook voor een SVG een gewone <img>
add_filter(
	'image_downsize',
	function ( $uit, $id ) {
		if ( ! harveys_is_svg( $id ) ) {
			return $uit;
		}
		list( $b, $h ) = harveys_svg_maten( $id );
		return array( wp_get_attachment_url( $id ), $b, $h, false );
	},
	10,
	2
);

// Een SVG heeft geen pixels: WordPress krijgt de maten uit de viewBox (zonder die
// meldt het bv. bij een upload via de blokeditor "Undefined array key width")...
add_filter(
	'wp_get_attachment_metadata',
	function ( $meta, $id ) {
		if ( ! harveys_is_svg( $id ) || ( is_array( $meta ) && isset( $meta['width'], $meta['height'] ) ) ) {
			return $meta;
		}
		list( $b, $h ) = harveys_svg_maten( $id );
		return array_merge(
			is_array( $meta ) ? $meta : array(),
			array(
				'width'  => $b,
				'height' => $h,
			)
		);
	},
	10,
	2
);

// ... en maakt er geen verkleinde versies van
add_filter(
	'wp_get_missing_image_subsizes',
	function ( $ontbrekend, $meta, $id ) {
		return harveys_is_svg( $id ) ? array() : $ontbrekend;
	},
	10,
	3
);

// Een voorbeeld van de SVG in de mediabibliotheek, in plaats van een icoontje
add_filter(
	'wp_prepare_attachment_for_js',
	function ( $antwoord, $bijlage ) {
		if ( 'image/svg+xml' !== $antwoord['mime'] ) {
			return $antwoord;
		}
		list( $b, $h )        = harveys_svg_maten( $bijlage->ID );
		$maat                 = array(
			'url'         => $antwoord['url'],
			'width'       => $b,
			'height'      => $h,
			'orientation' => $b >= $h ? 'landscape' : 'portrait',
		);
		$antwoord['sizes']    = array( 'full' => $maat, 'medium' => $maat, 'thumbnail' => $maat );
		$antwoord['width']    = $b;
		$antwoord['height']   = $h;
		$antwoord['type']     = 'image';
		$antwoord['subtype']  = 'svg+xml';
		return $antwoord;
	},
	10,
	2
);
