<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( !wp_next_scheduled( 'wpsc_recurring_tickets' ) ) {
    wp_schedule_event( time(), 'hourly', 'wpsc_recurring_tickets' );
}
if ( !wp_next_scheduled( 'wpsc_assign_threads' ) ) {
    wp_schedule_event( time(), 'hourly', 'wpsc_assign_threads' );
}