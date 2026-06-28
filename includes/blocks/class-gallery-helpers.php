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

	public static function resolve_overlay_text( int $attachment_id ): string {
		$caption = wp_get_attachment_caption( $attachment_id );
		return $caption !== '' ? $caption : (string) get_the_title( $attachment_id );
	}

	public static function build_units( array $cells ): array {
		$units = [];
		foreach ( $cells as $i => $cell ) {
			$units[] = [ 'cell' => $cell ] + self::cell_style( $i );
		}
		return $units;
	}

	public static function build_query_args( array $attrs ): array {
		$mode      = (string) ( $attrs['source_mode'] ?? 'all' );
		$max_items = (int) ( $attrs['max_items'] ?? 12 );

		$args = [
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'numberposts'    => $max_items > 0 ? $max_items : -1,
		];

		if ( $mode === 'manual' ) {
			$args['post__in'] = array_map( 'intval', $attrs['manual_ids'] ?? [] );
			$args['orderby']  = 'post__in';
			return $args;
		}

		$args['orderby'] = 'date';
		$args['order']   = 'DESC';

		if ( $mode === 'tag' ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'calypso_media_tag',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $attrs['tag_ids'] ?? [] ),
				],
			];
			return $args;
		}

		$args['tax_query'] = [
			[
				'taxonomy' => 'calypso_media_tag',
				'operator' => 'EXISTS',
			],
		];
		return $args;
	}
}
