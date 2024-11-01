<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

include_once( WPSC_PLUGIN_DIR . '/includes/functions.php' );

global $wpdb;

add_action( 'wpsc_search_fallback', 'wpsc_search_fallback_run' );
function wpsc_search_fallback_run() {
	global $wpdb;
	$mysqlver = $wpdb->dbh->server_info;
	if ( $mysqlver < '5.6.1' ) {
		$index = false;
		$sql = "SELECT (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpsc_threads_raw) AS count_raw, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpsc_threads) AS count_live";
		$counts = $wpdb->get_results( $sql );
		if ( !empty( $counts ) && !is_null( $counts ) ) {
			$count = $counts[0];
			if ( $count->count_raw != $count->count_live ) {
				$index = true;
			}
		} else {
			$index = true;
		}
		if ( $index ) {
			$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . 'wpsc_threads_raw';
			$drop = $wpdb->query( $sql );
			$sql = "CREATE TABLE " . $wpdb->prefix . "wpsc_threads_raw LIKE " . $wpdb->prefix . "wpsc_threads";
			$create = $wpdb->query( $sql );
			$sql = "INSERT INTO " . $wpdb->prefix . "wpsc_threads_raw SELECT * FROM " . $wpdb->prefix . "wpsc_threads";
			$copy = $wpdb->query( $sql );
			$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads";
			$threads = $wpdb->get_results( $sql );
			foreach ( $threads as $thread ) {
				$message = base64_decode( $thread->message );
				$sql = "UPDATE " . $wpdb->prefix . "wpsc_threads_raw SET message='" . esc_sql( $message ) . "' WHERE id=" . $thread->id;
				$update = $wpdb->query( $sql );
			}
		}
	}
}

add_action( 'wpsc_assign_threads', 'wpsc_assign_threads_cron' );
function wpsc_assign_threads_cron() {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE author_id=0 GROUP BY author_email";
    $threads = $wpdb->get_results( $sql );
    if ( !empty( $threads ) ) {
        foreach ( $threads as $thread ) {
            $user = get_user_by( 'email', $thread->author_email );
            if ( $user ) {
                $sql = "UPDATE " . $wpdb->prefix . "wpsc_threads SET author_id=" . $user->ID . " WHERE author_email='" . $thread->author_email . "'";
                $update = $wpdb->query( $sql );
            }
        }
    }
    $wpsc_options['wpsc_assign_threads_last_run'] = current_time( 'mysql', 1 );
    update_option( 'wpsc_options', $wpsc_options );
}

add_action( 'wpsc_recurring_tickets', 'wpsc_recurring_tickets_cron' );
function wpsc_recurring_tickets_cron() {
    do_wpsc_recurring_tickets();
}
add_action( 'wpsc_clean_attachments', 'do_wpsc_clean_attachments' );
if ( !function_exists( 'do_wpsc_clean_attachments' ) ) {
    function do_wpsc_clean_attachments() {
        global $wpdb;
        $all_attachments = array();
        $todelete = array();
        $missing = array();
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE attachments!=''";
        $results = $wpdb->get_results( $sql );
        foreach ( $results as $thread ) {
            $thread_id = $thread->id;
            $attachments = $thread->attachments;
            $thread_attachments = explode( ',', $attachments );
            foreach ( $thread_attachments as $attachment_id ) {
                $all_attachments[$attachment_id]['threads'][] = $thread_id;
            }
        }
        $attachments = array();
        $temp_attachments = $all_attachments;
        foreach ( $temp_attachments as $attachment_id => $data ) {
            //print_r( $attachment_id );
            $attachment_meta = get_post_meta( $attachment_id );
            $attachment_path = get_attached_file( $attachment_id );
            $attachment_file = basename( $attachment_path );
            $attachment_url = wp_get_attachment_url( $attachment_id );
            $all_attachments[$attachment_id]['path'] = $attachment_path;
            $all_attachments[$attachment_id]['url'] = $attachment_url;
            if ( !isset( $attachment_meta['filehash'][0] ) ) {
                if ( $attachment_path == '' ) {
                    $missing[$attachment_id] = $all_attachments[$attachment_id];
                } else if ( file_exists( $attachment_path ) ) {
                    $filehash = hash_file( 'md5', $attachment_path );
                    $all_attachments[$attachment_id]['filehash'] = $filehash;
                    $attachments[$filehash][] = $all_attachments[$attachment_id];
                    update_post_meta( $attachment_id, 'filehash', $filehash );
                } else {
                    $exists = false;
                    for ( $i=1; $i <= 10; $i++ ) {
                        if ( file_exists( ABSPATH . 'temp/' . $i . '/' . $attachment_file ) ) {
                            rename( ABSPATH . 'temp/' . $i . '/' . $attachment_file, $attachment_path );
                            $filehash = hash_file( 'md5', $attachment_path );
                            $all_attachments[$attachment_id]['filehash'] = $filehash;
                            $attachments[$filehash][] = $data;
                            $exists = true;
                        }
                    }
                    if ( !$exists ) {
                        $missing[] = $attachment_path;
                    }
                }
            } else {
                $filehash = $attachment_meta['filehash'][0];
                $all_attachments[$attachment_id]['filehash'] = $filehash;
                $attachments[$filehash][] = $data;
            }
        }
    }
}
add_action( 'wpsc_check_imap', 'do_wpsc_check_imap' );
if ( !function_exists( 'do_wpsc_check_imap' ) ) {
    function do_wpsc_check_imap() {
    	$response = wp_remote_get( WPSC_PLUGIN_URL . '/imap/imap.php',
		    array(
		        'timeout'     => 120
		    )
		);
	}
}