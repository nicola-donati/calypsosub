<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Tax_Livelli {

	public function init(): void {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
	}

	public function register_taxonomy(): void {
		register_taxonomy( 'calypso_livello', [ 'calypso_corso', 'calypso_uscita' ], [
			'label'        => __( 'Livelli', 'calypsosub' ),
			'labels'       => [
				'name'          => __( 'Livelli', 'calypsosub' ),
				'singular_name' => __( 'Livello', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi livello', 'calypsosub' ),
				'edit_item'     => __( 'Modifica livello', 'calypsosub' ),
			],
			'hierarchical' => false,
			'show_in_rest' => true,
			'show_ui'      => true,
			'rewrite'      => [ 'slug' => 'livello-corso' ],
		] );
	}
}
