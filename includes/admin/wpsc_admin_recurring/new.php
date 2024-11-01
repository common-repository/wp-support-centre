<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div id="wpsc_admin_new_recurring_ticket" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title"><span class="wpsc_required">* = required</span></h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
            	<div class="row row-eq-height">
					<div class="col-xs-12 col-sm-4">
		                <div class="form-group">
		                    <div class="col-xs-12">
		                        <label for="wpsc_admin_client_autocomplete_recurring"><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Name <span class="wpsc_required">*</span> <a href="#wpsc_ticket_client_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_client_dialog" class="wpsc_help"></a></label>
		                        <input type="text" id="wpsc_admin_client_autocomplete_recurring" class="wpsc_new_recurring_ticket form-control" value="" placeholder="<?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Name">
		                    </div>
		                </div>
		                <div class="form-group">
		                    <div class="col-xs-12">
		                        <label for="wpsc_new_recurring_ticket_client_email"><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Email <span class="wpsc_required">*</span></label>
		                        <input type="text" id="wpsc_new_recurring_ticket_client_email" class="form-control" value="" placeholder="<?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Email">
		                    </div>
		                </div>
		                <div class="form-group">
		                    <div class="col-xs-12">
		                        <label for="wpsc_new_recurring_ticket_agent_id">Agent <span class="wpsc_required">*</span></label>
		                        <select id="wpsc_new_recurring_ticket_agent_id" name="wpsc_new_recurring_ticket_agent_id" class="wpsc_new_recurring_ticket wpsc_new_recurring_ticket_validate form-control">
		                            <?php
		                            echo '<option value="">Please select...</option>';
		                            $args = array(
		                                'orderby' => 'display_name',
		                                'order' => 'ASC'
		                            );
		                            $all_users = get_users( $args );
		                            foreach ( $all_users as $user ) {
		                                if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
		                                    $selected = ( get_current_user_id() == $user->ID ) ? ' selected="selected"' : '';
		                                    echo '<option value="' . $user->ID . '"' . $selected . '>' . $user->display_name . '</option>';
		                                }
		                            }
		                            ?>
		                        </select>
		                    </div>
		                </div>
		                <div class="form-group">
		                    <div class="col-xs-12">
		                        <label for="wpsc_new_recurring_ticket_category">Category <span class="wpsc_required">*</span></label>
		                        <select id="wpsc_new_recurring_ticket_category" class="wpsc_new_recurring_ticket wpsc_new_recurring_ticket_validate form-control wpsc_category_select">
		                            <option value="">Please select...</option>
		                            <?php
		                            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories WHERE enabled=1";
		                            $result = $wpdb->get_results( $sql );
		                            foreach ( $result as $category ) {
		                                $selected = ( $category->is_default == 1 ) ? ' selected="selected"' : '';
		                                echo '<option value="' . $category->id . '"' . $selected . '>' . $category->category . '</option>';
		                            }
		                            ?>
		                        </select>
		                    </div>
		                </div>
		                <div class="form-group">
		                    <div class="col-xs-12">
		                        <label for="wpsc_new_recurring_ticket_priority">Priority <span class="wpsc_required">*</span></label>
		                        <select id="wpsc_new_recurring_ticket_priority" class="wpsc_new_recurring_ticket wpsc_new_recurring_ticket_validate form-control">
		                            <option value="">Please select...</option>
		                            <?php
		                            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority WHERE enabled=1";
		                            $result = $wpdb->get_results( $sql );
		                            foreach ( $result as $priority ) {
		                                $selected = ( $priority->is_default == 1 ) ? ' selected="selected"' : '';
		                                echo '<option value="' . $priority->id . '"' . $selected . '>' . $priority->priority . '</option>';
		                            }
		                            ?>
		                        </select>
		                    </div>
		                </div>
		                <div class="form-group largescreen-buttons">
		                	<div class="col-xs-12">
		                		<button type="button" class="wpsc_admin_button wpsc_new_recurring_ticket_button btn btn-primary btn-sm" id="wpsc_new_recurring_ticket_save">Create Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></button>
		                	</div>
		                </div>
					</div>
					<div class="col-xs-12 col-sm-8">
						<div class="form-group">
						    <div class="col-xs-12">
						        <label for="wpsc_admin_new_recurring_ticket_subject"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Subject <span class="wpsc_required">*</span></label>
						        <input type="text" id="wpsc_admin_new_recurring_ticket_subject" class="wpsc_new_recurring_ticket wpsc_new_recurring_ticket_validate form-control" value="" placeholder="<?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Subject">
						    </div>
						</div>
		                <div class="form-group">
		                    <div class="col-xs-12">
		                        <label for="wpsc_new_recurring_ticket_details"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Details <span class="wpsc_required">*</span></label>
		                        <textarea class="wpsc_ckeditor wpsc_admin_ticket_note form-control" id="wpsc_new_recurring_ticket_details" name="wpsc_new_recurring_ticket_details"></textarea>
		                    </div>
		                </div>
		                <div class="form-group">
		                    <div class="col-xs-12 col-md-4">
		                        <label for="wpsc_admin_new_recurring_ticket_attachments">Attachments</label>
		                        <input type="file" id="wpsc_admin_new_recurring_ticket_attachments"  class="wpsc_admin_new_recurring_ticket_attachments" multiple="multiple" >
		                    </div>
		                    <div class="col-xs-12 col-md-4">
		                        <label for="wpsc_new_recurring_ticket_schedule">Schedule <span class="wpsc_required">*</span></label>
		                        <select id="wpsc_new_recurring_ticket_schedule" class="wpsc_new_recurring_ticket wpsc_new_recurring_ticket_validate form-control">
		                            <option value="">Please select...</option>
		                            <option value="1">Daily</option>
		                            <option value="2">Weekly</option>
		                            <option value="3">Fortnightly</option>
		                            <option value="4">Monthly</option>
		                            <option value="5">Quarterly</option>
		                            <option value="6">Annually</option>
		                        </select>
		                    </div>
		                    <div class="col-xs-12 col-md-4">
		                        <label for="wpsc_recurring_ticket_date_from">Start Date <span class="wpsc_required">*</span></label>
		                        <input id="wpsc_recurring_ticket_date_from" name="wpsc_recurring_ticket_date_from" class="wpsc_new_recurring_ticket wpsc_recurring_ticket_date form-control" value="">
		                    </div>
		                </div>
		                <div class="form-group">
		                    <div class="col-xs-12 col-md-6">
		                        <div class="checkbox">
		                            <label for="wpsc_admin_new_recurring_ticket_enable"><input type="checkbox" id="wpsc_admin_new_recurring_ticket_enable" value="1" checked="checked"> Enable Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>?</label>
		                        </div>
		                    </div>
		                    <div class="col-xs-12 col-md-6">
		                        <div class="checkbox">
		                            <label for="wpsc_admin_new_recurring_ticket_notify"><input type="checkbox" id="wpsc_admin_new_recurring_ticket_notify" value="1" checked="checked"> Send Notifications?</label>
		                        </div>
		                    </div>
		                </div>
		                <div class="form-group smallscreen-buttons">
		                	<div class="col-xs-12">
		                		<button type="button" class="wpsc_admin_button wpsc_new_recurring_ticket_button btn btn-primary btn-sm" id="wpsc_new_recurring_ticket_save">Create Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></button>
		                	</div>
		                </div>
		        	</div>
	            </div>
	            <input type="hidden" id="wpsc_new_recurring_ticket_client_id" class="wpsc_new_recurring_ticket wpsc_new_recurring_ticket_validate" value="">
			</form>
        </div>
    </div>
</div>