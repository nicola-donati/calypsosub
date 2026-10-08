<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Client per l'invio di newsletter via API pubblica systeme.io
 * (https://developer.systeme.io/reference — autenticazione: header X-API-Key).
 *
 * Verificato contro lo schema OpenAPI pubblico e contro un invio reale il
 * 2026-10-08. Punti non ovvi dalla sola documentazione:
 * - Gli endpoint newsletter vivono sotto `/mailing/`, non alla radice:
 *   `/mailing/newsletters`.
 * - Il corpo di creazione annida subject/HTML dentro un oggetto `content`,
 *   non li mette come campi diretti.
 * - Il tag di targeting NON si imposta in creazione (quel payload non ha
 *   alcun campo per tag/segmento/lista): systeme.io lo richiede invece via
 *   un endpoint dedicato, `PUT /mailing/newsletters/{id}/included-tags`,
 *   che vuole ID numerici (`tagIds`), non nomi — da qui il giro in più per
 *   risolvere il nome del tag configurato in questo plugin sul suo id
 *   reale via `GET /api/tags?query=...`. Senza nessun tag assegnato,
 *   l'invio fallisce con HTTP 422 ("must have at least one tag").
 * - L'invio fallisce comunque con HTTP 422 se l'account systeme.io non ha
 *   un sender email verificato per le email marketing — questo va
 *   configurato nel pannello systeme.io stesso, nessuna API lo espone.
 */
class Calypsosub_SystemeIO_Client {

	private const API_BASE = 'https://api.systeme.io/api';

	/**
	 * Crea una newsletter, la restringe al tag indicato e la invia.
	 *
	 * @param string $subject
	 * @param string $body_html
	 * @param string $tag_name  Nome esatto del tag systeme.io dei destinatari.
	 * @return true|WP_Error
	 */
	public function send_newsletter( string $subject, string $body_html, string $tag_name ) {
		$api_key = trim( (string) get_option( 'calypsosub_systemeio_api_key', '' ) );
		if ( $api_key === '' ) {
			return new WP_Error( 'calypso_systemeio_no_key', __( 'API key systeme.io non configurata.', 'calypsosub' ) );
		}

		$tag_id = $this->resolve_tag_id( $tag_name, $api_key );
		if ( is_wp_error( $tag_id ) ) {
			return $tag_id;
		}

		$create = $this->request( 'POST', '/mailing/newsletters', $api_key, [
			'content' => [
				'subject'  => $subject,
				'bodyHtml' => $body_html,
			],
		] );
		if ( is_wp_error( $create ) ) {
			return $create;
		}

		$newsletter_id = $create['data']['id'] ?? null;
		if ( ! $newsletter_id ) {
			// The request succeeded (2xx) but the response didn't have the
			// shape this code expects. Still attach the actual response
			// here, or this failure mode is a dead end with nothing to
			// debug from.
			return new WP_Error( 'calypso_systemeio_no_id', __( 'systeme.io non ha restituito un ID newsletter.', 'calypsosub' ), $create['debug'] );
		}

		$tags = $this->request( 'PUT', "/mailing/newsletters/{$newsletter_id}/included-tags", $api_key, [
			'tagIds' => [ $tag_id ],
		] );
		if ( is_wp_error( $tags ) ) {
			return $tags;
		}

		// No request body: the send endpoint's schema doesn't accept or
		// need one, it just transitions the already-created newsletter to sent.
		$send = $this->request( 'POST', "/mailing/newsletters/{$newsletter_id}/send", $api_key, [] );
		if ( is_wp_error( $send ) ) {
			return $send;
		}

		return true;
	}

	/**
	 * Garantisce che un tag con questo nome esatto esista in systeme.io,
	 * creandolo se manca. Chiamato quando l'admin salva una categoria
	 * (Calypsosub_Admin_Menus), così un tag digitato a mano non resta "nel
	 * vuoto" fino al primo invio reale — a quel punto l'unico segnale sarebbe
	 * stato un HTTP 422 ("must have at least one tag") sulla comunicazione.
	 *
	 * @return true|WP_Error
	 */
	public function ensure_tag( string $tag_name ) {
		$api_key = trim( (string) get_option( 'calypsosub_systemeio_api_key', '' ) );
		if ( $api_key === '' ) {
			return new WP_Error( 'calypso_systemeio_no_key', __( 'API key systeme.io non configurata.', 'calypsosub' ) );
		}

		$id = $this->find_tag_id( $tag_name, $api_key );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		if ( null !== $id ) {
			return true;
		}

		$create = $this->request( 'POST', '/tags', $api_key, [ 'name' => $tag_name ] );
		if ( is_wp_error( $create ) ) {
			return $create;
		}

		return true;
	}

	/**
	 * @return int|WP_Error  Id numerico del tag, o errore (nessun tag con
	 *                       questo nome esatto — stesso identico tag che
	 *                       Calypsosub_Admin_Menus dovrebbe aver già creato
	 *                       via ensure_tag() al salvataggio della categoria —
	 *                       o la richiesta di ricerca stessa è fallita).
	 */
	private function resolve_tag_id( string $tag_name, string $api_key ) {
		$id = $this->find_tag_id( $tag_name, $api_key );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		if ( null === $id ) {
			return new WP_Error(
				'calypso_systemeio_tag_not_found',
				sprintf(
					/* translators: %s: configured tag name */
					__( 'Nessun tag systeme.io trovato con il nome "%s".', 'calypsosub' ), $tag_name
				)
			);
		}

		return $id;
	}

	/**
	 * @return int|null|WP_Error  Id del tag se trovato, null se nessun tag ha
	 *                            questo nome esatto, WP_Error se la ricerca
	 *                            stessa è fallita.
	 */
	private function find_tag_id( string $tag_name, string $api_key ) {
		$result = $this->request( 'GET', '/tags?query=' . rawurlencode( $tag_name ), $api_key, [] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		foreach ( (array) ( $result['data']['items'] ?? [] ) as $item ) {
			// "query" is a general search, non necessariamente un match
			// esatto — tiene solo il tag il cui nome corrisponde esattamente
			// (salvo maiuscole/minuscole) a quello cercato.
			if ( isset( $item['name'], $item['id'] ) && 0 === strcasecmp( (string) $item['name'], $tag_name ) ) {
				return (int) $item['id'];
			}
		}

		return null;
	}

	/**
	 * @return array{data:array,debug:array}|WP_Error  'data' è il corpo JSON
	 *               decodificato; 'debug' è sempre presente (anche su
	 *               successo) così un fallimento "logico" a valle — come un
	 *               200 senza il campo atteso — può comunque allegarlo al suo
	 *               WP_Error invece di restarne senza. Su errore, lo stesso
	 *               identico 'debug' è già dentro get_error_data().
	 */
	private function request( string $method, string $path, string $api_key, array $body ) {
		$url     = self::API_BASE . $path;
		$headers = [
			'Content-Type' => 'application/json',
			'X-API-Key'    => $api_key,
		];
		$body_json = $body ? wp_json_encode( $body ) : null;

		$response = wp_remote_request( $url, [
			'method'  => $method,
			'timeout' => 20,
			'headers' => $headers,
			'body'    => $body_json,
		] );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				$response->get_error_code(),
				$response->get_error_message(),
				self::debug_data( $method, $url, $headers, $body_json, null, null )
			);
		}

		$code      = wp_remote_retrieve_response_code( $response );
		$resp_body = wp_remote_retrieve_body( $response );
		$data      = json_decode( $resp_body, true );

		if ( $code < 200 || $code >= 300 ) {
			$msg = is_array( $data ) && isset( $data['message'] ) ? $data['message'] : $resp_body;
			return new WP_Error(
				'calypso_systemeio_api_error',
				sprintf(
					/* translators: 1: HTTP status code, 2: systeme.io error message/body */
					__( 'systeme.io API (HTTP %1$d): %2$s', 'calypsosub' ), $code, is_string( $msg ) ? $msg : wp_json_encode( $msg )
				),
				self::debug_data( $method, $url, $headers, $body_json, $code, $resp_body )
			);
		}

		return [
			'data'  => is_array( $data ) ? $data : [],
			'debug' => self::debug_data( $method, $url, $headers, $body_json, $code, $resp_body ),
		];
	}

	/** @param array<string,string> $headers */
	private static function debug_data( string $method, string $url, array $headers, ?string $body, ?int $response_code, ?string $response_body ): array {
		return [
			'method'        => $method,
			'url'           => $url,
			'headers'       => self::mask_headers( $headers ),
			'body'          => $body,
			'response_code' => $response_code,
			// Capped: a verbose/HTML error page from an upstream outage must
			// not bloat the comunicazione's post meta indefinitely.
			'response_body' => null !== $response_body ? substr( $response_body, 0, 2000 ) : null,
		];
	}

	/**
	 * Non mostra mai X-API-Key in chiaro nel log admin (visibile a chiunque
	 * possa modificare una comunicazione, non solo a chi ha configurato la
	 * chiave) — ne lascia visibili solo i primi/ultimi 4 caratteri, utile per
	 * verificare "è la chiave giusta?" senza esporla per intero.
	 *
	 * @param array<string,string> $headers
	 * @return array<string,string>
	 */
	private static function mask_headers( array $headers ): array {
		$out = [];
		foreach ( $headers as $name => $value ) {
			$out[ $name ] = 'x-api-key' === strtolower( $name ) ? self::mask_secret( (string) $value ) : $value;
		}
		return $out;
	}

	private static function mask_secret( string $value ): string {
		$len = strlen( $value );
		if ( $len <= 8 ) {
			return str_repeat( '•', $len );
		}
		return substr( $value, 0, 4 ) . str_repeat( '•', $len - 8 ) . substr( $value, -4 );
	}
}
