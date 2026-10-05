<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Dati strutturati schema.org per Uscite, Eventi, Corsi, Docenti + fallback
 * della meta description Yoast per i Docenti (CPT senza editor nativo, quindi
 * senza contenuto da cui Yoast possa generarla automaticamente).
 *
 * Il JSON-LD viene emesso in un <script> separato da quello di Yoast: nessuna
 * dipendenza dalle classi interne di Yoast, funziona anche se Yoast non è
 * attivo o cambia versione.
 */
class Calypsosub_Seo_Enhancements {

	public function init(): void {
		add_action( 'wp_head', [ $this, 'output_schema' ], 30 );
		add_filter( 'wpseo_metadesc', [ $this, 'docente_metadesc_fallback' ] );
	}

	public function output_schema(): void {
		if ( ! is_singular( [ 'calypso_uscita', 'calypso_evento', 'calypso_corso', 'calypso_docente' ] ) ) {
			return;
		}

		$id   = get_the_ID();
		$type = get_post_type( $id );

		switch ( $type ) {
			case 'calypso_uscita':
				$graph = $this->build_events_uscita( $id );
				break;
			case 'calypso_evento':
				$graph = $this->build_events_evento( $id );
				break;
			case 'calypso_corso':
				$graph = $this->build_course( $id );
				break;
			case 'calypso_docente':
				$graph = $this->build_person( $id );
				break;
			default:
				$graph = [];
		}

		if ( empty( $graph ) ) return;

		echo '<script type="application/ld+json" class="calypsosub-schema">'
			. wp_json_encode(
				[ '@context' => 'https://schema.org', '@graph' => $graph ],
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
			. '</script>' . "\n";
	}

	/**
	 * Fallback: se il CPT Docente non ha una meta description Yoast manuale,
	 * usa la bio breve/lunga (il CPT non ha 'editor', quindi Yoast non ha
	 * contenuto da cui generarla da solo).
	 */
	public function docente_metadesc_fallback( string $metadesc ): string {
		if ( $metadesc !== '' || ! is_singular( 'calypso_docente' ) ) {
			return $metadesc;
		}
		$id  = get_the_ID();
		$bio = (string) get_post_meta( $id, '_docente_bio_breve', true );
		if ( $bio === '' ) {
			$bio = (string) get_post_meta( $id, '_docente_bio', true );
		}
		return $bio !== '' ? $this->excerpt_text( $bio, 155 ) : $metadesc;
	}

	private function organization(): array {
		return [
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		];
	}

	private function build_events_uscita( int $id ): array {
		$title  = get_the_title( $id );
		$url    = get_permalink( $id );
		$luogo  = (string) get_post_meta( $id, '_uscita_luogo', true );
		$desc   = (string) get_post_meta( $id, '_uscita_desc_breve', true );
		if ( $desc === '' ) {
			$desc = (string) get_post_field( 'post_content', $id );
		}
		$desc = $this->excerpt_text( $desc, 300 );
		$img  = get_the_post_thumbnail_url( $id, 'large' ) ?: '';

		$occorrenze = function_exists( 'calypso_get_occorrenze_by_uscita' )
			? calypso_get_occorrenze_by_uscita( $id )
			: [];

		$events = [];
		foreach ( $occorrenze as $occ ) {
			$start = $this->iso_date( (string) get_post_meta( $occ->ID, '_occorrenza_uscita_data', true ) );
			if ( $start === '' ) continue;

			$event = $this->base_event( $title, $url, $start, $luogo, $img, $desc );
			$event['@id'] = $url . '#event-' . $occ->ID;
			$events[]     = $event;
		}
		return $events;
	}

	private function build_events_evento( int $id ): array {
		$title     = get_the_title( $id );
		$url       = get_permalink( $id );
		$luogo     = (string) get_post_meta( $id, '_evento_luogo', true );
		$indirizzo = (string) get_post_meta( $id, '_evento_indirizzo', true );
		$desc      = $this->excerpt_text( (string) get_post_field( 'post_content', $id ), 300 );
		$img       = get_the_post_thumbnail_url( $id, 'large' ) ?: '';
		$dates     = (array) ( get_post_meta( $id, '_evento_date', true ) ?: [] );

		$events = [];
		foreach ( $dates as $idx => $dt ) {
			$start = $this->iso_date( (string) $dt );
			if ( $start === '' ) continue;

			$event        = $this->base_event( $title, $url, $start, $luogo, $img, $desc, $indirizzo );
			$event['@id'] = $url . '#event-' . $idx;
			$events[]     = $event;
		}
		return $events;
	}

	private function base_event( string $title, string $url, string $start, string $luogo, string $img, string $desc, string $indirizzo = '' ): array {
		$event = [
			'@type'               => 'Event',
			'name'                => $title,
			'url'                 => $url,
			'startDate'           => $start,
			'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
			'eventStatus'         => 'https://schema.org/EventScheduled',
			'organizer'           => $this->organization(),
		];
		if ( $luogo !== '' || $indirizzo !== '' ) {
			$event['location'] = [ '@type' => 'Place' ];
			if ( $luogo !== '' )     $event['location']['name']    = $luogo;
			if ( $indirizzo !== '' ) $event['location']['address'] = $indirizzo;
		}
		if ( $img !== '' )   $event['image']       = [ $img ];
		if ( $desc !== '' )  $event['description'] = $desc;
		return $event;
	}

	private function build_course( int $id ): array {
		$title = get_the_title( $id );
		$url   = get_permalink( $id );
		$desc  = (string) get_post_meta( $id, '_corso_desc_breve', true );
		if ( $desc === '' ) {
			$desc = (string) get_post_field( 'post_content', $id );
		}
		$desc = $this->excerpt_text( $desc, 300 );

		$docenti_ids = (array) ( get_post_meta( $id, '_corso_docenti_ids', true ) ?: [] );
		$instructors = [];
		foreach ( $docenti_ids as $did ) {
			$did = (int) $did;
			if ( ! $did || get_post_status( $did ) !== 'publish' ) continue;
			$nome = trim(
				(string) get_post_meta( $did, '_docente_nome', true ) . ' ' .
				(string) get_post_meta( $did, '_docente_cognome', true )
			);
			$instructors[] = [
				'@type' => 'Person',
				'name'  => $nome !== '' ? $nome : get_the_title( $did ),
				'url'   => get_permalink( $did ),
			];
		}

		$course = [
			'@type'    => 'Course',
			'@id'      => $url . '#course',
			'name'     => $title,
			'url'      => $url,
			'provider' => $this->organization(),
		];
		if ( $desc !== '' ) $course['description'] = $desc;

		$instance = [ '@type' => 'CourseInstance', 'courseMode' => 'Onsite' ];
		if ( $instructors ) $instance['instructor'] = $instructors;
		$course['hasCourseInstance'] = [ $instance ];

		return [ $course ];
	}

	private function build_person( int $id ): array {
		$nome    = (string) get_post_meta( $id, '_docente_nome', true );
		$cognome = (string) get_post_meta( $id, '_docente_cognome', true );
		$full    = trim( "$nome $cognome" ) ?: get_the_title( $id );
		$ruolo   = (string) get_post_meta( $id, '_docente_ruolo', true );

		$bio = (string) get_post_meta( $id, '_docente_bio_breve', true );
		if ( $bio === '' ) $bio = (string) get_post_meta( $id, '_docente_bio', true );
		$bio = $this->excerpt_text( $bio, 300 );

		$email  = (string) get_post_meta( $id, '_docente_email', true );
		$tel    = (string) get_post_meta( $id, '_docente_telefono', true );
		$img    = get_the_post_thumbnail_url( $id, 'large' ) ?: '';
		$social = (array) ( get_post_meta( $id, '_docente_social', true ) ?: [] );

		$specs_raw = get_post_meta( $id, '_docente_specializzazioni', true );
		if ( is_array( $specs_raw ) ) {
			$specs = array_values( array_filter( $specs_raw ) );
		} elseif ( is_string( $specs_raw ) && $specs_raw !== '' ) {
			$specs = array_values( array_filter( array_map( 'trim', explode( ',', $specs_raw ) ) ) );
		} else {
			$specs = [];
		}

		$brevetti = get_the_terms( $id, 'calypso_brevetto' );
		$brevetti = ( ! is_wp_error( $brevetti ) && $brevetti ) ? wp_list_pluck( $brevetti, 'name' ) : [];

		$person = [
			'@type'    => 'Person',
			'@id'      => get_permalink( $id ) . '#person',
			'name'     => $full,
			'url'      => get_permalink( $id ),
			'worksFor' => $this->organization(),
		];
		if ( $ruolo !== '' ) $person['jobTitle']    = $ruolo;
		if ( $bio !== '' )   $person['description'] = $bio;
		if ( $img !== '' )   $person['image']       = $img;
		if ( $email !== '' ) $person['email']       = $email;
		if ( $tel !== '' )   $person['telephone']   = $tel;
		if ( $specs )        $person['knowsAbout']  = $specs;

		if ( $brevetti ) {
			$person['hasCredential'] = array_map(
				static fn( string $name ): array => [ '@type' => 'EducationalOccupationalCredential', 'name' => $name ],
				$brevetti
			);
		}

		$same_as = [];
		foreach ( $social as $s ) {
			if ( ! empty( $s['url'] ) ) $same_as[] = $s['url'];
		}
		if ( $same_as ) $person['sameAs'] = $same_as;

		return [ $person ];
	}

	private function iso_date( string $dt ): string {
		if ( $dt === '' ) return '';
		try {
			$tz = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
			return ( new DateTimeImmutable( $dt, $tz ) )->format( DATE_ATOM );
		} catch ( Exception $e ) {
			return '';
		}
	}

	private function excerpt_text( string $text, int $len ): string {
		$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) );
		if ( $text === '' || mb_strlen( $text ) <= $len ) return $text;
		return mb_substr( $text, 0, $len - 1 ) . '…';
	}
}
