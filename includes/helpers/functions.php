<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
 * Lista corsi.
 */
function calypso_get_corsi( array $args = [] ): array {
	$query = new WP_Query( array_merge( [
		'post_type'      => 'calypso_corso',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_corso_data_inizio',
		'order'          => 'ASC',
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
