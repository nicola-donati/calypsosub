<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin page "Corsi → Importa da Excel".
 * Step 1: upload an .xlsx/.csv file and see a preview (nothing is written).
 * Step 2: confirm, and courses are created or updated.
 * Courses are matched by slug first, then by title. Empty cells and
 * "[DA DEFINIRE]" placeholders never overwrite existing data.
 */
class Calypsosub_Course_Importer {

	const PAGE_SLUG      = 'calypsosub-import-corsi';
	const CAPABILITY     = 'calypsosub_manage';
	const NONCE_ACTION   = 'calypsosub_course_import';
	const TRANSIENT_BASE = 'calypsosub_course_import_';
	const MAX_FILE_BYTES = 5 * 1024 * 1024;

	/** Meta keys that keep line breaks. */
	const MULTILINE_META = [ '_corso_competenze', '_corso_requisiti', '_corso_materiale', '_corso_desc_breve' ];

	private Calypsosub_Course_Spreadsheet_Reader $reader;
	private Calypsosub_Course_Import_Mapper $mapper;

	public function __construct( ?Calypsosub_Course_Spreadsheet_Reader $reader = null, ?Calypsosub_Course_Import_Mapper $mapper = null ) {
		$this->reader = $reader ?? new Calypsosub_Course_Spreadsheet_Reader();
		$this->mapper = $mapper ?? new Calypsosub_Course_Import_Mapper();
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_post_calypsosub_course_import_template', [ $this, 'download_template' ] );
	}

	public function register_page(): void {
		add_submenu_page(
			'edit.php?post_type=calypso_corso',
			__( 'Importa corsi da Excel', 'calypsosub' ),
			__( 'Importa da Excel', 'calypsosub' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );
		}
		echo '<div class="wrap calypso-import">';
		echo '<h1>' . esc_html__( 'Importa corsi da Excel o CSV', 'calypsosub' ) . '</h1>';
		$this->render_styles();

		$step = isset( $_POST['calypso_import_step'] ) ? sanitize_key( $_POST['calypso_import_step'] ) : '';
		if ( $step !== '' ) {
			check_admin_referer( self::NONCE_ACTION );
		}

		if ( $step === 'preview' ) {
			$this->handle_preview();
		} elseif ( $step === 'import' ) {
			$this->handle_import();
		} elseif ( $step === 'images' ) {
			$this->handle_images();
		} else {
			$this->render_upload_form();
		}
		echo '</div>';
	}

	/* ───────────── Step 0: form ───────────── */

