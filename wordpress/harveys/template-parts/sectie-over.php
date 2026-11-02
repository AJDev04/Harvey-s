<?php
/**
 * Over ons: de uitspraak over de volle breedte (de woorden lichten op terwijl je
 * scrolt, js/main.js setupStatement), daaronder de tekst en een carrousel met
 * foto's van de winkel.
 */

defined( 'ABSPATH' ) || exit;

$harveys_titel = harveys_optie( 'over_titel' );
$harveys_tekst = harveys_optie( 'over_tekst' );
if ( ! $harveys_titel && ! $harveys_tekst ) {
	return;
}
$harveys_fotos  = harveys_fotos( 'over_fotos' );
$harveys_aantal = count( $harveys_fotos );
?>
<section class="sectie reveal" id="over" aria-labelledby="over-titel">
	<div class="container">
		<div class="sectie__kop">
			<h2 class="kop" id="over-titel"><?php echo esc_html( $harveys_titel ?: 'Over ons' ); ?></h2>
		</div>
		<?php if ( harveys_optie( 'over_statement' ) ) : ?>
			<p class="statement"><?php echo harveys_nadruk( harveys_optie( 'over_statement' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- ge-escaped in harveys_nadruk ?></p>
		<?php endif; ?>
	</div>
	<div class="container over">
		<div class="over__tekst">
			<?php echo harveys_alineas( $harveys_tekst ); // phpcs:ignore WordPress.Security.EscapeOutput -- ge-escaped in harveys_alineas ?>
		</div>
		<?php if ( $harveys_aantal ) : ?>
			<div class="carrousel over__carrousel" id="over-carrousel" aria-roledescription="carrousel" aria-label="Foto's van de winkel">
				<div class="carrousel__spoor" tabindex="0">
					<?php foreach ( $harveys_fotos as $harveys_i => $harveys_foto ) : ?>
						<figure class="carrousel__slide" aria-label="<?php echo esc_attr( sprintf( 'Foto %d van %d', $harveys_i + 1, $harveys_aantal ) ); ?>">
							<?php echo harveys_beeld( $harveys_foto, 'large', array( 'sizes' => '(min-width: 56em) 40rem, 78vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php if ( wp_get_attachment_caption( $harveys_foto ) ) : ?>
								<figcaption><?php echo esc_html( wp_get_attachment_caption( $harveys_foto ) ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
				<?php if ( $harveys_aantal > 1 ) : ?>
					<div class="carrousel__balk">
						<p class="carrousel__teller label"><span class="carrousel__nu">01</span> / <?php echo esc_html( sprintf( '%02d', $harveys_aantal ) ); ?></p>
						<div class="carrousel__knoppen">
							<button type="button" data-richting="-1" aria-label="Vorige foto">
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 12H5m6-6-6 6 6 6" /></svg>
							</button>
							<button type="button" data-richting="1" aria-label="Volgende foto">
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
							</button>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
