<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/storia-club — timeline storica del club.
 *
 * Lista articoli (post) filtrati per categoria/tag/data/anno o selezione
 * manuale, renderizzata come timeline orizzontale (desktop) o verticale
 * (mobile), con anno/titolo/testo risolti con priorità configurabile.
 */

$a = $attributes ?? [];

$columns          = max( 1, (int) ( $a['columns'] ?? 5 ) );
$gap              = (int) ( $a['gap'] ?? 32 );
$max_width        = (int) ( $a['max_width'] ?? 1320 );
$bg_color         = (string) ( $a['bg_color'] ?? '' );
$desktop_overflow = (string) ( $a['desktop_overflow'] ?? 'hide' );
$padding_y        = (int) ( $a['padding_y'] ?? 0 );
$padding_x        = (int) ( $a['padding_x'] ?? 0 );

$title_source = (string) ( $a['title_source'] ?? 'post_title' );
$text_source  = (string) ( $a['text_source'] ?? 'excerpt' );
$clickable    = (bool) ( $a['clickable'] ?? true );
$link_new_tab = (bool) ( $a['link_new_tab'] ?? false );

$dot_size       = max( 4, (int) ( $a['dot_size'] ?? 12 ) );
$line_thickness = max( 1, (int) ( $a['line_thickness'] ?? 1 ) );

$gap_dot_year   = (int) ( $a['gap_dot_year'] ?? 18 );
$gap_year_title = (int) ( $a['gap_year_title'] ?? 8 );
$gap_title_text = (int) ( $a['gap_title_text'] ?? 6 );

$year_size           = (int) ( $a['year_size'] ?? 36 );
$year_font_weight    = (int) ( $a['year_font_weight'] ?? 800 );
$year_letter_spacing = (int) ( $a['year_letter_spacing'] ?? 0 );
$year_font           = (string) ( $a['year_font'] ?? '' );

$title_size           = (int) ( $a['title_size'] ?? 18 );
$title_font_weight    = (int) ( $a['title_font_weight'] ?? 800 );
$title_letter_spacing = (int) ( $a['title_letter_spacing'] ?? 0 );
$title_transform      = (string) ( $a['title_transform'] ?? 'uppercase' );
$title_font           = (string) ( $a['title_font'] ?? '' );

$text_size        = (int) ( $a['text_size'] ?? 13 );
$text_font_weight = (int) ( $a['text_font_weight'] ?? 400 );
$text_line_height = (int) ( $a['text_line_height'] ?? 150 );

$safe_css_color = function ( $value, $default ) {
	$value = trim( (string) $value );
	return preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,%\s]+\)|hsla?\([0-9.,%\s]+\)|[a-zA-Z]+)$/', $value ) ? $value : $default;
};

$line_color     = $safe_css_color( $a['line_color'] ?? 'rgba(95,184,200,.25)', 'rgba(95,184,200,.25)' );
$dot_color      = $safe_css_color( $a['dot_color'] ?? '#5FB8C8', '#5FB8C8' );
$dot_color_last = $safe_css_color( $a['dot_color_last'] ?? '#FF6B4A', '#FF6B4A' );
$year_color     = $safe_css_color( $a['year_color'] ?? '#5FB8C8', '#5FB8C8' );
$title_color    = $safe_css_color( $a['title_color'] ?? '#ffffff', '#ffffff' );
$text_color     = $safe_css_color( $a['text_color'] ?? '#ffffff', '#ffffff' );
$bg_color_safe  = $bg_color !== '' ? $safe_css_color( $bg_color, '' ) : '';

$items = Calypsosub_Storia_Helpers::get_items( $a );

if ( empty( $items ) ) {
	return;
}

if ( $desktop_overflow === 'hide' && count( $items ) > $columns ) {
	$items = array_slice( $items, 0, $columns );
}

$last_index = count( $items ) - 1;

