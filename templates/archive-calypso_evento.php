<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'block_template_part' ) ) {
	echo '<div class="cso-site-header-wrap">';
	block_template_part( 'header' );
	echo '</div>';
}

/* ── Default 50 eventi via shared class ── */
$today          = current_time( 'Y-m-d' );
$eventi         = Calypsosub_Ajax_Eventi::query( '', '', '', '', true );
$booking_counts = Calypsosub_Ajax_Eventi::booking_counts( $eventi );

/* ── Opzioni filtro luogo ── */
$all_pids   = get_posts( [ 'post_type' => 'calypso_evento', 'posts_per_page' => -1, 'fields' => 'ids' ] );
$all_luoghi = [];
foreach ( $all_pids as $pid ) {
	$l = trim( (string) get_post_meta( $pid, '_evento_luogo', true ) );
	if ( $l && ! in_array( $l, $all_luoghi, true ) ) $all_luoghi[] = $l;
}
sort( $all_luoghi );

$eventi_nonce = wp_create_nonce( 'calypso_eventi_search' );
$ajax_url     = admin_url( 'admin-ajax.php' );

$hero_img_id  = (int) get_option( 'calypsosub_hero_img_eventi', 0 );
$hero_img_url = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';
$_ov_c = calypsosub_opt( 'eventi', 'overlay_color', '#061826' );
$_ov_o = (int) calypsosub_opt( 'eventi', 'overlay_opacity', '88' );
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

