<?php
/**
 * Nieuws: de nieuwste berichten (Nieuws in de admin), met foto, datum, titel en
 * een korte samenvatting. Is het bericht langer, dan volgt een link naar de rest.
 * Bij meer dan één bericht staan ze in een carrousel, het nieuwste eerst; de
 * bezoeker klikt of veegt zelf verder (js/main.js, setupCarrousel).
 */

defined( 'ABSPATH' ) || exit;

$harveys_nieuws = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, (int) harveys_optie( 'nieuws_aantal' ) ),
		'ignore_sticky_posts' => false,
		'no_found_rows'       => true,
	)
);
if ( ! $harveys_nieuws->have_posts() ) {
	return;
}
$harveys_aantal    = $harveys_nieuws->post_count;
$harveys_carrousel = $harveys_aantal > 1;
?>
<section class="sectie reveal" id="nieuws" aria-labelledby="nieuws-titel">
	<div class="container">
		<div class="sectie__kop">
			<h2 class="kop" id="nieuws-titel">Nieuws</h2>
		</div>
		<?php if ( $harveys_carrousel ) : ?>
			<div class="carrousel carrousel--nieuws" aria-roledescription="carrousel" aria-label="Nieuwsberichten">
				<div class="carrousel__spoor" tabindex="0">
		<?php else : ?>
			<div class="nieuws">
		<?php endif; ?>
			<?php
			while ( $harveys_nieuws->have_posts() ) :
				$harveys_nieuws->the_post();
				$harveys_tekst = has_excerpt() ? get_the_excerpt() : wp_strip_all_tags( strip_shortcodes( get_the_content() ) );
				$harveys_kort  = wp_trim_words( $harveys_tekst, 45 );
				// "Lees verder" als de tekst ingekort is, of als er naast de samenvatting nog meer tekst in het bericht staat
				$harveys_meer  = $harveys_kort !== trim( preg_replace( '/\s+/', ' ', $harveys_tekst ) )
					|| ( has_excerpt() && '' !== trim( wp_strip_all_tags( strip_shortcodes( get_the_content() ) ) ) );
				?>
				<?php if ( $harveys_carrousel ) : ?>
					<div class="carrousel__slide" role="group" aria-label="<?php echo esc_attr( sprintf( 'Bericht %d van %d', $harveys_nieuws->current_post + 1, $harveys_aantal ) ); ?>">
				<?php endif; ?>
				<article class="nieuws-item nieuws-item--uitgelicht<?php echo has_post_thumbnail() ? '' : ' nieuws-item--zonder-beeld'; ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="beeld beeld--4x5 beeld--onthul">
							<?php the_post_thumbnail( 'large', array( 'sizes' => '(min-width: 48em) 42vw, 100vw' ) ); ?>
						</div>
					<?php endif; ?>
					<div class="nieuws-item__tekst">
						<time class="label" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
						<h3><?php the_title(); ?></h3>
						<?php if ( '' !== trim( $harveys_kort ) ) : ?>
							<p><?php echo esc_html( $harveys_kort ); ?></p>
						<?php endif; ?>
						<?php if ( $harveys_meer ) : ?>
							<a class="pil-link nieuws-item__meer" href="<?php the_permalink(); ?>">Lees verder<span class="visually-hidden">: <?php the_title(); ?></span></a>
						<?php endif; ?>
					</div>
				</article>
				<?php if ( $harveys_carrousel ) : ?>
					</div>
				<?php endif; ?>
			<?php endwhile; ?>
		</div>
		<?php if ( $harveys_carrousel ) : ?>
				<div class="carrousel__balk">
					<p class="carrousel__teller label"><span class="carrousel__nu">01</span> / <?php echo esc_html( sprintf( '%02d', $harveys_aantal ) ); ?></p>
					<div class="carrousel__knoppen">
						<button type="button" data-richting="-1" aria-label="Vorig bericht">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 12H5m6-6-6 6 6 6" /></svg>
						</button>
						<button type="button" data-richting="1" aria-label="Volgend bericht">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
