<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save custom status colour
 *
 *
 */
function wpsc_save_status_colour() {
    global $wpdb;
    $return = array();
    $wpsc_id = trim( $_POST['wpsc_id'] );
	$wpsc_status = trim( $_POST['wpsc_status'] );
	$wpsc_prefix = trim( $_POST['wpsc_prefix'] );
    $wpsc_colour = trim( $_POST['wpsc_colour'] );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_status SET status='" . esc_sql( $wpsc_status ) . "', status_prefix='" . esc_sql( $wpsc_prefix ) . "', colour='" . esc_sql( $wpsc_colour ) . "' WHERE id=" . esc_sql( $wpsc_id );
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}