/* Layout corpo */
.cso-archive-body{background:var(--c-bone,#f6f1e6);padding:80px 48px 96px}
.cso-archive-inner{max-width:1320px;margin:0 auto;display:grid;grid-template-columns:280px 1fr;gap:60px;align-items:start}

/* Sidebar filtri */
.cso-filtri{position:sticky;top:24px;align-self:flex-start}
.cso-filtri__eyebrow{font-weight:600;letter-spacing:.16em;text-transform:uppercase;margin:0 0 20px;display:block;font-size:16px;color:var(--c-wave,#1B77A7)}
.cso-filtri__groups{display:flex;flex-direction:column;gap:20px}
.cso-filtri__group-label{font-size:12px;font-weight:700;color:var(--c-deep,#1B77A7);margin:0 0 8px;letter-spacing:.04em;text-transform:uppercase}
.cso-filtri__field{width:100%;padding:8px 12px;border:1.5px solid rgba(11,26,38,.15);border-radius:8px;font-size:13px;color:var(--c-ink,#0b1a26);background:#fff;outline:none;font-family:inherit;box-sizing:border-box}
.cso-filtri__field:focus{border-color:var(--c-deep,#1B77A7)}
.cso-filtri__date-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.cso-filtri__actions{margin-top:20px;display:flex;gap:10px;flex-wrap:wrap}
.cso-filtri__btn-apply{display:inline-flex;align-items:center;padding:10px 20px;background:var(--c-deep,#1B77A7);border:none;border-radius:999px;cursor:pointer;font-weight:600;font-size:13px;color:#fff;font-family:inherit}
.cso-filtri__btn-apply:hover{background:var(--c-abyss,#061826)}
.cso-filtri__btn-reset{display:inline-flex;align-items:center;padding:9px 16px;background:transparent;border:1.5px solid rgba(11,26,38,.2);border-radius:999px;cursor:pointer;font-weight:500;font-size:13px;color:rgba(11,26,38,.6);font-family:inherit}
.cso-filtri__btn-reset:hover{border-color:rgba(11,26,38,.45)}

/* Mesi */
.cso-mese{margin-bottom:56px}
.cso-mese:last-child{margin-bottom:0}
.cso-mese__heading{margin:0 0 24px;display:flex;align-items:baseline;gap:16px;font-size:56px;color:var(--c-deep,#1B77A7)}
.cso-mese__count{font-weight:400;letter-spacing:.1em;text-transform:uppercase;font-size:18px;color:rgba(11,26,38,.45)}

/* Lista eventi */
.cso-eventi-list{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 12px 40px -20px rgba(10,37,64,.2)}

/* Riga evento */
.cso-evento-row{
	display:grid;
	grid-template-columns:90px 1fr 200px 110px 120px;
	align-items:center;
	padding:20px 24px;
	border-top:1px solid rgba(11,26,38,.06);
	background:#fff;
	column-gap:16px;
}
.cso-evento-row:first-child{border-top:none}

/* Passato */
.cso-evento-row--passato{background:rgba(11,26,38,.03)}
.cso-evento-row--passato .cso-evento-row__daynum{color:rgba(11,26,38,.3) !important}
.cso-evento-row--passato .cso-evento-row__title{color:rgba(11,26,38,.5) !important}
.cso-evento-row--passato .cso-evento-row__dayname{color:rgba(11,26,38,.3) !important}

/* Colonna data */
.cso-evento-row__date{line-height:1}
.cso-evento-row__dayname{font-weight:600;letter-spacing:.18em;text-transform:uppercase;margin:0 0 4px;display:block;font-size:12px;color:rgba(11,26,38,.5)}
.cso-evento-row__daynum{font-weight:900;line-height:1;margin:0;font-size:46px;color:var(--c-deep,#1B77A7)}
.cso-evento-row__time{font-size:12px;color:rgba(11,26,38,.5);display:block;margin-top:4px;font-family:var(--f-mono,monospace);letter-spacing:.08em}

/* Colonna info */
.cso-evento-row__info a{text-decoration:none;color:inherit;display:block}
.cso-evento-row__title{font-weight:700;margin:0 0 4px;line-height:1.2;font-size:26px;color:var(--c-deep,#1B77A7)}
.cso-evento-row__luogo{display:flex;align-items:center;gap:5px;margin:0;font-size:14px;color:rgba(11,26,38,.6)}

/* Colonna sottotitolo */
.cso-evento-row__sottotitolo{font-size:14px;color:rgba(11,26,38,.55);line-height:1.4}

/* Concluso badge */
.cso-evento-row__concluso{display:inline-flex;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:rgba(11,26,38,.08);color:rgba(11,26,38,.4)}

/* CTA */
.cso-evento-row__cta{justify-self:end}
.cso-btn-dark{display:inline-flex;align-items:center;padding:10px 18px;background:var(--c-deep,#1B77A7);border:none;border-radius:999px;cursor:pointer;text-decoration:none;white-space:nowrap;transition:background .15s;font-size:14px;font-weight:600;color:#fff}
.cso-btn-dark:hover{background:var(--c-abyss,#061826)}
.cso-btn-dark--disabled{background:rgba(11,26,38,.08);pointer-events:none;cursor:default;color:rgba(11,26,38,.3)}

/* Spinner */
.cso-eventi-wrap{position:relative;min-height:60px}
.cso-eventi-spinner{display:none;position:absolute;inset:0;background:rgba(246,241,230,.75);align-items:flex-start;justify-content:center;padding-top:60px;z-index:10;backdrop-filter:blur(2px)}
.cso-eventi-spinner__inner{width:36px;height:36px;border:3px solid rgba(27,119,167,.2);border-top-color:var(--c-deep,#1B77A7);border-radius:50%;animation:cso-spin .8s linear infinite}
@keyframes cso-spin{to{transform:rotate(360deg)}}

/* Empty */
.cso-empty{padding:64px 0;text-align:center}
.cso-empty__title{font-size:24px;color:var(--c-deep,#1B77A7)}
.cso-empty__sub{font-size:16px;color:rgba(11,26,38,.55)}

/* posti (riusa classi da uscite) */
.cso-uscita-row__posti{display:inline-flex;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;white-space:nowrap}
.cso-uscita-row__posti--ok{background:rgba(38,203,251,.12);color:var(--c-deep,#1B77A7)}
.cso-uscita-row__posti--warn{background:rgba(233,191,38,.18);color:#b38000}
.cso-uscita-row__posti--full{background:rgba(220,53,69,.12);color:#b00020}
.cso-uscita-row__posti--libera{background:rgba(38,203,251,.08);color:rgba(11,26,38,.55)}

/* Responsive ≤1024px */
@media(max-width:1024px){
	.cso-hero{padding:calc(90px + 24px) 28px 64px}
	.cso-archive .cso-hero__title{font-size:76px}
	.cso-archive-body{padding:56px 28px 72px}
	.cso-archive-inner{grid-template-columns:1fr;gap:32px}
	.cso-filtri{position:static;background:rgba(10,37,64,.04);border-radius:14px;padding:24px}
	.cso-filtri__groups{display:grid;grid-template-columns:repeat(3,1fr);gap:20px 28px}
	.cso-mese{margin-bottom:40px}
	.cso-mese__heading{font-size:46px}
	.cso-evento-row{grid-template-columns:76px 1fr 160px 96px 110px;padding:18px 20px;column-gap:12px}
}

/* Responsive ≤760px */
@media(max-width:760px){
	.cso-hero{padding:calc(90px + 20px) 20px 48px}
	.cso-archive .cso-hero__title{font-size:52px}
	.cso-archive-body{padding:40px 20px 64px}
	.cso-filtri__groups{grid-template-columns:1fr 1fr;gap:18px 24px}
	.cso-mese{margin-bottom:32px}
	.cso-mese__heading{font-size:38px;gap:12px}
	.cso-eventi-list{box-shadow:0 10px 30px -18px rgba(10,37,64,.3)}
	.cso-evento-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px 14px;padding:18px}
	.cso-evento-row>*{min-width:0}
	.cso-evento-row__date   {order:1}
	.cso-evento-row__info   {order:2;flex:1 1 auto}
	.cso-evento-row__sottotitolo{order:3;flex:1 1 100%;padding-left:104px}
	.cso-uscita-row__posti,.cso-evento-row__concluso{order:4;margin-left:auto}
	.cso-evento-row__cta    {order:5;flex:none;width:100%}
	.cso-evento-row__daynum{font-size:30px !important}
	.cso-btn-dark{display:flex;width:100%;justify-content:center;padding:12px 18px;margin-top:4px;box-sizing:border-box}
}

/* Responsive ≤420px */
@media(max-width:420px){
	.cso-archive .cso-hero__title{font-size:42px}
	.cso-filtri__groups{grid-template-columns:1fr}
	.cso-mese__heading{font-size:32px}
}
</style>

<div class="cso-archive cso-archive--eventi">

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
		<?php echo esc_html( calypsosub_opt( 'eventi', 'archive_eyebrow', __( 'Eventi · ', 'calypsosub' ) . gmdate( 'Y' ) ) ); ?>
	</span>
	<h1 class="cso-hero__title display">
		<?php echo wp_kses( calypsosub_opt( 'eventi', 'archive_h1',
			__( 'Tutto quello che<br>succede al <em>club.</em>', 'calypsosub' )
		), [ 'em' => [], 'br' => [], 'strong' => [] ] ); ?>
	</h1>
	<p class="cso-hero__lead">
		<?php echo esc_html( calypsosub_opt( 'eventi', 'archive_lead',
			__( 'Cene sociali, gite in barca, workshop, raduni. Gli eventi del club, passati e futuri.', 'calypsosub' )
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

	<!-- Sidebar filtri -->
	<aside class="cso-filtri">
		<span class="cso-filtri__eyebrow"><?php esc_html_e( 'Filtra', 'calypsosub' ); ?></span>
		<div class="cso-filtri__groups">

			<div>
				<div class="cso-filtri__group-label"><?php esc_html_e( 'TESTO', 'calypsosub' ); ?></div>
				<input type="search" class="cso-filtri__field" id="cso-ev-q"
				       placeholder="<?php esc_attr_e( 'Cerca evento…', 'calypsosub' ); ?>"
				       aria-label="<?php esc_attr_e( 'Cerca evento per nome', 'calypsosub' ); ?>">
			</div>

			<div>
				<div class="cso-filtri__group-label"><?php esc_html_e( 'PERIODO', 'calypsosub' ); ?></div>
				<div class="cso-filtri__date-row">
					<input type="date" class="cso-filtri__field" id="cso-ev-da"
					       aria-label="<?php esc_attr_e( 'Dal', 'calypsosub' ); ?>">
					<input type="date" class="cso-filtri__field" id="cso-ev-a"
					       aria-label="<?php esc_attr_e( 'Al', 'calypsosub' ); ?>">
				</div>
			</div>

			<?php if ( ! empty( $all_luoghi ) ) : ?>
			<div>
				<div class="cso-filtri__group-label"><?php esc_html_e( 'LUOGO', 'calypsosub' ); ?></div>
				<select class="cso-filtri__field" id="cso-ev-luogo"
				        aria-label="<?php esc_attr_e( 'Filtra per luogo', 'calypsosub' ); ?>">
					<option value=""><?php esc_html_e( 'Tutti', 'calypsosub' ); ?></option>
					<?php foreach ( $all_luoghi as $loc ) : ?>
					<option value="<?php echo esc_attr( $loc ); ?>"><?php echo esc_html( $loc ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>

		</div><!-- .cso-filtri__groups -->

		<div class="cso-filtri__actions">
			<button type="button" class="cso-filtri__btn-apply" id="cso-ev-apply">
				<?php esc_html_e( 'Cerca', 'calypsosub' ); ?>
			</button>
			<button type="button" class="cso-filtri__btn-reset" id="cso-ev-reset" style="display:none">
				<?php esc_html_e( 'Rimuovi', 'calypsosub' ); ?>
			</button>
		</div>
	</aside>

	<!-- Contenuto principale -->
	<main class="cso-main">
		<div class="cso-eventi-wrap" id="cso-eventi-wrap">
			<div class="cso-eventi-spinner" id="cso-eventi-spinner" aria-hidden="true">
				<div class="cso-eventi-spinner__inner"></div>
			</div>
			<div id="cso-eventi-content">
				<?php echo Calypsosub_Ajax_Eventi::render( $eventi, $booking_counts, $today ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</main>

</div><!-- .cso-archive-inner -->
</section>
</div><!-- .cso-archive -->

<script>
(function () {
	var ajaxUrl  = <?php echo wp_json_encode( esc_url_raw( $ajax_url ) ); ?>;
	var nonce    = <?php echo wp_json_encode( $eventi_nonce ); ?>;

	var elQ       = document.getElementById('cso-ev-q');
	var elDa      = document.getElementById('cso-ev-da');
	var elA       = document.getElementById('cso-ev-a');
	var elLuogo   = document.getElementById('cso-ev-luogo');
	var elApply   = document.getElementById('cso-ev-apply');
	var elReset   = document.getElementById('cso-ev-reset');
	var elSpinner = document.getElementById('cso-eventi-spinner');
	var elContent = document.getElementById('cso-eventi-content');

	function isFiltered() {
		return (elQ     && elQ.value.trim()) ||
		       (elDa    && elDa.value) ||
		       (elA     && elA.value) ||
		       (elLuogo && elLuogo.value);
	}

	function doSearch() {
		var body = new URLSearchParams();
		body.append('action', 'calypso_eventi_search');
		body.append('nonce',  nonce);
		if (elQ)     body.append('q',     elQ.value.trim());
		if (elDa)    body.append('da',    elDa.value);
		if (elA)     body.append('a',     elA.value);
		if (elLuogo) body.append('luogo', elLuogo.value);

		if (elSpinner) elSpinner.style.display = 'flex';

		fetch(ajaxUrl, { method: 'POST', body: body })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.success && elContent) elContent.innerHTML = res.data.html;
			})
			.catch(function () {})
			.finally(function () {
				if (elSpinner) elSpinner.style.display = 'none';
				if (elReset)   elReset.style.display = isFiltered() ? '' : 'none';
			});
	}

	if (elApply) elApply.addEventListener('click', doSearch);

	/* Auto-search on Enter in text field */
	if (elQ) {
		elQ.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') doSearch();
		});
	}

	if (elReset) {
		elReset.addEventListener('click', function () {
			if (elQ)     elQ.value     = '';
			if (elDa)    elDa.value    = '';
			if (elA)     elA.value     = '';
			if (elLuogo) elLuogo.value = '';
			elReset.style.display = 'none';
			doSearch();
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
