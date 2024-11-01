<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * apply recurring ticket actions
 *
 *
 */
function wpsc_admin_apply_recurring_actions() {
    global $wpdb;
    $theSelectedIDs = explode( ',', $_POST['theSelectedIDs'] );
    $wpsc_recurring_action_status = $_POST['wpsc_recurring_action_status'];
    $wpsc_recurring_action_category = $_POST['wpsc_recurring_action_category'];
    $wpsc_recurring_action_priority = $_POST['wpsc_recurring_action_priority'];
    $wpsc_recurring_action_agent = $_POST['wpsc_recurring_action_agent'];
    $update_array = array();
    if ( $wpsc_recurring_action_status != '' ) {
        $update_array[] = 'status_id=' . esc_sql( $wpsc_recurring_action_status );
    }
    if ( $wpsc_recurring_action_category != '' ) {
        $update_array[] = 'category_id=' . esc_sql( $wpsc_recurring_action_category );
    }
    if ( $wpsc_recurring_action_priority != '' ) {
        $update_array[] = 'priority_id=' . esc_sql( $wpsc_recurring_action_priority );
    }
    if ( $wpsc_recurring_action_agent != '' ) {
        $update_array[] = 'agent_id=' . esc_sql( $wpsc_recurring_action_agent );
    }
    $update = implode( ',', $update_array );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets_recurring SET " . $update . " WHERE ";
    $i = 0;
    foreach ( $theSelectedIDs as $ticket_id ) {
        $sql .= ( $i == 0 ) ? ' id=' . $ticket_id : ' OR id=' . $ticket_id;
        $i = 1;
    }
    $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}