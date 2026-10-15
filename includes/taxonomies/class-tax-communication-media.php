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
