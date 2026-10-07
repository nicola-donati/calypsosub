<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/prenotazione — selezione uscita/evento/corso + form CF7
 * collegato. Vedi docs/superpowers/specs/2026-06-21-prenotazione-block-design.md
 */

$a = $attributes ?? [];

$require_login_uscite = (bool)   ( $a['require_login_uscite'] ?? true );
$require_login_eventi = (bool)   ( $a['require_login_eventi'] ?? true );
$require_login_corsi  = (bool)   ( $a['require_login_corsi']  ?? false );
$login_message        = (string) ( $a['login_message']        ?? 'Per prenotarti devi aver effettuato il login.' );

$require_login_map = [
	'uscite' => $require_login_uscite,
	'eventi' => $require_login_eventi,
	'corsi'  => $require_login_corsi,
];
$user_logged_in = is_user_logged_in();

/**
 * Dati dell'utente loggato per precompilare i campi del form CF7 (nome,
 * cognome, email) lato JS — vedi prefillUserFields() nello script in fondo.
 * Vuoto per i prenotanti anonimi: niente da precompilare.
 */
$user_prefill = [];
if ( $user_logged_in ) {
	$wp_user = wp_get_current_user();
	$first   = $wp_user->first_name;
	$last    = $wp_user->last_name;
	if ( $first === '' && $last === '' && $wp_user->display_name !== '' ) {
		$parts = explode( ' ', $wp_user->display_name, 2 );
		$first = $parts[0] ?? '';
		$last  = $parts[1] ?? '';
	}
	$user_prefill = [
		'nome'    => $first,
		'cognome' => $last,
		'email'   => $wp_user->user_email,
	];
}

$enable_uscite = (bool) ( $a['enable_uscite'] ?? true );
$enable_eventi = (bool) ( $a['enable_eventi'] ?? true );
$enable_corsi  = (bool) ( $a['enable_corsi']  ?? true );

$cf7_form_uscite   = (int) ( $a['cf7_form_uscite']   ?? 0 );
$cf7_form_eventi   = (int) ( $a['cf7_form_eventi']   ?? 0 );
$cf7_form_corsi    = (int) ( $a['cf7_form_corsi']    ?? 0 );
$cf7_form_fallback = (int) ( $a['cf7_form_fallback'] ?? 0 );
$fallback_title    = (string) ( $a['fallback_title'] ?? 'Nessuna disponibilità' );
$fallback_text     = (string) ( $a['fallback_text']  ?? 'Al momento non ci sono elementi prenotabili per questa tipologia. Scrivi la tua richiesta nel messaggio del modulo e ti risponderemo appena possibile.' );

$max_items_per_tab = max( 1, (int) ( $a['max_items_per_tab'] ?? 60 ) );
$cards_per_page     = max( 1, (int) ( $a['cards_per_page'] ?? 12 ) );
$card_columns       = max( 2, (int) ( $a['card_columns'] ?? 4 ) );
$max_width          = (int) ( $a['max_width'] ?? 1320 );

$select_bg_color  = (string) ( $a['select_bg_color']  ?? '#F5F1E7' );
$mobile_select_bg_color = (string) ( $a['mobile_select_bg_color'] ?? '#0a2540' );
$select_padding_y = (int)    ( $a['select_padding_y'] ?? 40 );

$tabs_track_bg_color         = (string) ( $a['tabs_track_bg_color']         ?? '#ffffff' );
$tab_text_color              = (string) ( $a['tab_text_color']              ?? '#0a2540' );
$tab_active_bg_color         = (string) ( $a['tab_active_bg_color']         ?? '#0a2540' );
$tab_active_text_color       = (string) ( $a['tab_active_text_color']       ?? '#ffffff' );
$tab_count_bg_color          = (string) ( $a['tab_count_bg_color']          ?? '#eef1f4' );
$tab_count_text_color        = (string) ( $a['tab_count_text_color']       ?? '#0a2540' );
$tab_count_active_bg_color   = (string) ( $a['tab_count_active_bg_color']   ?? '#2f87b3' );
$tab_count_active_text_color = (string) ( $a['tab_count_active_text_color'] ?? '#ffffff' );
$tab_font_size               = (int)    ( $a['tab_font_size']               ?? 14 );
$tab_font_weight             = (int)    ( $a['tab_font_weight']             ?? 600 );

$card_bg_color                 = (string) ( $a['card_bg_color']                 ?? '#ffffff' );
$card_img_bg_color             = (string) ( $a['card_img_bg_color']             ?? '#0a2540' );
$card_media_height              = (int)    ( $a['card_media_height']              ?? 220 );
$card_radius                    = (int)    ( $a['card_radius']                    ?? 16 );
$card_selected_border_color     = (string) ( $a['card_selected_border_color']     ?? '#ff6b4a' );
$card_selected_badge_bg_color   = (string) ( $a['card_selected_badge_bg_color']   ?? '#f5a623' );
$card_selected_badge_text_color = (string) ( $a['card_selected_badge_text_color'] ?? '#ffffff' );
$card_date_bg_color             = (string) ( $a['card_date_bg_color']             ?? '#ffffff' );
$card_date_num_color            = (string) ( $a['card_date_num_color']            ?? '#3473A5' );
$card_date_label_color          = (string) ( $a['card_date_label_color']          ?? '#5c6b75' );
$card_media_title_bg_color      = (string) ( $a['card_media_title_bg_color']      ?? 'rgba(10,37,64,.6)' );
$card_media_title_color         = (string) ( $a['card_media_title_color']         ?? '#ffffff' );
$card_type_badge_bg_color       = (string) ( $a['card_type_badge_bg_color']       ?? '#e6f1fa' );
$card_type_badge_text_color     = (string) ( $a['card_type_badge_text_color']     ?? '#3473A5' );
$card_title_color               = (string) ( $a['card_title_color']               ?? '#0a2540' );
$card_title_size                = (int)    ( $a['card_title_size']                ?? 18 );
$card_title_font_weight         = (int)    ( $a['card_title_font_weight']         ?? 700 );
$card_meta_text_color           = (string) ( $a['card_meta_text_color']           ?? '#5c6b75' );
$card_divider_color             = (string) ( $a['card_divider_color']             ?? '#e9edf0' );
$card_level_text_color          = (string) ( $a['card_level_text_color']          ?? '#5c6b75' );
$card_spots_text_color          = (string) ( $a['card_spots_text_color']          ?? '#3473A5' );
$card_spots_warn_color          = (string) ( $a['card_spots_warn_color']          ?? '#ff6b4a' );

$filter_active_border_color = (string) ( $a['filter_active_border_color'] ?? '#f5a623' );
$filter_active_bg_color     = (string) ( $a['filter_active_bg_color']     ?? '#fff7e8' );
$filter_active_text_color   = (string) ( $a['filter_active_text_color']   ?? '#b9790a' );

$data_bg_color    = (string) ( $a['data_bg_color']    ?? '#3473A5' );
$mobile_data_bg_color = (string) ( $a['mobile_data_bg_color'] ?? '#F5F1E7' );
$data_padding_y   = (int)    ( $a['data_padding_y']   ?? 48 );

$form_bg_color          = (string) ( $a['form_bg_color']          ?? '#ffffff' );
$form_radius             = (int)    ( $a['form_radius']             ?? 18 );
$form_title_tag          = calypsosub_title_tag( (string) ( $a['form_title_tag'] ?? 'h3' ), 'h3' );
$form_title_color        = (string) ( $a['form_title_color']        ?? '#0a2540' );
$form_title_size         = (int)    ( $a['form_title_size']         ?? 20 );
$form_title_font_weight  = (int)    ( $a['form_title_font_weight']  ?? 800 );

$sidebar_bg_color    = (string) ( $a['sidebar_bg_color']    ?? '#0a2540' );
$sidebar_text_color  = (string) ( $a['sidebar_text_color'] ?? '#ffffff' );
$sidebar_radius      = (int)    ( $a['sidebar_radius']     ?? 18 );
$side_badge_bg_color = (string) ( $a['side_badge_bg_color'] ?? '#ff6b4a' );
$side_title_tag          = calypsosub_title_tag( (string) ( $a['side_title_tag'] ?? 'h3' ), 'h3' );
$side_title_color        = (string) ( $a['side_title_color']        ?? '#ffffff' );
$side_title_size         = (int)    ( $a['side_title_size']         ?? 18 );
$side_title_font_weight  = (int)    ( $a['side_title_font_weight']  ?? 800 );
$side_luogo_color        = (string) ( $a['side_luogo_color']        ?? 'rgba(255,255,255,.75)' );
$side_label_color        = (string) ( $a['side_label_color']        ?? 'rgba(255,255,255,.55)' );
$side_value_color        = (string) ( $a['side_value_color']        ?? '#ffffff' );
$mobile_summary_border_color = (string) ( $a['mobile_summary_border_color'] ?? 'rgba(255,255,255,.15)' );
$mobile_summary_border_width = (int)    ( $a['mobile_summary_border_width'] ?? 0 );

global $calypsosub_booking_manager;

$tipi = [];
if ( $enable_corsi  && $cf7_form_corsi )  $tipi['corsi']  = $cf7_form_corsi;
if ( $enable_uscite && $cf7_form_uscite ) $tipi['uscite'] = $cf7_form_uscite;
if ( $enable_eventi && $cf7_form_eventi ) $tipi['eventi'] = $cf7_form_eventi;

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
	$id  = $post->ID;
	$src = get_post_thumbnail_id( $id ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large' ) : false;
	$card = [
		'id'    => $id,
		'tipo'  => $tipo,
		'title' => get_the_title( $id ),
		'img'   => $src ? $src[0] : '',
		'img_w' => $src ? (int) $src[1] : 0,
		'img_h' => $src ? (int) $src[2] : 0,
	];

	if ( $tipo === 'uscite' ) {
		$uscita_id = (int) get_post_meta( $id, '_occorrenza_uscita_uscita_id', true );
		$card['id']        = $id; // l'ID prenotabile resta quello dell'occorrenza
		$card['uscita_id'] = $uscita_id; // per raggruppare "altre date dello stesso itinerario" in mobile
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
		$card['scaduta']   = $card['data'] !== '' && $card['data'] < current_time( 'Y-m-d\TH:i' );
		$card['disponibile'] = $card['scaduta'] ? false : ( $card['posti'] === null ? true : ( $card['posti'] > 0 ) );
	} elseif ( $tipo === 'eventi' ) {
		$date = (array) ( get_post_meta( $id, '_evento_date', true ) ?: [] );
		$card['data']  = calypso_next_future_date( $date );
		$card['luogo'] = (string) get_post_meta( $id, '_evento_luogo', true );
		$max           = get_post_meta( $id, '_evento_max_partecipanti', true );
		$card['max']   = ( $max !== '' && $max !== false ) ? (int) $max : null;
		$card['posti'] = $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ? $calypsosub_booking_manager->get_remaining_spots( $id ) : null;
		$card['badge']   = __( 'Evento', 'calypsosub' );
		$card['livello'] = __( 'Ingresso libero', 'calypsosub' );
		$card['sottotitolo'] = $card['luogo'];
		$card['mese_key']  = $card['data'] ? date( 'Y-m', strtotime( $card['data'] ) ) : '';
		$card['scaduta']   = $card['data'] !== '' && $card['data'] < current_time( 'Y-m-d\TH:i' );
		$card['disponibile'] = $card['scaduta'] ? false : ( $card['posti'] === null ? true : ( $card['posti'] > 0 ) );
	} else {
		$card['badge']        = (string) get_post_meta( $id, '_corso_badge', true ) ?: __( 'Corso', 'calypsosub' );
		$card['durata']       = (string) get_post_meta( $id, '_corso_stat_durata', true );
		$card['immersioni']   = (string) get_post_meta( $id, '_corso_stat_pratica', true );
		$card['profondita']   = (string) get_post_meta( $id, '_corso_stat_profondita', true );
		$card['periodo']      = (string) get_post_meta( $id, '_corso_periodo', true );
		$card['requisiti']    = (string) get_post_meta( $id, '_corso_requisiti', true );
		$card['max']          = null;
		$card['posti']        = null;
		$livelli         = wp_get_post_terms( $id, 'calypso_livello', [ 'fields' => 'names' ] );
		$card['livello'] = ( ! is_wp_error( $livelli ) && ! empty( $livelli ) ) ? $livelli[0] : __( 'Tutti i livelli', 'calypsosub' );
		$card['sottotitolo'] = trim( $card['periodo'] . ( ! empty( $card['durata'] ) ? ' · ' . $card['durata'] : '' ) );
		$card['mese_key']     = '';
		$card['disponibile']  = true;
	}
	return $card;
};

