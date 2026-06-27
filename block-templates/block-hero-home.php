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
 *
 *   text_color, overlay_color                                    — colori sfondo/testo generali
 *   eyebrow_color, eyebrow_size, eyebrow_weight                   — stile eyebrow
 *   title_color, title_em_color, title_size, title_weight, title_font — stile titolo
 *   desc_color, desc_opacity, desc_size, desc_font                — stile descrizione
 *   btn1_bg, btn1_color, btn1_hover_bg, btn1_size, btn1_weight     — stile bottone primario
 *   btn2_bg, btn2_hover_bg, btn2_border, btn2_color, btn2_size, btn2_weight — stile bottone secondario
 *   scroll_color                                                  — colore indicatore scroll
 *   pu_bg, pu_border, pu_dot_color, pu_text_color,
 *   pu_accent_color, pu_warn_color                                — stile card prossima uscita
 *   marquee_bg, marquee_color, marquee_size, marquee_weight,
 *   marquee_sep_color                                             — stile ticker
 */

if ( ! function_exists( 'csh_css_raw' ) ) {
	/* Sanitizza valori CSS liberi (colori/rgba) per uso sicuro dentro style="" e <style> */
	function csh_css_raw( $v ) {
		return preg_replace( '/[^#a-zA-Z0-9.,()%\s\-]/', '', (string) $v );
	}
}
if ( ! function_exists( 'csh_hex2rgba' ) ) {
	function csh_hex2rgba( $hex, $alpha ) {
		$hex = trim( (string) $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
			$hex = '061826';
		}
		list( $r, $g, $b ) = array_map( function ( $c ) { return hexdec( $c ); }, [ substr( $hex, 0, 2 ), substr( $hex, 2, 2 ), substr( $hex, 4, 2 ) ] );
		return "rgba($r,$g,$b,$alpha)";
	}
}

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

/* ── Stile configurabile ── */
$text_color      = csh_css_raw( $attr['text_color']      ?? '#ffffff' );
$overlay_color   = csh_css_raw( $attr['overlay_color']   ?? '#061826' );

$eyebrow_color   = csh_css_raw( $attr['eyebrow_color']   ?? '#26CBFB' );
$eyebrow_size    = (int)        ( $attr['eyebrow_size']    ?? 14 );
$eyebrow_weight  = (int)        ( $attr['eyebrow_weight']  ?? 600 );

$title_color     = csh_css_raw( $attr['title_color']     ?? '#ffffff' );
$title_em_color  = csh_css_raw( $attr['title_em_color']  ?? '#26CBFB' );
$title_size      = (int)        ( $attr['title_size']      ?? 108 );
$title_weight    = (int)        ( $attr['title_weight']    ?? 700 );
$title_font      = preg_replace( '/[^a-zA-Z0-9 ,\"\'\-]/', '', (string) ( $attr['title_font'] ?? '' ) );

$desc_color      = csh_css_raw( $attr['desc_color']      ?? '#ffffff' );
$desc_opacity    = (int)        ( $attr['desc_opacity']    ?? 92 );
$desc_size       = (int)        ( $attr['desc_size']       ?? 0 );
$desc_font       = preg_replace( '/[^a-zA-Z0-9 ,\"\'\-]/', '', (string) ( $attr['desc_font']  ?? '' ) );

$btn1_bg         = csh_css_raw( $attr['btn1_bg']         ?? '#ff6b4a' );
$btn1_color      = csh_css_raw( $attr['btn1_color']      ?? '#ffffff' );
$btn1_hover_bg   = csh_css_raw( $attr['btn1_hover_bg']   ?? '#e04a2a' );
$btn1_size       = (int)        ( $attr['btn1_size']       ?? 15 );
$btn1_weight     = (int)        ( $attr['btn1_weight']     ?? 700 );

