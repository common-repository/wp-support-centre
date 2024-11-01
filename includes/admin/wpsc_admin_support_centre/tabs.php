<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<ul id="wpsc_admin_tabs" class="nav nav-tabs">
    <?php
    $tickets = ( isset( $_COOKIE['wpsc_open_tickets'] ) && $_COOKIE['wpsc_open_tickets'] != '' ) ? explode( ',', $_COOKIE['wpsc_open_tickets'] ) : array();
    sort( $tickets );
    $active_ticket = ( isset( $_COOKIE['wpsc_active_ticket'] ) && $_COOKIE['wpsc_active_ticket'] != '' ) ? $_COOKIE['wpsc_active_ticket'] : '';
    $active_ticket = ( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] == 'true' ) ? '' : $active_ticket;
    $active_ticket = ( isset( $_GET['uid'] ) && isset( $_GET['account_id'] ) ) ? 'new' : $active_ticket;
    ?>
    <li class="<?php echo $active_ticket == '' ? 'active' : ''; ?>"><a data-toggle="tab" href="#wpsc_admin_tickets"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s <i class="glyphicon glyphicon-refresh wpsc_tickets_refresh"></i></a></li>
    <li class="<?php echo isset( $_GET['uid'] ) && isset( $_GET['account_id'] ) ? 'active' : ''; ?>"><a data-toggle="tab" href="#wpsc_admin_new_ticket">Create New <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></a></li>
    <?php
    /*if ( isset( $wpsc_options['wpsc_enable_email_piping_catch_all'] ) && $wpsc_options['wpsc_enable_email_piping_catch_all'] == 1 ) {
        echo '<li><a data-toggle="tab" href="#wpsc_admin_piping_catch_all">Piping Catch All</a></li>';
    }*/
    if ( !isset( $_REQUEST['filter'] ) ) {
        foreach ( $tickets as $ticket_id ) {
            $isActive = ( $active_ticket == $ticket_id ) ? 'active ' : '';
            echo '<li id="wpsc_ticket_tab_' . $ticket_id . '" data-id="' . $ticket_id . '" class="' . $isActive . 'wpsc_admin_ticket_tab"><a data-toggle="tab" href="#wpsc_view_ticket_' . $ticket_id . '">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ': ' . $ticket_id . ' <i class="glyphicon glyphicon-remove-sign wpsc_tab_close"></i></a></li>';
        }
    }
    ?>
</ul>