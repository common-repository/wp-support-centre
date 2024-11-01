<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * apply ticket actions
 *
 *
 */
function wpsc_admin_apply_actions() {
    global $wpdb;
    $theSelectedIDs = explode( ',', $_POST['theSelectedIDs'] );
    $wpsc_ticket_action_status = $_POST['wpsc_ticket_action_status'];
    $wpsc_ticket_action_category = $_POST['wpsc_ticket_action_category'];
    $wpsc_ticket_action_agent = $_POST['wpsc_ticket_action_agent'];
    $wpsc_ticket_action_priority = $_POST['wpsc_ticket_action_priority'];
    $update_array = array();
    if ( $wpsc_ticket_action_category != '' ) {
        $update_array[] = 'category_id=' . esc_sql( $wpsc_ticket_action_category );
    }
    if ( $wpsc_ticket_action_agent != '' ) {
        $update_array[] = 'agent_id=' . esc_sql( $wpsc_ticket_action_agent );
    }
    if ( $wpsc_ticket_action_priority != '' ) {
        $update_array[] = 'priority_id=' . esc_sql( $wpsc_ticket_action_priority );
    }
    $update_array[] = "updated_timestamp='" . current_time( 'mysql', 1 ) . "'";
    $update = implode( ',', $update_array );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET " . $update . " WHERE ";
    $i = 0;
    foreach ( $theSelectedIDs as $ticket_id ) {
        $sql .= ( $i == 0 ) ? ' id=' . $ticket_id : ' OR id=' . $ticket_id;
        $i = 1;
    }
    $wpdb->query( $sql );
	if ( $wpsc_ticket_action_status != '' ) {
		foreach ( $theSelectedIDs as $ticket_id ) {
			wpsc_update_ticket_status( $ticket_id, $wpsc_ticket_action_status );
		}
	}
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}