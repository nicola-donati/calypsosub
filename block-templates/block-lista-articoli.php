<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$a = $attributes ?? [];

/* ── Sorgente ── */
$source_mode        = (string) ( $a['source_mode']        ?? 'all' );
$category_ids       = (array)  ( $a['category_ids']       ?? [] );
$tag_ids            = (array)  ( $a['tag_ids']            ?? [] );
$manual_ids         = (array)  ( $a['manual_ids']         ?? [] );
$order_by           = (string) ( $a['order_by']           ?? 'date' );
$order              = (string) ( $a['order']              ?? 'DESC' );
$max_items          = (int)    ( $a['max_items']          ?? 0 );

/* ── Comportamento ── */
$show_link          = (bool)   ( $a['show_link']          ?? true );
$link_target        = (string) ( $a['link_target']        ?? '_self' );
$show_image         = (bool)   ( $a['show_image']         ?? true );
$image_size         = (string) ( $a['image_size']         ?? 'medium_large' );
$image_label_prefix = (string) ( $a['image_label_prefix'] ?? 'ARCHIVIO' );
$show_excerpt       = (bool)   ( $a['show_excerpt']       ?? true );
$excerpt_length     = (int)    ( $a['excerpt_length']     ?? 30 );

/* ── Colonna sinistra ── */
$left_field        = (string) ( $a['left_field']        ?? 'post_date' );
$left_format       = (string) ( $a['left_format']       ?? 'Y' );
$left_custom_field = (string) ( $a['left_custom_field'] ?? '' );

/* ── Layout sezione ── */
$bg_color      = (string) ( $a['bg_color']      ?? '#f6f1e6' );
$max_width     = (int)    ( $a['max_width']     ?? 1100 );
$padding_y     = (int)    ( $a['padding_y']     ?? 60 );
$padding_x     = (int)    ( $a['padding_x']     ?? 24 );
$margin_top    = (int)    ( $a['margin_top']    ?? 0 );
$margin_right  = (int)    ( $a['margin_right']  ?? 0 );
$margin_bottom = (int)    ( $a['margin_bottom'] ?? 0 );
$margin_left   = (int)    ( $a['margin_left']   ?? 0 );
$row_gap_y     = (int)    ( $a['row_gap_y']     ?? 0 );

/* ── Colori sinistra ── */
$left_color  = (string) ( $a['left_color']  ?? '#c8a84b' );
$left_size   = (int)    ( $a['left_size']   ?? 80 );
$left_weight = (int)    ( $a['left_weight'] ?? 900 );
$left_col_width = (int) ( $a['left_col_width'] ?? 140 );

/* ── Colori titolo ── */
$title_color  = (string) ( $a['title_color']  ?? '#1B77A7' );
$title_size   = (int)    ( $a['title_size']   ?? 22 );
$title_weight = (int)    ( $a['title_weight'] ?? 800 );
$title_upper  = (bool)   ( $a['title_upper']  ?? true );

/* ── Testo corpo ── */
$text_color = (string) ( $a['text_color'] ?? '#3d5265' );
$text_size  = (int)    ( $a['text_size']  ?? 15 );

/* ── Separatore ── */
$separator_color = (string) ( $a['separator_color'] ?? 'rgba(10,37,64,.12)' );

/* ── Immagine ── */
$image_ratio       = (string) ( $a['image_ratio']       ?? '16/9' );
$image_col_width   = (int)    ( $a['image_col_width']   ?? 360 );
$image_radius      = (int)    ( $a['image_radius']      ?? 8 );
$image_label_bg    = (string) ( $a['image_label_bg']    ?? 'rgba(10,37,64,.6)' );
$image_label_color = (string) ( $a['image_label_color'] ?? '#ffffff' );
$image_label_size  = (int)    ( $a['image_label_size']  ?? 10 );

/* ── Query articoli ── */
$q_args = [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $max_items > 0 ? $max_items : -1,
	'orderby'        => $order_by === 'random' ? 'rand' : $order_by,
	'order'          => strtoupper( $order ),
];

