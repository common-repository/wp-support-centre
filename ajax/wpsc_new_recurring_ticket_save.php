<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create New Recurring Ticket Admin
 *
 *
 */
function wpsc_new_recurring_ticket_save() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
    // save attachments
    $attachments = wpsc_save_attachments();
    // check if existing client or create new user
    $client_id = $_POST['wpsc_new_recurring_ticket_client_id'];
    $client_email = $_POST['wpsc_new_recurring_ticket_client_email'];
    if ( $client_id != 0 ) {
        if ( false !== get_user_by( 'id', $client_id ) ) {
            $user = get_user_by( 'id', $client_id );
            $first_name = get_user_meta( $client_id, 'first_name', true );
			$last_name = get_user_meta( $client_id, 'last_name', true );
			if ( false === strpos( $user->display_name, ' ' ) && ( $first_name != '' && $last_name != '' ) ) {
				$client_name = $first_name . ' ' . $last_name;
			} else {
				$client_name = $user->display_name;
			}
            $client_email = $user->user_email;
        } else if ( false !== get_user_by( 'email', $client_email ) ) {
            $user = get_user_by( 'email', $client_email );
            $client_id = $user->ID;
            $first_name = get_user_meta( $client_id, 'first_name', true );
			$last_name = get_user_meta( $client_id, 'last_name', true );
			if ( false === strpos( $user->display_name, ' ' ) && ( $first_name != '' && $last_name != '' ) ) {
				$client_name = $first_name . ' ' . $last_name;
			} else {
				$client_name = $user->display_name;
			}
        } else if ( false !== get_user_by( 'login', $client_email ) ) {
            $user = get_user_by( 'login', $client_email );
            $client_id = $user->ID;
            $first_name = get_user_meta( $client_id, 'first_name', true );
			$last_name = get_user_meta( $client_id, 'last_name', true );
			if ( false === strpos( $user->display_name, ' ' ) && ( $first_name != '' && $last_name != '' ) ) {
				$client_name = $first_name . ' ' . $last_name;
			} else {
				$client_name = $user->display_name;
			}
        } else {
            $client_id = 0;
        }
    } else if ( false !== get_user_by( 'email', $client_email ) ) {
        $user = get_user_by( 'email', $client_email );
        $client_id = $user->ID;
        $first_name = get_user_meta( $client_id, 'first_name', true );
		$last_name = get_user_meta( $client_id, 'last_name', true );
		if ( false === strpos( $user->display_name, ' ' ) && ( $first_name != '' && $last_name != '' ) ) {
			$client_name = $first_name . ' ' . $last_name;
		} else {
			$client_name = $user->display_name;
		}
    } else if ( false !== get_user_by( 'login', $client_email ) ) {
        $user = get_user_by( 'login', $client_email );
        $client_id = $user->ID;
        $first_name = get_user_meta( $client_id, 'first_name', true );
		$last_name = get_user_meta( $client_id, 'last_name', true );
		if ( false === strpos( $user->display_name, ' ' ) && ( $first_name != '' && $last_name != '' ) ) {
			$client_name = $first_name . ' ' . $last_name;
		} else {
			$client_name = $user->display_name;
		}
    } else {
        $client_id = 0;
    }
    if ( $client_id == 0 ) {
        $split_name = explode( ' ', $_POST['wpsc_admin_client_autocomplete_recurring'], 2 );
        $first_name = $split_name[0];
        $last_name = $split_name[1];
        $client_name = $_POST['wpsc_admin_client_autocomplete_recurring'];
        $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
        $args = array(
            'user_pass' => $random_password,
            'user_login' => $client_email,
            'user_email' => $client_email,
            'display_name' => $client_name,
            'first_name' => trim( $first_name ),
            'last_name' => trim( $last_name )
        );
        $client_id = wp_insert_user( $args );
        wp_new_user_notification( $client_id, null, 'both' );
    }
    $enabled = $_POST['wpsc_admin_new_recurring_ticket_enable'];
    $data = array(
        'client_id' => $client_id,
        'subject' => $_POST['wpsc_admin_new_recurring_ticket_subject'],
        'thread' => $_POST['wpsc_new_recurring_ticket_details'],
        'attachments' => $attachments,
        'status_id' => $status,
        'category_id' => $_POST['wpsc_new_recurring_ticket_category'],
        'priority_id' => $_POST['wpsc_new_recurring_ticket_priority'],
        'agent_id' => $_POST['wpsc_new_recurring_ticket_agent_id'],
        'enabled' => $enabled,
        'notify' => $_POST['wpsc_admin_new_recurring_ticket_notify'],
        'schedule' => $_POST['wpsc_new_recurring_ticket_schedule'],
        'start_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_recurring_ticket_date_from'] ) ),
        'next_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_recurring_ticket_date_from'] ) )
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
    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_tickets_recurring', $data, $format );
    $return['status'] = 'true';
    $return['ticket_id'] = $ticket_id;
    echo json_encode( $return );
    wp_die();
}