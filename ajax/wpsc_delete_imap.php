<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Delete Custom Status
 *
 *
 */
function wpsc_delete_imap() {
    global $wpdb;
    $return = array();
    $imap_id = trim( $_POST['imap_id'] );
    $sql = "DELETE FROM " . $wpdb->prefix . "wpsc_imap WHERE id=" . $imap_id;
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}