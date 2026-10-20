<?php
/**
 * Nieuwsbrief: wie zich inschrijft, komt in de lijst Website → Inschrijvingen.
 * Daar kan je de lijst als CSV downloaden, bv. om in Mailchimp te importeren.
 * Een verborgen veld houdt de meeste spambots tegen.
 */

defined( 'ABSPATH' ) || exit;

const HARVEYS_INSCHRIJVING = 'harveys_inschrijving';

add_action(
	'init',
	function () {
		register_post_type(
			HARVEYS_INSCHRIJVING,
			array(
				'labels'       => array(
					'name'          => 'Inschrijvingen nieuwsbrief',
					'singular_name' => 'Inschrijving',
					'menu_name'     => 'Inschrijvingen',
					'all_items'     => 'Inschrijvingen',
					'search_items'  => 'Zoeken',
					'not_found'     => 'Nog geen inschrijvingen.',
					'edit_item'     => 'Inschrijving',
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'harveys-algemeen',
				'supports'     => array( 'title' ),
				'capabilities' => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap' => true,
				'show_in_rest' => false,
			)
		);
	}
);

/** Verwerkt het formulier; met JavaScript als JSON, zonder terug naar de pagina. */
function harveys_inschrijven() {
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$bot   = ! empty( $_POST['website'] );
	$ajax  = ! empty( $_POST['ajax'] );

	$ok = false;
	if ( $bot ) {
		$ok = true; // een bot krijgt "bedankt", maar er wordt niets bewaard
	} elseif ( is_email( $email ) ) {
		$bestaat = get_posts(
			array(
				'post_type'      => HARVEYS_INSCHRIJVING,
				'title'          => $email,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		$ok = $bestaat || wp_insert_post(
			array(
				'post_type'   => HARVEYS_INSCHRIJVING,
				'post_title'  => $email,
				'post_status' => 'publish',
			)
		);
	}

	if ( $ajax ) {
		$ok ? wp_send_json_success() : wp_send_json_error( null, 400 );
	}
	$terug = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$terug = remove_query_arg( 'nieuwsbrief', strtok( $terug, '#' ) );
	wp_safe_redirect( add_query_arg( 'nieuwsbrief', $ok ? 'ok' : 'fout', $terug ) . '#nieuwsbrief' );
	exit;
}
add_action( 'admin_post_nopriv_harveys_inschrijven', 'harveys_inschrijven' );
add_action( 'admin_post_harveys_inschrijven', 'harveys_inschrijven' );

/* ---------- Exporteren ---------- */

add_action(
	'manage_posts_extra_tablenav',
	function ( $waar ) {
		if ( 'top' !== $waar || get_current_screen()->post_type !== HARVEYS_INSCHRIJVING ) {
			return;
		}
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=harveys_export' ), 'harveys_export' );
		echo '<div class="alignleft actions"><a class="button button-primary" href="' . esc_url( $url ) . '">Download als CSV</a></div>';
	}
);

add_action(
	'admin_post_harveys_export',
	function () {
		if ( ! current_user_can( 'edit_others_posts' ) || ! check_admin_referer( 'harveys_export' ) ) {
			wp_die( 'Geen toegang.' );
		}
		$rijen = get_posts(
			array(
				'post_type'      => HARVEYS_INSCHRIJVING,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="nieuwsbrief-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$uit = fopen( 'php://output', 'w' );
		fputcsv( $uit, array( 'E-mailadres', 'Ingeschreven op' ) );
		foreach ( $rijen as $rij ) {
			// een adres dat met = of + begint, zou een spreadsheet als formule lezen
			$email = preg_match( '/^[=+\-@]/', $rij->post_title ) ? "'" . $rij->post_title : $rij->post_title;
			fputcsv( $uit, array( $email, get_the_date( 'Y-m-d H:i', $rij ) ) );
		}
		fclose( $uit );
		exit;
	}
);
