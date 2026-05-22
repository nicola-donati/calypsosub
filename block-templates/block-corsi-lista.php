<?php
/**
 * Block template: Lista Corsi
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$corsi = calypso_get_corsi();
?>
<style>
.calypso-list{--c-deep:#0a2540;--c-wave:#1d6f9c;--c-coral:#ff6b4a;--c-bone:#f6f1e6;--c-foam:#cfe9ee;--c-ink:#0b1a26;--radius:4px;--radius-lg:12px;--f-body:"DM Sans",-apple-system,BlinkMacSystemFont,sans-serif;--f-display:"Big Shoulders Display","Anton",Impact,sans-serif;font-family:var(--f-body);max-width:1320px;margin:0 auto;padding:0 24px}
.calypso-list__filters{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:28px}
.calypso-list__filter-input{padding:8px 14px;border:1px solid var(--c-foam);border-radius:var(--radius);font-family:var(--f-body);font-size:14px;color:var(--c-ink)}
.calypso-list__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px}
.calypso-card{background:#fff;border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 8px rgba(10,37,64,.08);transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column}
.calypso-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(10,37,64,.14)}
.calypso-card__img{width:100%;height:200px;object-fit:cover;background:var(--c-foam)}
.calypso-card__img-placeholder{width:100%;height:200px;background:linear-gradient(135deg,var(--c-deep),var(--c-wave));display:flex;align-items:center;justify-content:center;color:#fff;font-size:40px}
.calypso-card__body{padding:20px;flex:1;display:flex;flex-direction:column}
.calypso-card__badge{display:inline-block;background:var(--c-deep);color:#fff;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:3px 10px;border-radius:20px;margin-bottom:10px}
.calypso-card__title{font-family:var(--f-display);font-size:22px;color:var(--c-deep);margin:0 0 6px;line-height:1.15}
.calypso-card__subtitle{color:var(--c-wave);font-size:14px;margin:0 0 10px}
.calypso-card__meta{font-size:13px;color:#555;margin-bottom:12px;display:flex;flex-direction:column;gap:4px}
.calypso-card__desc{font-size:14px;color:#444;flex:1;margin-bottom:16px;line-height:1.6}
.calypso-card__footer{margin-top:auto}
.calypso-btn{display:inline-block;background:var(--c-coral);color:#fff;font-family:var(--f-display);font-size:15px;letter-spacing:.04em;text-transform:uppercase;padding:10px 20px;border-radius:var(--radius);text-decoration:none;transition:background .15s}
.calypso-btn:hover{background:#e04a2a}
.calypso-docenti-list{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
.calypso-docente-chip{font-size:12px;background:var(--c-foam);color:var(--c-wave);padding:3px 10px;border-radius:20px}
.calypso-empty{text-align:center;color:#888;padding:48px 0;font-size:16px}
@media(max-width:640px){.calypso-list__grid{grid-template-columns:1fr}}
</style>

<div class="calypso-list" data-list="corsi">
	<div class="calypso-list__filters">
		<input type="text" class="calypso-list__filter-input" data-filter="search"
		       placeholder="<?php esc_attr_e( 'Cerca corso…', 'calypsosub' ); ?>">
	</div>

	<?php if ( empty( $corsi ) ) : ?>
		<p class="calypso-empty"><?php _e( 'Nessun corso disponibile.', 'calypsosub' ); ?></p>
	<?php else : ?>
	<div class="calypso-list__grid">
		<?php foreach ( $corsi as $post ) :
			$post_id      = $post->ID;
			$luogo        = get_post_meta( $post_id, '_corso_luogo', true );
			$data_inizio  = get_post_meta( $post_id, '_corso_data_inizio', true );
			$data_fine    = get_post_meta( $post_id, '_corso_data_fine', true );
			$docenti_ids  = (array) ( get_post_meta( $post_id, '_corso_docenti_ids', true ) ?: [] );
			$direttore_id = (int) get_post_meta( $post_id, '_corso_direttore_id', true );
			$desc_breve   = get_post_meta( $post_id, '_corso_desc_breve', true );
			$img          = get_the_post_thumbnail_url( $post_id, 'medium_large' );

			$periodo = '';
			if ( $data_inizio && $data_fine ) {
				$periodo = date_i18n( get_option( 'date_format' ), strtotime( $data_inizio ) )
				         . ' — '
				         . date_i18n( get_option( 'date_format' ), strtotime( $data_fine ) );
			} elseif ( $data_inizio ) {
				$periodo = date_i18n( get_option( 'date_format' ), strtotime( $data_inizio ) );
			}
		?>
		<article class="calypso-card"
		         data-search="<?php echo esc_attr( strtolower( $post->post_title . ' ' . $luogo ) ); ?>">
			<?php if ( $img ) : ?>
				<img class="calypso-card__img" src="<?php echo esc_url( $img ); ?>"
				     alt="<?php echo esc_attr( $post->post_title ); ?>">
			<?php else : ?>
				<div class="calypso-card__img-placeholder">🎓</div>
			<?php endif; ?>
			<div class="calypso-card__body">
				<span class="calypso-card__badge"><?php _e( 'Corso', 'calypsosub' ); ?></span>
				<h3 class="calypso-card__title">
					<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" style="color:inherit;text-decoration:none">
						<?php echo esc_html( $post->post_title ); ?>
					</a>
				</h3>
				<?php if ( $luogo ) : ?>
					<p class="calypso-card__subtitle">📍 <?php echo esc_html( $luogo ); ?></p>
				<?php endif; ?>
				<div class="calypso-card__meta">
					<?php if ( $periodo ) : ?>
						<span>📅 <?php echo esc_html( $periodo ); ?></span>
					<?php endif; ?>
					<?php if ( $direttore_id ) : ?>
						<span>👤 <?php echo esc_html( get_the_title( $direttore_id ) ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $docenti_ids ) ) : ?>
				<div class="calypso-docenti-list">
					<?php foreach ( $docenti_ids as $did ) : ?>
					<span class="calypso-docente-chip"><?php echo esc_html( get_the_title( $did ) ); ?></span>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
				<?php if ( $desc_breve ) : ?>
					<p class="calypso-card__desc"><?php echo esc_html( $desc_breve ); ?></p>
				<?php endif; ?>
				<div class="calypso-card__footer">
					<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"
					   class="calypso-btn"><?php _e( 'Scopri il corso', 'calypsosub' ); ?></a>
				</div>
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
	var cards = list.querySelectorAll('.calypso-card');
	list.querySelector('[data-filter="search"]').addEventListener('input', function () {
		var q = this.value.toLowerCase();
		cards.forEach(function (c) { c.style.display = !q || c.dataset.search.includes(q) ? '' : 'none'; });
	});
})();
</script>
