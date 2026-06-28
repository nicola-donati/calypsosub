<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$a = $attributes ?? [];

/* ── Sorgente ── */
$source_mode   = (string) ( $a['source_mode']   ?? 'all' );
$manual_ids    = (array)  ( $a['manual_ids']    ?? [] );
$livello_slugs = (array)  ( $a['livello_slugs'] ?? [] );
$max_items     = (int)    ( $a['max_items']     ?? 0 );
$order         = (string) ( $a['order']         ?? 'menu_order' );

/* ── Comportamento ── */
$show_link            = (bool)   ( $a['show_link']            ?? true );
$link_target          = (string) ( $a['link_target']          ?? '_self' );
$show_badge           = (bool)   ( $a['show_badge']           ?? true );
$show_stats           = (bool)   ( $a['show_stats']           ?? true );
$show_stat_durata     = (bool)   ( $a['show_stat_durata']     ?? true );
$show_stat_pratica    = (bool)   ( $a['show_stat_pratica']    ?? true );
$show_stat_profondita = (bool)   ( $a['show_stat_profondita'] ?? false );
$show_stat_badge      = (bool)   ( $a['show_stat_badge']      ?? false );
$show_right           = (bool)   ( $a['show_right']           ?? true );
$right_meta_key       = (string) ( $a['right_meta_key']       ?? '_corso_stat_durata' );
$right_label          = (string) ( $a['right_label']          ?? 'DURATA' );
$right_unit           = (string) ( $a['right_unit']           ?? '' );

/* ── Featured ── */
$featured_enabled = (bool) ( $a['featured_enabled'] ?? true );
$featured_index   = (int)  ( $a['featured_index']   ?? 0 );

/* ── Layout sezione ── */
$bg_color      = (string) ( $a['bg_color']      ?? '#f6f1e6' );
$max_width     = (int)    ( $a['max_width']     ?? 900 );
$padding_y     = (int)    ( $a['padding_y']     ?? 48 );
$padding_x     = (int)    ( $a['padding_x']     ?? 24 );
$gap           = (int)    ( $a['gap']           ?? 12 );
$margin_top    = (int)    ( $a['margin_top']    ?? 0 );
$margin_right  = (int)    ( $a['margin_right']  ?? 0 );
$margin_bottom = (int)    ( $a['margin_bottom'] ?? 0 );
$margin_left   = (int)    ( $a['margin_left']   ?? 0 );

/* ── Righe normali ── */
$row_bg           = (string) ( $a['row_bg']           ?? '#ffffff' );
$row_text_color   = (string) ( $a['row_text_color']   ?? '#0a2540' );
$row_border_color = (string) ( $a['row_border_color'] ?? 'rgba(10,37,64,.08)' );
$row_radius       = (int)    ( $a['row_radius']       ?? 16 );
$row_padding_y    = (int)    ( $a['row_padding_y']    ?? 20 );
$row_padding_x    = (int)    ( $a['row_padding_x']    ?? 28 );
$row_shadow       = (string) ( $a['row_shadow']       ?? '0 4px 16px -8px rgba(10,37,64,.15)' );

/* ── Riga featured ── */
$feat_bg                = (string) ( $a['feat_bg']                ?? '#0a2540' );
$feat_text_color        = (string) ( $a['feat_text_color']        ?? '#ffffff' );
$feat_badge_bg          = (string) ( $a['feat_badge_bg']          ?? 'rgba(255,255,255,.15)' );
$feat_badge_color       = (string) ( $a['feat_badge_color']       ?? '#ffffff' );
$feat_badge_border      = (string) ( $a['feat_badge_border']      ?? 'rgba(255,255,255,.3)' );
$feat_stats_color       = (string) ( $a['feat_stats_color']       ?? 'rgba(255,255,255,.7)' );
$feat_right_label_color = (string) ( $a['feat_right_label_color'] ?? 'rgba(255,255,255,.55)' );
$feat_right_value_color = (string) ( $a['feat_right_value_color'] ?? '#ffffff' );

