<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/prenotazione — selezione uscita/evento/corso + form CF7
 * collegato. Vedi docs/superpowers/specs/2026-06-21-prenotazione-block-design.md
 */

$a = $attributes ?? [];

$enable_uscite = (bool) ( $a['enable_uscite'] ?? true );
$enable_eventi = (bool) ( $a['enable_eventi'] ?? true );
$enable_corsi  = (bool) ( $a['enable_corsi']  ?? true );

$cf7_form_uscite = (int) ( $a['cf7_form_uscite'] ?? 0 );
$cf7_form_eventi = (int) ( $a['cf7_form_eventi'] ?? 0 );
$cf7_form_corsi  = (int) ( $a['cf7_form_corsi']  ?? 0 );

$max_items_per_tab = max( 1, (int) ( $a['max_items_per_tab'] ?? 6 ) );
$max_width          = (int) ( $a['max_width'] ?? 1320 );

global $calypsosub_booking_manager;

$tipi = [];
if ( $enable_uscite && $cf7_form_uscite ) $tipi['uscite'] = $cf7_form_uscite;
if ( $enable_eventi && $cf7_form_eventi ) $tipi['eventi'] = $cf7_form_eventi;
if ( $enable_corsi  && $cf7_form_corsi )  $tipi['corsi']  = $cf7_form_corsi;

if ( empty( $tipi ) ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p style="padding:24px;background:#fef3c7;color:#92400e">Blocco Prenotazione: nessuna tipologia abilitata con un form CF7 collegato. Configura il blocco nell\'editor.</p>';
	}
	return;
}

$preselect_id   = absint( $_GET['prenota_id'] ?? 0 );
$preselect_type = $preselect_id ? get_post_type( $preselect_id ) : '';
$preselect_tab  = '';
foreach ( $tipi as $tipo => $form_id ) {
	$cpt = [ 'uscite' => 'calypso_occ_uscita', 'eventi' => 'calypso_evento', 'corsi' => 'calypso_corso' ][ $tipo ];
	if ( $preselect_type === $cpt && get_post_status( $preselect_id ) === 'publish' ) {
		$preselect_tab = $tipo;
		break;
	}
}
if ( $preselect_tab === '' ) {
	$preselect_tab = array_key_first( $tipi );
}

/**
 * Costruisce i dati di una card per il markup + per la sidebar (via data-*
 * JSON), normalizzati per tipo.
 */
$build_card = static function ( WP_Post $post, string $tipo ) use ( $calypsosub_booking_manager ) {
	$id = $post->ID;
	$card = [ 'id' => $id, 'tipo' => $tipo, 'title' => get_the_title( $id ), 'img' => get_the_post_thumbnail_url( $id, 'large' ) ?: '' ];

	if ( $tipo === 'uscite' ) {
		$uscita_id = (int) get_post_meta( $id, '_occorrenza_uscita_uscita_id', true );
		$card['id']        = $id; // l'ID prenotabile resta quello dell'occorrenza
		$card['title']     = $uscita_id ? get_the_title( $uscita_id ) : $card['title'];
		if ( $uscita_id && get_post_thumbnail_id( $uscita_id ) ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $uscita_id ), 'large' );
			$card['img']   = $src ? $src[0] : '';
			$card['img_w'] = $src ? (int) $src[1] : 0;
			$card['img_h'] = $src ? (int) $src[2] : 0;
		}
		$card['data']      = (string) get_post_meta( $id, '_occorrenza_uscita_data', true );
		$card['luogo']     = $uscita_id ? (string) get_post_meta( $uscita_id, '_uscita_luogo', true ) : '';
		$card['incluso']   = $uscita_id ? (string) get_post_meta( $uscita_id, '_uscita_incluso', true ) : '';
		$card['portare']   = $uscita_id ? (string) get_post_meta( $uscita_id, '_uscita_cosa_portare', true ) : '';
		$card['cancellazione'] = $uscita_id ? (string) get_post_meta( $uscita_id, '_uscita_note_cancellazione', true ) : '';
		$card['ritrovo']   = $uscita_id ? (string) get_post_meta( $uscita_id, '_uscita_ritrovo', true ) : '';
		$max               = get_post_meta( $id, '_occorrenza_uscita_posti', true );
		$card['max']       = ( $max !== '' && $max !== false ) ? (int) $max : null;
		$card['posti']     = $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ? $calypsosub_booking_manager->get_remaining_spots( $id ) : null;
		$card['badge']     = __( 'Uscita', 'calypsosub' );
		$card['livello']   = __( 'Tutti i livelli', 'calypsosub' );
		$card['sottotitolo'] = (string) $card['luogo'];
		$card['mese_key']  = $card['data'] ? date( 'Y-m', strtotime( $card['data'] ) ) : '';
		$card['disponibile'] = $card['posti'] === null ? true : ( $card['posti'] > 0 );
	} elseif ( $tipo === 'eventi' ) {
		$date = (array) ( get_post_meta( $id, '_evento_date', true ) ?: [] );
		$card['data']  = calypso_next_future_date( $date );
		$card['luogo'] = (string) get_post_meta( $id, '_evento_luogo', true );
		$card['posti'] = $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ? $calypsosub_booking_manager->get_remaining_spots( $id ) : null;
	} else {
		$card['badge']        = (string) get_post_meta( $id, '_corso_badge', true );
		$card['durata']       = (string) get_post_meta( $id, '_corso_stat_durata', true );
		$card['immersioni']   = (string) get_post_meta( $id, '_corso_stat_pratica', true );
		$card['profondita']   = (string) get_post_meta( $id, '_corso_stat_profondita', true );
		$card['periodo']      = (string) get_post_meta( $id, '_corso_periodo', true );
		$card['requisiti']    = (string) get_post_meta( $id, '_corso_requisiti', true );
	}
	return $card;
};

