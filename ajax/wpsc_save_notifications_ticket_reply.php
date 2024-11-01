<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save Notifications - Reply Ticket
 *
 *
 */
function wpsc_save_notifications_ticket_reply() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
    $wpsc_notification_ticket_reply_client = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_reply_client'] ) );
    $wpsc_notification_ticket_reply_admin = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_reply_admin'] ) );
    $wpsc_notification_ticket_reply_client_enable = $_POST['wpsc_notification_ticket_reply_client_enable'];
    $wpsc_notification_ticket_reply_admin_enable = $_POST['wpsc_notification_ticket_reply_admin_enable'];
    $wpsc_reply_include = $_POST['wpsc_reply_include'];
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . esc_sql( $wpsc_notification_ticket_reply_client ) . "' WHERE title='notification_ticket_reply_client'";
    $wpdb->query( $sql );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . esc_sql( $wpsc_notification_ticket_reply_admin ) . "' WHERE title='notification_ticket_reply_admin'";
    $wpdb->query( $sql );
    $wpsc_options['wpsc_notification_ticket_reply_client_enable'] = $wpsc_notification_ticket_reply_client_enable;
    $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] = $wpsc_notification_ticket_reply_admin_enable;
    $wpsc_options['wpsc_reply_include'] = $wpsc_reply_include;
    update_option( 'wpsc_options', $wpsc_options );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}