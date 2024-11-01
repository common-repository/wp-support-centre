<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create New Ticket Thread
 *
 *
 */
function wpsc_new_note() {
    global $wpdb;
    $ticket_id = $_POST['wpsc_ticket_id'];
    $notify = $_POST['wpsc_notify'];
    // save attachments
    $attachments = wpsc_save_attachments();
    // identify user
    if ( $_POST['wpsc_admin_thread_create_as'] == 'agent' ) {
        $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE ID=' . $_POST['wpsc_admin_new_thread_agent'] );
        $author_id = $user->ID;
        $author = $user->display_name;
        $author_email = $user->user_email;
    } else if ( $_POST['wpsc_admin_thread_create_as'] == 'client' ) {
        $author_id = $wpdb->get_var( "SELECT client_id FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . $ticket_id );
        $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE ID=' . $author_id );
        $author = $user->display_name;
        $author_email = $user->user_email;
    } else {
        $author_email = $_POST['wpsc_admin_thread_from_email'];
        $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE user_email=' . $author_email );
        if ( null !== $user ) {
            $author_id = $user->ID;
            $author = $user->display_name;
        } else {
            $author_id = 0;
            $author = $_POST['wpsc_admin_thread_from_name'];
        }
    }
    // update ticket
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET
        subject='" . $_POST['wpsc_ticket_subject'] . "',
        category_id='" . $_POST['wpsc_admin_new_thread_category'] . "',
        agent_id='" . $_POST['wpsc_admin_new_thread_agent'] . "',
        priority_id='" . $_POST['wpsc_admin_new_thread_priority'] . "',
        client_phone='" . $_POST['wpsc_admin_new_thread_client_phone'] . "',
        updated_timestamp='" . current_time( 'mysql', 1 ) . "',
        updated_by='" . $author_id . "'
        WHERE id=" . $ticket_id;
    $update = $wpdb->query( $sql );
	wpsc_update_ticket_status( $ticket_id, $_POST['wpsc_admin_new_thread_status'] );
    // add thread
    $to_email = '';
    if ( $notify == 'true' ) {
    	$notification = 1;
		$to_array = ( isset( $_POST['wpsc_admin_new_thread_to'] ) && $_POST['wpsc_admin_new_thread_to'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_thread_to'] ) : array();
		if ( is_array( $to_array ) && !empty( $to_array ) ) {
	        $to = array();
	        foreach ( $to_array as $to_item ) {
	            $user = get_user_by( 'email', $to_item );
	            if ( $user ) {
	                $to[] = $user->display_name . ' <' . $to_item . '>';
	            } else {
	                $to[] = $to_item;
	            }
	        }
		}
		$to_email = implode( ',', $to );
    } else {
		$notification = 0;
		$to_email = '';
    }
	$cc_email = ( isset( $_POST['wpsc_admin_new_thread_cc'] ) && !is_null( $_POST['wpsc_admin_new_thread_cc'] ) && $_POST['wpsc_admin_new_thread_cc'] !== 'null' ) ?  $_POST['wpsc_admin_new_thread_cc'] : '';
	$bcc_email = ( isset( $_POST['wpsc_admin_new_thread_bcc'] ) && !is_null( $_POST['wpsc_admin_new_thread_bcc'] ) && $_POST['wpsc_admin_new_thread_bcc'] !== 'null' ) ?  $_POST['wpsc_admin_new_thread_bcc'] : '';
    $data = array(
        'ticket_id' => $ticket_id,
        'message' => base64_encode( $_POST['wpsc_admin_ticket_note'] ),
        'attachments' => $attachments,
        'author_id' => $author_id,
        'author' => $author,
        'author_email' => $author_email,
        'to_email' => $to_email,
        'cc_email' => $cc_email,
        'bcc_email' => $bcc_email,
        'is_private' => $_POST['wpsc_admin_thread_is_private'],
        'notification' => $notification,
        'thread_timestamp' => current_time( 'mysql', 1 )
    );
    $format = array(
        '%d',
        '%s',
        '%s',
        '%d',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%d',
        '%d',
        '%s'
    );
    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
    $thread_id = $wpdb->insert_id;
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
	}
    // send notifications
    if ( $notify == 'true' ) {
        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
        wpsc_notification( 'reply_ticket', $ticket_id, $thread_id );
    }
    $return['status'] = 'true';
    $return['ticket_id'] = $ticket_id;
    echo json_encode( $return );
    wp_die();
}