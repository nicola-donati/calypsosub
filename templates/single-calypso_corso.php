<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$id = get_the_ID();

$sottotitolo  = (string) get_post_meta( $id, '_corso_sottotitolo', true );
$luogo        = (string) get_post_meta( $id, '_corso_luogo', true );
$materiale    = (string) get_post_meta( $id, '_corso_materiale', true );
$data_inizio  = (string) get_post_meta( $id, '_corso_data_inizio', true );
$data_fine    = (string) get_post_meta( $id, '_corso_data_fine', true );
$direttore_id = (int)    get_post_meta( $id, '_corso_direttore_id', true );
$docenti_ids  = (array)  ( get_post_meta( $id, '_corso_docenti_ids', true ) ?: [] );
$date_lezioni = (array)  ( get_post_meta( $id, '_corso_date_lezioni', true ) ?: [] );
sort( $date_lezioni );

$fmt_date = static function ( string $dt ): string {
	$ts = strtotime( $dt );
	return $ts ? wp_date( 'j F Y', $ts ) : $dt;
};
$fmt_datetime = static function ( string $dt ): string {
	$ts = strtotime( $dt );
	return $ts ? wp_date( 'j F Y — H:i', $ts ) : $dt;
};
?>
<style>
.cso{--c-deep:#0a2540;--c-wave:#1d6f9c;--c-coral:#ff6b4a;--c-bone:#f6f1e6;--c-foam:#cfe9ee;--c-ink:#0b1a26;--radius:4px;--radius-lg:12px;--f-body:"DM Sans",-apple-system,BlinkMacSystemFont,sans-serif;--f-display:"Big Shoulders Display","Anton",Impact,sans-serif;font-family:var(--f-body);color:var(--c-ink)}
@import url('https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;900&family=DM+Sans:wght@400;500;600&display=swap');
.cso-hero{position:relative;height:420px;display:flex;align-items:flex-end;overflow:hidden}
.cso-hero__bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform 6s ease}
.cso-hero__bg--gradient{background:linear-gradient(135deg,var(--c-deep) 0%,#0e4d6b 50%,var(--c-wave) 100%)}
.cso-hero:hover .cso-hero__bg{transform:scale(1.03)}
.cso-hero__overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(10,37,64,.85) 0%,rgba(10,37,64,.3) 60%,transparent 100%)}
.cso-hero__content{position:relative;z-index:1;padding:40px 48px;max-width:800px}
.cso-badge{display:inline-block;background:#1a8a4a;color:#fff;font-family:var(--f-body);font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:12px}
.cso-hero__title{font-family:var(--f-display);font-size:clamp(36px,5vw,60px);font-weight:900;color:#fff;margin:0 0 8px;line-height:1.05;text-transform:uppercase}
.cso-hero__subtitle{font-size:17px;color:rgba(255,255,255,.8);margin:0}
.cso-infobar{background:var(--c-deep);color:#fff;padding:18px 48px;display:flex;flex-wrap:wrap;gap:20px}
.cso-infobar__pill{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border-radius:20px;padding:6px 14px;font-size:14px}
.cso-infobar__icon{font-size:18px;opacity:.7}
.cso-infobar__label{opacity:.6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;display:block}
.cso-infobar__value{font-weight:600}
.cso-body{max-width:1320px;margin:0 auto;padding:48px 24px;display:grid;grid-template-columns:1fr 340px;gap:48px;align-items:start}
@media(max-width:900px){.cso-body{grid-template-columns:1fr}}
.cso-section{margin-bottom:40px}
.cso-section__title{font-family:var(--f-display);font-size:22px;font-weight:700;color:var(--c-deep);text-transform:uppercase;letter-spacing:.04em;margin:0 0 16px;padding-bottom:8px;border-bottom:3px solid var(--c-foam)}
.cso-prose{font-size:16px;line-height:1.75;color:var(--c-ink)}
.cso-prose p{margin:0 0 1em}

/* Docenti */
.cso-docenti-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px}
.cso-docente-card{background:#fff;border-radius:var(--radius-lg);box-shadow:0 2px 10px rgba(10,37,64,.08);overflow:hidden;text-align:center;padding:20px;text-decoration:none;color:var(--c-ink);transition:transform .2s,box-shadow .2s;display:block}
.cso-docente-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(10,37,64,.14)}
.cso-docente-card img{width:72px;height:72px;border-radius:50%;object-fit:cover;margin-bottom:12px;border:3px solid var(--c-foam)}
.cso-docente-card__avatar{width:72px;height:72px;border-radius:50%;background:var(--c-foam);display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 12px}
.cso-docente-card__name{font-weight:600;font-size:15px;margin:0 0 4px}
.cso-docente-card__ruolo{font-size:12px;color:var(--c-wave)}

/* Sidebar schedule */
.cso-card{background:#fff;border-radius:var(--radius-lg);box-shadow:0 4px 24px rgba(10,37,64,.1);overflow:hidden;position:sticky;top:24px}
.cso-card__head{background:var(--c-deep);color:#fff;padding:20px 24px}
.cso-card__head-title{font-family:var(--f-display);font-size:18px;font-weight:700;text-transform:uppercase;margin:0}
.cso-card__body{padding:20px 24px}
.cso-schedule{list-style:none;margin:0;padding:0}
.cso-schedule li{display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--c-foam);font-size:14px}
.cso-schedule li:last-child{border-bottom:none}
.cso-schedule__num{width:24px;height:24px;background:var(--c-coral);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;margin-top:1px}
.cso-schedule__text{line-height:1.4}
.cso-schedule__date{font-weight:600;color:var(--c-deep)}
.cso-schedule__time{color:#666;font-size:12px}
.cso-empty{text-align:center;padding:24px;color:#999;font-size:14px}
</style>

<div class="cso">

<section class="cso-hero">
	<?php if ( has_post_thumbnail( $id ) ) : ?>
	<div class="cso-hero__bg" style="background-image:url(<?php echo esc_url( get_the_post_thumbnail_url( $id, 'full' ) ); ?>)"></div>
	<?php else : ?>
	<div class="cso-hero__bg cso-hero__bg--gradient"></div>
	<?php endif; ?>
	<div class="cso-hero__overlay"></div>
	<div class="cso-hero__content">
		<span class="cso-badge"><?php esc_html_e( 'Corso', 'calypsosub' ); ?></span>
		<h1 class="cso-hero__title"><?php the_title(); ?></h1>
		<?php if ( $sottotitolo ) : ?>
		<p class="cso-hero__subtitle"><?php echo esc_html( $sottotitolo ); ?></p>
		<?php endif; ?>
	</div>
</section>

<div class="cso-infobar">
	<?php if ( $luogo ) : ?>
	<div class="cso-infobar__pill">
		<span class="cso-infobar__icon">📍</span>
		<div>
			<span class="cso-infobar__label"><?php esc_html_e( 'Luogo', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value"><?php echo esc_html( $luogo ); ?></span>
		</div>
	</div>
	<?php endif; ?>
	<?php if ( $data_inizio ) : ?>
	<div class="cso-infobar__pill">
		<span class="cso-infobar__icon">🗓️</span>
		<div>
			<span class="cso-infobar__label"><?php esc_html_e( 'Inizio', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value"><?php echo esc_html( $fmt_date( $data_inizio ) ); ?></span>
		</div>
	</div>
	<?php endif; ?>
	<?php if ( $data_fine ) : ?>
	<div class="cso-infobar__pill">
		<span class="cso-infobar__icon">🏁</span>
		<div>
			<span class="cso-infobar__label"><?php esc_html_e( 'Fine', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value"><?php echo esc_html( $fmt_date( $data_fine ) ); ?></span>
		</div>
	</div>
	<?php endif; ?>
	<?php if ( $date_lezioni ) : ?>
	<div class="cso-infobar__pill">
		<span class="cso-infobar__icon">📚</span>
		<div>
			<span class="cso-infobar__label"><?php esc_html_e( 'Lezioni', 'calypsosub' ); ?></span>
			<span class="cso-infobar__value"><?php echo esc_html( count( $date_lezioni ) ); ?></span>
		</div>
	</div>
	<?php endif; ?>
</div>

<div class="cso-body">
	<div class="cso-main">

		<?php if ( get_the_content() ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Descrizione', 'calypsosub' ); ?></h2>
			<div class="cso-prose"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $materiale ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Materiale incluso', 'calypsosub' ); ?></h2>
			<div class="cso-prose"><?php echo nl2br( esc_html( $materiale ) ); ?></div>
		</div>
		<?php endif; ?>

		<?php if ( $docenti_ids || $direttore_id ) : ?>
		<div class="cso-section">
			<h2 class="cso-section__title"><?php esc_html_e( 'Docenti', 'calypsosub' ); ?></h2>
			<div class="cso-docenti-grid">
				<?php
				$all_shown = $docenti_ids;
				if ( $direttore_id && ! in_array( $direttore_id, $all_shown, true ) ) {
					array_unshift( $all_shown, $direttore_id );
				}
				foreach ( $all_shown as $did ) :
					$ruolo = (string) get_post_meta( $did, '_docente_ruolo', true );
					$is_dir = ( $did === $direttore_id );
				?>
				<a href="<?php echo esc_url( get_permalink( $did ) ); ?>" class="cso-docente-card">
					<?php if ( has_post_thumbnail( $did ) ) : ?>
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $did, 'thumbnail' ) ); ?>"
					     alt="<?php echo esc_attr( get_the_title( $did ) ); ?>">
					<?php else : ?>
					<div class="cso-docente-card__avatar">🤿</div>
					<?php endif; ?>
					<div class="cso-docente-card__name"><?php echo esc_html( get_the_title( $did ) ); ?></div>
					<div class="cso-docente-card__ruolo">
						<?php echo esc_html( $is_dir ? __( 'Direttore', 'calypsosub' ) . ( $ruolo ? ' · ' . $ruolo : '' ) : $ruolo ); ?>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div><!-- .cso-main -->

	<aside>
		<div class="cso-card">
			<div class="cso-card__head">
				<p class="cso-card__head-title"><?php esc_html_e( 'Calendario lezioni', 'calypsosub' ); ?></p>
			</div>
			<div class="cso-card__body">
				<?php if ( $date_lezioni ) : ?>
				<ul class="cso-schedule">
					<?php foreach ( $date_lezioni as $n => $dt ) :
						$ts   = strtotime( $dt );
						$giorno = $ts ? wp_date( 'j F Y', $ts ) : $dt;
						$ora    = $ts && strlen( $dt ) > 10 ? wp_date( 'H:i', $ts ) : '';
					?>
					<li>
						<span class="cso-schedule__num"><?php echo esc_html( $n + 1 ); ?></span>
						<div class="cso-schedule__text">
							<div class="cso-schedule__date"><?php echo esc_html( $giorno ); ?></div>
							<?php if ( $ora ) : ?>
							<div class="cso-schedule__time"><?php echo esc_html( $ora ); ?></div>
							<?php endif; ?>
						</div>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php else : ?>
				<p class="cso-empty"><?php esc_html_e( 'Date non ancora definite.', 'calypsosub' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</aside>

</div><!-- .cso-body -->
</div><!-- .cso -->

<?php get_footer(); ?>
