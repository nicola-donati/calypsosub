<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Storia_Helpers {

	public const META_YEAR  = '_calypso_timeline_year';
	public const META_TITLE = '_calypso_timeline_title';
	public const META_TEXT  = '_calypso_timeline_text';

	public static function resolve_year( int $post_id ): int {
		$year = (int) get_post_meta( $post_id, self::META_YEAR, true );
		if ( $year > 0 ) {
			return $year;
		}
		return (int) get_the_date( 'Y', $post_id );
	}

	public static function resolve_title( int $post_id, string $title_source = 'post_title' ): string {
		if ( $title_source === 'custom' ) {
			$custom = get_post_meta( $post_id, self::META_TITLE, true );
			if ( is_string( $custom ) && trim( $custom ) !== '' ) {
				return $custom;
			}
		}
		return (string) get_the_title( $post_id );
	}

	public static function resolve_text( int $post_id, string $text_source = 'excerpt' ): string {
		if ( $text_source === 'custom' ) {
			$custom = get_post_meta( $post_id, self::META_TEXT, true );
			if ( is_string( $custom ) && trim( $custom ) !== '' ) {
				return $custom;
			}
		}
		$excerpt = get_the_excerpt( $post_id );
		return is_string( $excerpt ) ? $excerpt : '';
	}

	/**
	 * @return WP_Post[]
	 */
	public static function get_items( array $attrs ): array {
		$mode = (string) ( $attrs['source_mode'] ?? 'query' );

		if ( $mode === 'manual' ) {
			$manual_ids = array_map( 'intval', $attrs['manual_ids'] ?? [] );
			if ( empty( $manual_ids ) ) {
				return [];
			}
			return get_posts( [
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'post__in'       => $manual_ids,
				'orderby'        => 'post__in',
				'numberposts'    => -1,
				'suppress_filters' => false,
			] );
		}

		$args = [
			'post_type'         => 'post',
			'post_status'       => 'publish',
			'numberposts'       => -1,
			'suppress_filters'  => false,
		];

		$category_ids = array_map( 'intval', $attrs['category_ids'] ?? [] );
		$tag_ids      = array_map( 'intval', $attrs['tag_ids'] ?? [] );
		$tax_query    = [];

		if ( ! empty( $category_ids ) ) {
			$tax_query[] = [
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			];
		}
		if ( ! empty( $tag_ids ) ) {
			$tax_query[] = [
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => $tag_ids,
			];
		}
		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}

		$date_from = (string) ( $attrs['date_from'] ?? '' );
		$date_to   = (string) ( $attrs['date_to'] ?? '' );
		if ( $date_from !== '' || $date_to !== '' ) {
			$date_query = [ 'inclusive' => true ];
			if ( $date_from !== '' ) {
				$date_query['after'] = $date_from;
			}
			if ( $date_to !== '' ) {
				$date_query['before'] = $date_to;
			}
			$args['date_query'] = [ $date_query ];
		}

		$items = get_posts( $args );

		$year_from = (int) ( $attrs['year_from'] ?? 0 );
		$year_to   = (int) ( $attrs['year_to'] ?? 0 );
		if ( $year_from > 0 || $year_to > 0 ) {
			$items = array_values( array_filter( $items, function ( $post ) use ( $year_from, $year_to ) {
				$year = self::resolve_year( $post->ID );
				if ( $year_from > 0 && $year < $year_from ) {
					return false;
				}
				if ( $year_to > 0 && $year > $year_to ) {
					return false;
				}
				return true;
			} ) );
		}

		$order_by = (string) ( $attrs['order_by'] ?? 'event_year' );
		$order    = (string) ( $attrs['order'] ?? 'asc' );

		usort( $items, function ( $a, $b ) use ( $order_by ) {
			if ( $order_by === 'post_date' ) {
				return strcmp( $a->post_date, $b->post_date );
			}
			return self::resolve_year( $a->ID ) <=> self::resolve_year( $b->ID );
		} );

		if ( $order === 'desc' ) {
			$items = array_reverse( $items );
		}

		$max_items = (int) ( $attrs['max_items'] ?? 0 );
		if ( $max_items > 0 ) {
			$items = array_slice( $items, 0, $max_items );
		}

		return $items;
	}
}
