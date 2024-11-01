<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output .= '<div id="wpsc_reminders_' . $ticket_id . '" class="tab-pane fade">';
    $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<h2>Reminders</h2>';
            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_reminders WHERE ticket_id=" . $ticket_id;
            $results = $wpdb->get_results( $sql );
            if ( !empty( $results ) ) {
                $output .= '<table id="wpsc_admin_reminders_table_' . $ticket_id . '" class="wpsc_admin_reminders_datatable table table-striped table-bordered wpsc_fullwidth">';
                    $output .= '<thead>';
                        $output .= '<tr>';
                            $output .= '<th>Subject</th>';
                            $output .= '<th>Type</th>';
                            $output .= '<th>Due</th>';
							$output .= '<th>Actions</th>';
                        $output .= '</tr>';
                    $output .= '</thead>';
                    $output .= '<tfoot>';
                        $output .= '<tr>';
                            $output .= '<th>Subject</th>';
                            $output .= '<th></th>';
                            $output .= '<th>Due</th>';
							$output .= '<th></th>';
                        $output .= '</tr>';
                    $output .= '</tfoot>';
                    $output .= '<tbody>';
                        foreach( $results as $reminder ) {
                        	$type = ( $reminder->type == 0 ) ? 'Auto' : 'Custom';
                            $output .= '<tr>';
                                $output .= '<td>' . apply_filters( 'wpsc_reminder_subject', $reminder->subject, $reminder->type ) . '</td>';
                                $output .= '<td class="align_centre">' . $type . '</td>';
                                $output .= '<td class="align_centre">' . get_date_from_gmt( $reminder->due_timestamp ) . '</td>';
								$output .= '<td class="align_centre"></td>';
                            $output .= '</tr>';
                        }
                    $output .= '</tbody>';
                $output .= '</table>';
            } else {
                $output .= '<p>No reminders found for this ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '.</p>';
            }
        $output .= '</div>';
    $output .= '</div>';
$output .= '</div>';