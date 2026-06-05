<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Attributi LazyBlocks ── */
$eyebrow      = ! empty( $attributes['eyebrow'] )    ? $attributes['eyebrow']    : '03 — Prossime uscite';
$heading      = ! empty( $attributes['heading'] )    ? $attributes['heading']    : '';
$link_text    = ! empty( $attributes['link_text'] )  ? $attributes['link_text']  : 'Calendario completo';
$link_url     = ! empty( $attributes['link_url'] )   ? $attributes['link_url']   : '';
$max_items    = max( 1, (int) ( $attributes['max_items'] ?? 6 ) );
$show_uscite  = isset( $attributes['show_uscite'] )  ? (bool) $attributes['show_uscite']  : true;
$show_eventi  = isset( $attributes['show_eventi'] )  ? (bool) $attributes['show_eventi']  : true;
$show_corsi   = isset( $attributes['show_corsi'] )   ? (bool) $attributes['show_corsi']   : true;

$today = current_time( 'Y-m-d' );
$bm    = $GLOBALS['calypsosub_booking_manager'] ?? null;
$items = [];

/* ── Uscite ── */
if ( $show_uscite ) {
	foreach ( get_posts( [ 'post_type' => 'calypso_uscita', 'posts_per_page' => -1, 'post_status' => 'publish' ] ) as $u ) {
		$dates = array_values( array_filter( (array) ( get_post_meta( $u->ID, '_uscita_date', true ) ?: [] ) ) );
		sort( $dates );
		$prima = null;
		foreach ( $dates as $d ) {
			if ( substr( $d, 0, 10 ) >= $today ) { $prima = $d; break; }
		}
		if ( ! $prima ) continue;

		$livelli = wp_get_post_terms( $u->ID, 'calypso_livello', [ 'fields' => 'names' ] );
		$badge   = ( ! is_wp_error( $livelli ) && ! empty( $livelli ) ) ? $livelli[0] : __( 'Tutti i livelli', 'calypsosub' );
		$ritrovo = (string) get_post_meta( $u->ID, '_uscita_ritrovo', true );
		$ora     = strlen( $prima ) > 10 ? substr( $prima, 11, 5 ) : '';
		if ( $ora && $ritrovo ) $ritrovo = $ora . ' · ' . $ritrovo;
		elseif ( $ora )          $ritrovo = $ora;

		if ( $bm instanceof Calypsosub_Booking_Manager ) {
			$spots        = $bm->get_remaining_spots( $u->ID );
			$lista_attesa = (bool) get_post_meta( $u->ID, '_uscita_lista_attesa', true );
		} else {
			$spots        = null;
			$lista_attesa = false;
		}

		$items[] = [
			'ts'           => strtotime( $prima ),
			'date_str'     => substr( $prima, 0, 10 ),
			'type'         => 'uscita',
			'title'        => $u->post_title,
			'luogo'        => (string) get_post_meta( $u->ID, '_uscita_luogo', true ),
			'ritrovo'      => $ritrovo,
			'badge'        => $badge,
			'spots'        => $spots,
			'lista_attesa' => $lista_attesa,
			'url'          => get_permalink( $u->ID ),
		];
	}
}

/* ── Eventi ── */
if ( $show_eventi ) {
	foreach ( get_posts( [ 'post_type' => 'calypso_evento', 'posts_per_page' => -1, 'post_status' => 'publish' ] ) as $ev ) {
		$dates = array_values( array_filter( (array) ( get_post_meta( $ev->ID, '_evento_date', true ) ?: [] ) ) );
		sort( $dates );
		$prima = null;
		foreach ( $dates as $d ) {
			if ( substr( $d, 0, 10 ) >= $today ) { $prima = $d; break; }
		}
		if ( ! $prima ) continue;

		if ( $bm instanceof Calypsosub_Booking_Manager ) {
			$spots        = $bm->get_remaining_spots( $ev->ID );
			$lista_attesa = (bool) get_post_meta( $ev->ID, '_evento_lista_attesa', true );
		} else {
			$spots        = null;
			$lista_attesa = false;
		}

		$luogo = (string) get_post_meta( $ev->ID, '_evento_luogo', true );
		$items[] = [
			'ts'           => strtotime( $prima ),
			'date_str'     => substr( $prima, 0, 10 ),
			'type'         => 'evento',
			'title'        => $ev->post_title,
			'luogo'        => $luogo,
			'ritrovo'      => $luogo,
			'badge'        => __( 'Evento', 'calypsosub' ),
			'spots'        => $spots,
			'lista_attesa' => $lista_attesa,
			'url'          => get_permalink( $ev->ID ),
		];
	}
}

