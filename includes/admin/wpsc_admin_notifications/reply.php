<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div id="wpsc_ticket_reply" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Ticket Reply Notifications</h4></div>
        <div class="panel-body panel-body-wheat">
            <p>You can elect to set reply threads to include previous threads in the reply notification if desired.  The options are:</p>
            <ul>
                <li><strong>None:</strong> Do not include any previous threads</li>
                <li><strong>Latest:</strong> Include the most recent thread in the email notification</li>
                <li><strong>All:</strong> Include all threads in the email notification</li>
            </ul>
            <p>You can also select which threads to include when creating the thread reply.</p>
            <div class="form-group">
                <div class="col-xs-12 col-md-4">
                    <div class="radio">
                        <?php $selected = ( $wpsc_options['wpsc_reply_include'] == 0 ) ? ' checked="checked"' : ''; ?>
                        <label for="wpsc_reply_include_none"><input type="radio" name="wpsc_reply_include" id="wpsc_reply_include_none" class="wpsc_reply_include" value="0"<?php echo $selected; ?> /> None</label>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="radio">
                        <?php $selected = ( $wpsc_options['wpsc_reply_include'] == 1 ) ? ' checked="checked"' : ''; ?>
                        <label for="wpsc_reply_include_latest"><input type="radio" name="wpsc_reply_include" id="wpsc_reply_include_latest" class="wpsc_reply_include" value="1"<?php echo $selected; ?> /> Latest</label>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="radio">
                        <?php $selected = ( $wpsc_options['wpsc_reply_include'] == 2 ) ? ' checked="checked"' : ''; ?>
                        <label for="wpsc_reply_include_all"><input type="radio" name="wpsc_reply_include" id="wpsc_reply_include_all" class="wpsc_reply_include" value="2"<?php echo $selected; ?> /> All</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Reply Client Notification</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <div class="checkbox">
                            <?php $checked = ( $wpsc_options['wpsc_notification_ticket_reply_client_enable'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_notification_ticket_reply_client_enable"><input type="checkbox" id="wpsc_notification_ticket_reply_client_enable" <?php echo $checked; ?> /> Enable?</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <label for="wpsc_notification_ticket_reply_client">Notification (<a href="#wpsc_notification_shortcodes" data-toggle="modal">Shortcodes</a>)</label>
                        <?php
                        $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_client'";
                        $result = $wpdb->get_var( $sql );
                        $result = html_entity_decode( stripcslashes( $result ) );
                        $wpsc_notification_ticket_reply_client = str_replace( $find, $replace, $result );
                        ?>
                        <textarea class="wpsc_ckeditor" id="wpsc_notification_ticket_reply_client" name="wpsc_notification_ticket_reply_client"><?php echo html_entity_decode( stripcslashes( $wpsc_notification_ticket_reply_client ) ); ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Reply Admin Notification</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <div class="checkbox">
                            <?php $checked = ( $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_notification_ticket_reply_admin_enable"><input type="checkbox" id="wpsc_notification_ticket_reply_admin_enable" <?php echo $checked; ?> /> Enable?</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <label for="wpsc_notification_ticket_reply_admin">Notification (<a href="#wpsc_notification_shortcodes" data-toggle="modal">Shortcodes</a>)</label>
                        <?php
                        $sql = "SELECT notification FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_admin'";
                        $result = $wpdb->get_var( $sql );
                        $result = html_entity_decode( stripcslashes( $result ) );
                        $wpsc_notification_ticket_reply_admin = str_replace( $find, $replace, $result );
                        ?>
                        <textarea class="wpsc_ckeditor" id="wpsc_notification_ticket_reply_admin" name="wpsc_notification_ticket_reply_admin"><?php echo html_entity_decode( stripcslashes( $wpsc_notification_ticket_reply_admin ) ); ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_save_notifications_ticket_reply">Save Notifications</button> <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_reset_notifications_ticket_reply">Reset to Default</button>
</div>