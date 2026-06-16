<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/promo-card — card promozionale personalizzabile
 *
 * Tutti gli attributi (testo, stile, immagine, tipografia) sono
 * configurabili dall'editor Gutenberg. Progettato per uso in griglia,
 * riga o colonna — l'output è un singolo <div> senza wrapper extra.
 */

$a = $attributes ?? [];

/* ── Immagine ── */
$image_id       = (int)    ( $a['image_id']         ?? 0 );
$image_alt      = (string) ( $a['image_alt']         ?? '' );
$image_height   = (int)    ( $a['image_height']      ?? 200 );
$image_obj_fit  = (string) ( $a['image_object_fit']  ?? 'cover' );
$image_obj_pos  = (string) ( $a['image_object_pos']  ?? 'center center' );

/* ── Overlay ── */
$overlay_text  = (string) ( $a['overlay_text']  ?? '' );
$overlay_bg    = (string) ( $a['overlay_bg']    ?? 'rgba(6,24,38,0.6)' );
$overlay_color = (string) ( $a['overlay_color'] ?? '#ffffff' );
$overlay_size  = (int)    ( $a['overlay_size']  ?? 10 );

/* ── Contenuto ── */
$eyebrow   = (string) ( $a['eyebrow']     ?? '' );
$title     = (string) ( $a['title']       ?? '' );
$desc      = (string) ( $a['description'] ?? '' );
$link_text = (string) ( $a['link_text']   ?? 'Scopri' );
$link_url  = (string) ( $a['link_url']    ?? '' );
$link_tab  = (bool)   ( $a['link_new_tab'] ?? false );

/* ── Stile card ── */
$card_bg     = (string) ( $a['card_bg']     ?? '#ffffff' );
$card_radius = (int)    ( $a['card_radius']  ?? 16 );
$card_pad    = (int)    ( $a['card_padding'] ?? 24 );
$card_shadow = isset( $a['card_shadow'] ) ? (bool) $a['card_shadow'] : true;

/* ── Tipografia eyebrow ── */
$eyebrow_color = (string) ( $a['eyebrow_color'] ?? '#1B77A7' );
$eyebrow_size  = (int)    ( $a['eyebrow_size']  ?? 13 );

/* ── Tipografia titolo ── */
$title_color     = (string) ( $a['title_color']    ?? '#061826' );
$title_size      = (int)    ( $a['title_size']      ?? 42 );
$title_weight    = (string) ( $a['title_weight']    ?? '900' );
$title_transform = (string) ( $a['title_transform'] ?? 'uppercase' );
$title_font      = (string) ( $a['title_font']      ?? '' );

/* ── Tipografia descrizione ── */
$desc_color = (string) ( $a['desc_color'] ?? '#3d5a6c' );
$desc_size  = (int)    ( $a['desc_size']  ?? 14 );

/* ── Link ── */
$link_color = (string) ( $a['link_color'] ?? '#1B77A7' );
$link_size  = (int)    ( $a['link_size']  ?? 13 );

/* ── Immagine URL ── */
$img_url = $image_id ? ( wp_get_attachment_image_url( $image_id, 'large' ) ?: '' ) : '';

/* ── Card wrapper style ── */
$card_css = sprintf(
	'background:%s;border-radius:%dpx;overflow:hidden;display:flex;flex-direction:column;position:relative;height:100%%;',
	esc_attr( $card_bg ),
	$card_radius
);
if ( $card_shadow ) {
	$card_css .= 'box-shadow:0 8px 32px -8px rgba(6,24,38,.18);';
}

/* ── Title style ── */
$title_css = sprintf(
	'color:%s;font-size:%dpx;font-weight:%s;text-transform:%s;line-height:1;margin:0 0 12px;',
	esc_attr( $title_color ),
	$title_size,
	esc_attr( $title_weight ),
	esc_attr( $title_transform )
);
if ( $title_font ) {
	$title_css .= 'font-family:' . esc_attr( $title_font ) . ';';
}
?>
<div class="calypso-promo-card" style="<?php echo $card_css; ?>">

	<?php if ( $img_url || $overlay_text ) : ?>
	<div class="calypso-promo-card__img-wrap" style="height:<?php echo $image_height; ?>px;position:relative;overflow:hidden;background:#c0d8e4;">
		<?php if ( $img_url ) : ?>
		<img class="calypso-promo-card__img"
		     src="<?php echo esc_url( $img_url ); ?>"
		     alt="<?php echo esc_attr( $image_alt ?: $title ); ?>"
		     loading="lazy"
		     style="width:100%;height:100%;object-fit:<?php echo esc_attr( $image_obj_fit ); ?>;object-position:<?php echo esc_attr( $image_obj_pos ); ?>;display:block;">
		<?php endif; ?>
		<?php if ( $overlay_text ) : ?>
		<div class="calypso-promo-card__overlay"
		     style="position:absolute;bottom:0;left:0;padding:10px 14px;background:<?php echo esc_attr( $overlay_bg ); ?>;color:<?php echo esc_attr( $overlay_color ); ?>;font-size:<?php echo $overlay_size; ?>px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;border-top-right-radius:8px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);">
			<?php echo esc_html( $overlay_text ); ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="calypso-promo-card__body" style="padding:<?php echo $card_pad; ?>px;flex:1;display:flex;flex-direction:column;">

		<?php if ( $eyebrow ) : ?>
		<div class="calypso-promo-card__eyebrow"
		     style="color:<?php echo esc_attr( $eyebrow_color ); ?>;font-size:<?php echo $eyebrow_size; ?>px;font-weight:700;margin:0 0 6px;">
			<?php echo esc_html( $eyebrow ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $title ) : ?>
		<div class="calypso-promo-card__title" style="<?php echo $title_css; ?>">
			<?php echo esc_html( $title ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $desc ) : ?>
		<div class="calypso-promo-card__desc"
		     style="color:<?php echo esc_attr( $desc_color ); ?>;font-size:<?php echo $desc_size; ?>px;line-height:1.5;margin:0 0 16px;flex:1;">
			<?php echo esc_html( $desc ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $link_text && $link_url ) : ?>
		<a href="<?php echo esc_url( $link_url ); ?>"
		   class="calypso-promo-card__link"
		   style="color:<?php echo esc_attr( $link_color ); ?>;font-size:<?php echo $link_size; ?>px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;"
		   <?php echo $link_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
			<?php echo esc_html( $link_text ); ?> <span aria-hidden="true">→</span>
		</a>
		<style>.calypso-promo-card__link::after{content:'';position:absolute;inset:0;z-index:1;}</style>
		<?php endif; ?>

	</div>
</div>