/* ── Corsi (occorrenze future) ── */
if ( $show_corsi ) {
	$occs = get_posts( [
		'post_type'      => 'calypso_occorrenza',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_key'       => '_occorrenza_data_inizio',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => [ [ 'key' => '_occorrenza_data_inizio', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' ] ],
	] );
	foreach ( $occs as $occ ) {
		$inizio   = (string) get_post_meta( $occ->ID, '_occorrenza_data_inizio', true );
		if ( ! $inizio ) continue;
		$corso_id = (int) get_post_meta( $occ->ID, '_occorrenza_corso_id', true );
		$corso    = $corso_id ? get_post( $corso_id ) : null;
		$title    = $corso ? $corso->post_title : $occ->post_title;
		$url      = $corso ? get_permalink( $corso_id ) : get_permalink( $occ->ID );
		$luogo    = (string) get_post_meta( $occ->ID, '_occorrenza_luogo', true );
		$posti    = get_post_meta( $occ->ID, '_occorrenza_posti', true );
		$spots    = ( $posti !== '' && $posti !== false ) ? (int) $posti : null;

		$items[] = [
			'ts'           => strtotime( $inizio ),
			'date_str'     => $inizio,
			'type'         => 'corso',
			'title'        => $title,
			'luogo'        => $luogo,
			'ritrovo'      => $luogo,
			'badge'        => __( 'Corso', 'calypsosub' ),
			'spots'        => $spots,
			'lista_attesa' => false,
			'url'          => $url,
		];
	}
}

/* ── Ordina e limita ── */
usort( $items, static fn( $a, $b ) => $a['ts'] - $b['ts'] );
$items = array_slice( $items, 0, $max_items );

/* ── Heading dinamico se vuoto ── */
if ( ! $heading && ! empty( $items ) ) {
	$m1 = date_i18n( 'F', $items[0]['ts'] );
	$m2 = date_i18n( 'F', end( $items )['ts'] );
	$heading = $m1 !== $m2
		? "$m1 e $m2,<br>il mare ci aspetta."
		: "$m1,<br>il mare ci aspetta.";
}

/* ── URL archivio default ── */
if ( ! $link_url ) {
	$link_url = get_post_type_archive_link( 'calypso_uscita' ) ?: '#';
}

