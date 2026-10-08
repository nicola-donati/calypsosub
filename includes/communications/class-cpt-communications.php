<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CPT "Comunicazioni": l'operatore scrive titolo (oggetto) + corpo con
 * l'editor classico di WP, sceglie una categoria (che determina sia il tag
 * systeme.io sia i gruppi Telegram di destinazione) e i canali da usare.
 *
 * L'invio riusa il publish box nativo di WP invece di un bottone custom:
 * "Pubblica subito" = invio a breve, "Programma per..." = invio schedulato
 * (gestito dal cron nativo `publish_future_{post_type}` di WP). Vedi
 * Calypsosub_Communication_Dispatcher per cosa succede al momento effettivo.
 *
 * Tutta la sezione vive sotto un'unica voce di menu ("Comunicazioni"): la
 * voce "Aggiungi nuovo" generata automaticamente da WP è nascosta dalla
 * sidebar (il bottone "Aggiungi nuovo" in cima alla lista basta). Le
 * impostazioni (tag systeme.io, bot Telegram, categorie) vivono nella tab
 * "Comunicazioni" della pagina Calypso Sub → Impostazioni già esistente
 * (Calypsosub_Admin_Menus), non in una pagina/menu separati.
 */
class Calypsosub_CPT_Communications {

	public const POST_TYPE = 'cso_communication';

	public function init(): void {
		add_action( 'init',                      [ $this, 'register_post_type' ] );
		add_action( 'admin_menu',                [ $this, 'hide_add_new_submenu' ], 999 );
		add_action( 'add_meta_boxes',            [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_meta' ], 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',       [ $this, 'add_columns' ] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'render_column' ], 10, 2 );

		add_filter( 'wp_insert_post_data',                    [ $this, 'lock_dispatched_content' ], 10, 2 );
		add_action( 'edit_form_after_title',                  [ $this, 'render_top_notices' ] );
		add_action( 'admin_print_footer_scripts-post.php',    [ $this, 'print_lock_script' ] );
		add_action( 'admin_post_calypso_cancel_communication', [ $this, 'handle_cancel' ] );
		add_action( 'admin_post_calypso_retry_communication',  [ $this, 'handle_retry' ] );
	}

	/** Una comunicazione per cui il dispatch è già partito non si tocca più. */
	private function is_locked( int $post_id ): bool {
		return (bool) get_post_meta( $post_id, '_com_dispatched', true );
	}

	public function register_post_type(): void {
		register_post_type( self::POST_TYPE, [
			'label'           => __( 'Comunicazioni', 'calypsosub' ),
			'labels'          => [
				'name'          => __( 'Comunicazioni', 'calypsosub' ),
				'singular_name' => __( 'Comunicazione', 'calypsosub' ),
				'add_new_item'  => __( 'Nuova comunicazione', 'calypsosub' ),
				'edit_item'     => __( 'Modifica comunicazione', 'calypsosub' ),
				'not_found'     => __( 'Nessuna comunicazione trovata', 'calypsosub' ),
			],
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'calypsosub',
			'capability_type' => 'post',
			'supports'        => [ 'title', 'editor' ],
			'menu_icon'       => 'dashicons-megaphone',
		] );
	}

