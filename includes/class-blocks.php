<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Blocks {

	private const BLOCKS = [
		'calypso/lista-uscite'    => [ 'file' => 'block-uscite-lista.php',     'title' => 'Lista Uscite' ],
		'calypso/lista-corsi'     => [ 'file' => 'block-corsi-lista.php',      'title' => 'Lista Corsi' ],
		'calypso/lista-docenti'   => [ 'file' => 'block-docenti-lista.php',    'title' => 'Lista Docenti' ],
		'calypso/lista-eventi'    => [ 'file' => 'block-eventi-lista.php',     'title' => 'Lista Eventi' ],
		'calypso/area-personale'  => [ 'file' => 'block-area-personale.php',   'title' => 'Area Personale' ],
		'calypso/prossima-uscita' => [
			'file'       => 'block-prossima-uscita.php',
			'title'      => 'Prossima Uscita',
			'attributes' => [
				'inside_hero' => [ 'type' => 'boolean', 'default' => false ],
			],
		],
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
			$path  = CALYPSOSUB_PATH . 'block-templates/' . $cfg['file'];
			$attrs = $cfg['attributes'] ?? [];
			register_block_type( $name, [
				'render_callback' => static function ( array $attributes ) use ( $path ): string {
					ob_start();
					include $path;
					return (string) ob_get_clean();
				},
				'category'   => 'calypso',
				'attributes' => $attrs,
			] );
		}
	}

	public function enqueue_editor_js(): void {
		$blocks_json = wp_json_encode(
			array_map(
				static fn( string $name, array $cfg ) => [
					'name'       => $name,
					'title'      => $cfg['title'],
					'attributes' => $cfg['attributes'] ?? [],
				],
				array_keys( self::BLOCKS ),
				array_values( self::BLOCKS )
			)
		);

		$js = <<<JS
(function (blocks, element, components) {
	var el = element.createElement;
	var ToggleControl = components.ToggleControl;
	var PanelBody    = components.PanelBody;
	var InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : null;

	var calypsoBlocks = {$blocks_json};
	calypsoBlocks.forEach(function (info) {
		if (blocks.getBlockType(info.name)) return;

		var hasInsideHero = info.attributes && info.attributes.inside_hero !== undefined;

		blocks.registerBlockType(info.name, {
			title: info.title,
			category: 'calypso',
			icon: 'grid-view',
			attributes: info.attributes || {},
			edit: function (props) {
				var children = [
					el('div', {
						style: {
							padding: '12px 16px',
							background: '#e8f4f8',
							borderLeft: '4px solid #1d6f9c',
							fontFamily: 'monospace',
							fontSize: '13px',
							color: '#0a2540'
						}
					}, '⚓ ' + info.title + (hasInsideHero
						? ' — ' + (props.attributes.inside_hero ? 'Dentro hero (desktop)' : 'Fuori hero (mobile)')
						: ' (server-side rendered)'
					))
				];
				if (hasInsideHero && InspectorControls) {
					children.unshift(el(InspectorControls, {},
						el(PanelBody, { title: 'Impostazioni', initialOpen: true },
							el(ToggleControl, {
								label: 'Dentro hero',
								help: props.attributes.inside_hero
									? 'Card floating — visibile solo su desktop'
									: 'Strip section — visibile solo su mobile',
								checked: !!props.attributes.inside_hero,
								onChange: function (val) { props.setAttributes({ inside_hero: val }); }
							})
						)
					));
				}
				return el(element.Fragment, {}, children);
			},
			save: function () { return null; },
		});
	});
}(window.wp.blocks, window.wp.element, window.wp.components));
JS;

		wp_add_inline_script( 'wp-blocks', $js );
	}
}
