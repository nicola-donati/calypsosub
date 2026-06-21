<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CF7_Booking_Category {

	public const META_KEY = '_calypso_booking_category';

	private const CATEGORIES = [
		''       => 'Nessuna',
		'uscite' => 'Prenotazione Uscite',
		'eventi' => 'Prenotazione Eventi',
		'corsi'  => 'Richiesta Corsi',
	];

	public function init(): void {
		add_filter( 'wpcf7_editor_panels', [ $this, 'add_panel' ] );
		add_action( 'wpcf7_save_contact_form', [ $this, 'on_save' ] );
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
	}

	public function add_panel( array $panels ): array {
		$panels['calypso-booking'] = [
			'title'    => __( 'Calypso', 'calypsosub' ),
			'callback' => [ $this, 'render_panel' ],
		];
		return $panels;
	}

	public function render_panel( WPCF7_ContactForm $post ): void {
		$current = (string) get_post_meta( $post->id(), self::META_KEY, true );
		?>
		<h2><?php esc_html_e( 'Categoria prenotazione Calypso', 'calypsosub' ); ?></h2>
		<p><?php esc_html_e( 'Se questo form serve per raccogliere una prenotazione/richiesta dal plugin Calypso, scegli a quale tipo appartiene. Il blocco "Prenotazione" potrà selezionarlo solo se categorizzato qui.', 'calypsosub' ); ?></p>
		<fieldset>
			<select name="calypso-booking-category">
				<?php foreach ( self::CATEGORIES as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
				<?php endforeach; ?>
			</select>
		</fieldset>
		<?php
	}

	public function on_save( WPCF7_ContactForm $contact_form ): void {
		$category = isset( $_POST['calypso-booking-category'] ) ? sanitize_key( wp_unslash( $_POST['calypso-booking-category'] ) ) : '';
		if ( ! array_key_exists( $category, self::CATEGORIES ) ) {
			$category = '';
		}
		update_post_meta( $contact_form->id(), self::META_KEY, $category );

		$missing = $category !== '' ? $this->missing_required_fields( $contact_form, $category ) : [];
		$transient_key = 'calypso_cf7_missing_' . $contact_form->id();
		if ( $missing ) {
			set_transient( $transient_key, $missing, 5 * MINUTE_IN_SECONDS );
		} else {
			delete_transient( $transient_key );
		}
	}

	private function missing_required_fields( WPCF7_ContactForm $contact_form, string $category ): array {
		$campi = (array) ( get_option( 'calypsosub_opts_' . $category, [] )['campi_prenotazione'] ?? [] );
		$required = array_values( array_filter( $campi, static fn( $c ) => ( $c['obbligatorio'] ?? '' ) === '1' ) );
		if ( ! $required ) return [];

		$tag_names = array_map( static fn( $tag ) => $tag->name, $contact_form->scan_form_tags() );

		$missing = [];
		foreach ( $required as $campo ) {
			if ( ! in_array( $campo['nome'], $tag_names, true ) ) {
				$missing[] = $campo['label'] !== '' ? $campo['label'] . ' (' . $campo['nome'] . ')' : $campo['nome'];
			}
		}
		return $missing;
	}

	public function admin_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== 'wpcf7' || ! isset( $_GET['post'] ) ) return;

		$form_id = absint( $_GET['post'] );
		$missing = get_transient( 'calypso_cf7_missing_' . $form_id );
		if ( ! $missing ) return;
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Attenzione — questo form è categorizzato per il plugin Calypso ma mancano campi obbligatori:', 'calypsosub' ); ?></strong>
				<?php echo esc_html( implode( ', ', (array) $missing ) ); ?>.
				<?php esc_html_e( 'Il form resta salvato, ma il blocco "Prenotazione" potrebbe non funzionare correttamente finché non aggiungi questi campi.', 'calypsosub' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * @return array{id:int,title:string}[]
	 */
	public function forms_for_category( string $category ): array {
		if ( ! array_key_exists( $category, self::CATEGORIES ) || $category === '' ) return [];

		$query = new WP_Query( [
			'post_type'      => 'wpcf7_contact_form',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => self::META_KEY,
			'meta_value'     => $category,
		] );

		return array_map(
			static fn( WP_Post $p ) => [ 'id' => $p->ID, 'title' => get_the_title( $p ) ],
			$query->posts
		);
	}
}
