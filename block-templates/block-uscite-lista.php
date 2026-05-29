<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$uscite = calypso_get_uscite();
?>
<style>
.calypso-list{max-width:1320px;margin:0 auto;padding:0 24px}
.calypso-list__filters{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:28px}
.calypso-list__filter-input{padding:8px 14px;border:1px solid var(--c-foam);border-radius:var(--radius);font-size:14px;color:var(--c-ink)}
.calypso-list__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px}
.calypso-card{background:#fff;border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 8px rgba(10,37,64,.08);transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column}
.calypso-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(10,37,64,.14)}
.calypso-card__img{width:100%;height:200px;object-fit:cover;background:var(--c-foam)}
.calypso-card__img-placeholder{width:100%;height:200px;background:var(--c-foam);display:flex;align-items:center;justify-content:center;color:var(--c-wave);font-size:40px}
.calypso-card__body{padding:20px;flex:1;display:flex;flex-direction:column}
.calypso-card__badge{display:inline-block;background:var(--c-foam);color:var(--c-wave);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:3px 10px;border-radius:20px;margin-bottom:10px}
.calypso-card__title{font-size:22px;color:var(--c-deep);margin:0 0 6px;line-height:1.15}
.calypso-card__subtitle{color:var(--c-wave);font-size:14px;margin:0 0 10px}
.calypso-card__meta{font-size:13px;color:#555;margin-bottom:12px;display:flex;flex-direction:column;gap:4px}
.calypso-card__meta span{display:flex;align-items:center;gap:6px}
.calypso-card__desc{font-size:14px;color:#444;flex:1;margin-bottom:16px;line-height:1.6}
.calypso-card__footer{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.calypso-card__spots{font-size:13px;font-weight:600;color:var(--c-wave)}
.calypso-card__spots--full{color:var(--c-coral)}
.calypso-btn{display:inline-block;background:var(--c-coral);color:#fff;font-size:15px;letter-spacing:.04em;text-transform:uppercase;padding:10px 20px;border-radius:var(--radius);text-decoration:none;border:none;cursor:pointer;transition:background .15s}
.calypso-btn:hover{background:#e04a2a}
.calypso-btn--secondary{background:transparent;border:2px solid var(--c-coral);color:var(--c-coral)}
.calypso-btn--secondary:hover{background:var(--c-coral);color:#fff}
.calypso-empty{text-align:center;color:#888;padding:48px 0;font-size:16px}
@media(max-width:640px){.calypso-list__grid{grid-template-columns:1fr}}
</style>

<div class="calypso-list" data-list="uscite">
	<div class="calypso-list__filters">
		<input type="text" class="calypso-list__filter-input" data-filter="search"
		       placeholder="<?php esc_attr_e( 'Cerca uscita…', 'calypsosub' ); ?>">
		<input type="text" class="calypso-list__filter-input" data-filter="luogo"
		       placeholder="<?php esc_attr_e( 'Filtra per luogo…', 'calypsosub' ); ?>">
	</div>

	<?php if ( empty( $uscite ) ) : ?>
		<p class="calypso-empty"><?php _e( 'Nessuna uscita in programma.', 'calypsosub' ); ?></p>
	<?php else : ?>
	<div class="calypso-list__grid">
		<?php foreach ( $uscite as $post ) :
			$post_id  = $post->ID;
			$luogo    = get_post_meta( $post_id, '_uscita_luogo', true );
			$date_raw = (array) ( get_post_meta( $post_id, '_uscita_date', true ) ?: [] );
			$prima_data = ! empty( $date_raw )
				? date_i18n( get_option( 'date_format' ), strtotime( $date_raw[0] ) )
				: '';
			$max      = get_post_meta( $post_id, '_uscita_max_partecipanti', true );
			$spots    = calypso_can_book( $post_id, get_current_user_id() )
				? ( isset( $GLOBALS['calypsosub_booking_manager'] )
					? $GLOBALS['calypsosub_booking_manager']->get_remaining_spots( $post_id )
					: null )
				: null;
			$desc_breve = get_post_meta( $post_id, '_uscita_desc_breve', true );
			$img      = get_the_post_thumbnail_url( $post_id, 'medium_large' );
		?>
		<article class="calypso-card"
		         data-search="<?php echo esc_attr( strtolower( $post->post_title . ' ' . $luogo ) ); ?>"
		         data-luogo="<?php echo esc_attr( strtolower( $luogo ) ); ?>">
			<?php if ( $img ) : ?>
				<img class="calypso-card__img" src="<?php echo esc_url( $img ); ?>"
				     alt="<?php echo esc_attr( $post->post_title ); ?>">
			<?php else : ?>
				<div class="calypso-card__img-placeholder">🤿</div>
			<?php endif; ?>
			<div class="calypso-card__body">
				<span class="calypso-card__badge"><?php _e( 'Uscita', 'calypsosub' ); ?></span>
				<h3 class="calypso-card__title">
					<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" style="color:inherit;text-decoration:none">
						<?php echo esc_html( $post->post_title ); ?>
					</a>
				</h3>
				<?php if ( $luogo ) : ?>
					<p class="calypso-card__subtitle">📍 <?php echo esc_html( $luogo ); ?></p>
				<?php endif; ?>
				<div class="calypso-card__meta">
					<?php if ( $prima_data ) : ?>
						<span>📅 <?php echo esc_html( $prima_data ); ?></span>
					<?php endif; ?>
					<?php if ( count( $date_raw ) > 1 ) : ?>
						<span><?php printf( esc_html__( '+%d date', 'calypsosub' ), count( $date_raw ) - 1 ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $desc_breve ) : ?>
					<p class="calypso-card__desc"><?php echo esc_html( $desc_breve ); ?></p>
				<?php endif; ?>
				<div class="calypso-card__footer">
					<?php if ( $max !== '' && $max !== false ) :
						$class = $spots === 0 ? 'calypso-card__spots--full' : '';
					?>
						<span class="calypso-card__spots <?php echo esc_attr( $class ); ?>">
							<?php if ( $spots === 0 ) :
								_e( 'Esaurito', 'calypsosub' );
							else :
								printf( esc_html__( '%d posti liberi', 'calypsosub' ), (int) $spots );
							endif; ?>
						</span>
					<?php else : ?>
						<span class="calypso-card__spots"><?php _e( 'Partecipazione libera', 'calypsosub' ); ?></span>
					<?php endif; ?>
					<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"
					   class="calypso-btn"><?php _e( 'Dettagli', 'calypsosub' ); ?></a>
				</div>
			</div>
		</article>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>

<script>
(function () {
	var list = document.querySelector('[data-list="uscite"]');
	if (!list) return;
	var cards = list.querySelectorAll('.calypso-card');
	list.querySelectorAll('.calypso-list__filter-input').forEach(function (inp) {
		inp.addEventListener('input', filter);
	});
	function filter() {
		var search = (list.querySelector('[data-filter="search"]').value || '').toLowerCase();
		var luogo  = (list.querySelector('[data-filter="luogo"]').value || '').toLowerCase();
		cards.forEach(function (card) {
			var show = (!search || card.dataset.search.includes(search))
			        && (!luogo  || card.dataset.luogo.includes(luogo));
			card.style.display = show ? '' : 'none';
		});
	}
})();
</script>
