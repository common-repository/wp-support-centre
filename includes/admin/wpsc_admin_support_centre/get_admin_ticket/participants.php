<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output .= '<div id="wpsc_participants_' . $ticket_id . '" class="tab-pane fade">';
    $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<h2>Participants</h2>';
            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE ticket_id=" . $ticket_id . " GROUP BY author_email ORDER BY id ASC";
            $results = $wpdb->get_results( $sql );
            if ( !empty( $results ) ) {
                $output .= '<table id="wpsc_admin_participants_table_' . $ticket_id . '" class="wpsc_admin_participant_datatable table table-striped table-bordered wpsc_fullwidth">';
                    $output .= '<thead>';
                        $output .= '<tr>';
                            $output .= '<th>ID</th>';
                            $output .= '<th>Name</th>';
                            $output .= '<th>Email</th>';
                        $output .= '</tr>';
                    $output .= '</thead>';
                    $output .= '<tfoot>';
                        $output .= '<tr>';
                            $output .= '<th>ID</th>';
                            $output .= '<th>Name</th>';
                            $output .= '<th>Email</th>';
                        $output .= '</tr>';
                    $output .= '</tfoot>';
                    $output .= '<tbody>';
                        foreach( $results as $participant ) {
                            $output .= '<tr>';
                                $output .= '<td class="align_centre">' . $participant->author_id . '</td>';
                                $output .= '<td class="align_centre">' . $participant->author . '</td>';
                                $output .= '<td class="align_centre"><a href="mailto:' . $participant->author_email . '">' . $participant->author_email . '</a></td>';
                            $output .= '</tr>';
                        }
                    $output .= '</tbody>';
                $output .= '</table>';
            } else {
                $output .= '<p>No participants found for this ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '.</p>';
            }
        $output .= '</div>';
    $output .= '</div>';
    $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<h2>Shared Users</h2>';
            $blog_id = get_current_blog_id();
            $shared_users = explode( ',', $ticket->shared_users );
            $users = get_users( 'blog_id=' . $blog_id . '&orderby=display_name' );
            $output .= '<form method="post" class="form-horizontal">';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12 col-md-3">';
                        if ( !empty( $users ) ) {
                            $output .= '<select id="wpsc_ticket_shared_users_' . $ticket->id . '" class="wpsc_ticket_shared_users wpsc_chosen form-control" multiple="multiple" >';
                                foreach ( $users as $user ) {
                                    $selected = ( in_array( $user->ID, $shared_users ) ) ? ' selected="selected"' : '';
                                    $output .= '<option value="' . $user->ID . '"' . $selected . '>' . $user->display_name . ' (' . $user->user_email . ')</option>';
                                }
                            $output .= '</select>';
                        } else {
                            $output .= '<p>No users found.</p>';
                        }
                    $output .= '</div>';
                    $output .= '<div class="col-xs-12 col-md-9"></div>';
                $output .= '</div>';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12 col-md-3">';
                        $output .= '<button type="button" class="wpsc_ticket_share_button btn btn-primary btn-sm" id="wpsc_ticket_share_' . $ticket->id . '" data-id="' . $ticket->id . '">Save</button>';
                    $output .= '</div>';
                    $output .= '<div class="col-xs-12 col-md-9"></div>';
                $output .= '</div>';
            $output .= '</form>';
        $output .= '</div>';
    $output .= '</div>';
$output .= '</div>';