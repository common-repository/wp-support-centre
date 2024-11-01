<?php
define( 'BASE_PATH', find_wordpress_base_path() . "/" );
require( BASE_PATH . 'wp-load.php' );
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header, $wpdb;
if ( isset( $_GET['tid'] ) && $_GET['tid'] != '' ) {
    $thread_id = $_GET['tid'];
    $ip = '';
    if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) { //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads_read WHERE thread_id=" . $thread_id . " AND ip='" . $ip . "'";
    $check = $wpdb->get_results( $sql );
    //var_dump( $check );
    if ( $wpdb->num_rows == 0 ) {
        $read_timestamp =current_time( 'mysql', 1 );
        $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_threads_read (thread_id,ip,read_timestamp) VALUES ('" . $thread_id . "','" . $ip . "','" . $read_timestamp . "')";
        $insert = $wpdb->query( $sql );
    }
}
function find_wordpress_base_path() {
    $dir = dirname(__FILE__);
    do {
        if( file_exists( $dir . "/wp-config.php") ) {
            return $dir;
        }
    } while( $dir = realpath( "$dir/.." ) );
    return null;
}
?>