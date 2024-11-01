<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output .= wpsc_get_user_information( $ticket->client_id, $ticket->client, true, $ticket_id );