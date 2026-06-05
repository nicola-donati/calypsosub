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
					'Hero' => [
						'hero_badge'       => [ 'label' => 'Badge nel hero (testo, es. "Istruttore")', 'default' => 'Istruttore' ],
					],
					'Sezione Bio' => [
						'bio_eyebrow'      => [ 'label' => 'Eyebrow',                          'default' => 'Bio' ],
						'bio_heading'      => [ 'label' => 'Titolo  (usa {nome} come segnaposto)', 'default' => 'Chi è {nome}.' ],
					],
					'Sezione Specializzazioni' => [
						'specs_eyebrow'    => [ 'label' => 'Eyebrow',  'default' => 'Specializzazioni' ],
						'specs_heading'    => [ 'label' => 'Titolo',   'default' => 'Cosa porta sotto.' ],
					],
					'Sezione Certificazioni' => [
						'certs_eyebrow'    => [ 'label' => 'Eyebrow',  'default' => 'Certificazioni' ],
						'certs_heading'    => [ 'label' => 'Titolo',   'default' => 'Brevetti.' ],
					],
					'Galleria foto' => [
						'gallery_eyebrow'  => [ 'label' => 'Eyebrow',  'default' => 'Galleria foto' ],
						'gallery_heading'  => [ 'label' => 'Titolo  (usa {nome} come segnaposto)', 'default' => 'Dal logbook di {nome}.' ],
					],
					'VCard — etichette contatti' => [
						'vcard_label_email'   => [ 'label' => 'Etichetta Email',    'default' => 'Email' ],
						'vcard_label_phone'   => [ 'label' => 'Etichetta Telefono', 'default' => 'Telefono' ],
						'vcard_label_wp'      => [ 'label' => 'Etichetta Account WP', 'default' => 'WordPress' ],
					],
				],
			],

			'uscite' => [
				'cpt'   => 'calypso_uscita',
				'label' => 'Uscite',
				'groups' => [
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
						'badge'              => [ 'label' => 'Badge hero',                 'default' => 'Uscita subacquea' ],
						'sec_descrizione'    => [ 'label' => 'Titolo sezione Descrizione', 'default' => 'Descrizione' ],
						'sec_date'           => [ 'label' => 'Titolo sezione Date',        'default' => 'Date disponibili' ],
						'sec_ritrovo'        => [ 'label' => 'Titolo sezione Ritrovo',     'default' => 'Punto di ritrovo' ],
						'sec_incluso'        => [ 'label' => 'Titolo sezione Incluso',     'default' => "Cosa è incluso" ],
						'sec_cosa_portare'   => [ 'label' => 'Titolo sezione Cosa portare', 'default' => 'Cosa portare' ],
						'sec_cancellazione'  => [ 'label' => 'Titolo sezione Cancellazione', 'default' => 'Politica di cancellazione' ],
					],
					'Pagina singola — Sidebar prenotazione' => [
						'card_title'          => [ 'label' => 'Titolo card prenotazione',      'default' => 'Prenota' ],
						'btn_prenota_ora'     => [ 'label' => 'Bottone Prenota ora',            'default' => 'Prenota ora' ],
						'btn_area_personale'  => [ 'label' => 'Bottone Area personale',         'default' => 'Area personale' ],
						'label_posti'         => [ 'label' => 'Etichetta posti disponibili',    'default' => 'posti disponibili' ],
						'label_accompagnatori'=> [ 'label' => 'Etichetta campo accompagnatori', 'default' => 'N° accompagnatori' ],
						'label_allergie'      => [ 'label' => 'Etichetta campo allergie',       'default' => 'Allergie / note mediche' ],
						'msg_gia_prenotato'   => [ 'label' => 'Messaggio già prenotato',        'default' => '✓ Hai già una prenotazione attiva per questa uscita.' ],
						'msg_lista_avviso'    => [ 'label' => 'Avviso lista attesa',             'default' => "Posti esauriti — puoi iscriverti in lista d'attesa." ],
						'msg_esauriti'        => [ 'label' => 'Messaggio posti esauriti',        'default' => 'Posti esauriti per questa uscita.' ],
						'msg_accedi_cta'      => [ 'label' => 'Testo invito al login',           'default' => 'Accedi per prenotare questa uscita.' ],
					],
				],
			],

			'corsi' => [
				'cpt'   => 'calypso_corso',
				'label' => 'Corsi',
				'groups' => [
					'Hero' => [
						'breadcrumb_archive' => [ 'label' => 'Voce breadcrumb archivio', 'default' => 'Corsi' ],
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
						'sidebar_title'   => [ 'label' => 'Titolo sidebar',              'default' => 'In sintesi' ],
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
			],

			'eventi' => [
				'cpt'   => 'calypso_evento',
				'label' => 'Eventi',
				'groups' => [
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
			],
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
				'manage_options',
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
			.cso-settings-submit{margin-top:8px}
		' );
	}

	/* ── Render pagina ──────────────────────────────────────────────────────── */

	private function render( string $section ): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$cfg  = self::config()[ $section ];
		$opts = (array) get_option( 'calypsosub_opts_' . $section, [] );
		$saved = isset( $_GET['saved'] );
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

				<?php submit_button( 'Salva impostazioni', 'primary cso-settings-submit' ); ?>
			</form>
		</div>
		<?php
	}

	/* ── Salvataggio ────────────────────────────────────────────────────────── */

	public function save(): void {
		$section = sanitize_key( $_POST['_cso_section'] ?? '' );
		if ( ! $section || ! isset( self::config()[ $section ] ) ) wp_die( 'Sezione non valida.' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Permesso negato.' );
		check_admin_referer( 'calypsosub_settings_' . $section, '_cso_nonce' );

		$cfg      = self::config()[ $section ];
		$all_keys = array_merge( ...array_values( array_map( 'array_keys', $cfg['groups'] ) ) );
		$raw      = (array) ( $_POST['cso_opts'] ?? [] );
		$clean    = [];
		foreach ( $all_keys as $key ) {
			$val = $raw[ $key ] ?? '';
			$clean[ $key ] = sanitize_textarea_field( wp_unslash( $val ) );
		}

		update_option( 'calypsosub_opts_' . $section, $clean );

		wp_redirect( add_query_arg( [ 'page' => 'calypsosub-settings-' . $section, 'saved' => '1' ],
			admin_url( 'edit.php?post_type=' . $cfg['cpt'] ) ) );
		exit;
	}
}
