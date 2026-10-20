<?php
/**
 * Band: twee rijen grote tekst die met het scrollen meeschuiven (decoratief).
 * js/main.js (setupBand) herhaalt de tekst tot het scherm vol is.
 */

defined( 'ABSPATH' ) || exit;

$harveys_rijen = array_filter(
	array(
		array( '', -1, harveys_regels( harveys_optie( 'band_1' ) ) ),
		array( ' band__rij--lijn', 1, harveys_regels( harveys_optie( 'band_2' ) ) ),
	),
	function ( $rij ) {
		return (bool) $rij[2];
	}
);
if ( ! $harveys_rijen ) {
	return;
}
?>
<div class="band" aria-hidden="true">
	<?php foreach ( $harveys_rijen as $harveys_rij ) : ?>
		<div class="band__rij<?php echo esc_attr( $harveys_rij[0] ); ?>" data-richting="<?php echo (int) $harveys_rij[1]; ?>">
			<span class="band__tekst"><?php foreach ( $harveys_rij[2] as $harveys_stuk ) : ?><?php echo esc_html( $harveys_stuk ); ?><i class="band__ster"></i><?php endforeach; ?></span>
		</div>
	<?php endforeach; ?>
</div>
