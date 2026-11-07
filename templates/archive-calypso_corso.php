<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$cso_header_html = '';
$cso_footer_html = '';
if ( function_exists( 'block_template_part' ) ) {
	/* Pre-renderizzati PRIMA di get_header() così gli "elements styles"
	 * (es. colore/font dei link, .wp-elements-N) generati da questi
	 * blocchi vengono registrati in tempo utile per essere stampati
	 * nell'head da wp_head(), invece di arrivare troppo tardi. */
	ob_start();
	block_template_part( 'header' );
	$cso_header_html = do_shortcode( ob_get_clean() );

	ob_start();
	block_template_part( 'footer' );
	$cso_footer_html = do_shortcode( ob_get_clean() );
}

get_header();

if ( $cso_header_html !== '' ) {
	echo '<header class="wp-block-template-part cso-site-header-wrap">' . $cso_header_html . '</header>';
}

/* ── Dati ── */
$corsi = get_posts( [
	'post_type'      => 'calypso_corso',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
] );

$cat_tax       = Calypsosub_Tax_Categorie_Corsi::TAXONOMY;
$categorie     = Calypsosub_Tax_Categorie_Corsi::get_sorted_terms( true );
$livelli_terms = get_terms( [ 'taxonomy' => 'calypso_livello', 'hide_empty' => true ] );
$livelli_terms = is_wp_error( $livelli_terms ) ? [] : $livelli_terms;

/* Raggruppa i corsi per categoria, nell'ordine delle categorie. */
$gruppi = [];
foreach ( $categorie as $cat_term ) {
	$gruppi[ $cat_term->slug ] = [ 'term' => $cat_term, 'corsi' => [] ];
}
$gruppi['altri'] = [ 'term' => null, 'corsi' => [] ];
foreach ( $corsi as $corso ) {
	$cat_slugs = wp_get_post_terms( $corso->ID, $cat_tax, [ 'fields' => 'slugs' ] );
	$cat_slugs = is_wp_error( $cat_slugs ) ? [] : $cat_slugs;
	$gkey      = 'altri';
	foreach ( array_keys( $gruppi ) as $g ) {
		if ( in_array( $g, $cat_slugs, true ) ) { $gkey = $g; break; }
	}
	$gruppi[ $gkey ]['corsi'][] = $corso;
}
$gruppi = array_filter( $gruppi, fn( $g ) => ! empty( $g['corsi'] ) );
$mostra_titoli_gruppo = count( $gruppi ) > 1 || ! isset( $gruppi['altri'] );
$eta_presenti = array_map( 'intval', array_filter( array_map( fn( $c ) => get_post_meta( $c->ID, '_corso_eta_min', true ), $corsi ), fn( $v ) => $v !== '' ) );
/* Fasce di età per il filtro: valore URL, etichetta, età minima del corso da/a. Si mostrano solo le fasce con almeno un corso. */
$fasce_eta = array_values( array_filter( [
	[ '0-8',   __( 'Fino a 8 anni', 'calypsosub' ),    0,  8 ],
	[ '9-11',  __( 'Da 9 a 11 anni', 'calypsosub' ),   9,  11 ],
	[ '12-13', __( 'Da 12 a 13 anni', 'calypsosub' ),  12, 13 ],
	[ '14-15', __( 'Da 14 a 15 anni', 'calypsosub' ),  14, 15 ],
	[ '16-17', __( 'Da 16 a 17 anni', 'calypsosub' ),  16, 17 ],
	[ '18-',   __( 'Da 18 anni in su', 'calypsosub' ), 18, 999 ],
], function ( $f ) use ( $eta_presenti ) {
	foreach ( $eta_presenti as $e ) {
		if ( $e >= $f[2] && $e <= $f[3] ) return true;
	}
	return false;
} ) );

$prenotazioni_page_id = (int) get_option( 'calypsosub_prenotazioni_page_id', 0 );
$prenotazioni_page_ok = $prenotazioni_page_id && get_post_status( $prenotazioni_page_id ) === 'publish';

