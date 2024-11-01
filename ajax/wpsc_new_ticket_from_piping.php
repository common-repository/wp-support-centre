<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create New Ticket From Piping
 *
 *
 */
function wpsc_new_ticket_from_piping() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $email_id = $_POST['email_id'];
    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_piping_preview WHERE id=" . $email_id;
    $select = $wpdb->get_row( $sql );
    if ( !is_null( $select ) ) {
        $return['status'] = 'true';
        $return['client_id'] = $select->author_id;
        $return['client'] = $select->author;
        $return['client_email'] = $select->author_email;
        $return['subject'] = $select->subject;
        $return['thread'] = base64_decode( $select->message );
        $return['attachments'] = $select->attachments;
        $return['timestamp'] = $select->thread_timestamp;
    } else {
        $return['status'] = 'false';
    }
    echo json_encode( $return );
    wp_die();
}