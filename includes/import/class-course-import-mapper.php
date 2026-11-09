<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Maps spreadsheet rows (as returned by Calypsosub_Course_Spreadsheet_Reader)
 * to normalized course data. Pure PHP, no WordPress calls, so it can be unit
 * tested. Empty cells and placeholders such as "[DA DEFINIRE]" are dropped,
 * so they never overwrite data already saved on the site.
 */
class Calypsosub_Course_Import_Mapper {

	/** Highest "Fase N" column group read from the file. */
	const MAX_PHASES = 12;

	/** Course meta keys filled from plain text columns. */
	const META_COLUMNS = [
		'_corso_sottotitolo'     => [ 'titolo display', '_corso_sottotitolo' ],
		'_corso_badge'           => [ 'badge certificazione', '_corso_badge' ],
		'_corso_desc_breve'      => [ 'descrizione breve', '_corso_desc_breve' ],
		'_corso_competenze'      => [ 'cosa imparerai', '_corso_competenze' ],
		'_corso_stat_durata'     => [ 'durata', '_corso_stat_durata' ],
		'_corso_stat_pratica'    => [ 'immersioni / pratica', 'immersioni', '_corso_stat_pratica' ],
		'_corso_stat_profondita' => [ 'profondità', 'profondita', '_corso_stat_profondita' ],
		'_corso_periodo'         => [ 'periodo tipico', 'periodo', '_corso_periodo' ],
		'_corso_requisiti'       => [ 'requisiti di accesso', 'requisiti', '_corso_requisiti' ],
		'_corso_materiale'       => [ 'materiale incluso', 'materiale', '_corso_materiale' ],
		'_corso_contatto'        => [ 'contatto sidebar', 'contatto', '_corso_contatto' ],
		'_corso_contatto_nome'   => [ 'nome contatto', '_corso_contatto_nome' ],
		'_corso_link_iscrizione' => [ 'link iscrizione personalizzato', 'link iscrizione', '_corso_link_iscrizione' ],
	];

	/** Yoast SEO meta keys. */
	const YOAST_COLUMNS = [
		'_yoast_wpseo_title'    => [ 'title seo', 'yoast: titolo seo' ],
		'_yoast_wpseo_metadesc' => [ 'meta description', 'yoast: meta description' ],
		'_yoast_wpseo_focuskw'  => [ 'parola chiave yoast', 'frase chiave', 'yoast: frase chiave' ],
	];

	const OTHER_COLUMNS = [
		'title'     => [ 'corso (titolo wordpress)', 'corso', 'titolo', 'post_title' ],
		'slug'      => [ 'slug url', 'slug', '/corsi/<slug>/' ],
		'categoria' => [ 'categoria', 'linea' ],
		'livello'   => [ 'livello', 'tassonomia calypso_livello' ],
		'content'   => [ 'descrizione (contenuto pagina)', 'descrizione', 'post_content' ],
		'direttore' => [ 'direttore del corso', 'direttore', '_corso_direttore_id' ],
		'docenti'   => [ 'docenti del corso', 'docenti', '_corso_docenti_ids' ],
		'eta_min'   => [ 'età minima', 'eta minima', '_corso_eta_min' ],
		'approvato' => [ 'approvato' ],
	];

	/** Values in the header-description row of the template, skipped on import. */
	const TECHNICAL_TITLES = [ 'post_title' ];

	/**
	 * @param array<int, array<string, string>> $rows
	 * @param bool $only_approved Import only rows whose "Approvato" cell says yes.
	 * @return array{courses: array<int, array<string, mixed>>, has_approval_column: bool}
	 */
	public function map_rows( array $rows, bool $only_approved = true ): array {
		$has_approval = false;
		foreach ( $rows as $row ) {
			if ( $this->find_key( $row, self::OTHER_COLUMNS['approvato'] ) !== null ) {
				$has_approval = true;
				break;
			}
		}

		$courses  = [];
		$position = 0;
		foreach ( $rows as $index => $row ) {
			$title = $this->value( $row, self::OTHER_COLUMNS['title'] );
			if ( $title !== null && in_array( strtolower( $title ), self::TECHNICAL_TITLES, true ) ) {
				continue;
			}
			$position++;
			$course = $this->map_row( $row );
			$course['position'] = $position;
			$course['line']     = $index;

			if ( $course['title'] === '' ) {
				$course['skip'] = 'Titolo del corso mancante';
			} elseif ( $only_approved && $has_approval && ! $course['approved'] ) {
				$course['skip'] = 'Non approvato';
			}
			$courses[] = $course;
		}
		return [ 'courses' => $courses, 'has_approval_column' => $has_approval ];
	}

