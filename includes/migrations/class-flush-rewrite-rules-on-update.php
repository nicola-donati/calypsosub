<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Uploading a new plugin zip over an existing install never fires
 * register_activation_hook (WordPress only runs that on an explicit
 * activate), so the flush_rewrite_rules() in calypsosub_activate() is
 * skipped on every normal update. Any release that touches a CPT's slug,
 * rewrite rules or has_archive (uscite, eventi, occorrenze have all done
 * this recently) then leaves stale rules behind and every archive page
 * 404s until someone opens Impostazioni → Permalink and saves. This runs
 * the same flush once per version bump instead, without requiring that.
 */
class Calypsosub_Flush_Rewrite_Rules_On_Update {

	private const OPTION = 'calypsosub_rewrite_rules_version';

	public function init(): void {
		add_action( 'admin_init', [ $this, 'maybe_flush' ] );
	}

	public function maybe_flush(): void {
		if ( get_option( self::OPTION ) === CALYPSOSUB_VERSION ) return;
		if ( ! current_user_can( 'manage_options' ) ) return;

		flush_rewrite_rules();
		update_option( self::OPTION, CALYPSOSUB_VERSION );
	}
}
