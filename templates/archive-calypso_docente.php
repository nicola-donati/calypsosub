<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}

/* ── Dati ── */
$docenti = get_posts( [
	'post_type'      => 'calypso_docente',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'title',
	'order'          => 'ASC',
] );
$total = count( $docenti );

$hero_img_id  = (int) get_option( 'calypsosub_hero_img_docenti', 0 );
$hero_img_url = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';
$_ov_c = calypsosub_opt( 'docenti', 'overlay_color', '#061826' );
$_ov_o = (int) calypsosub_opt( 'docenti', 'overlay_opacity', '88' );
$_ad = [
	'body_bg'     => calypsosub_opt( 'docenti', 'design_arch_body_bg',    '#f6f1e6' ),
	'card_bg'     => calypsosub_opt( 'docenti', 'design_arch_card_bg',    '#ffffff' ),
	'card_radius' => max( 0, (int) calypsosub_opt( 'docenti', 'design_arch_card_radius', '12' ) ),
	'name_color'  => calypsosub_opt( 'docenti', 'design_arch_name_color', '#1B77A7' ),
	'role_color'  => calypsosub_opt( 'docenti', 'design_arch_role_color', '#1B77A7' ),
	'bio_color'   => calypsosub_opt( 'docenti', 'design_arch_bio_color',  '#283d4d' ),
];
list( $_r, $_g, $_b ) = array_map( 'hexdec', str_split( ltrim( $_ov_c, '#' ), 2 ) );
$overlay_gradient = sprintf( 'linear-gradient(rgba(%d,%d,%d,%.3f) 0%%,rgba(%d,%d,%d,%.3f) 40%%,rgba(%d,%d,%d,%.3f) 100%%)', $_r, $_g, $_b, round( $_ov_o / 100 * 0.682, 3 ), $_r, $_g, $_b, round( $_ov_o / 100 * 0.170, 3 ), $_r, $_g, $_b, round( $_ov_o / 100, 3 ) );
?>
<style>
.cso-archive{color:var(--c-ink,#0b1a26)}
.cso-archive a{color:inherit;text-decoration:none}

/* Hero */
.cso-hero{background:var(--c-deep,#1B77A7);color:#fff;padding:calc(90px + 32px) 48px 80px;position:relative}
.cso-hero__bg{position:absolute;inset:0;overflow:hidden}
.cso-hero__bg img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}
.cso-hero__overlay{position:absolute;inset:0;background:linear-gradient(rgba(6,24,38,.6) 0%,rgba(6,24,38,.15) 40%,rgba(6,24,38,.88) 100%)}
.cso-hero--has-img .cso-hero__inner,.cso-hero--has-img .cso-hero__scroll{position:relative;z-index:1}
.cso-hero h1,.cso-hero h2,.cso-hero h3{color:#fff}
.cso-hero__inner{max-width:1320px;margin:0 auto}
.cso-hero__eyebrow{font-weight:600;letter-spacing:.16em;text-transform:uppercase;margin:0 0 20px;display:block;font-size:16px;color:var(--c-aqua,#26CBFB)}
.cso-hero__title{font-size:124px;color:#fff;margin:0;text-shadow:0 2px 16px rgba(0,0,0,.95),0 6px 48px rgba(0,0,0,.8)}
.cso-hero__title em{font-style:italic;color:var(--c-aqua,#26CBFB)}
.cso-hero__lead{line-height:1.65;opacity:.85;margin:32px 0 0;max-width:700px;font-size:18px;color:#fff}

/* Body */
.cso-archive-body{background:var(--c-bone,#f6f1e6);padding:80px 48px 96px}
.cso-archive-inner{max-width:1320px;margin:0 auto}

/* Search */
.cso-docenti-search-wrap{text-align:center;margin-bottom:40px}
.cso-docenti-search{padding:10px 24px;border:1.5px solid rgba(11,26,38,.15);border-radius:999px;font-size:14px;color:var(--c-ink,#0b1a26);background:#fff;width:100%;max-width:360px;outline:none;font-family:inherit}
.cso-docenti-search:focus{border-color:var(--c-deep,#1B77A7)}

/* Griglia */
.cso-docenti-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}

/* Card */
.cso-docente-card{background:#fff;border:1px solid rgba(11,26,38,.06);border-radius:12px;overflow:hidden;display:flex;flex-direction:column;text-decoration:none;color:inherit;transition:box-shadow .2s,transform .15s}
.cso-docente-card:hover{box-shadow:0 12px 40px -16px rgba(10,37,64,.25);transform:translateY(-2px)}
.cso-docente-card__img{height:240px;background:linear-gradient(180deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7) 60%,var(--c-abyss,#061826));overflow:hidden;position:relative}
.cso-docente-card__img img{width:100%;height:100%;object-fit:cover;object-position:center top;display:block}
.cso-docente-card__body{padding:22px;flex:1;display:flex;flex-direction:column}
.cso-docente-card__name{font-size:28px;color:var(--c-deep,#1B77A7);margin:0 0 6px;line-height:1}
.cso-docente-card__soprannome{font-size:16px;color:rgba(11,26,38,.6);font-style:italic;margin:0 0 8px}
.cso-docente-card__ruolo{font-size:16px;color:var(--c-wave,#1B77A7);font-weight:600;margin:0 0 12px}
.cso-docente-card__bio{font-size:15px;line-height:1.55;color:rgba(11,26,38,.7);margin:0;flex:1;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.cso-docente-card__footer{margin-top:14px;padding-top:14px;border-top:1px solid rgba(11,26,38,.08);font-family:var(--f-mono,monospace);font-size:12px;color:rgba(11,26,38,.55);letter-spacing:.06em;text-transform:uppercase;line-height:1.6}

/* Empty */
.cso-empty{padding:64px 0;text-align:center}
.cso-empty__title{font-size:24px;color:var(--c-deep,#1B77A7)}

/* Responsive */
@media(max-width:1024px){
	.cso-hero{padding:calc(90px + 24px) 28px 64px}
	.cso-archive .cso-hero__title{font-size:76px}
	.cso-archive-body{padding:56px 28px 72px}
	.cso-docenti-grid{grid-template-columns:repeat(3,1fr);gap:16px}
}
@media(max-width:760px){
	.cso-hero{padding:calc(90px + 20px) 20px 48px}
	.cso-archive .cso-hero__title{font-size:52px}
	.cso-archive .cso-hero__lead{font-size:15px;margin-top:24px}
	.cso-archive-body{padding:40px 20px 64px}
	.cso-docenti-grid{grid-template-columns:repeat(2,1fr);gap:14px}
}
@media(max-width:420px){
	.cso-archive .cso-hero__title{font-size:42px}
	.cso-docenti-grid{grid-template-columns:1fr}
}
</style>
<style>
.cso-archive-body{background:<?php echo esc_attr($_ad['body_bg']); ?>}
.cso-docente-card{background:<?php echo esc_attr($_ad['card_bg']); ?>;border-radius:<?php echo esc_attr($_ad['card_radius']); ?>px}
.cso-docente-card__name{color:<?php echo esc_attr($_ad['name_color']); ?>}
.cso-docente-card__ruolo{color:<?php echo esc_attr($_ad['role_color']); ?>}
.cso-docente-card__bio{color:<?php echo esc_attr($_ad['bio_color']); ?>}
</style>

<div class="cso-archive cso-archive--docenti">

<!-- Hero -->
<section class="cso-hero<?php echo $hero_img_url ? ' cso-hero--has-img' : ''; ?>">
<?php if ( $hero_img_url ) : ?>
<div class="cso-hero__bg">
	<img src="<?php echo esc_url( $hero_img_url ); ?>" alt="" loading="eager" fetchpriority="high">
</div>
<div class="cso-hero__overlay" style="background:<?php echo esc_attr( $overlay_gradient ); ?>"></div>
<?php endif; ?>
<div class="cso-hero__inner">
	<span class="cso-hero__eyebrow">
		<?php echo esc_html( calypsosub_opt( 'docenti', 'archive_eyebrow',
			sprintf( /* translators: %d = number of instructors */ _n( 'Il team · %d docente', 'Il team · %d docenti', $total, 'calypsosub' ), $total )
		) ); ?>
	</span>
	<h1 class="cso-hero__title display">
		<?php echo wp_kses( calypsosub_opt( 'docenti', 'archive_h1',
			__( 'Le persone con cui<br>scenderai <em>sotto.</em>', 'calypsosub' )
		), [ 'em' => [], 'br' => [], 'strong' => [] ] ); ?>
	</h1>
	<p class="cso-hero__lead">
		<?php echo esc_html( calypsosub_opt( 'docenti', 'archive_lead',
			__( 'Tutti i nostri istruttori sono brevettati FIAS con anni di esperienza nei mari toscani.', 'calypsosub' )
		) ); ?>
	</p>
</div>
<div class="cso-hero__scroll" aria-hidden="true">
	SCORRI
	<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
</div>
</section>

<!-- Corpo -->
<section class="cso-archive-body">
<div class="cso-archive-inner">

	<!-- Ricerca -->
	<div class="cso-docenti-search-wrap">
		<input type="search" class="cso-docenti-search" id="cso-docenti-search"
		       placeholder="<?php esc_attr_e( 'Cerca per nome…', 'calypsosub' ); ?>"
		       aria-label="<?php esc_attr_e( 'Cerca docente per nome', 'calypsosub' ); ?>">
	</div>

	<!-- Griglia -->
	<div class="cso-docenti-grid" id="cso-docenti-grid">
	<?php foreach ( $docenti as $docente ) :
		$nome       = (string) get_post_meta( $docente->ID, '_docente_nome',           true );
		$cognome    = (string) get_post_meta( $docente->ID, '_docente_cognome',        true );
		$soprannome = (string) get_post_meta( $docente->ID, '_docente_soprannome',     true );
		$ruolo      = (string) get_post_meta( $docente->ID, '_docente_ruolo',          true );
		$bio     = (string) get_post_meta( $docente->ID, '_docente_bio',             true );
		$anni    = (int)    get_post_meta( $docente->ID, '_docente_anni_esperienza', true );
		$specs   = (array) ( get_post_meta( $docente->ID, '_docente_specializzazioni', true ) ?: [] );
		$specs   = array_filter( $specs );

		$img_url     = get_the_post_thumbnail_url( $docente->ID, 'medium_large' );
		$full_name   = trim( $nome . ' ' . $cognome ) ?: $docente->post_title;
		$search_attr = strtolower( $full_name );

		$footer_parts = [];
		if ( ! empty( $specs ) ) $footer_parts[] = implode( ' · ', array_map( 'esc_html', $specs ) );
		if ( $anni > 0 )         $footer_parts[] = esc_html( sprintf( /* translators: %d = years */ __( '%d anni', 'calypsosub' ), $anni ) );
	?>
	<a href="<?php echo esc_url( get_permalink( $docente->ID ) ); ?>"
	   class="cso-docente-card"
	   data-search="<?php echo esc_attr( $search_attr ); ?>">

		<div class="cso-docente-card__img">
			<?php if ( $img_url ) : ?>
			<img src="<?php echo esc_url( $img_url ); ?>"
			     alt="<?php echo esc_attr( $full_name ); ?>" loading="lazy">
			<?php endif; ?>
		</div>

		<div class="cso-docente-card__body">
			<div class="cso-docente-card__name display"><?php echo esc_html( $full_name ); ?></div>
			<?php if ( $soprannome ) : ?>
			<div class="cso-docente-card__soprannome">detto &ldquo;<?php echo esc_html( $soprannome ); ?>&rdquo;</div>
			<?php endif; ?>
			<?php if ( $ruolo ) : ?>
			<div class="cso-docente-card__ruolo"><?php echo esc_html( $ruolo ); ?></div>
			<?php endif; ?>
			<?php if ( $bio ) : ?>
			<p class="cso-docente-card__bio"><?php echo esc_html( $bio ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $footer_parts ) ) : ?>
			<div class="cso-docente-card__footer"><?php echo implode( ' &mdash; ', $footer_parts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</div>

	</a>
	<?php endforeach; ?>
	</div><!-- .cso-docenti-grid -->

	<div class="cso-empty" id="cso-docenti-empty" style="display:none">
		<p class="cso-empty__title"><?php esc_html_e( 'Nessun docente trovato.', 'calypsosub' ); ?></p>
	</div>

</div><!-- .cso-archive-inner -->
</section>
</div><!-- .cso-archive -->

<script>
(function () {
	var inp   = document.getElementById('cso-docenti-search');
	var grid  = document.getElementById('cso-docenti-grid');
	var empty = document.getElementById('cso-docenti-empty');
	if (!inp || !grid) return;
	var t;
	inp.addEventListener('input', function () {
		clearTimeout(t);
		t = setTimeout(function () {
			var q   = inp.value.toLowerCase().trim();
			var cnt = 0;
			grid.querySelectorAll('.cso-docente-card').forEach(function (card) {
				var show = !q || card.dataset.search.indexOf(q) !== -1;
				card.style.display = show ? '' : 'none';
				if (show) cnt++;
			});
			if (empty) empty.style.display = cnt === 0 ? '' : 'none';
		}, 200);
	});
})();
</script>

<?php
if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-footer-wrap">';
	block_template_part( 'footer' );
	echo '</div>';
}
get_footer();
?>
