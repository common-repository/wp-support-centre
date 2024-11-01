<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save New Template
 *
 *
 */
function wpsc_save_new_template() {
    global $wpdb;
    $wpsc_template_label = $_POST['wpsc_template_label'];
    $wpsc_template = $_POST['wpsc_template'];
    $data = array(
        'label' => $wpsc_template_label,
        'template' => $wpsc_template
    );
    $format = array(
        '%s',
        '%s'
    );
    if ( false !== $wpdb->insert( $wpdb->prefix . 'wpsc_templates', $data, $format ) ) {
        $return['status'] = 'true';
    } else {
        $return['status'] = 'false';
    }
    echo json_encode( $return );
    wp_die();
}