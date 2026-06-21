<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Legge un'opzione di impostazioni del plugin.
 * Se vuota restituisce il default.
 *
 * @param string $section  docenti | uscite | corsi | eventi
 * @param string $key      chiave del campo
 * @param string $default  valore di fallback
 */
function calypsosub_opt( string $section, string $key, string $default = '' ): string {
	static $cache = [];
	if ( ! isset( $cache[ $section ] ) ) {
		$cache[ $section ] = (array) get_option( 'calypsosub_opts_' . $section, [] );
	}
	$val = $cache[ $section ][ $key ] ?? '';
	return $val !== '' ? $val : $default;
}

/**
 * Wrapper per auth — estendibile con membership plugin.
 */
function calypso_is_user_logged_in(): bool {
	return apply_filters( 'calypso_is_user_logged_in', is_user_logged_in() );
}

function calypso_get_current_user_id(): int {
	return (int) apply_filters( 'calypso_current_user_id', get_current_user_id() );
}

/**
 * Lista uscite ordinate per data.
 *
 * @param array $args  WP_Query args extra (merge con defaults).
 * @return WP_Post[]
 */
function calypso_get_uscite( array $args = [] ): array {
	$query = new WP_Query( array_merge( [
		'post_type'      => 'calypso_uscita',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_uscita_date',
		'order'          => 'ASC',
	], $args ) );
	return $query->posts;
}

/**
 * Lista eventi.
 */
function calypso_get_eventi( array $args = [] ): array {
	$query = new WP_Query( array_merge( [
		'post_type'      => 'calypso_evento',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_evento_date',
		'order'          => 'ASC',
	], $args ) );
	return $query->posts;
}

/**
 * Lista corsi ordinati per titolo.
 */
function calypso_get_corsi( array $args = [] ): array {
	$query = new WP_Query( array_merge( [
		'post_type'      => 'calypso_corso',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	], $args ) );
	return $query->posts;
}

/**
 * Prossima occorrenza non passata per un corso.
 */
function calypso_get_next_occorrenza( int $corso_id ): ?WP_Post {
	$query = new WP_Query( [
		'post_type'      => 'calypso_occorrenza',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_occorrenza_data_inizio',
		'order'          => 'ASC',
		'meta_query'     => [
			'relation' => 'AND',
			[
				'key'     => '_occorrenza_corso_id',
				'value'   => $corso_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			],
			[
				'key'     => '_occorrenza_data_fine',
				'value'   => date( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			],
		],
	] );
	return $query->posts[0] ?? null;
}

/**
 * Periodo leggibile per un'occorrenza (es. "MAR – GIU 2026").
 */
function calypso_get_occorrenza_periodo( int $occorrenza_id ): string {
	$inizio = get_post_meta( $occorrenza_id, '_occorrenza_data_inizio', true );
	$fine   = get_post_meta( $occorrenza_id, '_occorrenza_data_fine', true );
	if ( ! $inizio ) return '';

	$ts_start  = strtotime( $inizio );
	$mese_ini  = strtoupper( date_i18n( 'M', $ts_start ) );
	$anno_ini  = date( 'Y', $ts_start );

	if ( $fine && $fine !== $inizio ) {
		$ts_end   = strtotime( $fine );
		$mese_fin = strtoupper( date_i18n( 'M', $ts_end ) );
		$anno_fin = date( 'Y', $ts_end );
		if ( $anno_ini === $anno_fin ) {
			return $mese_ini . ' – ' . $mese_fin . ' ' . $anno_ini;
		}
		return $mese_ini . ' ' . $anno_ini . ' – ' . $mese_fin . ' ' . $anno_fin;
	}
	return date_i18n( 'j M Y', $ts_start );
}

/**
 * Tutte le occorrenze di un corso, ordinate per data.
 *
 * @return WP_Post[]
 */
function calypso_get_occorrenze_by_corso( int $corso_id, array $args = [] ): array {
	$query = new WP_Query( array_merge( [
		'post_type'      => 'calypso_occorrenza',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_occorrenza_data_inizio',
		'order'          => 'ASC',
		'meta_query'     => [ [
			'key'     => '_occorrenza_corso_id',
			'value'   => $corso_id,
			'compare' => '=',
			'type'    => 'NUMERIC',
		] ],
	], $args ) );
	return $query->posts;
}

/**
 * Lista docenti.
 */
function calypso_get_docenti( array $args = [] ): array {
	$query = new WP_Query( array_merge( [
		'post_type'      => 'calypso_docente',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	], $args ) );
	return $query->posts;
}

/**
 * IDs prenotazioni dell'utente.
 *
 * @return int[]
 */
function calypso_get_user_bookings( int $user_id ): array {
	global $calypsosub_booking_manager;
	if ( ! $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) return [];
	return $calypsosub_booking_manager->get_user_bookings( $user_id );
}

/**
 * Verifica disponibilità per prenotazione.
 */
function calypso_can_book( int $post_id, int $user_id ): bool {
	global $calypsosub_booking_manager;
	if ( ! $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) return false;
	if ( $calypsosub_booking_manager->user_has_booking( $post_id, $user_id ) ) return false;
	$remaining = $calypsosub_booking_manager->get_remaining_spots( $post_id );
	if ( $remaining === null ) return true;
	if ( $remaining > 0 ) return true;
	$post_type   = get_post_type( $post_id );
	$meta_prefix = $post_type === 'calypso_uscita' ? '_uscita' : '_evento';
	return (bool) get_post_meta( $post_id, $meta_prefix . '_lista_attesa', true );
}

/**
 * Crea prenotazione.
 *
 * @return string|WP_Error  'confermata'|'lista_attesa'|WP_Error
 */
function calypso_book( int $post_id, int $user_id, array $data ): string|WP_Error {
	global $calypsosub_booking_manager;
	if ( ! $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) {
		return new WP_Error( 'not_init', 'Booking manager non inizializzato.' );
	}
	return $calypsosub_booking_manager->book( $post_id, $user_id, $data );
}

/**
 * Cancella prenotazione.
 */
function calypso_cancel_booking( int $booking_id, int $user_id ): bool|WP_Error {
	global $calypsosub_booking_manager;
	if ( ! $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) {
		return new WP_Error( 'not_init', 'Booking manager non inizializzato.' );
	}
	return $calypsosub_booking_manager->cancel_booking( $booking_id, $user_id );
}

/**
 * Prima data futura di un array di date stringa (Y-m-d o Y-m-d\TH:i),
 * o l'ultima se tutte sono passate, o '' se l'array è vuoto.
 */
function calypso_next_future_date( array $dates ): string {
	if ( empty( $dates ) ) return '';
	sort( $dates );
	$now = current_time( 'Y-m-d\TH:i' );
	foreach ( $dates as $dt ) {
		if ( $dt >= $now ) return $dt;
	}
	return (string) end( $dates );
}
