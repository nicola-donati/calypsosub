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
		add_action( 'wp_ajax_calypso_detect_telegram_groups', [ $this, 'ajax_detect_telegram_groups' ] );
	}

	public function ajax_detect_telegram_groups(): void {
		check_ajax_referer( 'calypso_detect_telegram_groups', 'nonce' );
		if ( ! current_user_can( 'calypsosub_manage' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permesso negato.', 'calypsosub' ) ] );
		}

		$groups = ( new Calypsosub_Telegram_Client() )->discover_groups();
		if ( is_wp_error( $groups ) ) {
			wp_send_json_error( [ 'message' => $groups->get_error_message() ] );
		}

		wp_send_json_success( [ 'groups' => $groups ] );
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
		if ( $active_tab !== 'generali' && $active_tab !== 'comunicazioni' && ! array_key_exists( $active_tab, self::ARCHIVE_TABS ) ) {
			$active_tab = 'generali';
		}

		$notification_emails = get_option( 'calypsosub_notification_emails', '' );
		$account_page_id     = (int) get_option( 'calypsosub_account_page_id', 0 );
		$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );

		$systemeio_api_key   = (string) get_option( 'calypsosub_systemeio_api_key', '' );
		$telegram_bot_token  = (string) get_option( 'calypsosub_telegram_bot_token', '' );
		$telegram_groups     = Calypsosub_Communications_Settings::get_telegram_groups();
		$communication_cats  = Calypsosub_Communications_Settings::get_categories();

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
			<a href="#" class="nav-tab cso-tab-btn" data-tab="comunicazioni"><?php _e( 'Comunicazioni', 'calypsosub' ); ?></a>
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
							<p class="description"><?php _e( 'Pagina che contiene il blocco "Prenotazione" — usata per generare i link "Prenota"/"Iscriviti" di uscite, eventi e corsi (il blocco gestisce tutti e tre con un tab per tipo).', 'calypsosub' ); ?></p>
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

			<!-- ── Tab: Comunicazioni ── -->
			<div id="cso-tab-comunicazioni" class="cso-tab-panel" style="display:none">
				<h2><?php _e( 'Integrazioni', 'calypsosub' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="com_systemeio_key"><?php _e( 'API key systeme.io', 'calypsosub' ); ?></label></th>
						<td>
							<input type="text" id="com_systemeio_key" name="com_systemeio_key"
							       value="<?php echo esc_attr( $systemeio_api_key ); ?>" class="regular-text">
							<p class="description"><?php _e( 'Profilo systeme.io → "MCP & API keys" → Public API keys.', 'calypsosub' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="com_telegram_token"><?php _e( 'Token bot Telegram', 'calypsosub' ); ?></label></th>
						<td>
							<input type="text" id="com_telegram_token" name="com_telegram_token"
							       value="<?php echo esc_attr( $telegram_bot_token ); ?>" class="regular-text">
							<p class="description"><?php _e( 'Ottenuto da @BotFather su Telegram con /newbot.', 'calypsosub' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php _e( 'Gruppi Telegram predefiniti', 'calypsosub' ); ?></h2>
				<p class="description"><?php _e( 'Il bot deve essere nel gruppo (consigliato: come amministratore). Salva prima il token qui sopra, poi usa "Rileva gruppi" per trovare automaticamente i gruppi in cui l\'hai appena aggiunto — funziona solo per gruppi con attività recente (ultime ~24h). In alternativa inserisci il chat_id a mano.', 'calypsosub' ); ?></p>
				<div id="cso-com-groups">
					<?php foreach ( $telegram_groups as $key => $g ) : ?>
					<div class="calypso-repeater-row">
						<input type="text" name="tg_label[]" placeholder="<?php esc_attr_e( 'Nome gruppo (es. Uscite)', 'calypsosub' ); ?>" value="<?php echo esc_attr( $g['label'] ); ?>" style="flex:2">
						<input type="text" name="tg_chat_id[]" placeholder="<?php esc_attr_e( 'chat_id (es. -100123456789)', 'calypsosub' ); ?>" value="<?php echo esc_attr( $g['chat_id'] ); ?>" style="flex:1">
						<input type="hidden" name="tg_key[]" value="<?php echo esc_attr( $key ); ?>">
						<button type="button" class="calypso-btn-remove">&#x2715;</button>
					</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button" id="cso-com-groups-add"><?php _e( '+ Aggiungi gruppo manualmente', 'calypsosub' ); ?></button>
				<button type="button" class="button" id="cso-com-groups-detect"><?php _e( '🔍 Rileva gruppi', 'calypsosub' ); ?></button>
				<span id="cso-com-groups-detect-status" style="margin-left:8px;color:#666"></span>
				<div id="cso-com-groups-detected" style="margin-top:10px"></div>

				<h2 style="margin-top:28px"><?php _e( 'Categorie comunicazioni', 'calypsosub' ); ?></h2>
				<p class="description"><?php _e( 'Ogni categoria collega un tag systeme.io (destinatari email) ai gruppi Telegram che devono riceverla.', 'calypsosub' ); ?></p>
				<div id="cso-com-categories">
					<?php $cat_i = 0; foreach ( $communication_cats as $key => $cat ) : ?>
					<div class="cso-com-cat-row" style="border:1px solid #ddd;border-radius:4px;padding:12px;margin-bottom:10px">
						<input type="hidden" name="cat_key[]" value="<?php echo esc_attr( $key ); ?>">
						<p style="display:flex;gap:10px">
							<input type="text" name="cat_label[]" placeholder="<?php esc_attr_e( 'Nome categoria (es. Uscite)', 'calypsosub' ); ?>" value="<?php echo esc_attr( $cat['label'] ); ?>" style="flex:1">
							<input type="text" name="cat_tag[]" placeholder="<?php esc_attr_e( 'Tag systeme.io (es. newsletter-uscite)', 'calypsosub' ); ?>" value="<?php echo esc_attr( $cat['tag_systemeio'] ); ?>" style="flex:1">
							<button type="button" class="calypso-btn-remove-cat button">&#x2715;</button>
						</p>
						<p>
							<strong><?php _e( 'Gruppi Telegram:', 'calypsosub' ); ?></strong><br>
							<?php foreach ( $telegram_groups as $gkey => $g ) : ?>
							<label style="margin-right:12px">
								<input type="checkbox" name="cat_groups[<?php echo (int) $cat_i; ?>][]" value="<?php echo esc_attr( $gkey ); ?>"
								       <?php checked( in_array( $gkey, (array) $cat['groups'], true ) ); ?>>
								<?php echo esc_html( $g['label'] ); ?>
							</label>
							<?php endforeach; ?>
							<?php if ( empty( $telegram_groups ) ) : ?>
							<em><?php _e( 'Nessun gruppo salvato ancora.', 'calypsosub' ); ?></em>
							<?php endif; ?>
						</p>
					</div>
					<?php $cat_i++; endforeach; ?>
				</div>
				<button type="button" class="button" id="cso-com-cat-add"><?php _e( '+ Aggiungi categoria', 'calypsosub' ); ?></button>
				<p class="description"><?php _e( 'I gruppi Telegram sono preselezionati tutti di default per una categoria nuova — deseleziona quelli che non devono riceverla.', 'calypsosub' ); ?></p>
			</div>

			<?php submit_button( __( 'Salva impostazioni', 'calypsosub' ) ); ?>
		</form>
		</div>

		<style>
		.calypso-repeater-row{display:flex;gap:8px;align-items:center;margin-bottom:6px}
		.calypso-btn-remove{background:#dc3545;color:#fff;border:none;border-radius:3px;padding:2px 8px;cursor:pointer}
		</style>
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

			/* ── Comunicazioni: repeater gruppi Telegram e categorie ── */
			function addGroupRow(label, chatId) {
				var row = document.createElement('div');
				row.className = 'calypso-repeater-row';
				row.innerHTML = '<input type="text" name="tg_label[]" placeholder="<?php echo esc_js( __( 'Nome gruppo (es. Uscite)', 'calypsosub' ) ); ?>" style="flex:2">' +
					'<input type="text" name="tg_chat_id[]" placeholder="<?php echo esc_js( __( 'chat_id (es. -100123456789)', 'calypsosub' ) ); ?>" style="flex:1">' +
					'<input type="hidden" name="tg_key[]" value="">' +
					'<button type="button" class="calypso-btn-remove">✕</button>';
				document.getElementById('cso-com-groups').appendChild(row);
				if (label) row.querySelector('input[name="tg_label[]"]').value = label;
				if (chatId) row.querySelector('input[name="tg_chat_id[]"]').value = chatId;
				return row;
			}

			var groupsAddBtn = document.getElementById('cso-com-groups-add');
			if (groupsAddBtn) {
				groupsAddBtn.addEventListener('click', function () { addGroupRow('', ''); });
			}

			/* ── Rileva gruppi via getUpdates del bot ── */
			var detectBtn    = document.getElementById('cso-com-groups-detect');
			var detectStatus = document.getElementById('cso-com-groups-detect-status');
			var detectedBox  = document.getElementById('cso-com-groups-detected');
			if (detectBtn) {
				detectBtn.addEventListener('click', function () {
					detectStatus.textContent = <?php echo wp_json_encode( __( 'Ricerca in corso…', 'calypsosub' ) ); ?>;
					detectedBox.innerHTML = '';

					var data = new URLSearchParams();
					data.append('action', 'calypso_detect_telegram_groups');
					data.append('nonce', <?php echo wp_json_encode( wp_create_nonce( 'calypso_detect_telegram_groups' ) ); ?>);

					fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: data })
						.then(function (r) { return r.json(); })
						.then(function (res) {
							if (!res.success) {
								detectStatus.textContent = (res.data && res.data.message) || <?php echo wp_json_encode( __( 'Errore.', 'calypsosub' ) ); ?>;
								return;
							}
							var groups = res.data.groups || [];
							if (!groups.length) {
								detectStatus.textContent = <?php echo wp_json_encode( __( 'Nessun gruppo rilevato — aggiungi il bot a un gruppo e riprova.', 'calypsosub' ) ); ?>;
								return;
							}
							detectStatus.textContent = '';
							var existingIds = Array.prototype.map.call(
								document.querySelectorAll('input[name="tg_chat_id[]"]'),
								function (i) { return i.value; }
							);
							groups.forEach(function (g) {
								var already = existingIds.indexOf(g.id) !== -1;
								var row = document.createElement('div');
								row.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:4px';
								var span = document.createElement('span');
								var code = document.createElement('code');
								code.textContent = g.id;
								span.appendChild( document.createTextNode( g.title + ' ' ) );
								span.appendChild( code );
								span.appendChild( document.createTextNode( ' (' + g.type + ')' ) );
								row.appendChild(span);
								var btn = document.createElement('button');
								btn.type = 'button';
								btn.className = 'button button-small';
								if (already) {
									btn.textContent = <?php echo wp_json_encode( __( 'Già aggiunto', 'calypsosub' ) ); ?>;
									btn.disabled = true;
								} else {
									btn.textContent = <?php echo wp_json_encode( __( '+ Aggiungi', 'calypsosub' ) ); ?>;
									btn.addEventListener('click', function () {
										addGroupRow(g.title, g.id);
										btn.textContent = <?php echo wp_json_encode( __( 'Aggiunto', 'calypsosub' ) ); ?>;
										btn.disabled = true;
									});
								}
								row.appendChild(btn);
								detectedBox.appendChild(row);
							});
						})
						.catch(function () {
							detectStatus.textContent = <?php echo wp_json_encode( __( 'Errore di rete.', 'calypsosub' ) ); ?>;
						});
				});
			}

			var csoTelegramGroups = <?php echo wp_json_encode( array_map(
				static fn( $gkey, $g ) => [ 'key' => $gkey, 'label' => $g['label'] ],
				array_keys( $telegram_groups ), array_values( $telegram_groups )
			) ); ?>;
			var catIdx = document.querySelectorAll('.cso-com-cat-row').length;

			var catAddBtn = document.getElementById('cso-com-cat-add');
			if (catAddBtn) {
				catAddBtn.addEventListener('click', function () {
					var idx = catIdx++;
					var row = document.createElement('div');
					row.className = 'cso-com-cat-row';
					row.style.cssText = 'border:1px solid #ddd;border-radius:4px;padding:12px;margin-bottom:10px';

					var groupsHtml = '';
					if (csoTelegramGroups.length) {
						csoTelegramGroups.forEach(function (g) {
							groupsHtml += '<label style="margin-right:12px">' +
								'<input type="checkbox" name="cat_groups[' + idx + '][]" value="' + g.key + '" checked> ' +
								g.label + '</label>';
						});
					} else {
						groupsHtml = '<em><?php echo esc_js( __( 'Nessun gruppo salvato ancora.', 'calypsosub' ) ); ?></em>';
					}

					row.innerHTML = '<input type="hidden" name="cat_key[]" value="">' +
						'<p style="display:flex;gap:10px">' +
						'<input type="text" name="cat_label[]" placeholder="<?php echo esc_js( __( 'Nome categoria (es. Uscite)', 'calypsosub' ) ); ?>" style="flex:1">' +
						'<input type="text" name="cat_tag[]" placeholder="<?php echo esc_js( __( 'Tag systeme.io (es. newsletter-uscite)', 'calypsosub' ) ); ?>" style="flex:1">' +
						'<button type="button" class="calypso-btn-remove-cat button">✕</button>' +
						'</p><p><strong><?php echo esc_js( __( 'Gruppi Telegram:', 'calypsosub' ) ); ?></strong><br>' + groupsHtml + '</p>';
					document.getElementById('cso-com-categories').appendChild(row);
				});
			}

			document.addEventListener('click', function (e) {
				if ( e.target.classList.contains('calypso-btn-remove') ) {
					e.target.closest('.calypso-repeater-row').remove();
				}
				if ( e.target.classList.contains('calypso-btn-remove-cat') ) {
					e.target.closest('.cso-com-cat-row').remove();
				}
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

		$this->save_communications_settings();
	}

	private function save_communications_settings(): void {
		update_option( 'calypsosub_systemeio_api_key', sanitize_text_field( wp_unslash( $_POST['com_systemeio_key'] ?? '' ) ) );
		update_option( 'calypsosub_telegram_bot_token', sanitize_text_field( wp_unslash( $_POST['com_telegram_token'] ?? '' ) ) );

		$existing_groups = Calypsosub_Communications_Settings::get_telegram_groups();
		$labels   = (array) ( $_POST['tg_label'] ?? [] );
		$chat_ids = (array) ( $_POST['tg_chat_id'] ?? [] );
		$keys_in  = (array) ( $_POST['tg_key'] ?? [] );

		$used_keys = [];
		$groups    = [];
		foreach ( $labels as $i => $label ) {
			$label   = sanitize_text_field( wp_unslash( $label ) );
			$chat_id = sanitize_text_field( wp_unslash( $chat_ids[ $i ] ?? '' ) );
			if ( $label === '' && $chat_id === '' ) continue;

			$key = sanitize_key( wp_unslash( $keys_in[ $i ] ?? '' ) );
			if ( $key === '' || ! isset( $existing_groups[ $key ] ) ) {
				/*
				 * sanitize_title() percent-encoda caratteri non-ASCII (es.
				 * emoji nel nome gruppo) invece di rimuoverli — sanitize_key()
				 * più avanti (qui e nel confronto cat_groups) toglierebbe solo
				 * i simboli '%', producendo una stringa diversa da questa e
				 * rompendo il confronto. Si pulisce la chiave una volta sola
				 * qui, alla creazione, così resta stabile per sempre dopo.
				 */
				$key = $this->unique_key( sanitize_key( sanitize_title( $label ) ) ?: 'group', $used_keys );
			}
			$used_keys[]  = $key;
			$groups[ $key ] = [ 'label' => $label, 'chat_id' => $chat_id ];
		}
		update_option( 'calypsosub_telegram_groups', $groups );

		$existing_cats = Calypsosub_Communications_Settings::get_categories();
		$cat_labels = (array) ( $_POST['cat_label'] ?? [] );
		$cat_tags   = (array) ( $_POST['cat_tag']   ?? [] );
		$cat_keys   = (array) ( $_POST['cat_key']   ?? [] );

		$cat_groups_in = (array) ( $_POST['cat_groups'] ?? [] );

		$used_cat_keys = [];
		$categories    = [];
		foreach ( $cat_labels as $i => $label ) {
			$label = sanitize_text_field( wp_unslash( $label ) );
			$tag   = sanitize_text_field( wp_unslash( $cat_tags[ $i ] ?? '' ) );
			if ( $label === '' ) continue;

			$key = sanitize_key( wp_unslash( $cat_keys[ $i ] ?? '' ) );
			if ( $key === '' || ! isset( $existing_cats[ $key ] ) ) {
				$key = $this->unique_key( sanitize_key( sanitize_title( $label ) ) ?: 'category', $used_cat_keys );
			}
			$used_cat_keys[] = $key;

			/*
			 * Indicizzato per posizione (cat_groups[i][]), non per chiave
			 * categoria: la chiave può essere rigenerata nello stesso submit
			 * (categoria nuova), quindi non è un riferimento stabile da usare
			 * come nome di campo — l'indice $i invece è sempre coerente con
			 * cat_label[$i]/cat_tag[$i] perché viene dallo stesso array.
			 */
			$selected_groups = array_map( 'sanitize_key', (array) ( $cat_groups_in[ $i ] ?? [] ) );
			$selected_groups = array_values( array_intersect( $selected_groups, array_keys( $groups ) ) );

			$categories[ $key ] = [ 'label' => $label, 'tag_systemeio' => $tag, 'groups' => $selected_groups ];
		}
		update_option( 'calypsosub_communication_categories', $categories );
	}

	/** @param string[] $used */
	private function unique_key( string $base, array $used ): string {
		$key = $base;
		$i   = 2;
		while ( in_array( $key, $used, true ) ) {
			$key = $base . '-' . $i;
			$i++;
		}
		return $key;
	}
}
