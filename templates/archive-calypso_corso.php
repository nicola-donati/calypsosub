<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}

/* ── Dati ── */
$corsi = get_posts( [
	'post_type'      => 'calypso_corso',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'title',
	'order'          => 'ASC',
] );

$livelli_terms = get_terms( [ 'taxonomy' => 'calypso_livello', 'hide_empty' => false ] );
$livelli_terms = is_wp_error( $livelli_terms ) ? [] : $livelli_terms;

$base_iscr_url = calypsosub_opt( 'corsi', 'link_iscrizione_base', home_url( '/iscrizione' ) );

$hero_img_id  = (int) get_option( 'calypsosub_hero_img_corsi', 0 );
$hero_img_url = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';
$_ov_c = calypsosub_opt( 'corsi', 'overlay_color', '#061826' );
$_ov_o = (int) calypsosub_opt( 'corsi', 'overlay_opacity', '88' );
list( $_r, $_g, $_b ) = array_map( 'hexdec', str_split( ltrim( $_ov_c, '#' ), 2 ) );
$overlay_gradient = sprintf( 'linear-gradient(rgba(%d,%d,%d,%.3f) 0%%,rgba(%d,%d,%d,%.3f) 40%%,rgba(%d,%d,%d,%.3f) 100%%)', $_r, $_g, $_b, round( $_ov_o / 100 * 0.682, 3 ), $_r, $_g, $_b, round( $_ov_o / 100 * 0.170, 3 ), $_r, $_g, $_b, round( $_ov_o / 100, 3 ) );
?>
<style>
.cso-archive{color:var(--c-ink,#0b1a26)}
.cso-archive h1,.cso-archive h2,.cso-archive h3,.cso-archive h4{color:var(--c-wave,#1B77A7);text-transform:uppercase}
.cso-archive a{color:inherit;text-decoration:none}
.cso-archive p,.cso-archive li,.cso-archive span,.cso-archive div{color:inherit}

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

/* Filtri corsi */
.cso-corsi-filters{display:flex;align-items:center;gap:16px;margin-bottom:32px;flex-wrap:wrap}
.cso-corsi-chips{display:flex;gap:8px;flex-wrap:wrap}
.cso-corsi-chip{padding:8px 18px;border-radius:999px;border:1.5px solid rgba(11,26,38,.15);background:transparent;color:rgba(11,26,38,.7);font-size:13px;font-weight:600;cursor:pointer;transition:background .15s,color .15s,border-color .15s;font-family:inherit}
.cso-corsi-chip:hover,.cso-corsi-chip--active{background:var(--c-deep,#1B77A7);border-color:var(--c-deep,#1B77A7);color:#fff}
.cso-corsi-search{padding:9px 18px;border:1.5px solid rgba(11,26,38,.15);border-radius:999px;font-size:13px;color:var(--c-ink,#0b1a26);background:#fff;min-width:200px;outline:none;font-family:inherit}
.cso-corsi-search:focus{border-color:var(--c-deep,#1B77A7)}

/* Griglia */
.cso-corsi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}

/* Card */
.cso-corso-card{background:#fff;border:1px solid rgba(11,26,38,.08);border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.cso-corso-card__img{height:240px;background:linear-gradient(180deg,var(--c-aqua,#26CBFB),var(--c-deep,#1B77A7) 60%,var(--c-abyss,#061826));position:relative;overflow:hidden}
.cso-corso-card__img img{width:100%;height:100%;object-fit:cover;display:block}
.cso-corso-card__body{padding:28px;flex:1;display:flex;flex-direction:column}
.cso-corso-card__meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.cso-corso-card__badge{display:inline-flex;padding:4px 12px;background:rgba(27,119,167,.1);color:var(--c-wave,#1B77A7);border-radius:999px;font-size:14px;font-weight:600}
.cso-corso-card__periodo{font-family:var(--f-mono,monospace);font-size:14px;letter-spacing:.08em;color:rgba(11,26,38,.55);text-transform:uppercase}
.cso-corso-card__title{font-size:28px;color:var(--c-deep,#1B77A7);margin:0 0 12px;line-height:1.1}
.cso-corso-card__title a{color:inherit;text-decoration:none}
.cso-corso-card__desc{font-size:15px;line-height:1.55;color:rgba(11,26,38,.7);margin:0 0 20px;flex:1;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.cso-corso-card__stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:16px 0;border-top:1px solid rgba(11,26,38,.08);margin-bottom:18px}
.cso-corso-card__stat-val{font-size:14px;font-weight:700;color:var(--c-deep,#1B77A7);margin-bottom:2px}
.cso-corso-card__stat-lbl{font-family:var(--f-mono,monospace);font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:rgba(11,26,38,.55)}
.cso-btn-coral{display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:var(--c-coral,#e9bf26);color:var(--c-abyss,#061826);font-size:15px;font-weight:700;border-radius:999px;text-decoration:none;align-self:flex-start;transition:background .15s;border:none;cursor:pointer;font-family:inherit}
.cso-btn-coral:hover{filter:brightness(.9)}

/* Empty */
.cso-empty{padding:64px 0;text-align:center}
.cso-empty__title{font-size:24px;color:var(--c-deep,#1B77A7)}
.cso-empty__sub{font-size:16px;color:rgba(11,26,38,.55)}

/* Responsive */
@media(max-width:1024px){
	.cso-hero{padding:calc(90px + 24px) 28px 64px}
	.cso-archive .cso-hero__title{font-size:76px}
	.cso-archive-body{padding:56px 28px 72px}
	.cso-corsi-grid{grid-template-columns:repeat(2,1fr);gap:16px}
}
@media(max-width:760px){
	.cso-hero{padding:calc(90px + 20px) 20px 48px}
	.cso-archive .cso-hero__title{font-size:52px}
	.cso-archive .cso-hero__lead{font-size:15px;margin-top:24px}
	.cso-archive-body{padding:40px 20px 64px}
	.cso-corsi-grid{grid-template-columns:1fr}
	.cso-corsi-filters{flex-direction:column;align-items:flex-start}
	.cso-corsi-search{width:100%}
}
@media(max-width:420px){
	.cso-archive .cso-hero__title{font-size:42px}
}
</style>

<div class="cso-archive cso-archive--corsi">

<!-- Hero -->
<section class="cso-hero<?php echo $hero_img_url ? ' cso-hero--has-img' : ''; ?>">
<?php if ( $hero_img_url ) : ?>
<div class="cso-hero__bg">
	<img src="<?php echo esc_url( $hero_img_url ); ?>" alt="" loading="eager" fetchpriority="high">
</div>
<div class="cso-hero__overlay" style="background:<?php echo esc_attr( $overlay_gradient ); ?>"></div>
<?php endif; ?>
<div class="cso-hero__inner">
	<span class="cso-hero__eyebrow"><?php echo esc_html( calypsosub_opt( 'corsi', 'archive_eyebrow', 'Corsi · ' . gmdate( 'Y' ) ) ); ?></span>
	<h1 class="cso-hero__title display">
		<?php echo wp_kses( calypsosub_opt( 'corsi', 'archive_h1',
			__( 'Sei corsi.<br>Una sola idea:<br><em>scendere bene.</em>', 'calypsosub' )
		), [ 'em' => [], 'br' => [], 'strong' => [] ] ); ?>
	</h1>
	<p class="cso-hero__lead">
		<?php echo esc_html( calypsosub_opt( 'corsi', 'archive_lead',
			__( 'Tutti i nostri corsi sono certificati FIAS, riconosciuti CMAS in tutto il mondo. Si parte dalla piscina di Arezzo, si finisce in mare aperto.', 'calypsosub' )
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

	<!-- Filtri -->
	<div class="cso-corsi-filters">
		<div class="cso-corsi-chips" role="group" aria-label="<?php esc_attr_e( 'Filtra per livello', 'calypsosub' ); ?>">
			<button class="cso-corsi-chip cso-corsi-chip--active" data-livello=""><?php esc_html_e( 'Tutti', 'calypsosub' ); ?></button>
			<?php foreach ( $livelli_terms as $term ) : ?>
			<button class="cso-corsi-chip" data-livello="<?php echo esc_attr( $term->slug ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
			<?php endforeach; ?>
		</div>
		<input type="search" class="cso-corsi-search"
		       placeholder="<?php esc_attr_e( 'Cerca corso…', 'calypsosub' ); ?>"
		       aria-label="<?php esc_attr_e( 'Cerca corso per nome', 'calypsosub' ); ?>">
	</div>

	<!-- Griglia -->
	<div class="cso-corsi-grid" id="cso-corsi-grid">
	<?php foreach ( $corsi as $corso ) :
		$badge    = (string) get_post_meta( $corso->ID, '_corso_badge',           true );
		$periodo  = (string) get_post_meta( $corso->ID, '_corso_periodo',          true );
		$desc     = (string) get_post_meta( $corso->ID, '_corso_desc_breve',       true );
		$durata   = (string) get_post_meta( $corso->ID, '_corso_stat_durata',      true );
		$pratica  = (string) get_post_meta( $corso->ID, '_corso_stat_pratica',     true );
		$prof     = (string) get_post_meta( $corso->ID, '_corso_stat_profondita',  true );
		$link_ovr = (string) get_post_meta( $corso->ID, '_corso_link_iscrizione',  true );

		$livelli_slugs = wp_get_post_terms( $corso->ID, 'calypso_livello', [ 'fields' => 'slugs' ] );
		$livelli_slugs = is_wp_error( $livelli_slugs ) ? [] : $livelli_slugs;
		$livelli_names = wp_get_post_terms( $corso->ID, 'calypso_livello', [ 'fields' => 'names' ] );
		$livelli_names = is_wp_error( $livelli_names ) ? [] : $livelli_names;
		$livello_label = ! empty( $livelli_names ) ? $livelli_names[0] : $badge;

		$cta_url = $link_ovr ?: add_query_arg( 'corso', $corso->post_name, $base_iscr_url );
		$img_url = get_the_post_thumbnail_url( $corso->ID, 'medium_large' );
	?>
	<article class="cso-corso-card"
	         data-livello="<?php echo esc_attr( implode( ' ', $livelli_slugs ) ); ?>"
	         data-title="<?php echo esc_attr( strtolower( $corso->post_title ) ); ?>">

		<div class="cso-corso-card__img">
			<?php if ( $img_url ) : ?>
			<img src="<?php echo esc_url( $img_url ); ?>"
			     alt="<?php echo esc_attr( $corso->post_title ); ?>" loading="lazy">
			<?php endif; ?>
		</div>

		<div class="cso-corso-card__body">

			<div class="cso-corso-card__meta">
				<?php if ( $livello_label ) : ?>
				<span class="cso-corso-card__badge"><?php echo esc_html( $livello_label ); ?></span>
				<?php endif; ?>
				<?php if ( $periodo ) : ?>
				<span class="cso-corso-card__periodo"><?php echo esc_html( $periodo ); ?></span>
				<?php endif; ?>
			</div>

			<h3 class="cso-corso-card__title display">
				<a href="<?php echo esc_url( get_permalink( $corso->ID ) ); ?>">
					<?php echo esc_html( $corso->post_title ); ?>
				</a>
			</h3>

			<?php if ( $desc ) : ?>
			<p class="cso-corso-card__desc"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>

			<?php if ( $durata || $pratica || $prof ) : ?>
			<div class="cso-corso-card__stats">
				<?php if ( $durata ) : ?>
				<div class="cso-corso-card__stat">
					<div class="cso-corso-card__stat-val"><?php echo esc_html( $durata ); ?></div>
					<div class="cso-corso-card__stat-lbl"><?php esc_html_e( 'Durata', 'calypsosub' ); ?></div>
				</div>
				<?php endif; ?>
				<?php if ( $pratica ) : ?>
				<div class="cso-corso-card__stat">
					<div class="cso-corso-card__stat-val"><?php echo esc_html( $pratica ); ?></div>
					<div class="cso-corso-card__stat-lbl"><?php esc_html_e( 'Pratica', 'calypsosub' ); ?></div>
				</div>
				<?php endif; ?>
				<?php if ( $prof ) : ?>
				<div class="cso-corso-card__stat">
					<div class="cso-corso-card__stat-val"><?php echo esc_html( $prof ); ?></div>
					<div class="cso-corso-card__stat-lbl"><?php esc_html_e( 'Profondità', 'calypsosub' ); ?></div>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<a href="<?php echo esc_url( $cta_url ); ?>" class="cso-btn-coral">
				<?php esc_html_e( 'Iscriviti al corso', 'calypsosub' ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
			</a>

		</div>
	</article>
	<?php endforeach; ?>
	</div><!-- .cso-corsi-grid -->

	<div class="cso-empty" id="cso-corsi-empty" style="display:none">
		<p class="cso-empty__title"><?php esc_html_e( 'Nessun corso trovato.', 'calypsosub' ); ?></p>
		<p class="cso-empty__sub">
			<a href="#" id="cso-corsi-reset"><?php esc_html_e( 'Rimuovi filtri', 'calypsosub' ); ?></a>
		</p>
	</div>

</div><!-- .cso-archive-inner -->
</section>
</div><!-- .cso-archive -->

<script>
(function () {
	var grid    = document.getElementById('cso-corsi-grid');
	var empty   = document.getElementById('cso-corsi-empty');
	var resetEl = document.getElementById('cso-corsi-reset');
	var chips   = document.querySelectorAll('.cso-corsi-chip');
	var search  = document.querySelector('.cso-corsi-search');
	var active  = '';

	function filter() {
		var q   = search ? search.value.toLowerCase().trim() : '';
		var cnt = 0;
		grid.querySelectorAll('.cso-corso-card').forEach(function (card) {
			var matchLiv = !active || card.dataset.livello.split(' ').indexOf(active) !== -1;
			var matchQ   = !q     || card.dataset.title.indexOf(q) !== -1;
			var show     = matchLiv && matchQ;
			card.style.display = show ? '' : 'none';
			if (show) cnt++;
		});
		if (empty) empty.style.display = cnt === 0 ? '' : 'none';
	}

	chips.forEach(function (chip) {
		chip.addEventListener('click', function () {
			chips.forEach(function (c) { c.classList.remove('cso-corsi-chip--active'); });
			chip.classList.add('cso-corsi-chip--active');
			active = chip.dataset.livello;
			filter();
		});
	});

	if (search) {
		var t;
		search.addEventListener('input', function () { clearTimeout(t); t = setTimeout(filter, 200); });
	}

	if (resetEl) {
		resetEl.addEventListener('click', function (e) {
			e.preventDefault();
			active = '';
			chips.forEach(function (c, i) { c.classList.toggle('cso-corsi-chip--active', i === 0); });
			if (search) search.value = '';
			filter();
		});
	}
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
