<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Uscite {

	public function init(): void {
		add_action( 'init',                     [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',           [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_uscita', [ $this, 'save_meta' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_uscita', [
			'label'        => __( 'Uscite', 'calypsosub' ),
			'labels'       => [
				'name'          => __( 'Uscite', 'calypsosub' ),
				'singular_name' => __( 'Uscita', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi uscita', 'calypsosub' ),
				'edit_item'     => __( 'Modifica uscita', 'calypsosub' ),
				'not_found'     => __( 'Nessuna uscita trovata', 'calypsosub' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'editor', 'thumbnail' ],
			'menu_icon'    => 'dashicons-location',
			'rewrite'      => [ 'slug' => 'uscite' ],
			'has_archive'  => true,
		] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'calypso_uscita_meta',
			__( 'Dettagli uscita', 'calypsosub' ),
			[ $this, 'render_meta_box' ],
			'calypso_uscita',
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		$d = $this->get_meta( $post->ID );
		wp_nonce_field( 'calypso_uscita_meta', 'calypso_uscita_nonce' );
		?>
		<style>
		.calypso-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
		.calypso-meta-field label{display:block;font-weight:600;margin-bottom:4px}
		.calypso-meta-field input,.calypso-meta-field textarea,.calypso-meta-field select{width:100%}
		.calypso-meta-field textarea{min-height:80px}
		.calypso-section-title{font-weight:700;font-size:13px;margin:16px 0 8px;border-bottom:1px solid #ddd;padding-bottom:4px}
		</style>

		<div class="calypso-meta-grid">
			<div class="calypso-meta-field">
				<label><?php _e( 'Sottotitolo', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_sottotitolo" value="<?php echo esc_attr( $d['sottotitolo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Descrizione breve', 'calypsosub' ); ?></label>
				<textarea name="calypso_desc_breve"><?php echo esc_textarea( $d['desc_breve'] ); ?></textarea>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Luogo', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_luogo" value="<?php echo esc_attr( $d['luogo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Punto di ritrovo', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_ritrovo" value="<?php echo esc_attr( $d['ritrovo'] ); ?>">
			</div>
		</div>

		<p class="calypso-section-title"><?php _e( 'Testi opzionali', 'calypsosub' ); ?></p>
		<div class="calypso-meta-grid">
			<div class="calypso-meta-field">
				<label><?php _e( 'Incluso nell\'uscita', 'calypsosub' ); ?></label>
				<textarea name="calypso_incluso"><?php echo esc_textarea( $d['incluso'] ); ?></textarea>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Cosa portare', 'calypsosub' ); ?></label>
				<textarea name="calypso_cosa_portare"><?php echo esc_textarea( $d['cosa_portare'] ); ?></textarea>
			</div>
			<div class="calypso-meta-field" style="grid-column:span 2">
				<label><?php _e( 'Note cancellazione', 'calypsosub' ); ?></label>
				<textarea name="calypso_note_cancellazione"><?php echo esc_textarea( $d['note_cancellazione'] ); ?></textarea>
			</div>
		</div>
		<?php
	}

	public function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['calypso_uscita_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['calypso_uscita_nonce'] ), 'calypso_uscita_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$text_fields = [
			'_uscita_sottotitolo'       => 'calypso_sottotitolo',
			'_uscita_desc_breve'        => 'calypso_desc_breve',
			'_uscita_luogo'             => 'calypso_luogo',
			'_uscita_ritrovo'           => 'calypso_ritrovo',
			'_uscita_incluso'           => 'calypso_incluso',
			'_uscita_cosa_portare'      => 'calypso_cosa_portare',
			'_uscita_note_cancellazione'=> 'calypso_note_cancellazione',
		];
		foreach ( $text_fields as $meta_key => $post_key ) {
			update_post_meta( $post_id, $meta_key,
				sanitize_textarea_field( wp_unslash( $_POST[ $post_key ] ?? '' ) ) );
		}

	}

	private function get_meta( int $post_id ): array {
		return [
			'sottotitolo'        => (string) get_post_meta( $post_id, '_uscita_sottotitolo', true ),
			'desc_breve'         => (string) get_post_meta( $post_id, '_uscita_desc_breve', true ),
			'luogo'              => (string) get_post_meta( $post_id, '_uscita_luogo', true ),
			'ritrovo'            => (string) get_post_meta( $post_id, '_uscita_ritrovo', true ),
			'incluso'            => (string) get_post_meta( $post_id, '_uscita_incluso', true ),
			'cosa_portare'       => (string) get_post_meta( $post_id, '_uscita_cosa_portare', true ),
			'note_cancellazione' => (string) get_post_meta( $post_id, '_uscita_note_cancellazione', true ),
		];
	}
}
