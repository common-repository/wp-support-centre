<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get Template
 *
 *
 */
function wpsc_get_template() {
    global $wpdb;
	$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $template_id = $_POST['template_id'];
	$ticket_id = $_POST['ticket_id'];
    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_templates WHERE id=" . $template_id;
    $template = $wpdb->get_row( $sql );
    if ( $wpdb-> num_rows > 0 ) {
    	$sql = "SELECT client_id FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . $ticket_id;
		$client_id = $wpdb->get_var( $sql );
		$user = get_user_by( 'id', $client_id );
		$first_name = ( !empty( $user ) && $user->first_name != '' ) ? $user->first_name : apply_filters( 'wpsc_client', 'Client', $wpsc_options );
        $return['status'] = 'true';
        $return['label'] = $template->label;
		$the_template = html_entity_decode( stripcslashes( $template->template ) );
		$now = current_time( 'timestamp' );
		$salutation = ( date( 'G', $now ) < 13 && date( 'G', $now ) != 0 ) ? 'Morning' : 'Afternoon';
		$salutation = ( $salutation == 'Afternoon' && date( 'G', $now ) > 18 ) ? 'Evening' : $salutation;
		$the_template = str_replace( '[wpsc_salutation]', 'Good ' . $salutation, $the_template );
		$the_template = str_replace( '[wpsc_ticket_id]', $ticket_id, $the_template );
		$the_template = str_replace( '[wpsc_client_first_name]', $first_name, $the_template );
        $return['template'] = $the_template;
    } else {
        $return['status'] = 'false';
        $return['label'] = '';
        $return['template'] = '';
    }
    echo json_encode( $return );
    wp_die();
}