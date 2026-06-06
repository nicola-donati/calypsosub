<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block LazyBlocks — Prossima uscita
 *
 * Attributi LazyBlocks:
 *   inside_hero  (toggle, default: false)
 *     true  → card floating glassmorphism (solo desktop ≥1024px, dentro hero)
 *     false → strip section orizzontale  (solo mobile <1024px, fuori hero)
 */

$inside_hero = ! empty( $attributes['inside_hero'] );

/* ── Trova la prossima uscita futura ── */
$today = current_time( 'Y-m-d' );
$raw   = get_posts( [
	'post_type'      => 'calypso_uscita',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
] );

$prossima = null;
foreach ( $raw as $u ) {
	$date_meta = get_post_meta( $u->ID, '_uscita_date', true );
	$dates     = is_array( $date_meta ) ? array_filter( $date_meta ) : [];
	sort( $dates );
	if ( empty( $dates ) ) continue;
	$prima = substr( $dates[0], 0, 10 );
	if ( $prima >= $today ) {
		if ( ! $prossima || $prima < $prossima->_prima ) {
			$u->_prima = $prima;
			$prossima  = $u;
		}
	}
}

if ( ! $prossima ) return;

/* ── Meta uscita ── */
$pid   = $prossima->ID;
$luogo = (string) get_post_meta( $pid, '_uscita_luogo',            true );
$n_imm = (int)    get_post_meta( $pid, '_uscita_n_immersioni',     true );
$max_p = get_post_meta( $pid, '_uscita_max_partecipanti', true );
$link  = get_permalink( $pid );

/* Posti rimasti */
$posti_liberi = null;
if ( $max_p !== '' && $max_p !== false ) {
	$bm           = $GLOBALS['calypsosub_booking_manager'] ?? null;
	$confermati   = $bm ? $bm->count_confirmed( $pid ) : 0;
	$posti_liberi = max( 0, (int) $max_p - $confermati );
}

/* Formato data */
$ts         = strtotime( $prossima->_prima );
$giorno_num = date_i18n( 'j', $ts );
$giorno_set = strtoupper( date_i18n( 'D', $ts ) );
$mese_abr   = strtoupper( date_i18n( 'M', $ts ) );

/* Stringa posti */
if ( $posti_liberi === null ) {
	$posti_str  = '';
	$posti_warn = false;
} elseif ( $posti_liberi === 0 ) {
	$posti_str  = __( 'Sold out', 'calypsosub' );
	$posti_warn = true;
} elseif ( $posti_liberi <= 3 ) {
	$posti_str  = '● ' . $posti_liberi . ' ' . _n( 'posto', 'posti', $posti_liberi, 'calypsosub' );
	$posti_warn = true;
} else {
	$posti_str  = $posti_liberi . ' ' . _n( 'posto', 'posti', $posti_liberi, 'calypsosub' );
	$posti_warn = false;
}

/* Stringa luogo + immersioni */
$meta_parts = [];
if ( $luogo ) $meta_parts[] = $luogo;
if ( $n_imm ) $meta_parts[] = $n_imm . ' ' . _n( 'immersione', 'immersioni', $n_imm, 'calypsosub' );
$meta_str = implode( ' · ', $meta_parts );
?>
<style>
/* ── Prossima uscita — comune ───────────────────────────────── */
.csou-prossima--hero,
.csou-prossima--strip{box-sizing:border-box}

