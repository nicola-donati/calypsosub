<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_Gallery_Helpers extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	private function dummy_cells( int $n ): array {
		$cells = [];
		for ( $i = 0; $i < $n; $i++ ) {
			$cells[] = [ 'id' => $i, 'ratio' => 1.0 ];
		}
		return $cells;
	}

	public function test_shape_for_ratio_horizontal(): void {
		$this->assertSame( 'horizontal', Calypsosub_Gallery_Helpers::shape_for_ratio( 1.5 ) );
		$this->assertSame( 'horizontal', Calypsosub_Gallery_Helpers::shape_for_ratio( 1.35 ) );
	}

	public function test_shape_for_ratio_vertical(): void {
		$this->assertSame( 'vertical', Calypsosub_Gallery_Helpers::shape_for_ratio( 0.66 ) );
		$this->assertSame( 'vertical', Calypsosub_Gallery_Helpers::shape_for_ratio( 0.74 ) );
	}

	public function test_shape_for_ratio_square(): void {
		$this->assertSame( 'square', Calypsosub_Gallery_Helpers::shape_for_ratio( 1.0 ) );
		$this->assertSame( 'square', Calypsosub_Gallery_Helpers::shape_for_ratio( 0.8 ) );
		$this->assertSame( 'square', Calypsosub_Gallery_Helpers::shape_for_ratio( 1.2 ) );
	}

	public function test_cell_span_normal_sizes(): void {
		$this->assertSame( [ 'col' => 1, 'row' => 1 ], Calypsosub_Gallery_Helpers::cell_span( 'square', false ) );
		$this->assertSame( [ 'col' => 2, 'row' => 1 ], Calypsosub_Gallery_Helpers::cell_span( 'horizontal', false ) );
		$this->assertSame( [ 'col' => 1, 'row' => 2 ], Calypsosub_Gallery_Helpers::cell_span( 'vertical', false ) );
	}

	public function test_cell_span_big_sizes_are_double_in_both_dimensions(): void {
		$this->assertSame( [ 'col' => 2, 'row' => 2 ], Calypsosub_Gallery_Helpers::cell_span( 'square', true ) );
		$this->assertSame( [ 'col' => 4, 'row' => 2 ], Calypsosub_Gallery_Helpers::cell_span( 'horizontal', true ) );
		$this->assertSame( [ 'col' => 2, 'row' => 4 ], Calypsosub_Gallery_Helpers::cell_span( 'vertical', true ) );
	}

	public function test_build_units_preserves_count_and_matches_shape(): void {
		$cells = [
			[ 'id' => 1, 'ratio' => 1.5 ],  // horizontal
			[ 'id' => 2, 'ratio' => 0.66 ], // vertical
			[ 'id' => 3, 'ratio' => 1.0 ],  // square
		];

		$units = Calypsosub_Gallery_Helpers::build_units( $cells, function () { return 100; } ); // never "big"

		$this->assertCount( 3, $units );
		$this->assertSame( [ 'col' => 2, 'row' => 1 ], [ 'col' => $units[0]['col'], 'row' => $units[0]['row'] ] );
		$this->assertSame( [ 'col' => 1, 'row' => 2 ], [ 'col' => $units[1]['col'], 'row' => $units[1]['row'] ] );
		$this->assertSame( [ 'col' => 1, 'row' => 1 ], [ 'col' => $units[2]['col'], 'row' => $units[2]['row'] ] );
	}

	public function test_build_units_uses_big_size_when_rng_hits(): void {
		$units = Calypsosub_Gallery_Helpers::build_units(
			[ [ 'id' => 1, 'ratio' => 1.0 ] ],
			function () { return 1; } // always "big" (<=35)
		);

		$this->assertSame( 2, $units[0]['col'] );
		$this->assertSame( 2, $units[0]['row'] );
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

	public function test_resolve_overlay_text_prefers_meta(): void {
		Functions\when( '_x' )->returnArg( 1 );
		Functions\expect( 'get_post_meta' )
			->once()
			->with( 42, '_calypso_overlay_text', true )
			->andReturn( 'Tartaruga · Caretta caretta' );

		$result = Calypsosub_Gallery_Helpers::resolve_overlay_text( 42 );

		$this->assertSame( 'Tartaruga · Caretta caretta', $result );
	}

	public function test_resolve_overlay_text_falls_back_to_caption(): void {
		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\expect( 'get_the_excerpt' )
			->once()
			->with( 42 )
			->andReturn( 'corallo rosso · 38m' );

		$result = Calypsosub_Gallery_Helpers::resolve_overlay_text( 42 );

		$this->assertSame( 'corallo rosso · 38m', $result );
	}

	public function test_resolve_overlay_text_falls_back_to_alt(): void {
		Functions\when( 'get_the_excerpt' )->justReturn( '' );
		Functions\when( 'get_post_meta' )
			->alias( function ( $id, $key, $single ) {
				return $key === '_wp_attachment_image_alt' ? 'murena' : '';
			} );

		$result = Calypsosub_Gallery_Helpers::resolve_overlay_text( 42 );

		$this->assertSame( 'murena', $result );
	}

	public function test_resolve_overlay_text_empty_when_all_empty(): void {
		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\when( 'get_the_excerpt' )->justReturn( '' );

		$result = Calypsosub_Gallery_Helpers::resolve_overlay_text( 42 );

		$this->assertSame( '', $result );
	}
}
