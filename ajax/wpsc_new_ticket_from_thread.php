<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create New Ticket From Thread
 *
 *
 */
function wpsc_new_ticket_from_thread() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $thread_id = $_POST['thread_id'];
    $client_id = $_POST['client_id'];
    if ( $client_id != 0 ) {
        if ( false !== get_user_by( 'id', $client_id ) ) {
            $user = get_user_by( 'id', $client_id );
            $client_name = $user->display_name;
            $client_email = $user->user_email;
        } else {
            $client_id = 0;
        }
    }
    if ( $client_id == 0 ) {
        $return['status'] = 'false';
        echo json_encode( $return );
        wp_die();
    }
    // select thread contents
    $sql = "SELECT message FROM " . $wpdb->prefix . "wpsc_threads WHERE id=" . $thread_id;
    $thread = $wpdb->get_var( $sql );
    $return['status'] = 'true';
    $return['client'] = $client_name;
    $return['client_email'] = $client_email;
    $return['thread'] = base64_encode( $thread );
    echo json_encode( $return );
    wp_die();
}