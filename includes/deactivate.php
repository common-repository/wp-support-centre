<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
wp_clear_scheduled_hook( 'wpsc_recurring_tickets' );