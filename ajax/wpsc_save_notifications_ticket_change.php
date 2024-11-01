<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save Notifications - Change Ticket
 *
 *
 */
function wpsc_save_notifications_ticket_change() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
    $wpsc_notification_ticket_change_client = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_change_client'] ) );
    $wpsc_notification_ticket_change_admin = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_change_admin'] ) );
    $wpsc_notification_ticket_change_client_enable = $_POST['wpsc_notification_ticket_change_client_enable'];
    $wpsc_notification_ticket_change_admin_enable = $_POST['wpsc_notification_ticket_change_admin_enable'];
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . esc_sql( $wpsc_notification_ticket_change_client ) . "' WHERE title='notification_ticket_change_client'";
    $wpdb->query( $sql );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . esc_sql( $wpsc_notification_ticket_change_admin ) . "' WHERE title='notification_ticket_change_admin'";
    $wpdb->query( $sql );
    $wpsc_options['wpsc_notification_ticket_change_client_enable'] = $wpsc_notification_ticket_change_client_enable;
    $wpsc_options['wpsc_notification_ticket_change_admin_enable'] = $wpsc_notification_ticket_change_admin_enable;
    update_option( 'wpsc_options', $wpsc_options );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}