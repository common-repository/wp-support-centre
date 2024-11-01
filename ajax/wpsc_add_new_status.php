<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add New Custom Status
 *
 *
 */
function wpsc_add_new_status() {
    global $wpdb;
    $return = array();
    $wpsc_new_status = trim( $_POST['wpsc_new_status'] );
	$wpsc_new_status_subject_prefix = trim( $_POST['wpsc_new_status_subject_prefix'] );
    $wpsc_new_status_colour = trim( $_POST['wpsc_new_status_colour'] );
    $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_status (status, status_prefix, colour, custom, enabled) VALUES ('" . esc_sql( $wpsc_new_status ) . "', '" . esc_sql( $wpsc_new_status_subject_prefix ) . "', '" . esc_sql( $wpsc_new_status_colour ) . "', 1, 1)";
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}