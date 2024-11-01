<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
$page_id = get_the_ID();
global $wpdb;
// processing
?>
<div class="wpsc-bootstrap-styles wpsc-bootstrap-styles-admin">
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
    <?php
    // ajax error
    ?>
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
    <?php
    // settings saved
    ?>
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
    <?php
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpsc_admin_recurring' ) {
        // recurring ticket actions
        ?>
        <div id="wpsc_admin_apply_recurring_actions_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Apply Changes</h4>
                    </div>
                    <div class="modal-body">
                        <p>Select the Status, Category, Agent and/or Priority for the selected recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s to be changed and then click <strong>Apply Changes</strong>.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'wpsc_admin_recurring' || $_GET['page'] == 'wp-support-centre' ) ) {
        // client autocomplete
        ?>
        <div id="wpsc_ticket_client_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p>For existing <?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?>s, start typing the name or email address of the <?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> and then select from the results.</p>
                        <p>For new <?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?>s, simply type the new <?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?>s full name (first and last name required)</p>
                        <p><strong>Note:</strong> Only registered users with a valid First and Last Name will be available for selection.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wp-support-centre' ) {
        // apply actions
        ?>
        <div id="wpsc_admin_apply_actions_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Apply Changes</h4>
                    </div>
                    <div class="modal-body">
                        <p>Select the Status, Category, Agent and/or Priority for the selected <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s to be changes to and then click <strong>Apply Changes</strong>. <strong><em>No notifications are sent.</em></strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // create ticket
        ?>
        <div id="wpsc_ticket_new_ticket_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Create <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Create <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></strong>: Click to create a new <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> <strong><em>without</em></strong> sending a notification.</p>
                        <p><strong>Create <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> & Notify</strong>: Click to create a new <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> <strong><em>and</em></strong> send a notification.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // save changes
        ?>
        <div id="wpsc_ticket_save_changes_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Save Changes</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Save Changes</strong>: Click to save changes to the Status, Category or Priority <strong><em>without</em></strong> sending a notification.</p>
                        <p><strong>Save Changes & Notify</strong>: Click to save changes to the Status, Category or Priority <strong><em>and</em></strong> send a notification.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // add note
        ?>
        <div id="wpsc_ticket_add_note_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Note</h4>
                    </div>
                    <div class="modal-body">
                        <p>Click to add a note. Changes to Status, Category and Priority will also be saved. <strong><em>No notification will be sent.</em></strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // send reply
        ?>
        <div id="wpsc_ticket_send_reply_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Send Reply</h4>
                    </div>
                    <div class="modal-body">
                        <p>Click to save the new note <strong><em>and send a notification.</em></strong>  Changes to Status, Category and Priority will also be saved.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // private thread
        ?>
        <div id="wpsc_ticket_private_thread_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Private Thread</h4>
                    </div>
                    <div class="modal-body">
                        <p>Selecting this will only allow the note to be seen by agents and supervisors (not clients)</p>
                        <p>Only applies when adding a note, will be ignored if sending a reply.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // thread notifications resent
        ?>
        <div id="wpsc_thread_notifications_sent_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Thread Notifications Sent</h4>
                    </div>
                    <div class="modal-body">
                        <p>The thread has been sent to <span id="wpsc_thread_resend_message"></span>.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // ticket not found
        ?>
        <div id="wpsc_ticket_not_found" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Not Found</h4>
                    </div>
                    <div class="modal-body">
                        <p>The selected <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> does not exist.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // ticket changes saved
        ?>
        <div id="wpsc_ticket_changes_saved" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Changes Saved</h4>
                    </div>
                    <div class="modal-body">
                        <p>The changes to this <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> have been saved.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'wp-support-centre' || $_GET['page'] == 'wpsc_admin_recurring' ) ) {
        // reply templates
        ?>
        <div id="wpsc_templates_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Select Reply Template</h4>
                    </div>
                    <div class="modal-body">
                        <?php
                        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_templates";
                        $templates = $wpdb->get_results( $sql, OBJECT );
                        if ( $wpdb->num_rows > 0 ) {
                            ?>
                            <table class="table table-striped table-bordered wpsc_admin_datatable wpsc_fullwidth" id="wpsc_admin_templates_select_table" data-editor="">
                                <thead>
                                    <tr>
                                        <th>Template</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>Template</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php
                                    foreach( $templates as $template ) {
                                        echo '<tr class="wpsc_template_insert_row" data-id="' . $template->id . '">';
                                            echo '<td>' . $template->label . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        } else {
                            echo '<p>No reply templates found.</p>';
                        }
                        ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ( $page_id == $wpsc_options['wpsc_support_page'] || ( isset( $_GET['page'] ) && $_GET['page'] == 'wp-support-centre' ) ) {
        // email inputs
        ?>
        <div id="wpsc_ticket_email_input" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Email Addresses</h4>
                    </div>
                    <div class="modal-body">
                        <p>Seperate multiple addresses by a comma (,)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpsc_admin_settings' ) {
        // item name
        ?>
        <div id="wpsc_item_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Item Name</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Item Name</strong>: eg: Ticket, Job, Task etc... (Default: Ticket)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // end user name
        ?>
        <div id="wpsc_client_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">End User Name</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>End User Name</strong>: eg: Client, Customer, User etc... (Default: Client)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // support page
        ?>
        <div id="wpsc_support_page_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Support Page</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Support Page</strong>: The page for the front end of <?php echo WPSC_TITLE; ?></p>
                        <p>Use shortcode <strong>[wpsc_tickets]</strong> to display the front end of <?php echo WPSC_TITLE; ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // recurring ticket create time
        ?>
        <div id="wpsc_recurring_tickets_scheduled_time_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s Create Time</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s Create Time</strong>: Note: Time is approximate. <a href="https://developer.wordpress.org/plugins/cron/understanding-wp-cron-scheduling/" target="_blank">Click here</a> for more information.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // from name
        ?>
        <div id="wpsc_email_from_name_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">From Name</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>From Name</strong>: eg: Helpdesk, Support etc... (Default: <?php echo get_option( 'blogname '); ?>)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // from email
        ?>
        <div id="wpsc_email_from_email_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">From Email</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>From Email</strong>: (Default: <?php echo get_option( 'admin_email '); ?>)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // reply to email
        ?>
        <div id="wpsc_email_reply_to_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Reply To Email</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Reply To Email</strong>: (Default: <?php echo get_option( 'admin_email '); ?>)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // signature shortcodes
        ?>
        <div id="wpsc_signature_shortcodes" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Available Shortcodes</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped table-bordered wpsc_admin_datatable wpsc_fullwidth" id="wpsc_signature_shortcodes_table" data-editor="">
                            <thead>
                                <tr>
                                    <th>Shortcode</th>
                                    <th>Output</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Template</th>
                                    <th>Output</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <tr><td>[wpsc_blog_name]</td><td><?php echo get_option( 'blogname' ); ?></td></tr>
                                <tr><td>[wpsc_site_url]</td><td><?php echo home_url( '/' ); ?></td></tr>
                                <tr>
                                    <td>[wpsc_support_centre_url]</td>
                                    <td>
                                        <?php
                                        if ( $wpsc_options['wpsc_support_page'] != 0 ) {
                                            echo get_permalink( $wpsc_options['wpsc_support_page'] );
                                        } else {
                                            echo '(Not Set)';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr><td>[wpsc_signature_from_name]</td><td><?php echo $wpsc_options['wpsc_email_from_name']; ?></td></tr>
                                <tr><td>[wpsc_signature_from_email]</td><td><?php echo $wpsc_options['wpsc_email_from_email']; ?></td></tr>
                                <tr><td>[wpsc_signature_reply_to]</td><td><?php echo $wpsc_options['wpsc_email_reply_to']; ?></td></tr>
                                <tr><td>[wpsc_signature_agent_name]</td><td>Agent Name</td></tr>
                                <tr><td>[wpsc_signature_agent_email]</td><td>Agent Email</td></tr>
                                <tr><td>[wpsc_signature]</td><td>Signature</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // enable email piping
        ?>
        <div id="wpsc_enable_email_piping_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Enable Email Piping</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Email Piping</strong></p>
                        <p>Email Piping is the method of sending email messages to a program allowing the program to process the message.</p>
                        <p>It is recommended that the email address being used be dedicated to email piping only.</p>
                        <p>You will also need to enable Email Piping within your Email Account / Hosting Server settings.</p>
                        <p><a href="https://wordpress.org/support/topic/where-to-find-email-piping-settings">Click here</a> for more information.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // enable email piping catch all
        ?>
        <div id="wpsc_enable_email_piping_catch_all_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Enable Email Piping</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Email Piping</strong></p>
                        <p>Often clients will send emails that you wish to include in a ticket that are not routed to the email account handling email piping.</p>
                        <p>Email Piping Catch All will catalog these emails and allow you to add the email to an existing ticket as a new thread or create a new ticket using the email.</p>
                        <p>You will also need to enable Email Piping within your Email Account / Hosting Server settings.</p>
                        <p><a href="https://wordpress.org/support/topic/where-to-find-email-piping-settings">Click here</a> for more information.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // piping email
        ?>
        <div id="wpsc_email_piping_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Piping Email</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Piping Email</strong>: (Default: <?php echo get_option( 'admin_email '); ?>)</p>
                        <p><strong>This must be configured in order for email piping to work.</strong></p>
                        <p><strong>Note: </strong>If you use more than one email account for email piping, please enter each email address seperated by a comma.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // piping add email to ticket
        ?>
        <div id="wpsc_add_email_to_ticket_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Email to Ticket</h4>
                    </div>
                    <div class="modal-body">
                        <p><strong>Success!</strong> Email has been added to selected ticket</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // default agent
        ?>
        <div id="wpsc_default_agent_dialog" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Default Agent</h4>
                    </div>
                    <div class="modal-body">
                        <p>When a new <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> is created by a <?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?>, this is the agent the <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> will be assigned to.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Continue</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpsc_admin_notifications' ) {
        // notification shortcodes
        ?>
        <div id="wpsc_notification_shortcodes" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Available Shortcodes</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped table-bordered wpsc_admin_datatable wpsc_fullwidth" id="wpsc_notification_shortcodes_table" data-editor="">
                            <thead>
                                <tr>
                                    <th>Shortcode</th>
                                    <th>Output</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Template</th>
                                    <th>Output</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <tr><td>[wpsc_item]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?></td></tr>
                                <tr><td>[wpsc_client]</td><td><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?></td></tr>
                                <tr><td>[wpsc_plugin_url]</td><td>WP Support Centre Plugin URL (<?php echo WPSC_PLUGIN_URL; ?>)</td></tr>
                                <tr><td>[wpsc_ticket_url]</td><td>Client Ticket URL (<?php echo WPSC_TICKET_URL . '/tickets/?ticket_id=xxx'; ?>)</td></tr> <?php // task to be completed ?>
                                <tr><td>[wpsc_admin_ticket_url]</td><td>Admin Ticket URL (<?php echo admin_url( 'admin.php' ) . '?page=wp-support-centre&ticket_id=xxx'; ?>)</td></tr>
                                <tr><td>[wpsc_ticket_no]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Number</td></tr>
                                <tr><td>[wpsc_ticket_subject]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Subject</td></tr>
                                <tr><td>[wpsc_ticket_status]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Status</td></tr>
                                <tr><td>[wpsc_ticket_category]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Category</td></tr>
                                <tr><td>[wpsc_ticket_priority]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Priority</td></tr>
                                <tr><td>[wpsc_thread_message]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Thread</td></tr>
                                <tr><td>[wpsc_ticket_agent]</td><td><?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> Agent</td></tr>
                                <tr><td>[wpsc_signature]</td><td>Signature</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    if ( $page_id == $wpsc_options['wpsc_support_page'] ) {
        // front new ticket created
        ?>
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
        <?php
    }
    ?>
</div>