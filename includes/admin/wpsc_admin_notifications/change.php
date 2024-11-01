<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div id="wpsc_ticket_change" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . apply_filters( 'wpsc_change_client_notification', ' Change Client Notification' ); ?></h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <div class="checkbox">
                            <?php $checked = ( $wpsc_options['wpsc_notification_ticket_change_client_enable'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_notification_ticket_change_client_enable"><input type="checkbox" id="wpsc_notification_ticket_change_client_enable" <?php echo $checked; ?> /> Enable?</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <label for="wpsc_notification_ticket_change_client">Notification (<a href="#wpsc_notification_shortcodes" data-toggle="modal">Shortcodes</a>)</label>
                        <?php
                        $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_change_client'";
                        $result = $wpdb->get_var( $sql );
                        $result = html_entity_decode( stripcslashes( $result ) );
                        $wpsc_notification_ticket_change_client = str_replace( $find, $replace, $result );
                        ?>
                        <textarea class="wpsc_ckeditor" id="wpsc_notification_ticket_change_client" name="wpsc_notification_ticket_change_client"><?php echo html_entity_decode( stripcslashes( $wpsc_notification_ticket_change_client ) ); ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Ticket Change Admin Notification</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <div class="checkbox">
                            <?php $checked = ( $wpsc_options['wpsc_notification_ticket_change_admin_enable'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_notification_ticket_change_admin_enable"><input type="checkbox" id="wpsc_notification_ticket_change_admin_enable" <?php echo $checked; ?> /> Enable?</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <label for="wpsc_notification_ticket_change_admin">Notification (<a href="#wpsc_notification_shortcodes" data-toggle="modal">Shortcodes</a>)</label>
                        <?php
                        $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_change_admin'";
                        $result = $wpdb->get_var( $sql );
                        $result = html_entity_decode( stripcslashes( $result ) );
                        $wpsc_notification_ticket_change_admin = str_replace( $find, $replace, $result );
                        ?>
                        <textarea class="wpsc_ckeditor" id="wpsc_notification_ticket_change_admin" name="wpsc_notification_ticket_change_admin"><?php echo html_entity_decode( stripcslashes( $wpsc_notification_ticket_change_admin ) ); ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_save_notifications_ticket_change">Save Notifications</button> <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_reset_notifications_ticket_change">Reset to Default</button>
</div>