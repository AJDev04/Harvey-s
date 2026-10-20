<?php
/**
 * Startinhoud importeren (Website → Startinhoud): zet de inhoud van de
 * oorspronkelijke site van Harvey's in WordPress, zodat de site er meteen uitziet
 * zoals het ontwerp. Daarna pas je alles aan zoals gewoonlijk.
 *
 * Eerst komen de teksten, merken, het nieuwsbericht en de pagina's erin; daarna
 * het logo en de foto's, in kleine stappen (een trage server stopt anders
 * halverwege). Logo en foto's staan in starter/ van het thema (enkel in
 * harveys.zip); inc/startinhoud-data.php zegt wat waar hoort.
 */

defined( 'ABSPATH' ) || exit;

const HARVEYS_IMPORT = 'harveys_import';

function harveys_starter_map() {
	return HARVEYS_MAP . '/starter';
}

add_action(
	'admin_menu',
	function () {
		add_submenu_page( 'harveys-algemeen', 'Startinhoud', 'Startinhoud', 'edit_theme_options', 'harveys-startinhoud', 'harveys_import_pagina' );
	},
	20
);

function harveys_import_pagina() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( 'Je hebt geen toegang tot deze pagina.' );
	}
	$wachtrij = get_option( HARVEYS_IMPORT );
	$klaar    = get_option( 'harveys_import_klaar' );
	?>
	<div class="wrap">
		<h1>Startinhoud</h1>
		<?php if ( is_array( $wachtrij ) ) : ?>
			<p>De foto's worden geïmporteerd. Laat deze pagina open tot het klaar is.</p>
			<p><progress id="harveys-voortgang" max="<?php echo (int) $wachtrij['totaal']; ?>" value="<?php echo (int) ( $wachtrij['totaal'] - count( $wachtrij['taken'] ) ); ?>" style="width:24rem"></progress>
				<span id="harveys-teller"></span></p>
			<p id="harveys-melding"></p>
			<script>
				( function () {
					var balk = document.getElementById( 'harveys-voortgang' );
					var teller = document.getElementById( 'harveys-teller' );
					var melding = document.getElementById( 'harveys-melding' );
					function stap() {
						var data = new URLSearchParams( { action: 'harveys_import_stap', _ajax_nonce: <?php echo wp_json_encode( wp_create_nonce( 'harveys_import' ) ); ?> } );
						fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: data } )
							.then( function ( r ) { return r.json(); } )
							.then( function ( a ) {
								if ( ! a.success ) throw new Error( a.data || 'onbekende fout' );
								balk.value = a.data.gedaan;
								teller.textContent = a.data.gedaan + ' van ' + a.data.totaal;
								if ( a.data.klaar ) {
									melding.innerHTML = '<strong>Klaar.</strong> ' + ( a.data.mislukt ? a.data.mislukt + ' bestand(en) konden niet geïmporteerd worden. ' : '' ) +
										'<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Bekijk de site</a>';
								} else {
									stap();
								}
							} )
							.catch( function ( e ) {
								melding.textContent = 'Er ging iets mis (' + e.message + '). Herlaad de pagina om verder te gaan.';
							} );
					}
					stap();
				} )();
			</script>
		<?php else : ?>
			<?php if ( $klaar ) : ?>
				<div class="notice notice-success"><p>De startinhoud is geïmporteerd op <?php echo esc_html( wp_date( 'j F Y \o\m H:i', (int) $klaar ) ); ?>.</p></div>
			<?php endif; ?>
			<p style="max-width:48rem">Dit zet de inhoud van de oorspronkelijke site van Harvey's in WordPress: het logo, alle teksten en instellingen onder Website,
				de winkels en openingsuren, de foto's van de collectie en de winkels, de merken met hun logo,
				één nieuwsbericht en de pagina's Privacy en Disclaimer. Daarna pas je alles aan zoals gewoonlijk.</p>
			<p style="max-width:48rem"><strong>Let op:</strong> de instellingen onder Website worden overschreven. Een logo of icoontje dat al
				gekozen is, blijft staan. Bedoeld voor de eerste keer; nog eens importeren zet de foto's er een tweede keer bij.
				Voor een andere winkel importeer je dit niet, maar vul je alles zelf in.</p>
			<?php if ( ! is_dir( harveys_starter_map() ) ) : ?>
				<p><em>Deze versie van het thema bevat geen startinhoud (de map starter/ ontbreekt).</em></p>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="harveys_import" />
					<?php wp_nonce_field( 'harveys_import' ); ?>
					<?php submit_button( $klaar ? 'Opnieuw importeren' : 'Startinhoud importeren', 'primary', 'submit', false, $klaar ? array( 'onclick' => "return confirm('De foto\\'s komen er dan een tweede keer bij. Toch importeren?')" ) : array() ); ?>
				</form>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/** Zet een bestand uit starter/ in de mediabibliotheek; geeft de id, of 0. */
