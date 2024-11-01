<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( isset( $_GET['rtid'] ) && $_GET['rtid'] != '' ) {
    $recurring_ticket_id = $_GET['rtid'];
    ?>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s</h4></div>
        <div class="panel-body panel-body-wheat">
            <p>Please wait... processing recurring ticket...</p>
        </div>
    </div>
    <?php
    do_wpsc_recurring_tickets( $recurring_ticket_id );
    ?>
    <meta http-equiv="refresh" content="3; url=<?php echo WPSC_ADMIN_URL . 'admin.php?page=wpsc_admin_recurring'; ?>" />
    <?php
    die();
}
?>

<div id="wpsc_admin_recurring_tickets" class="tab-pane fade active in">
    <?php
    //echo '<p>Cron Last Active: ' . $wpsc_options['wpsc_recurring_tickets_last_run'] . '</p>';
    $sql = "
        SELECT
            t.id,t.subject,t.client_id,t.agent_id,t.next_timestamp,t.schedule,t.category_id,t.enabled,
            s.status,s.colour AS status_colour,
            c.category,
            p.priority,p.colour AS priority_colour,
            ua.display_name AS agent
        FROM " . $wpdb->prefix . "wpsc_tickets_recurring t
        LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
        LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id";
    $tickets = $wpdb->get_results( $sql, OBJECT );
    if ( $wpdb->num_rows > 0 ) {
    	?>
    	<div class="panel panel-default">
			<div class="panel-heading"><h4 class="panel-title">Recurring <?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?>s</h4></div>
        	<div class="panel-body panel-body-wheat">
        		<form method="post" class="form-horizontal">
                	<div class="form-group">
                    	<div class="col-xs-12">
			                <table id="wpsc_admin_recurring_tickets_table" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth">
			                    <thead>
			                        <tr>
			                            <th><input type="checkbox" id="wpsc_select_all_recurring" /></th>
			                            <th>Client</th>
			                            <th>Subject</th>
			                            <th>Status</th>
			                            <th>Category</th>
			                            <th>Priority</th>
			                            <th>Agent</th>
			                            <th>Scheduled</th>
			                            <th>Next</th>
			                            <th></th>
			                        </tr>
			                    </thead>
			                    <tfoot>
			                        <tr>
			                            <th></th>
			                            <th>Client</th>
			                            <th>Subject</th>
			                            <th>Status</th>
			                            <th>Category</th>
			                            <th>Priority</th>
			                            <th>Agent</th>
			                            <th>Scheduled</th>
			                            <th>Next</th>
			                            <th></th>
			                        </tr>
			                    </tfoot>
			                    <tbody>
			                        <?php
			                        foreach( $tickets as $ticket ) {
			                            $user = get_user_by( 'id', $ticket->client_id );
			                            $status_background = $ticket->status_colour;
			                            $status_text = ( wpSupportCentre::wpsc_lightness( $background ) === true ) ? '#000000' : '#ffffff';
			                            $priority_background = $ticket->priority_colour;
			                            $priority_text = ( wpSupportCentre::wpsc_lightness( $background ) === true ) ? '#000000' : '#ffffff';
                                        $enabled_background = ( $ticket->enabled == 0 ) ? ' style="background-color:#C0C0C0;" ' : '';
			                            echo '<tr class="wpsc_recurring_ticket_row" ' . $enabled_background . ' id="' . $ticket->id . '">';
			                                echo '<td class="align_centre wpsc_select_recurring_ticket_td"><input type="checkbox" class="wpsc_select_recurring_ticket" value="' . $ticket->id . '" /></td>';
			                                echo '<td class="align_centre">' . $user->display_name . '</td>';
			                                echo '<td class="align_centre">' . substr( $ticket->subject, 0 ,25 ) . '...</td>';
			                                echo '<td class="align_centre" style="background-color:' . $status_background . ';color:' . $status_text . '">' . $ticket->status . '</td>';
			                                echo '<td class="align_centre">' . $ticket->category . '</td>';
			                                echo '<td class="align_centre" style="background-color:' . $priority_background . ';color:' . $priority_text . '">' . $ticket->priority . '</td>';
			                                echo '<td class="align_centre">' . $ticket->agent . '</td>';
			                                $schedule = array(
			                                    '1' => 'Daily',
			                                    '2' => 'Weekly',
			                                    '3' => 'Fortnightly',
			                                    '4' => 'Monthly',
			                                    '5' => 'Quarterly',
			                                    '6' => 'Annually'
			                                );
			                                echo '<td class="align_centre">' . $schedule[$ticket->schedule] . '</td>';
			                                echo '<td class="align_centre">' . date( "Y-m-d", strtotime( $ticket->next_timestamp ) ) . ' ' . $wpsc_options['wpsc_recurring_tickets_scheduled_time'] . '</td>';
											if ( $ticket->enabled == 0 ) {
												echo '<td></td>';
											} else {
												echo '<td class="align_centre wpsc_run_recurring_ticket_td"><a href="' . WPSC_ADMIN_URL . 'admin.php?page=wpsc_admin_recurring&rtid=' . $ticket->id . '">Run Now</a></td>';
											}
			                            echo '</tr>';
			                        }
			                        ?>
			                    </tbody>
			                </table>
						</div>
					</div>
				</form>
			</div>
		</div>
        <div id="wpsc_recurring_actions" class="wpsc_hidden">
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title">With selected...</h4></div>
                <div class="panel-body panel-body-wheat">
                    <form method="post" class="form-horizontal">
                        <div class="form-group">
                            <div class="col-xs-12 col-md-3">
                                <label for="wpsc_recurring_action_status">Status</label>
                                <select id="wpsc_recurring_action_status" name="wpsc_recurring_action_status" class="wpsc_recurring_action form-control">
                                    <?php
                                    echo '<option value="">Please select...</option>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_status ORDER BY status ASC";
                                    $results = $wpdb->get_results( $sql, OBJECT );
                                    if ( $wpdb->num_rows > 0 ) {
                                        foreach ($results as $status) {
                                            echo '<option value="' . $status->id . '">' . $status->status . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <label for="wpsc_recurring_action_category">Category</label>
                                <select id="wpsc_recurring_action_category" name="wpsc_recurring_action_category" class="wpsc_recurring_action form-control">
                                    <?php
                                    echo '<option value="">Please select...</option>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories ORDER BY category ASC";
                                    $results = $wpdb->get_results( $sql, OBJECT );
                                    if ( $wpdb->num_rows > 0 ) {
                                        foreach ($results as $category) {
                                            echo '<option value="' . $category->id . '">' . $category->category . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <label for="wpsc_recurring_action_priority">Priority</label>
                                <select id="wpsc_recurring_action_priority" name="wpsc_recurring_action_priority" class="wpsc_recurring_action form-control">
                                    <?php
                                    echo '<option value="">Please select...</option>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority ORDER BY priority ASC";
                                    $results = $wpdb->get_results( $sql, OBJECT );
                                    if ( $wpdb->num_rows > 0 ) {
                                        foreach ($results as $priority) {
                                            echo '<option value="' . $priority->id . '">' . $priority->priority . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <label for="wpsc_recurring_action_agent">Agent</label>
                                <select id="wpsc_recurring_action_agent" name="wpsc_recurring_action_agent" class="wpsc_recurring_action form-control">
                                    <?php
                                    echo '<option value="">Please select...</option>';
                                    $args = array(
                                        'orderby' => 'display_name',
                                        'order' => 'ASC'
                                    );
                                    $all_users = get_users( $args );
                                    foreach ( $all_users as $user ) {
                                        if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
                                            echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" name="wpsc_admin_apply_recurring_actions" id="wpsc_admin_apply_recurring_actions">Apply Changes</button> <a href="#wpsc_admin_apply_recurring_actions_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_admin_apply_recurring_actions_dialog" class="wpsc_help"></a>
                    </form>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<p>No recurring ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's found.</p>';
    }
    ?>
</div>