<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Eventi {

	public function init(): void {
		add_action( 'init',                     [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',           [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_evento', [ $this, 'save_meta' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_evento', [
			'label'        => __( 'Eventi', 'calypsosub' ),
			'labels'       => [
				'name'          => __( 'Eventi', 'calypsosub' ),
				'singular_name' => __( 'Evento', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi evento', 'calypsosub' ),
				'edit_item'     => __( 'Modifica evento', 'calypsosub' ),
				'not_found'     => __( 'Nessun evento trovato', 'calypsosub' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'editor', 'thumbnail' ],
			'menu_icon'    => 'dashicons-calendar-alt',
			'rewrite'      => [ 'slug' => 'eventi' ],
			'has_archive'  => true,
		] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'calypso_evento_meta',
			__( 'Dettagli evento', 'calypsosub' ),
			[ $this, 'render_meta_box' ],
			'calypso_evento',
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		$d = $this->get_meta( $post->ID );
		wp_nonce_field( 'calypso_evento_meta', 'calypso_evento_nonce' );
		?>
		<style>
		.calypso-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
		.calypso-meta-field label{display:block;font-weight:600;margin-bottom:4px}
		.calypso-meta-field input,.calypso-meta-field textarea{width:100%}
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
				<label><?php _e( 'Max partecipanti (vuoto = libera)', 'calypsosub' ); ?></label>
				<input type="number" min="0" name="calypso_max_partecipanti"
				       value="<?php echo esc_attr( $d['max_partecipanti'] ); ?>">
			</div>
		</div>

		<div class="calypso-meta-field" style="margin-bottom:12px">
			<label>
				<input type="checkbox" name="calypso_lista_attesa" value="1"
				       <?php checked( $d['lista_attesa'], 1 ); ?>>
				<?php _e( 'Abilita lista d\'attesa', 'calypsosub' ); ?>
			</label>
		</div>

		<p class="calypso-section-title"><?php _e( 'Date', 'calypsosub' ); ?></p>
		<div id="calypso-date-repeater">
			<?php foreach ( $d['date'] as $i => $dt ) :
				$dp = $dt ? substr( $dt, 0, 10 ) : '';
				$tp = strlen( $dt ) > 10 ? substr( $dt, 11, 5 ) : '';
			?>
			<div class="calypso-repeater-row">
				<input type="date" name="calypso_date_d[<?php echo (int) $i; ?>]"
				       value="<?php echo esc_attr( $dp ); ?>" style="flex:2">
				<input type="time" name="calypso_date_t[<?php echo (int) $i; ?>]"
				       value="<?php echo esc_attr( $tp ); ?>" style="flex:1">
				<button type="button" class="calypso-btn-remove">&#x2715;</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-date-add">
			<?php _e( '+ Aggiungi data', 'calypsosub' ); ?>
		</button>

		<script>
		(function () {
			var idx = document.getElementById('calypso-date-repeater')
			            .querySelectorAll('.calypso-repeater-row').length;

			document.getElementById('calypso-date-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-repeater-row';
				var inpD = document.createElement('input');
				inpD.type = 'date';
				inpD.name = 'calypso_date_d[' + idx + ']';
				inpD.style.flex = '2';
				var inpT = document.createElement('input');
				inpT.type = 'time';
				inpT.name = 'calypso_date_t[' + idx + ']';
				inpT.style.flex = '1';
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'calypso-btn-remove';
				btn.textContent = '✕';
				row.appendChild(inpD);
				row.appendChild(inpT);
				row.appendChild(btn);
				document.getElementById('calypso-date-repeater').appendChild(row);
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
		if ( ! isset( $_POST['calypso_evento_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['calypso_evento_nonce'] ), 'calypso_evento_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$text_fields = [
			'_evento_sottotitolo' => 'calypso_sottotitolo',
			'_evento_desc_breve'  => 'calypso_desc_breve',
			'_evento_luogo'       => 'calypso_luogo',
		];
		foreach ( $text_fields as $meta_key => $post_key ) {
			update_post_meta( $post_id, $meta_key,
				sanitize_textarea_field( wp_unslash( $_POST[ $post_key ] ?? '' ) ) );
		}

		$val = $_POST['calypso_max_partecipanti'] ?? '';
		update_post_meta( $post_id, '_evento_max_partecipanti', $val === '' ? '' : absint( $val ) );

		update_post_meta( $post_id, '_evento_lista_attesa',
			isset( $_POST['calypso_lista_attesa'] ) ? 1 : 0 );

		$date    = [];
		$dates_d = (array) ( $_POST['calypso_date_d'] ?? [] );
		$dates_t = (array) ( $_POST['calypso_date_t'] ?? [] );
		foreach ( $dates_d as $i => $dv ) {
			$dv = sanitize_text_field( wp_unslash( $dv ) );
			$tv = sanitize_text_field( wp_unslash( $dates_t[ $i ] ?? '' ) );
			if ( $dv ) $date[] = $tv ? $dv . 'T' . $tv : $dv;
		}
		update_post_meta( $post_id, '_evento_date', $date );
	}

	private function get_meta( int $post_id ): array {
		return [
			'sottotitolo'      => (string) get_post_meta( $post_id, '_evento_sottotitolo', true ),
			'desc_breve'       => (string) get_post_meta( $post_id, '_evento_desc_breve', true ),
			'luogo'            => (string) get_post_meta( $post_id, '_evento_luogo', true ),
			'max_partecipanti' => get_post_meta( $post_id, '_evento_max_partecipanti', true ),
			'lista_attesa'     => (int) get_post_meta( $post_id, '_evento_lista_attesa', true ),
			'date'             => (array) ( get_post_meta( $post_id, '_evento_date', true ) ?: [] ),
		];
	}
}
