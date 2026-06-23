<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Admin_Menus {

	private const ARCHIVE_TABS = [
		'uscite'  => 'Uscite',
		'corsi'   => 'Corsi',
		'docenti' => 'Docenti',
		'eventi'  => 'Eventi',
	];

	public function init(): void {
		add_action( 'admin_menu',            [ $this, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function register_menus(): void {
		add_menu_page(
			__( 'Calypso Sub', 'calypsosub' ),
			__( 'Calypso Sub', 'calypsosub' ),
			'calypsosub_manage',
			'calypsosub',
			[ $this, 'render_settings_page' ],
			'dashicons-flag',
			30
		);
		add_submenu_page(
			'calypsosub',
			__( 'Impostazioni', 'calypsosub' ),
			__( 'Impostazioni', 'calypsosub' ),
			'calypsosub_manage',
			'calypsosub',
			[ $this, 'render_settings_page' ]
		);
	}

	public function enqueue_scripts( string $hook ): void {
		if ( $hook !== 'toplevel_page_calypsosub' ) return;
		wp_enqueue_media();
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'calypsosub_manage' ) ) return;

		$active_tab = 'generali';
		if ( isset( $_POST['calypsosub_settings_nonce'] ) &&
		     wp_verify_nonce( sanitize_key( $_POST['calypsosub_settings_nonce'] ), 'calypsosub_settings' ) ) {
			$this->save_settings();
			$active_tab = sanitize_key( $_POST['cso_active_tab'] ?? 'generali' );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Impostazioni salvate.', 'calypsosub' ) . '</p></div>';
		} elseif ( isset( $_GET['cso_tab'] ) ) {
			$active_tab = sanitize_key( $_GET['cso_tab'] );
		}
		if ( $active_tab !== 'generali' && ! array_key_exists( $active_tab, self::ARCHIVE_TABS ) ) {
			$active_tab = 'generali';
		}

		$notification_emails = get_option( 'calypsosub_notification_emails', '' );
		$account_page_id     = (int) get_option( 'calypsosub_account_page_id', 0 );
		$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );

		/* Dati per ogni tab archivio */
		$tabs_data = [];
		foreach ( array_keys( self::ARCHIVE_TABS ) as $slug ) {
			$opts   = (array) get_option( 'calypsosub_opts_' . $slug, [] );
			$img_id = (int) get_option( 'calypsosub_hero_img_' . $slug, 0 );
			$tabs_data[ $slug ] = [
				'opts'    => $opts,
				'img_id'  => $img_id,
				'img_url' => $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '',
			];
		}
		?>
		<div class="wrap">
		<h1><?php _e( 'Calypso Sub — Impostazioni', 'calypsosub' ); ?></h1>

		<nav class="nav-tab-wrapper" id="cso-tabs-nav">
			<a href="#" class="nav-tab cso-tab-btn" data-tab="generali"><?php _e( 'Generali', 'calypsosub' ); ?></a>
			<?php foreach ( self::ARCHIVE_TABS as $slug => $label ) : ?>
			<a href="#" class="nav-tab cso-tab-btn" data-tab="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<form method="post">
			<?php wp_nonce_field( 'calypsosub_settings', 'calypsosub_settings_nonce' ); ?>
			<input type="hidden" name="cso_active_tab" id="cso-active-tab" value="<?php echo esc_attr( $active_tab ); ?>">

			<!-- ── Tab: Generali ── -->
			<div id="cso-tab-generali" class="cso-tab-panel" style="display:none">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="calypsosub_notification_emails"><?php _e( 'Email notifiche admin', 'calypsosub' ); ?></label>
						</th>
						<td>
							<input type="text" id="calypsosub_notification_emails"
							       name="calypsosub_notification_emails"
							       value="<?php echo esc_attr( $notification_emails ); ?>"
							       class="regular-text">
							<p class="description"><?php _e( 'Indirizzi email separati da virgola che ricevono notifica ad ogni nuova prenotazione.', 'calypsosub' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="calypsosub_account_page_id"><?php _e( 'Pagina area personale', 'calypsosub' ); ?></label>
						</th>
						<td>
							<?php wp_dropdown_pages( [
								'name'              => 'calypsosub_account_page_id',
								'id'                => 'calypsosub_account_page_id',
								'selected'          => $account_page_id,
								'show_option_none'  => __( '— seleziona pagina —', 'calypsosub' ),
								'option_none_value' => 0,
								'post_status'       => [ 'publish', 'private' ],
							] ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="calypsosub_prenotazioni_page_id"><?php _e( 'Pagina prenotazioni', 'calypsosub' ); ?></label>
						</th>
						<td>
							<?php wp_dropdown_pages( [
								'name'              => 'calypsosub_prenotazioni_page_id',
								'id'                => 'calypsosub_prenotazioni_page_id',
								'selected'          => $prenotazioni_page_id,
								'show_option_none'  => __( '— seleziona pagina —', 'calypsosub' ),
								'option_none_value' => 0,
								'post_status'       => [ 'publish', 'private' ],
							] ); ?>
							<p class="description"><?php _e( 'Pagina che contiene il blocco "Prenotazione" — usata per generare il link "Prenota" delle uscite.', 'calypsosub' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- ── Tab: un pannello per ogni archivio ── -->
			<?php foreach ( self::ARCHIVE_TABS as $slug => $label ) :
				$d      = $tabs_data[ $slug ];
				$opts   = $d['opts'];
				$ov_col = $opts['overlay_color']  ?? '#061826';
				$ov_op  = isset( $opts['overlay_opacity'] ) ? (int) $opts['overlay_opacity'] : 88;
			?>
			<div id="cso-tab-<?php echo esc_attr( $slug ); ?>" class="cso-tab-panel" style="display:none">
				<table class="form-table">

					<!-- Immagine hero -->
					<tr>
						<th scope="row"><?php _e( 'Immagine hero', 'calypsosub' ); ?></th>
						<td>
							<div style="display:flex;align-items:flex-start;gap:16px">
								<div id="cso-thumb-<?php echo esc_attr( $slug ); ?>"
								     style="width:160px;height:90px;border:1px solid #ddd;border-radius:4px;overflow:hidden;background:#f0f0f0;display:flex;align-items:center;justify-content:center;flex:0 0 auto">
									<?php if ( $d['img_url'] ) : ?>
									<img src="<?php echo esc_url( $d['img_url'] ); ?>" style="width:100%;height:100%;object-fit:cover;display:block" alt="">
									<?php else : ?>
									<span style="font-size:11px;color:#aaa;text-align:center;padding:4px"><?php _e( 'Nessuna', 'calypsosub' ); ?></span>
									<?php endif; ?>
								</div>
								<div>
									<input type="hidden" name="calypsosub_hero_img_<?php echo esc_attr( $slug ); ?>"
									       id="cso-input-<?php echo esc_attr( $slug ); ?>"
									       value="<?php echo esc_attr( $d['img_id'] ); ?>">
									<button type="button" class="button cso-media-choose"
									        data-slug="<?php echo esc_attr( $slug ); ?>"
									        data-title="<?php echo esc_attr( sprintf( __( 'Hero — %s', 'calypsosub' ), $label ) ); ?>">
										<?php _e( 'Scegli immagine', 'calypsosub' ); ?>
									</button>
									<button type="button" class="button cso-media-remove"
									        data-slug="<?php echo esc_attr( $slug ); ?>"
									        style="<?php echo $d['img_id'] ? '' : 'display:none;'; ?>margin-left:4px">
										<?php _e( 'Rimuovi', 'calypsosub' ); ?>
									</button>
									<p class="description" style="margin-top:6px"><?php _e( 'Consigliato: 1920×1080px.', 'calypsosub' ); ?></p>
								</div>
							</div>
						</td>
					</tr>

					<!-- Colore overlay -->
					<tr>
						<th scope="row">
							<label for="cso-<?php echo esc_attr( $slug ); ?>-ov-color"><?php _e( 'Colore overlay', 'calypsosub' ); ?></label>
						</th>
						<td>
							<input type="color"
							       id="cso-<?php echo esc_attr( $slug ); ?>-ov-color"
							       name="cso_<?php echo esc_attr( $slug ); ?>_overlay_color"
							       value="<?php echo esc_attr( $ov_col ); ?>">
							<p class="description"><?php _e( 'Colore base del gradiente sopra l\'immagine. Default: #061826.', 'calypsosub' ); ?></p>
						</td>
					</tr>

					<!-- Opacità overlay -->
					<tr>
						<th scope="row">
							<label for="cso-<?php echo esc_attr( $slug ); ?>-ov-op"><?php _e( 'Opacità overlay', 'calypsosub' ); ?></label>
						</th>
						<td>
							<div style="display:flex;align-items:center;gap:10px">
								<input type="range"
								       id="cso-<?php echo esc_attr( $slug ); ?>-ov-op"
								       name="cso_<?php echo esc_attr( $slug ); ?>_overlay_opacity"
								       min="0" max="100" value="<?php echo esc_attr( $ov_op ); ?>"
								       style="width:200px"
								       oninput="document.getElementById('cso-<?php echo esc_attr( $slug ); ?>-ov-op-val').textContent=this.value+'%'">
								<span id="cso-<?php echo esc_attr( $slug ); ?>-ov-op-val" style="font-weight:600;min-width:42px"><?php echo esc_html( $ov_op ); ?>%</span>
							</div>
							<p class="description"><?php _e( '0 = trasparente · 100 = massimo. Default: 88.', 'calypsosub' ); ?></p>
						</td>
					</tr>

					<!-- Eyebrow -->
					<tr>
						<th scope="row">
							<label for="cso-<?php echo esc_attr( $slug ); ?>-eyebrow"><?php _e( 'Eyebrow', 'calypsosub' ); ?></label>
						</th>
						<td>
							<input type="text"
							       id="cso-<?php echo esc_attr( $slug ); ?>-eyebrow"
							       name="cso_<?php echo esc_attr( $slug ); ?>_eyebrow"
							       value="<?php echo esc_attr( $opts['archive_eyebrow'] ?? '' ); ?>"
							       class="regular-text">
							<p class="description"><?php _e( 'Piccola etichetta sopra il titolo. Vuoto = testo predefinito. Solo testo.', 'calypsosub' ); ?></p>
						</td>
					</tr>

					<!-- Titolo H1 -->
					<tr>
						<th scope="row">
							<label for="cso-<?php echo esc_attr( $slug ); ?>-h1"><?php _e( 'Titolo hero', 'calypsosub' ); ?></label>
						</th>
						<td>
							<textarea id="cso-<?php echo esc_attr( $slug ); ?>-h1"
							          name="cso_<?php echo esc_attr( $slug ); ?>_h1"
							          class="large-text" rows="3"><?php echo esc_textarea( $opts['archive_h1'] ?? '' ); ?></textarea>
							<p class="description"><?php _e( 'Titolo grande. Vuoto = testo predefinito. HTML consentito: &lt;em&gt; &lt;br&gt; &lt;strong&gt;.', 'calypsosub' ); ?></p>
						</td>
					</tr>

					<!-- Lead / Sottotitolo -->
					<tr>
						<th scope="row">
							<label for="cso-<?php echo esc_attr( $slug ); ?>-lead"><?php _e( 'Sottotitolo', 'calypsosub' ); ?></label>
						</th>
						<td>
							<textarea id="cso-<?php echo esc_attr( $slug ); ?>-lead"
							          name="cso_<?php echo esc_attr( $slug ); ?>_lead"
							          class="large-text" rows="3"><?php echo esc_textarea( $opts['archive_lead'] ?? '' ); ?></textarea>
							<p class="description"><?php _e( 'Testo descrittivo sotto il titolo. Vuoto = testo predefinito. Solo testo.', 'calypsosub' ); ?></p>
						</td>
					</tr>

				</table>
			</div>
			<?php endforeach; ?>

			<?php submit_button( __( 'Salva impostazioni', 'calypsosub' ) ); ?>
		</form>
		</div>

		<script>
		(function () {
			var initialTab = <?php echo wp_json_encode( $active_tab ); ?>;
			var btns   = document.querySelectorAll('.cso-tab-btn');
			var panels = document.querySelectorAll('.cso-tab-panel');
			var hidden = document.getElementById('cso-active-tab');

			function activate(tab) {
				btns.forEach(function (b) {
					b.classList.toggle('nav-tab-active', b.dataset.tab === tab);
				});
				panels.forEach(function (p) {
					p.style.display = (p.id === 'cso-tab-' + tab) ? '' : 'none';
				});
				if (hidden) hidden.value = tab;
			}

			btns.forEach(function (btn) {
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					activate(btn.dataset.tab);
				});
			});

			activate(initialTab);

			/* ── Media picker ── */
			document.querySelectorAll('.cso-media-choose').forEach(function (btn) {
				btn.addEventListener('click', function () {
					var slug  = btn.dataset.slug;
					var title = btn.dataset.title;
					var frame = wp.media({
						title:    title,
						button:   { text: <?php echo wp_json_encode( __( 'Usa questa immagine', 'calypsosub' ) ); ?> },
						multiple: false,
						library:  { type: 'image' },
					});
					frame.on('select', function () {
						var att      = frame.state().get('selection').first().toJSON();
						var thumbUrl = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
						document.getElementById('cso-input-' + slug).value = att.id;
						var thumb = document.getElementById('cso-thumb-' + slug);
						thumb.innerHTML = '<img src="' + thumbUrl + '" style="width:100%;height:100%;object-fit:cover;display:block" alt="">';
						var rem = document.querySelector('.cso-media-remove[data-slug="' + slug + '"]');
						if (rem) rem.style.display = '';
					});
					frame.open();
				});
			});

			document.querySelectorAll('.cso-media-remove').forEach(function (btn) {
				btn.addEventListener('click', function () {
					var slug = btn.dataset.slug;
					document.getElementById('cso-input-' + slug).value = '';
					var thumb = document.getElementById('cso-thumb-' + slug);
					thumb.innerHTML = '<span style="font-size:11px;color:#aaa;text-align:center;padding:4px">' + <?php echo wp_json_encode( __( 'Nessuna', 'calypsosub' ) ); ?> + '</span>';
					btn.style.display = 'none';
				});
			});
		}());
		</script>
		<?php
	}

	private function save_settings(): void {
		update_option( 'calypsosub_notification_emails',
			sanitize_text_field( wp_unslash( $_POST['calypsosub_notification_emails'] ?? '' ) ) );
		update_option( 'calypsosub_account_page_id',
			absint( $_POST['calypsosub_account_page_id'] ?? 0 ) );
		update_option( 'calypsosub_prenotazioni_page_id',
			absint( $_POST['calypsosub_prenotazioni_page_id'] ?? 0 ) );

		foreach ( array_keys( self::ARCHIVE_TABS ) as $slug ) {
			update_option(
				'calypsosub_hero_img_' . $slug,
				absint( $_POST[ 'calypsosub_hero_img_' . $slug ] ?? 0 )
			);

			$existing = (array) get_option( 'calypsosub_opts_' . $slug, [] );
			$existing['archive_eyebrow'] = sanitize_text_field( wp_unslash( $_POST[ 'cso_' . $slug . '_eyebrow' ]        ?? '' ) );
			$existing['archive_h1']      = wp_kses( wp_unslash( $_POST[ 'cso_' . $slug . '_h1' ]                         ?? '' ), [ 'em' => [], 'br' => [], 'strong' => [] ] );
			$existing['archive_lead']    = sanitize_textarea_field( wp_unslash( $_POST[ 'cso_' . $slug . '_lead' ]        ?? '' ) );
			$existing['overlay_color']   = sanitize_hex_color( $_POST[ 'cso_' . $slug . '_overlay_color' ]               ?? '#061826' ) ?: '#061826';
			$existing['overlay_opacity'] = min( 100, max( 0, (int) ( $_POST[ 'cso_' . $slug . '_overlay_opacity' ]       ?? 88 ) ) );
			update_option( 'calypsosub_opts_' . $slug, $existing );
		}
	}
}
