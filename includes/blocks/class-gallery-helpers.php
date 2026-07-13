<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Gallery_Helpers {

	private const SHAPE_CELLS = [
		'horizontal' => [ 'col' => 2, 'row' => 1 ],
		'vertical'   => [ 'col' => 1, 'row' => 2 ],
		'square'     => [ 'col' => 1, 'row' => 1 ],
	];

	private const SHAPE_CELLS_BIG = [
		'horizontal' => [ 'col' => 3, 'row' => 2 ],
		'vertical'   => [ 'col' => 2, 'row' => 3 ],
		'square'     => [ 'col' => 2, 'row' => 2 ],
	];

	public static function classify_shape( float $ratio ): string {
		if ( $ratio >= 1.6 ) {
			return 'horizontal';
		}
		if ( $ratio <= 0.63 ) {
			return 'vertical';
		}
		return 'square';
	}

	public static function cell_style( float $ratio, bool $big = false ): array {
		$shape = self::classify_shape( $ratio );
		return $big ? self::SHAPE_CELLS_BIG[ $shape ] : self::SHAPE_CELLS[ $shape ];
	}

	public static function resolve_overlay_text( int $attachment_id ): string {
		$caption = wp_get_attachment_caption( $attachment_id );
		return $caption !== '' ? $caption : (string) get_the_title( $attachment_id );
	}

	/**
	 * Costruisce l'array di celle (url, ratio, alt, caption...) a partire da
	 * una lista di attachment ID. Condiviso tra blocco galleria e galleria
	 * docente per evitare di duplicare la logica di lettura media.
	 */
	public static function build_cells_from_attachments( array $attachment_ids, string $image_size = 'large' ): array {
		$cells = [];
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment_id = (int) $attachment_id;
			$img = wp_get_attachment_image_src( $attachment_id, $image_size );
			if ( ! $img ) {
				continue;
			}
			[ $img_url, $img_w, $img_h ] = $img;
			$overlay_text = self::resolve_overlay_text( $attachment_id );
			$alt          = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ?: $overlay_text;

			$cells[] = [
				'id'      => $attachment_id,
				'url'     => $img_url,
				'full'    => wp_get_attachment_image_url( $attachment_id, 'full' ) ?: $img_url,
				'ratio'   => $img_w > 0 && $img_h > 0 ? $img_w / $img_h : 1,
				'alt'     => $alt,
				'caption' => $overlay_text,
			];
		}
		return $cells;
	}

	/**
	 * Ogni cella diventa "big" con probabilità ~35% (indipendente, non legata
	 * all'indice): con foto dallo stesso aspect ratio (es. tutte quadrate) il
	 * layout deve comunque variare da un render all'altro, non ripetere
	 * sempre lo stesso scheletro.
	 */
	public static function build_units( array $cells, ?callable $is_big = null ): array {
		$is_big = $is_big ?? static function (): bool {
			return mt_rand( 1, 100 ) <= 35;
		};
		$units = [];
		foreach ( $cells as $cell ) {
			$units[] = [ 'cell' => $cell ] + self::cell_style( $cell['ratio'], (bool) $is_big() );
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
