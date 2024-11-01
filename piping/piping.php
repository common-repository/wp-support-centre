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
if ( $wpsc_options['wpsc_email_method'] == 1 && $wpsc_options['wpsc_enable_email_piping'] == 1 ) {
	if ( is_multisite() ) {
	    add_action( 'switch_blog', 'switch_to_blog_cache_clear', 10, 2 );
	    switch_to_blog( $blogID );
	}
	require_once( 'mailReader.php' );
	//error_log(time() . PHP_EOL,3,'pipe.log');
	$mail = new mailReader();
	$mail->readEmail();
	if( !$mail->isReturnedEmail( $mail ) ) {
	    $is_new = true;
	    if ( isset( $wpsc_options['wpsc_item_history'] ) && is_array( $wpsc_options['wpsc_item_history'] ) )  {
	        foreach ( $wpsc_options['wpsc_item_history'] as $wpsc_item ) {
	            if ( stristr( $mail->subject, '[' . $wpsc_item . ': ' ) ) { // is existing ticket
	                $is_new = false;
	            }
	        }
	    } else if ( stristr( $mail->subject, '[' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ': ' ) ) { // is existing ticket
	        $is_new = false;
	    }
	    if ( $is_new === false ) {
	        $pattern = '/\[([^]]+)\]/';
	        preg_match( $pattern, $mail->subject, $matches );
	        $ticket_id = preg_replace( "/[^0-9,.]/", "", $matches[0] );
	        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . $ticket_id;
	        $select = $wpdb->get_results( $sql );
	        if ( !$select || $select == NULL ) {
	            $is_new == true;
	        }
	    }
	    if ( $is_new ) { // new ticket
	        $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
	        $category = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_categories WHERE is_default=1" );
	        $priority = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_priority WHERE is_default=1" );
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
						libxml_use_internal_errors( $internalErrors );
						$message = $doc->saveHTML();
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
	        $seperator = $wpsc_options['wpsc_seperator'];
	        if ( strpos( $message, $seperator ) !== false ) {
	            $message = substr( $message, 0, strpos( $message, $seperator ) );
	        }
	        $user = get_user_by( 'email', $mail->from_email );
	        if ( $user ) {
	            $client_id = $user->ID;
	            $client_name = $user->display_name;
	            $client_email = $user->user_email;
	        } else {
	            if ( isset( $mail->from_name ) && $mail->from_name != '' ) {
	                $from_name = preg_replace( '/\s+/', ' ', $mail->from_name );
					$from_name = preg_replace( "/[^ \w@.]+/", "", html_entity_decode( $from_name, ENT_QUOTES ) );
					$from_name = trim( $from_name );
	                if ( strpos( $from_name, ' ') > 0 ) {
	                    $split_name = explode( ' ', $from_name, 2 );
	                    $first_name = $split_name[0];
	                    $last_name = $split_name[1];
	                } else {
	                    $first_name = $from_name;
	                    $last_name = '';
	                }
	            } else {
	                $first_name = '';
	                $last_name = '';
	            }
	            $client_name = $first_name . ' ' . $last_name;
	            $client_email = $mail->from_email;
	            $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
	            $args = array(
	                'user_pass' => $random_password,
	                'user_login' => $client_email,
	                'user_email' => $client_email,
	                'display_name' => $client_name,
	                'first_name' => $first_name,
	                'last_name' => $last_name
	            );
	            $client_id = wp_insert_user( $args );
	            wp_new_user_notification( $client_id, null, 'both' );
	        }
	        // select agent
	        if ( $wpsc_options['wpsc_default_agent'] == 's' ) {
	            $users = array();
	            $all_users = get_users();
	            foreach ( $all_users as $user ) {
	                if ( $user->has_cap( 'manage_wpsc_agent' ) ) {
	                    $users[] = $user->ID;
	                }
	            }
	            $rand = array_rand( $users );
	            $agent_id = $users[$rand];
	        } else if ( $wpsc_options['wpsc_default_agent'] == 'a' ) {
	            $users = array();
	            $all_users = get_users();
	            foreach ( $all_users as $user ) {
	                if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
	                    $users[] = $user->ID;
	                }
	            }
	            $rand = array_rand( $users );
	            $agent_id = $users[$rand];
	        } else {
	            $agent_id = $wpsc_options['wpsc_default_agent'];
	        }
			$agent = get_userdata( $agent_id );
			$to_email = $agent->user_email;
	        // create ticket
	        $data = array(
	            'subject' => $mail->subject,
	            'client_id' => $client_id,
	            'client' => $client_name,
	            'client_email' => $client_email,
	            'category_id' => $category,
	            'agent_id' => $agent_id,
	            'priority_id' => $priority,
	            'created_timestamp' => current_time( 'mysql', 1 ),
	            'updated_timestamp' => current_time( 'mysql', 1 ),
	            'updated_by'=> $client_id
	        );
	        $format = array(
	            '%s',
	            '%d',
	            '%s',
	            '%s',
	            '%d',
	            '%d',
	            '%d',
	            '%s',
	            '%s',
	            '%d'
	        );
	        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_tickets', $data, $format );
	        $ticket_id = $wpdb->insert_id;
			wpsc_update_ticket_status( $ticket_id, $status );
	        if ( is_array( $wpsc_options['wpsc_new_tickets'] ) ) {
	            if ( !in_array( $ticket_id, $wpsc_options['wpsc_new_tickets'] ) ) {
	                array_push( $wpsc_options['wpsc_new_tickets'], $ticket_id );
	            }
	        } else {
	            $wpsc_options['wpsc_new_tickets'] = array( $ticket_id );
	        }
			$wpsc_options['wpsc_sla'][$ticket_id] = '1';
			// add to and cc
			$cc = ( isset( $mail->cc_email ) ) ? $mail->cc_email : '';
	        // add thread
	        $data = array(
	            'ticket_id' => $ticket_id,
	            'message' => base64_encode( nl2br_save_html( $message ) ), // nl2br( nl2br_save_html( $message ) ),
	            'attachments' => $attachments,
	            'author_id' => $client_id,
	            'author' => $client_name,
	            'author_email' => $client_email,
	            'to_email' => $to_email,
	            'cc_email' => $cc_email,
	            'thread_timestamp' => current_time( 'mysql', 1 )
	        );
	        $format = array(
	            '%d',
	            '%s',
	            '%s',
	            '%d',
	            '%s',
	            '%s',
	            '%s',
	            '%s',
	            '%s'
	        );
	        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
	        $thread_id = $wpdb->insert_id;
	        // send notifications
	        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
	        wpsc_notification( 'new_ticket_front', $ticket_id, $thread_id );
	    } else { // reply
	        define( 'DIEONDBERROR', true );
	        ob_start();
	        $wpdb->show_errors();
	        $pattern = '/\[([^]]+)\]/';
	        preg_match( $pattern, $mail->subject, $matches );
	        $ticket_id = preg_replace( "/[^0-9,.]/", "", $matches[0] );
	        $user = get_user_by( 'email', $mail->from_email );
	        if ( $user ) {
	            $user_id = $user->ID;
	            $author = $user->display_name;
	            $author_email = $user->user_email;
	        } else {
	            $user_id = 0;
	            $author = $mail->from_name;
	            $author_email = $mail->from_email;
	        }
	        $message = $mail->body;
			if ( strpos( $message, '<body' ) ) {
				if ( preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches) ) {
					$message = $matches[0];
				}
			}
	        $attachments = '';
			$attach_ids = array();
			if ( is_array( $mail->attachment_ids ) ) {
				foreach ( $mail->attachment_ids as $attachment ) {
					if ( is_array( $attachment ) ) {
						$attach_ids[] = $attachment['id'];
						$doc = new DOMDocument;
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
	        // update ticket
	        $sql = "SELECT agent_id FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . $ticket_id;
	        $agent_id = $wpdb->get_var( $sql );
			$agent = get_userdata( $agent_id );
			$to_email = $agent->user_email;
	        if ( $user_id != $agent_id ) {
	            $status_id = 'status_id=5,';
	            if ( is_array( $wpsc_options['wpsc_replies'] ) ) {
	                if ( !in_array( $ticket_id, $wpsc_options['wpsc_replies'] ) ) {
	                    array_push( $wpsc_options['wpsc_replies'], $ticket_id );
	                }
	            } else {
	                $wpsc_options['wpsc_replies'] = array( $ticket_id );
	            }
				$wpsc_options['wpsc_sla'][$ticket_id] = '1';
	        }
	        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET
	            updated_timestamp='" . current_time( 'mysql', 1 ) . "',
	            updated_by='" . $user_id . "'
	            WHERE id=" . $ticket_id;
	        $update = $wpdb->query( $sql );
			if ( $user_id != $agent_id ) {
				wpsc_update_ticket_status( $ticket_id, $status_id );
			}
			// add to and cc
			$cc = ( isset( $mail->cc_email ) ) ? $mail->cc_email : '';
	        // add thread
	        $seperator = $wpsc_options['wpsc_seperator'];
	        if ( strpos( $message, $seperator ) !== false ) {
	            $message = substr( $message, 0, strpos( $message, $seperator ) );
	        }
	        $data = array(
	            'ticket_id' => $ticket_id,
	            'message' => base64_encode( nl2br_save_html( $message ) ), // nl2br( nl2br_save_html( $message ) ),
	            'attachments' => $attachments,
	            'author_id' => $user_id,
	            'author' => $author,
	            'author_email' => $author_email,
	            'to_email' => $to_email,
	            'cc_email' => $cc_email,
	            'thread_timestamp' => current_time( 'mysql', 1 )
	        );
	        $format = array(
	            '%d',
	            '%s',
	            '%s',
	            '%d',
	            '%s',
	            '%s',
	            '%s',
	            '%s',
	            '%s'
	        );
	        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
	        $thread_id = $wpdb->insert_id;
	        // send notifications
	        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
	        wpsc_notification( 'client_reply', $ticket_id, $thread_id );
	        $log = ob_get_clean();
	        //error_log( print_r( $log, TRUE ), 3, 'data_log.log' );
	        $wpdb->hide_errors();
	    }
	    update_option( 'wpsc_options', $wpsc_options );
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