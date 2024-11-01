<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
?>
<div class="wrap wpsc-bootstrap-styles">
    <h2><?php echo WPSC_TITLE; ?> - Settings</h2>
    <div id="wpsc_admin_message" class="wpsc_hidden"></div>
    <?php
    include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_settings/tabs.php' );
    ?>
    <div class="tab-content">
        <?php
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_settings/general.php' );
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_settings/agent_client.php' );
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_settings/email.php' );
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_settings/status.php' );
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_settings/category.php' );
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_settings/priority.php' );
        ?>
    </div>
</div>