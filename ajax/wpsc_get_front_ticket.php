<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get Front Ticket
 *
 *
 */
function wpsc_get_front_ticket() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $ticket_id = $_POST['ticket_id'];
    include_once( WPSC_PLUGIN_DIR . '/includes/get_front_ticket.php' );
    $ticket = wpsc_get_the_front_ticket( $ticket_id );
    $return['status'] = 'true';
    $return['ticket'] = $ticket;
    echo json_encode( $return );
    wp_die();
}