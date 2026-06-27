<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Article_Timeline_Fields {

	private const NONCE_ACTION = 'calypso_timeline_fields_save';
	private const NONCE_NAME   = 'calypso_timeline_fields_nonce';

	public function init(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post_post', [ $this, 'save' ] );
	}

	public function add_meta_box(): void {
		add_meta_box(
			'calypso_timeline_fields',
			__( 'Dati timeline storia club', 'calypsosub' ),
			[ $this, 'render' ],
			'post',
			'side',
			'default'
		);
	}

	public function render( WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$year  = get_post_meta( $post->ID, Calypsosub_Storia_Helpers::META_YEAR, true );
		$title = get_post_meta( $post->ID, Calypsosub_Storia_Helpers::META_TITLE, true );
		$text  = get_post_meta( $post->ID, Calypsosub_Storia_Helpers::META_TEXT, true );
		?>
		<p>
			<label for="calypso_timeline_year"><strong><?php esc_html_e( 'Anno evento', 'calypsosub' ); ?></strong></label><br>
			<input type="number" id="calypso_timeline_year" name="calypso_timeline_year" value="<?php echo esc_attr( $year ); ?>" style="width:100%;">
			<small><?php esc_html_e( 'Vuoto = usa anno di pubblicazione.', 'calypsosub' ); ?></small>
		</p>
		<p>
			<label for="calypso_timeline_title"><strong><?php esc_html_e( 'Titolo breve timeline', 'calypsosub' ); ?></strong></label><br>
			<input type="text" id="calypso_timeline_title" name="calypso_timeline_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%;">
			<small><?php esc_html_e( 'Vuoto = usa titolo articolo.', 'calypsosub' ); ?></small>
		</p>
		<p>
			<label for="calypso_timeline_text"><strong><?php esc_html_e( 'Testo breve timeline', 'calypsosub' ); ?></strong></label><br>
			<textarea id="calypso_timeline_text" name="calypso_timeline_text" rows="3" style="width:100%;"><?php echo esc_textarea( $text ); ?></textarea>
			<small><?php esc_html_e( 'Vuoto = usa excerpt articolo.', 'calypsosub' ); ?></small>
		</p>
		<?php
	}

	public function save( int $post_id ): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['calypso_timeline_year'] ) ) {
			$year = (int) $_POST['calypso_timeline_year'];
			if ( $year > 0 ) {
				update_post_meta( $post_id, Calypsosub_Storia_Helpers::META_YEAR, $year );
			} else {
				delete_post_meta( $post_id, Calypsosub_Storia_Helpers::META_YEAR );
			}
		}

		if ( isset( $_POST['calypso_timeline_title'] ) ) {
			$title = sanitize_text_field( wp_unslash( $_POST['calypso_timeline_title'] ) );
			if ( $title !== '' ) {
				update_post_meta( $post_id, Calypsosub_Storia_Helpers::META_TITLE, $title );
			} else {
				delete_post_meta( $post_id, Calypsosub_Storia_Helpers::META_TITLE );
			}
		}

		if ( isset( $_POST['calypso_timeline_text'] ) ) {
			$text = sanitize_textarea_field( wp_unslash( $_POST['calypso_timeline_text'] ) );
			if ( $text !== '' ) {
				update_post_meta( $post_id, Calypsosub_Storia_Helpers::META_TEXT, $text );
			} else {
				delete_post_meta( $post_id, Calypsosub_Storia_Helpers::META_TEXT );
			}
		}
	}
}
