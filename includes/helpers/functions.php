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
 * Variante numerica di calypsosub_opt(): se il valore salvato è 0 (impostato
 * volontariamente o per via del vecchio bug del pannello campi numerici),
 * usa comunque il default — 0 non è mai un valore sensato per dimensioni/pesi
 * font, quindi non ha senso distinguere "0 voluto" da "0 per errore".
 *
 * @param string $section  docenti | uscite | corsi | eventi
 * @param string $key      chiave del campo
 * @param string $default  valore di fallback (numerico, es. '14')
 */
function calypsosub_opt_int( string $section, string $key, string $default = '0' ): int {
	$val = (int) calypsosub_opt( $section, $key, $default );
	return $val !== 0 ? $val : (int) $default;
}

/**
 * Converte un hex (#rgb o #rrggbb) in stringa rgba() con opacità data.
 * Usata per overlay hero configurabili. Fallback su abyss se l'hex non è valido.
 */
function calypso_hex2rgba( string $hex, float $alpha ): string {
	$hex = ltrim( trim( $hex ), '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
		return "rgba(6,24,38,$alpha)";
	}
	[ $r, $g, $b ] = array_map( 'hexdec', [ substr( $hex, 0, 2 ), substr( $hex, 2, 2 ), substr( $hex, 4, 2 ) ] );
	return "rgba($r,$g,$b,$alpha)";
}

/**
 * Formatta una data salvata come 'Y-m-d' (sola data) o 'Y-m-d\TH:i' (data +
 * ora) usando i formati data/ora del sito. Se non è stata impostata un'ora
 * (stringa di 10 caratteri, nessun 'T'), l'ora viene omessa invece di
 * mostrare 00:00 — regola generale per eventi e uscite.
 */
function calypso_format_datetime( string $date_str ): string {
	if ( $date_str === '' ) return '';
	$ts = strtotime( $date_str );
	if ( ! $ts ) return '';
	$format = strlen( $date_str ) > 10
		? get_option( 'date_format' ) . ' ' . get_option( 'time_format' )
		: get_option( 'date_format' );
	return date_i18n( $format, $ts );
}

/**
 * Valida un tag heading scelto in editor (h1-h6). Qualsiasi altro valore
 * (incluso 'none' o input non atteso) ricade sul tag non-heading originale
 * del blocco, mai un'eco diretta della stringa non controllata.
 */
function calypsosub_title_tag( string $value, string $none_tag = 'div' ): string {
	static $allowed = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
	return in_array( $value, $allowed, true ) ? $value : $none_tag;
}

/**
 * Rende un titolo in HTML convertendo gli "a capo" e colorando le porzioni
 * racchiuse tra doppi asterischi — es. "Sotto la **superficie**" — con il
 * colore indicato. Tutto il testo resta correttamente escapato; eventuali
 * "**" non in coppia restano semplicemente come testo letterale.
 */
