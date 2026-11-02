<?php
/**
 * Cadeaubon: de echte bon uit de winkel (omslag en binnenkant, decoratief; ze
 * kantelen mee met de muis, js/main.js setupBon) met de uitleg ernaast.
 */

defined( 'ABSPATH' ) || exit;

if ( ! harveys_optie( 'cadeaubon_tonen' ) ) {
	return;
}
$harveys_winkels = harveys_winkels();
$harveys_bon     = harveys_bon_adres();
$harveys_feiten  = harveys_paren( harveys_optie( 'cadeaubon_feiten' ) );
$harveys_klein   = harveys_paren( harveys_optie( 'cadeaubon_klein' ) );
$harveys_bellen  = array_filter(
	$harveys_winkels,
	function ( $w ) {
		return '' !== $w['telefoon'];
	}
);
?>
<section class="sectie sectie--inkt reveal" id="cadeaubon" aria-labelledby="cadeaubon-titel">
	<div class="container">
		<div class="sectie__kop">
			<h2 class="kop" id="cadeaubon-titel">Cadeaubon</h2>
		</div>
		<div class="cadeaubon">
			<div class="cadeaubon__beeld" aria-hidden="true">
				<div class="cadeaubon__stapel">
					<div class="bon bon--omslag">
						<span class="bon__logo"><?php echo harveys_logo( 'naam' ); // phpcs:ignore WordPress.Security.EscapeOutput -- opgekuiste SVG ?></span>
						<?php if ( harveys_heeft_slogan() ) : ?>
							<span class="bon__slogan"><?php echo harveys_logo( 'slogan' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<?php endif; ?>
						<?php if ( harveys_optie( 'bon_merken' ) ) : ?>
							<span class="bon__merken"><?php echo esc_html( harveys_optie( 'bon_merken' ) ); ?></span>
						<?php endif; ?>
						<?php if ( $harveys_bon['adres'] ) : ?>
							<span class="bon__adres"><?php echo implode( '<br />', array_map( 'esc_html', $harveys_bon['adres'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<?php endif; ?>
						<span class="bon__adres"><?php echo esc_html( $harveys_bon['website'] ); ?></span>
					</div>
					<div class="bon bon--kaart">
						<span class="bon__logo"><?php echo harveys_logo( 'naam' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<?php if ( harveys_optie( 'bon_merken' ) ) : ?>
							<span class="bon__merken"><?php echo esc_html( harveys_optie( 'bon_merken' ) ); ?></span>
						<?php endif; ?>
						<span class="bon__titel">Cadeaubon</span>
						<span class="bon__veld">Geschonken door</span>
						<span class="bon__regels"></span>
						<span class="bon__geldig"><?php echo esc_html( harveys_optie( 'bon_geldig' ) ); ?></span>
						<span class="bon__glans"></span>
					</div>
				</div>
			</div>
			<div class="cadeaubon__tekst">
				<?php if ( harveys_optie( 'cadeaubon_lead' ) ) : ?>
					<p class="cadeaubon__lead"><?php echo harveys_nadruk( harveys_optie( 'cadeaubon_lead' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
				<?php if ( $harveys_feiten ) : ?>
					<dl class="cadeaubon__feiten">
						<?php foreach ( $harveys_feiten as $harveys_feit ) : ?>
							<div>
								<dt class="label"><?php echo esc_html( $harveys_feit[0] ); ?></dt>
								<dd><?php echo esc_html( $harveys_feit[1] ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
				<div class="cadeaubon__actie">
					<?php if ( isset( harveys_secties()['contact'] ) ) : // de knop springt naar Contact ?>
						<a class="knop knop--oranje magneet" href="#contact">
							<span><?php echo esc_html( harveys_optie( 'cadeaubon_knop' ) ?: 'Kom langs' ); ?></span>
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
						</a>
					<?php endif; ?>
					<?php if ( $harveys_bellen ) : ?>
						<p>Of bel:
							<?php
							$harveys_links = array();
							foreach ( $harveys_bellen as $harveys_w ) {
								$harveys_links[] = ( count( $harveys_bellen ) > 1 ? esc_html( $harveys_w['kort'] ) . ' ' : '' )
									. '<a href="' . esc_url( harveys_tel( $harveys_w['telefoon'] ) ) . '">' . esc_html( $harveys_w['telefoon'] ) . '</a>';
							}
							echo implode( ' · ', $harveys_links ); // phpcs:ignore WordPress.Security.EscapeOutput -- hierboven ge-escaped
							?>
						</p>
					<?php endif; ?>
				</div>
				<?php if ( $harveys_klein ) : ?>
					<dl class="cadeaubon__feiten cadeaubon__feiten--klein">
						<?php foreach ( $harveys_klein as $harveys_feit ) : ?>
							<div>
								<dt class="label"><?php echo esc_html( $harveys_feit[0] ); ?></dt>
								<dd><?php echo esc_html( $harveys_feit[1] ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
