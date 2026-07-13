<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Corsi {

	public function init(): void {
		add_action( 'init',                    [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',          [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_corso', [ $this, 'save_meta' ], 10, 2 );
		add_filter( 'admin_post_thumbnail_html', [ $this, 'featured_image_hero_checkbox' ], 10, 2 );
		add_action( 'admin_action_calypso_duplicate_corso', [ $this, 'duplicate_corso' ] );
		add_filter( 'post_row_actions',        [ $this, 'add_duplicate_row_action' ], 10, 2 );
		add_action( 'post_submitbox_misc_actions', [ $this, 'add_duplicate_button' ] );
	}

	public function add_duplicate_row_action( array $actions, WP_Post $post ): array {
		if ( $post->post_type !== 'calypso_corso' ) return $actions;
		if ( ! current_user_can( 'edit_posts' ) ) return $actions;
		$url = wp_nonce_url(
			admin_url( 'admin.php?action=calypso_duplicate_corso&post=' . $post->ID ),
			'calypso_duplicate_corso_' . $post->ID
		);
		$actions['duplicate'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplica', 'calypsosub' ) . '</a>';
		return $actions;
	}

	public function add_duplicate_button( WP_Post $post ): void {
		if ( $post->post_type !== 'calypso_corso' ) return;
		if ( ! current_user_can( 'edit_posts' ) ) return;
		$url = wp_nonce_url(
			admin_url( 'admin.php?action=calypso_duplicate_corso&post=' . $post->ID ),
			'calypso_duplicate_corso_' . $post->ID
		);
		echo '<div class="misc-pub-section" style="padding-top:10px;padding-bottom:10px">'
			. '<a href="' . esc_url( $url ) . '" class="button button-small">'
			. esc_html__( 'Duplica questo corso', 'calypsosub' )
			. '</a>'
			. ' <span style="color:#888;font-size:11px">' . esc_html__( 'Crea una bozza identica', 'calypsosub' ) . '</span>'
			. '</div>';
	}

	public function duplicate_corso(): void {
		if ( empty( $_GET['post'] ) || empty( $_GET['_wpnonce'] ) ) {
			wp_die( esc_html__( 'Parametri mancanti.', 'calypsosub' ) );
		}
		$post_id = absint( $_GET['post'] );
		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'calypso_duplicate_corso_' . $post_id ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'calypsosub' ) );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );
		}
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'calypso_corso' ) {
			wp_die( esc_html__( 'Corso non trovato.', 'calypsosub' ) );
		}

		$new_id = wp_insert_post( [
			'post_title'  => __( 'Copia di', 'calypsosub' ) . ' — ' . $post->post_title,
			'post_status' => 'draft',
			'post_type'   => 'calypso_corso',
			'post_author' => get_current_user_id(),
		] );

		if ( is_wp_error( $new_id ) ) {
			wp_die( esc_html( $new_id->get_error_message() ) );
		}

		foreach ( get_post_meta( $post_id ) as $key => $values ) {
			if ( in_array( $key, [ '_edit_lock', '_edit_last' ], true ) ) continue;
			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}

		foreach ( get_object_taxonomies( 'calypso_corso' ) as $tax ) {
			$terms = wp_get_object_terms( $post_id, $tax, [ 'fields' => 'ids' ] );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				wp_set_object_terms( $new_id, $terms, $tax );
			}
		}

		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
		exit;
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
		.calypso-fase-row{display:grid;grid-template-columns:1fr 100px auto;gap:8px;align-items:start;margin-bottom:8px;padding:12px;background:#f9f9f9;border-radius:4px}
		.calypso-fase-row__desc{grid-column:1/3}
		.calypso-fase-field{display:flex;flex-direction:column;gap:3px}
		.calypso-fase-field label{font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#444;margin:0}
		.calypso-fase-field label small{font-weight:400;text-transform:none;letter-spacing:0;color:#888;margin-left:4px}
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
			<div class="calypso-meta-field" style="grid-column:1/-1">
				<label><?php _e( 'Requisiti di accesso', 'calypsosub' ); ?> <small>(es. brevetto richiesto, età minima — mostrato nella sidebar del corso)</small></label>
				<textarea name="calypso_requisiti"><?php echo esc_textarea( $d['requisiti'] ); ?></textarea>
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
				<label><?php _e( 'Contatto sidebar', 'calypsosub' ); ?> <small>(tel. o email)</small></label>
				<input type="text" name="calypso_contatto" value="<?php echo esc_attr( $d['contatto'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Nome contatto', 'calypsosub' ); ?> <small>(es. "Riccardo" — sostituisce "Chiamaci" nel pulsante)</small></label>
				<input type="text" name="calypso_contatto_nome" value="<?php echo esc_attr( $d['contatto_nome'] ); ?>">
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
		<p style="margin:0 0 10px;color:#666;font-size:12px"><?php _e( 'Ogni riga è una fase del corso. <strong>Titolo</strong>: nome della sezione (es. AULA, PISCINA, MARE). <strong>Ore</strong>: numero o testo libero (es. "4", "4 ore", "½ giornata") — lascia vuoto se non applicabile. <strong>Descrizione</strong>: facoltativa, appare in piccolo sotto il titolo. L\'ordine delle righe riflette la sequenza mostrata in frontend.', 'calypsosub' ); ?></p>
		<div id="calypso-fasi-repeater">
			<?php foreach ( $d['fasi'] as $fase ) : ?>
			<div class="calypso-fase-row">
				<div class="calypso-fase-field">
					<label><?php _e( 'Titolo', 'calypsosub' ); ?> <small><?php _e( 'es. AULA, PISCINA, MARE', 'calypsosub' ); ?></small></label>
					<input type="text" name="calypso_fasi_titolo[]"
					       placeholder="<?php echo esc_attr( __( 'es. TEORIA', 'calypsosub' ) ); ?>"
					       value="<?php echo esc_attr( $fase['titolo'] ?? '' ); ?>">
				</div>
				<div class="calypso-fase-field">
					<label><?php _e( 'Ore', 'calypsosub' ); ?> <small><?php _e( 'es. 4, ½ giornata', 'calypsosub' ); ?></small></label>
					<input type="text" name="calypso_fasi_ore[]"
					       placeholder="<?php echo esc_attr( __( 'es. 8', 'calypsosub' ) ); ?>"
					       value="<?php echo esc_attr( $fase['ore'] ?? '' ); ?>">
				</div>
				<button type="button" class="calypso-btn-remove" style="margin-top:20px">&#x2715;</button>
				<div class="calypso-fase-field calypso-fase-row__desc">
					<label><?php _e( 'Descrizione', 'calypsosub' ); ?> <small><?php _e( 'facoltativa — appare sotto il titolo in frontend', 'calypsosub' ); ?></small></label>
					<textarea name="calypso_fasi_desc[]"
					          placeholder="<?php echo esc_attr( __( 'Breve descrizione del contenuto di questa fase…', 'calypsosub' ) ); ?>"><?php echo esc_textarea( $fase['desc'] ?? '' ); ?></textarea>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="calypso-fasi-add">
			<?php _e( '+ Aggiungi fase', 'calypsosub' ); ?>
		</button>

		<script>
		(function () {
			var lblTitolo = <?php echo wp_json_encode( __( 'Titolo', 'calypsosub' ) ); ?>;
			var lblTitoloHint = <?php echo wp_json_encode( __( 'es. AULA, PISCINA, MARE', 'calypsosub' ) ); ?>;
			var lblOre = <?php echo wp_json_encode( __( 'Ore', 'calypsosub' ) ); ?>;
			var lblOreHint = <?php echo wp_json_encode( __( 'es. 4, ½ giornata', 'calypsosub' ) ); ?>;
			var lblDesc = <?php echo wp_json_encode( __( 'Descrizione', 'calypsosub' ) ); ?>;
			var lblDescHint = <?php echo wp_json_encode( __( 'facoltativa — appare sotto il titolo in frontend', 'calypsosub' ) ); ?>;
			var phTitolo = <?php echo wp_json_encode( __( 'es. TEORIA', 'calypsosub' ) ); ?>;
			var phOre    = <?php echo wp_json_encode( __( 'es. 8', 'calypsosub' ) ); ?>;
			var phDesc   = <?php echo wp_json_encode( __( 'Breve descrizione del contenuto di questa fase…', 'calypsosub' ) ); ?>;

			function buildLabel(lbl, hint) {
				return '<label style="font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#444;margin:0">'
					+ lbl + ' <small style="font-weight:400;text-transform:none;letter-spacing:0;color:#888">' + hint + '</small></label>';
			}

			document.getElementById('calypso-fasi-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-fase-row';
				row.innerHTML =
					'<div class="calypso-fase-field">'
						+ buildLabel(lblTitolo, lblTitoloHint)
						+ '<input type="text" name="calypso_fasi_titolo[]" placeholder="' + phTitolo + '">'
					+ '</div>'
					+ '<div class="calypso-fase-field">'
						+ buildLabel(lblOre, lblOreHint)
						+ '<input type="text" name="calypso_fasi_ore[]" placeholder="' + phOre + '">'
					+ '</div>'
					+ '<button type="button" class="calypso-btn-remove" style="margin-top:20px">✕</button>'
					+ '<div class="calypso-fase-field calypso-fase-row__desc">'
						+ buildLabel(lblDesc, lblDescHint)
						+ '<textarea name="calypso_fasi_desc[]" placeholder="' + phDesc + '" style="min-height:56px;width:100%"></textarea>'
					+ '</div>';
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

	public function featured_image_hero_checkbox( string $html, int $post_id ): string {
		if ( get_post_type( $post_id ) !== 'calypso_corso' ) {
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

		update_post_meta( $post_id, '_corso_contatto_nome',
			sanitize_text_field( wp_unslash( $_POST['calypso_contatto_nome'] ?? '' ) ) );

		update_post_meta( $post_id, '_hero_use_featured_image',
			isset( $_POST['calypso_hero_bg'] ) ? '1' : '0' );

		update_post_meta( $post_id, '_corso_competenze',
			sanitize_textarea_field( wp_unslash( $_POST['calypso_competenze'] ?? '' ) ) );

		update_post_meta( $post_id, '_corso_requisiti',
			sanitize_textarea_field( wp_unslash( $_POST['calypso_requisiti'] ?? '' ) ) );

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
			'contatto'        => (string) get_post_meta( $post_id, '_corso_contatto', true ),
			'contatto_nome'   => (string) get_post_meta( $post_id, '_corso_contatto_nome', true ),
			'competenze'      => (string) get_post_meta( $post_id, '_corso_competenze', true ),
			'direttore_id'    => (int)    get_post_meta( $post_id, '_corso_direttore_id', true ),
			'docenti_ids'     => (array)  ( get_post_meta( $post_id, '_corso_docenti_ids', true ) ?: [] ),
			'fasi'                  => (array)  ( get_post_meta( $post_id, '_corso_fasi', true ) ?: [] ),
			'requisiti'             => (string) get_post_meta( $post_id, '_corso_requisiti', true ),
			'hero_use_featured_image' => (string) get_post_meta( $post_id, '_hero_use_featured_image', true ),
		];
	}
}
