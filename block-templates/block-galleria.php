<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/galleria — muro di foto masonry con didascalia in overlay.
 *
 * Sorgente immagini configurabile (tutti i media taggati / tag specifico /
 * selezione manuale), pattern di griglia fisso e automatico, lightbox
 * opzionale al click.
 */

$a = $attributes ?? [];

$gap                    = (int)    ( $a['gap']                    ?? 0 );
$row_height             = (int)    ( $a['row_height']              ?? 200 );
$max_width              = (int)    ( $a['max_width']                ?? 1320 );
$bg_color               = (string) ( $a['bg_color']                 ?? '' );
$lightbox               = (bool)   ( $a['lightbox']                 ?? false );
$overlay_size           = (int)    ( $a['overlay_size']             ?? 10 );
$overlay_font_weight    = (int)    ( $a['overlay_font_weight']      ?? 400 );
$overlay_letter_spacing = (int)    ( $a['overlay_letter_spacing']   ?? 12 );

$safe_css_color = function ( $value, $default ) {
	$value = trim( (string) $value );
	return preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,%\s]+\)|hsla?\([0-9.,%\s]+\)|[a-zA-Z]+)$/', $value ) ? $value : $default;
};

$overlay_bg    = $safe_css_color( $a['overlay_bg'] ?? 'rgba(0,0,0,.35)', 'rgba(0,0,0,.35)' );
$overlay_color = $safe_css_color( $a['overlay_color'] ?? '#ffffff', '#ffffff' );
$bg_color_safe = $bg_color !== '' ? $safe_css_color( $bg_color, '' ) : '';

$query_args = Calypsosub_Gallery_Helpers::build_query_args( $a );
$items      = get_posts( $query_args );

if ( empty( $items ) ) {
	return;
}

$uid = 'cso-gal-' . sprintf( '%08x', crc32( implode( ',', [ $max_width, $row_height, $gap, count( $items ) ] ) ) );

$mobile_row_height = (int) round( $row_height * 0.8 );
?>
<style>
#<?php echo $uid; ?> .cso-gal__wrap{max-width:<?php echo $max_width; ?>px;margin:0 auto;display:grid;grid-template-columns:repeat(6,1fr);grid-auto-rows:<?php echo $row_height; ?>px;gap:<?php echo $gap; ?>px;}
#<?php echo $uid; ?> .cso-gal__cell{position:relative;overflow:hidden;background:#0a2540;}
#<?php echo $uid; ?> .cso-gal__cell img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center center;display:block;}
#<?php echo $uid; ?> .cso-gal__cap{position:absolute;bottom:0;left:0;font-family:monospace;font-size:<?php echo $overlay_size; ?>px;letter-spacing:<?php echo $overlay_letter_spacing / 100; ?>em;text-transform:uppercase;font-weight:<?php echo $overlay_font_weight; ?>;padding:10px 12px;background:<?php echo esc_attr( $overlay_bg ); ?>;color:<?php echo esc_attr( $overlay_color ); ?>;backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);border-top-right-radius:4px;}
<?php if ( $lightbox ) : ?>
#<?php echo $uid; ?> .cso-gal__cell{cursor:zoom-in;}
<?php endif; ?>
@media(max-width:1024px){
	#<?php echo $uid; ?> .cso-gal__wrap{grid-template-columns:1fr 1fr;grid-auto-rows:minmax(<?php echo $mobile_row_height; ?>px,auto);}
	#<?php echo $uid; ?> .cso-gal__cell{grid-column:span var(--cso-mcol,1) !important;grid-row:span 1 !important;height:var(--cso-mh,<?php echo $mobile_row_height; ?>px);}
}
</style>
<section <?php echo $bg_color_safe ? 'style="background:' . esc_attr( $bg_color_safe ) . ';"' : ''; ?> id="<?php echo $uid; ?>">
	<div class="cso-gal__wrap">
		<?php foreach ( $items as $index => $item ) :
			$desktop_style = Calypsosub_Gallery_Helpers::cell_style( $index, false );
			$mobile_style  = Calypsosub_Gallery_Helpers::cell_style( $index, true );
			$mobile_height = $index % 8 === 0 ? (int) round( $mobile_row_height * 1.375 ) : $mobile_row_height;
			$img_url       = wp_get_attachment_image_url( $item->ID, 'large' );
			$overlay_text  = Calypsosub_Gallery_Helpers::resolve_overlay_text( $item->ID );
			$alt           = get_post_meta( $item->ID, '_wp_attachment_image_alt', true ) ?: $overlay_text;
			if ( ! $img_url ) {
				continue;
			}
		?>
		<div class="cso-gal__cell"
		     data-index="<?php echo (int) $index; ?>"
		     <?php if ( $lightbox ) : ?>data-lightbox-src="<?php echo esc_url( wp_get_attachment_image_url( $item->ID, 'full' ) ?: $img_url ); ?>"<?php endif; ?>
		     style="grid-column:span <?php echo $desktop_style['col']; ?>;grid-row:span <?php echo $desktop_style['row']; ?>;--cso-mcol:<?php echo $mobile_style['col']; ?>;--cso-mh:<?php echo $mobile_height; ?>px;">
			<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
			<?php if ( $overlay_text ) : ?>
			<div class="cso-gal__cap"><?php echo esc_html( $overlay_text ); ?></div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
</section>
<?php if ( $lightbox ) : ?>
<div id="<?php echo $uid; ?>-lightbox" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(6,24,38,.92);align-items:center;justify-content:center;cursor:zoom-out;">
	<img style="max-width:92vw;max-height:92vh;object-fit:contain;">
</div>
<script>
(function(){
	var section = document.getElementById('<?php echo $uid; ?>');
	var overlay = document.getElementById('<?php echo $uid; ?>-lightbox');
	if (!section || !overlay) return;
	var overlayImg = overlay.querySelector('img');

	function open(src){
		overlayImg.src = src;
		overlay.style.display = 'flex';
	}
	function close(){
		overlay.style.display = 'none';
		overlayImg.src = '';
	}

	section.addEventListener('click', function(e){
		var cell = e.target.closest('.cso-gal__cell');
		if (!cell) return;
		var src = cell.getAttribute('data-lightbox-src');
		if (src) open(src);
	});
	overlay.addEventListener('click', close);
	document.addEventListener('keydown', function(e){
		if (e.key === 'Escape') close();
	});
})();
</script>
<?php endif; ?>
