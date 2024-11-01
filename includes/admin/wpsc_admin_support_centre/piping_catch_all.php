<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output = '';
if ( isset( $wpsc_options['wpsc_enable_email_piping_catch_all'] ) && $wpsc_options['wpsc_enable_email_piping_catch_all'] == 1 ) {
    $output .= '<div id="wpsc_admin_piping_catch_all" class="tab-pane fade">';
        $output .= '<div class="panel panel-default">';
			$output .= '<div class="panel-heading"><h4 class="panel-title">Combined Inbox</h4></div>';
            $output .= '<div class="panel-body panel-body-wheat">';
                $output .= '<form method="post" class="form-horizontal">';
                    $output .= '<div class="form-group">';
                        $output .= '<div class="col-xs-12">';
                            $output .= '<table id="wpsc_admin_email_piping_preview" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth display">';
                                $output .= '<thead>';
                                    $output .= '<th>From</th>';
                                    $output .= '<th>Subject</th>';
                                    $output .= '<th>Received</th>';
                                    $output .= '<th></th>';
                                $output .= '</thead>';
                                $output .= '<tfoot>';
                                    $output .= '<th>From</th>';
                                    $output .= '<th>Subject</th>';
                                    $output .= '<th>Received</th>';
                                    $output .= '<th></th>';
                                $output .= '</tfoot>';
                                $output .= '<tbody>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_piping_preview ORDER BY thread_timestamp DESC";
                                    $email_array = $wpdb->get_results( $sql, OBJECT );
                                    foreach( $email_array as $email ) {
                                        $output .= '<tr class="email_row" data-id="' . $email->id . '">';
                                            $output .= '<td style="width:35%">' . $email->author . ' (' . $email->author_email . ')</td>';
                                            $output .= '<td style="width:50%">' . utf8_decode( imap_utf8( $email->subject ) ) . '</td>';
                                            $output .= '<td style="width:10%">' . get_date_from_gmt( $email->thread_timestamp ) . '</td>';
                                            $output .= '<td style="width:5%" class="align_centre">';
                                                if ( $email->attachments != '' ) {
                                                    $output .= '<span class="glyphicon glyphicon-paperclip"></span>';
                                                }
                                            $output .= '</td>';
                                        $output .= '</tr>';
                                    }
                                $output .= '</tbody>';
                            $output .= '</table>';
                        $output .= '</div>';
                    $output .= '</div>';
                $output .= '</form>';
            $output .= '</div>';
        $output .= '</div>';
    $output .= '</div>';
    echo $output;
}