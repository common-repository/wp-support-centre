<?php
header('Content-Type: text/html; charset=utf-8');
set_error_handler( 'wpsc_error' );
define( 'BASE_PATH', find_wordpress_base_path() . "/" );
require( BASE_PATH . 'wp-load.php' );
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header, $wpdb;
if ( isset( $_GET['uid'] ) && $_GET['uid'] != '' ) {
    $uid = $_GET['uid'];
    $transient = get_transient( $uid );
	$message = base64_decode( $transient['message'] );
   	$message = quoted_printable_decode( urldecode( $message ) );
	echo '<span style="display:none;">UID: ' . $uid . '</span>';
    echo html_entity_decode( stripcslashes( $message ) );
} else if ( isset( $_GET['tid'] ) && $_GET['tid'] != '' ) {
    $tid = $_GET['tid'];
    $message = $wpdb->get_var( "SELECT message FROM " . $wpdb->prefix . "wpsc_threads WHERE id=" . $tid );
	$message = base64_decode( $message );
	echo '<span style="display:none;">TID: ' . $tid . '</span>';
    echo html_entity_decode( stripcslashes( urldecode( $message ) ) );
} else {
	echo 'Unable to identify message';
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
function wpsc_error( $number, $message, $file, $line, $vars ) {
	$email = "
        <p>An error ($number) occurred on line 
        <strong>$line</strong> and in the <strong>file: $file.</strong> 
        <p> $message </p>";
         
    $email .= "<pre>" . print_r($vars, 1) . "</pre>";
     
    $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
     
    // Email the error to someone...
    error_log($email, 1, 'test@cloughit.com.au' );
 
    // Make sure that you decide how to respond to errors (on the user's side)
    // Either echo an error message, or kill the entire project. Up to you...
    // The code below ensures that we only "die" if the error was more than
    // just a NOTICE. 
    if ( ($number !== E_NOTICE) && ($number < 2048) ) {
        die("There was an error. Please try again later.");
    }
}
?>