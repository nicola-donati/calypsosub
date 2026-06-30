<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Attributi ── */
$attr_eyebrow      = (string)  ( $attributes['eyebrow']          ?? 'Prossime uscite' );
$attr_title        = (string)  ( $attributes['title']            ?? "Il mare\nci aspetta." );
$attr_show_hlink   = (bool)    ( $attributes['show_header_link'] ?? true );
$attr_hlink_text   = (string)  ( $attributes['header_link_text'] ?? 'Calendario completo' );
$attr_show_past    = (bool)    ( $attributes['show_past']        ?? false );
$attr_max_items    = (int)     ( $attributes['max_items']        ?? 0 );
$attr_show_badge   = (bool)    ( $attributes['show_badge']       ?? true );
$attr_show_ritrovo = (bool)    ( $attributes['show_ritrovo']     ?? true );
$attr_show_posti   = (bool)    ( $attributes['show_posti']       ?? true );
$attr_show_cta     = (bool)    ( $attributes['show_cta']         ?? true );
$attr_btn_prenota  = (string)  ( $attributes['btn_prenota']      ?? 'Prenota' );
$attr_btn_attesa   = (string)  ( $attributes['btn_attesa']       ?? "Lista d'attesa" );
$attr_btn_esaurito = (string)  ( $attributes['btn_esaurito']     ?? 'Esaurito' );
$attr_btn_term     = (string)  ( $attributes['btn_terminata']    ?? 'Terminata' );
$attr_lbl_ritrovo  = (string)  ( $attributes['lbl_ritrovo']      ?? 'RITROVO' );
$attr_lbl_liberi   = (string)  ( $attributes['lbl_liberi']       ?? 'Posti liberi' );
$attr_empty_title  = (string)  ( $attributes['empty_title']      ?? 'Nessuna uscita in programma.' );

/* ── Stile ── */
$bg_color         = (string) ( $attributes['bg_color']         ?? '#dff4f8' );
$max_width        = (int)    ( $attributes['max_width']        ?? 1320 );
$padding_y        = (int)    ( $attributes['padding_y']        ?? 80 );
$padding_x        = (int)    ( $attributes['padding_x']        ?? 48 );
$color_accent     = (string) ( $attributes['color_accent']     ?? '#1B77A7' );
$color_ink        = (string) ( $attributes['color_ink']        ?? '#0b1a26' );
$card_bg          = (string) ( $attributes['card_bg']          ?? '#ffffff' );
$card_radius      = (int)    ( $attributes['card_radius']      ?? 16 );
$card_shadow      = (string) ( $attributes['card_shadow']      ?? '0 4px 32px -8px rgba(10,37,64,.12)' );
$row_padding_y    = (int)    ( $attributes['row_padding_y']    ?? 28 );
$row_padding_x    = (int)    ( $attributes['row_padding_x']    ?? 32 );
$row_border_color = (string) ( $attributes['row_border_color'] ?? 'rgba(11,26,38,.07)' );
$btn_bg           = (string) ( $attributes['btn_bg']           ?? '#061826' );
$btn_color        = (string) ( $attributes['btn_color']        ?? '#ffffff' );
/* ── Testo intestazione ── */
$eyebrow_size      = (int)    ( $attributes['eyebrow_size']      ?? 13 );
$eyebrow_weight    = (int)    ( $attributes['eyebrow_weight']    ?? 600 );
$title_size        = (int)    ( $attributes['title_size']        ?? 76 );
$title_weight      = (int)    ( $attributes['title_weight']      ?? 900 );
$title_line_height = (string) ( $attributes['title_line_height'] ?? '.95' );
$head_link_size    = (int)    ( $attributes['head_link_size']    ?? 14 );
$head_link_weight  = (int)    ( $attributes['head_link_weight']  ?? 600 );
/* ── Testo righe ── */
$dayname_size       = (int) ( $attributes['dayname_size']       ?? 10 );
$daynum_size        = (int) ( $attributes['daynum_size']        ?? 58 );
$daynum_weight      = (int) ( $attributes['daynum_weight']      ?? 900 );
$month_size         = (int) ( $attributes['month_size']         ?? 10 );
$name_size          = (int) ( $attributes['name_size']          ?? 20 );
$name_weight        = (int) ( $attributes['name_weight']        ?? 700 );
$luogo_size         = (int) ( $attributes['luogo_size']         ?? 13 );
$ritrovo_label_size = (int) ( $attributes['ritrovo_label_size'] ?? 9 );
$ritrovo_val_size   = (int) ( $attributes['ritrovo_val_size']   ?? 11 );
$posti_size         = (int) ( $attributes['posti_size']         ?? 13 );
$badge_size         = (int) ( $attributes['badge_size']         ?? 12 );
$badge_weight       = (int) ( $attributes['badge_weight']       ?? 600 );
/* ── Bottone/Vuoto ── */
$btn_size   = (int) ( $attributes['btn_size']   ?? 13 );
$btn_weight = (int) ( $attributes['btn_weight'] ?? 700 );
$empty_size = (int) ( $attributes['empty_size'] ?? 16 );

