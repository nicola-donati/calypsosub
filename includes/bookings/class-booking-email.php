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
			wp_mail( $email, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
		}
	}

	// -------------------------------------------------------------------------
	// Internal
	// -------------------------------------------------------------------------

	private function send_to_user( int $booking_id, string $template_key ): void {
		$user_id = (int) get_post_meta( $booking_id, '_booking_user_id', true );
		$user    = get_user_by( 'id', $user_id );
		if ( ! $user ) return;

		$vars    = $this->build_vars( $booking_id );
		$subject = $this->get_template_subject( $template_key, $vars );
		$body    = $this->get_template_body( $template_key, $vars );

		wp_mail( $user->user_email, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
	}

	private function build_vars( int $booking_id ): array {
		$user_id = (int) get_post_meta( $booking_id, '_booking_user_id', true );
		$post_id = (int) get_post_meta( $booking_id, '_booking_post_id', true );
		$user    = get_user_by( 'id', $user_id );

		$post_type = get_post_type( $post_id );
		$titolo    = get_the_title( $post_id );

		if ( $post_type === 'calypso_occ_uscita' ) {
			$uscita_id  = (int) get_post_meta( $post_id, '_occorrenza_uscita_uscita_id', true );
			$luogo      = (string) get_post_meta( $uscita_id, '_uscita_luogo', true );
			$date_str   = (string) get_post_meta( $post_id, '_occorrenza_uscita_data', true );
			$prima_data = $date_str ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_str ) ) : '';
			$titolo     = $uscita_id ? get_the_title( $uscita_id ) : $titolo;
		} else {
			$luogo      = (string) get_post_meta( $post_id, '_evento_luogo', true );
			$date_raw   = (array) ( get_post_meta( $post_id, '_evento_date', true ) ?: [] );
			$prima_data = ! empty( $date_raw ) ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_raw[0] ) ) : '';
		}

		$accompagnatori = (int) get_post_meta( $booking_id, '_booking_companions', true );
		$allergie       = (string) get_post_meta( $booking_id, '_booking_allergies', true );
		$status         = (string) get_post_meta( $booking_id, '_booking_status', true );
		$booking_date   = (string) get_post_meta( $booking_id, '_booking_date', true );

		$account_page = get_option( 'calypsosub_account_page_id', 0 );

		return [
			'{nome_utente}'        => $user ? $user->display_name : '',
			'{email_utente}'       => $user ? $user->user_email : '',
			'{titolo_evento}'      => $titolo,
			'{data_evento}'        => $prima_data,
			'{luogo}'              => $luogo,
			'{num_accompagnatori}' => (string) $accompagnatori,
			'{allergie}'           => $allergie,
			'{stato_prenotazione}' => $status,
			'{link_area_personale}'=> $account_page ? get_permalink( $account_page ) : home_url(),
			'{data_prenotazione}'  => $booking_date,
		];
	}

	private function get_template_subject( string $key, array $vars ): string {
		$default = $this->default_templates()[ $key ]['subject'] ?? __( 'Notifica prenotazione', 'calypsosub' );
		$saved   = get_option( 'calypsosub_email_subject_' . $key, $default );
		return str_replace( array_keys( $vars ), array_values( $vars ), $saved );
	}

	private function get_template_body( string $key, array $vars ): string {
		$default = $this->default_templates()[ $key ]['body'] ?? '';
		$saved   = get_option( 'calypsosub_email_body_' . $key, $default );
		return str_replace( array_keys( $vars ), array_values( $vars ), $saved );
	}

	private function get_admin_recipients(): array {
		$raw = get_option( 'calypsosub_notification_emails', '' );
		if ( ! $raw ) return [];
		return array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	}

	public function default_templates(): array {
		return [
			'conferma_prenotazione' => [
				'subject' => __( 'Prenotazione confermata: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>La tua prenotazione per <strong>{titolo_evento}</strong> è confermata.</p>\n<p><strong>Data:</strong> {data_evento}<br><strong>Luogo:</strong> {luogo}<br><strong>Accompagnatori:</strong> {num_accompagnatori}</p>\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
			'prenotazione_annullata' => [
				'subject' => __( 'Prenotazione annullata: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>La tua prenotazione per <strong>{titolo_evento}</strong> è stata annullata.</p>\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
			'promosso_lista_attesa' => [
				'subject' => __( 'Posto disponibile: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>Si è liberato un posto per <strong>{titolo_evento}</strong>. La tua prenotazione è ora confermata!</p>\n<p><strong>Data:</strong> {data_evento}<br><strong>Luogo:</strong> {luogo}</p>\n<p><a href=\"{link_area_personale}\">Vedi i dettagli</a></p>", 'calypsosub' ),
			],
			'notifica_admin_prenotazione' => [
				'subject' => __( 'Nuova pre-prenotazione: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Nuova pre-prenotazione ricevuta — <strong>in attesa di conferma manuale</strong>.</p>\n<p><strong>Utente:</strong> {nome_utente} ({email_utente})<br><strong>Evento:</strong> {titolo_evento}<br><strong>Data:</strong> {data_evento}<br><strong>Accompagnatori:</strong> {num_accompagnatori}<br><strong>Allergie:</strong> {allergie}</p>", 'calypsosub' ),
			],
			'prenotazione_ricevuta' => [
				'subject' => __( 'Pre-prenotazione ricevuta: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>Abbiamo ricevuto la tua richiesta di partecipazione per <strong>{titolo_evento}</strong>.</p>\n<p>La tua pre-prenotazione è <strong>in attesa di conferma</strong> da parte dello staff. Riceverai una mail non appena sarà elaborata.</p>\n<p><strong>Data:</strong> {data_evento}<br><strong>Luogo:</strong> {luogo}<br><strong>Accompagnatori:</strong> {num_accompagnatori}</p>\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
			'prenotazione_rifiutata' => [
				'subject' => __( 'Aggiornamento prenotazione: {titolo_evento}', 'calypsosub' ),
				'body'    => __( "<p>Ciao {nome_utente},</p>\n<p>Purtroppo la tua prenotazione per <strong>{titolo_evento}</strong> non ha potuto essere confermata.</p>\n<p>Per maggiori informazioni contatta lo staff.</p>\n<p><a href=\"{link_area_personale}\">Gestisci le tue prenotazioni</a></p>", 'calypsosub' ),
			],
		];
	}
}
