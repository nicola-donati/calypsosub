<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Tax_Media_Tag {

	public const TAXONOMY = 'calypso_media_tag';

	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
	}

	public function register(): void {
		register_taxonomy( self::TAXONOMY, 'attachment', [
			'labels' => [
				'name'          => __( 'Tag Galleria', 'calypsosub' ),
				'singular_name' => __( 'Tag Galleria', 'calypsosub' ),
				'search_items'  => __( 'Cerca tag galleria', 'calypsosub' ),
				'all_items'     => __( 'Tutti i tag galleria', 'calypsosub' ),
				'edit_item'     => __( 'Modifica tag galleria', 'calypsosub' ),
				'update_item'   => __( 'Aggiorna tag galleria', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi nuovo tag galleria', 'calypsosub' ),
				'new_item_name' => __( 'Nome nuovo tag galleria', 'calypsosub' ),
				'menu_name'     => __( 'Tag Galleria', 'calypsosub' ),
			],
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rest_base'         => self::TAXONOMY,
			'query_var'         => true,
		] );
	}
}
