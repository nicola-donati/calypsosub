<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/hero-home — Hero configurabile per la home
 *
 * Attributi:
 *   image_id        (integer)  — attachment ID immagine sfondo
 *   eyebrow         (string)   — testo sopra il titolo
 *   eyebrow_wave    (boolean)  — mostra icona onda
 *   title           (string)   — titolo principale (usa \n per a capo)
 *   title_em        (string)   — parte in evidenza (aqua + corsivo) in fondo al titolo
 *   description     (string)   — paragrafo descrittivo
 *   btn1_text       (string)   — testo bottone primario
 *   btn1_url        (string)   — URL bottone primario
 *   btn2_text       (string)   — testo bottone secondario
 *   btn2_url        (string)   — URL bottone secondario
 *   show_uscita     (boolean)  — mostra card prossima uscita (solo desktop)
 *   marquee_on      (boolean)  — mostra ticker luoghi sotto hero
 *   marquee_items   (string)   — luoghi separati da virgola
 *   marquee_mobile  (boolean)  — mostra ticker anche su mobile
 */

$attr = $attributes ?? [];

$image_id      = (int)    ( $attr['image_id']     ?? 0 );
$eyebrow       = (string) ( $attr['eyebrow']      ?? __( 'La subacquea ad Arezzo dal 1978', 'calypsosub' ) );
$eyebrow_wave  = (bool)   ( $attr['eyebrow_wave'] ?? true );
$title         = (string) ( $attr['title']        ?? "Sotto la superficie\nc'è un" );
$title_em      = (string) ( $attr['title_em']     ?? __( 'altro mondo.', 'calypsosub' ) );
$description   = (string) ( $attr['description']  ?? __( "Calypso Sub è il club di chi crede che il mare non si visiti, si abiti. Quasi cinquant'anni di immersioni, corsi e amicizie in profondità.", 'calypsosub' ) );
$btn1_text     = (string) ( $attr['btn1_text']    ?? __( 'Diventa socio', 'calypsosub' ) );
$btn1_url      = (string) ( $attr['btn1_url']     ?? '' );
$btn2_text     = (string) ( $attr['btn2_text']    ?? __( 'Guarda il video', 'calypsosub' ) );
$btn2_url      = (string) ( $attr['btn2_url']     ?? '' );
$show_uscita   = (bool)   ( $attr['show_uscita']  ?? true );
$marquee_on    = (bool)   ( $attr['marquee_on']   ?? true );
$marquee_items = (string) ( $attr['marquee_items'] ?? 'Argentario,Elba,Giglio,Giannutri,Croazia,Egadi' );
$marquee_mob   = (bool)   ( $attr['marquee_mobile'] ?? false );

/* ── Immagine sfondo ── */
$bg_url = '';
if ( $image_id ) {
	$bg_url = wp_get_attachment_url( $image_id ) ?: '';
}

/* ── Titolo: \n → <br> ── */
$title_html = implode( '<br>', array_map( 'esc_html', explode( "\n", $title ) ) );

