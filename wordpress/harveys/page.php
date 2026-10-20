<?php
/**
 * Een gewone pagina (bv. Privacy of Disclaimer): titel en tekst in een smalle kolom.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
	<main id="main">
		<?php while ( have_posts() ) : the_post(); ?>
			<section class="sectie reveal" aria-labelledby="pagina-titel">
				<div class="container container--smal tekstpagina">
					<div class="sectie__kop">
						<h1 id="pagina-titel"><?php the_title(); ?></h1>
					</div>
					<div class="tekstpagina__inhoud">
						<?php the_content(); ?>
					</div>
				</div>
			</section>
		<?php endwhile; ?>
	</main>
<?php
get_footer();
