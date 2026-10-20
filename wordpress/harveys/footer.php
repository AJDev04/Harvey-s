<?php
/**
 * Einde van elke pagina: de footer met snelmenu, winkels, logo en de juridische links.
 */

defined( 'ABSPATH' ) || exit;

$harveys_secties = harveys_secties();
unset( $harveys_secties['home'] );
$harveys_privacy    = get_privacy_policy_url();
$harveys_disclaimer = harveys_optie( 'disclaimer_pagina' ) ? get_permalink( (int) harveys_optie( 'disclaimer_pagina' ) ) : '';
?>
	<footer class="footer">
		<div class="container">
			<div class="footer__boven">
				<nav class="footer__menu" aria-label="Snel naar">
					<?php foreach ( $harveys_secties as $harveys_id => $harveys_label ) : ?>
						<a href="<?php echo esc_url( harveys_sectie_url( $harveys_id ) ); ?>"><?php echo esc_html( $harveys_label ); ?></a>
					<?php endforeach; ?>
				</nav>
				<div class="footer__winkels">
					<?php foreach ( harveys_winkels() as $harveys_winkel ) : ?>
						<p>
							<?php echo esc_html( $harveys_winkel['naam'] ); ?><br />
							<?php echo esc_html( $harveys_winkel['straat'] ); ?>
							<?php if ( $harveys_winkel['telefoon'] ) : ?>
								· <a href="<?php echo esc_attr( harveys_tel( $harveys_winkel['telefoon'] ) ); ?>"><?php echo esc_html( $harveys_winkel['telefoon'] ); ?></a>
							<?php endif; ?>
						</p>
					<?php endforeach; ?>
				</div>
			</div>
			<a class="footer__logo" href="<?php echo esc_url( is_front_page() ? '#top' : home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ( is_front_page() ? ', naar boven' : ', naar de homepage' ) ); ?>">
				<?php echo harveys_logo_samen(); // phpcs:ignore WordPress.Security.EscapeOutput -- opgekuiste SVG ?>
			</a>
			<div class="footer__onder">
				<div class="footer__meta">
					<?php if ( harveys_optie( 'footer_regel' ) ) : ?>
						<span><?php echo esc_html( harveys_optie( 'footer_regel' ) ); ?></span>
					<?php endif; ?>
					<?php if ( harveys_optie( 'btw' ) ) : ?>
						<span><?php echo esc_html( harveys_optie( 'btw' ) ); ?></span>
					<?php endif; ?>
					<span>Copyright © <?php echo esc_html( wp_date( 'Y' ) . ' · ' . get_bloginfo( 'name' ) ); ?></span>
				</div>
				<?php if ( $harveys_privacy || $harveys_disclaimer ) : ?>
					<nav class="footer__links" aria-label="Juridisch">
						<?php if ( $harveys_disclaimer ) : ?>
							<a href="<?php echo esc_url( $harveys_disclaimer ); ?>">Disclaimer</a>
						<?php endif; ?>
						<?php if ( $harveys_privacy ) : ?>
							<a href="<?php echo esc_url( $harveys_privacy ); ?>">Privacy</a>
						<?php endif; ?>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</footer>

	<?php wp_footer(); ?>
</body>

</html>
