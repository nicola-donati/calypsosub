<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_User_Account {

	private Calypsosub_Booking_Manager $booking_manager;

	public function __construct( Calypsosub_Booking_Manager $booking_manager ) {
		$this->booking_manager = $booking_manager;
	}

	public function init(): void {
		add_shortcode( 'calypso_area_personale', [ $this, 'render_shortcode' ] );
	}

	public function render_shortcode( array $atts = [] ): string {
		if ( ! is_user_logged_in() ) {
			return '<p class="calypso-login-required">' .
			       esc_html__( 'Devi accedere per visualizzare la tua area personale.', 'calypsosub' ) .
			       ' <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' .
			       esc_html__( 'Accedi', 'calypsosub' ) . '</a></p>';
		}

		$user_id      = get_current_user_id();
		$booking_ids  = $this->booking_manager->get_user_bookings( $user_id );

		$active  = [];
		$history = [];

		foreach ( $booking_ids as $bid ) {
			$status = get_post_meta( $bid, '_booking_status', true );
			if ( in_array( $status, [ 'confermata', 'lista_attesa' ], true ) ) {
				$active[] = $bid;
			} else {
				$history[] = $bid;
			}
		}

		ob_start();
		$cancel_nonce = wp_create_nonce( 'calypso_cancel_nonce' );
		?>
		<div class="calypso-account">
			<h2><?php _e( 'Le mie prenotazioni', 'calypsosub' ); ?></h2>

			<?php if ( empty( $active ) ) : ?>
				<p><?php _e( 'Nessuna prenotazione attiva.', 'calypsosub' ); ?></p>
			<?php else : ?>
			<table class="calypso-bookings-table">
				<thead>
					<tr>
						<th><?php _e( 'Evento', 'calypsosub' ); ?></th>
						<th><?php _e( 'Data', 'calypsosub' ); ?></th>
						<th><?php _e( 'Stato', 'calypsosub' ); ?></th>
						<th><?php _e( 'Accompagnatori', 'calypsosub' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $active as $bid ) : ?>
					<?php
					$post_id    = (int) get_post_meta( $bid, '_booking_post_id', true );
					$post_type  = get_post_type( $post_id );
					$status     = (string) get_post_meta( $bid, '_booking_status', true );
					$companions = (int) get_post_meta( $bid, '_booking_companions', true );
					$mp         = $post_type === 'calypso_uscita' ? '_uscita' : '_evento';
					$date_raw   = (array) ( get_post_meta( $post_id, $mp . '_date', true ) ?: [] );
					$prima_data = ! empty( $date_raw )
						? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_raw[0] ) )
						: '—';
					?>
					<tr>
						<td>
							<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
								<?php echo esc_html( get_the_title( $post_id ) ); ?>
							</a>
						</td>
						<td><?php echo esc_html( $prima_data ); ?></td>
						<td><?php echo esc_html( $status ); ?></td>
						<td><?php echo esc_html( $companions ); ?></td>
						<td>
							<button type="button" class="calypso-cancel-btn"
							        data-booking-id="<?php echo esc_attr( $bid ); ?>"
							        data-nonce="<?php echo esc_attr( $cancel_nonce ); ?>">
								<?php _e( 'Cancella', 'calypsosub' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>

			<?php if ( ! empty( $history ) ) : ?>
			<h3><?php _e( 'Storico', 'calypsosub' ); ?></h3>
			<table class="calypso-bookings-table calypso-bookings-history">
				<thead>
					<tr>
						<th><?php _e( 'Evento', 'calypsosub' ); ?></th>
						<th><?php _e( 'Data', 'calypsosub' ); ?></th>
						<th><?php _e( 'Stato', 'calypsosub' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $history as $bid ) :
					$post_id   = (int) get_post_meta( $bid, '_booking_post_id', true );
					$post_type = get_post_type( $post_id );
					$status    = (string) get_post_meta( $bid, '_booking_status', true );
					$mp        = $post_type === 'calypso_uscita' ? '_uscita' : '_evento';
					$date_raw  = (array) ( get_post_meta( $post_id, $mp . '_date', true ) ?: [] );
					$prima_data = ! empty( $date_raw )
						? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_raw[0] ) )
						: '—';
				?>
					<tr>
						<td><?php echo esc_html( get_the_title( $post_id ) ); ?></td>
						<td><?php echo esc_html( $prima_data ); ?></td>
						<td><?php echo esc_html( $status ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div>

		<style>
		.calypso-account{max-width:800px}
		.calypso-bookings-table{width:100%;border-collapse:collapse;margin-bottom:24px}
		.calypso-bookings-table th,.calypso-bookings-table td{padding:8px 12px;border-bottom:1px solid #e5e7eb;text-align:left}
		.calypso-bookings-table th{background:#f9fafb;font-weight:600}
		.calypso-cancel-btn{background:#dc3545;color:#fff;border:none;border-radius:4px;padding:4px 12px;cursor:pointer}
		.calypso-cancel-btn:hover{background:#b02a37}
		</style>

		<script>
		(function () {
			var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

			document.querySelectorAll('.calypso-cancel-btn').forEach(function (btn) {
				btn.addEventListener('click', function () {
					if (!confirm(<?php echo wp_json_encode( __( 'Confermi la cancellazione della prenotazione?', 'calypsosub' ) ); ?>)) return;

					var bookingId = btn.dataset.bookingId;
					var nonce     = btn.dataset.nonce;
					var data      = new URLSearchParams({
						action:     'calypso_cancel_booking',
						booking_id: bookingId,
						nonce:      nonce
					});

					fetch(ajaxUrl, { method: 'POST', body: data })
						.then(function (r) { return r.json(); })
						.then(function (res) {
							if (res.success) {
								btn.closest('tr').remove();
							} else {
								alert(res.data.message || <?php echo wp_json_encode( __( 'Errore durante la cancellazione.', 'calypsosub' ) ); ?>);
							}
						});
				});
			});
		})();
		</script>
		<?php
		return ob_get_clean();
	}
}
