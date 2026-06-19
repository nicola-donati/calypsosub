<?php
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'ABSPATH', '/tmp/' );
define( 'CALYPSOSUB_VERSION', '1.0.0' );
define( 'CALYPSOSUB_PATH', dirname( __DIR__ ) . '/' );
define( 'CALYPSOSUB_URL', 'http://localhost/' );

require_once CALYPSOSUB_PATH . 'includes/bookings/class-booking-manager.php';
require_once CALYPSOSUB_PATH . 'includes/bookings/class-booking-email.php';
require_once CALYPSOSUB_PATH . 'includes/helpers/functions.php';
require_once CALYPSOSUB_PATH . 'includes/blocks/class-gallery-helpers.php';
