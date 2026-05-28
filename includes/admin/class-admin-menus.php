<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Admin_Menus {

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menus' ] );
	}

	public function register_menus(): void {
		add_menu_page(
			__( 'Calypso Sub', 'calypsosub' ),
			__( 'Calypso Sub', 'calypsosub' ),
			'manage_options',
			'calypsosub',
			[ $this, 'render_settings_page' ],
			'dashicons-flag',
			30
		);

		add_submenu_page(
			'calypsosub',
			__( 'Impostazioni', 'calypsosub' ),
			__( 'Impostazioni', 'calypsosub' ),
			'manage_options',
			'calypsosub',
			[ $this, 'render_settings_page' ]
		);
	}

	private array $cpt_labels = [
		'calypso_uscita'  => 'Uscite',
		'calypso_evento'  => 'Eventi',
		'calypso_corso'   => 'Corsi',
		'calypso_docente' => 'Docenti',
	];

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		if ( isset( $_POST['calypsosub_settings_nonce'] ) &&
		     wp_verify_nonce( sanitize_key( $_POST['calypsosub_settings_nonce'] ), 'calypsosub_settings' ) ) {
			$this->save_settings();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Impostazioni salvate.', 'calypsosub' ) . '</p></div>';
		}

		$notification_emails = get_option( 'calypsosub_notification_emails', '' );
		$account_page_id     = (int) get_option( 'calypsosub_account_page_id', 0 );
		$template_modes      = (array) get_option( 'calypsosub_template_modes', [] );
		?>
		<div class="wrap">
			<h1><?php _e( 'Calypso Sub — Impostazioni', 'calypsosub' ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'calypsosub_settings', 'calypsosub_settings_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="calypsosub_notification_emails">
								<?php _e( 'Email notifiche admin', 'calypsosub' ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="calypsosub_notification_emails"
							       name="calypsosub_notification_emails"
							       value="<?php echo esc_attr( $notification_emails ); ?>"
							       class="regular-text">
							<p class="description">
								<?php _e( 'Indirizzi email separati da virgola che ricevono notifica ad ogni nuova prenotazione.', 'calypsosub' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="calypsosub_account_page_id">
								<?php _e( 'Pagina area personale', 'calypsosub' ); ?>
							</label>
						</th>
						<td>
							<?php
							wp_dropdown_pages( [
								'name'              => 'calypsosub_account_page_id',
								'id'                => 'calypsosub_account_page_id',
								'selected'          => $account_page_id,
								'show_option_none'  => __( '— seleziona pagina —', 'calypsosub' ),
								'option_none_value' => 0,
							] );
							?>
						</td>
					</tr>
				</table>

				<h2><?php _e( 'Template pagine singole', 'calypsosub' ); ?></h2>
				<p class="description">
					<?php _e( 'Scegli quale template usare per la pagina di dettaglio di ogni tipo di contenuto.', 'calypsosub' ); ?>
				</p>
				<table class="form-table">
					<?php foreach ( $this->cpt_labels as $post_type => $label ) :
						$mode = $template_modes[ $post_type ] ?? 'plugin';
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $label ); ?></th>
						<td>
							<select name="calypsosub_template_modes[<?php echo esc_attr( $post_type ); ?>]">
								<option value="plugin" <?php selected( $mode, 'plugin' ); ?>>
									<?php _e( 'Plugin (template PHP incluso)', 'calypsosub' ); ?>
								</option>
								<option value="wp" <?php selected( $mode, 'wp' ); ?>>
									<?php _e( 'Tema / Editor Gutenberg', 'calypsosub' ); ?>
								</option>
							</select>
						</td>
					</tr>
					<?php endforeach; ?>
				</table>

				<?php submit_button( __( 'Salva impostazioni', 'calypsosub' ) ); ?>
			</form>
		</div>
		<?php
	}

	private function save_settings(): void {
		update_option( 'calypsosub_notification_emails',
			sanitize_text_field( wp_unslash( $_POST['calypsosub_notification_emails'] ?? '' ) ) );
		update_option( 'calypsosub_account_page_id',
			absint( $_POST['calypsosub_account_page_id'] ?? 0 ) );

		$raw_modes    = $_POST['calypsosub_template_modes'] ?? [];
		$allowed      = [ 'plugin', 'wp' ];
		$clean_modes  = [];
		foreach ( array_keys( $this->cpt_labels ) as $post_type ) {
			$val = $raw_modes[ $post_type ] ?? 'plugin';
			$clean_modes[ $post_type ] = in_array( $val, $allowed, true ) ? $val : 'plugin';
		}
		update_option( 'calypsosub_template_modes', $clean_modes );
	}
}
