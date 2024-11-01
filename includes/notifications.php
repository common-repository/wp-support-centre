<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
include_once( WPSC_PLUGIN_DIR . '/includes/shortcodes.php' );
function wpsc_notification( $type, $ticket_id, $thread_id ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $return = array();
    if ( $thread_id > 0 ) {
        $thread_select = ' ,th.message,th.attachments,th.author,th.author_email ';
        $thread_join = ' LEFT JOIN ' . $wpdb->prefix . 'wpsc_threads th ON th.id=' . $thread_id;
    } else {
        $thread_select = '';
        $thread_join = '';
    }
    $sql = "SELECT
                t.id,t.subject,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.category_id,t.priority_id,t.status_id,t.client_email,
                s.status,s.status_prefix,s.colour AS status_colour,
                c.category,
                p.priority,p.colour AS priority_colour,
                ua.display_name AS agent,ua.user_email as agent_email
                " . $thread_select . "
            FROM " . $wpdb->prefix . "wpsc_tickets t
            LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
            LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id
            " . $thread_join . "
            WHERE t.id=" . $ticket_id;
    $ticket = $wpdb->get_row( $sql );
    $theSubject = $ticket->status_prefix . ' [' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ': ' . $ticket_id . '] ' . html_entity_decode( stripcslashes( $ticket->subject ) );
    $theSubject = trim( $theSubject );
    switch( $type ) {
        case 'new_ticket':
            // client notification
            if ( $wpsc_options['wpsc_notification_ticket_new_client_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                if ( $wpsc_options['wpsc_use_agent_email'] == 1 ) {
                    $from_name = $ticket->agent;
                    $from_email = $ticket->agent_email;
                } else {
                    $from_name = ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' );
                    $from_email = ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' );
                }
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $to_array = ( isset( $_POST['wpsc_admin_new_ticket_to'] ) && $_POST['wpsc_admin_new_ticket_to'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_ticket_to'] ) : ( isset( $_POST['wpsc_admin_new_ticket_client_email'] ) && $_POST['wpsc_admin_new_ticket_client_email'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_ticket_client_email'] ) : array();
                $cc1 = ( isset( $_POST['wpsc_admin_new_ticket_cc'] ) && $_POST['wpsc_admin_new_ticket_cc'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_ticket_cc'] ) : array();
				if ( isset( $_POST['wpsc_admin_new_ticket_cc_select'] ) ) {
					if ( is_array( $_POST['wpsc_admin_new_ticket_cc_select'] ) ) {
						$cc = array_merge( $cc1, $_POST['wpsc_admin_new_ticket_cc_select'] );
					} else {
						if ( !is_null( $_POST['wpsc_admin_new_ticket_cc_select'] ) ) {
							$cc2 = explode( ',', $_POST['wpsc_admin_new_ticket_cc_select'] );
							$cc = ( is_array( $cc2 ) ) ? array_merge( $cc1, $cc2 ) : $cc1;
						}
					}
				} else {
					$cc = array();
				}
				$bcc1 = ( isset( $_POST['wpsc_admin_new_ticket_bcc'] ) && $_POST['wpsc_admin_new_ticket_bcc'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_ticket_bcc'] ) : array();
				if ( isset( $_POST['wpsc_admin_new_ticket_bcc_select'] ) ) {
					if ( is_array( $_POST['wpsc_admin_new_ticket_bcc_select'] ) ) {
						$bcc = array_merge( $bcc1, $_POST['wpsc_admin_new_ticket_bcc_select'] );
					} else {
						if ( !is_null( $_POST['wpsc_admin_new_ticket_bcc_select'] ) ) {
							$bcc2 = explode( ',', $_POST['wpsc_admin_new_ticket_bcc_select'] );
							$bcc = ( is_array( $bcc2 ) ) ? array_merge( $bcc1, $bcc2 ) : $bcc1;
						}
					}
				} else {
					$bcc = array();
				}
                if ( is_array( $to_array ) && !empty( $to_array ) ) {
                    $to = array();
                    foreach ( $to_array as $to_item ) {
                        $user = get_user_by( 'email', $to_item );
                        if ( $user ) {
                            $to[] = $user->display_name . ' <' . $to_item . '>';
                        } else {
                            $to[] = $to_item;
                        }
                    }
					do_action( 'wpsc_thread_notifications', $ticket_id, $thread_id, $to, $cc, $bcc );
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Reply-to: ' . $reply_to;
                    foreach ( $cc as $c ) {
                    	if ( !is_null( $c ) && !empty( $c ) && $c != '' ) {
                        	$headers[] = 'Cc: ' . $c;
						}
                    }
                    foreach ( $bcc as $bc ) {
                        if ( !is_null( $bc ) && !empty( $bc ) && $bc != '' ) {
                        	$headers[] = 'Bcc: ' . $bc;
						}
                    }
                    $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                    $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_client'";
                    $result = $wpdb->get_var( $sql );
                    $result = html_entity_decode( stripcslashes( $result ) );
                    $message .= $result;
                    $message = get_signature( $message );
                    $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                    $message .= '<p><img src="' . WPSC_PLUGIN_URL . 'assets/images/blank.gif?tid=' . $thread_id . '"></p>';
                    $attachments = array();
                    $attachment_ids = explode( ',', $ticket->attachments );
                    foreach ( $attachment_ids as $attachment_id ) {
                        //$attachment_url = wp_get_attachment_url( $attachment_id );
                        $attachment_url = get_attached_file( $attachment_id );
                        if ( $attachment_url ) {
                            $attachments[] = $attachment_url;
                        }
                    }
                    wp_mail( $to, $theSubject, $message, $headers, $attachments );
                }
            }
            // admin notification
            if ( $wpsc_options['wpsc_notification_ticket_new_admin_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $from_name = $_POST['wpsc_admin_new_ticket_client'];
                $from_email = $_POST['wpsc_admin_new_ticket_client_email'];
                $reply_to = $_POST['wpsc_admin_new_ticket_client_email'];
                $agent = get_user_by( 'id', $ticket->agent_id );
                $to = array( $agent->user_email );
                $cc = array();
                $bcc = array();
                $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_admin'";
                $result = $wpdb->get_var( $sql );
                $result = html_entity_decode( stripcslashes( $result ) );
                $message .= $result;
                $message = get_signature( $message );
                $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                $attachments = array();
                $attachment_ids = explode( ',', $ticket->attachments );
                foreach ( $attachment_ids as $attachment_id ) {
                    //$attachment_url = wp_get_attachment_url( $attachment_id );
                    $attachment_url = get_attached_file( $attachment_id );
                    if ( $attachment_url ) {
                        $attachments[] = $attachment_url;
                    }
                }
                wp_mail( $to, $theSubject, $message, $headers, $attachments );
            }
            break;
        case 'new_recurring_ticket':
            // client notification
            if ( $wpsc_options['wpsc_notification_ticket_new_client_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                if ( $wpsc_options['wpsc_use_agent_email'] == 1 ) {
                    $from_name = $ticket->agent;
                    $from_email = $ticket->agent_email;
                } else {
                    $from_name = ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' );
                    $from_email = ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' );
                }
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $to_array = ( $ticket->client_email != '' ) ? array( $ticket->client_email != '' ) : array();
				$cc = array();
				$bcc = array();
                if ( is_array( $to_array ) && !empty( $to_array ) ) {
                    $to = array();
                    foreach ( $to_array as $to_item ) {
                        $user = get_user_by( 'email', $to_item );
                        if ( $user ) {
                            $to[] = $user->display_name . ' <' . $to_item . '>';
                        } else {
                            $to[] = $to_item;
                        }
                    }
					do_action( 'wpsc_thread_notifications', $ticket_id, $thread_id, $to, $cc, $bcc );
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Reply-to: ' . $reply_to;
                    $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                    $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_client'";
                    $result = $wpdb->get_var( $sql );
                    $result = html_entity_decode( stripcslashes( $result ) );
                    $message .= $result;
                    $message = get_signature( $message );
                    $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                    $message .= '<p><img src="' . WPSC_PLUGIN_URL . 'assets/images/blank.gif?tid=' . $thread_id . '"></p>';
                    $attachments = array();
                    $attachment_ids = explode( ',', $ticket->attachments );
                    foreach ( $attachment_ids as $attachment_id ) {
                        //$attachment_url = wp_get_attachment_url( $attachment_id );
                        $attachment_url = get_attached_file( $attachment_id );
                        if ( $attachment_url ) {
                            $attachments[] = $attachment_url;
                        }
                    }
                    wp_mail( $to, $theSubject, $message, $headers, $attachments );
                }
            }
            // admin notification
            if ( $wpsc_options['wpsc_notification_ticket_new_admin_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $from_name = $ticket->client;
                $from_email = $ticket->client_email;
                $reply_to = $ticket->client_email;
                $agent = get_user_by( 'id', $ticket->agent_id );
                $to = array( $agent->user_email );
                $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                $headers[] = 'Reply-to: ' . $reply_to;
                $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_admin'";
                $result = $wpdb->get_var( $sql );
                $result = html_entity_decode( stripcslashes( $result ) );
                $message .= $result;
                $message = get_signature( $message );
                $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                $attachments = array();
                $attachment_ids = explode( ',', $ticket->attachments );
                foreach ( $attachment_ids as $attachment_id ) {
                    //$attachment_url = wp_get_attachment_url( $attachment_id );
                    $attachment_url = get_attached_file( $attachment_id );
                    if ( $attachment_url ) {
                        $attachments[] = $attachment_url;
                    }
                }
                wp_mail( $to, $theSubject, $message, $headers, $attachments );
            }
            break;
        case 'reply_ticket':
            // client notification
            if ( $wpsc_options['wpsc_notification_ticket_reply_client_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                if ( $wpsc_options['wpsc_use_agent_email'] == 1 ) {
                    $from_name = $ticket->agent;
                    $from_email = $ticket->agent_email;
                } else {
                    $from_name = ( isset( $_POST['wpsc_admin_thread_from_name'] ) && $_POST['wpsc_admin_thread_from_name'] != '' ) ? $_POST['wpsc_admin_thread_from_name'] : ( ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' ) );
                    $from_email = ( isset( $_POST['wpsc_admin_thread_from_email'] ) && $_POST['wpsc_admin_thread_from_email'] != '' ) ? $_POST['wpsc_admin_thread_from_email'] : ( ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' ) );
                }
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $to_array = ( isset( $_POST['wpsc_admin_new_thread_to'] ) && $_POST['wpsc_admin_new_thread_to'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_thread_to'] ) : array();
                $cc = ( isset( $_POST['wpsc_admin_new_thread_cc'] ) && $_POST['wpsc_admin_new_thread_cc'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_thread_cc'] ) : array();
                $bcc = ( isset( $_POST['wpsc_admin_new_thread_bcc'] ) && $_POST['wpsc_admin_new_thread_bcc'] != '' ) ? explode( ',', $_POST['wpsc_admin_new_thread_bcc'] ) : array();
                if ( is_array( $to_array ) && !empty( $to_array ) ) {
                    $to = array();
                    foreach ( $to_array as $to_item ) {
                        $user = get_user_by( 'email', $to_item );
                        if ( $user ) {
                            $to[] = $user->display_name . ' <' . $to_item . '>';
                        } else {
                            $to[] = $to_item;
                        }
                    }
					do_action( 'wpsc_thread_notifications', $ticket_id, $thread_id, $to, $cc, $bcc );
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Reply-to: ' . $reply_to;
                    foreach ( $cc as $c ) {
                    	if ( !is_null( $c ) && !empty( $c ) && $c != '' ) {
                        	$headers[] = 'Cc: ' . $c;
						}
                    }
                    foreach ( $bcc as $bc ) {
                        if ( !is_null( $bc ) && !empty( $bc ) && $bc != '' ) {
                        	$headers[] = 'Bcc: ' . $bc;
						}
                    }
                    $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                    $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_client'";
                    $result = $wpdb->get_var( $sql );
                    $result = html_entity_decode( stripcslashes( $result ) );
                    $message .= $result;
                    $message = get_signature( $message );
                    $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                    $message .= '<p><img src="' . WPSC_PLUGIN_URL . 'assets/images/blank.gif?tid=' . $thread_id . '"></p>';
                    if ( isset( $_POST['wpsc_admin_thread_inlcudes'] ) && $_POST['wpsc_admin_thread_inlcudes'] != '' ) {
                        $include_threads = explode( ',', $_POST['wpsc_admin_thread_inlcudes'] );
                        rsort( $include_threads );
                        foreach ($include_threads as $thread ) {
                            $message .= include_thread( $thread );
                        }
                    }
                    $attachments = array();
                    $attachment_ids = explode( ',', $ticket->attachments );
                    foreach ( $attachment_ids as $attachment_id ) {
                        //$attachment_url = wp_get_attachment_url( $attachment_id );
                        $attachment_url = get_attached_file( $attachment_id );
                        if ( $attachment_url ) {
                            $attachments[] = $attachment_url;
                        }
                    }
                    wp_mail( $to, $theSubject, $message, $headers, $attachments );
                }
            }
            // admin notification
            if ( $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $from_name = ( isset( $_POST['wpsc_admin_thread_from_name'] ) && $_POST['wpsc_admin_thread_from_name'] != '' ) ? $_POST['wpsc_admin_thread_from_name'] : ( ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' ) );
                $from_email = ( isset( $_POST['wpsc_admin_thread_from_email'] ) && $_POST['wpsc_admin_thread_from_email'] != '' ) ? $_POST['wpsc_admin_thread_from_email'] : ( ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' ) );
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $agent = get_user_by( 'id', $ticket->agent_id );
                $to = array( $agent->user_email );
                $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                $headers[] = 'Reply-to: ' . $reply_to;
                $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_admin'";
                $result = $wpdb->get_var( $sql );
                $result = html_entity_decode( stripcslashes( $result ) );
                $message .= $result;
                $message = get_signature( $message );
                $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                if ( isset( $_POST['wpsc_admin_thread_inlcudes'] ) && $_POST['wpsc_admin_thread_inlcudes'] != '' ) {
                    $include_threads = explode( ',', $_POST['wpsc_admin_thread_inlcudes'] );
                    rsort( $include_threads );
                    foreach ($include_threads as $thread ) {
                        $message .= include_thread( $thread );
                    }
                }
                $attachments = array();
                $attachment_ids = explode( ',', $ticket->attachments );
                foreach ( $attachment_ids as $attachment_id ) {
                    //$attachment_url = wp_get_attachment_url( $attachment_id );
                    $attachment_url = get_attached_file( $attachment_id );
                    if ( $attachment_url ) {
                        $attachments[] = $attachment_url;
                    }
                }
                wp_mail( $to, $theSubject, $message, $headers, $attachments );
            }
            break;
        case 'resend_notification':
            // client notification
            if ( $wpsc_options['wpsc_notification_ticket_reply_client_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                if ( $wpsc_options['wpsc_use_agent_email'] == 1 ) {
                    $from_name = $ticket->agent;
                    $from_email = $ticket->agent_email;
                } else {
                    $from_name = ( isset( $_POST['wpsc_admin_thread_from_name'] ) && $_POST['wpsc_admin_thread_from_name'] != '' ) ? $_POST['wpsc_admin_thread_from_name'] : ( ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' ) );
                    $from_email = ( isset( $_POST['wpsc_admin_thread_from_email'] ) && $_POST['wpsc_admin_thread_from_email'] != '' ) ? $_POST['wpsc_admin_thread_from_email'] : ( ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' ) );
                }
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $to_array = array( $ticket->client_email );
                if ( is_array( $to_array ) && !empty( $to_array ) ) {
                    $to = array();
                    foreach ( $to_array as $to_item ) {
                        $user = get_user_by( 'email', $to_item );
                        if ( $user ) {
                            $to[] = $user->display_name . ' <' . $to_item . '>';
                        } else {
                            $to[] = $to_item;
                        }
                    }
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Reply-to: ' . $reply_to;
                    $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                    $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_client'";
                    $result = $wpdb->get_var( $sql );
                    $result = html_entity_decode( stripcslashes( $result ) );
                    $message .= $result;
                    $message = get_signature( $message, true );
                    $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                    $message .= '<p><img src="' . WPSC_PLUGIN_URL . 'assets/images/blank.gif?tid=' . $thread_id . '"></p>';
                    if ( isset( $_POST['wpsc_admin_thread_inlcudes'] ) && $_POST['wpsc_admin_thread_inlcudes'] != '' ) {
                        $include_threads = explode( ',', $_POST['wpsc_admin_thread_inlcudes'] );
                        rsort( $include_threads );
                        foreach ($include_threads as $thread ) {
                            $message .= include_thread( $thread );
                        }
                    }
                    $attachments = array();
                    $attachment_ids = explode( ',', $ticket->attachments );
                    foreach ( $attachment_ids as $attachment_id ) {
                        //$attachment_url = wp_get_attachment_url( $attachment_id );
                        $attachment_url = get_attached_file( $attachment_id );
                        if ( $attachment_url ) {
                            $attachments[] = $attachment_url;
                        }
                    }
                    wp_mail( $to, $theSubject, $message, $headers, $attachments );
                    $return['client'] = 'true';
                } else {
                    $return['client'] = 'false';
                }
            } else {
                $return['client'] = 'false';
            }
            // admin notification
            if ( $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $from_name = ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' );
                $from_email = ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' );
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $agent = get_user_by( 'id', $ticket->agent_id );
                $to = array( $agent->user_email );
                $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                $headers[] = 'Reply-to: ' . $reply_to;
                $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_admin'";
                $result = $wpdb->get_var( $sql );
                $result = html_entity_decode( stripcslashes( $result ) );
                $message .= $result;
                $message = get_signature( $message, true );
                $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                if ( isset( $_POST['wpsc_admin_thread_inlcudes'] ) && $_POST['wpsc_admin_thread_inlcudes'] != '' ) {
                    $include_threads = explode( ',', $_POST['wpsc_admin_thread_inlcudes'] );
                    rsort( $include_threads );
                    foreach ($include_threads as $thread ) {
                        $message .= include_thread( $thread );
                    }
                }
                $attachments = array();
                $attachment_ids = explode( ',', $ticket->attachments );
                foreach ( $attachment_ids as $attachment_id ) {
                    //$attachment_url = wp_get_attachment_url( $attachment_id );
                    $attachment_url = get_attached_file( $attachment_id );
                    if ( $attachment_url ) {
                        $attachments[] = $attachment_url;
                    }
                }
                wp_mail( $to, $theSubject, $message, $headers, $attachments );
                $return['admin'] = 'true';
            } else {
                $return['admin'] = 'false';
            }
            return $return;
            break;
        case 'client_reply':
            // client notification
            if ( $wpsc_options['wpsc_notification_ticket_reply_client_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                if ( $wpsc_options['wpsc_use_agent_email'] == 1 ) {
                    $from_name = $ticket->agent;
                    $from_email = $ticket->agent_email;
                } else {
                    $from_name = ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' );
                    $from_email = ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' );
                }
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $to_array = ( isset( $_POST['wpsc_front_new_thread_to'] ) && $_POST['wpsc_front_new_thread_to'] != '' ) ? explode( ',', $_POST['wpsc_front_new_thread_to'] ) : array();
                $cc = ( isset( $_POST['wpsc_front_new_thread_cc'] ) && $_POST['wpsc_front_new_thread_cc'] != '' ) ? explode( ',', $_POST['wpsc_front_new_thread_cc'] ) : array();
                $bcc = ( isset( $_POST['wpsc_front_new_thread_bcc'] ) && $_POST['wpsc_front_new_thread_bcc'] != '' ) ? explode( ',', $_POST['wpsc_front_new_thread_bcc'] ) : array();
                if ( is_array( $to_array ) && !empty( $to_array ) ) {
                    $to = array();
                    foreach ( $to_array as $to_item ) {
                        $user = get_user_by( 'email', $to_item );
                        if ( $user ) {
                            $to[] = $user->display_name . ' <' . $to_item . '>';
                        } else {
                            $to[] = $to_item;
                        }
                    }
					do_action( 'wpsc_thread_notifications', $ticket_id, $thread_id, $to, $cc, $bcc );
                    foreach ( $cc as $c ) {
                    	if ( !is_null( $c ) && !empty( $c ) && $c != '' ) {
                        	$headers[] = 'Cc: ' . $c;
						}
                    }
                    foreach ( $bcc as $bc ) {
                        if ( !is_null( $bc ) && !empty( $bc ) && $bc != '' ) {
                        	$headers[] = 'Bcc: ' . $bc;
						}
                    }
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Reply-to: ' . $reply_to;
                    $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                    $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_client'";
                    $result = $wpdb->get_var( $sql );
                    $result = html_entity_decode( stripcslashes( $result ) );
                    $message .= $result;
                    $message = get_signature( $message );
                    $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                    $message .= '<p><img src="' . WPSC_PLUGIN_URL . 'assets/images/blank.gif?tid=' . $thread_id . '"></p>';
                    $attachments = array();
                    $attachment_ids = explode( ',', $ticket->attachments );
                    foreach ( $attachment_ids as $attachment_id ) {
                        //$attachment_url = wp_get_attachment_url( $attachment_id );
                        $attachment_url = get_attached_file( $attachment_id );
                        if ( $attachment_url ) {
                            $attachments[] = $attachment_url;
                        }
                    }
                    wp_mail( $to, $theSubject, $message, $headers, $attachments );
                }
            }
            // admin notification
            if ( $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $from_name = $ticket->author;
                $from_email = $ticket->author_email;
                $reply_to = $ticket->author_email;
                $to = array( $ticket->agent_email );
                $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                $headers[] = 'Reply-to: ' . $reply_to;
                $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_admin'";
                $result = $wpdb->get_var( $sql );
                $result = html_entity_decode( stripcslashes( $result ) );
                $message .= $result;
                $message = get_signature( $message );
                $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                $attachments = array();
                $attachment_ids = explode( ',', $ticket->attachments );
                foreach ( $attachment_ids as $attachment_id ) {
                    //$attachment_url = wp_get_attachment_url( $attachment_id );
                    $attachment_url = get_attached_file( $attachment_id );
                    if ( $attachment_url ) {
                        $attachments[] = $attachment_url;
                    }
                }
                wp_mail( $to, $theSubject, $message, $headers, $attachments );
            }
            break;
        case 'change_ticket':
            // client notification
            if ( $wpsc_options['wpsc_notification_ticket_change_client_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                if ( $wpsc_options['wpsc_use_agent_email'] == 1 ) {
                    $from_name = $ticket->agent;
                    $from_email = $ticket->agent_email;
                } else {
                    $from_name = ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' );
                    $from_email = ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' );
                }
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $to = array( $ticket->client_email );
				$cc = array();
				$bcc = array();
                if ( is_array( $to ) && !empty( $to ) ) {
                	do_action( 'wpsc_thread_notifications', $ticket_id, $thread_id, $to, $cc, $bcc );
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Reply-to: ' . $reply_to;
                    $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                    $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_change_client'";
                    $result = $wpdb->get_var( $sql );
                    $result = html_entity_decode( stripcslashes( $result ) );
                    $message .= $result;
                    $message = get_signature( $message );
                    $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                    $attachments = array();
                    $attachment_ids = explode( ',', $ticket->attachments );
                    foreach ( $attachment_ids as $attachment_id ) {
                        //$attachment_url = wp_get_attachment_url( $attachment_id );
                        $attachment_url = get_attached_file( $attachment_id );
                        if ( $attachment_url ) {
                            $attachments[] = $attachment_url;
                        }
                    }
                    wp_mail( $to, $theSubject, $message, $headers, $attachments );
                }
            }
            // admin notification
            if ( $wpsc_options['wpsc_notification_ticket_change_admin_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $from_name = ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' );
                $from_email = ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' );
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $agent = get_user_by( 'id', $ticket->agent_id );
                $to = array( $agent->user_email );
                $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                $headers[] = 'Reply-to: ' . $reply_to;
                $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_change_admin'";
                $result = $wpdb->get_var( $sql );
                $result = html_entity_decode( stripcslashes( $result ) );
                $message .= $result;
                $message = get_signature( $message );
                $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                $attachments = array();
                $attachment_ids = explode( ',', $ticket->attachments );
                foreach ( $attachment_ids as $attachment_id ) {
                    //$attachment_url = wp_get_attachment_url( $attachment_id );
                    $attachment_url = get_attached_file( $attachment_id );
                    if ( $attachment_url ) {
                        $attachments[] = $attachment_url;
                    }
                }
                wp_mail( $to, $theSubject, $message, $headers, $attachments );
            }
            break;
        case 'new_ticket_front':
            // client notification
            if ( $wpsc_options['wpsc_notification_ticket_new_client_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                if ( $wpsc_options['wpsc_use_agent_email'] == 1 ) {
                    $from_name = $ticket->agent;
                    $from_email = $ticket->agent_email;
                } else {
                    $from_name = ( $wpsc_options['wpsc_email_from_name'] != '' ) ? $wpsc_options['wpsc_email_from_name'] : get_option( 'blogname' );
                    $from_email = ( $wpsc_options['wpsc_email_from_email'] != '' ) ? $wpsc_options['wpsc_email_from_email'] : get_option( 'admin_email' );
                }
                $reply_to = ( $wpsc_options['wpsc_email_reply_to'] != '' ) ? $wpsc_options['wpsc_email_reply_to'] : get_option( 'admin_email' );
                $to_array = array( $ticket->client_email);
				$cc = array();
				$bcc = array();
                if ( is_array( $to_array ) && !empty( $to_array ) ) {
                    $to = array();
                    foreach ( $to_array as $to_item ) {
                        $user = get_user_by( 'email', $to_item );
                        $user = get_user_by( 'email', $to_item );
                        if ( $user ) {
                            $to[] = $user->display_name . ' <' . $to_item . '>';
                        } else {
                            $to[] = $to_item;
                        }
                    }
					do_action( 'wpsc_thread_notifications', $ticket_id, $thread_id, $to, $cc, $bcc );
                    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                    $headers[] = 'Reply-to: ' . $reply_to;
                    $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                    $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_client'";
                    $result = $wpdb->get_var( $sql );
                    $result = html_entity_decode( stripcslashes( $result ) );
                    $message .= $result;
                    $message = get_signature( $message );
                    $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                    $message .= '<p><img src="' . WPSC_PLUGIN_URL . 'assets/images/blank.gif?tid=' . $thread_id . '"></p>';
                    $attachments = array();
                    $attachment_ids = explode( ',', $ticket->attachments );
                    foreach ( $attachment_ids as $attachment_id ) {
                        //$attachment_url = wp_get_attachment_url( $attachment_id );
                        $attachment_url = get_attached_file( $attachment_id );
                        if ( $attachment_url ) {
                            $attachments[] = $attachment_url;
                        }
                    }
                    wp_mail( $to, $theSubject, $message, $headers, $attachments );
                }
            }
            // admin notification
            if ( $wpsc_options['wpsc_notification_ticket_new_admin_enable'] == 1 ) {
                $headers = array();
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $from_name = $ticket->client;
                $from_email = $ticket->client_email;
                $reply_to = $ticket->client_email;
                $to = array( $ticket->agent_email );
                $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
                $headers[] = 'Reply-to: ' . $reply_to;
                $message = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? WPSC_REPLY_ABOVE . '<br />' : '';
                $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_admin'";
                $result = $wpdb->get_var( $sql );
                $result = html_entity_decode( stripcslashes( $result ) );
                $message .= $result;
                $message = get_signature( $message );
                $message = apply_filters( 'wpsc_do_shortcodes_filter', $message, $ticket_id, $thread_id );
                $attachments = array();
                $attachment_ids = explode( ',', $ticket->attachments );
                foreach ( $attachment_ids as $attachment_id ) {
                    //$attachment_url = wp_get_attachment_url( $attachment_id );
                    $attachment_url = get_attached_file( $attachment_id );
                    if ( $attachment_url ) {
                        $attachments[] = $attachment_url;
                    }
                }
                wp_mail( $to, $theSubject, $message, $headers, $attachments );
            }
            break;
    }
}
function include_thread( $thread_id ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
	$sql = "
		SELECT
			*
		FROM
			" . $wpdb->prefix . "wpsc_threads
		WHERE
			id=" . $thread_id;
    $thread = $wpdb->get_row( $sql );
    if ( null !== $thread ) {
        $message = '';
        $message .= '<hr />';
        $message .= '<table border="0" cellspacing="0" cellpadding="5" style="background-color:#aaaaaa; color:#ffffff;width:100%;">';
            $message .= '<tbody>';
                $message .= '<tr>';
                    $message .= '<td style="width:106px;">' . get_avatar( $thread->author_id ) . '</td>';
                    $message .= '<td>';
                        $message .= '<span style="font-size:16px;"><strong>' . $thread->author . '</strong></span><br />';
                        $message .= '<em>' . $thread->author_email . '</em><br />';
                        $message .= '<em>' . get_date_from_gmt( $thread->thread_timestamp ) . '</em>';
                    $message .= '</td>';
					$message .= '<td>';
						$thread_to = $thread->author_email;
						$thread_cc = $thread->cc_email;
						$thread_bcc = $thread->bcc_email;
						if ( $thread_to != '' ) {
                            $message .= '<strong>To:</strong> ' . $thread_to . '<br />';
                            $message .= '<strong>CC:</strong> ' . $thread_cc . '<br />';
                            $message .= '<strong>BCC:</strong> ' . $thread_bcc;
						}
                    $message .= '</td>';
                $message .= '</tr>';
            $message .= '<tbody>';
        $message .= '</table>';
		$message .= html_entity_decode( stripcslashes( urldecode( base64_decode( $thread->message ) ) ) );
    }
    if ( $message != '' ) {
        return $message;
    }
}
function get_signature( $message, $agent = false ) {
    global $wpdb;
    $signature = $wpdb->get_var( 'SELECT signature FROM ' . $wpdb->prefix . 'wpsc_settings WHERE id=1' );
    if ( $agent == true || ( isset( $_POST['wpsc_admin_thread_create_as'] ) && $_POST['wpsc_admin_thread_create_as'] == 'agent' ) ) {
        $message = str_replace( '[wpsc_signature]', $signature, $message );
    } else {
        $message = str_replace( '[wpsc_signature]', '', $message );
    }
    return $message;
}