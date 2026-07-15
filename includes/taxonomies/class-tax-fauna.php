<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Tax_Fauna {

	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
	}

	public function register(): void {
		register_taxonomy( 'calypso_fauna', [ 'calypso_uscita' ], [
			'label'             => __( 'Fauna e habitat', 'calypsosub' ),
			'labels'            => [
				'name'          => __( 'Fauna e habitat', 'calypsosub' ),
				'singular_name' => __( 'Elemento', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi elemento', 'calypsosub' ),
				'edit_item'     => __( 'Modifica elemento', 'calypsosub' ),
			],
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'rewrite'           => [ 'slug' => 'fauna' ],
			'show_in_rest'      => true,
		] );
	}
}