/* ── Lookup giorni e mesi ── */
$giorni_it = [ 'Sun' => 'DOM', 'Mon' => 'LUN', 'Tue' => 'MAR', 'Wed' => 'MER', 'Thu' => 'GIO', 'Fri' => 'VEN', 'Sat' => 'SAB' ];
$mesi_it   = [
	'01' => 'GEN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
	'05' => 'MAG', '06' => 'GIU', '07' => 'LUG', '08' => 'AGO',
	'09' => 'SET', '10' => 'OTT', '11' => 'NOV', '12' => 'DIC',
];
?>
<style>
/* ── Calendario eventi — block-calendario ── */
.cso-cal{background:var(--c-foam,#cfe9ee);padding:96px 48px;color:var(--c-ink,#0b1a26)}
.cso-cal *{box-sizing:border-box}
.cso-cal a{text-decoration:none;color:inherit}

.cso-cal__inner{max-width:1320px;margin:0 auto}

.cso-cal__header{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:56px;flex-wrap:wrap;gap:24px}

.cso-cal__eyebrow{font-weight:500;letter-spacing:.16em;text-transform:uppercase;margin:0 0 16px;display:block;font-size:16px;color:var(--c-wave,#1B77A7)}

.cso-cal__heading{font-weight:900;text-transform:uppercase;line-height:.92;letter-spacing:-.01em;margin:0;font-size:76px;color:var(--c-deep,#1B77A7)}

.cso-cal__link{color:var(--c-deep,#1B77A7);display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:600;white-space:nowrap;align-self:flex-end}
.cso-cal__link:hover{color:var(--c-wave,#1B77A7)}

/* ── White card ── */
.cso-cal__card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 30px 80px -40px rgba(10,37,64,.3)}

/* ── Riga evento ── */
.cso-cal-row{
	display:grid;
	grid-template-columns:120px 1fr 180px 220px 140px 160px;
	align-items:center;
	padding:24px 28px;
	border-top:1px solid rgba(11,26,38,.06);
	background:#fff;
}
.cso-cal-row:first-child{border-top:none;background:rgba(233,191,38,.04)}

/* Col 1 — data */
.cso-cal-date{display:flex;flex-direction:column;align-items:flex-start}
.cso-cal-date__dayname{font-weight:500;letter-spacing:.12em;text-transform:uppercase;margin:0;font-size:11px;color:rgba(11,26,38,.5)}
.cso-cal-date__num{font-weight:900;line-height:1;letter-spacing:-.01em;text-transform:uppercase;margin:0;font-size:44px;color:var(--c-deep,#1B77A7)}
.cso-cal-date__month{font-weight:500;letter-spacing:.12em;text-transform:uppercase;margin-top:2px;font-size:11px;color:rgba(11,26,38,.5)}

/* Col 2 — titolo + luogo */
.cso-cal-info__title{font-weight:900;text-transform:uppercase;letter-spacing:-.01em;line-height:.92;display:block;margin-bottom:6px;font-size:26px;color:var(--c-deep,#1B77A7);transition:color .15s}
.cso-cal-info__title:hover{color:var(--c-wave,#1B77A7)}
.cso-cal-info__luogo{display:flex;align-items:center;gap:6px;margin:0;font-size:13px;color:rgba(11,26,38,.65)}
.cso-cal-info__posti{display:none} /* desktop: nascosto, visibile solo mobile */

/* Col 3 — badge tipo/livello */
.cso-cal-badge{display:inline-flex;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;justify-self:start;white-space:nowrap}
.cso-cal-badge--uscita{background:rgba(27,119,167,.1);color:var(--c-wave,#1B77A7)}
.cso-cal-badge--evento{background:rgba(233,191,38,.18);color:#7a5e00}
.cso-cal-badge--corso{background:rgba(38,203,251,.12);color:#006f8a}

/* Col 4 — ritrovo */
.cso-cal-ritrovo__label{letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;display:block;font-size:10px;color:rgba(11,26,38,.45)}
.cso-cal-ritrovo__val{font-size:11px;color:rgba(11,26,38,.6);letter-spacing:.06em;text-transform:uppercase;margin:0}

/* Col 5 — posti */
.cso-cal-posti{font-weight:600;letter-spacing:.08em;font-size:12px}
.cso-cal-posti--ok{color:rgba(11,26,38,.55)}
.cso-cal-posti--warn{color:var(--c-coral-deep,#ff6b4a)}
.cso-cal-posti--full{color:rgba(11,26,38,.3)}
.cso-cal-posti--free{color:rgba(11,26,38,.45)}

/* Col 6 — CTA */
.cso-cal-cta{justify-self:end}
.cso-cal-btn{
	display:inline-flex;align-items:center;
	padding:10px 18px;background:var(--c-deep,#1B77A7);
	border:none;border-radius:999px;cursor:pointer;
	text-decoration:none;white-space:nowrap;transition:background .15s;
	font-weight:600;font-size:12px;color:#fff;
}
.cso-cal-btn:hover{background:var(--c-abyss,#061826);color:#fff}

/* ── Empty state ── */
.cso-cal__empty{text-align:center;padding:56px 0;font-size:16px;color:rgba(11,26,38,.5)}

/* ══════════════════════════
   RESPONSIVE
   ══════════════════════════ */

/* Tablet ≤1100px */
@media(max-width:1100px){
	.cso-cal{padding:72px 28px}
	.cso-cal__heading{font-size:56px}
	.cso-cal-row{
		grid-template-columns:90px 1fr 140px 180px 110px 130px;
		padding:20px;
		column-gap:12px;
	}
	.cso-cal-date__num{font-size:36px}
	.cso-cal-info__title{font-size:20px}
}

/* Mobile ≤760px */
@media(max-width:760px){
	.cso-cal{padding:64px 20px}
	.cso-cal__heading{font-size:48px}
	.cso-cal__header{margin-bottom:32px}
	/* Riga → flex */
	.cso-cal-row{display:flex;align-items:center;gap:16px;padding:18px}
	/* Data: compatta */
	.cso-cal-date{flex:0 0 auto;min-width:50px;text-align:center;align-items:center}
	.cso-cal-date__dayname{display:none}
	.cso-cal-date__num{font-size:28px}
	.cso-cal-date__month{font-size:9px}
	/* Info */
	.cso-cal-info{flex:1 1 auto;min-width:0}
	.cso-cal-info__title{font-size:14px;font-weight:700;text-transform:none;letter-spacing:0;line-height:1.2;margin-bottom:2px}
	.cso-cal-info__luogo{font-size:12px}
	/* Posti mobile: appare nell'info */
	.cso-cal-info__posti{display:block;margin-top:4px;font-size:11px;letter-spacing:.06em}
	.cso-cal-info__posti--warn{color:var(--c-coral-deep,#ff6b4a)}
	.cso-cal-info__posti--ok{color:rgba(11,26,38,.5)}
	.cso-cal-info__posti--free{color:rgba(11,26,38,.4)}
	/* Nasconde badge, ritrovo, posti-colonna */
	.cso-cal-badge,.cso-cal-ritrovo,.cso-cal-posti{display:none}
	/* Bottone */
	.cso-cal-btn{padding:8px 14px;font-size:12px}
}

/* Mobile stretto ≤420px */
@media(max-width:420px){
	.cso-cal__heading{font-size:38px}
}
</style>

<section class="cso-cal">
<div class="cso-cal__inner">

	<div class="cso-cal__header">
		<div>
			<?php if ( $eyebrow ) : ?>
			<span class="cso-cal__eyebrow eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
			<h2 class="cso-cal__heading display">
				<?php echo wp_kses( $heading, [ 'em' => [], 'br' => [], 'strong' => [] ] ); ?>
			</h2>
			<?php endif; ?>
		</div>
		<?php if ( $link_url && $link_text ) : ?>
		<a href="<?php echo esc_url( $link_url ); ?>" class="cso-cal__link">
			<?php echo esc_html( $link_text ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>
		<?php endif; ?>
	</div>

	<?php if ( empty( $items ) ) : ?>
	<div class="cso-cal__empty">
		<?php _e( 'Nessun evento in programma.', 'calypsosub' ); ?>
	</div>
	<?php else : ?>
	<div class="cso-cal__card">
	<?php foreach ( $items as $item ) :
		$ts       = $item['ts'];
		$giorno   = $giorni_it[ date( 'D', $ts ) ] ?? date( 'D', $ts );
		$num      = date( 'j', $ts );
		$mese     = $mesi_it[ date( 'm', $ts ) ] ?? strtoupper( date_i18n( 'M', $ts ) );
		$type     = $item['type'];
		$spots    = $item['spots'];
		$lista    = $item['lista_attesa'];

		/* Posti html — desktop colonna */
		if ( $spots === null ) {
			$posti_cls  = 'cso-cal-posti--free';
			$posti_html = esc_html__( 'Libera partecipazione', 'calypsosub' );
			$posti_mob_cls = 'cso-cal-info__posti--free';
			$posti_mob     = $posti_html;
		} elseif ( $spots === 0 ) {
			if ( $lista ) {
				$posti_cls  = 'cso-cal-posti--warn';
				$posti_html = esc_html__( "Lista d'attesa", 'calypsosub' );
				$posti_mob_cls = 'cso-cal-info__posti--warn';
				$posti_mob     = $posti_html;
			} else {
				$posti_cls  = 'cso-cal-posti--full';
				$posti_html = esc_html__( 'Esaurito', 'calypsosub' );
				$posti_mob_cls = 'cso-cal-info__posti--ok';
				$posti_mob     = $posti_html;
			}
		} elseif ( $spots <= 4 ) {
			$posti_cls  = 'cso-cal-posti--warn';
			$posti_html = '● ' . esc_html( $spots . ' ' . _n( 'posto', 'posti', $spots, 'calypsosub' ) );
			$posti_mob_cls = 'cso-cal-info__posti--warn';
			$posti_mob     = $posti_html;
		} else {
			$posti_cls  = 'cso-cal-posti--ok';
			$posti_html = esc_html( $spots . ' ' . __( 'posti', 'calypsosub' ) );
			$posti_mob_cls = 'cso-cal-info__posti--ok';
			$posti_mob     = $posti_html;
		}

		/* Bottone label */
		if ( $spots === 0 && ! $lista ) {
			$btn_label    = __( 'Esaurito', 'calypsosub' );
			$btn_disabled = true;
		} elseif ( $spots === 0 && $lista ) {
			$btn_label    = __( "Lista d'attesa", 'calypsosub' );
			$btn_disabled = false;
		} else {
			$btn_label    = $type === 'corso'
				? __( 'Scopri', 'calypsosub' )
				: __( 'Prenota', 'calypsosub' );
			$btn_disabled = false;
		}
	?>
	<div class="cso-cal-row">

		<div class="cso-cal-date">
			<span class="cso-cal-date__dayname eyebrow"><?php echo esc_html( $giorno ); ?></span>
			<p class="cso-cal-date__num display"><?php echo esc_html( str_pad( $num, 2, '0', STR_PAD_LEFT ) ); ?></p>
			<span class="cso-cal-date__month eyebrow"><?php echo esc_html( $mese ); ?></span>
		</div>

		<div class="cso-cal-info">
			<a href="<?php echo esc_url( $item['url'] ); ?>" class="cso-cal-info__title display">
				<?php echo esc_html( $item['title'] ); ?>
			</a>
			<?php if ( $item['luogo'] ) : ?>
			<p class="cso-cal-info__luogo">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				<?php echo esc_html( $item['luogo'] ); ?>
			</p>
			<?php endif; ?>
			<!-- Mobile: posti inline nell'info -->
			<div class="cso-cal-info__posti <?php echo esc_attr( $posti_mob_cls ); ?>">
				<?php echo $posti_mob; ?>
			</div>
		</div>

		<div class="cso-cal-badge cso-cal-badge--<?php echo esc_attr( $type ); ?>">
			<?php echo esc_html( $item['badge'] ); ?>
		</div>

		<div class="cso-cal-ritrovo">
			<?php if ( $item['ritrovo'] ) : ?>
			<span class="cso-cal-ritrovo__label"><?php echo esc_html( calypsosub_opt( 'uscite', 'label_ritrovo', __( 'RITROVO', 'calypsosub' ) ) ); ?></span>
			<p class="cso-cal-ritrovo__val"><?php echo esc_html( $item['ritrovo'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="cso-cal-posti <?php echo esc_attr( $posti_cls ); ?>">
			<?php echo $posti_html; ?>
		</div>

		<div class="cso-cal-cta">
			<?php if ( $btn_disabled ) : ?>
			<span class="cso-cal-btn" style="background:rgba(11,26,38,.08);color:rgba(11,26,38,.35);pointer-events:none">
				<?php echo esc_html( $btn_label ); ?>
			</span>
			<?php else : ?>
			<a href="<?php echo esc_url( $item['url'] ); ?>" class="cso-cal-btn">
				<?php echo esc_html( $btn_label ); ?>
			</a>
			<?php endif; ?>
		</div>

	</div>
	<?php endforeach; ?>
	</div><!-- .cso-cal__card -->
	<?php endif; ?>

</div>
</section>
