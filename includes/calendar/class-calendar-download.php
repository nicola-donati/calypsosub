<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Pulsante unico "Aggiungi al calendario" per le pagine evento/uscita.
 *
 * Il link punta a questo endpoint (?calypso_ics=...) invece che a un data:
 * URI: un .ics servito come text/calendar apre direttamente il calendario
 * predefinito su iPhone/iPad/Mac (Apple Calendar) e su Windows (Outlook),
 * mentre i data: URI su iOS Safari non vengono aperti. Android non ha un
 * gestore .ics affidabile (spesso il file finisce solo nei download), quindi
 * lì si reindirizza a Google Calendar, che è il calendario di sistema.
 *
 * I dati dell'evento viaggiano nell'URL, firmati con wp_hash(): senza firma
 * chiunque potrebbe far servire a calypsosub.it un .ics con testo arbitrario.
 */
class Calypsosub_Calendar_Download {

	private const QUERY_VAR = 'calypso_ics';

	public function init(): void {
		add_action( 'template_redirect', [ $this, 'maybe_serve' ], 0 );
	}

	/**
	 * @param array{title:string,start:string,end?:string,location?:string,description?:string,duration?:float} $event
	 */
	public static function url( array $event ): string {
		$payload = rtrim( strtr( base64_encode( (string) wp_json_encode( [
			't' => $event['title'],
			's' => $event['start'],
			'e' => $event['end'] ?? '',
			'l' => $event['location'] ?? '',
			'd' => $event['description'] ?? '',
			'h' => (float) ( $event['duration'] ?? 2.0 ),
		] ) ), '+/', '-_' ), '=' );

		return add_query_arg( [ self::QUERY_VAR => $payload, 'sig' => self::sign( $payload ) ], home_url( '/' ) );
	}

	/** @param array{title:string,start:string,end?:string,location?:string,description?:string,duration?:float} $event */
	public static function render_button( array $event, string $modifier = '' ): void {
		?>
		<a class="cso-cal-btn<?php echo $modifier !== '' ? ' cso-cal-btn--' . esc_attr( $modifier ) : ''; ?>" href="<?php echo esc_url( self::url( $event ) ); ?>" rel="nofollow">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></svg>
			<span><?php esc_html_e( 'Aggiungi al calendario', 'calypsosub' ); ?></span>
		</a>
		<?php
	}

	private static function sign( string $payload ): string {
		return substr( wp_hash( self::QUERY_VAR . '|' . $payload ), 0, 20 );
	}

	public function maybe_serve(): void {
		if ( ! isset( $_GET[ self::QUERY_VAR ], $_GET['sig'] ) ) return;

		$payload = (string) wp_unslash( $_GET[ self::QUERY_VAR ] );
		if ( ! hash_equals( self::sign( $payload ), (string) wp_unslash( $_GET['sig'] ) ) ) {
			wp_die( esc_html__( 'Link al calendario non valido.', 'calypsosub' ), '', [ 'response' => 400 ] );
		}

		$data = json_decode( (string) base64_decode( strtr( $payload, '-_', '+/' ) ), true );
		if ( ! is_array( $data ) || empty( $data['t'] ) || empty( $data['s'] ) ) {
			wp_die( esc_html__( 'Link al calendario non valido.', 'calypsosub' ), '', [ 'response' => 400 ] );
		}

		$args = [ (string) $data['t'], (string) $data['s'], (string) ( $data['e'] ?? '' ), (string) ( $data['l'] ?? '' ), (string) ( $data['d'] ?? '' ), (float) ( $data['h'] ?? 2.0 ) ];

		// La risposta dipende dal dispositivo: niente cache condivise.
		nocache_headers();
		header( 'Vary: User-Agent' );

		$ua = (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' );
		if ( stripos( $ua, 'Android' ) !== false ) {
			wp_redirect( calypso_gcal_link( ...$args ) );
			exit;
		}

		$filename = ( sanitize_title( $args[0] ) ?: 'evento' ) . '.ics';
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: inline; filename="' . $filename . '"' );
		echo calypso_ics_content( ...$args ); // phpcs:ignore WordPress.Security.EscapeOutput -- file .ics, non HTML.
		exit;
	}
}