$items_by_tipo = [];
if ( isset( $tipi['uscite'] ) ) {
	$occorrenze_uscite = get_posts( [
		'post_type'      => 'calypso_occ_uscita',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_occorrenza_uscita_data',
		'order'          => 'ASC',
		'meta_query'     => [ [
			'key'     => '_occorrenza_uscita_data',
			'value'   => current_time( 'Y-m-d\TH:i' ),
			'compare' => '>=',
		] ],
	] );
	$items_by_tipo['uscite'] = array_map(
		static fn( $p ) => $build_card( $p, 'uscite' ),
		array_slice( $occorrenze_uscite, 0, $max_items_per_tab )
	);
}
if ( isset( $tipi['eventi'] ) ) {
	$items_by_tipo['eventi'] = array_map(
		static fn( $p ) => $build_card( $p, 'eventi' ),
		array_slice( calypso_get_eventi(), 0, $max_items_per_tab )
	);
}
if ( isset( $tipi['corsi'] ) ) {
	$items_by_tipo['corsi'] = array_map(
		static fn( $p ) => $build_card( $p, 'corsi' ),
		array_slice( calypso_get_corsi(), 0, $max_items_per_tab )
	);
}

$tipo_labels = [ 'uscite' => 'Uscite in mare', 'eventi' => 'Eventi sociali', 'corsi' => 'Corsi' ];

$uid = 'cso-pren-' . sprintf( '%08x', crc32( implode( ',', array_keys( $tipi ) ) . $max_width ) );

// Nonce CSRF per la richiesta AJAX di rendering del form (richiesto da
// Calypsosub_CF7_Booking_Handler::ajax_render_form() via check_ajax_referer()).
$ajax_nonce = wp_create_nonce( 'calypso_prenotazione_form' );

