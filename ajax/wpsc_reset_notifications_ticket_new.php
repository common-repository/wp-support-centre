<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Reset Notifications - New Ticket
 *
 *
 */
function wpsc_reset_notifications_ticket_new() {
    global $wpdb;
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification=default_notification WHERE title='notification_ticket_new_admin' OR title='notification_ticket_new_client'";
    $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}