<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add New Category
 *
 *
 */
function wpsc_add_new_category() {
    global $wpdb;
    $return = array();
    $wpsc_new_category = trim( $_POST['wpsc_new_category'] );
    $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_categories (category, custom, enabled) VALUES ('" . esc_sql( $wpsc_new_category ) . "', 1, 1)";
    $result = $wpdb->query( $sql );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}