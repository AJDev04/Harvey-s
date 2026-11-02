<?php
/**
 * Collectie: de foto's en video's van twee groepen, standaard Voor haar en Voor
 * hem (Website → Collectie). Met JavaScript worden de groepen twee tabbladen en
 * opent een foto of video in de lichtbak (js/galerij.js); zonder staan ze gewoon
 * onder elkaar. Een video speelt in het raster zonder geluid in een lus zodra ze
 * in beeld is (niet bij "minder beweging"), in de lichtbak met geluid.
 * Welke foto's groot zijn, rekent inc/galerij.php per groep uit, zodat het raster
 * op laptop en gsm vol is.
 */

defined( 'ABSPATH' ) || exit;

// elke groep als [naam, foto's]; een foto die niet meer in de mediabibliotheek staat, telt niet mee
$harveys_groepen = array();
foreach ( array( 'haar', 'hem' ) as $harveys_g ) {
	$harveys_fotos = harveys_fotos( 'collectie_' . $harveys_g, true );
	if ( $harveys_fotos ) {
		$harveys_groepen[] = array( harveys_optie( 'collectie_' . $harveys_g . '_titel' ), $harveys_fotos );
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
				<div class="galerij__raster galerij__raster--berekend">
					<?php
					$harveys_klassen = harveys_galerij_klassen( count( $harveys_fotos ) );
					foreach ( $harveys_fotos as $harveys_i => $harveys_foto ) :
						$harveys_video = harveys_is_video( $harveys_foto );
						// een video is een beeld van in de winkel: geen merk eronder, in de lichtbak "In de winkel"
						$harveys_merk  = $harveys_video ? '' : wp_get_attachment_caption( $harveys_foto );
						$harveys_kop   = $harveys_video ? 'In de winkel' : $harveys_merk;
						$harveys_naam  = $harveys_groep . ( $harveys_kop ? ' — ' . $harveys_kop : '' );
						if ( $harveys_video ) {
							// een video: zonder geluid, in een lus; js/galerij.js start ze pas als ze in beeld is
							$harveys_url    = wp_get_attachment_url( $harveys_foto );
							$harveys_poster = harveys_video_poster( $harveys_foto );
							$harveys_beeld  = sprintf(
								'<video class="galerij__video" src="%s"%s muted loop playsinline preload="metadata" aria-hidden="true"></video>',
								esc_url( $harveys_url . '#t=0.1' ),
								$harveys_poster ? ' poster="' . esc_url( $harveys_poster ) . '"' : ''
							);
							$harveys_data  = ' data-video="' . esc_url( $harveys_url ) . '" data-titel="' . esc_attr( 'Video in de winkel, ' . $harveys_groep ) . '"';
							$harveys_label = 'Bekijk video: ' . $harveys_naam;
						} else {
							// de alt-tekst uit de mediabibliotheek; de lichtbak toont ze bij de vergrote foto
							$harveys_beeld = harveys_beeld( $harveys_foto, 'large', array( 'sizes' => '(min-width: 64em) 25vw, (min-width: 48em) 33vw, 50vw' ) );
							$harveys_data  = ' data-groot="' . esc_url( wp_get_attachment_image_url( $harveys_foto, 'full' ) ) . '"';
							$harveys_label = 'Vergroot: ' . $harveys_naam;
						}
						$harveys_klasse = trim( 'galerij__item ' . ( $harveys_video ? 'galerij__item--video ' : '' ) . $harveys_klassen[ $harveys_i ] );
						?>
						<figure class="<?php echo esc_attr( $harveys_klasse ); ?>" data-merk="<?php echo esc_attr( $harveys_kop ); ?>" data-groep="<?php echo esc_attr( $harveys_groep ); ?>"<?php echo $harveys_data; // phpcs:ignore WordPress.Security.EscapeOutput -- hierboven ge-escaped ?>>
							<button class="galerij__knop" type="button" aria-label="<?php echo esc_attr( $harveys_label ); ?>">
								<?php echo $harveys_beeld; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_get_attachment_image of hierboven ge-escaped ?>
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

<!-- Lichtbak: vergrote foto of video uit de galerij -->
<dialog class="lichtbak" id="lichtbak" aria-label="Foto uit de collectie">
	<div class="lichtbak__inhoud">
		<div class="lichtbak__beeld">
			<img id="lichtbak-beeld" alt="" />
			<video id="lichtbak-video" controls playsinline preload="none" hidden></video>
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
