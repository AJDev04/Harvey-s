<?php
/**
 * De homepage: alle secties onder elkaar. Een sectie zonder inhoud slaat zichzelf over.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
	<main id="main">
		<?php
		foreach ( array( 'hero', 'band', 'nieuws', 'collectie', 'merken', 'over', 'cadeaubon', 'contact', 'nieuwsbrief' ) as $harveys_deel ) {
			get_template_part( 'template-parts/sectie', $harveys_deel );
		}
		?>
	</main>
<?php
get_footer();