	public function hide_add_new_submenu(): void {
		remove_submenu_page( 'calypsosub', 'post-new.php?post_type=' . self::POST_TYPE );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			self::POST_TYPE . '_meta',
			__( 'Destinatari e canali', 'calypsosub' ),
			[ $this, 'render_meta_box' ],
			self::POST_TYPE,
			'side',
			'high'
		);
		add_meta_box(
			self::POST_TYPE . '_status',
			__( 'Stato invio', 'calypsosub' ),
			[ $this, 'render_status_box' ],
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( self::POST_TYPE . '_meta', self::POST_TYPE . '_nonce' );

		$category_sel = (string) get_post_meta( $post->ID, '_com_category', true );
		$channels_sel = (array) get_post_meta( $post->ID, '_com_channels', true );
		$categories   = Calypsosub_Communications_Settings::get_categories();
		$locked       = $this->is_locked( $post->ID );
		$dis          = $locked ? 'disabled' : '';
		?>
		<p>
			<label for="cso_com_category"><strong><?php _e( 'Categoria', 'calypsosub' ); ?></strong></label><br>
			<select name="cso_com_category" id="cso_com_category" style="width:100%" <?php echo $dis; ?>>
				<option value=""><?php _e( '— seleziona —', 'calypsosub' ); ?></option>
				<?php foreach ( $categories as $key => $cat ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $category_sel, $key ); ?>>
					<?php echo esc_html( $cat['label'] ); ?>
				</option>
				<?php endforeach; ?>
			</select>
			<?php if ( empty( $categories ) ) : ?>
			<p class="description" style="color:#b32d2e">
				<?php
				printf(
					/* translators: %s: link to settings page */
					__( 'Nessuna categoria configurata. Vai in %s per crearne almeno una (definisce tag systeme.io + gruppi Telegram).', 'calypsosub' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=calypsosub&cso_tab=comunicazioni' ) ) . '">' . esc_html__( 'Calypso Sub → Impostazioni → Comunicazioni', 'calypsosub' ) . '</a>'
				);
				?>
			</p>
			<?php endif; ?>
		</p>
		<p>
			<strong><?php _e( 'Canali', 'calypsosub' ); ?></strong><br>
			<label>
				<input type="checkbox" name="cso_com_channels[]" value="email" <?php checked( in_array( 'email', $channels_sel, true ) ); ?> <?php echo $dis; ?>>
				<?php _e( 'Email (systeme.io)', 'calypsosub' ); ?>
			</label><br>
			<label>
				<input type="checkbox" name="cso_com_channels[]" value="telegram" <?php checked( in_array( 'telegram', $channels_sel, true ) ); ?> <?php echo $dis; ?>>
				<?php _e( 'Telegram', 'calypsosub' ); ?>
			</label>
		</p>
		<p class="description">
			<?php if ( $locked ) : ?>
				<?php _e( 'Comunicazione già inviata (o invio tentato) — non più modificabile.', 'calypsosub' ); ?>
			<?php else : ?>
				<?php _e( '"Pubblica subito" invia a breve (pochi secondi); "Programma per..." nel riquadro Pubblica qui sopra invia alla data/ora scelta.', 'calypsosub' ); ?>
			<?php endif; ?>
		</p>
		<?php
	}

	public function render_status_box( WP_Post $post ): void {
		$status = (string) get_post_meta( $post->ID, '_com_status', true );
		if ( $status === '' ) {
			echo '<p>' . esc_html__( 'Non ancora inviata.', 'calypsosub' ) . '</p>';
			return;
		}

		$labels = [
			'sending' => [ 'label' => __( 'In invio…', 'calypsosub' ),  'color' => '#92400e', 'bg' => '#fef3c7' ],
			'sent'    => [ 'label' => __( 'Inviata', 'calypsosub' ),    'color' => '#065f46', 'bg' => '#d1fae5' ],
			'failed'  => [ 'label' => __( 'Fallita (parzialmente o del tutto)', 'calypsosub' ), 'color' => '#991b1b', 'bg' => '#fee2e2' ],
		];
		$l = $labels[ $status ] ?? [ 'label' => $status, 'color' => '#444', 'bg' => '#eee' ];
		echo '<p><span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;background:' . esc_attr( $l['bg'] ) . ';color:' . esc_attr( $l['color'] ) . '">' . esc_html( $l['label'] ) . '</span></p>';

		if ( $status === 'failed' ) {
			$retry_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=calypso_retry_communication&post_id=' . $post->ID ),
				'calypso_retry_communication_' . $post->ID
			);
			echo '<p><a href="' . esc_url( $retry_url ) . '" class="button button-primary">' . esc_html__( 'Riprova invio', 'calypsosub' ) . '</a></p>';
		}

		$results = (array) get_post_meta( $post->ID, '_com_results', true );
		if ( ! $results ) return;

		echo '<ul style="margin-left:4px">';
		if ( isset( $results['email'] ) ) {
			$e = $results['email'];
			echo '<li><strong>' . esc_html__( 'Email', 'calypsosub' ) . ':</strong> ' .
				( $e['ok'] ? esc_html__( 'inviata', 'calypsosub' ) : '<span style="color:#991b1b">' . esc_html( $e['msg'] ) . '</span>' ) . '</li>';
			if ( ! $e['ok'] && ! empty( $e['debug'] ) ) {
				$this->render_debug_details( $e['debug'] );
			}
		}
		if ( isset( $results['telegram'] ) ) {
			echo '<li><strong>' . esc_html__( 'Telegram', 'calypsosub' ) . ':</strong><ul>';
			foreach ( (array) $results['telegram'] as $group_label => $g ) {
				echo '<li>' . esc_html( $group_label ) . ': ' .
					( $g['ok'] ? esc_html__( 'inviato', 'calypsosub' ) : '<span style="color:#991b1b">' . esc_html( $g['msg'] ) . '</span>' ) . '</li>';
				if ( ! $g['ok'] && ! empty( $g['debug'] ) ) {
					$this->render_debug_details( $g['debug'] );
				}
			}
			echo '</ul></li>';
		}
		echo '</ul>';
	}

