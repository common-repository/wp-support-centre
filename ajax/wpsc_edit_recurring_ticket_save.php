<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save Recurring Ticket Changes Admin
 *
 *
 */
function wpsc_edit_recurring_ticket_save() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $ticket_id = $_POST['ticket_id'];
    $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
    // save attachments
    $attachments = wpsc_save_attachments();
    // update ticket
    $enabled = $_POST['wpsc_admin_edit_recurring_ticket_enable'];
    $data = array(
        'client_id' => $_POST['wpsc_edit_recurring_ticket_client_id_' . $ticket_id],
        'subject' => $_POST['wpsc_admin_edit_recurring_ticket_subject_' . $ticket_id],
        'thread' => $_POST['wpsc_edit_recurring_ticket_details'],
        'attachments' => $attachments,
        'status_id' => $status,
        'category_id' => $_POST['wpsc_edit_recurring_ticket_category_' . $ticket_id],
        'priority_id' => $_POST['wpsc_edit_recurring_ticket_priority_' . $ticket_id],
        'agent_id' => $_POST['wpsc_edit_recurring_ticket_agent_id_' . $ticket_id],
        'enabled' => $enabled,
        'notify' => $_POST['wpsc_admin_edit_recurring_ticket_notify'],
        'schedule' => $_POST['wpsc_edit_recurring_ticket_schedule_' . $ticket_id],
        'start_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_edit_recurring_ticket_date_from_' . $ticket_id] ) ),
        'next_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_edit_recurring_ticket_date_from_' . $ticket_id] ) )
    );
    $format = array(
        '%d',
        '%s',
        '%s',
        '%s',
        '%d',
        '%d',
        '%d',
        '%d',
        '%d',
        '%d',
        '%d',
        '%s',
        '%s'
    );
    $where = array( 'id' => $ticket_id );
    $where_format = array( '%d' );
    $update = $wpdb->update( $wpdb->prefix . 'wpsc_tickets_recurring', $data, $where, $format, $where_format );
    $return['status'] = 'true';
    $return['ticket_id'] = $ticket_id;
    echo json_encode( $return );
    wp_die();
}