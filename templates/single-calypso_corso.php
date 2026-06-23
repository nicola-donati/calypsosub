<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

// Temi block: renderizza il vero header Gutenberg in un wrapper sticky.
if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}

$id = get_the_ID();

$badge           = (string) get_post_meta( $id, '_corso_badge',           true );
$sottotitolo     = (string) get_post_meta( $id, '_corso_sottotitolo',     true );
$desc_breve      = (string) get_post_meta( $id, '_corso_desc_breve',      true );
$stat_durata     = (string) get_post_meta( $id, '_corso_stat_durata',     true );
$stat_pratica    = (string) get_post_meta( $id, '_corso_stat_pratica',    true );
$stat_profondita = (string) get_post_meta( $id, '_corso_stat_profondita', true );
$periodo         = (string) get_post_meta( $id, '_corso_periodo',         true );
$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );
$contatto        = (string) get_post_meta( $id, '_corso_contatto',        true );
$contatto_nome   = (string) get_post_meta( $id, '_corso_contatto_nome',   true );
$materiale       = (string) get_post_meta( $id, '_corso_materiale',       true );
$direttore_id    = (int)    get_post_meta( $id, '_corso_direttore_id',    true );
$docenti_ids     = (array)  ( get_post_meta( $id, '_corso_docenti_ids',   true ) ?: [] );
$fasi            = (array)  ( get_post_meta( $id, '_corso_fasi',          true ) ?: [] );
$requisiti       = (string) get_post_meta( $id, '_corso_requisiti',       true );
$competenze_raw  = (string) get_post_meta( $id, '_corso_competenze',      true );
$competenze      = $competenze_raw
	? array_values( array_filter( array_map( 'trim', explode( "\n", $competenze_raw ) ) ) )
	: [];

$livelli = wp_get_post_terms( $id, 'calypso_livello', [ 'fields' => 'names' ] );
$livello = ( ! is_wp_error( $livelli ) && ! empty( $livelli ) ) ? $livelli[0] : '';
$img     = get_the_post_thumbnail_url( $id, 'full' );

$oggi     = date( 'Y-m-d' );
$all_occ  = calypso_get_occorrenze_by_corso( $id );
$prossime = array_values( array_filter( $all_occ, static function ( $o ) use ( $oggi ) {
	$fine = (string) get_post_meta( $o->ID, '_occorrenza_data_fine', true );
	return $fine !== '' && $fine >= $oggi;
} ) );

