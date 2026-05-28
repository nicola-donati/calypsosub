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
			'label'           => __( 'Corsi', 'calypsosub' ),
			'labels'          => [
				'name'          => __( 'Corsi', 'calypsosub' ),
				'singular_name' => __( 'Corso', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi corso', 'calypsosub' ),
				'edit_item'     => __( 'Modifica corso', 'calypsosub' ),
				'not_found'     => __( 'Nessun corso trovato', 'calypsosub' ),
			],
			'public'          => true,
			'show_in_rest'    => true,
			'supports'        => [ 'title', 'editor', 'thumbnail' ],
			'menu_icon'       => 'dashicons-welcome-learn-more',
			'rewrite'         => [ 'slug' => 'corsi' ],
			'has_archive'     => true,
			'capability_type' => 'post',
			'map_meta_cap'    => true,
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
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		] );

		$ph_titolo = esc_js( __( 'Titolo fase (es. AULA)', 'calypsosub' ) );
		$ph_ore    = esc_js( __( 'ore', 'calypsosub' ) );
		$ph_desc   = esc_js( __( 'Descrizione', 'calypsosub' ) );
		?>
		<style>
		.calypso-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
		.calypso-meta-field label{display:block;font-weight:600;margin-bottom:4px}
		.calypso-meta-field input,.calypso-meta-field textarea,.calypso-meta-field select{width:100%}
		.calypso-meta-field textarea{min-height:80px}
		.calypso-section-title{font-weight:700;font-size:13px;margin:16px 0 8px;border-bottom:1px solid #ddd;padding-bottom:4px}
		.calypso-fase-row{display:grid;grid-template-columns:1fr 80px auto;gap:8px;align-items:start;margin-bottom:8px;padding:10px;background:#f9f9f9;border-radius:4px}
		.calypso-fase-row__desc{grid-column:1/3}
		.calypso-fase-row textarea{min-height:56px;width:100%}
		.calypso-fase-row input{width:100%}
		.calypso-btn-remove{background:#dc3545;color:#fff;border:none;border-radius:3px;padding:4px 8px;cursor:pointer;align-self:start}
		</style>

		<div class="calypso-meta-grid">
			<div class="calypso-meta-field" style="grid-column:1/-1">
				<label><?php _e( 'Titolo display', 'calypsosub' ); ?> <small>(es. "QUATTRO FASI, NOVE MESI.")</small></label>
				<input type="text" name="calypso_sottotitolo" value="<?php echo esc_attr( $d['sottotitolo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Badge certificazione', 'calypsosub' ); ?> <small>(es. "PADI · CMAS")</small></label>
				<input type="text" name="calypso_badge" value="<?php echo esc_attr( $d['badge'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Periodo tipico', 'calypsosub' ); ?> <small>(es. "Sett–Giu")</small></label>
				<input type="text" name="calypso_periodo" value="<?php echo esc_attr( $d['periodo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Descrizione breve', 'calypsosub' ); ?></label>
				<textarea name="calypso_desc_breve"><?php echo esc_textarea( $d['desc_breve'] ); ?></textarea>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Materiale incluso', 'calypsosub' ); ?></label>
				<textarea name="calypso_materiale"><?php echo esc_textarea( $d['materiale'] ); ?></textarea>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Durata', 'calypsosub' ); ?> <small>(es. "32 ore")</small></label>
				<input type="text" name="calypso_stat_durata" value="<?php echo esc_attr( $d['stat_durata'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Immersioni / pratica', 'calypsosub' ); ?> <small>(es. "4 immersioni")</small></label>
				<input type="text" name="calypso_stat_pratica" value="<?php echo esc_attr( $d['stat_pratica'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Profondità', 'calypsosub' ); ?> <small>(es. "18 m")</small></label>
				<input type="text" name="calypso_stat_profondita" value="<?php echo esc_attr( $d['stat_profondita'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Link iscrizione esterno', 'calypsosub' ); ?></label>
				<input type="url" name="calypso_link_iscrizione" value="<?php echo esc_attr( $d['link_iscrizione'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Contatto sidebar', 'calypsosub' ); ?> <small>(tel. o email)</small></label>
				<input type="text" name="calypso_contatto" value="<?php echo esc_attr( $d['contatto'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Direttore del corso (default)', 'calypsosub' ); ?></label>
				<select name="calypso_direttore_id">
					<option value=""><?php _e( '— nessuno —', 'calypsosub' ); ?></option>
					<?php foreach ( $all_docenti as $did ) : ?>
					<option value="<?php echo esc_attr( $did ); ?>"
					        <?php selected( $d['direttore_id'], $did ); ?>>
						<?php echo esc_html( get_the_title( $did ) ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<p class="calypso-section-title"><?php _e( 'Cosa imparerai', 'calypsosub' ); ?> <small style="font-weight:400">(una competenza per riga)</small></p>
		<textarea name="calypso_competenze" style="width:100%;min-height:100px"><?php echo esc_textarea( $d['competenze'] ); ?></textarea>

		<p class="calypso-section-title"><?php _e( 'Docenti del corso (default)', 'calypsosub' ); ?></p>
		<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
			<?php foreach ( $all_docenti as $did ) : ?>
			<label style="display:flex;align-items:center;gap:4px">
				<input type="checkbox" name="calypso_docenti_ids[]"
				       value="<?php echo esc_attr( $did ); ?>"
				       <?php checked( in_array( $did, $d['docenti_ids'], true ) ); ?>>
				<?php echo esc_html( get_the_title( $did ) ); ?>
			</label>
			<?php endforeach; ?>
		</div>

		<p class="calypso-section-title"><?php _e( 'Programma del corso (fasi)', 'calypsosub' ); ?></p>
		<div id="calypso-fasi-repeater">
			<?php foreach ( $d['fasi'] as $fase ) : ?>
			<div class="calypso-fase-row">
				<input type="text" name="calypso_fasi_titolo[]"
				       placeholder="<?php echo esc_attr( __( 'Titolo fase', 'calypsosub' ) ); ?>"
				       value="<?php echo esc_attr( $fase['titolo'] ?? '' ); ?>">
				<input type="text" name="calypso_fasi_ore[]"
				       placeholder="<?php echo esc_attr( __( 'ore', 'calypsosub' ) ); ?>"
				       value="<?php echo esc_attr( $fase['ore'] ?? '' ); ?>">
				<button type="button" class="calypso-btn-remove">&#x2715;</button>
				<textarea name="calypso_fasi_desc[]"
				          class="calypso-fase-row__desc"
				          placeholder="<?php echo esc_attr( __( 'Descrizione', 'calypsosub' ) ); ?>"><?php echo esc_textarea( $fase['desc'] ?? '' ); ?></textarea>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-fasi-add">
			<?php _e( '+ Aggiungi fase', 'calypsosub' ); ?>
		</button>

		<script>
		(function () {
			var phTitolo = '<?php echo $ph_titolo; ?>';
			var phOre    = '<?php echo $ph_ore; ?>';
			var phDesc   = '<?php echo $ph_desc; ?>';

			document.getElementById('calypso-fasi-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-fase-row';

				var inpT = document.createElement('input');
				inpT.type = 'text';
				inpT.name = 'calypso_fasi_titolo[]';
				inpT.placeholder = phTitolo;
				inpT.style.width = '100%';

				var inpO = document.createElement('input');
				inpO.type = 'text';
				inpO.name = 'calypso_fasi_ore[]';
				inpO.placeholder = phOre;
				inpO.style.width = '100%';

				var btnR = document.createElement('button');
				btnR.type = 'button';
				btnR.className = 'calypso-btn-remove';
				btnR.textContent = '✕';

				var ta = document.createElement('textarea');
				ta.name = 'calypso_fasi_desc[]';
				ta.className = 'calypso-fase-row__desc';
				ta.placeholder = phDesc;
				ta.style.minHeight = '56px';
				ta.style.width = '100%';

				row.appendChild(inpT);
				row.appendChild(inpO);
				row.appendChild(btnR);
				row.appendChild(ta);
				document.getElementById('calypso-fasi-repeater').appendChild(row);
			});

			document.addEventListener('click', function (e) {
				if (e.target.classList.contains('calypso-btn-remove')) {
					e.target.closest('.calypso-fase-row').remove();
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
			'_corso_sottotitolo'     => 'calypso_sottotitolo',
			'_corso_badge'           => 'calypso_badge',
			'_corso_periodo'         => 'calypso_periodo',
			'_corso_desc_breve'      => 'calypso_desc_breve',
			'_corso_materiale'       => 'calypso_materiale',
			'_corso_stat_durata'     => 'calypso_stat_durata',
			'_corso_stat_pratica'    => 'calypso_stat_pratica',
			'_corso_stat_profondita' => 'calypso_stat_profondita',
			'_corso_contatto'        => 'calypso_contatto',
		];
		foreach ( $text_fields as $meta_key => $post_key ) {
			update_post_meta( $post_id, $meta_key,
				sanitize_text_field( wp_unslash( $_POST[ $post_key ] ?? '' ) ) );
		}

		update_post_meta( $post_id, '_corso_link_iscrizione',
			esc_url_raw( wp_unslash( $_POST['calypso_link_iscrizione'] ?? '' ) ) );

		update_post_meta( $post_id, '_corso_competenze',
			sanitize_textarea_field( wp_unslash( $_POST['calypso_competenze'] ?? '' ) ) );

		update_post_meta( $post_id, '_corso_direttore_id',
			absint( $_POST['calypso_direttore_id'] ?? 0 ) );

		$docenti_ids = array_map( 'absint', (array) ( $_POST['calypso_docenti_ids'] ?? [] ) );
		update_post_meta( $post_id, '_corso_docenti_ids', $docenti_ids );

		$fasi_t = (array) ( $_POST['calypso_fasi_titolo'] ?? [] );
		$fasi_d = (array) ( $_POST['calypso_fasi_desc'] ?? [] );
		$fasi_o = (array) ( $_POST['calypso_fasi_ore'] ?? [] );
		$fasi   = [];
		foreach ( $fasi_t as $i => $t ) {
			$t = sanitize_text_field( wp_unslash( $t ) );
			if ( $t ) {
				$fasi[] = [
					'titolo' => $t,
					'desc'   => sanitize_textarea_field( wp_unslash( $fasi_d[ $i ] ?? '' ) ),
					'ore'    => sanitize_text_field( wp_unslash( $fasi_o[ $i ] ?? '' ) ),
				];
			}
		}
		update_post_meta( $post_id, '_corso_fasi', $fasi );
	}

	private function get_meta( int $post_id ): array {
		return [
			'sottotitolo'     => (string) get_post_meta( $post_id, '_corso_sottotitolo', true ),
			'badge'           => (string) get_post_meta( $post_id, '_corso_badge', true ),
			'periodo'         => (string) get_post_meta( $post_id, '_corso_periodo', true ),
			'desc_breve'      => (string) get_post_meta( $post_id, '_corso_desc_breve', true ),
			'materiale'       => (string) get_post_meta( $post_id, '_corso_materiale', true ),
			'stat_durata'     => (string) get_post_meta( $post_id, '_corso_stat_durata', true ),
			'stat_pratica'    => (string) get_post_meta( $post_id, '_corso_stat_pratica', true ),
			'stat_profondita' => (string) get_post_meta( $post_id, '_corso_stat_profondita', true ),
			'link_iscrizione' => (string) get_post_meta( $post_id, '_corso_link_iscrizione', true ),
			'contatto'        => (string) get_post_meta( $post_id, '_corso_contatto', true ),
			'competenze'      => (string) get_post_meta( $post_id, '_corso_competenze', true ),
			'direttore_id'    => (int)    get_post_meta( $post_id, '_corso_direttore_id', true ),
			'docenti_ids'     => (array)  ( get_post_meta( $post_id, '_corso_docenti_ids', true ) ?: [] ),
			'fasi'            => (array)  ( get_post_meta( $post_id, '_corso_fasi', true ) ?: [] ),
		];
	}
}
