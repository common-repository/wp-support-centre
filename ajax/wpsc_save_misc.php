<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save admin general settings
 *
 *
 */
function wpsc_save_misc() {
	global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
	$wpsc_load_bootstrap_js_f = trim( $_POST['wpsc_load_bootstrap_js_f'] );
    $wpsc_load_bootstrap_js_a = trim( $_POST['wpsc_load_bootstrap_js_a'] );
    $wpsc_load_bootstrap_css_f = trim( $_POST['wpsc_load_bootstrap_css_f'] );
    $wpsc_load_bootstrap_css_a = trim( $_POST['wpsc_load_bootstrap_css_a'] );
    $wpsc_options['wpsc_load_bootstrap_js_f'] = $wpsc_load_bootstrap_js_f;
    $wpsc_options['wpsc_load_bootstrap_js_a'] = $wpsc_load_bootstrap_js_a;
	$wpsc_options['wpsc_load_bootstrap_css_f'] = $wpsc_load_bootstrap_css_f;
    $wpsc_options['wpsc_load_bootstrap_css_a'] = $wpsc_load_bootstrap_css_a;
    update_option( 'wpsc_options', $wpsc_options );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}