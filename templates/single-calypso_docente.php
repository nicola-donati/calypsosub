<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}

$id = get_the_ID();

$nome         = (string) get_post_meta( $id, '_docente_nome',             true );
$cognome      = (string) get_post_meta( $id, '_docente_cognome',          true );
$soprannome   = (string) get_post_meta( $id, '_docente_soprannome',       true );
$ruolo        = (string) get_post_meta( $id, '_docente_ruolo',            true );
$bio          = (string) get_post_meta( $id, '_docente_bio',              true );
$anni         = (int)    get_post_meta( $id, '_docente_anni_esperienza',  true );
$email        = (string) get_post_meta( $id, '_docente_email',            true );
$telefono     = (string) get_post_meta( $id, '_docente_telefono',         true );
$user_id      = (int)    get_post_meta( $id, '_docente_user_id',          true );
$social       = (array)  ( get_post_meta( $id, '_docente_social',         true ) ?: [] );
$galleria_ids = array_filter( array_map( 'absint', (array) ( get_post_meta( $id, '_docente_galleria', true ) ?: [] ) ) );
$galleria_ids = array_values( $galleria_ids );

// Specializzazioni: array nativo (con migrazione da vecchio CSV)
$specs_raw = get_post_meta( $id, '_docente_specializzazioni', true );
if ( is_array( $specs_raw ) ) {
	$specializzazioni = array_values( array_filter( $specs_raw ) );
} elseif ( is_string( $specs_raw ) && $specs_raw !== '' ) {
	$specializzazioni = array_values( array_filter( array_map( 'trim', explode( ',', $specs_raw ) ) ) );
} else {
	$specializzazioni = [];
}

$brevetti = get_the_terms( $id, 'calypso_brevetto' );
$brevetti = ( ! is_wp_error( $brevetti ) && $brevetti ) ? $brevetti : [];

$img_url      = get_the_post_thumbnail_url( $id, 'large' ) ?: '';
$hero_thumbs  = array_slice( $galleria_ids, 0, 3 );
$gallery_all  = $galleria_ids;

$wp_user_url  = $user_id ? get_author_posts_url( $user_id ) : '';
$wp_user_name = $user_id ? get_the_author_meta( 'display_name', $user_id ) : '';

$gallery_units = Calypsosub_Gallery_Helpers::build_units(
	Calypsosub_Gallery_Helpers::build_cells_from_attachments( $gallery_all )
);

/* Design settings */
$d = [
	'hero_bg'          => calypsosub_opt( 'docenti', 'design_hero_bg',           '#1B77A7' ),
	'hero_badge_bg'    => calypsosub_opt( 'docenti', 'design_hero_badge_bg',      '#ff6b4a' ),
	'hero_badge_color' => calypsosub_opt( 'docenti', 'design_hero_badge_color',   '#ffffff' ),
	'hero_sopr_bg'     => calypsosub_opt( 'docenti', 'design_hero_sopr_bg',       '#2a6fa8' ),
	'hero_sopr_color'  => calypsosub_opt( 'docenti', 'design_hero_sopr_color',    '#ffffff' ),
	'hero_name_size'   => max( 32, (int) calypsosub_opt( 'docenti', 'design_hero_name_size',  '96' ) ),
	'hero_name_color'  => calypsosub_opt( 'docenti', 'design_hero_name_color',    '#ffffff' ),
	'hero_sur_color'   => calypsosub_opt( 'docenti', 'design_hero_sur_color',     '#26CBFB' ),
	'hero_role_color'  => calypsosub_opt( 'docenti', 'design_hero_role_color',    '#ffffff' ),
	'hero_exp_color'   => calypsosub_opt( 'docenti', 'design_hero_exp_color',     '#26CBFB' ),
	'detail_bg'        => calypsosub_opt( 'docenti', 'design_detail_bg',          '#f6f1e6' ),
	'detail_eyebrow'   => calypsosub_opt( 'docenti', 'design_detail_eyebrow',     '#1B77A7' ),
	'detail_heading'   => calypsosub_opt( 'docenti', 'design_detail_heading',     '#1B77A7' ),
	'detail_prose'     => calypsosub_opt( 'docenti', 'design_detail_prose',       '#1a2f40' ),
	'spec_bg'          => calypsosub_opt( 'docenti', 'design_spec_bg',            '#ffffff' ),
	'spec_color'       => calypsosub_opt( 'docenti', 'design_spec_color',         '#1B77A7' ),
	'spec_dot'         => calypsosub_opt( 'docenti', 'design_spec_dot',           '#26CBFB' ),
	'brev_bg'          => calypsosub_opt( 'docenti', 'design_brev_bg',            '#1B77A7' ),
	'brev_color'       => calypsosub_opt( 'docenti', 'design_brev_color',         '#ffffff' ),
	'vcard_bg'         => calypsosub_opt( 'docenti', 'design_vcard_bg',           '#1B77A7' ),
	'vcard_name_size'  => max( 16, (int) calypsosub_opt( 'docenti', 'design_vcard_name_size', '28' ) ),
	'vcard_name_color' => calypsosub_opt( 'docenti', 'design_vcard_name_color',   '#ffffff' ),
	'vcard_role_color' => calypsosub_opt( 'docenti', 'design_vcard_role_color',   '#26CBFB' ),
	'gallery_bg'       => calypsosub_opt( 'docenti', 'design_gallery_bg',         '#cfe9ee' ),
	'gallery_eyebrow'  => calypsosub_opt( 'docenti', 'design_gallery_eyebrow',    '#1B77A7' ),
	'gallery_heading'  => calypsosub_opt( 'docenti', 'design_gallery_heading',    '#1B77A7' ),
];