	/**
	 * Log tecnico di un tentativo di invio fallito: metodo+URL, header (con
	 * X-API-Key/token Telegram già mascherati dal client che li ha prodotti,
	 * mai in chiaro qui), body inviato e, se arrivata, la risposta del
	 * servizio esterno. Collassato di default per non affollare il riquadro
	 * di stato quando tutto va bene.
	 *
	 * @param array{method?:string,url?:string,headers?:array<string,string>,body?:?string,response_code?:?int,response_body?:?string} $debug
	 */
	private function render_debug_details( array $debug ): void {
		echo '<details style="margin:2px 0 10px 16px;font-size:12px">';
		echo '<summary style="cursor:pointer;color:#6d6d6f">' . esc_html__( 'Dettagli tecnici invio', 'calypsosub' ) . '</summary>';
		echo '<pre style="white-space:pre-wrap;word-break:break-all;background:#f6f7f7;border:1px solid #dcdcde;padding:8px;border-radius:4px;margin-top:4px">';

		if ( isset( $debug['method'], $debug['url'] ) ) {
			echo esc_html( $debug['method'] . ' ' . $debug['url'] ) . "\n\n";
		}

		if ( ! empty( $debug['headers'] ) && is_array( $debug['headers'] ) ) {
			foreach ( $debug['headers'] as $name => $value ) {
				echo esc_html( $name . ': ' . $value ) . "\n";
			}
			echo "\n";
		}

		if ( ! empty( $debug['body'] ) ) {
			echo esc_html__( 'Body inviato:', 'calypsosub' ) . "\n" . esc_html( $debug['body'] ) . "\n\n";
		}

		if ( isset( $debug['response_code'] ) && null !== $debug['response_code'] ) {
			echo esc_html__( 'Risposta:', 'calypsosub' ) . ' HTTP ' . esc_html( (string) $debug['response_code'] ) . "\n";
		}
		if ( ! empty( $debug['response_body'] ) ) {
			echo esc_html( $debug['response_body'] );
		}

		echo '</pre></details>';
	}

