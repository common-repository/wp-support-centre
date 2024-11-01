<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save Agent Settings
 *
 *
 */
function wpsc_save_agent_settings() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
    $wpsc_agent_default = trim( $_POST['wpsc_agent_default'] );
    $wpsc_support_page = trim( $_POST['wpsc_support_page'] );
    $wpsc_options['wpsc_default_agent'] = $wpsc_agent_default;
    update_option( 'wpsc_options', $wpsc_options );
    $wpsc_agents = explode( ',', $_POST['wpsc_agents'] );
    $wpsc_supers = explode( ',', $_POST['wpsc_supers'] );
    $wpsc_agents_remove = explode( ',', $_POST['wpsc_agents_remove'] );
    $wpsc_supers_remove = explode( ',', $_POST['wpsc_supers_remove'] );
    foreach ( $wpsc_agents as $user_id ) {
        $user = new WP_User( $user_id );
        $user->add_cap( 'manage_wpsc_ticket' );
    }
    foreach ( $wpsc_supers as $user_id ) {
        $user = new WP_User( $user_id );
        $user->add_cap( 'manage_wpsc_agent' );
    }
    foreach ( $wpsc_agents_remove as $user_id ) {
        $user = new WP_User( $user_id );
        $user->remove_cap( 'manage_wpsc_ticket' );
    }
    foreach ( $wpsc_supers_remove as $user_id ) {
        $user = new WP_User( $user_id );
        $user->remove_cap( 'manage_wpsc_agent' );
    }
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}