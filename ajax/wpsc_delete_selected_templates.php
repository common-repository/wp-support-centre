<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Delete Selected Templates
 *
 *
 */
function wpsc_delete_selected_templates() {
    global $wpdb;
    $theIDs = explode( ',', $_POST['theIDs'] );
    foreach ( $theIDs as $template_id ) {
        $where = array(
            'id' => $template_id
        );
        $where_format = array(
            '%d'
        );
        $wpdb->delete( $wpdb->prefix . 'wpsc_templates', $where, $where_format );
    }
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}