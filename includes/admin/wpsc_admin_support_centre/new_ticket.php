<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$ticket = ( isset( $ticket ) ) ? $ticket : '';
$ticket_id = ( isset( $ticket->id ) ) ? $ticket->id : '';

if ( !isset( $_REQUEST['filter'] ) ) {
    if ( isset( $_GET['uid'] ) && isset( $_GET['account_id'] ) ) {
        $uid = $_GET['uid'];
        $account_id = $_GET['account_id'];
        $transient = wpsc_imap_get_by_id( $uid, $account_id );
    }
    ?>
    <div id="wpsc_admin_new_ticket" class="tab-pane fade <?php echo isset( $_GET['uid'] ) && isset( $_GET['account_id'] ) ? 'active in' : ''; ?>">
        <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title"><span class="wpsc_required">* = required</span></h4></div>
            <div class="panel-body panel-body-wheat">
                <form method="post" class="form-horizontal">
                    <div class="form-group">
                    	<div class="col-xs-12 col-md-4">
                    		<div class="form-group">
                        		<div class="col-xs-12 col-md-12">
                                    <label for="wpsc_admin_client_autocomplete"><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Name <span class="wpsc_required">*</span> <a href="#wpsc_ticket_client_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_client_dialog" class="wpsc_help"></a></label>
                                    <input type="text" id="wpsc_admin_client_autocomplete" class="wpsc_new_ticket wpsc_admin_new_ticket_validate form-control" value="<?php echo isset( $transient['user']['author'] ) ? $transient['user']['author'] : ''; ?>" placeholder="<?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Name">
                                </div>
                            </div>
                            <div class="form-group">
                        		<div class="col-xs-12 col-md-12">
                                    <label for="wpsc_admin_client_email_autocomplete"><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Email <span class="wpsc_required">*</span> <a href="#wpsc_ticket_client_email_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_client_email_dialog" class="wpsc_help"></a></label>
                                    <input type="text" id="wpsc_admin_new_ticket_client_email" class="wpsc_new_ticket wpsc_admin_new_ticket_validate form-control" value="<?php echo isset( $transient['user']['author_email'] ) ? $transient['user']['author_email'] : ''; ?>" placeholder="<?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Email">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 col-md-12">
                                    <label for="wpsc_admin_new_ticket_phone"><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Phone</label>
                                    <input type="text" id="wpsc_admin_new_ticket_phone" class="wpsc_new_ticket form-control" value="<?php echo isset( $transient['user']['phone'] ) ? $transient['user']['phone'] : ''; ?>" placeholder="<?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> Phone">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 col-md-12">
                                	<label for="wpsc_new_ticket_category">Category <span class="wpsc_required">*</span></label>
                                    <select id="wpsc_new_ticket_category" class="wpsc_new_ticket wpsc_admin_new_ticket_validate form-control wpsc_category_select">
                                        <option value="">Please select...</option>
                                        <?php
                                        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories WHERE enabled=1 ORDER BY category ASC";
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
                                <div class="col-xs-12 col-md-12">
                                	<label for="wpsc_new_ticket_priority">Priority <span class="wpsc_required">*</span></label>
                                    <select id="wpsc_new_ticket_priority" class="wpsc_new_ticket wpsc_admin_new_ticket_validate form-control">
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
                            <div class="form-group">
                                <div class="col-xs-12 col-md-12">
                                	<label for="wpsc_admin_new_ticket_subject"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Subject <span class="wpsc_required">*</span></label>
                            		<input type="text" id="wpsc_admin_new_ticket_subject" class="wpsc_new_ticket wpsc_admin_new_ticket_validate form-control" value="<?php echo isset( $transient['subject'] ) ? $transient['subject'] : ''; ?>" placeholder="<?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Subject">
                            	</div>
                            </div>
                            <div class="form-group largescreen-buttons">
                                <div class="col-xs-12 col-md-12">
                                	<div class="btn-group">
										<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">Create <span class="caret"></span></button>
										<ul class="dropdown-menu">
											<li><a href="#" class="wpsc_admin_button wpsc_admin_new_ticket_button wpsc_admin_new_ticket" id="wpsc_admin_new_ticket_save">Create <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></a></li>
											<li><a href="#" class="wpsc_admin_button wpsc_admin_new_ticket_button wpsc_admin_new_ticket" id="wpsc_admin_new_ticket_save_notify">Create <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> & Notify</a></li>
										</ul>
									</div>
									<a href="#wpsc_ticket_new_ticket_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_new_ticket_dialog" class="wpsc_help"></a>
                            	</div>
                            </div>
                    	</div>
                    	<div class="col-xs-12 col-md-8">
                    		<?php echo apply_filters( 'wpsc_additional_fields', '', $ticket, 'category' ); ?>
                    		<div class="form-group">
                                <div class="col-xs-12 col-md-12">
                                    <label for="wpsc_admin_new_ticket_details"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Details <span class="wpsc_required">*</span></label>
                                    <textarea class="wpsc_ckeditor wpsc_admin_ticket_note form-control" id="wpsc_admin_new_ticket_details" name="wpsc_admin_new_ticket_details"><?php echo isset( $transient['message'] ) ? base64_decode( $transient['message'] ) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 col-md-12">
                                	<label for="wpsc_admin_new_ticket_attachments">Attachments</label>
                            		<input type="file" id="wpsc_admin_new_ticket_attachments"  class="wpsc_admin_new_ticket_attachments" multiple="multiple" >
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 col-md-12">
                                	<div class="form-group">
		                                <div class="col-xs-12 col-md-4">
		                                    <div class="form-group">
		                                    	<?php
		                                    	$output = '';
		                    					$cc = '';
				                        		$cc .= '<div class="col-xs-12">';
						                            $cc .= '<label for="wpsc_admin_new_ticket_cc">CC:</label>';
													$cc .= wpsc_address_book( 'wpsc_admin_new_ticket_cc_select', 'wpsc_new_ticket wpsc_admin_new_ticket_email_select', $ticket_id );
												$cc .= '</div>';
												$cc .= '<div class="col-xs-12">';
													$cc .= '<input type="text" id="wpsc_admin_new_ticket_cc" value="" class="wpsc_new_ticket form-control" placeholder="CC">';
												$cc .= '</div>';
												$output .= apply_filters( 'wpsc_admin_ticket_cc', $cc, $ticket_id );
												echo $output;
												?>
											</div>
		                                </div>
		                                <div class="col-xs-12 col-md-4">
		                                    <div class="form-group">
		                                    	<?php
		                                    	$output = '';
		                    					$bcc = '';
				                        		$bcc .= '<div class="col-xs-12">';
						                            $bcc .= '<label for="wpsc_admin_new_ticket_bcc">BCC:</label>';
													$bcc .= wpsc_address_book( 'wpsc_admin_new_ticket_bcc_select', 'wpsc_new_ticket wpsc_admin_new_ticket_email_select', $ticket_id );
												$bcc .= '</div>';
												$bcc .= '<div class="col-xs-12">';
													$bcc .= '<input type="text" id="wpsc_admin_new_ticket_bcc" value="" class="wpsc_new_ticket form-control" placeholder="BCC">';
												$bcc .= '</div>';
												$output .= apply_filters( 'wpsc_admin_ticket_bcc', $bcc, $ticket_id );
												echo $output;
												?>
											</div>
		                                </div>
		                                <div class="col-xs-12 col-md-4"></div>
                                	</div>
                                </div>
                            </div>
                            <div class="form-group smallscreen-buttons">
                                <div class="col-xs-12 col-md-12">
                                	<button type="button" class="btn btn-group-xs btn-primary btn-sm">
										<span class="btn dropdown-toggle" data-toggle="dropdown">Create <span class="caret"></span></span>
										<ul class="dropdown-menu">
											<li><a href="#" class="wpsc_admin_button wpsc_admin_new_ticket_button wpsc_admin_new_ticket" id="wpsc_admin_new_ticket_save">Create <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></a></li>
											<li><a href="#" class="wpsc_admin_button wpsc_admin_new_ticket_button wpsc_admin_new_ticket" id="wpsc_admin_new_ticket_save_notify">Create <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> & Notify</a></li>
										</ul>
									</button>
									<a href="#wpsc_ticket_new_ticket_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_new_ticket_dialog" class="wpsc_help"></a>
                            	</div>
                            </div>
                    	</div>
                    </div>
                    <input type="hidden" id="wpsc_admin_new_ticket_client_id" class="wpsc_new_ticket wpsc_admin_new_ticket_validate" value="<?php echo isset( $transient['user']['user_id'] ) ? $transient['user']['user_id'] : ''; ?>">
                    <input type="hidden" id="wpsc_admin_new_ticket_client" class="wpsc_new_ticket wpsc_admin_new_ticket_validate" value="<?php echo isset( $transient['user']['author'] ) ? $transient['user']['author'] : ''; ?>">
                    <input type="hidden" id="wpsc_admin_new_ticket_to" class="wpsc_new_ticket wpsc_admin_new_ticket_validate" value="<?php echo isset( $transient['user']['author_email'] ) ? $transient['user']['author_email'] : ''; ?>">
                    <input type="hidden" id="wpsc_new_ticket_agent" class="wpsc_new_ticket" value="<?php echo get_current_user_id(); ?>">
                    <input type="hidden" id="wpsc_admin_new_ticket_existing_attachments" class="wpsc_new_ticket" value="<?php echo isset( $transient['attach'] ) ? $transient['attach'] : ''; ?>">
                    <input type="hidden" id="wpsc_admin_new_ticket_timestamp" class="wpsc_new_ticket" value="<?php echo isset( $transient['timestamp'] ) ? $transient['timestamp'] : ''; ?>">
                </form>
            </div>
        </div>
    </div>
    <?php
    $tickets = ( isset( $_COOKIE['wpsc_open_tickets'] ) && $_COOKIE['wpsc_open_tickets'] != '' ) ? explode( ',', $_COOKIE['wpsc_open_tickets'] ) : array();
    sort( $tickets );
    foreach ( $tickets as $ticket_id ) {
        $isActive = ( $active_ticket == $ticket_id ) ? ' active in' : '';
        ?>
        <div id="wpsc_view_ticket_<?php echo $ticket_id; ?>" class="tab-pane fade<?php echo $isActive; ?>">
            <?php echo wpsc_get_the_admin_ticket( $ticket_id ); ?>
        </div>
        <?php
    }
}