<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$corsi        = calypso_get_corsi();
$all_livelli  = get_terms( [ 'taxonomy' => 'calypso_livello', 'hide_empty' => true ] );
$has_livelli  = ! is_wp_error( $all_livelli ) && ! empty( $all_livelli );
?>
<style>
.calypso-corsi{max-width:1320px;margin:0 auto;padding:0 24px}

/* ── Filtri ── */
.calypso-corsi__filters{
  display:flex;align-items:center;justify-content:space-between;
  gap:16px;margin-bottom:32px;flex-wrap:wrap;
}
.calypso-corsi__levels{display:flex;gap:8px;flex-wrap:wrap}
.calypso-corsi__pill{
  padding:8px 18px;border-radius:999px;
  border:1.5px solid rgba(11,26,38,.15);
  background:transparent;color:rgba(11,26,38,.7);
  font-size:13px;font-weight:600;font-family:inherit;
  cursor:pointer;line-height:1;
  transition:background .15s,color .15s,border-color .15s;
}
.calypso-corsi__pill:hover{background:var(--c-deep,.0a2540);color:#fff;border-color:var(--c-deep,#0a2540)}
.calypso-corsi__pill.is-active{background:var(--c-deep,#0a2540);color:#fff;border-color:var(--c-deep,#0a2540)}
.calypso-corsi__search-wrap{position:relative;display:flex;align-items:center}
.calypso-corsi__search-wrap svg{position:absolute;left:12px;color:rgba(11,26,38,.35);pointer-events:none}
.calypso-list__filter-input{
  padding:9px 16px 9px 36px;
  border:1.5px solid rgba(11,26,38,.15);border-radius:999px;
  font-size:13px;font-weight:500;font-family:inherit;
  color:var(--c-ink,#0b1a26);background:#fff;
  min-width:200px;transition:border-color .15s;
}
.calypso-list__filter-input::placeholder{color:rgba(11,26,38,.4)}
.calypso-list__filter-input:focus{outline:none;border-color:var(--c-wave,#1B77A7)}

/* ── Grid ── */
.calypso-corsi__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px}
.calypso-corso-card__photo-wrap{position:relative;overflow:hidden}
.calypso-corso-card__photo-label{position:absolute;bottom:0;left:0;background:rgba(11,26,38,.72);color:var(--c-sand);font-size:10px;font-weight:500;letter-spacing:.14em;text-transform:uppercase;padding:6px 12px;backdrop-filter:blur(2px)}
.calypso-corso-card__stats{font-size:11px;font-weight:400;letter-spacing:.05em;padding:10px 20px;position:relative}
.calypso-corso-card__stats::before{content:'';position:absolute;top:0;left:20px;right:20px;height:1px;background:#e8e8e8}
.calypso-corso-card__stats-items{display:flex;flex-wrap:wrap;gap:0;list-style:none;margin:0;padding:0}
.calypso-corso-card__stats-items li{display:inline;color:#666!important}
.calypso-corso-card__stats-items li+li::before{content:" · ";color:#ccc!important}
.calypso-corso-card__footer{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:12px 20px 16px;flex-wrap:wrap;border-top:1px solid #f0f0f0}
.calypso-corso-card__period{font-size:11px;font-weight:600;color:var(--c-wave)!important;text-transform:uppercase;letter-spacing:.06em}
@media(max-width:640px){
  .calypso-corsi__grid{grid-template-columns:1fr}
  .calypso-list__filter-input{min-width:0;width:100%}
}
</style>

<div class="calypso-corsi" data-list="corsi">
	<div class="calypso-corsi__filters">

		<?php if ( $has_livelli ) : ?>
		<div class="calypso-corsi__levels">
			<button class="calypso-corsi__pill is-active" data-level=""><?php _e( 'Tutti', 'calypsosub' ); ?></button>
			<?php foreach ( $all_livelli as $term ) : ?>
			<button class="calypso-corsi__pill" data-level="<?php echo esc_attr( $term->slug ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="calypso-corsi__search-wrap">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
			<input type="text" class="calypso-list__filter-input" data-filter="search"
			       placeholder="<?php esc_attr_e( 'Cerca corso…', 'calypsosub' ); ?>">
		</div>

	</div>

	<?php if ( empty( $corsi ) ) : ?>
		<p class="calypso-empty"><?php _e( 'Nessun corso disponibile.', 'calypsosub' ); ?></p>
	<?php else : ?>
	<div class="calypso-corsi__grid">
		<?php foreach ( $corsi as $post ) :
			$post_id         = $post->ID;
			$desc_breve      = get_post_meta( $post_id, '_corso_desc_breve', true );
			$stat_durata     = get_post_meta( $post_id, '_corso_stat_durata', true );
			$stat_pratica    = get_post_meta( $post_id, '_corso_stat_pratica', true );
			$stat_profondita = get_post_meta( $post_id, '_corso_stat_profondita', true );
			$link_iscrizione = get_post_meta( $post_id, '_corso_link_iscrizione', true );
			$img             = get_the_post_thumbnail_url( $post_id, 'medium_large' );

			$livelli_raw  = wp_get_post_terms( $post_id, 'calypso_livello' );
			$livello_obj  = ( ! is_wp_error( $livelli_raw ) && ! empty( $livelli_raw ) ) ? $livelli_raw[0] : null;
			$livello      = $livello_obj ? $livello_obj->name : '';
			$livello_slug = $livello_obj ? $livello_obj->slug : '';

			$next_occ = calypso_get_next_occorrenza( $post_id );
			$periodo  = $next_occ ? calypso_get_occorrenza_periodo( $next_occ->ID ) : '';

			$stats = [];
			if ( $stat_durata )     $stats[] = $stat_durata;
			if ( $stat_pratica )    $stats[] = $stat_pratica;
			if ( $stat_profondita ) $stats[] = $stat_profondita;

			$title_upper = mb_strtoupper( $post->post_title );
		?>
		<article class="calypso-corso-card"
		         data-search="<?php echo esc_attr( strtolower( $post->post_title . ' ' . $livello ) ); ?>"
		         data-livello="<?php echo esc_attr( $livello_slug ); ?>">
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" style="display:contents;text-decoration:none">
				<div class="calypso-corso-card__photo-wrap">
					<?php if ( $img ) : ?>
						<img class="calypso-corso-card__img"
						     src="<?php echo esc_url( $img ); ?>"
						     alt="<?php echo esc_attr( $post->post_title ); ?>">
					<?php else : ?>
						<div class="calypso-corso-card__img-placeholder">🎓</div>
					<?php endif; ?>
					<div class="calypso-corso-card__photo-label">
						<?php echo esc_html( 'CORSO · ' . $title_upper ); ?>
					</div>
				</div>
				<div class="calypso-corso-card__body">
					<?php if ( $livello ) : ?>
						<span class="calypso-corso-card__level"><?php echo esc_html( $livello ); ?></span>
					<?php endif; ?>
					<h3 class="calypso-corso-card__title"><?php echo esc_html( $post->post_title ); ?></h3>
					<?php if ( $desc_breve ) : ?>
						<p class="calypso-corso-card__desc"><?php echo esc_html( $desc_breve ); ?></p>
					<?php endif; ?>
				</div>
			</a>
			<?php if ( ! empty( $stats ) ) : ?>
			<div class="calypso-corso-card__stats">
				<ul class="calypso-corso-card__stats-items">
					<?php foreach ( $stats as $item ) : ?>
						<li><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="calypso-corso-card__footer">
				<span class="calypso-corso-card__period">
					<?php echo $periodo ? esc_html( $periodo ) : esc_html__( 'Date da definire', 'calypsosub' ); ?>
				</span>
				<?php if ( $link_iscrizione ) : ?>
					<a href="<?php echo esc_url( $link_iscrizione ); ?>"
					   class="calypso-btn-gold" target="_blank" rel="noopener">
						<?php _e( 'Iscriviti →', 'calypsosub' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</article>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>

<script>
(function () {
	var list = document.querySelector('[data-list="corsi"]');
	if (!list) return;
	var cards       = Array.from(list.querySelectorAll('.calypso-corso-card'));
	var searchInput = list.querySelector('[data-filter="search"]');
	var pills       = Array.from(list.querySelectorAll('.calypso-corsi__pill'));
	var activeLevel = '';

	function applyFilters() {
		var q = searchInput ? searchInput.value.toLowerCase() : '';
		cards.forEach(function (c) {
			var matchSearch = !q || c.dataset.search.includes(q);
			var matchLevel  = !activeLevel || c.dataset.livello === activeLevel;
			c.style.display = (matchSearch && matchLevel) ? '' : 'none';
		});
	}

	if (searchInput) searchInput.addEventListener('input', applyFilters);

	pills.forEach(function (pill) {
		pill.addEventListener('click', function () {
			pills.forEach(function (p) { p.classList.remove('is-active'); });
			this.classList.add('is-active');
			activeLevel = this.dataset.level || '';
			applyFilters();
		});
	});
})();
</script>
