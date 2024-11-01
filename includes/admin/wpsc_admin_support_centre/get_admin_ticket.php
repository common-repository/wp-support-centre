<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket/functions.php' );

function wpsc_get_the_admin_ticket( $ticket_id ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
	if ( isset( $wpsc_options['wpsc_sla'] ) && !empty( $wpsc_options['wpsc_sla'] ) ) {
		if ( ( $key = array_search( $ticket_id, $wpsc_options['wpsc_sla'] ) ) !== false ) {
		    unset( $wpsc_options[$key] );
		}
		update_option( 'wpsc_options', $wpsc_options );
    }
    $output = '';
    include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket/tabs.php' );
    $output .= '<div class="tab-content" id="ticket-tab-content_' . $ticket_id . '">';
        include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket/ticket.php' );
		include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket/reminders.php' );
        include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket/participants.php' );
        include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket/attachments.php' );
        include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket/account_information.php' );
    $output .= '</div>';
    return $output;
}