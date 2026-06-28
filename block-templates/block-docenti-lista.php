<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$docenti = calypso_get_docenti();
?>
<style>
.calypso-docenti{max-width:1320px;margin:0 auto;padding:0 24px}
.calypso-docenti__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:28px}
.calypso-docente-card__link{display:flex;flex-direction:column;flex:1;text-decoration:none;color:inherit}
.calypso-docente-card__photo-wrap{position:relative;overflow:hidden}
.calypso-docente-card__photo-label{position:absolute;bottom:0;left:0;background:rgba(11,26,38,.72);color:var(--c-sand);font-size:10px;font-weight:500;letter-spacing:.14em;text-transform:uppercase;padding:6px 12px;backdrop-filter:blur(2px)}
.calypso-docente-card__name{font-size:26px;font-weight:800;color:var(--c-wave)!important;margin:0 0 4px;line-height:1.1;text-transform:uppercase;letter-spacing:-.01em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.calypso-docente-card__name:hover{color:var(--c-deep)!important}
.calypso-docente-card__soprannome{font-size:16px;color:rgba(10,37,64,.6)!important;font-style:italic;margin:0 0 6px}
.calypso-docente-card__ruolo{font-size:18px;font-weight:600;color:var(--c-gold)!important;text-transform:capitalize;margin:0 0 12px;letter-spacing:.01em}
.calypso-docente-card__bio{font-size:18px;color:#444!important;line-height:1.6;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.calypso-docente-card__stats{background:#fff;font-size:11px;font-weight:400;letter-spacing:.05em;padding:12px 20px;line-height:1.6;position:relative}
.calypso-docente-card__stats::before{content:'';position:absolute;top:0;left:20px;right:20px;height:1px;background:#e8e8e8}
.calypso-docente-card__stats-items{display:flex;flex-wrap:wrap;gap:0;list-style:none;margin:0;padding:0}
.calypso-docente-card__stats-items li{display:inline;color:#888!important}
.calypso-docente-card__stats-items li+li::before{content:" · ";color:#ccc!important}
@media(max-width:640px){.calypso-docenti__grid{grid-template-columns:1fr 1fr}}
@media(max-width:400px){.calypso-docenti__grid{grid-template-columns:1fr}}
</style>

<div class="calypso-docenti">
	<?php if ( empty( $docenti ) ) : ?>
		<p class="calypso-empty"><?php _e( 'Nessun docente trovato.', 'calypsosub' ); ?></p>
	<?php else : ?>
	<div class="calypso-docenti__grid">
		<?php foreach ( $docenti as $post ) :
			$post_id         = $post->ID;
			$nome            = get_post_meta( $post_id, '_docente_nome', true );
			$cognome         = get_post_meta( $post_id, '_docente_cognome', true );
			$soprannome      = (string) get_post_meta( $post_id, '_docente_soprannome', true );
			$ruolo           = get_post_meta( $post_id, '_docente_ruolo', true );
			$bio             = get_post_meta( $post_id, '_docente_bio', true );
			$specializzazioni= get_post_meta( $post_id, '_docente_specializzazioni', true );
			$anni            = (int) get_post_meta( $post_id, '_docente_anni_esperienza', true );
			$img             = get_the_post_thumbnail_url( $post_id, 'large' );
			$brevetti        = wp_get_post_terms( $post_id, 'calypso_brevetto', [ 'fields' => 'names' ] );
			if ( is_wp_error( $brevetti ) ) $brevetti = [];
			$display_name    = trim( $nome . ' ' . $cognome ) ?: $post->post_title;
			$display_upper   = mb_strtoupper( $display_name );
			$anno_inizio     = $anni > 0 ? ( (int) date( 'Y' ) - $anni ) : null;

			$stats = [];
			if ( $anno_inizio ) $stats[] = 'DAL ' . $anno_inizio;
			foreach ( $brevetti as $b ) $stats[] = $b;
			if ( $specializzazioni ) $stats[] = $specializzazioni;
			if ( $anni > 0 ) $stats[] = $anni . ' anni di mare';
		?>
		<article class="calypso-docente-card">
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="calypso-docente-card__link">
				<div class="calypso-docente-card__photo-wrap">
					<?php if ( $img ) : ?>
						<img class="calypso-docente-card__avatar"
						     src="<?php echo esc_url( $img ); ?>"
						     alt="<?php echo esc_attr( $display_name ); ?>">
					<?php else : ?>
						<div class="calypso-docente-card__avatar-placeholder">🤿</div>
					<?php endif; ?>
					<div class="calypso-docente-card__photo-label">
						<?php echo esc_html( 'RITRATTO · ' . mb_strtoupper( $nome ?: $post->post_title ) ); ?>
					</div>
				</div>
				<div class="calypso-docente-card__body">
					<h3 class="calypso-docente-card__name"><?php echo esc_html( $display_upper ); ?></h3>
					<?php if ( $soprannome ) : ?>
						<p class="calypso-docente-card__soprannome">detto &ldquo;<?php echo esc_html( $soprannome ); ?>&rdquo;</p>
					<?php endif; ?>
					<?php if ( $ruolo ) : ?>
						<p class="calypso-docente-card__ruolo"><?php echo esc_html( $ruolo ); ?></p>
					<?php endif; ?>
					<?php if ( $bio ) : ?>
						<p class="calypso-docente-card__bio"><?php echo esc_html( $bio ); ?></p>
					<?php endif; ?>
				</div>
			</a>
			<?php if ( ! empty( $stats ) ) : ?>
			<div class="calypso-docente-card__stats">
				<ul class="calypso-docente-card__stats-items">
					<?php foreach ( $stats as $item ) : ?>
						<li><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</article>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
