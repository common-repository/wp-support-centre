<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Delete Attachment
 *
 *
 */
function wpsc_delete_attachment() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
    $attachment_id = $_POST['attachment_id'];
    wp_delete_attachment( $attachment_id, true );
    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE attachments LIKE '%" . $attachment_id . "%'";
    $result = $wpdb->get_results( $sql );
    if ( null !== $result ) {
        foreach ( $result as $thread ) {
            $attachments = explode( ',', $thread->attachments );
            if ( ( $key = array_search( $attachment_id, $attachments ) ) !== false ) {
                unset( $attachments[$key] );
                $new_attachments = implode( ',', $attachments );
                $sql = "UPDATE " . $wpdb->prefix . "wpsc_threads SET attachments='" . esc_sql( $new_attachments ) . "' WHERE id=" . esc_sql( $thread->id );
                $update = $wpdb->query( $sql );
            }
        }
    }
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}