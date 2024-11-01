<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get Recurring Ticket
 *
 *
 */
function wpsc_get_recurring_ticket() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $ticket_id = $_POST['ticket_id'];
    include_once( WPSC_PLUGIN_DIR . '/includes/get_recurring_ticket.php' );
    $ticket = wpsc_get_the_recurring_ticket( $ticket_id );
    if ( $ticket != 'false' ) {
        $return['status'] = 'true';
        $return['ticket'] = $ticket;
    } else {
        $return['status'] = 'false';
    }
    echo json_encode( $return );
    wp_die();
}