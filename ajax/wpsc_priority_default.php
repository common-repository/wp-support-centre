<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Set Default Priority
 *
 *
 */
function wpsc_priority_default() {
    global $wpdb;
    $return = array();
    $wpsc_id = trim( $_POST['wpsc_id'] );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET is_default=0";
    $result = $wpdb->query( $sql );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET is_default=1 WHERE id=" . esc_sql( $wpsc_id );
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}