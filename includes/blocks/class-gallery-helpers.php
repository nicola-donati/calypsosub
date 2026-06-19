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

	public static function resolve_overlay_text( int $attachment_id ): string {
		$meta = get_post_meta( $attachment_id, '_calypso_overlay_text', true );
		if ( is_string( $meta ) && trim( $meta ) !== '' ) {
			return $meta;
		}

		$caption = get_the_excerpt( $attachment_id );
		if ( is_string( $caption ) && trim( $caption ) !== '' ) {
			return $caption;
		}

		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		return is_string( $alt ) ? $alt : '';
	}
}
