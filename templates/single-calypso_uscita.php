<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}

$id = get_the_ID();

$sottotitolo        = (string) get_post_meta( $id, '_uscita_sottotitolo', true );
$luogo              = (string) get_post_meta( $id, '_uscita_luogo', true );
$ritrovo            = (string) get_post_meta( $id, '_uscita_ritrovo', true );
$incluso            = (string) get_post_meta( $id, '_uscita_incluso', true );
$cosa_portare       = (string) get_post_meta( $id, '_uscita_cosa_portare', true );
$note_cancellazione = (string) get_post_meta( $id, '_uscita_note_cancellazione', true );

$fmt = static function ( string $dt ): string {
	$ts = strtotime( $dt );
	return $ts ? wp_date( 'j F Y — H:i', $ts ) : $dt;
};

$oggi = current_time( 'Y-m-d\TH:i' );
$occorrenze = calypso_get_occorrenze_by_uscita( $id );
$prossima_occ = null;
foreach ( $occorrenze as $occ ) {
	$occ_data = (string) get_post_meta( $occ->ID, '_occorrenza_uscita_data', true );
	if ( $occ_data >= $oggi ) { $prossima_occ = $occ; break; }
}
if ( ! $prossima_occ && $occorrenze ) $prossima_occ = end( $occorrenze );
$prossima = $prossima_occ ? (string) get_post_meta( $prossima_occ->ID, '_occorrenza_uscita_data', true ) : '';

$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );
$prenotazioni_page_ok = $prenotazioni_page_id && get_post_status( $prenotazioni_page_id ) === 'publish';

$booking_link = static function ( int $occorrenza_id ) use ( $prenotazioni_page_id ): string {
	return add_query_arg( 'prenota_id', $occorrenza_id, get_permalink( $prenotazioni_page_id ) );
};

