<?php
/**
 * Plugin Name: Calypso Sub Arezzo
 * Plugin URI:  https://calypsosub.it
 * Description: Gestione uscite, eventi, corsi, docenti e prenotazioni per ASD Calypso Sub Arezzo.
 * Version:     1.0.0
 * Author:      Nicola Donati
 * Text Domain: calypsosub
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CALYPSOSUB_VERSION', '1.0.0' );
define( 'CALYPSOSUB_PATH', plugin_dir_path( __FILE__ ) );
define( 'CALYPSOSUB_URL', plugin_dir_url( __FILE__ ) );

require_once CALYPSOSUB_PATH . 'includes/helpers/functions.php';
require_once CALYPSOSUB_PATH . 'includes/taxonomies/class-tax-brevetti.php';
require_once CALYPSOSUB_PATH . 'includes/taxonomies/class-tax-livelli.php';
require_once CALYPSOSUB_PATH . 'includes/taxonomies/class-tax-media-tag.php';
require_once CALYPSOSUB_PATH . 'includes/media/class-media-overlay-field.php';
require_once CALYPSOSUB_PATH . 'includes/blocks/class-gallery-helpers.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-docenti.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-uscite.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-eventi.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-corsi.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-occorrenze.php';
require_once CALYPSOSUB_PATH . 'includes/bookings/class-booking-email.php';
require_once CALYPSOSUB_PATH . 'includes/bookings/class-booking-manager.php';
require_once CALYPSOSUB_PATH . 'includes/admin/class-admin-menus.php';
require_once CALYPSOSUB_PATH . 'includes/admin/class-email-templates.php';
require_once CALYPSOSUB_PATH . 'includes/admin/class-settings-pages.php';
require_once CALYPSOSUB_PATH . 'includes/account/class-user-account.php';
require_once CALYPSOSUB_PATH . 'includes/class-template-loader.php';
require_once CALYPSOSUB_PATH . 'includes/class-blocks.php';
require_once CALYPSOSUB_PATH . 'includes/ajax/class-ajax-eventi.php';
add_action( 'wp_ajax_calypso_eventi_search',        [ 'Calypsosub_Ajax_Eventi', 'handle' ] );
add_action( 'wp_ajax_nopriv_calypso_eventi_search', [ 'Calypsosub_Ajax_Eventi', 'handle' ] );

add_action( 'wp_enqueue_scripts', function (): void {
	wp_enqueue_style(
		'calypsosub-global',
		CALYPSOSUB_URL . 'assets/css/calypsosub-global.css',
		[],
		CALYPSOSUB_VERSION
	);
} );

add_filter( 'use_block_editor_for_post_type', function ( bool $use, string $post_type ): bool {
	$types = [ 'calypso_uscita', 'calypso_evento', 'calypso_corso', 'calypso_docente', 'calypso_prenotazione', 'calypso_occorrenza' ];
	return in_array( $post_type, $types, true ) ? false : $use;
}, 10, 2 );

( new Calypsosub_Tax_Brevetti() )->init();
( new Calypsosub_Tax_Livelli() )->init();
( new Calypsosub_Tax_Media_Tag() )->init();
( new Calypsosub_Media_Overlay_Field() )->init();
( new Calypsosub_CPT_Docenti() )->init();
( new Calypsosub_CPT_Uscite() )->init();
( new Calypsosub_CPT_Eventi() )->init();
( new Calypsosub_CPT_Corsi() )->init();
( new Calypsosub_CPT_Occorrenze() )->init();

$email_manager                       = new Calypsosub_Booking_Email();
$GLOBALS['calypsosub_booking_manager'] = new Calypsosub_Booking_Manager( $email_manager );
$GLOBALS['calypsosub_booking_manager']->init();

( new Calypsosub_Template_Loader() )->init();
( new Calypsosub_Admin_Menus() )->init();
( new Calypsosub_Settings_Pages() )->init();
( new Calypsosub_Blocks() )->init();
( new Calypsosub_Email_Templates() )->init();
( new Calypsosub_User_Account( $GLOBALS['calypsosub_booking_manager'] ) )->init();

register_activation_hook( __FILE__, 'calypsosub_activate' );
function calypsosub_activate(): void {
    flush_rewrite_rules();
    calypsosub_grant_editor_caps();
}

function calypsosub_grant_editor_caps(): void {
    foreach ( [ 'administrator', 'editor' ] as $role_name ) {
        $role = get_role( $role_name );
        if ( $role ) $role->add_cap( 'calypsosub_manage', true );
    }
}

/* Assicura che gli editor abbiano la cap anche senza re-attivazione del plugin */
add_action( 'plugins_loaded', function (): void {
    $role = get_role( 'editor' );
    if ( $role && empty( $role->capabilities['calypsosub_manage'] ) ) {
        calypsosub_grant_editor_caps();
    }
} );
