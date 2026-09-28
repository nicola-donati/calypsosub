<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Block calypso/nav-menu — barra di navigazione con logo, link, blocco
 * Accedi/Esci e pulsanti personalizzati, organizzati in 3 gruppi
 * (sinistra/centro/destra) disposti su griglia — il gruppo centrale resta
 * centrato indipendentemente dalla larghezza degli altri due.
 *
 * Ogni gruppo ha il proprio layout (riga o colonna), allineamento, gap,
 * padding e margin — non per singolo elemento ma per l'intero gruppo.
 * Ogni singolo elemento resta nascondibile per viewport (desktop/tablet/
 * mobile, stessi breakpoint usati in tutto il plugin: >1024px desktop,
 * 761–1024px tablet, ≤760px mobile) e assegnabile a uno dei 3 gruppi.
 *
 * Sotto il breakpoint scelto in "hamburger_breakpoint" l'intera barra si
 * nasconde e viene sostituita da un'icona hamburger che apre un pannello
 * laterale con tutti gli elementi in un semplice elenco verticale (i
 * gruppi hanno senso solo nella barra orizzontale, non nel pannello).
 */

$a = $attributes ?? [];

/* ── Logo ── */
$logo_id           = (int) ( $a['logo_id'] ?? 0 );
$logo_alt          = (string) ( $a['logo_alt'] ?? '' );
$logo_height       = max( 12, (int) ( $a['logo_height'] ?? 32 ) );
$logo_link_home    = ! isset( $a['logo_link_home'] ) || ! empty( $a['logo_link_home'] );
$logo_group        = (string) ( $a['logo_group'] ?? '1' );
$logo_hide         = [
	'desktop' => ! empty( $a['logo_hide_desktop'] ),
	'tablet'  => ! empty( $a['logo_hide_tablet'] ),
	'mobile'  => ! empty( $a['logo_hide_mobile'] ),
];
$logo_src = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';

$nav_links = array_values( array_filter( (array) ( $a['nav_links'] ?? [] ), static fn( $it ) => is_array( $it ) && ( $it['label'] ?? '' ) !== '' ) );
$buttons   = array_values( array_filter( (array) ( $a['buttons']   ?? [] ), static fn( $it ) => is_array( $it ) && ( $it['label'] ?? '' ) !== '' ) );

$show_login_logout = ! empty( $a['show_login_logout'] );
$login_label        = (string) ( $a['login_label']  ?: __( 'Accedi', 'calypsosub' ) );
$logout_label       = (string) ( $a['logout_label'] ?: __( 'Esci', 'calypsosub' ) );
$ll_hide             = [
	'desktop' => ! empty( $a['login_logout_hide_desktop'] ),
	'tablet'  => ! empty( $a['login_logout_hide_tablet'] ),
	'mobile'  => ! empty( $a['login_logout_hide_mobile'] ),
];
$login_logout_group = (string) ( $a['login_logout_group'] ?? '1' );

$hamburger_breakpoint = in_array( $a['hamburger_breakpoint'] ?? 'mobile', [ 'none', 'tablet', 'mobile' ], true )
	? $a['hamburger_breakpoint']
	: 'mobile';
$sidebar_side = ( $a['sidebar_side'] ?? 'right' ) === 'left' ? 'left' : 'right';

$gap               = (int)    ( $a['gap']         ?? 24 );
$min_height        = (int)    ( $a['min_height']  ?? 0 );
$block_overlay     = ! empty( $a['block_overlay'] );
$padding_y         = (int)    ( $a['padding_y']    ?? 0 );
$padding_x         = (int)    ( $a['padding_x']    ?? 0 );
$margin_top        = (int)    ( $a['margin_top']    ?? 0 );
$margin_right      = (int)    ( $a['margin_right']  ?? 0 );
$margin_bottom     = (int)    ( $a['margin_bottom'] ?? 0 );
$margin_left       = (int)    ( $a['margin_left']   ?? 0 );
$link_color        = (string) ( $a['link_color']       ?: '#0b1a26' );
$link_hover_color  = (string) ( $a['link_hover_color'] ?: '#1B77A7' );
$link_size         = (int)    ( $a['link_size']   ?? 15 );
$link_weight       = (int)    ( $a['link_weight'] ?? 600 );
$link_font         = preg_replace( '/[^a-zA-Z0-9 ,\"\'\-]/', '', (string) ( $a['link_font'] ?? '' ) );
$link_upper        = ! empty( $a['link_upper'] );
$link_italic       = ! empty( $a['link_italic'] );
$link_decoration   = in_array( $a['link_decoration'] ?? 'none', [ 'none', 'underline', 'line-through', 'overline' ], true )
	? $a['link_decoration']
	: 'none';
