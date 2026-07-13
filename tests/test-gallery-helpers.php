<?php
use PHPUnit\Framework\TestCase;

class Test_Gallery_Helpers extends TestCase {

	public function test_classify_shape_horizontal(): void {
		$this->assertSame( 'horizontal', Calypsosub_Gallery_Helpers::classify_shape( 1.6 ) );
		$this->assertSame( 'horizontal', Calypsosub_Gallery_Helpers::classify_shape( 2.5 ) );
	}

	public function test_classify_shape_vertical(): void {
		$this->assertSame( 'vertical', Calypsosub_Gallery_Helpers::classify_shape( 0.63 ) );
		$this->assertSame( 'vertical', Calypsosub_Gallery_Helpers::classify_shape( 0.4 ) );
	}

	public function test_classify_shape_square(): void {
		$this->assertSame( 'square', Calypsosub_Gallery_Helpers::classify_shape( 1.0 ) );
		$this->assertSame( 'square', Calypsosub_Gallery_Helpers::classify_shape( 0.64 ) );
		$this->assertSame( 'square', Calypsosub_Gallery_Helpers::classify_shape( 1.59 ) );
	}

	public function test_cell_style_horizontal_regular(): void {
		$this->assertSame( [ 'col' => 2, 'row' => 1 ], Calypsosub_Gallery_Helpers::cell_style( 2.0, false ) );
	}

	public function test_cell_style_horizontal_big(): void {
		$this->assertSame( [ 'col' => 3, 'row' => 2 ], Calypsosub_Gallery_Helpers::cell_style( 2.0, true ) );
	}

	public function test_cell_style_vertical_regular(): void {
		$this->assertSame( [ 'col' => 1, 'row' => 2 ], Calypsosub_Gallery_Helpers::cell_style( 0.5, false ) );
	}

	public function test_cell_style_vertical_big(): void {
		$this->assertSame( [ 'col' => 2, 'row' => 3 ], Calypsosub_Gallery_Helpers::cell_style( 0.5, true ) );
	}

	public function test_cell_style_square_regular(): void {
		$this->assertSame( [ 'col' => 1, 'row' => 1 ], Calypsosub_Gallery_Helpers::cell_style( 1.0, false ) );
	}

	public function test_cell_style_square_big(): void {
		$this->assertSame( [ 'col' => 2, 'row' => 2 ], Calypsosub_Gallery_Helpers::cell_style( 1.0, true ) );
	}

	public function test_build_units_maps_cells_by_ratio_using_injected_big_flag(): void {
		$cells = [
			[ 'ratio' => 2.0 ],
			[ 'ratio' => 0.5 ],
			[ 'ratio' => 1.0 ],
		];
		// forza tutte le celle a "regular" per un confronto deterministico.
		$units = Calypsosub_Gallery_Helpers::build_units( $cells, static fn() => false );

		$this->assertSame( $cells[0], $units[0]['cell'] );
		$this->assertSame( [ 'col' => 2, 'row' => 1 ], [ 'col' => $units[0]['col'], 'row' => $units[0]['row'] ] );
		$this->assertSame( [ 'col' => 1, 'row' => 2 ], [ 'col' => $units[1]['col'], 'row' => $units[1]['row'] ] );
		$this->assertSame( [ 'col' => 1, 'row' => 1 ], [ 'col' => $units[2]['col'], 'row' => $units[2]['row'] ] );
	}

	public function test_build_units_uses_injected_big_flag_per_cell(): void {
		$cells = [ [ 'ratio' => 1.0 ], [ 'ratio' => 1.0 ] ];
		$units = Calypsosub_Gallery_Helpers::build_units( $cells, static fn() => true );

		$this->assertSame( [ 'col' => 2, 'row' => 2 ], [ 'col' => $units[0]['col'], 'row' => $units[0]['row'] ] );
		$this->assertSame( [ 'col' => 2, 'row' => 2 ], [ 'col' => $units[1]['col'], 'row' => $units[1]['row'] ] );
	}

	public function test_build_units_default_randomizer_stays_within_shape_variants(): void {
		$units = Calypsosub_Gallery_Helpers::build_units( [ [ 'ratio' => 1.0 ] ] );
		$this->assertContains(
			[ 'col' => $units[0]['col'], 'row' => $units[0]['row'] ],
			[ [ 'col' => 1, 'row' => 1 ], [ 'col' => 2, 'row' => 2 ] ]
		);
	}

	public function test_build_query_args_manual_mode(): void {
		$args = Calypsosub_Gallery_Helpers::build_query_args( [
			'source_mode' => 'manual',
			'manual_ids'  => [ 5, 3, 9 ],
			'max_items'   => 0,
		] );

		$this->assertSame( 'attachment', $args['post_type'] );
		$this->assertSame( 'image', $args['post_mime_type'] );
		$this->assertSame( 'inherit', $args['post_status'] );
		$this->assertSame( [ 5, 3, 9 ], $args['post__in'] );
		$this->assertSame( 'post__in', $args['orderby'] );
		$this->assertSame( -1, $args['numberposts'] );
		$this->assertArrayNotHasKey( 'tax_query', $args );
	}

	public function test_build_query_args_manual_mode_casts_ids_to_int(): void {
		$args = Calypsosub_Gallery_Helpers::build_query_args( [
			'source_mode' => 'manual',
			'manual_ids'  => [ '5', '3' ],
			'max_items'   => 0,
		] );

		$this->assertSame( [ 5, 3 ], $args['post__in'] );
	}

	public function test_build_query_args_tag_mode(): void {
		$args = Calypsosub_Gallery_Helpers::build_query_args( [
			'source_mode' => 'tag',
			'tag_ids'     => [ 12, 34 ],
			'max_items'   => 6,
		] );

		$this->assertSame( 6, $args['numberposts'] );
		$this->assertSame( 'date', $args['orderby'] );
		$this->assertSame( 'DESC', $args['order'] );
		$this->assertSame( [
			[
				'taxonomy' => 'calypso_media_tag',
				'field'    => 'term_id',
				'terms'    => [ 12, 34 ],
			],
		], $args['tax_query'] );
	}

	public function test_build_query_args_all_mode_requires_any_tag(): void {
		$args = Calypsosub_Gallery_Helpers::build_query_args( [
			'source_mode' => 'all',
			'max_items'   => 12,
		] );

		$this->assertSame( 12, $args['numberposts'] );
		$this->assertSame( [
			[
				'taxonomy' => 'calypso_media_tag',
				'operator' => 'EXISTS',
			],
		], $args['tax_query'] );
	}

	public function test_build_query_args_defaults_to_all_mode(): void {
		$args = Calypsosub_Gallery_Helpers::build_query_args( [] );

		$this->assertSame( 12, $args['numberposts'] );
		$this->assertSame( [
			[
				'taxonomy' => 'calypso_media_tag',
				'operator' => 'EXISTS',
			],
		], $args['tax_query'] );
	}
}
