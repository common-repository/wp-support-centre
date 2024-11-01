<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create New Ticket Admin
 *
 *
 */
function wpsc_admin_new_ticket_save() {
    global $wpdb;
    $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
    // save attachments
    $existing_attachments = $_POST['wpsc_admin_new_ticket_existing_attachments'];
    $attachments = wpsc_save_attachments();
    if ( $existing_attachments != '' ) {
        $attachments = ( $attachments != '' ) ? $existing_attachments . ',' . $attachments : $existing_attachments;
    }
    // check if existing client or create new user
    $client_id = $_POST['wpsc_admin_new_ticket_client_id'];
    $client_email = $_POST['wpsc_admin_new_ticket_client_email'];
    $client_phone = isset( $_POST['wpsc_admin_new_ticket_phone'] ) ? $_POST['wpsc_admin_new_ticket_phone'] : '';
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
        $split_name = explode( ' ', $_POST['wpsc_admin_new_ticket_client'], 2 );
        $first_name = $split_name[0];
        $last_name = $split_name[1];
        $client_name = $_POST['wpsc_admin_new_ticket_client'];
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
    // create ticket
    if ( $_POST['wpsc_admin_new_ticket_timestamp'] != '' ) {
        $timestamp = $_POST['wpsc_admin_new_ticket_timestamp'];
    } else {
        $timestamp = current_time( 'mysql', 1 );
    }
    $data = array(
        'subject' => $_POST['wpsc_admin_new_ticket_subject'],
        'client_id' => $client_id,
        'client' => $client_name,
        'client_email' => $client_email,
        'client_phone' => $client_phone,
        'category_id' => $_POST['wpsc_new_ticket_category'],
        'agent_id' => $_POST['wpsc_new_ticket_agent'],
        'priority_id' => $_POST['wpsc_new_ticket_priority'],
        'created_timestamp' => $timestamp,
        'updated_timestamp' => $timestamp,
        'updated_by'=> $_POST['wpsc_new_ticket_agent']
    );
    $format = array(
        '%s',
        '%d',
        '%s',
        '%s',
        '%s',
        '%d',
        '%d',
        '%d',
        '%s',
        '%s',
        '%d'
    );
    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_tickets', $data, $format );
    // add thread
    $ticket_id = $wpdb->insert_id;
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
	wpsc_update_ticket_status( $ticket_id, $status );
    $notification = ( $_POST['action_type'] == 'wpsc_admin_new_ticket_save_notify' ) ? 1 : 0;
	$cc_email = '';
	$cc1 = ( isset( $_POST['wpsc_admin_new_ticket_cc'] ) && $_POST['wpsc_admin_new_ticket_cc'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_ticket_cc'] ) : array();
	if ( isset( $_POST['wpsc_admin_new_ticket_cc_select'] ) && !is_null( $_POST['wpsc_admin_new_ticket_cc_select'] ) && $_POST['wpsc_admin_new_ticket_cc_select'] != 'null' ) {
		if ( is_array( $_POST['wpsc_admin_new_ticket_cc_select'] ) ) {
			$cc = array_merge( $cc1, $_POST['wpsc_admin_new_ticket_cc_select'] );
		} else {
			$cc2 = explode( ',', $_POST['wpsc_admin_new_ticket_cc_select'] );
			$cc = ( is_array( $cc2 ) ) ? array_merge( $cc1, $cc2 ) : $cc1;
		}
	} else {
		$cc = array();
	}
	$cc_email = ( is_array( $cc ) ) ? implode( ',', $cc ) : '';
	$bcc_email = '';
	$bcc1 = ( isset( $_POST['wpsc_admin_new_ticket_bcc'] ) && $_POST['wpsc_admin_new_ticket_bcc'] != '' && !is_null( $_POST['wpsc_admin_new_ticket_bcc'] ) ) ? explode( ',', $_POST['wpsc_admin_new_ticket_bcc'] ) : array();
	if ( isset( $_POST['wpsc_admin_new_ticket_bcc_select'] ) && !is_null( $_POST['wpsc_admin_new_ticket_bcc_select'] ) && $_POST['wpsc_admin_new_ticket_bcc_select'] != 'null' ) {
		if ( is_array( $_POST['wpsc_admin_new_ticket_bcc_select'] ) ) {
			$bcc = array_merge( $bcc1, $_POST['wpsc_admin_new_ticket_bcc_select'] );
		} else {
			$bcc2 = explode( ',', $_POST['wpsc_admin_new_ticket_bcc_select'] );
			$bcc = ( is_array( $bcc2 ) ) ? array_merge( $bcc1, $bcc2 ) : $bcc1;
		}
	} else {
		$bcc = array();
	}
	$bcc_email = ( is_array( $bcc ) ) ? implode( ',', $bcc ) : '';
    $data = array(
        'ticket_id' => $ticket_id,
        'message' => base64_encode( $_POST['wpsc_admin_new_ticket_details'] ),
        'attachments' => $attachments,
        'author_id' => $_POST['wpsc_admin_new_ticket_client_id'],
        'author' => $_POST['wpsc_admin_new_ticket_client'],
        'author_email' => $_POST['wpsc_admin_new_ticket_client_email'],
        'to_email' => $client_email,
        'cc_email' => $cc_email,
        'bcc_email' => $bcc_email,
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
        '%s'
    );
    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
    $thread_id = $wpdb->insert_id;
    // send notifications
    if ( $_POST['action_type'] == 'wpsc_admin_new_ticket_save_notify' ) {
        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
        wpsc_notification( 'new_ticket', $ticket_id, $thread_id );
    }
    $return['status'] = 'true';
    $return['ticket_id'] = $ticket_id;
    echo json_encode( $return );
    wp_die();
}