$items_by_tipo = [];
if ( isset( $tipi['uscite'] ) ) {
	// Uscite future (prenotabili), in ordine cronologico crescente.
	$occorrenze_future = get_posts( [
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
	// Uscite passate, non più prenotabili: mostrate comunque (con warning in
	// UI) sotto le future, ordinate dalla più recente alla più vecchia.
	$occorrenze_passate = get_posts( [
		'post_type'      => 'calypso_occ_uscita',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_occorrenza_uscita_data',
		'order'          => 'DESC',
		'meta_query'     => [ [
			'key'     => '_occorrenza_uscita_data',
			'value'   => current_time( 'Y-m-d\TH:i' ),
			'compare' => '<',
		] ],
	] );
	$occorrenze_uscite = array_merge( $occorrenze_future, $occorrenze_passate );
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

/**
 * Una tipologia è "senza nulla da prenotare" se non ha card, o se le ha ma
 * nessuna è disponibile (es. tutte le uscite elencate sono scadute/esaurite).
 * In quel caso, se è impostato un form di fallback, viene mostrato quello al
 * posto del form di prenotazione normale.
 */
$tipo_has_bookable = [];
foreach ( $items_by_tipo as $tipo => $cards ) {
	$tipo_has_bookable[ $tipo ] = (bool) array_filter( $cards, static fn( $c ) => $c['disponibile'] ?? true );
}

$tipo_labels = [ 'uscite' => __( 'Uscite in mare', 'calypsosub' ), 'eventi' => __( 'Eventi sociali', 'calypsosub' ), 'corsi' => __( 'Corsi', 'calypsosub' ) ];

// Link "Calendario completo" nel bottom-sheet mobile, uno per CPT, stessa
// convenzione usata nei template single-* (get_post_type_archive_link()).
$archive_links = [
	'uscite' => (string) ( get_post_type_archive_link( 'calypso_uscita' ) ?: '' ),
	'eventi' => (string) ( get_post_type_archive_link( 'calypso_evento' ) ?: '' ),
	'corsi'  => (string) ( get_post_type_archive_link( 'calypso_corso' )  ?: '' ),
];

$giorni_it = [ 'Sun' => 'DOM', 'Mon' => 'LUN', 'Tue' => 'MAR', 'Wed' => 'MER', 'Thu' => 'GIO', 'Fri' => 'VEN', 'Sat' => 'SAB' ];
$mesi_it   = [
	'01' => 'GEN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
	'05' => 'MAG', '06' => 'GIU', '07' => 'LUG', '08' => 'AGO',
	'09' => 'SET', '10' => 'OTT', '11' => 'NOV', '12' => 'DIC',
];
$mesi_it_full = [
	'01' => __( 'Gennaio', 'calypsosub' ), '02' => __( 'Febbraio', 'calypsosub' ),
	'03' => __( 'Marzo', 'calypsosub' ),   '04' => __( 'Aprile', 'calypsosub' ),
	'05' => __( 'Maggio', 'calypsosub' ),  '06' => __( 'Giugno', 'calypsosub' ),
	'07' => __( 'Luglio', 'calypsosub' ),  '08' => __( 'Agosto', 'calypsosub' ),
	'09' => __( 'Settembre', 'calypsosub' ), '10' => __( 'Ottobre', 'calypsosub' ),
	'11' => __( 'Novembre', 'calypsosub' ),  '12' => __( 'Dicembre', 'calypsosub' ),
];

/**
 * Per tab: opzioni filtro derivate dai dati reali (mese/luogo per
 * uscite/eventi, livello per corsi) — niente valori inventati.
 */
$filter_options = [];
foreach ( $items_by_tipo as $tipo => $cards ) {
	if ( $tipo === 'corsi' ) {
		$livelli = [];
		foreach ( $cards as $card ) {
			if ( ! empty( $card['livello'] ) ) $livelli[ $card['livello'] ] = $card['livello'];
		}
		ksort( $livelli, SORT_NATURAL | SORT_FLAG_CASE );
		$filter_options[ $tipo ] = [ 'livelli' => $livelli ];
	} else {
		$mesi = [];
		$luoghi = [];
		foreach ( $cards as $card ) {
			if ( ! empty( $card['mese_key'] ) ) {
				$label = $mesi_it_full[ substr( $card['mese_key'], 5, 2 ) ] ?? $card['mese_key'];
				$mesi[ $card['mese_key'] ] = $label . ' ' . substr( $card['mese_key'], 0, 4 );
			}
			if ( ! empty( $card['luogo'] ) ) $luoghi[ $card['luogo'] ] = $card['luogo'];
		}
		ksort( $mesi );
		ksort( $luoghi, SORT_NATURAL | SORT_FLAG_CASE );
		$filter_options[ $tipo ] = [ 'mesi' => $mesi, 'luoghi' => $luoghi ];
	}
}

/**
 * Badge data card (numero giorno + "MES · GIO") a partire dalla stringa
 * data della card, stesso formato usato in block-calendario.php.
 */
$card_date_badge = static function ( string $date_str ) use ( $giorni_it, $mesi_it ): ?array {
	if ( ! $date_str ) return null;
	$ts = strtotime( $date_str );
	if ( ! $ts ) return null;
	return [
		'num'   => date( 'j', $ts ),
		'mese'  => $mesi_it[ date( 'm', $ts ) ] ?? strtoupper( date_i18n( 'M', $ts ) ),
		'giorno' => $giorni_it[ date( 'D', $ts ) ] ?? date( 'D', $ts ),
	];
};

$uid = 'cso-pren-' . sprintf( '%08x', crc32( implode( ',', array_keys( $tipi ) ) . $max_width ) );

// Nonce CSRF per la richiesta AJAX di rendering del form (richiesto da
// Calypsosub_CF7_Booking_Handler::ajax_render_form() via check_ajax_referer()).
$ajax_nonce = wp_create_nonce( 'calypso_prenotazione_form' );

// Se il tab iniziale non ha nulla di prenotabile, mostra il form di fallback
// (se configurato) al posto del form di prenotazione, senza selezionare
// nessuna card né popolare la sidebar con i dettagli di un elemento non
// prenotabile.
$preselect_tab_bookable = $tipo_has_bookable[ $preselect_tab ] ?? true;
$preselect_tab_mode     = ( ! $preselect_tab_bookable && $cf7_form_fallback ) ? 'fallback' : 'normal';
$effective_preselect_id = $preselect_tab_mode === 'normal' ? $preselect_id : 0;

// Unica fonte di verità per cosa mostrare nel form-wrap/sidebar del tab
// iniziale: il login-wall ha sempre la precedenza sul fallback.
$preselect_form_mode = ( ! $user_logged_in && ! empty( $require_login_map[ $preselect_tab ] ) ) ? 'login' : $preselect_tab_mode;

// Pre-render del form CF7 del tipo attivo (solo se non bloccato da require_login).
$initial_form_html = '';
if ( $user_logged_in || empty( $require_login_map[ $preselect_tab ] ) ) {
	$initial_form_id = $preselect_tab_mode === 'fallback' ? $cf7_form_fallback : (int) $tipi[ $preselect_tab ];
	Calypsosub_CF7_Booking_Handler::$active_post_id = $effective_preselect_id ?: null;
	$initial_form_html = do_shortcode( '[contact-form-7 id="' . $initial_form_id . '"]' );
	Calypsosub_CF7_Booking_Handler::$active_post_id = null;
}

$preselected_card = null;
if ( $effective_preselect_id && isset( $items_by_tipo[ $preselect_tab ] ) ) {
	foreach ( $items_by_tipo[ $preselect_tab ] as $card ) {
		if ( $card['id'] === $effective_preselect_id ) { $preselected_card = $card; break; }
	}
}
?>
<style>
#<?php echo $uid; ?>{font-family:inherit;}
#<?php echo $uid; ?> *{box-sizing:border-box;}

/* ── Sezione 1: selezione (chiara) ── */
#<?php echo $uid; ?> .cso-pren__select{background:<?php echo esc_attr( $select_bg_color ); ?>;padding:<?php echo $select_padding_y; ?>px 24px <?php echo (int) ( $select_padding_y * 1.4 ); ?>px;}
#<?php echo $uid; ?> .cso-pren__inner{max-width:<?php echo $max_width; ?>px;margin:0 auto;}
#<?php echo $uid; ?> .cso-pren__tabs{display:inline-flex;align-items:center;gap:4px;margin-bottom:28px;flex-wrap:wrap;background:<?php echo esc_attr( $tabs_track_bg_color ); ?>;border-radius:999px;padding:6px;box-shadow:0 10px 30px -18px rgba(10,37,64,.35);}
#<?php echo $uid; ?> .cso-pren__tab{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:999px;border:none;background:transparent;cursor:pointer;font-weight:<?php echo $tab_font_weight; ?>;font-size:<?php echo $tab_font_size; ?>px;color:<?php echo esc_attr( $tab_text_color ); ?>;transition:background .15s,color .15s;}
#<?php echo $uid; ?> .cso-pren__tab.is-active{background:<?php echo esc_attr( $tab_active_bg_color ); ?>;color:<?php echo esc_attr( $tab_active_text_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__tab-count{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;border-radius:999px;font-size:11px;font-weight:700;background:<?php echo esc_attr( $tab_count_bg_color ); ?>;color:<?php echo esc_attr( $tab_count_text_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__tab.is-active .cso-pren__tab-count{background:<?php echo esc_attr( $tab_count_active_bg_color ); ?>;color:<?php echo esc_attr( $tab_count_active_text_color ); ?>;}

#<?php echo $uid; ?> .cso-pren__toolbar{display:flex;align-items:center;gap:28px;flex-wrap:wrap;margin-bottom:28px;}
#<?php echo $uid; ?> .cso-pren__toolbar .cso-pren__tabs{margin-bottom:0;}

#<?php echo $uid; ?> .cso-pren__filters{display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap;}
#<?php echo $uid; ?> .cso-pren__filter-group{display:flex;flex-direction:column;gap:7px;}
#<?php echo $uid; ?> .cso-pren__filter-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:<?php echo esc_attr( $card_level_text_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__filter-options{display:flex;gap:8px;flex-wrap:wrap;}
#<?php echo $uid; ?> .cso-pren__filter-btn{padding:9px 16px;border-radius:10px;border:1px solid <?php echo esc_attr( $card_divider_color ); ?>;background:<?php echo esc_attr( $card_bg_color ); ?>;color:<?php echo esc_attr( $card_meta_text_color ); ?>;font-size:13px;font-weight:600;font:inherit;cursor:pointer;white-space:nowrap;transition:border-color .15s,background .15s,color .15s;}
#<?php echo $uid; ?> .cso-pren__filter-btn:hover{border-color:<?php echo esc_attr( $filter_active_border_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__filter-btn.is-active{border-color:<?php echo esc_attr( $filter_active_border_color ); ?>;background:<?php echo esc_attr( $filter_active_bg_color ); ?>;color:<?php echo esc_attr( $filter_active_text_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__filter-reset{align-self:center;border:none;background:transparent;color:<?php echo esc_attr( $card_selected_border_color ); ?>;font-size:13px;font-weight:600;cursor:pointer;padding:4px 0;text-decoration:underline;}

#<?php echo $uid; ?> .cso-pren__cards{display:grid;grid-template-columns:repeat(<?php echo $card_columns; ?>,1fr);align-items:stretch;gap:20px;}
@media(max-width:1100px){#<?php echo $uid; ?> .cso-pren__cards{grid-template-columns:repeat(2,1fr);}}
@media(max-width:560px){#<?php echo $uid; ?> .cso-pren__cards{grid-template-columns:1fr;}}

#<?php echo $uid; ?> .cso-pren__empty{padding:40px 20px;text-align:center;color:<?php echo esc_attr( $card_meta_text_color ); ?>;font-size:14px;}
#<?php echo $uid; ?> .cso-pren__empty .cso-pren__filter-reset{margin-left:6px;}

#<?php echo $uid; ?> .cso-pren__pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:24px;}
#<?php echo $uid; ?> .cso-pren__page-btn{min-width:34px;height:34px;padding:0 8px;border-radius:999px;border:1px solid <?php echo esc_attr( $card_divider_color ); ?>;background:<?php echo esc_attr( $card_bg_color ); ?>;color:<?php echo esc_attr( $tab_text_color ); ?>;font-size:13px;font-weight:600;cursor:pointer;}
#<?php echo $uid; ?> .cso-pren__page-btn.is-active{background:<?php echo esc_attr( $tab_active_bg_color ); ?>;color:<?php echo esc_attr( $tab_active_text_color ); ?>;border-color:<?php echo esc_attr( $tab_active_bg_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__page-btn:disabled{opacity:.4;cursor:default;}
#<?php echo $uid; ?> .cso-pren__page-ellipsis{padding:0 4px;color:<?php echo esc_attr( $card_meta_text_color ); ?>;font-size:13px;}

#<?php echo $uid; ?> .cso-pren__card{display:flex;flex-direction:column;align-self:stretch;appearance:none;-webkit-appearance:none;margin:0;padding:0;font:inherit;outline:none;border:0;border-radius:<?php echo $card_radius; ?>px;overflow:hidden;cursor:pointer;background:<?php echo esc_attr( $card_bg_color ); ?>;text-align:left;box-shadow:0 10px 30px -16px rgba(10,37,64,.25);transition:transform .15s,box-shadow .15s;}
#<?php echo $uid; ?> .cso-pren__card:hover{transform:translateY(-2px);}
#<?php echo $uid; ?> .cso-pren__card:focus-visible{outline:2px solid <?php echo esc_attr( $card_selected_border_color ); ?>;outline-offset:2px;}
#<?php echo $uid; ?> .cso-pren__card.is-selected{box-shadow:0 0 0 3px <?php echo esc_attr( $card_selected_border_color ); ?>,0 10px 30px -16px rgba(10,37,64,.25);}
#<?php echo $uid; ?> .cso-pren__card.is-scaduta{cursor:default;opacity:.6;}
#<?php echo $uid; ?> .cso-pren__card-expired-badge{position:absolute;top:10px;right:10px;display:inline-flex;align-items:center;gap:5px;background:<?php echo esc_attr( $card_spots_warn_color ); ?>;color:#fff;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:5px 12px;border-radius:999px;}

#<?php echo $uid; ?> .cso-pren__card-media{position:relative;height:<?php echo $card_media_height; ?>px;background:<?php echo esc_attr( $card_img_bg_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__card-media-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;display:block;margin:0;}
#<?php echo $uid; ?> .cso-pren__card-date{position:absolute;top:10px;left:10px;background:<?php echo esc_attr( $card_date_bg_color ); ?>;border-radius:10px;padding:6px 10px;text-align:center;line-height:1.1;min-width:48px;box-shadow:0 6px 16px -8px rgba(10,37,64,.4);}
#<?php echo $uid; ?> .cso-pren__card-date-num{display:block;font-size:18px;font-weight:800;color:<?php echo esc_attr( $card_date_num_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__card-date-label{display:block;font-size:9px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:<?php echo esc_attr( $card_date_label_color ); ?>;margin-top:2px;}
#<?php echo $uid; ?> .cso-pren__card-selected-badge{display:none;position:absolute;top:10px;right:10px;align-items:center;gap:5px;background:<?php echo esc_attr( $card_selected_badge_bg_color ); ?>;color:<?php echo esc_attr( $card_selected_badge_text_color ); ?>;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:5px 12px;border-radius:999px;}
#<?php echo $uid; ?> .cso-pren__card.is-selected .cso-pren__card-selected-badge{display:inline-flex;}
#<?php echo $uid; ?> .cso-pren__card-selected-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;}
#<?php echo $uid; ?> .cso-pren__card-media-title{position:absolute;left:0;bottom:0;padding:10px 14px;background:<?php echo esc_attr( $card_media_title_bg_color ); ?>;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border-top-right-radius:8px;font-size:11px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:<?php echo esc_attr( $card_media_title_color ); ?>;}

#<?php echo $uid; ?> .cso-pren__card-body{padding:18px 16px;}
#<?php echo $uid; ?> .cso-pren__card-type-badge{display:inline-block;background:<?php echo esc_attr( $card_type_badge_bg_color ); ?>;color:<?php echo esc_attr( $card_type_badge_text_color ); ?>;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;padding:4px 12px;border-radius:999px;margin-bottom:10px;}
#<?php echo $uid; ?> .cso-pren__card-title{font-weight:<?php echo $card_title_font_weight; ?>;font-size:<?php echo $card_title_size; ?>px;color:<?php echo esc_attr( $card_title_color ); ?>;margin-bottom:8px;line-height:1.25;}
#<?php echo $uid; ?> .cso-pren__card-meta{display:flex;align-items:center;gap:6px;font-size:13px;color:<?php echo esc_attr( $card_meta_text_color ); ?>;margin-bottom:14px;}
#<?php echo $uid; ?> .cso-pren__card-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;padding-top:12px;border-top:1px solid <?php echo esc_attr( $card_divider_color ); ?>;font-size:12px;}
#<?php echo $uid; ?> .cso-pren__card-level{color:<?php echo esc_attr( $card_level_text_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__card-spots{font-weight:700;color:<?php echo esc_attr( $card_spots_text_color ); ?>;display:inline-flex;align-items:center;gap:5px;}
#<?php echo $uid; ?> .cso-pren__card-spots::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;}
#<?php echo $uid; ?> .cso-pren__card-spots--warn{color:<?php echo esc_attr( $card_spots_warn_color ); ?>;}

/* ── Sezione 2: dati + riepilogo (blu) ── */
#<?php echo $uid; ?> .cso-pren__data{background:<?php echo esc_attr( $data_bg_color ); ?>;padding:<?php echo $data_padding_y; ?>px 24px <?php echo (int) ( $data_padding_y * 1.33 ); ?>px;}
#<?php echo $uid; ?> .cso-pren__data-layout{max-width:<?php echo $max_width; ?>px;margin:0 auto;display:grid;grid-template-columns:1.4fr 1fr;gap:28px;align-items:start;}
@media(max-width:900px){#<?php echo $uid; ?> .cso-pren__data-layout{grid-template-columns:1fr;}}

#<?php echo $uid; ?> .cso-pren__form-card{background:<?php echo esc_attr( $form_bg_color ); ?>;border-radius:<?php echo $form_radius; ?>px;padding:32px;}
#<?php echo $uid; ?> .cso-pren__form-head{display:flex;align-items:center;gap:14px;margin-bottom:6px;}
#<?php echo $uid; ?> .cso-pren__form-title{font-size:<?php echo $form_title_size; ?>px;font-weight:<?php echo $form_title_font_weight; ?>;color:<?php echo esc_attr( $form_title_color ); ?>;margin:0;}
#<?php echo $uid; ?> .cso-pren__form-sub{font-size:13px;color:rgba(11,26,38,.55);margin:0 0 24px 50px;}
#<?php echo $uid; ?> .cso-pren__form-wrap.is-hidden{display:none;}

#<?php echo $uid; ?> .cso-pren__sidebar{position:sticky;top:24px;background:<?php echo esc_attr( $sidebar_bg_color ); ?>;color:<?php echo esc_attr( $sidebar_text_color ); ?>;border-radius:<?php echo $sidebar_radius; ?>px;overflow:hidden;}
#<?php echo $uid; ?> .cso-pren__side-img{height:160px;background:rgba(255,255,255,.08) center/cover;position:relative;}
#<?php echo $uid; ?> .cso-pren__side-badge{position:absolute;top:12px;left:12px;background:<?php echo esc_attr( $side_badge_bg_color ); ?>;color:#fff;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:4px 10px;border-radius:999px;}
#<?php echo $uid; ?> .cso-pren__side-body{padding:22px 24px 26px;}
#<?php echo $uid; ?> .cso-pren__side-title{margin:0 0 6px;font-size:<?php echo $side_title_size; ?>px;font-weight:<?php echo $side_title_font_weight; ?>;color:<?php echo esc_attr( $side_title_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__side-luogo{margin:0 0 16px;font-size:13px;color:<?php echo esc_attr( $side_luogo_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__sidebar dl{margin:0;}
#<?php echo $uid; ?> .cso-pren__sidebar dt{font-size:11px;text-transform:uppercase;color:<?php echo esc_attr( $side_label_color ); ?>;letter-spacing:.04em;margin-top:14px;}
#<?php echo $uid; ?> .cso-pren__sidebar dd{margin:2px 0 0;font-size:13px;color:<?php echo esc_attr( $side_value_color ); ?>;}
#<?php echo $uid; ?> .cso-pren__side-empty{padding:32px 24px;opacity:.7;font-size:13px;}
#<?php echo $uid; ?> .cso-pren__side-fallback-text{margin:0;font-size:13px;line-height:1.5;color:<?php echo esc_attr( $side_luogo_color ); ?>;}

/* ── Mobile/tablet (<=900px): sezione 1 diventa il riepilogo compatto
   (colori invertiti con la sezione dati: qui blu), sezione 2 resta solo
   il form (colori invertiti: qui chiara), sidebar nascosta perché il suo
   contenuto confluisce nella card di riepilogo. Tablet usa lo stesso
   layout, solo leggermente più largo/arioso. ── */
#<?php echo $uid; ?> .cso-pren__mobile-select{display:none;}
#<?php echo $uid; ?> .cso-pren__mobile-cta{display:none;}
#<?php echo $uid; ?> .cso-pren__sheet{display:none;}

@media(max-width:900px){
	#<?php echo $uid; ?> .cso-pren__select{background:<?php echo esc_attr( $mobile_select_bg_color ); ?>;padding:28px 20px 32px;}
	#<?php echo $uid; ?> .cso-pren__toolbar,
	#<?php echo $uid; ?> .cso-pren__panel{display:none !important;}
	#<?php echo $uid; ?> .cso-pren__mobile-select{display:block;}

	#<?php echo $uid; ?> .cso-pren__mobile-eyebrow{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:<?php echo esc_attr( $side_label_color ); ?>;margin-bottom:12px;}

	#<?php echo $uid; ?> .cso-pren__mobile-summary{background:<?php echo esc_attr( $sidebar_bg_color ); ?>;color:<?php echo esc_attr( $sidebar_text_color ); ?>;border-radius:<?php echo $sidebar_radius; ?>px;overflow:hidden;<?php echo $mobile_summary_border_width > 0 ? 'border:' . $mobile_summary_border_width . 'px solid ' . esc_attr( $mobile_summary_border_color ) . ';' : ''; ?>}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-media{position:relative;height:230px;background:<?php echo esc_attr( $card_img_bg_color ); ?> center/cover;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-date{position:absolute;top:10px;left:10px;background:<?php echo esc_attr( $card_date_bg_color ); ?>;border-radius:10px;padding:6px 10px;text-align:center;line-height:1.1;min-width:48px;box-shadow:0 6px 16px -8px rgba(10,37,64,.4);}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-date-num{display:block;font-size:18px;font-weight:800;color:<?php echo esc_attr( $card_date_num_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-date-label{display:block;font-size:9px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:<?php echo esc_attr( $card_date_label_color ); ?>;margin-top:2px;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-badge{position:absolute;top:12px;right:12px;background:<?php echo esc_attr( $side_badge_bg_color ); ?>;color:#fff;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:5px 12px;border-radius:999px;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-overlay{position:absolute;left:0;right:0;bottom:0;padding:44px 18px 16px;background:linear-gradient(to top,rgba(10,37,64,.92),rgba(10,37,64,.6) 60%,rgba(10,37,64,0));}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-title{margin:0 0 4px;font-size:<?php echo $side_title_size; ?>px;font-weight:<?php echo $side_title_font_weight; ?>;color:<?php echo esc_attr( $side_title_color ); ?>;line-height:1.25;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-luogo{margin:0;font-size:13px;color:<?php echo esc_attr( $side_luogo_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-body{padding:0 20px 20px;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-stats{display:grid;grid-template-columns:repeat(var(--cso-pren-stat-cols,3),1fr);gap:0;padding-top:16px;border-top:1px solid rgba(255,255,255,.12);}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-stat{padding-left:14px;border-left:1px solid rgba(255,255,255,.12);}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-stat:first-child{padding-left:0;border-left:none;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-stat dt{font-size:10px;text-transform:uppercase;color:<?php echo esc_attr( $side_label_color ); ?>;letter-spacing:.04em;margin:0;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-stat dd{margin:4px 0 0;font-size:14px;font-weight:700;color:<?php echo esc_attr( $side_value_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-empty{padding:28px 22px;font-size:13px;opacity:.85;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-fallback-text{margin:8px 0 0;font-size:13px;line-height:1.5;opacity:.85;}

	#<?php echo $uid; ?> .cso-pren__mobile-dates{margin-top:20px;}
	#<?php echo $uid; ?> .cso-pren__mobile-dates-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.7);margin-bottom:10px;}
	#<?php echo $uid; ?> .cso-pren__mobile-dates-track{display:flex;gap:8px;overflow-x:auto;padding-bottom:2px;-webkit-overflow-scrolling:touch;}
	#<?php echo $uid; ?> .cso-pren__mobile-date-pill{flex:0 0 auto;display:flex;flex-direction:column;align-items:center;gap:2px;min-width:58px;padding:8px 6px;border-radius:12px;border:1px solid rgba(255,255,255,.25);background:transparent;color:#fff;font:inherit;cursor:pointer;}
	#<?php echo $uid; ?> .cso-pren__mobile-date-pill-num{font-size:15px;font-weight:800;}
	#<?php echo $uid; ?> .cso-pren__mobile-date-pill-mese{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;opacity:.8;}
	#<?php echo $uid; ?> .cso-pren__mobile-date-pill-posti{font-size:9px;opacity:.75;margin-top:2px;}
	#<?php echo $uid; ?> .cso-pren__mobile-date-pill.is-active{background:#fff;border-color:#fff;color:<?php echo esc_attr( $sidebar_bg_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__mobile-date-pill:disabled{opacity:.45;cursor:default;}

	#<?php echo $uid; ?> .cso-pren__mobile-browse{display:block;width:100%;margin-top:20px;padding:14px 18px;border-radius:999px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.08);color:#fff;font:inherit;font-size:14px;font-weight:700;cursor:pointer;text-align:center;}

	#<?php echo $uid; ?> .cso-pren__data{background:<?php echo esc_attr( $mobile_data_bg_color ); ?>;padding:28px 20px 100px;}
	#<?php echo $uid; ?> .cso-pren__sidebar{display:none;}
	#<?php echo $uid; ?> .cso-pren__form-card{padding:22px;border-radius:<?php echo $form_radius; ?>px;}
	#<?php echo $uid; ?> .cso-pren__form-wrap .wpcf7-form > input[type="submit"],
	#<?php echo $uid; ?> .cso-pren__form-wrap .wpcf7-form input.wpcf7-submit{display:none;}

	#<?php echo $uid; ?> .cso-pren__mobile-cta.is-visible{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:40;align-items:center;gap:14px;padding:12px 16px calc(12px + env(safe-area-inset-bottom,0px));background:#fff;box-shadow:0 -8px 24px -12px rgba(10,37,64,.35);}
	#<?php echo $uid; ?> .cso-pren__mobile-cta-info{flex:1 1 auto;min-width:0;font-size:12px;font-weight:700;color:<?php echo esc_attr( $card_spots_text_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__mobile-cta-info.is-warn{color:<?php echo esc_attr( $card_spots_warn_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__mobile-cta-submit{flex:0 0 auto;border:none;border-radius:999px;padding:14px 22px;background:<?php echo esc_attr( $side_badge_bg_color ); ?>;color:#fff;font:inherit;font-size:14px;font-weight:700;cursor:pointer;white-space:nowrap;}

	#<?php echo $uid; ?> .cso-pren__sheet.is-open{display:block;position:fixed;inset:0;z-index:50;}
	#<?php echo $uid; ?> .cso-pren__sheet-backdrop{position:absolute;inset:0;background:rgba(10,37,64,.55);}
	#<?php echo $uid; ?> .cso-pren__sheet-panel{position:absolute;left:0;right:0;bottom:0;max-height:88vh;display:flex;flex-direction:column;background:<?php echo esc_attr( $mobile_data_bg_color ); ?>;border-radius:20px 20px 0 0;padding:10px 20px calc(16px + env(safe-area-inset-bottom,0px));}
	#<?php echo $uid; ?> .cso-pren__sheet-handle{width:40px;height:4px;border-radius:999px;background:<?php echo esc_attr( $card_divider_color ); ?>;margin:4px auto 14px;}
	#<?php echo $uid; ?> .cso-pren__sheet-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
	#<?php echo $uid; ?> .cso-pren__sheet-title{margin:0;font-size:18px;font-weight:800;color:<?php echo esc_attr( $form_title_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-close{border:none;background:transparent;color:<?php echo esc_attr( $card_date_num_color ); ?>;font:inherit;font-size:14px;font-weight:700;cursor:pointer;padding:4px;}
	#<?php echo $uid; ?> .cso-pren__sheet-search{width:100%;padding:13px 16px;border-radius:12px;border:1px solid <?php echo esc_attr( $card_divider_color ); ?>;background:#fff;font:inherit;font-size:14px;margin-bottom:12px;}
	#<?php echo $uid; ?> .cso-pren__sheet-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;}
	#<?php echo $uid; ?> .cso-pren__sheet-filter{padding:9px 16px;border-radius:999px;border:1px solid <?php echo esc_attr( $card_divider_color ); ?>;background:#fff;color:<?php echo esc_attr( $tab_text_color ); ?>;font:inherit;font-size:13px;font-weight:600;cursor:pointer;}
	#<?php echo $uid; ?> .cso-pren__sheet-filter.is-active{background:<?php echo esc_attr( $tab_active_bg_color ); ?>;border-color:<?php echo esc_attr( $tab_active_bg_color ); ?>;color:<?php echo esc_attr( $tab_active_text_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-list{overflow-y:auto;flex:1 1 auto;margin:4px -4px 0;padding:0 4px;}
	#<?php echo $uid; ?> .cso-pren__sheet-row{display:flex;align-items:center;gap:12px;width:100%;padding:14px 4px;border:none;border-top:1px solid <?php echo esc_attr( $card_divider_color ); ?>;background:transparent;font:inherit;text-align:left;cursor:pointer;}
	#<?php echo $uid; ?> .cso-pren__sheet-list>.cso-pren__sheet-row:first-child{border-top:none;}
	#<?php echo $uid; ?> .cso-pren__sheet-row:disabled{opacity:.5;cursor:default;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-date{flex:0 0 auto;width:44px;text-align:center;line-height:1.1;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-date-num{display:block;font-size:16px;font-weight:800;color:<?php echo esc_attr( $card_date_num_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-date-label{display:block;font-size:9px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:<?php echo esc_attr( $card_date_label_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-body{flex:1 1 auto;min-width:0;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-type{display:block;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:<?php echo esc_attr( $card_type_badge_text_color ); ?>;margin-bottom:2px;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-title{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:<?php echo esc_attr( $tab_active_bg_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-sub{font-size:12px;color:<?php echo esc_attr( $card_meta_text_color ); ?>;margin-top:2px;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-chosen{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:<?php echo esc_attr( $card_selected_badge_bg_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-chosen::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-posti{flex:0 0 auto;font-size:11px;font-weight:700;color:<?php echo esc_attr( $card_spots_text_color ); ?>;white-space:nowrap;}
	#<?php echo $uid; ?> .cso-pren__sheet-row-posti.is-warn{color:<?php echo esc_attr( $card_spots_warn_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-empty{padding:24px 4px;text-align:center;font-size:13px;color:<?php echo esc_attr( $card_meta_text_color ); ?>;}
	#<?php echo $uid; ?> .cso-pren__sheet-calendar-link{display:block;text-align:center;margin-top:10px;padding:10px 0 2px;font-size:13px;font-weight:700;color:<?php echo esc_attr( $card_date_num_color ); ?>;text-decoration:none;}
}

/* Tablet: stesso layout del mobile, solo un filo più largo/arioso. */
@media(min-width:601px) and (max-width:900px){
	#<?php echo $uid; ?> .cso-pren__select,
	#<?php echo $uid; ?> .cso-pren__data{padding-left:40px;padding-right:40px;}
	#<?php echo $uid; ?> .cso-pren__mobile-summary-stat{padding-left:22px;}
	#<?php echo $uid; ?> .cso-pren__sheet-panel{left:50%;right:auto;transform:translateX(-50%);width:min(560px,calc(100% - 40px));border-radius:20px;bottom:5vh;max-height:80vh;}
}
</style>
<div id="<?php echo $uid; ?>" data-preselect-id="<?php echo (int) $preselect_id; ?>" data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>" data-fallback-form-id="<?php echo (int) $cf7_form_fallback; ?>" data-user-prefill="<?php echo esc_attr( wp_json_encode( $user_prefill ) ); ?>">

	<section class="cso-pren__select">
		<div class="cso-pren__inner">

			<div class="cso-pren__mobile-select" data-mobile-select>
				<div class="cso-pren__mobile-eyebrow"><?php esc_html_e( 'Stai prenotando', 'calypsosub' ); ?></div>
				<div class="cso-pren__mobile-summary" data-mobile-summary></div>
				<div class="cso-pren__mobile-dates" data-mobile-dates style="display:none"></div>
				<button type="button" class="cso-pren__mobile-browse" data-mobile-browse><?php esc_html_e( "Prenota un'altra uscita, evento o corso", 'calypsosub' ); ?></button>
			</div>

			<div class="cso-pren__toolbar">
				<div class="cso-pren__tabs">
					<?php foreach ( $tipi as $tipo => $form_id ) : ?>
					<button type="button" class="cso-pren__tab<?php echo $tipo === $preselect_tab ? ' is-active' : ''; ?>" data-tipo="<?php echo esc_attr( $tipo ); ?>" data-form-id="<?php echo (int) $form_id; ?>">
						<?php echo esc_html( $tipo_labels[ $tipo ] ); ?>
						<span class="cso-pren__tab-count"><?php echo (int) count( $items_by_tipo[ $tipo ] ?? [] ); ?></span>
					</button>
					<?php endforeach; ?>
				</div>

				<?php foreach ( $items_by_tipo as $tipo => $cards ) :
					$opts = $filter_options[ $tipo ] ?? [];
				?>
				<div class="cso-pren__filters" data-filters data-tab-panel="<?php echo esc_attr( $tipo ); ?>" data-cards-per-page="<?php echo (int) $cards_per_page; ?>" style="<?php echo $tipo === $preselect_tab ? '' : 'display:none'; ?>">
					<?php if ( $tipo === 'corsi' ) : ?>
						<?php if ( ! empty( $opts['livelli'] ) ) : ?>
						<div class="cso-pren__filter-group" data-filter-group="livello">
							<span class="cso-pren__filter-label"><?php esc_html_e( 'Livello brevetto', 'calypsosub' ); ?></span>
							<div class="cso-pren__filter-options">
								<button type="button" class="cso-pren__filter-btn is-active" data-filter="livello" data-value=""><?php esc_html_e( 'Tutti', 'calypsosub' ); ?></button>
								<?php foreach ( $opts['livelli'] as $val => $label ) : ?>
								<button type="button" class="cso-pren__filter-btn" data-filter="livello" data-value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></button>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>
					<?php else : ?>
						<?php if ( ! empty( $opts['mesi'] ) ) : ?>
						<div class="cso-pren__filter-group" data-filter-group="mese">
							<span class="cso-pren__filter-label"><?php esc_html_e( 'Mese', 'calypsosub' ); ?></span>
							<div class="cso-pren__filter-options">
								<button type="button" class="cso-pren__filter-btn is-active" data-filter="mese" data-value=""><?php esc_html_e( 'Tutti', 'calypsosub' ); ?></button>
								<?php foreach ( $opts['mesi'] as $val => $label ) : ?>
								<button type="button" class="cso-pren__filter-btn" data-filter="mese" data-value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></button>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( ! empty( $opts['luoghi'] ) ) : ?>
						<div class="cso-pren__filter-group" data-filter-group="luogo">
							<span class="cso-pren__filter-label"><?php esc_html_e( 'Luogo', 'calypsosub' ); ?></span>
							<div class="cso-pren__filter-options">
								<button type="button" class="cso-pren__filter-btn is-active" data-filter="luogo" data-value=""><?php esc_html_e( 'Tutti', 'calypsosub' ); ?></button>
								<?php foreach ( $opts['luoghi'] as $val => $label ) : ?>
								<button type="button" class="cso-pren__filter-btn" data-filter="luogo" data-value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></button>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>
						<div class="cso-pren__filter-group" data-filter-group="disponibile">
							<span class="cso-pren__filter-label"><?php esc_html_e( 'Disponibilità', 'calypsosub' ); ?></span>
							<div class="cso-pren__filter-options">
								<button type="button" class="cso-pren__filter-btn is-active" data-filter="disponibile" data-value=""><?php esc_html_e( 'Tutti', 'calypsosub' ); ?></button>
								<button type="button" class="cso-pren__filter-btn" data-filter="disponibile" data-value="1"><?php esc_html_e( 'Solo disponibili', 'calypsosub' ); ?></button>
							</div>
						</div>
					<?php endif; ?>
					<button type="button" class="cso-pren__filter-reset" data-filter-reset style="display:none"><?php esc_html_e( 'Reimposta filtri', 'calypsosub' ); ?></button>
				</div>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $items_by_tipo as $tipo => $cards ) : ?>
			<div class="cso-pren__panel" data-tab-panel="<?php echo esc_attr( $tipo ); ?>" style="<?php echo $tipo === $preselect_tab ? '' : 'display:none'; ?>">

				<div class="cso-pren__cards" data-cards>
					<?php foreach ( $cards as $card ) :
						$is_selected = $card['id'] === $effective_preselect_id;
						$is_scaduta  = ! empty( $card['scaduta'] );
						$date_badge  = $card_date_badge( $card['data'] ?? '' );
						$spots_warn  = $is_scaduta || ( isset( $card['posti'] ) && $card['posti'] !== null && $card['posti'] <= 4 );
						$spots_text  = '';
						if ( $is_scaduta ) {
							$spots_text = __( 'Non prenotabile', 'calypsosub' );
						} elseif ( isset( $card['posti'] ) && $card['posti'] !== null && isset( $card['max'] ) && $card['max'] !== null ) {
							$spots_text = $card['posti'] . ' / ' . $card['max'];
						} elseif ( isset( $card['posti'] ) && $card['posti'] !== null ) {
							$spots_text = $card['posti'] > 0
								? sprintf( _n( '%d posto', '%d posti', $card['posti'], 'calypsosub' ), $card['posti'] )
								: __( 'Esaurito', 'calypsosub' );
						}
					?>
					<button type="button" class="cso-pren__card<?php echo $is_selected ? ' is-selected' : ''; ?><?php echo $is_scaduta ? ' is-scaduta' : ''; ?>" data-card="<?php echo esc_attr( wp_json_encode( $card ) ); ?>" data-filter-mese="<?php echo esc_attr( $card['mese_key'] ?? '' ); ?>" data-filter-luogo="<?php echo esc_attr( $card['luogo'] ?? '' ); ?>" data-filter-livello="<?php echo esc_attr( $card['livello'] ?? '' ); ?>" data-filter-disponibile="<?php echo ( $card['disponibile'] ?? true ) ? '1' : '0'; ?>"<?php echo $is_scaduta ? ' disabled aria-disabled="true"' : ''; ?>>
						<div class="cso-pren__card-media">
							<?php if ( $card['img'] ) : ?>
							<img class="cso-pren__card-media-img" src="<?php echo esc_url( $card['img'] ); ?>" width="<?php echo (int) $card['img_w']; ?>" height="<?php echo (int) $card['img_h']; ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" decoding="async">
							<?php endif; ?>
							<?php if ( $date_badge ) : ?>
							<div class="cso-pren__card-date">
								<span class="cso-pren__card-date-num"><?php echo esc_html( $date_badge['num'] ); ?></span>
								<span class="cso-pren__card-date-label"><?php echo esc_html( $date_badge['mese'] . ' · ' . $date_badge['giorno'] ); ?></span>
							</div>
							<?php endif; ?>
							<?php if ( $is_scaduta ) : ?>
							<span class="cso-pren__card-expired-badge"><?php esc_html_e( 'Non prenotabile', 'calypsosub' ); ?></span>
							<?php else : ?>
							<span class="cso-pren__card-selected-badge"><?php esc_html_e( 'Selezionata', 'calypsosub' ); ?></span>
							<?php endif; ?>
							<div class="cso-pren__card-media-title"><?php echo esc_html( $card['title'] ); ?></div>
						</div>
						<div class="cso-pren__card-body">
							<?php if ( ! empty( $card['badge'] ) ) : ?>
							<span class="cso-pren__card-type-badge"><?php echo esc_html( $card['badge'] ); ?></span>
							<?php endif; ?>
							<div class="cso-pren__card-title"><?php echo esc_html( $card['title'] ); ?></div>
							<?php if ( ! empty( $card['sottotitolo'] ) ) : ?>
							<div class="cso-pren__card-meta">📍 <?php echo esc_html( $card['sottotitolo'] ); ?></div>
							<?php endif; ?>
							<div class="cso-pren__card-footer">
								<span class="cso-pren__card-level"><?php echo esc_html( $card['livello'] ?? '' ); ?></span>
								<?php if ( $spots_text ) : ?>
								<span class="cso-pren__card-spots<?php echo $spots_warn ? ' cso-pren__card-spots--warn' : ''; ?>"><?php echo esc_html( $spots_text ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</button>
					<?php endforeach; ?>
				</div>

				<p class="cso-pren__empty" data-empty style="display:none"><?php esc_html_e( 'Nessun risultato con questi filtri.', 'calypsosub' ); ?> <button type="button" class="cso-pren__filter-reset" data-filter-reset><?php esc_html_e( 'Reimposta filtri', 'calypsosub' ); ?></button></p>

				<div class="cso-pren__pagination" data-pagination></div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="cso-pren__data">
		<div class="cso-pren__data-layout">
			<div class="cso-pren__form-card">
				<div class="cso-pren__form-head">
					<<?php echo $form_title_tag; ?> class="cso-pren__form-title"><?php esc_html_e( 'I tuoi dati', 'calypsosub' ); ?></<?php echo $form_title_tag; ?>>
				</div>
				<p class="cso-pren__form-sub"><?php esc_html_e( 'Completa con i tuoi dati per la prenotazione.', 'calypsosub' ); ?></p>

				<?php foreach ( $tipi as $tipo => $form_id ) : ?>
				<?php $form_wrap_mode = $tipo === $preselect_tab ? $preselect_form_mode : ''; ?>
				<div class="cso-pren__form-wrap<?php echo $tipo === $preselect_tab ? '' : ' is-hidden'; ?>" data-form-panel="<?php echo esc_attr( $tipo ); ?>" data-form-mode="<?php echo esc_attr( $form_wrap_mode ); ?>">
					<?php if ( $tipo === $preselect_tab ) : ?>
						<?php if ( $form_wrap_mode === 'login' ) : ?>
							<?php
							$login_url = wp_login_url( get_permalink() );
							echo '<div class="cso-pren__login-wall" style="padding:28px 24px;text-align:center;">'
								. '<p style="font-size:15px;color:#0a2540;margin:0 0 16px;">' . esc_html( $login_message ) . '</p>'
								. '<a href="' . esc_url( $login_url ) . '" style="display:inline-block;padding:12px 28px;background:#3473A5;color:#fff;border-radius:999px;font-weight:700;text-decoration:none;">Accedi</a>'
								. '</div>';
							?>
						<?php else : ?>
							<?php echo $initial_form_html; ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>

			<aside class="cso-pren__sidebar" data-sidebar>
				<?php if ( $preselected_card ) : ?>
					<?php // popolato lato server per il preselect iniziale, poi gestito da renderSidebar() in JS al cambio selezione. ?>
				<?php endif; ?>
				<div class="cso-pren__side-empty" data-sidebar-empty<?php echo ( $preselected_card || $preselect_form_mode === 'fallback' ) ? ' style="display:none"' : ''; ?>>
					<?php esc_html_e( 'Seleziona un elemento per vedere i dettagli.', 'calypsosub' ); ?>
				</div>
			</aside>
		</div>
	</section>

	<div class="cso-pren__sheet" data-sheet>
		<div class="cso-pren__sheet-backdrop" data-sheet-backdrop></div>
		<div class="cso-pren__sheet-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Cosa vuoi prenotare?', 'calypsosub' ); ?>">
			<div class="cso-pren__sheet-handle"></div>
			<div class="cso-pren__sheet-header">
				<h3 class="cso-pren__sheet-title"><?php esc_html_e( 'Cosa vuoi prenotare?', 'calypsosub' ); ?></h3>
				<button type="button" class="cso-pren__sheet-close" data-sheet-close><?php esc_html_e( 'Chiudi', 'calypsosub' ); ?></button>
			</div>
			<input type="search" class="cso-pren__sheet-search" data-sheet-search placeholder="<?php esc_attr_e( 'Cerca per nome o luogo', 'calypsosub' ); ?>">
			<div class="cso-pren__sheet-filters">
				<button type="button" class="cso-pren__sheet-filter is-active" data-sheet-filter=""><?php esc_html_e( 'Tutti', 'calypsosub' ); ?></button>
				<?php foreach ( $tipi as $tipo => $form_id ) : ?>
				<button type="button" class="cso-pren__sheet-filter" data-sheet-filter="<?php echo esc_attr( $tipo ); ?>"><?php echo esc_html( $tipo_labels[ $tipo ] ?? ucfirst( $tipo ) ); ?></button>
				<?php endforeach; ?>
			</div>
			<div class="cso-pren__sheet-list" data-sheet-list></div>
			<p class="cso-pren__sheet-empty" data-sheet-empty style="display:none"><?php esc_html_e( 'Nessun risultato.', 'calypsosub' ); ?></p>
			<a href="#" class="cso-pren__sheet-calendar-link" data-sheet-calendar-link><?php esc_html_e( 'Calendario completo →', 'calypsosub' ); ?></a>
		</div>
	</div>

	<div class="cso-pren__mobile-cta" data-mobile-cta>
		<div class="cso-pren__mobile-cta-info" data-mobile-cta-info></div>
		<button type="button" class="cso-pren__mobile-cta-submit" data-mobile-cta-submit><?php esc_html_e( 'Invia richiesta di prenotazione', 'calypsosub' ); ?></button>
	</div>
</div>
<script>
(function(){
	var root = document.getElementById('<?php echo $uid; ?>');
	if (!root) return;
	var ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
	var ajaxNonce = root.getAttribute('data-nonce');
	var fallbackFormId = parseInt(root.getAttribute('data-fallback-form-id'), 10) || 0;
	var sidebarEmptyText = <?php echo wp_json_encode( __( 'Seleziona un elemento per vedere i dettagli.', 'calypsosub' ) ); ?>;
	var fallbackTitle = <?php echo wp_json_encode( $fallback_title ); ?>;
	var fallbackText  = <?php echo wp_json_encode( $fallback_text ); ?>;
	var userPrefill = (function () {
		try { return JSON.parse(root.getAttribute('data-user-prefill') || '{}'); } catch (e) { return {}; }
	})();

	var sideTitleTag = '<?php echo esc_js( $side_title_tag ); ?>';
	var TIPO_TO_CPT  = { uscite: 'calypso_uscita', eventi: 'calypso_evento', corsi: 'calypso_corso' };
	var TIPO_BADGE   = { uscite: '<?php echo esc_js( __( 'Uscita', 'calypsosub' ) ); ?>', eventi: '<?php echo esc_js( __( 'Evento', 'calypsosub' ) ); ?>', corsi: '<?php echo esc_js( __( 'Corso', 'calypsosub' ) ); ?>' };
	var requireLogin = <?php echo wp_json_encode( array_map( 'boolval', $require_login_map ) ); ?>;
	var isLoggedIn   = <?php echo $user_logged_in ? 'true' : 'false'; ?>;
	var loginWallHtml = <?php echo wp_json_encode(
		'<div class="cso-pren__login-wall" style="padding:28px 24px;text-align:center;">'
		. '<p style="font-size:15px;color:#0a2540;margin:0 0 16px;">' . esc_html( $login_message ) . '</p>'
		. '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" style="display:inline-block;padding:12px 28px;background:#3473A5;color:#fff;border-radius:999px;font-weight:700;text-decoration:none;">Accedi</a>'
		. '</div>'
	); ?>;

	// ── Config/helper per il layout compatto mobile/tablet (riepilogo,
	// "altre date dello stesso itinerario", bottom-sheet "Cosa vuoi
	// prenotare?", barra CTA sticky) ──
	var archiveLinks = <?php echo wp_json_encode( $archive_links ); ?>;
	var MESI_IT_JS   = { '01':'GEN','02':'FEB','03':'MAR','04':'APR','05':'MAG','06':'GIU','07':'LUG','08':'AGO','09':'SET','10':'OTT','11':'NOV','12':'DIC' };
	var STATS_FIELDS = {
		uscite: [
			{ key: 'ritrovo', label: <?php echo wp_json_encode( __( 'Ritrovo', 'calypsosub' ) ); ?> },
			{ key: 'livello', label: <?php echo wp_json_encode( __( 'Livello', 'calypsosub' ) ); ?> },
			{ key: '__posti', label: <?php echo wp_json_encode( __( 'Posti', 'calypsosub' ) ); ?> }
		],
		eventi: [
			{ key: 'luogo', label: <?php echo wp_json_encode( __( 'Luogo', 'calypsosub' ) ); ?> },
			{ key: '__data', label: <?php echo wp_json_encode( __( 'Data', 'calypsosub' ) ); ?> },
			{ key: '__posti', label: <?php echo wp_json_encode( __( 'Posti', 'calypsosub' ) ); ?> }
		],
		corsi: [
			{ key: 'livello', label: <?php echo wp_json_encode( __( 'Livello', 'calypsosub' ) ); ?> },
			{ key: 'durata', label: <?php echo wp_json_encode( __( 'Durata', 'calypsosub' ) ); ?> },
			{ key: 'periodo', label: <?php echo wp_json_encode( __( 'Periodo', 'calypsosub' ) ); ?> }
		]
	};
	var currentSelectedCard = null; // { id, tipo } dell'ultima card selezionata, per il badge "Scelto" nel bottom-sheet

	function dateBadgeJs(dateStr) {
		if (!dateStr) return null;
		var ts = Date.parse(dateStr.replace(' ', 'T'));
		if (isNaN(ts)) return null;
		var d = new Date(ts);
		var mm = ('0' + (d.getMonth() + 1)).slice(-2);
		return { num: String(d.getDate()), mese: MESI_IT_JS[mm] || mm };
	}

	function spotsTextJs(card) {
		if (card.scaduta) return <?php echo wp_json_encode( __( 'Non prenotabile', 'calypsosub' ) ); ?>;
		if (card.posti != null) {
			if (card.max != null) return card.posti + ' / ' + card.max;
			return card.posti > 0 ? card.posti + ' <?php echo esc_js( __( 'posti', 'calypsosub' ) ); ?>' : <?php echo wp_json_encode( __( 'Esaurito', 'calypsosub' ) ); ?>;
		}
		return '';
	}

	/**
	 * Esegue fn isolando eventuali eccezioni (loggate in console, prefisso
	 * "cso-pren mobile") così un bug in una singola funzione del layout
	 * compatto mobile/tablet non blocca il resto dello script (tab, form,
	 * bottom-sheet, barra CTA restano funzionanti anche se una di queste
	 * fallisce).
	 */
	function safeCall(fn, label) {
		try {
			fn();
		} catch (err) {
			if (window.console) {
				if (console.error) console.error('[cso-pren mobile] ' + label, err);
			}
		}
	}

	function escHtml(s) {
		var d = document.createElement('div');
		d.textContent = String(s);
		return d.innerHTML;
	}

	function renderSidebar(card) {
		var sidebar = root.querySelector('[data-sidebar]');
		var badge = card.badge || TIPO_BADGE[card.tipo] || '';
		var html = '<div class="cso-pren__side-img" style="' + (card.img ? 'background-image:url(' + card.img + ')' : '') + '">';
		if (badge) html += '<span class="cso-pren__side-badge">' + escHtml(badge) + '</span>';
		html += '</div><div class="cso-pren__side-body"><' + sideTitleTag + ' class="cso-pren__side-title">' + escHtml(card.title) + '</' + sideTitleTag + '>';
		if (card.luogo) html += '<p class="cso-pren__side-luogo">📍 ' + escHtml(card.luogo) + '</p>';
		html += '<dl>';
		if (card.data) html += '<dt><?php echo esc_js( __( 'Data', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.data) + '</dd>';
		if (card.periodo) html += '<dt><?php echo esc_js( __( 'Periodo', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.periodo) + '</dd>';
		if (card.posti != null) html += '<dt><?php echo esc_js( __( 'Posti', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.posti) + ' <?php echo esc_js( __( 'disponibili', 'calypsosub' ) ); ?></dd>';
		if (card.incluso) html += '<dt><?php echo esc_js( __( 'Cosa è incluso', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.incluso) + '</dd>';
		if (card.portare) html += '<dt><?php echo esc_js( __( 'Cosa portare', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.portare) + '</dd>';
		if (card.cancellazione) html += '<dt><?php echo esc_js( __( 'Cancellazione', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.cancellazione) + '</dd>';
		if (card.durata) html += '<dt><?php echo esc_js( __( 'Durata', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.durata) + '</dd>';
		if (card.requisiti) html += '<dt><?php echo esc_js( __( 'Requisiti', 'calypsosub' ) ); ?></dt><dd>' + escHtml(card.requisiti) + '</dd>';
		html += '</dl></div>';
		sidebar.innerHTML = html;

		currentSelectedCard = { id: card.id, tipo: card.tipo };
		safeCall(function () { renderMobileSummary(card); }, 'renderMobileSummary');
		safeCall(function () { renderMobileDates(card); }, 'renderMobileDates');
		safeCall(function () { updateMobileCta(card); }, 'updateMobileCta');
		safeCall(refreshSheetChosen, 'refreshSheetChosen');
	}

	function clearSidebar() {
		var sidebar = root.querySelector('[data-sidebar]');
		if (sidebar) sidebar.innerHTML = '<div class="cso-pren__side-empty" data-sidebar-empty>' + escHtml(sidebarEmptyText) + '</div>';

		currentSelectedCard = null;
		var summary = root.querySelector('[data-mobile-summary]');
		if (summary) summary.innerHTML = '<div class="cso-pren__mobile-summary-empty">' + escHtml(sidebarEmptyText) + '</div>';
		safeCall(hideMobileDates, 'hideMobileDates');
		safeCall(hideMobileCta, 'hideMobileCta');
		safeCall(refreshSheetChosen, 'refreshSheetChosen');
	}

	function renderFallbackSidebar() {
		var sidebar = root.querySelector('[data-sidebar]');
		if (!sidebar) return;
		sidebar.innerHTML = '<div class="cso-pren__side-body"><' + sideTitleTag + ' class="cso-pren__side-title">' + escHtml(fallbackTitle) + '</' + sideTitleTag + '><p class="cso-pren__side-fallback-text">' + escHtml(fallbackText) + '</p></div>';

		currentSelectedCard = null;
		var summary = root.querySelector('[data-mobile-summary]');
		if (summary) summary.innerHTML = '<div class="cso-pren__mobile-summary-empty"><' + sideTitleTag + ' class="cso-pren__mobile-summary-title">' + escHtml(fallbackTitle) + '</' + sideTitleTag + '><p class="cso-pren__mobile-summary-fallback-text">' + escHtml(fallbackText) + '</p></div>';
		safeCall(hideMobileDates, 'hideMobileDates');
		safeCall(function () { showMobileCta(''); }, 'showMobileCta');
		safeCall(refreshSheetChosen, 'refreshSheetChosen');
	}

	function hideMobileDates() {
		var el = root.querySelector('[data-mobile-dates]');
		if (el) { el.style.display = 'none'; el.innerHTML = ''; }
	}

	function hideMobileCta() {
		var el = root.querySelector('[data-mobile-cta]');
		if (el) el.classList.remove('is-visible');
	}

	function showMobileCta(infoText, isWarn) {
		var el = root.querySelector('[data-mobile-cta]');
		var info = root.querySelector('[data-mobile-cta-info]');
		if (info) {
			info.textContent = infoText || '';
			info.classList.toggle('is-warn', !!isWarn);
		}
		if (el) el.classList.add('is-visible');
	}

	function renderMobileSummary(card) {
		var el = root.querySelector('[data-mobile-summary]');
		if (!el) return;
		var badge = card.badge || TIPO_BADGE[card.tipo] || '';
		var db = dateBadgeJs(card.data);
		var html = '<div class="cso-pren__mobile-summary-media" style="' + (card.img ? 'background-image:url(' + card.img + ')' : '') + '">';
		if (db) html += '<div class="cso-pren__mobile-summary-date"><span class="cso-pren__mobile-summary-date-num">' + escHtml(db.num) + '</span><span class="cso-pren__mobile-summary-date-label">' + escHtml(db.mese) + '</span></div>';
		if (badge) html += '<span class="cso-pren__mobile-summary-badge">' + escHtml(badge) + '</span>';
		html += '<div class="cso-pren__mobile-summary-overlay">';
		html += '<p class="cso-pren__mobile-summary-title">' + escHtml(card.title) + '</p>';
		if (card.luogo) html += '<p class="cso-pren__mobile-summary-luogo">📍 ' + escHtml(card.luogo) + '</p>';
		html += '</div></div><div class="cso-pren__mobile-summary-body">';

		var fields = STATS_FIELDS[card.tipo] || [];
		var stats = fields.map(function (f) {
			var value;
			if (f.key === '__posti') value = spotsTextJs(card);
			else if (f.key === '__data') value = card.data ? card.data.slice(0, 10) : '';
			else value = card[f.key];
			return value ? '<div class="cso-pren__mobile-summary-stat"><dt>' + escHtml(f.label) + '</dt><dd>' + escHtml(value) + '</dd></div>' : '';
		}).filter(Boolean);
		if (stats.length) {
			html += '<dl class="cso-pren__mobile-summary-stats" style="--cso-pren-stat-cols:' + stats.length + '">' + stats.join('') + '</dl>';
		}
		html += '</div>';
		el.innerHTML = html;
	}

	/**
	 * Riga "Altre date dello stesso itinerario": solo per le uscite, solo se
	 * l'uscita selezionata ha altre occorrenze (stesso uscita_id) oltre a sé
	 * stessa.
	 */
	function renderMobileDates(card) {
		var wrap = root.querySelector('[data-mobile-dates]');
		var track = wrap ? wrap.querySelector('.cso-pren__mobile-dates-track') : null;
		if (!wrap) return;
		if (card.tipo !== 'uscite' || !card.uscita_id) { hideMobileDates(); return; }

		var panel = panelForTipo('uscite');
		if (!panel) { hideMobileDates(); return; }
		var siblings = getCardsInPanel(panel)
			.map(function (btn) { return { btn: btn, card: JSON.parse(btn.getAttribute('data-card')) }; })
			.filter(function (x) { return x.card.uscita_id === card.uscita_id; });

		if (siblings.length <= 1) { hideMobileDates(); return; }

		if (!track) {
			wrap.innerHTML = '<div class="cso-pren__mobile-dates-label"><?php echo esc_js( __( 'Altre date dello stesso itinerario', 'calypsosub' ) ); ?></div><div class="cso-pren__mobile-dates-track"></div>';
			track = wrap.querySelector('.cso-pren__mobile-dates-track');
		}
		track.innerHTML = siblings.map(function (x) {
			var db = dateBadgeJs(x.card.data) || { num: '', mese: '' };
			var isActive = x.card.id === card.id;
			var disabled = x.card.scaduta ? !isActive : false;
			return '<button type="button" class="cso-pren__mobile-date-pill' + (isActive ? ' is-active' : '') + '" data-mobile-date-id="' + x.card.id + '"' + (disabled ? ' disabled' : '') + '>'
				+ '<span class="cso-pren__mobile-date-pill-num">' + escHtml(db.num) + '</span>'
				+ '<span class="cso-pren__mobile-date-pill-mese">' + escHtml(db.mese) + '</span>'
				+ '<span class="cso-pren__mobile-date-pill-posti">' + escHtml(spotsTextJs(x.card)) + '</span>'
				+ '</button>';
		}).join('');
		wrap.style.display = '';
	}

	function updateMobileCta(card) {
		var infoText = card.posti != null ? spotsTextJs(card) : '';
		var warn = card.posti != null ? card.posti <= 4 : false;
		showMobileCta(infoText, warn);
	}

	function panelHasBookable(panel) {
		if (!panel) return false;
		return getCardsInPanel(panel).some(function (c) { return c.getAttribute('data-filter-disponibile') === '1'; });
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

	function setHiddenPostId(formPanel, id, tipo) {
		var form = formPanel.querySelector('form.wpcf7-form');
		if (!form) return;
		ensureHiddenField(form, 'booking_post_id', id);
		ensureHiddenField(form, 'booking_post_type', TIPO_TO_CPT[tipo] || tipo);
	}

	/**
	 * Precompila i campi visibili del form CF7 (nome/cognome/email) con i
	 * dati dell'utente loggato, se il campo esiste nel form e non ha già un
	 * valore. Provano più nomi comuni perché i campi sono configurati
	 * liberamente in CF7, non per convenzione fissa.
	 */
	var PREFILL_FIELD_ALIASES = {
		nome:    ['nome', 'name', 'your-name'],
		cognome: ['cognome', 'surname', 'last-name'],
		email:   ['email', 'mail', 'e-mail', 'your-email']
	};
	function prefillUserFields(formPanel) {
		if (!userPrefill || !formPanel) return;
		var form = formPanel.querySelector('form.wpcf7-form');
		if (!form) return;
		Object.keys(PREFILL_FIELD_ALIASES).forEach(function (key) {
			var value = userPrefill[key];
			if (!value) return;
			PREFILL_FIELD_ALIASES[key].some(function (fieldName) {
				var input = form.querySelector('[name="' + fieldName + '"]');
				if (input ? !input.value : false) { input.value = value; return true; }
				return false;
			});
		});
	}

	function getCardsInPanel(panel) {
		return Array.prototype.slice.call(panel.querySelectorAll('.cso-pren__card'));
	}

	function getPanelState(panel) {
		if (!panel.__pren) panel.__pren = { filters: {}, page: 1 };
		return panel.__pren;
	}

	function panelForTipo(tipo) {
		return root.querySelector('.cso-pren__panel[data-tab-panel="' + tipo + '"]');
	}

	function getFiltersEl(panel) {
		var tipo = panel.getAttribute('data-tab-panel');
		return root.querySelector('.cso-pren__filters[data-tab-panel="' + tipo + '"]');
	}

	function cardMatchesFilters(card, filters) {
		for (var key in filters) {
			if (!filters[key]) continue;
			if (key === 'disponibile') {
				if (card.getAttribute('data-filter-disponibile') !== '1') return false;
			} else if (card.getAttribute('data-filter-' + key) !== filters[key]) {
				return false;
			}
		}
		return true;
	}

	function buildPagination(panel, totalPages, currentPage) {
		var el = panel.querySelector('[data-pagination]');
		if (!el) return;
		if (totalPages <= 1) { el.innerHTML = ''; return; }
		var html = '<button type="button" class="cso-pren__page-btn" data-page="' + (currentPage - 1) + '"' + (currentPage <= 1 ? ' disabled' : '') + '>‹</button>';
		var pages = [];
		for (var p = 1; p <= totalPages; p++) {
			if (p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1) {
				pages.push(p);
			} else if (pages[pages.length - 1] !== '…') {
				pages.push('…');
			}
		}
		pages.forEach(function (p) {
			if (p === '…') { html += '<span class="cso-pren__page-ellipsis">…</span>'; return; }
			html += '<button type="button" class="cso-pren__page-btn' + (p === currentPage ? ' is-active' : '') + '" data-page="' + p + '">' + p + '</button>';
		});
		html += '<button type="button" class="cso-pren__page-btn" data-page="' + (currentPage + 1) + '"' + (currentPage >= totalPages ? ' disabled' : '') + '>›</button>';
		el.innerHTML = html;
	}

	function renderPanel(panel, opts) {
		opts = opts || {};
		var state = getPanelState(panel);
		var filtersEl = getFiltersEl(panel);
		var perPage = parseInt(filtersEl ? filtersEl.getAttribute('data-cards-per-page') : '', 10) || 12;
		var cards = getCardsInPanel(panel);
		var filtered = cards.filter(function (c) { return cardMatchesFilters(c, state.filters); });
		var totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
		var page = opts.page || state.page || 1;
		if (page > totalPages) page = totalPages;
		if (page < 1) page = 1;
		state.page = page;

		var start = (page - 1) * perPage;
		cards.forEach(function (c) { c.style.display = 'none'; });
		filtered.slice(start, start + perPage).forEach(function (c) { c.style.display = ''; });

		var emptyEl = panel.querySelector('[data-empty]');
		var cardsEl = panel.querySelector('[data-cards]');
		if (filtered.length === 0) {
			if (emptyEl) emptyEl.style.display = '';
			if (cardsEl) cardsEl.style.display = 'none';
		} else {
			if (emptyEl) emptyEl.style.display = 'none';
			if (cardsEl) cardsEl.style.display = '';
		}

		buildPagination(panel, totalPages, page);

		var hasActiveFilter = Object.keys(state.filters).some(function (k) { return !!state.filters[k]; });
		if (filtersEl) {
			var toolbarReset = filtersEl.querySelector('[data-filter-reset]');
			if (toolbarReset) toolbarReset.style.display = hasActiveFilter ? '' : 'none';
		}
		panel.querySelectorAll('[data-filter-reset]').forEach(function (btn) { btn.style.display = hasActiveFilter ? '' : 'none'; });
	}

	function setFilter(panel, key, value) {
		var state = getPanelState(panel);
		state.filters[key] = value;
		renderPanel(panel, { page: 1 });
	}

	function resetFilters(panel) {
		var state = getPanelState(panel);
		state.filters = {};
		var filtersEl = getFiltersEl(panel);
		if (filtersEl) {
			filtersEl.querySelectorAll('[data-filter-group]').forEach(function (g) {
				g.querySelectorAll('.cso-pren__filter-btn').forEach(function (b) {
					b.classList.toggle('is-active', b.getAttribute('data-value') === '');
				});
			});
		}
		renderPanel(panel, { page: 1 });
	}

	function selectCard(btn) {
		var card = JSON.parse(btn.getAttribute('data-card'));
		root.querySelectorAll('.cso-pren__card.is-selected').forEach(function (el) { el.classList.remove('is-selected'); });
		btn.classList.add('is-selected');
		renderSidebar(card);
		var activeFormPanel = root.querySelector('.cso-pren__form-wrap:not(.is-hidden)');
		if (activeFormPanel) setHiddenPostId(activeFormPanel, card.id, card.tipo);
	}

	function autoSelectFirstVisible(panel) {
		if (!panel) return;
		var selected = panel.querySelector('.cso-pren__card.is-selected');
		if (selected) {
			var card = JSON.parse(selected.getAttribute('data-card'));
			var activeFormPanel = root.querySelector('.cso-pren__form-wrap:not(.is-hidden)');
			if (activeFormPanel) setHiddenPostId(activeFormPanel, card.id, card.tipo);
			return;
		}
		var cards = getCardsInPanel(panel).filter(function (c) { return !(c.style.display === 'none' || c.disabled); });
		if (cards.length) selectCard(cards[0]);
	}

	function loadCf7FormInto(formPanel, formId, mode, callback) {
		formPanel.innerHTML = '';
		var body = new FormData();
		body.append('action', 'calypso_prenotazione_form');
		body.append('cf7_form_id', formId);
		body.append('_ajax_nonce', ajaxNonce);
		fetch(ajaxUrl, { method: 'POST', body: body })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.success) {
					formPanel.innerHTML = res.data.html;
					formPanel.setAttribute('data-form-mode', mode);
					formPanel.classList.remove('is-hidden');
					if (window.wpcf7 ? window.wpcf7.init : false) {
						formPanel.querySelectorAll('.wpcf7-form').forEach(function (f) { window.wpcf7.init(f); });
					}
					prefillUserFields(formPanel);
					if (mode === 'normal') autoSelectFirstVisible(panelForTipo(formPanel.getAttribute('data-form-panel')));
				}
				if (callback) callback();
			});
	}

	/**
	 * Mostra, per la tab indicata, il form giusto: login-wall se richiesto,
	 * il form di fallback se non c'è nulla di prenotabile (e un fallback è
	 * configurato), altrimenti il form di prenotazione normale. `callback`
	 * (opzionale) viene invocato quando il form è pronto nel DOM — anche
	 * dopo il caricamento AJAX — usato dal bottom-sheet mobile per
	 * selezionare una card specifica dopo un cambio tab.
	 */
	function syncFormForPanel(tipo, callback) {
		var panel = panelForTipo(tipo);
		var formPanel = root.querySelector('[data-form-panel="' + tipo + '"]');
		if (!formPanel) { if (callback) callback(); return; }

		if (!isLoggedIn ? requireLogin[tipo] : false) {
			if (formPanel.getAttribute('data-form-mode') !== 'login') {
				formPanel.innerHTML = loginWallHtml;
				formPanel.setAttribute('data-form-mode', 'login');
			}
			formPanel.classList.remove('is-hidden');
			clearSidebar();
			root.querySelectorAll('.cso-pren__card.is-selected').forEach(function (el) { el.classList.remove('is-selected'); });
			if (callback) callback();
			return;
		}

		var wantMode = (!panelHasBookable(panel) ? fallbackFormId : false) ? 'fallback' : 'normal';
		var currentMode = formPanel.getAttribute('data-form-mode');

		if (wantMode === currentMode ? formPanel.innerHTML.trim() !== '' : false) {
			formPanel.classList.remove('is-hidden');
		} else {
			var tabBtn = root.querySelector('.cso-pren__tab[data-tipo="' + tipo + '"]');
			var wantFormId = wantMode === 'fallback' ? fallbackFormId : parseInt(tabBtn ? tabBtn.getAttribute('data-form-id') : 0, 10);
			loadCf7FormInto(formPanel, wantFormId, wantMode, callback);
			callback = null; // già passato a loadCf7FormInto, invocato dopo il fetch
		}

		if (wantMode === 'fallback') {
			renderFallbackSidebar();
			root.querySelectorAll('.cso-pren__card.is-selected').forEach(function (el) { el.classList.remove('is-selected'); });
		} else if (currentMode === wantMode) {
			autoSelectFirstVisible(panel);
		}
		if (callback) callback();
	}

	/**
	 * Attiva la tab `tipo` (desktop + mobile condividono lo stesso stato) e
	 * invoca `callback` quando il form relativo è pronto.
	 */
	function switchTab(tipo, callback) {
		var tabBtn = root.querySelector('.cso-pren__tab[data-tipo="' + tipo + '"]');
		if (!tabBtn) { if (callback) callback(); return; }
		if (tabBtn.classList.contains('is-active')) { if (callback) callback(); return; }

		root.querySelectorAll('.cso-pren__tab').forEach(function (el) { el.classList.remove('is-active'); });
		tabBtn.classList.add('is-active');
		root.querySelectorAll('[data-tab-panel]').forEach(function (el) {
			el.style.display = el.getAttribute('data-tab-panel') === tipo ? '' : 'none';
		});
		root.querySelectorAll('[data-form-panel]').forEach(function (el) { el.classList.add('is-hidden'); });

		syncFormForPanel(tipo, callback);
	}

	/**
	 * Seleziona una card specifica (per id) in una tipologia, cambiando tab
	 * se serve — usato dalle righe del bottom-sheet mobile.
	 */
	function selectCardInTipo(tipo, id) {
		switchTab(tipo, function () {
			var panel = panelForTipo(tipo);
			if (!panel) return;
			var btn = getCardsInPanel(panel).filter(function (c) { return !c.disabled; }).find(function (c) {
				return JSON.parse(c.getAttribute('data-card')).id === id;
			});
			if (btn) selectCard(btn);
		});
	}

	// ── Bottom-sheet "Cosa vuoi prenotare?" (mobile/tablet): lista di tutti
	// gli elementi prenotabili, di qualsiasi tipologia, con ricerca e filtro
	// per tipo — alternativa compatta alla griglia/tabs desktop. ──
	var sheetState = { tipo: '', q: '' };

	function getAllCardsData() {
		return root.querySelectorAll('.cso-pren__panel[data-tab-panel] .cso-pren__card[data-card]');
	}

	function openSheet() {
		var sheet = root.querySelector('[data-sheet]');
		if (!sheet) return;
		sheet.classList.add('is-open');
		document.body.style.overflow = 'hidden';
		safeCall(renderSheetList, 'renderSheetList');
		var search = root.querySelector('[data-sheet-search]');
		if (search) search.focus();
	}

	function closeSheet() {
		var sheet = root.querySelector('[data-sheet]');
		if (!sheet) return;
		sheet.classList.remove('is-open');
		document.body.style.overflow = '';
	}

	function isSheetRowChosen(c) {
		if (!currentSelectedCard) return false;
		if (currentSelectedCard.id !== c.id) return false;
		return currentSelectedCard.tipo === c.tipo;
	}

	function isLowSpotsWarning(c) {
		if (c.scaduta) return false;
		if (c.posti == null) return false;
		return c.posti <= 4;
	}

	function renderSheetList() {
		var listEl = root.querySelector('[data-sheet-list]');
		var emptyEl = root.querySelector('[data-sheet-empty]');
		var calendarLink = root.querySelector('[data-sheet-calendar-link]');
		if (!listEl) return;

		var q = sheetState.q.trim().toLowerCase();
		var rows = Array.prototype.map.call(getAllCardsData(), function (btn) {
			return JSON.parse(btn.getAttribute('data-card'));
		}).filter(function (c) {
			if (sheetState.tipo) {
				if (c.tipo !== sheetState.tipo) return false;
			}
			if (!q) return true;
			var haystack = (c.title + ' ' + (c.luogo || '') + ' ' + (c.sottotitolo || '')).toLowerCase();
			return haystack.indexOf(q) !== -1;
		});

		if (calendarLink) {
			var href = archiveLinks[sheetState.tipo] || archiveLinks.uscite || '#';
			calendarLink.setAttribute('href', href);
			calendarLink.style.display = href === '#' ? 'none' : '';
		}

		if (!rows.length) {
			listEl.innerHTML = '';
			if (emptyEl) emptyEl.style.display = '';
			return;
		}
		if (emptyEl) emptyEl.style.display = 'none';

		listEl.innerHTML = rows.map(function (c) {
			var db = dateBadgeJs(c.data) || { num: '', mese: '' };
			var isChosen = isSheetRowChosen(c);
			var posti = spotsTextJs(c);
			var warn = isLowSpotsWarning(c);
			return '<button type="button" class="cso-pren__sheet-row" data-row-id="' + c.id + '" data-row-tipo="' + escHtml(c.tipo) + '"' + (c.scaduta ? ' disabled' : '') + '>'
				+ '<span class="cso-pren__sheet-row-date"><span class="cso-pren__sheet-row-date-num">' + escHtml(db.num) + '</span><span class="cso-pren__sheet-row-date-label">' + escHtml(db.mese) + '</span></span>'
				+ '<span class="cso-pren__sheet-row-body">'
				+ '<span class="cso-pren__sheet-row-type">' + escHtml(c.badge || TIPO_BADGE[c.tipo] || '') + '</span>'
				+ '<span class="cso-pren__sheet-row-title">' + escHtml(c.title) + (isChosen ? '<span class="cso-pren__sheet-row-chosen"><?php echo esc_js( __( 'Scelto', 'calypsosub' ) ); ?></span>' : '') + '</span>'
				+ (c.sottotitolo ? '<span class="cso-pren__sheet-row-sub">' + escHtml(c.sottotitolo) + '</span>' : '')
				+ '</span>'
				+ (posti ? '<span class="cso-pren__sheet-row-posti' + (warn ? ' is-warn' : '') + '">' + escHtml(posti) + '</span>' : '')
				+ '</button>';
		}).join('');
	}

	/** Aggiorna solo il badge "Scelto" nel bottom-sheet se già aperto, senza ricostruire tutta la lista. */
	function refreshSheetChosen() {
		var sheet = root.querySelector('[data-sheet]');
		if (sheet) {
			if (sheet.classList.contains('is-open')) renderSheetList();
		}
	}

	/**
	 * Invia il form CF7 attivo cliccando il suo submit nativo (così CF7
	 * gestisce validazione/AJAX come farebbe con il proprio pulsante,
	 * anche se è nascosto via CSS sotto i 900px e sostituito dalla barra
	 * sticky "Invia richiesta di prenotazione").
	 */
	function submitActiveForm() {
		var activeFormPanel = root.querySelector('.cso-pren__form-wrap:not(.is-hidden)');
		if (!activeFormPanel) return;
		var submitBtn = activeFormPanel.querySelector('.wpcf7-form input[type="submit"], .wpcf7-form input.wpcf7-submit');
		if (submitBtn) submitBtn.click();
	}

	root.addEventListener('click', function (e) {
		var fBtn = e.target.closest('.cso-pren__filter-btn');
		if (fBtn) {
			var grp = fBtn.closest('[data-filter-group]');
			if (grp) {
				grp.querySelectorAll('.cso-pren__filter-btn').forEach(function (b) { b.classList.toggle('is-active', b === fBtn); });
			}
			var filtersWrap = fBtn.closest('[data-filters]');
			var fTipo = filtersWrap ? filtersWrap.getAttribute('data-tab-panel') : null;
			var fPanel = fTipo ? panelForTipo(fTipo) : null;
			if (fPanel) setFilter(fPanel, fBtn.getAttribute('data-filter'), fBtn.getAttribute('data-value') || '');
			return;
		}

		var pageBtn = e.target.closest('.cso-pren__page-btn');
		if (pageBtn) {
			if (pageBtn.disabled) return;
			renderPanel(pageBtn.closest('[data-tab-panel]'), { page: parseInt(pageBtn.getAttribute('data-page'), 10) });
			return;
		}

		var resetBtn = e.target.closest('[data-filter-reset]');
		if (resetBtn) {
			var resetCtx = resetBtn.closest('[data-tab-panel]');
			var resetTipo = resetCtx ? resetCtx.getAttribute('data-tab-panel') : null;
			var resetPanel = resetTipo ? panelForTipo(resetTipo) : null;
			if (resetPanel) resetFilters(resetPanel);
			return;
		}

		var cardBtn = e.target.closest('.cso-pren__card');
		if (cardBtn) { selectCard(cardBtn); return; }

		var tabBtn = e.target.closest('.cso-pren__tab');
		if (tabBtn) { switchTab(tabBtn.getAttribute('data-tipo')); return; }

		var browseBtn = e.target.closest('[data-mobile-browse]');
		if (browseBtn) { openSheet(); return; }

		var datePill = e.target.closest('.cso-pren__mobile-date-pill');
		if (datePill) {
			if (!datePill.disabled) {
				var dateTipo = currentSelectedCard ? currentSelectedCard.tipo : 'uscite';
				selectCardInTipo(dateTipo, parseInt(datePill.getAttribute('data-mobile-date-id'), 10));
				return;
			}
		}

		var ctaBtn = e.target.closest('[data-mobile-cta-submit]');
		if (ctaBtn) { submitActiveForm(); return; }

		if (e.target.closest('[data-sheet-close]') || e.target.closest('[data-sheet-backdrop]')) { closeSheet(); return; }

		var sheetFilterBtn = e.target.closest('[data-sheet-filter]');
		if (sheetFilterBtn) {
			root.querySelectorAll('[data-sheet-filter]').forEach(function (b) { b.classList.toggle('is-active', b === sheetFilterBtn); });
			sheetState.tipo = sheetFilterBtn.getAttribute('data-sheet-filter') || '';
			renderSheetList();
			return;
		}

		var sheetRow = e.target.closest('.cso-pren__sheet-row');
		if (sheetRow) {
			if (!sheetRow.disabled) {
				selectCardInTipo(sheetRow.getAttribute('data-row-tipo'), parseInt(sheetRow.getAttribute('data-row-id'), 10));
				closeSheet();
				return;
			}
		}
	});

	var sheetSearchInput = root.querySelector('[data-sheet-search]');
	if (sheetSearchInput) {
		sheetSearchInput.addEventListener('input', function () {
			sheetState.q = sheetSearchInput.value || '';
			renderSheetList();
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeSheet();
	});

	root.querySelectorAll('.cso-pren__panel[data-tab-panel]').forEach(function (panel) { renderPanel(panel, { page: 1 }); });

	prefillUserFields(root.querySelector('.cso-pren__form-wrap:not(.is-hidden)'));

	<?php if ( $preselected_card ) : ?>
	renderSidebar(<?php echo wp_json_encode( $preselected_card ); ?>);
	(function () {
		var presBtn = root.querySelector('.cso-pren__card.is-selected');
		if (!presBtn) return;
		var panel = presBtn.closest('[data-tab-panel]');
		if (!panel) return;
		var filtersEl = getFiltersEl(panel);
		var perPage = parseInt(filtersEl ? filtersEl.getAttribute('data-cards-per-page') : '', 10) || 12;
		var idx = getCardsInPanel(panel).indexOf(presBtn);
		if (idx >= 0) renderPanel(panel, { page: Math.floor(idx / perPage) + 1 });
	})();
	<?php endif; ?>

	<?php if ( $preselect_form_mode === 'fallback' ) : ?>
	renderFallbackSidebar();
	<?php else : ?>
	autoSelectFirstVisible(panelForTipo('<?php echo esc_js( $preselect_tab ); ?>'));
	<?php endif; ?>
})();
</script>
