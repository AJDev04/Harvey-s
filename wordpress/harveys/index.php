<?php
/**
 * Overzicht van berichten (bv. een archief of zoekresultaten). De homepage zelf is
 * front-page.php.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
	<main id="main">
		<section class="sectie reveal" aria-labelledby="pagina-titel">
			<div class="container container--smal tekstpagina">
				<div class="sectie__kop">
					<h1 id="pagina-titel"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
				</div>
				<div class="tekstpagina__inhoud">
					<?php if ( have_posts() ) : ?>
						<ul class="tekstpagina__lijst">
							<?php while ( have_posts() ) : the_post(); ?>
								<li>
									<time class="label" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</li>
							<?php endwhile; ?>
						</ul>
						<?php the_posts_pagination(); ?>
					<?php else : ?>
						<p>Hier is (nog) niets te zien.</p>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</main>
<?php
get_footer();