$btn2_bg         = csh_css_raw( $attr['btn2_bg']         ?? 'rgba(255,255,255,.1)' );
$btn2_hover_bg   = csh_css_raw( $attr['btn2_hover_bg']   ?? 'rgba(255,255,255,.18)' );
$btn2_border     = csh_css_raw( $attr['btn2_border']     ?? 'rgba(255,255,255,.25)' );
$btn2_color      = csh_css_raw( $attr['btn2_color']      ?? '#ffffff' );
$btn2_size       = (int)        ( $attr['btn2_size']       ?? 15 );
$btn2_weight     = (int)        ( $attr['btn2_weight']     ?? 600 );

$scroll_color    = csh_css_raw( $attr['scroll_color']    ?? 'rgba(255,255,255,.7)' );

$pu_bg           = csh_css_raw( $attr['pu_bg']           ?? 'rgba(255,255,255,.08)' );
$pu_border       = csh_css_raw( $attr['pu_border']       ?? 'rgba(255,255,255,.18)' );
$pu_dot_color    = csh_css_raw( $attr['pu_dot_color']    ?? '#54e09a' );
$pu_text_color   = csh_css_raw( $attr['pu_text_color']   ?? '#ffffff' );
$pu_accent_color = csh_css_raw( $attr['pu_accent_color'] ?? '#26CBFB' );
$pu_warn_color   = csh_css_raw( $attr['pu_warn_color']   ?? '#ff6b4a' );
$pu_dot_glow     = csh_hex2rgba( $pu_dot_color, .25 );

$marquee_bg        = csh_css_raw( $attr['marquee_bg']        ?? '#0a2540' );
$marquee_color     = csh_css_raw( $attr['marquee_color']     ?? '#ffffff' );
$marquee_size      = (int)        ( $attr['marquee_size']      ?? 28 );
$marquee_weight    = (int)        ( $attr['marquee_weight']    ?? 700 );
$marquee_sep_color = csh_css_raw( $attr['marquee_sep_color'] ?? '#ff6b4a' );

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
	$occs = get_posts( [
		'post_type'      => 'calypso_occ_uscita',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'meta_key'       => '_occorrenza_uscita_data',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => [ [ 'key' => '_occorrenza_uscita_data', 'value' => current_time( 'Y-m-d\TH:i' ), 'compare' => '>=' ] ],
	] );
	$pu = $occs ? $occs[0] : null;
	if ( $pu ) {
		$pu->_data = (string) get_post_meta( $pu->ID, '_occorrenza_uscita_data', true );
		$pid       = (int) get_post_meta( $pu->ID, '_occorrenza_uscita_uscita_id', true );
		if ( ! $pu->_data || ! $pid ) {
			$pu = null;
		}
	}
	if ( $pu ) {
		$pu_luogo      = (string) get_post_meta( $pid, '_uscita_luogo', true );
		$pu_nimm       = (int)    get_post_meta( $pid, '_uscita_n_immersioni', true );
		$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );
		$pu_link       = $prenotazioni_page_id
			? add_query_arg( 'prenota_id', $pu->ID, get_permalink( $prenotazioni_page_id ) )
			: get_permalink( $pid );
		$ts            = strtotime( $pu->_data );
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
		$bm            = $GLOBALS['calypsosub_booking_manager'] ?? null;
		$liberi        = $bm instanceof Calypsosub_Booking_Manager ? $bm->get_remaining_spots( $pu->ID ) : null;
		if ( $liberi !== null ) {
			if ( $liberi === 0 ) {
				$pu_posti = __( 'Sold out', 'calypsosub' ); $pu_warn = true;
			} elseif ( $liberi <= 3 ) {
				$pu_posti = '● ' . $liberi . ' ' . _n( 'posto', 'posti', $liberi, 'calypsosub' ); $pu_warn = true;
			} else {
				$pu_posti = $liberi . ' ' . __( 'posti', 'calypsosub' );
			}
		}
	}
}

