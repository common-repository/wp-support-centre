<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Copy Thread to Ticket
 *
 *
 */
function wpsc_copy_thread_to_ticket() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $thread_id = $_POST['thread_id'];
    $ticket_id = $_POST['ticket_id'];
    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE id=" . $thread_id;
    $thread = $wpdb->get_row( $sql, ARRAY_A );
    $count = 0;
    $keys = '';
    $values = '';
    foreach ( $thread as $k => $v ) {
        if ( $k != 'id' ) {
            $keys .= ( $count == 0 ) ? $k : ',' . $k;
            if ( $k == 'ticket_id' ) {
                $values .= ( $count == 0 ) ? "'" . $ticket_id . "'" : ",'" . $ticket_id . "'";
            } else {
                $values .= ( $count == 0 ) ? "'" . esc_sql( $v ) . "'" : ",'" . esc_sql( $v ) . "'";
            }
            $count++;
        }
    }
    $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_threads (" . $keys . ") VALUES (" . $values . ")";
    $wpdb->query( $sql );
	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . esc_sql( $ticket_id );
	$ticket = $wpdb->get_row( $sql );
	if ( !is_null( $ticket ) ) {
		$ticket_timestamp = strtotime( $ticket->updated_timestamp );
		$thread_timestamp = strtotime( $thread->thread_timestamp );
		if ( $thread_timestamp > $ticket_timestamp ) {
			$sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET updated_timestamp='" . esc_sql( $thread->thread_timestamp ) . "', updated_by='" . esc_sql( $thread->author_id ) . "', status_id=5 WHERE id=" . esc_sql( $ticket_id );
			$update = $wpdb->query( $sql );
		}
	}
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}