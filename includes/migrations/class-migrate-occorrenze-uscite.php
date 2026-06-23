<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Migrazione one-shot: converte le date dentro _uscita_date[] in post
 * calypso_occ_uscita separati, e ri-punta le prenotazioni esistenti
 * dalla scheda uscita alla nuova occorrenza generata.
 */
class Calypsosub_Migrate_Occorrenze_Uscite {

	private const OPTION = 'calypsosub_migrated_occorrenze_uscite';

	public function init(): void {
		add_action( 'admin_init', [ $this, 'maybe_run' ] );
	}

	public function maybe_run(): void {
		if ( get_option( self::OPTION ) ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;

		$this->run();
		update_option( self::OPTION, 1 );
	}

	public function run(): void {
		$uscite = get_posts( [
			'post_type'      => 'calypso_uscita',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		] );

		foreach ( $uscite as $uscita_id ) {
			$dates = (array) ( get_post_meta( $uscita_id, '_uscita_date', true ) ?: [] );
			if ( empty( $dates ) ) continue;

			$max_partecipanti   = get_post_meta( $uscita_id, '_uscita_max_partecipanti', true );
			$max_accompagnatori = get_post_meta( $uscita_id, '_uscita_max_accompagnatori', true );
			$lista_attesa        = (int) get_post_meta( $uscita_id, '_uscita_lista_attesa', true );

			$occorrenza_ids = [];
			foreach ( $dates as $date_str ) {
				$occ_id = wp_insert_post( [
					'post_type'   => 'calypso_occ_uscita',
					'post_title'  => get_the_title( $uscita_id ) . ' — ' . $date_str,
					'post_status' => 'publish',
				] );
				if ( is_wp_error( $occ_id ) ) continue;

				update_post_meta( $occ_id, '_occorrenza_uscita_uscita_id', $uscita_id );
				update_post_meta( $occ_id, '_occorrenza_uscita_data', $date_str );
				if ( $max_partecipanti !== '' && $max_partecipanti !== false ) {
					update_post_meta( $occ_id, '_occorrenza_uscita_posti', absint( $max_partecipanti ) );
				}
				if ( $max_accompagnatori !== '' && $max_accompagnatori !== false ) {
					update_post_meta( $occ_id, '_occorrenza_uscita_max_accompagnatori', absint( $max_accompagnatori ) );
				}
				update_post_meta( $occ_id, '_occorrenza_uscita_lista_attesa', $lista_attesa );

				$occorrenza_ids[] = $occ_id;
			}

			$this->repoint_bookings( $uscita_id, $occorrenza_ids );

			delete_post_meta( $uscita_id, '_uscita_date' );
			delete_post_meta( $uscita_id, '_uscita_max_partecipanti' );
			delete_post_meta( $uscita_id, '_uscita_max_accompagnatori' );
			delete_post_meta( $uscita_id, '_uscita_lista_attesa' );
		}
	}

	/**
	 * Ri-punta le prenotazioni esistenti sulla prima occorrenza generata
	 * per quella uscita. Il vecchio schema non registrava a quale data del
	 * repeater si riferisse una prenotazione, quindi non c'è modo di
	 * scegliere fra più occorrenze con certezza: si assegna sempre alla
	 * prima e si segnala nel log per verifica manuale se ce n'erano altre.
	 */
	private function repoint_bookings( int $uscita_id, array $occorrenza_ids ): void {
		if ( empty( $occorrenza_ids ) ) return;

		$bookings = get_posts( [
			'post_type'      => 'calypso_prenotazione',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [
				[ 'key' => '_booking_post_id',   'value' => $uscita_id, 'compare' => '=', 'type' => 'NUMERIC' ],
				[ 'key' => '_booking_post_type', 'value' => 'calypso_uscita', 'compare' => '=' ],
			],
		] );

		$target = $occorrenza_ids[0];
		foreach ( $bookings as $booking_id ) {
			update_post_meta( $booking_id, '_booking_post_id', $target );
			update_post_meta( $booking_id, '_booking_post_type', 'calypso_occ_uscita' );
			if ( count( $occorrenza_ids ) > 1 ) {
				error_log( sprintf(
					'[calypsosub] Migrazione occorrenze uscite: prenotazione #%d (uscita #%d) assegnata alla prima di %d occorrenze generate — verificare a mano la data corretta.',
					$booking_id, $uscita_id, count( $occorrenza_ids )
				) );
			}
		}
	}
}
