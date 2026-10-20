<?php
/**
 * Pagina niet gevonden.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
	<main id="main">
		<section class="sectie reveal" aria-labelledby="pagina-titel">
			<div class="container container--smal tekstpagina">
				<div class="sectie__kop">
					<h1 id="pagina-titel">Niet gevonden</h1>
				</div>
				<div class="tekstpagina__inhoud">
					<p>Deze pagina bestaat niet (meer).</p>
					<p><a class="pil-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">Naar de homepage</a></p>
				</div>
			</div>
		</section>
	</main>
<?php
get_footer();
