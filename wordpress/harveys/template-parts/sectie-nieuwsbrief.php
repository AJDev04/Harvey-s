<?php
/**
 * Nieuwsbrief: het formulier bewaart het adres in Website → Inschrijvingen
 * (inc/nieuwsbrief.php, met de bescherming tegen spam). Met JavaScript zonder de
 * pagina te verlaten.
 */

defined( 'ABSPATH' ) || exit;

if ( ! harveys_optie( 'nieuwsbrief_tonen' ) ) {
	return;
}
// zonder JavaScript komt de bezoeker hier terug met ?nieuwsbrief=ok of =fout
$harveys_status  = isset( $_GET['nieuwsbrief'] ) ? sanitize_key( $_GET['nieuwsbrief'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$harveys_bericht = array(
	'ok'   => 'Bedankt. U hoort binnenkort van ons.',
	'fout' => 'Vul een geldig e-mailadres in.',
);
?>
<section class="sectie sectie--oranje reveal" id="nieuwsbrief" aria-labelledby="nieuwsbrief-titel">
	<div class="container nieuwsbrief">
		<div class="nieuwsbrief__tekst">
			<h2 class="kop" id="nieuwsbrief-titel"><?php echo esc_html( harveys_optie( 'nieuwsbrief_titel' ) ?: 'Nieuwsbrief' ); ?></h2>
			<?php if ( harveys_optie( 'nieuwsbrief_tekst' ) ) : ?>
				<p><?php echo esc_html( harveys_optie( 'nieuwsbrief_tekst' ) ); ?></p>
			<?php endif; ?>
		</div>
		<form class="nieuwsbrief__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
			<input type="hidden" name="action" value="harveys_inschrijven" />
			<input type="hidden" name="harveys_t" value="<?php echo esc_attr( harveys_nieuwsbrief_tijd() ); ?>" />
			<label class="veld">
				<span class="veld__label label">E-mailadres</span>
				<input type="email" name="email" autocomplete="email" required placeholder="Uw e-mailadres" />
			</label>
			<!-- verborgen voor mensen; een spambot vult het in -->
			<label class="visually-hidden" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off" /></label>
			<button class="knop magneet" type="submit">
				<span>Abonneren</span>
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
			</button>
			<p class="nieuwsbrief__bericht" role="status" aria-live="polite"><?php echo isset( $harveys_bericht[ $harveys_status ] ) ? esc_html( $harveys_bericht[ $harveys_status ] ) : ''; ?></p>
		</form>
	</div>
</section>
