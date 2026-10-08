<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Esegue l'invio effettivo di una comunicazione quando il post diventa
 * "pubblicato" (subito o via lo scheduler nativo di WP per un invio
 * programmato). Il lavoro vero gira su un evento cron separato invece che
 * dentro la richiesta di salvataggio/pubblicazione in admin, per non far
 * dipendere il salvataggio del post dalla latenza delle API esterne.
 */
class Calypsosub_Communication_Dispatcher {

	public const DISPATCH_HOOK = 'calypsosub_dispatch_communication';

	private Calypsosub_SystemeIO_Client $systemeio;
	private Calypsosub_Telegram_Client  $telegram;

	public function __construct( Calypsosub_SystemeIO_Client $systemeio, Calypsosub_Telegram_Client $telegram ) {
		$this->systemeio = $systemeio;
		$this->telegram  = $telegram;
	}

	public function init(): void {
		// Deliberately save_post_{post_type} at a LATE priority, not the
		// native publish_{post_type} hook. WordPress fires publish_{post_type}
		// *before* save_post on a post's first publish — a brand-new
		// communication published directly (no earlier draft save) would
		// still have empty _com_category/_com_channels at that moment
		// (Calypsosub_CPT_Communications::save_meta() hasn't run yet), so
		// this would lock the post and schedule a dispatch that reads
		// nothing back, always failing with "categoria non configurata"
		// regardless of what the admin actually picked in the meta box.
		// Hanging both off the same save_post_{post_type} action makes the
		// ordering a guaranteed priority (save_meta is 10, this is 20)
		// instead of an unrelated-hooks race.
		add_action( 'save_post_' . Calypsosub_CPT_Communications::POST_TYPE, [ $this, 'maybe_dispatch_on_publish' ], 20, 2 );
		add_action( self::DISPATCH_HOOK, [ $this, 'dispatch' ] );
	}

	/**
	 * Fires on every save while the post is published (not only the first
	 * transition into it) — the `_com_dispatched` flag below prevents a
	 * double send when the operator reopens and resaves an already-sent
	 * communication.
	 */
	public function maybe_dispatch_on_publish( int $post_id, WP_Post $post ): void {
		if ( 'publish' !== $post->post_status ) return;
		if ( get_post_meta( $post_id, '_com_dispatched', true ) ) return;
		self::schedule( $post_id );
	}

	/**
	 * Segna la comunicazione come "in invio" e accoda il dispatch vero sul
	 * cron. Usato sia al primo invio (maybe_dispatch_on_publish) sia da un
	 * retry manuale su una comunicazione già fallita (vedi
	 * Calypsosub_CPT_Communications).
	 */
	public static function schedule( int $post_id ): void {
		update_post_meta( $post_id, '_com_dispatched', 1 );
		update_post_meta( $post_id, '_com_status', 'sending' );

		if ( ! wp_next_scheduled( self::DISPATCH_HOOK, [ $post_id ] ) ) {
			wp_schedule_single_event( time() + 5, self::DISPATCH_HOOK, [ $post_id ] );
		}
	}

	public function dispatch( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== Calypsosub_CPT_Communications::POST_TYPE ) return;

		$category_key = (string) get_post_meta( $post_id, '_com_category', true );
		$channels     = (array) get_post_meta( $post_id, '_com_channels', true );
		$categories   = Calypsosub_Communications_Settings::get_categories();
		$category     = $categories[ $category_key ] ?? null;

		if ( ! $category ) {
			update_post_meta( $post_id, '_com_status', 'failed' );
			update_post_meta( $post_id, '_com_results', [
				'error' => __( 'Categoria non configurata o rimossa dalle impostazioni.', 'calypsosub' ),
			] );
			return;
		}

		$subject   = get_the_title( $post );
		$body_html = wpautop( (string) $post->post_content );
		$results   = [];
		$all_ok    = true;

		if ( in_array( 'email', $channels, true ) ) {
			// systeme.io's public API has no tag/segment targeting on newsletter
			// create or send (see Calypsosub_SystemeIO_Client) — every email send
			// goes to the whole contact list regardless of the category's
			// tag_systemeio setting, which this call no longer reads.
			$result = $this->systemeio->send_newsletter( $subject, $body_html );

			$results['email'] = is_wp_error( $result )
				? [ 'ok' => false, 'msg' => $result->get_error_message(), 'debug' => $result->get_error_data() ]
				: [ 'ok' => true ];
			if ( is_wp_error( $result ) ) $all_ok = false;
		}

		if ( in_array( 'telegram', $channels, true ) ) {
			$groups     = Calypsosub_Communications_Settings::get_telegram_groups();
			$group_keys = $category['groups'];
			$text       = $this->telegram->html_to_plain_text( $body_html );
			$results['telegram'] = [];

			if ( empty( $group_keys ) ) {
				$results['telegram'][ __( '(nessun gruppo assegnato alla categoria)', 'calypsosub' ) ] = [
					'ok' => false, 'msg' => __( 'Nessun gruppo Telegram assegnato a questa categoria.', 'calypsosub' ),
				];
				$all_ok = false;
			}

			foreach ( $group_keys as $gkey ) {
				$group = $groups[ $gkey ] ?? null;
				$label = $group['label'] ?? $gkey;
				if ( ! $group || empty( $group['chat_id'] ) ) {
					$results['telegram'][ $label ] = [ 'ok' => false, 'msg' => __( 'Gruppo senza chat_id configurato.', 'calypsosub' ) ];
					$all_ok = false;
					continue;
				}
				$result = $this->telegram->send_message( $group['chat_id'], $subject . "\n\n" . $text );
				$results['telegram'][ $label ] = is_wp_error( $result )
					? [ 'ok' => false, 'msg' => $result->get_error_message(), 'debug' => $result->get_error_data() ]
					: [ 'ok' => true ];
				if ( is_wp_error( $result ) ) $all_ok = false;
			}
		}

		update_post_meta( $post_id, '_com_results', $results );
		update_post_meta( $post_id, '_com_status', $all_ok ? 'sent' : 'failed' );
	}
}