$related   = calypso_get_corsi( [ 'posts_per_page' => 3, 'post__not_in' => [ $id ] ] );
$hero_bg   = get_post_meta( $id, '_hero_use_featured_image', true ) === '1' && $img;
?>
<style>
/* ── Token locali ── */
.cso{
    color:var(--c-ink,#0b1a26);
}
/* Neutralizza FSE — heading wave (sfondo bone) */
.cso h1,.cso h2,.cso h3,.cso h4{color:var(--c-wave,#1B77A7);text-transform:uppercase}
.cso a{color:inherit;text-decoration:none}
.cso p,.cso li,.cso span,.cso div{color:inherit}
/* Related section sfondo chiaro — heading scuri */
.cso-related h2,.cso-related h3,.cso-related h4{color:var(--c-deep,#0a2540)}

/* ── Hero — parte da y=0 (sotto l'header sticky) ── */
.cso-hero{
  background:var(--c-deep,#0a2540);
  color:#fff;
  padding:calc(var(--cso-header-h) + 40px) 48px 64px;
  position:relative;
}
/* Hero con immagine in evidenza come sfondo */
.cso-hero--bg-img{
  background-size:cover;
  background-position:center center;
  background-repeat:no-repeat;
}
.cso-hero--bg-img .cso-hero__inner{position:relative;z-index:1}
.cso-hero h1,.cso-hero h2,.cso-hero h3{color:#fff;text-shadow:0 2px 14px rgba(0,0,0,.9),0 5px 36px rgba(0,0,0,.75),0 12px 56px rgba(0,0,0,.5)}
.cso-hero a{color:rgba(255,255,255,.55);text-decoration:none}
.cso-hero a:hover{color:rgba(255,255,255,.9)}
@media(max-width:1024px){.cso-hero{padding:calc(var(--cso-header-h) + 24px) 20px 40px}}

.cso-hero__inner{max-width:1320px;margin:0 auto}

.cso-hero__header{display:grid;grid-template-columns:1.4fr 1fr;gap:80px;align-items:end;margin-bottom:56px}
@media(max-width:1024px){.cso-hero__header{grid-template-columns:1fr;gap:24px;margin-bottom:32px}}

.cso-hero__badge{background:var(--c-coral,#ff6b4a)}

.cso-hero__sub{margin-top:16px;font-weight:600;line-height:1;text-shadow:0 2px 14px rgba(0,0,0,.9),0 5px 36px rgba(0,0,0,.75),0 12px 56px rgba(0,0,0,.5)}
.cso .cso-hero__sub{font-size:clamp(28px,5vw,72px);color:var(--c-aqua,#26CBFB)}
.cso-hero__lead{line-height:1.6;opacity:.85;margin:0;align-self:end}

.cso-hero__img{width:100%;height:480px;object-fit:cover;display:block;border-radius:12px}
.cso-hero__img-placeholder{font-size:64px;width:100%;height:480px;background:linear-gradient(135deg,rgba(255,255,255,.04) 0%,rgba(38,203,251,.1) 100%);display:flex;align-items:center;justify-content:center;border-radius:12px}

/* ── Layout corpo ── */
.cso-layout{max-width:1320px;margin:0 auto;padding:80px 48px 48px;display:grid;grid-template-columns:1.5fr 1fr;gap:64px;align-items:start}
@media(max-width:1024px){.cso-layout{grid-template-columns:1fr;padding:40px 20px}.cso-layout aside{order:-1}}

.cso-section{margin-bottom:72px}
.cso-section:last-child{margin-bottom:0}

/* Eyebrow + heading standard */
.cso-eyebrow{font-weight:500;letter-spacing:.16em;text-transform:uppercase;margin:0 0 14px;display:block}
.cso .cso-eyebrow{font-size:16px;color:var(--c-wave,#1B77A7)}
.cso-display-heading{font-size:clamp(28px,4vw,56px);font-weight:800;text-transform:uppercase;line-height:.96;margin:0 0 24px}
.cso-lead{line-height:1.7;margin:0 0 32px;max-width:640px}
.cso .cso-lead{font-size:18px;color:var(--c-ink,#0b1a26)}

/* ── Fasi ── */
.cso-fasi{margin:0;padding:0;list-style:none}
.cso-fasi>li{list-style:none}
.cso-fase{display:grid;grid-template-columns:52px 1fr 90px;gap:20px;padding:24px 0;border-top:1px solid rgba(11,26,38,.1);align-items:start}
.cso-fase:last-child{border-bottom:1px solid rgba(11,26,38,.1)}
.cso-fase__num{line-height:1;font-weight:800}
.cso .cso-fase__num{font-size:28px;color:var(--c-gold,#E9BF26)}
.cso-fase__titolo{font-weight:700;text-transform:uppercase;margin:0 0 6px;line-height:1.05}
.cso .cso-fase__titolo{font-size:24px;color:var(--c-wave,#1B77A7)}
.cso-fase__desc{line-height:1.6;margin:0}
.cso .cso-fase__desc{font-size:16px;color:var(--c-ink,#0b1a26)}
.cso-fase__ore{letter-spacing:.08em;text-transform:uppercase;text-align:right;align-self:start;padding-top:6px;line-height:1.6}
.cso .cso-fase__ore{font-size:16px;color:var(--c-wave,#1B77A7)}

/* ── Competenze ── */
.cso-competenze-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 32px}
@media(max-width:600px){.cso-competenze-grid{grid-template-columns:1fr}}
.cso-competenza{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid rgba(11,26,38,.08);line-height:1.5}
.cso .cso-competenza{font-size:18px;color:var(--c-ink,#0b1a26)}
.cso-competenza__plus{font-weight:700;flex-shrink:0;line-height:1.4;min-width:14px}
.cso .cso-competenza__plus{font-size:22px;color:var(--c-gold,#E9BF26)}

/* ── Docenti (sezione full-width) ── */
.cso-docenti-section{padding:64px 48px 80px}
.cso-docenti-section__inner{max-width:1320px;margin:0 auto}
@media(max-width:1024px){.cso-docenti-section{padding:48px 20px 64px}}
.cso-docenti-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:700px){.cso-docenti-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:400px){.cso-docenti-grid{grid-template-columns:1fr}}
.cso .cso-docente-mini__name{color:var(--c-deep,#0a2540)}
.cso .cso-docente-mini__ruolo{color:var(--c-wave,#1B77A7)}

/* ── Sidebar navy ── */
.cso-sintesi{background:var(--c-deep,#0a2540);border-radius:18px;box-shadow:0 6px 32px rgba(10,37,64,.28);position:sticky;top:24px;color:#fff;display:flex;flex-direction:column}
@media(max-width:1024px){.cso-sintesi{position:static}}
.cso-sintesi h2,.cso-sintesi h3{color:#fff}
.cso-sintesi p,.cso-sintesi span,.cso-sintesi div{color:inherit}

.cso-sintesi__head{padding:24px 24px 20px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-sintesi__cert{letter-spacing:.12em;text-transform:uppercase;font-weight:600;margin:0 0 12px;display:block}
.cso-sintesi .cso-sintesi__cert{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-sintesi__title{font-size:36px;font-weight:900;color:#fff;margin:0;line-height:.96;letter-spacing:-.01em}

.cso-sintesi__stats{padding:8px 24px 0;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-stat-row{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;padding:14px 0;border-top:1px solid rgba(255,255,255,.12)}
.cso-stat-row:first-child{border-top:none}
.cso-stat-row__label{letter-spacing:.08em;text-transform:uppercase;font-weight:600;flex-shrink:0}
.cso-sintesi .cso-stat-row__label{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-stat-row__val{font-weight:600;text-align:right}
.cso-sintesi .cso-stat-row__val{font-size:16px;color:#fff}
@media(max-width:600px){.cso-stat-row{flex-direction:column;gap:4px}.cso-stat-row__val{text-align:left}}

.cso-sintesi__requisiti{padding:20px 24px 20px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-sintesi__requisiti-label{letter-spacing:.08em;text-transform:uppercase;font-weight:600;margin:0 0 14px;display:block}
.cso-sintesi .cso-sintesi__requisiti-label{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-sintesi .cso-sintesi__requisiti-text{font-size:16px;font-weight:600;line-height:1.5;color:#fff;margin:0;padding:0;white-space:pre-line}

.cso-sintesi__inizi{padding:20px 24px 24px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-inizi-label{letter-spacing:.12em;text-transform:uppercase;font-weight:600;margin:0 0 14px;display:block}
.cso-sintesi .cso-inizi-label{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-inizio-row{font-size:16px;display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-top:1px solid rgba(255,255,255,.08);}
.cso-inizio-row:first-child{border-top:none}
.cso-inizio-row__date{font-weight:500}
.cso-sintesi .cso-inizio-row__date{color:#fff}
.cso-inizio-row__luogo{font-size:16px;font-weight:400;color:rgba(255,255,255,.55)}
.cso-inizio-row__spots{font-size:16px;letter-spacing:.04em}
.cso-inizio-row__spots--ok{color:rgba(255,255,255,.6)}
.cso-inizio-row__spots--warn{color:var(--c-coral,#ff6b4a)}
.cso-inizio-row__spots--full{color:rgba(255,255,255,.35)}

.cso-sintesi__cta{padding:20px 24px 24px}
.cso-btn-primary{display:block;background:var(--c-coral,#ff6b4a);font-weight:600;letter-spacing:.02em;padding:14px 22px;border-radius:999px;text-align:center;text-decoration:none;transition:background .15s;margin-bottom:10px;box-shadow:0 6px 18px -4px rgba(255,107,74,.55)}
.cso .cso-btn-primary{font-size:18px;color:#fff}
.cso .cso-btn-primary:hover{background:#e04a2a;color:#fff}
.cso-btn-secondary{display:block;text-align:center;padding:12px 22px;background:transparent;border:1.5px solid rgba(255,255,255,.3);border-radius:999px;font-weight:600;text-decoration:none;transition:border-color .15s,background .15s}
.cso .cso-btn-secondary{font-size:18px;color:#fff}
.cso .cso-btn-secondary:hover{border-color:#fff;background:rgba(255,255,255,.08);color:#fff}

/* ── Corsi correlati ── */
.cso-related{background:var(--c-bone,#f6f1e6);padding:80px 48px 96px}
@media(max-width:1024px){.cso-related{padding:48px 20px}}
.cso-related__inner{max-width:1320px;margin:0 auto}
.cso-related__header{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:40px;flex-wrap:wrap;gap:24px}
.cso-related__all{font-size:16px;font-weight:600;color:var(--c-deep,#0a2540);text-decoration:none;display:flex;align-items:center;gap:6px}
.cso-related__all:hover{color:var(--c-wave,#1B77A7)}
.cso-related__grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:700px){.cso-related__grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:400px){.cso-related__grid{grid-template-columns:1fr}}

.cso-thumb__level{display:inline-flex;padding:4px 10px;background:rgba(29,111,156,.1);color:var(--c-wave,#1B77A7);border-radius:999px;font-weight:600;align-self:flex-start;margin-bottom:14px}
.cso .cso-thumb__title{color:var(--c-deep,#0a2540)}
.cso .cso-thumb__desc{color:rgba(11,26,38,.65)}
.cso-thumb__link{font-size:16px;font-weight:600;color:var(--c-coral,#ff6b4a);display:flex;align-items:center;gap:6px}
</style>

<div class="cso">

<!-- HERO -->
<section class="cso-hero<?php echo $hero_bg ? ' cso-hero--bg-img' : ''; ?>"
         <?php if ( $hero_bg ) : ?>style="background-image:url('<?php echo esc_url( $img ); ?>')"<?php endif; ?>>
<div class="cso-hero__inner">

	<nav class="cso-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'calypsosub' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'calypsosub' ); ?></a>
		<span>/</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_corso' ) ); ?>"><?php echo esc_html( calypsosub_opt( 'corsi', 'breadcrumb_archive', __( 'Corsi', 'calypsosub' ) ) ); ?></a>
		<span>/</span>
		<span class="cso-breadcrumb__current"><?php the_title(); ?></span>
	</nav>

	<?php if ( $hero_bg ) : ?>
	<!-- Layout full-width quando immagine è sfondo -->
	<div>
		<?php if ( $livello ) : ?>
			<div class="cso-hero__badge"><?php echo esc_html( $livello ); ?></div>
		<?php endif; ?>
		<h1 class="cso-hero__title"><?php the_title(); ?></h1>
		<?php if ( $sottotitolo ) : ?>
			<div class="cso-hero__sub"><?php echo esc_html( $sottotitolo ); ?></div>
		<?php endif; ?>
		<?php if ( $desc_breve ) : ?>
			<p class="cso-hero__lead" style="max-width:640px;margin-top:24px"><?php echo esc_html( $desc_breve ); ?></p>
		<?php endif; ?>
	</div>
	<?php else : ?>
	<!-- Layout griglia: titolo sx, lead dx + immagine sotto -->
	<div class="cso-hero__header">
		<div>
			<?php if ( $livello ) : ?>
				<div class="cso-hero__badge"><?php echo esc_html( $livello ); ?></div>
			<?php endif; ?>
			<h1 class="cso-hero__title"><?php the_title(); ?></h1>
			<?php if ( $sottotitolo ) : ?>
				<div class="cso-hero__sub"><?php echo esc_html( $sottotitolo ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $desc_breve ) : ?>
			<p class="cso-hero__lead"><?php echo esc_html( $desc_breve ); ?></p>
		<?php endif; ?>
	</div>
	<?php endif; ?>

</div>
  <div class="cso-hero__scroll" aria-hidden="true">
    SCORRI
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
  </div>
</section>

<!-- CORPO: contenuto + sidebar sticky -->
<div class="cso-layout">

<main>

	<?php if ( $fasi ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'corsi', 'sec_programma_eyebrow', __( 'Il programma', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading">
			<?php echo $sottotitolo
				? esc_html( $sottotitolo )
				: esc_html__( 'Le fasi del corso.', 'calypsosub' ); ?>
		</h2>
		<ol class="cso-fasi">
			<?php foreach ( $fasi as $n => $fase ) :
				$ore_raw   = trim( $fase['ore'] ?? '' );
				$ore_label = $ore_raw
					? ( is_numeric( $ore_raw ) ? $ore_raw . ' ore' : $ore_raw )
					: '';
			?>
			<li class="cso-fase">
				<span class="cso-fase__num"><?php echo esc_html( str_pad( $n + 1, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<div>
					<p class="cso-fase__titolo"><?php echo esc_html( $fase['titolo'] ); ?></p>
					<?php if ( ! empty( $fase['desc'] ) ) : ?>
						<p class="cso-fase__desc"><?php echo esc_html( $fase['desc'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $ore_label ) : ?>
					<span class="cso-fase__ore"><?php echo esc_html( $ore_label ); ?></span>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $competenze ) ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'corsi', 'sec_competenze_eyebrow', __( 'Cosa imparerai', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading">
			<?php printf(
				esc_html( _n( '%d competenza da portare via.', '%d competenze da portare via.', count( $competenze ), 'calypsosub' ) ),
				count( $competenze )
			); ?>
		</h2>
		<div class="cso-competenze-grid">
			<?php foreach ( $competenze as $c ) : ?>
			<div class="cso-competenza">
				<span class="cso-competenza__plus">+</span>
				<span><?php echo esc_html( $c ); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $materiale ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'corsi', 'sec_materiale_eyebrow', __( 'Materiale', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'corsi', 'sec_materiale_heading', __( 'Cosa è incluso.', 'calypsosub' ) ) ); ?></h2>
		<p class="cso-lead" style="white-space:pre-line"><?php echo esc_html( $materiale ); ?></p>
	</div>
	<?php endif; ?>


</main>

<!-- SIDEBAR -->
<aside>
<div class="cso-sintesi">

	<div class="cso-sintesi__head">
		<?php if ( $badge ) : ?>
			<span class="cso-sintesi__cert"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<h2 class="cso-sintesi__title"><?php echo esc_html( calypsosub_opt( 'corsi', 'sidebar_title', __( 'In sintesi', 'calypsosub' ) ) ); ?></h2>
	</div>

	<?php
	$stats = array_filter( [
		calypsosub_opt( 'corsi', 'stat_durata',     __( 'Durata',     'calypsosub' ) ) => $stat_durata,
		calypsosub_opt( 'corsi', 'stat_immersioni',  __( 'Immersioni', 'calypsosub' ) ) => $stat_pratica,
		calypsosub_opt( 'corsi', 'stat_profondita',  __( 'Profondità', 'calypsosub' ) ) => $stat_profondita,
		calypsosub_opt( 'corsi', 'stat_periodo',     __( 'Periodo di riferimento', 'calypsosub' ) ) => $periodo,
	] );
	if ( ! empty( $stats ) ) : ?>
	<div class="cso-sintesi__stats">
		<?php foreach ( $stats as $slabel => $val ) : ?>
		<div class="cso-stat-row">
			<span class="cso-stat-row__label"><?php echo esc_html( $slabel ); ?></span>
			<span class="cso-stat-row__val"><?php echo esc_html( $val ); ?></span>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( $requisiti ) : ?>
	<div class="cso-sintesi__requisiti">
		<span class="cso-sintesi__requisiti-label"><?php echo esc_html( calypsosub_opt( 'corsi', 'sidebar_requisiti_label', __( 'Requisiti', 'calypsosub' ) ) ); ?></span>
		<p class="cso-sintesi__requisiti-text"><?php echo esc_html( trim( $requisiti ) ); ?></p>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $prossime ) ) : ?>
	<div class="cso-sintesi__inizi">
		<span class="cso-inizi-label"><?php echo esc_html( calypsosub_opt( 'corsi', 'inizi_label', __( 'Prossime lezioni', 'calypsosub' ) ) ); ?></span>
		<?php foreach ( $prossime as $occ ) :
			$occ_inizio = get_post_meta( $occ->ID, '_occorrenza_data_inizio', true );
			$occ_luogo  = (string) get_post_meta( $occ->ID, '_occorrenza_luogo', true );
			$occ_posti  = get_post_meta( $occ->ID, '_occorrenza_posti', true );
			$data_fmt   = $occ_inizio
				? date_i18n( 'j M', strtotime( $occ_inizio ) )
				: '—';

			if ( $occ_posti === '' || $occ_posti === false ) {
				$spots_html = '';
			} elseif ( (int) $occ_posti === 0 ) {
				$spots_html = '<span class="cso-inizio-row__spots cso-inizio-row__spots--full">'
					. esc_html__( 'sold out', 'calypsosub' ) . '</span>';
			} elseif ( (int) $occ_posti <= 3 ) {
				$spots_html = '<span class="cso-inizio-row__spots cso-inizio-row__spots--warn">● '
					. esc_html( $occ_posti ) . ' ' . esc_html__( 'posti', 'calypsosub' ) . '</span>';
			} else {
				$spots_html = '<span class="cso-inizio-row__spots cso-inizio-row__spots--ok">'
					. esc_html( $occ_posti ) . ' ' . esc_html__( 'posti liberi', 'calypsosub' ) . '</span>';
			}
		?>
		<div class="cso-inizio-row">
			<span class="cso-inizio-row__date">
				<?php echo esc_html( $data_fmt ); ?>
				<?php if ( $occ_luogo ) : ?>
					<span class="cso-inizio-row__luogo"> · <?php echo esc_html( $occ_luogo ); ?></span>
				<?php endif; ?>
			</span>
			<?php echo $spots_html; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="cso-sintesi__cta">
		<?php if ( $prenotazioni_page_id ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'prenota_id', $id, get_permalink( $prenotazioni_page_id ) ) ); ?>"
			   class="cso-btn-primary">
				<?php echo esc_html( calypsosub_opt( 'corsi', 'btn_iscrivi', __( 'Iscriviti al corso →', 'calypsosub' ) ) ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $contatto ) :
			$btn_label = $contatto_nome
				? $contatto_nome . ' · ' . $contatto
				: __( 'Chiamaci', 'calypsosub' ) . ' · ' . $contatto;
		?>
			<a href="<?php echo esc_attr( strpos( $contatto, '@' ) !== false
				? 'mailto:' . $contatto
				: 'tel:' . preg_replace( '/[^+0-9]/', '', $contatto ) ); ?>"
			   class="cso-btn-secondary">
				<?php echo esc_html( $btn_label ); ?>
			</a>
		<?php endif; ?>
	</div>

</div>
</aside>

</div><!-- .cso-layout -->

<?php
$all_shown = $docenti_ids;
if ( $direttore_id && ! in_array( $direttore_id, $all_shown, true ) ) {
	array_unshift( $all_shown, $direttore_id );
}
if ( ! empty( $all_shown ) ) : ?>
<section class="cso-docenti-section">
<div class="cso-docenti-section__inner">
	<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'corsi', 'sec_docenti_eyebrow', __( 'I docenti', 'calypsosub' ) ) ); ?></span>
	<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'corsi', 'sec_docenti_heading', __( 'I nostri docenti.', 'calypsosub' ) ) ); ?></h2>
	<div class="cso-docenti-grid">
		<?php foreach ( $all_shown as $did ) :
			$ruolo  = (string) get_post_meta( $did, '_docente_ruolo', true );
			$is_dir = ( $did === $direttore_id );
			$label  = $is_dir
				? __( 'Direttore', 'calypsosub' ) . ( $ruolo ? ' · ' . $ruolo : '' )
				: $ruolo;
		?>
		<a href="<?php echo esc_url( get_permalink( $did ) ); ?>" class="cso-docente-mini">
			<?php if ( has_post_thumbnail( $did ) ) : ?>
				<img src="<?php echo esc_url( get_the_post_thumbnail_url( $did, 'medium_large' ) ); ?>"
				     alt="<?php echo esc_attr( get_the_title( $did ) ); ?>">
			<?php else : ?>
				<div class="cso-docente-mini__avatar">🤿</div>
			<?php endif; ?>
			<div class="cso-docente-mini__body">
				<p class="cso-docente-mini__name"><?php echo esc_html( get_the_title( $did ) ); ?></p>
				<?php if ( $label ) : ?>
					<p class="cso-docente-mini__ruolo"><?php echo esc_html( $label ); ?></p>
				<?php endif; ?>
			</div>
		</a>
		<?php endforeach; ?>
	</div>
</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $related ) ) : ?>
<section class="cso-related">
<div class="cso-related__inner">
	<div class="cso-related__header">
		<div>
			<span class="cso-related__eyebrow"><?php echo esc_html( calypsosub_opt( 'corsi', 'related_eyebrow', __( 'Continua a scendere', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-display-heading" style="margin:0"><?php echo esc_html( calypsosub_opt( 'corsi', 'related_heading', __( 'Altri corsi.', 'calypsosub' ) ) ); ?></h2>
		</div>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_corso' ) ); ?>"
		   class="cso-related__all"><?php echo esc_html( calypsosub_opt( 'corsi', 'related_link', __( 'Tutti i corsi →', 'calypsosub' ) ) ); ?></a>
	</div>
	<div class="cso-related__grid">
		<?php foreach ( $related as $r ) :
			$r_img    = get_the_post_thumbnail_url( $r->ID, 'medium_large' );
			$r_desc   = (string) get_post_meta( $r->ID, '_corso_desc_breve', true );
			$r_livs   = wp_get_post_terms( $r->ID, 'calypso_livello', [ 'fields' => 'names' ] );
			$r_livello = ( ! is_wp_error( $r_livs ) && ! empty( $r_livs ) ) ? $r_livs[0] : '';
		?>
		<a href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>" class="cso-thumb">
			<?php if ( $r_img ) : ?>
				<img src="<?php echo esc_url( $r_img ); ?>" alt="<?php echo esc_attr( $r->post_title ); ?>">
			<?php else : ?>
				<div class="cso-thumb__placeholder">🎓</div>
			<?php endif; ?>
			<div class="cso-thumb__body">
				<?php if ( $r_livello ) : ?>
					<span class="cso-thumb__level"><?php echo esc_html( $r_livello ); ?></span>
				<?php endif; ?>
				<p class="cso-thumb__title"><?php echo esc_html( $r->post_title ); ?></p>
				<?php if ( $r_desc ) : ?>
					<p class="cso-thumb__desc"><?php echo esc_html( $r_desc ); ?></p>
				<?php endif; ?>
				<span class="cso-thumb__link"><?php echo esc_html( calypsosub_opt( 'corsi', 'related_card_link', __( 'Scopri il corso →', 'calypsosub' ) ) ); ?></span>
			</div>
		</a>
		<?php endforeach; ?>
	</div>
</div>
</section>
<?php endif; ?>

</div><!-- .cso -->

<?php
// Temi block: renderizza il vero footer Gutenberg.
if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-footer-wrap">';
	block_template_part( 'footer' );
	echo '</div>';
}
get_footer();
?>
