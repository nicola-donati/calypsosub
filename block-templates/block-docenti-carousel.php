<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$a = $attributes ?? [];

/* ── Sorgente & ordinamento ── */
$source_mode  = (string) ( $a['source_mode']  ?? 'all' );
$manual_ids   = (array)  ( $a['manual_ids']   ?? [] );
$priority_ids = (array)  ( $a['priority_ids'] ?? [] );
$order        = (string) ( $a['order']        ?? 'title' );
$max_items    = (int)    ( $a['max_items']    ?? 0 );

/* ── Comportamento ── */
$show_link          = (bool)   ( $a['show_link']          ?? true );
$link_target        = (string) ( $a['link_target']        ?? '_self' );
$show_photo_label   = (bool)   ( $a['show_photo_label']   ?? true );
$photo_label_prefix = (string) ( $a['photo_label_prefix'] ?? 'RITRATTO' );
$show_ruolo         = (bool)   ( $a['show_ruolo']         ?? true );
$show_bio           = (bool)   ( $a['show_bio']           ?? false );
$bio_lines          = (int)    ( $a['bio_lines']          ?? 2 );
$show_stats         = (bool)   ( $a['show_stats']         ?? true );
$show_stat_anno     = (bool)   ( $a['show_stat_anno']     ?? true );
$show_stat_brevetti = (bool)   ( $a['show_stat_brevetti'] ?? true );
$show_stat_spec     = (bool)   ( $a['show_stat_spec']     ?? false );
$stat_anno_label    = (string) ( $a['stat_anno_label']    ?? 'dal' );

/* ── Layout carosello ── */
$desktop_cols  = max( 1, (int) ( $a['desktop_cols']  ?? 3 ) );
$tablet_cols   = max( 1, (int) ( $a['tablet_cols']   ?? 2 ) );
$mobile_cols   = max( 1, (int) ( $a['mobile_cols']   ?? 1 ) );
$gap           = (int)    ( $a['gap']           ?? 24 );
$peek          = (int)    ( $a['peek']          ?? 40 );
$snap          = (string) ( $a['snap']          ?? 'start' );

/* ── Frecce ── */
$arrows_show      = (bool)   ( $a['arrows_show']      ?? true );
$arrows_position  = (string) ( $a['arrows_position']  ?? 'sides' );
$arrow_bg         = (string) ( $a['arrow_bg']         ?? '#ffffff' );
$arrow_color      = (string) ( $a['arrow_color']      ?? '#0a2540' );
$arrow_border     = (string) ( $a['arrow_border']     ?? 'rgba(10,37,64,.15)' );
$arrow_hover_bg   = (string) ( $a['arrow_hover_bg']   ?? '#0a2540' );
$arrow_hover_col  = (string) ( $a['arrow_hover_col']  ?? '#ffffff' );
$arrow_size       = (int)    ( $a['arrow_size']       ?? 44 );
$arrow_radius     = (int)    ( $a['arrow_radius']     ?? 999 );
$arrow_shadow     = (string) ( $a['arrow_shadow']     ?? '0 2px 12px -4px rgba(10,37,64,.25)' );
$arrow_shadow_show= (bool)   ( $a['arrow_shadow_show'] ?? true );

/* ── Sezione ── */
$bg_color      = (string) ( $a['bg_color']      ?? '' );
$max_width     = (int)    ( $a['max_width']     ?? 1320 );
$padding_y     = (int)    ( $a['padding_y']     ?? 48 );
$padding_x     = (int)    ( $a['padding_x']     ?? 24 );
$margin_top    = (int)    ( $a['margin_top']    ?? 0 );
$margin_right  = (int)    ( $a['margin_right']  ?? 0 );
$margin_bottom = (int)    ( $a['margin_bottom'] ?? 0 );
$margin_left   = (int)    ( $a['margin_left']   ?? 0 );

