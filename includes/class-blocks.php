<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Blocks {

	private const BLOCKS = [
		'calypso/lista-uscite' => [
			'file'  => 'block-lista-uscite.php',
			'title' => 'Lista Uscite',
			'icon'  => 'calendar-alt',
			'attributes' => [
				/* ── Intestazione sezione ── */
				'eyebrow'          => [ 'type' => 'string',  'default' => 'Prossime uscite' ],
				'title'            => [ 'type' => 'string',  'default' => "Il mare\nci aspetta." ],
				'show_header_link' => [ 'type' => 'boolean', 'default' => true ],
				'header_link_text' => [ 'type' => 'string',  'default' => 'Calendario completo' ],
				/* ── Comportamento ── */
				'show_past'  => [ 'type' => 'boolean', 'default' => false ],
				'max_items'  => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Colonne visibili ── */
				'show_badge'   => [ 'type' => 'boolean', 'default' => true ],
				'show_ritrovo' => [ 'type' => 'boolean', 'default' => true ],
				'show_posti'   => [ 'type' => 'boolean', 'default' => true ],
				'show_cta'     => [ 'type' => 'boolean', 'default' => true ],
				/* ── Etichette CTA ── */
				'btn_prenota'   => [ 'type' => 'string', 'default' => 'Prenota' ],
				'btn_attesa'    => [ 'type' => 'string', 'default' => "Lista d'attesa" ],
				'btn_esaurito'  => [ 'type' => 'string', 'default' => 'Esaurito' ],
				'btn_terminata' => [ 'type' => 'string', 'default' => 'Terminata' ],
				'lbl_ritrovo'   => [ 'type' => 'string', 'default' => 'RITROVO' ],
				'lbl_liberi'    => [ 'type' => 'string', 'default' => 'Posti liberi' ],
				/* ── Stato vuoto ── */
				'empty_title' => [ 'type' => 'string', 'default' => 'Nessuna uscita in programma.' ],
				/* ── Layout sezione ── */
				'bg_color'  => [ 'type' => 'string',  'default' => '#dff4f8' ],
				'max_width' => [ 'type' => 'integer', 'default' => 1320 ],
				'padding_y' => [ 'type' => 'integer', 'default' => 80 ],
				'padding_x' => [ 'type' => 'integer', 'default' => 48 ],
				/* ── Colori ── */
				'color_accent' => [ 'type' => 'string', 'default' => '#1B77A7' ],
				'color_ink'    => [ 'type' => 'string', 'default' => '#0b1a26' ],
				/* ── Card ── */
				'card_bg'     => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_radius' => [ 'type' => 'integer', 'default' => 16 ],
				'card_shadow' => [ 'type' => 'string',  'default' => '0 4px 32px -8px rgba(10,37,64,.12)' ],
				/* ── Righe ── */
				'row_padding_y'    => [ 'type' => 'integer', 'default' => 28 ],
				'row_padding_x'    => [ 'type' => 'integer', 'default' => 32 ],
				'row_border_color' => [ 'type' => 'string',  'default' => 'rgba(11,26,38,.07)' ],
				/* ── Bottone ── */
				'btn_bg'    => [ 'type' => 'string', 'default' => '#061826' ],
				'btn_color' => [ 'type' => 'string', 'default' => '#ffffff' ],
				/* ── Testo intestazione ── */
				'eyebrow_size'      => [ 'type' => 'integer', 'default' => 13 ],
				'eyebrow_weight'    => [ 'type' => 'integer', 'default' => 600 ],
				'title_size'        => [ 'type' => 'integer', 'default' => 76 ],
				'title_weight'      => [ 'type' => 'integer', 'default' => 900 ],
				'title_line_height' => [ 'type' => 'string',  'default' => '.95' ],
				'head_link_size'    => [ 'type' => 'integer', 'default' => 14 ],
				'head_link_weight'  => [ 'type' => 'integer', 'default' => 600 ],
				/* ── Testo righe ── */
				'dayname_size'      => [ 'type' => 'integer', 'default' => 10 ],
				'daynum_size'       => [ 'type' => 'integer', 'default' => 58 ],
				'daynum_weight'     => [ 'type' => 'integer', 'default' => 900 ],
				'month_size'        => [ 'type' => 'integer', 'default' => 10 ],
				'name_size'         => [ 'type' => 'integer', 'default' => 20 ],
				'name_weight'       => [ 'type' => 'integer', 'default' => 700 ],
				'luogo_size'        => [ 'type' => 'integer', 'default' => 13 ],
				'ritrovo_label_size'=> [ 'type' => 'integer', 'default' => 9 ],
				'ritrovo_val_size'  => [ 'type' => 'integer', 'default' => 11 ],
				'posti_size'        => [ 'type' => 'integer', 'default' => 13 ],
				'badge_size'        => [ 'type' => 'integer', 'default' => 12 ],
				'badge_weight'      => [ 'type' => 'integer', 'default' => 600 ],
				/* ── Bottone/Vuoto ── */
				'btn_size'    => [ 'type' => 'integer', 'default' => 13 ],
				'btn_weight'  => [ 'type' => 'integer', 'default' => 700 ],
				'empty_size'  => [ 'type' => 'integer', 'default' => 16 ],
			],
		],
		'calypso/calendario' => [
			'file'  => 'block-calendario.php',
			'title' => 'Calendario',
			'icon'  => 'calendar',
			'attributes' => [
				'eyebrow'     => [ 'type' => 'string',  'default' => '03 — Prossime uscite' ],
				'heading'     => [ 'type' => 'string',  'default' => '' ],
				'link_text'   => [ 'type' => 'string',  'default' => 'Calendario completo' ],
				'link_url'    => [ 'type' => 'string',  'default' => '' ],
				'max_items'   => [ 'type' => 'integer', 'default' => 6 ],
				'show_uscite' => [ 'type' => 'boolean', 'default' => true ],
				'show_eventi' => [ 'type' => 'boolean', 'default' => true ],
				'show_corsi'  => [ 'type' => 'boolean', 'default' => true ],
			],
		],
		'calypso/lista-corsi'     => [ 'file' => 'block-corsi-lista.php',      'title' => 'Lista Corsi' ],
		'calypso/corsi-strip' => [
			'file'  => 'block-corsi-strip.php',
			'title' => 'Corsi Strip',
			'icon'  => 'excerpt-view',
			'attributes' => [
				/* ── Sorgente ── */
				'source_mode'   => [ 'type' => 'string',  'default' => 'all' ],
				'manual_ids'    => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'livello_slugs' => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'string' ] ],
				'max_items'     => [ 'type' => 'integer', 'default' => 0 ],
				'order'         => [ 'type' => 'string',  'default' => 'menu_order' ],
				/* ── Comportamento ── */
				'show_link'            => [ 'type' => 'boolean', 'default' => true ],
				'link_target'          => [ 'type' => 'string',  'default' => '_self' ],
				'show_badge'           => [ 'type' => 'boolean', 'default' => true ],
				'show_stats'           => [ 'type' => 'boolean', 'default' => true ],
				'show_stat_durata'     => [ 'type' => 'boolean', 'default' => true ],
				'show_stat_pratica'    => [ 'type' => 'boolean', 'default' => true ],
				'show_stat_profondita' => [ 'type' => 'boolean', 'default' => false ],
				'show_stat_badge'      => [ 'type' => 'boolean', 'default' => false ],
				'show_right'           => [ 'type' => 'boolean', 'default' => true ],
				'right_meta_key'       => [ 'type' => 'string',  'default' => '_corso_stat_durata' ],
				'right_label'          => [ 'type' => 'string',  'default' => 'DURATA' ],
				'right_unit'           => [ 'type' => 'string',  'default' => '' ],
				/* ── Featured ── */
				'featured_enabled' => [ 'type' => 'boolean', 'default' => true ],
				'featured_index'   => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Layout sezione ── */
				'bg_color'      => [ 'type' => 'string',  'default' => '#f6f1e6' ],
				'max_width'     => [ 'type' => 'integer', 'default' => 900 ],
				'padding_y'     => [ 'type' => 'integer', 'default' => 48 ],
				'padding_x'     => [ 'type' => 'integer', 'default' => 24 ],
				'gap'           => [ 'type' => 'integer', 'default' => 12 ],
				'margin_top'    => [ 'type' => 'integer', 'default' => 0 ],
				'margin_right'  => [ 'type' => 'integer', 'default' => 0 ],
				'margin_bottom' => [ 'type' => 'integer', 'default' => 0 ],
				'margin_left'   => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Righe normali ── */
				'row_bg'           => [ 'type' => 'string',  'default' => '#ffffff' ],
				'row_text_color'   => [ 'type' => 'string',  'default' => '#0a2540' ],
				'row_border_color' => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.08)' ],
				'row_radius'       => [ 'type' => 'integer', 'default' => 16 ],
				'row_padding_y'    => [ 'type' => 'integer', 'default' => 20 ],
				'row_padding_x'    => [ 'type' => 'integer', 'default' => 28 ],
				'row_shadow'       => [ 'type' => 'string',  'default' => '0 4px 16px -8px rgba(10,37,64,.15)' ],
				/* ── Riga featured ── */
				'feat_bg'                => [ 'type' => 'string',  'default' => '#0a2540' ],
				'feat_text_color'        => [ 'type' => 'string',  'default' => '#ffffff' ],
				'feat_badge_bg'          => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.15)' ],
				'feat_badge_color'       => [ 'type' => 'string',  'default' => '#ffffff' ],
				'feat_badge_border'      => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.3)' ],
				'feat_stats_color'       => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.7)' ],
				'feat_right_label_color' => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.55)' ],
				'feat_right_value_color' => [ 'type' => 'string',  'default' => '#ffffff' ],
				/* ── Badge livello ── */
				'badge_bg'        => [ 'type' => 'string',  'default' => 'transparent' ],
				'badge_color'     => [ 'type' => 'string',  'default' => '#0a2540' ],
				'badge_border'    => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.2)' ],
				'badge_size'      => [ 'type' => 'integer', 'default' => 12 ],
				'badge_weight'    => [ 'type' => 'integer', 'default' => 600 ],
				'badge_radius'    => [ 'type' => 'integer', 'default' => 999 ],
				'badge_min_width' => [ 'type' => 'integer', 'default' => 72 ],
				/* ── Titolo ── */
				'title_size'   => [ 'type' => 'integer', 'default' => 22 ],
				'title_weight' => [ 'type' => 'integer', 'default' => 800 ],
				'title_upper'  => [ 'type' => 'boolean', 'default' => true ],
				/* ── Stats ── */
				'stats_size'  => [ 'type' => 'integer', 'default' => 14 ],
				'stats_color' => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.6)' ],
				/* ── Colonna destra ── */
				'right_label_size'   => [ 'type' => 'integer', 'default' => 10 ],
				'right_label_color'  => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.5)' ],
				'right_value_size'   => [ 'type' => 'integer', 'default' => 28 ],
				'right_value_color'  => [ 'type' => 'string',  'default' => '#0a2540' ],
				'right_value_weight' => [ 'type' => 'integer', 'default' => 800 ],
			],
		],
		'calypso/lista-docenti'   => [ 'file' => 'block-docenti-lista.php',    'title' => 'Lista Docenti' ],
		'calypso/docenti-carousel' => [
			'file'  => 'block-docenti-carousel.php',
			'title' => 'Docenti Carosello',
			'icon'  => 'groups',
			'attributes' => [
				/* ── Sorgente ── */
				'source_mode'  => [ 'type' => 'string',  'default' => 'all' ],
				'manual_ids'   => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'priority_ids' => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'order'        => [ 'type' => 'string',  'default' => 'title' ],
				'max_items'    => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Comportamento ── */
				'show_link'          => [ 'type' => 'boolean', 'default' => true ],
				'link_target'        => [ 'type' => 'string',  'default' => '_self' ],
				'show_photo_label'   => [ 'type' => 'boolean', 'default' => true ],
				'photo_label_prefix' => [ 'type' => 'string',  'default' => 'RITRATTO' ],
				'show_ruolo'         => [ 'type' => 'boolean', 'default' => true ],
				'show_bio'           => [ 'type' => 'boolean', 'default' => false ],
				'bio_lines'          => [ 'type' => 'integer', 'default' => 2 ],
				'show_stats'         => [ 'type' => 'boolean', 'default' => true ],
				'show_stat_anno'     => [ 'type' => 'boolean', 'default' => true ],
				'show_stat_brevetti' => [ 'type' => 'boolean', 'default' => true ],
				'show_stat_spec'     => [ 'type' => 'boolean', 'default' => false ],
				'stat_anno_label'    => [ 'type' => 'string',  'default' => 'dal' ],
				/* ── Carosello ── */
				'desktop_cols' => [ 'type' => 'integer', 'default' => 3 ],
				'tablet_cols'  => [ 'type' => 'integer', 'default' => 2 ],
				'mobile_cols'  => [ 'type' => 'integer', 'default' => 1 ],
				'gap'          => [ 'type' => 'integer', 'default' => 24 ],
				'peek'         => [ 'type' => 'integer', 'default' => 40 ],
				/* ── Frecce ── */
				'arrows_show'    => [ 'type' => 'boolean', 'default' => true ],
				'arrows_position'=> [ 'type' => 'string',  'default' => 'sides' ],
				'arrow_bg'       => [ 'type' => 'string',  'default' => '#ffffff' ],
				'arrow_color'    => [ 'type' => 'string',  'default' => '#0a2540' ],
				'arrow_border'   => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.15)' ],
				'arrow_hover_bg' => [ 'type' => 'string',  'default' => '#0a2540' ],
				'arrow_hover_col'=> [ 'type' => 'string',  'default' => '#ffffff' ],
				'arrow_size'     => [ 'type' => 'integer', 'default' => 44 ],
				'arrow_radius'   => [ 'type' => 'integer', 'default' => 999 ],
				'arrow_shadow'   => [ 'type' => 'string',  'default' => '0 2px 12px -4px rgba(10,37,64,.25)' ],
				/* ── Sezione ── */
				'bg_color'      => [ 'type' => 'string',  'default' => '' ],
				'max_width'     => [ 'type' => 'integer', 'default' => 1320 ],
				'padding_y'     => [ 'type' => 'integer', 'default' => 48 ],
				'padding_x'     => [ 'type' => 'integer', 'default' => 24 ],
				'margin_top'    => [ 'type' => 'integer', 'default' => 0 ],
				'margin_right'  => [ 'type' => 'integer', 'default' => 0 ],
				'margin_bottom' => [ 'type' => 'integer', 'default' => 0 ],
				'margin_left'   => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Card ── */
				'card_bg'      => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_radius'  => [ 'type' => 'integer', 'default' => 16 ],
				'card_shadow'  => [ 'type' => 'string',  'default' => '0 8px 28px -10px rgba(10,37,64,.18)' ],
				'card_border'  => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.06)' ],
				'card_body_px' => [ 'type' => 'integer', 'default' => 20 ],
				'card_body_py' => [ 'type' => 'integer', 'default' => 18 ],
				/* ── Foto ── */
				'photo_ratio'       => [ 'type' => 'string',  'default' => '3/4' ],
				'photo_label_bg'    => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.72)' ],
				'photo_label_color' => [ 'type' => 'string',  'default' => '#ffffff' ],
				'photo_label_size'  => [ 'type' => 'integer', 'default' => 9 ],
				/* ── Nome ── */
				'name_color'  => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'name_size'   => [ 'type' => 'integer', 'default' => 22 ],
				'name_weight' => [ 'type' => 'integer', 'default' => 800 ],
				'name_upper'  => [ 'type' => 'boolean', 'default' => true ],
				/* ── Soprannome ── */
				'show_soprannome' => [ 'type' => 'boolean', 'default' => true ],
				'sopr_color'      => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.6)' ],
				'sopr_size'       => [ 'type' => 'integer', 'default' => 15 ],
				/* ── Ruolo ── */
				'ruolo_color'  => [ 'type' => 'string',  'default' => '#b9790a' ],
				'ruolo_size'   => [ 'type' => 'integer', 'default' => 15 ],
				'ruolo_weight' => [ 'type' => 'integer', 'default' => 600 ],
				/* ── Bio ── */
				'bio_color' => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.7)' ],
				'bio_size'  => [ 'type' => 'integer', 'default' => 14 ],
				/* ── Stats ── */
				'stats_color'     => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.55)' ],
				'stats_size'      => [ 'type' => 'integer', 'default' => 12 ],
				'stats_sep_color' => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.2)' ],
				/* ── Font testi ── */
				'name_font'       => [ 'type' => 'string',  'default' => '' ],
				'body_font'       => [ 'type' => 'string',  'default' => '' ],
				/* ── Stile testo nome ── */
				'name_italic'      => [ 'type' => 'boolean', 'default' => false ],
				'name_decoration'  => [ 'type' => 'string',  'default' => 'none' ],
				/* ── Stile testo ruolo ── */
				'ruolo_italic'     => [ 'type' => 'boolean', 'default' => false ],
				'ruolo_decoration' => [ 'type' => 'string',  'default' => 'none' ],
				/* ── Stile testo soprannome ── */
				'sopr_decoration'  => [ 'type' => 'string',  'default' => 'none' ],
				/* ── Stile testo bio ── */
				'bio_italic'       => [ 'type' => 'boolean', 'default' => false ],
				/* ── Pallini paginazione ── */
				'dots_show'         => [ 'type' => 'boolean', 'default' => true ],
				'dots_color'        => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.2)' ],
				'dots_active_color' => [ 'type' => 'string',  'default' => '#0a2540' ],
				'dots_size'         => [ 'type' => 'integer', 'default' => 7 ],
				/* ── Autoplay ── */
				'autoplay'       => [ 'type' => 'boolean', 'default' => false ],
				'autoplay_speed' => [ 'type' => 'integer', 'default' => 3000 ],
			],
		],
		'calypso/lista-eventi'    => [ 'file' => 'block-eventi-lista.php',     'title' => 'Lista Eventi' ],
		'calypso/area-personale'  => [ 'file' => 'block-area-personale.php',   'title' => 'Area Personale' ],
		'calypso/prossima-uscita' => [
			'file'       => 'block-prossima-uscita.php',
			'title'      => 'Prossima Uscita',
			'attributes' => [
				'inside_hero' => [ 'type' => 'boolean', 'default' => false ],
			],
		],
		'calypso/hero-home' => [
			'file'       => 'block-hero-home.php',
			'title'      => 'Hero Home',
			'attributes' => [
				'image_id'       => [ 'type' => 'integer', 'default' => 0 ],
				'eyebrow'        => [ 'type' => 'string',  'default' => 'La subacquea ad Arezzo dal 1978' ],
				'eyebrow_wave'   => [ 'type' => 'boolean', 'default' => true ],
				'title'          => [ 'type' => 'string',  'default' => "Sotto la superficie\nc'è un" ],
				'title_em'       => [ 'type' => 'string',  'default' => 'altro mondo.' ],
				'description'    => [ 'type' => 'string',  'default' => '' ],
				'btn1_text'      => [ 'type' => 'string',  'default' => 'Diventa socio' ],
				'btn1_url'       => [ 'type' => 'string',  'default' => '' ],
				'btn2_text'      => [ 'type' => 'string',  'default' => 'Guarda il video' ],
				'btn2_url'       => [ 'type' => 'string',  'default' => '' ],
				'show_uscita'    => [ 'type' => 'boolean', 'default' => true ],
				'marquee_on'     => [ 'type' => 'boolean', 'default' => true ],
				'marquee_items'  => [ 'type' => 'string',  'default' => 'Argentario,Elba,Giglio,Giannutri,Croazia,Egadi' ],
				'marquee_mobile' => [ 'type' => 'boolean', 'default' => false ],
				/* ── Colori sfondo ── */
				'text_color'      => [ 'type' => 'string',  'default' => '#ffffff' ],
				'overlay_color'   => [ 'type' => 'string',  'default' => '#061826' ],
				/* ── Tipografia eyebrow ── */
				'eyebrow_color'   => [ 'type' => 'string',  'default' => '#26CBFB' ],
				'eyebrow_size'    => [ 'type' => 'integer', 'default' => 14 ],
				'eyebrow_weight'  => [ 'type' => 'integer', 'default' => 600 ],
				/* ── Tipografia titolo ── */
				'title_color'     => [ 'type' => 'string',  'default' => '#ffffff' ],
				'title_em_color'  => [ 'type' => 'string',  'default' => '#26CBFB' ],
				'title_size'      => [ 'type' => 'integer', 'default' => 108 ],
				'title_weight'    => [ 'type' => 'integer', 'default' => 700 ],
				'title_font'      => [ 'type' => 'string',  'default' => '' ],
				/* ── Tipografia descrizione ── */
				'desc_color'      => [ 'type' => 'string',  'default' => '#ffffff' ],
				'desc_opacity'    => [ 'type' => 'integer', 'default' => 92 ],
				'desc_size'       => [ 'type' => 'integer', 'default' => 0 ],
				'desc_font'       => [ 'type' => 'string',  'default' => '' ],
				/* ── Bottoni ── */
				'btn1_bg'         => [ 'type' => 'string',  'default' => '#ff6b4a' ],
				'btn1_color'      => [ 'type' => 'string',  'default' => '#ffffff' ],
				'btn1_hover_bg'   => [ 'type' => 'string',  'default' => '#e04a2a' ],
				'btn1_size'       => [ 'type' => 'integer', 'default' => 15 ],
				'btn1_weight'     => [ 'type' => 'integer', 'default' => 700 ],
				'btn2_bg'         => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.1)' ],
				'btn2_hover_bg'   => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.18)' ],
				'btn2_border'     => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.25)' ],
				'btn2_color'      => [ 'type' => 'string',  'default' => '#ffffff' ],
				'btn2_size'       => [ 'type' => 'integer', 'default' => 15 ],
				'btn2_weight'     => [ 'type' => 'integer', 'default' => 600 ],
				/* ── Scroll indicator ── */
				'scroll_color'    => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.7)' ],
				/* ── Card prossima uscita ── */
				'pu_bg'           => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.08)' ],
				'pu_border'       => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.18)' ],
				'pu_dot_color'    => [ 'type' => 'string',  'default' => '#54e09a' ],
				'pu_text_color'   => [ 'type' => 'string',  'default' => '#ffffff' ],
				'pu_accent_color' => [ 'type' => 'string',  'default' => '#26CBFB' ],
				'pu_warn_color'   => [ 'type' => 'string',  'default' => '#ff6b4a' ],
				/* ── Ticker luoghi ── */
				'marquee_bg'        => [ 'type' => 'string',  'default' => '#0a2540' ],
				'marquee_color'     => [ 'type' => 'string',  'default' => '#ffffff' ],
				'marquee_size'      => [ 'type' => 'integer', 'default' => 28 ],
				'marquee_weight'    => [ 'type' => 'integer', 'default' => 700 ],
				'marquee_sep_color' => [ 'type' => 'string',  'default' => '#ff6b4a' ],
			],
		],
		'calypso/sezione' => [
			'file'  => 'block-sezione.php',
			'title' => 'Sezione',
			'icon'  => 'layout',
			'attributes' => [
				'eyebrow'          => [ 'type' => 'string',  'default' => '' ],
				'title'            => [ 'type' => 'string',  'default' => '' ],
				'header_link_text' => [ 'type' => 'string',  'default' => '' ],
				'header_link_url'  => [ 'type' => 'string',  'default' => '' ],
				'bg_color'         => [ 'type' => 'string',  'default' => '#dff4f8' ],
				'bg_image_id'      => [ 'type' => 'integer', 'default' => 0 ],
				'padding_y'        => [ 'type' => 'integer', 'default' => 80 ],
				'max_width'        => [ 'type' => 'integer', 'default' => 1320 ],
				'eyebrow_color'          => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'title_color'            => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'title_size'             => [ 'type' => 'integer', 'default' => 76 ],
				'eyebrow_size'           => [ 'type' => 'integer', 'default' => 13 ],
				'eyebrow_letter_spacing' => [ 'type' => 'integer', 'default' => 16 ],
				'eyebrow_font_weight'    => [ 'type' => 'integer', 'default' => 600 ],
				'eyebrow_margin_bottom'  => [ 'type' => 'integer', 'default' => 16 ],
				'title_line_height'      => [ 'type' => 'integer', 'default' => 95 ],
				'title_font_weight'      => [ 'type' => 'integer', 'default' => 900 ],
				'link_color'             => [ 'type' => 'string',  'default' => '' ],
				'link_size'              => [ 'type' => 'integer', 'default' => 14 ],
				'link_font_weight'       => [ 'type' => 'integer', 'default' => 600 ],
				'padding_x'              => [ 'type' => 'integer', 'default' => 48 ],
				'head_margin_bottom'     => [ 'type' => 'integer', 'default' => 48 ],
			],
		],
		'calypso/promo-card' => [
			'file'  => 'block-promo-card.php',
			'title' => 'Promo Card',
			'icon'  => 'format-image',
			'attributes' => [
				/* ── Immagine ── */
				'image_id'         => [ 'type' => 'integer', 'default' => 0 ],
				'image_alt'        => [ 'type' => 'string',  'default' => '' ],
				'image_height'     => [ 'type' => 'integer', 'default' => 200 ],
				'image_object_fit' => [ 'type' => 'string',  'default' => 'cover' ],
				'image_object_pos' => [ 'type' => 'string',  'default' => 'center center' ],
				/* ── Overlay immagine ── */
				'overlay_text'     => [ 'type' => 'string',  'default' => '' ],
				'overlay_bg'       => [ 'type' => 'string',  'default' => 'rgba(6,24,38,0.45)' ],
				'overlay_color'    => [ 'type' => 'string',  'default' => '#ffffff' ],
				'overlay_size'     => [ 'type' => 'integer', 'default' => 10 ],
				/* ── Contenuto ── */
				'eyebrow'          => [ 'type' => 'string',  'default' => '01' ],
				'title'            => [ 'type' => 'string',  'default' => 'Titolo' ],
				'description'      => [ 'type' => 'string',  'default' => '' ],
				'link_text'        => [ 'type' => 'string',  'default' => 'Scopri' ],
				'link_url'         => [ 'type' => 'string',  'default' => '' ],
				'link_new_tab'     => [ 'type' => 'boolean', 'default' => false ],
				/* ── Stile card ── */
				'card_bg'          => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_radius'      => [ 'type' => 'integer', 'default' => 16 ],
				'card_padding'     => [ 'type' => 'integer', 'default' => 24 ],
				'card_shadow'      => [ 'type' => 'boolean', 'default' => true ],
				/* ── Tipografia eyebrow ── */
				'eyebrow_color'    => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'eyebrow_size'     => [ 'type' => 'integer', 'default' => 13 ],
				/* ── Tipografia titolo ── */
				'title_color'      => [ 'type' => 'string',  'default' => '#061826' ],
				'title_size'       => [ 'type' => 'integer', 'default' => 42 ],
				'title_weight'     => [ 'type' => 'string',  'default' => '900' ],
				'title_transform'  => [ 'type' => 'string',  'default' => 'uppercase' ],
				'title_font'       => [ 'type' => 'string',  'default' => '' ],
				/* ── Tipografia descrizione ── */
				'desc_color'       => [ 'type' => 'string',  'default' => '#3d5a6c' ],
				'desc_size'        => [ 'type' => 'integer', 'default' => 14 ],
				/* ── Link ── */
				'link_color'       => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'link_size'        => [ 'type' => 'integer', 'default' => 13 ],
			],
		],
		'calypso/galleria' => [
			'file'  => 'block-galleria.php',
			'title' => 'Galleria',
			'icon'  => 'format-gallery',
			'attributes' => [
				'source_mode'            => [ 'type' => 'string',  'default' => 'all' ],
				'tag_ids'                => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'manual_ids'             => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'max_items'              => [ 'type' => 'integer', 'default' => 12 ],
				'lightbox'               => [ 'type' => 'boolean', 'default' => false ],
				'gap'                    => [ 'type' => 'integer', 'default' => 0 ],
				'row_height'             => [ 'type' => 'integer', 'default' => 200 ],
				'max_width'              => [ 'type' => 'integer', 'default' => 1320 ],
				'bg_color'               => [ 'type' => 'string',  'default' => '' ],
				'overlay_bg'             => [ 'type' => 'string',  'default' => 'rgba(0,0,0,.35)' ],
				'overlay_color'          => [ 'type' => 'string',  'default' => '#ffffff' ],
				'overlay_size'           => [ 'type' => 'integer', 'default' => 10 ],
				'overlay_font_weight'    => [ 'type' => 'integer', 'default' => 400 ],
				'overlay_letter_spacing' => [ 'type' => 'integer', 'default' => 12 ],
				'margin_top'             => [ 'type' => 'integer', 'default' => 0 ],
				'margin_right'           => [ 'type' => 'integer', 'default' => 0 ],
				'margin_bottom'          => [ 'type' => 'integer', 'default' => 0 ],
				'margin_left'            => [ 'type' => 'integer', 'default' => 0 ],
			],
		],
		'calypso/lista-articoli' => [
			'file'  => 'block-lista-articoli.php',
			'title' => 'Lista Articoli',
			'icon'  => 'list-view',
			'attributes' => [
				/* ── Sorgente ── */
				'source_mode'        => [ 'type' => 'string',  'default' => 'all' ],
				'category_ids'       => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'tag_ids'            => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'manual_ids'         => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'order_by'           => [ 'type' => 'string',  'default' => 'date' ],
				'order'              => [ 'type' => 'string',  'default' => 'DESC' ],
				'max_items'          => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Comportamento ── */
				'show_link'          => [ 'type' => 'boolean', 'default' => true ],
				'link_target'        => [ 'type' => 'string',  'default' => '_self' ],
				'show_image'         => [ 'type' => 'boolean', 'default' => true ],
				'image_size'         => [ 'type' => 'string',  'default' => 'medium_large' ],
				'image_label_prefix' => [ 'type' => 'string',  'default' => 'ARCHIVIO' ],
				'show_excerpt'       => [ 'type' => 'boolean', 'default' => true ],
				'excerpt_length'     => [ 'type' => 'integer', 'default' => 30 ],
				/* ── Colonna sinistra ── */
				'left_field'        => [ 'type' => 'string',  'default' => 'post_date' ],
				'left_format'       => [ 'type' => 'string',  'default' => 'Y' ],
				'left_custom_field' => [ 'type' => 'string',  'default' => '' ],
				/* ── Layout sezione ── */
				'bg_color'      => [ 'type' => 'string',  'default' => '#f6f1e6' ],
				'max_width'     => [ 'type' => 'integer', 'default' => 1100 ],
				'padding_y'     => [ 'type' => 'integer', 'default' => 60 ],
				'padding_x'     => [ 'type' => 'integer', 'default' => 24 ],
				'row_gap_y'     => [ 'type' => 'integer', 'default' => 40 ],
				'margin_top'    => [ 'type' => 'integer', 'default' => 0 ],
				'margin_right'  => [ 'type' => 'integer', 'default' => 0 ],
				'margin_bottom' => [ 'type' => 'integer', 'default' => 0 ],
				'margin_left'   => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Separatore ── */
				'separator_color' => [ 'type' => 'string', 'default' => 'rgba(10,37,64,.12)' ],
				/* ── Colonna sinistra stile ── */
				'left_color'     => [ 'type' => 'string',  'default' => '#c8a84b' ],
				'left_size'      => [ 'type' => 'integer', 'default' => 80 ],
				'left_weight'    => [ 'type' => 'integer', 'default' => 900 ],
				'left_col_width' => [ 'type' => 'integer', 'default' => 140 ],
				/* ── Titolo ── */
				'title_color'  => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'title_size'   => [ 'type' => 'integer', 'default' => 22 ],
				'title_weight' => [ 'type' => 'integer', 'default' => 800 ],
				'title_upper'  => [ 'type' => 'boolean', 'default' => true ],
				/* ── Testo corpo ── */
				'text_color' => [ 'type' => 'string',  'default' => '#3d5265' ],
				'text_size'  => [ 'type' => 'integer', 'default' => 15 ],
				/* ── Immagine ── */
				'image_col_width'   => [ 'type' => 'integer', 'default' => 360 ],
				'image_ratio'       => [ 'type' => 'string',  'default' => '16/9' ],
				'image_radius'      => [ 'type' => 'integer', 'default' => 8 ],
				'image_label_bg'    => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.6)' ],
				'image_label_color' => [ 'type' => 'string',  'default' => '#ffffff' ],
				'image_label_size'  => [ 'type' => 'integer', 'default' => 10 ],
			],
		],
		'calypso/storia-club' => [
			'file'  => 'block-storia-club.php',
			'title' => 'Storia Club',
			'icon'  => 'backup',
			'attributes' => [
				'source_mode'    => [ 'type' => 'string',  'default' => 'query' ],
				'category_ids'   => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'tag_ids'        => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'manual_ids'     => [ 'type' => 'array',   'default' => [], 'items' => [ 'type' => 'integer' ] ],
				'date_from'      => [ 'type' => 'string',  'default' => '' ],
				'date_to'        => [ 'type' => 'string',  'default' => '' ],
				'year_from'      => [ 'type' => 'integer', 'default' => 0 ],
				'year_to'        => [ 'type' => 'integer', 'default' => 0 ],
				'max_items'      => [ 'type' => 'integer', 'default' => 0 ],
				'order_by'       => [ 'type' => 'string',  'default' => 'event_year' ],
				'order'          => [ 'type' => 'string',  'default' => 'asc' ],
				'title_source'   => [ 'type' => 'string',  'default' => 'post_title' ],
				'text_source'    => [ 'type' => 'string',  'default' => 'excerpt' ],
				'clickable'      => [ 'type' => 'boolean', 'default' => true ],
				'link_new_tab'   => [ 'type' => 'boolean', 'default' => false ],
				'columns'        => [ 'type' => 'integer', 'default' => 5 ],
				'desktop_overflow' => [ 'type' => 'string', 'default' => 'hide' ],
				'gap'            => [ 'type' => 'integer', 'default' => 32 ],
				'max_width'      => [ 'type' => 'integer', 'default' => 1320 ],
				'padding_y'      => [ 'type' => 'integer', 'default' => 0 ],
				'padding_x'      => [ 'type' => 'integer', 'default' => 0 ],
				'bg_color'       => [ 'type' => 'string',  'default' => '' ],
				'line_color'     => [ 'type' => 'string',  'default' => 'rgba(95,184,200,.25)' ],
				'line_thickness' => [ 'type' => 'integer', 'default' => 1 ],
				'dot_color'      => [ 'type' => 'string',  'default' => '#5FB8C8' ],
				'dot_color_last' => [ 'type' => 'string',  'default' => '#FF6B4A' ],
				'dot_size'       => [ 'type' => 'integer', 'default' => 12 ],
				'gap_dot_year'   => [ 'type' => 'integer', 'default' => 18 ],
				'gap_year_title' => [ 'type' => 'integer', 'default' => 8 ],
				'gap_title_text' => [ 'type' => 'integer', 'default' => 6 ],
				'year_color'           => [ 'type' => 'string',  'default' => '#5FB8C8' ],
				'year_size'            => [ 'type' => 'integer', 'default' => 36 ],
				'year_font_weight'     => [ 'type' => 'integer', 'default' => 800 ],
				'year_letter_spacing'  => [ 'type' => 'integer', 'default' => 0 ],
				'year_font'            => [ 'type' => 'string',  'default' => '' ],
				'title_color'          => [ 'type' => 'string',  'default' => '#ffffff' ],
				'title_size'           => [ 'type' => 'integer', 'default' => 18 ],
				'title_font_weight'    => [ 'type' => 'integer', 'default' => 800 ],
				'title_letter_spacing' => [ 'type' => 'integer', 'default' => 0 ],
				'title_transform'      => [ 'type' => 'string',  'default' => 'uppercase' ],
				'title_font'           => [ 'type' => 'string',  'default' => '' ],
				'text_color'           => [ 'type' => 'string',  'default' => '#ffffff' ],
				'text_size'            => [ 'type' => 'integer', 'default' => 13 ],
				'text_font_weight'     => [ 'type' => 'integer', 'default' => 400 ],
				'text_line_height'     => [ 'type' => 'integer', 'default' => 150 ],
			],
		],
		'calypso/prenotazione' => [
			'file'  => 'block-prenotazione.php',
			'title' => 'Prenotazione',
			'icon'  => 'tickets-alt',
			'attributes' => [
				'enable_uscite'      => [ 'type' => 'boolean', 'default' => true ],
				'enable_eventi'      => [ 'type' => 'boolean', 'default' => true ],
				'enable_corsi'       => [ 'type' => 'boolean', 'default' => true ],
				'cf7_form_uscite'    => [ 'type' => 'integer', 'default' => 0 ],
				'cf7_form_eventi'    => [ 'type' => 'integer', 'default' => 0 ],
				'cf7_form_corsi'     => [ 'type' => 'integer', 'default' => 0 ],
				'max_items_per_tab'  => [ 'type' => 'integer', 'default' => 60 ],
				'cards_per_page'     => [ 'type' => 'integer', 'default' => 12 ],
				'card_columns'       => [ 'type' => 'integer', 'default' => 4 ],
				'max_width'          => [ 'type' => 'integer', 'default' => 1320 ],

				/* Accesso per tipo */
				'require_login_uscite' => [ 'type' => 'boolean', 'default' => true ],
				'require_login_eventi' => [ 'type' => 'boolean', 'default' => true ],
				'require_login_corsi'  => [ 'type' => 'boolean', 'default' => false ],
				'login_message'        => [ 'type' => 'string',  'default' => 'Per prenotarti devi aver effettuato il login.' ],

				/* Sezione selezione — sfondo & layout */
				'select_bg_color'        => [ 'type' => 'string',  'default' => '#f6f1e6' ],
				'select_padding_y'       => [ 'type' => 'integer', 'default' => 40 ],

				/* Selettore tab */
				'tabs_track_bg_color'          => [ 'type' => 'string',  'default' => '#ffffff' ],
				'tab_text_color'               => [ 'type' => 'string',  'default' => '#0a2540' ],
				'tab_active_bg_color'          => [ 'type' => 'string',  'default' => '#0a2540' ],
				'tab_active_text_color'        => [ 'type' => 'string',  'default' => '#ffffff' ],
				'tab_count_bg_color'           => [ 'type' => 'string',  'default' => '#eef1f4' ],
				'tab_count_text_color'         => [ 'type' => 'string',  'default' => '#0a2540' ],
				'tab_count_active_bg_color'    => [ 'type' => 'string',  'default' => '#2f87b3' ],
				'tab_count_active_text_color'  => [ 'type' => 'string',  'default' => '#ffffff' ],
				'tab_font_size'                => [ 'type' => 'integer', 'default' => 14 ],
				'tab_font_weight'              => [ 'type' => 'integer', 'default' => 600 ],

				/* Card evento */
				'card_bg_color'                  => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_img_bg_color'              => [ 'type' => 'string',  'default' => '#0a2540' ],
				'card_media_height'              => [ 'type' => 'integer', 'default' => 220 ],
				'card_radius'                     => [ 'type' => 'integer', 'default' => 16 ],
				'card_selected_border_color'      => [ 'type' => 'string',  'default' => '#ff6b4a' ],
				'card_selected_badge_bg_color'    => [ 'type' => 'string',  'default' => '#f5a623' ],
				'card_selected_badge_text_color'  => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_date_bg_color'              => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_date_num_color'             => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'card_date_label_color'           => [ 'type' => 'string',  'default' => '#5c6b75' ],
				'card_media_title_bg_color'       => [ 'type' => 'string',  'default' => 'rgba(10,37,64,.6)' ],
				'card_media_title_color'          => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_type_badge_bg_color'        => [ 'type' => 'string',  'default' => '#e6f1fa' ],
				'card_type_badge_text_color'      => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'card_title_color'                => [ 'type' => 'string',  'default' => '#0a2540' ],
				'card_title_size'                 => [ 'type' => 'integer', 'default' => 18 ],
				'card_title_font_weight'          => [ 'type' => 'integer', 'default' => 700 ],
				'card_meta_text_color'            => [ 'type' => 'string',  'default' => '#5c6b75' ],
				'card_divider_color'              => [ 'type' => 'string',  'default' => '#e9edf0' ],
				'card_level_text_color'           => [ 'type' => 'string',  'default' => '#5c6b75' ],
				'card_spots_text_color'           => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'card_spots_warn_color'           => [ 'type' => 'string',  'default' => '#ff6b4a' ],

				/* Filtri (button-group stile radio) */
				'filter_active_border_color' => [ 'type' => 'string', 'default' => '#f5a623' ],
				'filter_active_bg_color'     => [ 'type' => 'string', 'default' => '#fff7e8' ],
				'filter_active_text_color'   => [ 'type' => 'string', 'default' => '#b9790a' ],

				/* Sezione dati — sfondo & layout */
				'data_bg_color'          => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'data_padding_y'         => [ 'type' => 'integer', 'default' => 48 ],

				/* Form prenotazione */
				'form_bg_color'             => [ 'type' => 'string',  'default' => '#ffffff' ],
				'form_radius'               => [ 'type' => 'integer', 'default' => 18 ],
				'form_title_color'          => [ 'type' => 'string',  'default' => '#0a2540' ],
				'form_title_size'           => [ 'type' => 'integer', 'default' => 20 ],
				'form_title_font_weight'    => [ 'type' => 'integer', 'default' => 800 ],

				/* Sidebar riepilogo */
				'sidebar_bg_color'    => [ 'type' => 'string',  'default' => '#0a2540' ],
				'sidebar_text_color'  => [ 'type' => 'string',  'default' => '#ffffff' ],
				'sidebar_radius'      => [ 'type' => 'integer', 'default' => 18 ],
				'side_badge_bg_color' => [ 'type' => 'string',  'default' => '#ff6b4a' ],
				'side_title_color'        => [ 'type' => 'string',  'default' => '#ffffff' ],
				'side_title_size'         => [ 'type' => 'integer', 'default' => 18 ],
				'side_title_font_weight'  => [ 'type' => 'integer', 'default' => 800 ],
				'side_luogo_color'        => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.75)' ],
				'side_label_color'        => [ 'type' => 'string',  'default' => 'rgba(255,255,255,.55)' ],
				'side_value_color'        => [ 'type' => 'string',  'default' => '#ffffff' ],
			],
		],
	];

	public function init(): void {
		add_filter( 'block_categories_all',       [ $this, 'register_category' ] );
		add_action( 'init',                        [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_js' ] );
	}

	public function register_category( array $categories ): array {
		return array_merge( $categories, [ [
			'slug'  => 'calypso',
			'title' => __( 'Calypso Sub', 'calypsosub' ),
			'icon'  => 'waves',
		] ] );
	}

	public function register_blocks(): void {
		foreach ( self::BLOCKS as $name => $cfg ) {
			$path  = CALYPSOSUB_PATH . 'block-templates/' . $cfg['file'];
			$attrs = $cfg['attributes'] ?? [];
			register_block_type( $name, [
				'render_callback' => static function ( array $attributes, string $content = '' ) use ( $path ): string {
					ob_start();
					include $path;
					return (string) ob_get_clean();
				},
				'category'   => 'calypso',
				'attributes' => $attrs,
			] );
		}
	}

	public function enqueue_editor_js(): void {
		wp_register_script(
			'calypso-blocks-editor',
			false,
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ],
			null,
			true
		);
		wp_enqueue_script( 'calypso-blocks-editor' );

		$blocks_json = wp_json_encode(
			array_map(
				static fn( string $name, array $cfg ) => [
					'name'       => $name,
					'title'      => $cfg['title'],
					'icon'       => $cfg['icon'] ?? 'grid-view',
					'attributes' => $cfg['attributes'] ?? [],
				],
				array_keys( self::BLOCKS ),
				array_values( self::BLOCKS )
			)
		);

		$js = <<<JS
(function (blocks, element, components, blockEditor) {
	var el = element.createElement;
	var Fragment          = element.Fragment;
	var InspectorControls = blockEditor ? blockEditor.InspectorControls : null;
	var MediaUploadCheck  = blockEditor ? blockEditor.MediaUploadCheck  : null;
	var MediaUpload       = blockEditor ? blockEditor.MediaUpload       : null;
	var PanelBody       = components.PanelBody;
	var PanelRow        = components.PanelRow;
	var TextControl     = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl   = components.ToggleControl;
	var RangeControl    = components.RangeControl;
	var Button          = components.Button;
	var Divider         = components.Divider || function() { return el('hr', { style: { margin: '12px 0', border: 'none', borderTop: '1px solid #e0e0e0' } }); };
	var SelectControl   = components.SelectControl;
	var ColorPalette    = components.ColorPalette;

	function getThemeColors() {
		try {
			return window.wp.data.select('core/block-editor').getSettings().colors || [];
		} catch(e) { return []; }
	}

	var calypsoBlocks = {$blocks_json};

	calypsoBlocks.forEach(function (info) {
		if (blocks.getBlockType(info.name)) return;

		/* ════════════════════════════════════════════
		   calypso/lista-uscite — controlli completi
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/lista-uscite') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'calendar-alt',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}
					function rangeRow(label, key, def, min, max, step) {
						return el(RangeControl, {
							label: label,
							value: a[key] !== undefined ? a[key] : def,
							min: min, max: max, step: step || 1,
							onChange: function (v) { var u = {}; u[key] = v === undefined ? def : v; set(u); }
						});
					}

					var controls = InspectorControls ? el(InspectorControls, {},

						/* ── Intestazione sezione ── */
						el(PanelBody, { title: 'Intestazione', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextareaControl, {
								label: 'Titolo (\\n per a capo)',
								help: 'Supporta \\n per andare a capo.',
								value: a.title || '',
								rows: 3,
								onChange: function (v) { set({ title: v }); }
							}),
							el(ToggleControl, {
								label: 'Mostra link "Calendario completo"',
								checked: !!a.show_header_link,
								onChange: function (v) { set({ show_header_link: v }); }
							}),
							a.show_header_link ? el(TextControl, {
								label: 'Testo del link',
								value: a.header_link_text || '',
								onChange: function (v) { set({ header_link_text: v }); }
							}) : null
						),

						/* ── Comportamento ── */
						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							el(ToggleControl, {
								label: 'Mostra uscite passate',
								help: a.show_past ? 'Visualizza anche le uscite già concluse.' : 'Solo uscite future.',
								checked: !!a.show_past,
								onChange: function (v) { set({ show_past: v }); }
							}),
							el(RangeControl, {
								label: 'Numero massimo di uscite (0 = tutte)',
								help: '0 = mostra tutte. Utile per limitare la sezione in homepage.',
								value: a.max_items || 0,
								min: 0,
								max: 50,
								step: 1,
								onChange: function (v) { set({ max_items: v || 0 }); }
							})
						),

						/* ── Colonne ── */
						el(PanelBody, { title: 'Colonne visibili', initialOpen: false },
							el(ToggleControl, {
								label: 'Badge livello',
								help: 'Etichetta del livello richiesto (es. OWD+).',
								checked: !!a.show_badge,
								onChange: function (v) { set({ show_badge: v }); }
							}),
							el(ToggleControl, {
								label: 'Punto di ritrovo',
								help: 'Orario e luogo di ritrovo.',
								checked: !!a.show_ritrovo,
								onChange: function (v) { set({ show_ritrovo: v }); }
							}),
							el(ToggleControl, {
								label: 'Posti disponibili',
								checked: !!a.show_posti,
								onChange: function (v) { set({ show_posti: v }); }
							}),
							el(ToggleControl, {
								label: 'Bottone prenotazione',
								checked: !!a.show_cta,
								onChange: function (v) { set({ show_cta: v }); }
							})
						),

						/* ── Etichette ── */
						el(PanelBody, { title: 'Etichette', initialOpen: false },
							el(TextControl, {
								label: 'Bottone prenota',
								value: a.btn_prenota || '',
								onChange: function (v) { set({ btn_prenota: v }); }
							}),
							el(TextControl, {
								label: 'Bottone lista d\'attesa',
								value: a.btn_attesa || '',
								onChange: function (v) { set({ btn_attesa: v }); }
							}),
							el(TextControl, {
								label: 'Bottone esaurito',
								value: a.btn_esaurito || '',
								onChange: function (v) { set({ btn_esaurito: v }); }
							}),
							el(TextControl, {
								label: 'Bottone terminata',
								value: a.btn_terminata || '',
								onChange: function (v) { set({ btn_terminata: v }); }
							}),
							el(TextControl, {
								label: 'Etichetta "RITROVO"',
								value: a.lbl_ritrovo || '',
								onChange: function (v) { set({ lbl_ritrovo: v }); }
							}),
							el(TextControl, {
								label: 'Etichetta "Posti liberi"',
								value: a.lbl_liberi || '',
								onChange: function (v) { set({ lbl_liberi: v }); }
							})
						),

						/* ── Stato vuoto ── */
						el(PanelBody, { title: 'Stato vuoto (nessuna uscita)', initialOpen: false },
							el(TextControl, {
								label: 'Testo quando non ci sono uscite',
								value: a.empty_title || '',
								onChange: function (v) { set({ empty_title: v }); }
							})
						),

						/* ── Layout sezione ── */
						el(PanelBody, { title: 'Layout sezione', initialOpen: false },
							colorRow('Sfondo sezione', 'bg_color'),
							rangeRow('Larghezza massima (px)', 'max_width', 1320, 400, 1920, 20),
							rangeRow('Padding verticale (px)', 'padding_y', 80, 0, 200, 4),
							rangeRow('Padding orizzontale (px)', 'padding_x', 48, 0, 120, 4)
						),

						/* ── Colori accento ── */
						el(PanelBody, { title: 'Colori accento', initialOpen: false },
							colorRow('Colore accento (titoli, date, badge)', 'color_accent'),
							colorRow('Colore testo principale', 'color_ink')
						),

						/* ── Stile card ── */
						el(PanelBody, { title: 'Stile card', initialOpen: false },
							colorRow('Sfondo card', 'card_bg'),
							rangeRow('Raggio bordi (px)', 'card_radius', 16, 0, 40, 2),
							el(TextControl, {
								label: 'Box-shadow card (CSS)',
								value: a.card_shadow !== undefined ? a.card_shadow : '0 4px 32px -8px rgba(10,37,64,.12)',
								onChange: function (v) { set({ card_shadow: v }); }
							})
						),

						/* ── Stile righe ── */
						el(PanelBody, { title: 'Stile righe', initialOpen: false },
							rangeRow('Padding verticale riga (px)', 'row_padding_y', 28, 8, 60, 2),
							rangeRow('Padding orizzontale riga (px)', 'row_padding_x', 32, 8, 80, 4),
							colorRow('Colore bordo separatore', 'row_border_color')
						),

						/* ── Stile bottone ── */
						el(PanelBody, { title: 'Stile bottone', initialOpen: false },
							colorRow('Sfondo bottone', 'btn_bg'),
							colorRow('Testo bottone', 'btn_color')
						),

						/* ── Testo intestazione ── */
						el(PanelBody, { title: 'Testo intestazione', initialOpen: false },
							rangeRow('Font size eyebrow (px)', 'eyebrow_size', 13, 8, 24, 1),
							rangeRow('Font weight eyebrow', 'eyebrow_weight', 600, 300, 900, 100),
							rangeRow('Font size titolo (px)', 'title_size', 76, 20, 120, 2),
							rangeRow('Font weight titolo', 'title_weight', 900, 300, 900, 100),
							el(TextControl, {
								label: 'Line-height titolo',
								value: a.title_line_height !== undefined ? a.title_line_height : '.95',
								onChange: function (v) { set({ title_line_height: v }); }
							}),
							rangeRow('Font size link header (px)', 'head_link_size', 14, 10, 24, 1),
							rangeRow('Font weight link header', 'head_link_weight', 600, 300, 900, 100)
						),

						/* ── Testo righe ── */
						el(PanelBody, { title: 'Testo righe', initialOpen: false },
							rangeRow('Font size giorno abbreviato (px)', 'dayname_size', 10, 7, 16, 1),
							rangeRow('Font size numero data (px)', 'daynum_size', 58, 24, 100, 2),
							rangeRow('Font weight numero data', 'daynum_weight', 900, 300, 900, 100),
							rangeRow('Font size mese abbreviato (px)', 'month_size', 10, 7, 16, 1),
							rangeRow('Font size nome uscita (px)', 'name_size', 20, 12, 40, 1),
							rangeRow('Font weight nome uscita', 'name_weight', 700, 300, 900, 100),
							rangeRow('Font size luogo (px)', 'luogo_size', 13, 9, 20, 1),
							rangeRow('Font size etichetta ritrovo (px)', 'ritrovo_label_size', 9, 7, 16, 1),
							rangeRow('Font size valore ritrovo (px)', 'ritrovo_val_size', 11, 8, 18, 1),
							rangeRow('Font size posti (px)', 'posti_size', 13, 9, 20, 1),
							rangeRow('Font size badge livello (px)', 'badge_size', 12, 8, 20, 1),
							rangeRow('Font weight badge livello', 'badge_weight', 600, 300, 900, 100)
						),

						/* ── Testo bottone e vuoto ── */
						el(PanelBody, { title: 'Testo bottone / vuoto', initialOpen: false },
							rangeRow('Font size bottone (px)', 'btn_size', 13, 9, 20, 1),
							rangeRow('Font weight bottone', 'btn_weight', 700, 300, 900, 100),
							rangeRow('Font size testo stato vuoto (px)', 'empty_size', 16, 10, 28, 1)
						)

					) : null;

					/* Preview nel canvas */
					var titleLines = (a.title || 'Il mare\\nci aspetta.').split('\\n');
					var preview = el('div', {
						style: { background: '#dff4f8', borderRadius: '8px', padding: '28px 24px', fontFamily: 'system-ui,sans-serif' }
					},
						el('div', { style: { fontSize: '10px', fontWeight: 700, letterSpacing: '.18em', textTransform: 'uppercase', color: '#26CBFB', marginBottom: '10px' } },
							a.eyebrow || 'Prossime uscite'
						),
						el('div', { style: { fontSize: '28px', fontWeight: 900, color: '#1B77A7', lineHeight: 1, marginBottom: '20px' } },
							titleLines.join(' · ')
						),
						/* Fake rows */
						el('div', { style: { background: '#fff', borderRadius: '12px', overflow: 'hidden', boxShadow: '0 8px 24px -10px rgba(10,37,64,.2)' } },
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '16px', padding: '14px 16px', fontSize: '12px', color: '#0b1a26' } },
								el('div', { style: { fontWeight: 900, fontSize: '24px', color: '#1B77A7', minWidth: '32px' } }, '14'),
								el('div', { style: { flex: 1 } },
									el('div', { style: { fontWeight: 700, color: '#1B77A7' } }, 'Nome uscita di esempio'),
									el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)' } }, '📍 Argentario · 2 immersioni')
								),
								a.show_badge ? el('div', { style: { background: 'rgba(27,119,167,.1)', color: '#1B77A7', padding: '3px 8px', borderRadius: '999px', fontSize: '10px', fontWeight: 700 } }, 'OWD+') : null,
								a.show_posti ? el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)', fontFamily: 'monospace' } }, '● 4 posti') : null,
								a.show_cta ? el('div', { style: { background: '#061826', color: '#fff', padding: '6px 12px', borderRadius: '999px', fontSize: '11px', fontWeight: 700 } }, a.btn_prenota || 'Prenota') : null
							),
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '16px', padding: '14px 16px', fontSize: '12px', color: '#0b1a26', borderTop: '1px solid rgba(11,26,38,.06)' } },
								el('div', { style: { fontWeight: 900, fontSize: '24px', color: '#1B77A7', minWidth: '32px' } }, '21'),
								el('div', { style: { flex: 1 } },
									el('div', { style: { fontWeight: 700, color: '#1B77A7' } }, 'Seconda uscita di esempio'),
									el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)' } }, '📍 Isola d\'Elba · weekend')
								),
								a.show_badge ? el('div', { style: { background: 'rgba(27,119,167,.1)', color: '#1B77A7', padding: '3px 8px', borderRadius: '999px', fontSize: '10px', fontWeight: 700 } }, 'AOWD+') : null,
								a.show_posti ? el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)', fontFamily: 'monospace' } }, '10 posti') : null,
								a.show_cta ? el('div', { style: { background: '#061826', color: '#fff', padding: '6px 12px', borderRadius: '999px', fontSize: '11px', fontWeight: 700 } }, a.btn_prenota || 'Prenota') : null
							)
						),
						el('div', { style: { fontSize: '10px', opacity: .55, marginTop: '10px', fontFamily: 'monospace' } },
							'calypso/lista-uscite' +
							(a.show_past ? ' · passate ON' : '') +
							(a.max_items > 0 ? ' · max ' + a.max_items : ' · tutte')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/calendario — controlli
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/calendario') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: 'calendar',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					var controls = InspectorControls ? el(InspectorControls, {},
						el(PanelBody, { title: 'Intestazione', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextareaControl, {
								label: 'Titolo (lascia vuoto = auto da mesi)',
								help: 'Se vuoto, il titolo viene generato automaticamente dai mesi degli eventi.',
								value: a.heading || '',
								rows: 2,
								onChange: function (v) { set({ heading: v }); }
							}),
							el(TextControl, {
								label: 'Testo link →',
								value: a.link_text || '',
								onChange: function (v) { set({ link_text: v }); }
							}),
							el(TextControl, {
								label: 'URL link (vuoto = archivio uscite)',
								value: a.link_url || '',
								type: 'url',
								onChange: function (v) { set({ link_url: v }); }
							})
						),
						el(PanelBody, { title: 'Contenuto', initialOpen: false },
							el(RangeControl, {
								label: 'Numero massimo di voci',
								value: a.max_items || 6,
								min: 1,
								max: 20,
								step: 1,
								onChange: function (v) { set({ max_items: v || 6 }); }
							}),
							el(ToggleControl, {
								label: 'Mostra uscite',
								checked: !!a.show_uscite,
								onChange: function (v) { set({ show_uscite: v }); }
							}),
							el(ToggleControl, {
								label: 'Mostra eventi',
								checked: !!a.show_eventi,
								onChange: function (v) { set({ show_eventi: v }); }
							}),
							el(ToggleControl, {
								label: 'Mostra corsi',
								checked: !!a.show_corsi,
								onChange: function (v) { set({ show_corsi: v }); }
							})
						)
					) : null;

					var preview = el('div', {
						style: { background: '#cfe9ee', borderRadius: '8px', padding: '24px', fontFamily: 'system-ui,sans-serif' }
					},
						el('div', { style: { fontSize: '10px', fontWeight: 700, letterSpacing: '.18em', textTransform: 'uppercase', color: '#1B77A7', marginBottom: '8px' } },
							a.eyebrow || '03 — Prossime uscite'
						),
						el('div', { style: { fontSize: '24px', fontWeight: 900, color: '#1B77A7', marginBottom: '16px', lineHeight: 1 } },
							a.heading || 'Mese e mese, il mare ci aspetta.'
						),
						el('div', { style: { background: '#fff', borderRadius: '10px', overflow: 'hidden', boxShadow: '0 4px 16px -4px rgba(10,37,64,.15)' } },
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '12px', padding: '12px 16px', fontSize: '12px', borderBottom: '1px solid rgba(11,26,38,.06)' } },
								el('div', { style: { fontWeight: 900, fontSize: '22px', color: '#1B77A7', minWidth: '28px' } }, '14'),
								el('div', { style: { flex: 1, fontWeight: 700, color: '#1B77A7', fontSize: '13px' } }, 'Secche di Tor di Cala'),
								el('div', { style: { background: 'rgba(27,119,167,.1)', color: '#1B77A7', padding: '2px 8px', borderRadius: '999px', fontSize: '10px' } }, 'OWD+'),
								el('div', { style: { background: '#1B77A7', color: '#fff', padding: '5px 10px', borderRadius: '999px', fontSize: '11px', fontWeight: 600 } }, 'Prenota')
							),
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '12px', padding: '12px 16px', fontSize: '12px' } },
								el('div', { style: { fontWeight: 900, fontSize: '22px', color: '#1B77A7', minWidth: '28px' } }, '21'),
								el('div', { style: { flex: 1, fontWeight: 700, color: '#1B77A7', fontSize: '13px' } }, 'Corso OWD — Sessione 3'),
								el('div', { style: { background: 'rgba(38,203,251,.12)', color: '#006f8a', padding: '2px 8px', borderRadius: '999px', fontSize: '10px' } }, 'Corso'),
								el('div', { style: { background: '#1B77A7', color: '#fff', padding: '5px 10px', borderRadius: '999px', fontSize: '11px', fontWeight: 600 } }, 'Scopri')
							)
						),
						el('div', { style: { fontSize: '10px', opacity: .55, marginTop: '10px', fontFamily: 'monospace' } },
							'calypso/calendario · max ' + (a.max_items || 6) +
							(a.show_uscite ? ' · uscite' : '') +
							(a.show_eventi ? ' · eventi' : '') +
							(a.show_corsi  ? ' · corsi'  : '')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/hero-home — controlli completi
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/hero-home') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: 'cover-image',
				attributes: info.attributes || {},
				edit: function (props) {
					var a = props.attributes;
					var set = props.setAttributes;

					var imgLabel = a.image_id
						? 'Immagine: ID ' + a.image_id
						: 'Nessuna immagine';

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

					var mediaBtn = (MediaUploadCheck && MediaUpload)
						? el(MediaUploadCheck, {},
							el(MediaUpload, {
								onSelect: function (media) { set({ image_id: media.id }); },
								allowedTypes: ['image'],
								value: a.image_id,
								render: function (ref) {
									return el(Button, {
										onClick: ref.open,
										variant: a.image_id ? 'secondary' : 'primary',
										style: { marginBottom: '8px' }
									}, a.image_id ? '⬡ Cambia immagine' : '⬡ Scegli immagine');
								}
							}))
						: null;

					var controls = InspectorControls
						? el(InspectorControls, {},
							el(PanelBody, { title: 'Immagine sfondo', initialOpen: true },
								mediaBtn,
								a.image_id
									? el(Button, {
										onClick: function () { set({ image_id: 0 }); },
										variant: 'link',
										isDestructive: true
									}, 'Rimuovi immagine')
									: null
							),
							el(PanelBody, { title: 'Testo', initialOpen: false },
								el(TextControl, {
									label: 'Eyebrow',
									value: a.eyebrow || '',
									onChange: function (v) { set({ eyebrow: v }); }
								}),
								el(ToggleControl, {
									label: 'Mostra icona onda',
									checked: !!a.eyebrow_wave,
									onChange: function (v) { set({ eyebrow_wave: v }); }
								}),
								el(TextareaControl, {
									label: 'Titolo (\\n per a capo)',
									value: a.title || '',
									onChange: function (v) { set({ title: v }); }
								}),
								el(TextControl, {
									label: 'Titolo — parte in evidenza (aqua)',
									value: a.title_em || '',
									onChange: function (v) { set({ title_em: v }); }
								}),
								el(TextareaControl, {
									label: 'Descrizione',
									value: a.description || '',
									onChange: function (v) { set({ description: v }); }
								})
							),
							el(PanelBody, { title: 'Bottoni', initialOpen: false },
								el(TextControl, {
									label: 'Bottone primario — testo',
									value: a.btn1_text || '',
									onChange: function (v) { set({ btn1_text: v }); }
								}),
								el(TextControl, {
									label: 'Bottone primario — URL',
									value: a.btn1_url || '',
									type: 'url',
									onChange: function (v) { set({ btn1_url: v }); }
								}),
								el(TextControl, {
									label: 'Bottone secondario — testo',
									value: a.btn2_text || '',
									onChange: function (v) { set({ btn2_text: v }); }
								}),
								el(TextControl, {
									label: 'Bottone secondario — URL',
									value: a.btn2_url || '',
									type: 'url',
									onChange: function (v) { set({ btn2_url: v }); }
								})
							),
							el(PanelBody, { title: 'Colori sfondo', initialOpen: false },
								colorRow('Colore testo hero', 'text_color'),
								colorRow('Colore overlay/sfondo', 'overlay_color')
							),
							el(PanelBody, { title: 'Tipografia — Eyebrow', initialOpen: false },
								colorRow('Colore', 'eyebrow_color'),
								el(RangeControl, {
									label: 'Font size (px)',
									value: a.eyebrow_size || 14,
									min: 8, max: 32, step: 1,
									onChange: function (v) { set({ eyebrow_size: v || 14 }); }
								}),
								el(RangeControl, {
									label: 'Font weight',
									value: a.eyebrow_weight || 600,
									min: 100, max: 900, step: 100,
									onChange: function (v) { set({ eyebrow_weight: v || 600 }); }
								})
							),
							el(PanelBody, { title: 'Tipografia — Titolo', initialOpen: false },
								colorRow('Colore', 'title_color'),
								colorRow('Colore parte in evidenza', 'title_em_color'),
								el(RangeControl, {
									label: 'Font size massimo (px)',
									value: a.title_size || 108,
									min: 32, max: 160, step: 2,
									onChange: function (v) { set({ title_size: v || 108 }); }
								}),
								el(RangeControl, {
									label: 'Font weight',
									value: a.title_weight || 700,
									min: 100, max: 900, step: 100,
									onChange: function (v) { set({ title_weight: v || 700 }); }
								}),
								el(TextControl, {
									label: 'Font family (vuoto = eredita tema)',
									help: 'Es: "Montserrat", sans-serif',
									value: a.title_font || '',
									onChange: function (v) { set({ title_font: v }); }
								})
							),
							el(PanelBody, { title: 'Tipografia — Descrizione', initialOpen: false },
								colorRow('Colore', 'desc_color'),
								el(RangeControl, {
									label: 'Opacità (%)',
									value: a.desc_opacity !== undefined ? a.desc_opacity : 92,
									min: 30, max: 100, step: 1,
									onChange: function (v) { set({ desc_opacity: v }); }
								}),
								el(RangeControl, {
									label: 'Font size (px, 0 = eredita tema)',
									value: a.desc_size || 0,
									min: 0, max: 32, step: 1,
									onChange: function (v) { set({ desc_size: v }); }
								}),
								el(TextControl, {
									label: 'Font family (vuoto = eredita tema)',
									value: a.desc_font || '',
									onChange: function (v) { set({ desc_font: v }); }
								})
							),
							el(PanelBody, { title: 'Bottoni — stile', initialOpen: false },
								colorRow('Bottone primario — sfondo', 'btn1_bg'),
								colorRow('Bottone primario — testo', 'btn1_color'),
								colorRow('Bottone primario — sfondo hover', 'btn1_hover_bg'),
								el(RangeControl, {
									label: 'Bottone primario — font size (px)',
									value: a.btn1_size || 15,
									min: 10, max: 24, step: 1,
									onChange: function (v) { set({ btn1_size: v || 15 }); }
								}),
								el(RangeControl, {
									label: 'Bottone primario — font weight',
									value: a.btn1_weight || 700,
									min: 100, max: 900, step: 100,
									onChange: function (v) { set({ btn1_weight: v || 700 }); }
								}),
								el(Divider, {}),
								el(TextControl, {
									label: 'Bottone secondario — sfondo (CSS, rgba OK)',
									value: a.btn2_bg || '',
									onChange: function (v) { set({ btn2_bg: v }); }
								}),
								el(TextControl, {
									label: 'Bottone secondario — sfondo hover (CSS, rgba OK)',
									value: a.btn2_hover_bg || '',
									onChange: function (v) { set({ btn2_hover_bg: v }); }
								}),
								el(TextControl, {
									label: 'Bottone secondario — bordo (CSS, rgba OK)',
									value: a.btn2_border || '',
									onChange: function (v) { set({ btn2_border: v }); }
								}),
								colorRow('Bottone secondario — testo', 'btn2_color'),
								el(RangeControl, {
									label: 'Bottone secondario — font size (px)',
									value: a.btn2_size || 15,
									min: 10, max: 24, step: 1,
									onChange: function (v) { set({ btn2_size: v || 15 }); }
								}),
								el(RangeControl, {
									label: 'Bottone secondario — font weight',
									value: a.btn2_weight || 600,
									min: 100, max: 900, step: 100,
									onChange: function (v) { set({ btn2_weight: v || 600 }); }
								})
							),
							el(PanelBody, { title: 'Prossima uscita', initialOpen: false },
								el(ToggleControl, {
									label: 'Mostra card prossima uscita',
									help: 'Visibile solo su desktop, auto-aggiornata dal database',
									checked: !!a.show_uscita,
									onChange: function (v) { set({ show_uscita: v }); }
								}),
								a.show_uscita ? el(Divider, {}) : null,
								a.show_uscita ? el(TextControl, {
									label: 'Sfondo card (CSS, rgba OK)',
									value: a.pu_bg || '',
									onChange: function (v) { set({ pu_bg: v }); }
								}) : null,
								a.show_uscita ? el(TextControl, {
									label: 'Bordo card (CSS, rgba OK)',
									value: a.pu_border || '',
									onChange: function (v) { set({ pu_border: v }); }
								}) : null,
								a.show_uscita ? colorRow('Colore indicatore (pallino)', 'pu_dot_color') : null,
								a.show_uscita ? colorRow('Colore testo', 'pu_text_color') : null,
								a.show_uscita ? colorRow('Colore accento (link/CTA)', 'pu_accent_color') : null,
								a.show_uscita ? colorRow('Colore avviso (posti pochi/sold out)', 'pu_warn_color') : null
							),
							el(PanelBody, { title: 'Ticker luoghi', initialOpen: false },
								el(ToggleControl, {
									label: 'Mostra ticker',
									checked: !!a.marquee_on,
									onChange: function (v) { set({ marquee_on: v }); }
								}),
								a.marquee_on ? el(TextareaControl, {
									label: 'Luoghi (separati da virgola)',
									value: a.marquee_items || '',
									onChange: function (v) { set({ marquee_items: v }); }
								}) : null,
								a.marquee_on ? el(ToggleControl, {
									label: 'Visibile su mobile',
									checked: !!a.marquee_mobile,
									onChange: function (v) { set({ marquee_mobile: v }); }
								}) : null,
								a.marquee_on ? el(Divider, {}) : null,
								a.marquee_on ? colorRow('Colore sfondo', 'marquee_bg') : null,
								a.marquee_on ? colorRow('Colore testo', 'marquee_color') : null,
								a.marquee_on ? colorRow('Colore separatore (★)', 'marquee_sep_color') : null,
								a.marquee_on ? el(RangeControl, {
									label: 'Font size (px)',
									value: a.marquee_size || 28,
									min: 12, max: 48, step: 1,
									onChange: function (v) { set({ marquee_size: v || 28 }); }
								}) : null,
								a.marquee_on ? el(RangeControl, {
									label: 'Font weight',
									value: a.marquee_weight || 700,
									min: 100, max: 900, step: 100,
									onChange: function (v) { set({ marquee_weight: v || 700 }); }
								}) : null,
								el(TextControl, {
									label: 'Colore indicatore scroll (CSS, rgba OK)',
									value: a.scroll_color || '',
									onChange: function (v) { set({ scroll_color: v }); }
								})
							)
						)
						: null;

					var preview = el('div', {
						style: {
							padding: '24px 20px',
							background: '#0a2540',
							borderRadius: '6px',
							color: '#fff',
							fontFamily: 'system-ui, sans-serif',
							minHeight: '120px',
							position: 'relative'
						}
					},
						el('div', { style: { fontSize: '10px', letterSpacing: '.12em', textTransform: 'uppercase', color: '#26CBFB', marginBottom: '8px' } },
							'~ ' + (a.eyebrow || 'Eyebrow')
						),
						el('div', { style: { fontSize: '28px', fontWeight: '900', lineHeight: '1', marginBottom: '8px' } },
							(a.title || 'Titolo').replace(/\\n/g, ' ') + ' ',
							el('em', { style: { color: '#26CBFB', fontStyle: 'normal' } }, a.title_em || 'em')
						),
						a.description ? el('div', { style: { fontSize: '12px', opacity: '.8', marginBottom: '12px' } }, a.description.substring(0, 80) + '…') : null,
						el('div', { style: { display: 'flex', gap: '8px' } },
							a.btn1_text ? el('span', { style: { background: '#ff6b4a', color: '#fff', fontSize: '11px', padding: '6px 12px', borderRadius: '999px', fontWeight: '700' } }, a.btn1_text) : null,
							a.btn2_text ? el('span', { style: { background: 'rgba(255,255,255,.1)', color: '#fff', fontSize: '11px', padding: '6px 12px', borderRadius: '999px', border: '1px solid rgba(255,255,255,.3)' } }, a.btn2_text) : null
						),
						el('div', { style: { fontSize: '10px', opacity: '.5', marginTop: '8px' } },
							'⚓ Hero Home · ' + (a.image_id ? 'img:' + a.image_id : 'no image') +
							(a.show_uscita ? ' · prossima uscita ON' : '') +
							(a.marquee_on ? ' · ticker ON' : '')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/promo-card — card personalizzabile
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/promo-card') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: 'format-image',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

					var mediaBtn = (MediaUploadCheck && MediaUpload)
						? el(MediaUploadCheck, {},
							el(MediaUpload, {
								onSelect: function (media) { set({ image_id: media.id }); },
								allowedTypes: ['image'],
								value: a.image_id,
								render: function (ref) {
									return el(Button, {
										onClick: ref.open,
										variant: a.image_id ? 'secondary' : 'primary',
										style: { marginBottom: '8px' }
									}, a.image_id ? '⬡ Cambia immagine' : '⬡ Scegli immagine');
								}
							}))
						: null;

					var controls = InspectorControls ? el(InspectorControls, {},

						/* ── Immagine ── */
						el(PanelBody, { title: 'Immagine', initialOpen: true },
							mediaBtn,
							a.image_id ? el(Button, {
								onClick: function () { set({ image_id: 0 }); },
								variant: 'link',
								isDestructive: true,
								style: { marginBottom: '12px' }
							}, 'Rimuovi immagine') : null,
							el(TextControl, {
								label: 'Alt text',
								value: a.image_alt || '',
								onChange: function (v) { set({ image_alt: v }); }
							}),
							el(RangeControl, {
								label: 'Altezza area immagine (px)',
								value: a.image_height || 200,
								min: 80,
								max: 600,
								step: 10,
								onChange: function (v) { set({ image_height: v || 200 }); }
							}),
							SelectControl ? el(SelectControl, {
								label: 'Object fit',
								value: a.image_object_fit || 'cover',
								options: [
									{ value: 'cover',   label: 'Cover (riempie)' },
									{ value: 'contain', label: 'Contain (mostra tutto)' },
									{ value: 'fill',    label: 'Fill (distorce)' },
									{ value: 'none',    label: 'Nessuno' }
								],
								onChange: function (v) { set({ image_object_fit: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Allineamento immagine',
								value: a.image_object_pos || 'center center',
								options: [
									{ value: 'center center', label: 'Centro' },
									{ value: 'top center',    label: 'Alto' },
									{ value: 'bottom center', label: 'Basso' },
									{ value: 'left center',   label: 'Sinistra' },
									{ value: 'right center',  label: 'Destra' }
								],
								onChange: function (v) { set({ image_object_pos: v }); }
							}) : null
						),

						/* ── Overlay ── */
						el(PanelBody, { title: 'Overlay immagine', initialOpen: false },
							el(TextControl, {
								label: 'Testo overlay (vuoto = nascosto)',
								value: a.overlay_text || '',
								onChange: function (v) { set({ overlay_text: v }); }
							}),
							colorRow('Sfondo overlay', 'overlay_bg'),
							colorRow('Colore testo overlay', 'overlay_color'),
							el(RangeControl, {
								label: 'Font size overlay (px)',
								value: a.overlay_size || 10,
								min: 8,
								max: 20,
								step: 1,
								onChange: function (v) { set({ overlay_size: v || 10 }); }
							})
						),

						/* ── Contenuto ── */
						el(PanelBody, { title: 'Contenuto', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow / Numero',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextControl, {
								label: 'Titolo',
								value: a.title || '',
								onChange: function (v) { set({ title: v }); }
							}),
							el(TextareaControl, {
								label: 'Descrizione',
								value: a.description || '',
								rows: 3,
								onChange: function (v) { set({ description: v }); }
							}),
							el(TextControl, {
								label: 'Testo link',
								value: a.link_text || '',
								onChange: function (v) { set({ link_text: v }); }
							}),
							el(TextControl, {
								label: 'URL link',
								value: a.link_url || '',
								type: 'url',
								onChange: function (v) { set({ link_url: v }); }
							}),
							el(ToggleControl, {
								label: 'Apri in nuova scheda',
								checked: !!a.link_new_tab,
								onChange: function (v) { set({ link_new_tab: v }); }
							})
						),

						/* ── Stile card ── */
						el(PanelBody, { title: 'Stile card', initialOpen: false },
							colorRow('Sfondo card', 'card_bg'),
							el(RangeControl, {
								label: 'Border radius (px)',
								value: a.card_radius !== undefined ? a.card_radius : 16,
								min: 0,
								max: 48,
								step: 2,
								onChange: function (v) { set({ card_radius: v }); }
							}),
							el(RangeControl, {
								label: 'Padding corpo (px)',
								value: a.card_padding !== undefined ? a.card_padding : 24,
								min: 0,
								max: 64,
								step: 4,
								onChange: function (v) { set({ card_padding: v }); }
							}),
							el(ToggleControl, {
								label: 'Ombra',
								checked: a.card_shadow !== false,
								onChange: function (v) { set({ card_shadow: v }); }
							})
						),

						/* ── Tipografia eyebrow ── */
						el(PanelBody, { title: 'Tipografia — Eyebrow', initialOpen: false },
							colorRow('Colore', 'eyebrow_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.eyebrow_size || 13,
								min: 8,
								max: 32,
								step: 1,
								onChange: function (v) { set({ eyebrow_size: v || 13 }); }
							})
						),

						/* ── Tipografia titolo ── */
						el(PanelBody, { title: 'Tipografia — Titolo', initialOpen: false },
							colorRow('Colore', 'title_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.title_size || 42,
								min: 16,
								max: 100,
								step: 2,
								onChange: function (v) { set({ title_size: v || 42 }); }
							}),
							SelectControl ? el(SelectControl, {
								label: 'Font weight',
								value: a.title_weight || '900',
								options: [
									{ value: '400', label: 'Regular (400)' },
									{ value: '600', label: 'SemiBold (600)' },
									{ value: '700', label: 'Bold (700)' },
									{ value: '800', label: 'ExtraBold (800)' },
									{ value: '900', label: 'Black (900)' }
								],
								onChange: function (v) { set({ title_weight: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Text transform',
								value: a.title_transform || 'uppercase',
								options: [
									{ value: 'uppercase',  label: 'MAIUSCOLO' },
									{ value: 'none',       label: 'Normale' },
									{ value: 'capitalize', label: 'Prima Lettera' }
								],
								onChange: function (v) { set({ title_transform: v }); }
							}) : null,
							el(TextControl, {
								label: 'Font family (vuoto = eredita tema)',
								help: 'Es: "Montserrat", sans-serif',
								value: a.title_font || '',
								onChange: function (v) { set({ title_font: v }); }
							})
						),

						/* ── Tipografia descrizione ── */
						el(PanelBody, { title: 'Tipografia — Descrizione', initialOpen: false },
							colorRow('Colore', 'desc_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.desc_size || 14,
								min: 10,
								max: 24,
								step: 1,
								onChange: function (v) { set({ desc_size: v || 14 }); }
							})
						),

						/* ── Link ── */
						el(PanelBody, { title: 'Tipografia — Link', initialOpen: false },
							colorRow('Colore', 'link_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.link_size || 13,
								min: 10,
								max: 24,
								step: 1,
								onChange: function (v) { set({ link_size: v || 13 }); }
							})
						)

					) : null;

					/* Preview nel canvas */
					var previewRadius = (a.card_radius !== undefined ? a.card_radius : 16) + 'px';
					var previewPad    = (a.card_padding !== undefined ? a.card_padding : 24) + 'px';
					var previewH      = Math.min(a.image_height || 200, 280) + 'px';

					var preview = el('div', {
						style: {
							maxWidth: '300px',
							borderRadius: previewRadius,
							overflow: 'hidden',
							background: a.card_bg || '#ffffff',
							fontFamily: 'system-ui, sans-serif',
							boxShadow: a.card_shadow !== false ? '0 8px 32px -8px rgba(6,24,38,.2)' : 'none'
						}
					},
						/* Image area */
						el('div', {
							style: {
								height: previewH,
								background: a.image_id ? '#b8d4e0' : '#d5e8ef',
								position: 'relative',
								overflow: 'hidden',
								display: 'flex',
								alignItems: 'flex-end'
							}
						},
							a.image_id ? el('div', {
								style: { position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#1B77A7', fontSize: '12px', opacity: .7, pointerEvents: 'none' }
							}, '📷 img #' + a.image_id) : null,
							a.overlay_text ? el('div', {
								style: {
									position: 'relative',
									width: '100%',
									padding: '8px 14px',
									background: a.overlay_bg || 'rgba(6,24,38,0.6)',
									color: a.overlay_color || '#fff',
									fontSize: (a.overlay_size || 10) + 'px',
									fontWeight: 700,
									letterSpacing: '.14em',
									textTransform: 'uppercase'
								}
							}, a.overlay_text) : null
						),
						/* Body */
						el('div', { style: { padding: previewPad } },
							a.eyebrow ? el('div', {
								style: { color: a.eyebrow_color || '#1B77A7', fontSize: (a.eyebrow_size || 13) + 'px', fontWeight: 700, marginBottom: '6px' }
							}, a.eyebrow) : null,
							a.title ? el('div', {
								style: {
									color: a.title_color || '#061826',
									fontSize: Math.min(a.title_size || 42, 36) + 'px',
									fontWeight: a.title_weight || '900',
									textTransform: a.title_transform || 'uppercase',
									lineHeight: 1,
									marginBottom: '10px',
									fontFamily: a.title_font || 'inherit'
								}
							}, a.title) : null,
							a.description ? el('div', {
								style: { color: a.desc_color || '#3d5a6c', fontSize: (a.desc_size || 14) + 'px', lineHeight: 1.5, marginBottom: '14px' }
							}, a.description) : null,
							el('div', {
								style: { color: a.link_color || '#1B77A7', fontSize: (a.link_size || 13) + 'px', fontWeight: 600 }
							}, (a.link_text || 'Scopri') + ' →')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/sezione — sezione generica con InnerBlocks
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/sezione') {
			var InnerBlocks = blockEditor ? blockEditor.InnerBlocks : null;
			if (!InnerBlocks) return;

			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'layout',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}
					var fwOpts = [
						{ value: '300', label: 'Light (300)' },
						{ value: '400', label: 'Regular (400)' },
						{ value: '500', label: 'Medium (500)' },
						{ value: '600', label: 'SemiBold (600)' },
						{ value: '700', label: 'Bold (700)' },
						{ value: '800', label: 'ExtraBold (800)' },
						{ value: '900', label: 'Black (900)' }
					];
					function weightRow(label, key, def) {
						return SelectControl ? el(SelectControl, {
							label: label,
							value: String(a[key] || def),
							options: fwOpts,
							onChange: function (v) { var u = {}; u[key] = parseInt(v, 10); set(u); }
						}) : null;
					}
					function subHead(text) {
						return el('p', { style: { fontSize: '10px', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.1em', color: '#757575', margin: '12px 0 6px', borderBottom: '1px solid #e0e0e0', paddingBottom: '5px' } }, text);
					}

					var mediaBtn = (MediaUploadCheck && MediaUpload)
						? el(MediaUploadCheck, {},
							el(MediaUpload, {
								onSelect: function (media) { set({ bg_image_id: media.id }); },
								allowedTypes: ['image'],
								value: a.bg_image_id,
								render: function (ref) {
									return el(Button, {
										onClick: ref.open,
										variant: a.bg_image_id ? 'secondary' : 'primary',
										style: { marginBottom: '8px' }
									}, a.bg_image_id ? '⬡ Cambia sfondo' : '⬡ Scegli immagine sfondo');
								}
							}))
						: null;

					var controls = InspectorControls ? el(InspectorControls, {},

						el(PanelBody, { title: 'Intestazione', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextareaControl, {
								label: 'Titolo (\\n per a capo)',
								value: a.title || '',
								rows: 3,
								onChange: function (v) { set({ title: v }); }
							}),
							el(TextControl, {
								label: 'Testo link intestazione (vuoto = nascosto)',
								value: a.header_link_text || '',
								onChange: function (v) { set({ header_link_text: v }); }
							}),
							a.header_link_text ? el(TextControl, {
								label: 'URL link intestazione',
								value: a.header_link_url || '',
								type: 'url',
								onChange: function (v) { set({ header_link_url: v }); }
							}) : null
						),

						el(PanelBody, { title: 'Sfondo', initialOpen: false },
							colorRow('Colore sfondo', 'bg_color'),
							mediaBtn,
							a.bg_image_id ? el(Button, {
								onClick: function () { set({ bg_image_id: 0 }); },
								variant: 'link',
								isDestructive: true,
								style: { marginBottom: '8px' }
							}, 'Rimuovi immagine') : null
						),

						el(PanelBody, { title: 'Tipografia', initialOpen: false },
							subHead('Eyebrow'),
							colorRow('Colore', 'eyebrow_color'),
							el(RangeControl, {
								label: 'Dimensione (px)',
								value: a.eyebrow_size || 13,
								min: 8, max: 40, step: 1,
								onChange: function (v) { set({ eyebrow_size: v || 13 }); }
							}),
							el(RangeControl, {
								label: 'Letter spacing (em ×100)',
								value: a.eyebrow_letter_spacing !== undefined ? a.eyebrow_letter_spacing : 16,
								min: 0, max: 50, step: 1,
								onChange: function (v) { set({ eyebrow_letter_spacing: v === undefined ? 16 : v }); }
							}),
							weightRow('Peso font', 'eyebrow_font_weight', 600),
							el(RangeControl, {
								label: 'Margine inferiore (px)',
								value: a.eyebrow_margin_bottom !== undefined ? a.eyebrow_margin_bottom : 16,
								min: 0, max: 60, step: 2,
								onChange: function (v) { set({ eyebrow_margin_bottom: v === undefined ? 16 : v }); }
							}),
							subHead('Titolo'),
							colorRow('Colore', 'title_color'),
							el(RangeControl, {
								label: 'Dimensione (px)',
								value: a.title_size || 76,
								min: 20, max: 120, step: 2,
								onChange: function (v) { set({ title_size: v || 76 }); }
							}),
							el(RangeControl, {
								label: 'Interlinea (×100, es. 95 = 0.95)',
								value: a.title_line_height !== undefined ? a.title_line_height : 95,
								min: 70, max: 200, step: 5,
								onChange: function (v) { set({ title_line_height: v === undefined ? 95 : v }); }
							}),
							weightRow('Peso font', 'title_font_weight', 900),
							subHead('Link intestazione'),
							colorRow('Colore link (vuoto = usa colore eyebrow)', 'link_color'),
							el(RangeControl, {
								label: 'Dimensione (px)',
								value: a.link_size || 14,
								min: 10, max: 30, step: 1,
								onChange: function (v) { set({ link_size: v || 14 }); }
							}),
							weightRow('Peso font', 'link_font_weight', 600)
						),

						el(PanelBody, { title: 'Spaziatura', initialOpen: false },
							el(RangeControl, {
								label: 'Padding verticale (px)',
								value: a.padding_y || 80,
								min: 0, max: 200, step: 8,
								onChange: function (v) { set({ padding_y: v || 80 }); }
							}),
							el(RangeControl, {
								label: 'Padding orizzontale (px)',
								value: a.padding_x !== undefined ? a.padding_x : 48,
								min: 0, max: 200, step: 4,
								onChange: function (v) { set({ padding_x: v === undefined ? 48 : v }); }
							}),
							el(RangeControl, {
								label: 'Margine sotto intestazione (px)',
								value: a.head_margin_bottom !== undefined ? a.head_margin_bottom : 48,
								min: 0, max: 120, step: 4,
								onChange: function (v) { set({ head_margin_bottom: v === undefined ? 48 : v }); }
							}),
							el(RangeControl, {
								label: 'Larghezza massima (px)',
								value: a.max_width || 1320,
								min: 400, max: 1920, step: 20,
								onChange: function (v) { set({ max_width: v || 1320 }); }
							})
						)

					) : null;

					var titleLines = (a.title || '').split('\\n');
					var hasHeader  = a.eyebrow || a.title || (a.header_link_text && a.header_link_url);
					var effLinkColor = (a.link_color && a.link_color !== '') ? a.link_color : (a.eyebrow_color || '#1B77A7');

					return el(Fragment, {},
						controls,
						el('section', {
							style: { backgroundColor: a.bg_color || '#dff4f8', fontFamily: 'system-ui,sans-serif' }
						},
							el('div', {
								style: {
									maxWidth: (a.max_width || 1320) + 'px',
									margin: '0 auto',
									padding: (a.padding_y || 80) + 'px ' + (a.padding_x !== undefined ? a.padding_x : 48) + 'px'
								}
							},
								hasHeader ? el('div', {
									style: { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', gap: '24px', marginBottom: (a.head_margin_bottom !== undefined ? a.head_margin_bottom : 48) + 'px', flexWrap: 'wrap' }
								},
									el('div', {},
										a.eyebrow ? el('span', {
											style: { display: 'block', fontWeight: a.eyebrow_font_weight || 600, letterSpacing: ((a.eyebrow_letter_spacing !== undefined ? a.eyebrow_letter_spacing : 16) / 100) + 'em', textTransform: 'uppercase', fontSize: (a.eyebrow_size || 13) + 'px', color: a.eyebrow_color || '#1B77A7', marginBottom: (a.eyebrow_margin_bottom !== undefined ? a.eyebrow_margin_bottom : 16) + 'px' }
										}, a.eyebrow) : null,
										a.title ? el('h2', {
											style: { fontSize: Math.min(a.title_size || 76, 60) + 'px', lineHeight: (a.title_line_height !== undefined ? a.title_line_height : 95) / 100, color: a.title_color || '#1B77A7', margin: 0, fontWeight: a.title_font_weight || 900 }
										}, titleLines.join(' · ')) : null
									),
									(a.header_link_text) ? el('span', {
										style: { flexShrink: 0, display: 'inline-flex', alignItems: 'center', gap: '8px', fontSize: (a.link_size || 14) + 'px', fontWeight: a.link_font_weight || 600, color: effLinkColor }
									}, a.header_link_text + ' →') : null
								) : null,
								el(InnerBlocks, {})
							)
						)
					);
				},
				save: function () {
					return el(InnerBlocks.Content, {});
				},
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/galleria — muro di foto con sorgente configurabile
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/galleria') {
			var useState  = element.useState;
			var useEffect = element.useEffect;

			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'format-gallery',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					var previewItemsState = useState([]);
					var previewItems = previewItemsState[0];
					var setPreviewItemsState = previewItemsState[1];
					var loadingState = useState(false);

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

					var fwOpts = [
						{ value: '300', label: 'Light (300)' },
						{ value: '400', label: 'Regular (400)' },
						{ value: '500', label: 'Medium (500)' },
						{ value: '600', label: 'SemiBold (600)' },
						{ value: '700', label: 'Bold (700)' },
						{ value: '800', label: 'ExtraBold (800)' },
						{ value: '900', label: 'Black (900)' }
					];
					function weightRow(label, key, def) {
						return SelectControl ? el(SelectControl, {
							label: label,
							value: String(a[key] || def),
							options: fwOpts,
							onChange: function (v) { var u = {}; u[key] = parseInt(v, 10); set(u); }
						}) : null;
					}

					var mediaBtn = (MediaUploadCheck && MediaUpload)
						? el(MediaUploadCheck, {},
							el(MediaUpload, {
								onSelect: function (media) {
									var list = Array.isArray(media) ? media : [media];
									set({ manual_ids: list.map(function (m) { return m.id; }) });
									setPreviewItemsState(list.map(function (m) {
										return {
											id: m.id,
											url: (m.sizes && m.sizes.medium ? m.sizes.medium.url : m.url),
											ratio: (m.width && m.height) ? (m.width / m.height) : 1
										};
									}));
								},
								allowedTypes: ['image'],
								multiple: true,
								gallery: true,
								value: a.manual_ids || [],
								render: function (ref) {
									return el(Button, {
										onClick: ref.open,
										variant: 'primary',
										style: { marginBottom: '8px' }
									}, (a.manual_ids && a.manual_ids.length) ? 'Modifica selezione (' + a.manual_ids.length + ')' : 'Scegli immagini');
								}
							}))
						: null;

					var tagOptionsState = useState([]);
					var tagOptions = tagOptionsState[0];
					var setTagOptions = tagOptionsState[1];

					useEffect(function () {
						if (!window.wp.apiFetch) return;
						window.wp.apiFetch({ path: '/wp/v2/calypso_media_tag?per_page=100' }).then(function (terms) {
							setTagOptions(terms.map(function (t) { return { value: String(t.id), label: t.name }; }));
						}).catch(function () { setTagOptions([]); });
					}, []);

					useEffect(function () {
						if (!window.wp.apiFetch) return;
						loadingState[1](true);
						var path = '/wp/v2/media?per_page=' + (a.max_items > 0 ? a.max_items : 24) + '&orderby=date&order=desc';
						if (a.source_mode === 'tag' && a.tag_ids && a.tag_ids.length) {
							path += '&calypso_media_tag=' + a.tag_ids.join(',');
						}
						window.wp.apiFetch({ path: path }).then(function (media) {
							var filtered = media;
							if (a.source_mode === 'all') {
								filtered = media.filter(function (m) {
									return m.calypso_media_tag && m.calypso_media_tag.length > 0;
								});
							}
							setPreviewItemsState(filtered.map(function (m) {
								var w = m.media_details && m.media_details.width;
								var h = m.media_details && m.media_details.height;
								return {
									id: m.id,
									url: (m.media_details && m.media_details.sizes && m.media_details.sizes.medium ? m.media_details.sizes.medium.source_url : m.source_url),
									ratio: (w && h) ? (w / h) : 1
								};
							}));
						}).catch(function () { setPreviewItemsState([]); }).finally(function () { loadingState[1](false); });
					}, [a.source_mode, JSON.stringify(a.tag_ids), a.max_items]);

					var controls = InspectorControls ? el(InspectorControls, {},

						el(PanelBody, { title: 'Sorgente immagini', initialOpen: true },
							SelectControl ? el(SelectControl, {
								label: 'Modalità',
								value: a.source_mode || 'all',
								options: [
									{ value: 'all',    label: 'Tutti i media taggati' },
									{ value: 'tag',    label: 'Per tag specifico' },
									{ value: 'manual', label: 'Selezione manuale' }
								],
								onChange: function (v) { set({ source_mode: v }); }
							}) : null,
							(a.source_mode === 'tag' && SelectControl) ? el(SelectControl, {
								label: 'Tag galleria',
								multiple: true,
								value: (a.tag_ids || []).map(String),
								options: tagOptions,
								onChange: function (v) { set({ tag_ids: (v || []).map(function (id) { return parseInt(id, 10); }) }); }
							}) : null,
							(a.source_mode === 'manual') ? mediaBtn : null
						),

						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							el(RangeControl, {
								label: 'Numero massimo immagini (0 = tutte)',
								value: a.max_items !== undefined ? a.max_items : 12,
								min: 0, max: 60, step: 1,
								onChange: function (v) { set({ max_items: v === undefined ? 12 : v }); }
							}),
							el(ToggleControl, {
								label: 'Lightbox al click',
								help: a.lightbox ? "Click sull'immagine apre l'anteprima a schermo intero." : 'Immagini non cliccabili.',
								checked: !!a.lightbox,
								onChange: function (v) { set({ lightbox: v }); }
							})
						),

						el(PanelBody, { title: 'Aspetto', initialOpen: false },
							el(RangeControl, {
								label: 'Gap tra celle (px)',
								value: a.gap !== undefined ? a.gap : 0,
								min: 0, max: 40, step: 2,
								onChange: function (v) { set({ gap: v === undefined ? 0 : v }); }
							}),
							el(RangeControl, {
								label: 'Altezza riga base (px)',
								value: a.row_height || 200,
								min: 80, max: 360, step: 10,
								onChange: function (v) { set({ row_height: v || 200 }); }
							}),
							el(RangeControl, {
								label: 'Larghezza massima (px, 0 = adatta al contenitore)',
								value: a.max_width !== undefined ? a.max_width : 1320,
								min: 0, max: 1920, step: 20,
								onChange: function (v) { set({ max_width: v === undefined ? 1320 : v }); }
							}),
							colorRow('Colore sfondo sezione', 'bg_color')
						),

						el(PanelBody, { title: 'Margini blocco', initialOpen: false },
							el(RangeControl, {
								label: 'Margine superiore (px)',
								value: a.margin_top !== undefined ? a.margin_top : 0,
								min: 0, max: 200, step: 4,
								onChange: function (v) { set({ margin_top: v === undefined ? 0 : v }); }
							}),
							el(RangeControl, {
								label: 'Margine inferiore (px)',
								value: a.margin_bottom !== undefined ? a.margin_bottom : 0,
								min: 0, max: 200, step: 4,
								onChange: function (v) { set({ margin_bottom: v === undefined ? 0 : v }); }
							}),
							el(RangeControl, {
								label: 'Margine sinistro (px)',
								value: a.margin_left !== undefined ? a.margin_left : 0,
								min: 0, max: 200, step: 4,
								onChange: function (v) { set({ margin_left: v === undefined ? 0 : v }); }
							}),
							el(RangeControl, {
								label: 'Margine destro (px)',
								value: a.margin_right !== undefined ? a.margin_right : 0,
								min: 0, max: 200, step: 4,
								onChange: function (v) { set({ margin_right: v === undefined ? 0 : v }); }
							})
						),

						el(PanelBody, { title: 'Overlay didascalia', initialOpen: false },
							colorRow('Colore testo', 'overlay_color'),
							colorRow('Sfondo overlay', 'overlay_bg'),
							el(RangeControl, {
								label: 'Dimensione testo (px)',
								value: a.overlay_size || 10,
								min: 8, max: 24, step: 1,
								onChange: function (v) { set({ overlay_size: v || 10 }); }
							}),
							weightRow('Peso font', 'overlay_font_weight', 400),
							el(RangeControl, {
								label: 'Letter spacing (em ×100)',
								value: a.overlay_letter_spacing !== undefined ? a.overlay_letter_spacing : 12,
								min: 0, max: 40, step: 1,
								onChange: function (v) { set({ overlay_letter_spacing: v === undefined ? 12 : v }); }
							})
						)

					) : null;

					var previewRowH = ((a.row_height || 200) / 2) + 'px';
					var fillerEls = [0, 1, 2, 3, 4, 5].map(function (i) {
						return el('div', { key: 'filler-' + i, style: { flexGrow: 999, height: 0, minWidth: 1 } });
					});

					var preview = previewItems.length
						? el('div', {
							style: {
								display: 'flex',
								flexWrap: 'wrap',
								gap: (a.gap !== undefined ? a.gap : 0) + 'px',
								maxWidth: '100%'
							}
						},
							previewItems.map(function (item) {
								var ratio = item.ratio || 1;
								return el('div', {
									key: item.id,
									style: {
										height: previewRowH,
										flexGrow: ratio,
										flexBasis: 'calc(' + ratio + ' * ' + previewRowH + ')',
										overflow: 'hidden',
										background: '#0a2540',
										position: 'relative'
									}
								}, item.url ? el('img', { src: item.url, style: { width: '100%', height: '100%', objectFit: 'cover', position: 'absolute', inset: 0 } }) : null);
							}).concat(fillerEls)
						)
						: el('p', { style: { fontSize: '12px', opacity: .6 } },
							loadingState[0] ? 'Caricamento immagini…' : 'Nessuna immagine trovata per questa sorgente.');

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/storia-club — timeline storica configurabile
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/storia-club') {
			var useState2  = element.useState;
			var useEffect2 = element.useEffect;

			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'backup',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

					var fwOpts2 = [
						{ value: '300', label: 'Light (300)' },
						{ value: '400', label: 'Regular (400)' },
						{ value: '500', label: 'Medium (500)' },
						{ value: '600', label: 'SemiBold (600)' },
						{ value: '700', label: 'Bold (700)' },
						{ value: '800', label: 'ExtraBold (800)' },
						{ value: '900', label: 'Black (900)' }
					];
					function weightRow2(label, key, def) {
						return SelectControl ? el(SelectControl, {
							label: label,
							value: String(a[key] || def),
							options: fwOpts2,
							onChange: function (v) { var u = {}; u[key] = parseInt(v, 10); set(u); }
						}) : null;
					}

					var categoryOptionsState = useState2([]);
					var categoryOptions = categoryOptionsState[0];
					var setCategoryOptions = categoryOptionsState[1];
					var tagOptionsState = useState2([]);
					var tagOptions = tagOptionsState[0];
					var setTagOptions = tagOptionsState[1];
					var previewItemsState = useState2([]);
					var previewItems = previewItemsState[0];
					var setPreviewItems = previewItemsState[1];
					var loadingState = useState2(false);
					var searchTermState = useState2('');
					var searchTerm = searchTermState[0];
					var setSearchTerm = searchTermState[1];
					var searchResultsState = useState2([]);
					var searchResults = searchResultsState[0];
					var setSearchResults = searchResultsState[1];
					var manualTitlesState = useState2({});
					var manualTitles = manualTitlesState[0];
					var setManualTitles = manualTitlesState[1];

					useEffect2(function () {
						if (!window.wp.apiFetch) return;
						window.wp.apiFetch({ path: '/wp/v2/categories?per_page=100' }).then(function (terms) {
							setCategoryOptions(terms.map(function (t) { return { value: String(t.id), label: t.name }; }));
						}).catch(function () { setCategoryOptions([]); });
						window.wp.apiFetch({ path: '/wp/v2/tags?per_page=100' }).then(function (terms) {
							setTagOptions(terms.map(function (t) { return { value: String(t.id), label: t.name }; }));
						}).catch(function () { setTagOptions([]); });
					}, []);

					useEffect2(function () {
						if (!window.wp.apiFetch || a.source_mode !== 'manual' || !(a.manual_ids || []).length) return;
						window.wp.apiFetch({ path: '/wp/v2/posts?include=' + a.manual_ids.join(',') + '&per_page=100' }).then(function (posts) {
							var map = {};
							posts.forEach(function (p) { map[p.id] = p.title.rendered; });
							setManualTitles(map);
						}).catch(function () {});
					}, [JSON.stringify(a.manual_ids), a.source_mode]);

					useEffect2(function () {
						if (!window.wp.apiFetch) return;
						if (a.source_mode === 'manual') {
							if (!(a.manual_ids || []).length) { setPreviewItems([]); return; }
							loadingState[1](true);
							window.wp.apiFetch({ path: '/wp/v2/posts?include=' + a.manual_ids.join(',') + '&per_page=100' }).then(function (posts) {
								var byId = {};
								posts.forEach(function (p) { byId[p.id] = p; });
								setPreviewItems(a.manual_ids.map(function (id) { return byId[id]; }).filter(Boolean));
							}).catch(function () { setPreviewItems([]); }).finally(function () { loadingState[1](false); });
							return;
						}
						loadingState[1](true);
						var path = '/wp/v2/posts?per_page=' + (a.max_items > 0 ? a.max_items : 20) + '&orderby=date&order=' + (a.order === 'desc' ? 'desc' : 'asc');
						if (a.category_ids && a.category_ids.length) path += '&categories=' + a.category_ids.join(',');
						if (a.tag_ids && a.tag_ids.length) path += '&tags=' + a.tag_ids.join(',');
						if (a.date_from) path += '&after=' + a.date_from + 'T00:00:00';
						if (a.date_to) path += '&before=' + a.date_to + 'T23:59:59';
						window.wp.apiFetch({ path: path }).then(function (posts) {
							setPreviewItems(posts);
						}).catch(function () { setPreviewItems([]); }).finally(function () { loadingState[1](false); });
					}, [a.source_mode, JSON.stringify(a.category_ids), JSON.stringify(a.tag_ids), JSON.stringify(a.manual_ids), a.date_from, a.date_to, a.max_items, a.order]);

					function doSearch(term) {
						setSearchTerm(term);
						if (!window.wp.apiFetch || !term) { setSearchResults([]); return; }
						window.wp.apiFetch({ path: '/wp/v2/posts?search=' + encodeURIComponent(term) + '&per_page=10' }).then(function (posts) {
							setSearchResults(posts);
						}).catch(function () { setSearchResults([]); });
					}

					function addManual(id) {
						var current = a.manual_ids || [];
						if (current.indexOf(id) === -1) set({ manual_ids: current.concat([id]) });
					}
					function removeManual(id) {
						set({ manual_ids: (a.manual_ids || []).filter(function (mid) { return mid !== id; }) });
					}

					var manualPicker = el('div', {},
						el(TextControl, {
							label: 'Cerca articoli da aggiungere',
							value: searchTerm,
							onChange: doSearch
						}),
						searchResults.length ? el('ul', { style: { listStyle: 'none', margin: '0 0 12px', padding: 0 } },
							searchResults.map(function (p) {
								return el('li', { key: p.id, style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '4px 0' } },
									el('span', { style: { fontSize: '12px' } }, p.title),
									el(Button, { variant: 'secondary', isSmall: true, onClick: function () { addManual(p.id); } }, '+ Aggiungi')
								);
							})
						) : null,
						(a.manual_ids || []).length ? el('ul', { style: { listStyle: 'none', margin: 0, padding: 0 } },
							a.manual_ids.map(function (id) {
								return el('li', { key: id, style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '4px 0', borderTop: '1px solid #eee' } },
									el('span', { style: { fontSize: '12px' } }, manualTitles[id] || ('#' + id)),
									el(Button, { variant: 'link', isDestructive: true, isSmall: true, onClick: function () { removeManual(id); } }, 'Rimuovi')
								);
							})
						) : el('p', { style: { fontSize: '11px', opacity: .6 } }, 'Nessun articolo selezionato.')
					);

					var controls = InspectorControls ? el(InspectorControls, {},

						el(PanelBody, { title: 'Sorgente articoli', initialOpen: true },
							SelectControl ? el(SelectControl, {
								label: 'Modalità',
								value: a.source_mode || 'query',
								options: [
									{ value: 'query',  label: 'Per categoria/tag/data' },
									{ value: 'manual', label: 'Selezione manuale' }
								],
								onChange: function (v) { set({ source_mode: v }); }
							}) : null,
							(a.source_mode === 'manual') ? manualPicker : el(Fragment, {},
								SelectControl ? el(SelectControl, {
									label: 'Categorie (combinate con AND su tag)',
									multiple: true,
									value: (a.category_ids || []).map(String),
									options: categoryOptions,
									onChange: function (v) { set({ category_ids: (v || []).map(function (id) { return parseInt(id, 10); }) }); }
								}) : null,
								SelectControl ? el(SelectControl, {
									label: 'Tag',
									multiple: true,
									value: (a.tag_ids || []).map(String),
									options: tagOptions,
									onChange: function (v) { set({ tag_ids: (v || []).map(function (id) { return parseInt(id, 10); }) }); }
								}) : null,
								el(TextControl, {
									label: 'Data pubblicazione da (YYYY-MM-DD)',
									value: a.date_from || '',
									type: 'date',
									onChange: function (v) { set({ date_from: v }); }
								}),
								el(TextControl, {
									label: 'Data pubblicazione a (YYYY-MM-DD)',
									value: a.date_to || '',
									type: 'date',
									onChange: function (v) { set({ date_to: v }); }
								}),
								el(RangeControl, {
									label: 'Anno evento da (0 = nessun limite)',
									value: a.year_from || 0,
									min: 0, max: 2100, step: 1,
									onChange: function (v) { set({ year_from: v || 0 }); }
								}),
								el(RangeControl, {
									label: 'Anno evento a (0 = nessun limite)',
									value: a.year_to || 0,
									min: 0, max: 2100, step: 1,
									onChange: function (v) { set({ year_to: v || 0 }); }
								})
							)
						),

						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							el(RangeControl, {
								label: 'Numero massimo articoli (0 = tutti)',
								value: a.max_items !== undefined ? a.max_items : 0,
								min: 0, max: 60, step: 1,
								onChange: function (v) { set({ max_items: v === undefined ? 0 : v }); }
							}),
							SelectControl ? el(SelectControl, {
								label: 'Ordina per',
								value: a.order_by || 'event_year',
								options: [
									{ value: 'event_year', label: 'Anno evento' },
									{ value: 'post_date',  label: 'Data pubblicazione' }
								],
								onChange: function (v) { set({ order_by: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Direzione',
								value: a.order || 'asc',
								options: [
									{ value: 'asc',  label: 'Crescente (cronologico)' },
									{ value: 'desc', label: 'Decrescente' }
								],
								onChange: function (v) { set({ order: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Titolo evento',
								value: a.title_source || 'post_title',
								options: [
									{ value: 'post_title', label: 'Titolo articolo' },
									{ value: 'custom',     label: 'Titolo breve timeline (custom)' }
								],
								onChange: function (v) { set({ title_source: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Testo evento',
								value: a.text_source || 'excerpt',
								options: [
									{ value: 'excerpt', label: 'Excerpt articolo' },
									{ value: 'custom',  label: 'Testo breve timeline (custom)' }
								],
								onChange: function (v) { set({ text_source: v }); }
							}) : null,
							el(ToggleControl, {
								label: 'Item cliccabili (link al permalink)',
								checked: a.clickable !== false,
								onChange: function (v) { set({ clickable: v }); }
							}),
							(a.clickable !== false) ? el(ToggleControl, {
								label: 'Apri in nuova scheda',
								checked: !!a.link_new_tab,
								onChange: function (v) { set({ link_new_tab: v }); }
							}) : null
						),

						el(PanelBody, { title: 'Aspetto', initialOpen: false },
							el(RangeControl, {
								label: 'Colonne (desktop)',
								value: a.columns || 5,
								min: 2, max: 8, step: 1,
								onChange: function (v) { set({ columns: v || 5 }); }
							}),
							SelectControl ? el(SelectControl, {
								label: 'Se gli articoli superano le colonne',
								help: a.desktop_overflow === 'scroll' ? 'La riga scorre orizzontalmente.' : 'Gli elementi extra vengono nascosti, i rimanenti centrati (nessuna scrollbar).',
								value: a.desktop_overflow || 'hide',
								options: [
									{ value: 'hide',   label: 'Nascondi extra e centra' },
									{ value: 'scroll', label: 'Scorri orizzontalmente' }
								],
								onChange: function (v) { set({ desktop_overflow: v }); }
							}) : null,
							el(RangeControl, {
								label: 'Gap (px)',
								value: a.gap !== undefined ? a.gap : 32,
								min: 0, max: 80, step: 4,
								onChange: function (v) { set({ gap: v === undefined ? 32 : v }); }
							}),
						el(RangeControl, {
								label: 'Larghezza massima (px)',
								value: a.max_width !== undefined ? a.max_width : 1320,
								min: 0, max: 1920, step: 20,
								onChange: function (v) { set({ max_width: v === undefined ? 1320 : v }); }
							}),
							el(RangeControl, {
								label: 'Padding verticale sezione (px)',
								value: a.padding_y !== undefined ? a.padding_y : 0,
								min: 0, max: 160, step: 4,
								onChange: function (v) { set({ padding_y: v === undefined ? 0 : v }); }
							}),
							el(RangeControl, {
								label: 'Padding orizzontale sezione (px)',
								value: a.padding_x !== undefined ? a.padding_x : 0,
								min: 0, max: 160, step: 4,
								onChange: function (v) { set({ padding_x: v === undefined ? 0 : v }); }
							}),
							colorRow('Sfondo sezione', 'bg_color'),
							colorRow('Linea timeline', 'line_color'),
							el(RangeControl, {
								label: 'Spessore linea (px)',
								value: a.line_thickness !== undefined ? a.line_thickness : 1,
								min: 1, max: 8, step: 1,
								onChange: function (v) { set({ line_thickness: v === undefined ? 1 : v }); }
							}),
							colorRow('Pallino', 'dot_color'),
							colorRow('Pallino ultimo item', 'dot_color_last'),
							el(RangeControl, {
								label: 'Dimensione pallino (px, desktop)',
								value: a.dot_size !== undefined ? a.dot_size : 12,
								min: 4, max: 32, step: 1,
								onChange: function (v) { set({ dot_size: v === undefined ? 12 : v }); }
							}),
							el(RangeControl, {
								label: 'Spazio pallino ↔ linea ↔ anno (px, simmetrico)',
								help: 'Stesso spazio sopra e sotto la linea: tra pallino e linea, e tra linea e anno.',
								value: a.gap_dot_year !== undefined ? a.gap_dot_year : 18,
								min: 0, max: 60, step: 2,
								onChange: function (v) { set({ gap_dot_year: v === undefined ? 18 : v }); }
							}),
							el(RangeControl, {
								label: 'Spazio anno → titolo (px)',
								value: a.gap_year_title !== undefined ? a.gap_year_title : 8,
								min: 0, max: 40, step: 2,
								onChange: function (v) { set({ gap_year_title: v === undefined ? 8 : v }); }
							}),
							el(RangeControl, {
								label: 'Spazio titolo → testo (px)',
								value: a.gap_title_text !== undefined ? a.gap_title_text : 6,
								min: 0, max: 40, step: 2,
								onChange: function (v) { set({ gap_title_text: v === undefined ? 6 : v }); }
							})
						),

						el(PanelBody, { title: 'Tipografia — Anno', initialOpen: false },
							colorRow('Colore', 'year_color'),
							el(RangeControl, {
								label: 'Dimensione (px)',
								value: a.year_size || 36,
								min: 14, max: 80, step: 2,
								onChange: function (v) { set({ year_size: v || 36 }); }
							}),
							weightRow2('Peso font', 'year_font_weight', 800),
							el(RangeControl, {
								label: 'Letter spacing (em ×100)',
								value: a.year_letter_spacing !== undefined ? a.year_letter_spacing : 0,
								min: -10, max: 40, step: 1,
								onChange: function (v) { set({ year_letter_spacing: v === undefined ? 0 : v }); }
							}),
							el(TextControl, {
								label: 'Font family (vuoto = eredita tema)',
								help: 'Es: "Big Shoulders Display", Impact, sans-serif',
								value: a.year_font || '',
								onChange: function (v) { set({ year_font: v }); }
							})
						),

						el(PanelBody, { title: 'Tipografia — Titolo', initialOpen: false },
							colorRow('Colore', 'title_color'),
							el(RangeControl, {
								label: 'Dimensione (px)',
								value: a.title_size || 18,
								min: 10, max: 48, step: 1,
								onChange: function (v) { set({ title_size: v || 18 }); }
							}),
							weightRow2('Peso font', 'title_font_weight', 800),
							el(RangeControl, {
								label: 'Letter spacing (em ×100)',
								value: a.title_letter_spacing !== undefined ? a.title_letter_spacing : 0,
								min: -10, max: 40, step: 1,
								onChange: function (v) { set({ title_letter_spacing: v === undefined ? 0 : v }); }
							}),
							SelectControl ? el(SelectControl, {
								label: 'Text transform',
								value: a.title_transform || 'uppercase',
								options: [
									{ value: 'uppercase',  label: 'MAIUSCOLO' },
									{ value: 'none',       label: 'Normale' },
									{ value: 'capitalize', label: 'Prima Lettera' }
								],
								onChange: function (v) { set({ title_transform: v }); }
							}) : null,
							el(TextControl, {
								label: 'Font family (vuoto = eredita tema)',
								help: 'Es: "Big Shoulders Display", Impact, sans-serif',
								value: a.title_font || '',
								onChange: function (v) { set({ title_font: v }); }
							})
						),

						el(PanelBody, { title: 'Tipografia — Testo', initialOpen: false },
							colorRow('Colore', 'text_color'),
							el(RangeControl, {
								label: 'Dimensione (px)',
								value: a.text_size || 13,
								min: 8, max: 24, step: 1,
								onChange: function (v) { set({ text_size: v || 13 }); }
							}),
							weightRow2('Peso font', 'text_font_weight', 400),
							el(RangeControl, {
								label: 'Interlinea (×100, es. 150 = 1.5)',
								value: a.text_line_height !== undefined ? a.text_line_height : 150,
								min: 100, max: 250, step: 5,
								onChange: function (v) { set({ text_line_height: v === undefined ? 150 : v }); }
							})
						)

					) : null;

					var isHideOverflow = (a.desktop_overflow || 'hide') === 'hide';
					var visibleItems = (isHideOverflow && previewItems.length > (a.columns || 5))
						? previewItems.slice(0, a.columns || 5)
						: previewItems;

					var preview = el('div', {
						style: {
							display: 'flex',
							justifyContent: isHideOverflow ? 'center' : 'flex-start',
							gap: (a.gap !== undefined ? a.gap : 32) + 'px',
							overflowX: isHideOverflow ? 'hidden' : 'auto',
							background: a.bg_color || '#061826',
							padding: '20px',
							fontFamily: 'system-ui,sans-serif'
						}
					},
						visibleItems.length
							? visibleItems.map(function (p, index) {
								var isLast = index === visibleItems.length - 1;
								var title = p.title && p.title.rendered ? p.title.rendered : '';
								var excerpt = p.excerpt && p.excerpt.rendered ? p.excerpt.rendered.replace(/<[^>]+>/g, '').trim() : '';
								return el('div', {
									key: p.id,
									style: { flex: '0 0 ' + (100 / (a.columns || 5)) + '%', minWidth: '140px' }
								},
									el('div', { style: { width: '12px', height: '12px', borderRadius: '50%', background: isLast ? (a.dot_color_last || '#FF6B4A') : (a.dot_color || '#5FB8C8'), marginBottom: '10px' } }),
									el('div', { style: { fontSize: '22px', fontWeight: 800, textTransform: 'uppercase', color: a.year_color || '#5FB8C8', marginBottom: '4px' } }, new Date(p.date).getFullYear()),
									el('div', { style: { fontSize: '13px', fontWeight: 800, textTransform: 'uppercase', color: a.title_color || '#fff', marginBottom: '4px' } }, title),
									el('div', { style: { fontSize: '11px', color: a.text_color || '#fff', opacity: .7 } }, excerpt.substring(0, 60))
								);
							})
							: el('p', { style: { fontSize: '12px', color: '#fff', opacity: .6 } },
								loadingState[0] ? 'Caricamento articoli…' : 'Nessun articolo trovato per questa sorgente.')
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/prenotazione — selezione + form CF7
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/prenotazione') {
			var useState3 = element.useState;

			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'tickets-alt',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					var formsState = useState3({ uscite: [], eventi: [], corsi: [] });
					var forms = formsState[0];
					var setForms = formsState[1];

					element.useEffect(function () {
						if (!window.wp.apiFetch) return;
						['uscite', 'eventi', 'corsi'].forEach(function (tipo) {
							window.wp.apiFetch({ path: '/calypso/v1/cf7-forms?category=' + tipo }).then(function (list) {
								setForms(function (prev) {
									var next = Object.assign({}, prev);
									next[tipo] = list;
									return next;
								});
							}).catch(function () {});
						});
					}, []);

					function formSelect(tipo, enableKey, attrKey) {
						var options = [{ value: 0, label: '— nessuno —' }].concat(
							(forms[tipo] || []).map(function (f) { return { value: f.id, label: f.title }; })
						);
						return el(Fragment, {},
							el(ToggleControl, {
								label: 'Abilita ' + tipo,
								checked: a[enableKey] !== false,
								onChange: function (v) { var u = {}; u[enableKey] = v; set(u); }
							}),
							a[enableKey] !== false ? (SelectControl ? el(SelectControl, {
								label: 'Form CF7 — ' + tipo,
								value: a[attrKey] || 0,
								options: options,
								onChange: function (v) { var u = {}; u[attrKey] = parseInt(v, 10); set(u); }
							}) : null) : null,
							(a[enableKey] !== false && (forms[tipo] || []).length === 0) ? el('p', { style: { fontSize: '11px', color: '#b32d2e' } },
								'Nessun form CF7 categorizzato come "' + tipo + '". Categorizzalo nel tab "Calypso" dell\'editor del form CF7.'
							) : null
						);
					}

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

					var fwOpts = [
						{ value: '300', label: 'Light (300)' },
						{ value: '400', label: 'Regular (400)' },
						{ value: '500', label: 'Medium (500)' },
						{ value: '600', label: 'SemiBold (600)' },
						{ value: '700', label: 'Bold (700)' },
						{ value: '800', label: 'ExtraBold (800)' },
						{ value: '900', label: 'Black (900)' }
					];
					function weightRow(label, key, def) {
						return SelectControl ? el(SelectControl, {
							label: label,
							value: String(a[key] || def),
							options: fwOpts,
							onChange: function (v) { var u = {}; u[key] = parseInt(v, 10); set(u); }
						}) : null;
					}
					function subHead(text) {
						return el('p', { style: { fontSize: '10px', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.1em', color: '#757575', margin: '12px 0 6px', borderBottom: '1px solid #e0e0e0', paddingBottom: '5px' } }, text);
					}

					var controls = InspectorControls ? el(InspectorControls, {},
						el(PanelBody, { title: 'Uscite', initialOpen: true }, formSelect('uscite', 'enable_uscite', 'cf7_form_uscite')),
						el(PanelBody, { title: 'Eventi', initialOpen: false }, formSelect('eventi', 'enable_eventi', 'cf7_form_eventi')),
						el(PanelBody, { title: 'Corsi', initialOpen: false }, formSelect('corsi', 'enable_corsi', 'cf7_form_corsi')),

						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							subHead('Accesso richiesto (per tipo)'),
							el(ToggleControl, {
								label: 'Uscite — richiedi login',
								help: 'Se attivo, chi non è loggato vede un messaggio con link al login.',
								checked: a.require_login_uscite !== false,
								onChange: function (v) { set({ require_login_uscite: v }); }
							}),
							el(ToggleControl, {
								label: 'Eventi — richiedi login',
								help: 'Se attivo, chi non è loggato vede un messaggio con link al login.',
								checked: a.require_login_eventi !== false,
								onChange: function (v) { set({ require_login_eventi: v }); }
							}),
							el(ToggleControl, {
								label: 'Corsi — richiedi login',
								help: 'Disattivato di default: la pre-iscrizione ai corsi è aperta a tutti.',
								checked: !!a.require_login_corsi,
								onChange: function (v) { set({ require_login_corsi: v }); }
							}),
							el(TextControl, {
								label: 'Messaggio per utenti non loggati',
								value: a.login_message || 'Per prenotarti devi aver effettuato il login.',
								onChange: function (v) { set({ login_message: v }); }
							}),
							el(RangeControl, {
								label: 'Elementi totali per tab (cap query)',
								help: 'Massimo elementi caricati per tab, prima dei filtri/paginazione.',
								value: a.max_items_per_tab || 60,
								min: 1, max: 200, step: 1,
								onChange: function (v) { set({ max_items_per_tab: v || 60 }); }
							}),
							el(RangeControl, {
								label: 'Card per pagina',
								help: 'Quante card mostrare per pagina (paginazione client-side).',
								value: a.cards_per_page || 12,
								min: 2, max: 48, step: 1,
								onChange: function (v) { set({ cards_per_page: v || 12 }); }
							}),
							el(RangeControl, {
								label: 'Colonne card (desktop)',
								value: a.card_columns || 4,
								min: 2, max: 5, step: 1,
								onChange: function (v) { set({ card_columns: v || 4 }); }
							}),
							el(RangeControl, {
								label: 'Larghezza massima (px)',
								value: a.max_width || 1320,
								min: 400, max: 1920, step: 20,
								onChange: function (v) { set({ max_width: v || 1320 }); }
							})
						),

						el(PanelBody, { title: 'Sezione selezione — sfondo', initialOpen: false },
							colorRow('Colore sfondo', 'select_bg_color'),
							el(RangeControl, {
								label: 'Padding verticale (px)',
								value: a.select_padding_y !== undefined ? a.select_padding_y : 40,
								min: 0, max: 120, step: 4,
								onChange: function (v) { set({ select_padding_y: v === undefined ? 40 : v }); }
							})
						),

						el(PanelBody, { title: 'Selettore tab', initialOpen: false },
							colorRow('Sfondo barra (track)', 'tabs_track_bg_color'),
							colorRow('Testo tab inattivo', 'tab_text_color'),
							colorRow('Sfondo tab attivo', 'tab_active_bg_color'),
							colorRow('Testo tab attivo', 'tab_active_text_color'),
							colorRow('Sfondo contatore inattivo', 'tab_count_bg_color'),
							colorRow('Testo contatore inattivo', 'tab_count_text_color'),
							colorRow('Sfondo contatore attivo', 'tab_count_active_bg_color'),
							colorRow('Testo contatore attivo', 'tab_count_active_text_color'),
							el(RangeControl, {
								label: 'Dimensione testo (px)',
								value: a.tab_font_size || 14,
								min: 10, max: 20, step: 1,
								onChange: function (v) { set({ tab_font_size: v || 14 }); }
							}),
							weightRow('Peso font', 'tab_font_weight', 600)
						),

						el(PanelBody, { title: 'Card evento', initialOpen: false },
							subHead('Card'),
							colorRow('Sfondo card', 'card_bg_color'),
							colorRow('Sfondo immagine (segnaposto)', 'card_img_bg_color'),
							el(RangeControl, {
								label: 'Altezza immagine (px)',
								value: a.card_media_height !== undefined ? a.card_media_height : 220,
								min: 120, max: 400, step: 10,
								onChange: function (v) { set({ card_media_height: v === undefined ? 220 : v }); }
							}),
							el(RangeControl, {
								label: 'Raggio bordo card (px)',
								value: a.card_radius !== undefined ? a.card_radius : 16,
								min: 0, max: 40, step: 1,
								onChange: function (v) { set({ card_radius: v === undefined ? 16 : v }); }
							}),
							subHead('Stato selezionata'),
							colorRow('Bordo card selezionata', 'card_selected_border_color'),
							colorRow('Sfondo badge "Selezionata"', 'card_selected_badge_bg_color'),
							colorRow('Testo badge "Selezionata"', 'card_selected_badge_text_color'),
							subHead('Badge data (sull\'immagine)'),
							colorRow('Sfondo', 'card_date_bg_color'),
							colorRow('Colore numero giorno', 'card_date_num_color'),
							colorRow('Colore mese/giorno settimana', 'card_date_label_color'),
							subHead('Overlay titolo (sull\'immagine)'),
							el(TextControl, {
								label: 'Sfondo overlay (CSS — rgba OK)',
								value: a.card_media_title_bg_color || 'rgba(10,37,64,.6)',
								onChange: function (v) { set({ card_media_title_bg_color: v }); }
							}),
							colorRow('Colore testo titolo overlay', 'card_media_title_color'),
							subHead('Badge tipologia (Uscita/Evento/Corso)'),
							colorRow('Sfondo', 'card_type_badge_bg_color'),
							colorRow('Testo', 'card_type_badge_text_color'),
							subHead('Titolo card'),
							colorRow('Colore', 'card_title_color'),
							el(RangeControl, {
								label: 'Dimensione (px)',
								value: a.card_title_size || 18,
								min: 10, max: 28, step: 1,
								onChange: function (v) { set({ card_title_size: v || 18 }); }
							}),
							weightRow('Peso font', 'card_title_font_weight', 700),
							subHead('Meta (luogo · dettagli)'),
							colorRow('Colore testo', 'card_meta_text_color'),
							subHead('Footer (livello + posti)'),
							colorRow('Colore separatore', 'card_divider_color'),
							colorRow('Colore testo livello', 'card_level_text_color'),
							colorRow('Colore testo posti', 'card_spots_text_color'),
							colorRow('Colore testo posti — allerta (pochi posti)', 'card_spots_warn_color')
						),

						el(PanelBody, { title: 'Filtri (mese/luogo/livello)', initialOpen: false },
							colorRow('Bordo opzione selezionata', 'filter_active_border_color'),
							colorRow('Sfondo opzione selezionata', 'filter_active_bg_color'),
							colorRow('Testo opzione selezionata', 'filter_active_text_color')
						),

						el(PanelBody, { title: 'Sezione dati — sfondo', initialOpen: false },
							colorRow('Colore sfondo', 'data_bg_color'),
							el(RangeControl, {
								label: 'Padding verticale (px)',
								value: a.data_padding_y !== undefined ? a.data_padding_y : 48,
								min: 0, max: 140, step: 4,
								onChange: function (v) { set({ data_padding_y: v === undefined ? 48 : v }); }
							})
						),

						el(PanelBody, { title: 'Form prenotazione', initialOpen: false },
							colorRow('Sfondo card form', 'form_bg_color'),
							colorRow('Colore titolo form', 'form_title_color'),
							el(RangeControl, {
								label: 'Dimensione titolo form (px)',
								value: a.form_title_size || 20,
								min: 14, max: 36, step: 1,
								onChange: function (v) { set({ form_title_size: v || 20 }); }
							}),
							weightRow('Peso titolo form', 'form_title_font_weight', 800),
							el(RangeControl, {
								label: 'Raggio bordo form (px)',
								value: a.form_radius !== undefined ? a.form_radius : 18,
								min: 0, max: 40, step: 1,
								onChange: function (v) { set({ form_radius: v === undefined ? 18 : v }); }
							})
						),

						el(PanelBody, { title: 'Sidebar riepilogo', initialOpen: false },
							colorRow('Sfondo sidebar', 'sidebar_bg_color'),
							colorRow('Colore testo sidebar (default)', 'sidebar_text_color'),
							colorRow('Sfondo badge (tipologia)', 'side_badge_bg_color'),
							el(RangeControl, {
								label: 'Raggio bordo sidebar (px)',
								value: a.sidebar_radius !== undefined ? a.sidebar_radius : 18,
								min: 0, max: 40, step: 1,
								onChange: function (v) { set({ sidebar_radius: v === undefined ? 18 : v }); }
							}),
							subHead('Titolo (nome elemento)'),
							colorRow('Colore titolo', 'side_title_color'),
							el(RangeControl, {
								label: 'Dimensione titolo (px)',
								value: a.side_title_size || 18,
								min: 12, max: 32, step: 1,
								onChange: function (v) { set({ side_title_size: v || 18 }); }
							}),
							weightRow('Peso titolo', 'side_title_font_weight', 800),
							subHead('Testi'),
							colorRow('Colore luogo (sotto il titolo)', 'side_luogo_color'),
							colorRow('Colore etichette (Data, Posti, ecc.)', 'side_label_color'),
							colorRow('Colore valori', 'side_value_color')
						)
					) : null;

					var enabledCount = ['uscite', 'eventi', 'corsi'].filter(function (t) { return a['enable_' + t] !== false; }).length;
					var preview = el('div', {
						style: { padding: '20px', background: '#e8f4f8', borderRadius: '8px', fontFamily: 'monospace', fontSize: '12px' }
					}, '🎫 Prenotazione · ' + enabledCount + ' tipologie abilitate');

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/docenti-carousel — carosello docenti
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/docenti-carousel') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'groups',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}
					function rangeRow(label, key, def, min, max, step) {
						return el(RangeControl, { label: label, value: a[key] !== undefined ? a[key] : def, min: min, max: max, step: step || 1, onChange: function (v) { var u = {}; u[key] = v === undefined ? def : v; set(u); } });
					}
					function textRow(label, key) {
						return el(TextControl, { label: label, value: a[key] !== undefined ? String(a[key]) : '', onChange: function (v) { var u = {}; u[key] = v; set(u); } });
					}

					/* ── Anteprima statica ── */
					var previewCards = ['Marco Bianchi', 'Elena Conti', 'Luca Pieri'].map(function (name, idx) {
						return el('div', {
							key: idx,
							style: {
								flex: '0 0 calc(' + (100 / (a.desktop_cols || 3)) + '% - ' + ((a.gap || 24) * ((a.desktop_cols || 3) - 1) / (a.desktop_cols || 3)) + 'px)',
								background: a.card_bg || '#fff',
								borderRadius: (a.card_radius || 16) + 'px',
								overflow: 'hidden',
								boxShadow: '0 4px 16px -8px rgba(10,37,64,.18)',
								fontSize: '13px',
							}
						},
							el('div', { style: { background: '#bcc8d2', paddingTop: '100%', position: 'relative' } },
								el('span', { style: {
									position: 'absolute', bottom: 0, left: 0,
									background: a.photo_label_bg || 'rgba(10,37,64,.72)',
									color: a.photo_label_color || '#fff',
									fontSize: (a.photo_label_size || 9) + 'px',
									fontWeight: 700, letterSpacing: '.14em', textTransform: 'uppercase',
									padding: '10px 14px', borderTopRightRadius: '8px',
								} }, (a.photo_label_prefix || 'RITRATTO') + ' · ' + name.toUpperCase())
							),
							el('div', { style: { padding: (a.card_body_py || 18) + 'px ' + (a.card_body_px || 20) + 'px' } },
								el('p', { style: {
									margin: 0, fontSize: (a.name_size || 22) + 'px', fontWeight: a.name_weight || 800,
									color: a.name_color || '#1B77A7', lineHeight: 1.1,
									textTransform: a.name_upper !== false ? 'uppercase' : 'none',
								} }, name),
								a.show_soprannome !== false ? el('p', { style: { margin: '4px 0 0', fontSize: (a.sopr_size || 15) + 'px', color: a.sopr_color || 'rgba(10,37,64,.6)', fontStyle: 'italic' } }, 'detto "Oceano"') : null,
								a.show_ruolo !== false ? el('p', { style: { margin: '4px 0 0', fontSize: (a.ruolo_size || 15) + 'px', fontWeight: a.ruolo_weight || 600, color: a.ruolo_color || '#b9790a' } }, 'Istruttore') : null,
								a.show_stats !== false ? el('ul', { style: { margin: '10px 0 0', padding: 0, listStyle: 'none', fontSize: (a.stats_size || 12) + 'px', color: a.stats_color || 'rgba(10,37,64,.55)', display: 'flex', flexWrap: 'wrap' } },
									el('li', {}, (a.stat_anno_label || 'dal') + ' 2008'),
									el('li', { style: { paddingLeft: '6px' } }, ' · AIDA Master')
								) : null
							)
						);
					});

					var preview = el('div', { style: { padding: '16px', background: a.bg_color || '#f5f5f5', borderRadius: '4px', position: 'relative' } },
						el('div', { style: { display: 'flex', gap: (a.gap || 24) + 'px', overflow: 'hidden' } }, previewCards),
						el('div', { style: { display: 'flex', justifyContent: 'space-between', marginTop: '12px' } },
							el('span', { style: { background: a.arrow_bg || '#fff', color: a.arrow_color || '#0a2540', borderRadius: (a.arrow_radius || 999) + 'px', width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center', border: '1px solid rgba(10,37,64,.15)', fontSize: '18px', cursor: 'default' } }, '←'),
							el('span', { style: { background: a.arrow_bg || '#fff', color: a.arrow_color || '#0a2540', borderRadius: (a.arrow_radius || 999) + 'px', width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center', border: '1px solid rgba(10,37,64,.15)', fontSize: '18px', cursor: 'default' } }, '→')
						)
					);

					var controls = InspectorControls ? el(InspectorControls, {},

						el(PanelBody, { title: 'Sorgente', initialOpen: true },
							el(SelectControl, { label: 'Modalità', value: a.source_mode || 'all',
								options: [{ value: 'all', label: 'Tutti i docenti' }, { value: 'manual', label: 'Selezione manuale (IDs)' }],
								onChange: function (v) { set({ source_mode: v }); }
							}),
							a.source_mode === 'manual' ? textRow('ID docenti separati da virgola (nell\'ordine desiderato)', 'manual_ids_str') : null,
							a.source_mode !== 'manual' ? el(SelectControl, { label: 'Ordinamento base', value: a.order || 'title',
								options: [
									{ value: 'title',      label: 'Titolo A→Z' },
									{ value: 'menu_order', label: 'Ordine menu (drag WP)' },
									{ value: 'date',       label: 'Data aggiunta (recente)' },
									{ value: 'rand',       label: 'Casuale' },
								],
								onChange: function (v) { set({ order: v }); }
							}) : null,
							a.source_mode !== 'manual' ? el('div', {},
								el('p', { style: { fontSize: '11px', fontWeight: 600, margin: '12px 0 4px', color: '#1e1e1e' } }, 'IDs priorità (vengono prima, in ordine)'),
								el('p', { style: { fontSize: '11px', color: '#666', margin: '0 0 6px' } }, 'Inserisci gli ID separati da virgola. Es: 42,17,8'),
								el(TextControl, {
									label: '',
									value: (a.priority_ids || []).join(','),
									onChange: function (v) {
										var ids = v.split(',').map(function(x){ return parseInt(x.trim(),10); }).filter(function(n){ return !isNaN(n) && n > 0; });
										set({ priority_ids: ids });
									}
								})
							) : null,
							rangeRow('Max docenti (0 = tutti)', 'max_items', 0, 0, 50, 1)
						),

						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							el(ToggleControl, { label: 'Card cliccabile', checked: a.show_link !== false, onChange: function (v) { set({ show_link: v }); } }),
							a.show_link !== false ? el(SelectControl, { label: 'Apri link in', value: a.link_target || '_self',
								options: [{ value: '_self', label: 'Stessa finestra' }, { value: '_blank', label: 'Nuova scheda' }],
								onChange: function (v) { set({ link_target: v }); }
							}) : null,
							el(ToggleControl, { label: 'Label foto (RITRATTO · NOME)', checked: a.show_photo_label !== false, onChange: function (v) { set({ show_photo_label: v }); } }),
							a.show_photo_label !== false ? textRow('Prefisso label foto', 'photo_label_prefix') : null,
							el(ToggleControl, { label: 'Mostra soprannome (detto "...")', checked: a.show_soprannome !== false, onChange: function (v) { set({ show_soprannome: v }); } }),
							el(ToggleControl, { label: 'Mostra ruolo', checked: a.show_ruolo !== false, onChange: function (v) { set({ show_ruolo: v }); } }),
							el(ToggleControl, { label: 'Mostra bio', checked: !!a.show_bio, onChange: function (v) { set({ show_bio: v }); } }),
							a.show_bio ? rangeRow('Righe bio (line-clamp)', 'bio_lines', 2, 1, 6, 1) : null,
							el(ToggleControl, { label: 'Mostra stats (anno · brevetti)', checked: a.show_stats !== false, onChange: function (v) { set({ show_stats: v }); } }),
							a.show_stats !== false ? el(ToggleControl, { label: '  · Anno inizio', checked: a.show_stat_anno !== false, onChange: function (v) { set({ show_stat_anno: v }); } }) : null,
							a.show_stats !== false && a.show_stat_anno !== false ? textRow('  Etichetta anno (es. "dal")', 'stat_anno_label') : null,
							a.show_stats !== false ? el(ToggleControl, { label: '  · Brevetti', checked: a.show_stat_brevetti !== false, onChange: function (v) { set({ show_stat_brevetti: v }); } }) : null,
							a.show_stats !== false ? el(ToggleControl, { label: '  · Specializzazioni', checked: !!a.show_stat_spec, onChange: function (v) { set({ show_stat_spec: v }); } }) : null
						),

						el(PanelBody, { title: 'Layout carosello', initialOpen: false },
							rangeRow('Card visibili — desktop', 'desktop_cols', 3, 1, 6, 1),
							rangeRow('Card visibili — tablet (≤1024px)', 'tablet_cols', 2, 1, 4, 1),
							rangeRow('Card visibili — mobile (≤640px)', 'mobile_cols', 1, 1, 3, 1),
							rangeRow('Gap tra card (px)', 'gap', 24, 0, 80, 4),
							rangeRow('Peek card successiva (px)', 'peek', 40, 0, 120, 8)
						),

						el(PanelBody, { title: 'Frecce navigazione', initialOpen: false },
							el(ToggleControl, { label: 'Mostra frecce', checked: a.arrows_show !== false, onChange: function (v) { set({ arrows_show: v }); } }),
							a.arrows_show !== false ? el(SelectControl, { label: 'Posizione frecce', value: a.arrows_position || 'sides',
								options: [
									{ value: 'sides',   label: 'Lati (floating)' },
									{ value: 'inside',  label: 'Dentro la viewport' },
									{ value: 'outside', label: 'Fuori la viewport' },
								],
								onChange: function (v) { set({ arrows_position: v }); }
							}) : null,
							a.arrows_show !== false ? colorRow('Sfondo freccia', 'arrow_bg') : null,
							a.arrows_show !== false ? colorRow('Icona freccia', 'arrow_color') : null,
							a.arrows_show !== false ? colorRow('Colore bordo freccia', 'arrow_border') : null,
							a.arrows_show !== false ? colorRow('Sfondo freccia hover', 'arrow_hover_bg') : null,
							a.arrows_show !== false ? colorRow('Icona freccia hover', 'arrow_hover_col') : null,
							a.arrows_show !== false ? rangeRow('Dimensione freccia (px)', 'arrow_size', 44, 28, 80, 2) : null,
							a.arrows_show !== false ? rangeRow('Border radius freccia (px)', 'arrow_radius', 999, 0, 999, 4) : null,
							a.arrows_show !== false ? el(TextControl, { label: 'Box-shadow freccia (CSS)', value: a.arrow_shadow || '0 2px 12px -4px rgba(10,37,64,.25)', onChange: function (v) { set({ arrow_shadow: v }); } }) : null
						),

						el(PanelBody, { title: 'Layout sezione', initialOpen: false },
							colorRow('Sfondo sezione', 'bg_color'),
							rangeRow('Larghezza massima (px)', 'max_width', 1320, 400, 1920, 20),
							rangeRow('Padding verticale (px)', 'padding_y', 48, 0, 200, 4),
							rangeRow('Padding orizzontale (px)', 'padding_x', 24, 0, 120, 4),
							rangeRow('Margine superiore (px)',  'margin_top',    0, 0, 200, 4),
							rangeRow('Margine inferiore (px)',  'margin_bottom', 0, 0, 200, 4),
							rangeRow('Margine sinistro (px)',   'margin_left',   0, 0, 200, 4),
							rangeRow('Margine destro (px)',     'margin_right',  0, 0, 200, 4)
						),

						el(PanelBody, { title: 'Stile card', initialOpen: false },
							colorRow('Sfondo card', 'card_bg'),
							rangeRow('Border radius card (px)', 'card_radius', 16, 0, 40, 2),
							el(TextControl, { label: 'Box-shadow card (CSS)', value: a.card_shadow || '0 8px 28px -10px rgba(10,37,64,.18)', onChange: function (v) { set({ card_shadow: v }); } }),
							colorRow('Colore bordo card', 'card_border'),
							rangeRow('Padding body orizzontale (px)', 'card_body_px', 20, 8, 60, 2),
							rangeRow('Padding body verticale (px)',   'card_body_py', 18, 8, 60, 2)
						),

						el(PanelBody, { title: 'Stile foto', initialOpen: false },
							el(SelectControl, { label: 'Aspect ratio foto', value: a.photo_ratio || '3/4',
								options: [
									{ value: '1/1',  label: 'Quadrato 1:1' },
									{ value: '4/3',  label: 'Orizzontale 4:3' },
									{ value: '3/4',  label: 'Verticale 3:4' },
									{ value: '2/3',  label: 'Verticale 2:3' },
									{ value: '9/16', label: 'Verticale 9:16' },
								],
								onChange: function (v) { set({ photo_ratio: v }); }
							}),
							colorRow('Sfondo label foto', 'photo_label_bg'),
							colorRow('Colore testo label foto', 'photo_label_color'),
							rangeRow('Font size label foto (px)', 'photo_label_size', 9, 7, 16, 1)
						),

						el(PanelBody, { title: 'Font testi card', initialOpen: false },
							el('p', { style: { fontSize: '11px', color: '#666', margin: '0 0 8px' } }, 'Lascia vuoto per ereditare il font del tema. Esempi: "Georgia, serif", "var(--f-body)".'),
							textRow('Font nome (font-family CSS)', 'name_font'),
							textRow('Font body — ruolo, bio, stats (font-family CSS)', 'body_font')
						),

						el(PanelBody, { title: 'Stile nome', initialOpen: false },
							colorRow('Colore nome', 'name_color'),
							rangeRow('Font size nome (px)', 'name_size', 22, 12, 48, 1),
							rangeRow('Font weight nome', 'name_weight', 800, 300, 900, 100),
							el(ToggleControl, { label: 'Nome maiuscolo', checked: a.name_upper !== false, onChange: function (v) { set({ name_upper: v }); } }),
							el(ToggleControl, { label: 'Corsivo', checked: !!a.name_italic, onChange: function (v) { set({ name_italic: v }); } }),
							el(SelectControl, { label: 'Decorazione testo', value: a.name_decoration || 'none',
								options: [
									{ value: 'none',         label: 'Nessuna' },
									{ value: 'underline',    label: 'Sottolineato' },
									{ value: 'line-through', label: 'Barrato' },
									{ value: 'overline',     label: 'Sopralineato' },
								],
								onChange: function (v) { set({ name_decoration: v }); }
							})
						),

						el(PanelBody, { title: 'Stile soprannome', initialOpen: false },
							colorRow('Colore soprannome', 'sopr_color'),
							rangeRow('Font size soprannome (px)', 'sopr_size', 15, 10, 28, 1),
							el(SelectControl, { label: 'Decorazione testo', value: a.sopr_decoration || 'none',
								options: [
									{ value: 'none',         label: 'Nessuna' },
									{ value: 'underline',    label: 'Sottolineato' },
									{ value: 'line-through', label: 'Barrato' },
								],
								onChange: function (v) { set({ sopr_decoration: v }); }
							})
						),

						el(PanelBody, { title: 'Stile ruolo', initialOpen: false },
							colorRow('Colore ruolo', 'ruolo_color'),
							rangeRow('Font size ruolo (px)', 'ruolo_size', 15, 10, 28, 1),
							rangeRow('Font weight ruolo', 'ruolo_weight', 600, 300, 900, 100),
							el(ToggleControl, { label: 'Corsivo', checked: !!a.ruolo_italic, onChange: function (v) { set({ ruolo_italic: v }); } }),
							el(SelectControl, { label: 'Decorazione testo', value: a.ruolo_decoration || 'none',
								options: [
									{ value: 'none',         label: 'Nessuna' },
									{ value: 'underline',    label: 'Sottolineato' },
									{ value: 'line-through', label: 'Barrato' },
								],
								onChange: function (v) { set({ ruolo_decoration: v }); }
							})
						),

						el(PanelBody, { title: 'Stile bio', initialOpen: false },
							colorRow('Colore bio', 'bio_color'),
							rangeRow('Font size bio (px)', 'bio_size', 14, 10, 22, 1),
							el(ToggleControl, { label: 'Corsivo', checked: !!a.bio_italic, onChange: function (v) { set({ bio_italic: v }); } })
						),

						el(PanelBody, { title: 'Stile stats', initialOpen: false },
							colorRow('Colore stats', 'stats_color'),
							rangeRow('Font size stats (px)', 'stats_size', 12, 9, 20, 1),
							colorRow('Colore separatore ·', 'stats_sep_color')
						),

						el(PanelBody, { title: 'Pallini paginazione', initialOpen: false },
							el(ToggleControl, { label: 'Mostra pallini', checked: a.dots_show !== false, onChange: function (v) { set({ dots_show: v }); } }),
							a.dots_show !== false ? colorRow('Colore pallino inattivo', 'dots_color') : null,
							a.dots_show !== false ? colorRow('Colore pallino attivo', 'dots_active_color') : null,
							a.dots_show !== false ? rangeRow('Dimensione pallino (px)', 'dots_size', 7, 4, 20, 1) : null
						),

						el(PanelBody, { title: 'Autoplay (scorrimento automatico)', initialOpen: false },
							el(ToggleControl, {
								label: 'Autoplay attivo',
								help: 'Il carosello scorre da solo. Si ferma quando il mouse è sopra.',
								checked: !!a.autoplay,
								onChange: function (v) { set({ autoplay: v }); }
							}),
							a.autoplay ? rangeRow('Velocità (millisecondi tra slide)', 'autoplay_speed', 3000, 500, 10000, 500) : null
						)

					) : null;

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/corsi-strip — lista corsi a righe
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/corsi-strip') {
			var useStateCS  = element.useState;
			var useEffectCS = element.useEffect;

			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'excerpt-view',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					var csAllState    = useStateCS([]);
					var csAll         = csAllState[0]; var setCsAll = csAllState[1];
					var csFilterState = useStateCS('');
					var csFilter      = csFilterState[0]; var setCsFilter = csFilterState[1];

					useEffectCS(function () {
						if (a.source_mode !== 'manual') return;
						if (csAll.length) return;
						window.wp.apiFetch({ path: '/wp/v2/calypso_corso?per_page=100&_fields=id,title&orderby=title&order=asc' })
							.then(function (posts) {
								setCsAll(posts.map(function (p) { return { id: p.id, title: p.title.rendered }; }));
							}).catch(function () {});
					}, [a.source_mode]);

					var csManualPicker = el('div', {},
						csAll.length === 0
							? el('p', { style: { fontSize: '12px', color: '#888', margin: '6px 0' } }, 'Caricamento corsi...')
							: el('div', {},
								el('input', {
									type: 'search',
									placeholder: 'Filtra corsi…',
									value: csFilter,
									onInput: function (e) { setCsFilter(e.target.value); },
									style: { width: '100%', padding: '6px 10px', marginBottom: '6px', border: '1px solid #ddd', borderRadius: '4px', fontSize: '12px', boxSizing: 'border-box' }
								}),
								el('div', { style: { maxHeight: '220px', overflowY: 'auto', border: '1px solid #e0e0e0', borderRadius: '4px' } },
									csAll
										.filter(function (c) { return !csFilter || c.title.toLowerCase().indexOf(csFilter.toLowerCase()) !== -1; })
										.map(function (c) {
											var sel = (a.manual_ids || []).indexOf(c.id) !== -1;
											var pos = sel ? ((a.manual_ids || []).indexOf(c.id) + 1) : null;
											return el('div', {
												key: c.id,
												onClick: function () {
													var cur = a.manual_ids || [];
													set({ manual_ids: sel ? cur.filter(function (id) { return id !== c.id; }) : cur.concat([c.id]) });
												},
												style: {
													padding: '7px 10px', cursor: 'pointer', fontSize: '12px',
													background: sel ? '#0a2540' : '#fff',
													color: sel ? '#fff' : '#1e1e1e',
													borderBottom: '1px solid #f0f0f0',
													display: 'flex', justifyContent: 'space-between', alignItems: 'center',
												}
											},
												el('span', {}, c.title),
												sel ? el('span', { style: { fontSize: '10px', opacity: .65 } }, '✓ ' + pos) : null
											);
										})
								),
								(a.manual_ids || []).length
									? el('p', { style: { fontSize: '11px', color: '#888', margin: '5px 0 0' } }, (a.manual_ids.length) + ' selezionati · clicca per rimuovere')
									: null
							)
					);

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

					function textRow(label, key) {
						return el(TextControl, {
							label: label,
							value: a[key] !== undefined ? a[key] : '',
							onChange: function (v) { var u = {}; u[key] = v; set(u); }
						});
					}

					function rangeRow(label, key, def, min, max, step) {
						return el(RangeControl, {
							label: label,
							value: a[key] !== undefined ? a[key] : def,
							min: min, max: max, step: step || 1,
							onChange: function (v) { var u = {}; u[key] = v === undefined ? def : v; set(u); }
						});
					}

					/* ── Anteprima statica ── */
					var previewRows = ['OPEN WATER DIVER', 'ADVANCED OPEN WATER', 'RESCUE DIVER'].map(function (title, idx) {
						var isFeat = a.featured_enabled && idx === (a.featured_index || 0);
						var rowBg      = isFeat ? (a.feat_bg || '#0a2540')      : (a.row_bg || '#fff');
						var rowColor   = isFeat ? (a.feat_text_color || '#fff') : (a.row_text_color || '#0a2540');
						var badgeBg    = isFeat ? (a.feat_badge_bg    || 'rgba(255,255,255,.15)') : (a.badge_bg || 'transparent');
						var badgeColor = isFeat ? (a.feat_badge_color || '#fff') : (a.badge_color || '#0a2540');
						var badgeBorder= isFeat ? (a.feat_badge_border|| 'rgba(255,255,255,.3)')  : (a.badge_border || 'rgba(10,37,64,.2)');
						var statsColor = isFeat ? (a.feat_stats_color || 'rgba(255,255,255,.7)') : (a.stats_color || 'rgba(10,37,64,.6)');
						var rlColor    = isFeat ? (a.feat_right_label_color || 'rgba(255,255,255,.55)') : (a.right_label_color || 'rgba(10,37,64,.5)');
						var rvColor    = isFeat ? (a.feat_right_value_color || '#fff') : (a.right_value_color || '#0a2540');
						return el('div', {
							key: idx,
							style: {
								display: 'flex', alignItems: 'center',
								gap: '16px',
								background: rowBg,
								color: rowColor,
								borderRadius: (a.row_radius || 16) + 'px',
								padding: '16px 22px',
								border: isFeat ? 'none' : ('1px solid ' + (a.row_border_color || 'rgba(10,37,64,.08)')),
								boxShadow: isFeat ? 'none' : (a.row_shadow || '0 4px 16px -8px rgba(10,37,64,.15)'),
							}
						},
							a.show_badge !== false ? el('span', {
								style: {
									flex: '0 0 auto', minWidth: (a.badge_min_width || 72) + 'px',
									display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
									padding: '5px 12px',
									borderRadius: (a.badge_radius || 999) + 'px',
									border: '1.5px solid ' + badgeBorder,
									background: badgeBg, color: badgeColor,
									fontSize: (a.badge_size || 12) + 'px',
									fontWeight: a.badge_weight || 600,
									whiteSpace: 'nowrap',
								}
							}, ['Base', 'Avanzato', 'Pro'][idx]) : null,
							el('div', { style: { flex: '1 1 0', minWidth: 0 } },
								el('p', { style: {
									margin: 0, padding: 0,
									fontSize: (a.title_size || 22) + 'px',
									fontWeight: a.title_weight || 800,
									lineHeight: 1.1,
									textTransform: a.title_upper !== false ? 'uppercase' : 'none',
									color: 'inherit',
								} }, title),
								a.show_stats !== false ? el('p', { style: {
									margin: '4px 0 0', fontSize: (a.stats_size || 14) + 'px',
									color: statsColor, lineHeight: 1.4,
								} }, '32 ore · 8 lezioni · brevetto FIAS') : null
							),
							a.show_right !== false ? el('div', { style: { flex: '0 0 auto', textAlign: 'right', minWidth: '70px' } },
								el('span', { style: {
									display: 'block', fontSize: (a.right_label_size || 10) + 'px',
									fontWeight: 700, letterSpacing: '.1em', textTransform: 'uppercase',
									color: rlColor, marginBottom: '2px',
								} }, a.right_label || 'DURATA'),
								el('span', { style: {
									display: 'block', fontSize: (a.right_value_size || 28) + 'px',
									fontWeight: a.right_value_weight || 800, lineHeight: 1,
									color: rvColor, letterSpacing: '-.02em',
								} }, [8, 5, 10][idx] + (a.right_unit ? ' ' + a.right_unit : ' SETT.'))
							) : null
						);
					});

					var preview = el('div', {
						style: {
							background: a.bg_color || '#f6f1e6',
							padding: '24px',
							borderRadius: '4px',
							display: 'flex', flexDirection: 'column',
							gap: (a.gap || 12) + 'px',
						}
					}, previewRows);

					var controls = InspectorControls ? el(InspectorControls, {},

						el(PanelBody, { title: 'Sorgente', initialOpen: true },
							el(SelectControl, {
								label: 'Modalità',
								value: a.source_mode || 'all',
								options: [
									{ value: 'all',     label: 'Tutti i corsi' },
									{ value: 'livello', label: 'Per livello' },
									{ value: 'manual',  label: 'Selezione manuale (IDs)' },
								],
								onChange: function (v) { set({ source_mode: v }); }
							}),
							(a.source_mode === 'manual') ? csManualPicker : null,
							(a.source_mode === 'livello') ? el(TextControl, {
								label: 'Slug livelli separati da virgola',
								value: (a.livello_slugs || []).join(','),
								onChange: function (v) {
									var slugs = v.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
									set({ livello_slugs: slugs });
								}
							}) : null,
							rangeRow('Max corsi (0 = tutti)', 'max_items', 0, 0, 30, 1),
							el(SelectControl, {
								label: 'Ordinamento',
								value: a.order || 'menu_order',
								options: [
									{ value: 'menu_order', label: 'Ordine menu' },
									{ value: 'title',      label: 'Titolo A→Z' },
									{ value: 'date',       label: 'Data recente' },
								],
								onChange: function (v) { set({ order: v }); }
							})
						),

						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							el(ToggleControl, {
								label: 'Riga cliccabile',
								checked: a.show_link !== false,
								onChange: function (v) { set({ show_link: v }); }
							}),
							a.show_link !== false ? el(SelectControl, {
								label: 'Apri link in',
								value: a.link_target || '_self',
								options: [
									{ value: '_self',  label: 'Stessa finestra' },
									{ value: '_blank', label: 'Nuova scheda' },
								],
								onChange: function (v) { set({ link_target: v }); }
							}) : null,
							el(ToggleControl, {
								label: 'Mostra badge livello',
								checked: a.show_badge !== false,
								onChange: function (v) { set({ show_badge: v }); }
							}),
							el(ToggleControl, {
								label: 'Mostra stats/sottotitolo',
								checked: a.show_stats !== false,
								onChange: function (v) { set({ show_stats: v }); }
							}),
							a.show_stats !== false ? el(ToggleControl, {
								label: '  · Durata (_corso_stat_durata)',
								checked: a.show_stat_durata !== false,
								onChange: function (v) { set({ show_stat_durata: v }); }
							}) : null,
							a.show_stats !== false ? el(ToggleControl, {
								label: '  · Pratica/Immersioni',
								checked: a.show_stat_pratica !== false,
								onChange: function (v) { set({ show_stat_pratica: v }); }
							}) : null,
							a.show_stats !== false ? el(ToggleControl, {
								label: '  · Profondità',
								checked: !!a.show_stat_profondita,
								onChange: function (v) { set({ show_stat_profondita: v }); }
							}) : null,
							a.show_stats !== false ? el(ToggleControl, {
								label: '  · Badge/brevetto (_corso_badge)',
								checked: !!a.show_stat_badge,
								onChange: function (v) { set({ show_stat_badge: v }); }
							}) : null,
							el(ToggleControl, {
								label: 'Mostra colonna destra',
								checked: a.show_right !== false,
								onChange: function (v) { set({ show_right: v }); }
							}),
							a.show_right !== false ? el(SelectControl, {
								label: 'Meta field colonna destra',
								value: a.right_meta_key || '_corso_stat_durata',
								options: [
									{ value: '_corso_stat_durata',     label: 'Durata (_corso_stat_durata)' },
									{ value: '_corso_stat_pratica',    label: 'Pratica (_corso_stat_pratica)' },
									{ value: '_corso_stat_profondita', label: 'Profondità (_corso_stat_profondita)' },
									{ value: '_corso_periodo',         label: 'Periodo (_corso_periodo)' },
									{ value: '_corso_badge',           label: 'Badge (_corso_badge)' },
								],
								onChange: function (v) { set({ right_meta_key: v }); }
							}) : null,
							a.show_right !== false ? textRow('Etichetta colonna destra', 'right_label') : null,
							a.show_right !== false ? textRow('Unità di misura (es. SETT.)', 'right_unit') : null
						),

						el(PanelBody, { title: 'Riga in evidenza (Featured)', initialOpen: false },
							el(ToggleControl, {
								label: 'Abilita riga in evidenza',
								checked: a.featured_enabled !== false,
								onChange: function (v) { set({ featured_enabled: v }); }
							}),
							a.featured_enabled !== false ? rangeRow('Indice riga evidenziata (0 = prima)', 'featured_index', 0, 0, 20, 1) : null,
							a.featured_enabled !== false ? colorRow('Sfondo featured', 'feat_bg') : null,
							a.featured_enabled !== false ? colorRow('Testo featured', 'feat_text_color') : null,
							a.featured_enabled !== false ? colorRow('Sfondo badge featured', 'feat_badge_bg') : null,
							a.featured_enabled !== false ? colorRow('Colore testo badge featured', 'feat_badge_color') : null,
							a.featured_enabled !== false ? colorRow('Colore bordo badge featured', 'feat_badge_border') : null,
							a.featured_enabled !== false ? colorRow('Colore stats featured', 'feat_stats_color') : null,
							a.featured_enabled !== false ? colorRow('Colore etichetta destra featured', 'feat_right_label_color') : null,
							a.featured_enabled !== false ? colorRow('Valore destra featured', 'feat_right_value_color') : null
						),

						el(PanelBody, { title: 'Layout sezione', initialOpen: false },
							colorRow('Sfondo sezione', 'bg_color'),
							rangeRow('Larghezza massima (px)', 'max_width', 900, 400, 1920, 20),
							rangeRow('Padding verticale (px)', 'padding_y', 48, 0, 200, 4),
							rangeRow('Padding orizzontale (px)', 'padding_x', 24, 0, 120, 4),
							rangeRow('Gap tra righe (px)', 'gap', 12, 0, 60, 2),
							rangeRow('Margine superiore (px)', 'margin_top', 0, 0, 200, 4),
							rangeRow('Margine inferiore (px)', 'margin_bottom', 0, 0, 200, 4),
							rangeRow('Margine sinistro (px)', 'margin_left', 0, 0, 200, 4),
							rangeRow('Margine destro (px)', 'margin_right', 0, 0, 200, 4)
						),

						el(PanelBody, { title: 'Stile righe', initialOpen: false },
							colorRow('Sfondo riga', 'row_bg'),
							colorRow('Colore testo riga', 'row_text_color'),
							colorRow('Colore bordo riga', 'row_border_color'),
							el(TextControl, {
								label: 'Box-shadow riga (CSS)',
								value: a.row_shadow || '0 4px 16px -8px rgba(10,37,64,.15)',
								onChange: function (v) { set({ row_shadow: v }); }
							}),
							rangeRow('Raggio bordi (px)', 'row_radius', 16, 0, 40, 2),
							rangeRow('Padding verticale riga (px)', 'row_padding_y', 20, 8, 60, 2),
							rangeRow('Padding orizzontale riga (px)', 'row_padding_x', 28, 8, 80, 4)
						),

						el(PanelBody, { title: 'Stile badge livello', initialOpen: false },
							colorRow('Sfondo badge', 'badge_bg'),
							colorRow('Testo badge', 'badge_color'),
							colorRow('Colore bordo badge', 'badge_border'),
							rangeRow('Font size badge (px)', 'badge_size', 12, 8, 20, 1),
							rangeRow('Font weight badge', 'badge_weight', 600, 300, 900, 100),
							rangeRow('Border radius badge (px)', 'badge_radius', 999, 0, 999, 4),
							rangeRow('Larghezza minima badge (px)', 'badge_min_width', 72, 40, 200, 4)
						),

						el(PanelBody, { title: 'Stile titolo', initialOpen: false },
							rangeRow('Font size titolo (px)', 'title_size', 22, 12, 60, 1),
							rangeRow('Font weight titolo', 'title_weight', 800, 300, 900, 100),
							el(ToggleControl, {
								label: 'Testo maiuscolo',
								checked: a.title_upper !== false,
								onChange: function (v) { set({ title_upper: v }); }
							})
						),

						el(PanelBody, { title: 'Stile stats/sottotitolo', initialOpen: false },
							rangeRow('Font size stats (px)', 'stats_size', 14, 10, 24, 1),
							colorRow('Colore stats', 'stats_color')
						),

						el(PanelBody, { title: 'Stile colonna destra', initialOpen: false },
							rangeRow('Font size etichetta (px)', 'right_label_size', 10, 8, 20, 1),
							colorRow('Colore etichetta', 'right_label_color'),
							rangeRow('Font size valore (px)', 'right_value_size', 28, 14, 60, 2),
							rangeRow('Font weight valore', 'right_value_weight', 800, 300, 900, 100),
							colorRow('Colore valore', 'right_value_color')
						)

					) : null;

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/lista-articoli — lista magazine con filtri
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/lista-articoli') {
			var useStateLA  = element.useState;
			var useEffectLA = element.useEffect;

			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'list-view',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					var catsState  = useStateLA([]);
					var cats       = catsState[0];
					var setCats    = catsState[1];
					var tagsState  = useStateLA([]);
					var tagsArr    = tagsState[0];
					var setTagsArr = tagsState[1];

					useEffectLA(function () {
						if (!window.wp || !window.wp.apiFetch) return;
						window.wp.apiFetch({ path: '/wp/v2/categories?per_page=100&hide_empty=false' })
							.then(function (res) { setCats(res.map(function (c) { return { value: c.id, label: c.name }; })); })
							.catch(function () {});
						window.wp.apiFetch({ path: '/wp/v2/tags?per_page=100&hide_empty=false' })
							.then(function (res) { setTagsArr(res.map(function (t) { return { value: t.id, label: t.name }; })); })
							.catch(function () {});
					}, []);

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}
					function rangeRow(label, key, def, min, max, step) {
						return el(RangeControl, {
							label: label,
							value: a[key] !== undefined ? a[key] : def,
							min: min, max: max, step: step || 1,
							onChange: function (v) { var u = {}; u[key] = v === undefined ? def : v; set(u); }
						});
					}

					function multiCheck(items, selectedIds, onChange) {
						return el('div', { style: { maxHeight: '180px', overflowY: 'auto', border: '1px solid #e0e0e0', borderRadius: '4px', padding: '6px 8px' } },
							items.length === 0
								? el('p', { style: { fontSize: '12px', color: '#999', margin: 0 } }, 'Caricamento…')
								: items.map(function (item) {
									var checked = selectedIds.indexOf(item.value) !== -1;
									return el('label', {
										key: item.value,
										style: { display: 'flex', alignItems: 'center', gap: '6px', padding: '3px 0', fontSize: '12px', cursor: 'pointer' }
									},
										el('input', {
											type: 'checkbox',
											checked: checked,
											onChange: function () {
												var next = checked
													? selectedIds.filter(function (id) { return id !== item.value; })
													: selectedIds.concat([item.value]);
												onChange(next);
											}
										}),
										item.label
									);
								})
						);
					}

					var showCats = a.source_mode === 'category' || a.source_mode === 'category_tag';
					var showTags = a.source_mode === 'tag'      || a.source_mode === 'category_tag';

					var controls = InspectorControls ? el(InspectorControls, {},

						el(PanelBody, { title: 'Sorgente', initialOpen: true },
							SelectControl ? el(SelectControl, {
								label: 'Modalità',
								value: a.source_mode || 'all',
								options: [
									{ value: 'all',          label: 'Tutti gli articoli' },
									{ value: 'category',     label: 'Per categoria' },
									{ value: 'tag',          label: 'Per tag' },
									{ value: 'category_tag', label: 'Categoria + Tag' },
									{ value: 'manual',       label: 'Selezione manuale (ID)' }
								],
								onChange: function (v) { set({ source_mode: v }); }
							}) : null,
							showCats ? el('div', { style: { marginTop: '8px' } },
								el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, 'Categorie'),
								multiCheck(cats, a.category_ids || [], function (ids) { set({ category_ids: ids }); })
							) : null,
							showTags ? el('div', { style: { marginTop: '8px' } },
								el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, 'Tag'),
								multiCheck(tagsArr, a.tag_ids || [], function (ids) { set({ tag_ids: ids }); })
							) : null,
							a.source_mode === 'manual' ? el(TextareaControl, {
								label: 'ID articoli (separati da virgola)',
								help: 'Es: 12, 45, 78',
								value: (a.manual_ids || []).join(', '),
								rows: 2,
								onChange: function (v) {
									var ids = v.split(',').map(function (s) { return parseInt(s.trim(), 10); }).filter(function (n) { return !isNaN(n) && n > 0; });
									set({ manual_ids: ids });
								}
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Ordina per',
								value: a.order_by || 'date',
								options: [
									{ value: 'date',       label: 'Data pubblicazione' },
									{ value: 'title',      label: 'Titolo (A-Z)' },
									{ value: 'modified',   label: 'Data modifica' },
									{ value: 'rand',       label: 'Casuale' },
									{ value: 'menu_order', label: 'Ordine manuale' }
								],
								onChange: function (v) { set({ order_by: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Direzione',
								value: a.order || 'DESC',
								options: [
									{ value: 'DESC', label: 'Decrescente (nuovi prima)' },
									{ value: 'ASC',  label: 'Crescente (vecchi prima)' }
								],
								onChange: function (v) { set({ order: v }); }
							}) : null,
							rangeRow('N° massimo articoli (0 = tutti)', 'max_items', 0, 0, 50, 1)
						),

						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							el(ToggleControl, {
								label: 'Link al click',
								checked: a.show_link !== false,
								onChange: function (v) { set({ show_link: v }); }
							}),
							a.show_link !== false ? SelectControl ? el(SelectControl, {
								label: 'Apri in',
								value: a.link_target || '_self',
								options: [
									{ value: '_self',  label: 'Stessa finestra' },
									{ value: '_blank', label: 'Nuova scheda' }
								],
								onChange: function (v) { set({ link_target: v }); }
							}) : null : null,
							el(ToggleControl, {
								label: 'Mostra immagine',
								checked: a.show_image !== false,
								onChange: function (v) { set({ show_image: v }); }
							}),
							a.show_image !== false ? el(TextControl, {
								label: 'Prefisso label immagine',
								help: 'Es: "ARCHIVIO" → "ARCHIVIO · 2024"',
								value: a.image_label_prefix || '',
								onChange: function (v) { set({ image_label_prefix: v }); }
							}) : null,
							el(ToggleControl, {
								label: 'Mostra excerpt',
								checked: a.show_excerpt !== false,
								onChange: function (v) { set({ show_excerpt: v }); }
							}),
							a.show_excerpt !== false ? rangeRow('Lunghezza excerpt (parole)', 'excerpt_length', 30, 5, 80, 5) : null
						),

						el(PanelBody, { title: 'Colonna sinistra', initialOpen: false },
							SelectControl ? el(SelectControl, {
								label: 'Campo sinistra',
								value: a.left_field || 'post_date',
								options: [
									{ value: 'post_date',    label: 'Data (formato configurabile)' },
									{ value: 'custom_field', label: 'Campo personalizzato' }
								],
								onChange: function (v) { set({ left_field: v }); }
							}) : null,
							a.left_field !== 'custom_field' ? el(TextControl, {
								label: 'Formato data (PHP)',
								help: 'Y = 2024 · j M = 14 Giu · d/m = 14/06',
								value: a.left_format || 'Y',
								onChange: function (v) { set({ left_format: v }); }
							}) : null,
							a.left_field === 'custom_field' ? el(TextControl, {
								label: 'Nome meta field',
								value: a.left_custom_field || '',
								onChange: function (v) { set({ left_custom_field: v }); }
							}) : null
						),

						el(PanelBody, { title: 'Layout sezione', initialOpen: false },
							colorRow('Sfondo', 'bg_color'),
							rangeRow('Larghezza massima (px)', 'max_width', 1100, 400, 1920, 20),
							rangeRow('Padding verticale (px)', 'padding_y', 60, 0, 200, 4),
							rangeRow('Padding orizzontale (px)', 'padding_x', 24, 0, 120, 4),
							rangeRow('Spazio interno riga (px)', 'row_gap_y', 40, 16, 100, 4),
							rangeRow('Margine superiore (px)', 'margin_top', 0, 0, 200, 4),
							rangeRow('Margine inferiore (px)', 'margin_bottom', 0, 0, 200, 4),
							rangeRow('Margine sinistro (px)', 'margin_left', 0, 0, 200, 4),
							rangeRow('Margine destro (px)', 'margin_right', 0, 0, 200, 4),
							colorRow('Colore separatore', 'separator_color')
						),

						el(PanelBody, { title: 'Stile — colonna sinistra', initialOpen: false },
							colorRow('Colore testo', 'left_color'),
							rangeRow('Font size (px)', 'left_size', 80, 20, 140, 2),
							rangeRow('Font weight', 'left_weight', 900, 300, 900, 100),
							rangeRow('Larghezza colonna (px)', 'left_col_width', 140, 60, 300, 10)
						),

						el(PanelBody, { title: 'Stile — titolo', initialOpen: false },
							colorRow('Colore titolo', 'title_color'),
							rangeRow('Font size (px)', 'title_size', 22, 12, 56, 1),
							rangeRow('Font weight', 'title_weight', 800, 300, 900, 100),
							el(ToggleControl, {
								label: 'Testo maiuscolo',
								checked: a.title_upper !== false,
								onChange: function (v) { set({ title_upper: v }); }
							})
						),

						el(PanelBody, { title: 'Stile — testo corpo', initialOpen: false },
							colorRow('Colore', 'text_color'),
							rangeRow('Font size (px)', 'text_size', 15, 11, 22, 1)
						),

						el(PanelBody, { title: 'Stile — immagine', initialOpen: false },
							rangeRow('Larghezza colonna immagine (px)', 'image_col_width', 360, 160, 600, 20),
							el(TextControl, {
								label: 'Aspect ratio (CSS)',
								help: 'Es: 16/9 · 4/3 · 3/2 · 1/1',
								value: a.image_ratio || '16/9',
								onChange: function (v) { set({ image_ratio: v }); }
							}),
							rangeRow('Border radius (px)', 'image_radius', 8, 0, 32, 2),
							colorRow('Sfondo label', 'image_label_bg'),
							colorRow('Colore testo label', 'image_label_color'),
							rangeRow('Font size label (px)', 'image_label_size', 10, 7, 18, 1)
						)

					) : null;

					/* Preview canvas */
					var fakeRows = [
						{ left: '1943', title: 'Cousteau e Gagnan inventano l\'Aqualung', exc: 'L\'autorespiratore moderno nasce a Marsiglia. Da quel momento il mare smette di essere appannaggio dei palombari.' },
						{ left: '1959', title: 'Nasce CMAS',                              exc: 'Confédération Mondiale des Activités Subaquatiques. Cousteau ne è il primo presidente.' },
						{ left: '1978', title: 'Calypso fonda il club ad Arezzo',         exc: 'Tre amici, due ingegneri e un dentista, affittano un garage in via Roma.' }
					];

					var preview = el('div', {
						style: { background: a.bg_color || '#f6f1e6', borderRadius: '8px', padding: '16px', fontFamily: 'system-ui,sans-serif' }
					},
						fakeRows.map(function (row, idx) {
							return el('div', {
								key: idx,
								style: {
									display: 'grid',
									gridTemplateColumns: Math.min(a.left_col_width || 140, 80) + 'px 1fr' + (a.show_image !== false ? ' 140px' : ''),
									gap: '20px',
									alignItems: 'start',
									padding: '20px 0',
									borderBottom: '1px solid ' + (a.separator_color || 'rgba(10,37,64,.12)'),
									borderTop: idx === 0 ? ('1px solid ' + (a.separator_color || 'rgba(10,37,64,.12)')) : 'none'
								}
							},
								el('div', {
									style: {
										fontSize: Math.min(a.left_size || 80, 36) + 'px',
										fontWeight: a.left_weight || 900,
										color: a.left_color || '#c8a84b',
										lineHeight: 1,
										letterSpacing: '-.02em'
									}
								}, row.left),
								el('div', {},
									el('p', {
										style: {
											margin: '0 0 8px',
											fontSize: Math.min(a.title_size || 22, 16) + 'px',
											fontWeight: a.title_weight || 800,
											textTransform: a.title_upper !== false ? 'uppercase' : 'none',
											color: a.title_color || '#1B77A7',
											lineHeight: 1.2
										}
									}, row.title),
									a.show_excerpt !== false ? el('p', {
										style: { margin: 0, fontSize: Math.min(a.text_size || 15, 12) + 'px', color: a.text_color || '#3d5265', lineHeight: 1.5 }
									}, row.exc.substring(0, 80) + '…') : null
								),
								a.show_image !== false ? el('div', {
									style: {
										borderRadius: (a.image_radius || 8) + 'px',
										overflow: 'hidden',
										background: '#b0c8d8',
										aspectRatio: '16/9',
										position: 'relative',
										display: 'flex',
										alignItems: 'flex-end'
									}
								},
									el('span', {
										style: {
											padding: '4px 8px',
											background: a.image_label_bg || 'rgba(10,37,64,.6)',
											color: a.image_label_color || '#fff',
											fontSize: '8px',
											fontWeight: 600,
											letterSpacing: '.12em',
											textTransform: 'uppercase',
											width: '100%'
										}
									}, (a.image_label_prefix || 'ARCHIVIO') + ' · ' + row.left)
								) : null
							);
						}),
						el('div', { style: { fontSize: '10px', opacity: .5, marginTop: '8px', fontFamily: 'monospace' } },
							'calypso/lista-articoli · ' + (a.source_mode || 'all') +
							(a.max_items > 0 ? ' · max ' + a.max_items : ' · tutti') +
							' · ' + (a.order_by || 'date') + ' ' + (a.order || 'DESC')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   Blocchi generici
		   ════════════════════════════════════════════ */
		var hasInsideHero = info.attributes && info.attributes.inside_hero !== undefined;

		blocks.registerBlockType(info.name, {
			title: info.title,
			category: 'calypso',
			icon: info.icon || 'grid-view',
			attributes: info.attributes || {},
			edit: function (props) {
				var children = [
					el('div', {
						style: {
							padding: '12px 16px',
							background: '#e8f4f8',
							borderLeft: '4px solid #1d6f9c',
							fontFamily: 'monospace',
							fontSize: '13px',
							color: '#0a2540'
						}
					}, '⚓ ' + info.title + (hasInsideHero
						? ' — ' + (props.attributes.inside_hero ? 'Dentro hero (desktop)' : 'Fuori hero (mobile)')
						: ' (server-side rendered)'
					))
				];
				if (hasInsideHero && InspectorControls) {
					children.unshift(el(InspectorControls, {},
						el(PanelBody, { title: 'Impostazioni', initialOpen: true },
							el(ToggleControl, {
								label: 'Dentro hero',
								help: props.attributes.inside_hero
									? 'Card floating — visibile solo su desktop'
									: 'Strip section — visibile solo su mobile',
								checked: !!props.attributes.inside_hero,
								onChange: function (val) { props.setAttributes({ inside_hero: val }); }
							})
						)
					));
				}
				return el(Fragment, {}, children);
			},
			save: function () { return null; },
		});
	});
}(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor));
JS;

		wp_add_inline_script( 'calypso-blocks-editor', $js );
	}
}
