<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
$page_id = get_the_ID();
global $wpdb;
if ( $page_id == $wpsc_options['wpsc_support_page'] ) {
	// processing
	?>
	<div class="wpsc-bootstrap-styles">
    	<div id="wpsc_processing" class="modal fade" style="display:none;" data-backdrop="static">
    	    <div class="modal-dialog">
    	        <div class="modal-content">
    	            <div class="modal-header">
    	                <h4 class="modal-title">Please Wait...</h4>
    	            </div>
    	            <div class="modal-body">
    	                <p><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/ajax-loader@2x.gif" style="vertical-align: middle;" /> Processing... Please Wait...</p>
    	            </div>
    	        </div>
    	    </div>
    	</div>
	</div>
	<?php
	// ajax error
	?>
	<div class="wpsc-bootstrap-styles">
    	<div id="wpsc_ajax_error" class="modal fade" style="display:none;" data-backdrop="static">
    	    <div class="modal-dialog">
    	        <div class="modal-content">
    	            <div class="modal-header">
    	                <h4 class="modal-title">Warning: An Error Has Occured</h4>
    	            </div>
    	            <div class="modal-body">
    	                <p><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/smile-happy_32.png" style="vertical-align: middle;" /> Something has gone wrong. Check the console for details. Click Close to continue.</p>
    	            </div>
    	            <div class="modal-footer">
    	                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
    	            </div>
    	        </div>
    	    </div>
    	</div>
    </div>
	<?php
	// settings saved
	?>
	<div class="wpsc-bootstrap-styles">
    	<div id="wpsc_settings_saved" class="modal fade" style="display:none;" data-backdrop="static">
    	    <div class="modal-dialog">
    	        <div class="modal-content">
    	            <div class="modal-header">
    	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    	                <h4 class="modal-title">Settings Saved</h4>
    	            </div>
    	            <div class="modal-body">
    	                <p>The settings have been saved successfully.</p>
    	            </div>
    	            <div class="modal-footer">
    	                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
    	            </div>
    	        </div>
    	    </div>
    	</div>
	</div>
	<?php
    // front new ticket created
    ?>
    <div class="wpsc-bootstrap-styles">
        <div id="wpsc_front_new_ticket_created_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">New Ticket Created Successfully</h4>
                    </div>
                    <div class="modal-body" id="wpsc_front_new_ticket_created_body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}