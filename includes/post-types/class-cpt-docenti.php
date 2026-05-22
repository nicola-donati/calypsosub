<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CPT_Docenti {

	public function init(): void {
		add_action( 'init',                      [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes',            [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_calypso_docente', [ $this, 'save_meta' ], 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type( 'calypso_docente', [
			'label'        => __( 'Docenti', 'calypsosub' ),
			'labels'       => [
				'name'          => __( 'Docenti', 'calypsosub' ),
				'singular_name' => __( 'Docente', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi docente', 'calypsosub' ),
				'edit_item'     => __( 'Modifica docente', 'calypsosub' ),
				'not_found'     => __( 'Nessun docente trovato', 'calypsosub' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'thumbnail' ],
			'menu_icon'    => 'dashicons-id',
			'rewrite'      => [ 'slug' => 'docenti' ],
			'has_archive'  => true,
		] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'calypso_docente_meta',
			__( 'Dati docente', 'calypsosub' ),
			[ $this, 'render_meta_box' ],
			'calypso_docente',
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		$d = $this->get_meta( $post->ID );
		wp_nonce_field( 'calypso_docente_meta', 'calypso_docente_nonce' );
		?>
		<style>
		.calypso-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
		.calypso-meta-field label{display:block;font-weight:600;margin-bottom:4px}
		.calypso-meta-field input,.calypso-meta-field select,.calypso-meta-field textarea{width:100%}
		.calypso-meta-field textarea{min-height:80px}
		.calypso-repeater-row{display:flex;gap:8px;align-items:center;margin-bottom:6px}
		.calypso-repeater-row input{flex:1}
		.calypso-btn-remove{background:#dc3545;color:#fff;border:none;border-radius:3px;padding:2px 8px;cursor:pointer}
		</style>

		<div class="calypso-meta-grid">
			<div class="calypso-meta-field">
				<label><?php _e( 'Nome', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_nome" value="<?php echo esc_attr( $d['nome'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Cognome', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_cognome" value="<?php echo esc_attr( $d['cognome'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Ruolo nel club', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_ruolo" value="<?php echo esc_attr( $d['ruolo'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Anni di esperienza', 'calypsosub' ); ?></label>
				<input type="number" min="0" name="calypso_anni_esperienza"
				       value="<?php echo esc_attr( $d['anni_esperienza'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Email', 'calypsosub' ); ?></label>
				<input type="email" name="calypso_email" value="<?php echo esc_attr( $d['email'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Telefono', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_telefono" value="<?php echo esc_attr( $d['telefono'] ); ?>">
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Account WordPress', 'calypsosub' ); ?></label>
				<select name="calypso_user_id">
					<option value=""><?php _e( '— nessuno —', 'calypsosub' ); ?></option>
					<?php foreach ( get_users( [ 'fields' => [ 'ID', 'display_name' ] ] ) as $u ) : ?>
					<option value="<?php echo esc_attr( $u->ID ); ?>"
					    <?php selected( $d['user_id'], $u->ID ); ?>>
						<?php echo esc_html( $u->display_name ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="calypso-meta-field">
				<label><?php _e( 'Specializzazioni', 'calypsosub' ); ?></label>
				<input type="text" name="calypso_specializzazioni"
				       value="<?php echo esc_attr( $d['specializzazioni'] ); ?>">
			</div>
		</div>

		<div class="calypso-meta-field" style="margin-bottom:16px">
			<label><?php _e( 'Bio', 'calypsosub' ); ?></label>
			<textarea name="calypso_bio"><?php echo esc_textarea( $d['bio'] ); ?></textarea>
		</div>

		<div class="calypso-meta-field" style="margin-bottom:16px">
			<label><?php _e( 'Galleria foto (URL)', 'calypsosub' ); ?></label>
			<div id="calypso-galleria-repeater">
				<?php foreach ( $d['galleria'] as $url ) : ?>
				<div class="calypso-repeater-row">
					<input type="url" name="calypso_galleria[]"
					       value="<?php echo esc_url( $url ); ?>" placeholder="https://...">
					<button type="button" class="calypso-btn-remove">&#x2715;</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button" id="calypso-galleria-add">
				<?php _e( '+ Aggiungi immagine', 'calypsosub' ); ?>
			</button>
		</div>

		<div class="calypso-meta-field">
			<label><?php _e( 'Social', 'calypsosub' ); ?></label>
			<div id="calypso-social-repeater">
				<?php foreach ( $d['social'] as $i => $s ) : ?>
				<div class="calypso-repeater-row">
					<input type="text" name="calypso_social[<?php echo (int) $i; ?>][nome]"
					       value="<?php echo esc_attr( $s['nome'] ); ?>"
					       placeholder="<?php esc_attr_e( 'Nome (es. Instagram)', 'calypsosub' ); ?>"
					       style="flex:0.4">
					<input type="url" name="calypso_social[<?php echo (int) $i; ?>][url]"
					       value="<?php echo esc_url( $s['url'] ); ?>" placeholder="https://...">
					<button type="button" class="calypso-btn-remove">&#x2715;</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button" id="calypso-social-add">
				<?php _e( '+ Aggiungi social', 'calypsosub' ); ?>
			</button>
		</div>

		<script>
		(function () {
			var socialPlaceholderNome = <?php echo wp_json_encode( __( 'Nome (es. Instagram)', 'calypsosub' ) ); ?>;

			function makeRemoveBtn() {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'calypso-btn-remove';
				btn.textContent = '✕';
				return btn;
			}
			function makeInput(type, name, placeholder, style) {
				var inp = document.createElement('input');
				inp.type = type;
				inp.name = name;
				inp.placeholder = placeholder;
				if (style) inp.setAttribute('style', style);
				return inp;
			}

			document.getElementById('calypso-galleria-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-repeater-row';
				row.appendChild(makeInput('url', 'calypso_galleria[]', 'https://...', ''));
				row.appendChild(makeRemoveBtn());
				document.getElementById('calypso-galleria-repeater').appendChild(row);
			});

			var socialIdx = document.getElementById('calypso-social-repeater')
			                  .querySelectorAll('.calypso-repeater-row').length;
			document.getElementById('calypso-social-add').addEventListener('click', function () {
				var row = document.createElement('div');
				row.className = 'calypso-repeater-row';
				row.appendChild(makeInput(
					'text', 'calypso_social[' + socialIdx + '][nome]',
					socialPlaceholderNome, 'flex:0.4'
				));
				row.appendChild(makeInput(
					'url', 'calypso_social[' + socialIdx + '][url]', 'https://...', ''
				));
				row.appendChild(makeRemoveBtn());
				document.getElementById('calypso-social-repeater').appendChild(row);
				socialIdx++;
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
		if ( ! isset( $_POST['calypso_docente_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST['calypso_docente_nonce'] ), 'calypso_docente_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$text_fields = [
			'_docente_nome'             => 'calypso_nome',
			'_docente_cognome'          => 'calypso_cognome',
			'_docente_ruolo'            => 'calypso_ruolo',
			'_docente_specializzazioni' => 'calypso_specializzazioni',
			'_docente_email'            => 'calypso_email',
			'_docente_telefono'         => 'calypso_telefono',
			'_docente_bio'              => 'calypso_bio',
		];
		foreach ( $text_fields as $meta_key => $post_key ) {
			update_post_meta( $post_id, $meta_key,
				sanitize_text_field( wp_unslash( $_POST[ $post_key ] ?? '' ) ) );
		}
		update_post_meta( $post_id, '_docente_anni_esperienza',
			absint( $_POST['calypso_anni_esperienza'] ?? 0 ) );
		update_post_meta( $post_id, '_docente_user_id',
			absint( $_POST['calypso_user_id'] ?? 0 ) );

		$galleria = [];
		foreach ( (array) ( $_POST['calypso_galleria'] ?? [] ) as $url ) {
			$clean = esc_url_raw( $url );
			if ( $clean ) $galleria[] = $clean;
		}
		update_post_meta( $post_id, '_docente_galleria', $galleria );

		$social = [];
		foreach ( (array) ( $_POST['calypso_social'] ?? [] ) as $s ) {
			$nome = sanitize_text_field( wp_unslash( $s['nome'] ?? '' ) );
			$url  = esc_url_raw( $s['url'] ?? '' );
			if ( $nome && $url ) $social[] = [ 'nome' => $nome, 'url' => $url ];
		}
		update_post_meta( $post_id, '_docente_social', $social );
	}

	private function get_meta( int $post_id ): array {
		return [
			'nome'             => (string) get_post_meta( $post_id, '_docente_nome', true ),
			'cognome'          => (string) get_post_meta( $post_id, '_docente_cognome', true ),
			'ruolo'            => (string) get_post_meta( $post_id, '_docente_ruolo', true ),
			'specializzazioni' => (string) get_post_meta( $post_id, '_docente_specializzazioni', true ),
			'email'            => (string) get_post_meta( $post_id, '_docente_email', true ),
			'telefono'         => (string) get_post_meta( $post_id, '_docente_telefono', true ),
			'bio'              => (string) get_post_meta( $post_id, '_docente_bio', true ),
			'anni_esperienza'  => (int)    get_post_meta( $post_id, '_docente_anni_esperienza', true ),
			'user_id'          => (int)    get_post_meta( $post_id, '_docente_user_id', true ),
			'galleria'         => (array)  ( get_post_meta( $post_id, '_docente_galleria', true ) ?: [] ),
			'social'           => (array)  ( get_post_meta( $post_id, '_docente_social', true ) ?: [] ),
		];
	}
}
