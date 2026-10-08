<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Client minimale per la Telegram Bot API (https://core.telegram.org/bots/api#sendmessage).
 * Un bot va creato una tantum via @BotFather; il token è salvato in
 * `calypsosub_telegram_bot_token` e i gruppi predefiniti in `calypsosub_telegram_groups`.
 */
class Calypsosub_Telegram_Client {

	/**
	 * Manda $text (plain text) a un singolo chat_id.
	 *
	 * @return true|WP_Error
	 */
	public function send_message( string $chat_id, string $text ) {
		$token = trim( (string) get_option( 'calypsosub_telegram_bot_token', '' ) );
		if ( $token === '' ) {
			return new WP_Error( 'calypso_telegram_no_token', __( 'Token del bot Telegram non configurato.', 'calypsosub' ) );
		}
		if ( $chat_id === '' ) {
			return new WP_Error( 'calypso_telegram_no_chat_id', __( 'chat_id mancante.', 'calypsosub' ) );
		}

		$url     = "https://api.telegram.org/bot{$token}/sendMessage";
		$headers = [ 'Content-Type' => 'application/json' ];
		$body    = wp_json_encode( [
			'chat_id'                  => $chat_id,
			'text'                     => $text,
			'disable_web_page_preview' => true,
		] );

		$response = wp_remote_post( $url, [
			'timeout' => 15,
			'headers' => $headers,
			'body'    => $body,
		] );

		// The bot token lives in the URL path itself (Telegram's API has no
		// separate auth header) — masked here too, same as systeme.io's
		// X-API-Key, before it ever reaches the admin-visible log.
		$masked_url = self::mask_token_in_url( $url, $token );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				$response->get_error_code(),
				$response->get_error_message(),
				self::debug_data( 'POST', $masked_url, $headers, $body, null, null )
			);
		}

		$code      = wp_remote_retrieve_response_code( $response );
		$resp_body = wp_remote_retrieve_body( $response );
		$data      = json_decode( $resp_body, true );

		if ( $code !== 200 || empty( $data['ok'] ) ) {
			$desc = is_array( $data ) && isset( $data['description'] ) ? $data['description'] : 'HTTP ' . $code;
			return new WP_Error(
				'calypso_telegram_api_error',
				sprintf(
					/* translators: %s: Telegram API error description */
					__( 'Telegram API: %s', 'calypsosub' ), $desc
				),
				self::debug_data( 'POST', $masked_url, $headers, $body, $code, $resp_body )
			);
		}

		return true;
	}

	/** @param array<string,string> $headers */
	private static function debug_data( string $method, string $url, array $headers, ?string $body, ?int $response_code, ?string $response_body ): array {
		return [
			'method'        => $method,
			'url'           => $url,
			'headers'       => $headers,
			'body'          => $body,
			'response_code' => $response_code,
			'response_body' => null !== $response_body ? substr( $response_body, 0, 2000 ) : null,
		];
	}

	private static function mask_token_in_url( string $url, string $token ): string {
		return str_replace( $token, self::mask_secret( $token ), $url );
	}

	private static function mask_secret( string $value ): string {
		$len = strlen( $value );
		if ( $len <= 8 ) {
			return str_repeat( '•', $len );
		}
		return substr( $value, 0, 4 ) . str_repeat( '•', $len - 8 ) . substr( $value, -4 );
	}

	/**
	 * Manda $text a più chat_id. Non si ferma al primo errore: ogni gruppo
	 * tenta l'invio indipendentemente dagli altri.
	 *
	 * @param string[] $chat_ids
	 * @return array<string,true|WP_Error> esito per chat_id
	 */
	public function send_to_many( array $chat_ids, string $text ): array {
		$results = [];
		foreach ( $chat_ids as $chat_id ) {
			$results[ $chat_id ] = $this->send_message( $chat_id, $text );
		}
		return $results;
	}

	/**
	 * Rileva i gruppi/canali in cui il bot è stato aggiunto di recente,
	 * leggendo lo storico di `getUpdates` (Telegram lo conserva solo per un
	 * tempo limitato, tipicamente ~24h) — non è un vero elenco "tutti i chat
	 * del bot" perché l'API Telegram non lo espone, è ricostruito dagli
	 * eventi ricevuti. Legge soltanto (nessun offset inviato), quindi può
	 * essere richiamato più volte senza "consumare" gli update per
	 * nient'altro stia già usando getUpdates sullo stesso bot.
	 *
	 * @return array<int,array{id:string,title:string,type:string}>|WP_Error
	 */
	public function discover_groups() {
		$token = trim( (string) get_option( 'calypsosub_telegram_bot_token', '' ) );
		if ( $token === '' ) {
			return new WP_Error( 'calypso_telegram_no_token', __( 'Token del bot Telegram non configurato.', 'calypsosub' ) );
		}

		$allowed_updates = rawurlencode( wp_json_encode( [ 'message', 'channel_post', 'my_chat_member', 'chat_member' ] ) );
		$response = wp_remote_get( "https://api.telegram.org/bot{$token}/getUpdates?limit=100&allowed_updates={$allowed_updates}", [ 'timeout' => 15 ] );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $code !== 200 || empty( $data['ok'] ) ) {
			$desc = is_array( $data ) && isset( $data['description'] ) ? $data['description'] : 'HTTP ' . $code;
			return new WP_Error( 'calypso_telegram_api_error', sprintf(
				/* translators: %s: Telegram API error description */
				__( 'Telegram API: %s', 'calypsosub' ), $desc
			) );
		}

		$fields     = [ 'message', 'edited_message', 'channel_post', 'edited_channel_post', 'my_chat_member', 'chat_member' ];
		$found      = [];
		$superseded = [];

		foreach ( (array) ( $data['result'] ?? [] ) as $update ) {
			foreach ( $fields as $field ) {
				$chat = $update[ $field ]['chat'] ?? null;
				if ( ! $chat || empty( $chat['id'] ) ) continue;

				/*
				 * Un gruppo "base" promosso a supergruppo (es. nominando un admin)
				 * cambia id — Telegram manda un messaggio con migrate_to_chat_id sul
				 * VECCHIO id per segnalarlo. Quel vecchio id resta nello storico degli
				 * update ma non è più utilizzabile: va scartato, tiene solo il nuovo.
				 */
				if ( isset( $update[ $field ]['migrate_to_chat_id'] ) ) {
					$superseded[ (string) $chat['id'] ] = true;
				}

				if ( ! in_array( $chat['type'] ?? '', [ 'group', 'supergroup', 'channel' ], true ) ) continue;
				$found[ (string) $chat['id'] ] = [
					'id'    => (string) $chat['id'],
					'title' => (string) ( $chat['title'] ?? $chat['id'] ),
					'type'  => (string) $chat['type'],
				];
			}
		}

		foreach ( $superseded as $old_id => $_ ) {
			unset( $found[ $old_id ] );
		}

		/*
		 * Rete di sicurezza per la stessa migrazione gruppo→supergruppo: il
		 * messaggio migrate_to_chat_id sopra potrebbe non essere più nello
		 * storico recuperabile. Se due risultati hanno lo STESSO titolo e uno
		 * è 'group' (il formato superato) mentre l'altro è 'supergroup' o
		 * 'channel', scarta quello 'group' — è sempre lui quello vecchio.
		 */
		$titles_with_upgrade = [];
		foreach ( $found as $entry ) {
			if ( in_array( $entry['type'], [ 'supergroup', 'channel' ], true ) ) {
				$titles_with_upgrade[ $entry['title'] ] = true;
			}
		}
		foreach ( $found as $id => $entry ) {
			if ( $entry['type'] === 'group' && isset( $titles_with_upgrade[ $entry['title'] ] ) ) {
				unset( $found[ $id ] );
			}
		}

		return array_values( $found );
	}

	/**
	 * Converte il corpo HTML di una comunicazione in testo semplice per
	 * Telegram. Niente parse_mode HTML/Markdown: stare su plain text evita i
	 * problemi di escaping delle entità HTML viste altrove nel plugin (vedi
	 * memoria "no & in block JS") — qui il rischio analogo sarebbe un '&'
	 * letterale nel testo non convertito in '&amp;', che Telegram in modalità
	 * HTML rifiuterebbe con un errore di parsing.
	 */
	public function html_to_plain_text( string $html ): string {
		$text = preg_replace( '/<\s*br\s*\/?\s*>/i', "\n", $html );
		$text = preg_replace( '/<\s*\/p\s*>/i', "\n\n", (string) $text );
		$text = wp_strip_all_tags( (string) $text );
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( "/\n{3,}/", "\n\n", $text );
		return trim( (string) $text );
	}
}
