<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}

$id = get_the_ID();

$desc_breve          = (string) get_post_meta( $id, '_uscita_desc_breve', true );
$luogo               = (string) get_post_meta( $id, '_uscita_luogo', true );
$ritrovo             = (string) get_post_meta( $id, '_uscita_ritrovo', true );
$incluso             = (string) get_post_meta( $id, '_uscita_incluso', true );
$cosa_portare        = (string) get_post_meta( $id, '_uscita_cosa_portare', true );
$note_cancellazione  = (string) get_post_meta( $id, '_uscita_note_cancellazione', true );
$imbarco_mezzo       = (string) get_post_meta( $id, '_uscita_imbarco_mezzo', true );
$rientro_previsto    = (string) get_post_meta( $id, '_uscita_rientro_previsto', true );
$num_immersioni_raw  = get_post_meta( $id, '_uscita_num_immersioni', true );
$fauna_extra         = (string) get_post_meta( $id, '_uscita_fauna_extra', true );
$immersioni          = (array)  ( get_post_meta( $id, '_uscita_immersioni', true ) ?: [] );
$programma_override  = (array)  ( get_post_meta( $id, '_uscita_programma_override', true ) ?: [] );
$galleria_ids        = array_values( array_filter( array_map( 'absint', (array) ( get_post_meta( $id, '_uscita_galleria', true ) ?: [] ) ) ) );

$livelli = wp_get_post_terms( $id, 'calypso_livello', [ 'fields' => 'names' ] );
$livello = ( ! is_wp_error( $livelli ) && ! empty( $livelli ) ) ? implode( ' · ', $livelli ) : '';

$fauna_terms = wp_get_post_terms( $id, 'calypso_fauna', [ 'fields' => 'names' ] );
$fauna_terms = is_wp_error( $fauna_terms ) ? [] : $fauna_terms;
$fauna_extra_list = $fauna_extra
	? array_values( array_filter( array_map( 'trim', explode( ',', $fauna_extra ) ) ) )
	: [];
$fauna_tags = array_merge( $fauna_terms, $fauna_extra_list );

$img     = get_the_post_thumbnail_url( $id, 'full' );
$hero_bg = get_post_meta( $id, '_hero_use_featured_image', true ) === '1' && $img;

$fmt = static function ( string $dt ): string {
	$ts = strtotime( $dt );
	return $ts ? wp_date( 'j F Y — H:i', $ts ) : $dt;
};
$fmt_time = static function ( string $dt ): string {
	$ts = strtotime( $dt );
	return $ts ? wp_date( 'H:i', $ts ) : '';
};

$oggi       = current_time( 'Y-m-d\TH:i' );
$occorrenze = calypso_get_occorrenze_by_uscita( $id );
$prossima_occ = null;
foreach ( $occorrenze as $occ ) {
	$occ_data = (string) get_post_meta( $occ->ID, '_occorrenza_uscita_data', true );
	if ( $occ_data >= $oggi ) { $prossima_occ = $occ; break; }
}
if ( ! $prossima_occ && $occorrenze ) $prossima_occ = end( $occorrenze );
$prossima      = $prossima_occ ? (string) get_post_meta( $prossima_occ->ID, '_occorrenza_uscita_data', true ) : '';
$prossima_time = $prossima ? $fmt_time( $prossima ) : '';

$num_immersioni = ( $num_immersioni_raw !== '' && $num_immersioni_raw !== false )
	? (int) $num_immersioni_raw
	: count( $immersioni );

$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );
$prenotazioni_page_ok = $prenotazioni_page_id && get_post_status( $prenotazioni_page_id ) === 'publish';

$booking_link = static function ( int $occorrenza_id ) use ( $prenotazioni_page_id ): string {
	return add_query_arg( 'prenota_id', $occorrenza_id, get_permalink( $prenotazioni_page_id ) );
};

global $calypsosub_booking_manager;

/* ── Programma della giornata: override manuale oppure derivato ── */
if ( ! empty( $programma_override ) ) {
	$programma = $programma_override;
} else {
	$programma = [];
	if ( $ritrovo ) {
		$programma[] = [
			'ora'         => $prossima_time,
			'titolo'      => __( 'Ritrovo', 'calypsosub' ),
			'descrizione' => $ritrovo,
		];
	}
	if ( $imbarco_mezzo ) {
		$programma[] = [
			'ora'         => '',
			'titolo'      => __( 'Imbarco', 'calypsosub' ),
			'descrizione' => $imbarco_mezzo,
		];
	}
	foreach ( $immersioni as $n => $imm ) {
		$programma[] = [
			'ora'         => $imm['ora'] ?? '',
			'titolo'      => ( $imm['nome'] ?? '' ) ?: sprintf( __( '%d° immersione', 'calypsosub' ), $n + 1 ),
			'descrizione' => $imm['descrizione'] ?? '',
		];
	}
	if ( $rientro_previsto ) {
		$programma[] = [
			'ora'         => $rientro_previsto,
			'titolo'      => __( 'Rientro', 'calypsosub' ),
			'descrizione' => $ritrovo,
		];
	}
}

$related = calypso_get_prossime_uscite_escluso( $id, 3 );

