<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get Ticket
 *
 *
 */
function wpsc_get_admin_ticket() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $ticket_id = $_POST['ticket_id'];
    include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket.php' );
    $ticket = wpsc_get_the_admin_ticket( $ticket_id );
    $return['status'] = 'true';
    $return['ticket'] = $ticket;
    echo json_encode( $return );
    wp_die();
}