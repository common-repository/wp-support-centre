<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add New Custom Status
 *
 *
 */
function wpsc_dismiss_notice() {
    $notice_id = isset( $_POST['notice_id'] ) && $_POST['notice_id'] != '' ? $_POST['notice_id'] : '';
    update_option( 'wpsc_notice_log', $notice_id );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}