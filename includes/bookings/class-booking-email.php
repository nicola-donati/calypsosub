<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Booking_Email {

	public function send_booking_received( int $booking_id ): void {
		$this->send_to_user( $booking_id, 'prenotazione_ricevuta' );
	}

	public function send_booking_confirmed( int $booking_id ): void {
		$this->send_to_user( $booking_id, 'conferma_prenotazione' );
	}

	public function send_booking_rejected( int $booking_id ): void {
		$this->send_to_user( $booking_id, 'prenotazione_rifiutata' );
	}

	public function send_booking_cancelled( int $booking_id ): void {
		$this->send_to_user( $booking_id, 'prenotazione_annullata' );
	}

	public function send_promoted_from_waitlist( int $booking_id ): void {
		$this->send_to_user( $booking_id, 'promosso_lista_attesa' );
	}

	public function send_admin_notification( int $booking_id ): void {
		$recipients = $this->get_admin_recipients();
		if ( empty( $recipients ) ) return;

		$vars    = $this->build_vars( $booking_id );
		$subject = $this->get_template_subject( 'notifica_admin_prenotazione', $vars );
		$body    = $this->get_template_body( 'notifica_admin_prenotazione', $vars );

		foreach ( $recipients as $email ) {
			$this->send( $email, $subject, $body );
		}
	}

	// -------------------------------------------------------------------------
	// Internal
	// -------------------------------------------------------------------------

	/**
	 * Destinatario della mail: utente WP se loggato, altrimenti i dati
	 * (nome/email) inseriti nel form da un prenotante anonimo.
	 *
	 * @return array{name:string,email:string,is_user:bool}
	 */
	private function resolve_recipient( int $booking_id ): array {
		$user_id = (int) get_post_meta( $booking_id, '_booking_user_id', true );
		$user    = $user_id ? get_user_by( 'id', $user_id ) : false;
		if ( $user ) {
			return [ 'name' => $user->display_name, 'email' => $user->user_email, 'is_user' => true ];
		}

		$form_data = (array) get_post_meta( $booking_id, '_booking_form_data', true );
		$email = calypso_extract_email( $form_data );
		$name = '';
		foreach ( [ 'nome', 'name', 'your-name' ] as $key ) {
			if ( ! empty( $form_data[ $key ] ) ) { $name = (string) $form_data[ $key ]; break; }
		}
		if ( $name !== '' && ! empty( $form_data['cognome'] ) ) {
			$name .= ' ' . $form_data['cognome'];
		}

		return [ 'name' => $name, 'email' => $email, 'is_user' => false ];
	}

	private function send_to_user( int $booking_id, string $template_key ): void {
		$recipient = $this->resolve_recipient( $booking_id );
		if ( empty( $recipient['email'] ) || ! is_email( $recipient['email'] ) ) return;

		$vars    = $this->build_vars( $booking_id );
		$subject = $this->get_template_subject( $template_key, $vars );
		$body    = $this->get_template_body( $template_key, $vars );

		$this->send( $recipient['email'], $subject, $body );
	}

	/**
	 * wp_mail() con il mittente delle impostazioni (Impostazioni → Generali)
	 * invece di "WordPress": nome del sito se non impostato, indirizzo solo
	 * se impostato. I filtri valgono solo per questo invio, non per le
	 * altre email di WordPress.
	 */
	private function send( string $to, string $subject, string $body ): void {
		$name    = trim( (string) get_option( 'calypsosub_email_from_name', '' ) ) ?: wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$address = trim( (string) get_option( 'calypsosub_email_from_address', '' ) );

		$set_name    = static fn() => $name;
		$set_address = static fn( $default ) => is_email( $address ) ? $address : $default;
		add_filter( 'wp_mail_from_name', $set_name, 99 );
		add_filter( 'wp_mail_from', $set_address, 99 );

		wp_mail( $to, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );

		remove_filter( 'wp_mail_from_name', $set_name, 99 );
		remove_filter( 'wp_mail_from', $set_address, 99 );
	}

	private function build_vars( int $booking_id ): array {
		$recipient = $this->resolve_recipient( $booking_id );
		$post_id = (int) get_post_meta( $booking_id, '_booking_post_id', true );

		$post_type = get_post_type( $post_id );
		$titolo    = get_the_title( $post_id );

		$indirizzo = '';
		// Stessi dati di navigatore/calendario delle pagine singole (vedi
		// templates/single-calypso_uscita.php e single-calypso_evento.php).
		$maps_query = '';
		$cal_event  = null;
		if ( $post_type === 'calypso_occ_uscita' ) {
			$uscita_id  = (int) get_post_meta( $post_id, '_occorrenza_uscita_uscita_id', true );
			$luogo      = (string) get_post_meta( $uscita_id, '_uscita_luogo', true );
			$indirizzo  = (string) get_post_meta( $uscita_id, '_uscita_indirizzo', true );
			$ritrovo    = (string) get_post_meta( $uscita_id, '_uscita_ritrovo', true );
			$rientro    = trim( (string) get_post_meta( $uscita_id, '_uscita_rientro_previsto', true ) );
			$date_str   = (string) get_post_meta( $post_id, '_occorrenza_uscita_data', true );
			$prima_data = $date_str ? calypso_format_datetime( $date_str ) : '';
			$titolo     = $uscita_id ? get_the_title( $uscita_id ) : $titolo;
			$maps_query = $indirizzo;
			if ( $date_str !== '' ) {
				$end_dt = ( strlen( $date_str ) > 10 && preg_match( '/^([01]?\d|2[0-3]):[0-5]\d$/', $rientro ) )
					? substr( $date_str, 0, 10 ) . 'T' . $rientro
					: '';
				$cal_event = [
					'title'       => $titolo,
					'start'       => $date_str,
					'end'         => $end_dt,
					'location'    => $indirizzo ?: ( $ritrovo ?: $luogo ),
					'description' => (string) get_post_meta( $uscita_id, '_uscita_desc_breve', true ),
					'duration'    => 4.0,
				];
			}
		} elseif ( $post_type === 'calypso_corso' ) {
			// Un corso non ha date proprie: si usano la prossima edizione
			// (occorrenza) e il suo luogo, o il periodo indicativo del corso.
			// Niente calendario: un corso dura settimane, non è un singolo
			// appuntamento.
			$occ        = calypso_get_next_occorrenza( $post_id );
			$inizio     = $occ ? (string) get_post_meta( $occ->ID, '_occorrenza_data_inizio', true ) : '';
			$prima_data = $inizio !== '' ? calypso_format_datetime( $inizio ) : (string) get_post_meta( $post_id, '_corso_periodo', true );
			$luogo      = $occ ? (string) get_post_meta( $occ->ID, '_occorrenza_luogo', true ) : '';
			$maps_query = $luogo;
		} else {
			$luogo      = (string) get_post_meta( $post_id, '_evento_luogo', true );
			$indirizzo  = (string) get_post_meta( $post_id, '_evento_indirizzo', true );
			$date_raw   = (array) ( get_post_meta( $post_id, '_evento_date', true ) ?: [] );
			$prossima   = calypso_next_future_date( $date_raw );
			$prima_data = $prossima !== '' ? calypso_format_datetime( $prossima ) : '';
			$maps_query = $indirizzo ?: $luogo;
			if ( $prossima !== '' ) {
				$cal_event = [
					'title'       => $titolo,
					'start'       => $prossima,
					'location'    => $indirizzo ?: $luogo,
					'description' => (string) get_post_meta( $post_id, '_evento_sottotitolo', true ),
				];
			}
		}
		$nav_url  = $maps_query !== '' ? Calypsosub_Navigation_Link::url( $maps_query ) : '';
		$waze_url = $maps_query !== '' ? Calypsosub_Navigation_Link::url( $maps_query, 'waze' ) : '';
		$cal_url  = $cal_event ? Calypsosub_Calendar_Download::url( $cal_event ) : '';

		$accompagnatori = Calypsosub_Booking_Manager::get_companions( $booking_id );
		$allergie       = (string) get_post_meta( $booking_id, '_booking_allergies', true );
		$status         = (string) get_post_meta( $booking_id, '_booking_status', true );
		$booking_date   = (string) get_post_meta( $booking_id, '_booking_date', true );

		$account_page = get_option( 'calypsosub_account_page_id', 0 );
		$manage_link  = $account_page ? get_permalink( $account_page ) : home_url();
		if ( ! $recipient['is_user'] && $account_page ) {
			$guest_token = (string) get_post_meta( $booking_id, '_booking_guest_token', true );
			if ( $guest_token !== '' ) {
				$manage_link = add_query_arg( 'booking_token', $guest_token, $manage_link );
			}
		}

		return [
			'{nome_utente}'        => $recipient['name'],
			'{email_utente}'       => $recipient['email'],
			'{titolo_evento}'      => $titolo,
			'{data_evento}'        => $prima_data,
			'{luogo}'              => $luogo,
			'{indirizzo}'          => $indirizzo,
			'{num_accompagnatori}' => (string) $accompagnatori,
			'{allergie}'           => $allergie,
			'{stato_prenotazione}' => $status,
			'{link_area_personale}'=> $manage_link,
			'{data_prenotazione}'  => $booking_date,
			'{link_calendario}'    => $cal_url,
			'{link_navigatore}'    => $nav_url,
			'{link_waze}'          => $waze_url,
			'{pulsanti_navigatore_calendario}' => self::nav_calendar_buttons_html( $nav_url, $waze_url, $cal_url ),
		];
	}

	/**
	 * Blocco pronto "Aggiungi al calendario" + "Apri sul navigatore" per le
	 * email, come nelle pagine evento/uscita: un pulsante ciascuno, l'app
	 * giusta la scelgono gli endpoint al clic (vedi
	 * Calypsosub_Calendar_Download e Calypsosub_Navigation_Link). Solo stili
	 * inline e niente SVG (Gmail li toglie); vuoto se non c'è né un luogo né
	 * una data.
	 */
	private static function nav_calendar_buttons_html( string $nav_url, string $waze_url, string $cal_url ): string {
		$btn  = 'display:inline-block;padding:12px 22px;margin:0 8px 8px 0;border-radius:999px;background:#3473A5;color:#ffffff;text-decoration:none;font-weight:bold;font-size:15px';
		$html = '';
		if ( $cal_url !== '' ) {
			$html .= '<a href="' . esc_url( $cal_url ) . '" style="' . $btn . '">&#128197; ' . esc_html__( 'Aggiungi al calendario', 'calypsosub' ) . '</a>';
		}
		if ( $nav_url !== '' ) {
			$html .= '<a href="' . esc_url( $nav_url ) . '" style="' . $btn . '">&#128205; ' . esc_html__( 'Apri sul navigatore', 'calypsosub' ) . '</a>';
		}
		if ( $html === '' ) return '';
		$html = '<p style="margin:20px 0 4px">' . $html . '</p>';
		if ( $waze_url !== '' ) {
			$html .= '<p style="margin:0 0 16px;font-size:13px"><a href="' . esc_url( $waze_url ) . '" style="color:#3473A5">' . esc_html__( 'Usi Waze? Apri con Waze', 'calypsosub' ) . '</a></p>';
		}
		return $html;
	}

	private function get_template_subject( string $key, array $vars ): string {
		$default = $this->default_templates()[ $key ]['subject'] ?? __( 'Notifica prenotazione', 'calypsosub' );
		$saved   = get_option( 'calypsosub_email_subject_' . $key, $default );
		return str_replace( array_keys( $vars ), array_values( $vars ), $saved );
	}

	private function get_template_body( string $key, array $vars ): string {
		$default = $this->default_templates()[ $key ]['body'] ?? '';
		$saved   = get_option( 'calypsosub_email_body_' . $key, $default );
		return str_replace( array_keys( $vars ), array_values( $vars ), self::strip_empty_fields( $saved, $vars ) );
	}

	/**
	 * Toglie le righe "<strong>Etichetta:</strong> {variabile}" quando la
	 * variabile è vuota (es. luogo di un corso senza edizione), invece di
	 * lasciare "Luogo:" senza niente dopo. Toglie anche il <br> che la
	 * separava dalla riga accanto; un paragrafo rimasto vuoto sparisce.
	 */
	public static function strip_empty_fields( string $template, array $vars ): string {
		foreach ( $vars as $placeholder => $value ) {
			if ( trim( (string) $value ) !== '' ) continue;
			$field    = '<strong>[^<]*<\/strong>\s*' . preg_quote( $placeholder, '/' );
			$template = preg_replace( '/' . $field . '\s*<br\s*\/?>\s*/i', '', $template );
			$template = preg_replace( '/\s*<br\s*\/?>\s*' . $field . '/i', '', $template );
			$template = preg_replace( '/' . $field . '/i', '', $template );
		}
		return preg_replace( '/<p>\s*<\/p>\s*/i', '', $template );
	}

	private function get_admin_recipients(): array {
		$raw = get_option( 'calypsosub_notification_emails', '' );
		if ( ! $raw ) return [];
		return array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	}

	/**
	 * Tutte le email inviate dal plugin. Ogni voce compare come scheda in
	 * Calypso Sub → Template Email (Calypsosub_Email_Templates scorre questo
	 * array): una nuova email aggiunta qui è subito modificabile dal sito.
	 */
	public function default_templates(): array {
		return [
			'prenotazione_ricevuta' => [
				'label'       => __( 'Prenotazione ricevuta', 'calypsosub' ),
				'description' => __( 'Inviata a chi prenota subito dopo l\'invio del modulo, finché lo staff non conferma.', 'calypsosub' ),
				'subject' => __( 'Pre-prenotazione ricevuta: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>Abbiamo ricevuto la tua richiesta di partecipazione per <strong>{titolo_evento}</strong>.</p>\n<p>La tua pre-prenotazione è <strong>in attesa di conferma</strong> da parte dello staff. Riceverai una mail non appena sarà elaborata.</p>\n<p><strong>Data:</strong> {data_evento}<br><strong>Luogo:</strong> {luogo}<br><strong>Accompagnatori:</strong> {num_accompagnatori}</p>\n{pulsanti_navigatore_calendario}\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
			'conferma_prenotazione' => [
				'label'       => __( 'Conferma prenotazione', 'calypsosub' ),
				'description' => __( 'Inviata a chi ha prenotato quando lo staff conferma la prenotazione.', 'calypsosub' ),
				'subject' => __( 'Prenotazione confermata: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>La tua prenotazione per <strong>{titolo_evento}</strong> è confermata.</p>\n<p><strong>Data:</strong> {data_evento}<br><strong>Luogo:</strong> {luogo}<br><strong>Accompagnatori:</strong> {num_accompagnatori}</p>\n{pulsanti_navigatore_calendario}\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
			'prenotazione_rifiutata' => [
				'label'       => __( 'Prenotazione rifiutata', 'calypsosub' ),
				'description' => __( 'Inviata a chi ha prenotato quando lo staff rifiuta la prenotazione.', 'calypsosub' ),
				'subject' => __( 'Aggiornamento prenotazione: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>Purtroppo la tua prenotazione per <strong>{titolo_evento}</strong> non ha potuto essere confermata.</p>\n<p>Per maggiori informazioni contatta lo staff.</p>\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
			'prenotazione_annullata' => [
				'label'       => __( 'Prenotazione annullata', 'calypsosub' ),
				'description' => __( 'Inviata a chi ha prenotato quando la prenotazione viene annullata.', 'calypsosub' ),
				'subject' => __( 'Prenotazione annullata: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>La tua prenotazione per <strong>{titolo_evento}</strong> è stata annullata.</p>\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
			'promosso_lista_attesa' => [
				'label'       => __( 'Promosso da lista d\'attesa', 'calypsosub' ),
				'description' => __( 'Inviata a chi era in lista d\'attesa quando si libera un posto.', 'calypsosub' ),
				'subject' => __( 'Posto disponibile: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>Si è liberato un posto per <strong>{titolo_evento}</strong>. La tua prenotazione è ora confermata!</p>\n<p><strong>Data:</strong> {data_evento}<br><strong>Luogo:</strong> {luogo}</p>\n{pulsanti_navigatore_calendario}\n<p><a href=\"{link_area_personale}\">Vedi i dettagli</a></p>", 'calypsosub' ),
			],
			'notifica_admin_prenotazione' => [
				'label'       => __( 'Notifica admin', 'calypsosub' ),
				'description' => __( 'Inviata agli indirizzi indicati in "Email notifiche admin" a ogni nuova prenotazione.', 'calypsosub' ),
				'subject' => __( 'Nuova pre-prenotazione: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Nuova pre-prenotazione ricevuta — <strong>in attesa di conferma manuale</strong>.</p>\n<p><strong>Utente:</strong> {nome_utente} ({email_utente})<br><strong>Evento:</strong> {titolo_evento}<br><strong>Data:</strong> {data_evento}<br><strong>Accompagnatori:</strong> {num_accompagnatori}<br><strong>Allergie:</strong> {allergie}</p>", 'calypsosub' ),
			],
		];
	}
}