/* ── Card ── */
$card_bg      = (string) ( $a['card_bg']      ?? '#ffffff' );
$card_radius  = (int)    ( $a['card_radius']  ?? 16 );
$card_shadow  = (string) ( $a['card_shadow']  ?? '0 8px 28px -10px rgba(10,37,64,.18)' );
$card_border  = (string) ( $a['card_border']  ?? 'rgba(10,37,64,.06)' );
$card_body_px = (int)    ( $a['card_body_px'] ?? 20 );
$card_body_py = (int)    ( $a['card_body_py'] ?? 18 );

/* ── Foto ── */
$photo_ratio    = (string) ( $a['photo_ratio']    ?? '3/4' );
$photo_label_bg = (string) ( $a['photo_label_bg'] ?? 'rgba(10,37,64,.72)' );
$photo_label_color = (string) ( $a['photo_label_color'] ?? '#ffffff' );
$photo_label_size  = (int)    ( $a['photo_label_size']  ?? 9 );

/* ── Nome ── */
$name_color  = (string) ( $a['name_color']  ?? '#1B77A7' );
$name_size   = (int)    ( $a['name_size']   ?? 22 );
$name_weight = (int)    ( $a['name_weight'] ?? 800 );
$name_upper  = (bool)   ( $a['name_upper']  ?? true );

/* ── Soprannome ── */
$show_soprannome = (bool)   ( $a['show_soprannome'] ?? true );
$sopr_color      = (string) ( $a['sopr_color']      ?? 'rgba(10,37,64,.6)' );
$sopr_size       = (int)    ( $a['sopr_size']       ?? 15 );

/* ── Ruolo ── */
$ruolo_color  = (string) ( $a['ruolo_color']  ?? '#b9790a' );
$ruolo_size   = (int)    ( $a['ruolo_size']   ?? 15 );
$ruolo_weight = (int)    ( $a['ruolo_weight'] ?? 600 );

/* ── Bio ── */
$bio_color = (string) ( $a['bio_color'] ?? 'rgba(10,37,64,.7)' );
$bio_size  = (int)    ( $a['bio_size']  ?? 14 );

/* ── Stats ── */
$stats_color  = (string) ( $a['stats_color']  ?? 'rgba(10,37,64,.55)' );
$stats_size   = (int)    ( $a['stats_size']   ?? 12 );
$stats_sep_color = (string) ( $a['stats_sep_color'] ?? 'rgba(10,37,64,.2)' );

/* ── Font testi ── */
$name_font = (string) ( $a['name_font'] ?? '' );
$body_font = (string) ( $a['body_font'] ?? '' );

/* ── Stile testo ── */
$name_italic      = (bool)   ( $a['name_italic']      ?? false );
$name_decoration  = (string) ( $a['name_decoration']  ?? 'none' );
$ruolo_italic     = (bool)   ( $a['ruolo_italic']     ?? false );
$ruolo_decoration = (string) ( $a['ruolo_decoration'] ?? 'none' );
$sopr_decoration  = (string) ( $a['sopr_decoration']  ?? 'none' );
$bio_italic       = (bool)   ( $a['bio_italic']       ?? false );

/* ── Pallini paginazione ── */
$dots_show         = (bool)   ( $a['dots_show']         ?? true );
$dots_color        = (string) ( $a['dots_color']        ?? 'rgba(10,37,64,.2)' );
$dots_active_color = (string) ( $a['dots_active_color'] ?? '#0a2540' );
$dots_size         = (int)    ( $a['dots_size']         ?? 7 );

/* ── Autoplay ── */
$autoplay       = (bool) ( $a['autoplay']       ?? false );
$autoplay_speed = max( 500, (int) ( $a['autoplay_speed'] ?? 3000 ) );

/* ── CSS sanitization ── */
$css = static function ( string $v ): string {
	return preg_replace( '/[^#a-zA-Z0-9.,()%\s\-\/]/', '', $v );
};

/* ── Query docenti ── */
$q_args = [ 'posts_per_page' => $max_items > 0 ? $max_items : -1 ];

