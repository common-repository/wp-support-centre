<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save Template Changes
 *
 *
 */
function wpsc_save_template_changes() {
    global $wpdb;
    $template_id = $_POST['template_id'];
    $wpsc_template_label_edit = $_POST['wpsc_template_label_edit'];
    $wpsc_edit_template = $_POST['wpsc_edit_template'];
    $data = array(
        'label' => $wpsc_template_label_edit,
        'template' => $wpsc_edit_template
    );
    $format = array(
        '%s',
        '%s'
    );
    $where = array(
        'id' => $template_id
    );
    $where_format = array(
        '%d'
    );
    if ( false !== $wpdb->update( $wpdb->prefix . 'wpsc_templates', $data, $where, $format, $where_format ) ) {
        $return['status'] = 'true';
    } else {
        $return['status'] = 'false';
    }
    echo json_encode( $return );
    wp_die();
}