// Pre-render del form CF7 del tipo attivo, con hidden fields per l'eventuale card pre-selezionata.
Calypsosub_CF7_Booking_Handler::$active_post_id = $preselect_id ?: null;
$initial_form_html = do_shortcode( '[contact-form-7 id="' . (int) $tipi[ $preselect_tab ] . '"]' );
Calypsosub_CF7_Booking_Handler::$active_post_id = null;
?>
<style>
#<?php echo $uid; ?>{max-width:<?php echo $max_width; ?>px;margin:0 auto;font-family:inherit;}
#<?php echo $uid; ?> .cso-pren__tabs{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;}
#<?php echo $uid; ?> .cso-pren__tab{padding:10px 20px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer;font-weight:600;font-size:13px;}
#<?php echo $uid; ?> .cso-pren__tab.is-active{background:#0a2540;color:#fff;border-color:#0a2540;}
#<?php echo $uid; ?> .cso-pren__layout{display:grid;grid-template-columns:1.5fr 1fr;gap:32px;align-items:start;}
@media(max-width:1024px){#<?php echo $uid; ?> .cso-pren__layout{grid-template-columns:1fr;}}
#<?php echo $uid; ?> .cso-pren__cards{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;}
#<?php echo $uid; ?> .cso-pren__card{border:1px solid rgba(0,0,0,.1);border-radius:14px;overflow:hidden;cursor:pointer;background:#fff;text-align:left;}
#<?php echo $uid; ?> .cso-pren__card.is-selected{border-color:#ff6b4a;box-shadow:0 8px 24px -8px rgba(255,107,74,.4);}
#<?php echo $uid; ?> .cso-pren__card-img{height:120px;background:#0a2540 center/cover;}
#<?php echo $uid; ?> .cso-pren__card-body{padding:14px;}
#<?php echo $uid; ?> .cso-pren__card-title{font-weight:700;font-size:14px;margin-bottom:4px;}
#<?php echo $uid; ?> .cso-pren__card-meta{font-size:12px;color:rgba(0,0,0,.55);}
#<?php echo $uid; ?> .cso-pren__sidebar{position:sticky;top:24px;background:#0a2540;color:#fff;border-radius:16px;padding:24px;}
#<?php echo $uid; ?> .cso-pren__sidebar h3{margin:0 0 12px;font-size:18px;}
#<?php echo $uid; ?> .cso-pren__sidebar dl{margin:0;}
#<?php echo $uid; ?> .cso-pren__sidebar dt{font-size:11px;text-transform:uppercase;opacity:.6;margin-top:12px;}
#<?php echo $uid; ?> .cso-pren__sidebar dd{margin:2px 0 0;font-size:13px;}
#<?php echo $uid; ?> .cso-pren__form-wrap{margin-top:32px;}
#<?php echo $uid; ?> .cso-pren__form-wrap.is-hidden{display:none;}
</style>
<div id="<?php echo $uid; ?>" data-preselect-id="<?php echo (int) $preselect_id; ?>" data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>">
	<div class="cso-pren__tabs">
		<?php foreach ( $tipi as $tipo => $form_id ) : ?>
		<button type="button" class="cso-pren__tab<?php echo $tipo === $preselect_tab ? ' is-active' : ''; ?>" data-tipo="<?php echo esc_attr( $tipo ); ?>" data-form-id="<?php echo (int) $form_id; ?>">
			<?php echo esc_html( $tipo_labels[ $tipo ] ); ?>
		</button>
		<?php endforeach; ?>
	</div>

	<div class="cso-pren__layout">
		<div>
			<?php foreach ( $items_by_tipo as $tipo => $cards ) : ?>
			<div class="cso-pren__cards" data-tab-panel="<?php echo esc_attr( $tipo ); ?>" style="<?php echo $tipo === $preselect_tab ? '' : 'display:none'; ?>">
				<?php foreach ( $cards as $card ) :
					$is_selected = $card['id'] === $preselect_id;
				?>
				<button type="button" class="cso-pren__card<?php echo $is_selected ? ' is-selected' : ''; ?>" data-card="<?php echo esc_attr( wp_json_encode( $card ) ); ?>">
					<div class="cso-pren__card-img" style="<?php echo $card['img'] ? 'background-image:url(' . esc_url( $card['img'] ) . ')' : ''; ?>"></div>
					<div class="cso-pren__card-body">
						<div class="cso-pren__card-title"><?php echo esc_html( $card['title'] ); ?></div>
						<div class="cso-pren__card-meta">
							<?php echo esc_html( $card['luogo'] ?? $card['periodo'] ?? '' ); ?>
						</div>
					</div>
				</button>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>

		<aside class="cso-pren__sidebar" data-sidebar>
			<p style="opacity:.7;font-size:13px">Seleziona un elemento per vedere i dettagli.</p>
		</aside>
	</div>

	<?php foreach ( $tipi as $tipo => $form_id ) : ?>
	<div class="cso-pren__form-wrap<?php echo $tipo === $preselect_tab ? '' : ' is-hidden'; ?>" data-form-panel="<?php echo esc_attr( $tipo ); ?>">
		<?php if ( $tipo === $preselect_tab ) : ?>
			<?php echo $initial_form_html; ?>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
</div>
<script>
(function(){
	var root = document.getElementById('<?php echo $uid; ?>');
	if (!root) return;
	var ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
	var ajaxNonce = root.getAttribute('data-nonce');

	function escHtml(s) {
		var d = document.createElement('div');
		d.textContent = String(s);
		return d.innerHTML;
	}

	function renderSidebar(card) {
		var sidebar = root.querySelector('[data-sidebar]');
		var html = '<h3>' + escHtml(card.title) + '</h3><dl>';
		if (card.luogo) html += '<dt>Luogo</dt><dd>' + escHtml(card.luogo) + '</dd>';
		if (card.data) html += '<dt>Data</dt><dd>' + escHtml(card.data) + '</dd>';
		if (card.posti !== null && card.posti !== undefined) html += '<dt>Posti</dt><dd>' + escHtml(card.posti) + ' disponibili</dd>';
		if (card.incluso) html += '<dt>Cosa è incluso</dt><dd>' + escHtml(card.incluso) + '</dd>';
		if (card.portare) html += '<dt>Cosa portare</dt><dd>' + escHtml(card.portare) + '</dd>';
		if (card.cancellazione) html += '<dt>Cancellazione</dt><dd>' + escHtml(card.cancellazione) + '</dd>';
		if (card.badge) html += '<dt>Certificazione</dt><dd>' + escHtml(card.badge) + '</dd>';
		if (card.durata) html += '<dt>Durata</dt><dd>' + escHtml(card.durata) + '</dd>';
		if (card.requisiti) html += '<dt>Requisiti</dt><dd>' + escHtml(card.requisiti) + '</dd>';
		html += '</dl>';
		sidebar.innerHTML = html;
	}

	function ensureHiddenField(form, name, value) {
		var input = form.querySelector('input[name="' + name + '"]');
		if (!input) {
			input = document.createElement('input');
			input.type = 'hidden';
			input.name = name;
			form.appendChild(input);
		}
		input.value = value;
	}

	// Mappa tipo card ('uscite'/'eventi'/'corsi') -> CPT slug, stesso formato
	// restituito da get_post_type() e letto da
	// Calypsosub_CF7_Booking_Handler::inject_hidden_fields()/create_booking().
	var TIPO_TO_CPT = { uscite: 'calypso_uscita', eventi: 'calypso_evento', corsi: 'calypso_corso' };

	function setHiddenPostId(formPanel, id, tipo) {
		var form = formPanel.querySelector('form.wpcf7-form');
		if (!form) return;
		ensureHiddenField(form, 'booking_post_id', id);
		ensureHiddenField(form, 'booking_post_type', TIPO_TO_CPT[tipo] || tipo);
	}

	function selectCard(btn) {
		var card = JSON.parse(btn.getAttribute('data-card'));
		root.querySelectorAll('.cso-pren__card.is-selected').forEach(function (el) { el.classList.remove('is-selected'); });
		btn.classList.add('is-selected');
		renderSidebar(card);
		var activeFormPanel = root.querySelector('.cso-pren__form-wrap:not(.is-hidden)');
		if (activeFormPanel) setHiddenPostId(activeFormPanel, card.id, card.tipo);
	}

	root.addEventListener('click', function (e) {
		var cardBtn = e.target.closest('.cso-pren__card');
		if (cardBtn) { selectCard(cardBtn); return; }

		var tabBtn = e.target.closest('.cso-pren__tab');
		if (tabBtn) {
			var tipo = tabBtn.getAttribute('data-tipo');
			root.querySelectorAll('.cso-pren__tab').forEach(function (el) { el.classList.remove('is-active'); });
			tabBtn.classList.add('is-active');
			root.querySelectorAll('[data-tab-panel]').forEach(function (el) {
				el.style.display = el.getAttribute('data-tab-panel') === tipo ? '' : 'none';
			});

			var formPanel = root.querySelector('[data-form-panel="' + tipo + '"]');
			root.querySelectorAll('[data-form-panel]').forEach(function (el) { el.classList.add('is-hidden'); });
			if (formPanel.innerHTML.trim() === '') {
				var body = new FormData();
				body.append('action', 'calypso_prenotazione_form');
				body.append('cf7_form_id', tabBtn.getAttribute('data-form-id'));
				body.append('_ajax_nonce', ajaxNonce);
				fetch(ajaxUrl, { method: 'POST', body: body })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (res.success) {
							formPanel.innerHTML = res.data.html;
							formPanel.classList.remove('is-hidden');
							if (window.wpcf7 && window.wpcf7.init) {
								formPanel.querySelectorAll('.wpcf7-form').forEach(function (f) { window.wpcf7.init(f); });
							}
						}
					});
			} else {
				formPanel.classList.remove('is-hidden');
			}
		}
	});
})();
</script>
