<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Template_Loader {

	private array $post_types = [
		'calypso_uscita',
		'calypso_evento',
		'calypso_corso',
		'calypso_docente',
	];

	public function init(): void {
		add_filter( 'template_include', [ $this, 'load_template' ] );
	}

	public function load_template( string $template ): string {
		if ( is_singular() ) {
			$post_type = get_post_type();
			if ( ! in_array( $post_type, $this->post_types, true ) ) return $template;
			$filename = 'single-' . $post_type . '.php';
		} elseif ( is_post_type_archive( $this->post_types ) ) {
			$post_type = get_query_var( 'post_type' );
			$filename  = 'archive-' . $post_type . '.php';
		} else {
			return $template;
		}

		$plugin_template = CALYPSOSUB_PATH . 'templates/' . $filename;
		if ( file_exists( $plugin_template ) ) return $plugin_template;

		return $template;
	}
}