if ( $source_mode === 'manual' && ! empty( $manual_ids ) ) {
	$q_args['post__in'] = array_map( 'intval', $manual_ids );
	$q_args['orderby']  = 'post__in';
} elseif ( $source_mode === 'category' && ! empty( $category_ids ) ) {
	$q_args['category__in'] = array_map( 'intval', $category_ids );
} elseif ( $source_mode === 'tag' && ! empty( $tag_ids ) ) {
	$q_args['tag__in'] = array_map( 'intval', $tag_ids );
} elseif ( $source_mode === 'category_tag' ) {
	if ( ! empty( $category_ids ) ) $q_args['category__in'] = array_map( 'intval', $category_ids );
	if ( ! empty( $tag_ids ) )      $q_args['tag__in']      = array_map( 'intval', $tag_ids );
}

$posts = get_posts( $q_args );
if ( empty( $posts ) ) return;

/* ── Helpers ── */
$css = static function ( string $v ): string {
	return preg_replace( '/[^#a-zA-Z0-9.,()%\s\-\/]/', '', $v );
};

$get_left_value = static function ( \WP_Post $post, string $field, string $format, string $custom_key ): string {
	if ( $field === 'post_date' ) {
		return (string) get_the_date( $format ?: 'Y', $post->ID );
	}
	if ( $field === 'custom_field' && $custom_key !== '' ) {
		return (string) get_post_meta( $post->ID, $custom_key, true );
	}
	return '';
};

$uid = 'cso-art-' . sprintf( '%08x', crc32( implode( ',', [ $max_width, $left_size, $image_col_width, count( $posts ) ] ) ) );

