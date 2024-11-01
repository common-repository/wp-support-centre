<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<ul id="wpsc_admin_recurring_tabs" class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#wpsc_admin_recurring_tickets">Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s</a></li>
    <li><a data-toggle="tab" href="#wpsc_admin_new_recurring_ticket">Create New Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></a></li>
</ul>