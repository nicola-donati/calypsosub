<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Settings_Pages {

	/* ── Configurazione campi per sezione ─────────────────────────────────── */

	private static function config(): array {
		return [

			'docenti' => [
				'cpt'   => 'calypso_docente',
				'label' => 'Docenti',
				'groups' => [
					'Hero — Testi' => [
						'hero_badge'       => [ 'label' => 'Badge ruolo (testo, es. "Istruttore")', 'default' => 'Istruttore' ],
					],
					'Hero — Design' => [
						'design_hero_bg'           => [ 'label' => 'Sfondo hero',                    'default' => '#1B77A7',             'type' => 'color' ],
						'design_hero_badge_bg'     => [ 'label' => 'Sfondo badge ruolo',             'default' => '#ff6b4a',             'type' => 'color' ],
						'design_hero_badge_color'  => [ 'label' => 'Testo badge ruolo',              'default' => '#ffffff',             'type' => 'color' ],
						'design_hero_sopr_bg'      => [ 'label' => 'Sfondo badge soprannome',  'default' => '#2a6fa8',             'type' => 'color' ],
						'design_hero_sopr_color'   => [ 'label' => 'Testo badge soprannome',         'default' => '#ffffff',             'type' => 'color' ],
						'design_hero_name_size'    => [ 'label' => 'Font size nome (px)',            'default' => '96',                  'type' => 'number' ],
						'design_hero_name_color'   => [ 'label' => 'Colore nome',                   'default' => '#ffffff',             'type' => 'color' ],
						'design_hero_sur_color'    => [ 'label' => 'Colore cognome',                 'default' => '#26CBFB',             'type' => 'color' ],
						'design_hero_role_color'   => [ 'label' => 'Colore ruolo',                  'default' => '#ffffff',             'type' => 'color' ],
						'design_hero_exp_color'    => [ 'label' => 'Colore valore anni esp.',        'default' => '#26CBFB',             'type' => 'color' ],
					],
					'Sezione Bio — Testi' => [
						'bio_eyebrow'      => [ 'label' => 'Eyebrow',                             'default' => 'Bio' ],
						'bio_heading'      => [ 'label' => 'Titolo (usa {nome} come segnaposto)',  'default' => 'Chi è {nome}.' ],
					],
					'Sezione Specializzazioni — Testi' => [
						'specs_eyebrow'    => [ 'label' => 'Eyebrow',  'default' => 'Specializzazioni' ],
						'specs_heading'    => [ 'label' => 'Titolo',   'default' => 'Cosa porta sotto.' ],
					],
					'Sezione Certificazioni — Testi' => [
						'certs_eyebrow'    => [ 'label' => 'Eyebrow',  'default' => 'Certificazioni' ],
						'certs_heading'    => [ 'label' => 'Titolo',   'default' => 'Brevetti.' ],
					],
					'Sezione dettaglio — Design' => [
						'design_detail_bg'       => [ 'label' => 'Sfondo sezione',        'default' => '#f6f1e6',            'type' => 'color' ],
						'design_detail_eyebrow'  => [ 'label' => 'Colore eyebrow',        'default' => '#1B77A7',            'type' => 'color' ],
						'design_detail_heading'  => [ 'label' => 'Colore heading',        'default' => '#1B77A7',            'type' => 'color' ],
						'design_detail_prose'    => [ 'label' => 'Colore testo bio',      'default' => '#1a2f40',  'type' => 'color' ],
						'design_spec_bg'         => [ 'label' => 'Sfondo chip spec.',     'default' => '#ffffff',            'type' => 'color' ],
						'design_spec_color'      => [ 'label' => 'Testo chip spec.',      'default' => '#1B77A7',            'type' => 'color' ],
						'design_spec_dot'        => [ 'label' => 'Puntino chip spec.',    'default' => '#26CBFB',            'type' => 'color' ],
						'design_brev_bg'         => [ 'label' => 'Sfondo chip brevetti',  'default' => '#1B77A7',            'type' => 'color' ],
						'design_brev_color'      => [ 'label' => 'Testo chip brevetti',   'default' => '#ffffff',            'type' => 'color' ],
					],
					'VCard — Testi' => [
						'vcard_label_email'   => [ 'label' => 'Etichetta Email',      'default' => 'Email' ],
						'vcard_label_phone'   => [ 'label' => 'Etichetta Telefono',   'default' => 'Telefono' ],
						'vcard_label_wp'      => [ 'label' => 'Etichetta Account WP', 'default' => 'WordPress' ],
					],
					'VCard — Design' => [
						'design_vcard_bg'         => [ 'label' => 'Sfondo vcard',         'default' => '#1B77A7',  'type' => 'color' ],
						'design_vcard_name_size'  => [ 'label' => 'Font size nome (px)',   'default' => '28',       'type' => 'number' ],
						'design_vcard_name_color' => [ 'label' => 'Colore nome',           'default' => '#ffffff',  'type' => 'color' ],
						'design_vcard_role_color' => [ 'label' => 'Colore ruolo',          'default' => '#26CBFB',  'type' => 'color' ],
					],
					'Galleria foto — Testi' => [
						'gallery_eyebrow'  => [ 'label' => 'Eyebrow',                                'default' => 'Galleria foto' ],
						'gallery_heading'  => [ 'label' => 'Titolo (usa {nome} come segnaposto)',     'default' => 'Dal logbook di {nome}.' ],
					],
					'Galleria foto — Design' => [
						'design_gallery_bg'      => [ 'label' => 'Sfondo sezione',   'default' => '#cfe9ee',  'type' => 'color' ],
						'design_gallery_eyebrow' => [ 'label' => 'Colore eyebrow',   'default' => '#1B77A7',  'type' => 'color' ],
						'design_gallery_heading' => [ 'label' => 'Colore heading',   'default' => '#1B77A7',  'type' => 'color' ],
					],
					'Archivio — Design' => [
						'overlay_color'          => [ 'label' => 'Colore overlay hero archivio',         'default' => '#061826',  'type' => 'color' ],
						'overlay_opacity'        => [ 'label' => 'Opacità overlay hero archivio (0-100)', 'default' => '88',       'type' => 'number' ],
						'design_arch_body_bg'    => [ 'label' => 'Sfondo corpo archivio',                'default' => '#f6f1e6',  'type' => 'color' ],
						'design_arch_card_bg'    => [ 'label' => 'Sfondo card docente',                  'default' => '#ffffff',  'type' => 'color' ],
						'design_arch_card_radius'=> [ 'label' => 'Border radius card (px)',              'default' => '12',       'type' => 'number' ],
						'design_arch_name_color' => [ 'label' => 'Colore nome in card',                  'default' => '#1B77A7',  'type' => 'color' ],
						'design_arch_role_color' => [ 'label' => 'Colore ruolo in card',                 'default' => '#1B77A7',  'type' => 'color' ],
						'design_arch_bio_color'  => [ 'label' => 'Colore bio in card',                  'default' => '#283d4d',  'type' => 'color' ],
					],
				],
			],

			'uscite' => [
				'cpt'   => 'calypso_uscita',
				'label' => 'Uscite',
				'groups' => [
					'Pagina singola — Hero design' => [
						'design_hero_bg'               => [ 'label' => 'Sfondo hero e sidebar',                'default' => '#0a2540',                'type' => 'color' ],
						'design_hero_overlay_color'    => [ 'label' => 'Colore overlay (se immagine di sfondo attiva)', 'default' => '#061826',        'type' => 'color' ],
						'design_badge_bg'              => [ 'label' => 'Sfondo badge e bottone principale',    'default' => '#ff6b4a',                'type' => 'color' ],
						'design_hero_badge_color'      => [ 'label' => 'Colore testo badge',                   'default' => '#ffffff',                'type' => 'color' ],
						'design_hero_badge_size'       => [ 'label' => 'Dimensione testo badge (px)',           'default' => '14',                     'type' => 'number' ],
						'design_hero_badge_weight'     => [ 'label' => 'Peso testo badge (100-900)',            'default' => '600',                    'type' => 'number' ],
						'design_hero_title_color'      => [ 'label' => 'Colore titolo (h1)',                    'default' => '#ffffff',                'type' => 'color' ],
						'design_hero_title_size'       => [ 'label' => 'Dimensione titolo (px, max clamp)',     'default' => '96',                     'type' => 'number' ],
						'design_hero_title_weight'     => [ 'label' => 'Peso titolo (100-900)',                 'default' => '700',                    'type' => 'number' ],
						'design_hero_title_font'       => [ 'label' => 'Font-family titolo (vuoto = eredita)',  'default' => '' ],
						'design_hero_sub_color'        => [ 'label' => 'Colore nota "proposta più volte"',      'default' => '#26CBFB',                'type' => 'color' ],
						'design_hero_sub_size'         => [ 'label' => 'Dimensione nota (px)',                  'default' => '16',                     'type' => 'number' ],
						'design_hero_sub_weight'       => [ 'label' => 'Peso nota (100-900)',                   'default' => '600',                    'type' => 'number' ],
						'design_hero_lead_color'       => [ 'label' => 'Colore testo lead (descrizione breve)', 'default' => '#ffffff',                'type' => 'color' ],
						'design_hero_lead_opacity'     => [ 'label' => 'Opacità testo lead (0-100)',            'default' => '85',                     'type' => 'number' ],
						'design_hero_lead_size'        => [ 'label' => 'Dimensione testo lead (px)',           'default' => '18',                     'type' => 'number' ],
						'design_hero_lead_font'        => [ 'label' => 'Font-family lead (vuoto = eredita)',    'default' => '' ],
						'design_hero_stat_bg'          => [ 'label' => 'Sfondo box statistiche hero',           'default' => 'rgba(6,24,38,.35)', 'type' => 'color' ],
						'design_hero_stat_label_color' => [ 'label' => 'Colore etichetta box statistiche',      'default' => '#26CBFB',                'type' => 'color' ],
						'design_hero_stat_value_color' => [ 'label' => 'Colore valore box statistiche',         'default' => '#ffffff',                'type' => 'color' ],
					],
					'Design — Colori sezioni e sidebar' => [
						'design_eyebrow'         => [ 'label' => 'Colore eyebrow e titoli sezione', 'default' => '#1B77A7',  'type' => 'color' ],
						'design_sidebar_accent'  => [ 'label' => 'Colore accento sidebar (labels)', 'default' => '#26CBFB',  'type' => 'color' ],
						'design_related_bg'      => [ 'label' => 'Sfondo sezione uscite correlate', 'default' => '#f6f1e6',  'type' => 'color' ],
					],
					'Archivio — Hero' => [
						'archive_eyebrow'       => [ 'label' => 'Eyebrow  (usa {anno} come segnaposto)', 'default' => 'Calendario · stagione {anno}' ],
						'archive_h1'            => [ 'label' => 'Titolo H1 (HTML consentito: &lt;em&gt; &lt;br&gt;)', 'default' => 'Ogni sabato,<br>da aprile<br>a <em>ottobre.</em>', 'type' => 'textarea' ],
						'archive_lead'          => [ 'label' => 'Testo introduttivo', 'default' => 'Le uscite del club partono dal porto di Cala Galera o di Porto Santo Stefano. Due immersioni a giornata, pranzo a bordo, rientro alle 17. Posti limitati, prenotazione obbligatoria 48 ore prima.', 'type' => 'textarea' ],
					],
					'Archivio — Filtri' => [
						'filtri_label'         => [ 'label' => 'Titolo sidebar filtri',  'default' => 'Filtra' ],
						'filtri_livello'       => [ 'label' => 'Gruppo Livello',          'default' => 'LIVELLO' ],
						'filtri_localita'      => [ 'label' => 'Gruppo Località',         'default' => 'LOCALITÀ' ],
						'filtri_disponibilita' => [ 'label' => 'Gruppo Disponibilità',   'default' => 'DISPONIBILITÀ' ],
						'filtri_liberi'        => [ 'label' => 'Opzione: posti liberi',  'default' => 'Posti liberi' ],
						'filtri_attesa'        => [ 'label' => "Opzione: lista d'attesa", 'default' => "Lista d'attesa" ],
						'btn_applica'          => [ 'label' => 'Bottone Applica',         'default' => 'Applica' ],
						'btn_rimuovi'          => [ 'label' => 'Bottone Rimuovi filtri',  'default' => 'Rimuovi' ],
					],
					'Archivio — Lista uscite' => [
						'label_ritrovo'        => [ 'label' => 'Etichetta colonna Ritrovo',    'default' => 'RITROVO' ],
						'btn_prenota'          => [ 'label' => 'Bottone Prenota',               'default' => 'Prenota' ],
						'btn_attesa'           => [ 'label' => "Bottone Lista d'attesa",        'default' => "Lista d'attesa" ],
						'btn_esaurito'         => [ 'label' => 'Bottone Esaurito (disabilitato)', 'default' => 'Esaurito' ],
						'empty_title'          => [ 'label' => 'Messaggio nessuna uscita',      'default' => 'Nessuna uscita trovata.' ],
						'empty_sub'            => [ 'label' => 'Sottotitolo messaggio vuoto',   'default' => 'Prova a modificare i filtri.' ],
						'empty_show_all'       => [ 'label' => 'Link mostra tutte',              'default' => 'Mostra tutte' ],
					],
					'Pagina singola — Sezioni' => [
						'badge'                   => [ 'label' => 'Badge hero (fallback se nessun livello assegnato)', 'default' => 'Itinerario in barca' ],
						'hero_note_piu_date'      => [ 'label' => 'Nota "più date" (mostrata se >1 data disponibile)', 'default' => 'Proposta più volte durante la stagione — vedi le date disponibili' ],
						'breadcrumb_archive'      => [ 'label' => 'Voce breadcrumb archivio',       'default' => 'Uscite' ],
						'sec_descrizione_eyebrow' => [ 'label' => 'Eyebrow sezione Descrizione',     'default' => "L'uscita" ],
						'sec_descrizione_heading' => [ 'label' => 'Titolo sezione Descrizione',      'default' => 'Descrizione.' ],
						'sec_immersioni_eyebrow'  => [ 'label' => 'Eyebrow sezione Immersioni',      'default' => 'Le immersioni' ],
						'sec_programma_eyebrow'   => [ 'label' => 'Eyebrow sezione Programma',       'default' => 'Il programma della giornata' ],
						'sec_fauna_eyebrow'       => [ 'label' => 'Eyebrow sezione Fauna',           'default' => 'Cosa vedrai sotto' ],
						'sec_fauna_heading'       => [ 'label' => 'Titolo sezione Fauna',            'default' => 'La vita della secca.' ],
						'sec_galleria_eyebrow'    => [ 'label' => 'Eyebrow sezione Galleria',        'default' => 'Galleria' ],
						'sec_galleria_heading'    => [ 'label' => 'Titolo sezione Galleria',         'default' => 'Dagli ultimi tuffi.' ],
						'sec_incluso'             => [ 'label' => 'Titolo box Inclusi (sidebar)',    'default' => "Inclusi nell'uscita" ],
						'sec_cosa_portare'        => [ 'label' => 'Titolo box Cosa portare (sidebar)', 'default' => 'Cosa portare' ],
						'sec_cancellazione'       => [ 'label' => 'Titolo box Cancellazione (sidebar)', 'default' => 'Cancellazione' ],
					],
					'Pagina singola — Sidebar' => [
						'sidebar_eyebrow' => [ 'label' => 'Eyebrow sidebar',          'default' => 'Itinerario' ],
						'sidebar_title'   => [ 'label' => 'Titolo sidebar',           'default' => 'Informazioni pratiche' ],
						'stat_ritrovo'    => [ 'label' => 'Etichetta Ritrovo',        'default' => 'Ritrovo' ],
						'stat_imbarco'    => [ 'label' => 'Etichetta Imbarco',        'default' => 'Imbarco' ],
						'stat_rientro'    => [ 'label' => 'Etichetta Rientro',        'default' => 'Rientro previsto' ],
						'stat_immersioni' => [ 'label' => 'Etichetta Immersioni',     'default' => 'Immersioni' ],
						'stat_difficolta' => [ 'label' => 'Etichetta Difficoltà',     'default' => 'Difficoltà' ],
						'inizi_label'     => [ 'label' => 'Etichetta Prossime date',  'default' => 'Prossime date' ],
						'inizi_note'      => [ 'label' => 'Nota sotto "Prossime date"', 'default' => 'Stesso itinerario, date diverse durante la stagione.' ],
						'btn_prenota_ora' => [ 'label' => 'Bottone Prenota',          'default' => 'Prenota' ],
					],
					'Pagina singola — Uscite correlate' => [
						'related_eyebrow'   => [ 'label' => 'Eyebrow',                     'default' => 'Continua a scendere' ],
						'related_heading'   => [ 'label' => 'Titolo sezione',              'default' => 'Altre uscite in calendario.' ],
						'related_link'      => [ 'label' => 'Link calendario completo',    'default' => 'Calendario completo →' ],
						'related_card_link' => [ 'label' => 'Link card uscita',            'default' => "Scopri l'uscita →" ],
					],
				],
				'campi_prenotazione_default' => [
					[ 'nome' => 'accompagnatori', 'label' => 'Accompagnatori', 'obbligatorio' => '1' ],
				],
			],

			'corsi' => [
				'cpt'   => 'calypso_corso',
				'label' => 'Corsi',
				'groups' => [
					'Pagina singola — Hero design' => [
						'design_hero_bg'           => [ 'label' => 'Sfondo hero e sidebar',                'default' => '#0a2540',  'type' => 'color' ],
						'design_hero_overlay_color'=> [ 'label' => 'Colore overlay (se immagine di sfondo attiva)', 'default' => '#061826', 'type' => 'color' ],
						'design_badge_bg'          => [ 'label' => 'Sfondo badge e bottone principale',    'default' => '#ff6b4a',  'type' => 'color' ],
						'design_hero_badge_color'  => [ 'label' => 'Colore testo badge',                   'default' => '#ffffff',  'type' => 'color' ],
						'design_hero_badge_size'   => [ 'label' => 'Dimensione testo badge (px)',          'default' => '14',      'type' => 'number' ],
						'design_hero_badge_weight' => [ 'label' => 'Peso testo badge (100-900)',           'default' => '600',     'type' => 'number' ],
						'design_hero_title_color'  => [ 'label' => 'Colore titolo (h1)',                    'default' => '#ffffff',  'type' => 'color' ],
						'design_hero_title_size'   => [ 'label' => 'Dimensione titolo (px, max clamp)',    'default' => '96',      'type' => 'number' ],
						'design_hero_title_weight'=> [ 'label' => 'Peso titolo (100-900)',                 'default' => '700',     'type' => 'number' ],
						'design_hero_title_font'   => [ 'label' => 'Font-family titolo (vuoto = eredita)', 'default' => '' ],
						'design_hero_sub_color'    => [ 'label' => 'Colore sottotitolo',                   'default' => '#26CBFB',  'type' => 'color' ],
						'design_hero_sub_size'     => [ 'label' => 'Dimensione sottotitolo (px, max clamp)', 'default' => '72',   'type' => 'number' ],
						'design_hero_sub_weight'   => [ 'label' => 'Peso sottotitolo (100-900)',           'default' => '700',     'type' => 'number' ],
						'design_hero_lead_color'   => [ 'label' => 'Colore testo lead',                    'default' => '#ffffff',  'type' => 'color' ],
						'design_hero_lead_opacity' => [ 'label' => 'Opacità testo lead (0-100)',            'default' => '85',      'type' => 'number' ],
						'design_hero_lead_size'    => [ 'label' => 'Dimensione testo lead (px)',           'default' => '18',      'type' => 'number' ],
						'design_hero_lead_font'    => [ 'label' => 'Font-family lead (vuoto = eredita)',   'default' => '' ],
					],
					'Design — Colori sezioni e sidebar' => [
						'design_eyebrow'         => [ 'label' => 'Colore eyebrow e titoli sezione', 'default' => '#1B77A7',  'type' => 'color' ],
						'design_sidebar_accent'  => [ 'label' => 'Colore accento sidebar (labels)', 'default' => '#26CBFB',  'type' => 'color' ],
						'design_related_bg'      => [ 'label' => 'Sfondo sezione corsi correlati',  'default' => '#f6f1e6',  'type' => 'color' ],
					],
					'Hero' => [
						'breadcrumb_archive' => [ 'label' => 'Voce breadcrumb archivio', 'default' => 'Corsi' ],
					],
					'Sezione Descrizione' => [
						'sec_descrizione_eyebrow' => [ 'label' => 'Eyebrow',  'default' => 'Il corso' ],
						'sec_descrizione_heading' => [ 'label' => 'Titolo',   'default' => 'Descrizione.' ],
					],
					'Sezione Programma' => [
						'sec_programma_eyebrow' => [ 'label' => 'Eyebrow',  'default' => 'Il programma' ],
					],
					'Sezione Competenze' => [
						'sec_competenze_eyebrow' => [ 'label' => 'Eyebrow', 'default' => 'Cosa imparerai' ],
					],
					'Sezione Materiale' => [
						'sec_materiale_eyebrow' => [ 'label' => 'Eyebrow', 'default' => 'Materiale' ],
						'sec_materiale_heading' => [ 'label' => 'Titolo',  'default' => "Cosa è incluso." ],
					],
					'Sezione Docenti' => [
						'sec_docenti_eyebrow' => [ 'label' => 'Eyebrow', 'default' => 'I docenti' ],
						'sec_docenti_heading' => [ 'label' => 'Titolo',  'default' => 'I nostri docenti.' ],
					],
					'Sidebar — In sintesi' => [
						'sidebar_title'          => [ 'label' => 'Titolo sidebar',              'default' => 'In sintesi' ],
						'sidebar_requisiti_label'=> [ 'label' => 'Etichetta Requisiti',         'default' => 'Requisiti' ],
						'stat_durata'     => [ 'label' => 'Etichetta Durata',            'default' => 'Durata' ],
						'stat_immersioni' => [ 'label' => 'Etichetta Immersioni',        'default' => 'Immersioni' ],
						'stat_profondita' => [ 'label' => 'Etichetta Profondità',        'default' => 'Profondità' ],
						'stat_periodo'    => [ 'label' => 'Etichetta Periodo',           'default' => 'Periodo' ],
						'inizi_label'     => [ 'label' => 'Etichetta Prossime lezioni',  'default' => 'Prossime lezioni' ],
						'btn_iscrivi'     => [ 'label' => 'Bottone Iscriviti',           'default' => 'Iscriviti al corso →' ],
					],
					'Corsi correlati' => [
						'related_eyebrow' => [ 'label' => 'Eyebrow',              'default' => 'Continua a scendere' ],
						'related_heading' => [ 'label' => 'Titolo sezione',       'default' => 'Altri corsi.' ],
						'related_link'    => [ 'label' => 'Link tutti i corsi',   'default' => 'Tutti i corsi →' ],
						'related_card_link' => [ 'label' => 'Link card corso',    'default' => 'Scopri il corso →' ],
					],
				],
				'campi_prenotazione_default' => [],
			],

			'eventi' => [
				'cpt'   => 'calypso_evento',
				'label' => 'Eventi',
				'groups' => [
					'Pagina singola — Hero design' => [
						'design_accent'            => [ 'label' => 'Colore accento (badge, card head, bottone, posti)', 'default' => '#1B77A7',  'type' => 'color' ],
						'design_deep'              => [ 'label' => 'Colore primario scuro (infobar, bottone secondario)', 'default' => '#0a2540',  'type' => 'color' ],
						'design_body_bg'           => [ 'label' => 'Sfondo corpo pagina',                                'default' => '#ffffff',  'type' => 'color' ],
						'design_hero_overlay_color'=> [ 'label' => 'Colore overlay su immagine hero',                    'default' => '#061826',  'type' => 'color' ],
						'design_hero_badge_color'  => [ 'label' => 'Colore testo badge',                                 'default' => '#ffffff',  'type' => 'color' ],
						'design_hero_badge_size'   => [ 'label' => 'Dimensione testo badge (px)',                       'default' => '14',       'type' => 'number' ],
						'design_hero_badge_weight' => [ 'label' => 'Peso testo badge (100-900)',                        'default' => '600',      'type' => 'number' ],
						'design_hero_title_color'  => [ 'label' => 'Colore titolo (h1)',                                  'default' => '#ffffff',  'type' => 'color' ],
						'design_hero_title_size'   => [ 'label' => 'Dimensione titolo (px, max clamp)',                 'default' => '72',       'type' => 'number' ],
						'design_hero_title_weight'=> [ 'label' => 'Peso titolo (100-900)',                              'default' => '700',      'type' => 'number' ],
						'design_hero_title_font'   => [ 'label' => 'Font-family titolo (vuoto = eredita)',              'default' => '' ],
						'design_hero_sub_color'    => [ 'label' => 'Colore sottotitolo',                                 'default' => '#ffffff',  'type' => 'color' ],
						'design_hero_sub_opacity'  => [ 'label' => 'Opacità sottotitolo (0-100)',                       'default' => '85',       'type' => 'number' ],
						'design_hero_sub_size'     => [ 'label' => 'Dimensione sottotitolo (px)',                       'default' => '18',       'type' => 'number' ],
						'design_hero_sub_weight'   => [ 'label' => 'Peso sottotitolo (100-900)',                        'default' => '400',      'type' => 'number' ],
					],
					'Hero' => [
						'badge' => [ 'label' => 'Badge hero', 'default' => 'Evento' ],
					],
					'Sezioni' => [
						'sec_descrizione' => [ 'label' => 'Titolo sezione Descrizione', 'default' => 'Descrizione' ],
						'sec_date'        => [ 'label' => 'Titolo sezione Date',        'default' => 'Date' ],
					],
					'Sidebar prenotazione' => [
						'card_title'         => [ 'label' => 'Titolo card',                    'default' => 'Partecipa' ],
						'btn_iscriviti'      => [ 'label' => 'Bottone Iscriviti',               'default' => 'Iscriviti' ],
						'btn_area_personale' => [ 'label' => 'Bottone Area personale',          'default' => 'Area personale' ],
						'label_posti'        => [ 'label' => 'Etichetta posti disponibili',     'default' => 'posti disponibili' ],
						'label_allergie'     => [ 'label' => 'Etichetta campo note',            'default' => 'Allergie / note' ],
						'msg_gia_iscritto'   => [ 'label' => 'Messaggio già iscritto',          'default' => '✓ Sei già iscritto a questo evento.' ],
						'msg_lista_avviso'   => [ 'label' => 'Avviso lista attesa',              'default' => "Posti esauriti — puoi iscriverti in lista d'attesa." ],
						'msg_esauriti'       => [ 'label' => 'Messaggio posti esauriti',         'default' => 'Posti esauriti.' ],
						'msg_accedi_cta'     => [ 'label' => "Testo invito al login",            'default' => "Accedi per iscriverti all'evento." ],
					],
				],
				'campi_prenotazione_default' => [],
			],
		];
	}

	/* ── Palette colori tema (per gli swatch nei campi color) ─────────────────── */

	private static function palette(): array {
		if ( function_exists( 'wp_get_global_settings' ) ) {
			$settings      = wp_get_global_settings( [ 'color', 'palette' ] );
			$theme_palette = $settings['theme'] ?? [];
			if ( $theme_palette ) {
				return array_map( static fn( $c ) => [
					'slug'  => $c['slug']  ?? '',
					'name'  => $c['name']  ?? '',
					'color' => $c['color'] ?? '',
				], $theme_palette );
			}
		}
		// Fallback se il tema non espone theme.json (allineato a theme.json del tema Calypso Sub).
		return [
			[ 'slug' => 'abyss',      'name' => 'Abyss',       'color' => '#061826' ],
			[ 'slug' => 'deep',       'name' => 'Deep',        'color' => '#0a2540' ],
			[ 'slug' => 'wave',       'name' => 'Wave',        'color' => '#1B77A7' ],
			[ 'slug' => 'aqua',       'name' => 'Aqua',        'color' => '#26CBFB' ],
			[ 'slug' => 'foam',       'name' => 'Foam',        'color' => '#cfe9ee' ],
			[ 'slug' => 'gold',       'name' => 'Gold',        'color' => '#E9BF26' ],
			[ 'slug' => 'bone',       'name' => 'Bone',        'color' => '#f6f1e6' ],
			[ 'slug' => 'ink',        'name' => 'Ink',         'color' => '#0b1a26' ],
			[ 'slug' => 'coral-deep', 'name' => 'Coral deep',  'color' => '#ff6b4a' ],
			[ 'slug' => 'white',      'name' => 'White',       'color' => '#ffffff' ],
		];
	}

	/* ── Registrazione ──────────────────────────────────────────────────────── */

	public function init(): void {
		add_action( 'admin_menu',                                    [ $this, 'register_pages' ] );
		add_action( 'admin_post_calypsosub_save_settings',           [ $this, 'save' ] );
		add_action( 'admin_enqueue_scripts',                         [ $this, 'enqueue_style' ] );
	}

	public function register_pages(): void {
		foreach ( self::config() as $section => $cfg ) {
			add_submenu_page(
				'edit.php?post_type=' . $cfg['cpt'],
				sprintf( 'Impostazioni — %s', $cfg['label'] ),
				'Impostazioni',
				'calypsosub_manage',
				'calypsosub-settings-' . $section,
				fn() => $this->render( $section )
			);
		}
	}

	public function enqueue_style( string $hook ): void {
		if ( ! str_contains( $hook, 'calypsosub-settings-' ) ) return;
		wp_add_inline_style( 'wp-admin', '
			.cso-settings-wrap{max-width:760px;margin-top:24px}
			.cso-settings-group{background:#fff;border:1px solid #ddd;border-radius:6px;margin-bottom:24px;padding:0}
			.cso-settings-group h3{margin:0;padding:12px 18px;font-size:13px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid #eee;color:#1d6f9c;background:#f8fbfd;border-radius:6px 6px 0 0}
			.cso-settings-table{width:100%;border-collapse:collapse}
			.cso-settings-table tr:not(:last-child) td,.cso-settings-table tr:not(:last-child) th{border-bottom:1px solid #f0f0f0}
			.cso-settings-table th{width:220px;padding:12px 18px;font-size:12px;color:#555;font-weight:600;vertical-align:top;text-align:left}
			.cso-settings-table td{padding:10px 18px}
			.cso-settings-table input[type=text],.cso-settings-table textarea{width:100%;font-size:13px}
			.cso-settings-table textarea{min-height:72px}
			.cso-settings-table input[type=number]{width:110px;font-size:13px}
			.cso-settings-submit{margin-top:8px}
			.cso-color-swatches{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
			.cso-color-swatch{width:28px;height:28px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.15);cursor:pointer;padding:0;position:relative;display:inline-block;flex-shrink:0}
			.cso-color-swatch.is-active{box-shadow:0 0 0 2px #fff,0 0 0 4px #1d6f9c}
			.cso-color-swatch--custom{overflow:hidden}
			.cso-color-swatch--custom::after{content:"\270E";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;color:rgba(255,255,255,.9);text-shadow:0 1px 2px rgba(0,0,0,.6);pointer-events:none}
			.cso-color-swatch--custom input[type=color]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;border:none;padding:0}
		' );
	}

	/* ── Render pagina ──────────────────────────────────────────────────────── */

	private function render( string $section ): void {
		if ( ! current_user_can( 'calypsosub_manage' ) ) return;
		$cfg     = self::config()[ $section ];
		$opts    = (array) get_option( 'calypsosub_opts_' . $section, [] );
		$saved   = isset( $_GET['saved'] );
		$palette = self::palette();
		?>
		<div class="wrap cso-settings-wrap">
			<h1><?php echo esc_html( 'Impostazioni — ' . $cfg['label'] ); ?></h1>
			<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>Impostazioni salvate.</p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'calypsosub_settings_' . $section, '_cso_nonce' ); ?>
				<input type="hidden" name="action" value="calypsosub_save_settings">
				<input type="hidden" name="_cso_section" value="<?php echo esc_attr( $section ); ?>">

				<?php foreach ( $cfg['groups'] as $group_label => $fields ) : ?>
				<div class="cso-settings-group">
					<h3><?php echo esc_html( $group_label ); ?></h3>
					<table class="cso-settings-table">
						<?php foreach ( $fields as $key => $field ) :
							$type = $field['type'] ?? 'text';
							$val  = $opts[ $key ] ?? '';
						?>
						<tr>
							<th><label for="cso-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php if ( $type === 'textarea' ) : ?>
								<textarea id="cso-<?php echo esc_attr( $key ); ?>"
								          name="cso_opts[<?php echo esc_attr( $key ); ?>]"
								          placeholder="<?php echo esc_attr( $field['default'] ); ?>"><?php echo esc_textarea( $val ); ?></textarea>
								<?php elseif ( $type === 'color' ) :
									$current   = $val ?: $field['default'];
									$is_hexish = (bool) preg_match( '/^#/', $field['default'] );
									$is_custom = true;
									foreach ( $palette as $p ) {
										if ( $p['color'] !== '' && strcasecmp( $p['color'], $current ) === 0 ) { $is_custom = false; break; }
									}
								?>
								<div class="cso-color-field">
									<div class="cso-color-swatches">
										<?php foreach ( $palette as $p ) : ?>
										<button type="button"
										        class="cso-color-swatch<?php echo ( ! $is_custom && strcasecmp( $p['color'], $current ) === 0 ) ? ' is-active' : ''; ?>"
										        style="background:<?php echo esc_attr( $p['color'] ); ?>"
										        data-color="<?php echo esc_attr( $p['color'] ); ?>"
										        title="<?php echo esc_attr( $p['name'] ); ?>"
										        aria-label="<?php echo esc_attr( $p['name'] ); ?>"></button>
										<?php endforeach; ?>
										<label class="cso-color-swatch cso-color-swatch--custom<?php echo $is_custom ? ' is-active' : ''; ?>"
										       style="background:<?php echo esc_attr( $current ); ?>"
										       title="<?php esc_attr_e( 'Colore personalizzato', 'calypsosub' ); ?>">
											<?php if ( $is_hexish ) : ?>
												<input type="color" class="cso-color-native" value="<?php echo esc_attr( $current ); ?>">
											<?php endif; ?>
										</label>
										<?php if ( ! $is_hexish ) : ?>
										<input type="text" class="cso-color-text" value="<?php echo esc_attr( $current ); ?>"
										       placeholder="es. rgba(255,255,255,.08)" style="width:180px;font-size:12px">
										<?php endif; ?>
									</div>
									<input type="hidden"
									       id="cso-<?php echo esc_attr( $key ); ?>"
									       name="cso_opts[<?php echo esc_attr( $key ); ?>]"
									       class="cso-color-value"
									       value="<?php echo esc_attr( $current ); ?>">
								</div>
								<?php elseif ( $type === 'number' ) : ?>
								<input type="number"
								       id="cso-<?php echo esc_attr( $key ); ?>"
								       name="cso_opts[<?php echo esc_attr( $key ); ?>]"
								       value="<?php echo esc_attr( $val ); ?>"
								       placeholder="<?php echo esc_attr( $field['default'] ); ?>">
								<?php else : ?>
								<input type="text"
								       id="cso-<?php echo esc_attr( $key ); ?>"
								       name="cso_opts[<?php echo esc_attr( $key ); ?>]"
								       value="<?php echo esc_attr( $val ); ?>"
								       placeholder="<?php echo esc_attr( $field['default'] ); ?>">
								<?php endif; ?>
								<p class="description" style="margin:4px 0 0;font-size:11px;color:#888">
									Default: <em><?php echo esc_html( $field['default'] ); ?></em>
								</p>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
				<?php endforeach; ?>

				<div class="cso-settings-group">
					<h3><?php esc_html_e( 'Campi prenotazione (per il form CF7 collegato)', 'calypsosub' ); ?></h3>
					<div style="padding:14px 18px 4px">
						<p class="description" style="margin:0 0 12px;font-size:12px;color:#666">
							<?php esc_html_e( 'Elenca qui i nomi dei campi che il form CF7 categorizzato per questa sezione deve avere. Se segnati come "obbligatorio" e mancanti nel form CF7, vedrai un avviso nell\'editor di quel form.', 'calypsosub' ); ?>
						</p>
						<div id="cso-campi-pren-repeater">
							<?php
							$campi = (array) ( $opts['campi_prenotazione'] ?? ( $cfg['campi_prenotazione_default'] ?? [] ) );
							foreach ( $campi as $campo ) :
							?>
							<div class="cso-campo-pren-row" style="display:grid;grid-template-columns:1fr 1fr 140px 32px;gap:10px;align-items:end;margin-bottom:10px">
								<div>
									<label style="display:block;font-size:11px;font-weight:600;color:#444;margin-bottom:4px"><?php esc_html_e( 'Nome campo (CF7)', 'calypsosub' ); ?></label>
									<input type="text" name="cso_campi_pren_nome[]" value="<?php echo esc_attr( $campo['nome'] ?? '' ); ?>" placeholder="es. accompagnatori">
								</div>
								<div>
									<label style="display:block;font-size:11px;font-weight:600;color:#444;margin-bottom:4px"><?php esc_html_e( 'Etichetta', 'calypsosub' ); ?></label>
									<input type="text" name="cso_campi_pren_label[]" value="<?php echo esc_attr( $campo['label'] ?? '' ); ?>" placeholder="es. Accompagnatori">
								</div>
								<div>
									<label style="display:block;font-size:11px;font-weight:600;color:#444;margin-bottom:4px"><?php esc_html_e( 'Obbligatorio', 'calypsosub' ); ?></label>
									<select name="cso_campi_pren_obbligatorio[]">
										<option value="1" <?php selected( ( $campo['obbligatorio'] ?? '' ) === '1' ); ?>><?php esc_html_e( 'Sì', 'calypsosub' ); ?></option>
										<option value="" <?php selected( ( $campo['obbligatorio'] ?? '' ) !== '1' ); ?>><?php esc_html_e( 'No', 'calypsosub' ); ?></option>
									</select>
								</div>
								<button type="button" class="cso-campo-pren-remove button" style="height:30px">&#x2715;</button>
							</div>
							<?php endforeach; ?>
						</div>
						<button type="button" class="button" id="cso-campi-pren-add"><?php esc_html_e( '+ Aggiungi campo', 'calypsosub' ); ?></button>
					</div>
				</div>

				<script>
				(function () {
					document.getElementById('cso-campi-pren-add').addEventListener('click', function () {
						var row = document.createElement('div');
						row.className = 'cso-campo-pren-row';
						row.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 140px 32px;gap:10px;align-items:end;margin-bottom:10px';
						row.innerHTML =
							'<div><label style="display:block;font-size:11px;font-weight:600;color:#444;margin-bottom:4px"><?php echo esc_js( __( 'Nome campo (CF7)', 'calypsosub' ) ); ?></label>'
								+ '<input type="text" name="cso_campi_pren_nome[]" placeholder="es. accompagnatori"></div>'
							+ '<div><label style="display:block;font-size:11px;font-weight:600;color:#444;margin-bottom:4px"><?php echo esc_js( __( 'Etichetta', 'calypsosub' ) ); ?></label>'
								+ '<input type="text" name="cso_campi_pren_label[]" placeholder="es. Accompagnatori"></div>'
							+ '<div><label style="display:block;font-size:11px;font-weight:600;color:#444;margin-bottom:4px"><?php echo esc_js( __( 'Obbligatorio', 'calypsosub' ) ); ?></label>'
								+ '<select name="cso_campi_pren_obbligatorio[]"><option value="1"><?php echo esc_js( __( 'Sì', 'calypsosub' ) ); ?></option><option value="" selected><?php echo esc_js( __( 'No', 'calypsosub' ) ); ?></option></select></div>'
							+ '<button type="button" class="cso-campo-pren-remove button" style="height:30px">✕</button>';
						document.getElementById('cso-campi-pren-repeater').appendChild(row);
					});
					document.addEventListener('click', function (e) {
						if (e.target.classList.contains('cso-campo-pren-remove')) {
							e.target.closest('.cso-campo-pren-row').remove();
						}
					});

					document.querySelectorAll('.cso-color-field').forEach(function (field) {
						var hidden      = field.querySelector('.cso-color-value');
						var customLabel = field.querySelector('.cso-color-swatch--custom');
						var nativeInput = field.querySelector('.cso-color-native');
						var textInput   = field.querySelector('.cso-color-text');

						field.querySelectorAll('.cso-color-swatch:not(.cso-color-swatch--custom)').forEach(function (btn) {
							btn.addEventListener('click', function () {
								hidden.value = btn.dataset.color;
								if (textInput) textInput.value = btn.dataset.color;
								field.querySelectorAll('.cso-color-swatch').forEach(function (b) { b.classList.remove('is-active'); });
								btn.classList.add('is-active');
							});
						});

						if (nativeInput) {
							nativeInput.addEventListener('input', function () {
								hidden.value = nativeInput.value;
								customLabel.style.background = nativeInput.value;
								field.querySelectorAll('.cso-color-swatch').forEach(function (b) { b.classList.remove('is-active'); });
								customLabel.classList.add('is-active');
							});
						}

						if (textInput) {
							textInput.addEventListener('input', function () {
								hidden.value = textInput.value;
								customLabel.style.background = textInput.value;
								field.querySelectorAll('.cso-color-swatch').forEach(function (b) { b.classList.remove('is-active'); });
								customLabel.classList.add('is-active');
							});
						}
					});
				})();
				</script>

				<?php submit_button( 'Salva impostazioni', 'primary cso-settings-submit' ); ?>
			</form>
		</div>
		<?php
	}

	/* ── Salvataggio ────────────────────────────────────────────────────────── */

	public function save(): void {
		$section = sanitize_key( $_POST['_cso_section'] ?? '' );
		if ( ! $section || ! isset( self::config()[ $section ] ) ) wp_die( 'Sezione non valida.' );
		if ( ! current_user_can( 'calypsosub_manage' ) ) wp_die( 'Permesso negato.' );
		check_admin_referer( 'calypsosub_settings_' . $section, '_cso_nonce' );

		$cfg        = self::config()[ $section ];
		$all_fields = array_merge( ...array_values( $cfg['groups'] ) );
		$raw        = (array) ( $_POST['cso_opts'] ?? [] );
		$clean      = [];
		foreach ( $all_fields as $key => $field ) {
			$val  = wp_unslash( $raw[ $key ] ?? '' );
			$type = $field['type'] ?? 'text';
			if ( $type === 'color' ) {
				if ( preg_match( '/^#/', $field['default'] ) ) {
					$clean[ $key ] = sanitize_hex_color( $val ) ?? '';
				} else {
					// Colori non-hex (rgba/hsla, es. overlay/box translucidi): stessa allowlist dei campi testo liberi.
					$s = sanitize_text_field( $val );
					$clean[ $key ] = $s !== '' && preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([\d.,\s%]+\)|hsla?\([\d.,\s%]+\))$/i', $s ) ? $s : '';
				}
			} elseif ( $type === 'number' ) {
				$clean[ $key ] = (string) (int) $val;
			} elseif ( $type === 'textarea' ) {
				$clean[ $key ] = sanitize_textarea_field( $val );
			} else {
				$s = sanitize_text_field( $val );
				// free-form CSS color fields (defaults start with # or rgba/hsla): strict allowlist
				if ( $s !== '' && preg_match( '/^(#|rgba?|hsla?)/i', $field['default'] ) ) {
					$clean[ $key ] = preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([\d.,\s%]+\)|hsla?\([\d.,\s%]+\))$/i', $s ) ? $s : '';
				} else {
					$clean[ $key ] = $s;
				}
			}
		}

		$campi_nome  = (array) ( $_POST['cso_campi_pren_nome'] ?? [] );
		$campi_label = (array) ( $_POST['cso_campi_pren_label'] ?? [] );
		$campi_obbl  = (array) ( $_POST['cso_campi_pren_obbligatorio'] ?? [] );
		$campi_clean = [];
		foreach ( $campi_nome as $i => $nome ) {
			$nome = sanitize_key( wp_unslash( $nome ) );
			if ( $nome === '' ) continue;
			$campi_clean[] = [
				'nome'         => $nome,
				'label'        => sanitize_text_field( wp_unslash( $campi_label[ $i ] ?? '' ) ),
				'obbligatorio' => ( $campi_obbl[ $i ] ?? '' ) === '1' ? '1' : '',
			];
		}
		$clean['campi_prenotazione'] = $campi_clean;
		update_option( 'calypsosub_opts_' . $section, $clean );

		wp_redirect( add_query_arg( [ 'page' => 'calypsosub-settings-' . $section, 'saved' => '1' ],
			admin_url( 'edit.php?post_type=' . $cfg['cpt'] ) ) );
		exit;
	}
}
