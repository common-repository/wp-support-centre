<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output .= '<ul id="wpsc_ticket_tabs_' .  $ticket_id . '" class="nav nav-tabs">';
    $output .= '<li class="active"><a data-toggle="tab" href="#wpsc_ticket_' . $ticket_id . '">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '</a></li>';
	$output .= '<li><a data-toggle="tab" href="#wpsc_reminders_' . $ticket_id . '">Reminders</a></li>';
    $output .= '<li><a data-toggle="tab" href="#wpsc_participants_' . $ticket_id . '">Participants</a></li>';
    $output .= '<li><a data-toggle="tab" href="#wpsc_attachments_' . $ticket_id . '">Attachments</a></li>';
    $output .= '<li><a data-toggle="tab" href="#wpsc_account_' . $ticket_id . '">Account Information</a></li>';
$output .= '</ul>';