/* ── Marquee ── */
$places  = array_values( array_filter( array_map( 'trim', explode( ',', $marquee_items ) ) ) );
$mq_dur  = max( 12, count( $places ) * 3 ); /* 3s per voce */
?>
<style>
/* ── Hero Home ─────────────────────────────────────────────── */
/* Full-bleed: sfugge dal padding del content container */
.csh-hero,.csh-marquee{
  margin-left:calc(-50vw + 50%);
  margin-right:calc(-50vw + 50%);
}
/* Altezza hero-home: totale meno il marquee (72px) — segue i breakpoint globali */
.csh-hero{position:relative;color:<?php echo $text_color; ?>;overflow:hidden;height:calc(800px - 72px);box-sizing:border-box}
.csh-marquee{height:72px;box-sizing:border-box;overflow:hidden}
@media(max-width:1024px){.csh-hero{height:calc(720px - 72px)}}
@media(max-width:768px){.csh-hero{height:calc(640px - 72px)}}
@media(max-width:480px){.csh-hero{height:calc(600px - 72px)}}

/*
 * Quando l'hero home è nella pagina, il site header FSE
 * diventa absolute (fluttua sopra l'hero, non occupa spazio).
 * :has() è supportato da tutti i browser moderni.
 */
body:has(.csh-hero) .wp-site-blocks>header,
body:has(.csh-hero) .wp-site-blocks>.wp-block-template-part:first-child>header{
  position:absolute;
  top:var(--wp-admin--admin-bar--height,0px);
  left:0;right:0;z-index:200;width:100%;
}
/* Azzera block gap WP tra i blocchi della pagina home */
body:has(.csh-hero) .wp-block-post-content>*,
body:has(.csh-hero) .entry-content>*{margin-top:0!important;margin-block-start:0!important}
.csh-hero__bg{position:absolute;inset:0;background:<?php echo $overlay_color; ?>;overflow:hidden}
.csh-hero__bg img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}
.csh-hero__overlay{
  position:absolute;inset:0;
  background:linear-gradient(<?php echo csh_hex2rgba( $overlay_color, .55 ); ?> 0%,<?php echo csh_hex2rgba( $overlay_color, .15 ); ?> 40%,<?php echo csh_hex2rgba( $overlay_color, .85 ); ?> 100%);
}
.csh-hero__content{
  position:absolute;left:48px;right:48px;bottom:80px;
  display:flex;align-items:flex-end;justify-content:space-between;gap:60px;
}
.csh-hero__left{max-width:820px;flex:1;min-width:0}
.csh-hero__eyebrow{
  color:<?php echo $eyebrow_color; ?>;display:flex;align-items:center;gap:10px;
  font-size:<?php echo $eyebrow_size; ?>px;font-weight:<?php echo $eyebrow_weight; ?>;letter-spacing:.08em;text-transform:uppercase;
}
.csh-hero__title{
  color:<?php echo $title_color; ?>;
  font-size:clamp(48px,7vw,<?php echo $title_size; ?>px);
  font-weight:<?php echo $title_weight; ?>;
  <?php if ( $title_font ) : ?>font-family:<?php echo $title_font; ?>;<?php endif; ?>
  margin-top:20px;
}
.csh-hero__title em{font-style:normal;color:<?php echo $title_em_color; ?>}
.csh-hero__desc{
  max-width:540px;color:<?php echo $desc_color; ?>;
  <?php if ( $desc_size ) : ?>font-size:<?php echo $desc_size; ?>px;<?php endif; ?>
  <?php if ( $desc_font ) : ?>font-family:<?php echo $desc_font; ?>;<?php endif; ?>
  opacity:<?php echo $desc_opacity / 100; ?>;margin-top:28px;
}
.csh-hero__btns{display:flex;gap:12px;margin-top:36px;flex-wrap:wrap}
.csh-btn-primary{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 22px;background:<?php echo $btn1_bg; ?>;color:<?php echo $btn1_color; ?>;
  font-size:<?php echo $btn1_size; ?>px;font-weight:<?php echo $btn1_weight; ?>;letter-spacing:.04em;text-transform:uppercase;
  border-radius:999px;text-decoration:none;border:none;cursor:pointer;
  transition:background .15s;box-shadow:0 6px 18px -4px rgba(255,107,74,.55);
}
.csh-btn-primary:hover{background:<?php echo $btn1_hover_bg; ?>;color:<?php echo $btn1_color; ?>}
.csh-btn-ghost{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 20px;background:<?php echo $btn2_bg; ?>;color:<?php echo $btn2_color; ?>;
  font-size:<?php echo $btn2_size; ?>px;font-weight:<?php echo $btn2_weight; ?>;border-radius:999px;text-decoration:none;
  border:1px solid <?php echo $btn2_border; ?>;cursor:pointer;
  transition:background .15s;backdrop-filter:blur(4px);
}
.csh-btn-ghost:hover{background:<?php echo $btn2_hover_bg; ?>;color:<?php echo $btn2_color; ?>}

