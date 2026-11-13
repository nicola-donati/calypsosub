<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Etichette e tipi dei campi del form di prenotazione, per mostrare
 * "Altri partecipanti" invece di "altri_partecipanti" e per costruire il
 * modulo di modifica in admin.
 *
 * In _booking_form_data ci sono solo i NOMI dei campi CF7. Le etichette si
 * ricavano, in ordine di priorità, da:
 * 1. i "Campi prenotazione" configurati nelle impostazioni del plugin;
 * 2. i moduli Contact Form 7 (testo dentro <label> prima del tag, o il
 *    placeholder del tag);
 * 3. il nome del campo reso leggibile ("note-richieste" → "Note richieste").
 */
class Calypsosub_Booking_Fields {

	/** @var array<string,array{label:string,type:string,options:string[]}>|null */
	private static ?array $fields = null;

	/** Campi tecnici da non mostrare né modificare. */
	public const HIDDEN = [ '_debug_login', 'booking_post_id', 'booking_post_type', 'booking_user_id', 'booking_user_sig' ];

	public static function label( string $name ): string {
		$fields = self::fields();
		return $fields[ $name ]['label'] ?? self::humanize( $name );
	}

	/** @return array{label:string,type:string,options:string[]} */
	public static function field( string $name ): array {
		return self::fields()[ $name ] ?? [ 'label' => self::humanize( $name ), 'type' => '', 'options' => [] ];
	}

	public static function is_hidden( string $name ): bool {
		return in_array( $name, self::HIDDEN, true ) || str_starts_with( $name, '_' );
	}

	public static function humanize( string $name ): string {
		$text = trim( preg_replace( '/[\s_-]+/', ' ', $name ) );
		return $text === '' ? $name : mb_strtoupper( mb_substr( $text, 0, 1 ) ) . mb_substr( $text, 1 );
	}

	/** @return array<string,array{label:string,type:string,options:string[]}> */
	private static function fields(): array {
		if ( self::$fields !== null ) return self::$fields;

		$fields = [];

		// 2. Moduli CF7: tutti, perché la prenotazione non registra quale
		//    modulo è stato usato (uno per tipologia nel blocco Prenotazione).
		$form_ids = get_posts( [ 'post_type' => 'wpcf7_contact_form', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ] );
		foreach ( $form_ids as $form_id ) {
			foreach ( self::parse_cf7_template( (string) get_post_meta( $form_id, '_form', true ) ) as $name => $field ) {
				if ( ! isset( $fields[ $name ] ) || ( $fields[ $name ]['label'] === self::humanize( $name ) && $field['label'] !== self::humanize( $name ) ) ) {
					$fields[ $name ] = $field;
				}
			}
		}

		// 1. Impostazioni del plugin: hanno la precedenza sull'etichetta.
		foreach ( [ 'uscite', 'eventi', 'corsi' ] as $category ) {
			$campi = (array) ( get_option( 'calypsosub_opts_' . $category, [] )['campi_prenotazione'] ?? [] );
			foreach ( $campi as $campo ) {
				$name  = (string) ( $campo['nome'] ?? '' );
				$label = trim( (string) ( $campo['label'] ?? '' ) );
				if ( $name === '' || $label === '' ) continue;
				$fields[ $name ]          = $fields[ $name ] ?? [ 'type' => '', 'options' => [] ];
				$fields[ $name ]['label'] = $label;
			}
		}

		return self::$fields = $fields;
	}

	/**
	 * Estrae nome, etichetta, tipo e opzioni dei tag CF7 di un modulo.
	 *
	 * @return array<string,array{label:string,type:string,options:string[]}>
	 */
	public static function parse_cf7_template( string $template ): array {
		$fields = [];
		if ( ! preg_match_all( '/\[([a-z_]+)\*?\s+([A-Za-z0-9_-]+)([^\]]*)\]/', $template, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
			return $fields;
		}

		foreach ( $tags as $tag ) {
			$type   = $tag[1][0];
			$name   = $tag[2][0];
			$rest   = $tag[3][0];
			$offset = $tag[0][1];
			if ( in_array( $type, [ 'submit', 'response', 'hidden' ], true ) ) continue;

			preg_match_all( '/"([^"]*)"/', $rest, $quoted );
			$options = $quoted[1];

			// Testo del <label> che contiene il tag, prima del tag stesso.
			$label  = '';
			$before = substr( $template, 0, $offset );
			$open   = strripos( $before, '<label' );
			if ( $open !== false && stripos( substr( $before, $open ), '</label>' ) === false ) {
				$segment = substr( $before, $open );
				// Più campi nello stesso <label>: solo il testo dopo il tag precedente.
				$prev = strrpos( $segment, ']' );
				if ( $prev !== false ) $segment = substr( $segment, $prev + 1 );
				$label = html_entity_decode( wp_strip_all_tags( $segment ), ENT_QUOTES, 'UTF-8' );
				$label = trim( preg_replace( '/\s+/', ' ', $label ), " \t\n\r:*" );
			}
			// <label>Testo</label> subito prima del tag (label non "avvolgente").
			if ( $label === '' && preg_match( '/<label[^>]*>([^<\[]+)<\/label>\s*(?:<br\s*\/?>\s*)?$/i', $before, $closed ) ) {
				$label = trim( preg_replace( '/\s+/', ' ', html_entity_decode( $closed[1], ENT_QUOTES, 'UTF-8' ) ), " \t\n\r:*" );
			}
			if ( $label === '' && preg_match( '/placeholder\s+"([^"]+)"/', $rest, $ph ) ) {
				$label = $ph[1];
			}
			if ( $label === '' && $type === 'acceptance' ) {
				// [acceptance nome] testo [/acceptance]
				if ( preg_match( '/\]\s*([^\[]+?)\s*\[\/acceptance\]/s', substr( $template, $offset ), $acc ) ) {
					$label = trim( wp_strip_all_tags( $acc[1] ) );
				}
			}

			// Per select/radio/checkbox i valori quotati sono le scelte; per
			// gli altri tipi il quotato è placeholder/valore predefinito.
			$choices = in_array( $type, [ 'select', 'radio', 'checkbox' ], true )
				? array_values( array_filter( $options, static fn( $o ) => ! preg_match( '/^placeholder$/i', $o ) ) )
				: [];

			$fields[ $name ] = [
				'label'   => $label !== '' ? mb_substr( $label, 0, 120 ) : self::humanize( $name ),
				'type'    => $type,
				'options' => $choices,
			];
		}
		return $fields;
	}
}
