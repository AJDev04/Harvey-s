<?php
/**
 * Merken: elk merk is een bericht met een naam, een logo (de uitgelichte
 * afbeelding) en één of twee groepen (Voor haar, Voor hem). Zonder logo staat de
 * naam er in letters. De volgorde is het veld Volgorde (laag eerst), daarna de naam.
 */

defined( 'ABSPATH' ) || exit;

const HARVEYS_MERK      = 'harveys_merk';
const HARVEYS_MERKGROEP = 'harveys_merkgroep';

/** De groepen: slug => naam, in de volgorde van de site. */
function harveys_merkgroepen() {
	return array(
		'voor-haar' => 'Voor haar',
		'voor-hem'  => 'Voor hem',
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			HARVEYS_MERK,
			array(
				'labels'              => array(
					'name'                  => 'Merken',
					'singular_name'         => 'Merk',
					'menu_name'             => 'Merken',
					'add_new'               => 'Nieuw merk',
					'add_new_item'          => 'Nieuw merk',
					'edit_item'             => 'Merk bewerken',
					'new_item'              => 'Nieuw merk',
					'search_items'          => 'Merken zoeken',
					'not_found'             => 'Nog geen merken.',
					'not_found_in_trash'    => 'Geen merken in de prullenbak.',
					'all_items'             => 'Alle merken',
					'featured_image'        => 'Logo',
					'set_featured_image'    => 'Logo kiezen',
					'remove_featured_image' => 'Logo verwijderen',
					'use_featured_image'    => 'Als logo gebruiken',
					'item_published'        => 'Merk toegevoegd.',
					'item_updated'          => 'Merk bijgewerkt.',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 21,
				'menu_icon'           => 'dashicons-tag',
				'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
				'show_in_rest'        => false,
				'exclude_from_search' => true,
			)
		);

		register_taxonomy(
			HARVEYS_MERKGROEP,
			HARVEYS_MERK,
			array(
				'labels'            => array(
					'name'          => 'Groepen',
					'singular_name' => 'Groep',
					'menu_name'     => 'Groepen',
				),
				'public'            => false,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'hierarchical'      => true, // vinkjes in plaats van een tekstvak
				'show_admin_column' => true,
				'show_in_rest'      => false,
			)
		);
	}
);

/** Maakt de groepen aan als ze er nog niet zijn. */
function harveys_maak_merkgroepen() {
	foreach ( harveys_merkgroepen() as $slug => $naam ) {
		if ( ! term_exists( $slug, HARVEYS_MERKGROEP ) ) {
			wp_insert_term( $naam, HARVEYS_MERKGROEP, array( 'slug' => $slug ) );
		}
	}
}
add_action( 'after_switch_theme', 'harveys_maak_merkgroepen' );

/** De merken van een groep, in volgorde. */
function harveys_merken_van( $slug ) {
	return get_posts(
		array(
			'post_type'      => HARVEYS_MERK,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'tax_query'      => array(
				array(
					'taxonomy' => HARVEYS_MERKGROEP,
					'field'    => 'slug',
					'terms'    => $slug,
				),
			),
		)
	);
}

/* ---------- In de admin ---------- */

// Uitleg boven het formulier van een merk
add_action(
	'edit_form_after_title',
	function ( $post ) {
		if ( HARVEYS_MERK !== $post->post_type ) {
			return;
		}
		echo '<p class="description" style="margin:1em 0">Kies rechts het logo (SVG of PNG met een transparante achtergrond werkt het best) en de groep(en). '
			. 'Zonder logo staat de naam van het merk er in letters. Bij Volgorde bepaal je de plaats in de band: lager staat eerst.</p>';
	}
);

// Een kolom met het logo, en de lijst standaard in de volgorde van de site
add_filter(
	'manage_' . HARVEYS_MERK . '_posts_columns',
	function ( $kolommen ) {
		$nieuw = array();
		foreach ( $kolommen as $sleutel => $naam ) {
			if ( 'title' === $sleutel ) {
				$nieuw['harveys_logo'] = 'Logo';
			}
			$nieuw[ $sleutel ] = $naam;
		}
		$nieuw['harveys_volgorde'] = 'Volgorde';
		unset( $nieuw['date'] );
		return $nieuw;
	}
);

add_action(
	'manage_' . HARVEYS_MERK . '_posts_custom_column',
	function ( $kolom, $id ) {
		if ( 'harveys_logo' === $kolom ) {
			$logo = get_post_thumbnail_id( $id );
			echo $logo
				? wp_get_attachment_image( $logo, 'medium', false, array( 'style' => 'width:90px;height:40px;object-fit:contain' ) )
				: '<span aria-hidden="true">—</span>';
		} elseif ( 'harveys_volgorde' === $kolom ) {
			echo (int) get_post_field( 'menu_order', $id );
		}
	},
	10,
	2
);

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() && $query->is_main_query() && HARVEYS_MERK === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		}
	}
);
