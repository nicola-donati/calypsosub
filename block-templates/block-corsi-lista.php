<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$corsi = calypso_get_corsi();
?>
<style>
.calypso-corsi{max-width:1320px;margin:0 auto;padding:0 24px}
.calypso-corsi__filters{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:28px}
.calypso-list__filter-input{padding:8px 14px;border:1px solid var(--c-foam);border-radius:var(--radius);font-size:14px;color:var(--c-ink)}
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
@media(max-width:640px){.calypso-corsi__grid{grid-template-columns:1fr}}
</style>

<div class="calypso-corsi" data-list="corsi">
	<div class="calypso-corsi__filters">
		<input type="text" class="calypso-list__filter-input" data-filter="search"
		       placeholder="<?php esc_attr_e( 'Cerca corso…', 'calypsosub' ); ?>">
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

			$livelli = wp_get_post_terms( $post_id, 'calypso_livello', [ 'fields' => 'names' ] );
			$livello = ( ! is_wp_error( $livelli ) && ! empty( $livelli ) ) ? $livelli[0] : '';

			$next_occ = calypso_get_next_occorrenza( $post_id );
			$periodo  = $next_occ ? calypso_get_occorrenza_periodo( $next_occ->ID ) : '';

			$stats = [];
			if ( $stat_durata )     $stats[] = $stat_durata;
			if ( $stat_pratica )    $stats[] = $stat_pratica;
			if ( $stat_profondita ) $stats[] = $stat_profondita;

			$title_upper = mb_strtoupper( $post->post_title );
		?>
		<article class="calypso-corso-card"
		         data-search="<?php echo esc_attr( strtolower( $post->post_title . ' ' . $livello ) ); ?>">
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
	var cards = list.querySelectorAll('.calypso-corso-card');
	list.querySelector('[data-filter="search"]').addEventListener('input', function () {
		var q = this.value.toLowerCase();
		cards.forEach(function (c) { c.style.display = !q || c.dataset.search.includes(q) ? '' : 'none'; });
	});
})();
</script>
