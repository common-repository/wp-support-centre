<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add New Priority
 *
 *
 */
function wpsc_add_new_priority() {
    global $wpdb;
    $return = array();
    $wpsc_new_priority = trim( $_POST['wpsc_new_priority'] );
	$wpsc_new_priority_sla = trim( $_POST['wpsc_new_priority_sla'] );
    $wpsc_new_priority_colour = trim( $_POST['wpsc_new_priority_colour'] );
    $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_priority (priority, colour, custom, enabled) VALUES ('" . esc_sql( $wpsc_new_priority ) . "', '" . esc_sql( $wpsc_new_priority_sla ) . "', '" . esc_sql( $wpsc_new_priority_colour ) . "', 1, 1)";
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}