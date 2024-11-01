<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div id="wpsc_settings_email" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Email Settings</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <?php
                        $current_site = get_site_url();
                        ?>
                        <p>The following settings will be used globally if 'Use Agent Email Settings' is not selected or if an agent's email is not from domain: <?php echo $current_site; ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_email_from_name">From Name <a href="#wpsc_email_from_name_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_email_from_name_dialog" class="wpsc_help"></a></label>
                        <input type="input" id="wpsc_email_from_name" name="wpsc_email_from_name" value="<?php echo $wpsc_options['wpsc_email_from_name']; ?>" class="form-control" />
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_email_from_email">From Email <a href="#wpsc_email_from_email_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_email_from_email" class="wpsc_help"></a></label>
                        <input type="input" id="wpsc_email_from_email" name="wpsc_email_from_email" value="<?php echo $wpsc_options['wpsc_email_from_email']; ?>" class="form-control" />
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_email_reply_to">Reply To Email <a href="#wpsc_email_reply_to_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_email_reply_to" class="wpsc_help"></a></label>
                        <input type="input" id="wpsc_email_reply_to" name="wpsc_email_reply_to" value="<?php echo $wpsc_options['wpsc_email_reply_to']; ?>" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <?php
                        $current_site = get_site_url();
                        ?>
                        <p>When 'Use Agent Email Settings' is selected, if an agent's email is from domain: <?php echo $current_site; ?> then the agent's Name and Email will be used to send notifications.</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <div class="checkbox">
                            <?php $checked = ( $wpsc_options['wpsc_use_agent_email'] == 1 ) ? ' checked="checked"' : ''; ?>
                            <label for="wpsc_use_agent_email"><input type="checkbox" id="wpsc_use_agent_email" <?php echo $checked; ?> /> Use Agent Email Settings?</label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-8"></div>
                </div>
                <button type="button" class="wpsc_wpsc_save_email btn btn-primary btn-sm">Save Settings</button>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Signature</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <label for="wpsc_email_from_name">Signature (<a href="#wpsc_signature_shortcodes" data-toggle="modal">Shortcodes</a>)</label>
                        <?php $signature = $wpdb->get_var( 'SELECT signature FROM ' . $wpdb->prefix . 'wpsc_settings WHERE id=1' ); ?>
                        <textarea class="wpsc_ckeditor wpsc_admin_signature form-control" id="wpsc_admin_signature" name="wpsc_admin_signature"><?php echo html_entity_decode( stripcslashes( $signature ) ); ?></textarea>
                    </div>
                </div>
                <button type="button" class="wpsc_wpsc_save_email btn btn-primary btn-sm">Save Settings</button>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Inbound Email Handling</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
            	<div class="form-group">
                    <div class="col-xs-12">
                    	<p>WP Support Centre can manage inbound emails using either <strong>Email Piping</strong> or direct <strong>IMAP</strong> access.</p>
                    	<ul>
                    		<li><strong>Email Piping:</strong> Email Piping is the method of sending email messages to a program, allowing that program to process the message. It is recommended that the email address being used be dedicated to email piping only. You will also need to enable Email Piping within your Email Account / Hosting Server settings. <a href="https://wordpress.org/support/topic/where-to-find-email-piping-settings" target="_blank">Click here</a> for more information.</li>
                    		<li><strong>IMAP:</strong> Using IMAP, WP Support Centre will access your mailbox directly and process emails from the inbox. Only a single email account can be configured. You will need to know your email server and account login settings.</li>
                    		<li><strong>Disabled:</strong> If you do not select <strong>Email Piping</strong> or <strong>IMAP</strong> then any inbound emails that relate to a ticket must be manually processed.</li>
                    	</ul>
					</div>
				</div>
				<div class="form-group">
                    <div class="col-xs-12">
                    	<p>Please select the inbound email handling method</p>
                        <label for="wpsc_email_method_piping" class="radio-inline">
                        	<?php $checked = ( $wpsc_options['wpsc_email_method'] == 1 ) ? ' checked="checked"' : ''; ?>
							<input type="radio" name="wpsc_email_method" id="wpsc_email_method_piping"<?php echo $checked; ?> value="1"> Email Piping
						</label>
						<label for="wpsc_email_method_imap" class="radio-inline">
							<?php $checked = ( $wpsc_options['wpsc_email_method'] == 2 ) ? ' checked="checked"' : ''; ?>
							<input type="radio" name="wpsc_email_method" id="wpsc_email_method_imap"<?php echo $checked; ?> value="2"> IMAP
						</label>
						<label for="wpsc_email_method_piping" class="radio-inline">
							<?php $checked = ( $wpsc_options['wpsc_email_method'] != 1 && $wpsc_options['wpsc_email_method'] != 2 ) ? ' checked="checked"' : ''; ?>
							<input type="radio" name="wpsc_email_method" id="wpsc_email_method_none"<?php echo $checked; ?> value="0"> Disabled
						</label>
						<hr />
                    </div>
                </div>
                <?php $class = ( $wpsc_options['wpsc_email_method'] == 1 ) ? '' : ' wpsc_hidden'; ?>
                <div class="panel panel-default<?php echo $class; ?>" id="wpsc_email_method_piping_settings">
                	<div class="panel-heading"><h4 class="panel-title">Email Piping Settings</h4></div>
        			<div class="panel-body panel-body-wheat">
		                <div class="form-group">
		                    <div class="col-xs-12 col-md-3">
		                        <div class="checkbox">
		                            <?php $checked = ( $wpsc_options['wpsc_enable_email_piping'] == 1 ) ? ' checked="checked"' : ''; ?>
		                            <label for="wpsc_enable_email_piping"><input type="checkbox" id="wpsc_enable_email_piping" <?php echo $checked; ?> /> Enable Email Piping? <a href="#wpsc_enable_email_piping_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_enable_email_piping_dialog" class="wpsc_help"></a></label>
		                        </div>
		                    </div>
		                    <div class="col-xs-12 col-md-9"></div>
		                </div>
		        	</div>
		       	</div>
		       	<?php $class = ( $wpsc_options['wpsc_email_method'] == 2 ) ? '' : ' wpsc_hidden'; ?>
		       	<div class="panel panel-default<?php echo $class; ?>" id="wpsc_email_method_imap_settings">
                	<div class="panel-heading"><h4 class="panel-title">IMAP Settings</h4></div>
        			<div class="panel-body panel-body-wheat">
		                <div class="form-group">
		                	<div class="col-xs-12">
		                		<table id="wpsc_admin_imap_accounts" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth display">
                            		<thead>
                                		<th>Server</th>
                                		<th>Port</th>
                                		<th>Username</th>
                                		<th>Password</th>
                                		<th>Type</th>
                                		<th></th>
                            		</thead>
                            		<tfoot>
                            			<th>Server</th>
                                		<th>Port</th>
                                		<th>Username</th>
                                		<th></th>
                                		<th>Type</th>
                                		<th></th>
		                            </tfoot>
                            		<tbody>
                            			<?php
                            			$primary = false;
                            			$output = '';
		                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_imap";
		                                $imap_array = $wpdb->get_results( $sql, OBJECT );
		                                foreach( $imap_array as $imap ) {
		                                	if ( $imap->imap_type == 1 ) {
		                                		$type = 'Primary';
												$primary = true;
											} else {
												$type = 'Catch All';
											}
		                                    $output .= '<tr>';
		                                        $output .= '<td>' . $imap->imap_server . '</td>';
												$output .= '<td>' . $imap->imap_port . '</td>';
												$output .= '<td>' . $imap->imap_username . '</td>';
												$output .= '<td>********</td>';
												$output .= '<td>' . $type . '</td>';
												$output .= '<td><span class="wpsc_delete_imap" id="delete_imap_' . $imap->id . '" data-id="' . $imap->id . '"> <img src="' . WPSC_PLUGIN_URL . 'assets/images/32/trash_32.png" title="Delete" class="wpsc_help"> Delete</span></td>';
		                                    $output .= '</tr>';
		                                }
										echo $output;
										?>
                            		</tbody>
                        		</table>
                        	</div>
                       </div>
                       <div class="form-group">
		        			<div class="col-xs-12 col-md-2">
		        				<label for="wpsc_imap_server">IMAP Server <a href="#wpsc_imap_server_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_imap_server" class="wpsc_help"></a></label>
                        		<input type="input" id="wpsc_imap_server" name="wpsc_imap_server" value="" class="form-control" placeholder="IMAP Server IP Address" />
		        			</div>
		        			<div class="col-xs-12 col-md-1">
		        				<label for="wpsc_imap_port">IMAP Port <a href="#wpsc_imap_port_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_imap_port" class="wpsc_help"></a></label>
                        		<input type="input" id="wpsc_imap_port" name="wpsc_imap_port" value="" class="form-control" placeholder="IMAP Port" />
		        			</div>
		            		<div class="col-xs-12 col-md-4">
		            			<label for="wpsc_imap_username">Username</label>
                        		<input type="input" id="wpsc_imap_username" name="wpsc_imap_username" value="" class="form-control" placeholder="Username" />
		            		</div>
		            		<div class="col-xs-12 col-md-4">
		            			<label for="wpsc_imap_password">Password</label>
                        		<input type="password" id="wpsc_imap_password" name="wpsc_imap_password" value="" class="form-control" placeholder="Password" />
		            		</div>
		            		<div class="col-xs-12 col-md-1">
		            			<label for="wpsc_imap_password">Type <a href="#wpsc_imap_type_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_imap_type" class="wpsc_help"></a></label>
		            			<select id="wpsc_imap_type" name="wpsc_imap_type">
		            				<?php
		            				if ( $primary === false ) {
		            					?>
		            					<option value="1">Primary</option>
		            					<?php
									}
									?>
		            				<option value="2">Catch All</option>
		            			</select>
		            		</div>
		    			</div>
		    		</div>
		    	</div>
                <input type="hidden" id="wpsc_email_piping" name="wpsc_email_piping" value="<?php echo $wpsc_options['wpsc_email_piping']; ?>" class="form-control" />
                <button type="button" class="wpsc_wpsc_save_email btn btn-primary btn-sm">Save Settings</button>
            </form>
        </div>
    </div>
</div>