$gallery_units = Calypsosub_Gallery_Helpers::build_units(
	Calypsosub_Gallery_Helpers::build_cells_from_attachments( $galleria_ids )
);
?>
<style>
.cso{color:var(--c-ink,#0b1a26)}
.cso h1,.cso h2,.cso h3,.cso h4{color:var(--c-wave,#1B77A7);text-transform:uppercase}
.cso a{color:inherit;text-decoration:none}
.cso p,.cso li,.cso span,.cso div{color:inherit}
.cso-related h2,.cso-related h3,.cso-related h4{color:var(--c-deep,#0a2540)}

/* ── Hero ── */
.cso-hero{background:var(--c-deep,#0a2540);color:#fff;padding:calc(var(--cso-header-h) + 40px) 48px 64px;position:relative}
.cso-hero--bg-img{background-size:cover;background-position:center center;background-repeat:no-repeat}
.cso-hero--bg-img .cso-hero__inner{position:relative;z-index:1}
.cso-hero__overlay{position:absolute;inset:0;pointer-events:none;z-index:0}
.cso-hero h1,.cso-hero h2,.cso-hero h3{text-shadow:0 2px 14px rgba(0,0,0,.9),0 5px 36px rgba(0,0,0,.75),0 12px 56px rgba(0,0,0,.5)}
.cso-hero a{color:rgba(255,255,255,.55);text-decoration:none}
.cso-hero a:hover{color:rgba(255,255,255,.9)}
@media(max-width:1024px){.cso-hero{padding:calc(var(--cso-header-h) + 24px) 20px 40px}}

.cso-hero__inner{max-width:1320px;margin:0 auto}
.cso-hero__header{display:grid;grid-template-columns:1.4fr 1fr;gap:80px;align-items:end;margin-bottom:40px}
@media(max-width:1024px){.cso-hero__header{grid-template-columns:1fr;gap:24px;margin-bottom:32px}}

.cso-hero__badge{background:var(--c-coral,#ff6b4a);display:inline-block;padding:8px 16px;border-radius:999px;letter-spacing:.06em;text-transform:uppercase;line-height:1}
.cso-hero__sub{margin-top:16px;line-height:1.5}
.cso-hero__lead{line-height:1.6;margin:0;align-self:end}

.cso-hero__stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:40px}
@media(max-width:700px){.cso-hero__stats{grid-template-columns:1fr}}
.cso-hero-stat{border-radius:10px;padding:16px 20px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px)}
.cso-hero-stat__label{letter-spacing:.1em;text-transform:uppercase;font-weight:600;opacity:.65;display:block;margin-bottom:6px}
.cso .cso-hero-stat__label{font-size:12px}
.cso-hero-stat__val{font-weight:800}
.cso .cso-hero-stat__val{font-size:22px}

.cso-hero__img{width:100%;height:480px;object-fit:cover;display:block;border-radius:12px}
.cso-hero__img-wrap{position:relative}
.cso-hero__img-cap{position:absolute;bottom:0;left:0;letter-spacing:.1em;text-transform:uppercase;padding:10px 14px;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);border-top-right-radius:8px;border-bottom-left-radius:12px}
.cso .cso-hero__img-cap{font-size:11px;color:rgba(255,255,255,.9)}

/* ── Layout corpo ── */
.cso-layout{max-width:1320px;margin:0 auto;padding:80px 48px 48px;display:grid;grid-template-columns:1.5fr 1fr;gap:64px;align-items:start}
@media(max-width:1024px){.cso-layout{grid-template-columns:1fr;padding:40px 20px}.cso-layout aside{order:-1}}

.cso-section{margin-bottom:72px}
.cso-section:last-child{margin-bottom:0}

.cso-eyebrow{font-weight:500;letter-spacing:.16em;text-transform:uppercase;margin:0 0 14px;display:block}
.cso .cso-eyebrow{font-size:16px;color:var(--c-wave,#1B77A7)}
.cso-display-heading{font-size:clamp(28px,4vw,56px);font-weight:800;text-transform:uppercase;line-height:.96;margin:0 0 24px}
.cso-prose{font-size:17px;line-height:1.75;max-width:720px}
.cso .cso-prose{color:var(--c-ink,#0b1a26)}
.cso-prose p{margin:0 0 1em}
.cso-prose p:last-child{margin-bottom:0}

/* ── Immersioni ── */
.cso-immersioni-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:700px){.cso-immersioni-grid{grid-template-columns:1fr}}
.cso-immersione-card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px -14px rgba(10,37,64,.3)}
.cso-immersione-card__img{width:100%;height:180px;object-fit:cover;display:block;background:linear-gradient(135deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7))}
.cso-immersione-card__body{padding:20px 22px}
.cso-immersione-card__eyebrow{letter-spacing:.1em;text-transform:uppercase;font-weight:600}
.cso .cso-immersione-card__eyebrow{font-size:12px;color:var(--c-gold,#E9BF26)}
.cso-immersione-card__title{font-weight:700;text-transform:uppercase;margin:6px 0}
.cso .cso-immersione-card__title{font-size:22px;color:var(--c-wave,#1B77A7)}
.cso-immersione-card__desc{line-height:1.6;margin:0 0 14px}
.cso .cso-immersione-card__desc{font-size:15px;color:rgba(11,26,38,.75)}
.cso-immersione-card__meta{display:flex;gap:18px;flex-wrap:wrap;border-top:1px solid rgba(11,26,38,.08);padding-top:12px}
.cso-immersione-card__meta-item{display:flex;flex-direction:column;gap:2px}
.cso .cso-immersione-card__meta-val{font-size:15px;font-weight:700;color:var(--c-deep,#0a2540)}
.cso .cso-immersione-card__meta-label{font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:rgba(11,26,38,.5)}

/* ── Programma (timeline) ── */
.cso-programma{margin:0;padding:0;list-style:none}
.cso-programma>li{list-style:none}
.cso-tappa{display:grid;grid-template-columns:70px 1fr;gap:20px;padding:20px 0;border-top:1px solid rgba(11,26,38,.1);align-items:start}
.cso-tappa:last-child{border-bottom:1px solid rgba(11,26,38,.1)}
.cso-tappa__ora{line-height:1.2;font-weight:800}
.cso .cso-tappa__ora{font-size:20px;color:var(--c-gold,#E9BF26)}
.cso-tappa__titolo{font-weight:700;text-transform:uppercase;margin:0 0 6px;line-height:1.05}
.cso .cso-tappa__titolo{font-size:22px;color:var(--c-wave,#1B77A7)}
.cso-tappa__desc{line-height:1.6;margin:0}
.cso .cso-tappa__desc{font-size:16px;color:var(--c-ink,#0b1a26)}

/* ── Tag fauna ── */
.cso-tags{display:flex;flex-wrap:wrap;gap:10px}
.cso-tag{display:inline-flex;padding:8px 16px;background:rgba(29,111,156,.1);color:var(--c-wave,#1B77A7);border-radius:999px;font-weight:600}
.cso .cso-tag{font-size:14px}

/* ── Galleria ── */
.cso-usc-gallery__grid{display:grid;grid-template-columns:repeat(3,1fr);grid-auto-rows:180px;gap:12px}
@media(max-width:700px){.cso-usc-gallery__grid{grid-template-columns:repeat(2,1fr);grid-auto-rows:140px}}
.cso-usc-gallery-item{border-radius:12px;overflow:hidden;position:relative;background:linear-gradient(135deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7))}
.cso-usc-gallery-item img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}

/* ── Sidebar navy ── */
.cso-sintesi{background:var(--c-deep,#0a2540);border-radius:18px;box-shadow:0 6px 32px rgba(10,37,64,.28);position:sticky;top:24px;color:#fff;display:flex;flex-direction:column}
@media(max-width:1024px){.cso-sintesi{position:static}}
.cso-sintesi h2,.cso-sintesi h3{color:#fff}
.cso-sintesi p,.cso-sintesi span,.cso-sintesi div{color:inherit}

.cso-sintesi__head{padding:24px 24px 20px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-sintesi__cert{letter-spacing:.12em;text-transform:uppercase;font-weight:600;margin:0 0 12px;display:block}
.cso .cso-sintesi__cert{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-sintesi__title{font-size:28px;font-weight:900;color:#fff;margin:0;line-height:.96;letter-spacing:-.01em}

.cso-sintesi__stats{padding:8px 24px 0;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-stat-row{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;padding:14px 0;border-top:1px solid rgba(255,255,255,.12)}
.cso-stat-row:first-child{border-top:none}
.cso-stat-row__label{letter-spacing:.08em;text-transform:uppercase;font-weight:600;flex-shrink:0}
.cso-sintesi .cso-stat-row__label{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-stat-row__val{font-weight:600;text-align:right}
.cso-sintesi .cso-stat-row__val{font-size:16px;color:#fff}
@media(max-width:600px){.cso-stat-row{flex-direction:column;gap:4px}.cso-stat-row__val{text-align:left}}

.cso-sintesi__checklist{padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-sintesi__checklist-label{letter-spacing:.08em;text-transform:uppercase;font-weight:600;margin:0 0 14px;display:block}
.cso-sintesi .cso-sintesi__checklist-label{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-checklist{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px}
.cso-checklist li{display:flex;align-items:flex-start;gap:8px}
.cso .cso-checklist li{font-size:16px;color:#fff}
.cso-checklist__check{color:var(--c-aqua,#26CBFB);flex-shrink:0}

.cso-sintesi__inizi{padding:20px 24px 24px;border-bottom:1px solid rgba(255,255,255,.12)}
.cso-inizi-label{letter-spacing:.12em;text-transform:uppercase;font-weight:600;margin:0 0 6px;display:block}
.cso .cso-inizi-label{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-inizi-note{opacity:.55;margin:0 0 14px}
.cso .cso-inizi-note{font-size:13px}
.cso-inizio-row{padding:12px 0;border-top:1px solid rgba(255,255,255,.08);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px}
.cso-inizio-row:first-child{border-top:none}
.cso-inizio-row__date{font-weight:500}
.cso .cso-inizio-row__date{color:#fff;font-size:16px}
.cso-inizio-row__spots{font-size:13px;letter-spacing:.03em}
.cso-inizio-row__spots--ok{color:rgba(255,255,255,.6)}
.cso-inizio-row__spots--warn{color:var(--c-coral,#ff6b4a)}
.cso-inizio-row__spots--full{color:rgba(255,255,255,.35)}
.cso-inizio-row__cta{display:inline-block;padding:8px 16px;background:var(--c-coral,#ff6b4a);color:#fff;border-radius:999px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;text-decoration:none}
.cso .cso-inizio-row__cta{font-size:12px}
.cso-inizio-row__cta--disabled{background:rgba(255,255,255,.15);color:rgba(255,255,255,.5);cursor:default}

.cso-sintesi__box{padding:20px 24px}
.cso-sintesi__box+.cso-sintesi__box{border-top:1px solid rgba(255,255,255,.12)}
.cso-sintesi__box-label{letter-spacing:.08em;text-transform:uppercase;font-weight:600;margin:0 0 10px;display:block}
.cso-sintesi .cso-sintesi__box-label{font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso .cso-sintesi__box-text{font-size:14px;line-height:1.6;color:rgba(255,255,255,.8);margin:0;white-space:pre-line}

/* ── Uscite correlate ── */
.cso-related{background:var(--c-bone,#f6f1e6);padding:80px 48px 96px}
@media(max-width:1024px){.cso-related{padding:48px 20px}}
.cso-related__inner{max-width:1320px;margin:0 auto}
.cso-related__header{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:40px;flex-wrap:wrap;gap:24px}
.cso-related__all{font-size:16px;font-weight:600;color:var(--c-deep,#0a2540);text-decoration:none;display:flex;align-items:center;gap:6px}
.cso-related__all:hover{color:var(--c-wave,#1B77A7)}
.cso-related__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
@media(max-width:900px){.cso-related__grid{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.cso-related__grid{grid-template-columns:1fr}}

.cso-thumb{background:#fff;border-radius:14px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 8px 24px -16px rgba(10,37,64,.3)}
.cso-thumb img{width:100%;height:180px;object-fit:cover;display:block}
.cso-thumb__placeholder{width:100%;height:180px;background:linear-gradient(135deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7));display:flex;align-items:center;justify-content:center;font-size:40px}
.cso-thumb__body{padding:18px 20px;display:flex;flex-direction:column;gap:6px;flex:1}
.cso-thumb__level{display:inline-flex;padding:4px 10px;background:rgba(29,111,156,.1);color:var(--c-wave,#1B77A7);border-radius:999px;font-weight:600;align-self:flex-start;font-size:11px}
.cso .cso-thumb__title{color:var(--c-deep,#0a2540);font-size:19px;font-weight:700;margin:0;text-transform:uppercase}
.cso .cso-thumb__desc{color:rgba(11,26,38,.65);font-size:14px;margin:0}
.cso-thumb__link{font-size:14px;font-weight:600;color:var(--c-coral,#ff6b4a);display:flex;align-items:center;gap:6px;margin-top:auto}
</style>
<?php
$_ud = [
	'hero_bg'           => calypsosub_opt( 'uscite', 'design_hero_bg',               '#0a2540' ),
	'hero_overlay'      => calypsosub_opt( 'uscite', 'design_hero_overlay_color',    '#061826' ),
	'badge_bg'          => calypsosub_opt( 'uscite', 'design_badge_bg',              '#ff6b4a' ),
	'hero_badge_color'  => calypsosub_opt( 'uscite', 'design_hero_badge_color',      '#ffffff' ),
	'hero_badge_size'   => (int) calypsosub_opt( 'uscite', 'design_hero_badge_size', '14' ),
	'hero_badge_weight' => (int) calypsosub_opt( 'uscite', 'design_hero_badge_weight', '600' ),
	'hero_title_color'  => calypsosub_opt( 'uscite', 'design_hero_title_color',      '#ffffff' ),
	'hero_title_size'   => (int) calypsosub_opt( 'uscite', 'design_hero_title_size', '96' ),
	'hero_title_weight' => (int) calypsosub_opt( 'uscite', 'design_hero_title_weight', '700' ),
	'hero_title_font'   => preg_replace( '/[^a-zA-Z0-9 ,\"\'\-]/', '', calypsosub_opt( 'uscite', 'design_hero_title_font', '' ) ),
	'hero_sub_color'    => calypsosub_opt( 'uscite', 'design_hero_sub_color',        '#26CBFB' ),
	'hero_sub_size'     => (int) calypsosub_opt( 'uscite', 'design_hero_sub_size',   '16' ),
	'hero_sub_weight'   => (int) calypsosub_opt( 'uscite', 'design_hero_sub_weight', '600' ),
	'hero_lead_color'   => calypsosub_opt( 'uscite', 'design_hero_lead_color',       '#ffffff' ),
	'hero_lead_opacity' => (int) calypsosub_opt( 'uscite', 'design_hero_lead_opacity','85' ),
	'hero_lead_size'    => (int) calypsosub_opt( 'uscite', 'design_hero_lead_size',  '18' ),
	'hero_lead_font'    => preg_replace( '/[^a-zA-Z0-9 ,\"\'\-]/', '', calypsosub_opt( 'uscite', 'design_hero_lead_font', '' ) ),
	'hero_stat_bg'      => calypsosub_opt( 'uscite', 'design_hero_stat_bg',          'rgba(255,255,255,.08)' ),
	'hero_stat_label'   => calypsosub_opt( 'uscite', 'design_hero_stat_label_color', '#26CBFB' ),
	'hero_stat_value'   => calypsosub_opt( 'uscite', 'design_hero_stat_value_color', '#ffffff' ),
	'eyebrow'        => calypsosub_opt( 'uscite', 'design_eyebrow',         '#1B77A7' ),
	'sidebar_accent' => calypsosub_opt( 'uscite', 'design_sidebar_accent',  '#26CBFB' ),
	'related_bg'     => calypsosub_opt( 'uscite', 'design_related_bg',      '#f6f1e6' ),
];
?>
<style>
.cso-hero{background:<?php echo esc_attr($_ud['hero_bg']); ?>}
.cso-hero__overlay{background:linear-gradient(180deg,<?php echo calypso_hex2rgba($_ud['hero_overlay'],.5); ?> 0%,<?php echo calypso_hex2rgba($_ud['hero_overlay'],.4); ?> 50%,<?php echo calypso_hex2rgba($_ud['hero_overlay'],.75); ?> 100%)}
.cso-hero__badge{background:<?php echo esc_attr($_ud['badge_bg']); ?>}
.cso .cso-hero__badge{color:<?php echo esc_attr($_ud['hero_badge_color']); ?>;font-size:<?php echo $_ud['hero_badge_size']; ?>px;font-weight:<?php echo $_ud['hero_badge_weight']; ?>}
.cso .cso-hero__title{
	color:<?php echo esc_attr($_ud['hero_title_color']); ?>;
	font-size:clamp(40px,7vw,<?php echo $_ud['hero_title_size']; ?>px);
	font-weight:<?php echo $_ud['hero_title_weight']; ?>;
	<?php if ($_ud['hero_title_font']) : ?>font-family:<?php echo $_ud['hero_title_font']; ?>;<?php endif; ?>
}
.cso .cso-hero__sub{color:<?php echo esc_attr($_ud['hero_sub_color']); ?>;font-size:<?php echo $_ud['hero_sub_size']; ?>px;font-weight:<?php echo $_ud['hero_sub_weight']; ?>}
.cso .cso-hero__lead{
	color:<?php echo esc_attr($_ud['hero_lead_color']); ?>;
	opacity:<?php echo max( 0, min( 100, $_ud['hero_lead_opacity'] ) ) / 100; ?>;
	font-size:<?php echo $_ud['hero_lead_size']; ?>px;
	<?php if ($_ud['hero_lead_font']) : ?>font-family:<?php echo $_ud['hero_lead_font']; ?>;<?php endif; ?>
}
.cso-hero-stat{background:<?php echo esc_attr($_ud['hero_stat_bg']); ?>}
.cso .cso-hero-stat__label{color:<?php echo esc_attr($_ud['hero_stat_label']); ?>}
.cso .cso-hero-stat__val{color:<?php echo esc_attr($_ud['hero_stat_value']); ?>}
.cso .cso-eyebrow{color:<?php echo esc_attr($_ud['eyebrow']); ?>}
.cso .cso-tappa__titolo{color:<?php echo esc_attr($_ud['eyebrow']); ?>}
.cso-sintesi{background:<?php echo esc_attr($_ud['hero_bg']); ?>}
.cso-sintesi .cso-sintesi__cert{color:<?php echo esc_attr($_ud['sidebar_accent']); ?>}
.cso-sintesi .cso-stat-row__label{color:<?php echo esc_attr($_ud['sidebar_accent']); ?>}
.cso-sintesi .cso-sintesi__checklist-label{color:<?php echo esc_attr($_ud['sidebar_accent']); ?>}
.cso-sintesi .cso-inizi-label{color:<?php echo esc_attr($_ud['sidebar_accent']); ?>}
.cso-sintesi .cso-sintesi__box-label{color:<?php echo esc_attr($_ud['sidebar_accent']); ?>}
.cso-inizio-row__cta{background:<?php echo esc_attr($_ud['badge_bg']); ?>}
.cso-thumb__link{color:<?php echo esc_attr($_ud['badge_bg']); ?>}
.cso-related{background:<?php echo esc_attr($_ud['related_bg']); ?>}
</style>

<div class="cso">

<!-- HERO -->
<section class="cso-hero<?php echo $hero_bg ? ' cso-hero--bg-img' : ''; ?>"
         <?php if ( $hero_bg ) : ?>style="background-image:url('<?php echo esc_url( $img ); ?>')"<?php endif; ?>>
<?php if ( $hero_bg ) : ?><div class="cso-hero__overlay"></div><?php endif; ?>
<div class="cso-hero__inner">

	<nav class="cso-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'calypsosub' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'calypsosub' ); ?></a>
		<span>/</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_uscita' ) ); ?>"><?php echo esc_html( calypsosub_opt( 'uscite', 'breadcrumb_archive', __( 'Uscite', 'calypsosub' ) ) ); ?></a>
		<span>/</span>
		<span class="cso-breadcrumb__current"><?php the_title(); ?></span>
	</nav>

	<?php if ( $hero_bg ) : ?>
	<div>
		<?php if ( $livello || $luogo ) : ?>
			<div class="cso-hero__badge"><?php echo esc_html( trim( ( $livello ?: calypsosub_opt( 'uscite', 'badge', __( 'Itinerario in barca', 'calypsosub' ) ) ) . ( $luogo ? ' · ' . $luogo : '' ) ) ); ?></div>
		<?php endif; ?>
		<h1 class="cso-hero__title"><?php the_title(); ?></h1>
		<?php if ( count( $occorrenze ) > 1 ) : ?>
			<div class="cso-hero__sub">📅 <?php echo esc_html( calypsosub_opt( 'uscite', 'hero_note_piu_date', __( 'Proposta più volte durante la stagione — vedi le date disponibili', 'calypsosub' ) ) ); ?></div>
		<?php endif; ?>
		<?php if ( $desc_breve ) : ?>
			<p class="cso-hero__lead" style="max-width:640px;margin-top:24px"><?php echo esc_html( $desc_breve ); ?></p>
		<?php endif; ?>
	</div>
	<?php else : ?>
	<div class="cso-hero__header">
		<div>
			<?php if ( $livello || $luogo ) : ?>
				<div class="cso-hero__badge"><?php echo esc_html( trim( ( $livello ?: calypsosub_opt( 'uscite', 'badge', __( 'Itinerario in barca', 'calypsosub' ) ) ) . ( $luogo ? ' · ' . $luogo : '' ) ) ); ?></div>
			<?php endif; ?>
			<h1 class="cso-hero__title"><?php the_title(); ?></h1>
			<?php if ( count( $occorrenze ) > 1 ) : ?>
				<div class="cso-hero__sub">📅 <?php echo esc_html( calypsosub_opt( 'uscite', 'hero_note_piu_date', __( 'Proposta più volte durante la stagione — vedi le date disponibili', 'calypsosub' ) ) ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $desc_breve ) : ?>
			<p class="cso-hero__lead"><?php echo esc_html( $desc_breve ); ?></p>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ( $num_immersioni || $livello || $luogo || $imbarco_mezzo ) : ?>
	<div class="cso-hero__stats">
		<?php if ( $num_immersioni ) : ?>
		<div class="cso-hero-stat">
			<span class="cso-hero-stat__label"><?php esc_html_e( 'Immersioni', 'calypsosub' ); ?></span>
			<span class="cso-hero-stat__val"><?php echo (int) $num_immersioni; ?></span>
		</div>
		<?php endif; ?>
		<?php if ( $livello ) : ?>
		<div class="cso-hero-stat">
			<span class="cso-hero-stat__label"><?php esc_html_e( 'Livello richiesto', 'calypsosub' ); ?></span>
			<span class="cso-hero-stat__val"><?php echo esc_html( $livello ); ?></span>
		</div>
		<?php endif; ?>
		<?php if ( $luogo || $imbarco_mezzo ) : ?>
		<div class="cso-hero-stat">
			<span class="cso-hero-stat__label"><?php esc_html_e( "Punto d'imbarco", 'calypsosub' ); ?></span>
			<span class="cso-hero-stat__val"><?php echo esc_html( $luogo ?: $imbarco_mezzo ); ?></span>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ( ! $hero_bg && $img ) : ?>
	<div class="cso-hero__img-wrap">
		<img class="cso-hero__img" src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>">
		<div class="cso-hero__img-cap"><?php echo esc_html( get_the_title() . ( $luogo ? ' · ' . $luogo : '' ) ); ?></div>
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

	<?php if ( get_the_content() ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_descrizione_eyebrow', __( "L'uscita", 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_descrizione_heading', __( 'Descrizione.', 'calypsosub' ) ) ); ?></h2>
		<div class="cso-prose"><?php the_content(); ?></div>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $immersioni ) ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_immersioni_eyebrow', __( 'Le immersioni', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading">
			<?php printf(
				esc_html( _n( '%d tuffo, la stessa secca.', '%d tuffi, la stessa secca.', count( $immersioni ), 'calypsosub' ) ),
				count( $immersioni )
			); ?>
		</h2>
		<div class="cso-immersioni-grid">
			<?php foreach ( $immersioni as $n => $imm ) :
				$foto_id  = (int) ( $imm['foto_id'] ?? 0 );
				$foto_url = $foto_id ? wp_get_attachment_image_url( $foto_id, 'medium_large' ) : '';
				$corrente_labels = [ 'bassa' => __( 'Bassa', 'calypsosub' ), 'media' => __( 'Media', 'calypsosub' ), 'forte' => __( 'Forte', 'calypsosub' ) ];
			?>
			<div class="cso-immersione-card">
				<?php if ( $foto_url ) : ?>
					<img class="cso-immersione-card__img" src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $imm['nome'] ?? '' ); ?>">
				<?php else : ?>
					<div class="cso-immersione-card__img"></div>
				<?php endif; ?>
				<div class="cso-immersione-card__body">
					<span class="cso-immersione-card__eyebrow">
						<?php printf( esc_html__( '%1$d° immersione · ore %2$s', 'calypsosub' ), $n + 1, esc_html( $imm['ora'] ?? '' ) ); ?>
					</span>
					<p class="cso-immersione-card__title"><?php echo esc_html( $imm['nome'] ?? '' ); ?></p>
					<?php if ( ! empty( $imm['descrizione'] ) ) : ?>
						<p class="cso-immersione-card__desc"><?php echo esc_html( $imm['descrizione'] ); ?></p>
					<?php endif; ?>
					<div class="cso-immersione-card__meta">
						<?php if ( ! empty( $imm['profondita_max'] ) ) : ?>
						<div class="cso-immersione-card__meta-item">
							<span class="cso-immersione-card__meta-val"><?php echo (int) $imm['profondita_max']; ?> m</span>
							<span class="cso-immersione-card__meta-label"><?php esc_html_e( 'Prof. max', 'calypsosub' ); ?></span>
						</div>
						<?php endif; ?>
						<?php if ( ! empty( $imm['durata_fondo'] ) ) : ?>
						<div class="cso-immersione-card__meta-item">
							<span class="cso-immersione-card__meta-val"><?php echo (int) $imm['durata_fondo']; ?> min</span>
							<span class="cso-immersione-card__meta-label"><?php esc_html_e( 'Fondo', 'calypsosub' ); ?></span>
						</div>
						<?php endif; ?>
						<?php if ( ! empty( $imm['corrente'] ) && isset( $corrente_labels[ $imm['corrente'] ] ) ) : ?>
						<div class="cso-immersione-card__meta-item">
							<span class="cso-immersione-card__meta-val"><?php echo esc_html( $corrente_labels[ $imm['corrente'] ] ); ?></span>
							<span class="cso-immersione-card__meta-label"><?php esc_html_e( 'Corrente', 'calypsosub' ); ?></span>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $programma ) ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_programma_eyebrow', __( 'Il programma della giornata', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading">
			<?php echo $prossima_time
				? sprintf( esc_html__( 'Dalle %s al rientro.', 'calypsosub' ), esc_html( $prossima_time ) )
				: esc_html__( 'Dal ritrovo al rientro.', 'calypsosub' ); ?>
		</h2>
		<ol class="cso-programma">
			<?php foreach ( $programma as $tappa ) : ?>
			<li class="cso-tappa">
				<span class="cso-tappa__ora"><?php echo esc_html( $tappa['ora'] ?? '' ); ?></span>
				<div>
					<p class="cso-tappa__titolo"><?php echo esc_html( $tappa['titolo'] ?? '' ); ?></p>
					<?php if ( ! empty( $tappa['descrizione'] ) ) : ?>
						<p class="cso-tappa__desc"><?php echo esc_html( $tappa['descrizione'] ); ?></p>
					<?php endif; ?>
				</div>
			</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $fauna_tags ) ) : ?>
	<div class="cso-section">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_fauna_eyebrow', __( 'Cosa vedrai sotto', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_fauna_heading', __( 'La vita della secca.', 'calypsosub' ) ) ); ?></h2>
		<div class="cso-tags">
			<?php foreach ( $fauna_tags as $tag ) : ?>
				<span class="cso-tag"><?php echo esc_html( $tag ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $galleria_ids ) ) : ?>
	<div class="cso-section cso-usc-gallery">
		<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_galleria_eyebrow', __( 'Galleria', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-display-heading"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_galleria_heading', __( 'Dagli ultimi tuffi.', 'calypsosub' ) ) ); ?></h2>
		<div class="cso-usc-gallery__grid">
			<?php foreach ( $gallery_units as $unit ) :
				$cell = $unit['cell'];
			?>
			<div class="cso-usc-gallery-item">
				<img src="<?php echo esc_url( $cell['url'] ); ?>" alt="<?php echo esc_attr( $cell['alt'] ); ?>" loading="lazy">
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

</main>

<!-- SIDEBAR -->
<aside>
<div class="cso-sintesi">

	<div class="cso-sintesi__head">
		<span class="cso-sintesi__cert"><?php echo esc_html( calypsosub_opt( 'uscite', 'sidebar_eyebrow', __( 'Itinerario', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-sintesi__title"><?php echo esc_html( calypsosub_opt( 'uscite', 'sidebar_title', __( 'Informazioni pratiche', 'calypsosub' ) ) ); ?></h2>
	</div>

	<?php
	$stats = array_filter( [
		calypsosub_opt( 'uscite', 'stat_ritrovo',    __( 'Ritrovo', 'calypsosub' ) )          => $ritrovo,
		calypsosub_opt( 'uscite', 'stat_imbarco',    __( 'Imbarco', 'calypsosub' ) )          => $imbarco_mezzo,
		calypsosub_opt( 'uscite', 'stat_rientro',    __( 'Rientro previsto', 'calypsosub' ) ) => $rientro_previsto,
		calypsosub_opt( 'uscite', 'stat_immersioni', __( 'Immersioni', 'calypsosub' ) )       => $num_immersioni ? (string) $num_immersioni : '',
		calypsosub_opt( 'uscite', 'stat_difficolta', __( 'Difficoltà', 'calypsosub' ) )       => $livello,
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

	<?php if ( $incluso ) :
		$incluso_righe = array_values( array_filter( array_map( 'trim', explode( "\n", $incluso ) ) ) );
	?>
	<div class="cso-sintesi__checklist">
		<span class="cso-sintesi__checklist-label"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_incluso', __( "Inclusi nell'uscita", 'calypsosub' ) ) ); ?></span>
		<ul class="cso-checklist">
			<?php foreach ( $incluso_righe as $riga ) : ?>
			<li><span class="cso-checklist__check">✓</span><span><?php echo esc_html( $riga ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $occorrenze ) ) : ?>
	<div class="cso-sintesi__inizi">
		<span class="cso-inizi-label"><?php echo esc_html( calypsosub_opt( 'uscite', 'inizi_label', __( 'Prossime date', 'calypsosub' ) ) ); ?></span>
		<p class="cso-inizi-note"><?php echo esc_html( calypsosub_opt( 'uscite', 'inizi_note', __( 'Stesso itinerario, date diverse durante la stagione.', 'calypsosub' ) ) ); ?></p>
		<?php foreach ( $occorrenze as $occ ) :
			$occ_data     = (string) get_post_meta( $occ->ID, '_occorrenza_uscita_data', true );
			$remaining    = $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager
				? $calypsosub_booking_manager->get_remaining_spots( $occ->ID )
				: null;
			$lista_attesa = (int) get_post_meta( $occ->ID, '_occorrenza_uscita_lista_attesa', true );
			$max_posti    = get_post_meta( $occ->ID, '_occorrenza_uscita_posti', true );

			if ( $remaining === null ) {
				$spots_html = '';
			} elseif ( $remaining > 0 ) {
				$spots_html = '<span class="cso-inizio-row__spots cso-inizio-row__spots--ok">'
					. sprintf( esc_html__( '%d di %d posti', 'calypsosub' ), $remaining, (int) $max_posti ) . '</span>';
			} elseif ( $lista_attesa ) {
				$spots_html = '<span class="cso-inizio-row__spots cso-inizio-row__spots--warn">'
					. esc_html__( "Lista d'attesa", 'calypsosub' ) . '</span>';
			} else {
				$spots_html = '<span class="cso-inizio-row__spots cso-inizio-row__spots--full">'
					. esc_html__( 'Al completo', 'calypsosub' ) . '</span>';
			}
		?>
		<div class="cso-inizio-row">
			<span class="cso-inizio-row__date"><?php echo esc_html( $fmt( $occ_data ) ); ?><br><?php echo $spots_html; ?></span>
			<?php if ( $prenotazioni_page_ok && ( $remaining === null || $remaining > 0 || $lista_attesa ) ) : ?>
				<a class="cso-inizio-row__cta" href="<?php echo esc_url( $booking_link( $occ->ID ) ); ?>">
					<?php echo esc_html( calypsosub_opt( 'uscite', 'btn_prenota_ora', __( 'Prenota', 'calypsosub' ) ) ); ?>
				</a>
			<?php elseif ( $prenotazioni_page_ok ) : ?>
				<span class="cso-inizio-row__cta cso-inizio-row__cta--disabled"><?php esc_html_e( 'Al completo', 'calypsosub' ); ?></span>
			<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
				<span class="cso-inizio-row__cta cso-inizio-row__cta--disabled"><?php esc_html_e( 'Configura pagina prenotazioni', 'calypsosub' ); ?></span>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( $cosa_portare ) : ?>
	<div class="cso-sintesi__box">
		<span class="cso-sintesi__box-label"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_cosa_portare', __( 'Cosa portare', 'calypsosub' ) ) ); ?></span>
		<p class="cso-sintesi__box-text"><?php echo esc_html( trim( $cosa_portare ) ); ?></p>
	</div>
	<?php endif; ?>

	<?php if ( $note_cancellazione ) : ?>
	<div class="cso-sintesi__box">
		<span class="cso-sintesi__box-label"><?php echo esc_html( calypsosub_opt( 'uscite', 'sec_cancellazione', __( 'Cancellazione', 'calypsosub' ) ) ); ?></span>
		<p class="cso-sintesi__box-text"><?php echo esc_html( trim( $note_cancellazione ) ); ?></p>
	</div>
	<?php endif; ?>

</div>
</aside>

</div><!-- .cso-layout -->

<?php if ( ! empty( $related ) ) : ?>
<section class="cso-related">
<div class="cso-related__inner">
	<div class="cso-related__header">
		<div>
			<span class="cso-eyebrow"><?php echo esc_html( calypsosub_opt( 'uscite', 'related_eyebrow', __( 'Continua a scendere', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-display-heading" style="margin:0"><?php echo esc_html( calypsosub_opt( 'uscite', 'related_heading', __( 'Altre uscite in calendario.', 'calypsosub' ) ) ); ?></h2>
		</div>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_uscita' ) ); ?>"
		   class="cso-related__all"><?php echo esc_html( calypsosub_opt( 'uscite', 'related_link', __( 'Calendario completo →', 'calypsosub' ) ) ); ?></a>
	</div>
	<div class="cso-related__grid">
		<?php foreach ( $related as $r ) :
			$r_img     = get_the_post_thumbnail_url( $r->ID, 'medium_large' );
			$r_luogo   = (string) get_post_meta( $r->ID, '_uscita_luogo', true );
			$r_livs    = wp_get_post_terms( $r->ID, 'calypso_livello', [ 'fields' => 'names' ] );
			$r_livello = ( ! is_wp_error( $r_livs ) && ! empty( $r_livs ) ) ? $r_livs[0] : '';
			$r_occs    = calypso_get_occorrenze_by_uscita( $r->ID );
			$r_prossima = null;
			foreach ( $r_occs as $r_occ ) {
				$r_occ_data = (string) get_post_meta( $r_occ->ID, '_occorrenza_uscita_data', true );
				if ( $r_occ_data >= $oggi ) { $r_prossima = $r_occ_data; break; }
			}
		?>
		<a href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>" class="cso-thumb">
			<?php if ( $r_img ) : ?>
				<img src="<?php echo esc_url( $r_img ); ?>" alt="<?php echo esc_attr( $r->post_title ); ?>">
			<?php else : ?>
				<div class="cso-thumb__placeholder">🤿</div>
			<?php endif; ?>
			<div class="cso-thumb__body">
				<?php if ( $r_livello ) : ?>
					<span class="cso-thumb__level"><?php echo esc_html( $r_livello ); ?></span>
				<?php endif; ?>
				<p class="cso-thumb__title"><?php echo esc_html( $r->post_title ); ?></p>
				<?php if ( $r_luogo || $r_prossima ) : ?>
					<p class="cso-thumb__desc"><?php echo esc_html( trim( ( $r_prossima ? $fmt( $r_prossima ) : '' ) . ( $r_luogo ? ' · ' . $r_luogo : '' ), ' ·' ) ); ?></p>
				<?php endif; ?>
				<span class="cso-thumb__link"><?php echo esc_html( calypsosub_opt( 'uscite', 'related_card_link', __( 'Scopri l\'uscita →', 'calypsosub' ) ) ); ?></span>
			</div>
		</a>
		<?php endforeach; ?>
	</div>
</div>
</section>
<?php endif; ?>

</div><!-- .cso -->

<?php
if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-footer-wrap">';
	block_template_part( 'footer' );
	echo '</div>';
}
get_footer();
?>
