<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get User Information
 *
 *
 */
function wpsc_get_user_data() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $user_id = $_POST['user_id'];
    $display_name = $_POST['display_name'];
    include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket.php' );
    $content = wpsc_get_user_information( $user_id, $display_name );
    $return['status'] = 'true';
    $return['content'] = $content;
    echo json_encode( $return );
    wp_die();
}