if ( $source_mode === 'manual' && ! empty( $manual_ids ) ) {
	$q_args['post__in'] = array_map( 'intval', $manual_ids );
	$q_args['orderby']  = 'post__in';
} else {
	if ( $order === 'date' ) {
		$q_args['orderby'] = 'date';
		$q_args['order']   = 'DESC';
	} elseif ( $order === 'menu_order' ) {
		$q_args['orderby'] = 'menu_order';
		$q_args['order']   = 'ASC';
	} elseif ( $order === 'rand' ) {
		$q_args['orderby'] = 'rand';
	} else {
		$q_args['orderby'] = 'title';
		$q_args['order']   = 'ASC';
	}
}

$docenti = calypso_get_docenti( $q_args );
if ( empty( $docenti ) ) return;

/* Priority sorting: metti prima priority_ids nell'ordine dato */
if ( ! empty( $priority_ids ) && $source_mode !== 'manual' ) {
	$prio     = array_filter( array_map( 'intval', $priority_ids ) );
	$prio_map = [];
	foreach ( $docenti as $p ) {
		if ( in_array( $p->ID, $prio, true ) ) $prio_map[ $p->ID ] = $p;
	}
	$rest = array_values( array_filter( $docenti, fn( $p ) => ! in_array( $p->ID, $prio, true ) ) );
	$ordered_prio = array_values( array_filter( array_map( fn( $id ) => $prio_map[ $id ] ?? null, $prio ) ) );
	$docenti = array_merge( $ordered_prio, $rest );
}

$uid   = 'cso-dcar-' . sprintf( '%08x', crc32( implode( ',', [ $max_width, $gap, $desktop_cols, count( $docenti ) ] ) ) );
$total = count( $docenti );

/* ── Arrow layout geometry ── */
$vp_margin       = $arrows_position === 'outside' ? ( $arrow_size + 12 ) : (int) ceil( $arrow_size / 2 );
$arrow_outer_pos = $arrows_position === 'outside' ? (int) round( ( $vp_margin - $arrow_size ) / 2 ) : -(int) ceil( $arrow_size / 2 );

/* ── Section style ── */
$section_styles = [];
if ( $bg_color ) $section_styles[] = 'background:' . $css( $bg_color );
$margin = $margin_top . 'px ' . $margin_right . 'px ' . $margin_bottom . 'px ' . $margin_left . 'px';
if ( $margin !== '0px 0px 0px 0px' ) $section_styles[] = 'margin:' . $margin;
$section_style_attr = $section_styles ? ' style="' . esc_attr( implode( ';', $section_styles ) ) . '"' : '';

/* ── Photo ratio to padding-top % ── */
$ratio_parts = array_map( 'intval', explode( '/', str_replace( ':', '/', $photo_ratio ) ) );
$ratio_pct   = ( isset( $ratio_parts[0] ) && $ratio_parts[0] > 0 )
	? round( ( ( $ratio_parts[1] ?? 4 ) / $ratio_parts[0] ) * 100, 2 )
	: 133.33;
