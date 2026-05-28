<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$id = get_the_ID();

$badge           = (string) get_post_meta( $id, '_corso_badge',           true );
$sottotitolo     = (string) get_post_meta( $id, '_corso_sottotitolo',     true );
$desc_breve      = (string) get_post_meta( $id, '_corso_desc_breve',      true );
$stat_durata     = (string) get_post_meta( $id, '_corso_stat_durata',     true );
$stat_pratica    = (string) get_post_meta( $id, '_corso_stat_pratica',    true );
$stat_profondita = (string) get_post_meta( $id, '_corso_stat_profondita', true );
$periodo         = (string) get_post_meta( $id, '_corso_periodo',         true );
$link_iscrizione = (string) get_post_meta( $id, '_corso_link_iscrizione', true );
$contatto        = (string) get_post_meta( $id, '_corso_contatto',        true );
$materiale       = (string) get_post_meta( $id, '_corso_materiale',       true );
$direttore_id    = (int)    get_post_meta( $id, '_corso_direttore_id',    true );
$docenti_ids     = (array)  ( get_post_meta( $id, '_corso_docenti_ids',   true ) ?: [] );
$fasi            = (array)  ( get_post_meta( $id, '_corso_fasi',          true ) ?: [] );
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

$related = calypso_get_corsi( [ 'posts_per_page' => 3, 'post__not_in' => [ $id ] ] );
?>
<style>
/* ── Token locali: sempre definiti, nessun !important necessario ── */
.cso{
  font-family:var(--f-body,"DM Sans",-apple-system,sans-serif);
  color:var(--c-ink,#0b1a26);
}
/* Neutralizza override alta specificità del tema FSE */
.cso h1,.cso h2,.cso h3,.cso h4{color:var(--c-deep,#0a2540);font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);text-transform:uppercase}
.cso a{color:inherit;text-decoration:none}
.cso p,.cso li,.cso span,.cso div{color:inherit}

/* ── Hero — sezione navy scura ── */
.cso-hero{background:var(--c-deep,#0a2540);color:#fff;padding:32px 48px 64px}
.cso-hero h1,.cso-hero h2,.cso-hero h3{color:#fff}
.cso-hero a{color:rgba(255,255,255,.55);text-decoration:none}
.cso-hero a:hover{color:rgba(255,255,255,.9)}
@media(max-width:900px){.cso-hero{padding:24px 20px 40px}}

.cso-hero__inner{max-width:1200px;margin:0 auto}

.cso-breadcrumb{display:flex;align-items:center;gap:8px;font-family:var(--f-mono,"DM Mono",monospace);font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:48px}
.cso-breadcrumb__current{color:#fff}

.cso-hero__header{display:grid;grid-template-columns:1.4fr 1fr;gap:80px;align-items:end;margin-bottom:56px}
@media(max-width:900px){.cso-hero__header{grid-template-columns:1fr;gap:24px;margin-bottom:32px}}

.cso-hero__badge{display:inline-flex;padding:6px 14px;background:var(--c-coral,#ff6b4a);color:#fff;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:24px}
.cso-hero__title{font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);font-size:clamp(56px,10vw,144px);font-weight:900;color:#fff;margin:0;line-height:.9;letter-spacing:-.02em;text-transform:uppercase}
.cso-hero__sub{font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);font-size:clamp(20px,3vw,30px);color:var(--c-aqua,#26CBFB);margin-top:16px;font-weight:600;line-height:1}
.cso-hero__lead{font-size:17px;line-height:1.6;opacity:.85;margin:0;align-self:end}

.cso-hero__img{width:100%;height:480px;object-fit:cover;display:block;border-radius:12px}
.cso-hero__img-placeholder{width:100%;height:480px;background:linear-gradient(135deg,rgba(255,255,255,.04) 0%,rgba(38,203,251,.1) 100%);display:flex;align-items:center;justify-content:center;font-size:64px;border-radius:12px}

/* ── Layout corpo ── */
.cso-layout{max-width:1200px;margin:0 auto;padding:80px 48px 96px;display:grid;grid-template-columns:1.5fr 1fr;gap:64px;align-items:start}
@media(max-width:900px){.cso-layout{grid-template-columns:1fr;padding:40px 20px}}

.cso-section{margin-bottom:72px}

/* Eyebrow + heading standard */
.cso-eyebrow{font-family:var(--f-mono,"DM Mono",monospace);font-size:11px;font-weight:500;letter-spacing:.16em;text-transform:uppercase;color:var(--c-wave,#1B77A7);margin:0 0 14px;display:block}
.cso-display-heading{font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);font-size:clamp(28px,4vw,56px);font-weight:800;color:var(--c-deep,#0a2540);text-transform:uppercase;line-height:.96;margin:0 0 24px}
.cso-lead{font-size:16px;line-height:1.7;color:rgba(11,26,38,.78);margin:0 0 32px;max-width:640px}

/* ── Fasi ── */
.cso-fasi{margin:0;padding:0;list-style:none}
.cso-fasi>li{list-style:none}
.cso-fase{display:grid;grid-template-columns:52px 1fr 90px;gap:20px;padding:24px 0;border-top:1px solid rgba(11,26,38,.1);align-items:start}
.cso-fase:last-child{border-bottom:1px solid rgba(11,26,38,.1)}
.cso-fase__num{font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);font-size:28px;color:var(--c-coral,#ff6b4a);line-height:1;font-weight:800}
.cso-fase__titolo{font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);font-size:24px;font-weight:700;text-transform:uppercase;color:var(--c-deep,#0a2540);margin:0 0 6px;line-height:1.05}
.cso-fase__desc{font-size:14px;color:rgba(11,26,38,.72);line-height:1.6;margin:0}
.cso-fase__ore{font-family:var(--f-mono,"DM Mono",monospace);font-size:11px;color:var(--c-wave,#1B77A7);letter-spacing:.08em;text-transform:uppercase;text-align:right;align-self:start;padding-top:6px;line-height:1.6}

/* ── Competenze ── */
.cso-competenze-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 32px}
@media(max-width:600px){.cso-competenze-grid{grid-template-columns:1fr}}
.cso-competenza{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid rgba(11,26,38,.08);font-size:14px;color:var(--c-ink,#0b1a26);line-height:1.5}
.cso-competenza__plus{color:var(--c-coral,#ff6b4a);font-weight:700;font-size:16px;flex-shrink:0;line-height:1.4;min-width:14px}

/* ── Docenti ── */
.cso-docenti-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px}
.cso-docente-mini{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(10,37,64,.08);text-align:center;padding:18px 16px;color:var(--c-ink,#0b1a26);transition:transform .2s,box-shadow .2s;display:block}
.cso-docente-mini:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(10,37,64,.14)}
.cso-docente-mini img{width:64px;height:64px;border-radius:50%;object-fit:cover;margin-bottom:10px;border:2px solid var(--c-foam,#cfe9ee)}
.cso-docente-mini__avatar{width:64px;height:64px;border-radius:50%;background:var(--c-foam,#cfe9ee);display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 10px}
.cso-docente-mini__name{font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);font-size:16px;font-weight:700;color:var(--c-deep,#0a2540);margin:0 0 3px}
.cso-docente-mini__ruolo{font-size:12px;color:var(--c-wave,#1B77A7)}

/* ── Sidebar navy ── */
.cso-sintesi{background:var(--c-deep,#0a2540);border-radius:12px;box-shadow:0 6px 32px rgba(10,37,64,.28);position:sticky;top:24px;color:#fff;display:flex;flex-direction:column}
@media(max-width:900px){.cso-sintesi{position:static}}
.cso-sintesi h2,.cso-sintesi h3{color:#fff}
.cso-sintesi p,.cso-sintesi span,.cso-sintesi div{color:inherit}

.cso-sintesi__head{padding:24px 24px 20px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-sintesi__cert{font-family:var(--f-mono,"DM Mono",monospace);font-size:11px;color:var(--c-aqua,#26CBFB);letter-spacing:.12em;text-transform:uppercase;font-weight:600;margin:0 0 12px;display:block}
.cso-sintesi__title{font-size:36px;font-weight:900;color:#fff;margin:0;line-height:.96;letter-spacing:-.01em}

.cso-sintesi__stats{padding:8px 24px 20px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-stat-row{display:flex;justify-content:space-between;align-items:baseline;padding:14px 0;border-top:1px solid rgba(255,255,255,.12)}
.cso-stat-row:first-child{border-top:none}
.cso-stat-row__label{font-family:var(--f-mono,"DM Mono",monospace);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--c-aqua,#26CBFB);font-weight:600}
.cso-stat-row__val{font-weight:600;font-size:15px;color:#fff}

.cso-sintesi__inizi{padding:20px 24px 24px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-inizi-label{font-family:var(--f-mono,"DM Mono",monospace);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--c-aqua,#26CBFB);font-weight:600;margin:0 0 14px;display:block}
.cso-inizio-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-top:1px solid rgba(255,255,255,.08);font-size:14px}
.cso-inizio-row:first-child{border-top:none}
.cso-inizio-row__date{color:#fff;font-weight:500}
.cso-inizio-row__luogo{font-size:13px;font-weight:400;color:rgba(255,255,255,.55)}
.cso-inizio-row__spots{font-family:var(--f-mono,"DM Mono",monospace);font-size:11px;letter-spacing:.04em}
.cso-inizio-row__spots--ok{color:rgba(255,255,255,.6)}
.cso-inizio-row__spots--warn{color:var(--c-coral,#ff6b4a)}
.cso-inizio-row__spots--full{color:rgba(255,255,255,.35)}

.cso-sintesi__cta{padding:20px 24px 24px}
.cso-btn-primary{display:block;background:var(--c-coral,#ff6b4a);color:#fff;font-family:var(--f-body,"DM Sans",-apple-system,sans-serif);font-size:14px;font-weight:600;letter-spacing:.02em;padding:14px 22px;border-radius:999px;text-align:center;text-decoration:none;transition:background .15s;margin-bottom:10px;box-shadow:0 6px 18px -4px rgba(255,107,74,.55)}
.cso-btn-primary:hover{background:#e04a2a;color:#fff}
.cso-btn-secondary{display:block;text-align:center;padding:12px 22px;background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:999px;font-size:14px;font-weight:600;text-decoration:none;transition:border-color .15s,background .15s}
.cso-btn-secondary:hover{border-color:#fff;background:rgba(255,255,255,.08);color:#fff}

/* ── Corsi correlati ── */
.cso-related{background:var(--c-bone,#f6f1e6);padding:80px 48px 96px}
@media(max-width:900px){.cso-related{padding:48px 20px}}
.cso-related__inner{max-width:1200px;margin:0 auto}
.cso-related__header{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:40px;flex-wrap:wrap;gap:24px}
.cso-related__all{font-size:14px;font-weight:600;color:var(--c-deep,#0a2540);text-decoration:none;display:flex;align-items:center;gap:6px}
.cso-related__all:hover{color:var(--c-wave,#1B77A7)}
.cso-related__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
@media(max-width:700px){.cso-related__grid{grid-template-columns:1fr}}

.cso-thumb{background:#fff;border-radius:14px;overflow:hidden;border:1px solid rgba(11,26,38,.08);display:flex;flex-direction:column;text-decoration:none;color:var(--c-ink,#0b1a26);transition:transform .2s,box-shadow .2s}
.cso-thumb:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(10,37,64,.14)}
.cso-thumb img{width:100%;height:160px;object-fit:cover;display:block}
.cso-thumb__placeholder{width:100%;height:160px;background:linear-gradient(135deg,var(--c-deep,#0a2540),var(--c-wave,#1B77A7));display:flex;align-items:center;justify-content:center;font-size:32px}
.cso-thumb__body{padding:24px;flex:1;display:flex;flex-direction:column}
.cso-thumb__level{display:inline-flex;padding:4px 10px;background:rgba(29,111,156,.1);color:var(--c-wave,#1B77A7);border-radius:999px;font-size:11px;font-weight:600;align-self:flex-start;margin-bottom:14px}
.cso-thumb__title{font-family:var(--f-display,"Big Shoulders Display",Impact,sans-serif);font-size:26px;font-weight:800;text-transform:uppercase;color:var(--c-deep,#0a2540);margin:0 0 8px;line-height:1}
.cso-thumb__desc{font-size:13px;color:rgba(11,26,38,.65);margin:0 0 18px;flex:1}
.cso-thumb__link{font-size:13px;font-weight:600;color:var(--c-coral,#ff6b4a);display:flex;align-items:center;gap:6px}
</style>

<div class="cso">

<!-- HERO: dark navy con breadcrumb, titolo grande, foto -->
<section class="cso-hero">
<div class="cso-hero__inner">

	<nav class="cso-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'calypsosub' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'calypsosub' ); ?></a>
		<span>/</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_corso' ) ); ?>"><?php _e( 'Corsi', 'calypsosub' ); ?></a>
		<span>/</span>
		<span class="cso-breadcrumb__current"><?php the_title(); ?></span>
	</nav>

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

	<?php if ( $img ) : ?>
		<img class="cso-hero__img"
		     src="<?php echo esc_url( $img ); ?>"
		     alt="<?php echo esc_attr( get_the_title() ); ?>">
	<?php else : ?>
		<div class="cso-hero__img-placeholder">🤿</div>
	<?php endif; ?>

</div>
</section>

<!-- CORPO: contenuto + sidebar sticky -->
<div class="cso-layout">

<main>

	<?php if ( $fasi ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php _e( 'Il programma', 'calypsosub' ); ?></span>
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
		<span class="cso-eyebrow"><?php _e( 'Cosa imparerai', 'calypsosub' ); ?></span>
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
		<span class="cso-eyebrow"><?php _e( 'Materiale', 'calypsosub' ); ?></span>
		<h2 class="cso-display-heading"><?php _e( 'Cosa è incluso.', 'calypsosub' ); ?></h2>
		<p class="cso-lead" style="white-space:pre-line"><?php echo esc_html( $materiale ); ?></p>
	</div>
	<?php endif; ?>

	<?php
	$all_shown = $docenti_ids;
	if ( $direttore_id && ! in_array( $direttore_id, $all_shown, true ) ) {
		array_unshift( $all_shown, $direttore_id );
	}
	if ( ! empty( $all_shown ) ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php _e( 'I docenti', 'calypsosub' ); ?></span>
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
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $did, 'thumbnail' ) ); ?>"
					     alt="<?php echo esc_attr( get_the_title( $did ) ); ?>">
				<?php else : ?>
					<div class="cso-docente-mini__avatar">🤿</div>
				<?php endif; ?>
				<p class="cso-docente-mini__name"><?php echo esc_html( get_the_title( $did ) ); ?></p>
				<?php if ( $label ) : ?>
					<p class="cso-docente-mini__ruolo"><?php echo esc_html( $label ); ?></p>
				<?php endif; ?>
			</a>
			<?php endforeach; ?>
		</div>
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
		<h2 class="cso-sintesi__title"><?php _e( 'In sintesi', 'calypsosub' ); ?></h2>
	</div>

	<?php
	$stats = array_filter( [
		__( 'Durata',     'calypsosub' ) => $stat_durata,
		__( 'Immersioni', 'calypsosub' ) => $stat_pratica,
		__( 'Profondità', 'calypsosub' ) => $stat_profondita,
		__( 'Periodo',    'calypsosub' ) => $periodo,
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

	<?php if ( ! empty( $prossime ) ) : ?>
	<div class="cso-sintesi__inizi">
		<span class="cso-inizi-label"><?php _e( 'Prossimi inizi', 'calypsosub' ); ?></span>
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
		<?php if ( $link_iscrizione ) : ?>
			<a href="<?php echo esc_url( $link_iscrizione ); ?>"
			   class="cso-btn-primary" target="_blank" rel="noopener">
				<?php _e( 'Iscriviti al corso →', 'calypsosub' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( $contatto ) : ?>
			<a href="<?php echo esc_attr( strpos( $contatto, '@' ) !== false
				? 'mailto:' . $contatto
				: 'tel:' . preg_replace( '/[^+0-9]/', '', $contatto ) ); ?>"
			   class="cso-btn-secondary">
				<?php echo esc_html( __( 'Chiamaci · ', 'calypsosub' ) . $contatto ); ?>
			</a>
		<?php endif; ?>
	</div>

</div>
</aside>

</div><!-- .cso-layout -->

<?php if ( ! empty( $related ) ) : ?>
<section class="cso-related">
<div class="cso-related__inner">
	<div class="cso-related__header">
		<div>
			<span class="cso-related__eyebrow"><?php _e( 'Continua a scendere', 'calypsosub' ); ?></span>
			<h2 class="cso-display-heading" style="margin:0"><?php _e( 'Altri corsi.', 'calypsosub' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_corso' ) ); ?>"
		   class="cso-related__all"><?php _e( 'Tutti i corsi →', 'calypsosub' ); ?></a>
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
				<span class="cso-thumb__link"><?php _e( 'Scopri il corso →', 'calypsosub' ); ?></span>
			</div>
		</a>
		<?php endforeach; ?>
	</div>
</div>
</section>
<?php endif; ?>

</div><!-- .cso -->

<?php get_footer(); ?>
