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
}
