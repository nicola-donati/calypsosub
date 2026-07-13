<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/galleria — muro di foto a grid con didascalia in overlay.
 *
 * Ogni immagine viene categorizzata per forma (orizzontale 2:1, verticale
 * 1:2, o quadrata) in base al proprio aspect ratio reale, e riceve una cella
 * CSS Grid con proporzioni coerenti (mai un'immagine rettangolare forzata in
 * uno spazio quadrato). Ogni tanto (35% delle volte) la cella è "big" — il
 * doppio in entrambe le dimensioni — per dare un punto focale al layout.
 * grid-auto-flow:dense impacca le celle minimizzando i buchi residui.
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
$source_mode            = (string) ( $a['source_mode']              ?? 'all' );
$margin_top             = (int)    ( $a['margin_top']                ?? 0 );
$margin_right           = (int)    ( $a['margin_right']              ?? 0 );
$margin_bottom          = (int)    ( $a['margin_bottom']             ?? 0 );
$margin_left            = (int)    ( $a['margin_left']               ?? 0 );

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

if ( $source_mode !== 'manual' ) {
	shuffle( $items );
}

$uid = 'cso-gal-' . sprintf( '%08x', crc32( implode( ',', [ $max_width, $row_height, $gap, count( $items ) ] ) ) );

$mobile_row_height = (int) round( $row_height * 0.8 );
// max_width è spesso solo un tetto CSS generoso: il contenitore reale è
// quasi sempre quello del tema (tipicamente ~1320px), non il valore
// configurato. Le colonne si calcolano sul più stretto dei due per evitare
// celle troppo lontane dal quadrato quando il cap è molto più largo del
// reale — le colonne restano comunque a "1fr" quindi non vanno mai in
// overflow indipendentemente da questa stima.
$desktop_cols = max( 4, (int) floor( min( $max_width, 1320 ) / max( 1, $row_height ) ) );
$mobile_cols  = 4;

$cells = Calypsosub_Gallery_Helpers::build_cells_from_attachments( wp_list_pluck( $items, 'ID' ) );

if ( empty( $cells ) ) {
	return;
}

$units = Calypsosub_Gallery_Helpers::build_units( $cells );
?>
<style>
#<?php echo $uid; ?> .cso-gal__wrap{--a:<?php echo $row_height; ?>px;max-width:<?php echo $max_width > 0 ? $max_width . 'px' : 'none'; ?>;width:100%;margin:0 auto;display:grid;grid-template-columns:repeat(<?php echo $desktop_cols; ?>, 1fr);grid-auto-rows:var(--a);grid-auto-flow:dense;gap:<?php echo $gap; ?>px;}
#<?php echo $uid; ?> .cso-gal__cell{position:relative;overflow:hidden;background:#0a2540;}
#<?php echo $uid; ?> .cso-gal__cell img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center center;display:block;}
#<?php echo $uid; ?> .cso-gal__cap{position:absolute;bottom:0;left:0;font-family:monospace;font-size:<?php echo $overlay_size; ?>px;letter-spacing:<?php echo $overlay_letter_spacing / 100; ?>em;text-transform:uppercase;font-weight:<?php echo $overlay_font_weight; ?>;padding:10px 12px;background:<?php echo esc_attr( $overlay_bg ); ?>;color:<?php echo esc_attr( $overlay_color ); ?>;backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);border-top-right-radius:4px;}
<?php if ( $lightbox ) : ?>
#<?php echo $uid; ?> .cso-gal__cell{cursor:zoom-in;}
<?php endif; ?>
@media(max-width:1024px){
	#<?php echo $uid; ?> .cso-gal__wrap{--a:<?php echo $mobile_row_height; ?>px;grid-template-columns:repeat(<?php echo $mobile_cols; ?>, 1fr);}
}
</style>
<?php
$section_style = [];
if ( $bg_color_safe ) {
	$section_style[] = 'background:' . esc_attr( $bg_color_safe );
}
$margin_css = $margin_top . 'px ' . $margin_right . 'px ' . $margin_bottom . 'px ' . $margin_left . 'px';
if ( $margin_css !== '0px 0px 0px 0px' ) {
	$section_style[] = 'margin:' . $margin_css;
}
$section_style_attr = $section_style ? ' style="' . implode( ';', $section_style ) . '"' : '';
?>
<section<?php echo $section_style_attr; ?> id="<?php echo $uid; ?>">
	<div class="cso-gal__wrap">
		<?php foreach ( $units as $index => $unit ) :
			$cell = $unit['cell'];
		?>
		<div class="cso-gal__cell"
		     data-index="<?php echo (int) $index; ?>"
		     <?php if ( $lightbox ) : ?>data-lightbox-src="<?php echo esc_url( $cell['full'] ); ?>"<?php endif; ?>
		     style="grid-column:span <?php echo (int) $unit['col']; ?>;grid-row:span <?php echo (int) $unit['row']; ?>;">
			<img src="<?php echo esc_url( $cell['url'] ); ?>" alt="<?php echo esc_attr( $cell['alt'] ); ?>" loading="lazy">
			<?php if ( $cell['caption'] ) : ?>
			<div class="cso-gal__cap"><?php echo esc_html( $cell['caption'] ); ?></div>
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