	/** @param array<string, string> $row */
	public function map_row( array $row ): array {
		$course = [
			'title'     => $this->value( $row, self::OTHER_COLUMNS['title'] ) ?? '',
			'slug'      => $this->value( $row, self::OTHER_COLUMNS['slug'] ) ?? '',
			'categoria' => $this->value( $row, self::OTHER_COLUMNS['categoria'] ),
			'livello'   => $this->value( $row, self::OTHER_COLUMNS['livello'] ),
			'content'   => $this->value( $row, self::OTHER_COLUMNS['content'] ),
			'direttore' => $this->value( $row, self::OTHER_COLUMNS['direttore'] ),
			'docenti'   => $this->split_list( $this->value( $row, self::OTHER_COLUMNS['docenti'] ) ),
			'approved'  => $this->is_yes( (string) $this->raw( $row, self::OTHER_COLUMNS['approvato'] ) ),
			'meta'      => [],
			'yoast'     => [],
			'fasi'      => $this->phases( $row ),
			'eta_min'   => null,
			'warnings'  => [],
			'skip'      => null,
		];

		foreach ( self::META_COLUMNS as $key => $aliases ) {
			$v = $this->is_clear_token( (string) $this->raw( $row, $aliases ) ) ? '' : $this->value( $row, $aliases );
			if ( $v !== null ) {
				$course['meta'][ $key ] = $v;
			}
		}
		foreach ( self::YOAST_COLUMNS as $key => $aliases ) {
			$v = $this->value( $row, $aliases );
			if ( $v !== null ) {
				$course['yoast'][ $key ] = $v;
			}
		}

		$age = $this->value( $row, self::OTHER_COLUMNS['eta_min'] );
		if ( $age !== null && preg_match( '/\d+/', $age, $m ) ) {
			$course['eta_min'] = (int) $m[0];
		} elseif ( isset( $course['meta']['_corso_requisiti'] )
			&& preg_match( '/et[àa] minima[:\s]+(\d+)/iu', $course['meta']['_corso_requisiti'], $m ) ) {
			$course['eta_min'] = (int) $m[1];
		}

		$course['warnings'] = $this->placeholder_warnings( $course );
		return $course;
	}

	/** True for "Sì", "si", "yes", "x", "1", "true", "ok". */
	public function is_yes( string $value ): bool {
		$v = strtolower( trim( $value ) );
		$v = str_replace( [ 'ì', 'í' ], 'i', $v );
		return in_array( $v, [ 'si', 'yes', 'y', 'x', '1', 'true', 'ok', 'approvato' ], true );
	}

	/** Fields the user can choose to update on existing courses, with their on-screen labels. */
	const SELECTABLE_FIELDS = [
		'title'                  => 'Titolo',
		'slug'                   => 'Slug (indirizzo della pagina)',
		'content'                => 'Descrizione (contenuto pagina)',
		'categoria'              => 'Categoria',
		'livello'                => 'Livello',
		'ordine'                 => 'Ordine nella pagina Corsi',
		'_corso_sottotitolo'     => 'Titolo display',
		'_corso_badge'           => 'Badge certificazione',
		'_corso_desc_breve'      => 'Descrizione breve',
		'_corso_competenze'      => 'Cosa imparerai',
		'fasi'                   => 'Fasi del programma',
		'_corso_stat_durata'     => 'Durata',
		'_corso_stat_pratica'    => 'Immersioni / pratica',
		'_corso_stat_profondita' => 'Profondità',
		'_corso_periodo'         => 'Periodo tipico',
		'_corso_requisiti'       => 'Requisiti di accesso',
		'eta_min'                => 'Età minima',
		'_corso_materiale'       => 'Materiale incluso',
		'_corso_contatto'        => 'Contatto sidebar',
		'_corso_contatto_nome'   => 'Nome contatto',
		'direttore'              => 'Direttore del corso',
		'docenti'                => 'Docenti del corso',
		'_corso_link_iscrizione' => 'Link iscrizione personalizzato',
		'_yoast_wpseo_title'     => 'Title SEO',
		'_yoast_wpseo_metadesc'  => 'Meta description',
		'_yoast_wpseo_focuskw'   => 'Parola chiave Yoast',
	];

	/**
	 * Selectable fields that at least one course of the file would write.
	 *
	 * @param array<int, array<string, mixed>> $courses
	 * @return string[]
	 */
	public function fields_in_file( array $courses ): array {
		$found = [];
		foreach ( $courses as $c ) {
			if ( ! empty( $c['skip'] ) ) continue;
			foreach ( array_keys( self::SELECTABLE_FIELDS ) as $f ) {
				if ( $this->writes_field( $c, $f ) ) $found[ $f ] = true;
			}
		}
		return array_values( array_filter( array_keys( self::SELECTABLE_FIELDS ), fn( $f ) => isset( $found[ $f ] ) ) );
	}