	private function render_upload_form( string $error = '' ): void {
		if ( $error ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
		}
		$template_url = wp_nonce_url( admin_url( 'admin-post.php?action=calypsosub_course_import_template' ), self::NONCE_ACTION );
		?>
		<p class="calypso-import__intro">
			<?php esc_html_e( 'Carica il file dei corsi (.xlsx o .csv). Prima vedrai un\'anteprima: nessun corso viene creato o modificato finché non confermi.', 'calypsosub' ); ?>
		</p>
		<ul class="calypso-import__rules">
			<li><?php esc_html_e( 'Nel file Excel viene letto il foglio «Corsi» (o il primo foglio). La prima riga deve contenere i nomi delle colonne.', 'calypsosub' ); ?></li>
			<li><?php esc_html_e( 'I corsi già presenti vengono riconosciuti dallo slug o, in mancanza, dal titolo, e vengono aggiornati.', 'calypsosub' ); ?></li>
			<li><?php esc_html_e( 'Le celle vuote e i segnaposto come [DA DEFINIRE] non sovrascrivono i dati già salvati.', 'calypsosub' ); ?></li>
			<li><?php esc_html_e( 'Per svuotare un campo del corso (per esempio "Materiale incluso") scrivi (vuoto) nella cella.', 'calypsosub' ); ?></li>
			<li><?php esc_html_e( 'Docenti e direttore vengono collegati per nome ai profili esistenti in Docenti.', 'calypsosub' ); ?></li>
		</ul>
		<p><a class="button" href="<?php echo esc_url( $template_url ); ?>"><?php esc_html_e( 'Scarica il modello CSV vuoto', 'calypsosub' ); ?></a></p>

		<form method="post" enctype="multipart/form-data" class="calypso-import__form">
			<?php wp_nonce_field( self::NONCE_ACTION ); ?>
			<input type="hidden" name="calypso_import_step" value="preview">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="calypso-import-file"><?php esc_html_e( 'File', 'calypsosub' ); ?></label></th>
					<td><input type="file" id="calypso-import-file" name="calypso_import_file" accept=".xlsx,.csv" required></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Quali righe', 'calypsosub' ); ?></th>
					<td><label><input type="checkbox" name="calypso_only_approved" value="1" checked>
						<?php esc_html_e( 'Solo i corsi con «Sì» nella colonna Approvato', 'calypsosub' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Corsi nuovi', 'calypsosub' ); ?></th>
					<td>
						<label><input type="radio" name="calypso_new_status" value="draft" checked> <?php esc_html_e( 'Crea come bozza', 'calypsosub' ); ?></label><br>
						<label><input type="radio" name="calypso_new_status" value="publish"> <?php esc_html_e( 'Pubblica subito', 'calypsosub' ); ?></label>
						<p class="description"><?php esc_html_e( 'I corsi già esistenti mantengono il loro stato.', 'calypsosub' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Corsi esistenti', 'calypsosub' ); ?></th>
					<td><label><input type="checkbox" name="calypso_update_existing" value="1" checked>
						<?php esc_html_e( 'Aggiorna i corsi già presenti sul sito', 'calypsosub' ); ?></label></td>
				</tr>
			</table>
			<?php submit_button( __( 'Mostra anteprima', 'calypsosub' ) ); ?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'Immagini dei corsi', 'calypsosub' ); ?></h2>
		<p class="calypso-import__intro">
			<?php esc_html_e( 'Carica uno ZIP di immagini (.jpg, .png o .webp) con il nome dello slug del corso. corso-base.jpg diventa l\'immagine in evidenza, solo se il corso non ne ha già una. corso-base-brevetto.png diventa l\'immagine del brevetto mostrata nella pagina del corso.', 'calypsosub' ); ?>
		</p>
		<form method="post" enctype="multipart/form-data" class="calypso-import__form">
			<?php wp_nonce_field( self::NONCE_ACTION ); ?>
			<input type="hidden" name="calypso_import_step" value="images">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="calypso-import-images"><?php esc_html_e( 'File ZIP', 'calypsosub' ); ?></label></th>
					<td><input type="file" id="calypso-import-images" name="calypso_import_images" accept=".zip" required></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Immagini già presenti', 'calypsosub' ); ?></th>
					<td><label><input type="checkbox" name="calypso_images_overwrite" value="1">
						<?php esc_html_e( 'Sostituisci le immagini già presenti (sconsigliato)', 'calypsosub' ); ?></label>
						<p class="description"><?php esc_html_e( 'Se il caricamento si interrompe per i tempi del server, ricarica lo stesso ZIP: le immagini già impostate vengono saltate.', 'calypsosub' ); ?></p></td>
				</tr>
			</table>
			<?php submit_button( __( 'Carica immagini', 'calypsosub' ), 'secondary' ); ?>
		</form>
		<?php
	}

	/* ───────────── Immagini da ZIP ───────────── */

	const IMAGE_EXTENSIONS   = [ 'jpg', 'jpeg', 'png', 'webp' ];
	const MAX_ZIP_BYTES      = 40 * 1024 * 1024;
	const MAX_IMAGE_BYTES    = 8 * 1024 * 1024;

	private function handle_images(): void {
		$file = $_FILES['calypso_import_images'] ?? null;
		if ( ! $file || ! empty( $file['error'] ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			$this->render_upload_form( __( 'Caricamento dello ZIP non riuscito: controlla che non superi il limite di upload del server.', 'calypsosub' ) );
			return;
		}
		if ( strtolower( pathinfo( (string) $file['name'], PATHINFO_EXTENSION ) ) !== 'zip' || (int) $file['size'] > self::MAX_ZIP_BYTES ) {
			$this->render_upload_form( __( 'Serve un file .zip di massimo 40 MB.', 'calypsosub' ) );
			return;
		}
		if ( ! class_exists( 'ZipArchive' ) ) {
			$this->render_upload_form( __( 'Il server non ha l\'estensione PHP zip.', 'calypsosub' ) );
			return;
		}
		$zip = new ZipArchive();
		if ( $zip->open( $file['tmp_name'] ) !== true ) {
			$this->render_upload_form( __( 'Lo ZIP non è leggibile.', 'calypsosub' ) );
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}

		$overwrite = ! empty( $_POST['calypso_images_overwrite'] );
		$index     = $this->course_slug_index();
		$results   = [];
		$by_course  = [];

		/* Collect files per course: "<slug>.jpg" = featured image, "<slug>-brevetto.png" = card image. */
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$stat = $zip->statIndex( $i );
			$path = (string) $stat['name'];
			$name = basename( $path );
			if ( $name === '' || str_starts_with( $name, '.' ) || str_contains( $path, '__MACOSX' ) || str_ends_with( $path, '/' ) ) {
				continue;
			}
			$ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, self::IMAGE_EXTENSIONS, true ) ) {
				$results[] = [ $name, 0, __( 'Saltato: non è un\'immagine', 'calypsosub' ) ];
				continue;
			}
			if ( (int) $stat['size'] > self::MAX_IMAGE_BYTES ) {
				$results[] = [ $name, 0, __( 'Saltato: immagine troppo grande', 'calypsosub' ) ];
				continue;
			}
			$base = pathinfo( $name, PATHINFO_FILENAME );
			$kind = 'featured';
			if ( preg_match( '/-brevetto$/i', $base ) ) {
				$kind = 'brevetto';
				$base = preg_replace( '/-brevetto$/i', '', $base );
			}
			$post_id = $index[ sanitize_title( $base ) ] ?? 0;
			if ( ! $post_id ) {
				$results[] = [ $name, 0, __( 'Nessun corso con questo slug', 'calypsosub' ) ];
				continue;
			}
			$by_course[ $post_id ][ $kind ] = [ 'index' => $i, 'name' => $name, 'ext' => $ext ];
		}

