<?php
/**
 * Block template: Lista Eventi
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$eventi = calypso_get_eventi();
?>
<style>
.calypso-list{--c-deep:#0a2540;--c-wave:#1d6f9c;--c-coral:#ff6b4a;--c-bone:#f6f1e6;--c-foam:#cfe9ee;--c-ink:#0b1a26;--radius:4px;--radius-lg:12px;--f-body:"DM Sans",-apple-system,BlinkMacSystemFont,sans-serif;--f-display:"Big Shoulders Display","Anton",Impact,sans-serif;font-family:var(--f-body);max-width:1320px;margin:0 auto;padding:0 24px}
.calypso-list__filters{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:28px}
.calypso-list__filter-input{padding:8px 14px;border:1px solid var(--c-foam);border-radius:var(--radius);font-family:var(--f-body);font-size:14px;color:var(--c-ink)}
.calypso-list__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px}
.calypso-card{background:#fff;border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 8px rgba(10,37,64,.08);transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column}
.calypso-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(10,37,64,.14)}
.calypso-card__img{width:100%;height:200px;object-fit:cover;background:var(--c-foam)}
.calypso-card__img-placeholder{width:100%;height:200px;background:var(--c-bone);display:flex;align-items:center;justify-content:center;color:var(--c-wave);font-size:40px}
.calypso-card__body{padding:20px;flex:1;display:flex;flex-direction:column}
.calypso-card__badge{display:inline-block;background:var(--c-bone);color:var(--c-deep);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:3px 10px;border-radius:20px;margin-bottom:10px}
.calypso-card__title{font-family:var(--f-display);font-size:22px;color:var(--c-deep);margin:0 0 6px;line-height:1.15}
.calypso-card__subtitle{color:var(--c-wave);font-size:14px;margin:0 0 10px}
.calypso-card__meta{font-size:13px;color:#555;margin-bottom:12px;display:flex;flex-direction:column;gap:4px}
.calypso-card__desc{font-size:14px;color:#444;flex:1;margin-bottom:16px;line-height:1.6}
.calypso-card__footer{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.calypso-card__spots{font-size:13px;font-weight:600;color:var(--c-wave)}
.calypso-card__spots--full{color:var(--c-coral)}
.calypso-btn{display:inline-block;background:var(--c-coral);color:#fff;font-family:var(--f-display);font-size:15px;letter-spacing:.04em;text-transform:uppercase;padding:10px 20px;border-radius:var(--radius);text-decoration:none;border:none;cursor:pointer;transition:background .15s}
.calypso-btn:hover{background:#e04a2a}
.calypso-empty{text-align:center;color:#888;padding:48px 0;font-size:16px}
@media(max-width:640px){.calypso-list__grid{grid-template-columns:1fr}}
</style>

<div class="calypso-list" data-list="eventi">
	<div class="calypso-list__filters">
		<input type="text" class="calypso-list__filter-input" data-filter="search"
		       placeholder="<?php esc_attr_e( 'Cerca evento…', 'calypsosub' ); ?>">
	</div>

	<?php if ( empty( $eventi ) ) : ?>
		<p class="calypso-empty"><?php _e( 'Nessun evento in programma.', 'calypsosub' ); ?></p>
	<?php else : ?>
	<div class="calypso-list__grid">
		<?php foreach ( $eventi as $post ) :
			$post_id    = $post->ID;
			$luogo      = get_post_meta( $post_id, '_evento_luogo', true );
			$date_raw   = (array) ( get_post_meta( $post_id, '_evento_date', true ) ?: [] );
			$prima_data = ! empty( $date_raw )
				? date_i18n( get_option( 'date_format' ), strtotime( $date_raw[0] ) )
				: '';
			$max        = get_post_meta( $post_id, '_evento_max_partecipanti', true );
			$spots      = isset( $GLOBALS['calypsosub_booking_manager'] )
				? $GLOBALS['calypsosub_booking_manager']->get_remaining_spots( $post_id )
				: null;
			$desc_breve = get_post_meta( $post_id, '_evento_desc_breve', true );
			$img        = get_the_post_thumbnail_url( $post_id, 'medium_large' );
		?>
		<article class="calypso-card"
		         data-search="<?php echo esc_attr( strtolower( $post->post_title . ' ' . $luogo ) ); ?>">
			<?php if ( $img ) : ?>
				<img class="calypso-card__img" src="<?php echo esc_url( $img ); ?>"
				     alt="<?php echo esc_attr( $post->post_title ); ?>">
			<?php else : ?>
				<div class="calypso-card__img-placeholder">🎉</div>
			<?php endif; ?>
			<div class="calypso-card__body">
				<span class="calypso-card__badge"><?php _e( 'Evento', 'calypsosub' ); ?></span>
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
				</div>
				<?php if ( $desc_breve ) : ?>
					<p class="calypso-card__desc"><?php echo esc_html( $desc_breve ); ?></p>
				<?php endif; ?>
				<div class="calypso-card__footer">
					<?php if ( $max !== '' && $max !== false ) :
						$full = $spots !== null && $spots === 0;
					?>
						<span class="calypso-card__spots <?php echo $full ? 'calypso-card__spots--full' : ''; ?>">
							<?php if ( $full ) :
								_e( 'Esaurito', 'calypsosub' );
							else :
								printf( esc_html__( '%d posti liberi', 'calypsosub' ), (int) $spots );
							endif; ?>
						</span>
					<?php else : ?>
						<span class="calypso-card__spots"><?php _e( 'Ingresso libero', 'calypsosub' ); ?></span>
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
	var list = document.querySelector('[data-list="eventi"]');
	if (!list) return;
	var cards = list.querySelectorAll('.calypso-card');
	list.querySelector('[data-filter="search"]').addEventListener('input', function () {
		var q = this.value.toLowerCase();
		cards.forEach(function (c) { c.style.display = !q || c.dataset.search.includes(q) ? '' : 'none'; });
	});
})();
</script>
