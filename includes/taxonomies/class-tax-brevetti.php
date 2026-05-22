<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Tax_Brevetti {

	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
	}

	public function register(): void {
		register_taxonomy( 'calypso_brevetto', [ 'calypso_docente' ], [
			'label'             => __( 'Brevetti', 'calypsosub' ),
			'labels'            => [
				'name'          => __( 'Brevetti', 'calypsosub' ),
				'singular_name' => __( 'Brevetto', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi brevetto', 'calypsosub' ),
				'edit_item'     => __( 'Modifica brevetto', 'calypsosub' ),
			],
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'rewrite'           => [ 'slug' => 'brevetto' ],
			'show_in_rest'      => true,
		] );
	}
}
