<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Corsi {

	public function init(): void {
		add_action( 'init',                    [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',          [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_corso', [ $this, 'save_meta' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_corso', [
			'label'        => __( 'Corsi', 'calypsosub' ),
			'labels'       => [
				'name'          => __( 'Corsi', 'calypsosub' ),
				'singular_name' => __( 'Corso', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi corso', 'calypsosub' ),
				'edit_item'     => __( 'Modifica corso', 'calypsosub' ),
				'not_found'     => __( 'Nessun corso trovato', 'calypsosub' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'editor', 'thumbnail' ],
			'menu_icon'    => 'dashicons-welcome-learn-more',
			'rewrite'      => [ 'slug' => 'corsi' ],
			'has_archive'  => true,
		] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'calypso_corso_meta',
			__( 'Dettagli corso', 'calypsosub' ),
			[ $this, 'render_meta_box' ],
			'calypso_corso',
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		$d = $this->get_meta( $post->ID );
		wp_nonce_field( 'calypso_corso_meta', 'calypso_corso_nonce' );

		$all_docenti = get_posts( [
			'post_type'      => 'calypso_docente',
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
				<label><?php _e( 'Materiale incluso', 'calypsosub' ); ?></label>
				<textarea name="calypso_materiale"><?php echo esc_textarea( $d['materiale'] ); ?></textarea>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Data inizio', 'calypsosub' ); ?></label>
				<input type="date" name="calypso_data_inizio" value="<?php echo esc_attr( $d['data_inizio'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Data fine', 'calypsosub' ); ?></label>
				<input type="date" name="calypso_data_fine" value="<?php echo esc_attr( $d['data_fine'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Direttore del corso', 'calypsosub' ); ?></label>
				<select name="calypso_direttore_id">
					<option value=""><?php _e( '— seleziona —', 'calypsosub' ); ?></option>
					<?php foreach ( $all_docenti as $did ) : ?>
					<option value="<?php echo esc_attr( $did ); ?>"
					        <?php selected( $d['direttore_id'], $did ); ?>>
						<?php echo esc_html( get_the_title( $did ) ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<p class="calypso-section-title"><?php _e( 'Docenti del corso', 'calypsosub' ); ?></p>
		<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
			<?php foreach ( $all_docenti as $did ) : ?>
			<label style="display:flex;align-items:center;gap:4px">
				<input type="checkbox" name="calypso_docenti_ids[]"
				       value="<?php echo esc_attr( $did ); ?>"
				       <?php checked( in_array( $did, $d['docenti_ids'], true ) ); ?>>
				<?php echo esc_html( get_the_title( $did ) ); ?>
			</label>
			<?php endforeach; ?>
		</div>

		<p class="calypso-section-title"><?php _e( 'Date lezioni', 'calypsosub' ); ?></p>
		<div id="calypso-lezioni-repeater">
			<?php foreach ( $d['date_lezioni'] as $i => $dt ) :
				$dp = $dt ? substr( $dt, 0, 10 ) : '';
				$tp = strlen( $dt ) > 10 ? substr( $dt, 11, 5 ) : '';
			?>
			<div class="calypso-repeater-row">
				<input type="date" name="calypso_date_lezioni_d[<?php echo (int) $i; ?>]"
				       value="<?php echo esc_attr( $dp ); ?>" style="flex:2">
				<input type="time" name="calypso_date_lezioni_t[<?php echo (int) $i; ?>]"
				       value="<?php echo esc_attr( $tp ); ?>" style="flex:1">
				<button type="button" class="calypso-btn-remove">&#x2715;</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-lezioni-add">
			<?php _e( '+ Aggiungi lezione', 'calypsosub' ); ?>
		</button>

		<script>
		(function () {
			var idx = document.getElementById('calypso-lezioni-repeater')
			            .querySelectorAll('.calypso-repeater-row').length;

			document.getElementById('calypso-lezioni-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-repeater-row';
				var inpD = document.createElement('input');
				inpD.type = 'date';
				inpD.name = 'calypso_date_lezioni_d[' + idx + ']';
				inpD.style.flex = '2';
				var inpT = document.createElement('input');
				inpT.type = 'time';
				inpT.name = 'calypso_date_lezioni_t[' + idx + ']';
				inpT.style.flex = '1';
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'calypso-btn-remove';
				btn.textContent = '✕';
				row.appendChild(inpD);
				row.appendChild(inpT);
				row.appendChild(btn);
				document.getElementById('calypso-lezioni-repeater').appendChild(row);
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
		if ( ! isset( $_POST['calypso_corso_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['calypso_corso_nonce'] ), 'calypso_corso_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$text_fields = [
			'_corso_sottotitolo' => 'calypso_sottotitolo',
			'_corso_desc_breve'  => 'calypso_desc_breve',
			'_corso_luogo'       => 'calypso_luogo',
			'_corso_materiale'   => 'calypso_materiale',
			'_corso_data_inizio' => 'calypso_data_inizio',
			'_corso_data_fine'   => 'calypso_data_fine',
		];
		foreach ( $text_fields as $meta_key => $post_key ) {
			update_post_meta( $post_id, $meta_key,
				sanitize_text_field( wp_unslash( $_POST[ $post_key ] ?? '' ) ) );
		}

		update_post_meta( $post_id, '_corso_direttore_id',
			absint( $_POST['calypso_direttore_id'] ?? 0 ) );

		$docenti_ids = array_map( 'absint', (array) ( $_POST['calypso_docenti_ids'] ?? [] ) );
		update_post_meta( $post_id, '_corso_docenti_ids', $docenti_ids );

		$date_lezioni = [];
		$lezioni_d    = (array) ( $_POST['calypso_date_lezioni_d'] ?? [] );
		$lezioni_t    = (array) ( $_POST['calypso_date_lezioni_t'] ?? [] );
		foreach ( $lezioni_d as $i => $dv ) {
			$dv = sanitize_text_field( wp_unslash( $dv ) );
			$tv = sanitize_text_field( wp_unslash( $lezioni_t[ $i ] ?? '' ) );
			if ( $dv ) $date_lezioni[] = $tv ? $dv . 'T' . $tv : $dv;
		}
		update_post_meta( $post_id, '_corso_date_lezioni', $date_lezioni );
	}

	private function get_meta( int $post_id ): array {
		return [
			'sottotitolo'  => (string) get_post_meta( $post_id, '_corso_sottotitolo', true ),
			'desc_breve'   => (string) get_post_meta( $post_id, '_corso_desc_breve', true ),
			'luogo'        => (string) get_post_meta( $post_id, '_corso_luogo', true ),
			'materiale'    => (string) get_post_meta( $post_id, '_corso_materiale', true ),
			'data_inizio'  => (string) get_post_meta( $post_id, '_corso_data_inizio', true ),
			'data_fine'    => (string) get_post_meta( $post_id, '_corso_data_fine', true ),
			'direttore_id' => (int)    get_post_meta( $post_id, '_corso_direttore_id', true ),
			'docenti_ids'  => (array)  ( get_post_meta( $post_id, '_corso_docenti_ids', true ) ?: [] ),
			'date_lezioni' => (array)  ( get_post_meta( $post_id, '_corso_date_lezioni', true ) ?: [] ),
		];
	}
}
