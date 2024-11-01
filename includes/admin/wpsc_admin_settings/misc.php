<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$wpsc_rename = ( isset( $wpsc_options['wpsc_rename'] ) && !empty( $wpsc_options['wpsc_rename'] ) ) ? $wpsc_options['wpsc_rename'] : 'Support Centre';
?>
<div id="wpsc_settings_misc" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title"><?php echo WPSC_TITLE; ?> Help</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        If you find that tabs and / or bottons are not functioning as they should, it may be that your theme or another installed plugin is also loading Bootstrap, a component that is used by WP Support Centre.  Deselect the options below to prevent WP Support Centre from loading Bootstrap.
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <div class="checkbox">
                            <?php $checked = ( !isset( $wpsc_options['wpsc_load_bootstrap_js_f'] ) || $wpsc_options['wpsc_load_bootstrap_js_f'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_load_bootstrap_js_f"><input type="checkbox" id="wpsc_load_bootstrap_js_f" <?php echo $checked; ?> /> Enable Bootstrap JavaScript (Front End)?</label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <div class="checkbox">
                            <?php $checked = ( !isset( $wpsc_options['wpsc_load_bootstrap_css_f'] ) || $wpsc_options['wpsc_load_bootstrap_css_f'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_load_bootstrap_css_f"><input type="checkbox" id="wpsc_load_bootstrap_css_f" <?php echo $checked; ?> /> Enable Bootstrap Styles (Front End)?</label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-8"></div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <div class="checkbox">
                            <?php $checked = ( !isset( $wpsc_options['wpsc_load_bootstrap_js_a'] ) || $wpsc_options['wpsc_load_bootstrap_js_a'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_load_bootstrap_js_a"><input type="checkbox" id="wpsc_load_bootstrap_js_a" <?php echo $checked; ?> /> Enable Bootstrap JavaScript (Admin)?</label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <div class="checkbox">
                            <?php $checked = ( !isset( $wpsc_options['wpsc_load_bootstrap_css_a'] ) || $wpsc_options['wpsc_load_bootstrap_css_a'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_load_bootstrap_css_a"><input type="checkbox" id="wpsc_load_bootstrap_css_a" <?php echo $checked; ?> /> Enable Bootstrap Styles (Admin)?</label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-8"></div>
                </div>
                <button type="button" class="wpsc_wpsc_save_misc btn btn-primary btn-sm">Save Settings</button>
            </form>
        </div>
    </div>
</div>