global $calypsosub_booking_manager;
?>
<style>
.cso{color:var(--c-ink,#0b1a26);--radius:4px;--radius-lg:12px}

/* Hero */
.cso-hero{position:relative;display:flex;align-items:flex-end;overflow:hidden}
.cso-hero__bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform 6s ease}
.cso-hero__bg--gradient{background:linear-gradient(135deg,var(--c-deep) 0%,var(--c-wave) 60%,#1a8a6e 100%)}
.cso-hero:hover .cso-hero__bg{transform:scale(1.03)}
.cso-hero__overlay{position:absolute;inset:0;background:linear-gradient(rgba(6,24,38,.45) 0%,rgba(6,24,38,.1) 40%,rgba(6,24,38,.85) 100%)}
.cso-hero__content{position:relative;z-index:1;padding:40px 48px;max-width:860px}
@media(max-width:1024px){.cso-hero__content{padding:32px 20px}}
.cso-badge{background:var(--c-coral)}
.cso-hero__title{margin:0 0 8px}
.cso-hero__subtitle{line-height:1.6;color:rgba(255,255,255,.85);margin:8px 0 0}

/* Info bar */
.cso-infobar{background:var(--c-deep);color:#fff;padding:18px 48px;display:flex;flex-wrap:wrap;gap:28px}
.cso-infobar__item{display:flex;align-items:center;gap:8px;font-size:14px}
.cso-infobar__icon{font-size:18px;opacity:.7}
.cso-infobar__label{opacity:.6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;display:block}
.cso-infobar__value{font-weight:600}
.cso-infobar__pill{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border-radius:20px;padding:6px 14px}

/* Layout */
.cso-body{max-width:900px;margin:0 auto;padding:48px 24px}

/* Sections */
.cso-section{margin-bottom:40px}
.cso-section__title{font-size:22px;font-weight:700;color:var(--c-deep);text-transform:uppercase;letter-spacing:.04em;margin:0 0 16px;padding-bottom:8px;border-bottom:3px solid var(--c-foam)}
.cso-prose{font-size:16px;line-height:1.75;color:var(--c-ink)}
.cso-prose p{margin:0 0 1em}

.cso-pills{display:flex;flex-wrap:wrap;gap:8px}
.cso-pill{background:var(--c-bone);border-radius:var(--radius);padding:6px 14px;font-size:14px;font-weight:500}

.cso-dates-list{list-style:none;margin:0;padding:0}
.cso-dates-list li{display:flex;align-items:center;gap:16px;padding:14px 0;border-bottom:1px solid var(--c-foam);font-size:15px;flex-wrap:wrap}
.cso-dates-list li:last-child{border-bottom:none}
.cso-dates-list__dot{width:8px;height:8px;background:var(--c-coral);border-radius:50%;flex-shrink:0}
.cso-dates-list__spots{margin-left:auto;font-size:13px;color:#666}
.cso-dates-list__cta{display:inline-block;padding:8px 18px;background:var(--c-coral);color:#fff;border-radius:999px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;text-decoration:none}
.cso-dates-list__cta--disabled{background:#ccc;color:#666;cursor:default}

/* Sidebar / card */
.cso-card{background:#fff;border-radius:var(--radius-lg);box-shadow:0 4px 24px rgba(10,37,64,.1);overflow:hidden;position:sticky;top:24px}
.cso-card__head{background:var(--c-deep);color:#fff;padding:24px}
.cso-card__head-title{font-size:20px;font-weight:700;text-transform:uppercase;margin:0 0 4px}
.cso-card__head-sub{font-size:13px;opacity:.7;margin:0}
.cso-card__body{padding:24px}
.cso-spots{text-align:center;margin-bottom:20px}
.cso-spots__num{font-size:48px;font-weight:900;color:var(--c-coral);line-height:1}
.cso-spots__label{font-size:13px;color:#666;margin-top:4px}
.cso-spots--waitlist .cso-spots__num{color:var(--c-wave)}
.cso-form-row{margin-bottom:16px}
.cso-form-row label{display:block;font-size:13px;font-weight:600;color:var(--c-deep);margin-bottom:6px}
.cso-form-row input,.cso-form-row textarea,.cso-form-row select{width:100%;padding:10px 14px;border:1.5px solid var(--c-foam);border-radius:var(--radius);font-size:14px;color:var(--c-ink);transition:border-color .2s}
.cso-form-row input:focus,.cso-form-row textarea:focus{outline:none;border-color:var(--c-wave)}
.cso-form-row textarea{min-height:80px;resize:vertical}
.cso-btn{display:block;width:100%;padding:14px;background:var(--c-coral);color:#fff;border:none;border-radius:999px;font-size:18px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer;transition:background .2s,transform .15s}
.cso-btn:hover{background:#e55a3a;transform:translateY(-1px)}
.cso-btn:disabled{background:#ccc;cursor:not-allowed;transform:none}
.cso-btn--secondary{background:var(--c-wave)}
.cso-btn--secondary:hover{background:#165c82}
.cso-notice{text-align:center;padding:16px;border-radius:var(--radius);font-size:14px;margin-bottom:16px}
.cso-notice--success{background:#d4edda;color:#155724}
.cso-notice--waitlist{background:#d1ecf1;color:#0c5460}
.cso-notice--error{background:#f8d7da;color:#721c24}
.cso-notice--info{background:var(--c-bone);color:var(--c-ink)}
.cso-login-cta{text-align:center;padding:24px}
.cso-login-cta p{margin:0 0 16px;font-size:14px;color:#666}
.cso-link{color:var(--c-wave);text-decoration:none;font-weight:600}
.cso-link:hover{text-decoration:underline}
#cso-booking-msg{display:none;margin-bottom:16px}
</style>
<?php
$_ud = [
	'accent'          => calypsosub_opt( 'uscite', 'design_accent',          '#ff6b4a' ),
	'deep'            => calypsosub_opt( 'uscite', 'design_deep',             '#0a2540' ),
	'section_heading' => calypsosub_opt( 'uscite', 'design_section_heading',  '#0a2540' ),
	'section_border'  => calypsosub_opt( 'uscite', 'design_section_border',   '#dce1e6' ),
	'body_bg'         => calypsosub_opt( 'uscite', 'design_body_bg',          '#ffffff' ),
];
?>
<style>
.cso{background:<?php echo esc_attr($_ud['body_bg']); ?>}
.cso-badge{background:<?php echo esc_attr($_ud['accent']); ?>}
.cso-infobar{background:<?php echo esc_attr($_ud['deep']); ?>}
.cso-card__head{background:<?php echo esc_attr($_ud['deep']); ?>}
.cso-spots__num{color:<?php echo esc_attr($_ud['accent']); ?>}
.cso-dates-list__dot{background:<?php echo esc_attr($_ud['accent']); ?>}
.cso-dates-list__cta{background:<?php echo esc_attr($_ud['accent']); ?>}
.cso-btn{background:<?php echo esc_attr($_ud['accent']); ?>}
.cso-btn--secondary{background:<?php echo esc_attr($_ud['deep']); ?>}
.cso-section__title{color:<?php echo esc_attr($_ud['section_heading']); ?>;border-bottom-color:<?php echo esc_attr($_ud['section_border']); ?>}
</style>

<div class="cso">

<!-- Hero -->
<section class="cso-hero">
	<?php if ( has_post_thumbnail( $id ) ) : ?>
	<div class="cso-hero__bg" style="background-image:url(<?php echo esc_url( get_the_post_thumbnail_url( $id, 'full' ) ); ?>)"></div>
	<?php else : ?>
	<div class="cso-hero__bg cso-hero__bg--gradient"></div>
	<?php endif; ?>
	<div class="cso-hero__overlay"></div>
	<div class="cso-hero__content">
		<span class="cso-badge"><?php echo esc_html( calypsosub_opt( 'uscite', 'badge', __( 'Uscita subacquea', 'calypsosub' ) ) ); ?></span>
		<h1 class="cso-hero__title"><?php the_title(); ?></h1>
		<?php if ( $sottotitolo ) : ?>
		<p class="cso-hero__subtitle"><?php echo esc_html( $sottotitolo ); ?></p>
		<?php endif; ?>
	</div>
	<div class="cso-hero__scroll" aria-hidden="true">
		SCORRI
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
	</div>
</section>

<!-- Info bar -->
<div class="cso-infobar">
	<?php if ( $luogo ) : ?>
	<div class="cso-infobar__pill">
		<span class="cso-infobar__icon">📍</span>
		<div>
			<span class="cso-infobar__label"><?php esc_html_e( 'Luogo', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value"><?php echo esc_html( $luogo ); ?></span>
		</div>
	</div>
	<?php endif; ?>
	<?php if ( $prossima ) : ?>
	<div class="cso-infobar__pill">
		<span class="cso-infobar__icon">📅</span>
		<div>
			<span class="cso-infobar__label"><?php esc_html_e( 'Prossima data', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value"><?php echo esc_html( $fmt( $prossima ) ); ?></span>
		</div>
	</div>
	<?php endif; ?>
</div>

<!-- Body -->
<div class="cso-body">
	<div class="cso-main">

		<?php if ( has_excerpt() || get_the_content() ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_descrizione', __( 'Descrizione', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-prose"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $occorrenze ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_date', __( 'Date disponibili', 'calypsosub' ) ) ); ?></h2>
			<ul class="cso-dates-list">
				<?php foreach ( $occorrenze as $occ ) :
					$occ_data    = (string) get_post_meta( $occ->ID, '_occorrenza_uscita_data', true );
					$remaining   = $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager
						? $calypsosub_booking_manager->get_remaining_spots( $occ->ID )
						: null;
					$lista_attesa = (int) get_post_meta( $occ->ID, '_occorrenza_uscita_lista_attesa', true );
				?>
				<li>
					<span class="cso-dates-list__dot"></span>
					<span><?php echo esc_html( $fmt( $occ_data ) ); ?></span>
					<?php if ( $remaining !== null ) : ?>
						<span class="cso-dates-list__spots">
						<?php
						if ( $remaining > 0 ) {
							echo esc_html( sprintf( __( '%d posti disponibili', 'calypsosub' ), $remaining ) );
						} elseif ( $lista_attesa ) {
							esc_html_e( "Lista d'attesa aperta", 'calypsosub' );
						} else {
							esc_html_e( 'Posti esauriti', 'calypsosub' );
						}
						?>
						</span>
					<?php endif; ?>
					<?php if ( $prenotazioni_page_ok ) : ?>
						<a class="cso-dates-list__cta" href="<?php echo esc_url( $booking_link( $occ->ID ) ); ?>">
							<?php echo esc_html( calypsosub_opt( 'uscite', 'btn_prenota_ora', __( 'Prenota', 'calypsosub' ) ) ); ?>
						</a>
					<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
						<span class="cso-dates-list__cta cso-dates-list__cta--disabled"><?php esc_html_e( 'Configura pagina prenotazioni', 'calypsosub' ); ?></span>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<?php if ( $ritrovo ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_ritrovo', __( 'Punto di ritrovo', 'calypsosub' ) ) ); ?></h2>
			<p class="cso-prose"><?php echo esc_html( $ritrovo ); ?></p>
		</div>
		<?php endif; ?>

		<?php if ( $incluso ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_incluso', __( "Cosa è incluso", 'calypsosub' ) ) ); ?></h2>
			<div class="cso-prose"><?php echo nl2br( esc_html( $incluso ) ); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $cosa_portare ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_cosa_portare', __( 'Cosa portare', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-prose"><?php echo nl2br( esc_html( $cosa_portare ) ); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $note_cancellazione ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_cancellazione', __( 'Politica di cancellazione', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-prose"><?php echo nl2br( esc_html( $note_cancellazione ) ); ?></div>
		</div>
		<?php endif; ?>

	</div><!-- .cso-main -->

</div><!-- .cso-body -->
</div><!-- .cso -->

<?php get_footer(); ?>
