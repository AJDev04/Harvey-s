<?php
/**
 * De pagina's onder Website in de admin: één tabblad per onderdeel van de site,
 * met de velden uit inc/instellingen.php.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		$tabs = harveys_tabbladen();
		add_menu_page( 'Website', 'Website', 'edit_others_posts', 'harveys-algemeen', 'harveys_beheer_pagina', 'dashicons-store', 3 );
		foreach ( $tabs as $sleutel => $tab ) {
			add_submenu_page( 'harveys-algemeen', $tab['titel'], $tab['titel'], $tab['recht'], 'harveys-' . $sleutel, 'harveys_beheer_pagina' );
		}
	}
);

// Inschrijvingen (een lijst, geen instellingen) onderaan in het menu Website
add_action(
	'admin_menu',
	function () {
		global $submenu;
		if ( empty( $submenu['harveys-algemeen'] ) ) {
			return;
		}
		$lijst = array();
		$rest  = array();
		foreach ( $submenu['harveys-algemeen'] as $item ) {
			if ( false !== strpos( $item[2], 'post_type=' . HARVEYS_INSCHRIJVING ) ) {
				$lijst[] = $item;
			} else {
				$rest[] = $item;
			}
		}
		$submenu['harveys-algemeen'] = array_merge( $rest, $lijst ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	},
	99
);

// "Berichten" heet hier "Nieuws": dat is waar ze op de site staan
add_filter(
	'post_type_labels_post',
	function ( $labels ) {
		$labels->name          = 'Nieuws';
		$labels->menu_name     = 'Nieuws';
		$labels->all_items     = 'Alle nieuwsberichten';
		$labels->add_new       = 'Nieuw bericht';
		$labels->add_new_item  = 'Nieuw nieuwsbericht';
		$labels->name_admin_bar = 'Nieuwsbericht';
		return $labels;
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( false === strpos( $hook, 'harveys-' ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'harveys-beheer', HARVEYS_URL . '/assets/beheer.js', array( 'jquery', 'jquery-ui-sortable' ), filemtime( HARVEYS_MAP . '/assets/beheer.js' ), true );
		wp_enqueue_style( 'harveys-beheer', HARVEYS_URL . '/assets/beheer.css', array(), filemtime( HARVEYS_MAP . '/assets/beheer.css' ) );
	}
);

/** Het tabblad van de pagina die nu open is. */
function harveys_beheer_tab() {
	$pagina = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
	$sleutel = substr( $pagina, strlen( 'harveys-' ) );
	$tabs    = harveys_tabbladen();
	return isset( $tabs[ $sleutel ] ) ? $sleutel : 'algemeen';
}

function harveys_beheer_pagina() {
	$tabs    = harveys_tabbladen();
	$sleutel = harveys_beheer_tab();
	$tab     = $tabs[ $sleutel ];
	if ( ! current_user_can( $tab['recht'] ) ) {
		wp_die( 'Je hebt geen toegang tot deze pagina.' );
	}
	?>
	<div class="wrap harveys-beheer">
		<h1>Website</h1>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $s => $t ) : ?>
				<?php if ( current_user_can( $t['recht'] ) ) : ?>
					<a class="nav-tab<?php echo $s === $sleutel ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=harveys-' . $s ) ); ?>"><?php echo esc_html( $t['titel'] ); ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
		<?php settings_errors(); ?>
		<?php if ( ! empty( $tab['uitleg'] ) ) : ?>
			<p class="harveys-uitleg"><?php echo wp_kses( $tab['uitleg'], array( 'a' => array( 'href' => array() ) ) ); ?></p>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( HARVEYS_OPTIE ); ?>
			<input type="hidden" name="<?php echo esc_attr( HARVEYS_OPTIE ); ?>[_tab]" value="<?php echo esc_attr( $sleutel ); ?>" />
			<?php
			$groepen = isset( $tab['groepen'] ) ? $tab['groepen'] : array( '' => $tab['velden'] );
			foreach ( $groepen as $groepnaam => $velden ) {
				if ( $groepnaam ) {
					echo '<h2>' . esc_html( $groepnaam ) . '</h2>';
				}
				echo '<table class="form-table" role="presentation">';
				foreach ( $velden as $veldsleutel => $veld ) {
					harveys_beheer_rij( $veldsleutel, $veld );
				}
				echo '</table>';
			}
			submit_button( 'Bewaren' );
			?>
		</form>
	</div>
	<?php
}