$margin_css    = $margin_top . 'px ' . $margin_right . 'px ' . $margin_bottom . 'px ' . $margin_left . 'px';
$section_style = 'background:' . $css( $bg_color ) . ';padding:' . $padding_y . 'px ' . $padding_x . 'px;';
if ( $margin_css !== '0px 0px 0px 0px' ) {
	$section_style .= 'margin:' . $margin_css . ';';
}
$title_transform = $title_upper ? 'uppercase' : 'none';
?>
<style>
#<?php echo $uid; ?>,#<?php echo $uid; ?> *{box-sizing:border-box;}
#<?php echo $uid; ?> .csart__inner{max-width:<?php echo $max_width; ?>px;margin:0 auto;}
#<?php echo $uid; ?> .csart__row{
  display:grid;
  grid-template-columns:<?php echo $left_col_width; ?>px 1fr<?php if ( $show_image ) : ?> <?php echo $image_col_width; ?>px<?php endif; ?>;
  gap:40px;
  align-items:start;
  padding:<?php echo $row_gap_y > 0 ? $row_gap_y : 40; ?>px 0;
  border-bottom:1px solid <?php echo $css( $separator_color ); ?>;
  text-decoration:none;
  color:inherit;
}
#<?php echo $uid; ?> .csart__row:first-child{border-top:1px solid <?php echo $css( $separator_color ); ?>;}
#<?php echo $uid; ?> a.csart__row{cursor:pointer;}
#<?php echo $uid; ?> .csart__left{
  font-size:<?php echo $left_size; ?>px;
  font-weight:<?php echo $left_weight; ?>;
  line-height:1;
  color:<?php echo $css( $left_color ); ?>;
  letter-spacing:-.02em;
  padding-top:4px;
}
#<?php echo $uid; ?> .csart__body{min-width:0;}
#<?php echo $uid; ?> .csart__title{
  margin:0 0 12px;
  font-size:<?php echo $title_size; ?>px;
  font-weight:<?php echo $title_weight; ?>;
  line-height:1.15;
  text-transform:<?php echo $title_transform; ?>;
  color:<?php echo $css( $title_color ); ?>;
}
#<?php echo $uid; ?> a.csart__row:hover .csart__title{
  text-decoration:underline;
  text-underline-offset:3px;
}
#<?php echo $uid; ?> .csart__excerpt{
  margin:0;
  font-size:<?php echo $text_size; ?>px;
  color:<?php echo $css( $text_color ); ?>;
  line-height:1.6;
}
#<?php echo $uid; ?> .csart__img-wrap{
  position:relative;
  border-radius:<?php echo $image_radius; ?>px;
  overflow:hidden;
  aspect-ratio:<?php echo $css( $image_ratio ); ?>;
  background:#d0dce5;
}
#<?php echo $uid; ?> .csart__img{
  width:100%;height:100%;
  object-fit:cover;
  display:block;
  transition:transform .4s ease;
}
#<?php echo $uid; ?> a.csart__row:hover .csart__img{transform:scale(1.04);}
#<?php echo $uid; ?> .csart__img-label{
  position:absolute;bottom:0;left:0;
  padding:7px 14px;
  background:<?php echo $css( $image_label_bg ); ?>;
  color:<?php echo $css( $image_label_color ); ?>;
  font-size:<?php echo $image_label_size; ?>px;
  font-weight:600;
  letter-spacing:.14em;
  text-transform:uppercase;
}
@media(max-width:900px){
  #<?php echo $uid; ?> .csart__row{
    grid-template-columns:<?php echo min( $left_col_width, 100 ); ?>px 1fr;
    gap:24px;
  }
  #<?php echo $uid; ?> .csart__img-wrap{grid-column:1/-1;}
  #<?php echo $uid; ?> .csart__left{font-size:<?php echo max( 40, (int) round( $left_size * .65 ) ); ?>px;}
}
@media(max-width:560px){
  #<?php echo $uid; ?> .csart__row{grid-template-columns:1fr;gap:16px;padding:28px 0;}
  #<?php echo $uid; ?> .csart__left{font-size:<?php echo max( 36, (int) round( $left_size * .55 ) ); ?>px;}
}
</style>
<section id="<?php echo $uid; ?>" style="<?php echo esc_attr( $section_style ); ?>">
	<div class="csart__inner">
	<?php foreach ( $posts as $post ) :
		$id         = $post->ID;
		$left_val   = $get_left_value( $post, $left_field, $left_format, $left_custom_field );

		$excerpt_raw  = $post->post_excerpt !== ''
			? $post->post_excerpt
			: wp_trim_words( $post->post_content, $excerpt_length, '…' );
		$excerpt_text = $show_excerpt ? wp_strip_all_tags( $excerpt_raw ) : '';

		$img_url    = $show_image ? get_the_post_thumbnail_url( $id, $image_size ) : '';

		$label_text = trim( $image_label_prefix . ( $left_val !== '' ? ' · ' . $left_val : '' ) );

		$row_tag = $show_link ? 'a' : 'div';
	?>
	<<?php echo $row_tag; ?> class="csart__row"
		<?php if ( $show_link ) : ?>href="<?php echo esc_url( get_permalink( $id ) ); ?>"<?php if ( $link_target === '_blank' ) : ?> target="_blank" rel="noopener"<?php endif; ?><?php endif; ?>>
		<div class="csart__left"><?php echo esc_html( $left_val ); ?></div>
		<div class="csart__body">
			<h3 class="csart__title"><?php echo esc_html( $post->post_title ); ?></h3>
			<?php if ( $show_excerpt && $excerpt_text !== '' ) : ?>
			<p class="csart__excerpt"><?php echo esc_html( $excerpt_text ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $show_image ) : ?>
		<div class="csart__img-wrap">
			<?php if ( $img_url ) : ?>
			<img class="csart__img" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" loading="lazy">
			<?php else : ?>
			<div class="csart__img" style="background:#c0d5e0;width:100%;height:100%;"></div>
			<?php endif; ?>
			<span class="csart__img-label"><?php echo esc_html( $label_text ); ?></span>
		</div>
		<?php endif; ?>
	</<?php echo $row_tag; ?>>
	<?php endforeach; ?>
	</div>
</section>
