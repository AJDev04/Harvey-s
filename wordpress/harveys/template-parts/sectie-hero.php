<?php
/**
 * Hero: het logo op het oranje vlak; bij het scrollen opent de video eronder
 * (js/main.js, setupHero en setupHeroVideo).
 */

defined( 'ABSPATH' ) || exit;

$harveys_video  = harveys_optie( 'hero_video' );
$harveys_poster = harveys_optie( 'hero_poster' ) ? wp_get_attachment_image_url( (int) harveys_optie( 'hero_poster' ), 'full' ) : '';
// een HLS-stream (.m3u8) speelt via hls.js; een gewoon bestand rechtstreeks
$harveys_bron = preg_match( '/\.m3u8(\?|$)/i', $harveys_video ) ? 'data-hls' : 'data-src';
$harveys_naam = get_bloginfo( 'name' );
$harveys_sub  = get_bloginfo( 'description' );
?>
<section class="hero" id="home" aria-label="<?php echo esc_attr( 'Welkom bij ' . $harveys_naam ); ?>">
	<div class="hero__plak">
		<div class="hero__venster">
			<?php if ( $harveys_video || $harveys_poster ) : ?>
				<video class="hero__video" autoplay muted loop playsinline preload="metadata"
					<?php echo $harveys_poster ? 'poster="' . esc_url( $harveys_poster ) . '"' : ''; ?>
					<?php echo $harveys_video ? esc_attr( $harveys_bron ) . '="' . esc_url( $harveys_video ) . '"' : ''; ?>
					aria-hidden="true" tabindex="-1"></video>
			<?php endif; ?>
		</div>
		<span class="hero__korrel" aria-hidden="true"></span>
		<div class="hero__inhoud">
			<h1 class="hero__logo">
				<span class="visually-hidden"><?php echo esc_html( $harveys_naam . ( $harveys_sub ? ' — ' . $harveys_sub : '' ) ); ?></span>
				<?php echo harveys_logo_samen( 'hero__logo-svg', true ); // phpcs:ignore WordPress.Security.EscapeOutput -- opgekuiste SVG ?>
			</h1>
		</div>
		<div class="hero__voet">
			<p class="hero__hoek label"><?php echo esc_html( harveys_optie( 'hero_links' ) ); ?></p>
			<p class="hero__hoek label"><?php echo esc_html( harveys_optie( 'hero_rechts' ) ); ?></p>
		</div>
	</div>
</section>
