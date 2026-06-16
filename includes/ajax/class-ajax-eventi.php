<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Ajax_Eventi {

	public static function handle(): void {
		check_ajax_referer( 'calypso_eventi_search', 'nonce' );

		$q     = sanitize_text_field( wp_unslash( $_POST['q']     ?? '' ) );
		$da    = sanitize_text_field( wp_unslash( $_POST['da']    ?? '' ) );
		$a     = sanitize_text_field( wp_unslash( $_POST['a']     ?? '' ) );
		$luogo = sanitize_text_field( wp_unslash( $_POST['luogo'] ?? '' ) );

		$today          = current_time( 'Y-m-d' );
		$eventi         = self::query( $q, $da, $a, $luogo, false );
		$booking_counts = self::booking_counts( $eventi );

		wp_send_json_success( [
			'html'  => self::render( $eventi, $booking_counts, $today ),
			'count' => count( $eventi ),
		] );
	}

	/**
	 * Query eventi with optional filters.
	 *
	 * @param string $q     Free-text search against title + sottotitolo.
	 * @param string $da    Date from (Y-m-d), inclusive.
	 * @param string $a     Date to   (Y-m-d), inclusive.
	 * @param string $luogo Exact location string match.
	 * @param bool   $limit Cap result at 50 (future-first then past-desc).
	 * @return WP_Post[]    Posts with _prima_data and _prima_ora properties set.
	 */
	/**
	 * @param string $from_date Y-m-d — includi passati da questa data ('' = tutti). Se impostato,
	 *                          garantisce almeno 5 passati anche se più vecchi del cutoff.
	 */
	public static function query( string $q, string $da, string $a, string $luogo, bool $limit = true, string $from_date = '' ): array {
		$all   = get_posts( [
			'post_type'      => 'calypso_evento',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		] );
		$today = current_time( 'Y-m-d' );

		$future   = [];
		$past_win = [];
		$past_old = [];

		foreach ( $all as $e ) {
			$date_meta = get_post_meta( $e->ID, '_evento_date', true );
			$dates     = is_array( $date_meta ) ? array_filter( $date_meta ) : [];
			if ( empty( $dates ) ) continue;
			sort( $dates );
			$prima_raw      = $dates[0];
			$prima          = substr( $prima_raw, 0, 10 );
			$e->_prima_data = $prima;
			$e->_prima_ora  = strlen( $prima_raw ) > 10 ? substr( $prima_raw, 11, 5 ) : '';

			/* Text filter */
			if ( $q !== '' ) {
				$hay_title = strtolower( $e->post_title );
				$hay_sub   = strtolower( (string) get_post_meta( $e->ID, '_evento_sottotitolo', true ) );
				$needle    = strtolower( $q );
				if ( strpos( $hay_title, $needle ) === false && strpos( $hay_sub, $needle ) === false ) {
					continue;
				}
			}

			/* Date range filter */
			if ( $da !== '' && $prima < $da ) continue;
			if ( $a  !== '' && $prima > $a  ) continue;

			/* Location filter */
			if ( $luogo !== '' && (string) get_post_meta( $e->ID, '_evento_luogo', true ) !== $luogo ) continue;

			if ( $prima >= $today ) {
				$future[] = $e;
			} elseif ( $from_date === '' || $prima >= $from_date ) {
				$past_win[] = $e;
			} else {
				$past_old[] = $e;
			}
		}

		/* Fallback: garantisce almeno 5 passati anche prima del cutoff */
		if ( $from_date !== '' ) {
			usort( $past_old, static fn( $x, $y ) => strcmp( $y->_prima_data, $x->_prima_data ) );
			$needed   = max( 0, 5 - count( $past_win ) );
			$past_win = array_merge( $past_win, array_slice( $past_old, 0, $needed ) );
		} else {
			$past_win = array_merge( $past_win, $past_old );
		}

		usort( $future,   static fn( $x, $y ) => strcmp( $x->_prima_data, $y->_prima_data ) );
		usort( $past_win, static fn( $x, $y ) => strcmp( $y->_prima_data, $x->_prima_data ) );

		$merged = array_merge( $future, $past_win );
		return $limit ? array_slice( $merged, 0, 50 ) : $merged;
	}

	/**
	 * Batch-fetch confirmed booking counts for a list of event posts.
	 *
	 * @param WP_Post[] $eventi
	 * @return array<int,int> Map of post_id → confirmed booking count.
	 */
	public static function booking_counts( array $eventi ): array {
		if ( empty( $eventi ) ) return [];
		global $wpdb;
		$ids     = array_map( static fn( $e ) => (int) $e->ID, $eventi );
		$safe_in = implode( ',', $ids );
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results(
			"SELECT pm_ev.meta_value AS pid, COUNT(*) AS cnt
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_ev  ON pm_ev.post_id  = p.ID AND pm_ev.meta_key  = '_booking_post_id'
			 INNER JOIN {$wpdb->postmeta} pm_sta ON pm_sta.post_id = p.ID AND pm_sta.meta_key = '_booking_status' AND pm_sta.meta_value = 'confermata'
			 WHERE p.post_type = 'calypso_prenotazione' AND pm_ev.meta_value IN ($safe_in)
			 GROUP BY pm_ev.meta_value"
		);
		// phpcs:enable
		$counts = [];
		foreach ( $rows as $row ) {
			$counts[ (int) $row->pid ] = (int) $row->cnt;
		}
		return $counts;
	}

	/**
	 * Render the month-grouped events list as an HTML string.
	 *
	 * @param WP_Post[]      $eventi         Posts with _prima_data/_prima_ora set.
	 * @param array<int,int> $booking_counts
	 * @param string         $today          Current date Y-m-d.
	 * @return string HTML.
	 */
	public static function render( array $eventi, array $booking_counts, string $today ): string {
		if ( empty( $eventi ) ) {
			return '<div class="cso-empty"><p class="cso-empty__title">'
				. esc_html__( 'Nessun evento trovato.', 'calypsosub' )
				. '</p></div>';
		}

		$mesi_it = [
			'01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo',
			'04' => 'Aprile',  '05' => 'Maggio',   '06' => 'Giugno',
			'07' => 'Luglio',  '08' => 'Agosto',   '09' => 'Settembre',
			'10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre',
		];
		$giorni_it = [
			'Sun' => 'DOM', 'Mon' => 'LUN', 'Tue' => 'MAR',
			'Wed' => 'MER', 'Thu' => 'GIO', 'Fri' => 'VEN', 'Sat' => 'SAB',
		];

		/* Group by Y-m */
		$per_mese = [];
		foreach ( $eventi as $e ) {
			$per_mese[ gmdate( 'Y-m', strtotime( $e->_prima_data ) ) ][] = $e;
		}

		ob_start();
		foreach ( $per_mese as $mese_key => $gruppo ) {
			[ $anno, $mm ] = explode( '-', $mese_key );
			$nome_mese     = $mesi_it[ $mm ] ?? ucfirst( date_i18n( 'F', mktime( 0, 0, 0, (int) $mm, 1 ) ) );
			$n             = count( $gruppo );
			unset( $anno ); // used only for splitting
			?>
			<div class="cso-mese">
				<h2 class="cso-mese__heading display">
					<?php echo esc_html( $nome_mese ); ?>
					<span class="cso-mese__count">
						<?php echo esc_html( sprintf( /* translators: %d = count */ _n( '%d evento', '%d eventi', $n, 'calypsosub' ), $n ) ); ?>
					</span>
				</h2>
				<div class="cso-eventi-list">
				<?php foreach ( $gruppo as $e ) :
					$passato     = $e->_prima_data < $today;
					$ts          = strtotime( $e->_prima_data );
					$giorno      = $giorni_it[ gmdate( 'D', $ts ) ] ?? gmdate( 'D', $ts );
					$num         = (int) gmdate( 'j', $ts );
					$sottotitolo = (string) get_post_meta( $e->ID, '_evento_sottotitolo', true );
					$luogo_ev    = (string) get_post_meta( $e->ID, '_evento_luogo',       true );

					if ( $passato ) {
						$posti_html = '<span class="cso-evento-row__concluso">'
							. esc_html__( 'Concluso', 'calypsosub' ) . '</span>';
						$btn_html   = '<span class="cso-btn-dark cso-btn-dark--disabled">'
							. esc_html__( 'Terminato', 'calypsosub' ) . '</span>';
					} else {
						$max          = get_post_meta( $e->ID, '_evento_max_partecipanti', true );
						$posti        = ( $max === '' || $max === false )
							? null
							: max( 0, (int) $max - ( $booking_counts[ $e->ID ] ?? 0 ) );
						$lista_attesa = (int) get_post_meta( $e->ID, '_evento_lista_attesa', true ) === 1;

						if ( $posti === null ) {
							$posti_html = '<span class="cso-uscita-row__posti cso-uscita-row__posti--libera">'
								. esc_html__( 'Posti liberi', 'calypsosub' ) . '</span>';
						} elseif ( $posti === 0 ) {
							$posti_html = $lista_attesa
								? '<span class="cso-uscita-row__posti cso-uscita-row__posti--warn">'
									. esc_html__( "Lista d'attesa", 'calypsosub' ) . '</span>'
								: '<span class="cso-uscita-row__posti cso-uscita-row__posti--full">'
									. esc_html__( 'Esaurito', 'calypsosub' ) . '</span>';
						} elseif ( $posti <= 3 ) {
							$posti_html = '<span class="cso-uscita-row__posti cso-uscita-row__posti--warn">● '
								. esc_html( $posti . ' ' . _n( 'posto', 'posti', $posti, 'calypsosub' ) ) . '</span>';
						} else {
							$posti_html = '<span class="cso-uscita-row__posti cso-uscita-row__posti--ok">'
								. esc_html( $posti . ' ' . __( 'posti', 'calypsosub' ) ) . '</span>';
						}

						$book_url = is_user_logged_in()
							? add_query_arg( 'prenota', '1', get_permalink( $e->ID ) )
							: wp_login_url( get_permalink( $e->ID ) );

						if ( $posti === 0 && ! $lista_attesa ) {
							$btn_html = '<span class="cso-btn-dark cso-btn-dark--disabled">'
								. esc_html__( 'Esaurito', 'calypsosub' ) . '</span>';
						} else {
							$btn_label = ( $posti === 0 && $lista_attesa )
								? __( "Lista d'attesa", 'calypsosub' )
								: __( 'Iscriviti', 'calypsosub' );
							$btn_html  = '<a href="' . esc_url( $book_url ) . '" class="cso-btn-dark">'
								. esc_html( $btn_label ) . '</a>';
						}
					}

					$row_class = 'cso-evento-row' . ( $passato ? ' cso-evento-row--passato' : '' );
				?>
				<div class="<?php echo esc_attr( $row_class ); ?>">

					<div class="cso-evento-row__date">
						<span class="cso-evento-row__dayname"><?php echo esc_html( $giorno ); ?></span>
						<p class="cso-evento-row__daynum display"><?php echo esc_html( str_pad( (string) $num, 2, '0', STR_PAD_LEFT ) ); ?></p>
						<?php if ( $e->_prima_ora ) : ?>
						<span class="cso-evento-row__time"><?php echo esc_html( $e->_prima_ora ); ?></span>
						<?php endif; ?>
					</div>

					<div class="cso-evento-row__info">
						<a href="<?php echo esc_url( get_permalink( $e->ID ) ); ?>">
							<p class="cso-evento-row__title"><?php echo esc_html( $e->post_title ); ?></p>
						</a>
						<?php if ( $luogo_ev ) : ?>
						<p class="cso-evento-row__luogo">
							<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
							<?php echo esc_html( $luogo_ev ); ?>
						</p>
						<?php endif; ?>
					</div>

					<div class="cso-evento-row__sottotitolo">
						<?php if ( $sottotitolo ) echo esc_html( $sottotitolo ); ?>
					</div>

					<?php echo $posti_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

					<div class="cso-evento-row__cta">
						<?php echo $btn_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>

				</div>
				<?php endforeach; ?>
				</div><!-- .cso-eventi-list -->
			</div><!-- .cso-mese -->
			<?php
		}
		return (string) ob_get_clean();
	}
}
