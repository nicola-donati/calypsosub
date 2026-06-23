<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Occorrenza_Uscite {

	public function init(): void {
		add_action( 'init',                                [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',                       [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_occ_uscita',  [ $this, 'save_meta' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_occ_uscita', [
			'label'           => __( 'Occorrenze uscite', 'calypsosub' ),
			'labels'          => [
				'name'          => __( 'Occorrenze', 'calypsosub' ),
				'singular_name' => __( 'Occorrenza', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi occorrenza', 'calypsosub' ),
				'edit_item'     => __( 'Modifica occorrenza', 'calypsosub' ),
				'not_found'     => __( 'Nessuna occorrenza trovata', 'calypsosub' ),
			],
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=calypso_uscita',
			'show_in_rest'    => false,
			'supports'        => [ 'title' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'menu_icon'       => 'dashicons-calendar-alt',
		] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'calypso_occ_uscita_meta',
			__( 'Dettagli occorrenza', 'calypsosub' ),
			[ $this, 'render_meta_box' ],
			'calypso_occ_uscita',
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		$d = $this->get_meta( $post->ID );
		wp_nonce_field( 'calypso_occ_uscita_meta', 'calypso_occ_uscita_nonce' );

		$all_uscite = get_posts( [
			'post_type'      => 'calypso_uscita',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		] );

		$dp = $d['data'] ? substr( $d['data'], 0, 10 ) : '';
		$tp = strlen( $d['data'] ) > 10 ? substr( $d['data'], 11, 5 ) : '';
		?>
		<style>
		.calypso-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
		.calypso-meta-field label{display:block;font-weight:600;margin-bottom:4px}
		.calypso-meta-field input,.calypso-meta-field textarea,.calypso-meta-field select{width:100%}
		.calypso-meta-field textarea{min-height:80px}
		</style>

		<div class="calypso-meta-grid">
			<div class="calypso-meta-field" style="grid-column:1/-1">
				<label><?php _e( 'Uscita', 'calypsosub' ); ?> *</label>
				<select name="calypso_occ_uscita_id" required>
					<option value=""><?php _e( '— seleziona uscita —', 'calypsosub' ); ?></option>
					<?php foreach ( $all_uscite as $uid ) : ?>
					<option value="<?php echo esc_attr( $uid ); ?>"
					        <?php selected( $d['uscita_id'], $uid ); ?>>
						<?php echo esc_html( get_the_title( $uid ) ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Data', 'calypsosub' ); ?> *</label>
				<input type="date" name="calypso_occ_data_d" value="<?php echo esc_attr( $dp ); ?>" required>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Ora', 'calypsosub' ); ?></label>
				<input type="time" name="calypso_occ_data_t" value="<?php echo esc_attr( $tp ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Posti disponibili', 'calypsosub' ); ?> <small>(lascia vuoto se illimitati)</small></label>
				<input type="number" min="0" name="calypso_occ_posti" value="<?php echo esc_attr( $d['posti'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Max accompagnatori per prenotazione', 'calypsosub' ); ?></label>
				<input type="number" min="0" name="calypso_occ_max_accompagnatori" value="<?php echo esc_attr( $d['max_accompagnatori'] ); ?>">
			</div>
			<div class="calypso-meta-field" style="grid-column:1/-1">
				<label><?php _e( 'Note', 'calypsosub' ); ?> <small>(override facoltativo delle note della scheda uscita per questa data)</small></label>
				<textarea name="calypso_occ_note"><?php echo esc_textarea( $d['note'] ); ?></textarea>
			</div>
		</div>

		<div class="calypso-meta-field" style="margin-bottom:12px">
			<label>
				<input type="checkbox" name="calypso_occ_lista_attesa" value="1"
				       <?php checked( $d['lista_attesa'], 1 ); ?>>
				<?php _e( 'Abilita lista d\'attesa', 'calypsosub' ); ?>
			</label>
		</div>
		<?php
	}

	public function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['calypso_occ_uscita_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['calypso_occ_uscita_nonce'] ), 'calypso_occ_uscita_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		update_post_meta( $post_id, '_occorrenza_uscita_uscita_id',
			absint( $_POST['calypso_occ_uscita_id'] ?? 0 ) );

		$dv = sanitize_text_field( wp_unslash( $_POST['calypso_occ_data_d'] ?? '' ) );
		$tv = sanitize_text_field( wp_unslash( $_POST['calypso_occ_data_t'] ?? '' ) );
		update_post_meta( $post_id, '_occorrenza_uscita_data', $dv ? ( $tv ? $dv . 'T' . $tv : $dv ) : '' );

		update_post_meta( $post_id, '_occorrenza_uscita_note',
			sanitize_textarea_field( wp_unslash( $_POST['calypso_occ_note'] ?? '' ) ) );

		$posti_raw = $_POST['calypso_occ_posti'] ?? '';
		if ( $posti_raw !== '' ) {
			update_post_meta( $post_id, '_occorrenza_uscita_posti', absint( $posti_raw ) );
		} else {
			delete_post_meta( $post_id, '_occorrenza_uscita_posti' );
		}

		$acc_raw = $_POST['calypso_occ_max_accompagnatori'] ?? '';
		if ( $acc_raw !== '' ) {
			update_post_meta( $post_id, '_occorrenza_uscita_max_accompagnatori', absint( $acc_raw ) );
		} else {
			delete_post_meta( $post_id, '_occorrenza_uscita_max_accompagnatori' );
		}

		update_post_meta( $post_id, '_occorrenza_uscita_lista_attesa',
			isset( $_POST['calypso_occ_lista_attesa'] ) ? 1 : 0 );
	}

	private function get_meta( int $post_id ): array {
		$posti_raw = get_post_meta( $post_id, '_occorrenza_uscita_posti', true );
		$acc_raw   = get_post_meta( $post_id, '_occorrenza_uscita_max_accompagnatori', true );
		return [
			'uscita_id'          => (int)    get_post_meta( $post_id, '_occorrenza_uscita_uscita_id', true ),
			'data'               => (string) get_post_meta( $post_id, '_occorrenza_uscita_data', true ),
			'posti'              => $posti_raw !== '' && $posti_raw !== false ? (string) $posti_raw : '',
			'max_accompagnatori' => $acc_raw   !== '' && $acc_raw   !== false ? (string) $acc_raw   : '',
			'lista_attesa'       => (int) get_post_meta( $post_id, '_occorrenza_uscita_lista_attesa', true ),
			'note'               => (string) get_post_meta( $post_id, '_occorrenza_uscita_note', true ),
		];
	}
}
