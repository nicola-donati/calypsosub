<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$cso_header_html = '';
$cso_footer_html = '';
if ( function_exists( 'block_template_part' ) ) {
	/* Pre-renderizzati PRIMA di get_header() così gli "elements styles"
	 * (es. colore/font dei link, .wp-elements-N) generati da questi
	 * blocchi vengono registrati in tempo utile per essere stampati
	 * nell'head da wp_head(), invece di arrivare troppo tardi. */
	ob_start();
	block_template_part( 'header' );
	$cso_header_html = do_shortcode( ob_get_clean() );

	ob_start();
	block_template_part( 'footer' );
	$cso_footer_html = do_shortcode( ob_get_clean() );
}

get_header();

if ( $cso_header_html !== '' ) {
	echo '<header class="wp-block-template-part cso-site-header-wrap">' . $cso_header_html . '</header>';
}

$id = get_the_ID();

$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );

$sottotitolo       = (string) get_post_meta( $id, '_evento_sottotitolo', true );
$luogo             = (string) get_post_meta( $id, '_evento_luogo', true );
$indirizzo         = (string) get_post_meta( $id, '_evento_indirizzo', true );
$luogo_descrizione = (string) get_post_meta( $id, '_evento_luogo_descrizione', true );
$luogo_foto_id     = (int) get_post_meta( $id, '_evento_luogo_foto_id', true );
$luogo_foto_url    = $luogo_foto_id ? (string) wp_get_attachment_image_url( $luogo_foto_id, 'large' ) : '';
$max_part          = get_post_meta( $id, '_evento_max_partecipanti', true );
$lista_attesa      = (int) get_post_meta( $id, '_evento_lista_attesa', true );
$date              = (array) ( get_post_meta( $id, '_evento_date', true ) ?: [] );

sort( $date );
$now   = current_time( 'Y-m-d\TH:i' );
$today = current_time( 'Y-m-d' );
$prossima = '';
foreach ( $date as $dt ) {
	if ( $dt >= $now ) { $prossima = $dt; break; }
}
if ( ! $prossima && $date ) $prossima = end( $date );

$fmt = static function ( string $dt ): string {
	$ts = strtotime( $dt );
	if ( ! $ts ) return $dt;
	return strlen( $dt ) > 10 ? wp_date( 'j F Y — H:i', $ts ) : wp_date( 'j F Y', $ts );
};

$dc_dow = $dc_day = $dc_mon = $dc_time = '';
if ( $prossima ) {
	$dc_ts   = strtotime( $prossima );
	$dc_dow  = mb_strtoupper( wp_date( 'D', $dc_ts ) );
	$dc_day  = wp_date( 'd', $dc_ts );
	$dc_mon  = mb_strtoupper( wp_date( 'M', $dc_ts ) );
	$dc_time = strlen( $prossima ) > 10 ? wp_date( 'H:i', $dc_ts ) : '';
}

$loc_line = implode( ' · ', array_filter( [ $luogo, $indirizzo ] ) );

$hero_img = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'full' ) : '';

global $calypsosub_booking_manager;
$user_id     = get_current_user_id();
$logged_in   = is_user_logged_in();
$has_booking = false;
$can_book    = false;
$remaining   = null;
$confirmed   = 0;

if ( $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) {
	$remaining   = $calypsosub_booking_manager->get_remaining_spots( $id );
	$confirmed   = $calypsosub_booking_manager->count_confirmed( $id );
	$has_booking = $logged_in && $calypsosub_booking_manager->user_has_booking( $id, $user_id );
	$can_book    = $logged_in && calypso_can_book( $id, $user_id );
}

/* Testo posti per l'hero-fact (stessa logica usata in origine per l'infobar) */
$posti_fact = '';
if ( $remaining !== null ) {
	if ( $remaining > 0 ) {
		$posti_fact = sprintf( __( '%d disponibili', 'calypsosub' ), $remaining );
	} elseif ( $lista_attesa ) {
		$posti_fact = __( "Lista d'attesa", 'calypsosub' );
	} else {
		$posti_fact = __( 'Esauriti', 'calypsosub' );
	}
}

