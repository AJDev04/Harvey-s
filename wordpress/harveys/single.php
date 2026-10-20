<?php
/**
 * Eén nieuwsbericht: datum, titel, foto en de volledige tekst.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
	<main id="main">
		<?php while ( have_posts() ) : the_post(); ?>
			<article class="sectie reveal" aria-labelledby="pagina-titel">
				<div class="container container--smal tekstpagina">
					<div class="sectie__kop">
						<time class="label tekstpagina__datum" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
						<h1 class="tekstpagina__titel" id="pagina-titel"><?php the_title(); ?></h1>
					</div>
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="beeld tekstpagina__beeld">
							<?php the_post_thumbnail( 'large', array( 'sizes' => '(min-width: 44rem) 44rem, 100vw' ) ); ?>
						</div>
					<?php endif; ?>
					<div class="tekstpagina__inhoud">
						<?php the_content(); ?>
					</div>
					<p><a class="pil-link" href="<?php echo esc_url( harveys_sectie_url( 'nieuws' ) ); ?>">← Terug naar het nieuws</a></p>
				</div>
			</article>
		<?php endwhile; ?>
	</main>
<?php
get_footer();
