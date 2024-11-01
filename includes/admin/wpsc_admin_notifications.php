<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
$find = array(
    '[wpsc_plugin_url]'
);
$replace = array(
    WPSC_PLUGIN_URL
);

?>
<div class="wrap wpsc-bootstrap-styles">
    <h2>Notifications</h2>
    <div id="wpsc_admin_message" class="wpsc_hidden"></div>
    <?php
    include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_notifications/tabs.php' );
	?>
    <div class="tab-content">
    	<?php
    	include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_notifications/new.php' );
		include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_notifications/reply.php' );
		include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_notifications/change.php' );
		?>
    </div>
</div>