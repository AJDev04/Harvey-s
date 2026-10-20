<?php
/**
 * Merken: per groep een band met logo's die traag voorbijschuift (Merken in de
 * admin). Een merk zonder logo staat er in letters.
 */

defined( 'ABSPATH' ) || exit;

$harveys_banden = array();
$harveys_kant   = array( 'links', 'rechts' );
foreach ( harveys_merkgroepen() as $harveys_slug => $harveys_standaard ) {
	$harveys_merken = harveys_merken_van( $harveys_slug );
	if ( $harveys_merken ) {
		$harveys_term     = get_term_by( 'slug', $harveys_slug, HARVEYS_MERKGROEP );
		$harveys_banden[] = array( $harveys_term ? $harveys_term->name : $harveys_standaard, $harveys_merken );
	}
}
if ( ! $harveys_banden ) {
	return;
}
?>
<section class="sectie sectie--oranje reveal" id="merken" aria-labelledby="merken-titel">
	<div class="container">
		<div class="sectie__kop">
			<h2 class="kop" id="merken-titel">Merken</h2>
			<?php if ( harveys_optie( 'merken_intro' ) ) : ?>
				<p><?php echo esc_html( harveys_optie( 'merken_intro' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php foreach ( $harveys_banden as $harveys_i => $harveys_band ) : ?>
		<div class="merkgroep">
			<div class="container">
				<h3><?php echo esc_html( $harveys_band[0] ); ?></h3>
			</div>
			<div class="merkband merkband--<?php echo esc_attr( $harveys_kant[ $harveys_i % 2 ] ); ?>">
				<div class="merkband__spoor">
					<ul class="merklijst">
						<?php
						foreach ( $harveys_band[1] as $harveys_merk ) :
							$harveys_logo = get_post_thumbnail_id( $harveys_merk );
							$harveys_url  = $harveys_logo ? wp_get_attachment_image_url( $harveys_logo, 'medium' ) : '';
							?>
							<li class="merk">
								<span class="merk__logo">
									<?php if ( $harveys_url ) : ?>
										<img src="<?php echo esc_url( $harveys_url ); ?>" alt="" loading="lazy" decoding="async" />
									<?php else : ?>
										<span class="merk__tekst" aria-hidden="true"><?php echo esc_html( get_the_title( $harveys_merk ) ); ?></span>
									<?php endif; ?>
								</span>
								<span class="merk__naam"><?php echo esc_html( get_the_title( $harveys_merk ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</section>
