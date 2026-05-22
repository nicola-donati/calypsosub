<?php
/**
 * Block template: Lista Docenti
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$docenti = calypso_get_docenti();
?>
<style>
.calypso-docenti{--c-deep:#0a2540;--c-wave:#1d6f9c;--c-coral:#ff6b4a;--c-bone:#f6f1e6;--c-foam:#cfe9ee;--c-ink:#0b1a26;--radius:4px;--radius-lg:12px;--f-body:"DM Sans",-apple-system,BlinkMacSystemFont,sans-serif;--f-display:"Big Shoulders Display","Anton",Impact,sans-serif;font-family:var(--f-body);max-width:1320px;margin:0 auto;padding:0 24px}
.calypso-docenti__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:24px}
.calypso-docente-card{background:#fff;border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 2px 8px rgba(10,37,64,.08);transition:transform .2s,box-shadow .2s;text-align:center}
.calypso-docente-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(10,37,64,.14)}
.calypso-docente-card__avatar{width:100%;height:220px;object-fit:cover;object-position:top}
.calypso-docente-card__avatar-placeholder{width:100%;height:220px;background:linear-gradient(160deg,var(--c-deep),var(--c-wave));display:flex;align-items:center;justify-content:center;color:#fff;font-size:64px}
.calypso-docente-card__body{padding:16px 20px 20px}
.calypso-docente-card__name{font-family:var(--f-display);font-size:20px;color:var(--c-deep);margin:0 0 4px;line-height:1.2}
.calypso-docente-card__ruolo{font-size:13px;color:var(--c-wave);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px}
.calypso-docente-card__bio{font-size:13px;color:#555;line-height:1.55;margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.calypso-docente-card__brevetti{display:flex;flex-wrap:wrap;gap:5px;justify-content:center;margin-bottom:12px}
.calypso-docente-card__brevetto{font-size:11px;background:var(--c-foam);color:var(--c-wave);padding:3px 10px;border-radius:20px}
.calypso-docente-card__social{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.calypso-docente-card__social a{font-size:13px;color:var(--c-wave);text-decoration:none;padding:4px 10px;border:1px solid var(--c-foam);border-radius:var(--radius)}
.calypso-docente-card__social a:hover{background:var(--c-foam)}
.calypso-empty{text-align:center;color:#888;padding:48px 0;font-size:16px}
@media(max-width:640px){.calypso-docenti__grid{grid-template-columns:1fr 1fr}}
@media(max-width:400px){.calypso-docenti__grid{grid-template-columns:1fr}}
</style>

<div class="calypso-docenti">
	<?php if ( empty( $docenti ) ) : ?>
		<p class="calypso-empty"><?php _e( 'Nessun docente trovato.', 'calypsosub' ); ?></p>
	<?php else : ?>
	<div class="calypso-docenti__grid">
		<?php foreach ( $docenti as $post ) :
			$post_id  = $post->ID;
			$nome     = get_post_meta( $post_id, '_docente_nome', true );
			$cognome  = get_post_meta( $post_id, '_docente_cognome', true );
			$ruolo    = get_post_meta( $post_id, '_docente_ruolo', true );
			$bio      = get_post_meta( $post_id, '_docente_bio', true );
			$social   = (array) ( get_post_meta( $post_id, '_docente_social', true ) ?: [] );
			$img      = get_the_post_thumbnail_url( $post_id, 'medium' );
			$brevetti = wp_get_post_terms( $post_id, 'calypso_brevetto', [ 'fields' => 'names' ] );
			if ( is_wp_error( $brevetti ) ) $brevetti = [];
			$display_name = trim( $nome . ' ' . $cognome ) ?: $post->post_title;
		?>
		<article class="calypso-docente-card">
			<?php if ( $img ) : ?>
				<img class="calypso-docente-card__avatar" src="<?php echo esc_url( $img ); ?>"
				     alt="<?php echo esc_attr( $display_name ); ?>">
			<?php else : ?>
				<div class="calypso-docente-card__avatar-placeholder">🤿</div>
			<?php endif; ?>
			<div class="calypso-docente-card__body">
				<h3 class="calypso-docente-card__name"><?php echo esc_html( $display_name ); ?></h3>
				<?php if ( $ruolo ) : ?>
					<p class="calypso-docente-card__ruolo"><?php echo esc_html( $ruolo ); ?></p>
				<?php endif; ?>
				<?php if ( $bio ) : ?>
					<p class="calypso-docente-card__bio"><?php echo esc_html( $bio ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $brevetti ) ) : ?>
					<div class="calypso-docente-card__brevetti">
						<?php foreach ( $brevetti as $b ) : ?>
							<span class="calypso-docente-card__brevetto"><?php echo esc_html( $b ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $social ) ) : ?>
					<div class="calypso-docente-card__social">
						<?php foreach ( $social as $s ) : ?>
							<a href="<?php echo esc_url( $s['url'] ); ?>"
							   target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $s['nome'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</article>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
