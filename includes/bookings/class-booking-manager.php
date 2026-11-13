<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Booking_Manager {

	private Calypsosub_Booking_Email $email;

	public function __construct( Calypsosub_Booking_Email $email ) {
		$this->email = $email;
	}

	public function init(): void {
		add_action( 'init',                          [ $this, 'register_post_type' ] );
		add_action( 'wp_ajax_calypso_book',          [ $this, 'ajax_book' ] );
		add_action( 'wp_ajax_calypso_cancel_booking',        [ $this, 'ajax_cancel' ] );
		add_action( 'wp_ajax_nopriv_calypso_cancel_booking', [ $this, 'ajax_cancel' ] );
		add_action( 'add_meta_boxes',                [ $this, 'add_booking_meta_box' ] );
		add_action( 'save_post_calypso_prenotazione', [ $this, 'save_booking_edit' ] );
		add_action( 'admin_notices',                 [ $this, 'booking_edit_notices' ] );
		add_filter( 'post_updated_messages',         [ $this, 'booking_updated_messages' ] );
		add_action( 'admin_head',                    [ $this, 'booking_admin_css' ] );
		add_action( 'admin_action_calypso_confirm_booking', [ $this, 'handle_confirm_booking' ] );
		add_action( 'admin_action_calypso_reject_booking',  [ $this, 'handle_reject_booking' ] );
		add_filter( 'post_row_actions',              [ $this, 'booking_row_actions' ], 10, 2 );
		add_filter( 'manage_calypso_prenotazione_posts_columns',       [ $this, 'add_booking_columns' ] );
		add_action( 'manage_calypso_prenotazione_posts_custom_column', [ $this, 'render_booking_column' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_prenotazione', [
			'label'           => __( 'Prenotazioni', 'calypsosub' ),
			'labels'          => [
				'name'               => __( 'Prenotazioni', 'calypsosub' ),
				'singular_name'      => __( 'Prenotazione', 'calypsosub' ),
				'edit_item'          => __( 'Dettaglio prenotazione', 'calypsosub' ),
				'view_item'          => __( 'Vedi prenotazione', 'calypsosub' ),
				'search_items'       => __( 'Cerca prenotazioni', 'calypsosub' ),
				'not_found'          => __( 'Nessuna prenotazione trovata', 'calypsosub' ),
				'all_items'          => __( 'Prenotazioni', 'calypsosub' ),
			],
			// Le prenotazioni nascono solo dal modulo sul sito: niente
			// "Aggiungi nuova" in admin.
			'capabilities'    => [ 'create_posts' => 'do_not_allow' ],
			'map_meta_cap'    => true,
			'public'          => false,
			'show_ui'         => true,
			// Niente voce di menu: si gestiscono da Calypso Sub → Prenotazioni
			// (Calypsosub_Bookings_Admin); il dettaglio singolo resta
			// raggiungibile da lì.
			'show_in_menu'    => false,
			'capability_type' => 'post',
			'supports'        => [],
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

		wp_send_json_success( [ 'message' => __( 'Pre-prenotazione ricevuta! Riceverai una conferma dallo staff.', 'calypsosub' ), 'status' => $result ] );
	}

	public function ajax_cancel(): void {
		check_ajax_referer( 'calypso_cancel_nonce', 'nonce' );

		$booking_id = absint( $_POST['booking_id'] ?? 0 );
		$token      = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );

		// Un ospite (non loggato) può annullare solo con il token della
		// propria prenotazione — niente login richiesto in quel caso.
		if ( ! $booking_id || ( ! is_user_logged_in() && $token === '' ) ) {
			wp_send_json_error( [ 'message' => __( 'Accesso non autorizzato.', 'calypsosub' ) ] );
		}

		$result = $this->cancel_booking( $booking_id, get_current_user_id(), $token );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => __( 'Prenotazione annullata.', 'calypsosub' ) ] );
	}

	// -------------------------------------------------------------------------
	// Core logic
	// -------------------------------------------------------------------------

	/**
	 * Numero di accompagnatori dal form. Il campo CF7 non ha sempre lo
	 * stesso nome: il form eventi live lo chiama "altri_partecipanti", non
	 * "accompagnatori" — prima veniva salvato 0 (segnalato 2026-11-13).
	 * Un select/radio CF7 arriva come array di un solo valore.
	 */
	public static function companions_from_form( array $form_data ): int {
		foreach ( [ 'accompagnatori', 'altri_partecipanti' ] as $key ) {
			if ( ! isset( $form_data[ $key ] ) ) continue;
			$value = is_array( $form_data[ $key ] ) ? reset( $form_data[ $key ] ) : $form_data[ $key ];
			return absint( $value );
		}
		return 0;
	}

	/** Limite accompagnatori quando l'uscita non ne imposta uno proprio. */
	public const DEFAULT_MAX_COMPANIONS = 10;

	/**
	 * Accompagnatori ammessi per una prenotazione: il "Max accompagnatori per
	 * prenotazione" dell'uscita se impostato, altrimenti un tetto fisso. Da
	 * quando i posti contano le persone, senza un limite un solo modulo
	 * (anche anonimo) potrebbe occupare tutti i posti.
	 */
	public static function max_companions( int $post_id ): int {
		if ( get_post_type( $post_id ) === 'calypso_occ_uscita' ) {
			$max = get_post_meta( $post_id, '_occorrenza_uscita_max_accompagnatori', true );
			if ( $max !== '' && $max !== false && $max !== null ) return (int) $max;
		}
		return self::DEFAULT_MAX_COMPANIONS;
	}

	/**
	 * Accompagnatori di una prenotazione. Le prenotazioni fatte prima della
	 * 1.0.90 hanno salvato 0 anche se il form diceva altro (campo
	 * "altri_partecipanti" non letto): in quel caso si ricalcola dai dati
	 * del form, senza riscrivere nulla nel database.
	 */
	public static function get_companions( int $booking_id ): int {
		$saved = (int) get_post_meta( $booking_id, '_booking_companions', true );
		if ( $saved > 0 ) return $saved;
		$form_data = get_post_meta( $booking_id, '_booking_form_data', true );
		return is_array( $form_data ) ? self::companions_from_form( $form_data ) : 0;
	}

	/**
	 * Crea una prenotazione. Restituisce status string o WP_Error.
	 *
	 * @param int   $post_id
	 * @param int   $user_id
	 * @param array $form_data  Dati liberi dal form (CF7 o legacy AJAX).
	 *                          Le chiavi 'accompagnatori' (int) e 'allergie'
	 *                          (string) hanno significato speciale per la
	 *                          logica di capacità; tutto il resto viene
	 *                          salvato come-è in _booking_form_data.
	 * @return string|WP_Error  'confermata'|'lista_attesa'|'in_attesa'|WP_Error
	 */
	public function book( int $post_id, int $user_id, array $form_data ): string|WP_Error {
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, [ 'calypso_occ_uscita', 'calypso_evento', 'calypso_corso' ], true ) ) {
			return new WP_Error( 'invalid_post', __( 'Tipo di contenuto non prenotabile.', 'calypsosub' ) );
		}

		// Prenotazione già esistente? Per i prenotanti anonimi (user_id 0)
		// non c'è uno user_id su cui basarsi: si usa l'email del form.
		$guest_email = $user_id ? '' : calypso_extract_email( $form_data );
		if ( $user_id ) {
			if ( $this->user_has_booking( $post_id, $user_id ) ) {
				return new WP_Error( 'already_booked', __( 'Hai già una richiesta per questo elemento.', 'calypsosub' ) );
			}
		} elseif ( $guest_email !== '' && $this->guest_has_booking( $post_id, $guest_email ) ) {
			return new WP_Error( 'already_booked', __( 'Hai già una richiesta per questo elemento.', 'calypsosub' ) );
		}

		if ( $post_type === 'calypso_corso' ) {
			$status = 'in_attesa';
		} else {
			$companions = self::companions_from_form( $form_data );
			if ( $companions > self::max_companions( $post_id ) ) {
				return new WP_Error( 'too_many_companions', sprintf( __( 'Puoi indicare al massimo %d accompagnatori.', 'calypsosub' ), self::max_companions( $post_id ) ) );
			}
			$status = $this->resolve_booking_status( $post_id, 1 + $companions );
			if ( is_wp_error( $status ) ) return $status;
		}

		$booking_id = wp_insert_post( [
			'post_type'   => 'calypso_prenotazione',
			'post_title'  => sprintf( 'Prenotazione #%d — utente %d', $post_id, $user_id ),
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $booking_id ) ) return $booking_id;

		update_post_meta( $booking_id, '_booking_post_id',      $post_id );
		update_post_meta( $booking_id, '_booking_post_type',    $post_type );
		update_post_meta( $booking_id, '_booking_user_id',      $user_id );
		update_post_meta( $booking_id, '_booking_companions',   self::companions_from_form( $form_data ) );
		update_post_meta( $booking_id, '_booking_allergies',    $form_data['allergie'] ?? '' );
		update_post_meta( $booking_id, '_booking_status',       $status );
		update_post_meta( $booking_id, '_booking_date',         current_time( 'mysql' ) );
		update_post_meta( $booking_id, '_booking_form_data',    $form_data );

		if ( ! $user_id ) {
			update_post_meta( $booking_id, '_booking_guest_email', strtolower( $guest_email ) );
			update_post_meta( $booking_id, '_booking_guest_token', wp_generate_password( 32, false ) );
		}

		$this->email->send_booking_received( $booking_id );
		$this->email->send_admin_notification( $booking_id );

		return $status;
	}

	/**
	 * Cancella una prenotazione. Promuove il primo in lista d'attesa se presente.
	 */
	public function cancel_booking( int $booking_id, int $user_id, string $token = '' ): bool|WP_Error {
		$owner       = (int) get_post_meta( $booking_id, '_booking_user_id', true );
		$guest_token = (string) get_post_meta( $booking_id, '_booking_guest_token', true );

		// Owner loggato: user_id deve corrispondere. Ospite: serve il token
		// della propria prenotazione — "owner === 0" da solo non basta, o
		// un ospite qualsiasi potrebbe annullare la prenotazione di un altro.
		$is_owner = $owner !== 0 && $owner === $user_id;
		$is_guest = $owner === 0 && $guest_token !== '' && $token !== '' && hash_equals( $guest_token, $token );

		if ( ! $is_owner && ! $is_guest && ! current_user_can( 'calypsosub_manage' ) ) {
			return new WP_Error( 'forbidden', __( 'Non autorizzato.', 'calypsosub' ) );
		}

		$current_status = get_post_meta( $booking_id, '_booking_status', true );
		if ( $current_status === 'annullata' ) {
			return new WP_Error( 'already_cancelled', __( 'Prenotazione già annullata.', 'calypsosub' ) );
		}

		update_post_meta( $booking_id, '_booking_status', 'annullata' );
		$this->email->send_booking_cancelled( $booking_id );

		// Promozione automatica solo quando si libera un posto CONFERMATO
		// dallo staff: annullare una richiesta ancora da confermare non deve
		// poter confermare d'ufficio altre persone (la promozione salta la
		// conferma manuale).
		if ( $current_status === 'confermata' ) {
			$post_id = (int) get_post_meta( $booking_id, '_booking_post_id', true );
			$this->promote_waitlist( $post_id );
		}

		return true;
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Stato iniziale di una nuova prenotazione da $persons persone
	 * (prenotante + accompagnatori). Se non ci stanno nei posti liberi va in
	 * lista d'attesa quando è abilitata, altrimenti è rifiutata.
	 */
	private function resolve_booking_status( int $post_id, int $persons = 1 ): string|WP_Error {
		$remaining = $this->get_remaining_spots( $post_id );
		if ( $remaining === null || $persons <= $remaining ) {
			return 'in_attesa';
		}
		if ( self::waitlist_enabled( $post_id ) ) {
			return 'lista_attesa';
		}
		return new WP_Error( 'full', __( 'Posti esauriti.', 'calypsosub' ) );
	}

	public static function waitlist_enabled( int $post_id ): bool {
		$meta_prefix = get_post_type( $post_id ) === 'calypso_occ_uscita' ? '_occorrenza_uscita' : '_evento';
		return (bool) get_post_meta( $post_id, $meta_prefix . '_lista_attesa', true );
	}

	/** Posti massimi (persone) di un'uscita/evento, null = illimitati. */
	public static function get_capacity( int $post_id ): ?int {
		$post_type = get_post_type( $post_id );
		if ( $post_type === 'calypso_occ_uscita' ) {
			$max = get_post_meta( $post_id, '_occorrenza_uscita_posti', true );
		} elseif ( $post_type === 'calypso_evento' ) {
			$max = get_post_meta( $post_id, '_evento_max_partecipanti', true );
		} else {
			return null;
		}
		return ( $max === '' || $max === null || $max === false ) ? null : (int) $max;
	}

	/**
	 * ID delle prenotazioni di un elemento negli stati indicati, in ordine
	 * di arrivo.
	 *
	 * @param string[] $statuses
	 * @return int[]
	 */
	public function get_booking_ids( int $post_id, array $statuses ): array {
		return array_map( 'intval', get_posts( [
			'post_type'      => 'calypso_prenotazione',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'ASC',
			'meta_query'     => [
				[ 'key' => '_booking_post_id', 'value' => $post_id, 'type' => 'NUMERIC' ],
				[ 'key' => '_booking_status',  'value' => $statuses, 'compare' => 'IN' ],
			],
		] ) );
	}

	/** Persone di una prenotazione: prenotante + accompagnatori. */
	public static function get_people( int $booking_id ): int {
		return 1 + self::get_companions( $booking_id );
	}

	/**
	 * Persone che occupano posti (prenotazioni confermate o in attesa di
	 * conferma): ogni prenotazione conta 1 + i suoi accompagnatori.
	 */
	public function count_people( int $post_id ): int {
		return array_sum( array_map( [ self::class, 'get_people' ], $this->get_booking_ids( $post_id, [ 'confermata', 'in_attesa' ] ) ) );
	}

	/** Posti liberi in persone, null = illimitati. */
	public function get_remaining_spots( int $post_id ): int|null {
		$max = self::get_capacity( $post_id );
		if ( $max === null ) return null;
		return max( 0, $max - $this->count_people( $post_id ) );
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
			   AND pm_status.meta_value IN ('confermata','lista_attesa','in_attesa')
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'",
			$post_id,
			$user_id
		) );
		return $count > 0;
	}

	/**
	 * Equivalente di user_has_booking() per i prenotanti anonimi: non c'è
	 * uno user_id su cui basarsi, quindi il controllo duplicati usa l'email
	 * inserita nel form (salvata in _booking_guest_email, minuscola).
	 */
	public function guest_has_booking( int $post_id, string $email ): bool {
		if ( $email === '' ) return false;
		global $wpdb;
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(p.ID)
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_pid ON p.ID = pm_pid.post_id
			   AND pm_pid.meta_key = '_booking_post_id' AND pm_pid.meta_value = %d
			 INNER JOIN {$wpdb->postmeta} pm_email ON p.ID = pm_email.post_id
			   AND pm_email.meta_key = '_booking_guest_email' AND pm_email.meta_value = %s
			 INNER JOIN {$wpdb->postmeta} pm_status ON p.ID = pm_status.post_id
			   AND pm_status.meta_key = '_booking_status'
			   AND pm_status.meta_value IN ('confermata','lista_attesa','in_attesa')
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'",
			$post_id,
			strtolower( $email )
		) );
		return $count > 0;
	}

	/**
	 * ID della prenotazione associata a un token ospite (link "gestisci la
	 * tua prenotazione" nelle email), o 0 se non trovato/non valido.
	 */
	public function get_booking_by_guest_token( string $token ): int {
		if ( $token === '' ) return 0;
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			   AND pm.meta_key = '_booking_guest_token' AND pm.meta_value = %s
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'
			 LIMIT 1",
			$token
		) );
	}

	public function get_user_bookings( int $user_id, bool $active_only = false ): array {
		global $wpdb;
		$status_clause = $active_only
			? "AND pm_status.meta_value IN ('confermata','lista_attesa','in_attesa')"
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

	// -------------------------------------------------------------------------
	// Admin: meta box, row actions, columns, confirm/reject
	// -------------------------------------------------------------------------

	public function booking_admin_css(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'calypso_prenotazione' ) return;
		?>
		<style>
		#submitdiv,#slugdiv,#authordiv,#postexcerpt,#trackbacksdiv,#commentstatusdiv,
		#commentsdiv,#revisionsdiv,#post-body-content,.wp-editor-area,
		#titlediv,#edit-slug-box{display:none!important}
		#post-body.columns-2{display:block}
		#side-sortables{display:none!important}
		#normal-sortables{margin:0}
		#postbox-container-2{width:100%!important;float:none}
		#calypso_booking_detail .inside{padding:16px 20px}
		</style>
		<?php
	}

	public function add_booking_meta_box(): void {
		add_meta_box(
			'calypso_booking_detail',
			__( 'Dettaglio prenotazione', 'calypsosub' ),
			[ $this, 'render_booking_meta_box' ],
			'calypso_prenotazione',
			'normal',
			'high'
		);
	}

	public function render_booking_meta_box( WP_Post $post ): void {
		$status     = (string) get_post_meta( $post->ID, '_booking_status', true );
		$post_id    = (int)    get_post_meta( $post->ID, '_booking_post_id', true );
		$user_id    = (int)    get_post_meta( $post->ID, '_booking_user_id', true );
		$companions = self::get_companions( $post->ID );
		$date       = (string) get_post_meta( $post->ID, '_booking_date', true );
		$post_type  = (string) get_post_meta( $post->ID, '_booking_post_type', true );
		$user       = $user_id ? get_user_by( 'id', $user_id ) : false;
		$form_data  = get_post_meta( $post->ID, '_booking_form_data', true );
		$form_data  = is_array( $form_data ) ? $form_data : [];
		$updated_at = (string) get_post_meta( $post->ID, '_booking_updated', true );

		$email_display = $user ? $user->user_email : calypso_extract_email( $form_data );

		$titolo = get_the_title( $post_id );
		if ( $post_type === 'calypso_occ_uscita' ) {
			$date_str    = (string) get_post_meta( $post_id, '_occorrenza_uscita_data', true );
			$data_evento = $date_str ? calypso_format_datetime( $date_str ) : '—';
			$uscita_id   = (int) get_post_meta( $post_id, '_occorrenza_uscita_uscita_id', true );
			$titolo      = $uscita_id ? get_the_title( $uscita_id ) : $titolo;
		} elseif ( $post_type === 'calypso_evento' ) {
			$prossima    = calypso_next_future_date( (array) ( get_post_meta( $post_id, '_evento_date', true ) ?: [] ) );
			$data_evento = $prossima ? calypso_format_datetime( $prossima ) : '—';
		} else {
			$data_evento = '—';
		}

		$l = Calypsosub_Bookings_Admin::status_labels()[ $status ] ?? [ 'label' => $status, 'color' => '#444', 'bg' => '#eee' ];

		$confirm_url = wp_nonce_url( admin_url( 'admin.php?action=calypso_confirm_booking&booking_id=' . $post->ID ), 'calypso_confirm_' . $post->ID );
		$reject_url  = wp_nonce_url( admin_url( 'admin.php?action=calypso_reject_booking&booking_id='  . $post->ID ), 'calypso_reject_'  . $post->ID );
		$list_url    = Calypsosub_Bookings_Admin::page_url( $post_id ? [ 'item' => $post_id ] : [] );
		?>
		<style>
		.calpren-head{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-bottom:20px}
		.calpren-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:13px;font-weight:700}
		.calpren-head .button{margin-left:0}
		.calpren-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px 32px;margin-bottom:24px}
		.calpren-field label,.calpren-section{display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#787c82;margin-bottom:4px}
		.calpren-field .val{font-size:14px;color:#1d2327}
		.calpren-section{font-size:12px;color:#1d2327;border-bottom:1px solid #e5e7eb;padding-bottom:6px;margin:8px 0 14px}
		.calpren-form{display:grid;grid-template-columns:minmax(160px,240px) 1fr;gap:10px 20px;align-items:start;max-width:900px}
		.calpren-form > label{font-weight:600;padding-top:6px}
		.calpren-form input[type=text],.calpren-form input[type=email],.calpren-form input[type=number],.calpren-form select,.calpren-form textarea{width:100%;max-width:520px}
		.calpren-form .calpren-ro{padding-top:6px;color:#50575e}
		.calpren-save{display:flex;flex-wrap:wrap;align-items:center;gap:16px;margin-top:20px;padding-top:16px;border-top:1px solid #e5e7eb}
		@media(max-width:782px){.calpren-form{grid-template-columns:1fr}.calpren-form > label{padding-top:0}}
		</style>

		<p><a href="<?php echo esc_url( $list_url ); ?>">← <?php esc_html_e( 'Torna all\'elenco prenotazioni', 'calypsosub' ); ?></a></p>

		<div class="calpren-head">
			<span class="calpren-badge" style="background:<?php echo esc_attr( $l['bg'] ); ?>;color:<?php echo esc_attr( $l['color'] ); ?>"><?php echo esc_html( $l['label'] ); ?></span>
			<?php if ( in_array( $status, [ 'in_attesa', 'lista_attesa' ], true ) ) : ?>
				<a href="<?php echo esc_url( $confirm_url ); ?>" class="button button-primary"><?php esc_html_e( '✓ Conferma prenotazione', 'calypsosub' ); ?></a>
				<a href="<?php echo esc_url( $reject_url ); ?>" class="button" style="color:#991b1b;border-color:#991b1b" onclick="return confirm('<?php echo esc_js( __( 'Rifiutare la prenotazione? Verrà inviata l\'email di rifiuto.', 'calypsosub' ) ); ?>')"><?php esc_html_e( '✕ Rifiuta', 'calypsosub' ); ?></a>
			<?php elseif ( $status === 'confermata' ) : ?>
				<a href="<?php echo esc_url( $reject_url ); ?>" class="button" style="color:#991b1b;border-color:#991b1b" onclick="return confirm('<?php echo esc_js( __( 'Annullare la prenotazione confermata? Verrà inviata un\'email.', 'calypsosub' ) ); ?>')"><?php esc_html_e( 'Annulla prenotazione', 'calypsosub' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="calpren-grid">
			<div class="calpren-field">
				<label><?php esc_html_e( 'Prenotazione per', 'calypsosub' ); ?></label>
				<span class="val"><?php echo $post_id ? '<a href="' . esc_url( $list_url ) . '">' . esc_html( $titolo ) . '</a>' : '—'; ?></span>
			</div>
			<div class="calpren-field">
				<label><?php esc_html_e( 'Data', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( $data_evento ); ?></span>
			</div>
			<div class="calpren-field">
				<label><?php esc_html_e( 'Account', 'calypsosub' ); ?></label>
				<span class="val"><?php echo $user ? esc_html( $user->display_name ) : esc_html__( 'Ospite (non registrato)', 'calypsosub' ); ?></span>
			</div>
			<div class="calpren-field">
				<label><?php esc_html_e( 'Email', 'calypsosub' ); ?></label>
				<span class="val"><?php echo $email_display ? '<a href="mailto:' . esc_attr( $email_display ) . '">' . esc_html( $email_display ) . '</a>' : '—'; ?></span>
			</div>
			<div class="calpren-field">
				<label><?php esc_html_e( 'Persone', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( sprintf( __( '%1$d (1 + %2$d accompagnatori)', 'calypsosub' ), 1 + $companions, $companions ) ); ?></span>
			</div>
			<div class="calpren-field">
				<label><?php esc_html_e( 'Ricevuta il', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( $date ? mysql2date( 'd/m/Y H:i', $date ) : '—' ); ?></span>
			</div>
			<?php if ( $updated_at ) : ?>
			<div class="calpren-field">
				<label><?php esc_html_e( 'Ultima modifica', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( mysql2date( 'd/m/Y H:i', $updated_at ) ); ?></span>
			</div>
			<?php endif; ?>
		</div>

		<div class="calpren-section"><?php esc_html_e( 'Dati della prenotazione', 'calypsosub' ); ?></div>
		<?php wp_nonce_field( 'calypso_booking_edit_' . $post->ID, 'calypso_booking_edit_nonce' ); ?>
		<div class="calpren-form">
			<?php foreach ( $form_data as $name => $value ) :
				$name = (string) $name;
				if ( Calypsosub_Booking_Fields::is_hidden( $name ) ) continue;
				$field = Calypsosub_Booking_Fields::field( $name );
				$id    = 'calpren-' . sanitize_html_class( $name );
				?>
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				<div><?php self::render_field_input( $name, $value, $field, $id ); ?></div>
			<?php endforeach; ?>
			<?php if ( ! array_filter( array_keys( $form_data ), static fn( $k ) => ! Calypsosub_Booking_Fields::is_hidden( (string) $k ) ) ) : ?>
				<p class="calpren-ro"><?php esc_html_e( 'Nessun dato inviato dal modulo.', 'calypsosub' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="calpren-save">
			<button type="submit" name="save" value="1" class="button button-primary button-large"><?php esc_html_e( 'Salva modifiche', 'calypsosub' ); ?></button>
			<label><input type="checkbox" name="calypso_booking_notify" value="1" checked> <?php echo esc_html( $email_display ? sprintf( __( 'Invia l\'email "Prenotazione modificata" a %s', 'calypsosub' ), $email_display ) : __( 'Invia l\'email "Prenotazione modificata"', 'calypsosub' ) ); ?></label>
		</div>
		<p class="description"><?php esc_html_e( 'L\'email viene inviata solo se qualcosa è cambiato. Il testo si modifica in Calypso Sub → Template Email → Prenotazione modificata.', 'calypsosub' ); ?></p>
		<?php
	}

	/**
	 * Campo di modifica per un dato del modulo, del tipo del tag CF7
	 * d'origine (quando noto).
	 *
	 * @param array{label:string,type:string,options:string[]} $field
	 */
	private static function render_field_input( string $name, $value, array $field, string $id ): void {
		$input_name = 'calypso_booking_form[' . $name . ']';
		$type       = $field['type'];
		$values     = array_map( 'strval', is_array( $value ) ? $value : [ $value ] );
		$options    = $field['options'];

		if ( $type === 'acceptance' ) {
			// Consenso dato da chi ha prenotato: non si modifica.
			echo '<div class="calpren-ro">' . esc_html( array_filter( $values ) ? __( 'Accettato', 'calypsosub' ) : __( 'Non accettato', 'calypsosub' ) ) . '</div>';
			echo '<input type="hidden" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( implode( ', ', $values ) ) . '">';
			return;
		}
		if ( $type === 'checkbox' && $options ) {
			echo '<input type="hidden" name="' . esc_attr( $input_name ) . '[]" value="">';
			foreach ( array_unique( array_merge( $options, array_filter( $values ) ) ) as $opt ) {
				echo '<label style="margin-right:14px"><input type="checkbox" name="' . esc_attr( $input_name ) . '[]" value="' . esc_attr( $opt ) . '"' . checked( in_array( $opt, $values, true ), true, false ) . '> ' . esc_html( $opt ) . '</label>';
			}
			return;
		}
		if ( in_array( $type, [ 'select', 'radio' ], true ) && $options ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $input_name ) . '">';
			foreach ( array_unique( array_merge( [ '' ], $options, array_filter( $values ) ) ) as $opt ) {
				echo '<option value="' . esc_attr( $opt ) . '"' . selected( $values[0] ?? '', $opt, false ) . '>' . esc_html( $opt === '' ? '—' : $opt ) . '</option>';
			}
			echo '</select>';
			return;
		}
		$joined = implode( ', ', $values );
		if ( $type === 'textarea' || strlen( $joined ) > 80 || str_contains( $joined, "\n" ) ) {
			echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $input_name ) . '" rows="3">' . esc_textarea( $joined ) . '</textarea>';
			return;
		}
		$html_type = ( $type === 'email' || in_array( $name, [ 'email', 'mail', 'e-mail', 'your-email' ], true ) ) ? 'email' : ( $type === 'number' || $name === 'accompagnatori' || $name === 'altri_partecipanti' ? 'number' : 'text' );
		echo '<input type="' . $html_type . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $joined ) . '"' . ( $html_type === 'number' ? ' min="0"' : '' ) . '>';
	}

	/**
	 * Salvataggio dei dati modificati dal dettaglio prenotazione in admin.
	 * Aggiorna anche i meta derivati (accompagnatori, email ospite usata per
	 * i doppioni, allergie) e, se richiesto e se qualcosa è cambiato, invia
	 * l'email "Prenotazione modificata".
	 */
	public function save_booking_edit( int $booking_id ): void {
		if ( ! isset( $_POST['calypso_booking_edit_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['calypso_booking_edit_nonce'] ), 'calypso_booking_edit_' . $booking_id ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'calypsosub_manage' ) ) return;

		$old    = get_post_meta( $booking_id, '_booking_form_data', true );
		$old    = is_array( $old ) ? $old : [];
		$posted = (array) wp_unslash( $_POST['calypso_booking_form'] ?? [] );
		$new    = $old;

		foreach ( $old as $name => $old_value ) {
			$name = (string) $name;
			if ( Calypsosub_Booking_Fields::is_hidden( $name ) || ! array_key_exists( $name, $posted ) ) continue;
			$field = Calypsosub_Booking_Fields::field( $name );
			$raw   = $posted[ $name ];

			if ( $field['type'] === 'acceptance' ) continue;
			if ( is_array( $raw ) ) {
				$new[ $name ] = array_values( array_filter( array_map( 'sanitize_text_field', $raw ), static fn( $v ) => $v !== '' ) );
			} elseif ( $field['type'] === 'email' || in_array( $name, [ 'email', 'mail', 'e-mail', 'your-email' ], true ) ) {
				$new[ $name ] = sanitize_email( $raw );
			} elseif ( $field['type'] === 'textarea' || str_contains( (string) $raw, "\n" ) ) {
				$new[ $name ] = sanitize_textarea_field( $raw );
			} else {
				$new[ $name ] = sanitize_text_field( $raw );
			}
			// Un valore singolo in origine resta singolo (es. select CF7 salvato come array).
			if ( is_array( $old_value ) && ! is_array( $new[ $name ] ) ) $new[ $name ] = [ $new[ $name ] ];
		}

		if ( $new === $old ) return;

		$post_id     = (int) get_post_meta( $booking_id, '_booking_post_id', true );
		$old_people  = self::get_people( $booking_id );
		$companions  = self::companions_from_form( $new );

		update_post_meta( $booking_id, '_booking_form_data', $new );
		update_post_meta( $booking_id, '_booking_companions', $companions );
		if ( array_key_exists( 'allergie', $new ) ) {
			update_post_meta( $booking_id, '_booking_allergies', is_array( $new['allergie'] ) ? implode( ', ', $new['allergie'] ) : (string) $new['allergie'] );
		}
		if ( ! (int) get_post_meta( $booking_id, '_booking_user_id', true ) ) {
			$email = calypso_extract_email( $new );
			if ( $email !== '' ) update_post_meta( $booking_id, '_booking_guest_email', strtolower( $email ) );
		}
		update_post_meta( $booking_id, '_booking_updated', current_time( 'mysql' ) );

		// Più persone di prima e posti superati: si salva comunque (decide lo
		// staff), ma lo si segnala dopo il redirect.
		$status   = (string) get_post_meta( $booking_id, '_booking_status', true );
		$capacity = self::get_capacity( $post_id );
		if ( in_array( $status, [ 'confermata', 'in_attesa' ], true ) && $capacity !== null
			&& 1 + $companions > $old_people && $this->count_people( $post_id ) > $capacity ) {
			set_transient( 'calypso_booking_over_' . get_current_user_id(), 1, 60 );
		}

		if ( ! empty( $_POST['calypso_booking_notify'] ) ) {
			$this->email->send_booking_updated( $booking_id );
		}
	}

	public function booking_edit_notices(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'calypso_prenotazione' || $screen->base !== 'post' ) return;
		if ( get_transient( 'calypso_booking_over_' . get_current_user_id() ) ) {
			delete_transient( 'calypso_booking_over_' . get_current_user_id() );
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Modifica salvata, ma con le nuove persone i posti dell\'evento risultano superati.', 'calypsosub' ) . '</p></div>';
		}
	}

	/** Messaggi "Articolo aggiornato" → "Prenotazione aggiornata". */
	public function booking_updated_messages( array $messages ): array {
		$messages['calypso_prenotazione'] = array_fill( 0, 11, '' );
		$messages['calypso_prenotazione'][1] = __( 'Prenotazione aggiornata.', 'calypsosub' );
		$messages['calypso_prenotazione'][4] = __( 'Prenotazione aggiornata.', 'calypsosub' );
		$messages['calypso_prenotazione'][6] = __( 'Prenotazione salvata.', 'calypsosub' );
		return $messages;
	}

	public function handle_confirm_booking(): void {
		$booking_id = absint( $_GET['booking_id'] ?? 0 );
		if ( ! $booking_id || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'calypso_confirm_' . $booking_id ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'calypsosub' ) );
		}
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );

		$this->admin_confirm( $booking_id );
		$this->redirect_back( 'confirmed' );
	}

	/** Torna alla pagina da cui è partita l'azione (elenco o dettaglio). */
	private function redirect_back( string $msg ): void {
		$back = wp_get_referer() ?: Calypsosub_Bookings_Admin::page_url();
		wp_safe_redirect( add_query_arg( [ 'cso_msg' => $msg, 'cso_count' => 1 ], $back ) );
		exit;
	}

	/** Conferma da admin (anche da lista d'attesa) e invia l'email. */
	public function admin_confirm( int $booking_id ): bool {
		$current = (string) get_post_meta( $booking_id, '_booking_status', true );
		if ( ! in_array( $current, [ 'in_attesa', 'lista_attesa' ], true ) ) return false;
		update_post_meta( $booking_id, '_booking_status', 'confermata' );
		$this->email->send_booking_confirmed( $booking_id );
		return true;
	}

	/**
	 * Rifiuta (se non ancora confermata) o annulla (se confermata) da admin e
	 * invia l'email. Un annullamento libera posti: si promuove la lista
	 * d'attesa, come quando annulla l'utente stesso (solo per prenotazioni
	 * confermate, vedi cancel_booking()).
	 */
	public function admin_reject( int $booking_id ): bool {
		$current = (string) get_post_meta( $booking_id, '_booking_status', true );
		if ( ! in_array( $current, [ 'in_attesa', 'lista_attesa', 'confermata' ], true ) ) return false;
		update_post_meta( $booking_id, '_booking_status', $current === 'confermata' ? 'annullata' : 'rifiutata' );
		$this->email->send_booking_rejected( $booking_id );
		if ( $current === 'confermata' ) {
			$this->promote_waitlist( (int) get_post_meta( $booking_id, '_booking_post_id', true ) );
		}
		return true;
	}

	public function handle_reject_booking(): void {
		$booking_id = absint( $_GET['booking_id'] ?? 0 );
		if ( ! $booking_id || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'calypso_reject_' . $booking_id ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'calypsosub' ) );
		}
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );

		$this->admin_reject( $booking_id );
		$this->redirect_back( 'rejected' );
	}

	public function booking_row_actions( array $actions, WP_Post $post ): array {
		if ( $post->post_type !== 'calypso_prenotazione' ) return $actions;
		$status = (string) get_post_meta( $post->ID, '_booking_status', true );

		if ( $status === 'in_attesa' ) {
			$confirm_url = wp_nonce_url( admin_url( 'admin.php?action=calypso_confirm_booking&booking_id=' . $post->ID ), 'calypso_confirm_' . $post->ID );
			$reject_url  = wp_nonce_url( admin_url( 'admin.php?action=calypso_reject_booking&booking_id='  . $post->ID ), 'calypso_reject_'  . $post->ID );
			$actions['confirm'] = '<a href="' . esc_url( $confirm_url ) . '" style="color:#065f46;font-weight:600">' . esc_html__( 'Conferma', 'calypsosub' ) . '</a>';
			$actions['reject']  = '<a href="' . esc_url( $reject_url )  . '" style="color:#991b1b">'               . esc_html__( 'Rifiuta', 'calypsosub' )   . '</a>';
		} elseif ( $status === 'confermata' ) {
			$reject_url = wp_nonce_url( admin_url( 'admin.php?action=calypso_reject_booking&booking_id=' . $post->ID ), 'calypso_reject_' . $post->ID );
			$actions['reject'] = '<a href="' . esc_url( $reject_url ) . '" style="color:#991b1b">' . esc_html__( 'Annulla', 'calypsosub' ) . '</a>';
		}
		return $actions;
	}

	public function add_booking_columns( array $cols ): array {
		$new = [];
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( $k === 'title' ) {
				$new['booking_status'] = __( 'Stato', 'calypsosub' );
				$new['booking_event']  = __( 'Evento', 'calypsosub' );
				$new['booking_user']   = __( 'Utente', 'calypsosub' );
			}
		}
		return $new;
	}

	public function render_booking_column( string $col, int $post_id ): void {
		$labels = [
			'in_attesa'    => [ 'label' => __( 'In attesa', 'calypsosub' ),  'color' => '#92400e', 'bg' => '#fef3c7' ],
			'confermata'   => [ 'label' => __( 'Confermata', 'calypsosub' ), 'color' => '#065f46', 'bg' => '#d1fae5' ],
			'lista_attesa' => [ 'label' => __( 'Lista attesa', 'calypsosub' ),'color' => '#1e40af', 'bg' => '#dbeafe' ],
			'annullata'    => [ 'label' => __( 'Annullata', 'calypsosub' ),  'color' => '#991b1b', 'bg' => '#fee2e2' ],
			'rifiutata'    => [ 'label' => __( 'Rifiutata', 'calypsosub' ),  'color' => '#991b1b', 'bg' => '#fee2e2' ],
		];
		if ( $col === 'booking_status' ) {
			$status = (string) get_post_meta( $post_id, '_booking_status', true );
			$l = $labels[ $status ] ?? [ 'label' => $status, 'color' => '#444', 'bg' => '#eee' ];
			echo '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;background:' . esc_attr( $l['bg'] ) . ';color:' . esc_attr( $l['color'] ) . '">' . esc_html( $l['label'] ) . '</span>';
		} elseif ( $col === 'booking_event' ) {
			$pid = (int) get_post_meta( $post_id, '_booking_post_id', true );
			echo '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . esc_html( get_the_title( $pid ) ) . '</a>';
		} elseif ( $col === 'booking_user' ) {
			$uid  = (int) get_post_meta( $post_id, '_booking_user_id', true );
			$user = $uid ? get_user_by( 'id', $uid ) : false;
			if ( $user ) {
				$nome = trim( $user->first_name . ' ' . $user->last_name );
				echo esc_html( $nome ?: $user->display_name );
				return;
			}
			$form_data = (array) get_post_meta( $post_id, '_booking_form_data', true );
			$nome      = trim( ( $form_data['nome'] ?? '' ) . ' ' . ( $form_data['cognome'] ?? '' ) );
			echo $nome !== ''
				? esc_html( $nome ) . ' <span style="color:#888">(' . esc_html__( 'ospite', 'calypsosub' ) . ')</span>'
				: esc_html__( 'Ospite', 'calypsosub' );
		}
	}

	/**
	 * Dopo un annullamento: promuove, in ordine di arrivo, le prenotazioni in
	 * lista d'attesa che entrano nei posti liberati (contando anche i loro
	 * accompagnatori). Una prenotazione troppo grande viene saltata, non
	 * blocca quelle dopo.
	 */
	private function promote_waitlist( int $post_id ): void {
		foreach ( $this->get_booking_ids( $post_id, [ 'lista_attesa' ] ) as $next_id ) {
			$remaining = $this->get_remaining_spots( $post_id );
			if ( $remaining !== null && self::get_people( $next_id ) > $remaining ) continue;
			update_post_meta( $next_id, '_booking_status', 'confermata' );
			$this->email->send_promoted_from_waitlist( $next_id );
		}
	}
}
