<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Delete Template
 *
 *
 */
function wpsc_delete_template() {
    global $wpdb;
    $template_id = $_POST['template_id'];
    $where = array(
        'id' => $template_id
    );
    $where_format = array(
        '%d'
    );
    if ( $wpdb->delete( $wpdb->prefix . 'wpsc_templates', $where, $where_format ) ) {
        $return['status'] = 'true';
    } else {
        $return['status'] = 'false';
    }
    echo json_encode( $return );
    wp_die();
}