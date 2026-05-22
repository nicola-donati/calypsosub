<?php
/**
 * Block template: Area Personale Utente
 * Inserire in una pagina WordPress dedicata come blocco HTML personalizzato:
 *   <?php include( CALYPSOSUB_PATH . 'block-templates/block-area-personale.php' ); ?>
 * Oppure usare lo shortcode [calypso_area_personale] registrato da class-user-account.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! is_user_logged_in() ) {
	?>
	<div class="calypso-account calypso-account--guest">
		<p><?php _e( 'Devi accedere per visualizzare la tua area personale.', 'calypsosub' ); ?></p>
		<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"
		   class="calypso-btn"><?php _e( 'Accedi', 'calypsosub' ); ?></a>
	</div>
	<?php
	return;
}

$user_id     = get_current_user_id();
$booking_ids = calypso_get_user_bookings( $user_id );

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

$cancel_nonce = wp_create_nonce( 'calypso_cancel_nonce' );
?>
<style>
.calypso-account{--c-deep:#0a2540;--c-wave:#1d6f9c;--c-coral:#ff6b4a;--c-bone:#f6f1e6;--c-foam:#cfe9ee;--radius:4px;--radius-lg:12px;--f-body:"DM Sans",-apple-system,BlinkMacSystemFont,sans-serif;--f-display:"Big Shoulders Display","Anton",Impact,sans-serif;font-family:var(--f-body);max-width:840px;margin:0 auto;padding:0 24px}
.calypso-account h2{font-family:var(--f-display);font-size:32px;color:var(--c-deep);margin:0 0 20px}
.calypso-account h3{font-family:var(--f-display);font-size:24px;color:var(--c-deep);margin:32px 0 16px}
.calypso-bookings-table{width:100%;border-collapse:collapse;margin-bottom:24px}
.calypso-bookings-table th{background:var(--c-deep);color:#fff;padding:10px 14px;text-align:left;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em}
.calypso-bookings-table td{padding:12px 14px;border-bottom:1px solid #e5e7eb;font-size:14px;vertical-align:middle}
.calypso-bookings-table tr:last-child td{border-bottom:none}
.calypso-bookings-table tr:hover td{background:#f9fafb}
.calypso-status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.calypso-status-badge--confermata{background:#d1fae5;color:#065f46}
.calypso-status-badge--lista_attesa{background:#fef3c7;color:#92400e}
.calypso-status-badge--annullata{background:#fee2e2;color:#991b1b}
.calypso-cancel-btn{background:transparent;border:1px solid var(--c-coral);color:var(--c-coral);border-radius:var(--radius);padding:5px 14px;font-size:13px;cursor:pointer;font-family:var(--f-body);transition:all .15s}
.calypso-cancel-btn:hover{background:var(--c-coral);color:#fff}
.calypso-btn{display:inline-block;background:var(--c-coral);color:#fff;font-family:var(--f-display);font-size:15px;letter-spacing:.04em;text-transform:uppercase;padding:10px 20px;border-radius:var(--radius);text-decoration:none;border:none;cursor:pointer;transition:background .15s}
.calypso-btn:hover{background:#e04a2a}
.calypso-empty-state{color:#888;padding:24px 0;font-size:15px}
.calypso-bookings-history td{color:#888}
</style>

<div class="calypso-account">
	<h2><?php _e( 'Le mie prenotazioni', 'calypsosub' ); ?></h2>

	<?php if ( empty( $active ) ) : ?>
		<p class="calypso-empty-state"><?php _e( 'Nessuna prenotazione attiva.', 'calypsosub' ); ?></p>
	<?php else : ?>
	<table class="calypso-bookings-table">
		<thead>
			<tr>
				<th><?php _e( 'Evento', 'calypsosub' ); ?></th>
				<th><?php _e( 'Data', 'calypsosub' ); ?></th>
				<th><?php _e( 'Stato', 'calypsosub' ); ?></th>
				<th><?php _e( 'Acc.', 'calypsosub' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $active as $bid ) :
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
				<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" style="color:inherit">
					<?php echo esc_html( get_the_title( $post_id ) ); ?>
				</a>
			</td>
			<td><?php echo esc_html( $prima_data ); ?></td>
			<td>
				<span class="calypso-status-badge calypso-status-badge--<?php echo esc_attr( $status ); ?>">
					<?php echo esc_html( $status === 'lista_attesa' ? __( 'Lista attesa', 'calypsosub' ) : ucfirst( $status ) ); ?>
				</span>
			</td>
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
			<td>
				<span class="calypso-status-badge calypso-status-badge--<?php echo esc_attr( $status ); ?>">
					<?php echo esc_html( ucfirst( $status ) ); ?>
				</span>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>

<script>
(function () {
	var ajaxUrl    = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
	var msgConfirm = <?php echo wp_json_encode( __( 'Confermi la cancellazione?', 'calypsosub' ) ); ?>;
	var msgError   = <?php echo wp_json_encode( __( 'Errore durante la cancellazione.', 'calypsosub' ) ); ?>;

	document.querySelectorAll('.calypso-cancel-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			if (!confirm(msgConfirm)) return;
			btn.disabled = true;
			var data = new URLSearchParams({
				action:     'calypso_cancel_booking',
				booking_id: btn.dataset.bookingId,
				nonce:      btn.dataset.nonce
			});
			fetch(ajaxUrl, { method: 'POST', body: data })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res.success) {
						btn.closest('tr').remove();
					} else {
						btn.disabled = false;
						alert(res.data && res.data.message ? res.data.message : msgError);
					}
				})
				.catch(function () {
					btn.disabled = false;
					alert(msgError);
				});
		});
	});
})();
</script>
