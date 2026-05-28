<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Blocks {

	private const BLOCKS = [
		'calypso/lista-uscite'   => [ 'file' => 'block-uscite-lista.php',  'title' => 'Lista Uscite' ],
		'calypso/lista-corsi'    => [ 'file' => 'block-corsi-lista.php',   'title' => 'Lista Corsi' ],
		'calypso/lista-docenti'  => [ 'file' => 'block-docenti-lista.php', 'title' => 'Lista Docenti' ],
		'calypso/lista-eventi'   => [ 'file' => 'block-eventi-lista.php',  'title' => 'Lista Eventi' ],
		'calypso/area-personale' => [ 'file' => 'block-area-personale.php','title' => 'Area Personale' ],
	];

	public function init(): void {
		add_filter( 'block_categories_all',       [ $this, 'register_category' ] );
		add_action( 'init',                        [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_js' ] );
	}

	public function register_category( array $categories ): array {
		return array_merge( $categories, [ [
			'slug'  => 'calypso',
			'title' => __( 'Calypso Sub', 'calypsosub' ),
			'icon'  => 'waves',
		] ] );
	}

	public function register_blocks(): void {
		foreach ( self::BLOCKS as $name => $cfg ) {
			$path = CALYPSOSUB_PATH . 'block-templates/' . $cfg['file'];
			register_block_type( $name, [
				'render_callback' => static function () use ( $path ): string {
					ob_start();
					include $path;
					return (string) ob_get_clean();
				},
				'category' => 'calypso',
			] );
		}
	}

	public function enqueue_editor_js(): void {
		$blocks_json = wp_json_encode(
			array_map(
				static fn( string $name, array $cfg ) => [ 'name' => $name, 'title' => $cfg['title'] ],
				array_keys( self::BLOCKS ),
				array_values( self::BLOCKS )
			)
		);

		$js = <<<JS
(function (blocks, element) {
	var el = element.createElement;
	var calypsoBlocks = {$blocks_json};
	calypsoBlocks.forEach(function (info) {
		if (blocks.getBlockType(info.name)) return;
		blocks.registerBlockType(info.name, {
			title: info.title,
			category: 'calypso',
			icon: 'grid-view',
			edit: function () {
				return el('div', {
					style: {
						padding: '12px 16px',
						background: '#e8f4f8',
						borderLeft: '4px solid #1d6f9c',
						fontFamily: 'monospace',
						fontSize: '13px',
						color: '#0a2540'
					}
				}, '⚓ ' + info.title + ' (server-side rendered)');
			},
			save: function () { return null; },
		});
	});
}(window.wp.blocks, window.wp.element));
JS;

		wp_add_inline_script( 'wp-blocks', $js );
	}
}