/*
 * In modalità "hide" la riga è display:inline-flex (si restringe al
 * contenuto per poter essere centrata) — una flex-basis percentuale non
 * funziona su un contenitore a larghezza automatica, serve un valore in
 * px. Possibile solo se max_width è impostato; altrimenti si ricade sulla
 * riga a larghezza piena (niente centratura, ma niente bug di layout).
 */
$can_center = $desktop_overflow === 'hide' && $max_width > 0;
$item_width = $can_center
	? ( (int) floor( ( $max_width - max( 0, $columns - 1 ) * $gap ) / $columns ) ) . 'px'
	: 'calc((100% - ' . max( 0, $columns - 1 ) . ' * ' . $gap . 'px) / ' . $columns . ')';

/*
 * Pallino e linea: il pallino è posizionato in modo assoluto rispetto
 * all'item (top:0) così la sua altezza non dipende dal flusso normale, e
 * la linea (::before della riga) è centrata esattamente sulla sua metà
 * (top: dot_size/2). Il pallino ha z-index superiore ed esplicito
 * "position", quindi viene sempre dipinto sopra la linea (un elemento
 * "static" verrebbe invece dipinto sotto un ::before assoluto, bug
 * osservato nella prima versione).
 */
/*
 * Ordine verticale: pallino → spazio (gap_dot_year) → linea → stesso
 * spazio → testo. La linea sta esattamente a metà strada fra il fondo del
 * pallino e l'inizio dell'anno, non al centro del pallino.
 */
$line_top      = $dot_size + $gap_dot_year;
$content_start = $line_top + $line_thickness + $gap_dot_year;

$mobile_dot_size = max( 4, (int) round( $dot_size * 0.75 ) );

