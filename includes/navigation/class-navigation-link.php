<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Pulsante unico "Apri sul navigatore" (pagine evento/uscita ed email).
 *
 * Il link punta a questo endpoint (?calypso_nav=<indirizzo>), che sceglie
 * l'app al momento del clic in base al dispositivo:
 * - Android: link geo:, che apre l'app di navigazione predefinita (Waze se
 *   l'utente l'ha scelta). Servito da una paginetta con ripiego automatico
 *   su Google Maps, perché alcuni browser/app di posta bloccano geo:.
 * - iPhone/iPad/Mac: Apple Maps.
 * - altro (computer): Google Maps nel browser.
 * Con &app=waze apre sempre Waze — su iPhone non c'è un modo affidabile per
 * rispettare un'eventuale app predefinita diversa da Apple Maps.
 *
 * Nessuna firma come in Calypsosub_Calendar_Download: le destinazioni sono
 * fisse (servizi mappe), l'indirizzo è solo testo di ricerca.
 */
class Calypsosub_Navigation_Link {

	private const QUERY_VAR = 'calypso_nav';

	public function init(): void {
		add_action( 'template_redirect', [ $this, 'maybe_redirect' ], 0 );
	}

	public static function url( string $address, string $app = '' ): string {
		$args = [ self::QUERY_VAR => trim( $address ) ];
		if ( $app !== '' ) $args['app'] = $app;
		return add_query_arg( array_map( 'rawurlencode', $args ), home_url( '/' ) );
	}

	/** Pulsante + link secondario "Apri con Waze"; stili nei template. */
	public static function render_button( string $address ): void {
		?>
		<a class="cso-icon-btn" href="<?php echo esc_url( self::url( $address ) ); ?>" rel="nofollow">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
			<span><?php esc_html_e( 'Apri sul navigatore', 'calypsosub' ); ?></span>
		</a>
		<a class="cso-icon-btn__alt" href="<?php echo esc_url( self::url( $address, 'waze' ) ); ?>" rel="nofollow"><?php esc_html_e( 'Usi Waze? Apri con Waze', 'calypsosub' ); ?></a>
		<?php
	}

	public function maybe_redirect(): void {
		if ( ! isset( $_GET[ self::QUERY_VAR ] ) ) return;

		$address = trim( sanitize_text_field( wp_unslash( $_GET[ self::QUERY_VAR ] ) ) );
		if ( $address === '' ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		$maps = calypso_maps_links( $address );
		$ua   = (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' );

		// La risposta dipende dal dispositivo: niente cache condivise.
		nocache_headers();
		header( 'Vary: User-Agent' );

		if ( ( $_GET['app'] ?? '' ) === 'waze' ) {
			wp_redirect( $maps['waze'] );
			exit;
		}
		if ( stripos( $ua, 'Android' ) !== false ) {
			$this->render_android_page( 'geo:0,0?q=' . rawurlencode( $address ), $maps['google'] );
			exit;
		}
		if ( preg_match( '/iPhone|iPad|iPod|Macintosh/i', $ua ) ) {
			wp_redirect( $maps['apple'] );
			exit;
		}
		wp_redirect( $maps['google'] );
		exit;
	}

	/**
	 * Apre geo: (app predefinita); se dopo un attimo la pagina è ancora in
	 * primo piano, il browser non l'ha gestito: si passa a Google Maps.
	 */
	private function render_android_page( string $geo_url, string $fallback_url ): void {
		header( 'Content-Type: text/html; charset=utf-8' );
		?>
<!doctype html>
<html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title><?php esc_html_e( 'Apertura navigatore…', 'calypsosub' ); ?></title></head>
<body style="font-family:sans-serif;text-align:center;padding:40px 20px">
<p><?php esc_html_e( 'Apertura del navigatore…', 'calypsosub' ); ?></p>
<p><a href="<?php echo esc_attr( $geo_url ); ?>"><?php esc_html_e( 'Apri con il navigatore predefinito', 'calypsosub' ); ?></a></p>
<p><a href="<?php echo esc_url( $fallback_url ); ?>"><?php esc_html_e( 'Apri in Google Maps', 'calypsosub' ); ?></a></p>
<script>
(function () {
	var geo = <?php echo wp_json_encode( $geo_url ); ?>;
	var fallback = <?php echo wp_json_encode( $fallback_url ); ?>;
	window.location.href = geo;
	setTimeout(function () {
		if (!document.hidden) window.location.replace(fallback);
	}, 1500);
})();
</script>
</body></html>
		<?php
	}
}
