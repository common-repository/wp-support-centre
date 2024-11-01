<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * save ticket changes
 *
 *
 */
function wpsc_ticket_save_changes() {
    global $wpdb;
    $ticket_id = $_POST['wpsc_ticket_id'];
    $wpsc_admin_new_thread_status = $_POST['wpsc_admin_new_thread_status'];
    $wpsc_admin_new_thread_category = $_POST['wpsc_admin_new_thread_category'];
    $wpsc_admin_new_thread_agent = $_POST['wpsc_admin_new_thread_agent'];
    $wpsc_admin_new_thread_priority = $_POST['wpsc_admin_new_thread_priority'];
    $wpsc_admin_new_thread_client_phone = $_POST['wpsc_admin_new_thread_client_phone'];
    $wpsc_ticket_subject = $_POST['wpsc_ticket_subject'];
    $update_array = array();
    if ( $wpsc_admin_new_thread_category != '' ) {
        $update_array[] = 'category_id=' . esc_sql( $wpsc_admin_new_thread_category );
    }
    if ( $wpsc_admin_new_thread_agent != '' ) {
        $update_array[] = 'agent_id=' . esc_sql( $wpsc_admin_new_thread_agent );
    }
    if ( $wpsc_admin_new_thread_priority != '' ) {
        $update_array[] = 'priority_id=' . esc_sql( $wpsc_admin_new_thread_priority );
    }
    if ( $wpsc_ticket_subject != '' ) {
        $update_array[] = "subject='" . esc_sql( $wpsc_ticket_subject) . "'";
    }
    $update_array[] = "client_phone='" . esc_sql( $wpsc_admin_new_thread_client_phone ) . "'";
    $update_array[] = "updated_by=" . get_current_user_id();
    $update_array[] = "updated_timestamp='" . current_time( 'mysql', 1 ) . "'";
    $update = implode( ',', $update_array );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET " . $update . " WHERE id=" . $ticket_id;
    $wpdb->query( $sql );
	if ( $wpsc_admin_new_thread_status != '' ) {
		wpsc_update_ticket_status( $ticket_id, $wpsc_admin_new_thread_status );
	}
	// add meta data
	if ( isset( $_POST['wpsc_additional_field'] ) ) {
		$wpsc_additional_field = json_decode( stripslashes( $_POST['wpsc_additional_field'] ) );
		foreach( $wpsc_additional_field as $additional_field ) {
			$field_id = $additional_field->field_id;
			$meta_value = $additional_field->meta_value;
			$sql = "DELETE FROM " . $wpdb->prefix . "wpsc_additional_fields_meta WHERE ticket_id=" . esc_sql( $ticket_id ) . " AND field_id='" . esc_sql( $field_id ) . "'";
			$delete = $wpdb->query( $sql );
			$sql = "INSERT INTO " . $wpdb->prefix . "wpsc_additional_fields_meta ( ticket_id, field_id, meta_value ) VALUES ( '" . esc_sql( $ticket_id ) . "', '" . esc_sql( $field_id ) . "', '" . esc_sql( $meta_value ) . "' )";
			$insert = $wpdb->query( $sql );
		}
		$wpdb->hide_errors();
	}
    // send notifications
    if ( $_POST['wpsc_notify'] == 'true' ) {
        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
        wpsc_notification( 'change_ticket', $ticket_id, 0 );
        $return['notify'] = 'true';
    }
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}