function harveys_import_bestand( $relatief, $titel, $alt = '', $bijschrift = '' ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$bron = harveys_starter_map() . '/' . $relatief;
	if ( ! is_readable( $bron ) ) {
		return 0;
	}
	// media_handle_sideload verplaatst het bestand: werk met een kopie
	$tijdelijk = wp_tempnam( basename( $bron ) );
	if ( ! $tijdelijk || ! copy( $bron, $tijdelijk ) ) {
		return 0;
	}
	$id = media_handle_sideload(
		array(
			'name'     => basename( $bron ),
			'tmp_name' => $tijdelijk,
		),
		0,
		$titel,
		array( 'post_excerpt' => $bijschrift )
	);
	if ( is_wp_error( $id ) ) {
		@unlink( $tijdelijk ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	return (int) $id;
}

/** Een bericht van een bepaald type met deze titel, of 0. */
function harveys_import_zoek( $type, $titel ) {
	$ids = get_posts(
		array(
			'post_type'      => $type,
			'title'          => $titel,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/** Stap 1: alles behalve de foto's, en de lijst met foto's die nog moeten komen. */
add_action(
	'admin_post_harveys_import',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) || ! check_admin_referer( 'harveys_import' ) ) {
			wp_die( 'Geen toegang.' );
		}
		$data  = require HARVEYS_MAP . '/inc/startinhoud-data.php';
		$taken = array();

		// Het logo van Harvey's, als er nog geen eigen logo of icoontje gekozen is
		foreach ( $data['logo'] as $sleutel => $logo ) {
			if ( ! harveys_optie( $sleutel ) ) {
				$taken[] = array( 'soort' => 'logo', 'sleutel' => $sleutel, 'bestand' => $logo[0], 'titel' => $logo[1] );
			}
		}

		// Instellingen: de lijsten met foto's beginnen leeg en vullen zich bij het importeren
		$instellingen = array_merge(
			(array) get_option( HARVEYS_OPTIE, array() ),
			$data['instellingen'],
			array(
				'collectie_haar' => array(),
				'collectie_hem'  => array(),
				'over_fotos'     => array(),
				'hero_poster'    => '',
				'winkel1_foto'   => '',
				'winkel2_foto'   => '',
			)
		);
		if ( '' === get_option( 'blogdescription' ) ) {
			update_option( 'blogdescription', $data['ondertitel'] );
		}

		// Merken met hun groepen; het logo komt later
		harveys_maak_merkgroepen();
		foreach ( $data['merken'] as $i => $merk ) {
			list( $naam, $logo, $groepen ) = $merk;
			$id = harveys_import_zoek( HARVEYS_MERK, $naam );
			if ( ! $id ) {
				$id = wp_insert_post(
					array(
						'post_type'   => HARVEYS_MERK,
						'post_title'  => $naam,
						'post_status' => 'publish',
						'menu_order'  => ( $i + 1 ) * 10,
					)
				);
			}
			if ( $id ) {
				wp_set_object_terms(
					$id,
					array_map(
						function ( $g ) {
							return 'voor-' . $g;
						},
						$groepen
					),
					HARVEYS_MERKGROEP
				);
				if ( $logo && ! has_post_thumbnail( $id ) ) {
					$taken[] = array( 'soort' => 'merk', 'bestand' => $logo, 'titel' => 'Logo ' . $naam, 'post' => $id );
				}
			}
		}

		// Pagina's: Privacy (ook als privacypagina van WordPress) en Disclaimer
		foreach ( $data['paginas'] as $pagina ) {
			$id = harveys_import_zoek( 'page', $pagina['titel'] );
			if ( ! $id ) {
				$id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_title'   => $pagina['titel'],
						'post_content' => $pagina['inhoud'],
						'post_status'  => 'publish',
					)
				);
			}
			if ( $id && $pagina['privacy'] ) {
				update_option( 'wp_page_for_privacy_policy', $id );
			} elseif ( $id ) {
				$instellingen['disclaimer_pagina'] = $id;
			}
		}

		// Het nieuwsbericht
		$nieuws = $data['nieuws'];
		$id     = harveys_import_zoek( 'post', $nieuws['titel'] );
		if ( ! $id ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'post',
					'post_title'   => $nieuws['titel'],
					'post_content' => $nieuws['tekst'] ? '<!-- wp:paragraph --><p>' . esc_html( $nieuws['tekst'] ) . '</p><!-- /wp:paragraph -->' : '',
					'post_status'  => 'publish',
					'post_date'    => $nieuws['datum'],
				)
			);
		}
		if ( $id && ! has_post_thumbnail( $id ) ) {
			$taken[] = array( 'soort' => 'nieuws', 'bestand' => $nieuws['foto'], 'titel' => $nieuws['titel'], 'alt' => $nieuws['alt'], 'post' => $id );
		}

		update_option( HARVEYS_OPTIE, $instellingen );

		// Het voorbeeldbericht van een nieuwe WordPress-site weg, als niemand eraan kwam
		$hallo = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hallo && $hallo->post_modified === $hallo->post_date ) {
			wp_trash_post( $hallo->ID );
		}

		// De foto's
		$taken[] = array( 'soort' => 'poster', 'bestand' => $data['poster'], 'titel' => 'Hero' );
		foreach ( $data['winkelfotos'] as $i => $foto ) {
			$taken[] = array( 'soort' => 'winkel', 'n' => $i + 1, 'bestand' => $foto[0], 'titel' => $foto[1], 'alt' => $foto[1] );
		}
		foreach ( $data['over_fotos'] as $foto ) {
			$taken[] = array( 'soort' => 'over', 'bestand' => $foto[0], 'titel' => $foto[2], 'alt' => $foto[1], 'bijschrift' => $foto[2] );
		}
		foreach ( $data['collectie'] as $groep => $fotos ) {
			foreach ( $fotos as $foto ) {
				$taken[] = array( 'soort' => 'collectie', 'groep' => $groep, 'bestand' => $foto[0], 'titel' => $foto[1], 'bijschrift' => $foto[1], 'alt' => isset( $foto[2] ) ? $foto[2] : '' );
			}
		}

		update_option(
			HARVEYS_IMPORT,
			array(
				'taken'   => $taken,
				'totaal'  => count( $taken ),
				'mislukt' => 0,
			),
			false
		);
		wp_safe_redirect( admin_url( 'admin.php?page=harveys-startinhoud' ) );
		exit;
	}
);

