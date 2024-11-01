<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$wpsc_rename = ( isset( $wpsc_options['wpsc_rename'] ) && !empty( $wpsc_options['wpsc_rename'] ) ) ? $wpsc_options['wpsc_rename'] : 'Support Centre';
?>
<div id="wpsc_settings_general" class="tab-pane fade active in">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title"><?php echo WPSC_TITLE; ?> Settings</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
            	<div class="form-group">
                    <div class="col-xs-12 col-md-6">
                        <label for="wpsc_item">Rename 'Support Centre' <a href="#wpsc_rename_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_rename_dialog" class="wpsc_help"></a></label>
                        <input type="input" id="wpsc_rename" name="wpsc_rename" value="<?php echo $wpsc_rename; ?>" class="form-control" />
                    </div>
                    <div class="col-xs-12 col-md-6"></div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-6">
                        <label for="wpsc_item">Item Name <a href="#wpsc_item_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_item_dialog" class="wpsc_help"></a></label>
                        <input type="input" id="wpsc_item" name="wpsc_item" value="<?php echo $wpsc_options['wpsc_item']; ?>" class="form-control" />
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <label for="wpsc_client">End User Name <a href="#wpsc_client_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_client_dialog" class="wpsc_help"></a></label>
                        <input type="input" id="wpsc_client" name="wpsc_client" value="<?php echo $wpsc_options['wpsc_client']; ?>" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-6">
                        <label for="wpsc_support_page">Support Page <a href="#wpsc_support_page_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_support_page_dialog" class="wpsc_help"></a></label>
                        <?php
                        $args = array(
                            'selected' => $wpsc_options['wpsc_support_page'],
                            'id' => 'wpsc_support_page',
                            'class' => 'form-control',
                            'show_option_none' => 'Please select...',
                            'option_none_value' => 0
                        );
                        wp_dropdown_pages( $args );
                        ?>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <label for="wpsc_support_page">Thank You Page <a href="#wpsc_thanks_page_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_thanks_page_dialog" class="wpsc_help"></a></label>
                        <?php
                        $args = array(
                            'selected' => $wpsc_options['wpsc_thanks_page'],
                            'id' => 'wpsc_thanks_page',
                            'class' => 'form-control',
                            'show_option_none' => 'Please select...',
                            'option_none_value' => 0
                        );
                        wp_dropdown_pages( $args );
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <div class="checkbox">
                            <?php $checked = ( !isset( $wpsc_options['wpsc_file_upload'] ) || $wpsc_options['wpsc_file_upload'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_file_upload"><input type="checkbox" id="wpsc_file_upload" <?php echo $checked; ?> /> Enable Front End File Upload?</label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-8"></div>
                </div>
                <button type="button" class="wpsc_wpsc_save_general btn btn-primary btn-sm">Save Settings</button>
            </form>
        </div>
    </div>
    <?php
	$output = '';
    $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-heading"><h4 class="panel-title">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' Settings</h4></div>';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<form method="post" class="form-horizontal">';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12 col-md-4">';
                        $output .= '<label for="wpsc_recurring_tickets_scheduled_time">Recurring ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's Create Time <a href="#wpsc_recurring_tickets_scheduled_time_dialog" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_recurring_tickets_scheduled_time_dialog" class="wpsc_help"></a></label>';
                        $output .= '<select id="wpsc_recurring_tickets_scheduled_time" name="wpsc_recurring_tickets_scheduled_time" class="form-control">';
                            for( $i = 0; $i < 24; $i++ ) {
                                $val = ( $i < 10 ) ? '0' . $i . ':00:00' : $i . ':00:00';
                                $dis = ( $i % 12 ? $i % 12 : 12 ) . ':00 ' . ( $i >= 12 ? 'pm' : 'am' );
                                $sel = $wpsc_options['wpsc_recurring_tickets_scheduled_time'] == $val ? ' selected="selected"': '';
                                $output .= '<option value="' . $val . '"' . $sel . '>' . $dis . '</option>';
                            }
                        $output .= '</select>';
                    $output .= '</div>';
                    $output .= '<div class="col-xs-12 col-md-8"></div>';
                $output .= '</div>';
                $output .= '<button type="button" class="wpsc_wpsc_save_general btn btn-primary btn-sm">Save Settings</button>';
            $output .= '</form>';
        $output .= '</div>';
    $output .= '</div>';
	echo $output;
    ?>
</div>