	private function writes_field( array $c, string $f ): bool {
		switch ( $f ) {
			case 'title':
				return ( $c['title'] ?? '' ) !== '';
			case 'slug':
				return ( $c['slug'] ?? '' ) !== '';
			case 'ordine':
				return true;
			case 'docenti':
				return ! empty( $c['docenti'] );
			case 'content': case 'categoria': case 'livello': case 'fasi': case 'eta_min': case 'direttore':
				return ( $c[ $f ] ?? null ) !== null;
			default:
				return array_key_exists( $f, $c['meta'] ?? [] ) || array_key_exists( $f, $c['yoast'] ?? [] );
		}
	}

	/**
	 * Drops from a mapped course every field not in $fields, so an update
	 * leaves those fields as they are on the site.
	 *
	 * @param string[] $fields
	 */
	public function restrict( array $course, array $fields ): array {
		$keep = array_flip( $fields );
		if ( ! isset( $keep['title'] ) )     $course['title']     = null;
		if ( ! isset( $keep['slug'] ) )      $course['slug']      = '';
		if ( ! isset( $keep['content'] ) )   $course['content']   = null;
		if ( ! isset( $keep['categoria'] ) ) $course['categoria'] = null;
		if ( ! isset( $keep['livello'] ) )   $course['livello']   = null;
		if ( ! isset( $keep['fasi'] ) )      $course['fasi']      = null;
		if ( ! isset( $keep['eta_min'] ) )   $course['eta_min']   = null;
		if ( ! isset( $keep['direttore'] ) ) $course['direttore'] = null;
		if ( ! isset( $keep['docenti'] ) )   $course['docenti']   = [];
		$course['set_order'] = isset( $keep['ordine'] );
		$course['meta']  = array_intersect_key( $course['meta'] ?? [], $keep );
		$course['yoast'] = array_intersect_key( $course['yoast'] ?? [], $keep );
		return $course;
	}

	/** Cell value that empties a course field on import (an empty cell leaves it unchanged). */
	const CLEAR_TOKEN = '(vuoto)';

	public function is_clear_token( string $value ): bool {
		return mb_strtolower( trim( $value ) ) === self::CLEAR_TOKEN;
	}

	/** True for empty cells and "[DA DEFINIRE]"-style placeholders. */
	public function is_placeholder( string $value ): bool {
		$v = trim( $value );
		return $v === '' || $v === '—' || $v === '-' || (bool) preg_match( '/^\[[^\]]*\]$/u', $v );
	}

	/** @param array<string, string> $row */
	private function phases( array $row ): ?array {
		$phases = [];
		$found  = false;
		for ( $n = 1; $n <= self::MAX_PHASES; $n++ ) {
			$titolo = $this->raw( $row, [ "fase $n titolo" ] );
			if ( $titolo !== null ) {
				$found = true;
			}
			$titolo = $this->clean( $titolo );
			if ( $titolo === null ) {
				continue;
			}
			$phases[] = [
				'titolo' => $titolo,
				'ore'    => $this->clean( $this->raw( $row, [ "fase $n ore" ] ) ) ?? '',
				'desc'   => $this->clean( $this->raw( $row, [ "fase $n descrizione" ] ) ) ?? '',
			];
		}
		/* null = the file has no phase columns: leave existing phases alone. */
		return $found ? $phases : null;
	}

	/** Lists text fields that still contain a "[...]" placeholder. */
	private function placeholder_warnings( array $course ): array {
		$fields = [ 'title' => $course['title'], 'content' => (string) $course['content'] ]
			+ $course['meta'] + $course['yoast'];
		$out = [];
		foreach ( $fields as $key => $value ) {
			if ( preg_match( '/\[(DA |da )?[A-ZÀ-Ü0-9][^\]]{0,40}\]/u', (string) $value ) ) {
				$out[] = $key;
			}
		}
		return $out;
	}

	/** @return string[]|null */
	private function split_list( ?string $value ): ?array {
		if ( $value === null ) {
			return null;
		}
		$items = preg_split( '/[\n,;]+/', $value );
		$items = array_values( array_filter( array_map( 'trim', $items ), fn( $s ) => $s !== '' ) );
		return $items ?: null;
	}

	/**
	 * Cleaned value of the first matching column, or null when missing,
	 * empty or a placeholder.
	 *
	 * @param array<string, string> $row
	 * @param string[] $aliases
	 */
	private function value( array $row, array $aliases ): ?string {
		return $this->clean( $this->raw( $row, $aliases ) );
	}

	private function clean( ?string $value ): ?string {
		if ( $value === null || $this->is_placeholder( $value ) ) {
			return null;
		}
		return trim( $value );
	}

	/** @param array<string, string> $row */
	private function raw( array $row, array $aliases ): ?string {
		$key = $this->find_key( $row, $aliases );
		return $key === null ? null : (string) $row[ $key ];
	}

	/** @param array<string, string> $row */
	private function find_key( array $row, array $aliases ): ?string {
		foreach ( array_keys( $row ) as $header ) {
			$norm = mb_strtolower( trim( (string) $header ) );
			if ( in_array( $norm, $aliases, true ) ) {
				return (string) $header;
			}
		}
		return null;
	}
}