$link_letter_spacing = (int) ( $a['link_letter_spacing'] ?? 0 );

$btn_primary_bg      = (string) ( $a['btn_primary_bg']      ?: '#ff6b4a' );
$btn_primary_color   = (string) ( $a['btn_primary_color']   ?: '#ffffff' );
$btn_secondary_bg    = (string) ( $a['btn_secondary_bg']    ?: '#061826' );
$btn_secondary_color = (string) ( $a['btn_secondary_color'] ?: '#ffffff' );

$hamburger_color       = (string) ( $a['hamburger_color']       ?: '#0b1a26' );
$sidebar_bg_color      = (string) ( $a['sidebar_bg_color']      ?: '#ffffff' );
$sidebar_text_color    = (string) ( $a['sidebar_text_color']    ?: '#0b1a26' );
$sidebar_overlay_color = (string) ( $a['sidebar_overlay_color'] ?: 'rgba(6,24,38,.6)' );

/* ── Gruppi: layout, allineamento, spaziatura interna/esterna ── */
$valid_dir   = [ 'row', 'column' ];
$valid_align = [ 'flex-start', 'center', 'flex-end', 'space-between' ];
$groups_cfg  = [];
foreach ( [ '1', '2', '3' ] as $n ) {
	$def_align = [ '1' => 'flex-start', '2' => 'center', '3' => 'flex-end' ][ $n ];
	$groups_cfg[ $n ] = [
		'direction' => in_array( $a[ "group{$n}_direction" ] ?? 'row', $valid_dir, true ) ? $a[ "group{$n}_direction" ] : 'row',
		'align'     => in_array( $a[ "group{$n}_align" ] ?? $def_align, $valid_align, true ) ? $a[ "group{$n}_align" ] : $def_align,
		'gap'       => (int) ( $a[ "group{$n}_gap" ] ?? 16 ),
		'padding_y' => (int) ( $a[ "group{$n}_padding_y" ] ?? 0 ),
		'padding_x' => (int) ( $a[ "group{$n}_padding_x" ] ?? 0 ),
		'margin'    => [
			(int) ( $a[ "group{$n}_margin_top" ]    ?? 0 ),
			(int) ( $a[ "group{$n}_margin_right" ]  ?? 0 ),
			(int) ( $a[ "group{$n}_margin_bottom" ] ?? 0 ),
			(int) ( $a[ "group{$n}_margin_left" ]   ?? 0 ),
		],
	];
}

if ( ! $logo_src && empty( $nav_links ) && ! $show_login_logout && empty( $buttons ) ) {
	return;
}

$uid = 'cso-nav-' . sprintf( '%08x', crc32( wp_json_encode( $a ) ) );

/* ── Accedi/Esci: URL con redirect alla pagina corrente ── */
$current_url = home_url( add_query_arg( null, null ) );
if ( is_user_logged_in() ) {
	$ll_url   = wp_logout_url( $current_url );
	$ll_label = $logout_label;
} else {
	$ll_url   = wp_login_url( $current_url );
	$ll_label = $login_label;
}

/* ── Elenco elementi da renderizzare, in ordine: logo, link, accedi/esci,
 *    pulsanti. Ogni elemento porta con sé il gruppo a cui appartiene
 *    (usato solo nella barra orizzontale, ignorato nel pannello laterale
 *    dove viene sempre reso come elenco verticale piatto). ── */
$normalize_group = static function ( $g ): string {
	return in_array( (string) $g, [ '1', '2', '3' ], true ) ? (string) $g : '1';
};

$items = [];

if ( $logo_src ) {
	$items[] = [ 'type' => 'logo', 'group' => $normalize_group( $logo_group ), 'hide' => $logo_hide ];
}
foreach ( $nav_links as $link ) {
	$items[] = [
		'type'  => 'link',
		'group' => $normalize_group( $link['group'] ?? '1' ),
		'hide'  => [
			'desktop' => ! empty( $link['hide_desktop'] ),
			'tablet'  => ! empty( $link['hide_tablet'] ),
			'mobile'  => ! empty( $link['hide_mobile'] ),
		],
		'data'  => $link,
	];
}
if ( $show_login_logout ) {
	$items[] = [ 'type' => 'login', 'group' => $normalize_group( $login_logout_group ), 'hide' => $ll_hide ];
}
foreach ( $buttons as $btn ) {
	$items[] = [
		'type'  => 'button',
		'group' => $normalize_group( $btn['group'] ?? '1' ),
		'hide'  => [
			'desktop' => ! empty( $btn['hide_desktop'] ),
			'tablet'  => ! empty( $btn['hide_tablet'] ),
			'mobile'  => ! empty( $btn['hide_mobile'] ),
		],
		'data'  => $btn,
	];
}

