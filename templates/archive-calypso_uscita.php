<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'block_template_part' ) ) {
	block_template_part( 'header' );
}

/* ── Filtri da GET ── */
$filter_livello  = array_map( 'sanitize_key',        (array) ( $_GET['livello']  ?? [] ) );
$filter_localita = array_map( 'sanitize_text_field',  (array) ( $_GET['localita'] ?? [] ) );
$filter_avail    = array_map( 'sanitize_key',        (array) ( $_GET['avail']     ?? [] ) );
$filter_livello  = array_filter( $filter_livello );
$filter_localita = array_filter( $filter_localita );
$filter_avail    = array_filter( $filter_avail );

/* ── Tutte le uscite pubblicate ── */
$raw   = get_posts( [ 'post_type' => 'calypso_uscita', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
$today = current_time( 'Y-m-d' );

$uscite = [];
foreach ( $raw as $u ) {
	$date_meta = get_post_meta( $u->ID, '_uscita_date', true );
	$dates     = is_array( $date_meta ) ? array_filter( $date_meta ) : [];
	sort( $dates ); // ISO strings — ordinamento lessicografico = cronologico
	if ( empty( $dates ) ) continue;
	$prima_raw  = $dates[0];                                        // "2026-06-15T08:30" o "2026-06-15"
	$prima      = substr( $prima_raw, 0, 10 );                     // "2026-06-15"
	if ( $prima < $today ) continue;
	$u->_prima_data = $prima;
	$u->_prima_ora  = strlen( $prima_raw ) > 10 ? substr( $prima_raw, 11, 5 ) : '';
	$u->_dates      = $dates;
	$uscite[]       = $u;
}
usort( $uscite, static fn( $a, $b ) => strcmp( $a->_prima_data, $b->_prima_data ) );

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

/* ── Posti, lista attesa per ogni uscita ── */
foreach ( $uscite as $u ) {
	$max = get_post_meta( $u->ID, '_uscita_max_partecipanti', true );
	if ( $max === '' || $max === false ) {
		$u->_posti = null;
	} else {
		$u->_posti = max( 0, (int) $max - ( $booking_counts[ $u->ID ] ?? 0 ) );
	}
	$u->_lista_attesa = get_post_meta( $u->ID, '_uscita_lista_attesa', true ) === '1';
	$u->_livelli      = wp_get_post_terms( $u->ID, 'calypso_livello', [ 'fields' => 'names' ] );
	$u->_livelli      = is_wp_error( $u->_livelli ) ? [] : $u->_livelli;
}

/* ── Filtro livello ── */
if ( ! empty( $filter_livello ) ) {
	$uscite = array_values( array_filter( $uscite, static function ( $u ) use ( $filter_livello ) {
		$slugs = wp_get_post_terms( $u->ID, 'calypso_livello', [ 'fields' => 'slugs' ] );
		return ! is_wp_error( $slugs ) && ! empty( array_intersect( $filter_livello, $slugs ) );
	} ) );
}

/* ── Filtro località ── */
if ( ! empty( $filter_localita ) ) {
	$uscite = array_values( array_filter( $uscite, static function ( $u ) use ( $filter_localita ) {
		return in_array( (string) get_post_meta( $u->ID, '_uscita_luogo', true ), $filter_localita, true );
	} ) );
}

/* ── Filtro disponibilità ── */
if ( in_array( 'liberi', $filter_avail, true ) ) {
	$uscite = array_values( array_filter( $uscite, static fn( $u ) => $u->_posti === null || $u->_posti > 0 ) );
}
if ( in_array( 'attesa', $filter_avail, true ) ) {
	$uscite = array_values( array_filter( $uscite, static fn( $u ) => $u->_posti === 0 && $u->_lista_attesa ) );
}

/* ── Opzioni per i filtri sidebar ── */
$livelli_terms = get_terms( [ 'taxonomy' => 'calypso_livello', 'hide_empty' => false ] );
$livelli_terms = is_wp_error( $livelli_terms ) ? [] : $livelli_terms;

$all_pids     = get_posts( [ 'post_type' => 'calypso_uscita', 'posts_per_page' => -1, 'fields' => 'ids' ] );
$all_localita = [];
foreach ( $all_pids as $pid ) {
	$l = trim( (string) get_post_meta( $pid, '_uscita_luogo', true ) );
	if ( $l && ! in_array( $l, $all_localita, true ) ) $all_localita[] = $l;
}
sort( $all_localita );

/* ── Raggruppa per mese ── */
$per_mese = [];
foreach ( $uscite as $u ) {
	$per_mese[ date( 'Y-m', strtotime( $u->_prima_data ) ) ][] = $u;
}

$mesi_it  = [
	'01' => 'Gennaio',   '02' => 'Febbraio', '03' => 'Marzo',
	'04' => 'Aprile',    '05' => 'Maggio',   '06' => 'Giugno',
	'07' => 'Luglio',    '08' => 'Agosto',   '09' => 'Settembre',
	'10' => 'Ottobre',   '11' => 'Novembre', '12' => 'Dicembre',
];
$giorni_it = [ 'Sun' => 'DOM', 'Mon' => 'LUN', 'Tue' => 'MAR', 'Wed' => 'MER', 'Thu' => 'GIO', 'Fri' => 'VEN', 'Sat' => 'SAB' ];

$archive_url = get_post_type_archive_link( 'calypso_uscita' );
$first_row   = true;
?>
<style>
.cso-archive{color:var(--c-ink,#0b1a26)}
.cso-archive h1,.cso-archive h2,.cso-archive h3,.cso-archive h4{color:var(--c-wave,#1B77A7);text-transform:uppercase}
.cso-archive a{color:inherit;text-decoration:none}
.cso-archive p,.cso-archive li,.cso-archive span,.cso-archive div{color:inherit}

/* ── Hero ── */
.cso-hero{
	background:var(--c-deep,#1B77A7);
	color:#fff;
	padding:calc(90px + 32px) 48px 80px;
	position:relative;
	min-height:clamp(600px,100dvh,1020px);
}
.cso-hero h1,.cso-hero h2,.cso-hero h3{color:#fff}
.cso-hero__inner{max-width:1320px;margin:0 auto}
.cso-hero__eyebrow{font-weight:600;letter-spacing:.16em;text-transform:uppercase;margin:0 0 20px;display:block}
.cso-archive .cso-hero__eyebrow{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-hero__title{font-size:124px;font-weight:900;color:#fff;margin:0;line-height:.92;letter-spacing:-.02em;text-transform:uppercase}
.cso-hero__title em{font-style:italic;color:var(--c-aqua,#26CBFB)}
.cso-hero__lead{line-height:1.65;opacity:.85;margin:32px 0 0;max-width:700px}
.cso-archive .cso-hero__lead{font-size:18px;color:#fff}

/* ── Layout corpo ── */
.cso-archive-body{background:var(--c-bone,#f6f1e6);padding:80px 48px 96px}
.cso-archive-inner{max-width:1320px;margin:0 auto;display:grid;grid-template-columns:280px 1fr;gap:60px;align-items:start}

/* ── Sidebar filtri ── */
.cso-filtri{position:sticky;top:24px;align-self:flex-start}
.cso-filtri__eyebrow{font-weight:600;letter-spacing:.16em;text-transform:uppercase;margin:0 0 20px;display:block}
.cso-archive .cso-filtri__eyebrow{font-size:16px;color:var(--c-wave,#1B77A7)}
.cso-filtri__groups{display:flex;flex-direction:column;gap:24px}
.cso-filtri__group-label{font-size:12px;font-weight:700;color:var(--c-deep,#1B77A7);margin:0 0 10px;letter-spacing:.04em;text-transform:uppercase}
.cso-filtri__item{display:flex;align-items:center;gap:10px;padding:6px 0;cursor:pointer}
.cso-archive .cso-filtri__item{font-size:13px;color:rgba(11,26,38,.75)}
.cso-filtri__item input[type="checkbox"]{accent-color:var(--c-wave,#1B77A7);width:14px;height:14px;flex-shrink:0;cursor:pointer}
.cso-filtri__actions{margin-top:24px;display:flex;gap:10px;flex-wrap:wrap}
.cso-filtri__btn-apply{display:inline-flex;align-items:center;padding:10px 20px;background:var(--c-deep,#1B77A7);border:none;border-radius:999px;cursor:pointer;font-weight:600;letter-spacing:.02em}
.cso-archive .cso-filtri__btn-apply{font-size:13px;color:#fff}
.cso-filtri__btn-apply:hover{background:var(--c-abyss,#061826)}
.cso-filtri__btn-reset{display:inline-flex;align-items:center;padding:9px 16px;background:transparent;border:1.5px solid rgba(11,26,38,.2);border-radius:999px;cursor:pointer;text-decoration:none;font-weight:500}
.cso-archive .cso-filtri__btn-reset{font-size:13px;color:rgba(11,26,38,.6)}
.cso-filtri__btn-reset:hover{border-color:rgba(11,26,38,.45)}

/* ── Contenuto mesi ── */
.cso-mese{margin-bottom:56px}
.cso-mese:last-child{margin-bottom:0}
.cso-mese__heading{margin:0 0 24px;display:flex;align-items:baseline;gap:16px}
.cso-archive .cso-mese__heading{font-size:56px;color:var(--c-deep,#1B77A7)}
.cso-mese__count{font-weight:400;letter-spacing:.1em;text-transform:uppercase}
.cso-archive .cso-mese__count{font-size:18px;color:rgba(11,26,38,.45)}

/* Card uscite del mese */
.cso-uscite-list{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 12px 40px -20px rgba(10,37,64,.2)}

/* Singola riga uscita */
.cso-uscita-row{
	display:grid;
	grid-template-columns:90px 1fr 110px 170px 110px 120px;
	align-items:center;
	padding:20px 24px;
	border-top:1px solid rgba(11,26,38,.06);
	background:#fff;
}
.cso-uscita-row:first-child{border-top:none}
.cso-uscita-row--highlight{background:#fff}

/* Colonna data */
.cso-uscita-row__date{line-height:1}
.cso-uscita-row__dayname{font-weight:600;letter-spacing:.18em;text-transform:uppercase;margin:0 0 4px;display:block}
.cso-archive .cso-uscita-row__dayname{font-size:10px;color:rgba(11,26,38,.5)}
.cso-uscita-row__daynum{font-weight:900;line-height:1;margin:0}
.cso-archive .cso-uscita-row__daynum{font-size:36px;color:var(--c-deep,#1B77A7)}

/* Colonna info */
.cso-uscita-row__info a{text-decoration:none;color:inherit;display:block}
.cso-uscita-row__title{font-weight:700;margin:0 0 4px;line-height:1.2}
.cso-archive .cso-uscita-row__title{font-size:26px;color:var(--c-deep,#1B77A7)}
.cso-uscita-row__luogo{display:flex;align-items:center;gap:5px;margin:0}
.cso-archive .cso-uscita-row__luogo{font-size:12px;color:rgba(11,26,38,.6)}

/* Badge livello */
.cso-uscita-row__badge{display:inline-flex;padding:3px 8px;border-radius:999px;font-weight:600;justify-self:start}
.cso-archive .cso-uscita-row__badge{font-size:10px;background:rgba(27,119,167,.1);color:var(--c-wave,#1B77A7)}

/* Ritrovo */
.cso-uscita-row__ritrovo-label{letter-spacing:.1em;text-transform:uppercase;margin:0 0 3px;display:block}
.cso-archive .cso-uscita-row__ritrovo-label{font-size:9px;color:rgba(11,26,38,.5)}
.cso-uscita-row__ritrovo-val{font-weight:500;margin:0}
.cso-archive .cso-uscita-row__ritrovo-val{font-size:11px;color:rgba(11,26,38,.6)}

/* Posti */
.cso-uscita-row__posti{font-weight:600;letter-spacing:.06em;margin:0}
.cso-archive .cso-uscita-row__posti{font-size:11px}
.cso-uscita-row__posti--ok{color:rgba(11,26,38,.55)}
.cso-uscita-row__posti--warn{color:var(--c-coral-deep,#ff6b4a)}
.cso-uscita-row__posti--full{color:rgba(11,26,38,.3)}
.cso-uscita-row__posti--libera{color:rgba(11,26,38,.45)}

/* CTA */
.cso-uscita-row__cta{justify-self:end}
.cso-btn-dark{
	display:inline-flex;align-items:center;
	padding:8px 14px;
	background:var(--c-deep,#1B77A7);
	border:none;border-radius:999px;
	cursor:pointer;text-decoration:none;
	white-space:nowrap;
	transition:background .15s;
}
.cso-archive .cso-btn-dark{font-size:12px;font-weight:600;color:#fff}
.cso-btn-dark:hover{background:var(--c-abyss,#061826)}
.cso-btn-dark--disabled{background:rgba(11,26,38,.08);pointer-events:none;cursor:default}
.cso-archive .cso-btn-dark--disabled{color:rgba(11,26,38,.3)}

/* Stato vuoto */
.cso-empty{padding:64px 0;text-align:center}
.cso-archive .cso-empty__title{font-size:24px;color:var(--c-deep,#1B77A7)}
.cso-archive .cso-empty__sub{font-size:16px;color:rgba(11,26,38,.55)}

/* ══════════════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════════════ */

/* ── Tablet ≤1024px ── */
@media(max-width:1024px){
	.cso-hero{padding:calc(90px + 24px) 28px 64px}
	.cso-archive .cso-hero__title{font-size:76px}
	.cso-archive .cso-hero__lead{font-size:16px}
	.cso-archive-body{padding:56px 28px 72px}
	/* Sidebar va sopra il contenuto */
	.cso-archive-inner{grid-template-columns:1fr;gap:32px}
	.cso-filtri{
		position:static;
		background:rgba(10,37,64,.04);
		border-radius:14px;
		padding:24px;
	}
	/* Filtri in griglia orizzontale */
	.cso-filtri__groups{display:grid;grid-template-columns:repeat(3,1fr);gap:24px 32px}
	.cso-mese{margin-bottom:40px}
	.cso-archive .cso-mese__heading{font-size:46px}
	/* Righe uscite più strette */
	.cso-uscita-row{
		grid-template-columns:76px 1fr 96px 150px 96px 110px;
		padding:18px 20px;
		column-gap:12px;
	}
}

/* ── Mobile ≤760px ── */
@media(max-width:760px){
	.cso-hero{padding:calc(90px + 20px) 20px 48px}
	.cso-archive .cso-hero__title{font-size:52px}
	.cso-archive .cso-hero__lead{font-size:15px;margin-top:24px}
	.cso-archive-body{padding:40px 20px 64px}
	/* Filtri in 2 colonne */
	.cso-filtri__groups{grid-template-columns:1fr 1fr;gap:22px 28px}
	.cso-mese{margin-bottom:32px}
	.cso-archive .cso-mese__heading{font-size:38px;gap:12px}
	/* Righe → flex wrap con ordinamento specifico */
	.cso-uscite-list{box-shadow:0 10px 30px -18px rgba(10,37,64,.3)}
	.cso-uscita-row{
		display:flex;flex-wrap:wrap;align-items:center;
		gap:8px 14px;padding:18px;
	}
	.cso-uscita-row>*{min-width:0}
	.cso-uscita-row__date   {order:1}
	.cso-uscita-row__info   {order:2;flex:1 1 auto}
	.cso-uscita-row__posti  {order:3;margin-left:auto;text-align:right;white-space:nowrap}
	.cso-uscita-row__badge  {order:4}
	.cso-uscita-row__ritrovo{order:5;flex:1 1 auto}
	/* CTA: width:100% su block (NON flex-basis) → rispetta il padding del container */
	.cso-uscita-row__cta    {order:6;flex:none;width:100%}
	.cso-archive .cso-uscita-row__daynum{font-size:30px}
	/* Bottone a piena larghezza — display:flex per sizing corretto su block */
	.cso-btn-dark{display:flex;width:100%;justify-content:center;padding:12px 18px;margin-top:4px;box-sizing:border-box}
	.cso-archive .cso-btn-dark{font-size:13px}
}

/* ── Mobile stretto ≤420px ── */
@media(max-width:420px){
	.cso-archive .cso-hero__title{font-size:42px}
	.cso-filtri__groups{grid-template-columns:1fr}
	.cso-archive .cso-mese__heading{font-size:32px}
}
</style>

<div class="cso-archive">

<!-- ── HERO ── -->
<section class="cso-hero">
<div class="cso-hero__inner">
	<span class="cso-hero__eyebrow"><?php echo esc_html( str_replace( '{anno}', date( 'Y' ),
		calypsosub_opt( 'uscite', 'archive_eyebrow', __( 'Calendario · stagione {anno}', 'calypsosub' ) )
	) ); ?></span>
	<h1 class="cso-hero__title">
		<?php echo wp_kses( calypsosub_opt( 'uscite', 'archive_h1',
			__( 'Ogni sabato,<br>da aprile<br>a <em>ottobre.</em>', 'calypsosub' )
		), [ 'em' => [], 'br' => [], 'strong' => [] ] ); ?>
	</h1>
	<p class="cso-hero__lead">
		<?php echo esc_html( calypsosub_opt( 'uscite', 'archive_lead',
			__( 'Le uscite del club partono dal porto di Cala Galera o di Porto Santo Stefano. Due immersioni a giornata, pranzo a bordo, rientro alle 17. Posti limitati, prenotazione obbligatoria 48 ore prima.', 'calypsosub' )
		) ); ?>
	</p>
</div>
</section>

<!-- ── CORPO ── -->
<section class="cso-archive-body">
<div class="cso-archive-inner">

	<!-- Sidebar filtri -->
	<aside class="cso-filtri">
	<form method="get" action="<?php echo esc_url( $archive_url ); ?>">

		<span class="cso-filtri__eyebrow"><?php echo esc_html( calypsosub_opt( 'uscite', 'filtri_label', __( 'Filtra', 'calypsosub' ) ) ); ?></span>

		<div class="cso-filtri__groups">

			<?php if ( ! empty( $livelli_terms ) ) : ?>
			<div>
				<div class="cso-filtri__group-label"><?php echo esc_html( calypsosub_opt( 'uscite', 'filtri_livello', __( 'LIVELLO', 'calypsosub' ) ) ); ?></div>
				<?php foreach ( $livelli_terms as $term ) : ?>
				<label class="cso-filtri__item">
					<input type="checkbox" name="livello[]" value="<?php echo esc_attr( $term->slug ); ?>"
						<?php checked( in_array( $term->slug, $filter_livello, true ) ); ?>>
					<?php echo esc_html( $term->name ); ?>
				</label>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $all_localita ) ) : ?>
			<div>
				<div class="cso-filtri__group-label"><?php echo esc_html( calypsosub_opt( 'uscite', 'filtri_localita', __( 'LOCALITÀ', 'calypsosub' ) ) ); ?></div>
				<?php foreach ( $all_localita as $loc ) : ?>
				<label class="cso-filtri__item">
					<input type="checkbox" name="localita[]" value="<?php echo esc_attr( $loc ); ?>"
						<?php checked( in_array( $loc, $filter_localita, true ) ); ?>>
					<?php echo esc_html( $loc ); ?>
				</label>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div>
				<div class="cso-filtri__group-label"><?php echo esc_html( calypsosub_opt( 'uscite', 'filtri_disponibilita', __( 'DISPONIBILITÀ', 'calypsosub' ) ) ); ?></div>
				<label class="cso-filtri__item">
					<input type="checkbox" name="avail[]" value="liberi"
						<?php checked( in_array( 'liberi', $filter_avail, true ) ); ?>>
					<?php echo esc_html( calypsosub_opt( 'uscite', 'filtri_liberi', __( 'Posti liberi', 'calypsosub' ) ) ); ?>
				</label>
				<label class="cso-filtri__item">
					<input type="checkbox" name="avail[]" value="attesa"
						<?php checked( in_array( 'attesa', $filter_avail, true ) ); ?>>
					<?php echo esc_html( calypsosub_opt( 'uscite', 'filtri_attesa', __( "Lista d'attesa", 'calypsosub' ) ) ); ?>
				</label>
			</div>

		</div><!-- .cso-filtri__groups -->

		<div class="cso-filtri__actions">
			<button type="submit" class="cso-filtri__btn-apply"><?php echo esc_html( calypsosub_opt( 'uscite', 'btn_applica', __( 'Applica', 'calypsosub' ) ) ); ?></button>
			<?php if ( ! empty( $filter_livello ) || ! empty( $filter_localita ) || ! empty( $filter_avail ) ) : ?>
			<a href="<?php echo esc_url( $archive_url ); ?>" class="cso-filtri__btn-reset"><?php echo esc_html( calypsosub_opt( 'uscite', 'btn_rimuovi', __( 'Rimuovi', 'calypsosub' ) ) ); ?></a>
			<?php endif; ?>
		</div>

	</form>
	</aside>

	<!-- Contenuto principale -->
	<main class="cso-main">

	<?php if ( empty( $per_mese ) ) : ?>

	<div class="cso-empty">
		<p class="cso-empty__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'empty_title', __( 'Nessuna uscita trovata.', 'calypsosub' ) ) ); ?></p>
		<?php if ( ! empty( $filter_livello ) || ! empty( $filter_localita ) || ! empty( $filter_avail ) ) : ?>
		<p class="cso-empty__sub">
			<?php echo esc_html( calypsosub_opt( 'uscite', 'empty_sub', __( 'Prova a modificare i filtri.', 'calypsosub' ) ) ); ?>
			<a href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( calypsosub_opt( 'uscite', 'empty_show_all', __( 'Mostra tutte', 'calypsosub' ) ) ); ?></a>
		</p>
		<?php endif; ?>
	</div>

	<?php else : ?>

	<?php foreach ( $per_mese as $mese_key => $uscite_mese ) :
		[ $anno, $mm ] = explode( '-', $mese_key );
		$nome_mese = $mesi_it[ $mm ] ?? ucfirst( date_i18n( 'F', mktime( 0, 0, 0, (int) $mm, 1 ) ) );
		$n = count( $uscite_mese );
	?>
	<div class="cso-mese">

		<h2 class="cso-mese__heading display">
			<?php echo esc_html( $nome_mese ); ?>
			<span class="cso-mese__count">
				<?php echo esc_html( sprintf( _n( '%d uscita', '%d uscite', $n, 'calypsosub' ), $n ) ); ?>
			</span>
		</h2>

		<div class="cso-uscite-list">
		<?php foreach ( $uscite_mese as $u ) :
			$ts       = strtotime( $u->_prima_data );
			$giorno   = $giorni_it[ date( 'D', $ts ) ] ?? date( 'D', $ts );
			$num      = date( 'j', $ts );
			$luogo    = (string) get_post_meta( $u->ID, '_uscita_luogo',   true );
			$ritrovo  = (string) get_post_meta( $u->ID, '_uscita_ritrovo', true );
			// Antepone orario al ritrovo se l'ora è disponibile
			if ( $u->_prima_ora && $ritrovo ) $ritrovo = $u->_prima_ora . ' · ' . $ritrovo;
			elseif ( $u->_prima_ora )          $ritrovo = $u->_prima_ora;

			$lbl_prenota  = calypsosub_opt( 'uscite', 'btn_prenota',  __( 'Prenota',        'calypsosub' ) );
			$lbl_attesa   = calypsosub_opt( 'uscite', 'btn_attesa',   __( "Lista d'attesa", 'calypsosub' ) );
			$lbl_esaurito = calypsosub_opt( 'uscite', 'btn_esaurito', __( 'Esaurito',        'calypsosub' ) );
			$lbl_ritrovo  = calypsosub_opt( 'uscite', 'label_ritrovo', __( 'RITROVO',        'calypsosub' ) );
			$livello  = ! empty( $u->_livelli ) ? $u->_livelli[0] : '';

			/* Pulsante */
			if ( $u->_posti === null || $u->_posti > 0 ) {
				$btn_label    = $lbl_prenota;
				$btn_disabled = false;
			} elseif ( $u->_lista_attesa ) {
				$btn_label    = $lbl_attesa;
				$btn_disabled = false;
			} else {
				$btn_label    = $lbl_esaurito;
				$btn_disabled = true;
			}

			/* Posti html */
			if ( $u->_posti === null ) {
				$posti_html = '<span class="cso-uscita-row__posti cso-uscita-row__posti--libera">'
					. esc_html( calypsosub_opt( 'uscite', 'filtri_liberi', __( 'Posti liberi', 'calypsosub' ) ) ) . '</span>';
			} elseif ( $u->_posti === 0 ) {
				$posti_html = $u->_lista_attesa
					? '<span class="cso-uscita-row__posti cso-uscita-row__posti--warn">' . esc_html( $lbl_attesa ) . '</span>'
					: '<span class="cso-uscita-row__posti cso-uscita-row__posti--full">' . esc_html( $lbl_esaurito ) . '</span>';
			} elseif ( $u->_posti <= 3 ) {
				$posti_html = '<span class="cso-uscita-row__posti cso-uscita-row__posti--warn">● '
					. esc_html( $u->_posti . ' ' . _n( 'posto', 'posti', $u->_posti, 'calypsosub' ) ) . '</span>';
			} else {
				$posti_html = '<span class="cso-uscita-row__posti cso-uscita-row__posti--ok">'
					. esc_html( $u->_posti . ' ' . __( 'posti', 'calypsosub' ) ) . '</span>';
			}

			$row_class = 'cso-uscita-row' . ( $first_row ? ' cso-uscita-row--highlight' : '' );
			$first_row = false;

			/* Link prenota */
			$book_url = is_user_logged_in()
				? add_query_arg( 'prenota', '1', get_permalink( $u->ID ) )
				: wp_login_url( get_permalink( $u->ID ) );
		?>
		<div class="<?php echo esc_attr( $row_class ); ?>">

			<div class="cso-uscita-row__date">
				<span class="cso-uscita-row__dayname"><?php echo esc_html( $giorno ); ?></span>
				<p class="cso-uscita-row__daynum display"><?php echo esc_html( str_pad( $num, 2, '0', STR_PAD_LEFT ) ); ?></p>
			</div>

			<div class="cso-uscita-row__info">
				<a href="<?php echo esc_url( get_permalink( $u->ID ) ); ?>">
					<p class="cso-uscita-row__title"><?php echo esc_html( get_the_title( $u->ID ) ); ?></p>
				</a>
				<?php if ( $luogo ) : ?>
				<p class="cso-uscita-row__luogo">
					<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<?php echo esc_html( $luogo ); ?>
				</p>
				<?php endif; ?>
			</div>

			<div class="cso-uscita-row__badge">
				<?php echo $livello ? esc_html( $livello ) : esc_html__( 'Tutti', 'calypsosub' ); ?>
			</div>

			<div class="cso-uscita-row__ritrovo">
				<?php if ( $ritrovo ) : ?>
				<span class="cso-uscita-row__ritrovo-label"><?php echo esc_html( $lbl_ritrovo ); ?></span>
				<p class="cso-uscita-row__ritrovo-val"><?php echo esc_html( $ritrovo ); ?></p>
				<?php endif; ?>
			</div>

			<?php echo $posti_html; ?>

			<div class="cso-uscita-row__cta">
				<?php if ( $btn_disabled ) : ?>
				<span class="cso-btn-dark cso-btn-dark--disabled"><?php echo esc_html( $btn_label ); ?></span>
				<?php else : ?>
				<a href="<?php echo esc_url( $book_url ); ?>" class="cso-btn-dark"><?php echo esc_html( $btn_label ); ?></a>
				<?php endif; ?>
			</div>

		</div>
		<?php endforeach; ?>
		</div><!-- .cso-uscite-list -->

	</div><!-- .cso-mese -->
	<?php endforeach; ?>

	<?php endif; ?>

	</main>

</div><!-- .cso-archive-inner -->
</section>

</div><!-- .cso-archive -->

<?php
if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-footer-wrap">';
	block_template_part( 'footer' );
	echo '</div>';
}
get_footer();
?>
