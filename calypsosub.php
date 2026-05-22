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

require_once CALYPSOSUB_PATH . 'includes/taxonomies/class-tax-brevetti.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-docenti.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-uscite.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-eventi.php';
require_once CALYPSOSUB_PATH . 'includes/post-types/class-cpt-corsi.php';
require_once CALYPSOSUB_PATH . 'includes/bookings/class-booking-email.php';
require_once CALYPSOSUB_PATH . 'includes/bookings/class-booking-manager.php';
require_once CALYPSOSUB_PATH . 'includes/admin/class-admin-menus.php';
require_once CALYPSOSUB_PATH . 'includes/admin/class-email-templates.php';
require_once CALYPSOSUB_PATH . 'includes/account/class-user-account.php';
require_once CALYPSOSUB_PATH . 'includes/helpers/functions.php';

( new Calypsosub_Tax_Brevetti() )->init();
( new Calypsosub_CPT_Docenti() )->init();
( new Calypsosub_CPT_Uscite() )->init();
( new Calypsosub_CPT_Eventi() )->init();
( new Calypsosub_CPT_Corsi() )->init();

$email_manager                  = new Calypsosub_Booking_Email();
$calypsosub_booking_manager     = new Calypsosub_Booking_Manager( $email_manager );
$calypsosub_booking_manager->init();

global $calypsosub_booking_manager;

( new Calypsosub_Admin_Menus() )->init();
( new Calypsosub_Email_Templates() )->init();
( new Calypsosub_User_Account( $calypsosub_booking_manager ) )->init();

register_activation_hook( __FILE__, 'calypsosub_activate' );
function calypsosub_activate(): void {
    flush_rewrite_rules();
}
