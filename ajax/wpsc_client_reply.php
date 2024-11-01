<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create New Ticket Reply
 *
 *
 */
function wpsc_client_reply() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $ticket_id = $_POST['wpsc_ticket_id'];
    $user_id = $_POST['wpsc_uid'];
    // save attachments
    $attachments = wpsc_save_attachments();
    // update ticket
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET
        priority_id='" . esc_sql( $_POST['wpsc_front_new_thread_priority'] ) . "',
        updated_timestamp='" . current_time( 'mysql', 1 ) . "',
        updated_by='" . esc_sql( $user_id ) . "'
        WHERE id=" . esc_sql( $ticket_id );
    $update = $wpdb->query( $sql );
	wpsc_update_ticket_status( $ticket_id, 5 );
    if ( is_array( $wpsc_options['wpsc_replies'] ) ) {
        if ( !in_array( $ticket_id, $wpsc_options['wpsc_replies'] ) ) {
            array_push( $wpsc_options['wpsc_replies'], $ticket_id );
        }
    } else {
        $wpsc_options['wpsc_replies'] = array( $ticket_id );
    }
    // add thread
    if ( $user_id != 0 ) {
        $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE ID=' . $user_id );
        if ( $user ) {
            $author = $user->display_name;
            $author_email = $user->user_email;
        } else {
            $author = ( isset( $_POST['wpsc_front_new_ticket_client_name'] ) ) ? $_POST['wpsc_front_new_ticket_client_name'] : 'Guest';
            $author_email = ( isset( $_POST['wpsc_front_new_ticket_client_email'] ) ) ? $_POST['wpsc_front_new_ticket_client_email'] : 'Guest Email';;
        }
    } else {
        $author = ( isset( $_POST['wpsc_front_new_ticket_client_name'] ) ) ? $_POST['wpsc_front_new_ticket_client_name'] : 'Guest';
        $author_email = ( isset( $_POST['wpsc_front_new_ticket_client_email'] ) ) ? $_POST['wpsc_front_new_ticket_client_email'] : 'Guest Email';;
    }
	$agent_id = $wpdb->get_var( 'SELECT agent_id FROM ' . $wpdb->prefix . ' WHERE id=' . $ticket_id );
	$agent = get_userdata( $agent_id );
	$to_email = $agent->user_email;
	$cc_email = ( isset( $_POST['wpsc_front_new_thread_cc'] ) && !is_null( $_POST['wpsc_front_new_thread_cc'] ) && $_POST['wpsc_front_new_thread_cc'] !== 'null' ) ?  $_POST['wpsc_front_new_thread_cc'] : '';
	$bcc_email = ( isset( $_POST['wpsc_front_new_thread_bcc'] ) && !is_null( $_POST['wpsc_front_new_thread_bcc'] ) && $_POST['wpsc_front_new_thread_bcc'] !== 'null' ) ?  $_POST['wpsc_front_new_thread_bcc'] : '';
    $data = array(
        'ticket_id' => $ticket_id,
        'message' => base64_encode( $_POST['wpsc_front_ticket_note'] ),
        'attachments' => $attachments,
        'author_id' => $user_id,
        'author' => $author,
        'author_email' => $author_email,
        'to_email' => $to_email,
        'cc_email' => $cc_email,
        'bcc_email' => $bcc_email,
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
        '%s'
    );
    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
    $thread_id = $wpdb->insert_id;
    // send notifications
    update_option( 'wpsc_options', $wpsc_options );
    include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
    wpsc_notification( 'client_reply', $ticket_id, $thread_id );
    $return['status'] = 'true';
    $return['ticket_id'] = $ticket_id;
    $return['attachments'] = $attachments;
    echo json_encode( $return );
    wp_die();
}