/* ── Prossima uscita ── */
$pu = null;
if ( $show_uscita ) {
	$today = current_time( 'Y-m-d' );
	foreach ( get_posts( [ 'post_type' => 'calypso_uscita', 'posts_per_page' => -1, 'post_status' => 'publish' ] ) as $u ) {
		$dates = array_filter( (array) ( get_post_meta( $u->ID, '_uscita_date', true ) ?: [] ) );
		sort( $dates );
		if ( empty( $dates ) ) continue;
		$prima = substr( $dates[0], 0, 10 );
		if ( $prima >= $today && ( ! $pu || $prima < $pu->_prima ) ) {
			$u->_prima = $prima;
			$pu        = $u;
		}
	}
	if ( $pu ) {
		$pid           = $pu->ID;
		$pu_luogo      = (string) get_post_meta( $pid, '_uscita_luogo', true );
		$pu_nimm       = (int)    get_post_meta( $pid, '_uscita_n_immersioni', true );
		$pu_max        = get_post_meta( $pid, '_uscita_max_partecipanti', true );
		$pu_link       = get_permalink( $pid );
		$ts            = strtotime( $pu->_prima );
		$pu_set        = strtoupper( date_i18n( 'D', $ts ) );
		$pu_num        = date_i18n( 'j', $ts );
		$pu_mes        = strtoupper( date_i18n( 'M', $ts ) );
		$pu_meta_parts = array_filter( [
			$pu_luogo,
			$pu_nimm ? $pu_nimm . ' ' . _n( 'immersione', 'immersioni', $pu_nimm, 'calypsosub' ) : '',
		] );
		$pu_meta       = implode( ' · ', $pu_meta_parts );
		$pu_warn       = false;
		$pu_posti      = '';
		if ( $pu_max !== '' && $pu_max !== false ) {
			$bm      = $GLOBALS['calypsosub_booking_manager'] ?? null;
			$conf    = $bm ? $bm->count_confirmed( $pid ) : 0;
			$liberi  = max( 0, (int) $pu_max - $conf );
			if ( $liberi === 0 ) {
				$pu_posti = __( 'Sold out', 'calypsosub' ); $pu_warn = true;
			} elseif ( $liberi <= 3 ) {
				$pu_posti = '● ' . $liberi . ' ' . _n( 'posto', 'posti', $liberi, 'calypsosub' ); $pu_warn = true;
			} else {
				$pu_posti = $liberi . ' / ' . (int) $pu_max . ' ' . __( 'posti', 'calypsosub' );
			}
		}
	}
}

