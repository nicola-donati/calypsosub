<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Client per l'invio di newsletter via API pubblica systeme.io
 * (https://developer.systeme.io/reference — autenticazione: header X-API-Key).
 *
 * NB: la forma esatta del payload di /api/newsletters (nomi campi per
 * contenuto HTML, tag di targeting, invio immediato vs bozza) è ricostruita
 * dalla documentazione pubblica systeme.io, non da un test end-to-end con una
 * chiave reale — al primo invio vero, se l'API risponde con un errore di
 * validazione, il messaggio di errore restituito da systeme.io (loggato in
 * $result->get_error_message()) indica quale campo rinominare qui.
 */
class Calypsosub_SystemeIO_Client {

	private const API_BASE = 'https://api.systeme.io/api';

	/**
	 * Crea e invia subito una newsletter broadcast, targettizzata sui tag indicati.
	 *
	 * @param string   $subject
	 * @param string   $body_html
	 * @param string[] $include_tags  Nomi dei tag systeme.io dei contatti da includere.
	 * @return true|WP_Error
	 */
	public function send_newsletter( string $subject, string $body_html, array $include_tags ) {
		$api_key = trim( (string) get_option( 'calypsosub_systemeio_api_key', '' ) );
		if ( $api_key === '' ) {
			return new WP_Error( 'calypso_systemeio_no_key', __( 'API key systeme.io non configurata.', 'calypsosub' ) );
		}

		$create = $this->request( 'POST', '/newsletters', $api_key, [
			'subject'      => $subject,
			'content'      => $body_html,
			'includedTags' => array_values( $include_tags ),
		] );
		if ( is_wp_error( $create ) ) {
			return $create;
		}

		$newsletter_id = $create['data']['id'] ?? null;
		if ( ! $newsletter_id ) {
			// The request succeeded (2xx) but the response didn't have the
			// shape this code expects — exactly the case the class-level
			// comment warns about (payload never verified against a real
			// key). Still attach the actual response here, or this failure
			// mode is a dead end with nothing to debug from.
			return new WP_Error( 'calypso_systemeio_no_id', __( 'systeme.io non ha restituito un ID newsletter.', 'calypsosub' ), $create['debug'] );
		}

		$send = $this->request( 'POST', "/newsletters/{$newsletter_id}/send", $api_key, [] );
		if ( is_wp_error( $send ) ) {
			return $send;
		}

		return true;
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
