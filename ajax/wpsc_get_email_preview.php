<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get Email Preview
 *
 *
 */
function wpsc_get_email_preview() {
    global $wpdb;
    $return = array();
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
	$uid = $_POST['email_id'];
	$account_id = $_POST['account'];
	$transient = wpsc_imap_get_by_id( $uid, $account_id );
    $thread_date = $transient['timestamp'];
    $email = '';
    $email .= '<div class="wpsc-bootstrap-styles">';
        $email .= '<div id="wpsc_email_preview_modal" class="modal fade" data-backdrop="static">';
            $email .= '<div class="modal-dialog piping-modal">';
                $email .= '<div class="modal-content">';
                    $email .= '<div class="modal-header">';
                        $email .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                        $email .= '<h4 class="modal-title">' . $transient['subject'] . '</h4>';
                    $email .= '</div>';
                    $email .= '<div class="modal-body piping-modal-body">';
                        $email .= '<div class="panel panel-default">';
                            $email .= '<table class="wpsc_fullwidth wpsc_thread_header">';
                                $email .= '<tr>';
                                    $email .= '<td class="wpsc_width106px">';
                                        $email .= get_avatar( $transient['user']['user_id'] );
                                    $email .= '</td>';
                                    $email .= '<td>';
                                        $email .= '<span class="wpsc_thread_header_author">' . $transient['user']['author'] . '</span><br />';
                                        $email .= '<em>' . $transient['user']['author_email'] . '</em><br />';
                                        $email .= '<em>' . $thread_date . '</em>';
                                    $email .= '</td>';
                                $email .= '</tr>';
                            $email .= '</table>';
                            $email .= '<iframe id="wpsc_email_preview_body" src="' . WPSC_PLUGIN_URL . 'includes/wpsc_email_body.php?uid=' . $transient['transient_id'] . '&rand=' . current_time( 'timestamp' ) . '"></iframe>';
                            $email .= '<hr />';
                            if ( !empty( $transient['attach_ids'] ) ) {
                                $email .= '<h3>Attachments</h3>';
                                foreach ( $transient['attach_ids'] as $attachment ) {
                                    $filename = basename( get_attached_file( $attachment ) );
                                    if ( $filename != '' ) {
                                        $url = wp_get_attachment_url( $attachment );
                                        $email .= '<em><a href="' . $url . '" target="_blank">' . $filename . '</a></em> ';
                                    } else {
                                    	$error = '<em>Error ' . $attachment  . ': File Not Found</em> ';
                                        $email .= apply_filters( 'wpsc_file_not_found', $error, $attachment );
                                    }
                                }
                            }
                        $email .= '</div>';
                    $email .= '</div>';
                    $email .= '<div class="modal-footer">';
                        $email .= '<button type="button" class="btn btn-primary btn-sm" id="wpsc_piping_new_ticket" data-id="' . $transient['email_id'] . '" data-uid="' . $uid . '" data-accountid="' . $account_id . '">' . apply_filters( 'wpsc_piping_new_ticket_button', 'Create New ' ) . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '</button> ';
                        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_tickets WHERE status_id!=2 AND status_id!=3 ORDER BY id DESC";
                        $tickets = $wpdb->get_results( $sql );
                        if ( !empty( $tickets ) && !is_null( $tickets ) ) {
                            $email .= '<button type="button" class="btn btn-primary btn-sm" id="wpsc_piping_new_thread" data-uid="' . $uid . '" data-accountid="' . $account_id . '">' . apply_filters( 'wpsc_piping_new_thread_button', 'Add To ' ) . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '</button> ';
                            $email .= '<select id="wpsc_open_tickets" style="display:none" data-id="' . $return['email_id'] . '">';
                                foreach ( $tickets as $ticket ) {
                                    $email .= '<option value="' . $ticket->id . '">[' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ': ' . $ticket->id . '] ' . stripcslashes( $ticket->subject ) . '</option>';
                                }
                            $email .= '</select> ';
                        }
                        $email .= '<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">' . apply_filters( 'wpsc_close_text', 'Close' ) . '</button>';
                    $email .= '</div>';
                $email .= '</div>';
            $email .= '</div>';
        $email .= '</div>';
    $email .= '</div>';
    $return['status'] = 'true';
    $return['modal'] = utf8_encode( $email );
    //$return['transient'] = $transient;
    echo json_encode( $return );
    wp_die();
}