/* ── Hero card (desktop ≥1024px) ────────────────────────────── */
.csou-prossima--hero{
  display:none; /* nascosta di default, visibile solo su desktop */
  width:280px;
  background:rgba(255,255,255,.08);
  backdrop-filter:blur(14px);
  -webkit-backdrop-filter:blur(14px);
  border:1px solid rgba(255,255,255,.18);
  border-radius:14px;
  padding:20px;
  color:#fff;
}
.csou-ph__head{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.csou-ph__dot{
  width:8px;height:8px;border-radius:50%;flex:0 0 auto;
  background:#54e09a;
  box-shadow:0 0 0 4px rgba(84,224,154,.25);
}
.csou-ph__eyebrow{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.7)}
.csou-ph__date{font-size:38px;font-weight:900;line-height:.95;letter-spacing:-.02em;color:#fff}
.csou-ph__meta{font-size:13px;opacity:.85;margin-top:12px;line-height:1.5;color:#fff}
.csou-ph__posti{
  margin-top:14px;font-size:12px;
  opacity:.7;letter-spacing:.04em;color:#fff;
}
.csou-ph__posti--warn{opacity:1;color:#ff6b4a}
.csou-ph__cta{
  margin-top:14px;display:inline-flex;align-items:center;gap:6px;
  color:var(--c-aqua,#26CBFB);font-size:13px;font-weight:600;
  text-decoration:none;letter-spacing:.02em;
}
.csou-ph__cta:hover{opacity:.8}

/* ── Strip section (mobile <1024px) ─────────────────────────── */
.csou-prossima--strip{
  display:block;
  background:var(--c-deep,#0a2540);
  padding:20px 24px;
}
.csou-strip__inner{
  max-width:1320px;
  margin:0 auto;
  display:flex;
  align-items:center;
  gap:20px;
  flex-wrap:wrap;
}
.csou-strip__date{
  display:flex;
  flex-direction:column;
  align-items:center;
  flex:0 0 auto;
  padding-right:20px;
  border-right:1px solid rgba(255,255,255,.15);
}
.csou-strip__num{
  font-size:40px;font-weight:900;line-height:1;
  color:var(--c-aqua,#26CBFB);letter-spacing:-.02em;
}
.csou-strip__label{
  font-size:10px;font-weight:600;letter-spacing:.12em;
  text-transform:uppercase;color:rgba(255,255,255,.6);
  margin-top:2px;
}
.csou-strip__info{flex:1;min-width:0}
.csou-strip__eyebrow{
  font-size:10px;font-weight:600;letter-spacing:.12em;
  text-transform:uppercase;color:#54e09a;margin-bottom:4px;
}
.csou-strip__titolo{
  font-size:16px;font-weight:700;color:#fff;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.csou-strip__meta{font-size:13px;color:rgba(255,255,255,.65);margin-top:2px}
.csou-strip__meta--warn{color:#ff6b4a}
.csou-strip__btn{
  flex:0 0 auto;
  display:inline-flex;align-items:center;
  background:var(--c-coral,#ff6b4a);color:#fff;
  font-size:13px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
  padding:10px 18px;border-radius:999px;text-decoration:none;
  transition:background .15s;white-space:nowrap;
}
.csou-strip__btn:hover{background:#e04a2a;color:#fff}

/* ── Visibilità per breakpoint ──────────────────────────────── */
@media(min-width:1024px){
  .csou-prossima--hero{display:block}   /* desktop: mostra hero card */
  .csou-prossima--strip{display:none}   /* desktop: nascondi strip   */
}
</style>

<?php if ( $inside_hero ) : ?>
<!-- ── Hero card (floating) ── -->
<div class="csou-prossima--hero">
  <div class="csou-ph__head">
    <span class="csou-ph__dot"></span>
    <span class="csou-ph__eyebrow"><?php _e( 'Prossima uscita', 'calypsosub' ); ?></span>
  </div>
  <div class="csou-ph__date">
    <?php echo esc_html( $giorno_set ); ?><br>
    <?php echo esc_html( $giorno_num . ' ' . $mese_abr ); ?>
  </div>
  <?php if ( $meta_str ) : ?>
  <div class="csou-ph__meta"><?php echo nl2br( esc_html( $prossima->post_title . "\n" . $meta_str ) ); ?></div>
  <?php endif; ?>
  <?php if ( $posti_str ) : ?>
  <div class="csou-ph__posti<?php echo $posti_warn ? ' csou-ph__posti--warn' : ''; ?>">
    <?php echo esc_html( $posti_str ); ?>
  </div>
  <?php endif; ?>
  <a href="<?php echo esc_url( $link ); ?>" class="csou-ph__cta">
    <?php _e( 'Prenota →', 'calypsosub' ); ?>
  </a>
</div>

<?php else : ?>
<!-- ── Strip section (mobile) ── -->
<section class="csou-prossima--strip">
  <div class="csou-strip__inner">
    <div class="csou-strip__date">
      <span class="csou-strip__num"><?php echo esc_html( $giorno_num ); ?></span>
      <span class="csou-strip__label"><?php echo esc_html( $giorno_set . ' · ' . $mese_abr ); ?></span>
    </div>
    <div class="csou-strip__info">
      <div class="csou-strip__eyebrow">● <?php _e( 'PROSSIMA USCITA', 'calypsosub' ); ?></div>
      <div class="csou-strip__titolo"><?php echo esc_html( $prossima->post_title ); ?></div>
      <?php $sub = array_filter( [ $luogo, $posti_str ] ); if ( $sub ) : ?>
      <div class="csou-strip__meta<?php echo $posti_warn ? ' csou-strip__meta--warn' : ''; ?>">
        <?php echo esc_html( implode( ' · ', $sub ) ); ?>
      </div>
      <?php endif; ?>
    </div>
    <a href="<?php echo esc_url( $link ); ?>" class="csou-strip__btn">
      <?php _e( 'Prenota', 'calypsosub' ); ?>
    </a>
  </div>
</section>
<?php endif; ?>