/** Eén rij in het formulier: label, veld en uitleg. */
function harveys_beheer_rij( $sleutel, $veld ) {
	$naam   = HARVEYS_OPTIE . '[' . $sleutel . ']';
	$id     = 'harveys-' . $sleutel;
	$waarde = harveys_optie( $sleutel );
	$label  = 'vinkje' === $veld['type'] ? '' : '<label for="' . esc_attr( $id ) . '">' . esc_html( $veld['label'] ) . '</label>';

	echo '<tr><th scope="row">' . $label . '</th><td>'; // phpcs:ignore WordPress.Security.EscapeOutput -- hierboven al ge-escaped

	switch ( $veld['type'] ) {
		case 'tekstvak':
			printf(
				'<textarea id="%s" name="%s" rows="%d" class="large-text">%s</textarea>',
				esc_attr( $id ),
				esc_attr( $naam ),
				isset( $veld['rijen'] ) ? (int) $veld['rijen'] : 4,
				esc_textarea( $waarde )
			);
			break;

		case 'url':
			printf( '<input type="url" id="%s" name="%s" value="%s" class="large-text" />', esc_attr( $id ), esc_attr( $naam ), esc_attr( $waarde ) );
			break;

		case 'kleur':
			printf(
				'<input type="color" id="%s" name="%s" value="%s" class="harveys-kleur" /> <code>%s</code>',
				esc_attr( $id ),
				esc_attr( $naam ),
				esc_attr( $waarde ),
				esc_html( $waarde )
			);
			if ( isset( $veld['standaard'] ) && strtolower( $waarde ) !== strtolower( $veld['standaard'] ) ) {
				printf( ' <button type="button" class="button-link harveys-kleur__terug" data-kleur="%1$s">Terug naar %1$s</button>', esc_attr( $veld['standaard'] ) );
			}
			break;

		case 'getal':
			printf(
				'<input type="number" id="%s" name="%s" value="%s" step="%s"%s%s class="small-text" /> %s',
				esc_attr( $id ),
				esc_attr( $naam ),
				esc_attr( $waarde ),
				esc_attr( isset( $veld['stap'] ) ? $veld['stap'] : '1' ),
				isset( $veld['min'] ) ? ' min="' . esc_attr( $veld['min'] ) . '"' : '',
				isset( $veld['max'] ) ? ' max="' . esc_attr( $veld['max'] ) . '"' : '',
				isset( $veld['na'] ) ? esc_html( $veld['na'] ) : ''
			);
			break;

		case 'vinkje':
			printf(
				'<label><input type="checkbox" id="%s" name="%s" value="1"%s /> %s</label>',
				esc_attr( $id ),
				esc_attr( $naam ),
				checked( (bool) $waarde, true, false ),
				esc_html( $veld['label'] )
			);
			break;

		case 'pagina':
			echo wp_dropdown_pages( // phpcs:ignore WordPress.Security.EscapeOutput -- WordPress escapet zelf
				array(
					'name'              => esc_attr( $naam ),
					'id'                => esc_attr( $id ),
					'selected'          => (int) $waarde,
					'show_option_none'  => '— Geen —',
					'option_none_value' => '',
					'echo'              => 0,
				)
			);
			break;

		case 'afbeelding':
		case 'svg':
			$voorbeeld = $waarde ? wp_get_attachment_image_url( (int) $waarde, 'medium' ) : '';
			// nog geen logo: zeg wat de site dan toont
			if ( ! $waarde && 'logo_naam' === $sleutel ) {
				echo '<p class="description harveys-standaard">Nu: de naam van de site in letters, ' . esc_html( get_bloginfo( 'name' ) ) . '.</p>';
			}
			printf(
				'<div class="harveys-media%s" data-type="%s"><div class="harveys-media__voorbeeld">%s</div><input type="hidden" id="%s" name="%s" value="%s" /><button type="button" class="button harveys-media__kies">%s</button> <button type="button" class="button-link harveys-media__weg">Verwijderen</button></div>',
				$voorbeeld ? ' heeft-waarde' : '',
				esc_attr( $veld['type'] ),
				$voorbeeld ? '<img src="' . esc_url( $voorbeeld ) . '" alt="" />' : '',
				esc_attr( $id ),
				esc_attr( $naam ),
				esc_attr( $waarde ),
				'svg' === $veld['type'] ? 'SVG kiezen of uploaden' : 'Foto kiezen of uploaden'
			);
			break;

		case 'galerij':
			$ids        = is_array( $waarde ) ? $waarde : array();
			$bijschrift = isset( $veld['bijschrift'] ) ? $veld['bijschrift'] : 'Merk';
			$met_video  = ! empty( $veld['video'] );
			$aantal     = array( 'foto' => 0, 'video' => 0 );
			echo '<div class="harveys-galerij" data-bijschrift="' . esc_attr( $bijschrift ) . '" data-video="' . ( $met_video ? '1' : '0' ) . '"><ul class="harveys-galerij__lijst">';
			foreach ( $ids as $foto ) {
				if ( $met_video && harveys_is_video( $foto ) ) {
					// een video: het stilstaand beeld als dat er is, anders het eerste beeld van de video zelf
					$poster    = harveys_video_poster( $foto, 'thumbnail' );
					$voorbeeld = '<video src="' . esc_url( wp_get_attachment_url( $foto ) . '#t=0.5' ) . '" muted playsinline preload="metadata"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . '></video>';
					$soort     = 'video';
				} else {
					$url = wp_get_attachment_image_url( $foto, 'thumbnail' );
					if ( ! $url ) {
						continue;
					}
					$voorbeeld = '<img src="' . esc_url( $url ) . '" alt="" />';
					$soort     = 'foto';
				}
				++$aantal[ $soort ];
				// een video is een beeld van in de winkel: daar hoort geen merk bij
				$veldje = 'video' === $soort ? '<span class="harveys-galerij__soort">Video in de winkel</span>' : sprintf(
					'<input type="text" name="harveys_bijschrift[%1$d]" value="%2$s" placeholder="%3$s" aria-label="%3$s" />',
					(int) $foto,
					esc_attr( wp_get_attachment_caption( $foto ) ),
					esc_attr( $bijschrift )
				);
				printf(
					'<li class="harveys-galerij__item" data-id="%1$d" data-soort="%2$s">%3$s%4$s<button type="button" class="harveys-galerij__weg" aria-label="Weghalen">×</button></li>',
					(int) $foto,
					esc_attr( $soort ),
					$voorbeeld, // phpcs:ignore WordPress.Security.EscapeOutput -- hierboven opgebouwd met esc_url
					$veldje // phpcs:ignore WordPress.Security.EscapeOutput -- hierboven ge-escaped
				);
			}
			printf(
				'</ul><input type="hidden" class="harveys-galerij__ids" id="%s" name="%s" value="%s" /><p><button type="button" class="button harveys-galerij__toevoegen">%s</button> <span class="harveys-galerij__aantal">%s</span></p><p class="harveys-galerij__melding" hidden></p></div>',
				esc_attr( $id ),
				esc_attr( $naam ),
				esc_attr( implode( ',', $ids ) ),
				esc_html( $met_video ? 'Foto\'s en video\'s toevoegen' : 'Foto\'s toevoegen' ),
				esc_html( harveys_galerij_telling( $aantal['foto'], $aantal['video'] ) )
			);
			break;

		case 'uren':
			$uren = is_array( $waarde ) && 7 === count( $waarde ) ? $waarde : array_fill( 0, 7, array( '', '' ) );
			echo '<table class="harveys-uren"><thead><tr><th></th><th>Van</th><th>Tot</th></tr></thead><tbody>';
			foreach ( harveys_dagen() as $d => $dag ) {
				printf(
					'<tr><th scope="row">%1$s</th><td><input type="time" name="%2$s[%3$d][van]" value="%4$s" aria-label="%1$s van" /></td><td><input type="time" name="%2$s[%3$d][tot]" value="%5$s" aria-label="%1$s tot" /></td></tr>',
					esc_html( ucfirst( $dag ) ),
					esc_attr( $naam ),
					(int) $d,
					esc_attr( $uren[ $d ][0] ),
					esc_attr( $uren[ $d ][1] )
				);
			}
			echo '</tbody></table>';
			break;

		default:
			printf( '<input type="text" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr( $id ), esc_attr( $naam ), esc_attr( $waarde ) );
	}

	if ( ! empty( $veld['hulp'] ) ) {
		echo '<p class="description">' . esc_html( $veld['hulp'] ) . '</p>';
	}
	echo '</td></tr>';
}

/** "3 foto's", of met video's erbij "3 foto's, 1 video" (de teller onder een galerij). */
function harveys_galerij_telling( $fotos, $videos ) {
	$tekst = 1 === (int) $fotos ? '1 foto' : (int) $fotos . ' foto\'s';
	if ( $videos ) {
		$tekst .= ', ' . ( 1 === (int) $videos ? '1 video' : (int) $videos . ' video\'s' );
	}
	return $tekst;
}
