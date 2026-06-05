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

	}
}
