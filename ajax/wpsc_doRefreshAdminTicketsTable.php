<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Refresh Admin Tickets Table
 *
 *
 */
function wpsc_doRefreshAdminTicketsTable() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
    $filter = '';
    include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_tickets_table.php' );
    $output = apply_filters( 'do_wpsc_admin_get_tickets_table', '', $filter, $wpsc_options );
    $return['status'] = 'true';
    $return['table'] = $output;
    echo json_encode( $return );
    wp_die();
}