<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save admin general settings
 *
 *
 */
function wpsc_save_general() {

	global $wpdb;
	$return = array();

    $wpsc_options = get_option( 'wpsc_options', array() );

	array_walk( $_POST, 'trim' );

	$wpsc_rename = $_POST['wpsc_rename'];
    $wpsc_item = $_POST['wpsc_item'];
    $wpsc_client = $_POST['wpsc_client'];
    $wpsc_support_page = $_POST['wpsc_support_page'];
	$wpsc_thanks_page = $_POST['wpsc_thanks_page'];
	$wpsc_file_upload = $_POST['wpsc_file_upload'];
    $wpsc_recurring_tickets_scheduled_time = $_POST['wpsc_recurring_tickets_scheduled_time'];

	if ( !empty( $wpsc_support_page ) && $wpsc_support_page != '0' ) {
		$wpsc_support_page_url = get_permalink( $wpsc_support_page );
	} else {
		$wpsc_support_page_url = '';
	}

	$wpsc_item_history = ( isset( $wpsc_options['wpsc_item_history'] ) && is_array( $wpsc_options['wpsc_item_history'] ) ) ? $wpsc_options['wpsc_item_history'] : array();
    $wpsc_item_history[] = $wpsc_item;
	array_unique( $wpsc_item_history );

	$wpsc_options['wpsc_item_history'] = $wpsc_item_history;
    $wpsc_options['wpsc_rename'] = $wpsc_rename;
	$wpsc_options['wpsc_item'] = $wpsc_item;
    $wpsc_options['wpsc_client'] = $wpsc_client;
    $wpsc_options['wpsc_support_page'] = $wpsc_support_page;
	$wpsc_options['wpsc_support_page_url'] = $wpsc_support_page_url;
    $wpsc_options['wpsc_thanks_page'] = $wpsc_thanks_page;
	$wpsc_options['wpsc_file_upload'] = $wpsc_file_upload;
    $wpsc_options['wpsc_recurring_tickets_scheduled_time'] = $wpsc_recurring_tickets_scheduled_time;

	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_tickets_recurring";
	$result = $wpdb->get_results( $sql, OBJECT );
    if ( !is_null( $result ) && !empty( $result ) ) {
        foreach( $result as $ticket ) {
        	$next_timestamp = $ticket->next_timestamp;
			$next_timestamp = date( 'Y-m-d', strtotime( $next_timestamp ) ) . ' ' . $wpsc_recurring_tickets_scheduled_time;
			$sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets_recurring SET next_timestamp='" . esc_sql( $next_timestamp ) . "' WHERE id=" . $ticket->id;
			$update = $wpdb->query( $sql );
		}
	}

    update_option( 'wpsc_options', $wpsc_options );

    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}