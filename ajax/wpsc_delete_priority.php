<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Delete Priority
 *
 *
 */
function wpsc_delete_priority() {
    global $wpdb;
    $return = array();
    $wpsc_id = trim( $_POST['wpsc_id'] );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET enabled=0 WHERE id=" . esc_sql( $wpsc_id );
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}