		foreach ( $by_course as $post_id => $files ) {
			$title    = get_the_title( $post_id );
			$featured = 0;

			if ( isset( $files['featured'] ) ) {
				$f = $files['featured'];
				if ( has_post_thumbnail( $post_id ) && ! $overwrite ) {
					$results[] = [ $f['name'], $post_id, __( 'Ha già un\'immagine in evidenza: lasciata com\'è', 'calypsosub' ) ];
				} else {
					/* translators: %s: course title */
					$featured = $this->sideload_from_zip( $zip, $f, $post_id, sprintf( __( 'Corso %s', 'calypsosub' ), $title ), $results );
					if ( $featured ) {
						set_post_thumbnail( $post_id, $featured );
						$results[] = [ $f['name'], $post_id, __( 'Immagine in evidenza impostata', 'calypsosub' ) ];
					}
				}
			}

			$has_card = (int) get_post_meta( $post_id, '_corso_brevetto_img_id', true ) > 0;
			if ( $has_card && ! $overwrite ) {
				if ( isset( $files['brevetto'] ) ) {
					$results[] = [ $files['brevetto']['name'], $post_id, __( 'Ha già l\'immagine del brevetto: lasciata com\'è', 'calypsosub' ) ];
				}
				continue;
			}
			/* translators: %s: course title */
			$label = sprintf( __( 'Brevetto %s', 'calypsosub' ), $title );
			$card  = 0;
			if ( isset( $files['brevetto'] ) ) {
				$card = $this->sideload_from_zip( $zip, $files['brevetto'], $post_id, $label, $results );
			} elseif ( $featured ) {
				$card = $featured;
			}
			if ( $card ) {
				update_post_meta( $post_id, '_corso_brevetto_img_id', $card );
				$results[] = [ $files['brevetto']['name'] ?? $files['featured']['name'], $post_id, __( 'Immagine del brevetto impostata', 'calypsosub' ) ];
			}
		}
		$zip->close();

		$set  = [ __( 'Immagine in evidenza impostata', 'calypsosub' ), __( 'Immagine del brevetto impostata', 'calypsosub' ) ];
		$done = count( array_filter( $results, fn( $r ) => in_array( $r[2], $set, true ) ) );
		echo '<div class="notice notice-success inline"><p>' . esc_html( sprintf(
			/* translators: %d: number of images */
			_n( 'Impostata %d immagine.', 'Impostate %d immagini.', $done, 'calypsosub' ),
			$done
		) ) . '</p></div>';
		echo '<table class="widefat striped calypso-import__table"><thead><tr><th>' . esc_html__( 'File', 'calypsosub' ) . '</th><th>' . esc_html__( 'Corso', 'calypsosub' ) . '</th><th>' . esc_html__( 'Esito', 'calypsosub' ) . '</th></tr></thead><tbody>';
		foreach ( $results as [ $name, $post_id, $msg ] ) {
			$course = $post_id
				? '<a href="' . esc_url( get_edit_post_link( (int) $post_id ) ?? '' ) . '">' . esc_html( get_the_title( (int) $post_id ) ) . '</a>'
				: '—';
			echo '<tr><td><code>' . esc_html( $name ) . '</code></td><td>' . $course . '</td><td>' . esc_html( $msg ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput -- $course escaped above
		}
		echo '</tbody></table>';
		echo '<p><a class="button" href="' . esc_url( $this->page_url() ) . '">' . esc_html__( 'Torna all\'importazione', 'calypsosub' ) . '</a></p>';
	}

