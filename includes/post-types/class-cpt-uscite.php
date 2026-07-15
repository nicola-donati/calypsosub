<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Uscite {

	public function init(): void {
		add_action( 'init',                     [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',           [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_uscita', [ $this, 'save_meta' ], 10, 2 );
		add_action( 'admin_enqueue_scripts',    [ $this, 'enqueue_media' ] );
		add_filter( 'admin_post_thumbnail_html', [ $this, 'featured_image_hero_checkbox' ], 10, 2 );
	}

	public function enqueue_media( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'calypso_uscita' ) return;
		wp_enqueue_media();
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

	public function featured_image_hero_checkbox( string $html, int $post_id ): string {
		if ( get_post_type( $post_id ) !== 'calypso_uscita' ) {
			return $html;
		}
		$checked = get_post_meta( $post_id, '_hero_use_featured_image', true ) === '1';
		$html .= '<p style="margin-top:10px;padding-top:10px;border-top:1px solid #ddd">'
			. '<label style="display:flex;align-items:center;gap:6px;cursor:pointer">'
			. '<input type="checkbox" name="calypso_hero_bg" value="1"' . ( $checked ? ' checked' : '' ) . ' style="width:auto;margin:0">'
			. '<span style="font-weight:600">' . esc_html__( 'Usa come sfondo hero', 'calypsosub' ) . '</span>'
			. '</label></p>';
		return $html;
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
		.calypso-fase-row{display:grid;grid-template-columns:1fr 100px auto;gap:8px;align-items:start;margin-bottom:8px;padding:12px;background:#f9f9f9;border-radius:4px}
		.calypso-fase-row__desc{grid-column:1/3}
		.calypso-fase-field{display:flex;flex-direction:column;gap:3px}
		.calypso-fase-field label{font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#444;margin:0}
		.calypso-fase-field label small{font-weight:400;text-transform:none;letter-spacing:0;color:#888;margin-left:4px}
		.calypso-fase-row textarea{min-height:56px;width:100%}
		.calypso-fase-row input,.calypso-fase-row select{width:100%}
		.calypso-btn-remove{background:#dc3545;color:#fff;border:none;border-radius:3px;padding:4px 8px;cursor:pointer;align-self:start}
		.calypso-immersione-row{display:grid;grid-template-columns:1fr 100px 100px 100px 120px auto;gap:8px;align-items:start;margin-bottom:8px;padding:12px;background:#f9f9f9;border-radius:4px}
		.calypso-immersione-row__desc{grid-column:1/6}
		.calypso-immersione-row__foto{grid-column:1/-1;display:flex;align-items:center;gap:8px}
		.calypso-media-row .calypso-thumb{width:48px;height:48px;object-fit:cover;border-radius:3px}
		</style>

		<div class="calypso-meta-grid">
			<div class="calypso-meta-field">
				<label><?php _e( 'Sottotitolo', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_sottotitolo" value="<?php echo esc_attr( $d['sottotitolo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Descrizione breve', 'calypsosub' ); ?> <small><?php _e( 'mostrata come lead nell\'hero', 'calypsosub' ); ?></small></label>
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
			<div class="calypso-meta-field">
				<label><?php _e( 'Imbarco (mezzo)', 'calypsosub' ); ?> <small><?php _e( 'es. "M/B Calypso II"', 'calypsosub' ); ?></small></label>
				<input type="text" name="calypso_imbarco_mezzo" value="<?php echo esc_attr( $d['imbarco_mezzo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Rientro previsto', 'calypsosub' ); ?> <small><?php _e( 'es. "17:00"', 'calypsosub' ); ?></small></label>
				<input type="text" name="calypso_rientro_previsto" value="<?php echo esc_attr( $d['rientro_previsto'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Numero immersioni', 'calypsosub' ); ?> <small><?php _e( 'lascia vuoto per usare il conteggio automatico delle immersioni sotto', 'calypsosub' ); ?></small></label>
				<input type="number" min="0" name="calypso_num_immersioni" value="<?php echo esc_attr( $d['num_immersioni'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Tag fauna aggiuntivi', 'calypsosub' ); ?> <small><?php _e( 'separati da virgola, in aggiunta ai termini "Fauna e habitat" qui sotto', 'calypsosub' ); ?></small></label>
				<input type="text" name="calypso_fauna_extra" value="<?php echo esc_attr( $d['fauna_extra'] ); ?>">
			</div>
		</div>

		<p class="calypso-section-title"><?php _e( 'Testi opzionali', 'calypsosub' ); ?></p>
		<div class="calypso-meta-grid">
			<div class="calypso-meta-field">
				<label><?php _e( 'Incluso nell\'uscita', 'calypsosub' ); ?> <small><?php _e( 'una voce per riga', 'calypsosub' ); ?></small></label>
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

		<p class="calypso-section-title"><?php _e( 'Le immersioni', 'calypsosub' ); ?> <small style="font-weight:400"><?php _e( 'una riga per ogni tuffo previsto — usate anche per generare il programma della giornata', 'calypsosub' ); ?></small></p>
		<div id="calypso-immersioni-repeater">
			<?php foreach ( $d['immersioni'] as $imm ) :
				$foto_id  = (int) ( $imm['foto_id'] ?? 0 );
				$foto_url = $foto_id ? wp_get_attachment_image_url( $foto_id, 'thumbnail' ) : '';
			?>
			<div class="calypso-immersione-row">
				<div class="calypso-fase-field">
					<label><?php _e( 'Nome', 'calypsosub' ); ?></label>
					<input type="text" name="calypso_immersioni_nome[]" value="<?php echo esc_attr( $imm['nome'] ?? '' ); ?>" placeholder="<?php echo esc_attr( __( 'es. La Secca Grande', 'calypsosub' ) ); ?>">
				</div>
				<div class="calypso-fase-field">
					<label><?php _e( 'Ora', 'calypsosub' ); ?></label>
					<input type="text" name="calypso_immersioni_ora[]" value="<?php echo esc_attr( $imm['ora'] ?? '' ); ?>" placeholder="09:30">
				</div>
				<div class="calypso-fase-field">
					<label><?php _e( 'Prof. max (m)', 'calypsosub' ); ?></label>
					<input type="number" name="calypso_immersioni_profondita[]" value="<?php echo esc_attr( $imm['profondita_max'] ?? '' ); ?>">
				</div>
				<div class="calypso-fase-field">
					<label><?php _e( 'Durata fondo (min)', 'calypsosub' ); ?></label>
					<input type="number" name="calypso_immersioni_durata[]" value="<?php echo esc_attr( $imm['durata_fondo'] ?? '' ); ?>">
				</div>
				<div class="calypso-fase-field">
					<label><?php _e( 'Corrente', 'calypsosub' ); ?></label>
					<select name="calypso_immersioni_corrente[]">
						<?php foreach ( [ '' => '—', 'bassa' => __( 'Bassa', 'calypsosub' ), 'media' => __( 'Media', 'calypsosub' ), 'forte' => __( 'Forte', 'calypsosub' ) ] as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $imm['corrente'] ?? '', $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<button type="button" class="calypso-btn-remove" style="margin-top:20px">&#x2715;</button>
				<div class="calypso-fase-field calypso-immersione-row__desc">
					<label><?php _e( 'Descrizione', 'calypsosub' ); ?></label>
					<textarea name="calypso_immersioni_desc[]"><?php echo esc_textarea( $imm['descrizione'] ?? '' ); ?></textarea>
				</div>
				<div class="calypso-immersione-row__foto calypso-media-row">
					<img src="<?php echo esc_url( $foto_url ); ?>" class="calypso-thumb" style="<?php echo $foto_url ? '' : 'display:none'; ?>">
					<input type="hidden" name="calypso_immersioni_foto[]" value="<?php echo (int) $foto_id; ?>">
					<button type="button" class="button calypso-media-change"><?php _e( 'Scegli foto', 'calypsosub' ); ?></button>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-immersioni-add">
			<?php _e( '+ Aggiungi immersione', 'calypsosub' ); ?>
		</button>

		<p class="calypso-section-title" style="margin-top:24px"><?php _e( 'Programma della giornata — override manuale', 'calypsosub' ); ?> <small style="font-weight:400"><?php _e( 'facoltativo: se compilato sostituisce il programma generato automaticamente da ritrovo/imbarco/immersioni/rientro', 'calypsosub' ); ?></small></p>
		<div id="calypso-programma-repeater">
			<?php foreach ( $d['programma_override'] as $tappa ) : ?>
			<div class="calypso-fase-row">
				<div class="calypso-fase-field">
					<label><?php _e( 'Titolo', 'calypsosub' ); ?></label>
					<input type="text" name="calypso_programma_titolo[]" value="<?php echo esc_attr( $tappa['titolo'] ?? '' ); ?>">
				</div>
				<div class="calypso-fase-field">
					<label><?php _e( 'Ora', 'calypsosub' ); ?></label>
					<input type="text" name="calypso_programma_ora[]" value="<?php echo esc_attr( $tappa['ora'] ?? '' ); ?>" placeholder="07:30">
				</div>
				<button type="button" class="calypso-btn-remove" style="margin-top:20px">&#x2715;</button>
				<div class="calypso-fase-field calypso-fase-row__desc">
					<label><?php _e( 'Descrizione', 'calypsosub' ); ?></label>
					<textarea name="calypso_programma_desc[]"><?php echo esc_textarea( $tappa['descrizione'] ?? '' ); ?></textarea>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-programma-add">
			<?php _e( '+ Aggiungi tappa', 'calypsosub' ); ?>
		</button>

		<p class="calypso-section-title" style="margin-top:24px"><?php _e( 'Galleria foto', 'calypsosub' ); ?></p>
		<div id="calypso-galleria-repeater">
			<?php foreach ( $d['galleria'] as $att_id ) :
				$thumb = wp_get_attachment_image_url( $att_id, 'thumbnail' );
			?>
			<div class="calypso-repeater-row calypso-media-row" style="display:inline-flex;align-items:center;gap:8px;margin:0 8px 8px 0">
				<img src="<?php echo esc_url( $thumb ); ?>" class="calypso-thumb" style="width:60px;height:60px;object-fit:cover;border-radius:3px">
				<input type="hidden" name="calypso_galleria[]" value="<?php echo (int) $att_id; ?>">
				<button type="button" class="button calypso-media-change"><?php _e( 'Cambia', 'calypsosub' ); ?></button>
				<button type="button" class="calypso-btn-remove">&#x2715;</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-galleria-add">
			<?php _e( '+ Aggiungi immagine', 'calypsosub' ); ?>
		</button>

		<script>
		(function () {
			function openMediaPicker(options, onSelect) {
				var frame = wp.media({
					title:    options.title || <?php echo wp_json_encode( __( 'Seleziona immagine', 'calypsosub' ) ); ?>,
					button:   { text: options.btnText || <?php echo wp_json_encode( __( 'Seleziona', 'calypsosub' ) ); ?> },
					multiple: options.multiple || false,
					library:  { type: 'image' }
				});
				frame.on('select', function () {
					onSelect(frame.state().get('selection').toArray());
				});
				frame.open();
			}

			/* ── Immersioni repeater ── */
			document.getElementById('calypso-immersioni-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-immersione-row';
				row.innerHTML =
					'<div class="calypso-fase-field"><label><?php echo esc_js( __( 'Nome', 'calypsosub' ) ); ?></label>'
						+ '<input type="text" name="calypso_immersioni_nome[]"></div>'
					+ '<div class="calypso-fase-field"><label><?php echo esc_js( __( 'Ora', 'calypsosub' ) ); ?></label>'
						+ '<input type="text" name="calypso_immersioni_ora[]" placeholder="09:30"></div>'
					+ '<div class="calypso-fase-field"><label><?php echo esc_js( __( 'Prof. max (m)', 'calypsosub' ) ); ?></label>'
						+ '<input type="number" name="calypso_immersioni_profondita[]"></div>'
					+ '<div class="calypso-fase-field"><label><?php echo esc_js( __( 'Durata fondo (min)', 'calypsosub' ) ); ?></label>'
						+ '<input type="number" name="calypso_immersioni_durata[]"></div>'
					+ '<div class="calypso-fase-field"><label><?php echo esc_js( __( 'Corrente', 'calypsosub' ) ); ?></label>'
						+ '<select name="calypso_immersioni_corrente[]">'
						+ '<option value="">—</option><option value="bassa"><?php echo esc_js( __( 'Bassa', 'calypsosub' ) ); ?></option>'
						+ '<option value="media"><?php echo esc_js( __( 'Media', 'calypsosub' ) ); ?></option>'
						+ '<option value="forte"><?php echo esc_js( __( 'Forte', 'calypsosub' ) ); ?></option></select></div>'
					+ '<button type="button" class="calypso-btn-remove" style="margin-top:20px">✕</button>'
					+ '<div class="calypso-fase-field calypso-immersione-row__desc"><label><?php echo esc_js( __( 'Descrizione', 'calypsosub' ) ); ?></label>'
						+ '<textarea name="calypso_immersioni_desc[]"></textarea></div>'
					+ '<div class="calypso-immersione-row__foto calypso-media-row">'
						+ '<img class="calypso-thumb" style="display:none">'
						+ '<input type="hidden" name="calypso_immersioni_foto[]" value="0">'
						+ '<button type="button" class="button calypso-media-change"><?php echo esc_js( __( 'Scegli foto', 'calypsosub' ) ); ?></button></div>';
				document.getElementById('calypso-immersioni-repeater').appendChild(row);
			});

			/* ── Programma override repeater ── */
			document.getElementById('calypso-programma-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-fase-row';
				row.innerHTML =
					'<div class="calypso-fase-field"><label><?php echo esc_js( __( 'Titolo', 'calypsosub' ) ); ?></label>'
						+ '<input type="text" name="calypso_programma_titolo[]"></div>'
					+ '<div class="calypso-fase-field"><label><?php echo esc_js( __( 'Ora', 'calypsosub' ) ); ?></label>'
						+ '<input type="text" name="calypso_programma_ora[]" placeholder="07:30"></div>'
					+ '<button type="button" class="calypso-btn-remove" style="margin-top:20px">✕</button>'
					+ '<div class="calypso-fase-field calypso-fase-row__desc"><label><?php echo esc_js( __( 'Descrizione', 'calypsosub' ) ); ?></label>'
						+ '<textarea name="calypso_programma_desc[]"></textarea></div>';
				document.getElementById('calypso-programma-repeater').appendChild(row);
			});

			/* ── Galleria repeater ── */
			document.getElementById('calypso-galleria-add').addEventListener('click', function () {
				openMediaPicker({ multiple: true }, function (attachments) {
					var repeater = document.getElementById('calypso-galleria-repeater');
					attachments.forEach(function (att) {
						var a = att.toJSON();
						var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
						var row = document.createElement('div');
						row.className = 'calypso-repeater-row calypso-media-row';
						row.style.cssText = 'display:inline-flex;align-items:center;gap:8px;margin:0 8px 8px 0';
						row.innerHTML =
							'<img src="' + thumb + '" class="calypso-thumb" style="width:60px;height:60px;object-fit:cover;border-radius:3px">'
							+ '<input type="hidden" name="calypso_galleria[]" value="' + a.id + '">'
							+ '<button type="button" class="button calypso-media-change"><?php echo esc_js( __( 'Cambia', 'calypsosub' ) ); ?></button>'
							+ '<button type="button" class="calypso-btn-remove">✕</button>';
						repeater.appendChild(row);
					});
				});
			});

			document.addEventListener('click', function (e) {
				if (e.target.classList.contains('calypso-media-change')) {
					var row = e.target.closest('.calypso-media-row');
					openMediaPicker({ multiple: false }, function (attachments) {
						var a = attachments[0].toJSON();
						var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
						var img = row.querySelector('.calypso-thumb');
						img.src = thumb;
						img.style.display = '';
						row.querySelector('input[type=hidden]').value = a.id;
					});
				}
				if (e.target.classList.contains('calypso-btn-remove')) {
					e.target.closest('.calypso-immersione-row, .calypso-fase-row, .calypso-repeater-row').remove();
				}
			});
		})();
		</script>
		<?php
	}

	public function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['calypso_uscita_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['calypso_uscita_nonce'] ), 'calypso_uscita_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$text_fields = [
			'_uscita_sottotitolo'        => 'calypso_sottotitolo',
			'_uscita_desc_breve'         => 'calypso_desc_breve',
			'_uscita_luogo'              => 'calypso_luogo',
			'_uscita_ritrovo'            => 'calypso_ritrovo',
			'_uscita_incluso'            => 'calypso_incluso',
			'_uscita_cosa_portare'       => 'calypso_cosa_portare',
			'_uscita_note_cancellazione' => 'calypso_note_cancellazione',
			'_uscita_imbarco_mezzo'      => 'calypso_imbarco_mezzo',
			'_uscita_rientro_previsto'   => 'calypso_rientro_previsto',
			'_uscita_fauna_extra'        => 'calypso_fauna_extra',
		];
		foreach ( $text_fields as $meta_key => $post_key ) {
			update_post_meta( $post_id, $meta_key,
				sanitize_textarea_field( wp_unslash( $_POST[ $post_key ] ?? '' ) ) );
		}

		$num_imm_raw = $_POST['calypso_num_immersioni'] ?? '';
		if ( $num_imm_raw !== '' ) {
			update_post_meta( $post_id, '_uscita_num_immersioni', absint( $num_imm_raw ) );
		} else {
			delete_post_meta( $post_id, '_uscita_num_immersioni' );
		}

		update_post_meta( $post_id, '_hero_use_featured_image',
			isset( $_POST['calypso_hero_bg'] ) ? '1' : '0' );

		/* Immersioni: repeater → array di array associativi */
		$imm_nome  = (array) ( $_POST['calypso_immersioni_nome']      ?? [] );
		$imm_ora   = (array) ( $_POST['calypso_immersioni_ora']       ?? [] );
		$imm_desc  = (array) ( $_POST['calypso_immersioni_desc']      ?? [] );
		$imm_prof  = (array) ( $_POST['calypso_immersioni_profondita'] ?? [] );
		$imm_dur   = (array) ( $_POST['calypso_immersioni_durata']    ?? [] );
		$imm_corr  = (array) ( $_POST['calypso_immersioni_corrente']  ?? [] );
		$imm_foto  = (array) ( $_POST['calypso_immersioni_foto']      ?? [] );
		$immersioni = [];
		foreach ( $imm_nome as $i => $nome ) {
			$nome = sanitize_text_field( wp_unslash( $nome ) );
			if ( $nome === '' ) continue;
			$immersioni[] = [
				'nome'           => $nome,
				'ora'            => sanitize_text_field( wp_unslash( $imm_ora[ $i ] ?? '' ) ),
				'descrizione'    => sanitize_textarea_field( wp_unslash( $imm_desc[ $i ] ?? '' ) ),
				'profondita_max' => absint( $imm_prof[ $i ] ?? 0 ),
				'durata_fondo'   => absint( $imm_dur[ $i ] ?? 0 ),
				'corrente'       => sanitize_key( wp_unslash( $imm_corr[ $i ] ?? '' ) ),
				'foto_id'        => absint( $imm_foto[ $i ] ?? 0 ),
			];
		}
		update_post_meta( $post_id, '_uscita_immersioni', $immersioni );

		/* Programma override: repeater → array di array associativi */
		$prg_titolo = (array) ( $_POST['calypso_programma_titolo'] ?? [] );
		$prg_ora    = (array) ( $_POST['calypso_programma_ora']    ?? [] );
		$prg_desc   = (array) ( $_POST['calypso_programma_desc']   ?? [] );
		$programma  = [];
		foreach ( $prg_titolo as $i => $titolo ) {
			$titolo = sanitize_text_field( wp_unslash( $titolo ) );
			if ( $titolo === '' ) continue;
			$programma[] = [
				'titolo'      => $titolo,
				'ora'         => sanitize_text_field( wp_unslash( $prg_ora[ $i ] ?? '' ) ),
				'descrizione' => sanitize_textarea_field( wp_unslash( $prg_desc[ $i ] ?? '' ) ),
			];
		}
		update_post_meta( $post_id, '_uscita_programma_override', $programma );

		/* Galleria: array di attachment ID */
		$galleria = [];
		foreach ( (array) ( $_POST['calypso_galleria'] ?? [] ) as $att_id ) {
			$att_id = absint( $att_id );
			if ( $att_id ) $galleria[] = $att_id;
		}
		update_post_meta( $post_id, '_uscita_galleria', $galleria );
	}

	private function get_meta( int $post_id ): array {
		$num_imm_raw = get_post_meta( $post_id, '_uscita_num_immersioni', true );
		return [
			'sottotitolo'        => (string) get_post_meta( $post_id, '_uscita_sottotitolo', true ),
			'desc_breve'         => (string) get_post_meta( $post_id, '_uscita_desc_breve', true ),
			'luogo'              => (string) get_post_meta( $post_id, '_uscita_luogo', true ),
			'ritrovo'            => (string) get_post_meta( $post_id, '_uscita_ritrovo', true ),
			'incluso'            => (string) get_post_meta( $post_id, '_uscita_incluso', true ),
			'cosa_portare'       => (string) get_post_meta( $post_id, '_uscita_cosa_portare', true ),
			'note_cancellazione' => (string) get_post_meta( $post_id, '_uscita_note_cancellazione', true ),
			'imbarco_mezzo'      => (string) get_post_meta( $post_id, '_uscita_imbarco_mezzo', true ),
			'rientro_previsto'   => (string) get_post_meta( $post_id, '_uscita_rientro_previsto', true ),
			'num_immersioni'     => $num_imm_raw !== '' && $num_imm_raw !== false ? (string) $num_imm_raw : '',
			'fauna_extra'        => (string) get_post_meta( $post_id, '_uscita_fauna_extra', true ),
			'immersioni'         => (array) ( get_post_meta( $post_id, '_uscita_immersioni', true ) ?: [] ),
			'programma_override' => (array) ( get_post_meta( $post_id, '_uscita_programma_override', true ) ?: [] ),
			'galleria'           => array_map( 'absint', (array) ( get_post_meta( $post_id, '_uscita_galleria', true ) ?: [] ) ),
			'hero_use_featured_image' => (string) get_post_meta( $post_id, '_hero_use_featured_image', true ),
		];
	}
}
