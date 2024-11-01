<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output .= '<div id="wpsc_attachments_' . $ticket_id . '" class="tab-pane fade">';
    $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<h2>Attachments</h2>';
            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE ticket_id=" . $ticket_id . " AND attachments!=''";
            $results = $wpdb->get_results( $sql );
            if ( !empty( $results ) ) {
                $attachments = array();
                foreach ( $results as $attachment ) {
                    $split = explode( ',', $attachment->attachments );
                    foreach ( $split as $id ) {
                        if ( !in_array( $id, $attachments ) ) {
                            $attachments[] = $id;
                        }
                    }
                }
                $output .= '<table id="wpsc_admin_attachments_table_' . $ticket_id . '" class="wpsc_admin_attachment_datatable table table-striped table-bordered wpsc_fullwidth">';
                    $output .= '<thead>';
                        $output .= '<tr>';
                            $output .= '<th></th>';
                            $output .= '<th>Attachment</th>';
                            $output .= '<th></th>';
                        $output .= '</tr>';
                    $output .= '</thead>';
                    $output .= '<tfoot>';
                        $output .= '<tr>';
                            $output .= '<th></th>';
                            $output .= '<th>Attachment</th>';
                            $output .= '<th></th>';
                        $output .= '</tr>';
                    $output .= '</tfoot>';
                    $output .= '<tbody>';
                        foreach ( $attachments as $attachment ) {
                            $output .= '<tr>';
                                $output .= '<td class="align_centre valign_middle"><a href="' . wp_get_attachment_url( $attachment ) . '" target="_blank">' . wp_get_attachment_image( $attachment, 'wpsc_thumbnail', 1 ) . '</a></td>';
                                $output .= '<td class="valign_middle"><a href="' . wp_get_attachment_url( $attachment ) . '" target="_blank">' . basename( get_attached_file( $attachment ) ) . '</a></td>';
                                $output .= '<td class="align_centre valign_middle"><span class="wpsc_delete_attachment" id="delete_attachment_' . $attachment . '" data-id="' . $attachment . '" data-ticket-id="' . $ticket_id . '"> <img src="' . WPSC_PLUGIN_URL . '/assets/images/32/trash_32.png" title="Delete" class="wpsc_help" /> Delete</span></td>';
                            $output .= '</tr>';
                        }
                    $output .= '</tbody>';
                $output .= '</table>';
            } else {
                $output .= '<p>No attachments found for this ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '.</p>';
            }
        $output .= '</div>';
    $output .= '</div>';
$output .= '</div>';