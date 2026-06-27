<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Gallery_Helpers {

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

	/**
	 * Determina la "forma" di un'immagine in base al suo aspect ratio reale,
	 * scegliendo il bucket più vicino fra orizzontale (2:1), verticale (1:2)
	 * o quadrata — così la cella assegnata avrà proporzioni simili
	 * all'immagine invece di forzarla in uno spazio sbagliato.
	 */
	public static function shape_for_ratio( float $ratio ): string {
		if ( $ratio >= 1.35 ) {
			return 'horizontal';
		}
		if ( $ratio <= 0.74 ) {
			return 'vertical';
		}
		return 'square';
	}

	/**
	 * Span della cella in unità di modulo "a" (= row_height), per forma e
	 * taglia. Le celle "big" sono il doppio in ogni dimensione rispetto alle
	 * normali, mantenendo le stesse proporzioni forma/orientamento.
	 */
	public static function cell_span( string $shape, bool $big ): array {
		switch ( $shape ) {
			case 'horizontal':
				return $big ? [ 'col' => 4, 'row' => 2 ] : [ 'col' => 2, 'row' => 1 ];
			case 'vertical':
				return $big ? [ 'col' => 2, 'row' => 4 ] : [ 'col' => 1, 'row' => 2 ];
			default:
				return $big ? [ 'col' => 2, 'row' => 2 ] : [ 'col' => 1, 'row' => 1 ];
		}
	}

	/**
	 * Assegna a ogni cella la propria forma e taglia (big ogni tanto, per
	 * varietà visiva). $rng riceve (min,max) e ritorna un intero;
	 * iniettabile nei test.
	 */
	public static function build_units( array $cells, ?callable $rng = null ): array {
		$rng = $rng ?? static function ( int $min, int $max ): int {
			return function_exists( 'wp_rand' ) ? wp_rand( $min, $max ) : mt_rand( $min, $max );
		};

		$units = [];
		foreach ( $cells as $cell ) {
			$shape = self::shape_for_ratio( (float) $cell['ratio'] );
			$big   = $rng( 1, 100 ) <= 35;
			$span  = self::cell_span( $shape, $big );

			$units[] = [
				'cell' => $cell,
				'col'  => $span['col'],
				'row'  => $span['row'],
			];
		}

		return $units;
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