?>
<style>
#<?php echo $uid; ?>,#<?php echo $uid; ?> *{box-sizing:border-box;}
#<?php echo $uid; ?>{padding:<?php echo $padding_y; ?>px <?php echo $padding_x; ?>px;}
#<?php echo $uid; ?> .dcar__inner{max-width:<?php echo $max_width; ?>px;margin:0 auto;position:relative;}
#<?php echo $uid; ?> .dcar__viewport{overflow:hidden;}
<?php if ( $arrows_position !== 'inside' ) : ?>
#<?php echo $uid; ?> .dcar__viewport{margin:0 <?php echo $vp_margin; ?>px;}
<?php endif; ?>
#<?php echo $uid; ?> .dcar__track{
  display:flex;
  gap:<?php echo $gap; ?>px;
  transition:transform .38s cubic-bezier(.4,0,.2,1);
  will-change:transform;
  cursor:grab;
}
#<?php echo $uid; ?> .dcar__track.is-dragging{cursor:grabbing;transition:none;}
#<?php echo $uid; ?> .dcar__card{
  flex:0 0 calc((100% - <?php echo ($desktop_cols - 1); ?> * <?php echo $gap; ?>px) / <?php echo $desktop_cols; ?> - <?php echo round($peek / $desktop_cols, 2); ?>px);
  background:<?php echo $css( $card_bg ); ?>;
  border-radius:<?php echo $card_radius; ?>px;
  box-shadow:<?php echo $css( $card_shadow ); ?>;
  border:1px solid <?php echo $css( $card_border ); ?>;
  overflow:hidden;
  display:flex;flex-direction:column;
  transition:transform .2s,box-shadow .2s;
  text-decoration:none;color:inherit;
}
#<?php echo $uid; ?> .dcar__card:hover{transform:translateY(-3px);box-shadow:<?php echo $css( $card_shadow ); ?>,0 16px 40px -16px rgba(10,37,64,.28);}
#<?php echo $uid; ?> .dcar__photo{position:relative;overflow:hidden;background:#0a2540;padding-top:<?php echo $ratio_pct; ?>%;}
#<?php echo $uid; ?> .dcar__photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top center;display:block;transition:transform .4s;}
#<?php echo $uid; ?> .dcar__card:hover .dcar__photo img{transform:scale(1.04);}
#<?php echo $uid; ?> .dcar__photo-label{
  position:absolute;bottom:0;left:0;
  background:<?php echo $css( $photo_label_bg ); ?>;
  color:<?php echo $css( $photo_label_color ); ?>;
  font-size:<?php echo $photo_label_size; ?>px;
  font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  padding:10px 14px;border-top-right-radius:8px;
  backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);
}
#<?php echo $uid; ?> .dcar__body{padding:<?php echo $card_body_py; ?>px <?php echo $card_body_px; ?>px;flex:1;display:flex;flex-direction:column;<?php if ( $body_font ) : ?>font-family:<?php echo $css( $body_font ); ?>;<?php endif; ?>}
#<?php echo $uid; ?> .dcar__name{
  margin:0;font-size:<?php echo $name_size; ?>px;font-weight:<?php echo $name_weight; ?>;
  color:<?php echo $css( $name_color ); ?>;line-height:1.1;
  text-transform:<?php echo $name_upper ? 'uppercase' : 'none'; ?>;
  letter-spacing:-.01em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
  font-style:<?php echo $name_italic ? 'italic' : 'normal'; ?>;
  text-decoration:<?php echo $css( $name_decoration ); ?>;
  <?php if ( $name_font ) : ?>font-family:<?php echo $css( $name_font ); ?>;<?php endif; ?>
}
#<?php echo $uid; ?> .dcar__soprannome{
  margin:4px 0 0;font-size:<?php echo $sopr_size; ?>px;
  color:<?php echo $css( $sopr_color ); ?>;font-style:italic;
  text-decoration:<?php echo $css( $sopr_decoration ); ?>;
}
#<?php echo $uid; ?> .dcar__ruolo{
  margin:4px 0 0;font-size:<?php echo $ruolo_size; ?>px;font-weight:<?php echo $ruolo_weight; ?>;
  color:<?php echo $css( $ruolo_color ); ?>;
  font-style:<?php echo $ruolo_italic ? 'italic' : 'normal'; ?>;
  text-decoration:<?php echo $css( $ruolo_decoration ); ?>;
}
#<?php echo $uid; ?> .dcar__bio{
  margin:10px 0 0;font-size:<?php echo $bio_size; ?>px;
  color:<?php echo $css( $bio_color ); ?>;line-height:1.55;
  font-style:<?php echo $bio_italic ? 'italic' : 'normal'; ?>;
  display:-webkit-box;-webkit-line-clamp:<?php echo $bio_lines; ?>;-webkit-box-orient:vertical;overflow:hidden;
}
#<?php echo $uid; ?> .dcar__stats{
  margin:auto 0 0;padding:<?php echo (int)round($card_body_py*.7); ?>px 0 0;
  border-top:1px solid <?php echo $css($stats_sep_color); ?>;
  font-size:<?php echo $stats_size; ?>px;color:<?php echo $css( $stats_color ); ?>;
  display:flex;flex-wrap:wrap;gap:0;list-style:none;
  <?php if ( $show_bio ) : ?>margin-top:10px;<?php endif; ?>
}
#<?php echo $uid; ?> .dcar__stats li+li::before{content:" · ";color:<?php echo $css($stats_sep_color); ?>;}
/* ── Frecce ── */
#<?php echo $uid; ?> .dcar__arrow{
  position:absolute;top:50%;transform:translateY(-50%);
  width:<?php echo $arrow_size; ?>px;height:<?php echo $arrow_size; ?>px;
  display:flex;align-items:center;justify-content:center;
  background:<?php echo $arrow_bg !== '' ? $css( $arrow_bg ) : 'transparent'; ?>;
  color:<?php echo $css( $arrow_color ); ?>;
  border:none;outline:none;
  border-radius:<?php echo $arrow_radius; ?>px;
  box-shadow:<?php echo $arrow_shadow_show && $arrow_shadow !== '' ? $css( $arrow_shadow ) : 'none'; ?>;
  cursor:pointer;z-index:2;transition:background .15s,color .15s,opacity .15s;
  user-select:none;font-size:<?php echo max(16, (int)round($arrow_size * .45)); ?>px;line-height:1;
  -webkit-appearance:none;appearance:none;padding:0;
}
#<?php echo $uid; ?> .dcar__arrow:hover{background:<?php echo $arrow_hover_bg !== '' ? $css( $arrow_hover_bg ) : 'transparent'; ?>;color:<?php echo $css( $arrow_hover_col ); ?>;}
#<?php echo $uid; ?> .dcar__arrow--prev{left:<?php echo $arrows_position === 'inside' ? '8px' : ( $arrow_outer_pos . 'px' ); ?>;}
#<?php echo $uid; ?> .dcar__arrow--next{right:<?php echo $arrows_position === 'inside' ? '8px' : ( $arrow_outer_pos . 'px' ); ?>;}
#<?php echo $uid; ?> .dcar__arrow.is-hidden{opacity:0;pointer-events:none;}
<?php if ( $dots_show ) : ?>
#<?php echo $uid; ?> .dcar__dots{display:flex;justify-content:center;gap:8px;margin-top:20px;}
#<?php echo $uid; ?> .dcar__dot{width:<?php echo $dots_size; ?>px;height:<?php echo $dots_size; ?>px;border-radius:50%;background:<?php echo $css( $dots_color ); ?>;cursor:pointer;transition:background .2s,transform .2s;}
#<?php echo $uid; ?> .dcar__dot.is-active{background:<?php echo $css( $dots_active_color ); ?>;transform:scale(1.35);}
<?php endif; ?>
@media(max-width:1024px){
  #<?php echo $uid; ?> .dcar__card{
    flex-basis:calc((100% - <?php echo ($tablet_cols - 1); ?> * <?php echo $gap; ?>px) / <?php echo $tablet_cols; ?> - <?php echo round($peek / $tablet_cols, 2); ?>px);
  }
}
@media(max-width:640px){
  #<?php echo $uid; ?> .dcar__track{gap:0;}
  #<?php echo $uid; ?> .dcar__card{flex-basis:100%;}
  #<?php echo $uid; ?> .dcar__viewport{margin:0;}
  #<?php echo $uid; ?> .dcar__arrow--prev{left:4px;}
  #<?php echo $uid; ?> .dcar__arrow--next{right:4px;}
}
</style>

