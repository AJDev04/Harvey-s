<?php
/**
 * Contact: de winkels met foto, adres en telefoon, daaronder de openingsuren en
 * het label "Nu open" (js/main.js, setupStatus, in Belgische tijd).
 */

defined( 'ABSPATH' ) || exit;

$harveys_winkels = harveys_winkels();
if ( ! $harveys_winkels ) {
	return;
}
$harveys_uren = harveys_uren_regels();
$harveys_data = harveys_uren_data();
?>
<section class="sectie sectie--linnen reveal" id="contact" aria-labelledby="contact-titel">
	<div class="container">
		<div class="sectie__kop">
			<h2 class="kop" id="contact-titel">Contact</h2>
			<?php if ( harveys_optie( 'contact_intro' ) ) : ?>
				<p><?php echo esc_html( harveys_optie( 'contact_intro' ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="contact">
			<?php foreach ( $harveys_winkels as $harveys_w ) : ?>
				<article class="winkel">
					<?php if ( $harveys_w['foto'] ) : ?>
						<div class="beeld beeld--4x3">
							<?php echo harveys_beeld( $harveys_w['foto'], 'large', array( 'sizes' => '(min-width: 48em) 50vw, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					<?php endif; ?>
					<div class="winkel__info">
						<?php if ( $harveys_w['nr'] && count( $harveys_winkels ) > 1 ) : // het huisnummer onderscheidt twee winkels; bij één winkel is het niet nodig ?>
							<span class="winkel__nr" aria-hidden="true"><?php echo esc_html( $harveys_w['nr'] ); ?></span>
						<?php endif; ?>
						<div class="winkel__tekst">
							<h3><?php echo esc_html( $harveys_w['naam'] ); ?></h3>
							<address>
								<span><?php echo esc_html( $harveys_w['straat'] ); ?></span>
								<span><?php echo esc_html( $harveys_w['gemeente'] ); ?></span>
								<?php if ( $harveys_w['telefoon'] ) : ?>
									<a class="pil-link" href="<?php echo esc_url( harveys_tel( $harveys_w['telefoon'] ) ); ?>"><?php echo esc_html( $harveys_w['telefoon'] ); ?></a>
								<?php endif; ?>
							</address>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php if ( $harveys_uren ) : ?>
			<div class="contact__uren">
				<h3>Openingsuren</h3>
				<div class="uren">
					<ul>
						<?php foreach ( $harveys_uren as $harveys_regel ) : ?>
							<li><?php echo esc_html( $harveys_regel ); ?></li>
						<?php endforeach; ?>
					</ul>
					<?php if ( $harveys_data ) : ?>
						<p class="status" hidden data-uren="<?php echo esc_attr( wp_json_encode( $harveys_data ) ); ?>" data-feestdagen="<?php echo harveys_optie( 'feestdagen_dicht' ) ? '1' : '0'; ?>"><span class="status__stip" aria-hidden="true"></span><span class="status__tekst"></span></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
