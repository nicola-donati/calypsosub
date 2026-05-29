<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$id = get_the_ID();

$nome           = (string) get_post_meta( $id, '_docente_nome', true );
$cognome        = (string) get_post_meta( $id, '_docente_cognome', true );
$ruolo          = (string) get_post_meta( $id, '_docente_ruolo', true );
$bio            = (string) get_post_meta( $id, '_docente_bio', true );
$anni           = get_post_meta( $id, '_docente_anni_esperienza', true );
$email          = (string) get_post_meta( $id, '_docente_email', true );
$telefono       = (string) get_post_meta( $id, '_docente_telefono', true );
$specializzazioni = (array) ( get_post_meta( $id, '_docente_specializzazioni', true ) ?: [] );
$social         = (array) ( get_post_meta( $id, '_docente_social', true ) ?: [] );

// Corsi insegnati da questo docente
$corsi_docente = get_posts( [
	'post_type'      => 'calypso_corso',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'meta_query'     => [
		'relation' => 'OR',
		[
			'key'     => '_corso_direttore_id',
			'value'   => $id,
			'compare' => '=',
		],
		[
			'key'     => '_corso_docenti_ids',
			'value'   => '"' . $id . '"',
			'compare' => 'LIKE',
		],
	],
] );

// Brevetti taxonomy
$brevetti = get_the_terms( $id, 'calypso_brevetto' );
?>
<style>
.cso{color:var(--c-ink,#0b1a26);--radius:4px;--radius-lg:12px}

/* Profile hero */
.cso-profile-hero{background:var(--c-deep);padding:64px 48px;display:flex;gap:48px;align-items:center;flex-wrap:wrap}
.cso-profile-hero__photo{width:180px;height:180px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.2);flex-shrink:0}
.cso-profile-hero__avatar{width:180px;height:180px;border-radius:50%;background:linear-gradient(135deg,var(--c-wave),var(--c-foam));display:flex;align-items:center;justify-content:center;font-size:72px;flex-shrink:0}
.cso-profile-hero__info{color:#fff}
.cso-badge{display:inline-block;background:var(--c-coral);color:#fff;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:12px}
.cso-profile-hero__name{font-size:clamp(36px,4vw,56px);font-weight:900;text-transform:uppercase;margin:0 0 4px;line-height:1.05}
.cso-profile-hero__ruolo{font-size:18px;color:var(--c-foam);margin:0 0 16px}
.cso-profile-hero__meta{display:flex;flex-wrap:wrap;gap:16px;margin-top:16px}
.cso-profile-hero__meta-item{display:flex;align-items:center;gap:6px;font-size:14px;color:rgba(255,255,255,.75)}
.cso-profile-hero__meta-item a{color:var(--c-foam);text-decoration:none}
.cso-profile-hero__meta-item a:hover{text-decoration:underline}

/* Body */
.cso-body{max-width:1320px;margin:0 auto;padding:48px 24px;display:grid;grid-template-columns:1fr 300px;gap:48px;align-items:start}
@media(max-width:900px){.cso-body{grid-template-columns:1fr}}
.cso-section{margin-bottom:40px}
.cso-section__title{font-size:22px;font-weight:700;color:var(--c-deep);text-transform:uppercase;letter-spacing:.04em;margin:0 0 16px;padding-bottom:8px;border-bottom:3px solid var(--c-foam)}
.cso-prose{font-size:16px;line-height:1.75;color:var(--c-ink)}
.cso-prose p{margin:0 0 1em}

/* Brevetti */
.cso-tags{display:flex;flex-wrap:wrap;gap:8px}
.cso-tag{background:var(--c-foam);color:var(--c-deep);border-radius:20px;padding:5px 14px;font-size:13px;font-weight:600}
.cso-tag--brevetto{background:var(--c-deep);color:#fff}

/* Corsi */
.cso-corsi-list{list-style:none;margin:0;padding:0}
.cso-corsi-list li{padding:10px 0;border-bottom:1px solid var(--c-foam)}
.cso-corsi-list li:last-child{border-bottom:none}
.cso-corsi-list a{color:var(--c-wave);text-decoration:none;font-weight:500;font-size:15px}
.cso-corsi-list a:hover{text-decoration:underline}
.cso-corsi-list__meta{font-size:12px;color:#888;margin-top:2px}

/* Sidebar card */
.cso-card{background:#fff;border-radius:var(--radius-lg);box-shadow:0 4px 24px rgba(10,37,64,.1);overflow:hidden;position:sticky;top:24px}
.cso-card__head{background:var(--c-wave);color:#fff;padding:20px 24px}
.cso-card__head-title{font-size:18px;font-weight:700;text-transform:uppercase;margin:0}
.cso-card__body{padding:20px 24px}
.cso-stat{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--c-foam)}
.cso-stat:last-child{border-bottom:none}
.cso-stat__label{font-size:13px;color:#666}
.cso-stat__value{font-weight:700;color:var(--c-deep)}

/* Social */
.cso-social{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px}
.cso-social a{display:inline-flex;align-items:center;gap:6px;background:var(--c-bone);border-radius:var(--radius);padding:7px 14px;font-size:13px;font-weight:600;color:var(--c-ink);text-decoration:none;transition:background .2s}
.cso-social a:hover{background:var(--c-foam)}
</style>

<div class="cso">

<!-- Profile hero -->
<section class="cso-profile-hero">
	<?php if ( has_post_thumbnail( $id ) ) : ?>
	<img class="cso-profile-hero__photo"
	     src="<?php echo esc_url( get_the_post_thumbnail_url( $id, 'large' ) ); ?>"
	     alt="<?php echo esc_attr( get_the_title( $id ) ); ?>">
	<?php else : ?>
	<div class="cso-profile-hero__avatar">🤿</div>
	<?php endif; ?>

	<div class="cso-profile-hero__info">
		<span class="cso-badge"><?php esc_html_e( 'Istruttore', 'calypsosub' ); ?></span>
		<h1 class="cso-profile-hero__name"><?php the_title(); ?></h1>
		<?php if ( $ruolo ) : ?>
		<p class="cso-profile-hero__ruolo"><?php echo esc_html( $ruolo ); ?></p>
		<?php endif; ?>

		<?php if ( $email || $telefono || $anni ) : ?>
		<div class="cso-profile-hero__meta">
			<?php if ( $anni ) : ?>
			<span class="cso-profile-hero__meta-item">⭐ <?php echo esc_html( sprintf( _n( '%d anno di esperienza', '%d anni di esperienza', (int) $anni, 'calypsosub' ), (int) $anni ) ); ?></span>
			<?php endif; ?>
			<?php if ( $email ) : ?>
			<span class="cso-profile-hero__meta-item">✉️ <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span>
			<?php endif; ?>
			<?php if ( $telefono ) : ?>
			<span class="cso-profile-hero__meta-item">📞 <a href="tel:<?php echo esc_attr( $telefono ); ?>"><?php echo esc_html( $telefono ); ?></a></span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</section>

<!-- Body -->
<div class="cso-body">
	<div class="cso-main">

		<?php if ( $bio || get_the_content() ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Biografia', 'calypsosub' ); ?></h2>
			<div class="cso-prose">
				<?php if ( get_the_content() ) : ?>
					<?php the_content(); ?>
				<?php elseif ( $bio ) : ?>
					<?php echo nl2br( esc_html( $bio ) ); ?>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $specializzazioni ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Specializzazioni', 'calypsosub' ); ?></h2>
			<div class="cso-tags">
				<?php foreach ( $specializzazioni as $spec ) : ?>
				<span class="cso-tag"><?php echo esc_html( $spec ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $brevetti && ! is_wp_error( $brevetti ) ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Brevetti e certificazioni', 'calypsosub' ); ?></h2>
			<div class="cso-tags">
				<?php foreach ( $brevetti as $brev ) : ?>
				<span class="cso-tag cso-tag--brevetto">🏅 <?php echo esc_html( $brev->name ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $corsi_docente ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Corsi', 'calypsosub' ); ?></h2>
			<ul class="cso-corsi-list">
				<?php foreach ( $corsi_docente as $corso ) :
					$c_inizio = get_post_meta( $corso->ID, '_corso_data_inizio', true );
					$c_luogo  = get_post_meta( $corso->ID, '_corso_luogo', true );
				?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $corso->ID ) ); ?>"><?php echo esc_html( get_the_title( $corso->ID ) ); ?></a>
					<?php if ( $c_inizio || $c_luogo ) : ?>
					<div class="cso-corsi-list__meta">
						<?php if ( $c_inizio ) : ?>
						<?php $ts = strtotime( $c_inizio ); echo $ts ? esc_html( wp_date( 'j F Y', $ts ) ) : ''; ?>
						<?php endif; ?>
						<?php if ( $c_luogo ) : ?>
						<?php if ( $c_inizio ) echo ' · '; echo esc_html( $c_luogo ); ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<?php if ( $social ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Profili social', 'calypsosub' ); ?></h2>
			<div class="cso-social">
				<?php foreach ( $social as $s ) :
					if ( empty( $s['url'] ) ) continue;
					$nome_s = ! empty( $s['nome'] ) ? $s['nome'] : parse_url( $s['url'], PHP_URL_HOST );
				?>
				<a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					🔗 <?php echo esc_html( $nome_s ); ?>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div><!-- .cso-main -->

	<aside>
		<div class="cso-card">
			<div class="cso-card__head">
				<p class="cso-card__head-title"><?php esc_html_e( 'Scheda', 'calypsosub' ); ?></p>
			</div>
			<div class="cso-card__body">
				<?php if ( $ruolo ) : ?>
				<div class="cso-stat">
					<span class="cso-stat__label"><?php esc_html_e( 'Ruolo', 'calypsosub' ); ?></span>
					<span class="cso-stat__value"><?php echo esc_html( $ruolo ); ?></span>
				</div>
				<?php endif; ?>
				<?php if ( $anni !== '' && $anni !== null ) : ?>
				<div class="cso-stat">
					<span class="cso-stat__label"><?php esc_html_e( 'Esperienza', 'calypsosub' ); ?></span>
					<span class="cso-stat__value"><?php echo esc_html( $anni ); ?> <?php esc_html_e( 'anni', 'calypsosub' ); ?></span>
				</div>
				<?php endif; ?>
				<?php if ( $corsi_docente ) : ?>
				<div class="cso-stat">
					<span class="cso-stat__label"><?php esc_html_e( 'Corsi attivi', 'calypsosub' ); ?></span>
					<span class="cso-stat__value"><?php echo esc_html( count( $corsi_docente ) ); ?></span>
				</div>
				<?php endif; ?>
				<?php if ( $brevetti && ! is_wp_error( $brevetti ) ) : ?>
				<div class="cso-stat">
					<span class="cso-stat__label"><?php esc_html_e( 'Brevetti', 'calypsosub' ); ?></span>
					<span class="cso-stat__value"><?php echo esc_html( count( $brevetti ) ); ?></span>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</aside>

</div><!-- .cso-body -->
</div><!-- .cso -->

<?php get_footer(); ?>
