<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function wpsc_get_user_information( $user_id, $display_name, $is_ticket = false, $ticket_id = '' ) {
    global $wpdb;
    $id = ( $is_ticket !== false ) ? $ticket_id : $user_id;
    $ticket = ( $is_ticket !== false ) ? 'true' : 'false';
    $output = '';
    $output .= '<div id="wpsc_account_' . $id . '" class="tab-pane fade">';
        $output .= '<div class="panel panel-default">';
            $output .= '<div class="panel-body panel-body-wheat">';
                $output .= '<h2>Account Information</h2>';
                $output .= '<p><a href="' . admin_url( 'admin.php?page=wp-support-centre&filter=true&client_id=' . $user_id ) . '">Ticket History</a></p>';
                $output .= '<form method="post" class="form-horizontal">';
                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_account WHERE user_id=" . $user_id;
                    $account = $wpdb->get_row( $sql );
                    if ( null !== $account ) {
                        $output .= '<div class="panel panel-default">';
                            $user = get_user_by( 'id', $account->updated_by );
                            $output .= '<div class="panel-heading"><h4 class="panel-title">' . $display_name . ' (' . $user_id . ') Last Updated: ' . get_date_from_gmt( $account->updated_timestamp ) . ' by ' . $user->display_name . '</h4></div>';
                            $output .= '<div class="panel-body panel-body-wheat">';
                                if ( $account->content != '' ) {
                                    $output .= stripcslashes( $account->content );
                                } else {
                                    $output .= '<p>No account information found for ' . $display_name . ' (' . $user_id . ')</p>';
                                }
                            $output .= '</div>';
                        $output .= '</div>';
                    } else {
                        $output .= '<div class="panel panel-default">';
                            $output .= '<div class="panel-heading"><h4 class="panel-title">' . $display_name . ' (' . $user_id . ')</h4></div>';
                            $output .= '<div class="panel-body panel-body-wheat">';
                                $output .= '<p>No account information found for ' . $display_name . ' (' . $user_id . ')</p>';
                            $output .= '</div>';
                        $output .= '</div>';
                    }
                    $output .= '<div class="panel panel-default">';
                        $output .= '<div class="panel-heading"><h4 class="panel-title">Edit Account Information</h4></div>';
                        $output .= '<div class="panel-body panel-body-wheat">';
                            $output .= '<div class="form-group">';
                                $output .= '<div class="col-xs-12">';
                                    $output .= '<label for="wpsc_account_information_' . $id . '">Account Information <span class="wpsc_required">*</span></label>';
                                    if ( null !== $account ) {
                                    	$output .= '<textarea class="wpsc_ckeditor wpsc_account_information form-control" id="wpsc_account_information_' . $id . '" name="wpsc_account_information_' . $id . '">' . html_entity_decode( stripcslashes( $account->content ) ) . '</textarea>';
									} else {
										$output .= '<textarea class="wpsc_ckeditor wpsc_account_information form-control" id="wpsc_account_information_' . $id . '" name="wpsc_account_information_' . $id . '"></textarea>';
									}
                                $output .= '</div>';
                            $output .= '</div>';
                            $output .= '<div class="form-group">';
                                $output .= '<div class="col-xs-12">';
                                    $output .= '<button type="button" class="wpsc_account_save_changes_button wpsc_account_save_changes btn btn-primary btn-sm" id="wpsc_account_save_changes_' . $id . '" data-id="' . $id . '" data-user-id="' . get_current_user_id() . '" data-account-id="' . $user_id . '" data-is-ticket="' . $ticket . '">Save Changes</button>';
                                $output .= '</div>';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</div>';
                $output .= '</form>';
            $output .= '</div>';
        $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function do_wpsc_admin_ticket_save_button( $button_save, $ticket_id ) {
	$button_save = '';
	$button_save .= '<div class="btn-group">';
		$button_save .= '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">Save <span class="caret"></span></button>';
		$button_save .= '<ul class="dropdown-menu">';
			$button_save .= '<li><a href="#" class="wpsc_ticket_save_changes_button wpsc_ticket_save_changes" id="wpsc_ticket_save_changes_' . $ticket_id . '" data-id="' . $ticket_id . '">Save Changes</a></li>';
			$button_save .= '<li><a href="#" class="wpsc_ticket_save_changes_button wpsc_ticket_save_changes wpsc_close" id="wpsc_ticket_save_changes_close_' . $ticket_id . '" data-id="' . $ticket_id . '">Save Changes & Close</a></li>';
			$button_save .= '<li><a href="#" class="wpsc_ticket_save_changes_button wpsc_ticket_save_changes_notify" id="wpsc_ticket_save_changes_notify_' . $ticket_id . '" data-id="' . $ticket_id . '">Save Changes & Notify</a></li>';
			$button_save .= '<li><a href="#" class="wpsc_ticket_save_changes_button wpsc_ticket_save_changes_notify wpsc_close" id="wpsc_ticket_save_changes_notify_close_' . $ticket_id . '" data-id="' . $ticket_id . '">Save Changes,Notify & Close</a></li>';
		$button_save .= '</ul>';
	$button_save .= '</div>';
	$button_save .= '<a href="#wpsc_ticket_save_changes_dialog" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_save_changes_dialog" class="wpsc_help"></a>';
	return $button_save;
}
add_filter( 'wpsc_admin_ticket_save_button', 'do_wpsc_admin_ticket_save_button', 10, 2 );

function do_wpsc_admin_ticket_note_button( $button_note, $ticket_id ) {
	$button_note = '';
	$button_note .= '<div class="btn-group">';
		$button_note .= '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">Note <span class="caret"></span></button>';
		$button_note .= '<ul class="dropdown-menu">';
			$button_note .= '<li><a href="#"  class="wpsc_ticket_new_thread_button wpsc_ticket_new_thread" id="wpsc_new_note_' . $ticket_id . '" data-id="' . $ticket_id . '">Add Note</a></li>';
			$button_note .= '<li><a href="#"  class="wpsc_ticket_new_thread_button wpsc_ticket_new_thread wpsc_close" id="wpsc_new_note_close_' . $ticket_id . '" data-id="' . $ticket_id . '">Add Note & Close</a></li>';
		$button_note .= '</ul>';
	$button_note .= '</div> ';
	$button_note .= '<a href="#wpsc_ticket_add_note_dialog" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_add_note_dialog" class="wpsc_help"></a>';
	return $button_note;
}
add_filter( 'wpsc_admin_ticket_note_button', 'do_wpsc_admin_ticket_note_button', 10, 2 );

function do_wpsc_admin_ticket_reply_button( $button_reply, $ticket_id ) {
	$button_reply = '';
	$button_reply .= '<div class="btn-group">';
		$button_reply .= '<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">Reply <span class="caret"></span></button>';
		$button_reply .= '<ul class="dropdown-menu">';
			$button_reply .= '<li><a href="#"  class="wpsc_ticket_new_thread_button wpsc_ticket_new_thread" id="wpsc_new_thread_' . $ticket_id . '" data-id="' . $ticket_id . '">Send Reply</a></li>';
			$button_reply .= '<li><a href="#"  class="wpsc_ticket_new_thread_button wpsc_ticket_new_thread wpsc_close" id="wpsc_new_thread_close_' . $ticket_id . '" data-id="' . $ticket_id . '">Send Reply & Close</a></li>';
		$button_reply .= '</ul>';
	$button_reply .= '</div> ';
	$button_reply .= '<a href="#wpsc_ticket_send_reply_dialog" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_send_reply_dialog" class="wpsc_help"></a>';
	return $button_reply;
}
add_filter( 'wpsc_admin_ticket_reply_button', 'do_wpsc_admin_ticket_reply_button', 10, 2 );

function do_wpsc_admin_ticket_threads( $value, $ticket, $wpsc_options ) {
	global $wpdb;
	$output = '';
	$sql = "
		SELECT
			*
		FROM
			" . $wpdb->prefix . "wpsc_threads
		WHERE
			ticket_id=" . $ticket->id . "
		ORDER BY
			thread_timestamp
		DESC";
	$threads = $wpdb->get_results( $sql, OBJECT );
	if ( $wpdb->num_rows > 0 ) {
		$selected = ( ( $wpdb->num_rows == 1 && $wpsc_options['wpsc_reply_include'] == '1' ) || $wpsc_options['wpsc_reply_include'] == '2' ) ? ' checked="checked"' : '';
	    $output .= '<div class="panel panel-default">';
	        $output .= '<div class="panel-body panel-body-wheat"><input type="checkbox" class="wpsc_admin_thread_include_all" id="wpsc_admin_thread_include_all_' . $ticket->id . '" data-id="' . $ticket->id . '"' . $selected . '> <label for="wpsc_admin_thread_include_all_' . $ticket->id . '">Include all threads in reply</label></div>';
	    $output .= '</div>';
	    $output .= '<div class="panel-group wpsc_thread_panel_group" id="wpsc_admin_ticket_threads_' . $ticket->id . '" data-id="' . $ticket->id . '">';
	        $thread_count = 1;
	        $pinnedThreads = '';
	        $unpinnedThreads = '';
	        foreach ( $threads as $thread ) {
	            $theThread = '';
	            $is_pinned = ( $thread->is_pinned == 1 ) ? ' <span class="glyphicon glyphicon-star wpsc_pinned_thread wpsc_pinned_thread_' . $ticket->id . ' no-collapsable" title="Click to unpin" data-id="' . $thread->id . '" data-count="' . $thread_count . '"></span>' : ' <span class="glyphicon glyphicon-star-empty wpsc_pinned_thread wpsc_unpinned_thread_' . $ticket->id . ' no-collapsable" title="Click to pin to top" data-id="' . $thread->id . '" data-count="' . $thread_count . '"></span>';
	            $theThread .= ( $thread->is_pinned == 1 ) ? '<div class="panel panel-default wpsc_ticket_thread_' . $ticket->id . ' wpsc_thread_' . $thread->id . ' wpsc_pinned_thread_panel_' . $ticket->id . '" data-id="' . $thread->id . '" data-count="' . $thread_count . '">' : '<div class="panel panel-default wpsc_ticket_thread_' . $ticket->id . ' wpsc_thread_' . $thread->id . ' wpsc_unpinned_thread_panel_' . $ticket->id . '" data-id="' . $thread->id . '" data-count="' . $thread_count . '">';
	                $theThread .=
	                	( $thread->is_pinned == 1 ) ?
	                	'<div class="panel-heading wpsc_ticket_thread_heading_' . $ticket->id . ' wpsc_thread_is_pinned" data-id="' . $thread->id . '" data-count="' . $thread_count . '" data-toggle="collapse" data-target="#wpsc_admin_ticket_thread_' . $thread->id . '" data-parent="#wpsc_admin_ticket_threads_' . $ticket->id . '">' :
	                	'<div class="panel-heading wpsc_ticket_thread_heading_' . $ticket->id . '" data-id="' . $thread->id . '" data-count="' . $thread_count . '" data-toggle="collapse" data-target="#wpsc_admin_ticket_thread_' . $thread->id . '" data-parent="#wpsc_admin_ticket_threads_' . $ticket->id . '">';

	                    $is_private = ( $thread->is_private == 1 ) ? ' <span class="glyphicon glyphicon-lock has-attachments" title="Private Thread"></span>' : '';
	                    $has_attachments = ( $thread->attachments != '' ) ? ' <span class="glyphicon glyphicon-paperclip has-attachments"></span>' : '';
	                    $is_notification = ( $thread->notification == 1 ) ? ' <span class="glyphicon glyphicon-envelope has-attachments"></span>' : '';
	                    $read = '';
	                    $is_read = '';
	                    if ( $thread->notification == 1 ) {
	                        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads_read WHERE thread_id=" . $thread->id . " ORDER BY read_timestamp ASC";
	                        $notifications = $wpdb->get_results( $sql, OBJECT );
	                        if ( $wpdb->num_rows > 0 ) {
	                            foreach ( $notifications as $notification ) {
	                                $read .= date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $notification->read_timestamp, 'Y-m-d H:i:s') ) ) . ' ' . get_date_from_gmt( $notification->read_timestamp, 'H:i:s') . ' IP: ' . $notification->ip . '&#13;';
	                            }
	                            $is_read = ' <span class="glyphicon glyphicon-thumbs-up has-attachments" title="' . $read . '"></span>';
	                        }
	                    }
	                    $selected = ( ( $wpsc_options['wpsc_reply_include'] == '1' && $thread_count == 1 ) || $wpsc_options['wpsc_reply_include'] == '2' ) ? ' checked="checked"' : '';
	                    $thread_date = date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $thread->thread_timestamp, 'Y-m-d H:i:s') ) ) . ' ' . get_date_from_gmt( $thread->thread_timestamp, 'H:i:s');
    					if ( $is_private == '' ) {
    						$selected = ( ( $wpsc_options['wpsc_reply_include'] == '1' && $thread_count == 1 ) || $wpsc_options['wpsc_reply_include'] == '2' ) ? ' checked="checked"' : '';
    						$include = ' <input type="checkbox" class="wpsc_admin_thread_include wpsc_admin_thread_include_' . $ticket->id . ' no-collapsable" data-id="' . $thread->id . '" data-ticket="' . $ticket->id . '"' . $selected . '>';
						} else {
							$selected = '';
							$include = ' <input type="checkbox" class="wpsc_admin_thread_include wpsc_admin_thread_include_' . $ticket->id . ' no-collapsable" data-id="' . $thread->id . '" data-ticket="' . $ticket->id . '" disabled="disabled">';
						}
	                    $theThread .= '<h4 class="panel-title">' . ($thread_count == 1 ? '<span class="glyphicon glyphicon-minus"></span>' : '<span class="glyphicon glyphicon-plus"></span>') . $include . ' ' . $is_pinned . ' ' . stripslashes( $thread->author ) . ' <em>' . $thread->author_email . '</em> [' . $thread_date . '][' . $ticket->id . ']' . $is_private . $has_attachments . $is_read . '</h4>';
	                $theThread .= '</div>';
	                $theThread .= '<div id="wpsc_admin_ticket_thread_' . $thread->id . '" class="wpsc_admin_ticket_thread_accordion panel-collapse collapse ' . ($thread_count == 1 ? 'in' : '') . '">';
	                    $theThread .= '<div class="panel-body panel-body-lightyellow">';
							$theThread .= '<div class="form-group wpsc-thread-header">';
								$theThread .= '<div class="col-xs-2">';
									$theThread .= get_avatar( $thread->author_id, 32 );
								$theThread .= '</div>';
								$theThread .= '<div class="col-xs-8">';
									$theThread .= '<span class="wpsc_thread_header_author">' . stripslashes( $thread->author ) . '</span> <em>' . $thread->author_email . '</em><br />';
									$theThread .= '<em>' . $thread_date . '</em><br />';
									$theThread .= '<hr />';
									$thread_to = !is_null( $thread->to_email ) && $thread->to_email != 'null' ? $thread->to_email : '';
									$thread_to = str_replace( array( '<', '>' ), array( '&lt;', '&gt'), $thread_to );
									$thread_cc = !is_null( $thread->cc_email ) && $thread->cc_email != 'null' ? $thread->cc_email : '';
									$thread_bcc = !is_null( $thread->bcc_email ) && $thread->bcc_email != 'null' ? $thread->bcc_email : '';
									if ( $thread_to != '' ) {
										$theThread .= ' <strong>To:</strong> ' . $thread_to;
									}
									if ( $thread_cc != '' ) {
										$theThread .= ' <strong>CC:</strong> ' . $thread_cc;
									}
									if ( $thread_bcc != '' ) {
										$theThread .= ' <strong>BCC:</strong> ' . $thread_bcc;
									}
								$theThread .= '</div>';
								$theThread .= '<div class="col-xs-2">';
									$theThread .= '<span class="glyphicon glyphicon-share-alt has-attachments" title="Copy Thread To Ticket" data-ticket-id="' . $ticket->id . '" data-thread_id="' . $thread->id . '"></span> ';
                                    $theThread .= '<span class="glyphicon glyphicon-new-window has-attachments" title="Create a New ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' using this Thread" data-id="' . $thread->id . '" data-client="' . $ticket->client_id . '"></span> ';
                                    $theThread .= '<span class="glyphicon glyphicon-envelope has-attachments" title="Resend Thread Notifications" data-ticket-id="' . $ticket->id . '" data-thread_id="' . $thread->id . '"></span> ';
								$theThread .= '</div>';
	                        $theThread .= '</div>';
	                        if ( $thread_count == 1 ) {
	                        	$theThread .= '<iframe class="wpsc_thread_body wpsc_shown" data-src="' . WPSC_PLUGIN_URL . 'includes/wpsc_email_body.php?tid=' . $thread->id . '" src="' . WPSC_PLUGIN_URL . 'includes/wpsc_email_body.php?tid=' . $thread->id . '"></iframe>';
	                		} else {
	                			$theThread .= '<iframe class="wpsc_thread_body" data-src="' . WPSC_PLUGIN_URL . 'includes/wpsc_email_body.php?tid=' . $thread->id . '"></iframe>';
	                		}
	                        $theThread .= '<hr />';
	                        if ( $thread->attachments != '' ) {
	                            $theThread .= '<h3>Attachments</h3>';
	                            $attachments = explode( ',', $thread->attachments );
	                            foreach ( $attachments as $attachment ) {
	                                $filename = basename( get_attached_file( $attachment ) );
	                                if ( $filename != '' ) {
	                                    $url = wp_get_attachment_url( $attachment );
	                                    $theThread .= '<em><a href="' . $url . '" target="_blank">' . $filename . '</a></em><br />';
	                                } else {
	                                    $theThread .= '<em>Error ' . $attachment  . ': File Not Found</em><br />';
	                                }
	                            }
	                        }
	                    $theThread .= '</div>';
	                $theThread .= '</div>';
	            $theThread .= '</div>';
	            if ( $thread->is_pinned == 1 ) {
	                $pinnedThreads .= $theThread;
	            } else {
	                $unpinnedThreads .= $theThread;
	            }
	            $thread_count++;
	        }
	        $output .= $pinnedThreads;
	        $output .= $unpinnedThreads;
	    $output .= '</div>';
	}
	return $output;
}
add_filter( 'wpsc_admin_ticket_threads', 'do_wpsc_admin_ticket_threads', 10, 3 );