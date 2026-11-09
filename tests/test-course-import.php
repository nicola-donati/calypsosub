<?php
use PHPUnit\Framework\TestCase;

require_once CALYPSOSUB_PATH . 'includes/import/class-course-spreadsheet-reader.php';
require_once CALYPSOSUB_PATH . 'includes/import/class-course-import-mapper.php';

class Test_Course_Import extends TestCase {

	private function fixture( string $name ): string {
		return __DIR__ . '/fixtures/' . $name;
	}

	private function xlsx_rows(): array {
		return ( new Calypsosub_Course_Spreadsheet_Reader() )->read( $this->fixture( 'corsi-esempio.xlsx' ), 'xlsx' );
	}

	/* ───── Reader ───── */

	public function test_xlsx_reads_the_corsi_sheet_not_the_first_one(): void {
		$rows = $this->xlsx_rows();
		$this->assertCount( 4, $rows );
		$this->assertSame( 'post_title', $rows[0]['Corso (titolo WordPress)'] );
		$this->assertSame( 'Corso Esempio', $rows[1]['Corso (titolo WordPress)'] );
	}

	public function test_xlsx_keeps_line_breaks_and_integers(): void {
		$row = $this->xlsx_rows()[1];
		$this->assertSame( "Prima cosa\nSeconda cosa", $row['Cosa imparerai'] );
		$this->assertSame( '10', $row['Fase 1 ore'] );
		$this->assertSame( '', $row['Car. title'], 'Formula without cached value reads as empty' );
	}

	public function test_csv_with_bom_semicolons_and_multiline_cells(): void {
		$rows = ( new Calypsosub_Course_Spreadsheet_Reader() )->read( $this->fixture( 'corsi-esempio.csv' ), 'csv' );
		$this->assertCount( 1, $rows, 'Empty rows are dropped' );
		$this->assertSame( 'Prova', $rows[0]['Linea'], 'BOM removed from first header' );
		$this->assertSame( "Riga uno\nRiga due", $rows[0]['Cosa imparerai'] );
	}

	public function test_unsupported_extension_throws(): void {
		$this->expectException( RuntimeException::class );
		( new Calypsosub_Course_Spreadsheet_Reader() )->read( $this->fixture( 'corsi-esempio.csv' ), 'ods' );
	}

	public function test_column_index(): void {
		$r = new Calypsosub_Course_Spreadsheet_Reader();
		$this->assertSame( 0, $r->column_index( 'A1' ) );
		$this->assertSame( 25, $r->column_index( 'Z9' ) );
		$this->assertSame( 27, $r->column_index( 'AB12' ) );
	}

	/* ───── Mapper ───── */

	public function test_mapper_skips_technical_row_and_unapproved_rows(): void {
		$result  = ( new Calypsosub_Course_Import_Mapper() )->map_rows( $this->xlsx_rows(), true );
		$courses = $result['courses'];
		$this->assertTrue( $result['has_approval_column'] );
		$this->assertCount( 3, $courses, 'Technical row removed' );
		$this->assertNull( $courses[0]['skip'] );
		$this->assertSame( 'Non approvato', $courses[1]['skip'] );
		$this->assertNull( $courses[2]['skip'], '"si" counts as approved' );
		$this->assertSame( [ 1, 2, 3 ], array_column( $courses, 'position' ) );
	}

	public function test_mapper_imports_everything_when_approval_filter_is_off(): void {
		$courses = ( new Calypsosub_Course_Import_Mapper() )->map_rows( $this->xlsx_rows(), false )['courses'];
		$this->assertNull( $courses[1]['skip'] );
	}

	public function test_mapper_maps_fields_and_drops_placeholders(): void {
		$c = ( new Calypsosub_Course_Import_Mapper() )->map_rows( $this->xlsx_rows() )['courses'][0];

		$this->assertSame( 'Corso Esempio', $c['title'] );
		$this->assertSame( 'corso-esempio', $c['slug'] );
		$this->assertSame( 'Prova', $c['categoria'] );
		$this->assertSame( 'Base', $c['livello'] );
		$this->assertSame( "Primo paragrafo.\n\nSecondo paragrafo.", $c['content'] );
		$this->assertSame( 'Uno, due, tre.', $c['meta']['_corso_sottotitolo'] );
		$this->assertSame( '20 m', $c['meta']['_corso_stat_profondita'] );
		$this->assertArrayNotHasKey( '_corso_periodo', $c['meta'], '[DA DEFINIRE] is not imported' );
		$this->assertArrayNotHasKey( '_corso_contatto', $c['meta'] );
		$this->assertArrayNotHasKey( '_corso_materiale', $c['meta'], 'Empty cells are not imported' );
		$this->assertSame( 'Corso Esempio | Test', $c['yoast']['_yoast_wpseo_title'] );
		$this->assertSame( 'corso esempio', $c['yoast']['_yoast_wpseo_focuskw'] );
		$this->assertSame( [ 'Mario Rossi', 'Anna Bianchi' ], $c['docenti'] );
		$this->assertSame( 14, $c['eta_min'], 'Age parsed from requisiti' );
		$this->assertSame( [], $c['warnings'] );
	}

