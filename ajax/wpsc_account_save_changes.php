<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save User Account Information
 *
 *
 */
function wpsc_account_save_changes() {
    global $wpdb;
    $user_id = $_POST['wpsc_user_id'];
    $account_id = $_POST['wpsc_account_id'];
    // save attachments
    $attachments = wpsc_save_attachments();
    // update or insert account information
    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_account WHERE user_id=" . $account_id;
    $result = $wpdb->get_row( $sql );
    if ( null !== $result ) {
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_account SET
            content='" . $_POST['wpsc_account_information'] . "',
            attachments='" . $attachments . "',
            updated_timestamp='" . current_time( 'mysql', 1 ) . "',
            updated_by='" . $user_id . "'
            WHERE user_id=" . $account_id;
        $update = $wpdb->query( $sql );
    } else {
        $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_account (user_id,content,attachments,created_timestamp,updated_timestamp,updated_by) VALUES (" . esc_sql( $account_id ) . ",'" . esc_sql( $_POST['wpsc_account_information'] ) . "','" . esc_sql( $attachments ) . "','" . current_time( 'mysql', 1 ) . "','" . current_time( 'mysql', 1 ) . "'," . esc_sql( $user_id ) . ")";
        $insert = $wpdb->query( $sql );
    }

    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}