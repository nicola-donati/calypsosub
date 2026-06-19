<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$a = $attributes ?? [];

$eyebrow       = (string) ( $a['eyebrow']          ?? '' );
$title         = (string) ( $a['title']            ?? '' );
$link_text     = (string) ( $a['header_link_text'] ?? '' );
$link_url      = (string) ( $a['header_link_url']  ?? '' );
$bg_color      = (string) ( $a['bg_color']         ?? '#dff4f8' );
$bg_image_id   = (int)    ( $a['bg_image_id']      ?? 0 );
$padding_y     = (int)    ( $a['padding_y']        ?? 80 );
$padding_x     = (int)    ( $a['padding_x']        ?? 48 );
$max_width     = (int)    ( $a['max_width']        ?? 1320 );
$eyebrow_color          = (string) ( $a['eyebrow_color']          ?? '#1B77A7' );
$eyebrow_size           = (int)    ( $a['eyebrow_size']           ?? 13 );
$eyebrow_letter_spacing = (int)    ( $a['eyebrow_letter_spacing'] ?? 16 );
$eyebrow_font_weight    = (int)    ( $a['eyebrow_font_weight']    ?? 600 );
$eyebrow_margin_bottom  = (int)    ( $a['eyebrow_margin_bottom']  ?? 16 );
$title_color            = (string) ( $a['title_color']            ?? '#1B77A7' );
$title_size             = (int)    ( $a['title_size']             ?? 76 );
$title_line_height      = (int)    ( $a['title_line_height']      ?? 95 );
$title_font_weight      = (int)    ( $a['title_font_weight']      ?? 900 );
$link_color             = (string) ( $a['link_color']             ?? '' );
$link_size              = (int)    ( $a['link_size']              ?? 14 );
$link_font_weight       = (int)    ( $a['link_font_weight']       ?? 600 );
$head_margin_bottom     = (int)    ( $a['head_margin_bottom']     ?? 48 );

$effective_link_color = $link_color ?: $eyebrow_color;

$bg_img_url = $bg_image_id ? ( wp_get_attachment_image_url( $bg_image_id, 'full' ) ?: '' ) : '';
$content    = $content ?? '';

$section_style = 'background-color:' . esc_attr( $bg_color ) . ';';
if ( $bg_img_url ) {
	$section_style .= 'background-image:url(' . esc_url( $bg_img_url ) . ');background-size:cover;background-position:center;';
}

$has_header = $eyebrow || $title || ( $link_text && $link_url );

$uid = 'cso-sez-' . sprintf( '%08x', crc32( implode( ',', [ $max_width, $padding_y, $padding_x, $title_size, $eyebrow_size, $eyebrow_margin_bottom, $head_margin_bottom ] ) ) );
?>
<style>
#<?php echo $uid; ?> .cso-sez__wrap{max-width:<?php echo $max_width; ?>px;margin:0 auto;padding:<?php echo $padding_y; ?>px <?php echo $padding_x; ?>px}
#<?php echo $uid; ?> .cso-sez__title{font-size:<?php echo $title_size; ?>px}
@media(max-width:1024px){
	#<?php echo $uid; ?> .cso-sez__wrap{padding:<?php echo (int)($padding_y*.8); ?>px <?php echo (int)($padding_x*.6); ?>px}
	#<?php echo $uid; ?> .cso-sez__title{font-size:<?php echo (int)($title_size*.73); ?>px}
}
@media(max-width:760px){
	#<?php echo $uid; ?> .cso-sez__wrap{padding:<?php echo (int)($padding_y*.6); ?>px <?php echo (int)($padding_x*.4); ?>px}
	#<?php echo $uid; ?> .cso-sez__title{font-size:<?php echo (int)($title_size*.5); ?>px}
}
</style>

<section id="<?php echo $uid; ?>" class="cso-sezione" style="<?php echo $section_style; ?>">
<div class="cso-sez__wrap">

	<?php if ( $has_header ) : ?>
	<div class="cso-sez__head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:<?php echo $head_margin_bottom; ?>px;flex-wrap:wrap;">
		<div>
			<?php if ( $eyebrow ) : ?>
			<span class="cso-sez__eyebrow" style="display:block;font-weight:<?php echo $eyebrow_font_weight; ?>;letter-spacing:<?php echo $eyebrow_letter_spacing / 100; ?>em;text-transform:uppercase;font-size:<?php echo $eyebrow_size; ?>px;color:<?php echo esc_attr( $eyebrow_color ); ?>;margin-bottom:<?php echo $eyebrow_margin_bottom; ?>px;">
				<?php echo esc_html( $eyebrow ); ?>
			</span>
			<?php endif; ?>
			<?php if ( $title ) : ?>
			<h2 class="cso-sez__title display" style="line-height:<?php echo $title_line_height / 100; ?>;color:<?php echo esc_attr( $title_color ); ?>;margin:0;font-weight:<?php echo $title_font_weight; ?>;">
				<?php echo nl2br( esc_html( $title ) ); ?>
			</h2>
			<?php endif; ?>
		</div>
		<?php if ( $link_text && $link_url ) : ?>
		<a href="<?php echo esc_url( $link_url ); ?>" class="cso-sez__head-link" style="flex-shrink:0;display:inline-flex;align-items:center;gap:8px;font-size:<?php echo $link_size; ?>px;font-weight:<?php echo $link_font_weight; ?>;color:<?php echo esc_attr( $effective_link_color ); ?>;white-space:nowrap;align-self:flex-end;padding-bottom:4px;text-decoration:none;">
			<?php echo esc_html( $link_text ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="cso-sez__content">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

</div>
</section>
