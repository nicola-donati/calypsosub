<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Taxonomy separata (non calypso_media_tag, usata per la galleria pubblica
 * e le cui query 'tag'/'all' mostrerebbero per errore anche queste foto)
 * per raggruppare nella libreria media le immagini inserite nelle
 * comunicazioni — un termine per comunicazione, così con tante
 * comunicazioni la libreria resta filtrabile una per una invece che un
 * unico mucchio indistinto.
 */
class Calypsosub_Tax_Communication_Media {

	public const TAXONOMY = 'calypso_com_media';

	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
		// register_taxonomy(..., 'show_admin_column' => true) adds a column
		// to the Media list table, but — unlike post categories/tags — WP
		// core does NOT add a filter dropdown for custom attachment
		// taxonomies on its own; restrict_manage_posts/pre_get_posts here
		// do that explicitly. Only the list view of Media (the grid/modal
		// view has no UI for arbitrary taxonomy filters at all) shows it.
		add_filter( 'restrict_manage_posts', [ $this, 'render_filter_dropdown' ], 10, 2 );
		add_action( 'pre_get_posts',         [ $this, 'apply_filter_to_query' ] );

		// Stessa cosa ma per la vista a griglia: lì il filtro va nel
		// Backbone JS del media grid (enqueue_grid_filter_script()), e il
		// valore scelto arriva via la query AJAX di quella griglia, non da
		// $_GET — va applicato con ajax_query_attachments_args, non con
		// pre_get_posts (che per la griglia non intercetta nulla di utile).
		add_action( 'wp_enqueue_media',             [ $this, 'enqueue_grid_filter_script' ] );
		add_filter( 'ajax_query_attachments_args',  [ $this, 'apply_filter_to_grid_query' ] );
	}

	public function enqueue_grid_filter_script(): void {
		$terms = get_terms( [ 'taxonomy' => self::TAXONOMY, 'hide_empty' => true ] );
		if ( is_wp_error( $terms ) || empty( $terms ) ) return;

		wp_enqueue_script(
			'calypsosub-admin-com-media-filter',
			CALYPSOSUB_URL . 'assets/js/admin-com-media-filter.js',
			[ 'media-views' ],
			CALYPSOSUB_VERSION,
			true
		);

		wp_add_inline_script(
			'calypsosub-admin-com-media-filter',
			'window.calypsoComMediaTerms = ' . wp_json_encode( array_map(
				static fn( WP_Term $t ) => [ 'slug' => $t->slug, 'name' => $t->name ],
				$terms
			) ) . ';window.calypsoComMediaAllLabel = ' . wp_json_encode( __( 'Tutte le comunicazioni', 'calypsosub' ) ) . ';',
			'before'
		);
	}

	/** @param array<string,mixed> $query */
	public function apply_filter_to_grid_query( array $query ): array {
		$selected = isset( $_REQUEST['query']['calypso_com_media'] )
			? sanitize_title( wp_unslash( $_REQUEST['query']['calypso_com_media'] ) )
			: '';
		if ( $selected === '' ) return $query;

		$query['tax_query'] = [ [
			'taxonomy' => self::TAXONOMY,
			'field'    => 'slug',
			'terms'    => $selected,
		] ];
		return $query;
	}

	public function render_filter_dropdown( string $post_type, string $which ): void {
		if ( $post_type !== 'attachment' ) return;

		wp_dropdown_categories( [
			'taxonomy'        => self::TAXONOMY,
			'name'            => self::TAXONOMY,
			'show_option_all' => get_taxonomy( self::TAXONOMY )->labels->all_items,
			'hide_empty'      => true,
			'hierarchical'    => false,
			'selected'        => isset( $_GET[ self::TAXONOMY ] ) ? sanitize_title( wp_unslash( $_GET[ self::TAXONOMY ] ) ) : '',
			'value_field'     => 'slug',
			'orderby'         => 'name',
		] );
	}

	public function apply_filter_to_query( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) return;
		if ( $query->get( 'post_type' ) !== 'attachment' ) return;
		$selected = isset( $_GET[ self::TAXONOMY ] ) ? sanitize_title( wp_unslash( $_GET[ self::TAXONOMY ] ) ) : '';
		if ( $selected === '' ) return;

		$query->set( 'tax_query', [ [
			'taxonomy' => self::TAXONOMY,
			'field'    => 'slug',
			'terms'    => $selected,
		] ] );
	}

	public function register(): void {
		register_taxonomy( self::TAXONOMY, 'attachment', [
			'labels' => [
				'name'          => __( 'Comunicazioni', 'calypsosub' ),
				'singular_name' => __( 'Comunicazione', 'calypsosub' ),
				'search_items'  => __( 'Cerca comunicazione', 'calypsosub' ),
				'all_items'     => __( 'Tutte le comunicazioni', 'calypsosub' ),
				'menu_name'     => __( 'Comunicazioni', 'calypsosub' ),
			],
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'query_var'         => false,
		] );
	}

	/**
	 * Crea (se non esiste) il termine dedicato a una comunicazione e vi
	 * assegna gli attachment passati — append, non replace: un'immagine
	 * riutilizzata da un'altra parte del sito mantiene i suoi altri termini.
	 *
	 * @param int[] $attachment_ids
	 */
	public static function tag_attachments_for_post( int $post_id, array $attachment_ids ): void {
		if ( empty( $attachment_ids ) ) return;

		$term_slug = 'cso-com-' . $post_id;
		$term      = get_term_by( 'slug', $term_slug, self::TAXONOMY );
		if ( ! $term ) {
			$title    = get_the_title( $post_id ) ?: ( '#' . $post_id );
			$inserted = wp_insert_term( $title, self::TAXONOMY, [ 'slug' => $term_slug ] );
			if ( is_wp_error( $inserted ) ) return;
			$term_id = $inserted['term_id'];
		} else {
			$term_id = $term->term_id;
		}

		foreach ( $attachment_ids as $attachment_id ) {
			wp_set_object_terms( $attachment_id, [ $term_id ], self::TAXONOMY, true );
		}
	}
}
