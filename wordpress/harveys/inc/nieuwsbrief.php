<?php
/**
 * Nieuwsbrief: wie zich inschrijft, komt in de lijst Website → Inschrijvingen.
 * Daar kan je de lijst als CSV downloaden, bv. om in Mailchimp te importeren.
 * Lijst en export zijn enkel voor beheerders: het zijn persoonsgegevens.
 *
 * Tegen spam, zonder dat een bezoeker iets merkt:
 * - een verborgen veld (website) dat enkel een spambot invult;
 * - een tijdsval: het formulier draagt het tijdstip waarop de pagina gemaakt werd
 *   (ondertekend); wie binnen 2 seconden verstuurt, is geen mens;
 * - hoogstens 10 inschrijvingen per IP-adres per 10 minuten.
 * Een bot krijgt gewoon "bedankt", maar er wordt niets bewaard.
 *
 * Er staat bewust geen nonce op het formulier: de homepage wordt vaak gecachet, en
 * een nonce in een oude pagina is na een dag ongeldig. Dan zou een echte bezoeker
 * zich niet meer kunnen inschrijven.
 */

defined( 'ABSPATH' ) || exit;

const HARVEYS_INSCHRIJVING = 'harveys_inschrijving';

/** Wie de inschrijvingen mag zien, aanpassen en exporteren. */
const HARVEYS_INSCHRIJVING_RECHT = 'manage_options';

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
				// enkel een beheerder; nieuwe inschrijvingen komen enkel via het formulier
				'capabilities' => array(
					'edit_posts'             => HARVEYS_INSCHRIJVING_RECHT,
					'edit_others_posts'      => HARVEYS_INSCHRIJVING_RECHT,
					'edit_published_posts'   => HARVEYS_INSCHRIJVING_RECHT,
					'edit_private_posts'     => HARVEYS_INSCHRIJVING_RECHT,
					'publish_posts'          => HARVEYS_INSCHRIJVING_RECHT,
					'read_private_posts'     => HARVEYS_INSCHRIJVING_RECHT,
					'delete_posts'           => HARVEYS_INSCHRIJVING_RECHT,
					'delete_others_posts'    => HARVEYS_INSCHRIJVING_RECHT,
					'delete_published_posts' => HARVEYS_INSCHRIJVING_RECHT,
					'delete_private_posts'   => HARVEYS_INSCHRIJVING_RECHT,
					'create_posts'           => 'do_not_allow',
				),
				'map_meta_cap' => true,
				'show_in_rest' => false,
			)
		);
	}
);

/** Het tijdstip voor de tijdsval in het formulier, ondertekend: "tijd-handtekening". */
function harveys_nieuwsbrief_tijd() {
	$tijd = (string) time();
	return $tijd . '-' . substr( wp_hash( 'harveys_nieuwsbrief|' . $tijd, 'nonce' ), 0, 12 );
}

/** Ziet de inzending eruit als van een spambot? */
function harveys_nieuwsbrief_bot() {
	// phpcs:disable WordPress.Security.NonceVerification -- openbaar formulier, zie bovenaan
	if ( ! empty( $_POST['website'] ) ) {
		return true;
	}
	$veld = isset( $_POST['harveys_t'] ) ? sanitize_text_field( wp_unslash( $_POST['harveys_t'] ) ) : '';
	// phpcs:enable
	if ( ! preg_match( '/^(\d+)-([0-9a-f]{12})$/', $veld, $m ) ) {
		return true;
	}
	if ( ! hash_equals( substr( wp_hash( 'harveys_nieuwsbrief|' . $m[1], 'nonce' ), 0, 12 ), $m[2] ) ) {
		return true;
	}
	return time() - (int) $m[1] < 2;
}

/** Telt een inschrijving voor dit IP-adres; false als het er te veel zijn. */
function harveys_nieuwsbrief_binnen_limiet() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( '' === $ip ) {
		return true;
	}
	$sleutel = 'harveys_nb_' . substr( wp_hash( $ip ), 0, 20 );
	$aantal  = (int) get_transient( $sleutel );
	if ( $aantal >= 10 ) {
		return false;
	}
	set_transient( $sleutel, $aantal + 1, 10 * MINUTE_IN_SECONDS );
	return true;
}

/** Bewaart een adres, tenzij het er al is. Geeft true als het (nu) in de lijst staat. */
function harveys_nieuwsbrief_bewaar( $email ) {
	$bestaat = get_posts(
		array(
			'post_type'              => HARVEYS_INSCHRIJVING,
			'title'                  => $email,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( $bestaat ) {
		return true;
	}
	return (bool) wp_insert_post(
		array(
			'post_type'   => HARVEYS_INSCHRIJVING,
			'post_title'  => $email,
			'post_status' => 'publish',
		)
	);
}

/** Verwerkt het formulier; met JavaScript als JSON, zonder terug naar de pagina. */
function harveys_inschrijven() {
	// phpcs:disable WordPress.Security.NonceVerification -- openbaar formulier, zie bovenaan
	$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	$ajax  = ! empty( $_POST['ajax'] );
	// phpcs:enable

	$status = 200;
	if ( ! harveys_optie( 'nieuwsbrief_tonen' ) ) {
		$ok     = false; // de sectie staat uit: niets aannemen
		$status = 403;
	} elseif ( harveys_nieuwsbrief_bot() ) {
		$ok = true; // een bot krijgt "bedankt", maar er wordt niets bewaard
	} elseif ( ! is_email( $email ) || strlen( $email ) > 254 ) {
		$ok     = false;
		$status = 400;
	} elseif ( ! harveys_nieuwsbrief_binnen_limiet() ) {
		$ok     = false;
		$status = 429;
	} else {
		// een adres dat er al staat, krijgt ook "bedankt": zo verraadt het formulier niet wie al ingeschreven is
		$ok = harveys_nieuwsbrief_bewaar( $email );
		if ( ! $ok ) {
			$status = 500;
		}
	}

	if ( $ajax ) {
		$ok ? wp_send_json_success() : wp_send_json_error( null, $status );
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
		$scherm = get_current_screen();
		if ( 'top' !== $waar || ! $scherm || HARVEYS_INSCHRIJVING !== $scherm->post_type || ! current_user_can( HARVEYS_INSCHRIJVING_RECHT ) ) {
			return;
		}
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=harveys_export' ), 'harveys_export' );
		echo '<div class="alignleft actions"><a class="button button-primary" href="' . esc_url( $url ) . '">Download als CSV</a></div>';
	}
);

add_action(
	'admin_post_harveys_export',
	function () {
		if ( ! current_user_can( HARVEYS_INSCHRIJVING_RECHT ) || ! check_admin_referer( 'harveys_export' ) ) {
			wp_die( 'Geen toegang.', '', array( 'response' => 403 ) );
		}
		$rijen = get_posts(
			array(
				'post_type'      => HARVEYS_INSCHRIJVING,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="nieuwsbrief-' . gmdate( 'Y-m-d' ) . '.csv"' );
		header( 'X-Content-Type-Options: nosniff' );
		$uit = fopen( 'php://output', 'w' );
		fputcsv( $uit, array( 'E-mailadres', 'Ingeschreven op' ), ',', '"', '' );
		foreach ( $rijen as $rij ) {
			// een cel die met = + - @ begint, zou een spreadsheet als formule lezen
			$email = preg_match( '/^[=+\-@\t\r]/', $rij->post_title ) ? "'" . $rij->post_title : $rij->post_title;
			fputcsv( $uit, array( $email, get_the_date( 'Y-m-d H:i', $rij ) ), ',', '"', '' );
		}
		fclose( $uit ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);