/* mappa slug social → icona svg inline */
function cso_social_icon( string $nome ): string {
	$s = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">';
	$icons = [
		'instagram' => $s.'<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
		'facebook'  => $s.'<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
		'youtube'   => $s.'<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>',
		'linkedin'  => $s.'<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
		'twitter'   => $s.'<path d="M4 4l16 16M4 20L20 4"/></svg>',
		'tiktok'    => $s.'<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>',
		'whatsapp'  => $s.'<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
		'telegram'  => $s.'<path d="M22 2 11 13"/><path d="M22 2 15 22 11 13 2 9l20-7z"/></svg>',
		'pinterest' => $s.'<path d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.65 7.86 6.39 9.29-.09-.78-.17-1.98.03-2.83l1.32-5.57s-.33-.66-.33-1.63c0-1.53.89-2.67 1.99-2.67.94 0 1.4.7 1.4 1.55 0 .94-.6 2.36-.91 3.67-.26 1.1.54 1.99 1.62 1.99 1.94 0 3.24-2.48 3.24-5.41 0-2.23-1.51-3.89-4.24-3.89-3.1 0-5.03 2.32-5.03 4.91 0 .89.26 1.52.67 2.01.07.09.08.17.05.26l-.25.99c-.04.16-.13.2-.3.12C6.1 14.61 5.4 12.96 5.4 11.12c0-3.04 2.57-6.69 7.66-6.69 4.1 0 6.81 2.98 6.81 6.19 0 4.23-2.35 7.38-5.83 7.38-1.17 0-2.27-.63-2.65-1.34l-.77 2.99c-.27 1.01-.81 2.03-1.29 2.82C10.03 22.86 11 23 12 23c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>',
		'vimeo'     => $s.'<path d="M22.43 7.01c-.09 2.01-1.49 4.77-4.21 8.26C15.37 18.87 13.04 21 11.14 21c-1.19 0-2.19-1.1-3.02-3.3L6.54 13.28C5.95 11.08 5.32 10 4.64 10c-.15 0-.69.32-1.6.97L2.08 9.73C3.12 8.35 4.58 7 6.03 5.68c1.65-1.5 2.88-2.29 3.7-2.36 1.95-.19 3.14 1.14 3.6 3.98l1.01 5.67c.56 2.54 1.18 3.81 1.85 3.81.52 0 1.31-.83 2.36-2.49 1.05-1.66 1.6-2.92 1.68-3.8.15-1.43-.41-2.15-1.68-2.15-.6 0-1.22.14-1.85.41"/></svg>',
		'twitch'    => $s.'<path d="M21 2H3v16h5v4l4-4h5l4-4V2zM11 11V7m5 4V7"/></svg>',
		'github'    => $s.'<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>',
		'website'   => $s.'<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
	];
	$key = strtolower( trim( $nome ) );
	return $icons[ $key ] ?? $icons['website'];
}
?>
<style>
/* ── Token locali ── */
.cso-doc{
	color:var(--c-ink,#0b1a26);
}
.cso-doc h1,.cso-doc h2,.cso-doc h3,.cso-doc h4{color:var(--c-wave,#1B77A7);text-transform:uppercase}
.cso-doc a{color:inherit;text-decoration:none}
.cso-doc p,.cso-doc li,.cso-doc span,.cso-doc div{color:inherit}

/* ── Hero ── */
.cso-doc-hero{
	background:var(--c-deep,#1B77A7);
	color:#fff;
	padding:calc(var(--cso-header-h) + 32px) 48px 64px;
}
.cso-doc-hero h1,.cso-doc-hero h2,.cso-doc-hero h3{color:#fff}
.cso-doc-hero__inner{max-width:1320px;margin:0 auto}

.cso-doc .cso-doc-hero__crumbs{font-size:16px;color:rgba(255,255,255,.55)}
.cso-doc-hero__crumbs a{color:rgba(255,255,255,.55);text-decoration:none}
.cso-doc-hero__crumbs a:hover{color:#fff}
.cso-doc-hero__crumbs .current{color:#fff}

.cso-doc-hero__grid{display:grid;grid-template-columns:1.25fr .95fr;gap:64px;align-items:end}

.cso-doc-hero__badges{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:24px}
.cso-doc-hero__badge{
	display:inline-flex;align-items:center;gap:8px;
	padding:6px 14px;background:var(--c-coral-deep,#ff6b4a);
	border-radius:999px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;
}
.cso-doc .cso-doc-hero__badge{font-size:11px;color:#fff}
.cso-doc-hero__badge--sopr{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);font-style:italic}

.cso-doc-hero__name{margin:0;line-height:.94;letter-spacing:-.02em;overflow-wrap:break-word}
.cso-doc .cso-doc-hero__name{font-size:96px;color:#fff}
.cso-doc-hero__name .sur{display:block}
.cso-doc .cso-doc-hero__name .sur{color:var(--c-aqua,#26CBFB)}

.cso-doc-hero__role{display:flex;align-items:center;gap:10px;font-weight:600;margin-top:16px;opacity:.92}
.cso-doc .cso-doc-hero__role{font-size:24px;color:#fff}

.cso-doc-hero__exp{
	display:inline-flex;align-items:center;gap:12px;margin-top:32px;
	background:rgba(255,255,255,.07);
	border:1px solid rgba(255,255,255,.16);border-radius:14px;padding:16px 22px;
}
.cso-doc-hero__exp-val{font-weight:800;line-height:.9}
.cso-doc .cso-doc-hero__exp-val{font-size:52px;color:var(--c-aqua,#26CBFB)}
.cso-doc-hero__exp-label{letter-spacing:.1em;text-transform:uppercase}
.cso-doc .cso-doc-hero__exp-label{font-size:11px;color:rgba(255,255,255,.75)}

/* Shots (hero photo stack) */
.cso-doc-shots{display:flex;flex-direction:column;gap:12px}
.cso-doc-shots__main{
	height:380px;border-radius:14px;overflow:hidden;
	background:linear-gradient(180deg,var(--c-aqua,#26CBFB),var(--c-wave,#1B77A7) 60%,var(--c-deep,#1B77A7));
	position:relative;
}
.cso-doc-shots__main img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.cso-doc-shots__thumbs{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.cso-doc-shots__thumb{
	height:96px;border-radius:10px;overflow:hidden;
	background:linear-gradient(135deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7));
	position:relative;
}
.cso-doc-shots__thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}

/* ── Detail body (bone) ── */
.cso-doc-detail{background:var(--c-bone,#f6f1e6);padding:80px 48px 96px}
.cso-doc-detail__inner{max-width:1320px;margin:0 auto;display:grid;grid-template-columns:1fr 380px;gap:56px;align-items:start}

/* Content blocks */
.cso-doc-content{display:flex;flex-direction:column;gap:64px;min-width:0}
.cso-doc-block{}
.cso-doc-block__eyebrow{font-weight:600;letter-spacing:.16em;text-transform:uppercase;margin:0 0 14px;display:block}
.cso-doc .cso-doc-block__eyebrow{font-size:16px;color:var(--c-wave,#1B77A7)}
.cso-doc-block__heading{font-weight:800;text-transform:uppercase;letter-spacing:-.01em;line-height:.96;margin:0 0 24px}
.cso-doc .cso-doc-block__heading{font-size:48px;color:var(--c-deep,#1B77A7)}

/* Bio prose */
.cso-doc-prose p{line-height:1.8;margin:0 0 18px;max-width:660px}
.cso-doc-prose p:last-child{margin-bottom:0}
.cso-doc .cso-doc-prose p{font-size:16px;color:rgba(11,26,38,.8)}

/* Specializzazioni chips */
.cso-doc-specs{display:flex;flex-wrap:wrap;gap:10px}
.cso-doc-spec{
	display:inline-flex;align-items:center;gap:9px;
	background:#fff;border:1px solid rgba(11,26,38,.1);
	border-radius:999px;padding:11px 18px;font-weight:600;
}
.cso-doc .cso-doc-spec{font-size:14px;color:var(--c-deep,#1B77A7)}
.cso-doc-spec__dot{width:7px;height:7px;border-radius:50%;background:var(--c-aqua,#26CBFB);flex:0 0 auto}

/* Brevetti */
.cso-doc-brevetti{display:flex;flex-wrap:wrap;gap:8px}
.cso-doc-brevetto{
	display:inline-flex;align-items:center;
	background:var(--c-deep,#1B77A7);border-radius:999px;padding:6px 14px;font-weight:600;
}
.cso-doc .cso-doc-brevetto{font-size:13px;color:#fff}

/* ── VCard sidebar (navy) ── */
.cso-doc-aside{position:sticky;top:24px}
.cso-doc-vcard{
	background:var(--c-deep,#1B77A7);border-radius:18px;overflow:hidden;
	box-shadow:0 30px 80px -40px rgba(10,37,64,.6);
}
.cso-doc-vcard h2,.cso-doc-vcard h3,.cso-doc-vcard p,.cso-doc-vcard span,.cso-doc-vcard div{color:#fff}

.cso-doc-vcard__photo{
	height:260px;overflow:hidden;position:relative;
	background:linear-gradient(180deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7));
}
.cso-doc-vcard__photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}

.cso-doc-vcard__head{padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.1)}
.cso-doc-vcard__name{font-weight:700;text-transform:uppercase;line-height:1;margin:0}
.cso-doc .cso-doc-vcard__name{font-size:28px;color:#fff}
.cso-doc-vcard__soprannome{font-style:italic;margin:4px 0 0}
.cso-doc .cso-doc-vcard__soprannome{font-size:15px;color:rgba(255,255,255,.75)}
.cso-doc-vcard__role{font-weight:600;margin:6px 0 0}
.cso-doc .cso-doc-vcard__role{font-size:15px;color:var(--c-aqua,#26CBFB)}
.cso-doc-vcard__exp{margin:4px 0 0}
.cso-doc .cso-doc-vcard__exp{font-size:13px;color:rgba(255,255,255,.75)}

/* Contatti */
.cso-doc-contact{padding:8px 14px 14px}
.cso-doc-crow{
	display:flex;align-items:center;gap:14px;padding:13px 10px;
	border-radius:10px;text-decoration:none;min-height:52px;
	transition:background .15s;
}
.cso-doc-vcard .cso-doc-crow{color:#fff}
.cso-doc-crow:hover{background:rgba(255,255,255,.06)}
.cso-doc-crow__icon{
	width:36px;height:36px;border-radius:9px;flex:0 0 auto;
	background:rgba(38,203,251,.14);
	display:flex;align-items:center;justify-content:center;
}
.cso-doc .cso-doc-crow__icon{color:var(--c-aqua,#26CBFB)}
.cso-doc-crow__label{display:block;letter-spacing:.08em;text-transform:uppercase;line-height:1.3;margin-bottom:3px}
.cso-doc . cso-doc-crow__label{font-size:10px;color:rgba(255,255,255,.55)}
.cso-doc-crow__val{display:block;font-weight:600;line-height:1.35;overflow-wrap:anywhere}
.cso-doc . cso-doc-crow__val{font-size:14px;color:#fff}

/* Social icons nella vcard */
.cso-doc-vcard-socials{display:flex;gap:10px;padding:6px 24px 22px}
.cso-doc-vcard-socials a{
	width:40px;height:40px;border-radius:50%;
	border:1px solid rgba(255,255,255,.2);
	display:flex;align-items:center;justify-content:center;
	transition:.15s;text-decoration:none;
}
.cso-doc-vcard-socials a{color:rgba(255,255,255,.8)}
.cso-doc-vcard-socials a:hover{border-color:var(--c-aqua,#26CBFB);color:var(--c-aqua,#26CBFB)}

/* ── Gallery (foam bg) ── */
.cso-doc-gallery{background:var(--c-foam,#cfe9ee);padding:80px 48px 96px}
.cso-doc-gallery__inner{max-width:1320px;margin:0 auto}
.cso-doc-gallery__header{margin-bottom:40px}
.cso-doc-gallery__eyebrow{font-weight:600;letter-spacing:.16em;text-transform:uppercase;margin:0 0 12px;display:block}
.cso-doc .cso-doc-gallery__eyebrow{font-size:16px;color:var(--c-wave,#1B77A7)}
.cso-doc-gallery__heading{font-weight:800;text-transform:uppercase;line-height:.96;margin:0}
.cso-doc .cso-doc-gallery__heading{font-size:48px;color:var(--c-deep,#1B77A7)}

.cso-doc-gallery__grid{
	display:grid;
	grid-template-columns:repeat(4,1fr);
	grid-auto-rows:200px;
	gap:12px;
}
.cso-doc-gallery-item{border-radius:12px;overflow:hidden;position:relative;background:linear-gradient(135deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7))}
.cso-doc-gallery-item img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.cso-doc-gallery-item__cap{
	position:absolute;bottom:0;left:0;
	letter-spacing:.1em;text-transform:uppercase;
	padding:8px 12px;background:rgba(0,0,0,.35);backdrop-filter:blur(4px);
	border-top-right-radius:4px;
}
.cso-doc . cso-doc-gallery-item__cap{font-size:10px;color:rgba(255,255,255,.85)}

/* ══════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════ */
@media(max-width:1040px){
	.cso-doc-hero{padding:calc(var(--cso-header-h) + 24px) 28px 56px}
	.cso-doc .cso-doc-hero__name{font-size:78px}
	.cso-doc-hero__grid{grid-template-columns:1fr;gap:36px}
	.cso-doc-shots{order:-1}
	.cso-doc-shots__main{height:340px}
	.cso-doc-detail{padding:48px 28px 72px}
	.cso-doc-detail__inner{grid-template-columns:1fr;gap:40px}
	.cso-doc-content{gap:48px}
	.cso-doc-aside{position:static;max-width:480px}
	.cso-doc-gallery{padding:56px 28px 72px}
	.cso-doc . cso-doc-block__heading{font-size:40px}
	.cso-doc . cso-doc-gallery__heading{font-size:40px}
}
@media(max-width:760px){
	.cso-doc-hero{padding:calc(var(--cso-header-h) + 20px) 20px 48px}
	.cso-doc .cso-doc-hero__name{font-size:54px}
	.cso-doc .cso-doc-hero__role{font-size:20px}
	.cso-doc-hero__crumbs{margin-bottom:32px}
	.cso-doc-shots__main{height:300px}
	.cso-doc-detail{padding:40px 20px 64px}
	.cso-doc-aside{max-width:none}
	.cso-doc . cso-doc-block__heading{font-size:36px}
	.cso-doc-gallery{padding:44px 20px 64px}
	.cso-doc . cso-doc-gallery__heading{font-size:36px}
	.cso-doc-gallery__grid{grid-auto-rows:150px}
}
@media(max-width:420px){
	.cso-doc .cso-doc-hero__name{font-size:44px}
	.cso-doc .cso-doc-hero__exp-val{font-size:42px}
	.cso-doc . cso-doc-block__heading{font-size:30px}
	.cso-doc-gallery__grid{grid-auto-rows:110px}
}
</style>
<style>
.cso-doc-hero{background:<?php echo esc_attr($d['hero_bg']); ?>}
.cso-doc-hero__badge{background:<?php echo esc_attr($d['hero_badge_bg']); ?>}
.cso-doc .cso-doc-hero__badge{color:<?php echo esc_attr($d['hero_badge_color']); ?>}
.cso-doc-hero__badge--sopr{background:<?php echo esc_attr($d['hero_sopr_bg']); ?>}
.cso-doc .cso-doc-hero__badge--sopr{color:<?php echo esc_attr($d['hero_sopr_color']); ?>}
.cso-doc .cso-doc-hero__name{font-size:<?php echo $d['hero_name_size']; ?>px;color:<?php echo esc_attr($d['hero_name_color']); ?>}
.cso-doc .cso-doc-hero__name .sur{color:<?php echo esc_attr($d['hero_sur_color']); ?>}
.cso-doc .cso-doc-hero__role{color:<?php echo esc_attr($d['hero_role_color']); ?>}
.cso-doc .cso-doc-hero__exp-val{color:<?php echo esc_attr($d['hero_exp_color']); ?>}
.cso-doc-detail{background:<?php echo esc_attr($d['detail_bg']); ?>}
.cso-doc .cso-doc-block__eyebrow{color:<?php echo esc_attr($d['detail_eyebrow']); ?>}
.cso-doc .cso-doc-block__heading{color:<?php echo esc_attr($d['detail_heading']); ?>}
.cso-doc-prose p{color:<?php echo esc_attr($d['detail_prose']); ?>}
.cso-doc-spec{background:<?php echo esc_attr($d['spec_bg']); ?>}
.cso-doc .cso-doc-spec{color:<?php echo esc_attr($d['spec_color']); ?>}
.cso-doc-spec__dot{background:<?php echo esc_attr($d['spec_dot']); ?>}
.cso-doc-brevetto{background:<?php echo esc_attr($d['brev_bg']); ?>}
.cso-doc .cso-doc-brevetto{color:<?php echo esc_attr($d['brev_color']); ?>}
.cso-doc-vcard{background:<?php echo esc_attr($d['vcard_bg']); ?>}
.cso-doc .cso-doc-vcard__name{font-size:<?php echo $d['vcard_name_size']; ?>px;color:<?php echo esc_attr($d['vcard_name_color']); ?>}
.cso-doc .cso-doc-vcard__role{color:<?php echo esc_attr($d['vcard_role_color']); ?>}
.cso-doc-gallery{background:<?php echo esc_attr($d['gallery_bg']); ?>}
.cso-doc .cso-doc-gallery__eyebrow{color:<?php echo esc_attr($d['gallery_eyebrow']); ?>}
.cso-doc .cso-doc-gallery__heading{color:<?php echo esc_attr($d['gallery_heading']); ?>}
</style>

<div class="cso-doc">

<!-- ── HERO ── -->
<header class="cso-doc-hero">
<div class="cso-doc-hero__inner">

	<nav class="cso-doc-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'calypsosub' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'calypsosub' ); ?></a>
		<span>/</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'calypso_docente' ) ); ?>"><?php _e( 'Istruttori', 'calypsosub' ); ?></a>
		<span>/</span>
		<span class="current"><?php the_title(); ?></span>
	</nav>

	<div class="cso-doc-hero__grid">

		<!-- Sinistra: dati -->
		<div>
			<div class="cso-doc-hero__badges">
				<div class="cso-doc-hero__badge">● <?php echo esc_html( $ruolo ?: calypsosub_opt( 'docenti', 'hero_badge', __( 'Istruttore', 'calypsosub' ) ) ); ?></div>
				<?php if ( $soprannome ) : ?>
				<div class="cso-doc-hero__badge cso-doc-hero__badge--sopr">detto &ldquo;<?php echo esc_html( $soprannome ); ?>&rdquo;</div>
				<?php endif; ?>
			</div>

			<h1 class="cso-doc-hero__name display">
				<?php echo esc_html( $nome ?: get_the_title() ); ?>
				<?php if ( $cognome ) : ?>
				<span class="sur"><?php echo esc_html( $cognome ); ?></span>
				<?php endif; ?>
			</h1>

			<?php if ( $ruolo ) : ?>
			<div class="cso-doc-hero__role"><?php echo esc_html( $ruolo ); ?></div>
			<?php endif; ?>

			<?php if ( $anni ) : ?>
			<div class="cso-doc-hero__exp">
				<span class="cso-doc-hero__exp-val display"><?php echo esc_html( $anni ); ?></span>
				<span class="cso-doc-hero__exp-label"><?php _e( 'anni di<br>esperienza', 'calypsosub' ); ?></span>
			</div>
			<?php endif; ?>
		</div>

		<!-- Destra: foto -->
		<div class="cso-doc-shots">
			<div class="cso-doc-shots__main">
				<?php if ( $img_url ) : ?>
				<img src="<?php echo esc_url( $img_url ); ?>"
				     alt="<?php echo esc_attr( get_the_title() ); ?>" loading="eager">
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $hero_thumbs ) ) : ?>
			<div class="cso-doc-shots__thumbs">
				<?php foreach ( $hero_thumbs as $att_id ) :
					$t_url = wp_get_attachment_image_url( $att_id, 'medium' );
					if ( ! $t_url ) continue;
				?>
				<div class="cso-doc-shots__thumb">
					<img src="<?php echo esc_url( $t_url ); ?>"
					     alt="<?php echo esc_attr( get_post_field( 'post_title', $att_id ) ); ?>" loading="lazy">
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

	</div>
</div>
</header>

<!-- ── DETAIL ── -->
<section class="cso-doc-detail">
<div class="cso-doc-detail__inner">

	<!-- Contenuto principale -->
	<div class="cso-doc-content">

		<?php if ( $bio ) : ?>
		<div class="cso-doc-block">
			<span class="cso-doc-block__eyebrow"><?php echo esc_html( calypsosub_opt( 'docenti', 'bio_eyebrow', __( 'Bio', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-doc-block__heading display">
				<?php echo esc_html( str_replace( '{nome}', $nome,
					calypsosub_opt( 'docenti', 'bio_heading', $nome ? "Chi è $nome." : __( 'La storia.', 'calypsosub' ) )
				) ); ?>
			</h2>
			<div class="cso-doc-prose">
				<?php echo wpautop( wp_kses_post( $bio ) ); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $specializzazioni ) ) : ?>
		<div class="cso-doc-block">
			<span class="cso-doc-block__eyebrow"><?php echo esc_html( calypsosub_opt( 'docenti', 'specs_eyebrow', __( 'Specializzazioni', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-doc-block__heading display"><?php echo esc_html( calypsosub_opt( 'docenti', 'specs_heading', __( 'Cosa porta sotto.', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-doc-specs">
				<?php foreach ( $specializzazioni as $spec ) : ?>
				<span class="cso-doc-spec">
					<span class="cso-doc-spec__dot" aria-hidden="true"></span>
					<?php echo esc_html( $spec ); ?>
				</span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $brevetti ) ) : ?>
		<div class="cso-doc-block">
			<span class="cso-doc-block__eyebrow"><?php echo esc_html( calypsosub_opt( 'docenti', 'certs_eyebrow', __( 'Certificazioni', 'calypsosub' ) ) ); ?></span>
			<h2 class="cso-doc-block__heading display"><?php echo esc_html( calypsosub_opt( 'docenti', 'certs_heading', __( 'Brevetti.', 'calypsosub' ) ) ); ?></h2>
			<div class="cso-doc-brevetti">
				<?php foreach ( $brevetti as $brev ) : ?>
				<span class="cso-doc-brevetto"><?php echo esc_html( $brev->name ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div>

	<!-- Sidebar vcard -->
	<aside class="cso-doc-aside">
	<div class="cso-doc-vcard">

		<div class="cso-doc-vcard__photo">
			<?php if ( $img_url ) : ?>
			<img src="<?php echo esc_url( get_the_post_thumbnail_url( $id, 'medium_large' ) ?: $img_url ); ?>"
			     alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
			<?php endif; ?>
		</div>

		<div class="cso-doc-vcard__head">
			<div class="cso-doc-vcard__name">
				<?php echo esc_html( trim( "$nome $cognome" ) ?: get_the_title() ); ?>
			</div>
			<?php if ( $soprannome ) : ?>
			<div class="cso-doc-vcard__soprannome">detto &ldquo;<?php echo esc_html( $soprannome ); ?>&rdquo;</div>
			<?php endif; ?>
			<?php if ( $ruolo ) : ?>
			<div class="cso-doc-vcard__role"><?php echo esc_html( $ruolo ); ?></div>
			<?php endif; ?>
			<?php if ( $anni ) : ?>
			<div class="cso-doc-vcard__exp">
				<?php echo esc_html( sprintf(
					_n( '%d anno di esperienza', '%d anni di esperienza', $anni, 'calypsosub' ),
					$anni
				) ); ?>
			</div>
			<?php endif; ?>
		</div>

		<div class="cso-doc-contact">

			<?php if ( $email ) : ?>
			<a class="cso-doc-crow" href="mailto:<?php echo esc_attr( $email ); ?>">
				<span class="cso-doc-crow__icon">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
				</span>
				<span class="cso-doc-crow__tx">
					<span class="cso-doc-crow__label"><?php echo esc_html( calypsosub_opt( 'docenti', 'vcard_label_email', __( 'Email', 'calypsosub' ) ) ); ?></span>
					<span class="cso-doc-crow__val"><?php echo esc_html( $email ); ?></span>
				</span>
			</a>
			<?php endif; ?>

			<?php if ( $telefono ) : ?>
			<a class="cso-doc-crow" href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $telefono ) ); ?>">
				<span class="cso-doc-crow__icon">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				</span>
				<span class="cso-doc-crow__tx">
					<span class="cso-doc-crow__label"><?php echo esc_html( calypsosub_opt( 'docenti', 'vcard_label_phone', __( 'Telefono', 'calypsosub' ) ) ); ?></span>
					<span class="cso-doc-crow__val"><?php echo esc_html( $telefono ); ?></span>
				</span>
			</a>
			<?php endif; ?>


		</div>

		<?php if ( ! empty( $social ) ) : ?>
		<div class="cso-doc-vcard-socials">
			<?php foreach ( $social as $s ) :
				if ( empty( $s['url'] ) ) continue;
				$label = ! empty( $s['nome'] ) ? $s['nome'] : parse_url( $s['url'], PHP_URL_HOST );
			?>
			<a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener noreferrer"
			   aria-label="<?php echo esc_attr( $label ); ?>">
				<?php echo cso_social_icon( $s['nome'] ?? '' ); ?>
			</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div>
	</aside>

</div>
  <div class="cso-hero__scroll" aria-hidden="true">
    SCORRI
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
  </div>
</section>

<!-- ── GALLERIA ── -->
<?php if ( ! empty( $gallery_all ) ) : ?>
<section class="cso-doc-gallery">
<div class="cso-doc-gallery__inner">

	<div class="cso-doc-gallery__header">
		<span class="cso-doc-gallery__eyebrow"><?php echo esc_html( calypsosub_opt( 'docenti', 'gallery_eyebrow', __( 'Galleria foto', 'calypsosub' ) ) ); ?></span>
		<h2 class="cso-doc-gallery__heading display">
			<?php echo esc_html( str_replace( '{nome}', $nome,
				calypsosub_opt( 'docenti', 'gallery_heading', $nome ? "Dal logbook di $nome." : __( 'Dal logbook.', 'calypsosub' ) )
			) ); ?>
		</h2>
	</div>

	<div class="cso-doc-gallery__grid">
		<?php foreach ( $gallery_units as $unit ) :
			$cell = $unit['cell'];
		?>
		<div class="cso-doc-gallery-item" style="grid-column:span <?php echo (int) $unit['col']; ?>;grid-row:span <?php echo (int) $unit['row']; ?>;">
			<img src="<?php echo esc_url( $cell['url'] ); ?>"
			     alt="<?php echo esc_attr( $cell['alt'] ); ?>" loading="lazy">
			<?php if ( $cell['caption'] ) : ?>
			<div class="cso-doc-gallery-item__cap"><?php echo esc_html( $cell['caption'] ); ?></div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>

</div>
</section>
<?php endif; ?>

</div><!-- .cso-doc -->

<?php
if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-footer-wrap">';
	block_template_part( 'footer' );
	echo '</div>';
}
get_footer();
?>
