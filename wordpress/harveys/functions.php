<?php
/**
 * Thema Harvey's — Casual Luxury.
 *
 * De vormgeving (assets/css, assets/js) is dezelfde als die van de statische site;
 * tools/maak-thema.sh kopieert ze hierheen. Alles wat per winkel verschilt (logo,
 * teksten, foto's, winkels, uren) staat in de instellingen onder Website.
 *
 *   inc/instellingen.php  de velden, ophalen en bewaren
 *   inc/beheer.php        de pagina's onder Website in de admin
 *   inc/svg.php           SVG's uploaden (opgekuist) en tonen
 *   inc/logo.php          het logo inline: naam, slogan en samengesteld
 *   inc/merken.php        het berichttype Merk met de groepen Voor haar / Voor hem
 *   inc/galerij.php       Collectie: welke foto's groot zijn, zodat het raster vol is
 *   inc/nieuwsbrief.php   inschrijvingen bewaren en exporteren
 *   inc/hulp.php          kleine helpers voor de sjablonen
 *   inc/setup.php         themasteun, scripts, stijlen en de <head>
 *   inc/startinhoud.php   de inhoud van de oorspronkelijke site importeren
 */

defined( 'ABSPATH' ) || exit;

define( 'HARVEYS_VERSIE', '1.3.3' );
define( 'HARVEYS_MAP', get_template_directory() );
define( 'HARVEYS_URL', get_template_directory_uri() );

require HARVEYS_MAP . '/inc/instellingen.php';
require HARVEYS_MAP . '/inc/svg.php';
require HARVEYS_MAP . '/inc/logo.php';
require HARVEYS_MAP . '/inc/merken.php';
require HARVEYS_MAP . '/inc/nieuwsbrief.php';
require HARVEYS_MAP . '/inc/hulp.php';
require HARVEYS_MAP . '/inc/galerij.php';
require HARVEYS_MAP . '/inc/setup.php';

if ( is_admin() ) {
	require HARVEYS_MAP . '/inc/beheer.php';
	require HARVEYS_MAP . '/inc/startinhoud.php';
}
