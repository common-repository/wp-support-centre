<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Resend Thread Notification
 *
 *
 */
function wpsc_resend_thread_notifications() {
    global $wpdb;
    $ticket_id = $_POST['ticket_id'];
    $thread_id = $_POST['thread_id'];
    include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
    $return = wpsc_notification( 'resend_notification', $ticket_id, $thread_id );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}