$show_bar = $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager
	&& $max_part !== '' && (int) $max_part > 0;
$bar_pct  = $show_bar ? max( 0, min( 100, (int) round( $confirmed / (int) $max_part * 100 ) ) ) : 0;

$maps_query = $indirizzo ?: $luogo;
$maps       = $maps_query ? calypso_maps_links( $maps_query ) : null;

$cal_links = null;
if ( $prossima ) {
	$cal_links = [
		'gcal' => calypso_gcal_link( get_the_title(), $prossima, '', $indirizzo ?: $luogo, $sottotitolo ),
		'ics'  => calypso_ics_data_uri( get_the_title(), $prossima, '', $indirizzo ?: $luogo, $sottotitolo ),
	];
}

/* ── Altri eventi in calendario (prossimi, escluso quello corrente) ── */
$correlati = [];
if ( class_exists( 'Calypsosub_Ajax_Eventi' ) ) {
	foreach ( Calypsosub_Ajax_Eventi::query( '', '', '', '', false ) as $ev ) {
		if ( (int) $ev->ID === $id || $ev->_prima_data < $today ) continue;
		$correlati[] = $ev;
		if ( count( $correlati ) >= 3 ) break;
	}
}
?>
<style>
.cso{color:var(--c-ink,#0b1a26);--radius:4px;--radius-lg:12px}

/* ── Hero: fascia colore piena + griglia datecard/titolo/fact, foto in box sotto (fedele al mockup) ── */
.cso .cso-hero{height:auto;overflow:visible;position:relative;color:#fff;padding:calc(var(--cso-header-h) + 40px) 48px 56px}
@media(max-width:1024px){.cso .cso-hero{padding:calc(var(--cso-header-h) + 24px) 20px 40px}}
.cso-hero a{text-decoration:none}
.cso-hero a:hover{color:rgba(255,255,255,.9)}

.cso-hero__inner{max-width:1320px;margin:0 auto}
.cso-hero__grid{display:grid;grid-template-columns:auto 1.3fr 1fr;gap:40px;align-items:end;margin-bottom:32px}
.cso-hero__grid .cso-datecard{grid-column:1}
.cso-hero__maintext{grid-column:2}
.cso-hero__lede{grid-column:3}
@media(max-width:1024px){
	.cso-hero__grid{grid-template-columns:auto 1fr}
	.cso-hero__maintext{grid-column:2}
	.cso-hero__lede{grid-column:1/-1}
}
@media(max-width:760px){
	.cso-hero__grid{grid-template-columns:1fr;gap:20px}
	.cso-hero__grid .cso-datecard,.cso-hero__maintext,.cso-hero__lede{grid-column:1}
}

.cso-datecard{background:#fff;color:var(--c-deep,#0a2540);border-radius:16px;padding:16px 22px 14px;text-align:center;min-width:120px}
.cso-datecard__dow{font-weight:600;letter-spacing:.12em;text-transform:uppercase;font-size:11px}
.cso-datecard__day{font-weight:800;font-size:68px;line-height:.9}
.cso-datecard__mon{font-weight:800;font-size:22px;letter-spacing:.04em;text-transform:uppercase}
.cso-datecard__time{border-top:1px solid rgba(11,26,38,.1);margin-top:10px;padding-top:8px;font-size:12px;color:rgba(11,26,38,.6)}
@media(max-width:760px){.cso-datecard{display:flex;align-items:center;gap:14px;text-align:left;padding:10px 16px}.cso-datecard__day{font-size:40px}}

.cso-badge{background:var(--c-wave);display:inline-block;padding:8px 16px;border-radius:999px;letter-spacing:.06em;text-transform:uppercase;line-height:1;margin-bottom:16px}
.cso-hero__title{margin:0 0 14px}
.cso-hero__loc{display:flex;align-items:center;gap:8px;font-size:15px;color:var(--c-aqua,#26CBFB);font-weight:500;flex-wrap:wrap}

.cso-hero__lede p{font-size:16px;line-height:1.6;opacity:.85;margin:0 0 20px}
.cso-hero__facts{display:flex;gap:12px;flex-wrap:wrap}
.cso-fact{flex:1 1 0;min-width:110px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:12px;padding:12px 14px}
.cso-fact__k{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--c-aqua,#26CBFB);margin-bottom:4px;opacity:.9}
.cso-fact__v{font-weight:800;font-size:22px;line-height:1;text-transform:uppercase}

.cso-hero__img-wrap{position:relative;border-radius:14px;overflow:hidden;height:380px}
.cso-hero__img-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.cso-hero__img-cap{position:absolute;bottom:0;left:0;letter-spacing:.1em;text-transform:uppercase;padding:10px 14px;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);border-top-right-radius:8px;font-size:11px;color:rgba(255,255,255,.9)}
@media(max-width:760px){.cso-hero__img-wrap{height:220px}}

.cso-body{max-width:1320px;margin:0 auto;padding:48px 24px;display:grid;grid-template-columns:1fr 360px;gap:48px;align-items:start}
@media(max-width:1024px){.cso-body{grid-template-columns:1fr}}
.cso-section{margin-bottom:56px}
.cso-section:last-child{margin-bottom:0}
.cso-eyebrow{font-weight:500;letter-spacing:.16em;text-transform:uppercase;margin:0 0 14px;display:block;font-size:16px;color:var(--c-wave)}
.cso-display-heading{font-size:clamp(28px,4vw,56px);font-weight:800;text-transform:uppercase;letter-spacing:-.01em;line-height:.96;color:var(--c-deep);margin:0 0 20px}
.cso-prose{font-size:17px;line-height:1.75;color:var(--c-ink);max-width:720px}
.cso-prose p{margin:0 0 1em}
.cso-prose p:last-child{margin-bottom:0}
.cso-dates-list{list-style:none;margin:0;padding:0}
.cso-dates-list li{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--c-foam);font-size:15px}
.cso-dates-list li:last-child{border-bottom:none}
.cso-dates-list__dot{width:8px;height:8px;background:var(--c-wave);border-radius:50%;flex-shrink:0}
.cso-dove{background:#fff;border:1px solid rgba(11,26,38,.08);border-radius:var(--radius-lg);overflow:hidden;display:grid;grid-template-columns:1.1fr 1fr}
.cso-dove--no-photo{grid-template-columns:1fr}
@media(max-width:700px){.cso-dove{grid-template-columns:1fr}}
.cso-dove__photo{position:relative;min-height:240px;background:linear-gradient(135deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7))}
.cso-dove__photo img{width:100%;height:100%;object-fit:cover;display:block}
.cso-dove__photo-cap{position:absolute;bottom:0;left:0;letter-spacing:.1em;text-transform:uppercase;padding:10px 14px;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);border-top-right-radius:8px;font-size:11px;color:rgba(255,255,255,.9)}
.cso-dove__info{padding:26px;display:flex;flex-direction:column;gap:14px}
.cso-dove__addr{display:flex;align-items:flex-start;gap:8px;font-weight:700;color:var(--c-deep);margin:0;font-size:15px}
.cso-dove__nav{display:flex;flex-wrap:wrap;gap:16px}
.cso-dove__nav a{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--c-wave);text-decoration:none}
.cso-dove__nav a:hover{color:var(--c-deep,#0a2540)}
.cso-card__links{padding:16px 24px;border-top:1px solid rgba(255,255,255,.1)}
.cso-card__links-label{letter-spacing:.08em;text-transform:uppercase;font-weight:600;font-size:11px;color:rgba(255,255,255,.6);display:block;margin-bottom:10px}
.cso-card__links-row{display:flex;flex-wrap:wrap;gap:8px}
.cso-card__links-row a{flex:1;display:flex;justify-content:center;padding:9px 10px;font-size:12px;font-weight:600;border:1px solid rgba(255,255,255,.2);border-radius:999px;color:#fff;text-decoration:none;white-space:nowrap}
.cso-card__links-row a:hover{border-color:rgba(255,255,255,.5)}
.cso-card{background:#fff;border-radius:18px;box-shadow:0 20px 50px -24px rgba(10,37,64,.5);overflow:hidden;position:sticky;top:24px}
.cso-card__head{color:#fff;padding:24px}
.cso-card__head-title{font-size:20px;font-weight:700;text-transform:uppercase;margin:0 0 4px}
.cso-card__head-sub{font-size:13px;opacity:.7;margin:0}
.cso-card__bar{height:6px;border-radius:999px;background:rgba(255,255,255,.25);overflow:hidden;margin-top:16px}
.cso-card__bar i{display:block;height:100%}
.cso-card__bar-label{display:flex;justify-content:space-between;gap:8px;font-size:11px;letter-spacing:.04em;text-transform:uppercase;opacity:.85;margin-top:8px}
.cso-card__body{padding:24px}
.cso-spots{text-align:center;margin-bottom:20px}
.cso-spots__num{font-size:48px;font-weight:900;line-height:1}
.cso-spots__label{font-size:13px;color:#666;margin-top:4px}
.cso-btn{display:block;width:100%;padding:14px;color:#fff;border:none;border-radius:999px;font-size:18px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer;text-decoration:none;text-align:center;transition:background .2s,transform .15s}
.cso-btn:hover{transform:translateY(-1px)}
.cso-notice{text-align:center;padding:16px;border-radius:var(--radius);font-size:14px;margin-bottom:16px}
.cso-notice--success{background:#d4edda;color:#155724}
.cso-notice--waitlist{background:#d1ecf1;color:#0c5460}
.cso-notice--error{background:#f8d7da;color:#721c24}
.cso-login-cta{text-align:center;padding:24px}
.cso-login-cta p{margin:0 0 16px;font-size:14px;color:#666}
.cso-related{background:var(--c-bone,#f6f1e6);padding:64px 24px}
.cso-related__inner{max-width:1320px;margin:0 auto}
.cso-related__head{display:flex;align-items:baseline;justify-content:space-between;gap:24px;flex-wrap:wrap;margin-bottom:32px}
.cso-related__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
@media(max-width:1024px){.cso-related__grid{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.cso-related__grid{grid-template-columns:1fr}}
</style>
<?php
$_evd = [
	'accent'           => calypsosub_opt( 'eventi', 'design_accent',           '#E9BF26' ),
	'deep'             => calypsosub_opt( 'eventi', 'design_deep',             '#1B77A7' ),
	'body_bg'          => calypsosub_opt( 'eventi', 'design_body_bg',          '#ffffff' ),
	'hero_overlay'     => calypsosub_opt( 'eventi', 'design_hero_overlay_color', '#061826' ),
	'hero_badge_color' => calypsosub_opt( 'eventi', 'design_hero_badge_color', '#ffffff' ),
	'hero_badge_size'  => calypsosub_opt_int( 'eventi', 'design_hero_badge_size', '14' ),
	'hero_badge_weight'=> calypsosub_opt_int( 'eventi', 'design_hero_badge_weight', '600' ),
	'hero_title_color' => calypsosub_opt( 'eventi', 'design_hero_title_color', '#ffffff' ),
	'hero_title_size'  => calypsosub_opt_int( 'eventi', 'design_hero_title_size', '104' ),
	'hero_title_weight'=> calypsosub_opt_int( 'eventi', 'design_hero_title_weight', '700' ),
	'hero_title_font'  => preg_replace( '/[^a-zA-Z0-9 ,\"\'\-]/', '', calypsosub_opt( 'eventi', 'design_hero_title_font', '' ) ),
	'hero_sub_color'   => calypsosub_opt( 'eventi', 'design_hero_sub_color',   '#ffffff' ),
	'hero_sub_opacity' => calypsosub_opt_int( 'eventi', 'design_hero_sub_opacity', '85' ),
	'hero_sub_size'    => calypsosub_opt_int( 'eventi', 'design_hero_sub_size', '18' ),
	'hero_sub_weight'  => calypsosub_opt_int( 'eventi', 'design_hero_sub_weight', '400' ),
];
?>
<style>
.cso{background:<?php echo esc_attr($_evd['body_bg']); ?>}
.cso .cso-hero{background-color:<?php echo esc_attr($_evd['deep']); ?>}
.cso-badge{background:<?php echo esc_attr($_evd['accent']); ?>}
.cso .cso-badge{color:<?php echo esc_attr($_evd['hero_badge_color']); ?>;font-size:<?php echo $_evd['hero_badge_size']; ?>px;font-weight:<?php echo $_evd['hero_badge_weight']; ?>}
.cso .cso-hero__title{
	color:<?php echo esc_attr($_evd['hero_title_color']); ?>;
	font-size:clamp(36px,7vw,<?php echo $_evd['hero_title_size']; ?>px);
	font-weight:<?php echo $_evd['hero_title_weight']; ?>;
	<?php if ($_evd['hero_title_font']) : ?>font-family:<?php echo $_evd['hero_title_font']; ?>;<?php endif; ?>
}
.cso .cso-hero__lede p{
	color:<?php echo esc_attr($_evd['hero_sub_color']); ?>;
	opacity:<?php echo max( 0, min( 100, $_evd['hero_sub_opacity'] ) ) / 100; ?>;
	font-size:<?php echo $_evd['hero_sub_size']; ?>px;
	font-weight:<?php echo $_evd['hero_sub_weight']; ?>;
}
.cso-card__head{background:<?php echo esc_attr($_evd['deep']); ?>}
.cso-spots__num{color:<?php echo esc_attr($_evd['accent']); ?>}
.cso-dates-list__dot{background:<?php echo esc_attr($_evd['accent']); ?>}
.cso-btn{background:<?php echo esc_attr($_evd['accent']); ?>}
.cso-card__bar i{background:<?php echo esc_attr($_evd['accent']); ?>}
</style>

<div class="cso">

<section class="cso-hero">
<div class="cso-hero__inner">

	<nav class="cso-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'calypsosub' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'calypsosub' ); ?></a>
		<span>/</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_evento' ) ); ?>"><?php echo esc_html( calypsosub_opt( 'eventi', 'breadcrumb_archive', __( 'Eventi', 'calypsosub' ) ) ); ?></a>
		<span>/</span>
		<span class="cso-breadcrumb__current"><?php the_title(); ?></span>
	</nav>

	<div class="cso-hero__grid">
		<?php if ( $prossima ) : ?>
		<div class="cso-datecard">
			<div class="cso-datecard__dow"><?php echo esc_html( $dc_dow ); ?></div>
			<div class="cso-datecard__day"><?php echo esc_html( $dc_day ); ?></div>
			<div class="cso-datecard__mon"><?php echo esc_html( $dc_mon ); ?></div>
			<?php if ( $dc_time ) : ?><div class="cso-datecard__time"><?php echo esc_html( $dc_time ); ?></div><?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="cso-hero__maintext">
			<span class="cso-badge"><?php echo esc_html( calypsosub_opt( 'eventi', 'badge', __( 'Evento', 'calypsosub' ) ) ); ?></span>
			<h1 class="cso-hero__title"><?php the_title(); ?></h1>
			<?php if ( $loc_line ) : ?>
			<div class="cso-hero__loc">📍 <?php echo esc_html( $loc_line ); ?></div>
			<?php endif; ?>
		</div>

		<div class="cso-hero__lede">
			<?php if ( $sottotitolo ) : ?>
			<p><?php echo esc_html( $sottotitolo ); ?></p>
			<?php endif; ?>
			<?php if ( $prossima || $posti_fact ) : ?>
			<div class="cso-hero__facts">
				<?php if ( $prossima ) : ?>
				<div class="cso-fact">
					<div class="cso-fact__k"><?php esc_html_e( 'Data', 'calypsosub' ); ?></div>
					<div class="cso-fact__v"><?php echo esc_html( $fmt( $prossima ) ); ?></div>
				</div>
				<?php endif; ?>
				<?php if ( $posti_fact ) : ?>
				<div class="cso-fact">
					<div class="cso-fact__k"><?php esc_html_e( 'Posti', 'calypsosub' ); ?></div>
					<div class="cso-fact__v"><?php echo esc_html( $posti_fact ); ?></div>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $hero_img ) : ?>
	<div class="cso-hero__img-wrap">
		<img src="<?php echo esc_url( $hero_img ); ?>" alt="<?php the_title_attribute(); ?>">
		<span class="cso-hero__img-cap"><?php echo esc_html( get_the_title() ); ?></span>
	</div>
	<?php endif; ?>

</div>
</section>

<div class="cso-body">
	<div class="cso-main">
		<?php if ( get_the_content() ) : ?>
		<div class="cso-section">
			<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_descrizione_eyebrow', __( "L'evento", 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_descrizione', __( 'Descrizione', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-prose"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $date ) : ?>
		<div class="cso-section">
			<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_date_eyebrow', __( 'Quando', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_date', __( 'Date', 'calypsosub' ) ) ); ?></h2>
			<ul class="cso-dates-list">
				<?php foreach ( $date as $dt ) : ?>
				<li><span class="cso-dates-list__dot"></span><?php echo esc_html( $fmt( $dt ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<?php if ( $indirizzo && $luogo_descrizione ) : ?>
		<div class="cso-section">
			<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_dove_eyebrow', __( 'Il luogo', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_dove', __( 'Dove', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-dove<?php echo $luogo_foto_url ? '' : ' cso-dove--no-photo'; ?>">
				<?php if ( $luogo_foto_url ) : ?>
				<div class="cso-dove__photo">
					<img src="<?php echo esc_url( $luogo_foto_url ); ?>" alt="<?php echo esc_attr( $luogo ?: $indirizzo ); ?>" loading="lazy">
					<span class="cso-dove__photo-cap"><?php echo esc_html( sprintf( /* translators: %s = nome del luogo */ __( 'Mappa · %s', 'calypsosub' ), $luogo ?: $indirizzo ) ); ?></span>
				</div>
				<?php endif; ?>
				<div class="cso-dove__info">
					<p class="cso-dove__addr">📍 <?php echo esc_html( $indirizzo ); ?></p>
					<div class="cso-prose"><?php echo wpautop( esc_html( $luogo_descrizione ) ); ?></div>
					<?php if ( $maps ) : ?>
					<div class="cso-dove__nav">
						<a href="<?php echo esc_url( $maps['google'] ); ?>" target="_blank" rel="noopener">Apri in Google Maps →</a>
						<a href="<?php echo esc_url( $maps['apple'] ); ?>" target="_blank" rel="noopener">Apple Maps →</a>
						<a href="<?php echo esc_url( $maps['waze'] ); ?>" target="_blank" rel="noopener">Waze →</a>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<aside>
		<div class="cso-card">
			<div class="cso-card__head">
				<p class="cso-card__head-title"><?php echo esc_html( calypsosub_opt( 'eventi', 'card_title', __( 'Partecipa', 'calypsosub' ) ) ); ?></p>
				<?php if ( $prossima ) : ?>
				<p class="cso-card__head-sub"><?php echo esc_html( $fmt( $prossima ) ); ?></p>
				<?php endif; ?>
				<?php if ( $show_bar ) : ?>
				<div class="cso-card__bar"><i style="width:<?php echo esc_attr( $bar_pct ); ?>%"></i></div>
				<div class="cso-card__bar-label">
					<span><?php echo esc_html( $confirmed ) . ' ' . esc_html( calypsosub_opt( 'eventi', 'label_prenotati', __( 'prenotati', 'calypsosub' ) ) ); ?></span>
					<span><?php echo esc_html( max( 0, (int) $remaining ) ) . ' ' . esc_html( calypsosub_opt( 'eventi', 'label_posti', __( 'posti disponibili', 'calypsosub' ) ) ); ?></span>
				</div>
				<?php endif; ?>
			</div>
			<div class="cso-card__body">

				<?php if ( $has_booking ) : ?>
				<div class="cso-notice cso-notice--success"><?php echo esc_html( calypsosub_opt( 'eventi', 'msg_gia_iscritto', __( '✓ Sei già iscritto a questo evento.', 'calypsosub' ) ) ); ?></div>
				<a href="<?php echo esc_url( get_permalink( get_option( 'calypsosub_account_page_id' ) ) ); ?>" class="cso-btn" style="background:<?php echo esc_attr( $_evd['deep'] ); ?>"><?php echo esc_html( calypsosub_opt( 'eventi', 'btn_area_personale', __( 'Area personale', 'calypsosub' ) ) ); ?></a>

				<?php elseif ( ! $logged_in ) : ?>
				<div class="cso-login-cta">
					<p><?php echo esc_html( calypsosub_opt( 'eventi', 'msg_accedi_cta', __( "Accedi per iscriverti all'evento.", 'calypsosub' ) ) ); ?></p>
					<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="cso-btn"><?php esc_html_e( 'Accedi', 'calypsosub' ); ?></a>
				</div>

				<?php elseif ( $can_book ) : ?>
				<?php if ( $remaining === 0 && $lista_attesa ) : ?>
				<div class="cso-notice cso-notice--waitlist"><?php echo esc_html( calypsosub_opt( 'eventi', 'msg_lista_avviso', __( "Posti esauriti — puoi iscriverti in lista d'attesa.", 'calypsosub' ) ) ); ?></div>
				<?php elseif ( $remaining !== null ) : ?>
				<div class="cso-spots"><div class="cso-spots__num"><?php echo esc_html( $remaining ); ?></div><div class="cso-spots__label"><?php echo esc_html( calypsosub_opt( 'eventi', 'label_posti', __( 'posti disponibili', 'calypsosub' ) ) ); ?></div></div>
				<?php endif; ?>
				<?php if ( $prenotazioni_page_id ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'prenota_id', $id, get_permalink( $prenotazioni_page_id ) ) ); ?>" class="cso-btn">
					<?php echo esc_html( calypsosub_opt( 'eventi', 'btn_iscriviti', __( 'Iscriviti', 'calypsosub' ) ) ); ?>
				</a>
				<?php endif; ?>

				<?php else : ?>
				<div class="cso-notice cso-notice--error"><?php echo esc_html( calypsosub_opt( 'eventi', 'msg_esauriti', __( 'Posti esauriti.', 'calypsosub' ) ) ); ?></div>
				<?php endif; ?>

			</div>
			<?php if ( $cal_links ) : ?>
			<div class="cso-card__links">
				<span class="cso-card__links-label"><?php esc_html_e( 'Aggiungi al calendario', 'calypsosub' ); ?></span>
				<div class="cso-card__links-row">
					<a href="<?php echo esc_url( $cal_links['gcal'] ); ?>" target="_blank" rel="noopener">Google</a>
					<a href="<?php echo esc_attr( $cal_links['ics'] ); ?>" download="<?php echo esc_attr( sanitize_title( get_the_title() ) ); ?>.ics">Apple / Outlook</a>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</aside>
</div>
</div>

<?php if ( $correlati ) : ?>
<section class="cso-related">
<div class="cso-related__inner">
	<div class="cso-related__head">
		<div>
			<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_correlati_eyebrow', __( 'Vita del club', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-display-heading" style="margin:0"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_correlati', __( 'Altri eventi in calendario', 'calypsosub' ) ) ); ?></h2>
		</div>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_evento' ) ); ?>"><?php esc_html_e( 'Calendario completo →', 'calypsosub' ); ?></a>
	</div>
	<div class="cso-related__grid">
		<?php foreach ( $correlati as $ev ) :
			$ev_id      = $ev->ID;
			$ev_luogo   = get_post_meta( $ev_id, '_evento_luogo', true );
			$ev_prima   = date_i18n( get_option( 'date_format' ), strtotime( $ev->_prima_data ) );
			$ev_max     = get_post_meta( $ev_id, '_evento_max_partecipanti', true );
			$ev_spots   = $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager
				? $calypsosub_booking_manager->get_remaining_spots( $ev_id )
				: null;
			$ev_desc    = get_post_meta( $ev_id, '_evento_desc_breve', true );
			$ev_img     = get_the_post_thumbnail_url( $ev_id, 'medium_large' );
		?>
		<article class="calypso-card">
			<?php if ( $ev_img ) : ?>
				<img class="calypso-card__img" src="<?php echo esc_url( $ev_img ); ?>" alt="<?php echo esc_attr( $ev->post_title ); ?>">
			<?php else : ?>
				<div class="calypso-card__img-placeholder">🎉</div>
			<?php endif; ?>
			<div class="calypso-card__body">
				<span class="calypso-card__badge"><?php esc_html_e( 'Evento', 'calypsosub' ); ?></span>
				<h3 class="calypso-card__title">
					<a href="<?php echo esc_url( get_permalink( $ev_id ) ); ?>" style="color:inherit;text-decoration:none">
						<?php echo esc_html( $ev->post_title ); ?>
					</a>
				</h3>
				<?php if ( $ev_luogo ) : ?>
					<p class="calypso-card__subtitle">📍 <?php echo esc_html( $ev_luogo ); ?></p>
				<?php endif; ?>
				<div class="calypso-card__meta">
					<span>📅 <?php echo esc_html( $ev_prima ); ?></span>
				</div>
				<?php if ( $ev_desc ) : ?>
					<p class="calypso-card__desc"><?php echo esc_html( $ev_desc ); ?></p>
				<?php endif; ?>
				<div class="calypso-card__footer">
					<?php if ( $ev_max !== '' && $ev_max !== false ) :
						$ev_full = $ev_spots !== null && $ev_spots === 0;
					?>
						<span class="calypso-card__spots <?php echo $ev_full ? 'calypso-card__spots--full' : ''; ?>">
							<?php if ( $ev_full ) :
								esc_html_e( 'Esaurito', 'calypsosub' );
							else :
								printf( esc_html__( '%d posti liberi', 'calypsosub' ), (int) $ev_spots );
							endif; ?>
						</span>
					<?php else : ?>
						<span class="calypso-card__spots"><?php esc_html_e( 'Ingresso libero', 'calypsosub' ); ?></span>
					<?php endif; ?>
					<a href="<?php echo esc_url( get_permalink( $ev_id ) ); ?>" class="calypso-btn"><?php esc_html_e( 'Dettagli', 'calypsosub' ); ?></a>
				</div>
			</div>
		</article>
		<?php endforeach; ?>
	</div>
</div>
</section>
<?php endif; ?>

<?php
if ( $cso_footer_html !== '' ) {
	echo '<footer class="wp-block-template-part cso-site-footer-wrap">' . $cso_footer_html . '</footer>';
}
get_footer();
