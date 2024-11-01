<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Pinned Thread
 *
 *
 */
function wpsc_pinned_thread() {
    global $wpdb;
    $return = array();
    $thread_id = $_POST['thread_id'];
    $val = $_POST['val'];
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_threads SET is_pinned='" . esc_sql( $val ) . "' WHERE id=" . esc_sql( $thread_id );
    $update = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}