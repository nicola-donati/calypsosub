<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_Email_Templates {

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_submenu' ] );
	}

	public function register_submenu(): void {
		add_submenu_page(
			'calypsosub',
			__( 'Template Email', 'calypsosub' ),
			__( 'Template Email', 'calypsosub' ),
			'manage_options',
			'calypsosub-email-templates',
			[ $this, 'render_page' ]
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$email = new Calypsosub_Booking_Email();
		$templates = $email->default_templates();

		$current_key = sanitize_key( $_GET['template'] ?? array_key_first( $templates ) );
		if ( ! array_key_exists( $current_key, $templates ) ) {
			$current_key = array_key_first( $templates );
		}

		if ( isset( $_POST['calypsosub_email_nonce'] ) &&
		     wp_verify_nonce( sanitize_key( $_POST['calypsosub_email_nonce'] ), 'calypsosub_email_' . $current_key ) ) {
			update_option(
				'calypsosub_email_subject_' . $current_key,
				sanitize_text_field( wp_unslash( $_POST['email_subject'] ?? '' ) )
			);
			update_option(
				'calypsosub_email_body_' . $current_key,
				wp_kses_post( wp_unslash( $_POST['email_body'] ?? '' ) )
			);
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Template salvato.', 'calypsosub' ) . '</p></div>';
		}

		$template_labels = [
			'conferma_prenotazione'       => __( 'Conferma prenotazione', 'calypsosub' ),
			'prenotazione_annullata'      => __( 'Prenotazione annullata', 'calypsosub' ),
			'promosso_lista_attesa'       => __( 'Promosso da lista attesa', 'calypsosub' ),
			'notifica_admin_prenotazione' => __( 'Notifica admin', 'calypsosub' ),
		];

		$current_subject = get_option(
			'calypsosub_email_subject_' . $current_key,
			$templates[ $current_key ]['subject']
		);
		$current_body = get_option(
			'calypsosub_email_body_' . $current_key,
			$templates[ $current_key ]['body']
		);

		$vars_list = '{nome_utente}, {email_utente}, {titolo_evento}, {data_evento}, {luogo}, {num_accompagnatori}, {allergie}, {stato_prenotazione}, {link_area_personale}, {data_prenotazione}';
		?>
		<div class="wrap">
			<h1><?php _e( 'Template Email', 'calypsosub' ); ?></h1>

			<nav class="nav-tab-wrapper" style="margin-bottom:20px">
				<?php foreach ( $template_labels as $key => $label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=calypsosub-email-templates&template=' . $key ) ); ?>"
				   class="nav-tab <?php echo $key === $current_key ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</a>
				<?php endforeach; ?>
			</nav>

			<p class="description" style="margin-bottom:16px">
				<?php _e( 'Variabili disponibili:', 'calypsosub' ); ?>
				<code><?php echo esc_html( $vars_list ); ?></code>
			</p>

			<form method="post">
				<?php wp_nonce_field( 'calypsosub_email_' . $current_key, 'calypsosub_email_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="email_subject"><?php _e( 'Oggetto', 'calypsosub' ); ?></label></th>
						<td>
							<input type="text" id="email_subject" name="email_subject"
							       value="<?php echo esc_attr( $current_subject ); ?>"
							       class="large-text">
						</td>
					</tr>
					<tr>
						<th><label for="email_body"><?php _e( 'Corpo (HTML)', 'calypsosub' ); ?></label></th>
						<td>
							<textarea id="email_body" name="email_body"
							          rows="14" class="large-text code"><?php echo esc_textarea( $current_body ); ?></textarea>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Salva template', 'calypsosub' ) ); ?>
			</form>
		</div>
		<?php
	}
}
