<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $wpdb;
function wpsc_get_the_front_ticket( $ticket_id ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $sql = "
        SELECT
            t.id,t.subject,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.category_id,t.priority_id,t.status_id,t.client_email,t.client_phone,t.shared_users,
            s.status,s.colour AS status_colour,
            c.category,
            p.priority,p.colour AS priority_colour,
            ua.display_name AS agent
        FROM " . $wpdb->prefix . "wpsc_tickets t
        LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
        LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id
        WHERE t.id=" . $ticket_id;
    $ticket = $wpdb->get_row( $sql, OBJECT );
    if ( $wpdb->num_rows > 0 ) {
        $user_id = get_current_user_id();
        $shared_users = explode( ',', $ticket->shared_users );
        if ( ( $user_id == $ticket->client_id ) || current_user_can( 'manage_wpsc_agent' ) || in_array( get_current_user_id(), $shared_users ) ) {
            $output = '';
            $output .= '<div class="panel panel-default">';
                $output .= '<div class="panel-heading"><h4 class="panel-title">' . stripcslashes( $ticket->subject ) . ' | <em>' . $ticket->client . ' (' . $ticket->client_email . ')</em> <span class="wpsc_required">* = required</span></h4></div>';
                $output .= '<div class="panel-body">';
                    $output .= '<form method="post" class="form-horizontal">';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12 col-md-6">';
                                $output .= '<label for="wpsc_front_new_thread_priority_' . $ticket->id . '">Priority <span class="wpsc_required">*</span></label>';
                                $output .= '<select id="wpsc_front_new_thread_priority_' . $ticket->id . '" class="wpsc_new_thread wpsc_new_thread_validate form-control">';
                                    $output .= '<option value="">Please select...</option>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority WHERE enabled=1";
                                    $result = $wpdb->get_results( $sql );
                                    foreach ( $result as $priority ) {
                                        $selected = ( $priority->id == $ticket->priority_id ) ? ' selected="selected"' : '';
                                        $output .= '<option value="' . $priority->id . '"' . $selected . '>' . $priority->priority . '</option>';
                                    }
                                $output .= '</select>';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-6">';
                                $output .= '<label for="wpsc_front_new_thread_phone_' . $ticket->id . '">Phone</label>';
                                $output .= '<input type="text" id="wpsc_front_new_thread_phone_' . $ticket->id . '" class="form-control" value="' . $ticket->client_phone . '">';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12 col-md-4">';
                                $output .= '<label for="wpsc_front_new_thread_status_' . $ticket->id . '">Status</label>';
                                $output .= '<input type="text" id="wpsc_front_new_thread_status_' . $ticket->id . '" class="form-control" readonly value="' . $ticket->status . '">';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-4">';
                                $output .= '<label for="wpsc_front_new_thread_category_' . $ticket->id . '">Category</label>';
                                $output .= '<input type="text" id="wpsc_front_new_thread_category_' . $ticket->id . '" class="form-control" readonly value="' . $ticket->category . '">';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-4">';
                                $output .= '<label for="wpsc_front_new_thread_agent_' . $ticket->id . '">Agent</label>';
                                $output .= '<input type="text" id="wpsc_front_new_thread_agent_' . $ticket->id . '" class="form-control" readonly value="' . $ticket->agent . '">';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12">';
								$label = '<label for="wpsc_front_ticket_note_' . $ticket->id . '">Your Reply <span class="wpsc_required">*</span></label>';
                                $output .= apply_filters( 'wpsc_front_ticket_details', $label );
                                $output .= '<textarea class="wpsc_ckeditor wpsc_front_ticket_note form-control" id="wpsc_front_ticket_note_' . $ticket->id . '" name="wpsc_front_ticket_note_' . $ticket->id . '">' . base64_decode( $ticket->message ) . '</textarea>';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12">';
                                $output .= '<label for="wpsc_front_new_thread_attachments_' . $ticket->id . '">Attachments</label>';
                                $output .= '<input type="file" id="wpsc_front_new_thread_attachments_' . $ticket->id . '"  class="wpsc_front_new_thread_attachments" multiple="multiple" >';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12 col-md-4">';
                                $output .= '<label for="wpsc_front_new_thread_to_' . $ticket->id . '">To</label>';
                                $output .= '<input type="text" id="wpsc_front_new_thread_to_' . $ticket->id . '" value="' . $ticket->client_email . '" class="form-control" readonly>';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-4">';
                                $output .= '<label for="wpsc_front_new_thread_cc_' . $ticket->id . '">CC <a href="#wpsc_ticket_email_input" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_email_input" class="wpsc_help"></a></label>';
                                $output .= '<input type="text" id="wpsc_front_new_thread_cc_' . $ticket->id . '" value="" class="wpsc_new_thread form-control wpsc_new_thread_validate">';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-4">';
                                $output .= '<label for="wpsc_front_new_thread_bcc_' . $ticket->id . '">BCC <a href="#wpsc_ticket_email_input" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_email_input" class="wpsc_help"></a></label>';
                                $output .= '<input type="text" id="wpsc_front_new_thread_bcc_' . $ticket->id . '" value="" class="wpsc_new_thread form-control wpsc_new_thread_validate">';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12">';
                                $output .= '<button type="button" class="wpsc_front_new_thread_button btn btn-primary btn-sm" id="wpsc_new_thread_' . $ticket->id . '" data-id="' . $ticket->id . '">Send Reply</button>';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<input type="hidden" id="wpsc_front_new_thread_client_' . $ticket->id . '" class="wpsc_new_thread" value="' . $ticket->client . '">';
                        $output .= '<input type="hidden" id="wpsc_front_new_thread_client_id_' . $ticket->id . '" class="wpsc_new_thread" value="' . $ticket->client_id . '">';
                        $output .= '<input type="hidden" id="wpsc_front_new_thread_client_email_' . $ticket->id . '" class="wpsc_new_thread" value="' . $ticket->client_email . '">';
                        $agent = get_userdata( $ticket->agent_id );
                        $output .= '<input type="hidden" id="wpsc_front_new_thread_agent_name_' . $ticket->id . '" class="wpsc_new_thread" value="' . $agent->display_name . '">';
                        $output .= '<input type="hidden" id="wpsc_front_new_thread_agent_id_' . $ticket->id . '" class="wpsc_new_thread" value="' . $agent->ID . '">';
                        $output .= '<input type="hidden" id="wpsc_front_new_thread_agent_email_' . $ticket->id . '" class="wpsc_new_thread" value="' . $agent->user_email . '">';
                        $output .= '<input type="hidden" id="wpsc_front_new_thread_agent_reply_to_' . $ticket->id . '" class="wpsc_new_thread" value="' . $wpsc_options['wpsc_email_reply_to'] . '">';
                    $output .= '</form>';
                $output .= '</div>';
            $output .= '</div>';
            if ( current_user_can( 'manage_wpsc_ticket' ) || current_user_can( 'manage_wpsc_agent' ) ) {
                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE ticket_id=" . $ticket->id . " ORDER BY thread_timestamp DESC";
            } else {
                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE ticket_id=" . $ticket->id . " AND is_private=0 ORDER BY thread_timestamp DESC";
            }
            $threads = $wpdb->get_results( $sql, OBJECT );
            if ( $wpdb->num_rows > 0 ) {
                $output .= '<div class="panel-group" id="wpsc_front_ticket_threads_' . $ticket->id . '">';
                    $thread_count = 1;
                    $selected = ( $wpsc_options['wpsc_reply_include'] == '2' ) ? ' checked="checked"' : '';
                    foreach ( $threads as $thread ) {
                        $output .= '<div class="panel panel-default">';
                            $output .= '<div class="panel-heading" data-toggle="collapse" data-target="#wpsc_front_ticket_thread_' . $thread->id . '" data-parent="#wpsc_front_ticket_threads_' . $ticket->id . '">';
                                $is_private = ( $thread->is_private == 1 ) ? ' <span class="glyphicon glyphicon-lock has-attachments" title="Private Thread"></span>' : '';
                                $has_attachments = ( $thread->attachments != '' ) ? ' <span class="glyphicon glyphicon-paperclip has-attachments"></span>' : '';
                                $output .= '<h4 class="panel-title">' . ($thread_count == 1 ? '<span class="glyphicon glyphicon-minus"></span>' : '<span class="glyphicon glyphicon-plus"></span>') . ' ' . $thread->author . ' <em>' . $thread->author_email . '</em> [' . get_date_from_gmt( $thread->thread_timestamp ) . ']' . $is_private . $has_attachments . '</h4>';
                            $output .= '</div>';
                            $output .= '<div id="wpsc_front_ticket_thread_' . $thread->id . '" class="panel-collapse collapse ' . ($thread_count == 1 ? 'in' : '') . '">';
                                $output .= '<div class="panel-body panel-body-wheat">';
                                    $output .= '<table class="wpsc_fullwidth wpsc_thread_header">';
                                        $output .= '<tr>';
                                            $output .= '<td class="wpsc_width106px">';
                                                $output .= get_avatar( $thread->author_id );
                                            $output .= '</td>';
                                            $output .= '<td>';
                                                $output .= '<span class="wpsc_thread_header_author">' . $thread->author . '</span><br />';
                                                $output .= '<em>' . $thread->author_email . '</em><br />';
                                                $output .= '<em>' . get_date_from_gmt( $thread->thread_timestamp ) . '</em>';
                                            $output .= '</td>';
                                            $output .= '<td class="wpsc_width106px valign_top">';
                                            $output .= '</td>';
                                        $output .= '</tr>';
                                    $output .= '</table>';
                                    $output .= base64_decode( $thread->message );
                                    $output .= '<hr />';
                                    if ( $thread->attachments != '' ) {
                                        $output .= '<h3>Attachments</h3>';
                                        $attachments = explode( ',', $thread->attachments );
                                        foreach ( $attachments as $attachment ) {
                                            $filename = basename( get_attached_file( $attachment ) );
                                            $url = wp_get_attachment_url( $attachment );
                                            $output .= '<em><a href="' . $url . '" target="_blank">' . $filename . '</a></em><br />';
                                        }
                                    }
                                $output .= '</div>';
                            $output .= '</div>';
                        $output .= '</div>';
                        $thread_count++;
                    }
                $output .= '</div>';
            }
        } else {
            $output = '';
            $output .= '<div class="panel panel-default">';
                $output .= '<div class="panel-heading"><h4 class="panel-title">Not Authorised</h4></div>';
                $output .= '<div class="panel-body">';
                    $output .= '<p>You do not have permissions to view this ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '.</p>';
                $output .= '</div>';
            $output .= '</div>';
        }
        return $output;
    } else {
        return 'false';
    }
}