<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Course categories (e.g. ARA, Apnea, Mermaid, Specialità). Used to group
 * courses on the /corsi/ archive and as a filter. Each term has an "order"
 * meta that decides the position of its group on the page.
 */
class Calypsosub_Tax_Categorie_Corsi {

	const TAXONOMY = 'calypso_categoria_corso';
	const ORDER_META = '_categoria_ordine';

	public function init(): void {
		add_action( 'init', [ $this, 'register' ] );
		add_action( self::TAXONOMY . '_add_form_fields', [ $this, 'render_add_field' ] );
		add_action( self::TAXONOMY . '_edit_form_fields', [ $this, 'render_edit_field' ] );
		add_action( 'created_' . self::TAXONOMY, [ $this, 'save_order' ] );
		add_action( 'edited_' . self::TAXONOMY, [ $this, 'save_order' ] );
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns', [ $this, 'add_order_column' ] );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column', [ $this, 'render_order_column' ], 10, 3 );
	}

	public function register(): void {
		register_taxonomy( self::TAXONOMY, [ 'calypso_corso' ], [
			'label'             => __( 'Categorie corsi', 'calypsosub' ),
			'labels'            => [
				'name'          => __( 'Categorie corsi', 'calypsosub' ),
				'singular_name' => __( 'Categoria corso', 'calypsosub' ),
				'add_new_item'  => __( 'Aggiungi categoria', 'calypsosub' ),
				'edit_item'     => __( 'Modifica categoria', 'calypsosub' ),
				'menu_name'     => __( 'Categorie', 'calypsosub' ),
			],
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'categoria-corso' ],
		] );
	}

	/**
	 * Categories sorted by their order meta, then by name.
	 *
	 * @return WP_Term[]
	 */
	public static function get_sorted_terms( bool $hide_empty = true ): array {
		$terms = get_terms( [ 'taxonomy' => self::TAXONOMY, 'hide_empty' => $hide_empty ] );
		if ( is_wp_error( $terms ) ) {
			return [];
		}
		usort( $terms, function ( WP_Term $a, WP_Term $b ): int {
			$oa = self::get_order( $a->term_id );
			$ob = self::get_order( $b->term_id );
			return $oa === $ob ? strcasecmp( $a->name, $b->name ) : $oa <=> $ob;
		} );
		return $terms;
	}

	public static function get_order( int $term_id ): int {
		$value = get_term_meta( $term_id, self::ORDER_META, true );
		return $value === '' ? 999 : (int) $value;
	}

	public function render_add_field(): void {
		?>
		<div class="form-field">
			<label for="calypso-categoria-ordine"><?php esc_html_e( 'Ordine', 'calypsosub' ); ?></label>
			<input type="number" name="calypso_categoria_ordine" id="calypso-categoria-ordine" value="" min="0" step="1">
			<p><?php esc_html_e( 'Posizione del gruppo nella pagina Corsi: numeri più bassi vengono prima.', 'calypsosub' ); ?></p>
		</div>
		<?php
		wp_nonce_field( 'calypso_categoria_ordine', 'calypso_categoria_ordine_nonce' );
	}

	public function render_edit_field( WP_Term $term ): void {
		$value = get_term_meta( $term->term_id, self::ORDER_META, true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="calypso-categoria-ordine"><?php esc_html_e( 'Ordine', 'calypsosub' ); ?></label></th>
			<td>
				<input type="number" name="calypso_categoria_ordine" id="calypso-categoria-ordine" value="<?php echo esc_attr( $value ); ?>" min="0" step="1">
				<p class="description"><?php esc_html_e( 'Posizione del gruppo nella pagina Corsi: numeri più bassi vengono prima.', 'calypsosub' ); ?></p>
				<?php wp_nonce_field( 'calypso_categoria_ordine', 'calypso_categoria_ordine_nonce' ); ?>
			</td>
		</tr>
		<?php
	}

	public function save_order( int $term_id ): void {
		if ( ! isset( $_POST['calypso_categoria_ordine_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['calypso_categoria_ordine_nonce'] ), 'calypso_categoria_ordine' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		$raw = trim( (string) wp_unslash( $_POST['calypso_categoria_ordine'] ?? '' ) );
		if ( $raw === '' ) {
			delete_term_meta( $term_id, self::ORDER_META );
		} else {
			update_term_meta( $term_id, self::ORDER_META, absint( $raw ) );
		}
	}

	public function add_order_column( array $columns ): array {
		$columns['calypso_ordine'] = __( 'Ordine', 'calypsosub' );
		return $columns;
	}

	public function render_order_column( string $content, string $column, int $term_id ): string {
		if ( $column !== 'calypso_ordine' ) {
			return $content;
		}
		$value = get_term_meta( $term_id, self::ORDER_META, true );
		return $value === '' ? '—' : esc_html( (string) $value );
	}
}