/* Scroll indicator */
.csh-hero__scroll{
  position:absolute;bottom:24px;left:50%;
  color:<?php echo $scroll_color; ?>;font-size:14px;
  letter-spacing:.18em;text-transform:uppercase;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  pointer-events:none;
  animation:cso-scroll-bounce 2s ease-in-out infinite;
}

/* Card prossima uscita */
.csh-pu{
  flex:0 0 auto;width:280px;
  background:<?php echo $pu_bg; ?>;
  backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
  border:1px solid <?php echo $pu_border; ?>;
  border-radius:14px;padding:20px;color:<?php echo $pu_text_color; ?>;
}
.csh-pu__head{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.csh-pu__dot{
  width:8px;height:8px;border-radius:50%;flex:0 0 auto;
  background:<?php echo $pu_dot_color; ?>;box-shadow:0 0 0 4px <?php echo $pu_dot_glow; ?>;
}
.csh-pu__eyebrow{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:<?php echo $pu_text_color; ?>;opacity:.7}
.csh-pu__date{
  font-size:38px;font-weight:900;line-height:.95;letter-spacing:-.02em;color:<?php echo $pu_text_color; ?>;
}
.csh-pu__meta{font-size:13px;opacity:.85;margin-top:12px;line-height:1.5;color:<?php echo $pu_text_color; ?>}
.csh-pu__posti{margin-top:14px;font-size:12px;opacity:.7;color:<?php echo $pu_text_color; ?>}
.csh-pu__posti--warn{opacity:1;color:<?php echo $pu_warn_color; ?>}
.csh-pu__cta{
  margin-top:14px;display:inline-flex;align-items:center;gap:6px;
  color:<?php echo $pu_accent_color; ?>;font-size:13px;font-weight:600;text-decoration:none;
}
.csh-pu__cta:hover{opacity:.8}

/* ── Marquee ────────────────────────────────────────────────── */
.csh-marquee{
  background:<?php echo $marquee_bg; ?>;color:<?php echo $marquee_color; ?>;
  display:flex;align-items:center;
  border-top:1px solid rgba(255,255,255,.06);
  margin:0!important;
}
.csh-hero{margin:0!important}
.csh-marquee__track{
  display:flex;gap:56px;white-space:nowrap;
  font-size:<?php echo $marquee_size; ?>px;font-weight:<?php echo $marquee_weight; ?>;letter-spacing:.04em;text-transform:uppercase;
  animation:csh-scroll <?php echo (int) $mq_dur; ?>s linear infinite;
  will-change:transform;
}
@keyframes csh-scroll{
  from{transform:translateX(0)}
  to{transform:translateX(-50%)}
}
.csh-marquee__sep{color:<?php echo $marquee_sep_color; ?>}

/* ── Mobile (≤1024px) ───────────────────────────────────────── */
@media(max-width:1024px){
  .csh-hero__content{
    left:20px;right:20px;bottom:32px;
    flex-direction:column;align-items:flex-start;gap:0;
  }
  .csh-hero__title{font-size:clamp(48px,13vw,72px);margin:14px 0 0}
  .csh-hero__desc{font-size:<?php echo $desc_size ?: 15; ?>px;margin-top:18px}
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
          <?php echo nl2br( esc_html( get_the_title( $pid ) . "\n" . $pu_meta ) ); ?>
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
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
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
