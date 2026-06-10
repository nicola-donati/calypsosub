<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$id = get_the_ID();

$sottotitolo  = (string) get_post_meta( $id, '_evento_sottotitolo', true );
$luogo        = (string) get_post_meta( $id, '_evento_luogo', true );
$max_part     = get_post_meta( $id, '_evento_max_partecipanti', true );
$lista_attesa = (int) get_post_meta( $id, '_evento_lista_attesa', true );
$date         = (array) ( get_post_meta( $id, '_evento_date', true ) ?: [] );

sort( $date );
$now = current_time( 'Y-m-d\TH:i' );
$prossima = '';
foreach ( $date as $dt ) {
	if ( $dt >= $now ) { $prossima = $dt; break; }
}
if ( ! $prossima && $date ) $prossima = end( $date );

$fmt = static function ( string $dt ): string {
	$ts = strtotime( $dt );
	return $ts ? wp_date( 'j F Y — H:i', $ts ) : $dt;
};

global $calypsosub_booking_manager;
$user_id     = get_current_user_id();
$logged_in   = is_user_logged_in();
$has_booking = false;
$can_book    = false;
$remaining   = null;

if ( $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) {
	$remaining   = $calypsosub_booking_manager->get_remaining_spots( $id );
	$has_booking = $logged_in && $calypsosub_booking_manager->user_has_booking( $id, $user_id );
	$can_book    = $logged_in && calypso_can_book( $id, $user_id );
}
?>
<style>
.cso{color:var(--c-ink,#0b1a26);--radius:4px;--radius-lg:12px}
.cso-hero{position:relative;display:flex;align-items:flex-end;overflow:hidden}
.cso-hero__bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform 6s ease}
.cso-hero__bg--gradient{background:linear-gradient(135deg,var(--c-deep) 0%,#1a5c8a 50%,var(--c-wave) 100%)}
.cso-hero:hover .cso-hero__bg{transform:scale(1.03)}
.cso-hero__overlay{position:absolute;inset:0;background:linear-gradient(rgba(6,24,38,.45) 0%,rgba(6,24,38,.1) 40%,rgba(6,24,38,.85) 100%)}
.cso-hero__content{position:relative;z-index:1;padding:40px 48px;max-width:860px}
@media(max-width:1024px){.cso-hero__content{padding:32px 20px}}
.cso-badge{background:var(--c-wave)}
.cso-hero__title{margin:0 0 8px}
.cso-hero__subtitle{line-height:1.6;color:rgba(255,255,255,.85);margin:8px 0 0}
.cso-infobar{background:var(--c-deep);color:#fff;padding:18px 48px;display:flex;flex-wrap:wrap;gap:28px}
.cso-infobar__pill{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border-radius:20px;padding:6px 14px;font-size:14px}
.cso-infobar__icon{font-size:18px;opacity:.7}
.cso-infobar__label{opacity:.6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;display:block}
.cso-infobar__value{font-weight:600}
.cso-body{max-width:1320px;margin:0 auto;padding:48px 24px;display:grid;grid-template-columns:1fr 360px;gap:48px;align-items:start}
@media(max-width:1024px){.cso-body{grid-template-columns:1fr}}
.cso-section{margin-bottom:40px}
.cso-section__title{font-size:22px;font-weight:700;color:var(--c-deep);text-transform:uppercase;letter-spacing:.04em;margin:0 0 16px;padding-bottom:8px;border-bottom:3px solid var(--c-foam)}
.cso-prose{font-size:16px;line-height:1.75;color:var(--c-ink)}
.cso-prose p{margin:0 0 1em}
.cso-dates-list{list-style:none;margin:0;padding:0}
.cso-dates-list li{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--c-foam);font-size:15px}
.cso-dates-list li:last-child{border-bottom:none}
.cso-dates-list__dot{width:8px;height:8px;background:var(--c-wave);border-radius:50%;flex-shrink:0}
.cso-card{background:#fff;border-radius:var(--radius-lg);box-shadow:0 4px 24px rgba(10,37,64,.1);overflow:hidden;position:sticky;top:24px}
.cso-card__head{background:var(--c-wave);color:#fff;padding:24px}
.cso-card__head-title{font-size:20px;font-weight:700;text-transform:uppercase;margin:0 0 4px}
.cso-card__head-sub{font-size:13px;opacity:.7;margin:0}
.cso-card__body{padding:24px}
.cso-spots{text-align:center;margin-bottom:20px}
.cso-spots__num{font-size:48px;font-weight:900;color:var(--c-wave);line-height:1}
.cso-spots__label{font-size:13px;color:#666;margin-top:4px}
.cso-form-row{margin-bottom:16px}
.cso-form-row label{display:block;font-size:13px;font-weight:600;color:var(--c-deep);margin-bottom:6px}
.cso-form-row input,.cso-form-row textarea{width:100%;padding:10px 14px;border:1.5px solid var(--c-foam);border-radius:var(--radius);font-size:14px;color:var(--c-ink);transition:border-color .2s}
.cso-form-row input:focus,.cso-form-row textarea:focus{outline:none;border-color:var(--c-wave)}
.cso-form-row textarea{min-height:80px;resize:vertical}
.cso-btn{display:block;width:100%;padding:14px;background:var(--c-wave);color:#fff;border:none;border-radius:999px;font-size:18px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;cursor:pointer;transition:background .2s,transform .15s}
.cso-btn:hover{background:#165c82;transform:translateY(-1px)}
.cso-btn:disabled{background:#ccc;cursor:not-allowed;transform:none}
.cso-btn--secondary{background:var(--c-deep)}
.cso-btn--secondary:hover{background:#061826}
.cso-notice{text-align:center;padding:16px;border-radius:var(--radius);font-size:14px;margin-bottom:16px}
.cso-notice--success{background:#d4edda;color:#155724}
.cso-notice--waitlist{background:#d1ecf1;color:#0c5460}
.cso-notice--error{background:#f8d7da;color:#721c24}
.cso-login-cta{text-align:center;padding:24px}
.cso-login-cta p{margin:0 0 16px;font-size:14px;color:#666}
#cso-booking-msg{display:none;margin-bottom:16px}
</style>

<div class="cso">

<section class="cso-hero">
	<?php if ( has_post_thumbnail( $id ) ) : ?>
	<div class="cso-hero__bg" style="background-image:url(<?php echo esc_url( get_the_post_thumbnail_url( $id, 'full' ) ); ?>)"></div>
	<?php else : ?>
	<div class="cso-hero__bg cso-hero__bg--gradient"></div>
	<?php endif; ?>
	<div class="cso-hero__overlay"></div>
	<div class="cso-hero__content">
		<span class="cso-badge"><?php echo esc_html( calypsosub_opt( 'eventi', 'badge', __( 'Evento', 'calypsosub' ) ) ); ?></span>
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
			<span class="cso-infobar__label"><?php esc_html_e( 'Data', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value"><?php echo esc_html( $fmt( $prossima ) ); ?></span>
		</div>
	</div>
	<?php endif; ?>
	<?php if ( $remaining !== null ) : ?>
	<div class="cso-infobar__pill">
		<span class="cso-infobar__icon">👥</span>
		<div>
			<span class="cso-infobar__label"><?php esc_html_e( 'Posti', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value">
				<?php
				if ( $remaining > 0 ) {
					echo esc_html( sprintf( __( '%d disponibili', 'calypsosub' ), $remaining ) );
				} elseif ( $lista_attesa ) {
					esc_html_e( 'Lista d\'attesa', 'calypsosub' );
				} else {
					esc_html_e( 'Esauriti', 'calypsosub' );
				}
				?>
			</span>
		</div>
	</div>
	<?php endif; ?>
</div>

<div class="cso-body">
	<div class="cso-main">
		<?php if ( get_the_content() ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_descrizione', __( 'Descrizione', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-prose"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $date ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php echo esc_html( calypsosub_opt( 'eventi', 'sec_date', __( 'Date', 'calypsosub' ) ) ); ?></h2>
			<ul class="cso-dates-list">
				<?php foreach ( $date as $dt ) : ?>
				<li><span class="cso-dates-list__dot"></span><?php echo esc_html( $fmt( $dt ) ); ?></li>
				<?php endforeach; ?>
			</ul>
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
			</div>
			<div class="cso-card__body">

				<?php if ( $has_booking ) : ?>
				<div class="cso-notice cso-notice--success"><?php echo esc_html( calypsosub_opt( 'eventi', 'msg_gia_iscritto', __( '✓ Sei già iscritto a questo evento.', 'calypsosub' ) ) ); ?></div>
				<a href="<?php echo esc_url( get_permalink( get_option( 'calypsosub_account_page_id' ) ) ); ?>" class="cso-btn cso-btn--secondary"><?php echo esc_html( calypsosub_opt( 'eventi', 'btn_area_personale', __( 'Area personale', 'calypsosub' ) ) ); ?></a>

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

				<div id="cso-booking-msg" class="cso-notice"></div>
				<form id="cso-booking-form">
					<div class="cso-form-row">
						<label for="cso-allergie"><?php echo esc_html( calypsosub_opt( 'eventi', 'label_allergie', __( 'Allergie / note', 'calypsosub' ) ) ); ?></label>
						<textarea id="cso-allergie" name="allergie" placeholder="<?php esc_attr_e( 'Facoltativo', 'calypsosub' ); ?>"></textarea>
					</div>
					<button type="submit" class="cso-btn" id="cso-book-btn"><?php echo esc_html( calypsosub_opt( 'eventi', 'btn_iscriviti', __( 'Iscriviti', 'calypsosub' ) ) ); ?></button>
				</form>

				<?php else : ?>
				<div class="cso-notice cso-notice--error"><?php echo esc_html( calypsosub_opt( 'eventi', 'msg_esauriti', __( 'Posti esauriti.', 'calypsosub' ) ) ); ?></div>
				<?php endif; ?>

			</div>
		</div>
	</aside>
</div>
</div>

<script>
(function () {
	var form = document.getElementById('cso-booking-form');
	if (!form) return;
	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var btn = document.getElementById('cso-book-btn');
		var msg = document.getElementById('cso-booking-msg');
		btn.disabled = true;
		btn.textContent = '<?php echo esc_js( __( 'Invio…', 'calypsosub' ) ); ?>';
		var data = new FormData();
		data.append('action', 'calypso_book');
		data.append('nonce', '<?php echo esc_js( wp_create_nonce( 'calypso_book_nonce' ) ); ?>');
		data.append('post_id', '<?php echo esc_js( (string) $id ); ?>');
		data.append('accompagnatori', '0');
		data.append('allergie', form.querySelector('[name="allergie"]').value);
		fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: data })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				msg.style.display = 'block';
				if (res.success) {
					msg.className = 'cso-notice ' + (res.data.status === 'lista_attesa' ? 'cso-notice--waitlist' : 'cso-notice--success');
					msg.textContent = res.data.message;
					form.style.display = 'none';
				} else {
					msg.className = 'cso-notice cso-notice--error';
					msg.textContent = res.data.message;
					btn.disabled = false;
					btn.textContent = '<?php echo esc_js( __( 'Iscriviti', 'calypsosub' ) ); ?>';
				}
			});
	});
})();
</script>

<?php get_footer(); ?>
