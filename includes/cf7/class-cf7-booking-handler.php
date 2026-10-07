<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CF7_Booking_Handler {

	public static ?int $active_post_id = null;

	public function init(): void {
		add_filter( 'wpcf7_form_hidden_fields', [ $this, 'inject_hidden_fields' ] );
		add_filter( 'wpcf7_validate', [ $this, 'validate_capacity' ], 20, 2 );
		add_action( 'wpcf7_before_send_mail', [ $this, 'create_booking' ], 10, 3 );
		add_action( 'rest_api_init', [ $this, 'register_rest_route' ] );
		add_action( 'wp_ajax_calypso_prenotazione_form',        [ $this, 'ajax_render_form' ] );
		add_action( 'wp_ajax_nopriv_calypso_prenotazione_form', [ $this, 'ajax_render_form' ] );
	}

	public function inject_hidden_fields( array $fields ): array {
		if ( self::$active_post_id !== null ) {
			$fields['booking_post_id']   = (string) self::$active_post_id;
			$fields['booking_post_type'] = (string) get_post_type( self::$active_post_id );
		}

		// Identità dell'utente loggato al momento del RENDER del form,
		// firmata per non poter essere falsificata. Serve perché CF7 invia
		// il form via REST API: se manca/non combina il nonce REST, il
		// cookie di login viene ignorato per QUELLA richiesta e
		// is_user_logged_in() risulta erroneamente false solo all'invio,
		// anche se al caricamento della pagina l'utente era correttamente
		// riconosciuto (resolve_user_id() usa questo come fallback).
		$uid = get_current_user_id();
		if ( $uid ) {
			$fields['booking_user_id']  = (string) $uid;
			$fields['booking_user_sig'] = wp_hash( $uid . '|' . wp_salt( 'auth' ) );
		}

		return $fields;
	}

	/**
	 * Utente loggato al momento dell'invio — o, se is_user_logged_in()
	 * risulta (erroneamente) false, quello firmato al render del form.
	 */
	private function resolve_user_id( array $data ): int {
		$live_uid = get_current_user_id();
		if ( $live_uid ) return $live_uid;

		$claimed_uid = absint( $data['booking_user_id'] ?? 0 );
		$claimed_sig = (string) ( $data['booking_user_sig'] ?? '' );
		if ( $claimed_uid && $claimed_sig !== ''
			&& hash_equals( wp_hash( $claimed_uid . '|' . wp_salt( 'auth' ) ), $claimed_sig ) ) {
			return $claimed_uid;
		}
		return 0;
	}

	public function validate_capacity( WPCF7_Validation $result, array $tags ): WPCF7_Validation {
		$post_id = absint( $_POST['booking_post_id'] ?? 0 );
		if ( ! $post_id ) return $result;

		// Niente requisito di login qui: alcune tipologie (es. Uscite) sono
		// configurate per accettare prenotazioni anche da utenti anonimi
		// (toggle "Richiedi login" del blocco Prenotazione). user_id è 0 per
		// un prenotante anonimo — il controllo duplicati usa l'email del
		// form in quel caso (calypso_extract_email()).
		$post_type   = get_post_type( $post_id );
		$user_id     = $this->resolve_user_id( wp_unslash( $_POST ) );
		$guest_email = $user_id ? '' : calypso_extract_email( wp_unslash( $_POST ) );

		if ( in_array( $post_type, [ 'calypso_occ_uscita', 'calypso_evento' ], true )
			&& ! calypso_can_book( $post_id, $user_id, $guest_email ) ) {
			$result->invalidate( 'booking_post_id', __( 'Posti esauriti o richiesta già inviata.', 'calypsosub' ) );
		} elseif ( $post_type === 'calypso_corso' ) {
			global $calypsosub_booking_manager;
			if ( $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) {
				$dup = $user_id
					? $calypsosub_booking_manager->user_has_booking( $post_id, $user_id )
					: ( $guest_email !== '' && $calypsosub_booking_manager->guest_has_booking( $post_id, $guest_email ) );
				if ( $dup ) {
					$result->invalidate( 'booking_post_id', __( 'Hai già una richiesta per questo corso.', 'calypsosub' ) );
				}
			}
		}

		return $result;
	}

	public function create_booking( WPCF7_ContactForm $contact_form, &$abort = false, $submission = null ): void {
		if ( ! $submission ) {
			$submission = WPCF7_Submission::get_instance();
		}
		if ( ! $submission ) return;

		$data = $submission->get_posted_data();
		$post_id = absint( $data['booking_post_id'] ?? 0 );
		if ( ! $post_id ) return;

		$user_id = $this->resolve_user_id( $data );

		unset( $data['booking_post_id'], $data['booking_post_type'], $data['booking_user_id'], $data['booking_user_sig'], $data['_wpcf7'], $data['_wpcf7_version'], $data['_wpcf7_locale'], $data['_wpcf7_unit_tag'], $data['_wpcf7_container_post'], $data['_wpnonce'] );

		// DIAGNOSTICA TEMPORANEA — per confermare che resolve_user_id()
		// recupera correttamente l'utente quando is_user_logged_in()
		// risulta erroneamente false all'invio (nonce REST mancante/non
		// valido). Da rimuovere una volta confermato. Appare tra i "Dati
		// inviati dal form" nel dettaglio prenotazione in admin.
		$has_login_cookie = false;
		foreach ( array_keys( $_COOKIE ) as $cookie_name ) {
			if ( str_starts_with( $cookie_name, 'wordpress_logged_in_' ) ) { $has_login_cookie = true; break; }
		}
		$data['_debug_login'] = sprintf(
			'is_user_logged_in=%s get_current_user_id=%d resolved_user_id=%d has_login_cookie=%s',
			is_user_logged_in() ? 'true' : 'false',
			get_current_user_id(),
			$user_id,
			$has_login_cookie ? 'true' : 'false'
		);

		$uploaded = $submission->uploaded_files();
		if ( $uploaded ) {
			$upload_dir = wp_upload_dir();
			$dest_dir   = trailingslashit( $upload_dir['basedir'] ) . 'calypso-prenotazioni/' . $post_id . '-' . $user_id . '/';
			wp_mkdir_p( $dest_dir );

			$htaccess = $dest_dir . '.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				file_put_contents( $htaccess, "Deny from all\n" );
			}

			$allowed_extensions = [ 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx' ];
			$forbidden_extensions = [ 'php', 'phtml', 'html', 'htm', 'svg', 'htaccess' ];

			foreach ( $uploaded as $field_name => $tmp_path ) {
				if ( ! is_string( $tmp_path ) || ! file_exists( $tmp_path ) ) continue;

				$original_name = basename( $tmp_path );
				$extension     = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );

				if ( in_array( $extension, $forbidden_extensions, true ) || ! in_array( $extension, $allowed_extensions, true ) ) {
					continue;
				}

				$random_suffix  = wp_generate_password( 8, false );
				$filename_base  = pathinfo( $original_name, PATHINFO_FILENAME );
				$randomized_name = sanitize_file_name( $filename_base . '-' . $random_suffix . '.' . $extension );
				$unique_name     = wp_unique_filename( $dest_dir, $randomized_name );

				$dest = $dest_dir . $unique_name;
				if ( copy( $tmp_path, $dest ) ) {
					$data[ $field_name ] = str_replace( $upload_dir['basedir'], '', $dest );
				}
			}
		}

		$result = calypso_book( $post_id, $user_id, $data );

		if ( is_wp_error( $result ) ) {
			// Il controllo di validazione pre-invio può non intercettare
			// tutto (es. race condition sull'ultimo posto, o un edge case
			// non coperto) — questo è l'ultimo baluardo: se la prenotazione
			// non è stata creata, non deve passare come "successo" muto.
			$abort = true;
			$submission->set_status( 'validation_failed' );
			$submission->set_response( $result->get_error_message() );
		}
	}

	public function register_rest_route(): void {
		register_rest_route( 'calypso/v1', '/cf7-forms', [
			'methods'             => 'GET',
			'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
			'callback'            => static function ( WP_REST_Request $request ) {
				$category = sanitize_key( $request->get_param( 'category' ) ?? '' );
				$cat_handler = new Calypsosub_CF7_Booking_Category();
				if ( $category === 'all' ) {
					return rest_ensure_response( $cat_handler->all_forms() );
				}
				return rest_ensure_response( $cat_handler->forms_for_category( $category ) );
			},
		] );
	}

	public function ajax_render_form(): void {
		check_ajax_referer( 'calypso_prenotazione_form', '_ajax_nonce' );

		$form_id = absint( $_POST['cf7_form_id'] ?? 0 );
		$post_id = absint( $_POST['post_id'] ?? 0 );
		if ( ! $form_id ) {
			wp_send_json_error( [ 'message' => 'Form non valido.' ] );
		}

		self::$active_post_id = $post_id ?: null;
		$html = do_shortcode( '[contact-form-7 id="' . $form_id . '"]' );
		self::$active_post_id = null;

		wp_send_json_success( [ 'html' => $html ] );
	}
}