/** Stap 2, herhaald: een paar foto's per keer, tot de lijst leeg is. */
add_action(
	'wp_ajax_harveys_import_stap',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) || ! check_ajax_referer( 'harveys_import', false, false ) ) {
			wp_send_json_error( 'geen toegang', 403 );
		}
		$wachtrij = get_option( HARVEYS_IMPORT );
		if ( ! is_array( $wachtrij ) ) {
			wp_send_json_error( 'er loopt geen import' );
		}
		$begin = microtime( true );

		while ( $wachtrij['taken'] && microtime( true ) - $begin < 12 ) {
			$taak = array_shift( $wachtrij['taken'] );
			$id   = harveys_import_bestand(
				$taak['bestand'],
				$taak['titel'],
				isset( $taak['alt'] ) ? $taak['alt'] : '',
				isset( $taak['bijschrift'] ) ? $taak['bijschrift'] : ''
			);
			if ( ! $id ) {
				$wachtrij['mislukt']++;
				continue;
			}
			$instellingen = (array) get_option( HARVEYS_OPTIE, array() );
			switch ( $taak['soort'] ) {
				case 'logo':
					$instellingen[ $taak['sleutel'] ] = $id;
					break;
				case 'merk':
				case 'nieuws':
					set_post_thumbnail( $taak['post'], $id );
					break;
				case 'poster':
					$instellingen['hero_poster'] = $id;
					break;
				case 'winkel':
					$instellingen[ 'winkel' . $taak['n'] . '_foto' ] = $id;
					break;
				case 'over':
					$instellingen['over_fotos'][] = $id;
					break;
				case 'collectie':
					$instellingen[ 'collectie_' . $taak['groep'] ][] = $id;
					break;
			}
			update_option( HARVEYS_OPTIE, $instellingen );
			// de wachtrij na elke foto bewaren: valt de server weg, dan gaat het verder waar het was
			update_option( HARVEYS_IMPORT, $wachtrij, false );
		}

		$klaar = ! $wachtrij['taken'];
		if ( $klaar ) {
			delete_option( HARVEYS_IMPORT );
			update_option( 'harveys_import_klaar', time() );
		} else {
			update_option( HARVEYS_IMPORT, $wachtrij, false );
		}
		wp_send_json_success(
			array(
				'klaar'   => $klaar,
				'gedaan'  => $wachtrij['totaal'] - count( $wachtrij['taken'] ),
				'totaal'  => $wachtrij['totaal'],
				'mislukt' => $wachtrij['mislukt'],
			)
		);
	}
);
