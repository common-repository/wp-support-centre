<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
?>
<div class="wrap wpsc-bootstrap-styles">
    <h2>Utilities</h2>
    <div id="wpsc_admin_message" class="wpsc_hidden"></div>
    <?php
    include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_utilities/tabs.php' );
	?>
    <div class="tab-content">
    	<?php
    	include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_utilities/statistics.php' );
		include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_utilities/compatibility.php' );
		?>
    </div>
</div>