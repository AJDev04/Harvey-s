<?php
/**
 * Begin van elke pagina: de <head> en de vaste header met het menu.
 */

defined( 'ABSPATH' ) || exit;

$harveys_naam    = get_bloginfo( 'name' );
$harveys_secties = harveys_secties();
unset( $harveys_secties['cadeaubon'] ); // die staat enkel in de footer
?><!doctype html>
<html <?php language_attributes(); ?> class="no-js">

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<a class="skip-link" href="#main">Naar de inhoud</a>
	<div id="top"></div>

	<header class="header">
		<div class="container header__inner">
			<a class="logo" href="<?php echo esc_url( is_front_page() ? '#top' : home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $harveys_naam . ( is_front_page() ? ', naar boven' : ', naar de homepage' ) ); ?>">
				<?php echo harveys_logo( 'naam' ); // phpcs:ignore WordPress.Security.EscapeOutput -- opgekuiste SVG ?>
			</a>
			<nav class="nav" aria-label="Hoofdmenu">
				<button class="nav__toggle" type="button" aria-expanded="false" aria-controls="hoofdmenu">
					<span class="nav__toggle-tekst" aria-hidden="true"><span>Menu</span><span>Sluit</span></span>
					<span class="nav__toggle-lijnen" aria-hidden="true"><i></i><i></i></span>
					<span class="visually-hidden">Menu</span>
				</button>
				<div class="nav__paneel" id="hoofdmenu">
					<ul class="nav__lijst">
						<?php foreach ( $harveys_secties as $harveys_id => $harveys_label ) : ?>
							<li><a class="nav__link" href="<?php echo esc_url( harveys_sectie_url( $harveys_id ) ); ?>"><?php echo esc_html( $harveys_label ); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<div class="nav__voet">
						<p class="nav__adres"><?php echo esc_html( harveys_optie( 'adres_kort' ) ); ?><br /><?php echo esc_html( harveys_optie( 'gemeente' ) ); ?></p>
						<?php if ( harveys_heeft_slogan() ) : ?>
							<p class="nav__slogan">
								<span class="visually-hidden"><?php bloginfo( 'description' ); ?></span>
								<?php echo harveys_logo( 'slogan' ); // phpcs:ignore WordPress.Security.EscapeOutput -- opgekuiste SVG ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</nav>
		</div>
	</header>