$uid = 'cso-storia-' . sprintf( '%08x', crc32( implode( ',', [ $max_width, $columns, $gap, count( $items ), $desktop_overflow, $dot_size, $gap_dot_year ] ) ) );
?>
<style>
#<?php echo $uid; ?> .cso-storia__wrap{max-width:<?php echo $max_width > 0 ? $max_width . 'px' : 'none'; ?>;width:100%;margin:0 auto;padding:<?php echo $padding_y; ?>px <?php echo $padding_x; ?>px;}
<?php if ( $can_center ) : ?>
#<?php echo $uid; ?> .cso-storia__wrap{display:flex;justify-content:center;}
#<?php echo $uid; ?> .cso-storia__row{display:inline-flex;gap:<?php echo $gap; ?>px;position:relative;}
<?php else : ?>
#<?php echo $uid; ?> .cso-storia__row{display:flex;gap:<?php echo $gap; ?>px;<?php echo $desktop_overflow === 'hide' ? 'overflow-x:hidden;justify-content:center;' : 'overflow-x:auto;'; ?>position:relative;}
<?php endif; ?>
#<?php echo $uid; ?> .cso-storia__row::before{content:'';position:absolute;left:0;right:0;top:<?php echo $line_top; ?>px;height:<?php echo $line_thickness; ?>px;background:<?php echo esc_attr( $line_color ); ?>;z-index:1;}
#<?php echo $uid; ?> .cso-storia__item{flex:0 0 <?php echo $item_width; ?>;position:relative;text-decoration:none;color:inherit;display:block;padding-top:<?php echo $content_start; ?>px;}
#<?php echo $uid; ?> .cso-storia__dot{display:block;position:absolute;top:0;left:0;width:<?php echo $dot_size; ?>px;height:<?php echo $dot_size; ?>px;border-radius:50%;background:<?php echo esc_attr( $dot_color ); ?>;z-index:2;}
#<?php echo $uid; ?> .cso-storia__dot--last{background:<?php echo esc_attr( $dot_color_last ); ?>;box-shadow:0 0 0 6px <?php echo esc_attr( $dot_color_last ); ?>33;}
#<?php echo $uid; ?> .cso-storia__year{font-size:<?php echo $year_size; ?>px;font-weight:<?php echo $year_font_weight; ?>;letter-spacing:<?php echo $year_letter_spacing / 100; ?>em;line-height:1.05;color:<?php echo esc_attr( $year_color ); ?>;margin:0 0 <?php echo $gap_year_title; ?>px;<?php echo $year_font !== '' ? 'font-family:' . esc_attr( $year_font ) . ';' : ''; ?>}
#<?php echo $uid; ?> .cso-storia__title{font-size:<?php echo $title_size; ?>px;font-weight:<?php echo $title_font_weight; ?>;letter-spacing:<?php echo $title_letter_spacing / 100; ?>em;line-height:1.2;text-transform:<?php echo esc_attr( $title_transform ); ?>;color:<?php echo esc_attr( $title_color ); ?>;margin:0 0 <?php echo $gap_title_text; ?>px;<?php echo $title_font !== '' ? 'font-family:' . esc_attr( $title_font ) . ';' : ''; ?>}
#<?php echo $uid; ?> .cso-storia__text{font-size:<?php echo $text_size; ?>px;font-weight:<?php echo $text_font_weight; ?>;line-height:<?php echo $text_line_height / 100; ?>;color:<?php echo esc_attr( $text_color ); ?>;opacity:.7;margin:0;}
<?php if ( $clickable ) : ?>
#<?php echo $uid; ?> .cso-storia__item{cursor:pointer;}
<?php endif; ?>
@media(max-width:1024px){
	#<?php echo $uid; ?> .cso-storia__wrap{display:block;}
	#<?php echo $uid; ?> .cso-storia__row{display:flex;flex-direction:column;gap:16px;overflow-x:visible;padding-top:0;border-left:<?php echo $line_thickness; ?>px solid <?php echo esc_attr( $line_color ); ?>;padding-left:20px;}
	#<?php echo $uid; ?> .cso-storia__row::before{content:none;}
	#<?php echo $uid; ?> .cso-storia__item{flex:none;padding-top:0;padding-left:0;}
	#<?php echo $uid; ?> .cso-storia__dot{position:absolute;left:-<?php echo 20 + (int) round( $mobile_dot_size / 2 ); ?>px;top:6px;width:<?php echo $mobile_dot_size; ?>px;height:<?php echo $mobile_dot_size; ?>px;}
	#<?php echo $uid; ?> .cso-storia__year{display:inline;font-size:<?php echo $title_size; ?>px;}
	#<?php echo $uid; ?> .cso-storia__title{display:inline;font-size:<?php echo $title_size; ?>px;}
}
</style>
<section <?php echo $bg_color_safe ? 'style="background:' . esc_attr( $bg_color_safe ) . ';"' : ''; ?> id="<?php echo $uid; ?>">
	<div class="cso-storia__wrap">
		<div class="cso-storia__row">
			<?php foreach ( $items as $index => $post ) :
				$is_last = ( $index === $last_index );
				$year    = Calypsosub_Storia_Helpers::resolve_year( $post->ID );
				$title   = Calypsosub_Storia_Helpers::resolve_title( $post->ID, $title_source );
				$text    = Calypsosub_Storia_Helpers::resolve_text( $post->ID, $text_source );
				$tag     = $clickable ? 'a' : 'div';
			?>
			<<?php echo $tag; ?> class="cso-storia__item"
				<?php if ( $clickable ) : ?>
				href="<?php echo esc_url( get_permalink( $post ) ); ?>"
				<?php if ( $link_new_tab ) : ?>target="_blank" rel="noopener"<?php endif; ?>
				<?php endif; ?>
			>
				<span class="cso-storia__dot<?php echo $is_last ? ' cso-storia__dot--last' : ''; ?>"></span>
				<div class="cso-storia__year"><?php echo esc_html( (string) $year ); ?></div>
				<div class="cso-storia__title"><?php echo esc_html( $title ); ?></div>
				<?php if ( $text ) : ?>
				<div class="cso-storia__text"><?php echo esc_html( $text ); ?></div>
				<?php endif; ?>
			</<?php echo $tag; ?>>
			<?php endforeach; ?>
		</div>
	</div>
</section>
