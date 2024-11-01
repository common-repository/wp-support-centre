<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save custom priority colour
 *
 *
 */
function wpsc_save_priority_colour() {
    global $wpdb;
    $return = array();
    $wpsc_id = trim( $_POST['wpsc_id'] );
    $wpsc_priority = trim( $_POST['wpsc_priority'] );
    $wpsc_sla = trim( $_POST['wpsc_sla'] );
	$wpsc_colour = trim( $_POST['wpsc_colour'] );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET priority='" . esc_sql( $wpsc_priority ) . "', priority_sla='" . esc_sql( $wpsc_sla ) . "', colour='" . esc_sql( $wpsc_colour ) . "' WHERE id=" . esc_sql( $wpsc_id );
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}