<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Blocks {

	private const BLOCKS = [
		'calypso/lista-uscite' => [
			'file'  => 'block-lista-uscite.php',
			'title' => 'Lista Uscite',
			'icon'  => 'calendar-alt',
			'attributes' => [
				/* ── Intestazione sezione ── */
				'eyebrow'          => [ 'type' => 'string',  'default' => 'Prossime uscite' ],
				'title'            => [ 'type' => 'string',  'default' => "Il mare\nci aspetta." ],
				'show_header_link' => [ 'type' => 'boolean', 'default' => true ],
				'header_link_text' => [ 'type' => 'string',  'default' => 'Calendario completo' ],
				/* ── Comportamento ── */
				'show_past'  => [ 'type' => 'boolean', 'default' => false ],
				'max_items'  => [ 'type' => 'integer', 'default' => 0 ],
				/* ── Colonne visibili ── */
				'show_badge'   => [ 'type' => 'boolean', 'default' => true ],
				'show_ritrovo' => [ 'type' => 'boolean', 'default' => true ],
				'show_posti'   => [ 'type' => 'boolean', 'default' => true ],
				'show_cta'     => [ 'type' => 'boolean', 'default' => true ],
				/* ── Etichette CTA ── */
				'btn_prenota'   => [ 'type' => 'string', 'default' => 'Prenota' ],
				'btn_attesa'    => [ 'type' => 'string', 'default' => "Lista d'attesa" ],
				'btn_esaurito'  => [ 'type' => 'string', 'default' => 'Esaurito' ],
				'btn_terminata' => [ 'type' => 'string', 'default' => 'Terminata' ],
				'lbl_ritrovo'   => [ 'type' => 'string', 'default' => 'RITROVO' ],
				'lbl_liberi'    => [ 'type' => 'string', 'default' => 'Posti liberi' ],
				/* ── Stato vuoto ── */
				'empty_title' => [ 'type' => 'string', 'default' => 'Nessuna uscita in programma.' ],
			],
		],
		'calypso/calendario' => [
			'file'  => 'block-calendario.php',
			'title' => 'Calendario',
			'icon'  => 'calendar',
			'attributes' => [
				'eyebrow'     => [ 'type' => 'string',  'default' => '03 — Prossime uscite' ],
				'heading'     => [ 'type' => 'string',  'default' => '' ],
				'link_text'   => [ 'type' => 'string',  'default' => 'Calendario completo' ],
				'link_url'    => [ 'type' => 'string',  'default' => '' ],
				'max_items'   => [ 'type' => 'integer', 'default' => 6 ],
				'show_uscite' => [ 'type' => 'boolean', 'default' => true ],
				'show_eventi' => [ 'type' => 'boolean', 'default' => true ],
				'show_corsi'  => [ 'type' => 'boolean', 'default' => true ],
			],
		],
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
		'calypso/sezione' => [
			'file'  => 'block-sezione.php',
			'title' => 'Sezione',
			'icon'  => 'layout',
			'attributes' => [
				'eyebrow'          => [ 'type' => 'string',  'default' => '' ],
				'title'            => [ 'type' => 'string',  'default' => '' ],
				'header_link_text' => [ 'type' => 'string',  'default' => '' ],
				'header_link_url'  => [ 'type' => 'string',  'default' => '' ],
				'bg_color'         => [ 'type' => 'string',  'default' => '#dff4f8' ],
				'bg_image_id'      => [ 'type' => 'integer', 'default' => 0 ],
				'padding_y'        => [ 'type' => 'integer', 'default' => 80 ],
				'max_width'        => [ 'type' => 'integer', 'default' => 1320 ],
				'eyebrow_color'    => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'title_color'      => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'title_size'       => [ 'type' => 'integer', 'default' => 76 ],
			],
		],
		'calypso/promo-card' => [
			'file'  => 'block-promo-card.php',
			'title' => 'Promo Card',
			'icon'  => 'format-image',
			'attributes' => [
				/* ── Immagine ── */
				'image_id'         => [ 'type' => 'integer', 'default' => 0 ],
				'image_alt'        => [ 'type' => 'string',  'default' => '' ],
				'image_height'     => [ 'type' => 'integer', 'default' => 200 ],
				'image_object_fit' => [ 'type' => 'string',  'default' => 'cover' ],
				'image_object_pos' => [ 'type' => 'string',  'default' => 'center center' ],
				/* ── Overlay immagine ── */
				'overlay_text'     => [ 'type' => 'string',  'default' => '' ],
				'overlay_bg'       => [ 'type' => 'string',  'default' => 'rgba(6,24,38,0.45)' ],
				'overlay_color'    => [ 'type' => 'string',  'default' => '#ffffff' ],
				'overlay_size'     => [ 'type' => 'integer', 'default' => 10 ],
				/* ── Contenuto ── */
				'eyebrow'          => [ 'type' => 'string',  'default' => '01' ],
				'title'            => [ 'type' => 'string',  'default' => 'Titolo' ],
				'description'      => [ 'type' => 'string',  'default' => '' ],
				'link_text'        => [ 'type' => 'string',  'default' => 'Scopri' ],
				'link_url'         => [ 'type' => 'string',  'default' => '' ],
				'link_new_tab'     => [ 'type' => 'boolean', 'default' => false ],
				/* ── Stile card ── */
				'card_bg'          => [ 'type' => 'string',  'default' => '#ffffff' ],
				'card_radius'      => [ 'type' => 'integer', 'default' => 16 ],
				'card_padding'     => [ 'type' => 'integer', 'default' => 24 ],
				'card_shadow'      => [ 'type' => 'boolean', 'default' => true ],
				/* ── Tipografia eyebrow ── */
				'eyebrow_color'    => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'eyebrow_size'     => [ 'type' => 'integer', 'default' => 13 ],
				/* ── Tipografia titolo ── */
				'title_color'      => [ 'type' => 'string',  'default' => '#061826' ],
				'title_size'       => [ 'type' => 'integer', 'default' => 42 ],
				'title_weight'     => [ 'type' => 'string',  'default' => '900' ],
				'title_transform'  => [ 'type' => 'string',  'default' => 'uppercase' ],
				'title_font'       => [ 'type' => 'string',  'default' => '' ],
				/* ── Tipografia descrizione ── */
				'desc_color'       => [ 'type' => 'string',  'default' => '#3d5a6c' ],
				'desc_size'        => [ 'type' => 'integer', 'default' => 14 ],
				/* ── Link ── */
				'link_color'       => [ 'type' => 'string',  'default' => '#1B77A7' ],
				'link_size'        => [ 'type' => 'integer', 'default' => 13 ],
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
				'render_callback' => static function ( array $attributes, string $content = '' ) use ( $path ): string {
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
					'icon'       => $cfg['icon'] ?? 'grid-view',
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
	var PanelBody       = components.PanelBody;
	var PanelRow        = components.PanelRow;
	var TextControl     = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl   = components.ToggleControl;
	var RangeControl    = components.RangeControl;
	var Button          = components.Button;
	var Divider         = components.Divider;
	var SelectControl   = components.SelectControl;
	var ColorPalette    = components.ColorPalette;

	function getThemeColors() {
		try {
			return window.wp.data.select('core/block-editor').getSettings().colors || [];
		} catch(e) { return []; }
	}

	var calypsoBlocks = {$blocks_json};

	calypsoBlocks.forEach(function (info) {
		if (blocks.getBlockType(info.name)) return;

		/* ════════════════════════════════════════════
		   calypso/lista-uscite — controlli completi
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/lista-uscite') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'calendar-alt',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					var controls = InspectorControls ? el(InspectorControls, {},

						/* ── Intestazione sezione ── */
						el(PanelBody, { title: 'Intestazione', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextareaControl, {
								label: 'Titolo (\\n per a capo)',
								help: 'Supporta \\n per andare a capo.',
								value: a.title || '',
								rows: 3,
								onChange: function (v) { set({ title: v }); }
							}),
							el(ToggleControl, {
								label: 'Mostra link "Calendario completo"',
								checked: !!a.show_header_link,
								onChange: function (v) { set({ show_header_link: v }); }
							}),
							a.show_header_link ? el(TextControl, {
								label: 'Testo del link',
								value: a.header_link_text || '',
								onChange: function (v) { set({ header_link_text: v }); }
							}) : null
						),

						/* ── Comportamento ── */
						el(PanelBody, { title: 'Comportamento', initialOpen: false },
							el(ToggleControl, {
								label: 'Mostra uscite passate',
								help: a.show_past ? 'Visualizza anche le uscite già concluse.' : 'Solo uscite future.',
								checked: !!a.show_past,
								onChange: function (v) { set({ show_past: v }); }
							}),
							el(RangeControl, {
								label: 'Numero massimo di uscite (0 = tutte)',
								help: '0 = mostra tutte. Utile per limitare la sezione in homepage.',
								value: a.max_items || 0,
								min: 0,
								max: 50,
								step: 1,
								onChange: function (v) { set({ max_items: v || 0 }); }
							})
						),

						/* ── Colonne ── */
						el(PanelBody, { title: 'Colonne visibili', initialOpen: false },
							el(ToggleControl, {
								label: 'Badge livello',
								help: 'Etichetta del livello richiesto (es. OWD+).',
								checked: !!a.show_badge,
								onChange: function (v) { set({ show_badge: v }); }
							}),
							el(ToggleControl, {
								label: 'Punto di ritrovo',
								help: 'Orario e luogo di ritrovo.',
								checked: !!a.show_ritrovo,
								onChange: function (v) { set({ show_ritrovo: v }); }
							}),
							el(ToggleControl, {
								label: 'Posti disponibili',
								checked: !!a.show_posti,
								onChange: function (v) { set({ show_posti: v }); }
							}),
							el(ToggleControl, {
								label: 'Bottone prenotazione',
								checked: !!a.show_cta,
								onChange: function (v) { set({ show_cta: v }); }
							})
						),

						/* ── Etichette ── */
						el(PanelBody, { title: 'Etichette', initialOpen: false },
							el(TextControl, {
								label: 'Bottone prenota',
								value: a.btn_prenota || '',
								onChange: function (v) { set({ btn_prenota: v }); }
							}),
							el(TextControl, {
								label: 'Bottone lista d\'attesa',
								value: a.btn_attesa || '',
								onChange: function (v) { set({ btn_attesa: v }); }
							}),
							el(TextControl, {
								label: 'Bottone esaurito',
								value: a.btn_esaurito || '',
								onChange: function (v) { set({ btn_esaurito: v }); }
							}),
							el(TextControl, {
								label: 'Bottone terminata',
								value: a.btn_terminata || '',
								onChange: function (v) { set({ btn_terminata: v }); }
							}),
							el(TextControl, {
								label: 'Etichetta "RITROVO"',
								value: a.lbl_ritrovo || '',
								onChange: function (v) { set({ lbl_ritrovo: v }); }
							}),
							el(TextControl, {
								label: 'Etichetta "Posti liberi"',
								value: a.lbl_liberi || '',
								onChange: function (v) { set({ lbl_liberi: v }); }
							})
						),

						/* ── Stato vuoto ── */
						el(PanelBody, { title: 'Stato vuoto (nessuna uscita)', initialOpen: false },
							el(TextControl, {
								label: 'Testo quando non ci sono uscite',
								value: a.empty_title || '',
								onChange: function (v) { set({ empty_title: v }); }
							})
						)

					) : null;

					/* Preview nel canvas */
					var titleLines = (a.title || 'Il mare\\nci aspetta.').split('\\n');
					var preview = el('div', {
						style: { background: '#dff4f8', borderRadius: '8px', padding: '28px 24px', fontFamily: 'system-ui,sans-serif' }
					},
						el('div', { style: { fontSize: '10px', fontWeight: 700, letterSpacing: '.18em', textTransform: 'uppercase', color: '#26CBFB', marginBottom: '10px' } },
							a.eyebrow || 'Prossime uscite'
						),
						el('div', { style: { fontSize: '28px', fontWeight: 900, color: '#1B77A7', lineHeight: 1, marginBottom: '20px' } },
							titleLines.join(' · ')
						),
						/* Fake rows */
						el('div', { style: { background: '#fff', borderRadius: '12px', overflow: 'hidden', boxShadow: '0 8px 24px -10px rgba(10,37,64,.2)' } },
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '16px', padding: '14px 16px', fontSize: '12px', color: '#0b1a26' } },
								el('div', { style: { fontWeight: 900, fontSize: '24px', color: '#1B77A7', minWidth: '32px' } }, '14'),
								el('div', { style: { flex: 1 } },
									el('div', { style: { fontWeight: 700, color: '#1B77A7' } }, 'Nome uscita di esempio'),
									el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)' } }, '📍 Argentario · 2 immersioni')
								),
								a.show_badge ? el('div', { style: { background: 'rgba(27,119,167,.1)', color: '#1B77A7', padding: '3px 8px', borderRadius: '999px', fontSize: '10px', fontWeight: 700 } }, 'OWD+') : null,
								a.show_posti ? el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)', fontFamily: 'monospace' } }, '● 4 posti') : null,
								a.show_cta ? el('div', { style: { background: '#061826', color: '#fff', padding: '6px 12px', borderRadius: '999px', fontSize: '11px', fontWeight: 700 } }, a.btn_prenota || 'Prenota') : null
							),
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '16px', padding: '14px 16px', fontSize: '12px', color: '#0b1a26', borderTop: '1px solid rgba(11,26,38,.06)' } },
								el('div', { style: { fontWeight: 900, fontSize: '24px', color: '#1B77A7', minWidth: '32px' } }, '21'),
								el('div', { style: { flex: 1 } },
									el('div', { style: { fontWeight: 700, color: '#1B77A7' } }, 'Seconda uscita di esempio'),
									el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)' } }, '📍 Isola d\'Elba · weekend')
								),
								a.show_badge ? el('div', { style: { background: 'rgba(27,119,167,.1)', color: '#1B77A7', padding: '3px 8px', borderRadius: '999px', fontSize: '10px', fontWeight: 700 } }, 'AOWD+') : null,
								a.show_posti ? el('div', { style: { fontSize: '11px', color: 'rgba(11,26,38,.5)', fontFamily: 'monospace' } }, '10 posti') : null,
								a.show_cta ? el('div', { style: { background: '#061826', color: '#fff', padding: '6px 12px', borderRadius: '999px', fontSize: '11px', fontWeight: 700 } }, a.btn_prenota || 'Prenota') : null
							)
						),
						el('div', { style: { fontSize: '10px', opacity: .55, marginTop: '10px', fontFamily: 'monospace' } },
							'calypso/lista-uscite' +
							(a.show_past ? ' · passate ON' : '') +
							(a.max_items > 0 ? ' · max ' + a.max_items : ' · tutte')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/calendario — controlli
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/calendario') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: 'calendar',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					var controls = InspectorControls ? el(InspectorControls, {},
						el(PanelBody, { title: 'Intestazione', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextareaControl, {
								label: 'Titolo (lascia vuoto = auto da mesi)',
								help: 'Se vuoto, il titolo viene generato automaticamente dai mesi degli eventi.',
								value: a.heading || '',
								rows: 2,
								onChange: function (v) { set({ heading: v }); }
							}),
							el(TextControl, {
								label: 'Testo link →',
								value: a.link_text || '',
								onChange: function (v) { set({ link_text: v }); }
							}),
							el(TextControl, {
								label: 'URL link (vuoto = archivio uscite)',
								value: a.link_url || '',
								type: 'url',
								onChange: function (v) { set({ link_url: v }); }
							})
						),
						el(PanelBody, { title: 'Contenuto', initialOpen: false },
							el(RangeControl, {
								label: 'Numero massimo di voci',
								value: a.max_items || 6,
								min: 1,
								max: 20,
								step: 1,
								onChange: function (v) { set({ max_items: v || 6 }); }
							}),
							el(ToggleControl, {
								label: 'Mostra uscite',
								checked: !!a.show_uscite,
								onChange: function (v) { set({ show_uscite: v }); }
							}),
							el(ToggleControl, {
								label: 'Mostra eventi',
								checked: !!a.show_eventi,
								onChange: function (v) { set({ show_eventi: v }); }
							}),
							el(ToggleControl, {
								label: 'Mostra corsi',
								checked: !!a.show_corsi,
								onChange: function (v) { set({ show_corsi: v }); }
							})
						)
					) : null;

					var preview = el('div', {
						style: { background: '#cfe9ee', borderRadius: '8px', padding: '24px', fontFamily: 'system-ui,sans-serif' }
					},
						el('div', { style: { fontSize: '10px', fontWeight: 700, letterSpacing: '.18em', textTransform: 'uppercase', color: '#1B77A7', marginBottom: '8px' } },
							a.eyebrow || '03 — Prossime uscite'
						),
						el('div', { style: { fontSize: '24px', fontWeight: 900, color: '#1B77A7', marginBottom: '16px', lineHeight: 1 } },
							a.heading || 'Mese e mese, il mare ci aspetta.'
						),
						el('div', { style: { background: '#fff', borderRadius: '10px', overflow: 'hidden', boxShadow: '0 4px 16px -4px rgba(10,37,64,.15)' } },
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '12px', padding: '12px 16px', fontSize: '12px', borderBottom: '1px solid rgba(11,26,38,.06)' } },
								el('div', { style: { fontWeight: 900, fontSize: '22px', color: '#1B77A7', minWidth: '28px' } }, '14'),
								el('div', { style: { flex: 1, fontWeight: 700, color: '#1B77A7', fontSize: '13px' } }, 'Secche di Tor di Cala'),
								el('div', { style: { background: 'rgba(27,119,167,.1)', color: '#1B77A7', padding: '2px 8px', borderRadius: '999px', fontSize: '10px' } }, 'OWD+'),
								el('div', { style: { background: '#1B77A7', color: '#fff', padding: '5px 10px', borderRadius: '999px', fontSize: '11px', fontWeight: 600 } }, 'Prenota')
							),
							el('div', { style: { display: 'flex', alignItems: 'center', gap: '12px', padding: '12px 16px', fontSize: '12px' } },
								el('div', { style: { fontWeight: 900, fontSize: '22px', color: '#1B77A7', minWidth: '28px' } }, '21'),
								el('div', { style: { flex: 1, fontWeight: 700, color: '#1B77A7', fontSize: '13px' } }, 'Corso OWD — Sessione 3'),
								el('div', { style: { background: 'rgba(38,203,251,.12)', color: '#006f8a', padding: '2px 8px', borderRadius: '999px', fontSize: '10px' } }, 'Corso'),
								el('div', { style: { background: '#1B77A7', color: '#fff', padding: '5px 10px', borderRadius: '999px', fontSize: '11px', fontWeight: 600 } }, 'Scopri')
							)
						),
						el('div', { style: { fontSize: '10px', opacity: .55, marginTop: '10px', fontFamily: 'monospace' } },
							'calypso/calendario · max ' + (a.max_items || 6) +
							(a.show_uscite ? ' · uscite' : '') +
							(a.show_eventi ? ' · eventi' : '') +
							(a.show_corsi  ? ' · corsi'  : '')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/hero-home — controlli completi
		   ════════════════════════════════════════════ */
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

		/* ════════════════════════════════════════════
		   calypso/promo-card — card personalizzabile
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/promo-card') {
			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: 'format-image',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

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

					var controls = InspectorControls ? el(InspectorControls, {},

						/* ── Immagine ── */
						el(PanelBody, { title: 'Immagine', initialOpen: true },
							mediaBtn,
							a.image_id ? el(Button, {
								onClick: function () { set({ image_id: 0 }); },
								variant: 'link',
								isDestructive: true,
								style: { marginBottom: '12px' }
							}, 'Rimuovi immagine') : null,
							el(TextControl, {
								label: 'Alt text',
								value: a.image_alt || '',
								onChange: function (v) { set({ image_alt: v }); }
							}),
							el(RangeControl, {
								label: 'Altezza area immagine (px)',
								value: a.image_height || 200,
								min: 80,
								max: 600,
								step: 10,
								onChange: function (v) { set({ image_height: v || 200 }); }
							}),
							SelectControl ? el(SelectControl, {
								label: 'Object fit',
								value: a.image_object_fit || 'cover',
								options: [
									{ value: 'cover',   label: 'Cover (riempie)' },
									{ value: 'contain', label: 'Contain (mostra tutto)' },
									{ value: 'fill',    label: 'Fill (distorce)' },
									{ value: 'none',    label: 'Nessuno' }
								],
								onChange: function (v) { set({ image_object_fit: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Allineamento immagine',
								value: a.image_object_pos || 'center center',
								options: [
									{ value: 'center center', label: 'Centro' },
									{ value: 'top center',    label: 'Alto' },
									{ value: 'bottom center', label: 'Basso' },
									{ value: 'left center',   label: 'Sinistra' },
									{ value: 'right center',  label: 'Destra' }
								],
								onChange: function (v) { set({ image_object_pos: v }); }
							}) : null
						),

						/* ── Overlay ── */
						el(PanelBody, { title: 'Overlay immagine', initialOpen: false },
							el(TextControl, {
								label: 'Testo overlay (vuoto = nascosto)',
								value: a.overlay_text || '',
								onChange: function (v) { set({ overlay_text: v }); }
							}),
							el(TextControl, {
								label: 'Sfondo overlay (CSS — rgba OK)',
								value: a.overlay_bg || 'rgba(6,24,38,0.6)',
								onChange: function (v) { set({ overlay_bg: v }); }
							}),
							colorRow('Colore testo overlay', 'overlay_color'),
							el(RangeControl, {
								label: 'Font size overlay (px)',
								value: a.overlay_size || 10,
								min: 8,
								max: 20,
								step: 1,
								onChange: function (v) { set({ overlay_size: v || 10 }); }
							})
						),

						/* ── Contenuto ── */
						el(PanelBody, { title: 'Contenuto', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow / Numero',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextControl, {
								label: 'Titolo',
								value: a.title || '',
								onChange: function (v) { set({ title: v }); }
							}),
							el(TextareaControl, {
								label: 'Descrizione',
								value: a.description || '',
								rows: 3,
								onChange: function (v) { set({ description: v }); }
							}),
							el(TextControl, {
								label: 'Testo link',
								value: a.link_text || '',
								onChange: function (v) { set({ link_text: v }); }
							}),
							el(TextControl, {
								label: 'URL link',
								value: a.link_url || '',
								type: 'url',
								onChange: function (v) { set({ link_url: v }); }
							}),
							el(ToggleControl, {
								label: 'Apri in nuova scheda',
								checked: !!a.link_new_tab,
								onChange: function (v) { set({ link_new_tab: v }); }
							})
						),

						/* ── Stile card ── */
						el(PanelBody, { title: 'Stile card', initialOpen: false },
							colorRow('Sfondo card', 'card_bg'),
							el(RangeControl, {
								label: 'Border radius (px)',
								value: a.card_radius !== undefined ? a.card_radius : 16,
								min: 0,
								max: 48,
								step: 2,
								onChange: function (v) { set({ card_radius: v }); }
							}),
							el(RangeControl, {
								label: 'Padding corpo (px)',
								value: a.card_padding !== undefined ? a.card_padding : 24,
								min: 0,
								max: 64,
								step: 4,
								onChange: function (v) { set({ card_padding: v }); }
							}),
							el(ToggleControl, {
								label: 'Ombra',
								checked: a.card_shadow !== false,
								onChange: function (v) { set({ card_shadow: v }); }
							})
						),

						/* ── Tipografia eyebrow ── */
						el(PanelBody, { title: 'Tipografia — Eyebrow', initialOpen: false },
							colorRow('Colore', 'eyebrow_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.eyebrow_size || 13,
								min: 8,
								max: 32,
								step: 1,
								onChange: function (v) { set({ eyebrow_size: v || 13 }); }
							})
						),

						/* ── Tipografia titolo ── */
						el(PanelBody, { title: 'Tipografia — Titolo', initialOpen: false },
							colorRow('Colore', 'title_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.title_size || 42,
								min: 16,
								max: 100,
								step: 2,
								onChange: function (v) { set({ title_size: v || 42 }); }
							}),
							SelectControl ? el(SelectControl, {
								label: 'Font weight',
								value: a.title_weight || '900',
								options: [
									{ value: '400', label: 'Regular (400)' },
									{ value: '600', label: 'SemiBold (600)' },
									{ value: '700', label: 'Bold (700)' },
									{ value: '800', label: 'ExtraBold (800)' },
									{ value: '900', label: 'Black (900)' }
								],
								onChange: function (v) { set({ title_weight: v }); }
							}) : null,
							SelectControl ? el(SelectControl, {
								label: 'Text transform',
								value: a.title_transform || 'uppercase',
								options: [
									{ value: 'uppercase',  label: 'MAIUSCOLO' },
									{ value: 'none',       label: 'Normale' },
									{ value: 'capitalize', label: 'Prima Lettera' }
								],
								onChange: function (v) { set({ title_transform: v }); }
							}) : null,
							el(TextControl, {
								label: 'Font family (vuoto = eredita tema)',
								help: 'Es: "Montserrat", sans-serif',
								value: a.title_font || '',
								onChange: function (v) { set({ title_font: v }); }
							})
						),

						/* ── Tipografia descrizione ── */
						el(PanelBody, { title: 'Tipografia — Descrizione', initialOpen: false },
							colorRow('Colore', 'desc_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.desc_size || 14,
								min: 10,
								max: 24,
								step: 1,
								onChange: function (v) { set({ desc_size: v || 14 }); }
							})
						),

						/* ── Link ── */
						el(PanelBody, { title: 'Tipografia — Link', initialOpen: false },
							colorRow('Colore', 'link_color'),
							el(RangeControl, {
								label: 'Font size (px)',
								value: a.link_size || 13,
								min: 10,
								max: 24,
								step: 1,
								onChange: function (v) { set({ link_size: v || 13 }); }
							})
						)

					) : null;

					/* Preview nel canvas */
					var previewRadius = (a.card_radius !== undefined ? a.card_radius : 16) + 'px';
					var previewPad    = (a.card_padding !== undefined ? a.card_padding : 24) + 'px';
					var previewH      = Math.min(a.image_height || 200, 280) + 'px';

					var preview = el('div', {
						style: {
							maxWidth: '300px',
							borderRadius: previewRadius,
							overflow: 'hidden',
							background: a.card_bg || '#ffffff',
							fontFamily: 'system-ui, sans-serif',
							boxShadow: a.card_shadow !== false ? '0 8px 32px -8px rgba(6,24,38,.2)' : 'none'
						}
					},
						/* Image area */
						el('div', {
							style: {
								height: previewH,
								background: a.image_id ? '#b8d4e0' : '#d5e8ef',
								position: 'relative',
								overflow: 'hidden',
								display: 'flex',
								alignItems: 'flex-end'
							}
						},
							a.image_id ? el('div', {
								style: { position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#1B77A7', fontSize: '12px', opacity: .7, pointerEvents: 'none' }
							}, '📷 img #' + a.image_id) : null,
							a.overlay_text ? el('div', {
								style: {
									position: 'relative',
									width: '100%',
									padding: '8px 14px',
									background: a.overlay_bg || 'rgba(6,24,38,0.6)',
									color: a.overlay_color || '#fff',
									fontSize: (a.overlay_size || 10) + 'px',
									fontWeight: 700,
									letterSpacing: '.14em',
									textTransform: 'uppercase'
								}
							}, a.overlay_text) : null
						),
						/* Body */
						el('div', { style: { padding: previewPad } },
							a.eyebrow ? el('div', {
								style: { color: a.eyebrow_color || '#1B77A7', fontSize: (a.eyebrow_size || 13) + 'px', fontWeight: 700, marginBottom: '6px' }
							}, a.eyebrow) : null,
							a.title ? el('div', {
								style: {
									color: a.title_color || '#061826',
									fontSize: Math.min(a.title_size || 42, 36) + 'px',
									fontWeight: a.title_weight || '900',
									textTransform: a.title_transform || 'uppercase',
									lineHeight: 1,
									marginBottom: '10px',
									fontFamily: a.title_font || 'inherit'
								}
							}, a.title) : null,
							a.description ? el('div', {
								style: { color: a.desc_color || '#3d5a6c', fontSize: (a.desc_size || 14) + 'px', lineHeight: 1.5, marginBottom: '14px' }
							}, a.description) : null,
							el('div', {
								style: { color: a.link_color || '#1B77A7', fontSize: (a.link_size || 13) + 'px', fontWeight: 600 }
							}, (a.link_text || 'Scopri') + ' →')
						)
					);

					return el(Fragment, {}, controls, preview);
				},
				save: function () { return null; },
			});
			return;
		}

		/* ════════════════════════════════════════════
		   calypso/sezione — sezione generica con InnerBlocks
		   ════════════════════════════════════════════ */
		if (info.name === 'calypso/sezione') {
			var InnerBlocks = blockEditor ? blockEditor.InnerBlocks : null;
			if (!InnerBlocks) return;

			blocks.registerBlockType(info.name, {
				title: info.title,
				category: 'calypso',
				icon: info.icon || 'layout',
				attributes: info.attributes || {},
				edit: function (props) {
					var a   = props.attributes;
					var set = props.setAttributes;

					function colorRow(label, key) {
						return el('div', { style: { marginBottom: '12px' } },
							el('p', { style: { fontSize: '11px', fontWeight: 500, color: '#1e1e1e', margin: '0 0 6px' } }, label),
							el(ColorPalette, {
								colors: getThemeColors(),
								value: a[key] || '',
								onChange: function (v) { var u = {}; u[key] = v || ''; set(u); }
							})
						);
					}

					var mediaBtn = (MediaUploadCheck && MediaUpload)
						? el(MediaUploadCheck, {},
							el(MediaUpload, {
								onSelect: function (media) { set({ bg_image_id: media.id }); },
								allowedTypes: ['image'],
								value: a.bg_image_id,
								render: function (ref) {
									return el(Button, {
										onClick: ref.open,
										variant: a.bg_image_id ? 'secondary' : 'primary',
										style: { marginBottom: '8px' }
									}, a.bg_image_id ? '⬡ Cambia sfondo' : '⬡ Scegli immagine sfondo');
								}
							}))
						: null;

					var controls = InspectorControls ? el(InspectorControls, {},

						el(PanelBody, { title: 'Intestazione', initialOpen: true },
							el(TextControl, {
								label: 'Eyebrow',
								value: a.eyebrow || '',
								onChange: function (v) { set({ eyebrow: v }); }
							}),
							el(TextareaControl, {
								label: 'Titolo (\\n per a capo)',
								value: a.title || '',
								rows: 3,
								onChange: function (v) { set({ title: v }); }
							}),
							el(TextControl, {
								label: 'Testo link intestazione (vuoto = nascosto)',
								value: a.header_link_text || '',
								onChange: function (v) { set({ header_link_text: v }); }
							}),
							a.header_link_text ? el(TextControl, {
								label: 'URL link intestazione',
								value: a.header_link_url || '',
								type: 'url',
								onChange: function (v) { set({ header_link_url: v }); }
							}) : null
						),

						el(PanelBody, { title: 'Sfondo', initialOpen: false },
							colorRow('Colore sfondo', 'bg_color'),
							mediaBtn,
							a.bg_image_id ? el(Button, {
								onClick: function () { set({ bg_image_id: 0 }); },
								variant: 'link',
								isDestructive: true,
								style: { marginBottom: '8px' }
							}, 'Rimuovi immagine') : null
						),

						el(PanelBody, { title: 'Tipografia', initialOpen: false },
							colorRow('Colore eyebrow / link', 'eyebrow_color'),
							colorRow('Colore titolo', 'title_color'),
							el(RangeControl, {
								label: 'Dimensione titolo (px)',
								value: a.title_size || 76,
								min: 20,
								max: 120,
								step: 2,
								onChange: function (v) { set({ title_size: v || 76 }); }
							})
						),

						el(PanelBody, { title: 'Spaziatura', initialOpen: false },
							el(RangeControl, {
								label: 'Padding verticale (px)',
								value: a.padding_y || 80,
								min: 0,
								max: 200,
								step: 8,
								onChange: function (v) { set({ padding_y: v || 80 }); }
							}),
							el(RangeControl, {
								label: 'Larghezza massima (px)',
								value: a.max_width || 1320,
								min: 400,
								max: 1920,
								step: 20,
								onChange: function (v) { set({ max_width: v || 1320 }); }
							})
						)

					) : null;

					var titleLines = (a.title || '').split('\\n');
					var hasHeader  = a.eyebrow || a.title || (a.header_link_text && a.header_link_url);

					return el(Fragment, {},
						controls,
						el('section', {
							style: { backgroundColor: a.bg_color || '#dff4f8', fontFamily: 'system-ui,sans-serif' }
						},
							el('div', {
								style: {
									maxWidth: (a.max_width || 1320) + 'px',
									margin: '0 auto',
									padding: (a.padding_y || 80) + 'px 48px'
								}
							},
								hasHeader ? el('div', {
									style: { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', gap: '24px', marginBottom: '48px', flexWrap: 'wrap' }
								},
									el('div', {},
										a.eyebrow ? el('span', {
											style: { display: 'block', fontWeight: 600, letterSpacing: '.16em', textTransform: 'uppercase', fontSize: '13px', color: a.eyebrow_color || '#1B77A7', marginBottom: '16px' }
										}, a.eyebrow) : null,
										a.title ? el('h2', {
											style: { fontSize: Math.min(a.title_size || 76, 60) + 'px', lineHeight: .95, color: a.title_color || '#1B77A7', margin: 0, fontWeight: 900 }
										}, titleLines.join(' · ')) : null
									),
									(a.header_link_text) ? el('span', {
										style: { flexShrink: 0, display: 'inline-flex', alignItems: 'center', gap: '8px', fontSize: '14px', fontWeight: 600, color: a.eyebrow_color || '#1B77A7' }
									}, a.header_link_text + ' →') : null
								) : null,
								el(InnerBlocks, {})
							)
						)
					);
				},
				save: function () {
					return el(InnerBlocks.Content, {});
				},
			});
			return;
		}

		/* ════════════════════════════════════════════
		   Blocchi generici
		   ════════════════════════════════════════════ */
		var hasInsideHero = info.attributes && info.attributes.inside_hero !== undefined;

		blocks.registerBlockType(info.name, {
			title: info.title,
			category: 'calypso',
			icon: info.icon || 'grid-view',
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