$hero_img_id  = (int) get_option( 'calypsosub_hero_img_corsi', 0 );
$hero_img_url = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';
$_ov_c = calypsosub_opt( 'corsi', 'overlay_color', '#061826' );
$_ov_o = calypsosub_opt_int( 'corsi', 'overlay_opacity', '88' );
list( $_r, $_g, $_b ) = array_map( 'hexdec', str_split( ltrim( $_ov_c, '#' ), 2 ) );
$overlay_gradient = sprintf( 'linear-gradient(rgba(%d,%d,%d,%.3f) 0%%,rgba(%d,%d,%d,%.3f) 40%%,rgba(%d,%d,%d,%.3f) 100%%)', $_r, $_g, $_b, round( $_ov_o / 100 * 0.682, 3 ), $_r, $_g, $_b, round( $_ov_o / 100 * 0.170, 3 ), $_r, $_g, $_b, round( $_ov_o / 100, 3 ) );

$_acd = [
	'cta_bg'       => calypsosub_opt( 'corsi', 'design_arch_cta_bg',       '#E9BF26' ),
	'cta_color'    => calypsosub_opt( 'corsi', 'design_arch_cta_color',    '#061826' ),
	'cta_hover_bg' => calypsosub_opt( 'corsi', 'design_arch_cta_hover_bg', '#d4aa1e' ),
];
?>
<style>
.cso-archive h1,.cso-archive h2,.cso-archive h3,.cso-archive h4{color:var(--c-wave,#3473A5);text-transform:uppercase}
.cso-archive a{color:inherit;text-decoration:none}
.cso-archive p,.cso-archive li,.cso-archive span,.cso-archive div{color:inherit}

/* Hero */
.cso-hero{background:var(--c-deep,#3473A5);color:#fff;padding:calc(90px + 32px) 48px 80px;position:relative;height:auto;overflow:visible}
.cso-hero__bg{position:absolute;inset:0;overflow:hidden}
.cso-hero__bg img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}
.cso-hero__overlay{position:absolute;inset:0;background:linear-gradient(rgba(6,24,38,.6) 0%,rgba(6,24,38,.15) 40%,rgba(6,24,38,.88) 100%)}
.cso-hero--has-img .cso-hero__inner{position:relative;z-index:1}
.cso-hero h1,.cso-hero h2,.cso-hero h3{color:#fff}
.cso-hero__inner{width:100%}
.cso-hero__eyebrow{font-weight:600;letter-spacing:.16em;text-transform:uppercase;margin:0 0 20px;display:block;font-size:16px;color:var(--c-aqua,#59C6F9)}
.cso-hero__title{font-size:124px;color:#fff;margin:0;text-shadow:0 2px 16px rgba(0,0,0,.95),0 6px 48px rgba(0,0,0,.8)}
.cso-hero__title em{font-style:italic;color:var(--c-aqua,#59C6F9)}
.cso-hero__lead{line-height:1.65;opacity:.85;margin:32px 0 0;max-width:700px;font-size:18px;color:#fff}

/* Body */
.cso-archive-body{background:var(--c-bone,#F5F1E7);padding:80px 48px 96px}
.cso-archive-inner{max-width:1320px;margin:0 auto}

/* Filtri corsi */
.cso-corsi-filters{position:sticky;top:0;z-index:5;background:var(--c-bone,#F5F1E7);padding:16px 0 20px;margin-bottom:24px;border-bottom:1px solid rgba(11,26,38,.08)}
html.admin-bar .cso-corsi-filters{top:var(--wp-admin--admin-bar--height,32px)}
.cso-corsi-chips{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.cso-corsi-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:999px;border:1.5px solid rgba(11,26,38,.15);background:#fff;color:rgba(11,26,38,.75);font-size:14px;font-weight:600;cursor:pointer;transition:background .15s,color .15s,border-color .15s;font-family:inherit}
.cso-corsi-chip:hover{border-color:var(--c-deep,#3473A5);color:var(--c-deep,#3473A5)}
.cso-corsi-chip--active,.cso-corsi-chip--active:hover{background:var(--c-deep,#3473A5);border-color:var(--c-deep,#3473A5);color:#fff}
.cso-corsi-chip__count{font-size:12px;font-weight:700;padding:1px 8px;border-radius:999px;background:rgba(11,26,38,.07)}
.cso-corsi-chip--active .cso-corsi-chip__count{background:rgba(255,255,255,.22)}
.cso-corsi-controls{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
.cso-corsi-field{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:rgba(11,26,38,.65);white-space:nowrap;flex-shrink:0}
.cso-corsi-select,.cso-corsi-search{padding:9px 16px;border:1.5px solid rgba(11,26,38,.15);border-radius:999px;font-size:14px;color:var(--c-ink,#0b1a26);background:#fff;outline:none;font-family:inherit}
.cso-corsi-select:focus,.cso-corsi-search:focus{border-color:var(--c-deep,#3473A5)}
.cso-corsi-select{cursor:pointer;padding-right:36px}
.cso-corsi-search{min-width:220px;flex:1;max-width:340px}
.cso-corsi-count{margin-left:auto;font-size:14px;font-weight:600;color:rgba(11,26,38,.6)}
.cso-corsi-reset{font-size:14px;font-weight:600;color:var(--c-wave,#3473A5)!important;text-decoration:underline!important;background:none;border:none;cursor:pointer;font-family:inherit;padding:0}
.cso-corsi-reset[hidden]{display:none}

/* Gruppi */
.cso-corsi-group{margin-bottom:56px}
.cso-corsi-group[hidden]{display:none}
.cso-corsi-group__head{display:flex;align-items:baseline;gap:14px;margin:0 0 20px;padding-bottom:10px;border-bottom:2px solid var(--c-deep,#3473A5)}
.cso-corsi-group__title{font-size:34px;margin:0;line-height:1.1}
.cso-corsi-group__count{font-family:var(--f-mono,monospace);font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:rgba(11,26,38,.55)}
.cso-corsi-group__desc{margin:-8px 0 20px;max-width:760px;font-size:16px;line-height:1.55;color:rgba(11,26,38,.7)}
.cso-corso-card__age{font-size:13px;font-weight:600;color:rgba(11,26,38,.6)}
.cso-corso-card[hidden]{display:none}

/* Griglia */
.cso-corsi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}

/* Card */
.cso-corso-card{background:#fff;border:1px solid rgba(11,26,38,.08);border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.cso-corso-card__img{height:240px;background:linear-gradient(180deg,var(--c-aqua,#59C6F9),var(--c-deep,#3473A5) 60%,var(--c-abyss,#061826));position:relative;overflow:hidden}
.cso-corso-card__img img{width:100%;height:100%;object-fit:cover;display:block}
.cso-corso-card__img--card img{object-fit:contain;padding:24px 32px;box-sizing:border-box;filter:drop-shadow(0 8px 18px rgba(0,0,0,.3))}
.cso-corso-card__body{padding:28px;flex:1;display:flex;flex-direction:column}
.cso-corso-card__meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.cso-corso-card__badge{display:inline-flex;padding:4px 12px;background:rgba(27,119,167,.1);color:var(--c-wave,#3473A5);border-radius:999px;font-size:14px;font-weight:600}
.cso-corso-card__periodo{font-family:var(--f-mono,monospace);font-size:14px;letter-spacing:.08em;color:rgba(11,26,38,.55);text-transform:uppercase}
.cso-corso-card__title{font-size:28px;color:var(--c-deep,#3473A5);margin:0 0 12px;line-height:1.1}
.cso-corso-card__title a{color:inherit;text-decoration:none}
.cso-corso-card__desc{font-size:15px;line-height:1.55;color:rgba(11,26,38,.7);margin:0 0 20px;flex:1;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.cso-corso-card__stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:16px 0;border-top:1px solid rgba(11,26,38,.08);margin-bottom:18px}
.cso-corso-card__stat{display:flex;flex-direction:column;justify-content:flex-end}
.cso-corso-card__stat:not(:first-child){border-left:1px solid rgba(11,26,38,.1);padding-left:12px}
.cso-corso-card__stat-val{font-size:14px;font-weight:700;color:var(--c-ink,#0b1a26);margin-bottom:2px}
.cso-corso-card__stat-lbl{font-family:var(--f-mono,monospace);font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:rgba(11,26,38,.55)}
.cso-btn-coral{display:inline-flex;align-items:center;gap:8px;padding:12px 20px;font-size:15px;font-weight:700;border-radius:999px;text-decoration:none;align-self:flex-start;transition:background .15s;border:none;cursor:pointer;font-family:inherit}

/* Empty */
.cso-empty{padding:64px 0;text-align:center}
.cso-empty__title{font-size:24px;color:var(--c-deep,#3473A5)}
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
	.cso-corsi-filters{position:static}
	.cso-corsi-chips{flex-wrap:nowrap;overflow-x:auto;padding-bottom:4px}
	.cso-corsi-chip{flex:0 0 auto}
	.cso-corsi-search{max-width:none;width:100%}
	.cso-corsi-field{flex:1 1 calc(50% - 6px);min-width:0}
	.cso-corsi-field .cso-corsi-select{width:100%;min-width:0;text-overflow:ellipsis}
	.cso-corsi-count{margin-left:0}
	.cso-corsi-group__title{font-size:26px}
}
@media(max-width:420px){
	.cso-archive .cso-hero__title{font-size:42px}
}
</style>
<style>
.cso-archive .cso-btn-coral{background:<?php echo esc_attr( $_acd['cta_bg'] ); ?>;color:<?php echo esc_attr( $_acd['cta_color'] ); ?>}
.cso-archive .cso-btn-coral:hover{background:<?php echo esc_attr( $_acd['cta_hover_bg'] ); ?>}
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
	<nav class="cso-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'calypsosub' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'calypsosub' ); ?></a>
		<span>/</span>
		<span class="cso-breadcrumb__current"><?php echo esc_html( calypsosub_opt( 'corsi', 'breadcrumb_archive', __( 'Corsi', 'calypsosub' ) ) ); ?></span>
	</nav>
	<span class="cso-hero__eyebrow"><?php echo esc_html( str_replace( '{anno}', gmdate( 'Y' ),
		calypsosub_opt( 'corsi', 'archive_eyebrow', __( 'Corsi · {anno}', 'calypsosub' ) )
	) ); ?></span>
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
	<div class="cso-corsi-filters" id="cso-corsi-filters">
		<?php if ( count( $categorie ) > 1 ) : ?>
		<div class="cso-corsi-chips" role="group" aria-label="<?php esc_attr_e( 'Filtra per categoria', 'calypsosub' ); ?>">
			<button type="button" class="cso-corsi-chip cso-corsi-chip--active" data-categoria="" aria-pressed="true">
				<?php esc_html_e( 'Tutti', 'calypsosub' ); ?>
				<span class="cso-corsi-chip__count"><?php echo esc_html( (string) count( $corsi ) ); ?></span>
			</button>
			<?php foreach ( $gruppi as $key => $gruppo ) :
				if ( ! $gruppo['term'] ) continue; ?>
			<button type="button" class="cso-corsi-chip" data-categoria="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
				<?php echo esc_html( $gruppo['term']->name ); ?>
				<span class="cso-corsi-chip__count"><?php echo esc_html( (string) count( $gruppo['corsi'] ) ); ?></span>
			</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="cso-corsi-controls">
			<?php if ( count( $livelli_terms ) > 1 ) : ?>
			<label class="cso-corsi-field">
				<span class="screen-reader-text"><?php esc_html_e( 'Livello', 'calypsosub' ); ?></span>
				<select class="cso-corsi-select" id="cso-corsi-livello">
					<option value=""><?php esc_html_e( 'Tutti i livelli', 'calypsosub' ); ?></option>
					<?php foreach ( $livelli_terms as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php endif; ?>

			<?php if ( count( $fasce_eta ) > 1 ) : ?>
			<label class="cso-corsi-field">
				<span class="screen-reader-text"><?php esc_html_e( 'Età di accesso', 'calypsosub' ); ?></span>
				<select class="cso-corsi-select" id="cso-corsi-eta">
					<option value=""><?php esc_html_e( 'Tutte le età', 'calypsosub' ); ?></option>
					<?php foreach ( $fasce_eta as $f ) : ?>
					<option value="<?php echo esc_attr( $f[0] ); ?>"><?php echo esc_html( $f[1] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php endif; ?>

			<input type="search" class="cso-corsi-search" id="cso-corsi-search"
			       placeholder="<?php esc_attr_e( 'Cerca un corso…', 'calypsosub' ); ?>"
			       aria-label="<?php esc_attr_e( 'Cerca un corso', 'calypsosub' ); ?>">

			<button type="button" class="cso-corsi-reset" id="cso-corsi-reset" hidden><?php esc_html_e( 'Azzera filtri', 'calypsosub' ); ?></button>
			<span class="cso-corsi-count" id="cso-corsi-count" aria-live="polite"></span>
		</div>
	</div>

	<!-- Gruppi -->
	<div id="cso-corsi-groups">
	<?php foreach ( $gruppi as $key => $gruppo ) :
		$term = $gruppo['term']; ?>
	<section class="cso-corsi-group" data-group="<?php echo esc_attr( $key ); ?>"<?php echo $mostra_titoli_gruppo ? ' aria-labelledby="cso-gruppo-' . esc_attr( $key ) . '"' : ''; ?>>
		<?php if ( $mostra_titoli_gruppo ) : ?>
		<div class="cso-corsi-group__head">
			<h2 class="cso-corsi-group__title display" id="cso-gruppo-<?php echo esc_attr( $key ); ?>">
				<?php echo esc_html( $term ? $term->name : __( 'Altri corsi', 'calypsosub' ) ); ?>
			</h2>
			<span class="cso-corsi-group__count" data-group-count></span>
		</div>
		<?php if ( $term && $term->description ) : ?>
		<p class="cso-corsi-group__desc"><?php echo esc_html( $term->description ); ?></p>
		<?php endif; ?>
		<?php endif; ?>

		<div class="cso-corsi-grid">
		<?php foreach ( $gruppo['corsi'] as $corso ) :
			$periodo  = (string) get_post_meta( $corso->ID, '_corso_periodo',          true );
			$desc     = (string) get_post_meta( $corso->ID, '_corso_desc_breve',       true );
			$durata   = (string) get_post_meta( $corso->ID, '_corso_stat_durata',      true );
			$pratica  = (string) get_post_meta( $corso->ID, '_corso_stat_pratica',     true );
			$prof     = (string) get_post_meta( $corso->ID, '_corso_stat_profondita',  true );
			$link_ovr = (string) get_post_meta( $corso->ID, '_corso_link_iscrizione',  true );
			$eta_min  = (string) get_post_meta( $corso->ID, '_corso_eta_min',          true );

			$livelli_slugs = wp_get_post_terms( $corso->ID, 'calypso_livello', [ 'fields' => 'slugs' ] );
			$livelli_slugs = is_wp_error( $livelli_slugs ) ? [] : $livelli_slugs;
			$livelli_names = wp_get_post_terms( $corso->ID, 'calypso_livello', [ 'fields' => 'names' ] );
			$livelli_names = is_wp_error( $livelli_names ) ? [] : $livelli_names;
			// Niente fallback sul badge certificazione (_corso_badge): è un campo
			// distinto, non un sostituto del livello. Nessun termine = nessuna pill.
			$livello_label = ! empty( $livelli_names ) ? $livelli_names[0] : '';

			$cta_url = $link_ovr ?: ( $prenotazioni_page_ok ? add_query_arg( 'prenota_id', $corso->ID, get_permalink( $prenotazioni_page_id ) ) : '' );
			$img_url     = get_the_post_thumbnail_url( $corso->ID, 'medium_large' );
			$img_is_card = false;
			if ( ! $img_url ) {
				/* Nessuna immagine in evidenza: si usa la tessera del brevetto. */
				$card_id = (int) get_post_meta( $corso->ID, '_corso_brevetto_img_id', true );
				$img_url = $card_id ? (string) wp_get_attachment_image_url( $card_id, 'medium_large' ) : '';
				$img_is_card = $img_url !== '';
			}
			$search  = mb_strtolower( wp_strip_all_tags( implode( ' ', [ $corso->post_title, $desc, $term ? $term->name : '', implode( ' ', $livelli_names ) ] ) ) );
		?>
		<article class="cso-corso-card"
		         data-categoria="<?php echo esc_attr( $key ); ?>"
		         data-livello="<?php echo esc_attr( implode( ' ', $livelli_slugs ) ); ?>"
		         data-eta="<?php echo esc_attr( $eta_min ); ?>"
		         data-search="<?php echo esc_attr( $search ); ?>">

			<div class="cso-corso-card__img<?php echo $img_is_card ? ' cso-corso-card__img--card' : ''; ?>">
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
					<?php if ( $eta_min !== '' ) : ?>
					<span class="cso-corso-card__age"><?php echo esc_html( sprintf( __( 'Dai %s anni', 'calypsosub' ), $eta_min ) ); ?></span>
					<?php elseif ( $periodo ) : ?>
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

				<?php if ( $cta_url ) : ?>
				<a href="<?php echo esc_url( $cta_url ); ?>" class="cso-btn-coral">
					<?php esc_html_e( 'Richiedi iscrizione', 'calypsosub' ); ?>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
				</a>
				<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
				<span class="cso-btn-coral" style="opacity:.5;cursor:default" title="<?php esc_attr_e( 'Imposta la Pagina prenotazioni in Calypso Sub → Impostazioni → Generali', 'calypsosub' ); ?>"><?php esc_html_e( 'Configura pagina prenotazioni', 'calypsosub' ); ?></span>
				<?php endif; ?>

			</div>
		</article>
		<?php endforeach; ?>
		</div><!-- .cso-corsi-grid -->
	</section>
	<?php endforeach; ?>
	</div><!-- #cso-corsi-groups -->

	<div class="cso-empty" id="cso-corsi-empty" hidden>
		<p class="cso-empty__title"><?php esc_html_e( 'Nessun corso trovato.', 'calypsosub' ); ?></p>
		<p class="cso-empty__sub">
			<a href="#" id="cso-corsi-empty-reset"><?php esc_html_e( 'Rimuovi filtri', 'calypsosub' ); ?></a>
		</p>
	</div>

</div><!-- .cso-archive-inner -->
</section>
</div><!-- .cso-archive -->

<script>
(function () {
	var root     = document.getElementById('cso-corsi-groups');
	if (!root) return;
	var groups   = root.querySelectorAll('.cso-corsi-group');
	var cards    = root.querySelectorAll('.cso-corso-card');
	var chips    = document.querySelectorAll('.cso-corsi-chip');
	var livello  = document.getElementById('cso-corsi-livello');
	var eta      = document.getElementById('cso-corsi-eta');
	var search   = document.getElementById('cso-corsi-search');
	var reset    = document.getElementById('cso-corsi-reset');
	var count    = document.getElementById('cso-corsi-count');
	var empty    = document.getElementById('cso-corsi-empty');
	var emptyRst = document.getElementById('cso-corsi-empty-reset');
	var state    = { categoria: '', livello: '', eta: '', q: '' };

	function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }

	function apply() {
		var q   = state.q.toLowerCase().trim();
		var range = state.eta ? state.eta.split('-') : null;
		var ageMin = range ? parseInt(range[0], 10) : NaN;
		var ageMax = range && range[1] !== '' ? parseInt(range[1], 10) : Infinity;
		var tot = 0;
		cards.forEach(function (card) {
			var cardAge = parseInt(card.dataset.eta, 10);
			var ok = (!state.categoria || card.dataset.categoria === state.categoria)
				&& (!state.livello || (' ' + card.dataset.livello + ' ').indexOf(' ' + state.livello + ' ') !== -1)
				&& (!range || (!isNaN(cardAge) && cardAge >= ageMin && cardAge <= ageMax))
				&& (!q || card.dataset.search.indexOf(q) !== -1);
			card.hidden = !ok;
			if (ok) tot++;
		});
		groups.forEach(function (group) {
			var visible = group.querySelectorAll('.cso-corso-card:not([hidden])').length;
			group.hidden = visible === 0;
			var label = group.querySelector('[data-group-count]');
			if (label) label.textContent = plural(visible, '<?php echo esc_js( __( 'corso', 'calypsosub' ) ); ?>', '<?php echo esc_js( __( 'corsi', 'calypsosub' ) ); ?>');
		});
		chips.forEach(function (chip) {
			var on = chip.dataset.categoria === state.categoria;
			chip.classList.toggle('cso-corsi-chip--active', on);
			chip.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		var filtered = state.categoria || state.livello || state.eta || state.q;
		if (count) count.textContent = plural(tot, '<?php echo esc_js( __( 'corso', 'calypsosub' ) ); ?>', '<?php echo esc_js( __( 'corsi', 'calypsosub' ) ); ?>');
		if (reset) reset.hidden = !filtered;
		if (empty) empty.hidden = tot !== 0;
		syncUrl();
	}

	function syncUrl() {
		if (!window.history || !history.replaceState) return;
		var params = new URLSearchParams(window.location.search);
		['categoria', 'livello', 'eta', 'q'].forEach(function (k) {
			if (state[k]) params.set(k, state[k]); else params.delete(k);
		});
		var qs = params.toString();
		history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : '') + window.location.hash);
	}

	function clearAll(e) {
		if (e) e.preventDefault();
		state = { categoria: '', livello: '', eta: '', q: '' };
		if (livello) livello.value = '';
		if (eta) eta.value = '';
		if (search) search.value = '';
		apply();
	}

	chips.forEach(function (chip) {
		chip.addEventListener('click', function () {
			state.categoria = chip.dataset.categoria;
			apply();
		});
	});
	if (livello) livello.addEventListener('change', function () { state.livello = livello.value; apply(); });
	if (eta) eta.addEventListener('change', function () { state.eta = eta.value; apply(); });
	if (search) {
		var t;
		search.addEventListener('input', function () {
			clearTimeout(t);
			t = setTimeout(function () { state.q = search.value; apply(); }, 150);
		});
	}
	if (reset) reset.addEventListener('click', clearAll);
	if (emptyRst) emptyRst.addEventListener('click', clearAll);

	/* Filtri iniziali dall'indirizzo (es. /corsi/?categoria=apnea&eta=12). */
	var params = new URLSearchParams(window.location.search);
	state.categoria = params.get('categoria') || '';
	if (state.categoria && !document.querySelector('.cso-corsi-chip[data-categoria="' + CSS.escape(state.categoria) + '"]')) state.categoria = '';
	state.livello = livello && params.get('livello') ? params.get('livello') : '';
	if (livello) { livello.value = state.livello; state.livello = livello.value; }
	state.eta = eta && params.get('eta') ? params.get('eta') : '';
	if (eta) { eta.value = state.eta; state.eta = eta.value; }
	state.q = search && params.get('q') ? params.get('q') : '';
	if (search) search.value = state.q;
	apply();
})();
</script>

<?php
if ( $cso_footer_html !== '' ) {
	echo '<footer class="wp-block-template-part cso-site-footer-wrap">' . $cso_footer_html . '</footer>';
}
get_footer();
?>
