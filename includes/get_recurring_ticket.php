<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $wpdb;
function wpsc_get_the_recurring_ticket( $ticket_id ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $sql = "
        SELECT
            t.id,t.subject,t.client_id,t.agent_id,t.category_id,t.priority_id,t.status_id,t.start_timestamp,t.next_timestamp,t.thread,t.attachments,t.enabled,t.notify,t.schedule,
            s.status,s.colour AS status_colour,
            c.category,
            p.priority,p.colour AS priority_colour,
            ua.display_name AS agent,
            uc.user_email AS client_email,uc.display_name AS client
        FROM " . $wpdb->prefix . "wpsc_tickets_recurring t
        LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
        LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id
        LEFT JOIN " . $wpdb->prefix . "users uc ON uc.ID=t.client_id
        WHERE t.id=" . $ticket_id;
    $ticket = $wpdb->get_row( $sql, OBJECT );
    if ( $wpdb->num_rows > 0 ) {
        $output = '';
        $output .= '<div class="panel panel-default">';
            $output .= '<div class="panel-heading"><h4 class="panel-title"><span class="wpsc_required">* = required</span></h4></div>';
            $output .= '<div class="panel-body panel-body-wheat">';
                $output .= '<form method="post" class="form-horizontal">';
                    $output .= '<div class="form-group">';
                        $output .= '<div class="col-xs-12 col-md-3">';
                            $output .= '<label for="wpsc_admin_client_autocomplete_recurring_' . $ticket->id . '">' . apply_filters( 'wpsc_client', 'Client', $wpsc_options ) . ' <span class="wpsc_required">*</span> <a href="#wpsc_ticket_client_dialog" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_client_dialog" class="wpsc_help"></a></label>';
                            $output .= '<td><input type="text" id="wpsc_admin_client_autocomplete_recurring_' . $ticket->id . '" class="form-control" value="' . $ticket->client . '"></td>';
                        $output .= '</div>';
                        $output .= '<div class="col-xs-12 col-md-3">';
                            $output .= '<label for="wpsc_edit_recurring_ticket_agent_id_' . $ticket->id . '">Agent <span class="wpsc_required">*</span></label>';
                            $output .= '<select id="wpsc_edit_recurring_ticket_agent_id_' . $ticket->id . '" name="wpsc_edit_recurring_ticket_agent_id_' . $ticket->id . '" class="wpsc_edit_recurring_ticket wpsc_edit_recurring_ticket_validate form-control">';
                                $output .= '<option value="">Please select...</option>';
                                $args = array(
                                    'orderby' => 'display_name',
                                    'order' => 'ASC'
                                );
                                $all_users = get_users( $args );
                                foreach ( $all_users as $user ) {
                                    if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
                                        $selected = ( $ticket->agent_id == $user->ID ) ? ' selected="selected"' : '';
                                        $output .= '<option value="' . $user->ID . '"' . $selected . '>' . $user->display_name . '</option>';
                                    }
                                }
                            $output .= '</select>';
                        $output .= '</div>';
                        $output .= '<div class="col-xs-12 col-md-3">';
                            $output .= '<label for="wpsc_edit_recurring_ticket_category_' . $ticket->id . '">Category <span class="wpsc_required">*</span></label>';
                            $output .= '<select id="wpsc_edit_recurring_ticket_category_' . $ticket->id . '" class="wpsc_edit_recurring_ticket wpsc_edit_recurring_ticket_validate form-control">';
                                $output .= '<option value="">Please select...</option>';
                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories WHERE enabled=1";
                                $result = $wpdb->get_results( $sql );
                                foreach ( $result as $category ) {
                                    $selected = ( $category->id == $ticket->category_id ) ? ' selected="selected"' : '';
                                    $output .= '<option value="' . $category->id . '"' . $selected . '>' . $category->category . '</option>';
                                }
                            $output .= '</select>';
                        $output .= '</div>';
                        $output .= '<div class="col-xs-12 col-md-3">';
                            $output .= '<label for="wpsc_edit_recurring_ticket_priority_' . $ticket->id . '">Priority <span class="wpsc_required">*</span></label>';
                            $output .= '<select id="wpsc_edit_recurring_ticket_priority_' . $ticket->id . '" class="wpsc_edit_recurring_ticket wpsc_edit_recurring_ticket_validate form-control">';
                                $output .= '<option value="">Please select...</option>';
                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority WHERE enabled=1";
                                $result = $wpdb->get_results( $sql );
                                foreach ( $result as $priority ) {
                                    $selected = ( $priority->id == $ticket->priority_id ) ? ' selected="selected"' : '';
                                    $output .= '<option value="' . $priority->id . '"' . $selected . '>' . $priority->priority . '</option>';
                                }
                            $output .= '</select>';
                        $output .= '</div>';
                    $output .= '</div>';
                    $output .= '<div class="form-group">';
                        $output .= '<div class="col-xs-12">';
                            $output .= '<label for="wpsc_admin_edit_recurring_ticket_subject_' . $ticket->id . '">Subject <span class="wpsc_required">*</span></label>';
                            $output .= '<input type="text" id="wpsc_admin_edit_recurring_ticket_subject_' . $ticket->id . '" class="wpsc_edit_recurring_ticket wpsc_edit_recurring_ticket_validate form-control" value="' . stripcslashes( $ticket->subject ) . '">';
                        $output .= '</div>';
                    $output .= '</div>';
                    $output .= '<div class="form-group">';
                        $output .= '<div class="col-xs-12">';
                            $output .= '<label for="wpsc_edit_recurring_ticket_details_' . $ticket->id . '">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' Details <span class="wpsc_required">*</span></label>';
                            $output .= '<textarea class="wpsc_ckeditor form-control" id="wpsc_edit_recurring_ticket_details_' . $ticket->id . '" name="wpsc_edit_recurring_ticket_details_' . $ticket->id . '">' . html_entity_decode( stripcslashes( $ticket->thread ) ) . '</textarea>';
                        $output .= '</div>';
                    $output .= '</div>';
                    $output .= '<div class="form-group">';
                        $output .= '<div class="col-xs-12 col-md-6">';
                            $output .= '<label for="wpsc_admin_edit_recurring_ticket_attachments_' . $ticket->id . '">Attachments</label>';
                            $output .= '<input type="file" id="wpsc_admin_edit_recurring_ticket_attachments_' . $ticket->id . '" class="wpsc_admin_edit_recurring_ticket_attachments" multiple="multiple" >';
                        $output .= '</div>';
                        $output .= '<div class="col-xs-12 col-md-6">';
                            if ( $ticket->attachments != '' ) {
                                $attachments = explode( ',', $ticket->attachments );
                                $output .= '<table class="wpsc_fullwidth">';
                                    foreach ( $attachments as $attachment ) {
                                        $filename = basename( get_attached_file( $attachment ) );
                                        $url = wp_get_attachment_url( $attachment );
                                        $output .= '<tr><td style="width:40px;"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/trash_32.png" title="Delete Attachment" data-id="' . $attachment . '" data-ticket="' . $ticket->id . '" class="wpsc_recurring_ticket_delete_attachment wpsc_help"></td><td><em><a href="' . $url . '" target="_blank">' . $filename . '</a></em></td></tr>';
                                    }
                                $output .= '</table>';
                            }
                        $output .= '</div>';
                    $output .= '</div>';
                    $output .= '<div class="form-group">';
                        $output .= '<div class="col-xs-12 col-md-6">';
                            $output .= '<label for="wpsc_edit_recurring_ticket_schedule_' . $ticket->id . '">Schedule <span class="wpsc_required">*</span></label>';
                            $output .= '<select id="wpsc_edit_recurring_ticket_schedule_' . $ticket->id . '" class="wpsc_edit_recurring_ticket wpsc_edit_recurring_ticket_validate wpsc_fullwidth">';
                                $output .= '<option value="">Please select...</option>';
                                $output .= '<option value="1"' . ( ( $ticket->schedule == 1 ) ? ' selected="selected"' : '' ) . '>Daily</option>';
                                $output .= '<option value="2"' . ( ( $ticket->schedule == 2 ) ? ' selected="selected"' : '' ) . '>Weekly</option>';
                                $output .= '<option value="3"' . ( ( $ticket->schedule == 3 ) ? ' selected="selected"' : '' ) . '>Fortnightly</option>';
                                $output .= '<option value="4"' . ( ( $ticket->schedule == 4 ) ? ' selected="selected"' : '' ) . '>Monthly</option>';
                                $output .= '<option value="5"' . ( ( $ticket->schedule == 5 ) ? ' selected="selected"' : '' ) . '>Quarterly</option>';
                                $output .= '<option value="6"' . ( ( $ticket->schedule == 6 ) ? ' selected="selected"' : '' ) . '>Annually</option>';
                            $output .= '</select>';
                        $output .= '</div>';
                        $output .= '<div class="col-xs-12 col-md-6">';
                            $output .= '<label for="wpsc_edit_recurring_ticket_date_from_' . $ticket->id . '">Start Date <span class="wpsc_required">*</span></label>';
                            $output .= '<input id="wpsc_edit_recurring_ticket_date_from_' . $ticket->id . '" name="wpsc_edit_recurring_ticket_date_from_' . $ticket->id . '" class="wpsc_edit_recurring_ticket wpsc_recurring_ticket_date wpsc_edit_recurring_ticket_validate form-control" value="' . date( "Y-m-d", strtotime( $ticket->next_timestamp ) ) . '">';
                        $output .= '</div>';
                    $output .= '</div>';
                    $output .= '<div class="form-group">';
                        $output .= '<div class="col-xs-12 col-md-6">';
                            $output .= '<div class="checkbox">';
                                $selected = ( $ticket->enabled == 1 ) ? ' checked="checked"' : '';
                                $output .= '<label for="wpsc_admin_edit_recurring_ticket_enable_' . $ticket->id . '"><input type="checkbox" id="wpsc_admin_edit_recurring_ticket_enable_' . $ticket->id . '" value="1"' . $selected . '> Enable Recurring ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '?</label>';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="col-xs-12 col-md-6">';
                            $output .= '<div class="checkbox">';
                                $selected = ( $ticket->notify == 1 ) ? ' checked="checked"' : '';
                                $output .= '<label for="wpsc_admin_edit_recurring_ticket_notify_' . $ticket->id . '"><input type="checkbox" id="wpsc_admin_edit_recurring_ticket_notify_' . $ticket->id . '" value="1"' . $selected . '> Send Notifications?</label>';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</div>';
                    $output .= '<input type="hidden" id="wpsc_edit_recurring_ticket_client_id_' . $ticket->id . '" class="wpsc_edit_recurring_ticket wpsc_edit_recurring_ticket_validate" value="' . $ticket->client_id . '">';
                    $output .= '<input type="hidden" id="wpsc_edit_recurring_ticket_attachments_existing_' . $ticket->id . '" class="wpsc_edit_recurring_ticket" value="' . $ticket->attachments . '">';
                    $output .= '<button type="button" class="wpsc_admin_button wpsc_save_recurring_ticket_button btn btn-primary btn-sm" id="wpsc_edit_recurring_ticket_save_' . $ticket->id . '" data-id="' . $ticket->id . '">Save Changes</button> <button type="button" class="wpsc_admin_button wpsc_delete_recurring_ticket_button btn btn-primary btn-sm" id="wpsc_delete_recurring_ticket_' . $ticket->id . '" data-id="' . $ticket->id . '">Delete Recurring ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '</button>';
                $output .= '</form>';
            $output .= '</div>';
        $output .= '</div>';
        return $output;
    } else {
        return 'false';
    }
}
?>