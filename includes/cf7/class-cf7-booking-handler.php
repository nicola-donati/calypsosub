<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CF7_Booking_Handler {

	public static ?int $active_post_id = null;

	public function init(): void {
		add_filter( 'wpcf7_form_hidden_fields', [ $this, 'inject_hidden_fields' ] );
		add_filter( 'wpcf7_validate', [ $this, 'validate_capacity' ], 20, 2 );
		add_action( 'wpcf7_before_send_mail', [ $this, 'create_booking' ], 10, 1 );
		add_action( 'rest_api_init', [ $this, 'register_rest_route' ] );
		add_action( 'wp_ajax_calypso_prenotazione_form',        [ $this, 'ajax_render_form' ] );
		add_action( 'wp_ajax_nopriv_calypso_prenotazione_form', [ $this, 'ajax_render_form' ] );
	}

	public function inject_hidden_fields( array $fields ): array {
		if ( self::$active_post_id === null ) return $fields;
		$fields['booking_post_id']   = (string) self::$active_post_id;
		$fields['booking_post_type'] = (string) get_post_type( self::$active_post_id );
		return $fields;
	}

	public function validate_capacity( WPCF7_Validation $result, array $tags ): WPCF7_Validation {
		$post_id = absint( $_POST['booking_post_id'] ?? 0 );
		if ( ! $post_id ) return $result;

		if ( ! is_user_logged_in() ) {
			$result->invalidate( 'booking_post_id', __( 'Devi accedere per inviare questa richiesta.', 'calypsosub' ) );
			return $result;
		}

		$post_type = get_post_type( $post_id );
		$user_id   = get_current_user_id();

		if ( in_array( $post_type, [ 'calypso_occ_uscita', 'calypso_evento' ], true )
			&& ! calypso_can_book( $post_id, $user_id ) ) {
			$result->invalidate( 'booking_post_id', __( 'Posti esauriti o richiesta già inviata.', 'calypsosub' ) );
		} elseif ( $post_type === 'calypso_corso' ) {
			global $calypsosub_booking_manager;
			if ( $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager
				&& $calypsosub_booking_manager->user_has_booking( $post_id, $user_id ) ) {
				$result->invalidate( 'booking_post_id', __( 'Hai già una richiesta per questo corso.', 'calypsosub' ) );
			}
		}

		return $result;
	}

	public function create_booking( WPCF7_ContactForm $contact_form ): void {
		$submission = WPCF7_Submission::get_instance();
		if ( ! $submission ) return;

		$data = $submission->get_posted_data();
		$post_id = absint( $data['booking_post_id'] ?? 0 );
		if ( ! $post_id || ! is_user_logged_in() ) return;

		unset( $data['booking_post_id'], $data['booking_post_type'], $data['_wpcf7'], $data['_wpcf7_version'], $data['_wpcf7_locale'], $data['_wpcf7_unit_tag'], $data['_wpcf7_container_post'], $data['_wpnonce'] );

		$uploaded = $submission->uploaded_files();
		if ( $uploaded ) {
			$upload_dir = wp_upload_dir();
			$dest_dir   = trailingslashit( $upload_dir['basedir'] ) . 'calypso-prenotazioni/' . $post_id . '-' . get_current_user_id() . '/';
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

		calypso_book( $post_id, get_current_user_id(), $data );
	}

	public function register_rest_route(): void {
		register_rest_route( 'calypso/v1', '/cf7-forms', [
			'methods'             => 'GET',
			'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
			'callback'            => static function ( WP_REST_Request $request ) {
				$category = sanitize_key( $request->get_param( 'category' ) ?? '' );
				$cat_handler = new Calypsosub_CF7_Booking_Category();
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