<section id="<?php echo $uid; ?>"<?php echo $section_style_attr; ?>>
	<div class="dcar__inner">

		<?php if ( $arrows_show ) : ?>
		<button class="dcar__arrow dcar__arrow--prev" aria-label="Precedente" data-dir="prev">
			<svg width="13" height="20" viewBox="0 0 13 20" fill="currentColor"><path d="M13,0 L0,10 L13,20 Q9,10 13,0 Z"/></svg>
		</button>
		<button class="dcar__arrow dcar__arrow--next" aria-label="Successivo" data-dir="next">
			<svg width="13" height="20" viewBox="0 0 13 20" fill="currentColor"><path d="M0,0 L13,10 L0,20 Q4,10 0,0 Z"/></svg>
		</button>
		<?php endif; ?>

		<div class="dcar__viewport">
			<div class="dcar__track" data-carousel="<?php echo $uid; ?>">
			<?php foreach ( $docenti as $post ) :
				$id      = $post->ID;
				$nome        = (string) get_post_meta( $id, '_docente_nome',        true );
				$cognome     = (string) get_post_meta( $id, '_docente_cognome',     true );
				$soprannome  = (string) get_post_meta( $id, '_docente_soprannome',  true );
				$ruolo       = (string) get_post_meta( $id, '_docente_ruolo',       true );
				$bio     = (string) get_post_meta( $id, '_docente_bio_breve', true );
				$anni    = (int)    get_post_meta( $id, '_docente_anni_esperienza', true );
				$spec    = get_post_meta( $id, '_docente_specializzazioni', true );
				$img     = get_the_post_thumbnail_url( $id, 'large' );
				$brevetti = wp_get_post_terms( $id, 'calypso_brevetto', [ 'fields' => 'names' ] );
				if ( is_wp_error( $brevetti ) ) $brevetti = [];

				$display_name = trim( $nome . ' ' . $cognome ) ?: $post->post_title;
				$anno_inizio  = $anni > 0 ? ( (int) date( 'Y' ) - $anni ) : 0;

				$stats = [];
				if ( $show_stat_anno && $anno_inizio ) $stats[] = $stat_anno_label . ' ' . $anno_inizio;
				if ( $show_stat_brevetti ) foreach ( $brevetti as $b ) $stats[] = $b;
				if ( $show_stat_spec && $spec ) $stats[] = is_array( $spec ) ? implode( ', ', $spec ) : $spec;

				$card_tag = $show_link ? 'a' : 'div';
				$card_href = $show_link ? ' href="' . esc_url( get_permalink( $id ) ) . '"' . ( $link_target === '_blank' ? ' target="_blank" rel="noopener"' : '' ) : '';
			?>
			<<?php echo $card_tag; ?> class="dcar__card"<?php echo $card_href; ?>>
				<div class="dcar__photo">
					<?php if ( $img ) : ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" loading="lazy">
					<?php endif; ?>
					<?php if ( $show_photo_label ) : ?>
					<span class="dcar__photo-label"><?php echo esc_html( trim( $photo_label_prefix . ' · ' . mb_strtoupper( $display_name ) ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="dcar__body">
					<p class="dcar__name"><?php echo esc_html( $display_name ); ?></p>
					<?php if ( $show_soprannome && $soprannome ) : ?>
					<p class="dcar__soprannome">detto &ldquo;<?php echo esc_html( $soprannome ); ?>&rdquo;</p>
					<?php endif; ?>
					<?php if ( $show_ruolo && $ruolo ) : ?>
					<p class="dcar__ruolo"><?php echo esc_html( $ruolo ); ?></p>
					<?php endif; ?>
					<?php if ( $show_bio && $bio ) : ?>
					<p class="dcar__bio"><?php echo esc_html( $bio ); ?></p>
					<?php endif; ?>
					<?php if ( $show_stats && ! empty( $stats ) ) : ?>
					<ul class="dcar__stats">
						<?php foreach ( $stats as $s ) : ?>
						<li><?php echo esc_html( $s ); ?></li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
			</<?php echo $card_tag; ?>>
			<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $dots_show ) : ?>
		<div class="dcar__dots">
			<?php for ( $i = 0; $i < $total; $i++ ) : ?>
			<button class="dcar__dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-dot="<?php echo $i; ?>" aria-label="Vai al docente <?php echo $i + 1; ?>"></button>
			<?php endfor; ?>
		</div>
		<?php endif; ?>

	</div>
</section>
<script>
(function () {
	var uid   = '<?php echo $uid; ?>';
	var wrap  = document.getElementById(uid);
	if (!wrap) return;
	var track  = wrap.querySelector('.dcar__track');
	var cards  = Array.from(track.querySelectorAll('.dcar__card'));
	var dots   = Array.from(wrap.querySelectorAll('.dcar__dot'));
	var btnPrev= wrap.querySelector('.dcar__arrow--prev');
	var btnNext= wrap.querySelector('.dcar__arrow--next');
	var total  = cards.length;
	var current= 0;

	function colsNow() {
		var w = wrap.offsetWidth;
		if (w <= 640) return <?php echo $mobile_cols; ?>;
		if (w <= 1024) return <?php echo $tablet_cols; ?>;
		return <?php echo $desktop_cols; ?>;
	}

	function go(idx) {
		var cols = colsNow();
		var max  = Math.max(0, total - cols);
		current  = Math.max(0, Math.min(idx, max));
		var card = cards[current];
		if (!card) return;
		var offset = card.offsetLeft - cards[0].offsetLeft;
		track.style.transform = 'translateX(-' + offset + 'px)';
		dots.forEach(function (d, i) { d.classList.toggle('is-active', i === current); });
		if (btnPrev) btnPrev.classList.toggle('is-hidden', current === 0);
		if (btnNext) btnNext.classList.toggle('is-hidden', current >= max);
	}

	if (btnPrev) btnPrev.addEventListener('click', function () { go(current - 1); });
	if (btnNext) btnNext.addEventListener('click', function () { go(current + 1); });
	dots.forEach(function (d) { d.addEventListener('click', function () { go(parseInt(d.dataset.dot, 10)); }); });

	/* Touch/drag */
	var dragStartX = 0, isDragging = false;
	track.addEventListener('mousedown',  function (e) { dragStartX = e.clientX; isDragging = true; track.classList.add('is-dragging'); });
	track.addEventListener('mousemove',  function (e) { if (!isDragging) return; e.preventDefault(); });
	track.addEventListener('mouseup',    function (e) { if (!isDragging) return; isDragging = false; track.classList.remove('is-dragging'); var d = e.clientX - dragStartX; if (Math.abs(d) > 40) go(current + (d < 0 ? 1 : -1)); });
	track.addEventListener('mouseleave', function ()  { if (isDragging) { isDragging = false; track.classList.remove('is-dragging'); } });
	track.addEventListener('touchstart', function (e) { dragStartX = e.touches[0].clientX; }, { passive: true });
	track.addEventListener('touchend',   function (e) { var d = e.changedTouches[0].clientX - dragStartX; if (Math.abs(d) > 40) go(current + (d < 0 ? 1 : -1)); }, { passive: true });

	/* Keyboard */
	wrap.addEventListener('keydown', function (e) {
		if (e.key === 'ArrowLeft')  go(current - 1);
		if (e.key === 'ArrowRight') go(current + 1);
	});

	go(0);

	/* ── Autoplay ── */
	var autoplay      = <?php echo $autoplay ? 'true' : 'false'; ?>;
	var autoplaySpeed = <?php echo $autoplay_speed; ?>;
	var autoTimer     = null;

	function startAuto() {
		if (!autoplay) { return; }
		if (autoTimer !== null) { clearInterval(autoTimer); }
		autoTimer = setInterval(function () {
			var cols = colsNow();
			var max  = Math.max(0, total - cols);
			var next = current + 1;
			if (next > max) { next = 0; }
			go(next);
		}, autoplaySpeed);
	}
	function stopAuto() {
		if (autoTimer !== null) { clearInterval(autoTimer); autoTimer = null; }
	}
	if (autoplay) {
		wrap.addEventListener('mouseenter', stopAuto);
		wrap.addEventListener('mouseleave', startAuto);
		startAuto();
	}

	window.addEventListener('resize', function () { go(current); });
})();
</script>
