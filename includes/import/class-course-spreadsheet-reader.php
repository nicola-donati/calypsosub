<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reads a course spreadsheet (.xlsx or .csv) into a list of associative rows
 * keyed by the header row. No external libraries: .xlsx files are read with
 * ZipArchive + SimpleXML, .csv files with fgetcsv (comma or semicolon).
 */
class Calypsosub_Course_Spreadsheet_Reader {

	/** Preferred worksheet name in .xlsx files. */
	const PREFERRED_SHEET = 'Corsi';

	/**
	 * @param string $path      Absolute path of the file on disk.
	 * @param string $extension "xlsx" or "csv" (the uploaded name's extension).
	 * @return array<int, array<string, string>> Rows keyed by header, in file order.
	 * @throws RuntimeException When the file cannot be read.
	 */
	public function read( string $path, string $extension ): array {
		$extension = strtolower( $extension );
		if ( $extension === 'xlsx' ) {
			$matrix = $this->read_xlsx_matrix( $path );
		} elseif ( $extension === 'csv' ) {
			$matrix = $this->read_csv_matrix( $path );
		} else {
			throw new RuntimeException( 'Formato non supportato: usa un file .xlsx o .csv.' );
		}
		return $this->matrix_to_rows( $matrix );
	}

	/**
	 * Turns a matrix (first row = headers) into associative rows.
	 * Empty headers are ignored; fully empty rows are dropped.
	 *
	 * @param array<int, array<int, string>> $matrix
	 * @return array<int, array<string, string>>
	 */
	public function matrix_to_rows( array $matrix ): array {
		if ( empty( $matrix ) ) {
			return [];
		}
		$headers = array_map( fn( $h ) => trim( (string) $h ), array_shift( $matrix ) );
		$rows    = [];
		foreach ( $matrix as $line ) {
			$row      = [];
			$has_data = false;
			foreach ( $headers as $i => $header ) {
				if ( $header === '' ) {
					continue;
				}
				$value          = isset( $line[ $i ] ) ? trim( str_replace( "\r\n", "\n", (string) $line[ $i ] ) ) : '';
				$row[ $header ] = $value;
				if ( $value !== '' ) {
					$has_data = true;
				}
			}
			if ( $has_data ) {
				$rows[] = $row;
			}
		}
		return $rows;
	}

	/** @return array<int, array<int, string>> */
	private function read_csv_matrix( string $path ): array {
		$raw = file_get_contents( $path );
		if ( $raw === false ) {
			throw new RuntimeException( 'Impossibile leggere il file CSV.' );
		}
		$raw = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );
		if ( ! mb_check_encoding( $raw, 'UTF-8' ) ) {
			$raw = mb_convert_encoding( $raw, 'UTF-8', 'Windows-1252' );
		}
		$first_line = strtok( $raw, "\n" );
		$delimiter  = substr_count( (string) $first_line, ';' ) > substr_count( (string) $first_line, ',' ) ? ';' : ',';

