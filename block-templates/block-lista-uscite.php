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

$archive_url = get_post_type_archive_link( 'calypso_uscita' );
$today       = current_time( 'Y-m-d' );

/* ── Query ── */
$raw    = get_posts( [ 'post_type' => 'calypso_uscita', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
$uscite = [];
foreach ( $raw as $u ) {
	$date_meta = get_post_meta( $u->ID, '_uscita_date', true );
	$dates     = is_array( $date_meta ) ? array_filter( $date_meta ) : [];
	sort( $dates );
	if ( empty( $dates ) ) continue;
	$prima_raw      = $dates[0];
	$prima          = substr( $prima_raw, 0, 10 );
	if ( ! $attr_show_past && $prima < $today ) continue;
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

/* ── Meta per ogni uscita ── */
foreach ( $uscite as $u ) {
	$max              = get_post_meta( $u->ID, '_uscita_max_partecipanti', true );
	$u->_posti        = ( $max === '' || $max === false ) ? null : max( 0, (int) $max - ( $booking_counts[ $u->ID ] ?? 0 ) );
	$u->_lista_attesa = get_post_meta( $u->ID, '_uscita_lista_attesa', true ) === '1';
	$livelli          = wp_get_post_terms( $u->ID, 'calypso_livello', [ 'fields' => 'names' ] );
	$u->_livelli      = is_wp_error( $livelli ) ? [] : $livelli;
}

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
?>
<style>
.cso-lu{background:#dff4f8;color:var(--c-ink,#0b1a26)}
.cso-lu a{text-decoration:none;color:inherit}

/* ── Wrapper sezione ── */
.cso-lu__wrap{max-width:1320px;margin:0 auto;padding:80px 48px}

/* ── Header ── */
.cso-lu__head{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:48px;flex-wrap:wrap}
.cso-lu__eyebrow{display:block;font-weight:600;letter-spacing:.16em;text-transform:uppercase;font-size:13px;color:var(--c-deep,#1B77A7);margin-bottom:16px}
.cso-lu__title{font-size:76px;line-height:.95;color:var(--c-deep,#1B77A7);margin:0;font-weight:900}
.cso-lu__head-link{flex-shrink:0;display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:var(--c-deep,#1B77A7);white-space:nowrap;align-self:flex-end;padding-bottom:4px}
.cso-lu__head-link:hover{opacity:.7}

/* ── Card lista ── */
.cso-lu__card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 32px -8px rgba(10,37,64,.12)}

/* ── Riga ── */
.cso-lu__row{
	display:grid;
	grid-template-columns:<?php echo esc_attr( $grid_template ); ?>;
	align-items:center;
	padding:28px 32px;
	column-gap:0;
	border-bottom:1px solid rgba(11,26,38,.07);
}
.cso-lu__row:last-child{border-bottom:none}

/* Data */
.cso-lu__date{display:flex;flex-direction:column;line-height:1}
.cso-lu__dayname{font-family:var(--f-mono,monospace);font-size:10px;color:rgba(11,26,38,.45);letter-spacing:.14em;margin-bottom:1px}
.cso-lu__daynum{font-size:58px;font-weight:900;color:var(--c-deep,#1B77A7);line-height:1;margin:0}
.cso-lu__month{font-family:var(--f-mono,monospace);font-size:10px;color:rgba(11,26,38,.45);letter-spacing:.14em;margin-top:2px}

/* Info */
.cso-lu__name{font-size:20px;font-weight:700;color:var(--c-deep,#1B77A7);text-transform:uppercase;margin:0 0 5px;line-height:1.1}
.cso-lu__name:hover{opacity:.8}
.cso-lu__luogo{display:flex;align-items:center;gap:5px;font-size:13px;color:rgba(11,26,38,.55);margin:0}

/* Badge livello */
.cso-lu__badge{display:inline-flex;padding:5px 12px;border-radius:999px;background:rgba(27,119,167,.12);color:var(--c-deep,#1B77A7);font-size:12px;font-weight:600;justify-self:start}

/* Ritrovo */
.cso-lu__ritrovo-label{display:block;font-family:var(--f-mono,monospace);font-size:9px;letter-spacing:.12em;text-transform:uppercase;color:rgba(11,26,38,.4);margin-bottom:4px}
.cso-lu__ritrovo-val{font-family:var(--f-mono,monospace);font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:rgba(11,26,38,.55)}

/* Posti */
.cso-lu__posti{font-family:var(--f-mono,monospace);font-size:13px;letter-spacing:.06em}
.cso-lu__posti--warn{color:var(--c-coral,#e9bf26);font-weight:600}
.cso-lu__posti--ok{color:rgba(11,26,38,.45)}
.cso-lu__posti--full{color:rgba(11,26,38,.25)}
.cso-lu__posti--libera{color:rgba(11,26,38,.35)}

/* CTA */
.cso-lu__btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 20px;background:var(--c-abyss,#061826);color:#fff;border-radius:999px;font-size:13px;font-weight:700;white-space:nowrap;border:none;cursor:pointer;transition:background .15s;justify-self:end}
.cso-lu__btn:hover{background:var(--c-deep,#1B77A7);color:#fff}
.cso-lu__btn--disabled{background:rgba(11,26,38,.08);color:rgba(11,26,38,.3);pointer-events:none;cursor:default}

/* Empty */
.cso-lu__empty{padding:48px 32px;text-align:center;font-size:16px;color:rgba(11,26,38,.5)}

/* ══════════════════════
   RESPONSIVE
   ══════════════════════ */
@media(max-width:1024px){
	.cso-lu__wrap{padding:64px 28px}
	.cso-lu__title{font-size:56px}
	.cso-lu__row{padding:24px 24px}
	.cso-lu__daynum{font-size:46px}
	.cso-lu__name{font-size:17px}
}

@media(max-width:760px){
	.cso-lu__wrap{padding:48px 20px}
	.cso-lu__head{margin-bottom:32px}
	.cso-lu__title{font-size:38px}
	.cso-lu__head-link{font-size:13px}

	/* Su mobile: 3 colonne → data | info+posti | btn */
	.cso-lu__row{
		display:grid;
		grid-template-columns:70px 1fr auto;
		grid-template-rows:auto auto;
		align-items:center;
		column-gap:14px;
		row-gap:0;
		padding:20px 18px;
	}
	.cso-lu__date   {grid-column:1;grid-row:1/3;align-self:center}
	.cso-lu__info   {grid-column:2;grid-row:1;padding-top:2px}
	.cso-lu__posti  {grid-column:2;grid-row:2;padding-bottom:2px}
	.cso-lu__cta    {grid-column:3;grid-row:1/3;align-self:center}
	.cso-lu__badge  {display:none}
	.cso-lu__ritrovo{display:none}

	.cso-lu__daynum{font-size:36px}
	.cso-lu__name{font-size:15px}
	.cso-lu__luogo{font-size:12px}
	.cso-lu__posti{font-size:12px;margin-top:3px}
	.cso-lu__btn{padding:9px 14px;font-size:12px}
}

@media(max-width:420px){
	.cso-lu__title{font-size:30px}
	.cso-lu__row{padding:18px 14px;column-gap:10px}
	.cso-lu__daynum{font-size:30px}
}
</style>

<div class="cso-lu">
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
		$luogo   = (string) get_post_meta( $u->ID, '_uscita_luogo',   true );
		$ritrovo = (string) get_post_meta( $u->ID, '_uscita_ritrovo', true );
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

		$book_url = is_user_logged_in()
			? add_query_arg( 'prenota', '1', get_permalink( $u->ID ) )
			: wp_login_url( get_permalink( $u->ID ) );
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
			<a href="<?php echo esc_url( get_permalink( $u->ID ) ); ?>" class="cso-lu__name display">
				<?php echo esc_html( get_the_title( $u->ID ) ); ?>
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
