<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create New Ticket Front
 *
 *
 */
function wpsc_front_new_ticket_save() {
    global $wpdb;
	$post = serialize( $_POST );
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
    // save attachments
    $attachments = wpsc_save_attachments();
    $client_id = $_POST['wpsc_front_new_ticket_client_id'];
    $client_email = $_POST['wpsc_front_new_ticket_client_email'];
    $client_phone = isset( $_POST['wpsc_front_new_ticket_client_phone'] ) ? $_POST['wpsc_front_new_ticket_client_phone'] : '';
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
        if ( isset( $_POST['wpsc_front_new_ticket_client_name'] ) && $_POST['wpsc_front_new_ticket_client_name'] != '' ) {
            if ( strpos( $_POST['wpsc_front_new_ticket_client_name'], ' ') > 0 ) {
                $split_name = explode( ' ', $_POST['wpsc_front_new_ticket_client_name'], 2 );
                $first_name = $split_name[0];
                $last_name = $split_name[1];
            } else {
                $first_name = $_POST['wpsc_front_new_ticket_client_name'];
                $last_name = '';
            }
        } else {
            $first_name = $_POST['wpsc_front_new_ticket_client_first_name'];
            $last_name = $_POST['wpsc_front_new_ticket_client_last_name'];
        }
        $client_name = trim( $first_name . ' ' . $last_name );
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
        $return['new_user'] = 'true';
    } else {
        $return['new_user'] = 'false';
    }
    // select agent
    if ( $wpsc_options['wpsc_default_agent'] == 's' ) {
        $users = array();
        $all_users = get_users();
        foreach ( $all_users as $user ) {
            if ( $user->has_cap( 'manage_wpsc_agent' ) ) {
                $users[] = $user->ID;
            }
        }
        $rand = array_rand( $users );
        $agent_id = $users[$rand];
    } else if ( $wpsc_options['wpsc_default_agent'] == 'a' ) {
        $users = array();
        $all_users = get_users();
        foreach ( $all_users as $user ) {
            if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
                $users[] = $user->ID;
            }
        }
        $rand = array_rand( $users );
        $agent_id = $users[$rand];
    } else {
        $agent_id = $wpsc_options['wpsc_default_agent'];
    }
	$agent = get_userdata( $agent_id );
	$to_email = $agent->user_email;
    // create ticket
    $data = array(
        'subject' => $_POST['wpsc_front_new_ticket_subject'],
        'client_id' => $client_id,
        'client' => $client_name,
        'client_email' => $client_email,
        'client_phone' => $client_phone,
        'category_id' => $_POST['wpsc_front_new_ticket_category'],
        'agent_id' => $agent_id,
        'priority_id' => $_POST['wpsc_front_new_ticket_priority'],
        'created_timestamp' => current_time( 'mysql', 1 ),
        'updated_timestamp' => current_time( 'mysql', 1 ),
        'updated_by'=> $client_id
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
	$ticket_id = $wpdb->insert_id;
	wpsc_update_ticket_status( $ticket_id, $status );
	// add meta data
	if ( isset( $_POST['wpsc_additional_field'] ) ) {
		$wpsc_additional_field = json_decode( stripslashes( $_POST['wpsc_additional_field'] ) );
		foreach( $wpsc_additional_field as $additional_field ) {
			$field_id = $additional_field->field_id;
			$meta_value = $additional_field->meta_value;
			$sql = "INSERT INTO " . $wpdb->prefix . "wpsc_additional_fields_meta ( ticket_id, field_id, meta_value ) VALUES ( '" . esc_sql( $ticket_id ) . "', '" . esc_sql( $field_id ) . "', '" . esc_sql( $meta_value ) . "' )";
			$insert = $wpdb->query( $sql );
		}
	}
    // add thread
    if ( is_array( $wpsc_options['wpsc_new_tickets'] ) ) {
        if ( !in_array( $ticket_id, $wpsc_options['wpsc_new_tickets'] ) ) {
            array_push( $wpsc_options['wpsc_new_tickets'], $ticket_id );
        }
    } else {
        $wpsc_options['wpsc_new_tickets'] = array( $ticket_id );
    }
	$to_array = ( isset( $_POST['wpsc_front_new_thread_to'] ) && $_POST['wpsc_front_new_thread_to'] != '' ) ? explode( ',', $_POST['wpsc_front_new_thread_to'] ) : array();
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
	$to_email = ( is_array( $to ) ) ? implode( ',', $to ) : $to;
    $data = array(
        'ticket_id' => $ticket_id,
        'message' => base64_encode( $_POST['wpsc_front_new_ticket_details'] ),
        'attachments' => $attachments,
        'author_id' => $client_id,
        'author' => $client_name,
        'author_email' => $client_email,
        'to_email' => $to_email,
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
        '%s'
    );
    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
    $thread_id = $wpdb->insert_id;
    // send notifications
    update_option( 'wpsc_options', $wpsc_options );
    include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
    wpsc_notification( 'new_ticket_front', $ticket_id, $thread_id );
    $return['status'] = 'true';
    $return['ticket_id'] = $ticket_id;
    $return['uid'] = $client_id;
    echo json_encode( $return );
    wp_die();
}