#!/usr/bin/php -q
<?php
error_reporting(0);
@ini_set('display_errors', 0);
ob_start();
if ( !file_exists( 'http_host.dat' ) || !file_exists( 'blog_id.dat' ) ) {
    return NULL;
}
$_SERVER['HTTP_HOST'] = file_get_contents( 'http_host.dat' );
$blogID = file_get_contents( 'blog_id.dat' );;
define( 'BASE_PATH', find_wordpress_base_path() . "/" );
require( BASE_PATH . 'wp-load.php' );
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header, $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
if ( $wpsc_options['wpsc_email_method'] == 1 && $wpsc_options['wpsc_enable_email_piping_catch_all'] == 1 ) {
	if ( is_multisite() ) {
	    add_action( 'switch_blog', 'switch_to_blog_cache_clear', 10, 2 );
	    switch_to_blog( $blogID );
	}
	require_once( 'mailReader.php' );
	//error_log(time() . PHP_EOL,3,'pipe.log');
	$mail = new mailReader();
	$mail->readEmail();
	if( !$mail->isReturnedEmail( $mail ) ) {
		$message = $mail->body;
		if ( strpos( $message, '<body' ) ) {
			if ( preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches) ) {
				$message = $matches[0];
			}
		}
		$message = htmlentities( $message );
	    $attachments = '';
		$attach_ids = array();
		if ( is_array( $mail->attachment_ids ) ) {
			foreach ( $mail->attachment_ids as $attachment ) {
				if ( is_array( $attachment ) ) {
					$attach_ids[] = $attachment['id'];
					$doc = new DOMDocument;
					$internalErrors = libxml_use_internal_errors( true );
					$doc->loadHTML( $message );
					$imgs = $doc->getElementsByTagName( 'img' );
					if( $imgs->length > 0 ) {
						for ( $i = 0; $i <= $imgs->length - 1; $i++ ) {
							$src = $imgs->item( $i )->getAttribute( 'src' );
							if ( strpos( $src, $attachment['filename'] ) ) {
								$imgs->item( $i )->setAttribute( 'src', $attachment['url'] );
							}
						}
					}
					$message = $doc->saveHTML();
					libxml_use_internal_errors( $internalErrors );
				} else {
					$attach_ids[] = $attachment;
				}
			}
		} else if ( $mail->attachment_ids != '' && !is_null( $mail->attachment_ids ) ) {
			$attach_ids[] = $mail->attachment_ids;
		}
		$attachments  = ( is_array( $attach_ids ) ) ? implode( ',', $attach_ids ) : $attach_ids;
	    //$attachments = ( is_array( $mail->attachment_ids ) ) ? implode( ',', $mail->attachment_ids ) : $mail->attachment_ids;
	    //$attachments = ( is_null( $attachments ) ) ? '' : $attachments;
	    $subject = $mail->subject;
	    $user = get_user_by( 'email', $mail->from_email );
	    if ( $user ) {
	        $user_id = $user->ID;
	        $author = $user->display_name;
	        $author_email = $user->user_email;
	    } else {
	        $user_id = 0;
	        $author = ( $mail->from_name != '' ) ? preg_replace( "/[^ \w@.]+/", "", html_entity_decode( $mail->from_name, ENT_QUOTES ) ) : $mail->from_email;
			$author = trim( $author );
	        $author_email = $mail->from_email;
	    }
	    // create piping preview
	    $data = array(
	        'subject' => $subject,
	        'message' => base64_encode( nl2br_save_html( $message ) ), // nl2br( nl2br_save_html( $message ) ),
	        'attachments' => $attachments,
	        'author_id' => $user_id,
	        'author' => $author,
	        'author_email' => $author_email,
	        'thread_timestamp' => current_time( 'mysql', 1 )
	    );
	    $format = array(
	        '%s',
	        '%s',
	        '%s',
	        '%d',
	        '%s',
	        '%s',
	        '%s'
	    );
	    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_piping_preview', $data, $format );
	    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_piping_preview WHERE thread_timestamp<DATE_SUB(curdate(), INTERVAL 7 DAY)";
	    $emails = $wpdb->get_results( $sql );
	    if ( !empty( $emails ) && !is_null( $emails ) ) {
	        foreach ( $emails as $email ) {
	            $attachments = $email->attachments;
	            if ( $attachments != '' ) {
	                $ids = explode( ',', $attachments );
	                foreach ( $ids as $id ) {
	                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE attachments LIKE '%" . $id . "%'";
	                    $check = $wpdb->get_results( $sql );
	                    if ( empty( $check ) ) {
	                        wp_delete_attachment( $id, true );
	                    }
	                }
	            }
	            $email_id = $email->id;
	            $sql = "DELETE FROM " . $wpdb->prefix . "wpsc_piping_preview WHERE id=" . $email_id;
	            $delete = $wpdb->query( $sql );
	        }
	    }
	}
	if ( is_multisite() ) {
	    restore_current_blog();
	}
}
ob_end_clean();
return NULL;
function find_wordpress_base_path() {
    $dir = dirname(__FILE__);
    do {
        if( file_exists( $dir . "/wp-config.php") ) {
            return $dir;
        }
    } while( $dir = realpath( "$dir/.." ) );
    return null;
}
function nl2br_save_html( $string ) {
    if( !preg_match( "#</.*>#", $string ) ) { // avoid looping if no tags in the string.
        return $string;
    }
    $string = str_replace( array( "\r\n", "\r", "\n" ), "\n", $string );
    $lines = explode( "\n", $string );
    $output = '';
    foreach( $lines as $line ) {
        $line = rtrim( $line );
        if( !preg_match("#</?[^/<>]*>$#", $line ) ) { // See if the line finished with has an html opening or closing tag
            $line .= '<br />';
        }
        $output .= $line;
    }
    return $output;
}
function switch_to_blog_cache_clear( $blog_id, $prev_blog_id = 0 ) {
    if ( $blog_id === $prev_blog_id )
        return;

    wp_cache_delete( 'notoptions', 'options' );
    wp_cache_delete( 'alloptions', 'options' );
}
?>