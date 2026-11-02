<?php
/**
 * Themasteun, stijlen en scripts, en wat er in de <head> komt.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		// een video in Media krijgt een uitgelichte afbeelding: het stilstaand beeld in de Collectie
		add_post_type_support( 'attachment:video', 'thumbnail' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
	}
);

/** De kleur van de site als #rrggbb (Website → Logo en kleur). */
function harveys_kleur() {
	$kleur = sanitize_hex_color( (string) harveys_optie( 'kleur' ) );
	return $kleur ? strtolower( $kleur ) : HARVEYS_KLEUR;
}

/** Versie van een bestand voor de cache: wanneer het laatst gewijzigd is. */
function harveys_versie( $pad ) {
	$bestand = HARVEYS_MAP . '/' . $pad;
	return file_exists( $bestand ) ? (string) filemtime( $bestand ) : HARVEYS_VERSIE;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'harveys-fonts',
			'https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Instrument+Serif:ital@0;1&family=Michroma&display=swap',
			array(),
			null
		);
		$vorige = 'harveys-fonts';
		foreach ( array( 'tokens', 'base', 'layout', 'components', 'beweging', 'wordpress' ) as $naam ) {
			$pad = 'assets/css/' . $naam . '.css';
			wp_enqueue_style( 'harveys-' . $naam, HARVEYS_URL . '/' . $pad, array( $vorige ), harveys_versie( $pad ) );
			$vorige = 'harveys-' . $naam;
		}

		// een andere kleur dan die van Harvey's (Website → Logo en kleur): de rest volgt
		$kleur = harveys_kleur();
		if ( HARVEYS_KLEUR !== $kleur ) {
			wp_add_inline_style(
				'harveys-tokens',
				sprintf(
					':root{--oranje:%1$s;--oranje-diep:color-mix(in srgb,%1$s 82%%,#000);--oranje-licht:color-mix(in srgb,%1$s 65%%,#fff);}',
					$kleur
				)
			);
		}

		wp_enqueue_script( 'harveys-main', HARVEYS_URL . '/assets/js/main.js', array(), harveys_versie( 'assets/js/main.js' ), array( 'in_footer' => true ) );
		if ( is_front_page() ) {
			wp_enqueue_script( 'harveys-galerij', HARVEYS_URL . '/assets/js/galerij.js', array( 'harveys-main' ), harveys_versie( 'assets/js/galerij.js' ), array( 'in_footer' => true ) );
			// de homepage gebruikt geen blokken: die stijlen hoeven niet mee
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'classic-theme-styles' );
			wp_dequeue_style( 'global-styles' );
		}
	}
);

// De lettertypes komen van Google Fonts: alvast verbinden
add_filter(
	'wp_resource_hints',
	function ( $hints, $soort ) {
		if ( 'preconnect' === $soort ) {
			$hints[] = 'https://fonts.googleapis.com';
			$hints[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $hints;
	},
	10,
	2
);

add_action(
	'wp_head',
	function () {
		echo '<meta name="theme-color" content="' . esc_attr( harveys_kleur() ) . '" />' . "\n";
		// een SEO-plugin zet zelf een beschrijving
		$seo = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
		if ( is_front_page() && ! $seo && harveys_optie( 'beschrijving' ) ) {
			echo '<meta name="description" content="' . esc_attr( harveys_optie( 'beschrijving' ) ) . '" />' . "\n";
		}
		// een Site-pictogram uit de Customizer gaat voor; anders het icoontje uit Logo en kleur
		$icoon = (int) harveys_optie( 'favicon' );
		if ( ! has_site_icon() && $icoon && harveys_is_svg( $icoon ) ) {
			echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( wp_get_attachment_url( $icoon ) ) . '" />' . "\n";
		}
	},
	2
);

add_filter(
	'document_title_separator',
	function () {
		return '|';
	}
);

// De homepage heeft een hero onder de header; andere pagina's beginnen eronder
add_filter(
	'body_class',
	function ( $klassen ) {
		$klassen[] = is_front_page() ? 'pagina-home' : 'pagina-tekst';
		return $klassen;
	}
);

// Geen emoji-script: de site gebruikt geen emoji
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Een korte samenvatting van een nieuwsbericht op de homepage
add_filter(
	'excerpt_more',
	function () {
		return '…';
	}
);
