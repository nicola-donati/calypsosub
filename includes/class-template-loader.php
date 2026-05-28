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
		if ( ! is_singular() ) return $template;

		$post_type = get_post_type();
		if ( ! in_array( $post_type, $this->post_types, true ) ) return $template;

		$modes = (array) get_option( 'calypsosub_template_modes', [] );
		$mode  = $modes[ $post_type ] ?? 'plugin';

		// Modalità tema/editor: lascia fare a WordPress (tema classico o FSE)
		if ( 'wp' === $mode ) return $template;

		$filename = 'single-' . $post_type . '.php';

		// Theme override PHP: il tema può mettere calypsosub/{filename} nella sua cartella
		$theme_override = locate_template( [ 'calypsosub/' . $filename ] );
		if ( $theme_override ) return $theme_override;

		// Fallback: template del plugin
		$plugin_template = CALYPSOSUB_PATH . 'templates/' . $filename;
		if ( file_exists( $plugin_template ) ) return $plugin_template;

		return $template;
	}
}
