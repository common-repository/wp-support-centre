<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_tickets_table.php' );
include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket.php' );

global $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
$tickets = ( isset( $_COOKIE['wpsc_open_tickets'] ) && $_COOKIE['wpsc_open_tickets'] != '' ) ? explode( ',', $_COOKIE['wpsc_open_tickets'] ) : array();
/*if ( isset( $_REQUEST['ticket_id'] ) && $_REQUEST['ticket_id'] != '' ) {
    if ( !in_array( $_REQUEST['ticket_id'], $tickets ) ) {
        $tickets[] = $_REQUEST['ticket_id'];
        setcookie( 'wpsc_open_tickets', implode(',', $tickets ), time() + ( 30 * 86400 ) );
        $_COOKIE['wpsc_open_tickets'] = implode(',', $tickets );
        setcookie( 'wpsc_active_ticket', $_REQUEST['ticket_id'], time() + ( 30 * 86400 ) );
        $_COOKIE['wpsc_active_ticket'] = implode(',', $tickets );
    }
}*/
$editor = array();
if ( isset( $_POST['wpsc_do_admin_apply_filter'] ) || isset( $_POST['wpsc_do_admin_clear_filter'] ) ) {
    foreach( $_POST as $k => $v ) {
        if ( strpos( $k, 'wpsc_ticket_filter' ) == 0 ) {
            $wpsc_options[get_current_user_id()][$k] = $v;
        }
    }
    update_option( 'wpsc_options', $wpsc_options );
}
?>
<div class="wrap wpsc-bootstrap-styles">
    <h2><?php echo WPSC_TITLE; ?></h2>
    <div id="wpsc_admin_message" class="wpsc_hidden"></div>
    <?php
    include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/tabs.php' );
	?>
    <div class="tab-content" id="admin-tab-content">
        <div id="wpsc_admin_tickets" class="tab-pane fade <?php echo $active_ticket == '' ? 'active in' : ''; ?>">
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s</h4></div>
                <div class="panel-body panel-body-wheat" id="wpsc_admin_tickets_table_container">
                    <?php
                    echo apply_filters( 'do_wpsc_admin_get_tickets_table', '', $wpsc_options );
                    ?>
                </div>
            </div>
            <?php
            if ( !isset( $_REQUEST['filter'] ) ) {
            	?>
                <div id="wpsc_ticket_filters" class="panel-group">
                	<?php
                	include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/quick_find.php' );
					include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/filter.php' );
					include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/search.php' );
					?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/piping_catch_all.php' );
		include( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/new_ticket.php' );
        ?>
    </div>
</div>