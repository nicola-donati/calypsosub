<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Gallery_Helpers {

	private const DESKTOP_PATTERN = [
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

	private const MOBILE_PATTERN = [
		[ 'col' => 2, 'row' => 1 ],
		[ 'col' => 1, 'row' => 1 ],
		[ 'col' => 1, 'row' => 1 ],
		[ 'col' => 1, 'row' => 1 ],
		[ 'col' => 1, 'row' => 1 ],
		[ 'col' => 1, 'row' => 1 ],
		[ 'col' => 1, 'row' => 1 ],
		[ 'col' => 1, 'row' => 1 ],
	];

	public static function cell_style( int $index, bool $mobile = false ): array {
		$pattern = $mobile ? self::MOBILE_PATTERN : self::DESKTOP_PATTERN;
		return $pattern[ $index % count( $pattern ) ];
	}
}