	public function save_meta( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST[ self::POST_TYPE . '_nonce' ] ) ) return;
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ self::POST_TYPE . '_nonce' ] ), self::POST_TYPE . '_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( $this->is_locked( $post_id ) ) return;

		update_post_meta( $post_id, '_com_category', sanitize_key( $_POST['cso_com_category'] ?? '' ) );

		$channels = array_values( array_intersect(
			(array) ( $_POST['cso_com_channels'] ?? [] ),
			[ 'email', 'telegram' ]
		) );
		update_post_meta( $post_id, '_com_channels', $channels );
	}

	public function add_columns( array $cols ): array {
		$new = [];
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( $k === 'title' ) {
				$new['com_category'] = __( 'Categoria', 'calypsosub' );
				$new['com_channels'] = __( 'Canali', 'calypsosub' );
				$new['com_status']   = __( 'Stato', 'calypsosub' );
			}
		}
		return $new;
	}

	public function render_column( string $col, int $post_id ): void {
		if ( $col === 'com_category' ) {
			$key        = (string) get_post_meta( $post_id, '_com_category', true );
			$categories = Calypsosub_Communications_Settings::get_categories();
			echo esc_html( $categories[ $key ]['label'] ?? $key );
		} elseif ( $col === 'com_channels' ) {
			$channels = (array) get_post_meta( $post_id, '_com_channels', true );
			echo esc_html( implode( ', ', $channels ) );
		} elseif ( $col === 'com_status' ) {
			echo esc_html( (string) get_post_meta( $post_id, '_com_status', true ) ?: '—' );
		}
	}

	/**
	 * Difesa server-side contro la modifica di titolo/corpo una volta che il
	 * dispatch è partito — l'editor nativo di WP salva questi due campi
	 * direttamente via wp_insert_post(), non passa dal meta box/save_meta(),
	 * quindi va intercettato qui e non lì.
	 */
	public function lock_dispatched_content( array $data, array $postarr ): array {
		$post_id = (int) ( $postarr['ID'] ?? 0 );
		if ( ! $post_id || get_post_type( $post_id ) !== self::POST_TYPE ) return $data;
		if ( ! $this->is_locked( $post_id ) ) return $data;

		$existing = get_post( $post_id );
		if ( $existing ) {
			$data['post_title']   = $existing->post_title;
			$data['post_content'] = $existing->post_content;
		}
		return $data;
	}

	public function render_top_notices( WP_Post $post ): void {
		if ( $post->post_type !== self::POST_TYPE ) return;

		if ( $this->is_locked( $post->ID ) ) {
			echo '<div class="notice notice-info" style="margin:15px 0 0"><p>' .
				esc_html__( 'Questa comunicazione è stata inviata (o l\'invio è stato tentato): titolo, contenuto, categoria e canali non sono più modificabili.', 'calypsosub' ) .
				'</p></div>';
			return;
		}

		if ( $post->post_status === 'future' ) {
			$cancel_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=calypso_cancel_communication&post_id=' . $post->ID ),
				'calypso_cancel_communication_' . $post->ID
			);
			echo '<div class="notice notice-warning" style="margin:15px 0 0"><p>' .
				sprintf(
					/* translators: %s: scheduled date/time */
					esc_html__( 'Programmata per l\'invio il %s.', 'calypsosub' ),
					esc_html( get_the_date( '', $post ) . ' ' . get_the_time( '', $post ) )
				) .
				' <a href="' . esc_url( $cancel_url ) . '" class="button">' . esc_html__( 'Annulla programmazione', 'calypsosub' ) . '</a>' .
				'</p></div>';
		}
	}

	/**
	 * Disabilita titolo ed editor via JS quando bloccata — la vera difesa è
	 * server-side (save_meta()/lock_dispatched_content()), questo è solo per
	 * non mostrare campi modificabili che poi non salverebbero comunque.
	 */
	public function print_lock_script(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== self::POST_TYPE ) return;

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id || ! $this->is_locked( $post_id ) ) return;
		?>
		<script>
		(function () {
			var t = document.getElementById('title'); if (t) t.readOnly = true;
			var c = document.getElementById('content'); if (c) c.readOnly = true;
			if (window.tinymce) {
				var ed = tinymce.get('content');
				if (ed) ed.getBody().setAttribute('contenteditable', 'false');
			}
			var pub = document.getElementById('publishing-action');
			if (pub) pub.style.display = 'none';
		})();
		</script>
		<?php
	}

	public function handle_cancel(): void {
		$post_id = absint( $_GET['post_id'] ?? 0 );
		check_admin_referer( 'calypso_cancel_communication_' . $post_id );
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'calypsosub' ) );
		}
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== self::POST_TYPE ) {
			wp_die( esc_html__( 'Comunicazione non trovata.', 'calypsosub' ) );
		}
		if ( $this->is_locked( $post_id ) || $post->post_status !== 'future' ) {
			wp_die( esc_html__( 'Questa comunicazione non è (più) annullabile.', 'calypsosub' ) );
		}

		wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $post_id ) );
		exit;
	}

	public function handle_retry(): void {
		$post_id = absint( $_GET['post_id'] ?? 0 );
		check_admin_referer( 'calypso_retry_communication_' . $post_id );
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'calypsosub' ) );
		}
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== self::POST_TYPE ) {
			wp_die( esc_html__( 'Comunicazione non trovata.', 'calypsosub' ) );
		}
		if ( (string) get_post_meta( $post_id, '_com_status', true ) !== 'failed' ) {
			wp_die( esc_html__( 'Si può riprovare solo l\'invio di una comunicazione fallita.', 'calypsosub' ) );
		}

		Calypsosub_Communication_Dispatcher::schedule( $post_id );
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $post_id ) );
		exit;
	}
}
