<?php
/**
 * Collectie: de foto's van twee groepen, standaard Voor haar en Voor hem
 * (Website → Collectie). Met JavaScript worden de groepen twee tabbladen en opent
 * een foto in de lichtbak (js/galerij.js); zonder staan ze gewoon onder elkaar.
 */

defined( 'ABSPATH' ) || exit;

// elke groep als [naam, foto's]
$harveys_groepen = array();
foreach ( array( 'haar', 'hem' ) as $harveys_g ) {
	if ( harveys_optie( 'collectie_' . $harveys_g ) ) {
		$harveys_groepen[] = array( harveys_optie( 'collectie_' . $harveys_g . '_titel' ), harveys_optie( 'collectie_' . $harveys_g ) );
	}
}
if ( ! $harveys_groepen ) {
	return;
}
?>
<section class="sectie sectie--linnen reveal" id="collectie" aria-labelledby="collectie-titel">
	<div class="container">
		<div class="sectie__kop">
			<h2 class="kop" id="collectie-titel">Collectie</h2>
			<?php if ( harveys_optie( 'collectie_intro' ) ) : ?>
				<p><?php echo esc_html( harveys_optie( 'collectie_intro' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="container galerij" id="galerij">
		<?php foreach ( $harveys_groepen as list( $harveys_groep, $harveys_fotos ) ) : ?>
			<div class="galerij__groep">
				<h3><?php echo esc_html( $harveys_groep ); ?></h3>
				<div class="galerij__raster">
					<?php
					foreach ( $harveys_fotos as $harveys_foto ) :
						// de alt-tekst uit de mediabibliotheek; de lichtbak toont ze bij de vergrote foto
						$harveys_beeld = harveys_beeld( $harveys_foto, 'large', array( 'sizes' => '(min-width: 64em) 25vw, (min-width: 48em) 33vw, 50vw' ) );
						if ( ! $harveys_beeld ) {
							continue;
						}
						$harveys_merk = wp_get_attachment_caption( $harveys_foto );
						?>
						<figure class="galerij__item" data-merk="<?php echo esc_attr( $harveys_merk ); ?>" data-groep="<?php echo esc_attr( $harveys_groep ); ?>" data-groot="<?php echo esc_url( wp_get_attachment_image_url( $harveys_foto, 'full' ) ); ?>">
							<button class="galerij__knop" type="button" aria-label="<?php echo esc_attr( 'Vergroot: ' . $harveys_groep . ( $harveys_merk ? ' — ' . $harveys_merk : '' ) ); ?>">
								<?php echo $harveys_beeld; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image ?>
							</button>
							<?php if ( $harveys_merk ) : ?>
								<figcaption><?php echo esc_html( $harveys_merk ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<span class="galerij__cursor" aria-hidden="true">Bekijk</span>
</section>

<!-- Lichtbak: vergrote foto uit de galerij -->
<dialog class="lichtbak" id="lichtbak" aria-label="Foto uit de collectie">
	<div class="lichtbak__inhoud">
		<div class="lichtbak__beeld">
			<img id="lichtbak-beeld" alt="" />
		</div>
		<div class="lichtbak__tekst">
			<p class="lichtbak__nummer label"><span id="lichtbak-nummer">01</span> / <span id="lichtbak-totaal">01</span></p>
			<h3 class="lichtbak__titel" id="lichtbak-titel"></h3>
			<p class="lichtbak__groep" id="lichtbak-groep"></p>
			<div class="lichtbak__nav">
				<button class="lichtbak__pijl" type="button" data-richting="-1">Vorige</button>
				<button class="lichtbak__pijl" type="button" data-richting="1">Volgende</button>
			</div>
		</div>
	</div>
	<button class="lichtbak__sluit" type="button" aria-label="Sluiten" autofocus>Sluiten</button>
</dialog>
