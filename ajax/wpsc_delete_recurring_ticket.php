<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Delete Recurring Ticket Admin
 *
 *
 */
function wpsc_delete_recurring_ticket() {
    global $wpdb;
    $ticket_id = $_POST['ticket_id'];
    $where = array( 'id' => $ticket_id );
    $where_format = array( '%d' );
    $delete = $wpdb->delete( $wpdb->prefix . 'wpsc_tickets_recurring', $where, $where_format );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}