function calypsosub_render_highlighted_title( string $text, string $highlight_color ): string {
	$parts = preg_split( '/\*\*(.+?)\*\*/su', $text, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( $parts === false ) {
		return nl2br( esc_html( $text ) );
	}
	$html = '';
	foreach ( $parts as $i => $part ) {
		if ( $part === '' ) continue;
		$escaped = nl2br( esc_html( $part ) );
		$html   .= ( $i % 2 === 1 )
			? '<span style="color:' . esc_attr( $highlight_color ) . '">' . $escaped . '</span>'
			: $escaped;
	}
	return $html;
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
 * Occorrenze (date) di una scheda uscita, ordinate per data crescente.
 */
function calypso_get_occorrenze_by_uscita( int $uscita_id, array $args = [] ): array {
	$query = new WP_Query( array_merge( [
		'post_type'      => 'calypso_occ_uscita',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_occorrenza_uscita_data',
		'order'          => 'ASC',
		'meta_query'     => [ [
			'key'     => '_occorrenza_uscita_uscita_id',
			'value'   => $uscita_id,
			'compare' => '=',
			'type'    => 'NUMERIC',
		] ],
	], $args ) );
	return $query->posts;
}

/**
 * Prossime uscite (esclusa una data), una per scheda uscita, ordinate
 * per data della prima occorrenza futura crescente. Usata per la sezione
 * "Altre uscite in calendario" nella pagina singola uscita.
 *
 * @return WP_Post[] Post di tipo calypso_uscita.
 */
function calypso_get_prossime_uscite_escluso( int $uscita_id_escluso, int $limit = 3 ): array {
	$oggi = current_time( 'Y-m-d\TH:i' );

	$occ_query = new WP_Query( [
		'post_type'      => 'calypso_occ_uscita',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => '_occorrenza_uscita_data',
		'order'          => 'ASC',
		'meta_query'     => [ [
			'key'     => '_occorrenza_uscita_data',
			'value'   => $oggi,
			'compare' => '>=',
			'type'    => 'DATETIME',
		] ],
	] );

	$uscite_ids = [];
	foreach ( $occ_query->posts as $occ ) {
		$uid = (int) get_post_meta( $occ->ID, '_occorrenza_uscita_uscita_id', true );
		if ( ! $uid || $uid === $uscita_id_escluso ) continue;
		if ( in_array( $uid, $uscite_ids, true ) ) continue;
		$uscite_ids[] = $uid;
		if ( count( $uscite_ids ) >= $limit ) break;
	}

	if ( ! $uscite_ids ) return [];

	$uscite = array_map( 'get_post', $uscite_ids );
	return array_values( array_filter( $uscite, static fn( $u ) => $u instanceof WP_Post && $u->post_status === 'publish' ) );
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
 * Email di un prenotante anonimo dai dati liberi del form (CF7): i nomi dei
 * campi sono configurati liberamente lato admin, quindi si prova una lista
 * di alias comuni.
 */
function calypso_extract_email( array $data ): string {
	foreach ( [ 'email', 'mail', 'e-mail', 'your-email' ] as $key ) {
		if ( ! empty( $data[ $key ] ) && is_string( $data[ $key ] ) ) {
			return sanitize_email( $data[ $key ] );
		}
	}
	return '';
}

/**
 * Verifica disponibilità per prenotazione. $guest_email è usata per il
 * controllo duplicati solo quando $user_id è 0 (prenotante anonimo): non
 * c'è un utente loggato su cui basarsi, quindi si usa l'email del form.
 */
function calypso_can_book( int $post_id, int $user_id, string $guest_email = '' ): bool {
	global $calypsosub_booking_manager;
	if ( ! $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) return false;
	if ( $user_id ) {
		if ( $calypsosub_booking_manager->user_has_booking( $post_id, $user_id ) ) return false;
	} elseif ( $guest_email !== '' && $calypsosub_booking_manager->guest_has_booking( $post_id, $guest_email ) ) {
		return false;
	}

	$now       = current_time( 'Y-m-d\TH:i' );
	$post_type = get_post_type( $post_id );
	if ( $post_type === 'calypso_occ_uscita' ) {
		$data = (string) get_post_meta( $post_id, '_occorrenza_uscita_data', true );
		if ( $data !== '' && $data < $now ) return false;
	} elseif ( $post_type === 'calypso_evento' ) {
		$dates = (array) ( get_post_meta( $post_id, '_evento_date', true ) ?: [] );
		if ( $dates && calypso_next_future_date( $dates ) < $now ) return false;
	}

	$remaining = $calypsosub_booking_manager->get_remaining_spots( $post_id );
	if ( $remaining === null ) return true;
	if ( $remaining > 0 ) return true;
	$meta_prefix = $post_type === 'calypso_occ_uscita' ? '_occorrenza_uscita' : '_evento';
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
function calypso_cancel_booking( int $booking_id, int $user_id, string $token = '' ): bool|WP_Error {
	global $calypsosub_booking_manager;
	if ( ! $calypsosub_booking_manager instanceof Calypsosub_Booking_Manager ) {
		return new WP_Error( 'not_init', 'Booking manager non inizializzato.' );
	}
	return $calypsosub_booking_manager->cancel_booking( $booking_id, $user_id, $token );
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

/**
 * Link per aprire un indirizzo/luogo nei navigatori più comuni sul cellulare.
 * $query è testo libero (indirizzo completo o nome del luogo) — tutti e tre
 * i servizi accettano ricerca testuale, non serve geocodificare lato server.
 *
 * @return array{google:string,apple:string,waze:string}
 */
function calypso_maps_links( string $query ): array {
	$q = rawurlencode( trim( $query ) );
	return [
		'google' => "https://www.google.com/maps/search/?api=1&query={$q}",
		'apple'  => "https://maps.apple.com/?q={$q}",
		'waze'   => "https://waze.com/ul?q={$q}&navigate=yes",
	];
}

/**
 * Escapa un valore di testo per un campo ICS (RFC 5545): virgole, punti e
 * virgola e newline vanno preceduti da backslash.
 */
function calypso_ics_escape( string $text ): string {
	return str_replace( [ '\\', ',', ';', "\n" ], [ '\\\\', '\\,', '\\;', '\\n' ], trim( $text ) );
}

/**
 * Costruisce un file .ics (singolo VEVENT) e lo restituisce come data: URI,
 * pronto per un <a href="..." download="evento.ics">. Nessun endpoint
 * server separato necessario. L'orario è quello locale del sito (floating,
 * senza fuso) — sufficiente per un singolo club, niente VTIMEZONE.
 *
 * @param string      $title     Titolo evento.
 * @param string      $start     'Y-m-d' (tutto il giorno) o 'Y-m-d\TH:i'.
 * @param string      $end       Come $start, stesso formato di $start; '' = calcolata da $duration_hours.
 * @param string      $location  Luogo/indirizzo.
 * @param string      $description
 * @param float       $duration_hours  Usata solo se $end è vuoto e $start ha un orario.
 */
function calypso_ics_data_uri( string $title, string $start, string $end, string $location, string $description = '', float $duration_hours = 2.0 ): string {
	$all_day = strlen( $start ) <= 10;

	if ( $all_day ) {
		$dtstart = 'DTSTART;VALUE=DATE:' . str_replace( '-', '', $start );
		$end_ts  = strtotime( ( $end ?: $start ) . ' +1 day' );
		$dtend   = 'DTEND;VALUE=DATE:' . gmdate( 'Ymd', $end_ts );
	} else {
		$start_ts = strtotime( str_replace( 'T', ' ', $start ) );
		$end_ts   = $end !== '' ? strtotime( str_replace( 'T', ' ', $end ) ) : $start_ts + (int) round( $duration_hours * HOUR_IN_SECONDS );
		$dtstart  = 'DTSTART:' . gmdate( 'Ymd\THis', $start_ts );
		$dtend    = 'DTEND:' . gmdate( 'Ymd\THis', $end_ts );
	}

	$lines = [
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//Calypso Sub Arezzo//Eventi//IT',
		'CALSCALE:GREGORIAN',
		'BEGIN:VEVENT',
		'UID:' . md5( $title . $start . $location ) . '@calypsosub.it',
		'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
		$dtstart,
		$dtend,
		'SUMMARY:' . calypso_ics_escape( $title ),
	];
	if ( $location )    $lines[] = 'LOCATION:' . calypso_ics_escape( $location );
	if ( $description ) $lines[] = 'DESCRIPTION:' . calypso_ics_escape( $description );
	$lines[] = 'END:VEVENT';
	$lines[] = 'END:VCALENDAR';

	$ics = implode( "\r\n", $lines );
	return 'data:text/calendar;charset=utf8,' . rawurlencode( $ics );
}

/**
 * Link "Aggiungi a Google Calendar" (web). Stessi parametri di calypso_ics_data_uri().
 */
function calypso_gcal_link( string $title, string $start, string $end, string $location, string $description = '', float $duration_hours = 2.0 ): string {
	$all_day = strlen( $start ) <= 10;

	if ( $all_day ) {
		$start_fmt = str_replace( '-', '', $start );
		$end_ts    = strtotime( ( $end ?: $start ) . ' +1 day' );
		$end_fmt   = gmdate( 'Ymd', $end_ts );
	} else {
		$start_gmt = get_gmt_from_date( str_replace( 'T', ' ', $start ), 'Ymd\THis' );
		$end_src   = $end !== '' ? str_replace( 'T', ' ', $end ) : date( 'Y-m-d H:i:s', strtotime( str_replace( 'T', ' ', $start ) ) + (int) round( $duration_hours * HOUR_IN_SECONDS ) );
		$end_gmt   = get_gmt_from_date( $end_src, 'Ymd\THis' );
		$start_fmt = $start_gmt . 'Z';
		$end_fmt   = $end_gmt . 'Z';
	}

	$args = [
		'action'   => 'TEMPLATE',
		'text'     => $title,
		'dates'    => $start_fmt . '/' . $end_fmt,
		'details'  => $description,
		'location' => $location,
	];
	return 'https://calendar.google.com/calendar/render?' . http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );
}
