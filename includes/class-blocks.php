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
		'calypso/hero-home' => [
			'file'       => 'block-hero-home.php',
			'title'      => 'Hero Home',
			'attributes' => [
				'image_id'       => [ 'type' => 'integer', 'default' => 0 ],
				'eyebrow'        => [ 'type' => 'string',  'default' => 'La subacquea ad Arezzo dal 1978' ],
				'eyebrow_wave'   => [ 'type' => 'boolean', 'default' => true ],
				'title'          => [ 'type' => 'string',  'default' => "Sotto la superficie\nc'è un" ],
				'title_em'       => [ 'type' => 'string',  'default' => 'altro mondo.' ],
				'description'    => [ 'type' => 'string',  'default' => '' ],
				'btn1_text'      => [ 'type' => 'string',  'default' => 'Diventa socio' ],
				'btn1_url'       => [ 'type' => 'string',  'default' => '' ],
				'btn2_text'      => [ 'type' => 'string',  'default' => 'Guarda il video' ],
				'btn2_url'       => [ 'type' => 'string',  'default' => '' ],
				'show_uscita'    => [ 'type' => 'boolean', 'default' => true ],
				'marquee_on'     => [ 'type' => 'boolean', 'default' => true ],
				'marquee_items'  => [ 'type' => 'string',  'default' => 'Argentario,Elba,Giglio,Giannutri,Croazia,Egadi' ],
				'marquee_mobile' => [ 'type' => 'boolean', 'default' => false ],
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
		wp_register_script(
			'calypso-blocks-editor',
			false,
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ],
			null,
			true
		);
		wp_enqueue_script( 'calypso-blocks-editor' );

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
(function (blocks, element, components, blockEditor) {
	var el = element.createElement;
	var Fragment          = element.Fragment;
	var InspectorControls = blockEditor ? blockEditor.InspectorControls : null;
	var MediaUploadCheck  = blockEditor ? blockEditor.MediaUploadCheck  : null;
	var MediaUpload       = blockEditor ? blockEditor.MediaUpload       : null;
	var PanelBody     = components.PanelBody;
	var TextControl   = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var Button        = components.Button;

	var calypsoBlocks = {$blocks_json};

	calypsoBlocks.forEach(function (info) {
		if (blocks.getBlockType(info.name)) return;

		/* ── Controlli speciali per hero-home ── */
		if (info.name === 'calypso/hero-home') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: 'cover-image',
				attributes: info.attributes || {},
				edit: function (props) {
					var a = props.attributes;
					var set = props.setAttributes;

					var imgLabel = a.image_id
						? 'Immagine: ID ' + a.image_id
						: 'Nessuna immagine';

					var mediaBtn = (MediaUploadCheck && MediaUpload)
						? el(MediaUploadCheck, {},
							el(MediaUpload, {
								onSelect: function (media) { set({ image_id: media.id }); },
								allowedTypes: ['image'],
								value: a.image_id,
								render: function (ref) {
									return el(Button, {
										onClick: ref.open,
										variant: a.image_id ? 'secondary' : 'primary',
										style: { marginBottom: '8px' }
									}, a.image_id ? '⬡ Cambia immagine' : '⬡ Scegli immagine');
								}
							}))
						: null;

					var controls = InspectorControls
						? el(InspectorControls, {},
							el(PanelBody, { title: 'Immagine sfondo', initialOpen: true },
								mediaBtn,
								a.image_id
									? el(Button, {
										onClick: function () { set({ image_id: 0 }); },
										variant: 'link',
										isDestructive: true
									}, 'Rimuovi immagine')
									: null
							),
							el(PanelBody, { title: 'Testo', initialOpen: false },
								el(TextControl, {
									label: 'Eyebrow',
									value: a.eyebrow || '',
									onChange: function (v) { set({ eyebrow: v }); }
								}),
								el(ToggleControl, {
									label: 'Mostra icona onda',
									checked: !!a.eyebrow_wave,
									onChange: function (v) { set({ eyebrow_wave: v }); }
								}),
								el(TextareaControl, {
									label: 'Titolo (\\n per a capo)',
									value: a.title || '',
									onChange: function (v) { set({ title: v }); }
								}),
								el(TextControl, {
									label: 'Titolo — parte in evidenza (aqua)',
									value: a.title_em || '',
									onChange: function (v) { set({ title_em: v }); }
								}),
								el(TextareaControl, {
									label: 'Descrizione',
									value: a.description || '',
									onChange: function (v) { set({ description: v }); }
								})
							),
							el(PanelBody, { title: 'Bottoni', initialOpen: false },
								el(TextControl, {
									label: 'Bottone primario — testo',
									value: a.btn1_text || '',
									onChange: function (v) { set({ btn1_text: v }); }
								}),
								el(TextControl, {
									label: 'Bottone primario — URL',
									value: a.btn1_url || '',
									type: 'url',
									onChange: function (v) { set({ btn1_url: v }); }
								}),
								el(TextControl, {
									label: 'Bottone secondario — testo',
									value: a.btn2_text || '',
									onChange: function (v) { set({ btn2_text: v }); }
								}),
								el(TextControl, {
									label: 'Bottone secondario — URL',
									value: a.btn2_url || '',
									type: 'url',
									onChange: function (v) { set({ btn2_url: v }); }
								})
							),
							el(PanelBody, { title: 'Prossima uscita', initialOpen: false },
								el(ToggleControl, {
									label: 'Mostra card prossima uscita',
									help: 'Visibile solo su desktop, auto-aggiornata dal database',
									checked: !!a.show_uscita,
									onChange: function (v) { set({ show_uscita: v }); }
								})
							),
							el(PanelBody, { title: 'Ticker luoghi', initialOpen: false },
								el(ToggleControl, {
									label: 'Mostra ticker',
									checked: !!a.marquee_on,
									onChange: function (v) { set({ marquee_on: v }); }
								}),
								a.marquee_on ? el(TextareaControl, {
									label: 'Luoghi (separati da virgola)',
									value: a.marquee_items || '',
									onChange: function (v) { set({ marquee_items: v }); }
								}) : null,
								a.marquee_on ? el(ToggleControl, {
									label: 'Visibile su mobile',
									checked: !!a.marquee_mobile,
									onChange: function (v) { set({ marquee_mobile: v }); }
								}) : null
							)
						)
						: null;

					var preview = el('div', {
						style: {
							padding: '24px 20px',
							background: '#0a2540',
							borderRadius: '6px',
							color: '#fff',
							fontFamily: 'system-ui, sans-serif',
							minHeight: '120px',
							position: 'relative'
						}
					},
						el('div', { style: { fontSize: '10px', letterSpacing: '.12em', textTransform: 'uppercase', color: '#26CBFB', marginBottom: '8px' } },
							'~ ' + (a.eyebrow || 'Eyebrow')
						),
						el('div', { style: { fontSize: '28px', fontWeight: '900', lineHeight: '1', marginBottom: '8px' } },
							(a.title || 'Titolo').replace(/\\n/g, ' ') + ' ',
							el('em', { style: { color: '#26CBFB', fontStyle: 'normal' } }, a.title_em || 'em')
						),
						a.description ? el('div', { style: { fontSize: '12px', opacity: '.8', marginBottom: '12px' } }, a.description.substring(0, 80) + '…') : null,
						el('div', { style: { display: 'flex', gap: '8px' } },
							a.btn1_text ? el('span', { style: { background: '#ff6b4a', color: '#fff', fontSize: '11px', padding: '6px 12px', borderRadius: '999px', fontWeight: '700' } }, a.btn1_text) : null,
							a.btn2_text ? el('span', { style: { background: 'rgba(255,255,255,.1)', color: '#fff', fontSize: '11px', padding: '6px 12px', borderRadius: '999px', border: '1px solid rgba(255,255,255,.3)' } }, a.btn2_text) : null
						),
						el('div', { style: { fontSize: '10px', opacity: '.5', marginTop: '8px' } },
							'⚓ Hero Home · ' + (a.image_id ? 'img:' + a.image_id : 'no image') +
							(a.show_uscita ? ' · prossima uscita ON' : '') +
							(a.marquee_on ? ' · ticker ON' : '')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ── Blocchi generici ── */
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
				return el(Fragment, {}, children);
			},
			save: function () { return null; },
		});
	});
}(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor));
JS;

		wp_add_inline_script( 'calypso-blocks-editor', $js );
	}
}
