<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save admin email settings
 *
 *
 */
function wpsc_save_email() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
	$return = array();
    $wpsc_email_from_name = trim( $_POST['wpsc_email_from_name'] );
    $wpsc_email_from_email = trim( $_POST['wpsc_email_from_email'] );
    $wpsc_email_reply_to = trim( $_POST['wpsc_email_reply_to'] );
    $wpsc_use_agent_email = trim( $_POST['wpsc_use_agent_email'] );
	$wpsc_email_method = trim( $_POST['wpsc_email_method'] );
	$wpsc_options['wpsc_email_method'] = $wpsc_email_method;
	if ( $wpsc_email_method == 1 ) {
		$wpsc_enable_email_piping = trim( $_POST['wpsc_enable_email_piping'] );
    	$wpsc_enable_email_piping_catch_all = trim( $_POST['wpsc_enable_email_piping_catch_all'] );
    	$wpsc_email_piping = trim( $_POST['wpsc_email_piping'] );
		$wpsc_options['wpsc_enable_email_piping'] = $wpsc_enable_email_piping;
    	$wpsc_options['wpsc_enable_email_piping_catch_all'] = $wpsc_enable_email_piping_catch_all;
    	$wpsc_options['wpsc_email_piping'] = $wpsc_email_piping;
	} else if ( $wpsc_email_method == 2 ) {
		$wpsc_imap_server = trim( $_POST['wpsc_imap_server'] );
		$wpsc_imap_port = trim( $_POST['wpsc_imap_port'] );
		//$wpsc_imap_argstring = trim( $_POST['wpsc_imap_argstring'] );
		$wpsc_imap_username = trim( $_POST['wpsc_imap_username'] );
		$wpsc_imap_password = trim( $_POST['wpsc_imap_password'] );
		$wpsc_imap_type = trim( $_POST['wpsc_imap_type'] );
		//if ( $wpsc_imap_username != '' && $wpsc_imap_password != '' && $wpsc_imap_server != '' && $wpsc_imap_port != '' && $wpsc_imap_argstring != '' ) {
		if ( $wpsc_imap_username != '' && $wpsc_imap_password != '' && $wpsc_imap_server != '' && $wpsc_imap_port != '' ) {
			$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_imap WHERE imap_server='" . esc_sql( $wpsc_imap_server ) . " AND imap_username='" . esc_sql( $wpsc_imap_username ) . "'";
			$result = $wpdb->get_row( $sql );
			if ( $result === NULL ) {
				/*$sql = "INSERT INTO
							" . $wpdb->prefix . "wpsc_imap
						(
							imap_server,
							imap_port,
							imap_argstring,
							imap_username,
							imap_password,
							imap_type
						) VALUES (
							'" . esc_sql( $wpsc_imap_server ) . "',
							'" . esc_sql( $wpsc_imap_port ) . "',
							'" . esc_sql( $wpsc_imap_argstring ) . "',
							'" . esc_sql( $wpsc_imap_username ) . "',
							'" . esc_sql( $wpsc_imap_password ) . "',
							'" . esc_sql( $wpsc_imap_type ) . "'
						)";*/
				$sql = "INSERT INTO
                            " . $wpdb->prefix . "wpsc_imap
                        (
                            imap_server,
                            imap_port,
                            imap_username,
                            imap_password,
                            imap_type
                        ) VALUES (
                            '" . esc_sql( $wpsc_imap_server ) . "',
                            '" . esc_sql( $wpsc_imap_port ) . "',
                            '" . esc_sql( $wpsc_imap_username ) . "',
                            '" . esc_sql( $wpsc_imap_password ) . "',
                            '" . esc_sql( $wpsc_imap_type ) . "'
                        )";
				$insert = $wpdb->query( $sql );
			}
		}
		//$sql = "DELETE FROM " . $wpdb->prefix . "wpsc_imap WHERE imap_server='' OR imap_port='' OR imap_argstring='' OR imap_username='' OR imap_password=''";
		$sql = "DELETE FROM " . $wpdb->prefix . "wpsc_imap WHERE imap_server='' OR imap_port='' OR imap_username='' OR imap_password=''";
		$clean = $wpdb->query( $sql );
		/*$wpsc_options['wpsc_imap_server'] = $wpsc_imap_server;
		$wpsc_options['wpsc_imap_port'] = $wpsc_imap_port;
		$wpsc_options['wpsc_imap_argstring'] = $wpsc_imap_argstring;
		$wpsc_options['wpsc_imap_username'] = $wpsc_imap_username;
		$wpsc_options['wpsc_imap_password'] = $wpsc_imap_password;
		$wpsc_options['wpsc_imap_password'] = $wpsc_imap_password;*/
	}
    $wpsc_options['wpsc_email_from_name'] = $wpsc_email_from_name;
    $wpsc_options['wpsc_email_from_email'] = $wpsc_email_from_email;
    $wpsc_options['wpsc_email_reply_to'] = $wpsc_email_reply_to;
    $wpsc_options['wpsc_use_agent_email'] = $wpsc_use_agent_email;
    update_option( 'wpsc_options', $wpsc_options );
    $wpsc_admin_signature = trim( $_POST['wpsc_admin_signature'] );
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_settings SET signature='" . $wpsc_admin_signature . "' WHERE ID=1";
    $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}