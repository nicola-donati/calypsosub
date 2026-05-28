<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Occorrenze {

	public function init(): void {
		add_action( 'init',                          [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',                [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_occorrenza',  [ $this, 'save_meta' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_occorrenza', [
			'label'           => __( 'Occorrenze corsi', 'calypsosub' ),
			'labels'          => [
				'name'          => __( 'Occorrenze', 'calypsosub' ),
				'singular_name' => __( 'Occorrenza', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi occorrenza', 'calypsosub' ),
				'edit_item'     => __( 'Modifica occorrenza', 'calypsosub' ),
				'not_found'     => __( 'Nessuna occorrenza trovata', 'calypsosub' ),
			],
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=calypso_corso',
			'show_in_rest'    => false,
			'supports'        => [ 'title' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'menu_icon'       => 'dashicons-calendar-alt',
		] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'calypso_occorrenza_meta',
			__( 'Dettagli occorrenza', 'calypsosub' ),
			[ $this, 'render_meta_box' ],
			'calypso_occorrenza',
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		$d = $this->get_meta( $post->ID );
		wp_nonce_field( 'calypso_occorrenza_meta', 'calypso_occorrenza_nonce' );

		$all_corsi = get_posts( [
			'post_type'      => 'calypso_corso',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		] );

		$all_docenti = get_posts( [
			'post_type'      => 'calypso_docente',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		] );
		?>
		<style>
		.calypso-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
		.calypso-meta-field label{display:block;font-weight:600;margin-bottom:4px}
		.calypso-meta-field input,.calypso-meta-field textarea,.calypso-meta-field select{width:100%}
		.calypso-meta-field textarea{min-height:80px}
		.calypso-repeater-row{display:flex;gap:8px;align-items:center;margin-bottom:6px}
		.calypso-repeater-row input{flex:1}
		.calypso-btn-remove{background:#dc3545;color:#fff;border:none;border-radius:3px;padding:2px 8px;cursor:pointer}
		.calypso-section-title{font-weight:700;font-size:13px;margin:16px 0 8px;border-bottom:1px solid #ddd;padding-bottom:4px}
		</style>

		<div class="calypso-meta-grid">
			<div class="calypso-meta-field" style="grid-column:1/-1">
				<label><?php _e( 'Corso', 'calypsosub' ); ?> *</label>
				<select name="calypso_occ_corso_id" required>
					<option value=""><?php _e( '— seleziona corso —', 'calypsosub' ); ?></option>
					<?php foreach ( $all_corsi as $cid ) : ?>
					<option value="<?php echo esc_attr( $cid ); ?>"
					        <?php selected( $d['corso_id'], $cid ); ?>>
						<?php echo esc_html( get_the_title( $cid ) ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Luogo', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_occ_luogo" value="<?php echo esc_attr( $d['luogo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Posti disponibili', 'calypsosub' ); ?> <small>(lascia vuoto se illimitati)</small></label>
				<input type="number" min="0" name="calypso_occ_posti" value="<?php echo esc_attr( $d['posti'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Direttore del corso', 'calypsosub' ); ?></label>
				<select name="calypso_occ_direttore_id">
					<option value=""><?php _e( '— usa default del corso —', 'calypsosub' ); ?></option>
					<?php foreach ( $all_docenti as $did ) : ?>
					<option value="<?php echo esc_attr( $did ); ?>"
					        <?php selected( $d['direttore_id'], $did ); ?>>
						<?php echo esc_html( get_the_title( $did ) ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="calypso-meta-field" style="grid-column:1/-1">
				<label><?php _e( 'Note', 'calypsosub' ); ?></label>
				<textarea name="calypso_occ_note"><?php echo esc_textarea( $d['note'] ); ?></textarea>
			</div>
		</div>

		<p class="calypso-section-title"><?php _e( 'Docenti', 'calypsosub' ); ?></p>
		<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
			<?php foreach ( $all_docenti as $did ) : ?>
			<label style="display:flex;align-items:center;gap:4px">
				<input type="checkbox" name="calypso_occ_docenti_ids[]"
				       value="<?php echo esc_attr( $did ); ?>"
				       <?php checked( in_array( $did, $d['docenti_ids'], true ) ); ?>>
				<?php echo esc_html( get_the_title( $did ) ); ?>
			</label>
			<?php endforeach; ?>
		</div>

		<p class="calypso-section-title"><?php _e( 'Date lezioni', 'calypsosub' ); ?></p>
		<div id="calypso-occ-repeater">
			<?php foreach ( $d['dates'] as $i => $dt ) :
				$dp = $dt ? substr( $dt, 0, 10 ) : '';
				$tp = strlen( $dt ) > 10 ? substr( $dt, 11, 5 ) : '';
			?>
			<div class="calypso-repeater-row">
				<input type="date" name="calypso_occ_dates_d[<?php echo (int) $i; ?>]"
				       value="<?php echo esc_attr( $dp ); ?>" style="flex:2">
				<input type="time" name="calypso_occ_dates_t[<?php echo (int) $i; ?>]"
				       value="<?php echo esc_attr( $tp ); ?>" style="flex:1">
				<button type="button" class="calypso-btn-remove">&#x2715;</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-occ-add">
			<?php _e( '+ Aggiungi data', 'calypsosub' ); ?>
		</button>

		<script>
		(function () {
			var idx = document.getElementById('calypso-occ-repeater')
			            .querySelectorAll('.calypso-repeater-row').length;

			document.getElementById('calypso-occ-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-repeater-row';
				var inpD = document.createElement('input');
				inpD.type = 'date';
				inpD.name = 'calypso_occ_dates_d[' + idx + ']';
				inpD.style.flex = '2';
				var inpT = document.createElement('input');
				inpT.type = 'time';
				inpT.name = 'calypso_occ_dates_t[' + idx + ']';
				inpT.style.flex = '1';
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'calypso-btn-remove';
				btn.textContent = '✕';
				row.appendChild(inpD);
				row.appendChild(inpT);
				row.appendChild(btn);
				document.getElementById('calypso-occ-repeater').appendChild(row);
				idx++;
			});

			document.addEventListener('click', function (e) {
				if (e.target.classList.contains('calypso-btn-remove')) {
					e.target.closest('.calypso-repeater-row').remove();
				}
			});
		})();
		</script>
		<?php
	}

	public function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['calypso_occorrenza_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['calypso_occorrenza_nonce'] ), 'calypso_occorrenza_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		update_post_meta( $post_id, '_occorrenza_corso_id',
			absint( $_POST['calypso_occ_corso_id'] ?? 0 ) );

		update_post_meta( $post_id, '_occorrenza_luogo',
			sanitize_text_field( wp_unslash( $_POST['calypso_occ_luogo'] ?? '' ) ) );

		update_post_meta( $post_id, '_occorrenza_direttore_id',
			absint( $_POST['calypso_occ_direttore_id'] ?? 0 ) );

		update_post_meta( $post_id, '_occorrenza_note',
			sanitize_textarea_field( wp_unslash( $_POST['calypso_occ_note'] ?? '' ) ) );

		$posti_raw = $_POST['calypso_occ_posti'] ?? '';
		if ( $posti_raw !== '' ) {
			update_post_meta( $post_id, '_occorrenza_posti', absint( $posti_raw ) );
		} else {
			delete_post_meta( $post_id, '_occorrenza_posti' );
		}

		$docenti_ids = array_map( 'absint', (array) ( $_POST['calypso_occ_docenti_ids'] ?? [] ) );
		update_post_meta( $post_id, '_occorrenza_docenti_ids', $docenti_ids );

		$dates  = [];
		$raw_d  = (array) ( $_POST['calypso_occ_dates_d'] ?? [] );
		$raw_t  = (array) ( $_POST['calypso_occ_dates_t'] ?? [] );
		foreach ( $raw_d as $i => $dv ) {
			$dv = sanitize_text_field( wp_unslash( $dv ) );
			$tv = sanitize_text_field( wp_unslash( $raw_t[ $i ] ?? '' ) );
			if ( $dv ) $dates[] = $tv ? $dv . 'T' . $tv : $dv;
		}
		update_post_meta( $post_id, '_occorrenza_dates', $dates );

		if ( ! empty( $dates ) ) {
			$timestamps = array_map( 'strtotime', $dates );
			update_post_meta( $post_id, '_occorrenza_data_inizio', date( 'Y-m-d', min( $timestamps ) ) );
			update_post_meta( $post_id, '_occorrenza_data_fine',   date( 'Y-m-d', max( $timestamps ) ) );
		} else {
			delete_post_meta( $post_id, '_occorrenza_data_inizio' );
			delete_post_meta( $post_id, '_occorrenza_data_fine' );
		}
	}

	private function get_meta( int $post_id ): array {
		$posti_raw = get_post_meta( $post_id, '_occorrenza_posti', true );
		return [
			'corso_id'    => (int)    get_post_meta( $post_id, '_occorrenza_corso_id', true ),
			'luogo'       => (string) get_post_meta( $post_id, '_occorrenza_luogo', true ),
			'posti'       => $posti_raw !== '' && $posti_raw !== false ? (string) $posti_raw : '',
			'direttore_id'=> (int)    get_post_meta( $post_id, '_occorrenza_direttore_id', true ),
			'note'        => (string) get_post_meta( $post_id, '_occorrenza_note', true ),
			'docenti_ids' => (array)  ( get_post_meta( $post_id, '_occorrenza_docenti_ids', true ) ?: [] ),
			'dates'       => (array)  ( get_post_meta( $post_id, '_occorrenza_dates', true ) ?: [] ),
		];
	}
}