/* ── Badge livello ── */
$badge_bg        = (string) ( $a['badge_bg']        ?? 'transparent' );
$badge_color     = (string) ( $a['badge_color']     ?? '#0a2540' );
$badge_border    = (string) ( $a['badge_border']    ?? 'rgba(10,37,64,.2)' );
$badge_size      = (int)    ( $a['badge_size']      ?? 12 );
$badge_weight    = (int)    ( $a['badge_weight']    ?? 600 );
$badge_radius    = (int)    ( $a['badge_radius']    ?? 999 );
$badge_min_width = (int)    ( $a['badge_min_width'] ?? 72 );

/* ── Titolo ── */
$title_size   = (int)  ( $a['title_size']   ?? 22 );
$title_weight = (int)  ( $a['title_weight'] ?? 800 );
$title_upper  = (bool) ( $a['title_upper']  ?? true );

/* ── Stats/sottotitolo ── */
$stats_size  = (int)    ( $a['stats_size']  ?? 14 );
$stats_color = (string) ( $a['stats_color'] ?? 'rgba(10,37,64,.6)' );

/* ── Colonna destra ── */
$right_label_size   = (int)    ( $a['right_label_size']   ?? 10 );
$right_label_color  = (string) ( $a['right_label_color']  ?? 'rgba(10,37,64,.5)' );
$right_value_size   = (int)    ( $a['right_value_size']   ?? 28 );
$right_value_color  = (string) ( $a['right_value_color']  ?? '#0a2540' );
$right_value_weight = (int)    ( $a['right_value_weight'] ?? 800 );

/* ── Query corsi ── */
$q_args = [ 'posts_per_page' => $max_items > 0 ? $max_items : -1 ];

if ( $source_mode === 'manual' && ! empty( $manual_ids ) ) {
	$q_args['post__in'] = array_map( 'intval', $manual_ids );
	$q_args['orderby']  = 'post__in';
} elseif ( $source_mode === 'livello' && ! empty( $livello_slugs ) ) {
	$q_args['tax_query'] = [ [
		'taxonomy' => 'calypso_livello',
		'field'    => 'slug',
		'terms'    => $livello_slugs,
	] ];
}

if ( $order === 'title' ) {
	$q_args['orderby'] = 'title';
	$q_args['order']   = 'ASC';
} elseif ( $order === 'date' ) {
	$q_args['orderby'] = 'date';
	$q_args['order']   = 'DESC';
} elseif ( $order === 'menu_order' && $source_mode !== 'manual' ) {
	$q_args['orderby'] = 'menu_order';
	$q_args['order']   = 'ASC';
}

$corsi = calypso_get_corsi( $q_args );
if ( empty( $corsi ) ) return;

/* ── CSS sanitization ── */
$css = static function ( string $v ): string {
	return preg_replace( '/[^#a-zA-Z0-9.,()%\s\-\/]/', '', $v );
};

$uid = 'cso-cstrip-' . sprintf( '%08x', crc32( implode( ',', [ $max_width, $gap, $row_radius, count( $corsi ) ] ) ) );