	/**
	 * Copies one ZIP entry to a temp file and adds it to the media library.
	 *
	 * @param array{index: int, name: string, ext: string} $file
	 * @return int Attachment ID, 0 on error (the error is added to $results).
	 */
	private function sideload_from_zip( ZipArchive $zip, array $file, int $post_id, string $label, array &$results ): int {
		$tmp = wp_tempnam( $file['name'] );
		if ( ! $tmp || file_put_contents( $tmp, $zip->getFromIndex( $file['index'] ) ) === false ) {
			$results[] = [ $file['name'], $post_id, __( 'Errore: file temporaneo non scrivibile', 'calypsosub' ) ];
			return 0;
		}
		$att = media_handle_sideload( [ 'name' => sanitize_file_name( $file['name'] ), 'tmp_name' => $tmp ], $post_id, $label );
		if ( is_wp_error( $att ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			$results[] = [ $file['name'], $post_id, __( 'Errore:', 'calypsosub' ) . ' ' . $att->get_error_message() ];
			return 0;
		}
		update_post_meta( $att, '_wp_attachment_image_alt', $label );
		return (int) $att;
	}

	/** @return array<string, int> Course slug (and sanitized title) → post ID. */
	private function course_slug_index(): array {
		$posts = get_posts( [
			'post_type'      => 'calypso_corso',
			'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		] );
		$index = [];
		foreach ( $posts as $p ) {
			if ( $p->post_name !== '' ) {
				$index[ $p->post_name ] ??= $p->ID;
			}
		}
		foreach ( $posts as $p ) {
			$index[ sanitize_title( $p->post_title ) ] ??= $p->ID;
		}
		return $index;
	}

	/* ───────────── Step 1: preview ───────────── */

	private function handle_preview(): void {
		$file = $_FILES['calypso_import_file'] ?? null;
		if ( ! $file || ! empty( $file['error'] ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			$this->render_upload_form( __( 'Caricamento non riuscito: scegli un file e riprova.', 'calypsosub' ) );
			return;
		}
		if ( (int) $file['size'] > self::MAX_FILE_BYTES ) {
			$this->render_upload_form( __( 'Il file supera i 5 MB.', 'calypsosub' ) );
			return;
		}
		$ext = strtolower( pathinfo( (string) $file['name'], PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, [ 'xlsx', 'csv' ], true ) ) {
			$this->render_upload_form( __( 'Formato non supportato: usa un file .xlsx o .csv.', 'calypsosub' ) );
			return;
		}

		try {
			$rows = $this->reader->read( $file['tmp_name'], $ext );
		} catch ( RuntimeException $e ) {
			$this->render_upload_form( $e->getMessage() );
			return;
		}

		$options = [
			'only_approved'   => ! empty( $_POST['calypso_only_approved'] ),
			'new_status'      => ( $_POST['calypso_new_status'] ?? 'draft' ) === 'publish' ? 'publish' : 'draft',
			'update_existing' => ! empty( $_POST['calypso_update_existing'] ),
		];
		$mapped = $this->mapper->map_rows( $rows, $options['only_approved'] );
		$plan   = $this->build_plan( $mapped['courses'], $options );

		set_transient( $this->transient_key(), [ 'plan' => $plan, 'options' => $options ], HOUR_IN_SECONDS );

		$counts = array_count_values( array_column( $plan, 'action' ) );
		echo '<h2>' . esc_html__( 'Anteprima', 'calypsosub' ) . '</h2>';
		if ( $options['only_approved'] && ! $mapped['has_approval_column'] ) {
			echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Il file non ha la colonna «Approvato»: verranno importate tutte le righe.', 'calypsosub' ) . '</p></div>';
		}
		printf(
			'<p class="calypso-import__summary">%s</p>',
			esc_html( sprintf(
				/* translators: 1: created, 2: updated, 3: skipped */
				__( 'Da creare: %1$d · Da aggiornare: %2$d · Saltati: %3$d', 'calypsosub' ),
				$counts['create'] ?? 0, $counts['update'] ?? 0, $counts['skip'] ?? 0
			) )
		);
		$this->render_plan_table( $plan );

		if ( ( $counts['create'] ?? 0 ) + ( $counts['update'] ?? 0 ) > 0 ) {
			?>
			<form method="post">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<input type="hidden" name="calypso_import_step" value="import">
				<p>
					<?php submit_button( __( 'Conferma e importa', 'calypsosub' ), 'primary', 'submit', false ); ?>
					<a class="button" href="<?php echo esc_url( $this->page_url() ); ?>"><?php esc_html_e( 'Annulla', 'calypsosub' ); ?></a>
				</p>
			</form>
			<?php
		} else {
			echo '<p><a class="button" href="' . esc_url( $this->page_url() ) . '">' . esc_html__( 'Carica un altro file', 'calypsosub' ) . '</a></p>';
		}
	}

	/**
	 * Decides, for each mapped course, whether it will be created, updated or skipped.
	 *
	 * @param array<int, array<string, mixed>> $courses
	 * @return array<int, array<string, mixed>>
	 */
	public function build_plan( array $courses, array $options ): array {
		$index   = $this->existing_courses_index();
		$docenti = $this->docenti_index();
		$plan    = [];

		foreach ( $courses as $course ) {
			$item = [
				'course'   => $course,
				'action'   => 'skip',
				'post_id'  => 0,
				'reason'   => $course['skip'] ?? '',
				'notes'    => [],
			];
			if ( empty( $course['skip'] ) ) {
				$slug     = $course['slug'] !== '' ? sanitize_title( $course['slug'] ) : '';
				$by_slug  = $slug !== '' ? ( $index['slug'][ $slug ] ?? 0 ) : 0;
				$by_title = $index['title'][ mb_strtolower( $course['title'] ) ] ?? 0;
				$match    = $by_slug ?: $by_title;

				if ( $match ) {
					if ( $options['update_existing'] ) {
						$item['action']  = 'update';
						$item['post_id'] = $match;
						$current_slug    = get_post_field( 'post_name', $match );
						if ( $slug !== '' && $current_slug !== $slug ) {
							/* translators: 1: old slug, 2: new slug */
							$item['notes'][] = sprintf( __( 'L\'indirizzo cambia da /%1$s/ a /%2$s/ (WordPress reindirizza il vecchio).', 'calypsosub' ), $current_slug, $slug );
						}
					} else {
						$item['reason'] = __( 'Esiste già (aggiornamento disattivato)', 'calypsosub' );
					}
				} else {
					$item['action'] = 'create';
				}

				foreach ( $course['warnings'] as $field ) {
					/* translators: %s: field name */
					$item['notes'][] = sprintf( __( 'Il campo «%s» contiene ancora un segnaposto tra parentesi quadre.', 'calypsosub' ), $this->field_label( $field ) );
				}
				$names = array_merge( (array) ( $course['docenti'] ?? [] ), $course['direttore'] ? [ $course['direttore'] ] : [] );
				foreach ( $names as $name ) {
					if ( ! isset( $docenti[ mb_strtolower( $name ) ] ) ) {
						/* translators: %s: person name */
						$item['notes'][] = sprintf( __( 'Docente «%s» non trovato: non verrà collegato.', 'calypsosub' ), $name );
					}
				}
			}
			$plan[] = $item;
		}
		return $plan;
	}

	private function render_plan_table( array $plan ): void {
		$labels = [
			'create' => __( 'Crea', 'calypsosub' ),
			'update' => __( 'Aggiorna', 'calypsosub' ),
			'skip'   => __( 'Salta', 'calypsosub' ),
		];
		?>
		<table class="widefat striped calypso-import__table">
			<thead><tr>
				<th><?php esc_html_e( 'N.', 'calypsosub' ); ?></th>
				<th><?php esc_html_e( 'Corso', 'calypsosub' ); ?></th>
				<th><?php esc_html_e( 'Azione', 'calypsosub' ); ?></th>
				<th><?php esc_html_e( 'Categoria', 'calypsosub' ); ?></th>
				<th><?php esc_html_e( 'Livello', 'calypsosub' ); ?></th>
				<th><?php esc_html_e( 'Note', 'calypsosub' ); ?></th>
			</tr></thead>
			<tbody>
			<?php foreach ( $plan as $item ) :
				$c = $item['course']; ?>
				<tr class="calypso-import__row--<?php echo esc_attr( $item['action'] ); ?>">
					<td><?php echo esc_html( (string) $c['position'] ); ?></td>
					<td><strong><?php echo esc_html( $c['title'] !== '' ? $c['title'] : '—' ); ?></strong>
						<?php if ( $c['slug'] ) : ?><br><code>/corsi/<?php echo esc_html( sanitize_title( $c['slug'] ) ); ?>/</code><?php endif; ?></td>
					<td><span class="calypso-import__badge calypso-import__badge--<?php echo esc_attr( $item['action'] ); ?>"><?php echo esc_html( $labels[ $item['action'] ] ); ?></span>
						<?php if ( $item['post_id'] ) : ?>
							<a href="<?php echo esc_url( get_edit_post_link( $item['post_id'] ) ?? '' ); ?>">#<?php echo esc_html( (string) $item['post_id'] ); ?></a>
						<?php endif; ?>
						<?php if ( $item['reason'] ) : ?><br><small><?php echo esc_html( $item['reason'] ); ?></small><?php endif; ?></td>
					<td><?php echo esc_html( $c['categoria'] ?? '—' ); ?></td>
					<td><?php echo esc_html( $c['livello'] ?? '—' ); ?></td>
					<td><?php if ( $item['notes'] ) : ?><ul><?php foreach ( $item['notes'] as $n ) : ?><li><?php echo esc_html( $n ); ?></li><?php endforeach; ?></ul><?php endif; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/* ───────────── Step 2: import ───────────── */

	private function handle_import(): void {
		$data = get_transient( $this->transient_key() );
		if ( ! is_array( $data ) || empty( $data['plan'] ) ) {
			$this->render_upload_form( __( 'L\'anteprima è scaduta: carica di nuovo il file.', 'calypsosub' ) );
			return;
		}
		delete_transient( $this->transient_key() );

		$results = [];
		foreach ( $data['plan'] as $item ) {
			if ( $item['action'] === 'skip' ) {
				continue;
			}
			$results[] = $this->apply( $item, $data['options'] );
		}

		$ok  = array_filter( $results, fn( $r ) => ! empty( $r['post_id'] ) );
		$err = array_filter( $results, fn( $r ) => empty( $r['post_id'] ) );
		echo '<div class="notice notice-success inline"><p>' . esc_html( sprintf(
			/* translators: %d: number of courses */
			_n( 'Importato %d corso.', 'Importati %d corsi.', count( $ok ), 'calypsosub' ),
			count( $ok )
		) ) . '</p></div>';
		if ( $err ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Alcuni corsi non sono stati salvati:', 'calypsosub' ) . '</p><ul>';
			foreach ( $err as $r ) {
				echo '<li>' . esc_html( $r['title'] . ': ' . $r['error'] ) . '</li>';
			}
			echo '</ul></div>';
		}
		echo '<table class="widefat striped calypso-import__table"><thead><tr><th>' . esc_html__( 'Corso', 'calypsosub' ) . '</th><th>' . esc_html__( 'Esito', 'calypsosub' ) . '</th></tr></thead><tbody>';
		foreach ( $ok as $r ) {
			printf(
				'<tr><td><a href="%s">%s</a></td><td>%s</td></tr>',
				esc_url( get_edit_post_link( $r['post_id'] ) ?? '' ),
				esc_html( $r['title'] ),
				esc_html( $r['created'] ? __( 'Creato', 'calypsosub' ) : __( 'Aggiornato', 'calypsosub' ) )
			);
		}
		echo '</tbody></table>';
		echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'edit.php?post_type=calypso_corso' ) ) . '">' . esc_html__( 'Vai ai corsi', 'calypsosub' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( $this->page_url() ) . '">' . esc_html__( 'Importa un altro file', 'calypsosub' ) . '</a></p>';
	}

	/**
	 * Writes one course. Meta goes through meta_input so it is saved before
	 * save_post runs (Yoast rebuilds its SEO data on save_post).
	 *
	 * @return array{title: string, post_id: int, created: bool, error: string}
	 */
	public function apply( array $item, array $options ): array {
		$c       = $item['course'];
		$created = $item['action'] === 'create';
		$meta    = $this->sanitize_meta( $c['meta'] + $c['yoast'] );
		if ( $c['eta_min'] !== null ) {
			$meta['_corso_eta_min'] = (int) $c['eta_min'];
		}
		if ( $c['fasi'] !== null ) {
			$meta['_corso_fasi'] = array_map( fn( $f ) => [
				'titolo' => sanitize_text_field( $f['titolo'] ),
				'desc'   => sanitize_textarea_field( $f['desc'] ),
				'ore'    => sanitize_text_field( $f['ore'] ),
			], $c['fasi'] );
		}
		$docenti = $this->docenti_index();
		if ( ! empty( $c['direttore'] ) && isset( $docenti[ mb_strtolower( $c['direttore'] ) ] ) ) {
			$meta['_corso_direttore_id'] = $docenti[ mb_strtolower( $c['direttore'] ) ];
		}
		if ( ! empty( $c['docenti'] ) ) {
			$ids = [];
			foreach ( $c['docenti'] as $name ) {
				if ( isset( $docenti[ mb_strtolower( $name ) ] ) ) {
					$ids[] = $docenti[ mb_strtolower( $name ) ];
				}
			}
			if ( $ids ) {
				$meta['_corso_docenti_ids'] = $ids;
			}
		}

		$postarr = [
			'post_type'  => 'calypso_corso',
			'post_title' => sanitize_text_field( $c['title'] ),
			'menu_order' => (int) $c['position'] * 10,
			'meta_input' => $meta,
		];
		if ( $c['slug'] !== '' ) {
			$postarr['post_name'] = sanitize_title( $c['slug'] );
		}
		if ( $c['content'] !== null ) {
			$postarr['post_content'] = wp_kses_post( $c['content'] );
		}

		if ( $created ) {
			$postarr['post_status'] = $options['new_status'];
			$post_id                = wp_insert_post( $postarr, true );
		} else {
			$postarr['ID'] = (int) $item['post_id'];
			$post_id       = wp_update_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return [
				'title'   => $c['title'],
				'post_id' => 0,
				'created' => $created,
				'error'   => is_wp_error( $post_id ) ? $post_id->get_error_message() : __( 'Errore sconosciuto', 'calypsosub' ),
			];
		}

		if ( ! empty( $c['livello'] ) ) {
			wp_set_object_terms( $post_id, [ $c['livello'] ], 'calypso_livello' );
		}
		if ( ! empty( $c['categoria'] ) ) {
			$this->assign_category( (int) $post_id, $c['categoria'], (int) $c['position'] );
		}

		return [ 'title' => $c['title'], 'post_id' => (int) $post_id, 'created' => $created, 'error' => '' ];
	}

	/** Sets the course category; a new category gets the order of its first course in the file. */
	private function assign_category( int $post_id, string $name, int $position ): void {
		$tax  = Calypsosub_Tax_Categorie_Corsi::TAXONOMY;
		$term = term_exists( $name, $tax );
		if ( ! $term ) {
			$term = wp_insert_term( $name, $tax );
			if ( is_wp_error( $term ) ) {
				return;
			}
			update_term_meta( (int) $term['term_id'], Calypsosub_Tax_Categorie_Corsi::ORDER_META, $position * 10 );
		}
		wp_set_object_terms( $post_id, [ (int) $term['term_id'] ], $tax );
	}

	/** @param array<string, string> $meta */
	private function sanitize_meta( array $meta ): array {
		$out = [];
		foreach ( $meta as $key => $value ) {
			if ( $key === '_corso_link_iscrizione' ) {
				$out[ $key ] = esc_url_raw( $value );
			} elseif ( in_array( $key, self::MULTILINE_META, true ) ) {
				$out[ $key ] = sanitize_textarea_field( $value );
			} else {
				$out[ $key ] = sanitize_text_field( $value );
			}
		}
		return $out;
	}

	/* ───────────── Helpers ───────────── */

	/** @return array{slug: array<string,int>, title: array<string,int>} */
	private function existing_courses_index(): array {
		$posts = get_posts( [
			'post_type'      => 'calypso_corso',
			'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		] );
		$index = [ 'slug' => [], 'title' => [] ];
		foreach ( $posts as $p ) {
			if ( $p->post_name !== '' ) {
				$index['slug'][ $p->post_name ] ??= $p->ID;
			}
			$index['title'][ mb_strtolower( $p->post_title ) ] ??= $p->ID;
		}
		return $index;
	}

	/** @return array<string, int> Lower-cased docente name → post ID. */
	private function docenti_index(): array {
		static $cache = null;
		if ( $cache !== null ) {
			return $cache;
		}
		$cache = [];
		$posts = get_posts( [ 'post_type' => 'calypso_docente', 'post_status' => 'publish', 'posts_per_page' => -1 ] );
		foreach ( $posts as $p ) {
			$cache[ mb_strtolower( trim( $p->post_title ) ) ] = $p->ID;
		}
		return $cache;
	}

	private function field_label( string $key ): string {
		$labels = [
			'title'                  => __( 'Titolo', 'calypsosub' ),
			'content'                => __( 'Descrizione', 'calypsosub' ),
			'_corso_sottotitolo'     => __( 'Titolo display', 'calypsosub' ),
			'_corso_badge'           => __( 'Badge', 'calypsosub' ),
			'_corso_desc_breve'      => __( 'Descrizione breve', 'calypsosub' ),
			'_corso_competenze'      => __( 'Cosa imparerai', 'calypsosub' ),
			'_corso_stat_durata'     => __( 'Durata', 'calypsosub' ),
			'_corso_stat_pratica'    => __( 'Immersioni / pratica', 'calypsosub' ),
			'_corso_stat_profondita' => __( 'Profondità', 'calypsosub' ),
			'_corso_periodo'         => __( 'Periodo', 'calypsosub' ),
			'_corso_requisiti'       => __( 'Requisiti', 'calypsosub' ),
			'_corso_materiale'       => __( 'Materiale', 'calypsosub' ),
			'_yoast_wpseo_title'     => __( 'Title SEO', 'calypsosub' ),
			'_yoast_wpseo_metadesc'  => __( 'Meta description', 'calypsosub' ),
			'_yoast_wpseo_focuskw'   => __( 'Parola chiave', 'calypsosub' ),
		];
		return $labels[ $key ] ?? $key;
	}

	private function transient_key(): string {
		return self::TRANSIENT_BASE . get_current_user_id();
	}

	private function page_url(): string {
		return admin_url( 'edit.php?post_type=calypso_corso&page=' . self::PAGE_SLUG );
	}

	/** Streams an empty CSV template with every supported column. */
	public function download_template(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Permessi insufficienti.', 'calypsosub' ) );
		}
		check_admin_referer( self::NONCE_ACTION );
		$headers = [
			'Linea', 'Corso (titolo WordPress)', 'Slug URL', 'Livello', 'Età minima',
			'Parola chiave Yoast', 'Title SEO', 'Meta description',
			'Titolo display', 'Badge certificazione', 'Descrizione breve', 'Descrizione (contenuto pagina)', 'Cosa imparerai',
		];
		for ( $n = 1; $n <= 4; $n++ ) {
			$headers[] = "Fase $n titolo";
			$headers[] = "Fase $n ore";
			$headers[] = "Fase $n descrizione";
		}
		$headers = array_merge( $headers, [
			'Durata', 'Immersioni / pratica', 'Profondità', 'Periodo tipico', 'Requisiti di accesso', 'Materiale incluso',
			'Contatto sidebar', 'Nome contatto', 'Direttore del corso', 'Docenti del corso', 'Link iscrizione personalizzato', 'Approvato',
		] );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="modello-corsi.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, $headers, ';', '"', '' );
		fclose( $out );
		exit;
	}

	private function render_styles(): void {
		?>
		<style>
		.calypso-import__intro{font-size:14px;max-width:760px}
		.calypso-import__rules{list-style:disc;margin-left:20px;max-width:760px}
		.calypso-import__summary{font-size:15px;font-weight:600}
		.calypso-import__table{margin-top:12px}
		.calypso-import__table ul{margin:0;list-style:disc;padding-left:16px}
		.calypso-import__badge{display:inline-block;padding:2px 10px;border-radius:999px;font-weight:600;font-size:12px}
		.calypso-import__badge--create{background:#e5f4e4;color:#1e6b2b}
		.calypso-import__badge--update{background:#e7f1fb;color:#1b4f8a}
		.calypso-import__badge--skip{background:#f0efec;color:#555}
		.calypso-import__row--skip td{opacity:.7}
		</style>
		<?php
	}
}
