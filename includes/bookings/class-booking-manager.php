<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Booking_Manager {

	private Calypsosub_Booking_Email $email;

	public function __construct( Calypsosub_Booking_Email $email ) {
		$this->email = $email;
	}

	public function init(): void {
		add_action( 'init',               [ $this, 'register_post_type' ] );
		add_action( 'wp_ajax_calypso_book',          [ $this, 'ajax_book' ] );
		add_action( 'wp_ajax_calypso_cancel_booking',[ $this, 'ajax_cancel' ] );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_prenotazione', [
			'label'           => __( 'Prenotazioni', 'calypsosub' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'calypsosub',
			'capability_type' => 'post',
			'supports'        => [ 'title' ],
		] );
	}

	// -------------------------------------------------------------------------
	// AJAX handlers
	// -------------------------------------------------------------------------

	public function ajax_book(): void {
		check_ajax_referer( 'calypso_book_nonce', 'nonce' );

		$post_id      = absint( $_POST['post_id'] ?? 0 );
		$accompagnatori = absint( $_POST['accompagnatori'] ?? 0 );
		$allergie     = sanitize_textarea_field( wp_unslash( $_POST['allergie'] ?? '' ) );

		if ( ! $post_id || ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => __( 'Accesso non autorizzato.', 'calypsosub' ) ] );
		}

		$user_id = get_current_user_id();
		$result  = $this->book( $post_id, $user_id, [
			'accompagnatori' => $accompagnatori,
			'allergie'       => $allergie,
		] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => __( 'Prenotazione confermata!', 'calypsosub' ), 'status' => $result ] );
	}

	public function ajax_cancel(): void {
		check_ajax_referer( 'calypso_cancel_nonce', 'nonce' );

		$booking_id = absint( $_POST['booking_id'] ?? 0 );

		if ( ! $booking_id || ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => __( 'Accesso non autorizzato.', 'calypsosub' ) ] );
		}

		$result = $this->cancel_booking( $booking_id, get_current_user_id() );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => __( 'Prenotazione annullata.', 'calypsosub' ) ] );
	}

	// -------------------------------------------------------------------------
	// Core logic
	// -------------------------------------------------------------------------

	/**
	 * Crea una prenotazione. Restituisce status string o WP_Error.
	 *
	 * @param int   $post_id
	 * @param int   $user_id
	 * @param array $data  { accompagnatori: int, allergie: string }
	 * @return string|WP_Error  'confermata'|'lista_attesa'|WP_Error
	 */
	public function book( int $post_id, int $user_id, array $data ): string|WP_Error {
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, [ 'calypso_uscita', 'calypso_evento' ], true ) ) {
			return new WP_Error( 'invalid_post', __( 'Tipo di contenuto non prenotabile.', 'calypsosub' ) );
		}

		// Prenotazione già esistente?
		if ( $this->user_has_booking( $post_id, $user_id ) ) {
			return new WP_Error( 'already_booked', __( 'Hai già una prenotazione per questo evento.', 'calypsosub' ) );
		}

		$status = $this->resolve_booking_status( $post_id );
		if ( is_wp_error( $status ) ) return $status;

		$booking_id = wp_insert_post( [
			'post_type'   => 'calypso_prenotazione',
			'post_title'  => sprintf( 'Prenotazione #%d — utente %d', $post_id, $user_id ),
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $booking_id ) ) return $booking_id;

		update_post_meta( $booking_id, '_booking_post_id',      $post_id );
		update_post_meta( $booking_id, '_booking_post_type',    $post_type );
		update_post_meta( $booking_id, '_booking_user_id',      $user_id );
		update_post_meta( $booking_id, '_booking_companions',   absint( $data['accompagnatori'] ?? 0 ) );
		update_post_meta( $booking_id, '_booking_allergies',    $data['allergie'] ?? '' );
		update_post_meta( $booking_id, '_booking_status',       $status );
		update_post_meta( $booking_id, '_booking_date',         current_time( 'mysql' ) );

		$this->email->send_booking_confirmed( $booking_id );
		$this->email->send_admin_notification( $booking_id );

		return $status;
	}

	/**
	 * Cancella una prenotazione. Promuove il primo in lista d'attesa se presente.
	 */
	public function cancel_booking( int $booking_id, int $user_id ): bool|WP_Error {
		$owner = (int) get_post_meta( $booking_id, '_booking_user_id', true );
		if ( $owner !== $user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'forbidden', __( 'Non autorizzato.', 'calypsosub' ) );
		}

		$current_status = get_post_meta( $booking_id, '_booking_status', true );
		if ( $current_status === 'annullata' ) {
			return new WP_Error( 'already_cancelled', __( 'Prenotazione già annullata.', 'calypsosub' ) );
		}

		update_post_meta( $booking_id, '_booking_status', 'annullata' );
		$this->email->send_booking_cancelled( $booking_id );

		// Promuovi primo in lista d'attesa
		if ( $current_status === 'confermata' ) {
			$post_id = (int) get_post_meta( $booking_id, '_booking_post_id', true );
			$this->promote_waitlist( $post_id );
		}

		return true;
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function resolve_booking_status( int $post_id ): string|WP_Error {
		$post_type   = get_post_type( $post_id );
		$meta_prefix = $post_type === 'calypso_uscita' ? '_uscita' : '_evento';
		$max         = get_post_meta( $post_id, $meta_prefix . '_max_partecipanti', true );

		if ( $max === '' || $max === null ) {
			return 'confermata';
		}

		$confirmed = $this->count_confirmed( $post_id );
		if ( $confirmed < (int) $max ) {
			return 'confermata';
		}

		$lista_attesa = (int) get_post_meta( $post_id, $meta_prefix . '_lista_attesa', true );
		if ( $lista_attesa ) {
			return 'lista_attesa';
		}

		return new WP_Error( 'full', __( 'Posti esauriti.', 'calypsosub' ) );
	}

	public function count_confirmed( int $post_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(p.ID)
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_pid ON p.ID = pm_pid.post_id
			   AND pm_pid.meta_key = '_booking_post_id' AND pm_pid.meta_value = %d
			 INNER JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id
			   AND pm_status.meta_key = '_booking_status' AND pm_status.meta_value = 'confermata'
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'",
			$post_id
		) );
	}

	public function count_waitlist( int $post_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(p.ID)
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_pid ON p.ID = pm_pid.post_id
			   AND pm_pid.meta_key = '_booking_post_id' AND pm_pid.meta_value = %d
			 INNER JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id
			   AND pm_status.meta_key = '_booking_status' AND pm_status.meta_value = 'lista_attesa'
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'",
			$post_id
		) );
	}

	public function get_remaining_spots( int $post_id ): int|null {
		$post_type   = get_post_type( $post_id );
		$meta_prefix = $post_type === 'calypso_uscita' ? '_uscita' : '_evento';
		$max         = get_post_meta( $post_id, $meta_prefix . '_max_partecipanti', true );

		if ( $max === '' || $max === null ) return null;

		return max( 0, (int) $max - $this->count_confirmed( $post_id ) );
	}

	public function user_has_booking( int $post_id, int $user_id ): bool {
		global $wpdb;
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(p.ID)
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_pid ON p.ID = pm_pid.post_id
			   AND pm_pid.meta_key = '_booking_post_id' AND pm_pid.meta_value = %d
			 INNER JOIN {$wpdb->postmeta} pm_uid ON p.ID = pm_uid.post_id
			   AND pm_uid.meta_key = '_booking_user_id' AND pm_uid.meta_value = %d
			 INNER JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id
			   AND pm_status.meta_key = '_booking_status'
			   AND pm_status.meta_value IN ('confermata','lista_attesa')
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'",
			$post_id,
			$user_id
		) );
		return $count > 0;
	}

	public function get_user_bookings( int $user_id, bool $active_only = false ): array {
		global $wpdb;
		$status_clause = $active_only
			? "AND pm_status.meta_value IN ('confermata','lista_attesa')"
			: '';

		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT p.ID
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_uid ON p.ID = pm_uid.post_id
			   AND pm_uid.meta_key = '_booking_user_id' AND pm_uid.meta_value = %d
			 INNER JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id
			   AND pm_status.meta_key = '_booking_status' {$status_clause}
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'
			 ORDER BY p.post_date DESC",
			$user_id
		) );

		return array_map( 'absint', $ids );
	}

	private function promote_waitlist( int $post_id ): void {
		global $wpdb;
		$next_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT p.ID
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_pid ON p.ID = pm_pid.post_id
			   AND pm_pid.meta_key = '_booking_post_id' AND pm_pid.meta_value = %d
			 INNER JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id
			   AND pm_status.meta_key = '_booking_status' AND pm_status.meta_value = 'lista_attesa'
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'
			 ORDER BY p.post_date ASC LIMIT 1",
			$post_id
		) );

		if ( $next_id ) {
			update_post_meta( (int) $next_id, '_booking_status', 'confermata' );
			$this->email->send_promoted_from_waitlist( (int) $next_id );
		}
	}
}