$margin_css    = $margin_top . 'px ' . $margin_right . 'px ' . $margin_bottom . 'px ' . $margin_left . 'px';
$section_style = 'background:' . $css( $bg_color ) . ';padding:' . $padding_y . 'px ' . $padding_x . 'px;';
if ( $margin_css !== '0px 0px 0px 0px' ) {
	$section_style .= 'margin:' . $margin_css . ';';
}
$title_transform = $title_upper ? 'uppercase' : 'none';
?>
<style>
#<?php echo $uid; ?>,#<?php echo $uid; ?> *{box-sizing:border-box;}
#<?php echo $uid; ?> .csstrip__inner{max-width:<?php echo $max_width; ?>px;margin:0 auto;}
#<?php echo $uid; ?> .csstrip__list{display:flex;flex-direction:column;gap:<?php echo $gap; ?>px;}
#<?php echo $uid; ?> .csstrip__row{
  display:flex;align-items:center;
  gap:<?php echo max( 12, (int) round( $row_padding_x * 0.75 ) ); ?>px;
  background:<?php echo $css( $row_bg ); ?>;
  color:<?php echo $css( $row_text_color ); ?>;
  border:1px solid <?php echo $css( $row_border_color ); ?>;
  border-radius:<?php echo $row_radius; ?>px;
  padding:<?php echo $row_padding_y; ?>px <?php echo $row_padding_x; ?>px;
  box-shadow:<?php echo $css( $row_shadow ); ?>;
  text-decoration:none;
  transition:transform .15s,box-shadow .15s;
}
#<?php echo $uid; ?> a.csstrip__row:hover{
  transform:translateY(-2px);
  box-shadow:<?php echo $css( $row_shadow ); ?>,0 10px 28px -10px rgba(10,37,64,.22);
}
#<?php echo $uid; ?> .csstrip__row--feat{
  background:<?php echo $css( $feat_bg ); ?>;
  color:<?php echo $css( $feat_text_color ); ?>;
  border-color:transparent;
  box-shadow:none;
}
#<?php echo $uid; ?> .csstrip__badge{
  flex:0 0 auto;
  min-width:<?php echo $badge_min_width; ?>px;
  display:inline-flex;align-items:center;justify-content:center;
  padding:6px 14px;
  border-radius:<?php echo $badge_radius; ?>px;
  border:1.5px solid <?php echo $css( $badge_border ); ?>;
  background:<?php echo $css( $badge_bg ); ?>;
  color:<?php echo $css( $badge_color ); ?>;
  font-size:<?php echo $badge_size; ?>px;
  font-weight:<?php echo $badge_weight; ?>;
  letter-spacing:.04em;
  white-space:nowrap;
}
#<?php echo $uid; ?> .csstrip__row--feat .csstrip__badge{
  background:<?php echo $css( $feat_badge_bg ); ?>;
  color:<?php echo $css( $feat_badge_color ); ?>;
  border-color:<?php echo $css( $feat_badge_border ); ?>;
}
#<?php echo $uid; ?> .csstrip__body{flex:1 1 0;min-width:0;}
#<?php echo $uid; ?> .csstrip__title{
  margin:0;padding:0;
  font-size:<?php echo $title_size; ?>px;
  font-weight:<?php echo $title_weight; ?>;
  line-height:1.1;
  letter-spacing:-.01em;
  text-transform:<?php echo $title_transform; ?>;
  color:inherit;
}
#<?php echo $uid; ?> .csstrip__stats{
  margin:5px 0 0;padding:0;
  font-size:<?php echo $stats_size; ?>px;
  color:<?php echo $css( $stats_color ); ?>;
  line-height:1.4;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#<?php echo $uid; ?> .csstrip__row--feat .csstrip__stats{
  color:<?php echo $css( $feat_stats_color ); ?>;
}
#<?php echo $uid; ?> .csstrip__right{
  flex:0 0 auto;
  text-align:right;
  min-width:80px;
}
#<?php echo $uid; ?> .csstrip__right-label{
  display:block;
  font-size:<?php echo $right_label_size; ?>px;
  font-weight:700;
  letter-spacing:.1em;
  text-transform:uppercase;
  color:<?php echo $css( $right_label_color ); ?>;
  margin-bottom:2px;
}
#<?php echo $uid; ?> .csstrip__row--feat .csstrip__right-label{
  color:<?php echo $css( $feat_right_label_color ); ?>;
}
#<?php echo $uid; ?> .csstrip__right-value{
  display:block;
  font-size:<?php echo $right_value_size; ?>px;
  font-weight:<?php echo $right_value_weight; ?>;
  line-height:1;
  color:<?php echo $css( $right_value_color ); ?>;
  letter-spacing:-.02em;
}
#<?php echo $uid; ?> .csstrip__row--feat .csstrip__right-value{
  color:<?php echo $css( $feat_right_value_color ); ?>;
}
@media(max-width:640px){
  #<?php echo $uid; ?> .csstrip__row{flex-wrap:wrap;gap:10px;padding:<?php echo (int)round($row_padding_y*.8); ?>px <?php echo (int)round($row_padding_x*.8); ?>px;}
  #<?php echo $uid; ?> .csstrip__right{flex:0 0 100%;text-align:left;display:flex;align-items:baseline;gap:8px;}
  #<?php echo $uid; ?> .csstrip__right-label{font-size:10px;align-self:center;}
  #<?php echo $uid; ?> .csstrip__right-value{font-size:<?php echo max(18,(int)round($right_value_size*.75)); ?>px;}
  #<?php echo $uid; ?> .csstrip__title{font-size:<?php echo max(16,(int)round($title_size*.85)); ?>px;}
}
</style>
<section id="<?php echo $uid; ?>" style="<?php echo esc_attr( $section_style ); ?>">
	<div class="csstrip__inner">
		<div class="csstrip__list">
		<?php foreach ( $corsi as $idx => $post ) :
			$id      = $post->ID;
			$is_feat = $featured_enabled && ( $idx === $featured_index );

			$livelli     = wp_get_post_terms( $id, 'calypso_livello' );
			$livello_obj = ! is_wp_error( $livelli ) && ! empty( $livelli ) ? $livelli[0] : null;
			$livello     = $livello_obj ? $livello_obj->name : '';

			$stats = [];
			if ( $show_stat_durata ) {
				$v = (string) get_post_meta( $id, '_corso_stat_durata', true );
				if ( $v !== '' ) $stats[] = $v;
			}
			if ( $show_stat_pratica ) {
				$v = (string) get_post_meta( $id, '_corso_stat_pratica', true );
				if ( $v !== '' ) $stats[] = $v;
			}
			if ( $show_stat_profondita ) {
				$v = (string) get_post_meta( $id, '_corso_stat_profondita', true );
				if ( $v !== '' ) $stats[] = $v;
			}
			if ( $show_stat_badge ) {
				$v = (string) get_post_meta( $id, '_corso_badge', true );
				if ( $v !== '' ) $stats[] = $v;
			}

			$right_raw = $right_meta_key ? (string) get_post_meta( $id, $right_meta_key, true ) : '';
			$right_val = $right_unit !== '' ? trim( $right_raw . ' ' . $right_unit ) : $right_raw;

			$row_tag   = $show_link ? 'a' : 'div';
			$row_class = 'csstrip__row' . ( $is_feat ? ' csstrip__row--feat' : '' );
		?>
		<<?php echo $row_tag; ?> class="<?php echo esc_attr( $row_class ); ?>"
			<?php if ( $show_link ) : ?>href="<?php echo esc_url( get_permalink( $id ) ); ?>"<?php if ( $link_target === '_blank' ) : ?> target="_blank" rel="noopener"<?php endif; ?><?php endif; ?>>
			<?php if ( $show_badge && $livello !== '' ) : ?>
			<span class="csstrip__badge"><?php echo esc_html( $livello ); ?></span>
			<?php endif; ?>
			<div class="csstrip__body">
				<p class="csstrip__title"><?php echo esc_html( $post->post_title ); ?></p>
				<?php if ( $show_stats && ! empty( $stats ) ) : ?>
				<p class="csstrip__stats"><?php echo esc_html( implode( ' · ', $stats ) ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $show_right && ( $right_label !== '' || $right_val !== '' ) ) : ?>
			<div class="csstrip__right">
				<?php if ( $right_label !== '' ) : ?>
				<span class="csstrip__right-label"><?php echo esc_html( $right_label ); ?></span>
				<?php endif; ?>
				<?php if ( $right_val !== '' ) : ?>
				<span class="csstrip__right-value"><?php echo esc_html( $right_val ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</<?php echo $row_tag; ?>>
		<?php endforeach; ?>
		</div>
	</div>
</section>
