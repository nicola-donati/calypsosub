<?php
/**
 * Contact Form 7 and its reCAPTCHA load their scripts on every page, even where
 * no form is shown. This keeps them only on pages that actually render a form
 * (contact page, booking page, admission request, home with its form).
 *
 * The check happens in the footer, after the page content has been rendered:
 * CF7 prints its scripts in the footer, so they can still be removed there.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Calypsosub_CF7_Assets {

	/** Script handles registered by Contact Form 7 and its reCAPTCHA integration. */
	const SCRIPT_HANDLES = [ 'contact-form-7', 'swv', 'wpcf7-recaptcha', 'google-recaptcha' ];

	private bool $form_rendered = false;

	public function init(): void {
		add_filter( 'wpcf7_form_elements', [ $this, 'mark_form_rendered' ] );
		add_action( 'wp_footer', [ $this, 'drop_unused_scripts' ], 1 );
	}

	public function mark_form_rendered( $html ) {
		$this->form_rendered = true;
		return $html;
	}

	public function drop_unused_scripts(): void {
		if ( is_admin() || $this->form_rendered ) {
			return;
		}
		if ( (bool) apply_filters( 'calypsosub_keep_cf7_scripts', false ) ) {
			return;
		}
		foreach ( self::SCRIPT_HANDLES as $handle ) {
			wp_dequeue_script( $handle );
		}
	}
}
