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
		// The primary trigger is Calypsosub_CPT_Communications::save_meta()
		// calling self::schedule() directly (a plain method call, right
		// after it persists _com_category/_com_channels — no dependency on
		// WordPress's hook-firing order for the normal "edit in admin,
		// click Pubblica" path).
		//
		// This hook exists ONLY for a "Programma per..." (scheduled)
		// communication's actual future→publish transition, which happens
		// later from WP-Cron's own check_and_publish_future_post() — a
		// request with no $_POST/nonce at all, so save_meta() no-ops on its
		// very first guard and never reaches its own schedule() call.
		//
		// It must NOT fire during a normal admin form submission: WordPress
		// calls publish_{post_type} BEFORE save_post_{post_type} on every
		// publish (direct or transition), so if this ran unconditionally
		// here it would lock the post (schedule() sets _com_dispatched)
		// before save_meta() ever got to persist _com_category/_com_channels
		// — reproducing the exact bug this whole mechanism exists to avoid.
		// maybe_dispatch_on_publish() below checks for the form's own nonce
		// to tell the two cases apart.
		add_action( 'publish_' . Calypsosub_CPT_Communications::POST_TYPE, [ $this, 'maybe_dispatch_on_publish' ] );
		add_action( self::DISPATCH_HOOK, [ $this, 'dispatch' ] );
	}

	public function maybe_dispatch_on_publish( int $post_id ): void {
		// Present only on a real admin form submission (the edit screen's
		// own meta box renders it) — never on a WP-Cron request. When it's
		// there, save_meta() is about to run in this same request (on
		// save_post_{post_type}, which always fires after this hook) and
		// will call schedule() itself once category/channels are safely
		// persisted. Calling it here too would be redundant at best, and a
		// race at worst — so skip entirely rather than rely on schedule()'s
		// idempotency to paper over it.
		if ( isset( $_POST[ Calypsosub_CPT_Communications::POST_TYPE . '_nonce' ] ) ) {
			return;
		}
		if ( get_post_meta( $post_id, '_com_dispatched', true ) ) return;
		self::schedule( $post_id );
	}

	/**
	 * Segna la comunicazione come "in invio" e accoda il dispatch vero sul
	 * cron. Usato sia al primo invio (chiamato direttamente da
	 * Calypsosub_CPT_Communications::save_meta()) sia da un retry manuale su
	 * una comunicazione già fallita (vedi Calypsosub_CPT_Communications).
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

		// Scelte col picker dedicato (vedi Calypsosub_CPT_Communications),
		// non inserite nel corpo: la dimensione si risolve qui,
		// indipendentemente da quale anteprima l'operatore vedeva nel picker
		// — l'editor classico inserirebbe di default una versione ridotta,
		// questo evita del tutto quel problema. Telegram riceve l'originale
		// ('full'); la mail la versione 'large' (~1024px), più che
		// sufficiente per una colonna email da 600px e molto più leggera.
		$image_ids  = (array) get_post_meta( $post_id, '_com_image_ids', true );
		$image_urls = array_values( array_filter( array_map(
			static fn( $id ) => wp_get_attachment_image_url( (int) $id, 'full' ),
			$image_ids
		) ) );

		if ( in_array( 'email', $channels, true ) ) {
			// Immagini DOPO il testo, a larghezza fissa 600px (standard
			// email): con l'originale in cima e solo max-width:100%, in Gmail
			// la foto riempiva lo schermo e il testo finiva fuori vista
			// (segnalato 2026-11-13). L'attributo width serve per i client
			// che ignorano il CSS (Outlook desktop).
			// Un'immagine più stretta di 600px resta alla sua larghezza
			// invece di essere ingrandita.
			$email_images = '';
			foreach ( $image_ids as $id ) {
				$src = wp_get_attachment_image_src( (int) $id, 'large' );
				if ( ! $src ) continue;
				$width = min( 600, (int) $src[1] ?: 600 );
				$email_images .= '<p><img src="' . esc_url( $src[0] ) . '" width="' . $width . '" alt="" style="display:block;width:100%;max-width:' . $width . 'px;height:auto;border:0"></p>';
			}
			$email_body = $body_html . $email_images;

			$tag = (string) $category['tag_systemeio'];
			$result = $tag !== ''
				? $this->systemeio->send_newsletter( $subject, $email_body, $tag )
				: new WP_Error( 'calypso_com_no_tag', __( 'Nessun tag systeme.io impostato per questa categoria.', 'calypsosub' ) );

			$results['email'] = is_wp_error( $result )
				? [ 'ok' => false, 'msg' => $result->get_error_message(), 'debug' => $result->get_error_data() ]
				: [ 'ok' => true ];
			if ( is_wp_error( $result ) ) $all_ok = false;
		}

		if ( in_array( 'telegram', $channels, true ) ) {
			$groups     = Calypsosub_Communications_Settings::get_telegram_groups();
			$group_keys = $category['groups'];
			$message    = $this->telegram->html_to_message( $body_html );
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
				$result = $this->telegram->send_communication( $group['chat_id'], $subject, $message['text'], $message['entities'], $image_urls );
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