$css = static function ( string $v ): string {
	return preg_replace( '/[^#a-zA-Z0-9.,()%\s\-\/]/', '', $v );
};
$uid = 'cso-lu-' . sprintf( '%08x', crc32( implode( ',', [ $bg_color, $max_width, $card_radius, $color_accent ] ) ) );

$archive_url = get_post_type_archive_link( 'calypso_uscita' );
$today       = current_time( 'Y-m-d' );

/* ── Query (occorrenze) ── */
$raw    = get_posts( [ 'post_type' => 'calypso_occ_uscita', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
$uscite = [];
foreach ( $raw as $u ) {
	$prima_raw = (string) get_post_meta( $u->ID, '_occorrenza_uscita_data', true );
	$uscita_id = (int) get_post_meta( $u->ID, '_occorrenza_uscita_uscita_id', true );
	if ( ! $prima_raw || ! $uscita_id ) continue;
	$prima = substr( $prima_raw, 0, 10 );
	if ( ! $attr_show_past && $prima < $today ) continue;
	$u->_uscita_id  = $uscita_id;
	$u->_prima_data = $prima;
	$u->_prima_ora  = strlen( $prima_raw ) > 10 ? substr( $prima_raw, 11, 5 ) : '';
	$u->_passata    = $prima < $today;
	$uscite[]       = $u;
}
usort( $uscite, static fn( $a, $b ) => strcmp( $a->_prima_data, $b->_prima_data ) );
if ( $attr_max_items > 0 ) {
	$uscite = array_slice( $uscite, 0, $attr_max_items );
}

/* ── Conteggio prenotazioni (batch) ── */
$booking_counts = [];
if ( ! empty( $uscite ) ) {
	$ids     = array_map( static fn( $u ) => (int) $u->ID, $uscite );
	$safe_in = implode( ',', $ids );
	global $wpdb;
	$rows = $wpdb->get_results(
		"SELECT pm_ev.meta_value AS pid, COUNT(*) AS cnt
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm_ev  ON pm_ev.post_id  = p.ID AND pm_ev.meta_key  = '_booking_post_id'
		 INNER JOIN {$wpdb->postmeta} pm_sta ON pm_sta.post_id = p.ID AND pm_sta.meta_key = '_booking_status' AND pm_sta.meta_value = 'confermata'
		 WHERE p.post_type = 'calypso_prenotazione' AND pm_ev.meta_value IN ($safe_in)
		 GROUP BY pm_ev.meta_value"
	);
	foreach ( $rows as $row ) {
		$booking_counts[ (int) $row->pid ] = (int) $row->cnt;
	}
}

/* ── Meta per ogni occorrenza ── */
foreach ( $uscite as $u ) {
	$max              = get_post_meta( $u->ID, '_occorrenza_uscita_posti', true );
	$u->_posti        = ( $max === '' || $max === false ) ? null : max( 0, (int) $max - ( $booking_counts[ $u->ID ] ?? 0 ) );
	$u->_lista_attesa = get_post_meta( $u->ID, '_occorrenza_uscita_lista_attesa', true ) === '1';
	$livelli          = wp_get_post_terms( $u->_uscita_id, 'calypso_livello', [ 'fields' => 'names' ] );
	$u->_livelli      = is_wp_error( $livelli ) ? [] : $livelli;
}

$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );

/* ── Grid columns dinamica ── */
$gcols = [ '90px', '1fr' ];
if ( $attr_show_badge )   $gcols[] = '160px';
if ( $attr_show_ritrovo ) $gcols[] = '220px';
if ( $attr_show_posti )   $gcols[] = '120px';
if ( $attr_show_cta )     $gcols[] = '140px';
$grid_template = implode( ' ', $gcols );

$mesi_short = [
	'01'=>'GEN','02'=>'FEB','03'=>'MAR','04'=>'APR',
	'05'=>'MAG','06'=>'GIU','07'=>'LUG','08'=>'AGO',
	'09'=>'SET','10'=>'OTT','11'=>'NOV','12'=>'DIC',
];
$giorni_it = ['Sun'=>'DOM','Mon'=>'LUN','Tue'=>'MAR','Wed'=>'MER','Thu'=>'GIO','Fri'=>'VEN','Sat'=>'SAB'];
$py_md  = max( 40, (int) round( $padding_y * 0.8 ) );
$px_md  = max( 20, (int) round( $padding_x * 0.583 ) );
$py_sm  = max( 24, (int) round( $padding_y * 0.6 ) );
$px_sm  = max( 12, (int) round( $padding_x * 0.417 ) );
$title_md  = max( 24, (int) round( $title_size  * 0.737 ) );
$daynum_md = max( 20, (int) round( $daynum_size * 0.793 ) );
$name_md   = max( 13, (int) round( $name_size   * 0.85  ) );
$title_sm  = max( 20, (int) round( $title_size  * 0.5   ) );
$daynum_sm = max( 18, (int) round( $daynum_size * 0.621 ) );
$name_sm   = max( 12, (int) round( $name_size   * 0.75  ) );
$luogo_sm  = max( 10, (int) round( $luogo_size  * 0.923 ) );
$title_xs  = max( 18, (int) round( $title_size  * 0.395 ) );
$daynum_xs = max( 16, (int) round( $daynum_size * 0.517 ) );
?>
<style>
#<?php echo $uid; ?>{--c-deep:<?php echo $css( $color_accent ); ?>;--c-ink:<?php echo $css( $color_ink ); ?>;--c-abyss:<?php echo $css( $btn_bg ); ?>;background:<?php echo $css( $bg_color ); ?>;color:var(--c-ink)}
#<?php echo $uid; ?> a{text-decoration:none;color:inherit}
#<?php echo $uid; ?> .cso-lu__wrap{max-width:<?php echo $max_width; ?>px;margin:0 auto;padding:<?php echo $padding_y; ?>px <?php echo $padding_x; ?>px}
#<?php echo $uid; ?> .cso-lu__head{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:48px;flex-wrap:wrap}
#<?php echo $uid; ?> .cso-lu__eyebrow{display:block;font-weight:<?php echo $eyebrow_weight; ?>;letter-spacing:.16em;text-transform:uppercase;font-size:<?php echo $eyebrow_size; ?>px;color:var(--c-deep);margin-bottom:16px}
#<?php echo $uid; ?> .cso-lu__title{font-size:<?php echo $title_size; ?>px;line-height:<?php echo $css( $title_line_height ); ?>;color:var(--c-deep);margin:0;font-weight:<?php echo $title_weight; ?>}
#<?php echo $uid; ?> .cso-lu__head-link{flex-shrink:0;display:inline-flex;align-items:center;gap:8px;font-size:<?php echo $head_link_size; ?>px;font-weight:<?php echo $head_link_weight; ?>;color:var(--c-deep);white-space:nowrap;align-self:flex-end;padding-bottom:4px}
#<?php echo $uid; ?> .cso-lu__head-link:hover{opacity:.7}
#<?php echo $uid; ?> .cso-lu__card{background:<?php echo $css( $card_bg ); ?>;border-radius:<?php echo $card_radius; ?>px;overflow:hidden;box-shadow:<?php echo $css( $card_shadow ); ?>}
#<?php echo $uid; ?> .cso-lu__row{display:grid;grid-template-columns:<?php echo esc_attr( $grid_template ); ?>;align-items:center;padding:<?php echo $row_padding_y; ?>px <?php echo $row_padding_x; ?>px;column-gap:0;border-bottom:1px solid <?php echo $css( $row_border_color ); ?>}
#<?php echo $uid; ?> .cso-lu__row:last-child{border-bottom:none}
#<?php echo $uid; ?> .cso-lu__date{display:flex;flex-direction:column;line-height:1}
#<?php echo $uid; ?> .cso-lu__dayname{font-family:var(--f-mono,monospace);font-size:<?php echo $dayname_size; ?>px;color:rgba(11,26,38,.45);letter-spacing:.14em;margin-bottom:1px}
#<?php echo $uid; ?> .cso-lu__daynum{font-size:<?php echo $daynum_size; ?>px;font-weight:<?php echo $daynum_weight; ?>;color:var(--c-deep);line-height:1;margin:0}
#<?php echo $uid; ?> .cso-lu__month{font-family:var(--f-mono,monospace);font-size:<?php echo $month_size; ?>px;color:rgba(11,26,38,.45);letter-spacing:.14em;margin-top:2px}
#<?php echo $uid; ?> .cso-lu__name{font-size:<?php echo $name_size; ?>px;font-weight:<?php echo $name_weight; ?>;color:var(--c-deep);text-transform:uppercase;margin:0 0 5px;line-height:1.1}
#<?php echo $uid; ?> .cso-lu__name:hover{opacity:.8}
#<?php echo $uid; ?> .cso-lu__luogo{display:flex;align-items:center;gap:5px;font-size:<?php echo $luogo_size; ?>px;color:rgba(11,26,38,.55);margin:0}
#<?php echo $uid; ?> .cso-lu__badge{display:inline-flex;padding:5px 12px;border-radius:999px;background:rgba(27,119,167,.12);color:var(--c-deep);font-size:<?php echo $badge_size; ?>px;font-weight:<?php echo $badge_weight; ?>;justify-self:start}
#<?php echo $uid; ?> .cso-lu__ritrovo-label{display:block;font-family:var(--f-mono,monospace);font-size:<?php echo $ritrovo_label_size; ?>px;letter-spacing:.12em;text-transform:uppercase;color:rgba(11,26,38,.4);margin-bottom:4px}
#<?php echo $uid; ?> .cso-lu__ritrovo-val{font-family:var(--f-mono,monospace);font-size:<?php echo $ritrovo_val_size; ?>px;letter-spacing:.06em;text-transform:uppercase;color:rgba(11,26,38,.55)}
#<?php echo $uid; ?> .cso-lu__posti{font-family:var(--f-mono,monospace);font-size:<?php echo $posti_size; ?>px;letter-spacing:.06em}
#<?php echo $uid; ?> .cso-lu__posti--warn{color:var(--c-coral,#e9bf26);font-weight:600}
#<?php echo $uid; ?> .cso-lu__posti--ok{color:rgba(11,26,38,.45)}
#<?php echo $uid; ?> .cso-lu__posti--full{color:rgba(11,26,38,.25)}
#<?php echo $uid; ?> .cso-lu__posti--libera{color:rgba(11,26,38,.35)}
#<?php echo $uid; ?> .cso-lu__btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 20px;background:var(--c-abyss);color:<?php echo $css( $btn_color ); ?>;border-radius:999px;font-size:<?php echo $btn_size; ?>px;font-weight:<?php echo $btn_weight; ?>;white-space:nowrap;border:none;cursor:pointer;transition:background .15s;justify-self:end}
#<?php echo $uid; ?> .cso-lu__btn:hover{background:var(--c-deep);color:<?php echo $css( $btn_color ); ?>}
#<?php echo $uid; ?> .cso-lu__btn--disabled{background:rgba(11,26,38,.08);color:rgba(11,26,38,.3);pointer-events:none;cursor:default}
#<?php echo $uid; ?> .cso-lu__empty{padding:48px 32px;text-align:center;font-size:<?php echo $empty_size; ?>px;color:rgba(11,26,38,.5)}
@media(max-width:1024px){
	#<?php echo $uid; ?> .cso-lu__wrap{padding:<?php echo $py_md; ?>px <?php echo $px_md; ?>px}
	#<?php echo $uid; ?> .cso-lu__title{font-size:<?php echo $title_md; ?>px}
	#<?php echo $uid; ?> .cso-lu__row{padding:24px 24px}
	#<?php echo $uid; ?> .cso-lu__daynum{font-size:<?php echo $daynum_md; ?>px}
	#<?php echo $uid; ?> .cso-lu__name{font-size:<?php echo $name_md; ?>px}
}
@media(max-width:760px){
	#<?php echo $uid; ?> .cso-lu__wrap{padding:<?php echo $py_sm; ?>px <?php echo $px_sm; ?>px}
	#<?php echo $uid; ?> .cso-lu__head{margin-bottom:32px}
	#<?php echo $uid; ?> .cso-lu__title{font-size:<?php echo $title_sm; ?>px}
	#<?php echo $uid; ?> .cso-lu__head-link{font-size:13px}
	#<?php echo $uid; ?> .cso-lu__row{display:grid;grid-template-columns:70px 1fr auto;grid-template-rows:auto auto;align-items:center;column-gap:14px;row-gap:0;padding:20px 18px}
	#<?php echo $uid; ?> .cso-lu__date{grid-column:1;grid-row:1/3;align-self:center}
	#<?php echo $uid; ?> .cso-lu__info{grid-column:2;grid-row:1;padding-top:2px}
	#<?php echo $uid; ?> .cso-lu__posti{grid-column:2;grid-row:2;padding-bottom:2px}
	#<?php echo $uid; ?> .cso-lu__cta{grid-column:3;grid-row:1/3;align-self:center}
	#<?php echo $uid; ?> .cso-lu__badge{display:none}
	#<?php echo $uid; ?> .cso-lu__ritrovo{display:none}
	#<?php echo $uid; ?> .cso-lu__daynum{font-size:<?php echo $daynum_sm; ?>px}
	#<?php echo $uid; ?> .cso-lu__name{font-size:<?php echo $name_sm; ?>px}
	#<?php echo $uid; ?> .cso-lu__luogo{font-size:<?php echo $luogo_sm; ?>px}
	#<?php echo $uid; ?> .cso-lu__posti{font-size:12px;margin-top:3px}
	#<?php echo $uid; ?> .cso-lu__btn{padding:9px 14px;font-size:12px}
}
@media(max-width:420px){
	#<?php echo $uid; ?> .cso-lu__title{font-size:<?php echo $title_xs; ?>px}
	#<?php echo $uid; ?> .cso-lu__row{padding:18px 14px;column-gap:10px}
	#<?php echo $uid; ?> .cso-lu__daynum{font-size:<?php echo $daynum_xs; ?>px}
}
</style>

<div class="cso-lu" id="<?php echo $uid; ?>">
<div class="cso-lu__wrap">

	<!-- Header -->
	<div class="cso-lu__head">
		<div>
			<span class="cso-lu__eyebrow"><?php echo esc_html( $attr_eyebrow ); ?></span>
			<h2 class="cso-lu__title display"><?php echo nl2br( esc_html( $attr_title ) ); ?></h2>
		</div>
		<?php if ( $attr_show_hlink && $archive_url ) : ?>
		<a href="<?php echo esc_url( $archive_url ); ?>" class="cso-lu__head-link">
			<?php echo esc_html( $attr_hlink_text ?: 'Calendario completo' ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>
		<?php endif; ?>
	</div>

	<!-- Card lista -->
	<div class="cso-lu__card">

	<?php if ( empty( $uscite ) ) : ?>
		<p class="cso-lu__empty"><?php echo esc_html( $attr_empty_title ); ?></p>
	<?php else : ?>

	<?php foreach ( $uscite as $u ) :
		$ts      = strtotime( $u->_prima_data );
		$mm      = date( 'm', $ts );
		$giorno  = $giorni_it[ date( 'D', $ts ) ] ?? date( 'D', $ts );
		$num     = date( 'j', $ts );
		$mese_ab = $mesi_short[ $mm ] ?? strtoupper( date( 'M', $ts ) );
		$luogo   = (string) get_post_meta( $u->_uscita_id, '_uscita_luogo',   true );
		$ritrovo = (string) get_post_meta( $u->_uscita_id, '_uscita_ritrovo', true );
		if ( $u->_prima_ora && $ritrovo ) $ritrovo = $u->_prima_ora . ' · ' . $ritrovo;
		elseif ( $u->_prima_ora )          $ritrovo = $u->_prima_ora;

		$livello = ! empty( $u->_livelli ) ? $u->_livelli[0] : '';

		/* Stato posti */
		if ( $u->_posti === null ) {
			$posti_html = '<span class="cso-lu__posti cso-lu__posti--libera">' . esc_html( $attr_lbl_liberi ) . '</span>';
		} elseif ( $u->_posti === 0 ) {
			$posti_html = $u->_lista_attesa
				? '<span class="cso-lu__posti cso-lu__posti--warn">' . esc_html( $attr_btn_attesa ) . '</span>'
				: '<span class="cso-lu__posti cso-lu__posti--full">' . esc_html( $attr_btn_esaurito ) . '</span>';
		} elseif ( $u->_posti <= 3 ) {
			$posti_html = '<span class="cso-lu__posti cso-lu__posti--warn">● '
				. esc_html( $u->_posti . ' ' . _n( 'posto', 'posti', $u->_posti, 'calypsosub' ) ) . '</span>';
		} else {
			$posti_html = '<span class="cso-lu__posti cso-lu__posti--ok">'
				. esc_html( $u->_posti . ' ' . __( 'posti', 'calypsosub' ) ) . '</span>';
		}

		/* Stato bottone */
		if ( $u->_passata ) {
			$btn_label = $attr_btn_term; $btn_disabled = true;
		} elseif ( $u->_posti === null || $u->_posti > 0 ) {
			$btn_label = $attr_btn_prenota; $btn_disabled = false;
		} elseif ( $u->_lista_attesa ) {
			$btn_label = $attr_btn_attesa; $btn_disabled = false;
		} else {
			$btn_label = $attr_btn_esaurito; $btn_disabled = true;
		}

		$book_url = $prenotazioni_page_id
			? add_query_arg( 'prenota_id', $u->ID, get_permalink( $prenotazioni_page_id ) )
			: get_permalink( $u->_uscita_id );
	?>
	<div class="cso-lu__row<?php echo $u->_passata ? ' cso-lu__row--passata' : ''; ?>">

		<!-- Data -->
		<div class="cso-lu__date">
			<span class="cso-lu__dayname"><?php echo esc_html( $giorno ); ?></span>
			<span class="cso-lu__daynum display"><?php echo esc_html( str_pad( $num, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="cso-lu__month"><?php echo esc_html( $mese_ab ); ?></span>
		</div>

		<!-- Info -->
		<div class="cso-lu__info">
			<a href="<?php echo esc_url( get_permalink( $u->_uscita_id ) ); ?>" class="cso-lu__name display">
				<?php echo esc_html( get_the_title( $u->_uscita_id ) ); ?>
			</a>
			<?php if ( $luogo ) : ?>
			<p class="cso-lu__luogo">
				<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				<?php echo esc_html( $luogo ); ?>
			</p>
			<?php endif; ?>
		</div>

		<?php if ( $attr_show_badge ) : ?>
		<div class="cso-lu__badge">
			<?php echo esc_html( $livello ?: __( 'Tutti i livelli', 'calypsosub' ) ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $attr_show_ritrovo ) : ?>
		<div class="cso-lu__ritrovo">
			<?php if ( $ritrovo ) : ?>
			<span class="cso-lu__ritrovo-label"><?php echo esc_html( $attr_lbl_ritrovo ); ?></span>
			<span class="cso-lu__ritrovo-val"><?php echo esc_html( $ritrovo ); ?></span>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( $attr_show_posti ) : ?>
		<?php echo $posti_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<?php if ( $attr_show_cta ) : ?>
		<div>
			<?php if ( $btn_disabled ) : ?>
			<span class="cso-lu__btn cso-lu__btn--disabled"><?php echo esc_html( $btn_label ); ?></span>
			<?php else : ?>
			<a href="<?php echo esc_url( $book_url ); ?>" class="cso-lu__btn"><?php echo esc_html( $btn_label ); ?></a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

	</div>
	<?php endforeach; ?>

	<?php endif; ?>

	</div><!-- .cso-lu__card -->

</div><!-- .cso-lu__wrap -->
</div><!-- .cso-lu -->
