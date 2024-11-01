<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Reset Notifications - Change Ticket
 *
 *
 */
function wpsc_reset_notifications_ticket_change() {
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification=default_notification WHERE title='notification_ticket_change_admin' OR title='notification_ticket_change_client'";
    $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}