	public function test_mapper_builds_phases(): void {
		$c = ( new Calypsosub_Course_Import_Mapper() )->map_rows( $this->xlsx_rows() )['courses'][0];
		$this->assertSame( [
			[ 'titolo' => 'AULA', 'ore' => '10', 'desc' => 'Teoria generale.' ],
			[ 'titolo' => 'MARE', 'ore' => '2 immersioni', 'desc' => '2 immersioni con gli istruttori.' ],
		], $c['fasi'] );
	}

	public function test_phases_are_null_when_file_has_no_phase_columns(): void {
		$c = ( new Calypsosub_Course_Import_Mapper() )->map_row( [ 'Corso' => 'X' ] );
		$this->assertNull( $c['fasi'], 'Existing phases must not be wiped' );
	}

	public function test_mapper_warns_about_bracket_placeholders_inside_text(): void {
		$c = ( new Calypsosub_Course_Import_Mapper() )->map_rows( $this->xlsx_rows() )['courses'][2];
		$this->assertContains( '_corso_sottotitolo', $c['warnings'] );
	}

	public function test_clear_token_empties_meta_fields(): void {
		$rows = [ [ 'Corso (titolo WordPress)' => 'Corso X', 'Materiale incluso' => ' (Vuoto) ', 'Titolo display' => 'Ciao.', 'Durata' => '' ] ];
		$c    = ( new Calypsosub_Course_Import_Mapper() )->map_rows( $rows, false )['courses'][0];
		$this->assertSame( '', $c['meta']['_corso_materiale'], '"(vuoto)" clears the field' );
		$this->assertSame( 'Ciao.', $c['meta']['_corso_sottotitolo'] );
		$this->assertArrayNotHasKey( '_corso_stat_durata', $c['meta'], 'An empty cell still leaves the field unchanged' );
	}

	public function test_restrict_keeps_only_selected_fields(): void {
		$m = new Calypsosub_Course_Import_Mapper();
		$c = $m->map_rows( $this->xlsx_rows() )['courses'][0];
		$r = $m->restrict( $c, [ '_corso_badge', '_yoast_wpseo_title' ] );
		$this->assertNull( $r['title'], 'Title left unchanged' );
		$this->assertSame( '', $r['slug'] );
		$this->assertNull( $r['content'] );
		$this->assertNull( $r['fasi'] );
		$this->assertFalse( $r['set_order'] );
		$this->assertSame( [ '_yoast_wpseo_title' ], array_keys( $r['yoast'] ) );
		$this->assertArrayNotHasKey( '_corso_sottotitolo', $r['meta'] );
		$this->assertSame( $c['title'], $m->restrict( $c, [ 'title' ] )['title'] );
		$this->assertTrue( $m->restrict( $c, [ 'ordine' ] )['set_order'] );
	}

	public function test_fields_in_file_lists_only_columns_with_values(): void {
		$rows   = [ [ 'Corso (titolo WordPress)' => 'Corso X', 'Badge certificazione' => 'FIAS', 'Durata' => '', 'Materiale incluso' => '(vuoto)' ] ];
		$m      = new Calypsosub_Course_Import_Mapper();
		$fields = $m->fields_in_file( $m->map_rows( $rows, false )['courses'] );
		$this->assertContains( '_corso_badge', $fields );
		$this->assertContains( '_corso_materiale', $fields, '(vuoto) counts: it writes an empty value' );
		$this->assertContains( 'title', $fields );
		$this->assertNotContains( '_corso_stat_durata', $fields );
		$this->assertNotContains( 'content', $fields );
	}

	public function test_explicit_age_column_wins(): void {
		$rows = ( new Calypsosub_Course_Spreadsheet_Reader() )->read( $this->fixture( 'corsi-esempio.csv' ), 'csv' );
		$c    = ( new Calypsosub_Course_Import_Mapper() )->map_rows( $rows )['courses'][0];
		$this->assertSame( 12, $c['eta_min'] );
		$this->assertSame( 'Prova', $c['categoria'], '"Linea" column maps to categoria' );
	}

	public function test_missing_title_is_skipped(): void {
		$courses = ( new Calypsosub_Course_Import_Mapper() )->map_rows( [ [ 'Corso (titolo WordPress)' => '', 'Livello' => 'Base' ] ] )['courses'];
		$this->assertSame( 'Titolo del corso mancante', $courses[0]['skip'] );
	}

	/**
	 * @dataProvider yes_values
	 */
	public function test_is_yes( string $value, bool $expected ): void {
		$this->assertSame( $expected, ( new Calypsosub_Course_Import_Mapper() )->is_yes( $value ) );
	}

	public function yes_values(): array {
		return [ [ 'Sì', true ], [ 'si', true ], [ 'SI', true ], [ 'x', true ], [ 'No', false ], [ 'Da rivedere', false ], [ '', false ] ];
	}

	/**
	 * @dataProvider placeholder_values
	 */
	public function test_is_placeholder( string $value, bool $expected ): void {
		$this->assertSame( $expected, ( new Calypsosub_Course_Import_Mapper() )->is_placeholder( $value ) );
	}

	public function placeholder_values(): array {
		return [ [ '[DA DEFINIRE]', true ], [ '  ', true ], [ '—', true ], [ '[9] anni', false ], [ 'Testo', false ] ];
	}
}