/* ── Classi di visibilità per viewport ── */
$vis_class = static function ( array $hide ): string {
	$classes = [];
	if ( ! empty( $hide['desktop'] ) ) $classes[] = 'cso-nav__item--hide-desktop';
	if ( ! empty( $hide['tablet'] ) )  $classes[] = 'cso-nav__item--hide-tablet';
	if ( ! empty( $hide['mobile'] ) )  $classes[] = 'cso-nav__item--hide-mobile';
	return implode( ' ', $classes );
};

/* ── Rende un singolo elemento (logo/link/accedi-esci/pulsante). ── */
$emit_item = function ( array $item ) use ( $vis_class, $logo_src, $logo_alt, $logo_height, $logo_link_home, $ll_url, $ll_label ) {
	$hide_class = $vis_class( $item['hide'] );

	switch ( $item['type'] ) {
		case 'logo':
			$img = '<img class="cso-nav__logo-img" src="' . esc_url( $logo_src ) . '" alt="' . esc_attr( $logo_alt ) . '" style="height:' . (int) $logo_height . 'px;width:auto;display:block">';
			if ( $logo_link_home ) {
				echo '<a class="cso-nav__item cso-nav__logo ' . esc_attr( $hide_class ) . '" href="' . esc_url( home_url( '/' ) ) . '">' . $img . '</a>';
			} else {
				echo '<span class="cso-nav__item cso-nav__logo ' . esc_attr( $hide_class ) . '">' . $img . '</span>';
			}
			break;

		case 'link':
			$link   = $item['data'];
			$target = ! empty( $link['new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
			echo '<a class="cso-nav__item cso-nav__link ' . esc_attr( $hide_class ) . '" href="' . esc_url( (string) ( $link['url'] ?? '' ) ) . '"' . $target . '>'
				. esc_html( (string) ( $link['label'] ?? '' ) )
				. '</a>';
			break;

		case 'login':
			echo '<a class="cso-nav__item cso-nav__login ' . esc_attr( $hide_class ) . '" href="' . esc_url( $ll_url ) . '">'
				. esc_html( $ll_label )
				. '</a>';
			break;

		case 'button':
			$btn    = $item['data'];
			$style  = ( $btn['style'] ?? 'primary' ) === 'secondary' ? 'secondary' : 'primary';
			$target = ! empty( $btn['new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
			echo '<a class="cso-nav__item cso-nav__btn cso-nav__btn--' . esc_attr( $style ) . ' ' . esc_attr( $hide_class ) . '" href="' . esc_url( (string) ( $btn['url'] ?? '' ) ) . '"' . $target . '>'
				. esc_html( (string) ( $btn['label'] ?? '' ) )
				. '</a>';
			break;
	}
};

/* ── Suddivisione per gruppo, solo per la barra orizzontale ── */
$buckets = [ '1' => [], '2' => [], '3' => [] ];
foreach ( $items as $it ) {
	$buckets[ $it['group'] ][] = $it;
}
?>
<style>
#<?php echo $uid; ?>{position:relative;padding:<?php echo $padding_y; ?>px <?php echo $padding_x; ?>px;margin:<?php echo $margin_top; ?>px <?php echo $margin_right; ?>px <?php echo $margin_bottom; ?>px <?php echo $margin_left; ?>px}
<?php if ( $block_overlay ) : ?>
#<?php echo $uid; ?>-shell{position:relative;height:0;overflow:visible}
#<?php echo $uid; ?>{position:absolute;top:0;left:0;right:0;z-index:20}
<?php endif; ?>
#<?php echo $uid; ?> .cso-nav__bar{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:<?php echo $gap; ?>px<?php echo $min_height > 0 ? ';min-height:' . $min_height . 'px' : ''; ?>}
<?php foreach ( $groups_cfg as $n => $g ) : ?>
#<?php echo $uid; ?> .cso-nav__group--<?php echo $n; ?>{
	display:flex;
	flex-direction:<?php echo esc_attr( $g['direction'] ); ?>;
	align-items:center;
	justify-content:<?php echo esc_attr( $g['align'] ); ?>;
	flex-wrap:wrap;
	gap:<?php echo $g['gap']; ?>px;
	padding:<?php echo $g['padding_y']; ?>px <?php echo $g['padding_x']; ?>px;
	margin:<?php echo implode( 'px ', $g['margin'] ); ?>px;
}
<?php endforeach; ?>
#<?php echo $uid; ?> .cso-nav__logo-img{object-fit:contain}
#<?php echo $uid; ?> .cso-nav__link{
	color:<?php echo esc_attr( $link_color ); ?>;
	font-size:<?php echo $link_size; ?>px;
	font-weight:<?php echo $link_weight; ?>;
	text-decoration:<?php echo esc_attr( $link_decoration ); ?>;
	text-transform:<?php echo $link_upper ? 'uppercase' : 'none'; ?>;
	font-style:<?php echo $link_italic ? 'italic' : 'normal'; ?>;
	letter-spacing:<?php echo $link_letter_spacing / 100; ?>em;
	<?php if ( $link_font !== '' ) : ?>font-family:<?php echo $link_font; ?>;<?php endif; ?>
	transition:color .15s;
}
#<?php echo $uid; ?> .cso-nav__link:hover{color:<?php echo esc_attr( $link_hover_color ); ?>}
#<?php echo $uid; ?> .cso-nav__login{color:<?php echo esc_attr( $link_color ); ?>;font-size:<?php echo $link_size; ?>px;font-weight:<?php echo $link_weight; ?>;text-decoration:none;border:1px solid currentColor;border-radius:999px;padding:6px 16px;transition:color .15s,border-color .15s}
#<?php echo $uid; ?> .cso-nav__login:hover{color:<?php echo esc_attr( $link_hover_color ); ?>}
#<?php echo $uid; ?> .cso-nav__btn{display:inline-flex;align-items:center;font-size:<?php echo $link_size; ?>px;font-weight:700;text-decoration:none;border-radius:999px;padding:8px 20px;transition:filter .15s}
#<?php echo $uid; ?> .cso-nav__btn:hover{filter:brightness(.92)}
#<?php echo $uid; ?> .cso-nav__btn--primary{background:<?php echo esc_attr( $btn_primary_bg ); ?>;color:<?php echo esc_attr( $btn_primary_color ); ?>}
#<?php echo $uid; ?> .cso-nav__btn--secondary{background:<?php echo esc_attr( $btn_secondary_bg ); ?>;color:<?php echo esc_attr( $btn_secondary_color ); ?>}

#<?php echo $uid; ?> .cso-nav__hamburger{display:none;align-items:center;justify-content:center;width:40px;height:40px;border:none;background:transparent;cursor:pointer;padding:0}
#<?php echo $uid; ?> .cso-nav__hamburger span,
#<?php echo $uid; ?> .cso-nav__hamburger span::before,
#<?php echo $uid; ?> .cso-nav__hamburger span::after{content:'';display:block;width:22px;height:2px;background:<?php echo esc_attr( $hamburger_color ); ?>;transition:transform .2s,opacity .2s}
#<?php echo $uid; ?> .cso-nav__hamburger span{position:relative}
#<?php echo $uid; ?> .cso-nav__hamburger span::before{position:absolute;top:-7px}
#<?php echo $uid; ?> .cso-nav__hamburger span::after{position:absolute;top:7px}
#<?php echo $uid; ?> .cso-nav__hamburger[aria-expanded="true"] span{background:transparent}
#<?php echo $uid; ?> .cso-nav__hamburger[aria-expanded="true"] span::before{transform:rotate(45deg);top:0}
#<?php echo $uid; ?> .cso-nav__hamburger[aria-expanded="true"] span::after{transform:rotate(-45deg);top:0}

#<?php echo $uid; ?> .cso-nav__overlay{position:fixed;inset:0;background:<?php echo esc_attr( $sidebar_overlay_color ); ?>;opacity:0;pointer-events:none;transition:opacity .2s ease;z-index:9998}
#<?php echo $uid; ?> .cso-nav__overlay.is-open{opacity:1;pointer-events:auto}
#<?php echo $uid; ?> .cso-nav__sidebar{
	position:fixed;top:0;<?php echo esc_attr( $sidebar_side ); ?>:0;height:100%;width:min(86vw,340px);
	background:<?php echo esc_attr( $sidebar_bg_color ); ?>;color:<?php echo esc_attr( $sidebar_text_color ); ?>;
	box-shadow:0 0 40px rgba(0,0,0,.25);z-index:9999;overflow-y:auto;
	padding:24px;display:flex;flex-direction:column;gap:18px;align-items:flex-start;
	transform:translateX(<?php echo $sidebar_side === 'left' ? '-105%' : '105%'; ?>);transition:transform .25s ease;
}
#<?php echo $uid; ?> .cso-nav__sidebar.is-open{transform:translateX(0)}
#<?php echo $uid; ?> .cso-nav__sidebar .cso-nav__link,
#<?php echo $uid; ?> .cso-nav__sidebar .cso-nav__login{color:<?php echo esc_attr( $sidebar_text_color ); ?>}
#<?php echo $uid; ?> .cso-nav__sidebar-close{align-self:flex-end;background:none;border:none;font-size:26px;line-height:1;cursor:pointer;color:<?php echo esc_attr( $sidebar_text_color ); ?>;padding:4px}

<?php
/* Visibilità per viewport — 3 fasce coerenti col resto del plugin */
$breakpoints_css = "
#{$uid} .cso-nav__item--hide-mobile{}
@media(max-width:760px){
	#{$uid} .cso-nav__item--hide-mobile{display:none!important}
}
@media(min-width:761px) and (max-width:1024px){
	#{$uid} .cso-nav__item--hide-tablet{display:none!important}
}
@media(min-width:1025px){
	#{$uid} .cso-nav__item--hide-desktop{display:none!important}
}
";
echo $breakpoints_css;

/* Breakpoint di collasso a hamburger */
if ( $hamburger_breakpoint !== 'none' ) {
	$hb_max = $hamburger_breakpoint === 'tablet' ? 1024 : 760;
	echo "@media(max-width:{$hb_max}px){\n"
		. "\t#{$uid} .cso-nav__bar{display:none}\n"
		. "\t#{$uid} .cso-nav__hamburger{display:inline-flex}\n"
		. "}\n";
}
?>
</style>

<?php if ( $block_overlay ) : ?>
<div id="<?php echo esc_attr( $uid ); ?>-shell">
<?php endif; ?>
<div class="cso-nav" id="<?php echo esc_attr( $uid ); ?>">

	<nav class="cso-nav__bar" aria-label="<?php esc_attr_e( 'Menu principale', 'calypsosub' ); ?>">
		<?php foreach ( [ '1', '2', '3' ] as $n ) : ?>
		<div class="cso-nav__group cso-nav__group--<?php echo $n; ?>">
			<?php foreach ( $buckets[ $n ] as $it ) : $emit_item( $it ); endforeach; ?>
		</div>
		<?php endforeach; ?>
	</nav>

	<?php if ( $hamburger_breakpoint !== 'none' ) : ?>
	<button type="button" class="cso-nav__hamburger" id="<?php echo esc_attr( $uid ); ?>-toggle"
	        aria-label="<?php esc_attr_e( 'Apri menu', 'calypsosub' ); ?>"
	        aria-expanded="false" aria-controls="<?php echo esc_attr( $uid ); ?>-sidebar">
		<span></span>
	</button>

	<div class="cso-nav__overlay" id="<?php echo esc_attr( $uid ); ?>-overlay"></div>

	<aside class="cso-nav__sidebar" id="<?php echo esc_attr( $uid ); ?>-sidebar" aria-hidden="true">
		<button type="button" class="cso-nav__sidebar-close" id="<?php echo esc_attr( $uid ); ?>-close"
		        aria-label="<?php esc_attr_e( 'Chiudi menu', 'calypsosub' ); ?>">&times;</button>
		<?php foreach ( $items as $it ) : $emit_item( $it ); endforeach; ?>
	</aside>

	<script>
	(function(){
		var root     = document.getElementById(<?php echo wp_json_encode( $uid ); ?>);
		var toggle   = document.getElementById(<?php echo wp_json_encode( $uid . '-toggle' ); ?>);
		var closeBtn = document.getElementById(<?php echo wp_json_encode( $uid . '-close' ); ?>);
		var overlay  = document.getElementById(<?php echo wp_json_encode( $uid . '-overlay' ); ?>);
		var sidebar  = document.getElementById(<?php echo wp_json_encode( $uid . '-sidebar' ); ?>);
		if (!root || !toggle || !overlay || !sidebar) return;

		function open(){
			sidebar.classList.add('is-open');
			overlay.classList.add('is-open');
			toggle.setAttribute('aria-expanded', 'true');
			sidebar.setAttribute('aria-hidden', 'false');
		}
		function close(){
			sidebar.classList.remove('is-open');
			overlay.classList.remove('is-open');
			toggle.setAttribute('aria-expanded', 'false');
			sidebar.setAttribute('aria-hidden', 'true');
		}
		toggle.addEventListener('click', function(){
			(sidebar.classList.contains('is-open') ? close : open)();
		});
		if (closeBtn) closeBtn.addEventListener('click', close);
		overlay.addEventListener('click', close);
		document.addEventListener('keydown', function(e){
			if (e.key === 'Escape') close();
		});
	})();
	</script>
	<?php endif; ?>

</div>
<?php if ( $block_overlay ) : ?>
</div>
<?php endif; ?>
