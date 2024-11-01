<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Share Ticket
 *
 *
 */
function wpsc_ticket_shared_users() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
    $ticket_id = $_POST['ticket_id'];
    $wpsc_ticket_shared_users = $_POST['wpsc_ticket_shared_users'];
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET shared_users='" . esc_sql( $wpsc_ticket_shared_users ) . "' WHERE id=" . esc_sql( $ticket_id );
    $update = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}