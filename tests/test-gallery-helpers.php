<?php
use PHPUnit\Framework\TestCase;

class Test_Gallery_Helpers extends TestCase {

	public function test_cell_style_desktop_pattern(): void {
		$expected = [
			[ 'col' => 3, 'row' => 2 ],
			[ 'col' => 2, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 2, 'row' => 1 ],
			[ 'col' => 2, 'row' => 2 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
		];
		foreach ( $expected as $index => $style ) {
			$this->assertSame( $style, Calypsosub_Gallery_Helpers::cell_style( $index ) );
		}
	}

	public function test_cell_style_desktop_wraps_after_ten(): void {
		$this->assertSame(
			Calypsosub_Gallery_Helpers::cell_style( 0 ),
			Calypsosub_Gallery_Helpers::cell_style( 10 )
		);
		$this->assertSame(
			Calypsosub_Gallery_Helpers::cell_style( 3 ),
			Calypsosub_Gallery_Helpers::cell_style( 13 )
		);
	}

	public function test_cell_style_mobile_pattern(): void {
		$expected = [
			[ 'col' => 2, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
			[ 'col' => 1, 'row' => 1 ],
		];
		foreach ( $expected as $index => $style ) {
			$this->assertSame( $style, Calypsosub_Gallery_Helpers::cell_style( $index, true ) );
		}
	}

	public function test_cell_style_mobile_wraps_after_eight(): void {
		$this->assertSame(
			Calypsosub_Gallery_Helpers::cell_style( 0, true ),
			Calypsosub_Gallery_Helpers::cell_style( 8, true )
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
