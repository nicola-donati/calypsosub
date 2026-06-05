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
		add_action( 'wp_ajax_calypso_cancel_booking',[ $this, 'ajax_cancel' ] );
		add_action( 'add_meta_boxes',                [ $this, 'add_booking_meta_box' ] );
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
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'calypsosub',
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

		$this->email->send_booking_received( $booking_id );
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
			return 'in_attesa';
		}

		if ( $this->count_confirmed( $post_id ) < (int) $max ) {
			return 'in_attesa';
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
			   AND pm_status.meta_key = '_booking_status'
			   AND pm_status.meta_value IN ('confermata','in_attesa')
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
			   AND pm_status.meta_value IN ('confermata','lista_attesa','in_attesa')
			 WHERE p.post_type = 'calypso_prenotazione' AND p.post_status = 'publish'",
			$post_id,
			$user_id
		) );
		return $count > 0;
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
		$companions = (int)    get_post_meta( $post->ID, '_booking_companions', true );
		$allergies  = (string) get_post_meta( $post->ID, '_booking_allergies', true );
		$date       = (string) get_post_meta( $post->ID, '_booking_date', true );
		$post_type  = (string) get_post_meta( $post->ID, '_booking_post_type', true );
		$user       = get_user_by( 'id', $user_id );

		$mp         = $post_type === 'calypso_uscita' ? '_uscita' : '_evento';
		$date_raw   = (array) ( get_post_meta( $post_id, $mp . '_date', true ) ?: [] );
		$data_evento = ! empty( $date_raw ) ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_raw[0] ) ) : '—';

		$labels = [
			'in_attesa'    => [ 'label' => __( 'In attesa di conferma', 'calypsosub' ), 'color' => '#92400e', 'bg' => '#fef3c7' ],
			'confermata'   => [ 'label' => __( 'Confermata', 'calypsosub' ),            'color' => '#065f46', 'bg' => '#d1fae5' ],
			'lista_attesa' => [ 'label' => __( 'Lista attesa', 'calypsosub' ),           'color' => '#1e40af', 'bg' => '#dbeafe' ],
			'annullata'    => [ 'label' => __( 'Annullata', 'calypsosub' ),              'color' => '#991b1b', 'bg' => '#fee2e2' ],
			'rifiutata'    => [ 'label' => __( 'Rifiutata', 'calypsosub' ),              'color' => '#991b1b', 'bg' => '#fee2e2' ],
		];
		$l = $labels[ $status ] ?? [ 'label' => $status, 'color' => '#444', 'bg' => '#eee' ];

		$confirm_url = wp_nonce_url( admin_url( 'admin.php?action=calypso_confirm_booking&booking_id=' . $post->ID ), 'calypso_confirm_' . $post->ID );
		$reject_url  = wp_nonce_url( admin_url( 'admin.php?action=calypso_reject_booking&booking_id='  . $post->ID ), 'calypso_reject_'  . $post->ID );
		?>
		<style>
		.calpren-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 32px;margin-bottom:24px}
		.calpren-field label{display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#888;margin-bottom:4px}
		.calpren-field .val{font-size:14px;color:#1d2327}
		.calpren-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:13px;font-weight:700}
		.calpren-actions{display:flex;gap:10px;padding-top:16px;border-top:1px solid #e5e7eb}
		</style>
		<div class="calpren-grid">
			<div class="calpren-field" style="grid-column:1/-1">
				<label><?php _e( 'Stato', 'calypsosub' ); ?></label>
				<span class="calpren-badge" style="background:<?php echo esc_attr( $l['bg'] ); ?>;color:<?php echo esc_attr( $l['color'] ); ?>"><?php echo esc_html( $l['label'] ); ?></span>
			</div>
			<div class="calpren-field">
				<label><?php _e( 'Utente', 'calypsosub' ); ?></label>
				<span class="val"><?php echo $user ? esc_html( $user->display_name ) : esc_html( $user_id ); ?></span>
			</div>
			<div class="calpren-field">
				<label><?php _e( 'Email', 'calypsosub' ); ?></label>
				<span class="val"><?php echo $user ? esc_html( $user->user_email ) : '—'; ?></span>
			</div>
			<div class="calpren-field">
				<label><?php _e( 'Evento', 'calypsosub' ); ?></label>
				<span class="val"><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></span>
			</div>
			<div class="calpren-field">
				<label><?php _e( 'Data evento', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( $data_evento ); ?></span>
			</div>
			<div class="calpren-field">
				<label><?php _e( 'Accompagnatori', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( $companions ); ?></span>
			</div>
			<div class="calpren-field">
				<label><?php _e( 'Pre-prenotazione ricevuta il', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( $date ); ?></span>
			</div>
			<?php if ( $allergies ) : ?>
			<div class="calpren-field" style="grid-column:1/-1">
				<label><?php _e( 'Allergie / note', 'calypsosub' ); ?></label>
				<span class="val"><?php echo esc_html( $allergies ); ?></span>
			</div>
			<?php endif; ?>
		</div>
		<?php if ( $status === 'in_attesa' ) : ?>
		<div class="calpren-actions">
			<a href="<?php echo esc_url( $confirm_url ); ?>" class="button button-primary"><?php _e( '✓ Conferma prenotazione', 'calypsosub' ); ?></a>
			<a href="<?php echo esc_url( $reject_url ); ?>"  class="button" style="color:#991b1b;border-color:#991b1b"><?php _e( '✕ Rifiuta', 'calypsosub' ); ?></a>
		</div>
		<?php elseif ( $status === 'confermata' ) : ?>
		<div class="calpren-actions">
			<a href="<?php echo esc_url( $reject_url ); ?>" class="button" style="color:#991b1b;border-color:#991b1b"><?php _e( 'Annulla prenotazione', 'calypsosub' ); ?></a>
		</div>
		<?php endif; ?>
		<?php
	}

	public function handle_confirm_booking(): void {
		$booking_id = absint( $_GET['booking_id'] ?? 0 );
		if ( ! $booking_id || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'calypso_confirm_' . $booking_id ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'calypsosub' ) );
		}
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );

		update_post_meta( $booking_id, '_booking_status', 'confermata' );
		$this->email->send_booking_confirmed( $booking_id );

		wp_redirect( add_query_arg( [ 'post_type' => 'calypso_prenotazione', 'calypso_msg' => 'confirmed' ], admin_url( 'edit.php' ) ) );
		exit;
	}

	public function handle_reject_booking(): void {
		$booking_id = absint( $_GET['booking_id'] ?? 0 );
		if ( ! $booking_id || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'calypso_reject_' . $booking_id ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'calypsosub' ) );
		}
		if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );

		$current = (string) get_post_meta( $booking_id, '_booking_status', true );
		$new_status = ( $current === 'confermata' ) ? 'annullata' : 'rifiutata';
		update_post_meta( $booking_id, '_booking_status', $new_status );
		$this->email->send_booking_rejected( $booking_id );

		wp_redirect( add_query_arg( [ 'post_type' => 'calypso_prenotazione', 'calypso_msg' => 'rejected' ], admin_url( 'edit.php' ) ) );
		exit;
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
			$user = get_user_by( 'id', $uid );
			if ( $user ) {
				$nome    = trim( $user->first_name . ' ' . $user->last_name );
				echo esc_html( $nome ?: $user->display_name );
			} else {
				echo esc_html( $uid );
			}
		}
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
