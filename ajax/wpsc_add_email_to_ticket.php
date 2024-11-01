<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add Email To Ticket
 *
 *
 */
function wpsc_add_email_to_ticket() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $uid = $_POST['uid'];
    $account_id = $_POST['account_id'];
    $ticket_id = $_POST['ticket_id'];
    $transient = wpsc_imap_get_by_id( $uid, $account_id );
	$thread_timestamp = get_gmt_from_date( $transient['timestamp'], $format = 'Y-m-d H:i:s' );
	$attachments = explode( ',', $transient['attach'] );
    // add thread
    $data = array(
        'ticket_id' => $ticket_id,
        'message' => $transient['message'],
        'attachments' => $transient['attach'],
        'author_id' => $transient['user']['user_id'],
        'author' => $transient['user']['author'],
        'author_email' => $transient['user']['author_email'],
        'to_email' => $transient['to_email'],
        'cc_email' => $transient['cc_email'],
        'bcc_email' => $transient['bcc_email'],
        'thread_timestamp' => $thread_timestamp
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
	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . esc_sql( $ticket_id );
	$ticket = $wpdb->get_row( $sql );
	if ( !is_null( $ticket ) ) {
		$ticket_timestamp_unix = strtotime( $ticket->updated_timestamp );
		$thread_timestamp_unix = strtotime( $thread_timestamp );
		if ( $thread_timestamp_unix > $ticket_timestamp_unix ) {
			$sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET updated_timestamp='" . esc_sql( $thread_timestamp ) . "', updated_by='" . esc_sql( $transient['user']['user_id'] ) . "', status_id=5 WHERE id=" . esc_sql( $ticket_id );
			$update = $wpdb->query( $sql );
		}
	}
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}