/* ── Marquee ── */
$places  = array_values( array_filter( array_map( 'trim', explode( ',', $marquee_items ) ) ) );
$mq_dur  = max( 12, count( $places ) * 3 ); /* 3s per voce */
?>
<?php
/* ── Header sovrascritto (come nei template single) ── */
if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}
?>
<style>
/* ── Hero Home ─────────────────────────────────────────────── */
/* Full-bleed: sfugge dal padding del content container della pagina */
.csh-hero,.csh-marquee{
  margin-left:calc(-50vw + 50%);
  margin-right:calc(-50vw + 50%);
}
.csh-hero{position:relative;height:1020px;color:#fff;overflow:hidden}
.csh-hero__bg{position:absolute;inset:0;background:var(--c-abyss,#061826);overflow:hidden}
.csh-hero__bg img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}
.csh-hero__overlay{
  position:absolute;inset:0;
  background:linear-gradient(rgba(6,24,38,.55) 0%,rgba(6,24,38,.15) 40%,rgba(6,24,38,.85) 100%);
}
.csh-hero__content{
  position:absolute;left:48px;right:48px;bottom:80px;
  display:flex;align-items:flex-end;justify-content:space-between;gap:60px;
}
.csh-hero__left{max-width:820px;flex:1;min-width:0}
.csh-hero__eyebrow{
  color:var(--c-aqua,#26CBFB);display:flex;align-items:center;gap:10px;
  font-size:14px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;
}
.csh-hero__title{
  font-size:clamp(64px,8vw,108px);
  font-family:var(--f-display,'Big Shoulders Display',Impact,sans-serif);
  font-weight:900;line-height:.92;letter-spacing:-.02em;
  margin:20px 0 8px;color:#fff;
  text-shadow:0 2px 20px rgba(0,0,0,.4);
}
.csh-hero__title em{font-style:normal;color:var(--c-aqua,#26CBFB)}
.csh-hero__desc{
  font-size:19px;line-height:1.5;max-width:540px;
  opacity:.92;margin-top:28px;color:#fff;
  text-shadow:0 1px 6px rgba(0,0,0,.3);
}
.csh-hero__btns{display:flex;gap:12px;margin-top:36px;flex-wrap:wrap}
.csh-btn-primary{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 22px;background:var(--c-coral,#ff6b4a);color:#fff;
  font-size:15px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
  border-radius:999px;text-decoration:none;border:none;cursor:pointer;
  transition:background .15s;box-shadow:0 6px 18px -4px rgba(255,107,74,.55);
}
.csh-btn-primary:hover{background:#e04a2a;color:#fff}
.csh-btn-ghost{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 20px;background:rgba(255,255,255,.1);color:#fff;
  font-size:15px;font-weight:600;border-radius:999px;text-decoration:none;
  border:1px solid rgba(255,255,255,.25);cursor:pointer;
  transition:background .15s;backdrop-filter:blur(4px);
}
.csh-btn-ghost:hover{background:rgba(255,255,255,.18);color:#fff}

/* Scroll indicator */
.csh-hero__scroll{
  position:absolute;bottom:24px;left:50%;transform:translateX(-50%);
  color:rgba(255,255,255,.7);font-size:11px;
  font-family:var(--f-mono,monospace);letter-spacing:.18em;
  display:flex;flex-direction:column;align-items:center;gap:8px;
  text-decoration:none;pointer-events:none;
}

/* Card prossima uscita */
.csh-pu{
  flex:0 0 auto;width:280px;
  background:rgba(255,255,255,.08);
  backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
  border:1px solid rgba(255,255,255,.18);
  border-radius:14px;padding:20px;color:#fff;
}
.csh-pu__head{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.csh-pu__dot{
  width:8px;height:8px;border-radius:50%;flex:0 0 auto;
  background:#54e09a;box-shadow:0 0 0 4px rgba(84,224,154,.25);
}
.csh-pu__eyebrow{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.7)}
.csh-pu__date{
  font-size:38px;font-weight:900;line-height:.95;letter-spacing:-.02em;
  font-family:var(--f-display,'Big Shoulders Display',Impact,sans-serif);color:#fff;
}
.csh-pu__meta{font-size:13px;opacity:.85;margin-top:12px;line-height:1.5;color:#fff}
.csh-pu__posti{margin-top:14px;font-size:12px;font-family:var(--f-mono,monospace);opacity:.7;color:#fff}
.csh-pu__posti--warn{opacity:1;color:#ff6b4a}
.csh-pu__cta{
  margin-top:14px;display:inline-flex;align-items:center;gap:6px;
  color:var(--c-aqua,#26CBFB);font-size:13px;font-weight:600;text-decoration:none;
}
.csh-pu__cta:hover{opacity:.8}

/* ── Marquee ────────────────────────────────────────────────── */
.csh-marquee{
  background:var(--c-deep,#0a2540);color:#fff;
  padding:20px 0;overflow:hidden;
  border-top:1px solid rgba(255,255,255,.06);
  margin:0!important;
}
.csh-hero{margin:0!important}
.csh-marquee__track{
  display:flex;gap:56px;white-space:nowrap;
  font-family:var(--f-display,'Big Shoulders Display',Impact,sans-serif);
  font-size:28px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
  animation:csh-scroll <?php echo (int) $mq_dur; ?>s linear infinite;
  will-change:transform;
}
@keyframes csh-scroll{
  from{transform:translateX(0)}
  to{transform:translateX(-50%)}
}
.csh-marquee__sep{color:var(--c-coral,#ff6b4a)}

/* ── Mobile (≤1024px) ───────────────────────────────────────── */
@media(max-width:1024px){
  .csh-hero{height:720px}
  .csh-hero__content{
    left:20px;right:20px;bottom:32px;
    flex-direction:column;align-items:flex-start;gap:0;
  }
  .csh-hero__title{font-size:clamp(48px,13vw,72px);margin:14px 0 0}
  .csh-hero__desc{font-size:15px;margin-top:18px}
  .csh-hero__btns{flex-direction:column;align-items:stretch;margin-top:24px}
  .csh-btn-primary,.csh-btn-ghost{justify-content:center}
  .csh-pu{display:none}
  .csh-hero__eyebrow{font-size:11px}
  .csh-hero__scroll{display:none}
  <?php if ( ! $marquee_mob ) : ?>.csh-marquee{display:none}<?php endif; ?>
}
</style>

<!-- ── HERO ── -->
<section class="csh-hero">

  <div class="csh-hero__bg">
    <?php if ( $bg_url ) : ?>
      <img src="<?php echo esc_url( $bg_url ); ?>" alt="" loading="eager" fetchpriority="high">
    <?php endif; ?>
  </div>
  <div class="csh-hero__overlay"></div>

  <div class="csh-hero__content">
    <!-- Sinistra: testo + CTA -->
    <div class="csh-hero__left">
      <?php if ( $eyebrow ) : ?>
      <div class="csh-hero__eyebrow">
        <?php if ( $eyebrow_wave ) : ?>
          <svg width="20" height="12" viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
            <path d="M1 6 C 4 1, 8 11, 12 6 S 20 1, 23 6"/>
          </svg>
        <?php endif; ?>
        <?php echo esc_html( $eyebrow ); ?>
      </div>
      <?php endif; ?>

      <h1 class="csh-hero__title">
        <?php echo $title_html; ?>
        <?php if ( $title_em ) : ?>
          <?php echo $title ? ' ' : ''; ?><em><?php echo esc_html( $title_em ); ?></em>
        <?php endif; ?>
      </h1>

      <?php if ( $description ) : ?>
      <p class="csh-hero__desc"><?php echo esc_html( $description ); ?></p>
      <?php endif; ?>

      <?php if ( $btn1_text || $btn2_text ) : ?>
      <div class="csh-hero__btns">
        <?php if ( $btn1_text ) : ?>
          <a href="<?php echo esc_url( $btn1_url ?: '#' ); ?>" class="csh-btn-primary">
            <?php echo esc_html( $btn1_text ); ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        <?php endif; ?>
        <?php if ( $btn2_text ) : ?>
          <a href="<?php echo esc_url( $btn2_url ?: '#' ); ?>" class="csh-btn-ghost">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            <?php echo esc_html( $btn2_text ); ?>
          </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Destra: card prossima uscita (solo desktop) -->
    <?php if ( $show_uscita && $pu ) : ?>
    <div class="csh-pu">
      <div class="csh-pu__head">
        <span class="csh-pu__dot"></span>
        <span class="csh-pu__eyebrow"><?php _e( 'Prossima uscita', 'calypsosub' ); ?></span>
      </div>
      <div class="csh-pu__date">
        <?php echo esc_html( $pu_set ); ?><br>
        <?php echo esc_html( $pu_num . ' ' . $pu_mes ); ?>
      </div>
      <?php if ( $pu_meta ) : ?>
        <div class="csh-pu__meta">
          <?php echo nl2br( esc_html( $pu->post_title . "\n" . $pu_meta ) ); ?>
        </div>
      <?php endif; ?>
      <?php if ( $pu_posti ) : ?>
        <div class="csh-pu__posti<?php echo $pu_warn ? ' csh-pu__posti--warn' : ''; ?>">
          <?php echo esc_html( $pu_posti ); ?>
        </div>
      <?php endif; ?>
      <a href="<?php echo esc_url( $pu_link ); ?>" class="csh-pu__cta">
        <?php _e( 'Prenota →', 'calypsosub' ); ?>
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Scroll indicator -->
  <div class="csh-hero__scroll" aria-hidden="true">
    SCORRI
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
  </div>

</section>

<?php if ( $marquee_on && ! empty( $places ) ) : ?>
<!-- ── MARQUEE ── -->
<section class="csh-marquee" aria-hidden="true">
  <div class="csh-marquee__track">
    <?php
    /* Duplica per loop seamless (trackWidth × 2 = animazione -50%) */
    $all = array_merge( $places, $places );
    foreach ( $all as $i => $p ) :
    ?>
      <?php if ( $i > 0 ) : ?><span class="csh-marquee__sep">★</span><?php endif; ?>
      <span><?php echo esc_html( $p ); ?></span>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