		$handle = fopen( 'php://temp', 'r+' );
		fwrite( $handle, $raw );
		rewind( $handle );
		$matrix = [];
		while ( ( $line = fgetcsv( $handle, 0, $delimiter, '"', '' ) ) !== false ) {
			if ( $line === [ null ] ) {
				continue;
			}
			$matrix[] = array_map( fn( $v ) => (string) $v, $line );
		}
		fclose( $handle );
		return $matrix;
	}

	/** @return array<int, array<int, string>> */
	private function read_xlsx_matrix( string $path ): array {
		if ( ! class_exists( 'ZipArchive' ) ) {
			throw new RuntimeException( 'Il server non ha l\'estensione PHP zip: salva il file come CSV e riprova.' );
		}
		$zip = new ZipArchive();
		if ( $zip->open( $path ) !== true ) {
			throw new RuntimeException( 'Il file non è un Excel .xlsx valido.' );
		}
		try {
			$shared     = $this->read_shared_strings( $zip );
			$sheet_path = $this->find_sheet_path( $zip );
			$sheet_xml  = $zip->getFromName( $sheet_path );
			if ( $sheet_xml === false ) {
				throw new RuntimeException( 'Foglio non trovato nel file Excel.' );
			}
			return $this->parse_sheet( $sheet_xml, $shared );
		} finally {
			$zip->close();
		}
	}

	/** @return array<int, string> */
	private function read_shared_strings( ZipArchive $zip ): array {
		$xml = $zip->getFromName( 'xl/sharedStrings.xml' );
		if ( $xml === false ) {
			return [];
		}
		$doc = $this->load_xml( $xml );
		$out = [];
		foreach ( $doc->si as $si ) {
			$out[] = $this->string_item_text( $si );
		}
		return $out;
	}

	/** Text of an <si> or <is> node: plain <t> or rich-text runs <r><t>. */
	private function string_item_text( SimpleXMLElement $node ): string {
		$text     = '';
		$children = $node->children();
		if ( $children === null ) {
			return '';
		}
		foreach ( $children as $child ) {
			$name = $child->getName();
			if ( $name === 't' ) {
				$text .= (string) $child;
			} elseif ( $name === 'r' ) {
				foreach ( $child->children() as $part ) {
					if ( $part->getName() === 't' ) {
						$text .= (string) $part;
					}
				}
			}
		}
		return $text;
	}

	/** Finds the worksheet named "Corsi", or the first worksheet. */
	private function find_sheet_path( ZipArchive $zip ): string {
		$workbook = $zip->getFromName( 'xl/workbook.xml' );
		$rels     = $zip->getFromName( 'xl/_rels/workbook.xml.rels' );
		if ( $workbook === false || $rels === false ) {
			return 'xl/worksheets/sheet1.xml';
		}
		$wb_doc  = $this->load_xml( $workbook );
		$rel_doc = $this->load_xml( $rels );

		$targets = [];
		foreach ( $rel_doc->Relationship as $rel ) {
			$targets[ (string) $rel['Id'] ] = (string) $rel['Target'];
		}

		$chosen = null;
		$first  = null;
		foreach ( $wb_doc->sheets->sheet as $sheet ) {
			$rid = (string) $sheet->attributes( 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' )['id'];
			if ( $first === null ) {
				$first = $rid;
			}
			if ( trim( (string) $sheet['name'] ) === self::PREFERRED_SHEET ) {
				$chosen = $rid;
				break;
			}
		}
		$target = $targets[ $chosen ?? $first ] ?? 'worksheets/sheet1.xml';
		$target = ltrim( $target, '/' );
		return str_starts_with( $target, 'xl/' ) ? $target : 'xl/' . $target;
	}

	/**
	 * @param array<int, string> $shared
	 * @return array<int, array<int, string>>
	 */
	private function parse_sheet( string $xml, array $shared ): array {
		$doc    = $this->load_xml( $xml );
		$matrix = [];
		foreach ( $doc->sheetData->row as $row ) {
			$line = [];
			foreach ( $row->c as $cell ) {
				$col          = $this->column_index( (string) $cell['r'] );
				$line[ $col ] = $this->cell_value( $cell, $shared );
			}
			if ( $line ) {
				$max  = max( array_keys( $line ) );
				$full = array_fill( 0, $max + 1, '' );
				foreach ( $line as $i => $v ) {
					$full[ $i ] = $v;
				}
				$matrix[ (int) $row['r'] - 1 ] = $full;
			}
		}
		if ( ! $matrix ) {
			return [];
		}
		ksort( $matrix );
		/* Keep row positions: missing rows become empty rows (dropped later). */
		$out = [];
		for ( $i = 0, $last = max( array_keys( $matrix ) ); $i <= $last; $i++ ) {
			$out[] = $matrix[ $i ] ?? [];
		}
		return $out;
	}

	/** @param array<int, string> $shared */
	private function cell_value( SimpleXMLElement $cell, array $shared ): string {
		$type = (string) $cell['t'];
		if ( $type === 's' ) {
			return $shared[ (int) $cell->v ] ?? '';
		}
		if ( $type === 'inlineStr' ) {
			return isset( $cell->is ) ? $this->string_item_text( $cell->is ) : '';
		}
		if ( $type === 'b' ) {
			return ( (string) $cell->v ) === '1' ? 'TRUE' : 'FALSE';
		}
		$value = (string) $cell->v;
		/* Integers stored as floats ("10.0") come back as "10". */
		if ( is_numeric( $value ) && (float) $value == (int) $value && ! str_contains( $value, 'E' ) ) {
			return (string) (int) $value;
		}
		return $value;
	}

	/** "AB12" → 27 (0-based column index). */
	public function column_index( string $ref ): int {
		$letters = preg_replace( '/[^A-Z]/', '', strtoupper( $ref ) );
		$index   = 0;
		foreach ( str_split( $letters ) as $ch ) {
			$index = $index * 26 + ( ord( $ch ) - 64 );
		}
		return max( 0, $index - 1 );
	}

	private function load_xml( string $xml ): SimpleXMLElement {
		$prev = libxml_use_internal_errors( true );
		$doc  = simplexml_load_string( $xml, 'SimpleXMLElement', LIBXML_NONET );
		libxml_use_internal_errors( $prev );
		if ( $doc === false ) {
			throw new RuntimeException( 'Il file Excel è danneggiato o non leggibile.' );
		}
		return $doc;
	}
}
